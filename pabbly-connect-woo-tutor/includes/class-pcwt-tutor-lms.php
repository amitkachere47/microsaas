<?php

class PCWT_Tutor_LMS {

    public function __construct() {
        add_action( 'tutor_after_enroll', array( $this, 'after_enroll' ), 10, 2 );
    }

    public function after_enroll( $course_id, $enrollment_id ) {
        $active_webhooks = get_option( 'pcwt_active_webhooks' );
        if ( ! isset( $active_webhooks['tutor_after_enroll'] ) || $active_webhooks['tutor_after_enroll'] != 1 ) {
            return;
        }

        $webhook_url = get_option( 'pcwt_webhook_url' );
        if ( empty( $webhook_url ) ) {
            return;
        }

        $user_id = get_current_user_id();
        $user_info = get_userdata( $user_id );
        $course = get_post( $course_id );

        $data = array(
            'event'         => 'tutor_after_enroll',
            'user_id'       => $user_id,
            'user_email'    => $user_info->user_email,
            'user_name'     => $user_info->display_name,
            'course_id'     => $course_id,
            'course_title'  => $course->post_title,
            'enrollment_id' => $enrollment_id,
            'enrollment_date' => current_time( 'mysql' ),
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

new PCWT_Tutor_LMS();
