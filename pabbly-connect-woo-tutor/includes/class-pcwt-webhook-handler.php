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

        $this->send_webhook( $webhook_url, $event, $data );
    }

    private function send_webhook( $webhook_url, $event, $data ) {
        $payload = array_merge( array('event' => $event), $data );
        $args = array(
            'body'        => json_encode( $payload ),
            'headers'     => array( 'Content-Type' => 'application/json' ),
            'timeout'     => 60,
            'redirection' => 5,
            'blocking'    => true,
            'httpversion' => '1.0',
            'sslverify'   => false,
            'data_format' => 'body',
        );

        $response = wp_remote_post( $webhook_url, $args );

        $this->log_request( $event, $payload, $response );
    }

    private function log_request( $event, $request_data, $response ) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'pcwt_webhook_logs';

        $status = 'failed';
        if ( ! is_wp_error( $response ) && in_array( wp_remote_retrieve_response_code( $response ), array( 200, 201, 204 ) ) ) {
            $status = 'success';
        }

        $wpdb->insert(
            $table_name,
            array(
                'event'      => $event,
                'status'     => $status,
                'request'    => json_encode( $request_data ),
                'response'   => is_wp_error( $response ) ? $response->get_error_message() : wp_remote_retrieve_body( $response ),
                'created_at' => current_time( 'mysql' ),
            )
        );
    }
}
