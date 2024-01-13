# Integration for listmonk mailing list and newsletter service (WordPress plugin)

This WordPress plugin integrates [listmonk](https://listmonk.app/) with your WordPress / WooCommerce website. You can use it to let people subscribe to your newsletter from a form on your website (using WPForms) or you can let people subscribe to your newsletter from the WooCommerce checkout page. 

Using listmonk as an open source newsletter and mailing tool, makes you (1) less dependent on services like Mailchimp, (2) costs less and (3) has no limits on the amounts of emails you can send i.e. per month.

## How to use this plugin

1. Download the plugin and upload the folder to you WordPress plugin directory. Plugin will be available through the WordPress plugin search function later.
2. Make sure you have access to a configured listmonk server. You can connect listmonk to email services like Amazon SES, costing you as little as 0,1$ per thousand emails. For this WordPress integration plugin you need the listmonk URL, username and password.
3. Use either WPForms or the WooCommerce checkout to send subscriber data to listmonk. If you want to use the WooCommerce checkout to automatically subscribe customers to your mailing lists, you should have a custom fields plugin installed.
4. Figure out the _listmonk list ID_ that you want to use to subscribe everyone to. See listmonk documentation for more details.
5. Open the settings page of listmonk integration to enter things like url, username and password. You can also enable and disable the WooCommerce and WPForms components. 
<img width="1279" alt="Screenshot 2024-01-13 at 03 58 42" src="https://github.com/post-duif/listmonk-WooCommerce-plugin/assets/126626953/5383d893-8963-41ed-9cc6-dc767782c2e7">

## How does the plugin work?
This plugin uses the well-documented listmonk API to send data from your WordPress server to the listmonk server over HTTPS.

## Requirements
This plugin uses cURL to communicate with the listmonk API. It needs to be enabled on your server.

## Future plans 
I plan to include an option to add a custom checkout field to this plugin, which would remove the need for installing another plugin to create a custom checkout field named. 

## What about privacy?

For each users that subscribes to a mailing list, their IP address and is recorded as well as a timestamp of subscription time. For subscribers through the WooCommerce checkout, there is no double opt-in, but for people subscribing through a form there is a double opt-in. 

## Contribute

Feel free to submit an issue or pull request if you have any questions or suggestions for improvement. If you have knowledge of the listmonk API you can adapt this plugin to fit your specific needs (i.e. change all subscriptions to opt-in).

## License
GNU General Public License v3.0. No commercial closed-source usage allowed.
