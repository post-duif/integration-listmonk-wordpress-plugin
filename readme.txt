=== Integration for listmonk mailing list and newsletter service ===
Contributors: postduif
Tags: listmonk, newsletter, WordPress, WooCommerce, subscribers, mail, mailing, api
Donate link: https://buymeacoffee.com/postduif
Requires at least: 6.0
Tested up to: 6.4.3
Requires PHP: 7.4
Stable tag: 1.3.3
License: GNU General Public License v3.0
License URI: https://www.gnu.org/licenses/gpl-3.0.html#license-textf

Integrates the open-source mailing list tool listmonk with WordPress/WooCommerce so users can subscribe to your mailing list.

== Description ==
This WordPress plugin integrates listmonk with your WordPress / WooCommerce website.

You can use it to:

- Let people subscribe to your newsletter through a form on your website using WPForms or Contact Form 7.
- Allow people to subscribe to your newsletter from the WooCommerce checkout page. After a customer pays, their email address and name will be sent to the listmonk mailing list of your choice.

Using listmonk as an open-source newsletter and mailing tool makes you less dependent on services like Mailchimp, costs less, and has no limits on the amount of emails you can send per month.

## How to use this plugin
1. Download the plugin and upload the folder to your WordPress plugin directory.
2. Ensure you have access to a configured listmonk server. You can connect listmonk to email services like Amazon SES, which costs as little as $0.10 per thousand emails.
3. Access the plugin's settings page from your WordPress admin dashboard under Settings to enable and disable components and enter listmonk credentials.
4. Determine the listmonk list ID you want to subscribe people to. See listmonk documentation for more details.
5. Utilize WPForms, Contact Form 7, or a custom field on the WooCommerce checkout page to send subscriber data to listmonk.
6. When using a custom field on the WooCommerce checkout page, you can input a text customers will see during checkout.

## Requirements
- WordPress website (latest version recommended);
- Accessible listmonk server over HTTPS (tested up to listmonk v3.0.0);
- WPForms or Contact Form 7 for form-based subscriptions;
- WooCommerce for checkout-based subscriptions; (classic checkout supported, block-based checkout experimentally supported).

## Privacy and Security
The plugin records the IP address each subscriber. Subscribers through WooCommerce checkout do not require double opt-in, unlike form-based subscriptions. API credentials are stored securely on your server with encryption for the password. Please note that this plugin does not have any form of rate-limiting, so it is your own responsibilty to use a CATCHPA when using i.e. WPForms, to limit the amount of fake subscriptions that could be sent to your listmonk server.

## Suggestions, Bugs, and Contributions
For bugs or suggestions, please create an issue on GitHub. Contributions, especially from those knowledgeable in PHP and the listmonk API, are welcome.

## License
GNU General Public License v3.0. No commercial closed-source usage allowed.

## Thanks
Huge thanks to Kailash Nadh for creating listmonk!

## Dependency on external services
This plugin uses two external services: (1) a listmonk server of your choice, for which you are solely responsible and (2) a link to [Buy me a Coffee](buymeacoffee.com) to voluntarily support the development of this plugin. See their privacy policy [here](https://www.buymeacoffee.com/privacy-policy). This plugin can be used without donating. This plugin does not store or use any customer data: this is all being handled by the listmonk server you connect to.

== Installation ==
Installation is straightforward. Upload the plugin to your WordPress plugin directory and configure it from the settings page.

== Frequently Asked Questions ==
# I don\'t have a listmonk server, will this plugin work?
No, you need a listmonk server for this plugin to work. You can either host one yourself, or use easy and freely available services lika Pikapods or Railway.

# Where do I enter my listmonk credentials?
You can find the plugin\'s settings, called \"Integration for listmonk\" under the Settings tab in WordPress. There you need to enable the components you want to use (form based and/or checkout based subscription) and fill in your credentials.

# Where do I find my listmonk list ID?
Click on a mailing list you created in listmonk. On the upper corner it should display the ID.

# Do users have to do double opt-in after subscribing to a mailing list?
Only if you use the form-based component of this plugin. For all users that subscribe to a mailing list via the WooCommerce checkout page, their email address is already assumed confirmed, because it is unlikely someone will pay for a product with another persons email address just to spam them.

# What about security?
This plugin encrypts your listmonk password before storing it in the WordPress database. Always make sure to choose long and unique passwords; this is your own responsibility.

# I have a problem with listmonk, can you help me out?
Please submit an issue to the listmonk Github repository (I am not the maintainer of listmonk).

# What form do I have to select when I use WPForms?
The plugin only works with the default \"newsletter\" form, which has a first/last name field and an email field.

# Where can I find the WPForms Form ID?
Check the shortcode of the form you created. It should be formatted like "wpforms id=100", where 100 is the Form ID that you need to enter in the listmonk integration settings page.

# What form do I have to select when I use Contact Form 7?
Make sure the forms contains the fields 'your-email' and 'your-name' in order for this plugin to work.

# How do I find the page ID for the Contact Form 7 integration?
This refers to the page ID of the page you entered the Contact Form 7 shortcode on. If you edit that page in your WordPress admin panel, the url will contain for example "page=57". That number you will need to enter in the listmonk integration settings. 

# I have a problem with this plugin, can you help me?
I created this plugin in my free time. I may have time to help you, but no guarantees! 

== Screenshots ==
1. Settings page of the plugin.
2. Newsletter opt-in on the WooCommerce checkout. After a customer pays, their email address and name will be sent to the mailing list of your choice.
3. Newsletter subscription form example. Created with WPForms, data will be sent to your listmonk server.

== Changelog ==
1.3.3 First public version on the WordPress plugin directory