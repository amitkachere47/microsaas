<?php

class PCWT_WooCommerce {

    public function __construct() {
        add_action( 'woocommerce_order_status_changed', array( $this, 'order_status_changed' ), 10, 4 );
    }

    public function order_status_changed( $order_id, $old_status, $new_status, $order ) {
        $active_webhooks = get_option( 'pcwt_active_webhooks' );
        if ( ! isset( $active_webhooks['woocommerce_order_status_changed'] ) || $active_webhooks['woocommerce_order_status_changed'] != 1 ) {
            return;
        }

        $webhook_url = get_option( 'pcwt_webhook_url' );
        if ( empty( $webhook_url ) ) {
            return;
        }

        $data = array(
            'event'             => 'woocommerce_order_status_changed',
            'order_id'          => $order_id,
            'old_status'        => $old_status,
            'new_status'        => $new_status,
            'customer_id'       => $order->get_customer_id(),
            'customer_email'    => $order->get_billing_email(),
            'total'             => $order->get_total(),
            'currency'          => $order->get_currency(),
            'payment_method'    => $order->get_payment_method_title(),
        );

        $this->send_webhook( $webhook_url, $data );
    }

    private function send_webhook( $webhook_url, $data ) {
        $args = array(
            'body'        => json_encode( $data ),
            'headers'     => array(
                'Content-Type' => 'application/json',
            ),
            'timeout'     => 60,
            'redirection' => 5,
            'blocking'    => true,
            'httpversion' => '1.0',
            'sslverify'   => false,
            'data_format' => 'body',
        );

        wp_remote_post( $webhook_url, $args );
    }
}

new PCWT_WooCommerce();
