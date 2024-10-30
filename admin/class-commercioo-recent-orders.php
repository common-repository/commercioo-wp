<?php

namespace commercioo\admin;
Class Comm_Recent_Orders
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
    }

    public function comm_recent_orders(){
        $view_recent_orders = $this->comm_do_recent_orders($_GET);
        echo wp_json_encode($view_recent_orders);
        wp_die();
    }

    public function comm_do_recent_orders($args)
    {
        $output['data'] = [];
        $order_items = get_posts( $args );

        $i = 0;
        if ($order_items) {
            foreach ($order_items as $val) {
                $order_id = $val->ID;
                $_billing_address = get_post_meta($order_id, "_billing_address", true);
                $first_name = isset($_billing_address['billing_first_name']) ? $_billing_address['billing_first_name'] : '';
                $last_name = isset($_billing_address['billing_last_name']) ? $_billing_address['billing_last_name'] : '';
                $billing_email = isset($_billing_address['billing_email']) ? $_billing_address['billing_email'] : '';
                $customer_name = $first_name . " " . $last_name;

                $order = new \Commercioo\Models\Order($order_id);
                $order_items = $order->get_order_items();
                $get_total = esc_html(comm_money_format($order->get_total()));
                $count_prod = count($order_items);
                $product_name = $order_items[0]->item_name;
                $res_count_prod = $count_prod;
                if($count_prod>1){
                    $res_count_prod = $res_count_prod-1;
                }

                $status = $val->post_status;

                if ($status == "comm_pending") {
                    $status = "pending";
                    $badge_status = "pending";
                } elseif ($status == "comm_processing") {
                    $status = "processing";
                    $badge_status = "processing";
                } elseif ($status == "comm_completed") {
                    $status = "complete";
                    $badge_status = "completed";
                } elseif ($status == "comm_refunded") {
                    $status = "refund";
                    $badge_status = "refunded";
                }elseif ($status == "comm_abandoned") {
                    $status = "abandoned";
                    $badge_status = "abandoned";
                } else {
                    $status = "inactive";
                    $badge_status = "inactive-status";
                }
                $orders_id = "#".$order_id;

                if ($status == "complete") {
                    $post_title = $orders_id;
                }else{
                    $post_title = "<a href='".comm_controller()->comm_dash_page('comm_order',$order_id)."&action=edit' class='c-cursor 
                    comm_edit_order' data-bs-container='body'
                 data-bs-toggle='popover' data-bs-placement='top' data-bs-trigger='hover' data-bs-content='Edit order' target='_blank' data-id='$order_id'>";

                    $post_title .= $orders_id;
                    $post_title .= "</a>";
                }

                $status_badge ='<span class="badge c-ar-badge-rounded '.$badge_status.'">'.ucfirst($status)
                    .'</span>';

                $output['data'][$i] = [
                    $post_title,
                    $customer_name,
                    $billing_email,
                    $product_name ." (+".$res_count_prod.")",
                    $get_total,
                    $status_badge
                ];
                $i++;
            }
        }
        return $output;
    }
    /**
     * Register the stylesheets for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_styles()
    {
        if (function_exists("comm_controller")) {
            if (comm_controller()->is_comm_page()) {
                if (comm_controller()->is_comm_page(["admin_page_comm_dashboard", 'commercioo_page_comm_dashboard'])) {
                    wp_enqueue_style('commercioo-recent-orders', COMMERCIOO_URL . 'admin/css/commercioo-recent-orders.css', array(), COMMERCIOO_VERSION, 'all');
                }
            }
        }
    }
    /**
     * Enqueue style and scripts
     *
     * @param  string $suffix Current admin page.
     */
    public function enqueue_scripts($suffix)
    {
        wp_enqueue_script("jquery");
        if (function_exists("comm_controller")) {
            if (comm_controller()->is_comm_page()) {
                if (comm_controller()->is_comm_page(["admin_page_comm_dashboard", 'commercioo_page_comm_dashboard'])) {
                    wp_enqueue_script('commercioo-recent-orders', COMMERCIOO_URL . 'admin/js/commercioo-recent-orders.js', array('jquery'), COMMERCIOO_VERSION, true);
                }
            }
        }
    }
}