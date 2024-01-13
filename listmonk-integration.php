<?php
/*
Plugin Name: listmonk Wordpress Integration
Plugin URI: https://www.wouternieuwenhuizen.nl
Description: Connects Wordpress to listmonk through API, sending mailing list subscriber data to listmonk.
Author: Wouter
Version: 0.3
Requires PHP: 7.4
Requires at least: 5.7
Author URI: https://www.wouternieuwenhuizen.nl
License: GNU General Public License v3.0
License URI: https://www.gnu.org/licenses/gpl-3.0.html#license-textf
*/

// required for encrypting the listmonk password
require_once plugin_dir_path( __FILE__ ) . 'fsd-data-encryption.php';

## get user ip
function get_the_user_ip() {
    if ( ! empty( $_SERVER['HTTP_CLIENT_IP'] ) ) {
    //check ip from share internet
    $ip = $_SERVER['HTTP_CLIENT_IP'];
    } elseif ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
    //to check ip is passed from proxy
    $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
    $ip = $_SERVER['REMOTE_ADDR'];
    }
    return ($ip);
}

// check if listmonk credentials are configured
function are_listmonk_settings_configured() {
    $listmonk_url = get_option('listmonk_url');
    $listmonk_username = get_option('listmonk_username');
    $listmonk_password = get_option('listmonk_password');

    return !empty($listmonk_url) && !empty($listmonk_username) && !empty($listmonk_password);
}

## function to send data to listmonk through cURL
function send_data_to_listmonk($url, $body, $username, $password) {
    // Create a new cURL resource

    $ch = curl_init($url);

    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/json',
        'Authorization: Basic ' . base64_encode($username . ':' . $password)
    ));

    // Set the content type to application/json
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    // Execute the POST request
    $result = curl_exec($ch);
    if(curl_errno($ch)) {
        error_log('cURL error: ' . curl_error($ch)); // log curl error
    }

    curl_close($ch); // Close cURL resource

    return $result;
}

// this function sends WPforms data to an external API (listmonk) through https

function wpf_dev_process_complete( $fields, $entry, $form_data, $entry_id ) {
    if (!are_listmonk_settings_configured()) {
        return; // Abort if settings are not configured
    }
    $listmonk_wpforms_form_id = absint(get_option('listmonk_wpforms_form_id')); // convert form id from option to integer
    $form_listmonk_on = get_option('form_listmonk_on'); // check if the listmonk form option is disabled in settings

    // check if the form id matches the form id from the settings page and if the listmonk form option is enabled
    if (get_option('form_listmonk_on') != 'yes' || absint($form_data['id']) !== $listmonk_wpforms_form_id) { 
        return;
    }

    // define variables
    $ip = get_the_user_ip(); // define ip address of user, used for listmonk consent recording
    $website_name = get_bloginfo( 'name' ); // Retrieves the website's name from the WordPress database
    $listmonk_list_id = get_option('listmonk_list_id', 0); // get listmonk list id from settings page

    //these attributes are used by listmonk as extra data for each subscriber. can be changed to your liking
    $attributes = [
        'subscription_origin' => 'website form', // this is the origin of the subscription, as opposed to payment
        'confirmed_consent' => 'true', // user gave consent to receive newsletter
        'ip_address' => $ip, // ip address of user
        'consent_agreement' => 'I consent to receiving periodic newsletters from ' . $website_name . '.', // Use the website name dynamically
        ] ;

    // remove email from name field input
    $pattern = '/[^@\s]*@[^@\s]*\.[^@\s]*/'; // to avoid spam
    $replacement = '[removed]';

    // sanitize name input
    $name = strip_tags($fields['1']['value']); // get name from form
    $name_email_stripped = preg_replace($pattern, $replacement, $name); // remove email from name field input
    $name_stripped_all = preg_replace('/[a-zA-Z]*[:\/\/]*[A-Za-z0-9\-_]+\.+[A-Za-z0-9\.\/%&=\?\-_]+/i', $replacement, $name_email_stripped); // remove urls from name field input

    // this body will be sent to listmonk 
    $body = array(
		'name' => $name_stripped_all,
		'email' => $fields['2']['value'],
        'status' => 'enabled',
        'lists' => [(int)$listmonk_list_id],
        'preconfirm_subscriptions' => false,
        'attribs' => $attributes,   
	);

    #listmonk credentials
    $listmonk_url = get_option('listmonk_url');
    $listmonk_username = get_option('listmonk_username');

    ## password decryption
    $encryption = new FSD_Data_Encryption();
    $encrypted_password = get_option('listmonk_password');
    $listmonk_password = $encryption->decrypt($encrypted_password);

    // append the url from the settings page with the correct API endpoint
    $url = $listmonk_url . '/api/subscribers';    
    
    // using the send_data_to_listmonk function we defined earlier, we communicate with the listmonk API through cURL
    send_data_to_listmonk($url, $body, $listmonk_username, $listmonk_password);

}
add_action( 'wpforms_process_complete', 'wpf_dev_process_complete', 10, 4 );

