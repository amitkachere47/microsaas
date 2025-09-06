<?php

class PCWT_WooCommerce extends PCWT_Webhook_Handler {

    public function __construct() {
        add_action( 'woocommerce_order_status_changed', array( $this, 'order_status_changed' ), 10, 4 );
        add_action( 'woocommerce_new_order', array( $this, 'new_order' ), 10, 1 );
        add_action( 'save_post_product', array( $this, 'new_product' ), 10, 3 );
        add_action( 'woocommerce_add_to_cart', array( $this, 'add_to_cart' ), 10, 6 );
    }

    public function order_status_changed( $order_id, $old_status, $new_status, $order ) {
        $this->trigger_webhook( 'woocommerce_order_status_changed', array(
            'order_id'       => $order_id,
            'old_status'     => $old_status,
            'new_status'     => $new_status,
            'customer_id'    => $order->get_customer_id(),
            'customer_email' => $order->get_billing_email(),
            'total'          => $order->get_total(),
        ) );
    }

    public function new_order( $order_id ) {
        $order = wc_get_order( $order_id );
        $this->trigger_webhook( 'woocommerce_new_order', array(
            'order_id'       => $order_id,
            'status'         => $order->get_status(),
            'customer_id'    => $order->get_customer_id(),
            'customer_email' => $order->get_billing_email(),
            'total'          => $order->get_total(),
        ) );
    }

    public function new_product( $post_id, $post, $update ) {
        if ( $update ) {
            return;
        }
        $product = wc_get_product( $post_id );
        $this->trigger_webhook( 'woocommerce_new_product', array(
            'product_id' => $post_id,
            'name'       => $product->get_name(),
            'sku'        => $product->get_sku(),
            'price'      => $product->get_price(),
        ) );
    }

    public function add_to_cart( $cart_item_key, $product_id, $quantity, $variation_id, $variation, $cart_item_data ) {
        $product = wc_get_product( $product_id );
        $this->trigger_webhook( 'woocommerce_add_to_cart', array(
            'product_id'   => $product_id,
            'product_name' => $product->get_name(),
            'quantity'     => $quantity,
            'user_id'      => get_current_user_id(),
        ) );
    }
}

new PCWT_WooCommerce();
