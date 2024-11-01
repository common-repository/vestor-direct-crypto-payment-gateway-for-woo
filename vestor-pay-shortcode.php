<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Enqueue the necessary scripts for QR code generation and custom JS
function vestor_pay_enqueue_scripts() {
    wp_enqueue_script(
        'qrcodejs', 
        plugins_url('/js/qrcode.min.js', __FILE__), // Enqueue locally stored qrcode.min.js
        array(), 
        null, 
        true // Load in footer
    );

    wp_enqueue_script(
        'vestor-pay-js',
        plugins_url('/js/vestor-pay.js', __FILE__), // Enqueue custom JS file
        array('jquery', 'qrcodejs'), 
        null, 
        true // Load in footer
    );

    // Ensure the order ID from URL is passed to the JS
    if (isset($_GET['order_id'])) {
        $order_id = intval(sanitize_text_field(wp_unslash($_GET['order_id']))); // Sanitize the order ID
    } else {
        $order_id = 0; // Default to 0 if no order ID
    }

    // Localize script to pass PHP variables to JS
    global $amount_usd; // Ensure amount_usd is set globally or derived earlier
    wp_localize_script('vestor-pay-js', 'vestorPay', array(
        'pluginUrl' => esc_url(plugins_url('/', __FILE__)),
        'amountUsd' => esc_html(number_format($amount_usd, 2)), // Pass the amount in correct format
        'orderId'   => esc_attr($order_id),  // Pass the sanitized order ID from the URL
        'ajaxUrl'   => esc_url(admin_url('admin-ajax.php')),
        'nonce'     => wp_create_nonce('vestor_pay_nonce_action')
    ));
}
add_action('wp_enqueue_scripts', 'vestor_pay_enqueue_scripts');

