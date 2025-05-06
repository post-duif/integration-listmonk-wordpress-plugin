# Integration for listmonk mailing list and newsletter service (WordPress plugin)

This WordPress plugin integrates [listmonk](https://listmonk.app/) with your WordPress / WooCommerce website. You can use it to (1) let people subscribe to your newsletter from a form on your website (using WPForms or Contact Form 7), or (2) you can let people subscribe to your newsletter from the WooCommerce checkout page. After a customer pays, their email address and name will be sent to the listmonk mailing list of your choice.

Using listmonk as an open source newsletter and mailing tool, makes you (1) less dependent on services like Mailchimp, (2) costs less and (3) has no limits on the amounts of emails you can send i.e. per month.

## How to use this plugin

1. Download the plugin and upload the folder to your WordPress plugin directory. Or just install it through the WordPress plugin directory: https://wordpress.org/plugins/integration-for-listmonk-mailing-list-and-newsletter-manager/
2. Make sure you have access to a configured listmonk server. You can connect listmonk to email services like Amazon SES, costing you as little as 0.1$ per thousand emails.
3. Use either WPForms (recommended), Contact Form 7 or a custom field on the WooCommerce checkout page to send subscriber data to listmonk. You can add the checkout field with this plugin if you use the classic WooCommerce checkout. WooCommerce block based checkout is currently experimentally supported.
4. Figure out the _listmonk list ID_ that you want people to subscribe to. See [listmonk documentation](https://listmonk.app/docs/) for more details.
5. Open the settings page of this plugin from your WordPress admin dashboard under Settings.  You can enable and disable the WooCommerce and WPForms/Contact Form 7 components. Enter your the listmonk list ID and listmonk server url.
6. Starting with the newest versions of listmonk, you now first need to create an API user in the listmonk settings. See listmonk documentation on how to do that. Make sure the API user has necessary permissions. After creating this APi user in your listmonk server settings, add the credentials (username and password) to the settings page of this plugin in your WordPress installation.
<img width="1296" alt="Screenshot 2024-01-14 at 02 53 00" src="https://github.com/post-duif/integration-listmonk-wordpress-plugin/assets/126626953/e7baa929-824d-4699-8fe0-9e7125382862">


## How does the plugin work?
This plugin uses the well-documented listmonk API to send data from your WordPress server to the listmonk server over HTTPS.

Customers can tick a checkbox on the WooCommerce checkout page to subscribe to your newsletter:
![image](https://github.com/post-duif/integration-listmonk-wordpress-plugin/assets/126626953/21bed5de-445b-4a48-9498-6a65fc6d6a97)

Or you can use a newsletter form created by the free third-party plugin WPForms:
![image](https://github.com/post-duif/integration-listmonk-wordpress-plugin/assets/126626953/bf17ae67-8617-4650-a5ed-d61901999d3c)

Or use Contact Form 7 as an alternative option. 

## Requirements
- A website running the latest version of WordPress;
- A listmonk server accessible over HTTPS (tested up to listmonk v4.0.0);
- For using this plugin with a form that people can fill in to subscribe to your mailing list, you should have the free WPForms plugin installed and choose their standard newsletter form. Input that form ID in the settings page of this plugin. Recently support for Contact Forms 7 was also added;
- For using this plugin to let customers subscribe to your mailing list during WooCommerce checkout, you need to have WooCommerce installed and the component activated in the plugin settings.

## What about privacy?
For each user that subscribes to a mailing list, their IP address and is recorded as well as a timestamp of subscription time. Subscribers through the WooCommerce checkout do not have to double opt-in, but people subscribing through a form need to confirm their subscription to avoid spam. When people subscribe through WooCommerce checkout, their country is also saved to listmonk. 

Your listmonk API credentials are saved locally on your server and not saved anywhere externally. Your API password is encrypted before it is stored in the WordPress database. 

## Suggestions and bugs
If you encounter any bugs or if you have suggestions for improvement, please create an issue on GitHub.

## Contribute
Feel free to submit an issue or pull request if you have any questions or suggestions for improvement. If you have knowledge of PHP and the listmonk API you can adapt this plugin to fit your specific needs (i.e. change all subscriptions to opt-in).

## License
GNU General Public License v3.0. No commercial closed-source usage allowed.
