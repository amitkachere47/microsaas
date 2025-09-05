<?php

require_once 'bootstrap.php';

echo "--- Testing WooCommerce Webhooks ---\n";

// Mock the WC_Order class
if (!class_exists('WC_Order')) {
    class WC_Order {
        public function get_customer_id() { return 1; }
        public function get_billing_email() { return 'test@example.com'; }
        public function get_total() { return 99.99; }
        public function get_currency() { return 'USD'; }
        public function get_payment_method_title() { return 'Stripe'; }
        public function get_status() { return 'processing'; }
    }
}
if (!class_exists('WC_Product')) {
    class WC_Product {
        public function get_name() { return 'Test Product'; }
        public function get_sku() { return 'TEST-SKU'; }
        public function get_price() { return 49.99; }
    }
}

// Test order_status_changed
echo "Testing order_status_changed...\n";
update_option( 'pcwt_webhook_url', 'http://test.com/webhook' );
update_option( 'pcwt_active_webhooks', array( 'woocommerce_order_status_changed' => 1 ) );
$order = new WC_Order();
do_action( 'woocommerce_order_status_changed', 789, 'processing', 'completed', $order );
echo "Webhook should be sent.\n\n";

// Test new_order
echo "Testing new_order...\n";
update_option( 'pcwt_active_webhooks', array( 'woocommerce_new_order' => 1 ) );
do_action( 'woocommerce_new_order', 789 );
echo "Webhook should be sent.\n\n";

// Test new_product
echo "Testing new_product...\n";
update_option( 'pcwt_active_webhooks', array( 'woocommerce_new_product' => 1 ) );
$post = new stdClass();
do_action( 'save_post_product', 123, $post, false );
echo "Webhook should be sent.\n\n";

// Test new_customer
echo "Testing new_customer...\n";
update_option( 'pcwt_active_webhooks', array( 'woocommerce_new_customer' => 1 ) );
do_action( 'user_register', 456 );
echo "Webhook should be sent.\n\n";

// Test add_to_cart
echo "Testing add_to_cart...\n";
update_option( 'pcwt_active_webhooks', array( 'woocommerce_add_to_cart' => 1 ) );
do_action( 'woocommerce_add_to_cart', 'key', 789, 1, 0, array(), array() );
echo "Webhook should be sent.\n\n";


echo "--- WooCommerce Tests Complete ---\n";
