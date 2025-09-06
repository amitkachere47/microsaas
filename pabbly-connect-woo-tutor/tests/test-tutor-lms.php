<?php

require_once 'bootstrap.php';

echo "--- Testing Tutor LMS Webhooks ---\n";

// Test after_enroll with complex AND/OR conditions
echo "Testing after_enroll with complex AND/OR conditions...\n";
update_option( 'pcwt_webhook_url', 'http://test.com/webhook' );
update_option( 'pcwt_active_webhooks', array( 'tutor_after_enroll' => 1 ) );

$conditions = array(
    'tutor_after_enroll' => array(
        'logic' => 'and',
        'conditions' => array(
            array( 'field' => 'course_id', 'operator' => 'is', 'value' => 123 ),
            array(
                'logic' => 'or',
                'conditions' => array(
                    array( 'field' => 'user_id', 'operator' => 'is', 'value' => 1 ),
                    array( 'field' => 'user_id', 'operator' => 'is', 'value' => 2 )
                )
            )
        )
    )
);
update_option( 'pcwt_webhook_conditions', $conditions );

global $mock_post;

echo "Test case 1: Should pass (course_id=123, user_id=1)\n";
$mock_post = (object) array( 'post_author' => 1 );
do_action( 'tutor_after_enroll', 123, 456 );

echo "Test case 2: Should pass (course_id=123, user_id=2)\n";
$mock_post = (object) array( 'post_author' => 2 );
do_action( 'tutor_after_enroll', 123, 456 );

echo "Test case 3: Should fail (course_id=999, user_id=1)\n";
$mock_post = (object) array( 'post_author' => 1 );
do_action( 'tutor_after_enroll', 999, 456 );

echo "Test case 4: Should fail (course_id=123, user_id=3)\n";
$mock_post = (object) array( 'post_author' => 3 );
do_action( 'tutor_after_enroll', 123, 456 );

echo "--- Tutor LMS Tests Complete ---\n";
