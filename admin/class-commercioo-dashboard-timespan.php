<?php
namespace commercioo\admin;
use DatePeriod;
use DateTime;
use DateInterval;
Class Comm_Dashboard_Timepan
{
    // instance
    private static $instance;
    private $setting_prefix = 'comm_setting';

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

    public function endpoint_register()
    {
        // read all settings
        register_rest_route('commercioo/v1', '/get_timespan_by_date', array(
            'methods' => \WP_REST_Server::CREATABLE,
            'callback' => array($this, 'get_timespan_by_date'),
            'args' => array(
                'start_date' => ['required' => true],
                'end_date' => ['required' => false],
            ),
            'permission_callback' => function () {
                return current_user_can('publish_posts');
            }
        ));
    }

    /**
     * @param $request
     * get_timespan_by_date
     * @return \WP_Error|\WP_REST_Response
     */
    public function get_timespan_by_date($request)
    {
        $params = $request->get_params();
        global $wpdb;
        $startDate = null;
        $endDate = null;

        if ($params['start_date']) {
            $startDate = strtolower($params['start_date']);
        }

        if ($params['end_date']) {
            $endDate = strtolower($params['end_date']);
        }
        if (!$endDate) {
            $endDate = $startDate;
        }

        $results = comm_controller()->comm_get_result_data('comm_completed', "comm_order", 'ID', $startDate, $endDate);
        $order_ids = [];
        $order_user_id = [];
        $order_id = '';
		$order_items = null;

        if ($results) {
            foreach ($results as $orders) {
                $order = new \Commercioo\Models\Order($orders->ID);
                $get_user = $order->get_customer();
                $user_id = ($get_user) ? $get_user->get_user()->ID : null;
                $order_ids [] = $orders->ID;
                $order_user_id [] = $user_id;
            }
            $order_id = implode(",", $order_ids);
        }
		
		if ( count( $order_ids ) > 0 ) {
			$order_items = $wpdb->get_results(
				$wpdb->prepare( "SELECT * FROM {$wpdb->prefix}commercioo_order_items WHERE order_id in (%s)", $order_id )
			);
		}

        $revenue = 0;

        if ($order_items) {
            foreach ($order_items as $gross_val) {
                $revenue += $gross_val->item_price * $gross_val->item_order_qty;
            }
        }

        $total_order = count(comm_controller()->comm_get_result_data(["comm_pending", "comm_processing", "comm_completed", "comm_refunded"], "comm_order", 'ID', $startDate, $endDate));
        $sales = count($results);
        
		if (is_comm_pro()) {
            if (function_exists("comm_do_pro_dashboard_ARPU_statistic")) {
                $arpu = comm_do_pro_dashboard_ARPU_statistic($revenue, $order_user_id);
                $result['arpu'] = $arpu;
            }
        }

        $result['revenue'] = $revenue;
        $result['refund'] = count(comm_controller()->comm_get_result_data('comm_refunded', "comm_order", 'ID', $startDate, $endDate));
        $result['sales'] = $sales;
        $result['customer'] = count($order_user_id);
        $result['processing'] = count(comm_controller()->comm_get_result_data('comm_processing', "comm_order", 'ID', $startDate, $endDate));
        $result['pending'] = count(comm_controller()->comm_get_result_data('comm_pending', "comm_order", 'ID', $startDate, $endDate));
        $result['total_order'] = $total_order;

        return rest_ensure_response($result);
    }
}