<?php

class PCWT_Installer {

    public function __construct() {
        register_activation_hook( PCWT_FILE, array( $this, 'activate' ) );
    }

    public function activate() {
        $this->create_log_table();
    }

    private function create_log_table() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'pcwt_webhook_logs';
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            event varchar(255) NOT NULL,
            status varchar(10) NOT NULL,
            request longtext NOT NULL,
            response longtext NOT NULL,
            created_at datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
            PRIMARY KEY  (id)
        ) $charset_collate;";

        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        dbDelta( $sql );
    }
}

new PCWT_Installer();
