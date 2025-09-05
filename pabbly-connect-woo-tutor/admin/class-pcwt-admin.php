<?php

class PCWT_Admin {

    public function __construct() {
        add_action( 'admin_menu', array( $this, 'add_plugin_page' ) );
        add_action( 'admin_init', array( $this, 'page_init' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
    }

    public function enqueue_scripts( $hook ) {
        if ( 'settings_page_pabbly-connect-settings' !== $hook ) {
            return;
        }
        wp_enqueue_style( 'pcwt-admin-css', plugin_dir_url( __FILE__ ) . 'css/admin.css', array(), '1.0.0' );
        wp_enqueue_script( 'pcwt-admin-js', plugin_dir_url( __FILE__ ) . 'js/admin.js', array( 'jquery' ), '1.0.0', true );
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
        register_setting( 'pcwt_option_group', 'pcwt_webhook_url', array( $this, 'sanitize_webhook_url' ) );
        register_setting( 'pcwt_option_group', 'pcwt_active_webhooks', array( $this, 'sanitize_active_webhooks' ) );

        // Webhook URL Section
        add_settings_section( 'webhook_settings_section', 'Webhook Settings', null, 'pcwt-admin' );
        add_settings_field( 'webhook_url', 'Pabbly Webhook URL', array( $this, 'webhook_url_callback' ), 'pcwt-admin', 'webhook_settings_section' );

        // WordPress Core Triggers
        add_settings_section( 'wordpress_core_triggers', '<button type="button" class="button-link section-toggle">WordPress Core Triggers</button>', null, 'pcwt-admin' );
        add_settings_field( 'user_register', 'User Registers', array( $this, 'checkbox_callback' ), 'pcwt-admin', 'wordpress_core_triggers', array( 'id' => 'user_register' ) );
        add_settings_field( 'publish_post', 'Post Published', array( $this, 'checkbox_callback' ), 'pcwt-admin', 'wordpress_core_triggers', array( 'id' => 'publish_post' ) );
        add_settings_field( 'comment_post', 'Comment Posted', array( $this, 'checkbox_callback' ), 'pcwt-admin', 'wordpress_core_triggers', array( 'id' => 'comment_post' ) );

        // WooCommerce Triggers
        add_settings_section( 'woocommerce_triggers', '<button type="button" class="button-link section-toggle">WooCommerce Triggers</button>', null, 'pcwt-admin' );
        add_settings_field( 'woocommerce_order_status_changed', 'Order Status Changed', array( $this, 'checkbox_callback' ), 'pcwt-admin', 'woocommerce_triggers', array( 'id' => 'woocommerce_order_status_changed' ) );
        add_settings_field( 'woocommerce_new_order', 'Order Created', array( $this, 'checkbox_callback' ), 'pcwt-admin', 'woocommerce_triggers', array( 'id' => 'woocommerce_new_order' ) );
        add_settings_field( 'woocommerce_new_product', 'Product Created', array( $this, 'checkbox_callback' ), 'pcwt-admin', 'woocommerce_triggers', array( 'id' => 'woocommerce_new_product' ) );
        add_settings_field( 'woocommerce_new_customer', 'Customer Created', array( $this, 'checkbox_callback' ), 'pcwt-admin', 'woocommerce_triggers', array( 'id' => 'woocommerce_new_customer' ) );
        add_settings_field( 'woocommerce_add_to_cart', 'Product Added to Cart', array( $this, 'checkbox_callback' ), 'pcwt-admin', 'woocommerce_triggers', array( 'id' => 'woocommerce_add_to_cart' ) );

        // Tutor LMS Triggers
        add_settings_section( 'tutor_lms_triggers', '<button type="button" class="button-link section-toggle">Tutor LMS Triggers</button>', null, 'pcwt-admin' );
        add_settings_field( 'tutor_after_enroll', 'Student Enrolls in Course', array( $this, 'checkbox_callback' ), 'pcwt-admin', 'tutor_lms_triggers', array( 'id' => 'tutor_after_enroll' ) );
        add_settings_field( 'tutor_lesson_completed', 'Lesson Completed', array( $this, 'checkbox_callback' ), 'pcwt-admin', 'tutor_lms_triggers', array( 'id' => 'tutor_lesson_completed' ) );
        add_settings_field( 'tutor_quiz_passed', 'Quiz Passed', array( $this, 'checkbox_callback' ), 'pcwt-admin', 'tutor_lms_triggers', array( 'id' => 'tutor_quiz_passed' ) );
        add_settings_field( 'tutor_assignment_submitted', 'Assignment Submitted', array( $this, 'checkbox_callback' ), 'pcwt-admin', 'tutor_lms_triggers', array( 'id' => 'tutor_assignment_submitted' ) );
        add_settings_field( 'tutor_question_posted', 'Question Posted', array( $this, 'checkbox_callback' ), 'pcwt-admin', 'tutor_lms_triggers', array( 'id' => 'tutor_question_posted' ) );
    }

    public function sanitize_webhook_url( $input ) {
        return esc_url_raw( $input );
    }

    public function sanitize_active_webhooks( $input ) {
        $output = array();
        if ( is_array( $input ) ) {
            foreach ( $input as $key => $value ) {
                $output[ sanitize_key( $key ) ] = intval( $value );
            }
        }
        return $output;
    }

    public function webhook_url_callback() {
        $webhook_url = get_option( 'pcwt_webhook_url' );
        printf( '<input type="text" id="webhook_url" name="pcwt_webhook_url" value="%s" size="50" />', isset( $webhook_url ) ? esc_attr( $webhook_url ) : '' );
    }

    public function checkbox_callback( $args ) {
        $options = get_option( 'pcwt_active_webhooks' );
        $id = $args['id'];
        $checked = isset( $options[$id] ) && $options[$id] == 1 ? 'checked' : '';
        echo "<input type='checkbox' id='$id' name='pcwt_active_webhooks[$id]' value='1' $checked />";
    }
}

if ( is_admin() ) {
    new PCWT_Admin();
}
