<?php
namespace commercioo\admin;
Class Comm_Customer
{
    // instance
    private static $instance;
    private $api;

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
        $this->api = \Comm_API::get_instance();
        
    }

    public function register_rest_fields()
    {
        self::commercioo_custom_rest_api();
    }

    private function commercioo_custom_rest_api()
    {
        register_rest_route('commercioo/v1', '/comm_detail_customer/(?P<id>[a-zA-Z0-9-]+)', array(
            'methods' => \WP_REST_Server::READABLE,
            'callback' => array($this, 'comm_detail_customer'),
            'args' => array(
                'id' => array(
                    'validate_callback' => function ($param, $request, $key) {
                        return $this->validate_customer_id($param, $request);
                    }
                ),
            ),
            'permission_callback' => function () {
                return current_user_can('publish_posts');
            }
        ));

         // sync customer
        register_rest_route( 'commercioo/v1', '/sync_customer', array(
            'methods'  => \WP_REST_Server::READABLE,
            'callback' => array( $this, 'sync_customer' ),
            'permission_callback' => function () {
                return current_user_can( 'publish_posts' );
            }
        ) );

        // Delete Customer
        register_rest_route('commercioo/v1', '/comm_delete_customer/', array(
            'methods' => \WP_REST_Server::CREATABLE,
            'callback' => array($this, 'comm_delete_customer'),
            'args' => array(
                'id' => array(
                    'validate_callback' => function ($param, $request, $key) {
                        if(is_array($param)){
                            foreach ($param as $user_id) {
                                return $this->validate_customer_id($user_id,$request); // Fix PHP Fatal error:  Uncaught Error: Call to a member function get_params(), missing 1 argument $request
                            }
                        }else{
                            return $this->validate_customer_id($param,$request); // Fix PHP Fatal error:  Uncaught Error: Call to a member function get_params(), missing 1 argument $request
                        }
                    }
                ),
            ),
            'permission_callback' => function () {
                return current_user_can('publish_posts');
            }
        ));

    }
    public function validate_customer_id($user_id, $request = null){
        $params = $request->get_params();
        $is_success = true;
        if(!is_numeric($user_id)){
            return false;
        }
        if(!empty($user_id)){
            $customer = new \Commercioo\Models\Customer($user_id);
            $data = $customer->get_single_customer();
            if(!$data){
                $data = $customer->get_customer_by_id($user_id);
            }
        }else{
            $customer = new \Commercioo\Models\Customer($user_id);
            $data = $customer->get_customer_by_id($params['customer_id']);
        }

        if (!$data) {
            return new \WP_Error('comm_error', "could not find original post: " .
                $user_id, array('status' =>
                404));
        }
        return $is_success;
    }
    public function comm_detail_customer($request) {
        global $comm_options;
        
        $params   = $request->get_params();
        $user_id = $params['id'];
        $customer_id = $params['customer_id'];
        $customer = new \Commercioo\Models\Customer($user_id,$customer_id );
        if($customer_id){
            $detail_customer = $customer->get_customer_by_id( $customer_id );
        }else{
            $detail_customer = $customer->get_single_customer( );
        }
        $sales =  $customer->get_customer_orders('comm_completed','ID',null,null);
        $orders = $customer->get_customer_orders(["comm_pending", "comm_processing", "comm_completed", "comm_refunded","comm_abandoned"],'ID',null,null);
        $total_spent = 0 ;
        if($sales){
            foreach ($sales as $item) {
                $order = new \Commercioo\Models\Order($item->ID);
                $total_spent += $order->get_total();
            }
        }
        if ( $detail_customer ) {
            $status_label = sprintf( 
                "%s #%s",
                __( 'Customer ID', 'commercioo' ), 
                esc_html( $detail_customer->customer_id )
            );
            $user_data = get_user_by('id', $detail_customer->user_id);
            $user_name = '';
            $user_email = '';
            $user_phone = '';
            $user_registered = '';
            $user_id = $detail_customer->user_id;

            if ($user_data) {
                $user_id = $user_data->ID;
                $user_name = $user_data->data->user_login;
                $user_email = $user_data->data->user_email;
                $user_registered = $user_data->data->user_registered;
                $billing_phone = get_user_meta($user_id,'comm_billing_phone',true);
                $shipping_phone = get_user_meta($user_id,'comm_shipping_phone',true);
                $user_phone = isset($shipping_phone) && !empty($shipping_phone) ? $shipping_phone : $billing_phone;
            }
            // generate html_content
            ob_start();
            ?>
            <div class="row w-100 mx-0">
                <div class="col-md-12 c-followup-wrap">
                    <table class="detail_customer">
                        <tbody>
                            <tr>
                                <td>
                                    <?php esc_html_e( 'User Detail', 'commercioo');?>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <?php esc_html_e( 'User ID', 'commercioo');?>:
                                </td>
                                <td>
                                    <?php echo esc_attr($user_id)?>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <?php esc_html_e( 'Name', 'commercioo');?>:
                                </td>
                                <td>
                                    <?php echo esc_attr($user_name)?>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <?php esc_html_e( 'Mobile Phone', 'commercioo');?>:
                                </td>
                                <td>
                                    <?php echo esc_attr($user_phone);?>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <?php esc_html_e( 'Email', 'commercioo');?>:
                                </td>
                                <td>
                                    <?php echo esc_attr($user_email);?>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <?php esc_html_e( 'Registration Date', 'commercioo');?>:
                                </td>
                                <td>
                                    <?php echo esc_attr($user_registered);?>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="row w-100 mx-0 set-font-size-14">
                <div class="col-md-12 c-followup-wrap">
                    <table class="detail_customer">
                        <tbody>
                        <tr>
                            <td>
                                <?php esc_html_e( 'Customer Detail', 'commercioo');?>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <?php esc_html_e( 'Name', 'commercioo');?>:
                            </td>
                            <td>
                                <?php echo esc_attr(!empty( $detail_customer->name ) && $detail_customer->name !=' ' ? $detail_customer->name : '-');?>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <?php esc_html_e( 'Address', 'commercioo');?>:
                            </td>
                            <td>
                                <?php echo esc_html($detail_customer->address);?>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <?php esc_html_e( 'Mobile Phone', 'commercioo');?>:
                            </td>
                            <td>
                                <?php echo esc_html($detail_customer->phone);?>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <?php esc_html_e( 'Email', 'commercioo');?>:
                            </td>
                            <td>
                                <?php echo esc_html($detail_customer->email);?>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="row w-100 mx-0 set-font-size-14">
                <div class="col-md-12 c-followup-wrap">
                    <table class="detail_customer">
                        <tbody>
                            <tr>
                                <td>
                                    <?php esc_html_e( 'Total Spent', 'commercioo');?>:
                                </td>
                                <td>
                                    <?php echo esc_html(\Commercioo\Helper::formatted_currency($total_spent));?>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <?php esc_html_e( 'Last Order', 'commercioo');?>:
                                </td>
                                <td>
                                    <?php echo esc_html(($orders) ? date('M j, Y',strtotime(end($orders)->post_date)).' @ '.date('H:i:s',strtotime(end($orders)->post_date)) : "-");?>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="row w-100 mx-0">
                <div class="col-12 c-list-customer c-filter-customer-wrap">
                    <div class="btn-group c-list-orders c-general-orders group-grid-wrap" role="group">
                         <span class="btn c-btn-filter-list group-grid-wrap-item comm-filter active pl-0" data-status="any"><?php
                             _e("All Orders", "commercioo_order"); ?>
                             <span class="comm_count_all">(<?php echo esc_attr(count( $orders )) ?>)</span></span>
                        <span class="btn c-btn-filter-list c-set-padding-left-1 group-grid-wrap-item comm-filter"
                              data-status="complete"><?php
                            _e("Completed", "commercioo_order"); ?>
                            <span class="comm_count_complete">(<?php echo esc_attr(count( $sales )) ?>)</span></span>
                    </div>
                </div>
            </div>
            <?php
            $data = [
                'orderby'=>'id',
                'order'=>'DESC',
                'post_type'=>'comm_order',
                'numberposts'=>10,
                'post_status'=>json_encode(["comm_pending", "comm_processing", "comm_completed"]),
            ];
            ?>
            <table id="comm-recent-orders" class="table-responsive table c-list-table-data w-100" data-tbl="orders" data-table='<?php echo esc_html(json_encode($data));?>'>
                <thead class="c-table-top-products-head">
                <tr>
                    <?php
                    $columns = apply_filters('comm_admin_recent_orders_data_columns', array(
                        'Order ID',
                        'Date',
                        'Product',
                        'Total',
                        'Status',
                    ));
                    foreach ($columns as $column) {
                        echo wp_kses_post('<th class="th-lg">' . esc_html($column) . '</th>');
                    }
                    ?>
                </tr>
                </thead>
                <tbody class="c-table-recent-orders-body">
                    <?php foreach ($orders as $item) {
                        $order = new \Commercioo\Models\Order($item->ID);
                        $status = $item->post_status;
                        $detail_order = $order->get_order_items();
                        $total_order = count($detail_order);
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
                        }?>
                        <tr role="row w-100 mx-0" class="odd">
                            <td><a href='<?php echo esc_url(comm_controller()->comm_dash_page("comm_order"));?>&action=edit&id=<?php echo esc_attr($item->ID);?>' class='c-cursor' data-bs-container='body'
                 data-bs-toggle='popover' data-bs-placement='top' data-bs-trigger='hover' data-bs-content='Edit order' data-id='<?php echo esc_attr($item->ID);?>' target='_blank'>#<?php echo esc_attr($item->ID);?></a></td>
                            <td><?php echo esc_html(date('M j, Y',strtotime($item->post_date)).' @ '.date('H:i:s',strtotime($item->post_date)));?></td>
                            <td><?php echo esc_attr((strlen($detail_order[0]->item_name) > 35) ? substr($detail_order[0]->item_name, 0, 35).'...' : $detail_order[0]->item_name);?> <?php echo esc_html(($total_order > 1) ? '(+'. ($total_order - 1) .')' : '') ;?></td>
                            <td><?php echo esc_html(\Commercioo\Helper::formatted_currency($order->get_total()));?></td>
                            <td><span class="badge c-ar-badge-rounded <?php echo esc_html($badge_status);?>"><?php echo esc_attr(ucfirst($status));?></span></td>
                        </tr>
                    <?php };?>
                </tbody>
            </table>

            <?php
            $html_content = ob_get_clean();
            
            // send response
            wp_send_json_success( array(
                'status_label'         => $status_label,
                'detail_order_content' => $html_content,
            ), 200 );
        }
        else {
            wp_send_json_error( array( 'message' => __( 'Invalid order data!', 'commercioo' ) ), 400 );
        }
    }

    public function get_unsync_order(){
        $results = array();
        $meta_query = array(
            array( 
                'key' => '_customer_id', 
                'compare' => 'NOT EXISTS'
            )
        );
        $result = comm_controller()->comm_get_result_data(["comm_pending", "comm_processing", "comm_completed", "comm_refunded"], "comm_order", 'ID', null, null, $meta_query);
        return $result;
    }

    public function sync_customer(){
        $orders = $this->get_unsync_order();

        $customer_ids = array();
        foreach ( $orders as $item ) {
            $order = new \Commercioo\Models\Order( $item->ID );
            $user_id = $order->get_user_id();
            $billing_address = $order->get_billing_address();
            $customer = new \Commercioo\Models\Customer( $user_id );
            $customer_id = $customer->set_customer( $billing_address );
            $customer_ids[] = $customer_id;
            $order->update_customer_id($customer_id);
        }

        $results = array(
            'customers' => $customer_ids,
            'total' => count($customer_ids),
        );

        return rest_ensure_response( $results );
    }

    public function comm_delete_customer($request){
        $params   = $request->get_params();
        $customer_id = $params['id'];
        
        if(is_array($customer_id)){
            foreach ($customer_id as $item) {
                $customer = new \Commercioo\Models\Customer(null, $item );
                $customer->delete_customer();
            }
        }else{
            $customer = new \Commercioo\Models\Customer( null,$customer_id );
            $customer->delete_customer();
        }
        $results = array(
            'customer' => $customer_id
        );

        return rest_ensure_response( $results );
    }

}

//Comm_Order::getInstance();