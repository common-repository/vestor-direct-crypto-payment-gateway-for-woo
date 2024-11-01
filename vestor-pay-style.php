<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

function vestor_pay_enqueue_styles() {
    wp_enqueue_style('vestor-pay-styles', plugin_dir_url(__FILE__) . '/css/vestor-pay-styles.css?ver=12');
}
add_action('wp_enqueue_scripts', 'vestor_pay_enqueue_styles');
