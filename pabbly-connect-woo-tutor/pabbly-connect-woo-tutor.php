<?php
/**
 * Plugin Name: Pabbly Connect for Tutor LMS & WooCommerce
 * Plugin URI: https://example.com/
 * Description: Connects Tutor LMS Pro and WooCommerce to Pabbly Connect using webhooks.
 * Version: 1.1.3
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

define( 'PCWT_FILE', __FILE__ );

final class PCWT_Connector {

    private static $_instance = null;

    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    private function __construct() {
        add_action( 'plugins_loaded', array( $this, 'init' ) );
    }

    public function init() {
        $this->includes();

        new PCWT_Installer();
        new PCWT_WordPress_Core();

        if ( is_admin() ) {
            new PCWT_Admin();
        }

        if ( $this->is_tutor_lms_active() ) {
            new PCWT_Tutor_LMS();
        }

        if ( $this->is_woocommerce_active() ) {
            new PCWT_WooCommerce();
        }

        add_action( 'admin_notices', array( $this, 'admin_notices' ) );
    }

    public function includes() {
        require_once plugin_dir_path( __FILE__ ) . 'includes/class-pcwt-webhook-handler.php';
        require_once plugin_dir_path( __FILE__ ) . 'includes/class-pcwt-installer.php';
        require_once plugin_dir_path( __FILE__ ) . 'includes/class-pcwt-wordpress-core.php';

        if ( is_admin() ) {
            require_once plugin_dir_path( __FILE__ ) . 'admin/class-pcwt-log-list-table.php';
            require_once plugin_dir_path( __FILE__ ) . 'admin/class-pcwt-admin.php';
        }

        if ( $this->is_tutor_lms_active() ) {
            require_once plugin_dir_path( __FILE__ ) . 'includes/class-pcwt-tutor-lms.php';
        }

        if ( $this->is_woocommerce_active() ) {
            require_once plugin_dir_path( __FILE__ ) . 'includes/class-pcwt-woocommerce.php';
        }
    }

    public function admin_notices() {
        if ( ! $this->is_tutor_lms_active() ) {
            ?>
            <div class="notice notice-error">
                <p><?php _e( 'Pabbly Connect for Tutor LMS & WooCommerce requires <strong>Tutor LMS</strong> to be installed and active.', 'pcwt' ); ?></p>
            </div>
            <?php
        }
        if ( ! $this->is_woocommerce_active() ) {
            ?>
            <div class="notice notice-error">
                <p><?php _e( 'Pabbly Connect for Tutor LMS & WooCommerce requires <strong>WooCommerce</strong> to be installed and active.', 'pcwt' ); ?></p>
            </div>
            <?php
        }
    }

    public function is_tutor_lms_active() {
        return class_exists( 'Tutor' );
    }

    public function is_woocommerce_active() {
        return class_exists( 'WooCommerce' );
    }
}

function pcwt_connector() {
    return PCWT_Connector::instance();
}

// Kick it off.
pcwt_connector();
