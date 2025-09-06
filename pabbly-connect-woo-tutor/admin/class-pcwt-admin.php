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
        wp_enqueue_style( 'pcwt-admin-css', plugin_dir_url( __FILE__ ) . 'css/admin.css', array(), '1.4.1' );
        wp_enqueue_script( 'jquery-ui-sortable' );
        wp_enqueue_script( 'pcwt-admin-js', plugin_dir_url( __FILE__ ) . 'js/admin.js', array( 'jquery', 'jquery-ui-sortable' ), '1.4.1', true );
    }

    public function add_plugin_page() {
        add_menu_page( 'Pabbly Connect', 'Pabbly Connect', 'manage_options', 'pabbly-connect-settings', array( $this, 'create_admin_page' ), 'dashicons-share' );
        add_submenu_page( 'pabbly-connect-settings', 'Webhook Logs', 'Webhook Logs', 'manage_options', 'pabbly-connect-logs', array( $this, 'create_logs_page' ) );
    }

    public function create_logs_page() {
        require_once plugin_dir_path( __FILE__ ) . 'class-pcwt-log-list-table.php';
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
        $this->render_templates();
    }

    public function page_init() {
        register_setting( 'pcwt_option_group', 'pcwt_webhook_url', array( $this, 'sanitize_webhook_url' ) );
        register_setting( 'pcwt_option_group', 'pcwt_active_webhooks', array( $this, 'sanitize_settings' ) );
        register_setting( 'pcwt_option_group', 'pcwt_webhook_conditions', array( $this, 'sanitize_conditions' ) );

        add_settings_section( 'webhook_settings_section', 'Webhook Settings', null, 'pabbly-connect-settings' );
        add_settings_field( 'webhook_url', 'Pabbly Webhook URL', array( $this, 'webhook_url_callback' ), 'pabbly-connect-settings', 'webhook_settings_section' );

        $triggers = PCWT_Trigger_Manager::get_triggers();
        foreach ( $triggers as $group => $group_triggers ) {
            $section_id = sanitize_key($group) . '_triggers';
            add_settings_section( $section_id, '<button type="button" class="button-link section-toggle">' . $group . ' Triggers</button>', null, 'pabbly-connect-settings' );
            foreach ( $group_triggers as $trigger_id => $trigger_label ) {
                add_settings_field( $trigger_id, $trigger_label, array( $this, 'checkbox_callback' ), 'pabbly-connect-settings', $section_id, array( 'id' => $trigger_id ) );
            }
        }
    }

    public function sanitize_webhook_url( $input ) { return esc_url_raw( $input ); }
    public function sanitize_settings( $input ) {
        $output = array();
        if ( is_array( $input ) ) {
            foreach ( $input as $key => $value ) {
                $output[ sanitize_key( $key ) ] = intval( $value );
            }
        }
        return $output;
    }
    public function sanitize_conditions( $input ) {
        $output = array();
        if ( ! is_array( $input ) ) {
            return $output;
        }
        foreach ( $input as $trigger => $conditions ) {
            $output[ sanitize_key( $trigger ) ] = $this->sanitize_condition_group( $conditions );
        }
        return $output;
    }

    private function sanitize_condition_group( $group ) {
        $output = array();
        if ( ! is_array( $group ) ) {
            return $output;
        }
        if ( isset( $group['logic'] ) ) {
            $output['logic'] = sanitize_text_field( $group['logic'] );
        }
        if ( isset( $group['conditions'] ) && is_array( $group['conditions'] ) ) {
            foreach ( $group['conditions'] as $item ) {
                if ( isset( $item['conditions'] ) ) {
                    $output['conditions'][] = $this->sanitize_condition_group( $item );
                } else {
                    $output['conditions'][] = array(
                        'field'    => sanitize_text_field( $item['field'] ),
                        'operator' => sanitize_text_field( $item['operator'] ),
                        'value'    => sanitize_text_field( $item['value'] ),
                    );
                }
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
        $conditions = get_option( 'pcwt_webhook_conditions' );
        $id = $args['id'];
        $checked = isset( $options[$id] ) && $options[$id] == 1 ? 'checked' : '';

        echo "<input type='checkbox' id='$id' name='pcwt_active_webhooks[$id]' value='1' $checked class='webhook-checkbox' />";

        echo "<div class='conditions-wrapper' style='" . ( $checked ? '' : 'display:none;' ) . "'>";
        echo "<h4>Conditions</h4>";

        $trigger_conditions = isset( $conditions[$id] ) ? $conditions[$id] : array();
        $this->render_condition_group( $id, $trigger_conditions, "pcwt_webhook_conditions[$id]" );

        echo "</div>";
    }

    private function render_condition_group( $trigger, $group, $path ) {
        $logic = isset( $group['logic'] ) ? $group['logic'] : 'and';
        $conditions = isset( $group['conditions'] ) ? $group['conditions'] : array();

        echo "<div class='condition-group'>";
        echo "<div class='group-logic'><select name='{$path}[logic]'>";
        echo "<option value='and' " . selected( $logic, 'and', false ) . ">AND</option>";
        echo "<option value='or' " . selected( $logic, 'or', false ) . ">OR</option>";
        echo "</select></div>";

        echo "<div class='conditions-list'>";
        if ( ! empty( $conditions ) ) {
            foreach ( $conditions as $index => $item ) {
                $item_path = "{$path}[conditions][{$index}]";
                if ( isset( $item['conditions'] ) ) {
                    $this->render_condition_group( $trigger, $item, $item_path );
                } else {
                    $this->render_condition_row( $trigger, $item, $item_path );
                }
            }
        }
        echo "</div>";

        echo "<div class='group-actions'>";
        echo "<button type='button' class='button add-condition' data-trigger='$trigger'>Add Condition</button>";
        echo "<button type='button' class='button add-group' data-trigger='$trigger'>Add Group</button>";
        echo "<button type='button' class='button remove-group'>Remove Group</button>";
        echo "</div></div>";
    }

    private function render_condition_row( $trigger, $condition, $path ) {
        $field = isset( $condition['field'] ) ? $condition['field'] : '';
        $operator = isset( $condition['operator'] ) ? $condition['operator'] : '';
        $value = isset( $condition['value'] ) ? $condition['value'] : '';

        echo "<div class='condition-row'>";
        echo "<select name='{$path}[field]'>";
        foreach ( PCWT_Trigger_Manager::get_trigger_fields( $trigger ) as $field_key => $field_label ) {
            echo "<option value='$field_key' " . selected( $field, $field_key, false ) . ">$field_label</option>";
        }
        echo "</select>";
        echo "<select name='{$path}[operator]'>";
        foreach ( $this->get_operators() as $op_key => $op_label ) {
            echo "<option value='$op_key' " . selected( $operator, $op_key, false ) . ">$op_label</option>";
        }
        echo "</select>";
        echo "<input type='text' name='{$path}[value]' value='" . esc_attr( $value ) . "' />";
        echo "<button type='button' class='button remove-condition'>Remove</button>";
        echo "</div>";
    }

    private function get_operators() {
        return array( 'is' => 'Is', 'is_not' => 'Is Not', 'contains' => 'Contains', 'does_not_contain' => 'Does Not Contain' );
    }

    private function render_templates() {
        ?>
        <div id="condition-row-template" style="display: none;">
            <?php $this->render_condition_row( '{{trigger}}', array(), '{{path}}' ); ?>
        </div>
        <div id="condition-group-template" style="display: none;">
            <?php $this->render_condition_group( '{{trigger}}', array(), '{{path}}' ); ?>
        </div>
        <?php
    }
}

if ( is_admin() ) { new PCWT_Admin(); }
