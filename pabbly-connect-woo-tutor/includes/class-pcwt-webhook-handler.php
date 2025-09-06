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

        $condition_result = $this->evaluate_conditions( $event, $data );
        if ( ! $condition_result['success'] ) {
            $this->log_request( $event, $data, 'stopped', $condition_result['message'] );
            return;
        }

        $this->send_webhook( $webhook_url, $event, $data );
    }

    private function evaluate_conditions( $event, $data ) {
        $conditions = get_option( 'pcwt_webhook_conditions' );
        if ( ! isset( $conditions[$event] ) || empty( $conditions[$event] ) ) {
            return array( 'success' => true, 'message' => '' );
        }
        return $this->process_condition_group( $conditions[$event], $data );
    }

    private function process_condition_group( $group, $data ) {
        $logic = isset( $group['logic'] ) ? $group['logic'] : 'and';
        $results = array();

        if ( ! isset( $group['conditions'] ) || ! is_array( $group['conditions'] ) ) {
            return array( 'success' => true, 'message' => '' );
        }

        foreach ( $group['conditions'] as $item ) {
            if ( isset( $item['conditions'] ) ) {
                $results[] = $this->process_condition_group( $item, $data );
            } else {
                $results[] = $this->process_condition( $item, $data );
            }
        }

        if ( 'and' === $logic ) {
            foreach ( $results as $result ) {
                if ( ! $result['success'] ) return $result;
            }
            return array( 'success' => true, 'message' => '' );
        } else {
            foreach ( $results as $result ) {
                if ( $result['success'] ) return array( 'success' => true, 'message' => '' );
            }
            return array( 'success' => false, 'message' => 'No OR conditions were met.' );
        }
    }

    private function process_condition( $condition, $data ) {
        if ( ! isset( $data[ $condition['field'] ] ) ) {
            return array( 'success' => false, 'message' => "Field '{$condition['field']}' not found in data." );
        }

        $data_value = $data[ $condition['field'] ];
        $condition_value = $condition['value'];
        $operator = $condition['operator'];
        $success = false;

        switch ( $operator ) {
            case 'is': $success = ( $data_value == $condition_value ); break;
            case 'is_not': $success = ( $data_value != $condition_value ); break;
            case 'contains': $success = ( stripos( (string) $data_value, (string) $condition_value ) !== false ); break;
            case 'does_not_contain': $success = ( stripos( (string) $data_value, (string) $condition_value ) === false ); break;
        }

        if ( ! $success ) {
            return array( 'success' => false, 'message' => "Condition failed: {$condition['field']} {$operator} {$condition['value']}" );
        }
        return array( 'success' => true, 'message' => '' );
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

    private function log_request( $event, $request_data, $response, $message = '' ) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'pcwt_webhook_logs';

        $status = 'failed';
        $response_body = $message;

        if ( $response === 'stopped' ) {
            $status = 'stopped';
        } elseif ( ! is_wp_error( $response ) && in_array( wp_remote_retrieve_response_code( $response ), array( 200, 201, 204 ) ) ) {
            $status = 'success';
            $response_body = wp_remote_retrieve_body( $response );
        } elseif ( is_wp_error( $response ) ) {
            $response_body = $response->get_error_message();
        }

        $wpdb->insert(
            $table_name,
            array(
                'event'      => $event,
                'status'     => $status,
                'request'    => json_encode( $request_data ),
                'response'   => $response_body,
                'created_at' => current_time( 'mysql' ),
            )
        );
    }
}
