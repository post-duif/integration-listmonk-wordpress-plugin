# listmonk-woocommerce-plugin

This Wordpress plugin integrates [Listmonk](https://listmonk.app/) with your Wordpress / Woocommerce website. You can use it to let people subscribe to your newsletter from a form on your website (using WPForms) or you can let people subscribe to your newsletter from the Woocommerce checkout page. 

Using Listmonk as an open source newsletter and mailing tool, makes you (1) less dependent on services like Mailchimp, (2) costs less and (3) has no limits on the amounts of emails you can send i.e. per month.

## How to use this plugin

1. Make sure you have access to a configured Listmonk server. You can connect Listmonk to email services like Amazon SES, costing you as little as 0,1$ per thousand emails. For this Wordpress integration plugin you need the Listmonk URL, username and password.
2. Use either WPForms or the Woocommerce checkout to send subscriber data to Listmonk. If you want to use the Woocommerce checkout to automatically subscribe customers to your mailing lists, you should have a custom fields plugin installed.
3. Figure out the _Listmonk list ID_ that you want to use to subscribe everyone to. See Listmonk documentation for more details.
4. Open the settings page of Listmonk integration to enter things like url, username and password. You can also enable and disable the Woocommerce and WPForms components. 
<img width="1279" alt="Screenshot 2024-01-13 at 03 58 42" src="https://github.com/post-duif/listmonk-woocommerce-plugin/assets/126626953/5383d893-8963-41ed-9cc6-dc767782c2e7">

## How does the plugin work?

This plugin uses the well-documented Listmonk API to send data from your Wordpress server to the Listmonk server over HTTPS.

## Requirements
This plugin uses cURL to communicate with the Listmonk API. It needs to be enabled on your server.

## What about privacy?

For each users that subscribes to a mailing list, their IP address and is recorded as well as a timestamp of subscription time. For subscribers through the Woocommerce checkout, there is no double opt-in, but for people subscribing through a form there is a double opt-in. 

## Contribute

Feel free to submit an issue or pull request if you have any questions or suggestions for improvement. If you have knowledge of the Listmonk API you can adapt this plugin to fit your specific needs (i.e. change all subscriptions to opt-in).

## License
GNU General Public License v3.0. No commercial closed-source usage allowed.
