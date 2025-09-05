<?php

class PCWT_Admin {

    public function __construct() {
        add_action( 'admin_menu', array( $this, 'add_plugin_page' ) );
        add_action( 'admin_init', array( $this, 'page_init' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
    }

    public function enqueue_scripts( $hook ) {
        if ( 'pabbly-connect_page_pabbly-connect-logs' !== $hook && 'toplevel_page_pabbly-connect-settings' !== $hook && 'pabbly-connect_page_pabbly-connect-settings' !== $hook) {
            return;
        }
        wp_enqueue_style( 'pcwt-admin-css', plugin_dir_url( __FILE__ ) . 'css/admin.css', array(), '1.1.0' );
        wp_enqueue_script( 'pcwt-admin-js', plugin_dir_url( __FILE__ ) . 'js/admin.js', array( 'jquery' ), '1.1.0', true );
    }

    public function add_plugin_page() {
        add_menu_page( 'Pabbly Connect', 'Pabbly Connect', 'manage_options', 'pabbly-connect-settings', array( $this, 'create_admin_page' ), 'dashicons-share' );
        add_submenu_page( 'pabbly-connect-settings', 'Webhook Logs', 'Webhook Logs', 'manage_options', 'pabbly-connect-logs', array( $this, 'create_logs_page' ) );
    }

    public function create_logs_page() {
        $log_table = new PCWT_Log_List_Table();
        $log_table->prepare_items();
        ?>
        <div class="wrap">
            <h1>Webhook Logs</h1>
            <?php $log_table->display(); ?>
        </div>
        <?php
    }

    public function create_admin_page() {
        ?>
        <div class="wrap">
            <h1>Pabbly Connect for Tutor LMS & WooCommerce</h1>
            <p>Configure the webhooks to send data to Pabbly Connect.</p>
            <form method="post" action="options.php">
            <?php
                settings_fields( 'pcwt_option_group' );
                do_settings_sections( 'pabbly-connect-settings' );
                submit_button();
            ?>
            </form>
        </div>
        <?php
    }

    public function page_init() {
        register_setting( 'pcwt_option_group', 'pcwt_webhook_url', array( $this, 'sanitize_webhook_url' ) );
        register_setting( 'pcwt_option_group', 'pcwt_active_webhooks', array( $this, 'sanitize_settings' ) );
        register_setting( 'pcwt_option_group', 'pcwt_tutor_lms_courses' );

        add_settings_section( 'webhook_settings_section', 'Webhook Settings', null, 'pabbly-connect-settings' );
        add_settings_field( 'webhook_url', 'Pabbly Webhook URL', array( $this, 'webhook_url_callback' ), 'pabbly-connect-settings', 'webhook_settings_section' );

        add_settings_section( 'wordpress_core_triggers', '<button type="button" class="button-link section-toggle">WordPress Core Triggers</button>', null, 'pabbly-connect-settings' );
        add_settings_field( 'user_register', 'User Registers', array( $this, 'checkbox_callback' ), 'pabbly-connect-settings', 'wordpress_core_triggers', array( 'id' => 'user_register' ) );
        add_settings_field( 'publish_post', 'Post Published', array( $this, 'checkbox_callback' ), 'pabbly-connect-settings', 'wordpress_core_triggers', array( 'id' => 'publish_post' ) );
        add_settings_field( 'comment_post', 'Comment Posted', array( $this, 'checkbox_callback' ), 'pabbly-connect-settings', 'wordpress_core_triggers', array( 'id' => 'comment_post' ) );

        add_settings_section( 'woocommerce_triggers', '<button type="button" class="button-link section-toggle">WooCommerce Triggers</button>', null, 'pabbly-connect-settings' );
        add_settings_field( 'woocommerce_order_status_changed', 'Order Status Changed', array( $this, 'checkbox_callback' ), 'pabbly-connect-settings', 'woocommerce_triggers', array( 'id' => 'woocommerce_order_status_changed' ) );
        add_settings_field( 'woocommerce_new_order', 'Order Created', array( $this, 'checkbox_callback' ), 'pabbly-connect-settings', 'woocommerce_triggers', array( 'id' => 'woocommerce_new_order' ) );

        add_settings_section( 'tutor_lms_triggers', '<button type="button" class="button-link section-toggle">Tutor LMS Triggers</button>', null, 'pabbly-connect-settings' );
        add_settings_field( 'tutor_lms_courses', 'Filter by Course', array( $this, 'tutor_lms_courses_callback' ), 'pabbly-connect-settings', 'tutor_lms_triggers' );
        add_settings_field( 'tutor_after_enroll', 'Student Enrolls in Course', array( $this, 'checkbox_callback' ), 'pabbly-connect-settings', 'tutor_lms_triggers', array( 'id' => 'tutor_after_enroll' ) );
    }

    public function sanitize_webhook_url( $input ) { return esc_url_raw( $input ); }
    public function sanitize_settings( $input ) {
        $output = array();
        if ( is_array( $input ) ) {
            foreach ( $input as $key => $value ) {
                $output[ sanitize_key( $key ) ] = is_array( $value ) ? array_map( 'intval', $value ) : intval( $value );
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

    public function tutor_lms_courses_callback() {
        $courses = get_posts( array( 'post_type' => 'courses', 'numberposts' => -1 ) );
        $selected_courses = get_option( 'pcwt_tutor_lms_courses', array() );
        echo '<select id="tutor_lms_courses" name="pcwt_tutor_lms_courses[]" multiple="multiple" style="width:100%;">';
        echo '<option value="all" ' . selected( in_array( 'all', $selected_courses ), true, false ) . '>All Courses</option>';
        if( !empty( $courses ) ) {
            foreach ( $courses as $course ) {
                echo '<option value="' . esc_attr( $course->ID ) . '" ' . selected( in_array( $course->ID, $selected_courses ), true, false ) . '>' . esc_html( $course->post_title ) . '</option>';
            }
        }
        echo '</select>';
    }
}

if ( is_admin() ) { new PCWT_Admin(); }
