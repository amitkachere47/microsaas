<?php

class PCWT_Admin {

    public function __construct() {
        add_action( 'admin_menu', array( $this, 'add_plugin_page' ) );
        add_action( 'admin_init', array( $this, 'page_init' ) );
    }

    public function add_plugin_page() {
        add_options_page(
            'Pabbly Connect Settings',
            'Pabbly Connect',
            'manage_options',
            'pabbly-connect-settings',
            array( $this, 'create_admin_page' )
        );
    }

    public function create_admin_page() {
        ?>
        <div class="wrap">
            <h1>Pabbly Connect for Tutor LMS & WooCommerce</h1>
            <p>Configure the webhooks to send data to Pabbly Connect.</p>
            <form method="post" action="options.php">
            <?php
                settings_fields( 'pcwt_option_group' );
                do_settings_sections( 'pcwt-admin' );
                submit_button();
            ?>
            </form>
        </div>
        <?php
    }

    public function page_init() {
        // Register Webhook URL setting
        register_setting(
            'pcwt_option_group',
            'pcwt_webhook_url',
            array( $this, 'sanitize_webhook_url' )
        );

        // Register Active Webhooks setting
        register_setting(
            'pcwt_option_group',
            'pcwt_active_webhooks',
            array( $this, 'sanitize_active_webhooks' )
        );

        // Webhook URL Section
        add_settings_section(
            'webhook_settings_section',
            'Webhook Settings',
            array( $this, 'print_section_info' ),
            'pcwt-admin'
        );

        add_settings_field(
            'webhook_url',
            'Pabbly Webhook URL',
            array( $this, 'webhook_url_callback' ),
            'pcwt-admin',
            'webhook_settings_section'
        );

        // Active Webhooks Section
        add_settings_section(
            'active_webhooks_section',
            'Enable/Disable Webhooks',
            array( $this, 'print_webhooks_section_info' ),
            'pcwt-admin'
        );

        add_settings_field(
            'tutor_after_enroll',
            'Tutor LMS: Student Enrolls in a Course',
            array( $this, 'tutor_after_enroll_callback' ),
            'pcwt-admin',
            'active_webhooks_section'
        );

        add_settings_field(
            'woocommerce_order_status_changed',
            'WooCommerce: Order Status Changed',
            array( $this, 'woocommerce_order_status_changed_callback' ),
            'pcwt-admin',
            'active_webhooks_section'
        );
    }

    public function sanitize_webhook_url( $input ) {
        if( isset( $input ) )
            return esc_url_raw( $input );
        return '';
    }

    public function sanitize_active_webhooks( $input ) {
        $output = array();
        if( is_array( $input ) ) {
            foreach( $input as $key => $value ) {
                $output[$key] = intval( $value );
            }
        }
        return $output;
    }

    public function print_section_info() {
        print 'Enter your Pabbly Connect webhook URL below. This is where all the data will be sent.';
    }

    public function print_webhooks_section_info() {
        print 'Select the events for which you want to send data to Pabbly Connect.';
    }

    public function webhook_url_callback() {
        $webhook_url = get_option( 'pcwt_webhook_url' );
        printf(
            '<input type="text" id="webhook_url" name="pcwt_webhook_url" value="%s" size="50" />',
            isset( $webhook_url ) ? esc_attr( $webhook_url ) : ''
        );
    }

    public function tutor_after_enroll_callback() {
        $options = get_option( 'pcwt_active_webhooks' );
        $checked = isset( $options['tutor_after_enroll'] ) && $options['tutor_after_enroll'] == 1 ? 'checked' : '';
        echo '<input type="checkbox" id="tutor_after_enroll" name="pcwt_active_webhooks[tutor_after_enroll]" value="1" ' . $checked . ' />';
    }

    public function woocommerce_order_status_changed_callback() {
        $options = get_option( 'pcwt_active_webhooks' );
        $checked = isset( $options['woocommerce_order_status_changed'] ) && $options['woocommerce_order_status_changed'] == 1 ? 'checked' : '';
        echo '<input type="checkbox" id="woocommerce_order_status_changed" name="pcwt_active_webhooks[woocommerce_order_status_changed]" value="1" ' . $checked . ' />';
    }
}

if( is_admin() ) {
    new PCWT_Admin();
}
