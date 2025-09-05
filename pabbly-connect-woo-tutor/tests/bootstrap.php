<?php

// Mock WordPress functions and constants
define( 'WPINC', 'wp-includes' );
define( 'ABSPATH', dirname( __DIR__ ) . '/' );
define( 'PLUGIN_ROOT', dirname( __DIR__ ) . '/' );

$mock_options = array();
$mock_actions = array();

function add_action( $hook, $callback, $priority = 10, $accepted_args = 1 ) {
    global $mock_actions;
    $mock_actions[$hook][] = $callback;
}

function do_action( $hook, ...$args ) {
    global $mock_actions;
    if (isset($mock_actions[$hook])) {
        foreach ($mock_actions[$hook] as $callback) {
            call_user_func_array($callback, $args);
        }
    }
}

function add_filter( $hook, $callback, $priority = 10, $accepted_args = 1 ) {}
function get_option( $option_name, $default = false ) {
    global $mock_options;
    return isset( $mock_options[$option_name] ) ? $mock_options[$option_name] : $default;
}
function update_option( $option_name, $option_value ) {
    global $mock_options;
    $mock_options[$option_name] = $option_value;
    return true;
}
function plugin_dir_path( $file ) { return PLUGIN_ROOT; }
function plugin_dir_url( $file ) { return 'http://example.com/wp-content/plugins/pabbly-connect-woo-tutor/'; }
function esc_url_raw( $url ) { return $url; }
function esc_attr( $text ) { return $text; }
function is_admin() { return true; }
function get_current_user_id() { return 1; }
function get_userdata( $user_id ) {
    $user = new stdClass();
    $user->user_email = 'test@example.com';
    $user->display_name = 'Test User';
    $user->roles = array('customer');
    return $user;
}
function get_post( $post_id ) {
    $post = new stdClass();
    $post->post_title = 'Test Post';
    $post->post_author = 1;
    $post->post_parent = 123;
    return $post;
}
function current_time( $type ) { return date( 'Y-m-d H:i:s' ); }
function wp_remote_post( $url, $args ) {
    echo "--- Webhook Sent ---\n";
    echo "URL: $url\n";
    echo "Data: " . $args['body'] . "\n";
    echo "--------------------\n";
}
function wc_get_order( $order_id ) { return new WC_Order(); }
function wc_get_product( $product_id ) { return new WC_Product(); }
function get_comment( $comment_id ) {
    $comment = new stdClass();
    $comment->comment_author = 'Test Author';
    $comment->comment_content = 'Test comment';
    $comment->comment_post_ID = 123;
    return $comment;
}
function get_post_meta( $post_id, $key, $single ) { return 123; }
function get_the_title( $post_id ) { return 'Test Title'; }
function wp_enqueue_style( ...$args ) {}
function wp_enqueue_script( ...$args ) {}
function sanitize_key( $key ) { return $key; }

// Load the plugin
require_once ABSPATH . 'pabbly-connect-woo-tutor.php';
