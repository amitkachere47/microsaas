<?php

require_once 'bootstrap.php';

echo "--- Testing WordPress Core Webhooks ---\n";

// Test user_register
echo "Testing user_register...\n";
update_option( 'pcwt_webhook_url', 'http://test.com/webhook' );
update_option( 'pcwt_active_webhooks', array( 'user_register' => 1 ) );
do_action( 'user_register', 123 );
echo "Webhook should be sent.\n\n";

// Test publish_post
echo "Testing publish_post...\n";
update_option( 'pcwt_active_webhooks', array( 'publish_post' => 1 ) );
$post = new stdClass();
$post->post_title = 'Test Post';
$post->post_type = 'post';
$post->post_author = 1;
do_action( 'publish_post', 456, $post );
echo "Webhook should be sent.\n\n";

// Test comment_post
echo "Testing comment_post...\n";
update_option( 'pcwt_active_webhooks', array( 'comment_post' => 1 ) );
do_action( 'comment_post', 789, 1 );
echo "Webhook should be sent.\n\n";

echo "--- WordPress Core Tests Complete ---\n";