## send subscriber data to listmonk after paying 

add_action( 'woocommerce_thankyou', 'sub_newsletter_after_order', 10, 1 );
function sub_newsletter_after_order( $order_id ){
    // check ff the listmonk checkout component is enabled in settings
    if (get_option('checkout_listmonk_on') != 'yes') { 
        return;
    }
    if( ! $order_id ){ // if order id is not set, return
        return;
    }

    if (!are_listmonk_settings_configured()) {
        return; // Abort if settings are not configured
    }

    $order = wc_get_order( $order_id ); // Get an instance of the WC_Order Object

    // check for user newsletter consent
    $field_name = 'newsletter_optin'; // change this field to the name of your custom field for storing user consent in a checkbox
    $consent = $order->get_meta($field_name);
    if($consent != 'true'){
        return;
    }
    
    // get user info from the woocommerce order API
    $email = $order->get_billing_email(); // Get Customer billing email
    $name = $order->get_billing_first_name();
    $country = $order->get_billing_country();
    $ip = $order->get_customer_ip_address();
    $website_name = get_bloginfo( 'name' ); // Retrieves the website's name from the WordPress database

    $listmonk_list_id = get_option('listmonk_list_id', 0);

    ## for listmonk
    $attributes = [
        'country' => $country,
        'subscription_origin' => 'payment',
        'ip_address' => $ip,
        'confirmed_consent' => true,
        'consent_agreement' => 'I consent to receiving periodic newsletters from ' . $website_name . '.',
    ] ;

    // body to send with POST to the API
    $body = array(
        'name'  => $name,
        'email' => $email,
        'status' => 'enabled',
        #'lists' => [2],
        'lists' => [(int)$listmonk_list_id],
        'attribs' => $attributes,
        'preconfirm_subscriptions' => true,
     ) ;

    #listmonk credentials
    $listmonk_url = get_option('listmonk_url');
    $listmonk_username = get_option('listmonk_username');

    ## password decryption using the fsd-data-encryption class
    $encryption = new FSD_Data_Encryption();
    $encrypted_password = get_option('listmonk_password');
    $listmonk_password = $encryption->decrypt($encrypted_password);
    
    // append the url from the settings page
    $url = $listmonk_url . '/api/subscribers';

    // using the send_data_to_listmonk function we defined earlier, we communicate with the listmonk API through cURL
    send_data_to_listmonk($url, $body, $listmonk_username, $listmonk_password);
}

### SETTINGS PAGE ###

// Add a top-level menu for the plugin settings in the admin dashboard
add_action('admin_menu', 'listmonk_top_lvl_menu');
function listmonk_top_lvl_menu(){
    add_options_page(
        'listmonk Integration Settings', // Page title
        'listmonk Integration', // Menu title
        'manage_options', // Capability required
        'listmonk_integration', // Menu slug
        'listmonk_integration_page_callback', // Function to display the settings page
        4 // Position in the menu
    );
}

