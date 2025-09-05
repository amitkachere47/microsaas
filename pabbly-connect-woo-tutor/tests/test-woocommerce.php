<?php

require_once 'bootstrap.php';

echo "--- Testing WooCommerce Webhook ---\n";

// Mock the WC_Order class
if (!class_exists('WC_Order')) {
    class WC_Order {
        public function get_customer_id() { return 1; }
        public function get_billing_email() { return 'test@example.com'; }
        public function get_total() { return 99.99; }
        public function get_currency() { return 'USD'; }
        public function get_payment_method_title() { return 'Stripe'; }
    }
}


// 1. Test with webhook disabled
echo "1. Testing with webhook disabled...\n";
update_option( 'pcwt_webhook_url', 'http://test.com/webhook' );
update_option( 'pcwt_active_webhooks', array( 'woocommerce_order_status_changed' => 0 ) );
$order = new WC_Order();
do_action( 'woocommerce_order_status_changed', 789, 'processing', 'completed', $order );
echo "Webhook should not be sent.\n\n";

// 2. Test with webhook enabled
echo "2. Testing with webhook enabled...\n";
update_option( 'pcwt_active_webhooks', array( 'woocommerce_order_status_changed' => 1 ) );
do_action( 'woocommerce_order_status_changed', 789, 'processing', 'completed', $order );
echo "Webhook should be sent.\n\n";

// 3. Test with no webhook URL
echo "3. Testing with no webhook URL...\n";
update_option( 'pcwt_webhook_url', '' );
do_action( 'woocommerce_order_status_changed', 789, 'processing', 'completed', $order );
echo "Webhook should not be sent.\n";

echo "--- WooCommerce Test Complete ---\n";
