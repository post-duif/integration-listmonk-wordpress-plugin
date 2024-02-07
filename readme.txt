=== Integration for listmonk mailing list and newsletter service ===
Contributors: postduif
Tags: listmonk,newsletter,wordpress,woocommerce,subscribers,mail,mailing,api
Donate link: https://buymeacoffee.com/woutern
Requires at least: 5.7
Tested up to: 6.4.2
Requires PHP: 7.4
Stable tag: 1.2.1
License: GNU General Public License v3.0
License URI: https://www.gnu.org/licenses/gpl-3.0.html#license-textf

Integrates the open source mailing list tool listmonk with Wordpress / Woocommerce so users can subscribe to your mailing list.

== Description ==
This Wordpress plugin integrates [listmonk](https://listmonk.app/) with your Wordpress / WooCommerce website.

You can use it to (1) let people subscribe to your newsletter through a form on your website (using the free WPForms plugin, you can download it in the plugin directory), (2) or you can let people subscribe to your newsletter from the WooCommerce checkout page. After a customer pays, their email address and name will be sent to the listmonk mailing list of your choice.

Using Listmonk as an open source newsletter and mailing tool, makes you (1) less dependent on paid, closed source services like Mailchimp, (2) costs less and (3) has no limits on the amounts of emails you can send i.e. per month.

## How to use this plugin

1. Make sure you have access to a configured listmonk server. See the listmonk documentation for more information on how to setup listmonk, either on your own server or easily hosted versions on services like Railway and Pikapods. You can connect Listmonk to email services like Amazon SES, costing you as little as 0,1$ per thousand emails. For this Wordpress integration plugin you need the Listmonk URL, username and password.
2. Use either WPForms or the WooCommerce checkout to send subscriber data to listmonk. You can add a custom checkout box to the WooCommerce checkout in the settings of this plugin, so people can opt-in to your mailing list. 
3. Figure out the _Listmonk list ID_ that you want to use to subscribe everyone to. See listmonk documentation for more details.
4. Open the settings page of Listmonk integration to enter things like url, username and password. You can also enable and disable the Woocommerce and WPForms components. 

## How does this plugin work?

This plugin uses the well-documented listmonk API to send data from your Wordpress server to the Listmonk server over HTTPS.

## Requirements
- An accessible listmonk server
- If you want to use it so people can subscribe to a mailing list during checkout, you need WooCommerce enabled. Currently only the classic WooCommerce checkout is supported; support for WooCommerce blocks will follow. 
- If you want to use this plugin so people can fill in a form on your website to subscribe to you mailing list, you need to have the free [WPForms](https://wordpress.org/plugins/wpforms-lite/) plugin enabled. 

## Is this plugin free?
Yes, 100%!

## What about privacy?
For each users that subscribes to a mailing list, their IP address and is recorded as well as a timestamp of subscription time. For subscribers through the WooCommerce checkout, there is no double opt-in, but for people subscribing through a form there is a double opt-in. 

## Dependency on external services
This plugin uses two external services: (1) a listmonk server of your choice, for which you are solely responsible and (2) a link to [Buy me a Coffee](buymeacoffee.com) to voluntarily support the development of this plugin. See their privacy policy [here](https://www.buymeacoffee.com/privacy-policy). This plugin can be used without donating. This plugin does not store or use any customer data: this is all being handled by the listmonk server you connect to.

## Contribute
Feel free to submit an issue or pull request on [Github](https://github.com/post-duif/integration-listmonk-wordpress-plugin) if you have any questions or suggestions for improvement. If you have knowledge of the Listmonk API you can adapt this plugin to fit your specific needs (i.e. change all subscriptions to opt-in).

## License
GNU General Public License v3.0. No commercial closed-source usage allowed.

## Thanks
Huge thanks to Kailash Nadh for creating listmonk!

== Installation ==
See FAQ and description. A listmonk server is required.

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

# I have a problem with this plugin, can you help me?
I created this plugin in my free time. I may have time to help you, but no guarantees! 

== Screenshots ==
1. Settings page of the plugin.
2. Newsletter opt-in on the WooCommerce checkout. After a customer pays, their email address and name will be sent to the mailing list of your choice.
3. Newsletter subscription form example. Created with WPForms, data will be sent to your listmonk server.

== Changelog ==
1.2.0 First public version on the WordPress plugin directory