<?php

require_once 'bootstrap.php';

echo "--- Testing Tutor LMS Webhooks ---\n";

// Test after_enroll with no filter
echo "Testing after_enroll with no course filter...\n";
update_option( 'pcwt_webhook_url', 'http://test.com/webhook' );
update_option( 'pcwt_active_webhooks', array( 'tutor_after_enroll' => 1 ) );
update_option( 'pcwt_tutor_lms_courses', array( 'all' ) );
do_action( 'tutor_after_enroll', 123, 456 );
echo "Webhook should be sent.\n\n";

// Test after_enroll with matching course filter
echo "Testing after_enroll with matching course filter...\n";
update_option( 'pcwt_tutor_lms_courses', array( 123 ) );
do_action( 'tutor_after_enroll', 123, 456 );
echo "Webhook should be sent.\n\n";

// Test after_enroll with non-matching course filter
echo "Testing after_enroll with non-matching course filter...\n";
update_option( 'pcwt_tutor_lms_courses', array( 999 ) );
do_action( 'tutor_after_enroll', 123, 456 );
echo "Webhook should NOT be sent.\n\n";

// Test lesson_completed with course filter
echo "Testing lesson_completed with course filter...\n";
update_option( 'pcwt_active_webhooks', array( 'tutor_lesson_completed' => 1 ) );
update_option( 'pcwt_tutor_lms_courses', array( 123 ) );
do_action( 'tutor_lesson_completed_after', 789 );
echo "Webhook should be sent.\n\n";

// ... you could add more tests for other triggers as well

echo "--- Tutor LMS Tests Complete ---\n";