// Callback function to render the plugin settings page
function listmonk_integration_page_callback(){
    ?>
        <div class="wrap">
            <h1><?php echo get_admin_page_title(); ?></h1>
            <form method="post" action="options.php">
                <?php
                    settings_fields('listmonk_integration_settings');
                    do_settings_sections('listmonk_integration');
                    submit_button();
                ?>
            </form>
        </div>
            <!-- "Buy Me a Coffee" button -->
    <div style="margin-top: 20px; transform: scale(0.8); transform-origin: top left;">
        <p><em>Enjoying this free & open source plugin?</em></p><script type="text/javascript" src="https://cdnjs.buymeacoffee.com/1.0.0/button.prod.min.js" data-name="bmc-button" data-slug="woutern" data-color="#FFDD00" data-emoji="" data-font="Poppins" data-text="Buy me a coffee" data-outline-color="#000000" data-font-color="#000000" data-coffee-color="#ffffff"></script>
    </div>
    <?php
}

// is inputted url actually reachable?
function is_url_reachable($url){
    // Use cURL to attempt to connect to the URL
    $handle = curl_init($url);
    curl_setopt($handle,  CURLOPT_RETURNTRANSFER, TRUE);

    // Get the HTTP response code
    $response = curl_exec($handle);
    $httpCode = curl_getinfo($handle, CURLINFO_HTTP_CODE);

    curl_close($handle);

    // Check if the HTTP response code is 200 (OK)
    if($httpCode == 200) {
        return true;
    } else {
        return false;
    }
}

// Sanitize checkbox input
function sanitize_checkbox($input){
    return 'on' === $input ? 'yes' : 'no';
}

function sanitize_listmonk_url($input){
    // Log the input URL
    error_log('sanitize_listmonk_url - Input URL: ' . $input);

    // Trim whitespace
    $url = trim($input);

    // Log the trimmed URL
    error_log('sanitize_listmonk_url - Trimmed URL: ' . $url);

    // Check if the URL is empty
    if (empty($url)) {
        error_log('sanitize_listmonk_url - URL is empty after trim');
        return '';
    }

    // Check if "https://" is missing, prepend it if necessary
    if (substr($url, 0, 8) !== "https://" && substr($url, 0, 7) !== "http://") {
        $url = "https://" . $url;
        error_log('sanitize_listmonk_url - Prepended https:// to URL: ' . $url);
    }

    // Remove a trailing slash if present
    if (substr($url, -1) == '/') {
        $url = rtrim($url, '/');
    }

    // Validate the URL
    if (filter_var($url, FILTER_VALIDATE_URL) === FALSE) {
        error_log('sanitize_listmonk_url - URL validation failed: ' . $url);
        add_settings_error(
            'listmonk_url', 
            'invalid_url', 
            'Please enter a valid URL.'
        );
        return get_option('listmonk_url');
    }

    // Check if the URL is reachable
    if (!is_url_reachable($url)) {
        add_settings_error(
            'listmonk_url', 
            'unreachable_url', 
            'The URL you provided is not reachable, so it cannot be used to connect to listmonk.'
        );
        return get_option('listmonk_url');
    }

    error_log('sanitize_listmonk_url - URL validation passed: ' . $url);
    return $url;
}

// Sanitize and validate the list ID
function sanitize_list_id($input){
    $new_input = absint($input);
    if ($new_input < 0) {
        add_settings_error(
            'listmonk_list_id', 
            'invalid_listmonk_list_id', 
            'listmonk list ID should be a positive number.'
        );
        return get_option('listmonk_list_id');
    }
    return $new_input;
}

function sanitize_listmonk_password($input){
    if (empty($input)) {
        return get_option('listmonk_password');
    }
    // Encrypt the new password
    $encryption = new FSD_Data_Encryption();
    $encrypted_password = $encryption->encrypt(sanitize_text_field($input));

    return $encrypted_password;
}

