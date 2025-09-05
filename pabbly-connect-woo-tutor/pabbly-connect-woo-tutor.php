<?php
/**
 * Plugin Name: Pabbly Connect for Tutor LMS & WooCommerce
 * Plugin URI: https://example.com/
 * Description: Connects Tutor LMS Pro and WooCommerce to Pabbly Connect using webhooks.
 * Version: 1.0.0
 * Author: Jules
 * Author URI: https://example.com/
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain: pcwt
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

// Include the admin class.
require_once plugin_dir_path( __FILE__ ) . 'admin/class-pcwt-admin.php';

// Include the webhook handler base class.
require_once plugin_dir_path( __FILE__ ) . 'includes/class-pcwt-webhook-handler.php';

// Include the Tutor LMS integration class.
require_once plugin_dir_path( __FILE__ ) . 'includes/class-pcwt-tutor-lms.php';

// Include the WooCommerce integration class.
require_once plugin_dir_path( __FILE__ ) . 'includes/class-pcwt-woocommerce.php';

// Include the WordPress Core integration class.
require_once plugin_dir_path( __FILE__ ) . 'includes/class-pcwt-wordpress-core.php';
