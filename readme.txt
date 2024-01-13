=== listmonk Wordpress Integration ===
Contributors: postduif
Tags: listmonk,newsletter,wordpress,woocommerce,subscribers,mail,mailing
Donate link: https://buymeacoffee.com/woutern
Requires at least: 5.7
Tested up to: 6.4.2
Requires PHP: 7.4
License: GNU General Public License v3.0
License URI: https://www.gnu.org/licenses/gpl-3.0.html#license-textf

Integrates the open source mailing list tool listmonk with Wordpress / Woocommerce so users can subscribe to your mailing list.

== Description ==
This Wordpress plugin integrates [Listmonk](https://listmonk.app/) with your Wordpress / WooCommerce website. You can use it to let people subscribe to your newsletter from a form on your website (using WPForms plugin, you can download it in the plugin directory) or you can let people subscribe to your newsletter from the WooCommerce checkout page. 

Using Listmonk as an open source newsletter and mailing tool, makes you (1) less dependent on paid, closed source services like Mailchimp, (2) costs less and (3) has no limits on the amounts of emails you can send i.e. per month.

## How to use this plugin

1. Make sure you have access to a configured listmonk server. See the listmonk documentation for more information on how to setup listmonk, either on your own server or easily hosted versions on services like Railway and Pikapods. You can connect Listmonk to email services like Amazon SES, costing you as little as 0,1$ per thousand emails. For this Wordpress integration plugin you need the Listmonk URL, username and password.
2. Use either WPForms or the WooCommerce checkout to send subscriber data to Listmonk. If you want to use the WooCommerce checkout to automatically subscribe customers to your mailing lists, you should have a custom fields plugin installed.
3. Figure out the _Listmonk list ID_ that you want to use to subscribe everyone to. See Listmonk documentation for more details.
4. Open the settings page of Listmonk integration to enter things like url, username and password. You can also enable and disable the Woocommerce and WPForms components. 

## How does this plugin work?

This plugin uses the well-documented Listmonk API to send data from your Wordpress server to the Listmonk server over HTTPS.

## Requirements
- An accessible listmonk server
- If you want to use it so people can subscribe to a mailing list during checkout, you need Woocommerce enabled and a plugin that can create a custom checkout field (such as Checkout Field Editor for WooCommerce). 
- If you want to use this plugin so people can fill in a form on your website to subscribe to you mailing list, you need to have the free WPForms plugin enabled.
- This plugin uses cURL to communicate with the Listmonk API. It needs to be enabled on your server.

## Is this plugin free?
Yes, 100%!

## What about privacy?

For each users that subscribes to a mailing list, their IP address and is recorded as well as a timestamp of subscription time. For subscribers through the WooCommerce checkout, there is no double opt-in, but for people subscribing through a form there is a double opt-in. 

## Contribute

Feel free to submit an issue or pull request on Github if you have any questions or suggestions for improvement. If you have knowledge of the Listmonk API you can adapt this plugin to fit your specific needs (i.e. change all subscriptions to opt-in).

## License
GNU General Public License v3.0. No commercial closed-source usage allowed.

## Thanks

Huge thanks to Kailash Nadh for creating listmonk!

== Installation ==
See FAQ and description.

== Frequently Asked Questions ==
# I don\'t have a listmonk server, will this plugin work?
No, you need a listmonk server for this plugin to work. You can either host one yourself, or use easy and freely available services lika Pikapods or Railway.

# Where do I enter my listmonk credentials?
You can find the plugin\'s settings, called \"listmonk Integration\" under the Settings tab in WordPress. There you need to enable the components you want to use (form based and/or checkout based subscription) and fill in your credentials.

# Where do I find my listmonk list ID?
Click on a mailing list you created in listmonk. On the upper corner it should display the ID.

# What should be the name of the custom checkout field that this plugin uses?
The plugin looks for if the user checked a checkbox named \"newsletter_optin\". If the user has given that consent, their information is sent to listmonk.

# Do users have to do double opt-in after subscribing to a mailing list?
Only if you use the form-based component of this plugin. For all users that subscribe to a mailing list via the WooCommerce checkout page, their email address is already assumed confirmed, because it is unlikely someone will pay for a product with another persons email address just to spam them.

# What about security?
This plugin encrypts your listmonk password before storing it in the WordPress database. Always make sure to choose long and unique passwords; this is your own responsibility.

# I have a problem with listmonk, can you help me out?
Please submit an issue to the listmonk Github repository (I am not the maintainer of listmonk).

# What form do I have to select when I use WPForms?
The plugin only works with the default \"newsletter\" form, which has a first name field and an email field.

== Changelog ==
0.3 - First version