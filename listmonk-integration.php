<?php
/*
Plugin Name: Integration for listmonk mailing list and newsletter manager
Text Domain: integration-listmonk
Plugin URI: https://github.com/post-duif/integration-listmonk-wordpress-plugin
Description: Connects the open source listmonk mailing list and newsletter service to WordPress and WooCommerce, so users can subscribe to your mailing lists through a form on your website or through WooCommerce checkout.
Author: postduif
Version: 1.2.0
Requires PHP: 7.4
Requires at least: 5.7
License: GNU General Public License v3.0
License URI: https://www.gnu.org/licenses/gpl-3.0.html#license-textf
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

// check if WooCommerce checkout block is active
function listmonk_is_checkout_block_enabled() {
    $checkout_page_id = wc_get_page_id('checkout');

    if ($checkout_page_id && $checkout_page_id != -1) {
        $has_checkout_block = WC_Blocks_Utils::has_block_in_page($checkout_page_id, 'woocommerce/checkout');
        return $has_checkout_block;
    } else {
        return false;
    }
}

// for when the user uninstalls

// Register the uninstall hook
register_uninstall_hook(__FILE__, 'listmonk_uninstall');

// ensure that all options are deleted when the user uninstalls the plugin
function listmonk_uninstall() {
    delete_option('listmonk_username');
    delete_option('listmonk_password');
    delete_option('listmonk_url');
    delete_option('listmonk_list_id');
    delete_option('listmonk_wpforms_form_id');
    delete_option('listmonk_form_on');
    delete_option('listmonk_checkout_on');
}


// start of the code to add newsletter checkbox to checkout

function listmonk_initialize_listmonk_integration() {
    if (get_option('listmonk_checkout_on') !== 'yes') {
        return;
    }

    add_filter('woocommerce_checkout_fields', 'listmonk_add_newsletter_checkbox_to_checkout'); // add newsletter checkbox to checkout
    add_action('woocommerce_checkout_update_order_meta', 'listmonk_save_newsletter_subscription_checkbox'); // save newsletter checkbox value to order meta
    add_action('woocommerce_admin_order_data_after_billing_address', 'listmonk_display_newsletter_subscription_in_admin_order_meta', 10, 1); // display newsletter checkbox value in admin order meta
}
// initialize the listmonk integration
add_action('wp_loaded', 'listmonk_initialize_listmonk_integration');

// add newsletter checkbox to checkout
function listmonk_add_newsletter_checkbox_to_checkout($fields) {
    if(listmonk_is_checkout_block_enabled()) {
        return; // Abort if the checkout block is enabled
    }
    $email_priority = isset($fields['billing']['billing_email']['priority']) ? $fields['billing']['billing_email']['priority'] : 20;
    
    // Retrieve the custom label text from the options, with a default value
    $optin_label = esc_html(get_option('listmonk_optin_text', __('Subscribe to our newsletter', 'integration-listmonk')));

    // Check if $optin_label is empty, if so, use the default text
    if (empty($optin_label)) {
        $optin_label = __('Subscribe to our newsletter', 'integration-listmonk');
    }
    $fields['billing']['newsletter_optin'] = array(
        'type'      => 'checkbox',
        'label'     => $optin_label,  // Use the retrieved label text here
        'required'  => false,
        'class'     => array('form-row-wide'),
        'clear'     => true,
        'priority'  => $email_priority + 2, // Slightly higher priority than email
    );

    return $fields;
}


// save newsletter checkbox value to order meta
function listmonk_save_newsletter_subscription_checkbox($order_id) {
    $newsletter_optin = isset($_POST['newsletter_optin']) ? 'true' : 'false';

    $order = wc_get_order($order_id);
    $order->update_meta_data('newsletter_optin', $newsletter_optin);
    $order->save();
}

// display newsletter checkbox value in admin order meta
function listmonk_display_newsletter_subscription_in_admin_order_meta($order) {
    $subscribed = $order->get_meta('newsletter_optin', true);
    $display_value = ($subscribed === 'true') ? 'Yes' : 'No'; // Display 'Yes' for 'true', 'No' otherwise`
    echo '<p><strong>' . __('Newsletter subscription consent (listmonk):', 'integration-listmonk') . '</strong> ' . $display_value . '</p>';
}

// end of the code to add newsletter checkbox to checkout

// required for encrypting the listmonk password
require_once plugin_dir_path( __FILE__ ) . 'includes/fsd-data-encryption.php';

## get user ip
function listmonk_get_the_user_ip() {
    if ( ! empty( $_SERVER['HTTP_CLIENT_IP'] ) ) {
    //check ip from share internet
    $ip = sanitize_text_field($_SERVER['HTTP_CLIENT_IP']);
    } elseif ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
    //to check ip is passed from proxy
    $ip = sanitize_text_field($_SERVER['HTTP_X_FORWARDED_FOR']); // get ip address of user
    } else {
    $ip = sanitize_text_field($_SERVER['REMOTE_ADDR']); // get ip address of user
    }
    return ($ip); // return ip address
}

// check if listmonk credentials are configured
function listmonk_are_listmonk_settings_configured() {
    $listmonk_url = sanitize_text_field(get_option('listmonk_url')); // get listmonk url from settings page
    $listmonk_username = sanitize_text_field(get_option('listmonk_username')); // get listmonk username from settings page
    $listmonk_password = sanitize_text_field(get_option('listmonk_password')); // get listmonk password from settings page

    return !empty($listmonk_url) && !empty($listmonk_username) && !empty($listmonk_password); // return true if all settings are configured
}

## function to send data to listmonk through WordPress HTTP API
function listmonk_send_data_to_listmonk_wordpress_http_api($url, $body, $username, $password) {
    // Sanitize the URL
    $url = esc_url_raw($url);

    // Prepare the headers
    $headers = array(
        'Authorization' => 'Basic ' . base64_encode($username . ':' . $password),
        'Content-Type' => 'application/json',
    );

    // Setup the body and headers
    $args = array(
        'body'    => json_encode($body),
        'headers' => $headers,
        'method'  => 'POST'
    );

    // Make the request
    $response = wp_remote_post($url, $args);

    // Check for error in response
    if (is_wp_error($response)) {
        error_log('Listmonk API error: ' . $response->get_error_message());
        return [
            'status_code' => wp_remote_retrieve_response_code($response),
            'body' => wp_remote_retrieve_body($response),
            'error_message' => 'Listmonk API error: ' . $response->get_error_message()

        ];
    }

    $httpCode = wp_remote_retrieve_response_code($response);
    $body = wp_remote_retrieve_body($response);

    // Handling different HTTP error codes
    switch ($httpCode) {
        case 400:
            error_log('Listmonk API error: Missing or bad request parameters or values');
            break;
        case 403:
            error_log('Listmonk API error: Session expired or invalidate. Must relogin');
            break;
        case 404:
            error_log('Listmonk API error: Request resource was not found');
            break;
        case 405:
            error_log('Listmonk API error: Request method is not allowed on the requested endpoint');
            break;
        case 410:
            error_log('Listmonk API error: The requested resource is gone permanently');
            break;
        case 429:
            error_log('Listmonk API error: Too many requests to the API (rate limiting)');
            break;
        case 500:
            error_log('Listmonk API error: Something unexpected went wrong');
            break;
        case 502:
            error_log('Listmonk API error: The backend OMS is down and the API is unable to communicate with it');
            break;
        case 503:
            error_log('Listmonk API error: Service unavailable; the API is down');
            break;
        case 504:
            error_log('Listmonk API error: Gateway timeout; the API is unreachable');
            break;
        default:
            if ($httpCode >= 400) {
                // Generic error for other 4xx and 5xx HTTP codes
                error_log("Listmonk API error (HTTP code $httpCode): " . json_decode($body)->message);
                $errorMessage = "Listmonk API error (HTTP code $httpCode): " . json_decode($body)->message;
            }
    }

    return [
        'status_code' => $httpCode,
        'body' => json_decode($body, true),
        'error_message' => $errorMessage
    ];
}

// this function sends WPforms data to an external API (listmonk) through https
function listmonk_send_data_through_wpforms( $fields, $entry, $form_data, $entry_id ) {
    if (!listmonk_are_listmonk_settings_configured()) {
        return; // Abort if settings are not configured
    }
    $listmonk_wpforms_form_id = absint(get_option('listmonk_wpforms_form_id')); // convert form id from option to integer
    $listmonk_form_on = sanitize_text_field(get_option('listmonk_form_on')); // check if the listmonk form option is disabled in settings

    // check if the form id matches the form id from the settings page and if the listmonk form option is enabled
    if (get_option('listmonk_form_on') != 'yes' || absint($form_data['id']) !== $listmonk_wpforms_form_id) { 
        return;
    }

    // define variables
    $ip = listmonk_get_the_user_ip(); // define ip address of user, used for listmonk consent recording
    $website_name = sanitize_text_field(get_bloginfo( 'name' )); // Retrieves the website's name from the WordPress database
    $listmonk_list_id = absint(get_option('listmonk_list_id', 0)); // get listmonk list id from settings page

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
    $name = sanitize_text_field(strip_tags($fields['1']['value'])); // get name from form; this assumes it is the first field in the form
    $name_email_stripped = preg_replace($pattern, $replacement, $name); // remove email from name field input
    $name_stripped_all = preg_replace('/[a-zA-Z]*[:\/\/]*[A-Za-z0-9\-_]+\.+[A-Za-z0-9\.\/%&=\?\-_]+/i', $replacement, $name_email_stripped); // remove urls from name field input

    // this body will be sent to listmonk 
    $body = array(
		'name' => $name_stripped_all, // get name from form
		'email' => sanitize_email($fields['2']['value']), // get email from form, this assumes it is the second field in the form
        'status' => 'enabled', // set to enabled to subscribe user
        'lists' => [(int)$listmonk_list_id], // convert list id to integer
        'preconfirm_subscriptions' => false, // set to true if you want to send a confirmation email to the user
        'attribs' => $attributes,   
	);

    #listmonk credentials
    $listmonk_url = esc_url_raw(get_option('listmonk_url'));
    $listmonk_username = sanitize_text_field(get_option('listmonk_username'));

    ## password decryption
    $encryption = new FSD_Data_Encryption();
    $encrypted_password = sanitize_text_field(get_option('listmonk_password'));
    $listmonk_password = $encryption->decrypt($encrypted_password);

    // append the url from the settings page with the correct API endpoint
    $url = $listmonk_url . '/api/subscribers';    
    
    // using the send_data_to_listmonk function we defined earlier, we communicate with the listmonk API through WordPress HTTP API
    listmonk_send_data_to_listmonk_wordpress_http_api($url, $body, $listmonk_username, $listmonk_password);

}
add_action( 'wpforms_process_complete', 'listmonk_send_data_through_wpforms', 10, 4 );

## send subscriber data to listmonk after paying 

add_action( 'woocommerce_thankyou', 'listmonk_send_data_afer_checkout', 10, 1 );
function listmonk_send_data_afer_checkout( $order_id ){
    // check ff the listmonk checkout component is enabled in settings
    if (get_option('listmonk_checkout_on') != 'yes') { 
        return;
    }
    if( ! $order_id ){ // if order id is not set, return
        return;
    }

    if (!listmonk_are_listmonk_settings_configured()) {
        return; // Abort if settings are not configured
    }

    $order = wc_get_order( absint($order_id) ); // Get an instance of the WC_Order Object

    // check for user newsletter consent
    $field_name = 'newsletter_optin'; // change this field to the name of your custom field for storing user consent in a checkbox
    $consent = $order->get_meta($field_name); // get user consent from custom field
    if ($consent != 'true' && $consent != '1') { // if user did not give consent, return
        return;
    }
    
    // get user info from the woocommerce order API
    $email = sanitize_email($order->get_billing_email()); // Get Customer billing email
    $name = sanitize_text_field($order->get_billing_first_name()); // Get Customer billing first name
    $country = $order->get_billing_country(); // Get Customer billing country
    $ip = $order->get_customer_ip_address(); // Get Customer ip address
    $website_name = sanitize_text_field(get_bloginfo( 'name' )); // Retrieves the website's name from the WordPress database

    $listmonk_list_id = absint(get_option('listmonk_list_id', 0)); // get listmonk list id from settings page

    ## for listmonk
    $attributes = [
        'country' => $country,
        'subscription_origin' => 'payment',
        'ip_address' => $ip,
        'confirmed_consent' => true, // user gave consent to receive newsletter
        'consent_agreement' => 'I consent to receiving periodic newsletters from ' . $website_name . '.', // Use the website name dynamically
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
    $listmonk_url = esc_url_raw(get_option('listmonk_url'));
    $listmonk_username = sanitize_text_field(get_option('listmonk_username'));

    ## password decryption using the fsd-data-encryption class
    $encryption = new FSD_Data_Encryption();
    $encrypted_password = sanitize_text_field(get_option('listmonk_password'));
    $listmonk_password = $encryption->decrypt($encrypted_password);
    
    // append the url from the settings page
    $url = $listmonk_url . '/api/subscribers';

    // using the send_data_to_listmonk function we defined earlier, we communicate with the listmonk API through WordPress HTTP API

    $response = listmonk_send_data_to_listmonk_wordpress_http_api($url, $body, $listmonk_username, $listmonk_password);

    if ($response['status_code'] == 200) {
        $order->add_order_note('Customer subscribed to listmonk mailing list (ID = ' . $listmonk_list_id . ').');
    }elseif($response['status_code'] == 4093333) { // change back
        $order->add_order_note('Email address already exists in listmonk mailing list ' . $listmonk_list_id . ', customer had already subscribed.');
    }else{
        $order->add_order_note($response['error_message']);
    }

}

### SETTINGS PAGE ###

// Add a top-level menu for the plugin settings in the admin dashboard
add_action('admin_menu', 'listmonk_top_lvl_menu');
function listmonk_top_lvl_menu(){
    add_options_page(
        'Settings - Integration for listmonk mailing list and newsletter manager', // Page title
        'Integration for listmonk', // Menu title
        'manage_options', // Capability required
        'listmonk_integration', // Menu slug
        'listmonk_integration_page_callback', // Function to display the settings page
        4 // Position in the menu, 4 = right below 'Settings'
    );
}

// Callback function to render the plugin settings page
function listmonk_integration_page_callback(){ // Function to render the plugin settings page

    // Display warning if both conditions are met
    if (listmonk_is_checkout_block_enabled() && get_option('listmonk_checkout_on') == 'yes') {
        echo '<div class="notice notice-warning">';
        echo '<p>The new <a href="' . esc_url('https://woo.com/checkout-blocks/') . '">WooCommerce checkout block</a> is enabled on your site. This means the listmonk integration that
        let\'s users subscribe to your newsletter from the checkout page will not work. Compatibility with the WooCommerce checkout block is being worked on.
        If this integration is important to you, <a href="' . esc_url('https://woo.com/document/cart-checkout-blocks-status/#section-7') . '">consider switching back to the old WooCommerce checkout experience</a>.</p>';
        echo '</div>';
    }

    ?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
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
        <p><em>Enjoying this free & open source plugin?</em></p><a href="https://www.buymeacoffee.com/postduif" target="_blank"><img src="https://cdn.buymeacoffee.com/buttons/v2/default-yellow.png" alt="Buy Me A Coffee" style="height: 60px !important;width: 217px !important;" ></a>
    </div>
    <?php
}

// Sanitize checkbox input
function listmonk_sanitize_checkbox($input){
    return 'on' === $input ? 'yes' : 'no'; // Return 'yes' if the checkbox is checked, otherwise return 'no'
}

function listmonk_sanitize_listmonk_url($input) {
    // Trim whitespace from the input
    $url = trim($input);

    // Check if the URL is empty
    if (empty($url)) {
        add_settings_error(
            'listmonk_url', 
            'empty_url', 
            'The URL field cannot be empty.' // Error message
        );
        return get_option('listmonk_url'); // Return the previous value
    }

    // Check if "https://" or "http://" is missing, and if missing, check validity after prepending
    if (!preg_match('~^(?:f|ht)tps?://~i', $url)) {
        $prefixedUrl = "https://" . $url;
        if (!filter_var($prefixedUrl, FILTER_VALIDATE_URL)) {
            add_settings_error(
                'listmonk_url', 
                'invalid_url', 
                'Please enter a valid URL.' // Error message
            );
            return get_option('listmonk_url'); // Return the previous value
        }
        $url = $prefixedUrl;
    } else {
        // If it already has http or https, just validate it
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            add_settings_error(
                'listmonk_url', 
                'invalid_url', 
                'Please enter a valid URL.' // Error message
            );
            return get_option('listmonk_url'); // Return the previous value
        }
    }

    // Remove a trailing slash if present
    $url = rtrim($url, '/');

    return $url; // Return the sanitized URL
}


// Sanitize and validate the list ID
function listmonk_sanitize_list_id($input){ // Function to sanitize the listmonk list ID
    $new_input = absint($input);
    if ($new_input < 0) {
        add_settings_error(
            'listmonk_list_id', 
            'invalid_listmonk_list_id', 
            'listmonk list ID should be a positive number.' // Error message
        );
        return get_option('listmonk_list_id'); // Return the previous value
    }
    return $new_input;
}

function listmonk_sanitize_listmonk_password($input){ // Function to sanitize the listmonk password
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
    $page_slug = 'listmonk_integration'; // Slug of the settings page
    $option_group = 'listmonk_integration_settings'; // Option group for the settings fields

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
    register_setting($option_group, 'listmonk_checkout_on', 'listmonk_sanitize_checkbox');
    add_settings_field(
        'listmonk_checkout_on', // Field ID
        'Enable listmonk integration on WooCommerce Checkout:', // Field title
        'listmonk_render_checkbox_field', // Callback for field markup
        $page_slug, // Page slug
        'listmonk_plugin_components', // Section ID
        array('name' => 'listmonk_checkout_on') // Additional arguments for the callback function
    );
 // Register and add settings fields
    register_setting($option_group, 'listmonk_form_on', 'listmonk_sanitize_checkbox');
    add_settings_field(
        'listmonk_form_on',
        'Enable listmonk integration on a custom form using WPForms plugin:',
        'listmonk_render_checkbox_field',
        $page_slug,
        'listmonk_plugin_components',
        array('name' => 'listmonk_form_on')
    );
// Register and add settings fields
    register_setting($option_group, 'listmonk_wpforms_form_id', 'listmonk_sanitize_list_id');
    add_settings_field(
        'listmonk_wpforms_form_id',
        'WPForms Form ID:',
        'listmonk_render_wpforms_form_id_field',
        $page_slug,
        'listmonk_plugin_components',
        array('name' => 'listmonk_wpforms_form_id')
    );
// Register and add settings fields
    register_setting($option_group, 'listmonk_list_id', 'listmonk_sanitize_list_id');
    add_settings_field(
        'listmonk_list_id',
        'listmonk list ID:',
        'listmonk_render_listmonk_list_id_field',
        $page_slug,
        'listmonk_credentials',
        array('name' => 'listmonk_list_id')
    );
// Register and add settings fields
    register_setting($option_group, 'listmonk_url', 'listmonk_sanitize_listmonk_url');
    add_settings_field(
        'listmonk_url',
        'listmonk URL:',
        'listmonk_render_text_field',
        $page_slug,
        'listmonk_credentials',
        array('name' => 'listmonk_url')
    );
// Register and add settings fields
    register_setting($option_group, 'listmonk_username', 'sanitize_text_field');
    add_settings_field(
        'listmonk_username',
        'listmonk username:',
        'listmonk_render_text_field',
        $page_slug,
        'listmonk_credentials',
        array('name' => 'listmonk_username')
    );
// Register and add settings fields
    register_setting($option_group, 'listmonk_password', 'listmonk_sanitize_listmonk_password');
    add_settings_field(
        'listmonk_password',
        'listmonk password:',
        'listmonk_render_text_field',
        $page_slug,
        'listmonk_credentials',
        array('name' => 'listmonk_password')
    );

    // Register and add settings fields for extra textbox
    register_setting($option_group, 'listmonk_optin_text', 'sanitize_text_field');
    add_settings_field(
        'listmonk_optin_text', // Field ID
        'WooCommerce checkout newsletter opt-in text:', // Field title
        'listmonk_render_listmonk_optin_text', // Callback for field markup
        $page_slug, // Page slug
        'listmonk_plugin_components', // Section ID
        array('name' => 'listmonk_optin_text') // Additional arguments for the callback function
    );
}

// Description for the 'Plugin Components' section
function listmonk_plugin_components_description() { // Function to render the description for the 'Plugin Components' section
    echo '<p>Integration for listmonk mailing list and newsletter manager can be enabled in two ways:</p><p> (1) On the WooCommerce checkout. Customers can check a box to subscribe to your newsletter. This check box will be added below the email address field.
    You can customize the text they will see by changing the text in the text box below. Currently only the old WooCommerce checkout is supported (so not the WooCommerce Blocks based checkout).
    </p><p>(2) On any page on your website, using a custom newsletter form from the <a href="https://wordpress.org/plugins/wpforms-lite/">WPForms plugin</a> that you can include anywhere on your website. You can enter the WPForms form ID on this settings page.
    </p><p>See <a href="https://listmonk.app/docs/">the listmonk documentation</a> for more information on how to setup listmonk, either on your own server or easily hosted versions on services like <a href="https://railway.app/new/template/listmonk">Railway</a> and <a href="https://www.pikapods.com/pods?run=listmonk">Pikapods</a>.</p>
    ';
}

// Description for the 'listmonk Credentials' section
function listmonk_credentials_description() { // Function to render the description for the 'listmonk Credentials' section
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

function listmonk_render_listmonk_optin_text($args) {
    $option_name = $args['name'];
    $value = get_option($option_name, 'Subscribe to our newsletter'); // Default value if option is not set
    $disabled = get_option('listmonk_checkout_on') !== 'yes' ? 'readonly' : '';

    echo '<input class="listmonk-text-input" type="text" id="' . esc_attr($option_name) . '" name="' . esc_attr($option_name) . '" value="' . esc_attr($value) . '" ' . $disabled . ' />';
    echo '<p class="description">This text will be shown on the WooCommerce checkout page when listmonk integration is enabled.</p>';
}

function listmonk_render_text_field($args){
    $field_type = 'text';
    $autocomplete = ''; // Autocomplete attribute
    $placeholder = ''; // Default placeholder text
    $help_text = ''; // Default help text

    // If the field is for the password
    if ($args['name'] == 'listmonk_password') {
        $field_type = 'password';
        $autocomplete = 'autocomplete="new-password"'; // Set autocomplete attribute for password field
        $placeholder = 'Enter new password to change'; // Informative placeholder text for the password field
        $help_text = '<p class="description">Leave blank to keep the current password.</p>'; // Help text for the password field
    } else if ($args['name'] == 'listmonk_username') {
        $autocomplete = 'autocomplete="username"'; // Set autocomplete attribute for username field
    }
    $option = get_option($args['name'], ''); // Get the option value for other fields

    // Print the input field
    printf(
        '<input class="listmonk-text-input" type="%s" id="%s" name="%s" value="%s" %s %s />', // The placeholders are replaced with the specified values
        esc_attr($field_type), // Field type (text or password)
        esc_attr($args['name']), // Field ID
        esc_attr($args['name']), // Field name
        esc_attr($field_type == 'password' ? '' : $option), // Field value, empty for password fields
        esc_attr($placeholder), // Placeholder text
        esc_attr($autocomplete) // Autocomplete attribute
    );

    // Echo the help text
    if (!empty($help_text)) {
        echo $help_text; // Help text
    }
}

function listmonk_render_checkbox_field($args){ // Function to render checkbox field
    $value = get_option($args['name']); // Get the current value of the option
    
    // Check if WPForms or any plugin with a name starting with "wpforms" is active
    if ($args['name'] === 'listmonk_form_on' && !listmonk_is_plugin_active_with_prefix('wpforms')) {
        // WPForms or a matching plugin is not active and the field is "listmonk_form_on," disable the checkbox and set it as unchecked
        $disabled = 'disabled="disabled"';
        $checked = '';
        $message = 'WPForms not installed'; // Message for when WPForms is not installed
    } else {
        // WPForms or a matching plugin is active or the field is not "listmonk_form_on," enable the checkbox and set its value based on the option
        $disabled = '';
        $checked = checked($value, 'yes', false); // Set to checked if the option value is ' yes'
        $message = ''; // No message when WPForms is active or the field is not "listmonk_form_on"
    }
    
    ?>
    <label>
        <input type="checkbox" name="<?php echo esc_attr($args['name']); ?>" <?php echo esc_attr($checked); ?> <?php echo esc_attr($disabled); ?> /> Yes
    </label>
    <?php
    if ($message) {
        echo '<p class="description">' . esc_html($message) . '</p>'; // Show message if WPForms is not installed
    }
    // Include JavaScript for dynamic toggling
    ?>
    <script type="text/javascript">
    jQuery(document).ready(function($) {
        $('#listmonk_form_on').change(function() {
            var isFormEnabled = $(this).is(':checked');
            $('#listmonk_wpforms_form_id').prop('readonly', !isFormEnabled);
        }).change(); // Initialize on page load
    });
    jQuery(document).ready(function($) {
        // Function to visually toggle textbox state
        function toggleTextboxState(isEnabled) {
            $('#listmonk_optin_text').prop('readonly', !isEnabled).toggleClass('disabled-textbox', !isEnabled);
        }

        // Event handler for checkbox change
        $('#listmonk_checkout_on').change(function() {
            toggleTextboxState($(this).is(':checked'));
        }).change(); // Initialize on page load
    });
    </script>



    <?php
}

function listmonk_is_plugin_active_with_prefix($prefix){ // Function to check if a plugin with a name starting with a prefix is active
    $active_plugins = get_option('active_plugins'); // Get all active plugins
    
    foreach ($active_plugins as $plugin) { // Loop through all active plugins
        if (strpos($plugin, $prefix) === 0) { // Check if the plugin name starts with the prefix
            return true;
        }
    }
    
    return false;
}

// Function to render WPForms Form ID field
function listmonk_render_wpforms_form_id_field($args) {
    $option_name = esc_attr($args['name']);
    $value = esc_attr(get_option($option_name, '')); // Default value if option is not set
    $disabled = get_option('listmonk_form_on') !== 'yes' ? 'readonly' : '';

    echo '<input class="listmonk-number-input" type="number" id="' . esc_attr($option_name) . '" name="' . esc_attr($option_name) . '" value="' . esc_attr($value) . '" ' . $disabled . ' />';
    echo '<p class="description">Enter the WPForms Form ID here. This ID is used when listmonk integration with WPForms is enabled.</p>';
}


// Function to render listmonk List ID field
function listmonk_render_listmonk_list_id_field($args) { // Function to render listmonk List ID field
    $option = get_option($args['name'], '1'); // Default value is 1

    printf(
        '<input class="listmonk-number-input" type="number" id="listmonk_list_id" name="%s" value="%d" />', // The %s placeholders are replaced with the values in the following order
        esc_attr($args['name']),
        esc_attr($option)
    );
}

// Render a button on the WordPress plugin page to easily access this plugin's settings
function listmonk_plugin_page_settings_link($links) {
    $settings_link = '<a href="options-general.php?page=listmonk_integration">Settings</a>';
    array_push($links, $settings_link);
    return $links;
}

add_filter('plugin_action_links_listmonk-integration/listmonk-integration.php', 'listmonk_plugin_page_settings_link');

?>
