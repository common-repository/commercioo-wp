<?php
namespace commercioo\admin;
Class Comm_Order_Logs {
    // instance
    private static $instance;
    private $api;
    // getInstance
    public static function get_instance() {
        if( ! isset( self::$instance ) ) {
            self::$instance = new self;
        }

        return self::$instance;
    }

    // __construct
    private function __construct() {
        $this->api = \Comm_API::get_instance();
    }

    public function endpoint_register() {
        // create-able
        register_rest_route( 'commercioo/v1', '/order_logs/(?P<id>[a-zA-Z0-9-]+)', array(
            'methods' => \WP_REST_Server::CREATABLE,
            'callback' => array( $this, 'insert_order_log' ),
            'args' => array(
                'id' => array(
                    'validate_callback' => function( $param, $request, $key ) {
                        return is_numeric( $param );
                    }
                ),
                'name' => array(
                    'validate_callback' => function( $param, $request, $key ) {
                        // allow only underscore and alphanumeric
                        return boolval( preg_match( '/^[a-zA-Z0-9_]+$/', $param ) );
                    }
                ),
                'description'
            ),
            'permission_callback' => function () {
                return current_user_can( 'publish_posts' );
            }
        ));

        // delete-able
        register_rest_route( 'commercioo/v1', '/order_logs/item/(?P<id>[a-zA-Z0-9-]+)', array(
            'methods' => \WP_REST_Server::DELETABLE,
            'callback' => array( $this, 'delete_log' ),
            'args' => array(
                'id' => array(
                    'validate_callback' => function( $param, $request, $key ) {
                        return is_numeric( $param );
                    }
                ),
            ),
            'permission_callback' => function () {
                return current_user_can( 'publish_posts' );
            }
        ));

        // read-able
        register_rest_route( 'commercioo/v1', '/order_logs/(?P<id>[a-zA-Z0-9-]+)', array(
            'methods' => \WP_REST_Server::READABLE,
            'callback' => array( $this, 'list_logs_per_order' ),
            'args' => array(
                'id' => array(
                    'validate_callback' => function( $param, $request, $key ) {
                        return is_numeric( $param );
                    }
                ),
            ),
            'permission_callback' => function () {
                return current_user_can( 'publish_posts' );
            }
        ));
    }

    public function insert_order_log( $request ) {
        $params = $request->get_params();
        $order_id = intval( $params[ 'id' ] );
        $log_name = sanitize_text_field( $params[ 'name' ] );
        $log_description = sanitize_text_field( $params[ 'description' ] );

        global $wpdb;
        $db_table = $wpdb->prefix . 'commercioo_order_logs';

        // insert log
        $insert = $wpdb->insert(
            $db_table,
            array(
                'order_id' => $order_id,
                'log_name' => $log_name,
                'log_description' => $log_description,
                'log_date' => date( "Y-m-d H:i:s" ),
            ),
            array( '%d', '%s', '%s', '%s' )
        );

        // get result
        if ( ! $insert ) {
            return new \WP_Error( 'error_insert_data', 'Error insert data', array( 'status' => 404 ) );
        }
        else {
            $results = $this->get_list_logs_per_order( $order_id );
            return rest_ensure_response( $results );
        }
    }

    public function delete_log( $request ) {
        $params = $request->get_params();
        $log_id = intval( $params[ 'id' ] );

        global $wpdb;
        $db_table = $wpdb->prefix . 'commercioo_order_logs';

        // get order_id by log_id
        $order_id = $wpdb->get_var( $wpdb->prepare( "
            SELECT order_id 
            FROM $db_table
            WHERE log_id = %d
            LIMIT 0, 1",
            $log_id
        ) );

        // delete the log data
        $delete = $wpdb->delete(
            $db_table,
            array( 'log_id' => $log_id ),
            array( '%d' )
        );

        // get result
        if ( ! $delete ) {
            return new \WP_Error( 'error_delete_data', 'Error delete data', array( 'status' => 404 ) );
        }
        else if ( ! intval( $order_id ) ) {
            return new \WP_Error( 'data_not_found', 'Order data not found', array( 'status' => 404 ) );
        }
        else {
            $results = $this->get_list_logs_per_order( $order_id );
            return rest_ensure_response( $results );
        }
    }

    public function list_logs_per_order( $request ) {
        $params = $request->get_params();
        $order_id = intval( $params[ 'id' ] );

        // get from database
        $results = $this->get_list_logs_per_order( $order_id );

        return rest_ensure_response( $results );
    }

    private function get_list_logs_per_order( $order_id ) {
        global $wpdb;
        $db_table = $wpdb->prefix . 'commercioo_order_logs';

        // get from database
        $order_logs = $wpdb->get_results( $wpdb->prepare( "
            SELECT log_id as id, 
            	log_name as name, 
            	log_description as description,
            	log_date 
            FROM $db_table
            WHERE order_id = %d
            ORDER BY log_id DESC",
            $order_id
        ) );

        // results with order id
        $results = array(
            'order_id' => $order_id,
            'order_logs' => $order_logs,
        );

        return $results;
    }
}

//Comm_Order_Logs::getInstance();