// Initialize plugin settings
add_action('admin_init', 'listmonk_settings_fields');
function listmonk_settings_fields(){
    $page_slug = 'listmonk_integration';
    $option_group = 'listmonk_integration_settings';

    // Add 'Plugin Components' section
    add_settings_section(
        'listmonk_plugin_components', // Section ID
        'Plugin Components', // Section title
        'listmonk_plugin_components_description', // Callback for section description
        $page_slug // Page slug
    );

    // Add 'listmonk Credentials' section
    add_settings_section(
        'listmonk_credentials', // Section ID
        'listmonk Credentials', // Section title
        'listmonk_credentials_description', // Callback for section description
        $page_slug // Page slug
    );

    // Register and add settings fields
    register_setting($option_group, 'checkout_listmonk_on', 'sanitize_checkbox');
    add_settings_field(
        'checkout_listmonk_on',
        'Enable listmonk integration on Woocommerce Checkout:',
        'render_checkbox_field',
        $page_slug,
        'listmonk_plugin_components',
        array('name' => 'checkout_listmonk_on')
    );

    register_setting($option_group, 'form_listmonk_on', 'sanitize_checkbox');
    add_settings_field(
        'form_listmonk_on',
        'Enable listmonk integration on a custom form using WPForms plugin:',
        'render_checkbox_field',
        $page_slug,
        'listmonk_plugin_components',
        array('name' => 'form_listmonk_on')
    );

    register_setting($option_group, 'listmonk_wpforms_form_id', 'sanitize_list_id');
    add_settings_field(
        'listmonk_wpforms_form_id',
        'WPForms Form ID:',
        'render_wpforms_form_id_field',
        $page_slug,
        'listmonk_plugin_components',
        array('name' => 'listmonk_wpforms_form_id')
    );

    register_setting($option_group, 'listmonk_list_id', 'sanitize_list_id');
    add_settings_field(
        'listmonk_list_id',
        'listmonk list ID:',
        'render_listmonk_list_id_field',
        $page_slug,
        'listmonk_credentials',
        array('name' => 'listmonk_list_id')
    );

    register_setting($option_group, 'listmonk_url', 'sanitize_listmonk_url');
    add_settings_field(
        'listmonk_url',
        'listmonk URL:',
        'render_text_field',
        $page_slug,
        'listmonk_credentials',
        array('name' => 'listmonk_url')
    );

    register_setting($option_group, 'listmonk_username', 'sanitize_text_field');
    add_settings_field(
        'listmonk_username',
        'listmonk Username:',
        'render_text_field',
        $page_slug,
        'listmonk_credentials',
        array('name' => 'listmonk_username')
    );

    register_setting($option_group, 'listmonk_password', 'sanitize_listmonk_password');
    add_settings_field(
        'listmonk_password',
        'listmonk Password:',
        'render_text_field',
        $page_slug,
        'listmonk_credentials',
        array('name' => 'listmonk_password')
    );
}
/* works 
// Function to render checkbox fields
function render_checkbox_field($args){
    $value = get_option($args['name']);
    ?>
        <label>
            <input type="checkbox" name="<?php echo esc_attr($args['name']); ?>" <?php checked($value, 'yes'); ?> /> Yes
        </label>
    <?php
} */ 

// Description for the 'Plugin Components' section
function listmonk_plugin_components_description() {
    echo '<p>listmonk integration can be enabled in two ways. (1) On the Woocommerce checkout. Customers can check a box to subscribe to the newsletter.
    For this to work you need to  work with a custom fields plugin to show a newsletter checkbox to the user.
    The default custom field that is checked for consent is "newsletter_optin". You can create this for example with the custom fields plugin. See the README for more info.
    (2) On a custom page, using a custom newsletter form from the WPForms plugin that you can include anywhere on your website. You can enter the WPForms form ID on this settings page.
    </p><p>See <a href="https://listmonk.app/docs/">the listmonk documentation</a> for more information on how to setup listmonk, either on your own server or easily hosted versions on services like <a href="https://railway.app/new/template/listmonk">Railway</a> and <a href="https://www.pikapods.com/pods?run=listmonk">Pikapods</a></p>
    ';
}

// Description for the 'listmonk Credentials' section
function listmonk_credentials_description() {
    echo '<p>In order for the integration to work, you need to provide your listmonk credentials. First input the listmonk list ID you want to 
    send all new subscribers to. This ID is shown in listmonk when you click on a list. Second, you input the url of your listmonk server. Third, you input your listmonk username and password for authentication.</p>';
}

// Hook into the admin_enqueue_scripts action
add_action('admin_enqueue_scripts', 'listmonk_admin_styles');

