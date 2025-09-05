<?php

require_once 'bootstrap.php';

echo "--- Testing Tutor LMS Webhook ---\n";

// 1. Test with webhook disabled
echo "1. Testing with webhook disabled...\n";
update_option( 'pcwt_webhook_url', 'http://test.com/webhook' );
update_option( 'pcwt_active_webhooks', array( 'tutor_after_enroll' => 0 ) );
do_action( 'tutor_after_enroll', 123, 456 );
echo "Webhook should not be sent.\n\n";

// 2. Test with webhook enabled
echo "2. Testing with webhook enabled...\n";
update_option( 'pcwt_active_webhooks', array( 'tutor_after_enroll' => 1 ) );
do_action( 'tutor_after_enroll', 123, 456 );
echo "Webhook should be sent.\n\n";

// 3. Test with no webhook URL
echo "3. Testing with no webhook URL...\n";
update_option( 'pcwt_webhook_url', '' );
do_action( 'tutor_after_enroll', 123, 456 );
echo "Webhook should not be sent.\n";

echo "--- Tutor LMS Test Complete ---\n";
