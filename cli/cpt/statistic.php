<?php
namespace commercioo\admin;
use DatePeriod;
use DateTime;
use DateInterval;

Class Comm_Statistic {
    // instance
    private static $instance;
    private $setting_prefix = 'comm_setting';

    // getInstance
    public static function get_instance() {
        if (!isset(self::$instance)) {
            self::$instance = new self;
        }

        return self::$instance;
    }

    // __construct
    public function __construct() {
		// silent is a golden
    }

    public function endpoint_register() {
        // read all settings
        register_rest_route('commercioo/v1', '/get_statistic_by_date', array(
            'methods' => \WP_REST_Server::CREATABLE,
            'callback' => array($this, 'get_statistic_by_date'),
            'args' => array(
                'start_date' => ['required' => true],
                'end_date' => ['required' => false],
            ),
            'permission_callback' => function () {
                return current_user_can('publish_posts');
            }
        ));
        register_rest_route('commercioo/v1', '/get_chart_by_date', array(
            'methods' => \WP_REST_Server::CREATABLE,
            'callback' => array($this, 'get_chart_by_date'),
            'args' => array(
                'start_date' => ['required' => true],
                'end_date' => ['required' => false],
            ),
            'permission_callback' => function () {
                return current_user_can('publish_posts');
            }
        ));
        
        // get page statistics data
        register_rest_route( 'commercioo/v1', '/get_page_statistics_data', array(
            'methods'  => \WP_REST_Server::CREATABLE,
            'callback' => array( $this, 'get_page_statistics_data' ),
            'args'     => array(
                'start_date' => array( 'required' => true ),
                'end_date'   => array( 'required' => true ),
                'product_id' => array( 'required' => false ),
            ),
            'permission_callback' => function () {
                return current_user_can( 'publish_posts' );
            }
        ) );
        
        // get product rank by: orders, sales, customers, revenue
        register_rest_route( 'commercioo/v1', '/get_statistics_product_rank', array(
            'methods'  => \WP_REST_Server::CREATABLE,
            'callback' => array( $this, 'get_statistics_product_rank' ),
            'args'     => array(
                'start_date' => array( 'required' => true ),
                'end_date'   => array( 'required' => true ),
                'product_id' => array( 'required' => false ),
            ),
            'permission_callback' => function () {
                return current_user_can( 'publish_posts' );
            }
        ) );
    }

    /**
     * @param $request
     * get_statistic_by_date
     * @return \WP_Error|\WP_REST_Response
     */
    public function get_statistic_by_date( $request ) {
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
        
		if ($results) {
            foreach ($results as $orders) {
                $order = new \Commercioo\Models\Order($orders->ID);
                $user_id = $order->get_customer()->get_user()->ID;
                $order_ids [] = $orders->ID;
                $order_user_id [] = $user_id;
            }
            $order_id = implode(",", $order_ids);
        }

        $order_items = $wpdb->get_results($wpdb->prepare('SELECT * FROM `'.$wpdb->prefix.'commercioo_order_items` WHERE order_id in (' . $order_id . ')'));

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

    /**
     * @param $request
     * get_chart_by_date
     * @return \WP_Error|\WP_REST_Response
     */
    public function get_chart_by_date($request) {
        global $wpdb;

        $params = $request->get_params();
        $startDate = null;
        $endDate = null;
        $product_id = (!empty($params['product_id'])) ? $params['product_id'] : null;

        if ($params['start_date']) {
            $startDate = $this->get_valid_date($params['start_date']);
        }

        if ($params['end_date']) {
            $endDate = $this->get_valid_date($params['end_date']);
        }
        if (!$endDate) {
            $endDate = $startDate;
        }
        $product_revenue = 0;
        if(!empty($product_id)){
            $results = $wpdb->get_results("SELECT wp.* FROM ".$wpdb->prefix."posts wp INNER JOIN ".$wpdb->prefix."commercioo_order_items wc ON wc.order_id = wp.ID WHERE DATE(wp.post_date) >= '".date("Y-m-d", strtotime($startDate))."' and DATE(wp.post_date) <= '".date("Y-m-d", strtotime($endDate))."' and wp.post_status = 'comm_completed' and wc.product_id = ".$product_id." group by wp.ID",OBJECT);
        }else{
            $results = $wpdb->get_results("SELECT wp.* FROM ".$wpdb->prefix."posts wp INNER JOIN ".$wpdb->prefix."commercioo_order_items wc ON wc.order_id = wp.ID WHERE DATE(wp.post_date) >= '".date("Y-m-d", strtotime($startDate))."' and DATE(wp.post_date) <= '".date("Y-m-d", strtotime($endDate))."' and wp.post_status = 'comm_completed' group by wp.ID",OBJECT);

        }

        $order_user_id = [];
        $order_returning_user_id = [];
        $order_repeat_user_id = [];
        $sales_snapshot = [];
        $datediff = strtotime($endDate) - strtotime($startDate);
        $format = 'Y-m-d';

        if ($results) {
            foreach ($results as $orders) {
                $order = new \Commercioo\Models\Order($orders->ID);
                $user_id = $order->get_customer()->get_user()->ID;
                $order_user_id [] = $user_id;
                if(!in_array($user_id, $order_returning_user_id)){
                    $order_returning_user_id[] = $user_id;
                }else{
                    if(!in_array($user_id, $order_repeat_user_id)){
                        $order_repeat_user_id[] = $user_id;
                    }
                }
                $date = strtotime(date($format,strtotime($orders->post_date)))*1000;
                $index = array_search($date, array_column($sales_snapshot, 't'));
                if($index !== false){
                    $sales_snapshot[$index]->y = $sales_snapshot[$index]->y+1;
                }else{
                    $sales_snapshot[] = (object) ['t' => $date,'y'=> 1 ];
                }
            }
        }

        //Generate zero value for empty date
        $period = new DatePeriod(
             new DateTime(date($format, strtotime($startDate))),
             new DateInterval('P1D'),
             new DateTime(date($format, strtotime("+1 day",strtotime($endDate))))
        );

        foreach ($period as $key => $value) {
            $date = strtotime($value->format('Y-m-d'))*1000;
            $index = array_search($date, array_column($sales_snapshot, 't'));
            if($index === false){
                $sales_snapshot[] = (object) ['t' => $date,'y'=> 0 ];
            }
        }

        usort($sales_snapshot, function($a, $b) {return strcmp($a->t, $b->t);});

        $result['customer'] = count($order_returning_user_id);
        $result['returning_customer'] = count($order_repeat_user_id);
        $result['pending'] = $this->get_pending_number( $startDate, $endDate, $product_id );
        $result['processing'] = $this->get_processing_number( $startDate, $endDate, $product_id );
        $result['completed'] = $this->get_sales_number( $startDate, $endDate, $product_id );
        $result['refund'] = $this->get_refund_number( $startDate, $endDate, $product_id );
        $result['sales'] = $this->get_sales_number( $startDate, $endDate );
        $result['product_sales'] = $this->get_sales_number( $startDate, $endDate, $product_id );
        $result['sales_snapshot'] = $sales_snapshot;


        return rest_ensure_response($result);
    }

    /**
     * @param $request
     * get_page_statistics_data
     * @return \WP_Error|\WP_REST_Response
     */
    public function get_page_statistics_data( $request ) {
        $params     = $request->get_params();
        $start_date = sanitize_text_field( $params['start_date'] );
        $end_date   = sanitize_text_field( $params['end_date'] );
        $product_id = intval( $params['product_id'] );
        $product_id = $product_id > 0 ? $product_id : null;
        $results    = $this->get_page_statistics_data_by_date( $start_date, $end_date, $product_id );
        
        return rest_ensure_response( $results );
    }
    
    // currently the default date query is the last 30 days
    public function get_page_statistics_data_by_date( $start_date = null, $end_date = null, $product_id = null ) {
        $start_date = $this->get_valid_date( $start_date, 'today - 29 days' );
        $end_date   = $this->get_valid_date( $end_date );
        
        // checkout views 
        $page_views     = new \Commercioo\Page_Views;
        $page_key       = $product_id ? "checkout%-{$product_id}-%" : 'checkout%';
        $checkout_views = $page_views->get_recorded( $page_key, $start_date, $end_date, true );

        // orders & sales
        $orders_number = $this->get_orders_number( $start_date, $end_date, $product_id );
        $sales_number  = $this->get_sales_number( $start_date, $end_date, $product_id );

        // percentage of orders, sales, and closing rate 
        $orders_percentage = $checkout_views > 0 ? ( $orders_number / $checkout_views * 100 ) : 0;
        $sales_percentage  = $checkout_views > 0 ? ( $sales_number / $checkout_views * 100 ) : 0;
        $closing_rate      = $orders_number > 0 ? ( $sales_number / $orders_number * 100 ) : 0;

        // customer number and ASPU
        $customers_number = $this->get_sales_customers_number( $start_date, $end_date, $product_id );
        $aspu             = $customers_number > 0 ? ( $sales_number / $customers_number ) : 0;

        // total revenue & arpu
        $revenue = $this->get_total_revenue( $start_date, $end_date, $product_id );
        $arpu    = $customers_number > 0 ? ( $revenue / $customers_number ) : 0;

        // refund number and refund amount
        $refund_number = $this->get_refund_number( $start_date, $end_date, $product_id );
        $refund_amount = $this->get_refund_amount( $start_date, $end_date, $product_id );

        // refund number and refund amount percentage
        $refund_number_percentage = $sales_number > 0 ? ( $refund_number / $sales_number * 100 ) : 0;
        $refund_amount_percentage = $revenue > 0 ? ( $refund_amount / $revenue * 100 ) : 0;

        // product rank by sales
        $product_rank_by_sales = $this->get_product_rank_by_sales( $start_date, $end_date, $product_id, $sales_number );

        return array(
            'start_date'               => $start_date,
            'end_date'                 => $end_date,
            'product_id'               => $product_id,
            'checkout_views'           => $checkout_views,
            'orders_number'            => $orders_number,
            'sales_number'             => $sales_number,
            'orders_percentage'        => $this->to_decimal( $orders_percentage ) . '%',
            'sales_percentage'         => $this->to_decimal( $sales_percentage ) . '%',
            'closing_rate'             => $this->to_decimal( $closing_rate ) . '%',
            'customers_number'         => $customers_number,
            'aspu'                     => $this->to_decimal( $aspu ),
            'revenue'                  => $this->to_decimal( $revenue ),            
            'arpu'                     => $this->to_decimal( $arpu ),           
            'refund_number'            => $refund_number,           
            'refund_amount'            => $this->to_decimal( $refund_amount ),          
            'refund_number_percentage' => $this->to_decimal( $refund_number_percentage ) . '%',     
            'refund_amount_percentage' => $this->to_decimal( $refund_amount_percentage ) . '%', 
            'product_rank_by_sales'    => is_numeric( $product_rank_by_sales ) ? '#' . $product_rank_by_sales : $product_rank_by_sales,
            'product_rank_label'       => __( 'PRODUCT RANK BY SALES', 'commercioo' ),
        );
    }

    /**
     * @param $request
     * get_statistics_product_rank
     * @return \WP_Error|\WP_REST_Response
     */
	public function get_statistics_product_rank( $request ) {
		$params 	= $request->get_params();
		$start_date = sanitize_text_field( $params['start_date'] );
		$end_date   = sanitize_text_field( $params['end_date'] );
		$product_id = intval( $params['product_id'] );
		$product_id = $product_id > 0 ? $product_id : null;
		$type   	= sanitize_text_field( $params['type'] );

		// ensure the input dates
		$start_date = $this->get_valid_date( $start_date, 'today - 29 days' );
		$end_date   = $this->get_valid_date( $end_date );

		// default label and value
		$value = "N/A";	
		$label = "N/A";	
		
		// set output by type
		switch ( $type ) {
			case 'sales':
				$sales_number  = $this->get_sales_number( $start_date, $end_date, $product_id );
				$value 	   	   = $this->get_product_rank_by_sales( $start_date, $end_date, $product_id, $sales_number );
				$label		   = __( 'PRODUCT RANK BY SALES', 'commercioo' );
				break;
			
			case 'orders':
				if ( class_exists( 'Commercioo_Pro\Statistics' ) ) {
					$pro_statistics = new \Commercioo_Pro\Statistics;
					$orders_number  = $this->get_orders_number( $start_date, $end_date, $product_id );
					$value 	    	= $pro_statistics->get_product_rank_by_orders( $start_date, $end_date, $product_id, $orders_number );
					$label		   	= __( 'PRODUCT RANK BY ORDERS', 'commercioo' );
				}				
				break;
			
			case 'customers':
				if ( class_exists( 'Commercioo_Pro\Statistics' ) ) {
					$pro_statistics    = new \Commercioo_Pro\Statistics;
					$customers_number  = $this->get_sales_customers_number( $start_date, $end_date, $product_id );
					$value 	    	   = $pro_statistics->get_product_rank_by_customers( $start_date, $end_date, $product_id, $customers_number );
					$label		   	   = __( 'PRODUCT RANK BY CUSTOMERS', 'commercioo' );
				}				
				break;

			case 'revenue':
				if ( class_exists( 'Commercioo_Pro\Statistics' ) ) {
					$pro_statistics = new \Commercioo_Pro\Statistics;
					$revenue  		= $this->get_total_revenue( $start_date, $end_date, $product_id );
					$value 	    	= $pro_statistics->get_product_rank_by_revenue( $start_date, $end_date, $product_id, $revenue );
					$label		   	= __( 'PRODUCT RANK BY REVENUE', 'commercioo' );
				}				
				break;

			default:
				// silent is golden
				break;
		}
			
		// response data
		$results = array(
			'value'	=> is_numeric( $value ) ? '#' . $value : $value,
			'label' => $label,
		);

		// response the request
		return rest_ensure_response( $results );
	}

	// decimal with 2 digit after comma
	private function to_decimal( $number ) {
		// not numeric
		if ( ! is_numeric( $number ) ) {
			return 0;
		}

		// not a number (NAN)
		if ( is_nan( $number ) ) {
			return 0;
		}

		// float or not
		$digits_after_comma = is_float( $number ) ? 2 : 0;

		return number_format( $number, $digits_after_comma, ',', '.' );
	}

	/**
	 * Check whether the given date is valid or not
	 * then return the formatted valid one
	 */
	private function get_valid_date( $date, $default = 'today' ) {
		if ( ( $timestamp = strtotime( $date ) ) !== false ) {
			return date( 'Y-m-d', $timestamp );
		} else {
			return date( 'Y-m-d', strtotime( $default ) );
		}
	}
	
	private function get_orders_number( $start_date, $end_date, $product_id = null ) {
		$status = array( 'comm_pending', 'comm_processing', 'comm_completed', 'comm_refunded' );
		return $this->get_order_by_date_product_and_status( $status, $start_date, $end_date, $product_id );
	}

    private function get_pending_number( $start_date, $end_date, $product_id = null ) {
        $status = array( 'comm_pending' );
        return $this->get_order_by_date_product_and_status( $status, $start_date, $end_date, $product_id );
    }

    private function get_processing_number( $start_date, $end_date, $product_id = null ) {
        $status = array( 'comm_processing' );
        return $this->get_order_by_date_product_and_status( $status, $start_date, $end_date, $product_id );
    }

	private function get_sales_number( $start_date, $end_date, $product_id = null ) {
		$status = array( 'comm_completed' );
		return $this->get_order_by_date_product_and_status( $status, $start_date, $end_date, $product_id );
	}

	private function get_refund_number( $start_date, $end_date, $product_id = null ) {
		$status = array( 'comm_refunded' );
		return $this->get_order_by_date_product_and_status( $status, $start_date, $end_date, $product_id );
	}

	private function get_order_by_date_product_and_status( $status, $start_date, $end_date, $product_id = null ) {
		global $wpdb;
		$order_status = '';

		// order statuses
		foreach ( $status as $key => $value ) {
			$order_status .= $wpdb->prepare( " OR {$wpdb->posts}.post_status = %s", $value );
		}

		// remove the first OR
		$order_status = ltrim( $order_status, " OR " );

		// main query
		if ( intval( $product_id ) > 0) {
			$query = $wpdb->prepare( "SELECT SQL_CALC_FOUND_ROWS DISTINCT {$wpdb->posts}.ID
				FROM {$wpdb->posts} 
				JOIN {$wpdb->prefix}commercioo_order_items
					ON {$wpdb->posts}.ID={$wpdb->prefix}commercioo_order_items.order_id
				WHERE 1=1
					AND ( ( {$wpdb->posts}.post_date >= %s AND {$wpdb->posts}.post_date <= %s ) )
					AND {$wpdb->posts}.post_type = %s
					AND ( ( $order_status ) )
					AND {$wpdb->prefix}commercioo_order_items.product_id = %d				
				LIMIT 0, 10",
				"$start_date 00:00:00", "$end_date 23:59:59", 'comm_order', $product_id 
			);
		} else {
			$query = $wpdb->prepare( "SELECT SQL_CALC_FOUND_ROWS DISTINCT {$wpdb->posts}.ID
				FROM {$wpdb->posts}
				WHERE 1=1
					AND ( ( {$wpdb->posts}.post_date >= %s AND {$wpdb->posts}.post_date <= %s ) )
					AND {$wpdb->posts}.post_type = %s
					AND ( ( $order_status ) )
				LIMIT 0, 10",
				"$start_date 00:00:00", "$end_date 23:59:59", 'comm_order'
			);
		}

		// run the main query
		$col_results = $wpdb->get_col( $query );
		
		// get the total found rows
		$found_rows = $wpdb->get_var( "SELECT FOUND_ROWS()" );
		
		// return total number
		return intval( $found_rows );
	}

	private function get_sales_customers_number( $start_date, $end_date, $product_id = null ) {
		global $wpdb;

		if ( intval( $product_id ) > 0) {
			$query = $wpdb->prepare( "SELECT SQL_CALC_FOUND_ROWS DISTINCT {$wpdb->postmeta}.meta_value
				FROM {$wpdb->posts}
				JOIN {$wpdb->prefix}commercioo_order_items
					ON {$wpdb->posts}.ID={$wpdb->prefix}commercioo_order_items.order_id 
				JOIN {$wpdb->postmeta}
					ON {$wpdb->posts}.ID={$wpdb->postmeta}.post_id
				WHERE 1=1
					AND ( ( {$wpdb->posts}.post_date >= %s AND {$wpdb->posts}.post_date <= %s ) )
					AND {$wpdb->posts}.post_type = %s
					AND {$wpdb->posts}.post_status = %s
					AND {$wpdb->prefix}commercioo_order_items.product_id = %d
					AND {$wpdb->postmeta}.meta_key = %s
				LIMIT 0, 10",
				"$start_date 00:00:00", "$end_date 23:59:59", 'comm_order', 'comm_completed', $product_id, '_user_id'
			);
		} else {
			$query = $wpdb->prepare( "SELECT SQL_CALC_FOUND_ROWS DISTINCT {$wpdb->postmeta}.meta_value
				FROM {$wpdb->posts}
				JOIN {$wpdb->postmeta}
					ON {$wpdb->posts}.ID={$wpdb->postmeta}.post_id
				WHERE 1=1
					AND ( ( {$wpdb->posts}.post_date >= %s AND {$wpdb->posts}.post_date <= %s ) )
					AND {$wpdb->posts}.post_type = %s
					AND {$wpdb->posts}.post_status = %s
					AND {$wpdb->postmeta}.meta_key = %s
				LIMIT 0, 10",
				"$start_date 00:00:00", "$end_date 23:59:59", 'comm_order', 'comm_completed', '_user_id'
			);
		}

		// run the main query
		$col_results = $wpdb->get_col( $query );
		
		// get the total found rows
		$found_rows = $wpdb->get_var( "SELECT FOUND_ROWS()" );
		
		// return total number
		return intval( $found_rows );
	}

	private function get_total_revenue( $start_date, $end_date, $product_id = null ) {
		$status = array( 'comm_completed' );
		return $this->get_total_product_price_by_order_status( $status, $start_date, $end_date, $product_id );
	}

	private function get_refund_amount( $start_date, $end_date, $product_id = null ) {
		$status = array( 'comm_refunded' );
		return $this->get_total_product_price_by_order_status( $status, $start_date, $end_date, $product_id );
	}

	private function get_total_product_price_by_order_status( $status, $start_date, $end_date, $product_id = null ) {
		global $wpdb;
		$order_status = '';

		// order statuses
		foreach ( $status as $key => $value ) {
			$order_status .= $wpdb->prepare( " OR {$wpdb->posts}.post_status = %s", $value );
		}

		// remove the first OR
		$order_status = ltrim( $order_status, " OR " );

		// main query
		if ( intval( $product_id ) > 0) {
			$query = $wpdb->prepare( "SELECT SUM( {$wpdb->prefix}commercioo_order_items.item_price * {$wpdb->prefix}commercioo_order_items.item_order_qty )
				FROM {$wpdb->posts}
				JOIN {$wpdb->prefix}commercioo_order_items
					ON {$wpdb->posts}.ID={$wpdb->prefix}commercioo_order_items.order_id 
				WHERE 1=1
					AND ( ( {$wpdb->posts}.post_date >= %s AND {$wpdb->posts}.post_date <= %s ) )
					AND {$wpdb->posts}.post_type = %s
					AND ( ( $order_status ) )
					AND {$wpdb->prefix}commercioo_order_items.product_id = %d",
				"$start_date 00:00:00", "$end_date 23:59:59", 'comm_order', $product_id
			);
		}
		else {
			$query = $wpdb->prepare( "SELECT SUM( {$wpdb->prefix}commercioo_order_items.item_price * {$wpdb->prefix}commercioo_order_items.item_order_qty )
				FROM {$wpdb->posts}
				JOIN {$wpdb->prefix}commercioo_order_items
					ON {$wpdb->posts}.ID={$wpdb->prefix}commercioo_order_items.order_id 
				WHERE 1=1
					AND ( ( {$wpdb->posts}.post_date >= %s AND {$wpdb->posts}.post_date <= %s ) )
					AND {$wpdb->posts}.post_type = %s
					AND ( ( $order_status ) )",
				"$start_date 00:00:00", "$end_date 23:59:59", 'comm_order'
			);
		}

		// run the main query
		$sum = $wpdb->get_var( $query );
		
		// return total number
		return intval( $sum );
	}

	private function get_product_rank_by_sales( $start_date, $end_date, $product_id = null, $sales_number = 0 ) {
		global $wpdb;

		/**
		 * Will return N/A if:
		 * - the sales number from previous calculation is zero or below
		 * - the provided product_id is zero or not integer
		 */
		if ( $sales_number <= 0 ) {
			return 'N/A';
		} elseif ( intval( $product_id ) <= 0 ) {
			return 'N/A';
		} else {
			// sales column
			$sales_column = "SUM({$wpdb->prefix}commercioo_order_items.item_order_qty) as sales";

			// where clause
			$where_clause = $wpdb->prepare( "1=1
				AND ( ( {$wpdb->posts}.post_date >= %s AND {$wpdb->posts}.post_date <= %s ) )
				AND {$wpdb->posts}.post_type = %s
				AND ( ( {$wpdb->posts}.post_status = %s ) )",
				"$start_date 00:00:00", "$end_date 23:59:59", 'comm_order', 'comm_completed'
			);

			// the main query
			$query = $wpdb->prepare( "SELECT $sales_column
				FROM {$wpdb->posts} 
				JOIN {$wpdb->prefix}commercioo_order_items
					ON {$wpdb->posts}.ID={$wpdb->prefix}commercioo_order_items.order_id
				WHERE $where_clause
				GROUP BY {$wpdb->prefix}commercioo_order_items.product_id
				HAVING sales > (
					SELECT $sales_column
					FROM {$wpdb->posts} 
					JOIN {$wpdb->prefix}commercioo_order_items
						ON {$wpdb->posts}.ID={$wpdb->prefix}commercioo_order_items.order_id
					WHERE $where_clause
						AND {$wpdb->prefix}commercioo_order_items.product_id = %d
				)", 
				$product_id 
			);
		}

		// run the main query
		$col_results = $wpdb->get_col( $query );
		
		// get the total found rows (+1 for rank)
		$rank = $wpdb->get_var( "SELECT FOUND_ROWS() + 1" );
		
		// return rank
		return intval( $rank );
	}
}