// Shortcode for displaying the payment form
function vestor_pay_shortcode() {
    // Ensure the order_id is present and sanitize it
    if (!isset($_GET['order_id'])) {
        return '<p>' . esc_html__('Invalid payment page. Please check your order details.', 'vestor-direct-crypto-payment-gateway-for-woocommerce') . '</p>';
    }

    // Sanitize and validate order_id
    $order_id = intval(sanitize_text_field(wp_unslash($_GET['order_id'])));
    $order = wc_get_order($order_id);

    // Check if order exists
    if (!$order) {
        return '<p>' . esc_html__('Invalid payment page. Please check your order details.', 'vestor-direct-crypto-payment-gateway-for-woocommerce') . '</p>';
    }

    $amount_zar = $order->get_total();
    $gateway = new WC_Gateway_Vestor_Pay();
    $conversion_rate = $gateway->conversion_rate;
    $amount_usd = $amount_zar / $conversion_rate; // Convert ZAR to USD

    $addresses = $gateway->addresses;
    $plugin_url = esc_url(plugin_dir_url(__FILE__)); // Get the plugin directory URL
    ob_start();
    ?>
<div id="vestor-payment-container" style="max-width:500px;background-color: #fff;border-radius:28px; border: 1px solid #ddd; padding:30px; margin:15px auto;">
    <div id="payment-selection">
        <!-- Display the amount in USD -->
        <h3 style="text-align: center;">USD <?php echo esc_html(number_format($amount_usd, 2)); ?></h3>
        <ul id="crypto-list">
            <?php foreach ($addresses as $currency => $address): ?>
                <?php if (!empty($address)): ?>
                    <li data-currency="<?php echo esc_attr($currency); ?>" data-address="<?php echo esc_attr($address); ?>" class="crypto-item">
                        <div class="crypto-pill">
                            <img src="<?php echo esc_url($plugin_url . 'icons/icon-' . strtolower(esc_attr($currency)) . '.svg'); ?>" alt="<?php echo esc_attr($currency); ?>" class="crypto-icon" />
                            <span class="currency-name"><?php echo esc_html($currency); ?></span>
                            <?php if ($currency == 'USDT_ETH' || $currency == 'USDT_TRX' || $currency == 'USDT_BSC') : ?><span class="network-label"><?php echo ($currency == 'USDT_ETH') ? esc_html('ETH') : (($currency == 'USDT_TRX') ? esc_html('TRX') : (($currency == 'USDT_BSC') ? esc_html('BSC') : '')); ?></span><?php endif; ?>
                        </div>
                        <span class="currency-code"><?php echo esc_html($currency); ?></span>
                    </li>
                <?php endif; ?>
            <?php endforeach; ?>
        </ul>
    </div>
  
    <div id="payment-details" class="hidden" style="text-align: center;">
        <h3><?php esc_html_e('Send payment', 'vestor-direct-crypto-payment-gateway-for-woocommerce'); ?></h3>
        <div id="qr-code" style="margin-bottom: 25px; display: inline-block;"></div>
        <p style="word-wrap: break-word;margin-bottom:8px;"><img id="crypto-icon-large" src="" alt="" class="crypto-icon-large" style="width:20px;height:20px;"> <?php esc_html_e('Only send', 'vestor-direct-crypto-payment-gateway-for-woocommerce'); ?> <span id="crypto-name"></span> <?php esc_html_e('to this address:', 'vestor-direct-crypto-payment-gateway-for-woocommerce'); ?></p>
        <div id="crypto-address" style="cursor: pointer; font-weight: bold; display: inline-block; word-wrap: break-word; max-width: 100%;margin-bottom:5px;"></div>
        <span id="copy-text" style="font-weight: bold; cursor: pointer;"><?php esc_html_e('Copy', 'vestor-direct-crypto-payment-gateway-for-woocommerce'); ?></span>
        <p style="margin-bottom:7px"><?php esc_html_e('Pay amount:', 'vestor-direct-crypto-payment-gateway-for-woocommerce'); ?> <?php echo esc_html(number_format($amount_usd, 2)); ?> <span id="crypto-unit">USD</span><br> <?php esc_html_e('Send the equivalent to the wallet address.', 'vestor-direct-crypto-payment-gateway-for-woocommerce'); ?></p>
        
        <button id="back-button" style="margin: 10px auto;"><?php esc_html_e('Back', 'vestor-direct-crypto-payment-gateway-for-woocommerce'); ?></button>

        <hr style="color:#000; margin-top: 20px;">

        <!-- Upload proof of payment -->
         <form id="payment-proof-form" enctype="multipart/form-data" style="margin-top: 15px;">
            <?php wp_nonce_field('vestor_pay_nonce_action', 'vestor_pay_nonce_field'); ?>
            <div class="form-group">
                <label for="payment-proof" style="color:#000!important;" class="form-label"><?php esc_html_e('Upload Payment Proof', 'vestor-direct-crypto-payment-gateway-for-woocommerce'); ?></label>
                <input type="file" id="payment-proof" name="payment_proof" class="form-control" accept="image/*,application/pdf" required>
            </div>
            <input type="hidden" id="crypto_used" name="crypto_used" value="">
            <button type="button" id="complete-payment" class="btn btn-primary"><?php esc_html_e('Complete payment', 'vestor-direct-crypto-payment-gateway-for-woocommerce'); ?></button>
        </form>
        <p id="confirmation-message" style="display:none; color: green;"><?php esc_html_e('Your payment will be verified by admin.', 'vestor-direct-crypto-payment-gateway-for-woocommerce'); ?></p>
    </div>
</div>
    <?php
    return ob_get_clean();
}
add_shortcode('vestor-pay', 'vestor_pay_shortcode');

// Upload Payment Proof
function upload_payment_proof() {
    check_ajax_referer('vestor_pay_nonce_action', 'vestor_pay_nonce_field');

    $order_id = isset($_POST['order_id']) ? intval(sanitize_text_field(wp_unslash($_POST['order_id']))) : 0;
    $crypto_used = isset($_POST['crypto_used']) ? sanitize_text_field(wp_unslash($_POST['crypto_used'])) : '';

    if ($order_id <= 0) {
        wp_send_json_error('Invalid order ID.');
        wp_die();
    }

    if (isset($_FILES['payment_proof']['name']) && !empty($_FILES['payment_proof']['name'])) {
        $uploaded = wp_handle_upload($_FILES['payment_proof'], array('test_form' => false));
        if (isset($uploaded['url']) && !isset($uploaded['error'])) {
            $proof_url = esc_url_raw($uploaded['url']);

            update_post_meta($order_id, '_payment_proof_url', $proof_url);
            update_post_meta($order_id, '_payment_proof_status', 'pending');
            update_post_meta($order_id, '_crypto_used', $crypto_used);

            $admin_email = get_option('admin_email');
            $subject = 'New Payment Proof for Order #' . $order_id;
            $message = 'A new payment proof has been uploaded for order #' . $order_id . '. View it here: ' . esc_url($proof_url);
            $headers = array('Content-Type: text/html; charset=UTF-8', 'From: Vestor Finance <' . sanitize_email($admin_email) . '>');

            wp_mail($admin_email, $subject, $message, $headers);
            wp_send_json_success('Payment proof uploaded successfully.');
        } else {
            wp_send_json_error('Error uploading file.');
        }
    } else {
        wp_send_json_error('No file uploaded.');
    }
    wp_die();
}
add_action('wp_ajax_upload_payment_proof', 'upload_payment_proof');
add_action('wp_ajax_nopriv_upload_payment_proof', 'upload_payment_proof');

