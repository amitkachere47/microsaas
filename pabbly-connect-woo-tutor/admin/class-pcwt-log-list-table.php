<?php

class PCWT_Log_List_Table extends WP_List_Table {

    public function __construct() {
        parent::__construct( array(
            'singular' => 'Webhook Log',
            'plural'   => 'Webhook Logs',
            'ajax'     => false
        ) );
    }

    public function get_columns() {
        return array(
            'id'         => 'ID',
            'event'      => 'Event',
            'status'     => 'Status',
            'request'    => 'Request',
            'response'   => 'Response',
            'created_at' => 'Date',
        );
    }

    public function prepare_items() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'pcwt_webhook_logs';
        $per_page = 20;

        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();
        $this->_column_headers = array( $columns, $hidden, $sortable );

        $current_page = $this->get_pagenum();
        $total_items = $wpdb->get_var( "SELECT COUNT(id) FROM $table_name" );

        $this->set_pagination_args( array(
            'total_items' => $total_items,
            'per_page'    => $per_page
        ) );

        $orderby = ( isset( $_REQUEST['orderby'] ) && in_array( $_REQUEST['orderby'], array_keys( $this->get_sortable_columns() ) ) ) ? $_REQUEST['orderby'] : 'created_at';
        $order = ( isset( $_REQUEST['order'] ) && in_array( $_REQUEST['order'], array( 'asc', 'desc' ) ) ) ? $_REQUEST['order'] : 'desc';

        $offset = ( $current_page - 1 ) * $per_page;
        $this->items = $wpdb->get_results( $wpdb->prepare(
            "SELECT * FROM $table_name ORDER BY $orderby $order LIMIT %d OFFSET %d",
            $per_page,
            $offset
        ), ARRAY_A );
    }

    public function column_default( $item, $column_name ) {
        return $item[ $column_name ];
    }

    public function get_sortable_columns() {
        return array(
            'event'      => array( 'event', false ),
            'status'     => array( 'status', false ),
            'created_at' => array( 'created_at', true ),
        );
    }

    public function column_status( $item ) {
        return sprintf( '<span class="status-%s">%s</span>', esc_attr( $item['status'] ), esc_html( $item['status'] ) );
    }

    public function column_request( $item ) {
        return '<pre>' . esc_html( $item['request'] ) . '</pre>';
    }

    public function column_response( $item ) {
        return '<pre>' . esc_html( $item['response'] ) . '</pre>';
    }
}
