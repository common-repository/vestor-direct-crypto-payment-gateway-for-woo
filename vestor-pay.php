<?php
/**
 * Plugin Name: Vestor Direct Crypto Payment Gateway for WooCommerce
 * Description: A payment gateway plugin for WooCommerce to receive crypto with no middlemen.
 * Author: Vestor Finance
 * Version: 1.0
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */


if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Check if WooCommerce is active
add_action('plugins_loaded', 'vestor_pay_init', 11);

function vestor_pay_init() {
    // Ensure WooCommerce is active
    if (!class_exists('WC_Payment_Gateway')) {
        return;
    }

    // Include necessary files
    include_once('class-vestor-pay-gateway.php'); 
    include_once('vestor-pay-shortcode.php'); 
    include_once('vestor-pay-style.php'); 

    // Initialize the payment gateway class
    add_filter('woocommerce_payment_gateways', 'add_vestor_pay_gateway');
}

function add_vestor_pay_gateway($methods) {
    $methods[] = 'WC_Gateway_Vestor_Pay';
    return $methods;
}