// Function to add custom styles to the admin page
function listmonk_admin_styles($hook) {
    // Check if we're on the specific plugin admin page
    if ('settings_page_listmonk_integration' !== $hook) {
        return;
    }

    // Add custom CSS
    ?>
    <style type="text/css">
        .listmonk-text-input {
            width: 100%; /* Adjust the width as needed */
            max-width: 350px; /* Optional: Set a maximum width */
        }
        .listmonk-number-input {
            width: 70px; /* Specific width for number inputs */
        }
    </style>
    <?php
}

function render_text_field($args){
    $field_type = 'text';
    $placeholder = '';
    $help_text = '';

    // If the field is for the password
    if ($args['name'] == 'listmonk_password') {
        $field_type = 'password';
        $option = get_option($args['name'], '');
        
        // Always use informative placeholder text and help text for the password field
        $placeholder = 'placeholder="Enter new password to change"';
        $help_text = '<p class="description">Leave blank to keep the current password.</p>';
    } else {
        $option = get_option($args['name'], '');
    }

    printf(
        '<input class="listmonk-text-input" type="%s" id="%s" name="%s" value="%s" %s />%s',
        esc_attr($field_type),
        esc_attr($args['name']),
        esc_attr($args['name']),
        esc_attr($field_type == 'password' ? '' : $option),
        $placeholder,
        $help_text
    );
}

function render_checkbox_field($args){
    $value = get_option($args['name']);
    
    // Check if WPForms or any plugin with a name starting with "wpforms" is active
    if ($args['name'] === 'form_listmonk_on' && !is_plugin_active_with_prefix('wpforms')) {
        // WPForms or a matching plugin is not active and the field is "form_listmonk_on," disable the checkbox and set it as unchecked
        $disabled = 'disabled="disabled"';
        $checked = '';
        $message = 'WPForms not installed'; // Message for when WPForms is not installed
    } else {
        // WPForms or a matching plugin is active or the field is not "form_listmonk_on," enable the checkbox and set its value based on the option
        $disabled = '';
        $checked = checked($value, 'yes', false); // Set to checked if the option value is ' yes'
        $message = ''; // No message when WPForms is active or the field is not "form_listmonk_on"
    }
    
    ?>
    <label>
        <input type="checkbox" name="<?php echo esc_attr($args['name']); ?>" <?php echo $checked; ?> <?php echo $disabled; ?> /> Yes
    </label>
    <?php
    if ($message) {
        echo '<p class="description">' . esc_html($message) . '</p>';
    }
    // Include JavaScript for dynamic toggling
    ?>
    <script type="text/javascript">
        jQuery(document).ready(function($) {
            $('#<?php echo esc_attr($args['name']); ?>').change(function() {
                var isChecked = $(this).is(':checked');
                console.log('Checkbox changed: ', isChecked);

                // Explicitly targeting the WPForms Form ID field
                if (isChecked) {
                    $('#listmonk_wpforms_form_id').removeAttr('disabled');
                } else {
                    $('#listmonk_wpforms_form_id').attr('disabled', 'disabled');
                }
            }).change(); // Trigger change to set initial state
        });
    </script>
    <?php
}
function is_plugin_active_with_prefix($prefix){
    $active_plugins = get_option('active_plugins');
    
    foreach ($active_plugins as $plugin) {
        if (strpos($plugin, $prefix) === 0) {
            return true;
        }
    }
    
    return false;
}

// Function to render WPForms Form ID field
function render_wpforms_form_id_field($args) {
    $option = get_option($args['name'], '1');
    $disabled = get_option('form_listmonk_on') !== 'yes' ? 'disabled' : '';

    printf(
        '<input class="listmonk-number-input" type="number" id="listmonk_wpforms_form_id" name="%s" value="%d" %s />',
        esc_attr($args['name']),
        esc_attr($option),
        $disabled
    );
}

// Function to render listmonk List ID field
function render_listmonk_list_id_field($args) {
    $option = get_option($args['name'], '1');

    printf(
        '<input class="listmonk-number-input" type="number" id="listmonk_list_id" name="%s" value="%d" />',
        esc_attr($args['name']),
        esc_attr($option)
    );
}

?>