// Admin menu for viewing payment proofs
add_action('admin_menu', 'register_payment_proof_menu_page');
function register_payment_proof_menu_page() {
    add_menu_page(
        'Payment Proofs',
        'Payment Proofs',
        'manage_options',
        'payment-proofs',
        'payment_proof_menu_page',
        'dashicons-clipboard',
        6
    );
}

function payment_proof_menu_page() {
    global $wpdb;

    // Fetch orders with payment proofs
    $orders_with_proof = $wpdb->get_results("
        SELECT post_id as order_id, meta_value as proof_url 
        FROM {$wpdb->postmeta} 
        WHERE meta_key = '_payment_proof_url'
    ");

    echo '<h1>Payment Proofs</h1>';
    echo '<table class="wp-list-table widefat fixed striped">';
    echo '<thead><tr><th>Order ID</th><th>*</th><th>Proof</th><th>Status</th><th>Action</th></tr></thead>';
    echo '<tbody>';

    foreach ($orders_with_proof as $proof) {
        $order_id = $proof->order_id;
        $proof_url = esc_url($proof->proof_url);
        $status = get_post_meta($order_id, '_payment_proof_status', true);
        $crypto_used = get_post_meta($order_id, '_crypto_used', true); // Fetch crypto used

        echo '<tr>';
        echo '<td>' . esc_html($order_id) . '</td>';
        echo '<td>' . esc_html($crypto_used) . '</td>'; // Display the crypto used
        echo '<td><a href="' . esc_url($proof_url) . '" target="_blank">' . esc_html__('View Proof', 'vestor-direct-crypto-payment-gateway-for-woocommerce') . '</a></td>';
        echo '<td>' . esc_html(ucfirst($status)) . '</td>';
        echo '<td>';
        if ($status == 'pending') {
            echo '<a href="' . esc_url(add_query_arg(array('action' => 'accept', 'order_id' => esc_attr($order_id)), admin_url('admin.php?page=payment-proofs'))) . '">' . esc_html__('Accept', 'vestor-direct-crypto-payment-gateway-for-woocommerce') . '</a> | ';
            echo '<a href="' . esc_url(add_query_arg(array('action' => 'reject', 'order_id' => esc_attr($order_id)), admin_url('admin.php?page=payment-proofs'))) . '">' . esc_html__('Reject', 'vestor-direct-crypto-payment-gateway-for-woocommerce') . '</a>';
        }
        echo '</td>';
        echo '</tr>';
    }

    echo '</tbody>';
    echo '</table>';
}


// Handle payment proof actions in the admin panel
add_action('init', 'handle_payment_proof_actions');
function handle_payment_proof_actions() {
    if (is_admin() && isset($_GET['page']) && $_GET['page'] == 'payment-proofs' && isset($_GET['action']) && isset($_GET['order_id'])) {
        $order_id = intval(sanitize_text_field(wp_unslash($_GET['order_id'])));
        $action = sanitize_text_field(wp_unslash($_GET['action']));

        if ($order_id > 0) {
            $order = wc_get_order($order_id);

            if ($order && $action === 'accept') {
                $order->update_status('completed');
                update_post_meta($order_id, '_payment_proof_status', 'accepted');
                wp_redirect(admin_url('admin.php?page=payment-proofs'));
                exit;
            } elseif ($order && $action === 'reject') {
                $customer_email = sanitize_email($order->get_billing_email());
                update_post_meta($order_id, '_payment_proof_status', 'rejected');

                $subject = 'Your Payment Proof Has Been Rejected';
                $message = 'Your payment proof for order #' . $order_id . ' has been rejected.';
                $headers = array('Content-Type: text/html; charset=UTF-8');

                wp_mail($customer_email, $subject, $message, $headers);
                wp_redirect(admin_url('admin.php?page=payment-proofs'));
                exit;
            }
        }
    }
}
