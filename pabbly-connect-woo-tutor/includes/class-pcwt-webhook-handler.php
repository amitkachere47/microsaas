<?php

abstract class PCWT_Webhook_Handler {

    protected function trigger_webhook( $event, $data ) {
        $active_webhooks = get_option( 'pcwt_active_webhooks' );
        if ( ! isset( $active_webhooks[$event] ) || $active_webhooks[$event] != 1 ) {
            return;
        }

        $webhook_url = get_option( 'pcwt_webhook_url' );
        if ( empty( $webhook_url ) ) {
            return;
        }

        $data = array_merge( array('event' => $event), $data );

        $this->send_webhook( $webhook_url, $data );
    }

    private function send_webhook( $webhook_url, $data ) {
        $args = array(
            'body'        => json_encode( $data ),
            'headers'     => array( 'Content-Type' => 'application/json' ),
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
