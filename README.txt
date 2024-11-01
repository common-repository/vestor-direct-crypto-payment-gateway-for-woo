=== Vestor Direct Crypto Payment Gateway for WooCommerce ===
Contributors: vestorfinance  
Tags: WooCommerce, Payment Gateway, Cryptocurrency, Bitcoin, Ethereum  
Requires at least: 5.6  
Tested up to: 6.6  
Requires PHP: 7.4  
Stable tag: 1.0  
License: GPLv2 or later  
License URI: https://www.gnu.org/licenses/gpl-2.0.html  

A WooCommerce payment gateway plugin for users to pay with cryptocurrencies and upload proof of payment for manual confirmation by the admin.

== Description ==

Vestor Direct Crypto Payment Gateway for WooCommerce is a WooCommerce payment gateway plugin designed to offer a seamless cryptocurrency payment experience. Customers can pay using various cryptocurrencies, such as Bitcoin, Ethereum, and more.

The plugin includes a custom payment page where users can select their preferred cryptocurrency, view the corresponding wallet address and QR code, and complete their payment within a specified time limit. After making the payment, users are required to upload proof of payment, which the admin will manually review to confirm and mark the order as complete.

Key features:
* Accept payments in multiple cryptocurrencies.
* Custom payment page with wallet address and QR code display.
* Users upload proof of payment for manual review by the admin.
* Automatic conversion from the store's native currency to USD using a configurable exchange rate.
* 1-hour countdown timer for completing payments.
* Compatible with WooCommerce's native order management.

== Installation ==

1. Download the plugin and extract the `vestor-direct-crypto-payment-gateway-for-woo` folder.  
2. Upload the `vestor-direct-woo-crypto-payment-gateway` folder to the `/wp-content/plugins/` directory.  
3. Activate the plugin through the 'Plugins' menu in WordPress.  
4. Go to WooCommerce > Settings > Payments and enable "Vestor Direct Crypto Payment Gateway for WooCommerce."  
5. Configure the settings by entering the necessary details, such as cryptocurrency wallet addresses, conversion rate, and any additional settings.  
6. Use the shortcode `[vestor-pay]` on the page you want users to be taken to when making payments.  
7. In the settings, select the page with the shortcode as the payment page.  
8. To confirm payments, you will find a WordPress admin menu under "Payment Proofs."

== Frequently Asked Questions ==

= How do I set up the plugin? =

After activating the plugin, go to WooCommerce > Settings > Payments and select "Vestor Direct Crypto Payment Gateway for WooCommerce." You will need to provide wallet addresses for the supported cryptocurrencies, set the default exchange rate for conversion from your store's native currency to USD, and adjust other settings as needed.

= What cryptocurrencies are supported? =

The plugin supports various cryptocurrencies, including Bitcoin, Ethereum, and others that you add to the settings.

= How does the currency conversion work? =

The plugin converts the cart total from your store's native currency to USD based on a configurable exchange rate you enter in the settings.

= How are payments confirmed? =

Payments are not confirmed automatically. Users must upload proof of payment, which the admin will manually check. Once verified, the admin accepts the payment, and the order is completed.

= Can I customize the payment page design? =

The payment page is designed to match the provided design. Any customization should be done by modifying the plugin code carefully or by consulting with the plugin developer.

== Screenshots ==

1. **Payment Gateway Settings** - Configure cryptocurrency wallet addresses, conversion rates, and other settings.  
2. **Payment Page** - The custom payment page where users can select a cryptocurrency, view wallet addresses, and complete their payment.  
3. **Proof of Payment Upload** - Users upload proof of payment for admin review.  
4. **Admin Order Confirmation** - Admin interface for reviewing payment proofs and confirming orders.

== Changelog ==

= 1.0.0 =  
* Initial release.  
* Added support for multiple cryptocurrencies.  
* Included custom payment page with QR code and address display.  
* Implemented automatic conversion from the store's native currency to USD.  
* Added manual payment proof upload and admin confirmation.

== Upgrade Notice ==

= 1.0.0 =  
Initial release of Vestor Direct Crypto Payment Gateway for WooCommerce. Ensure you have WooCommerce installed and properly configured before activating this plugin.

== License ==

Vestor Direct Crypto Payment Gateway for WooCommerce is licensed under the GPLv2 or later. This plugin is free and open-source software. You can modify it, redistribute it, or use it in any way you like, as long as you comply with the GPLv2 or later terms.

== Credits ==

Developed by Vestor Finance.

== Additional Notes ==

This plugin uses the **QRcode.js** JavaScript library (included locally within the plugin) to generate QR codes for cryptocurrency payments. No external APIs or services are used for this function, ensuring that all files are hosted locally within the plugin. The plugin does not store any payment information on your server. Users must upload proof of payment, and admins are responsible for manually confirming payments and completing orders. Make sure to configure the wallet addresses and other settings carefully to avoid payment issues.
