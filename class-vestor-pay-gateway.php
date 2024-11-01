<?php 
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class WC_Gateway_Vestor_Pay extends WC_Payment_Gateway {

    public function __construct() {
        $this->id = 'vestor_pay';
        $this->icon = ''; // URL of the payment gateway icon
        $this->has_fields = true;
        $this->method_title = __('Vestor Pay', 'vestor-direct-crypto-payment-gateway-for-woocommerce');
        $this->method_description = __('Custom Payment Gateway with Vestor Pay', 'vestor-direct-crypto-payment-gateway-for-woocommerce');

        $this->init_form_fields();
        $this->init_settings();

        $this->title = $this->get_option('title');
        $this->description = $this->get_option('description');
        $this->conversion_rate = floatval($this->get_option('conversion_rate', 17)); // Default conversion rate to 17 if not set
        $this->payment_page_id = $this->get_option('payment_page_id'); // Get selected payment page ID

        // Retrieve the addresses entered in the admin panel
        $this->addresses = array(
            'BTC'  => $this->get_option('btc_address'),
            'BCH'  => $this->get_option('bch_address'),
            'ETH'       => $this->get_option('eth_address'),
            'USDT_ETH'  => $this->get_option('usdt_eth_address'),
            'USDC'      => $this->get_option('usdc_address'),
            'SHIB'      => $this->get_option('shib_address'),
            'LINK'  => $this->get_option('link_address'),
            'SOL'       => $this->get_option('sol_address'),
            'USDT_TRX'  => $this->get_option('usdt_trx_address'),
            'LTC'       => $this->get_option('ltc_address'),
            'DOGE'      => $this->get_option('doge_address'),
            'BNB'       => $this->get_option('bnb_address'),
            'BUSD'      => $this->get_option('busd_address'),
            'USDT_BSC'  => $this->get_option('usdt_bsc_address')
        );

        add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));
        add_action('woocommerce_thankyou_' . $this->id, array($this, 'thankyou_page'));
    }

    public function init_form_fields() {
        $this->form_fields = array(
            'enabled' => array(
                'title' => __('Enable/Disable', 'vestor-direct-crypto-payment-gateway-for-woocommerce'),
                'type' => 'checkbox',
                'label' => __('Enable Vestor Pay Payment Gateway', 'vestor-direct-crypto-payment-gateway-for-woocommerce'),
                'default' => 'yes'
            ),
            'title' => array(
                'title' => __('Title', 'vestor-direct-crypto-payment-gateway-for-woocommerce'),
                'type' => 'text',
                'description' => __('This controls the title displayed during checkout.', 'vestor-direct-crypto-payment-gateway-for-woocommerce'),
                'default' => __('Vestor Pay', 'vestor-direct-crypto-payment-gateway-for-woocommerce'),
                'desc_tip' => true
            ),
            'description' => array(
                'title' => __('Description', 'vestor-direct-crypto-payment-gateway-for-woocommerce'),
                'type' => 'textarea',
                'description' => __('This controls the description displayed during checkout.', 'vestor-direct-crypto-payment-gateway-for-woocommerce'),
                'default' => __('Pay with Vestor Pay.', 'vestor-direct-crypto-payment-gateway-for-woocommerce')
            ),
            'conversion_rate' => array(
                'title' => __('Store Native Currency to USD Conversion Rate', 'vestor-direct-crypto-payment-gateway-for-woocommerce'),
                'type' => 'number',
                'description' => __('Enter the conversion rate from Store Currency to USD. Default is 17.', 'vestor-direct-crypto-payment-gateway-for-woocommerce'),
                'default' => '17',
                'desc_tip' => true,
                'custom_attributes' => array('step' => '0.01', 'min' => '0.01')
            ),
            'payment_page_id' => array(
                'title' => __('Payment Page', 'vestor-direct-crypto-payment-gateway-for-woocommerce'),
                'type' => 'select',
                'description' => __('Select the page that has the [vestor-pay] shortcode.', 'vestor-direct-crypto-payment-gateway-for-woocommerce'),
                'options' => $this->get_pages(),
                'desc_tip' => true
            ),
            // Fields for each currency address
            'btc_address' => array(
                'title' => __('Bitcoin (BTC) Address', 'vestor-direct-crypto-payment-gateway-for-woocommerce'),
                'type' => 'text',
                'description' => __('Enter the Bitcoin (BTC) address.', 'vestor-direct-crypto-payment-gateway-for-woocommerce'),
                'default' => ''
            ),
            'bch_address' => array(
                'title' => __('Bitcoin Cash (BCH) Address', 'vestor-direct-crypto-payment-gateway-for-woocommerce'),
                'type' => 'text',
                'description' => __('Enter the Bitcoin Cash (BCH) address.', 'vestor-direct-crypto-payment-gateway-for-woocommerce'),
                'default' => ''
            ),
            'link_address' => array(
                'title' => __('Chainlink (LINK) Address', 'vestor-direct-crypto-payment-gateway-for-woocommerce'),
                'type' => 'text',
                'description' => __('Enter the Chainlink (LINK) address.', 'vestor-direct-crypto-payment-gateway-for-woocommerce'),
                'default' => ''
            ),
            'busd_address' => array(
                'title' => __('Binance USD (BUSD) Address', 'vestor-direct-crypto-payment-gateway-for-woocommerce'),
                'type' => 'text',
                'description' => __('Enter the Binance USD (BUSD) address.', 'vestor-direct-crypto-payment-gateway-for-woocommerce'),
                'default' => ''
            ),
            'doge_address' => array(
                'title' => __('Dogecoin (DOGE) Address', 'vestor-direct-crypto-payment-gateway-for-woocommerce'),
                'type' => 'text',
                'description' => __('Enter the Dogecoin (DOGE) address.', 'vestor-direct-crypto-payment-gateway-for-woocommerce'),
                'default' => ''
            ),
            'eth_address' => array(
                'title' => __('Ethereum (ETH) Address', 'vestor-direct-crypto-payment-gateway-for-woocommerce'),
                'type' => 'text',
                'description' => __('Enter the Ethereum (ETH) address.', 'vestor-direct-crypto-payment-gateway-for-woocommerce'),
                'default' => ''
            ),
            'ltc_address' => array(
                'title' => __('Litecoin (LTC) Address', 'vestor-direct-crypto-payment-gateway-for-woocommerce'),
                'type' => 'text',
                'description' => __('Enter the Litecoin (LTC) address.', 'vestor-direct-crypto-payment-gateway-for-woocommerce'),
                'default' => ''
            ),
            'shib_address' => array(
                'title' => __('Shiba Inu (SHIB) Address', 'vestor-direct-crypto-payment-gateway-for-woocommerce'),
                'type' => 'text',
                'description' => __('Enter the Shiba Inu (SHIB) address.', 'vestor-direct-crypto-payment-gateway-for-woocommerce'),
                'default' => ''
            ),
            'sol_address' => array(
                'title' => __('Solana (SOL) Address', 'vestor-direct-crypto-payment-gateway-for-woocommerce'),
                'type' => 'text',
                'description' => __('Enter the Solana (SOL) address.', 'vestor-direct-crypto-payment-gateway-for-woocommerce'),
                'default' => ''
            ),
            'usdc_address' => array(
                'title' => __('USD Coin (USDC) Address', 'vestor-direct-crypto-payment-gateway-for-woocommerce'),
                'type' => 'text',
                'description' => __('Enter the USD Coin (USDC) address.', 'vestor-direct-crypto-payment-gateway-for-woocommerce'),
                'default' => ''
            ),
            'usdt_bsc_address' => array(
                'title' => __('Tether (USDT) BEP-20 Address', 'vestor-direct-crypto-payment-gateway-for-woocommerce'),
                'type' => 'text',
                'description' => __('Enter the Tether (USDT) BEP-20 address.', 'vestor-direct-crypto-payment-gateway-for-woocommerce'),
                'default' => ''
            ),
            'usdt_eth_address' => array(
                'title' => __('Tether (USDT) ERC-20 Address', 'vestor-direct-crypto-payment-gateway-for-woocommerce'),
                'type' => 'text',
                'description' => __('Enter the Tether (USDT) ERC-20 address.', 'vestor-direct-crypto-payment-gateway-for-woocommerce'),
                'default' => ''
            ),
            'usdt_trx_address' => array(
                'title' => __('Tether (USDT) TRC-20 Address', 'vestor-direct-crypto-payment-gateway-for-woocommerce'),
                'type' => 'text',
                'description' => __('Enter the Tether (USDT) TRC-20 address.', 'vestor-direct-crypto-payment-gateway-for-woocommerce'),
                'default' => ''
            ),

        );
    }

    // Method to retrieve all pages for the dropdown
    public function get_pages() {
        $pages = get_pages();
        $options = array();
        foreach ($pages as $page) {
            $options[$page->ID] = $page->post_title;
        }
        return $options;
    }

    public function payment_fields() {
        echo '<p>' . __('You will be redirected to the payment page.', 'vestor-direct-crypto-payment-gateway-for-woocommerce') . '</p>';
    }

    public function process_payment($order_id) {
        $order = wc_get_order($order_id);

        // Mark as pending (we're awaiting the payment)
        $order->update_status('pending', __('Awaiting Vestor Pay payment', 'vestor-direct-crypto-payment-gateway-for-woocommerce'));

        // Clear the cart
        WC()->cart->empty_cart();

        // Get the selected payment page URL
        $payment_page_url = get_permalink($this->payment_page_id) . '?order_id=' . $order_id;

        // Redirect to the custom payment page
        return array(
            'result' => 'success',
            'redirect' => $payment_page_url
        );
    }

    public function thankyou_page() {
        echo '<p>' . __('Thank you for your order! Please check your email for instructions on how to pay.', 'vestor-direct-crypto-payment-gateway-for-woocommerce') . '</p>';
    }
}
?>
