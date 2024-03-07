if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

// listmonk-admin.js
jQuery(document).ready(function($) {
    $('#listmonk_form_on').change(function() {
        var isFormEnabled = $(this).is(':checked');
        $('#listmonk_wpforms_form_id').prop('readonly', !isFormEnabled);
    }).change(); // Initialize on page load

    // Function to visually toggle textbox state
    function toggleTextboxState(isEnabled) {
        $('#listmonk_optin_text').prop('readonly', !isEnabled).toggleClass('disabled-textbox', !isEnabled);
    }

    // Event handler for checkbox change
    $('#listmonk_checkout_on').change(function() {
        toggleTextboxState($(this).is(':checked'));
    }).change(); // Initialize on page load
});
