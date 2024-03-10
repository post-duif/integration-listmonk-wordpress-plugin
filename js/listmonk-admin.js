if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

// listmonk-admin.js
jQuery(document).ready(function($) {
    // Function to handle the form toggle logic
    function handleFormToggle() {
        var isWPFormsEnabled = $('#listmonk_form_on').is(':checked');
        $('#listmonk_wpforms_form_id').prop('readonly', !isWPFormsEnabled);

        var isCF7IntegrationEnabled = $('#listmonk_cf7_integration_on').is(':checked');
        // Assuming 'listmonk_cf7_form_id' is the ID of the related input field
        $('#listmonk_cf7_form_id').prop('readonly', !isCF7IntegrationEnabled);
    }

    // Function to visually toggle textbox state
    function toggleTextboxState(isEnabled) {
        $('#listmonk_optin_text').prop('readonly', !isEnabled).toggleClass('disabled-textbox', !isEnabled);
    }

    // Event handler for WPForms checkbox change
    $('#listmonk_form_on').change(handleFormToggle).change(); // Initialize on page load

    // Event handler for CF7 checkbox change
    $('#listmonk_cf7_integration_on').change(handleFormToggle).change(); // Initialize on page load

    // Event handler for Checkout checkbox change
    $('#listmonk_checkout_on').change(function() {
        toggleTextboxState($(this).is(':checked'));
    }).change(); // Initialize on page load
});
