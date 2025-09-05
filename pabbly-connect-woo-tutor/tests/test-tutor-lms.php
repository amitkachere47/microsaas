<?php

require_once 'bootstrap.php';

echo "--- Testing Tutor LMS Webhooks ---\n";

// Test after_enroll
echo "Testing after_enroll...\n";
update_option( 'pcwt_webhook_url', 'http://test.com/webhook' );
update_option( 'pcwt_active_webhooks', array( 'tutor_after_enroll' => 1 ) );
do_action( 'tutor_after_enroll', 123, 456 );
echo "Webhook should be sent.\n\n";

// Test lesson_completed
echo "Testing lesson_completed...\n";
update_option( 'pcwt_active_webhooks', array( 'tutor_lesson_completed' => 1 ) );
do_action( 'tutor_lesson_completed_after', 789 );
echo "Webhook should be sent.\n\n";

// Test quiz_passed
echo "Testing quiz_passed...\n";
update_option( 'pcwt_active_webhooks', array( 'tutor_quiz_passed' => 1 ) );
$attempt = new stdClass();
$attempt->is_passed = true;
$attempt->user_id = 1;
$attempt->quiz_id = 123;
$attempt->course_id = 456;
do_action( 'tutor_quiz/attempt_ended', $attempt );
echo "Webhook should be sent.\n\n";

// Test assignment_submitted
echo "Testing assignment_submitted...\n";
update_option( 'pcwt_active_webhooks', array( 'tutor_assignment_submitted' => 1 ) );
$assignment_submit = new stdClass();
$assignment_submit->post_author = 1;
$assignment_submit->post_parent = 123;
do_action( 'tutor_assignment/after_submit', 456 );
echo "Webhook should be sent.\n\n";

// Test question_posted
echo "Testing question_posted...\n";
update_option( 'pcwt_active_webhooks', array( 'tutor_question_posted' => 1 ) );
$question = new stdClass();
$question->post_author = 1;
do_action( 'tutor_qna/new_question_posted', 789 );
echo "Webhook should be sent.\n\n";

echo "--- Tutor LMS Tests Complete ---\n";
