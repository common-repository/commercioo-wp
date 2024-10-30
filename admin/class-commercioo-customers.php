<?php

namespace commercioo\admin;
Class Comm_Customers
{
    // instance
    private static $instance;

    // getInstance
    public static function get_instance()
    {
        if (!isset(self::$instance)) {
            self::$instance = new self;
        }

        return self::$instance;
    }

    // __construct
    public function __construct()
    {
        add_action( 'admin_post_comm_update_customer', array( $this , 'comm_update_customer') );
    }

    public function comm_customers(){
        $view_customers = $this->comm_do_customers(sanitize_post($_GET));
        echo wp_json_encode($view_customers);
        wp_die();
    }

    public function get_customers($args){
        global $wpdb;
        $results = array();

        $db_table = $wpdb->prefix . 'commercioo_customers';

        // get from database
        if ( $args['columns'][1]['search']['value'] ) {
            $results = $wpdb->get_results(
                $wpdb->prepare( "
                    SELECT * FROM $db_table 
                    WHERE name LIKE %s
                    ORDER BY customer_id DESC 
                    LIMIT %d, %d",
                    '%' . $wpdb->esc_like( $args['columns'][1]['search']['value'] ) . '%', 
                    intval( $args['start'] ), 
                    intval( $args['length'] )
                )
            );
        } else {
            $results = $wpdb->get_results(
                $wpdb->prepare( "
                    SELECT * FROM $db_table 
                    ORDER BY customer_id DESC 
                    LIMIT %d, %d",
                    intval( $args['start'] ), 
                    intval( $args['length'] )
                )
            );
        }

        return $results;
    }

    public function get_all_customers( $args ) {
        global $wpdb;
        $results = array();

        $db_table = $wpdb->prefix . 'commercioo_customers';

        if( $args['columns'][1]['search']['value'] ) {
            $results = $wpdb->get_results(
                $wpdb->prepare( "
                    SELECT * FROM $db_table 
                    WHERE name LIKE %s 
                    ORDER BY customer_id DESC",
                    '%' . $wpdb->esc_like( $args['columns'][1]['search']['value'] ) . '%'
                )
            );
        } else {
            $results = $wpdb->get_results( "
                SELECT * FROM $db_table 
                ORDER BY customer_id DESC"
            );
        }

        return $results;
    }

    public function comm_do_customers( $args ) {
        global $wpdb, $comm_options;
        $output['data'] = [];
        
        $user_items = $this->get_customers( $args );
        $all = $this->get_all_customers( $args );
        
        $i = 0;
        if ($user_items) {
            foreach ($user_items as $val) {
                $customer = new \Commercioo\Models\Customer($val->user_id,$val->customer_id);
                $customer_id = $val->customer_id;
                $sales =  $customer->get_customer_orders('comm_completed','ID',null,null);
                $orders = $customer->get_customer_orders(["comm_pending", "comm_processing", "comm_completed", "comm_refunded","comm_abandoned"],'ID',null,null);
                $total_spent = 0 ;
                if($sales){
                    foreach ($sales as $item) {
                        $order = new \Commercioo\Models\Order($item->ID);
                        $total_spent += $order->get_total();
                    }
                }

                if($customer->get_user()){
                    $checkbox = '<div class="table-option"><input type="checkbox" name="customer_id" value="' . $val->customer_id . '"><span class="btn btn-default" type="button" data-bs-toggle="dropdown"><i class="fa fa-angle-down"></i></span><ul class="dropdown-menu dropdown-menu-right"><li><a href="'. comm_controller()->comm_dash_page("comm_customers") .'&action=edit&id=' . $val->user_id . '">Edit</a></li><li class="delete"><a data-id="' . $val->customer_id . '" class="c-delete">Delete</a></li></ul></div>';
                }else{
                    $checkbox = '<div class="table-option"><input type="checkbox" name="customer_id" value="' . $val->customer_id . '"><span class="btn btn-default" type="button" data-bs-toggle="dropdown"><i class="fa fa-angle-down"></i></span><ul class="dropdown-menu dropdown-menu-right"><li class="delete"><a data-id="' . $val->customer_id . '" class="c-delete">Delete</a></li></ul></div>';
                }
                $name = !empty( $val->name ) && $val->name !=' ' ? $val->name : '-';
                $output['data'][$i] = [
                    $checkbox ,
                    ($customer->get_user()) ? '<a href="'. comm_controller()->comm_dash_page("comm_customers") .'&action=edit&id=' . $val->user_id . '" class="c-cursor c-link-color c-show-detail-customer" data-bs-container="body" data-bs-toggle="popover" data-bs-placement="top" data-bs-trigger="hover" data-bs-content="'. __( 'Edit customer', 'commercioo' ) .'">#'. $customer_id .'</a>' :
                    '<span class="c-show-detail-customer" data-bs-container="body">#'. $customer_id .'</span>',
                    '<span class="c-cursor c-link-color c-show-detail-customer" data-bs-container="body" data-bs-toggle="modal" data-bs-placement="top" data-bs-trigger="hover" data-bs-content="'. __( 'View customer detail', 'commercioo' ) .'" data-bs-target="#modaldetailcustomer" data-id="' . $val->user_id . '" data-customer="' . $customer_id . '" data-original-title="" title="">' . $name . '</span>',
                    $val->email,
                    $val->phone,
                    count($orders),
                    count($sales),
                    \Commercioo\Helper::formatted_currency($total_spent),
                    ($orders) ? date('M j, Y',strtotime(end($orders)->post_date)).' @ '.date('H:i:s',strtotime(end($orders)->post_date)) : "-",
                    date('M j, Y',strtotime($val->date_registered)).' @ '.date('H:i:s',strtotime($val->date_registered))
                ];
                $i++;
            }
        }
        $output['recordsFiltered'] = count($all);
        $output['recordsTotal'] = count($all);
        return $output;
    }

    public function comm_update_customer(){
        global $wp;

        if ( ! isset( $_REQUEST['comm-action-nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['comm-action-nonce'] ) ), 'comm_update_customer' ) ) {
            return;
        }

        if ( empty( $_POST['action'] ) || 'comm_update_customer' !== sanitize_title($_POST['action']) ) {
            return;
        }

        $user_id = absint($_POST['user_id']);

        if ( $user_id <= 0 ) {
            return;
        }

        $customer = new \Commercioo\Models\Customer( $user_id );

        if ( ! $customer ) {
            return;
        }
        $load_address = isset( $wp->query_vars['edit-address'] ) ? sanitize_title( $wp->query_vars['edit-address'] ) : 'billing';
        $fields   = $customer->get_fields();
        //$required = array( 'first_name', 'last_name', 'country', 'state', 'city', 'street_address', 'zip', 'phone', 'email' );
        $billing  = array();
        $shipping  = array();

        foreach ( $fields as $field ) {

            // Get Value.
            $billing_value = sanitize_text_field(isset( $_POST['billing_address'][ 'billing_'.$field ] ) ? wp_unslash( $_POST['billing_address'][ 'billing_'.$field ] ) : '');
            $shipping_value = sanitize_text_field(isset( $_POST['shipping_address'][ 'shipping_'.$field ] ) ? wp_unslash( $_POST['shipping_address'][ 'shipping_'.$field ] ) : '');

            // Validation: Required fields  .
            // if ( in_array( $field, $required ) && empty( $billing_value ) ) {
            //     comm_add_notice( sprintf( __( '%s is a required field.', 'commercioo' ), $field ), 'error' );
            // }

            if ( ! empty( $billing_value ) ) {
                switch ( $field ) {
                    case 'first_name':
                        if ( strlen( trim( preg_replace( '/[\s\-A-Za-z0-9]/', '', $billing_value ) ) ) > 0 ) {
                            comm_add_notice(__('Please enter the first_name.', 'commercioo'), 'error');
                        }
                        break;
                    case 'phone':
                        if ( 0 < strlen( trim( preg_replace( '/[\s\#0-9_\-\+\/\(\)\.]/', '', $billing_value ) ) ) ) {
                            comm_add_notice(sprintf(__('%s is not a valid phone number.', 'commercioo'), $billing_value), 'error');
                        }
                        break;
                    case 'email':
                        $billing_value = strtolower( $billing_value );

                        if ( ! is_email( $billing_value ) ) {
                            /* translators: %s: Email address. */
                            comm_add_notice(sprintf(__('%s is not a valid email address.', 'woocommerce'), $billing_value), 'error');
                        }
                        break;
                }
            }
            $billing[ $field ] = $billing_value;
            $shipping[ $field ] = $shipping_value;
        }

        /**
         * Hook: commercioo_after_save_address_validation.
         *
         * Allow developers to add custom validation logic and throw an error to prevent save.
         *
         * @param int         $user_id User ID being saved.
         * @param string      $load_address Type of address e.g. billing or shipping.
         * @param array       $address The address fields.
         * @param WC_Customer $customer The customer object being saved. @since 3.6.0
         */
        //do_action( 'commercioo_after_save_address_validation', $user_id, $load_address, $address, $customer );

        // if ( 0 < comm_notice_count( 'error' ) ) {
        //     var_dump(comm_print_notices());
        //     return;
        // }

        foreach ( $billing as $field => $value ) {
            update_user_meta( $user_id, 'comm_billing_' . $field, $value );
        }

        foreach ( $shipping as $field => $value ) {
            update_user_meta( $user_id, 'comm_shipping_' . $field, $value );
        }

        comm_add_notice(__( 'Address changed successfully.', 'commercioo' ));
        $customer_id = $customer->set_customer(sanitize_text_field($_POST['billing_address']));
        do_action( 'commercioo_customer_save_address', $user_id, $load_address );
        wp_safe_redirect(admin_url('admin.php?page=comm_customers&msg=1'));
        exit;
    }
}