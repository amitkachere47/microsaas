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

function add_filter( $hook, $callback, $priority = 10, $accepted_args = 1 ) {
    // For testing purposes, we can just check if the filter is added
}

function get_option( $option_name, $default = false ) {
    global $mock_options;
    return isset( $mock_options[$option_name] ) ? $mock_options[$option_name] : $default;
}

function update_option( $option_name, $option_value ) {
    global $mock_options;
    $mock_options[$option_name] = $option_value;
    return true;
}

function plugin_dir_path( $file ) {
    return PLUGIN_ROOT;
}

function esc_url_raw( $url ) {
    return $url;
}

function esc_attr( $text ) {
    return $text;
}

function is_admin() {
    return true;
}

function get_current_user_id() {
    return 1;
}

function get_userdata( $user_id ) {
    $user = new stdClass();
    $user->user_email = 'test@example.com';
    $user->display_name = 'Test User';
    return $user;
}

function get_post( $post_id ) {
    $post = new stdClass();
    $post->post_title = 'Test Course';
    return $post;
}

function current_time( $type ) {
    return date( 'Y-m-d H:i:s' );
}

function wp_remote_post( $url, $args ) {
    echo "--- Webhook Sent ---\n";
    echo "URL: $url\n";
    echo "Data: " . $args['body'] . "\n";
    echo "--------------------\n";
}

// Load the plugin
require_once ABSPATH . 'pabbly-connect-woo-tutor.php';
