<?php
namespace commercioo\admin;
Class Comm_Order
{
    // instance
    private static $instance;
    private $api;
    protected $grandTotal=0;
    protected $subTotal=0;
    protected $item_order = array();

    // getInstance
    public static function get_instance()
    {
        if (!isset(self::$instance)) {
            self::$instance = new self;
        }

        return self::$instance;
    }

    // custom post statuses
    public $order_statuses;

    // __construct
    public function __construct()
    {
        $this->api = \Comm_API::get_instance();
        // declare order_statuses
        $this->order_statuses = array(
            'comm_pending' => 'Pending',
            'comm_processing' => 'Processing',
            'comm_completed' => 'Completed',
            'comm_refunded' => 'Refunded',
            'comm_abandoned' => 'Abandoned',
            'comm_failed' => 'Failed',
        );
    }


    public function register_post_type_and_taxonomies()
    {
        // register post type
        $this->api->comm_register_post_type('comm_order', array(), false);
    }

    public function register_order_statuses()
    {
        // iterate to register post statuses
        foreach ($this->order_statuses as $name => $label) {
            register_post_status($name, array(
                'label' => _x($label, 'commercioo'),
                'public' => false,
            ));
        }
    }

    public function include_custom_order_statuses($query)
    {
        // is request publish only
        $post_statuses = $query->get('post_status');
        $is_publish = is_array($post_statuses) && count($post_statuses) == 1 && $post_statuses[0] == 'publish';

        // include post statuses on archive
        if ($query->is_post_type_archive('comm_order') && $is_publish) {
            $query->set('post_status', array_keys($this->order_statuses));
        }
    }

    public function rest_pre_insert($prepared_post, $request)
    {
        $params = $request->get_params();
        // set the self generated title on insert
        if (!isset($prepared_post->ID)) {
            $prepared_post->post_title = sprintf("comm_order_%s", uniqid());
        }


        /**
         * Update user's billing and shipping address
         * Create a new user if no logged-in user
         */
        if(!isset($params['id'])){
            $user = \commercioo\admin\Comm_Users::get_instance();
            $user->comm_insert_user_order( $request );
        }

        // get the request body then decode it
        $body = $request->get_body();
        $decoded_body = json_decode($body);

        // set post_excerpt with rest field order_notes
        if (isset($decoded_body->order_notes)) {
            $prepared_post->post_excerpt = sanitize_textarea_field($decoded_body->order_notes);
        }

        return $prepared_post;
    }



    public function rest_prepare($response, $post, $request)
    {
        // remove unnecessary response data
        unset(
            $response->data['guid'],
            $response->data['link'],
            $response->data['title'],
            $response->data['template'],
            $response->data['product_id'],
            $response->data['item_order_qty'],
            $response->data['custom_price']
        );

        // add some calculated data
        $response->data['shipping_price'] = floatval(get_post_meta($post->ID, '_shipping_price', true));
        $response->data['items_total_price'] = floatval(get_post_meta($post->ID, '_items_total_price', true));

        // get rejected items
        $rejected_items = get_query_var('rejected_items', false);

        // return rejected items if any
        if ($rejected_items) {
            $response->data['rejected_items'] = $rejected_items;
        }

        return $response;
    }
    public function register_rest_fields()
    {
        /**
         * Field type reference:
         * https://core.trac.wordpress.org/browser/tags/5.4/src/wp-includes/rest-api.php#L116
         */
        $fields = array(
            'user_id' => 'integer',
            'billing_address' => 'object',
            'shipping_address' => 'object',
            'billing_email' => 'string',
            'billing_phone' => 'string',
            'payment_method' => 'string',
            'order_label' => 'string',
            'order_notes' => 'string',
            'shipping_method' => 'string',
            'shipping_number' => 'string',
            'shipping_price' => 'string',
            'order_items' => 'array',
            'order_currency' => 'string',
            'order_total' => 'number',
        );

        // iterate to register the fields
        foreach ($fields as $field_name => $type) {
            register_rest_field('comm_order', $field_name, array(
                'get_callback' => array($this, 'get_rest_field'),
                'update_callback' => array($this, 'update_rest_field'),
                'schema' => array('type' => $type),
            ));
        }
        self::commercioo_custom_rest_api();
    }

    private function commercioo_custom_rest_api()
    {
        register_rest_route('commercioo/v1', '/comm_get_price/', array(
            'methods' => \WP_REST_Server::READABLE,
            'callback' => array($this, 'comm_get_price'),
            'args' => array(
                "prodID" => ['required' => true],
                "prodQty" => ['required' => true],
                "prodTotalPrice" => ['required' => true],
            ),
            'permission_callback' => function () {
                return current_user_can('publish_posts');
            }
        ));

        register_rest_route('commercioo/v1', '/comm_detail_order/(?P<id>[a-zA-Z0-9-]+)', array(
            'methods' => \WP_REST_Server::READABLE,
            'callback' => array($this, 'comm_detail_order'),
            'args' => array(
                'id' => array(
                    'validate_callback' => function ($param, $request, $key) {
                        return $this->validate_order_id($param);
                    }
                ),
            ),
            'permission_callback' => function () {
                return current_user_can('publish_posts');
            }
        ));
        // Autoresponder Table action
        register_rest_route('commercioo/v2', '/comm_order_action', array(
            'methods' => \WP_REST_Server::EDITABLE,
            'callback' => array($this, 'order_action'),
            'permission_callback' => function () {
                return current_user_can('publish_posts');
            }
        ));
    }
    
    public function comm_detail_order($request) {
        global $comm_options;
        
        $params   = $request->get_params();
        $order_id = $params['id'];
        $order    = comm_get_order( $order_id );
        $listData = get_post( $order_id );
		
		if ( $listData ) {
            $order_user_id 	  		= get_post_meta( $order_id, "_user_id", true );
			$status 		  		= $order->get_order_status();
            $billing_address        = $order->get_billing_address();
			$billing_display_name	= sprintf( "%s %s", $billing_address['billing_first_name'], $billing_address['billing_last_name'] );

			// generate status
            if ( $status == "pending" ) {
                $badge_status = "c-ar-badge-rounded pending";
			} elseif ( $status == "processing" ) {
                $badge_status = "c-ar-badge-rounded processing";
			} elseif ( $status == "completed" ) {
                $badge_status = "c-ar-badge-rounded complete";
			} elseif ( $status == "refunded" ) {
                $badge_status = "c-ar-badge-rounded refund";
			} elseif ( $status == "abandoned" ) {
                $badge_status = "c-ar-badge-rounded abandoned";
            } else {
                $badge_status = "c-ar-badge-rounded inactive-status";
            }
            
            // generate status badge
            $id_label = sprintf( 
                "%s #%s",
                __( 'Order ID', 'commercioo' ), 
                esc_html( $order_id )
            );

            $status_label = sprintf( 
                "<span class='badge %s c-badge-status-pending'>%s</span>",
                esc_attr( $badge_status ), 
                esc_html( ucfirst( $status ) )
            );
            // generate html_content
            ob_start();
            ?>
            <div class="row m-0 mb-2">
                <div class="col-md-3">
                    <label class="c-label">Status:</label>
                </div>
                <div class="col-md-6">
                    <span class='badge <?php esc_attr_e( $badge_status ) ?> c-badge-status-pending'><?php esc_html_e( ucfirst( $status ) ) ?><span>
                </div>
                <div class="col-md-3"></div>
            </div>
            <div class="c-line-dash-settings mb-3"></div>
            <div class="row m-0 mb-2">
                <div class="col-md-3">
                    <label class="c-label">Name:</label>
                </div>
                <div class="col-md-6">
                    <?php echo esc_html( $billing_display_name ) ?> (ID: <?php echo esc_html( $order_user_id ) ?>)
                </div>
                <div class="col-md-3"></div>
            </div>
            <div class="row m-0 mb-2">
                <div class="col-md-3">
                    <label class="c-label">Address:</label>
                </div>
                <div class="col-md-6">
                    <?php echo esc_html($order->get_formatted_address( 'billing', ', ', "{street_address}\n{city}\n{state}\n{zip}\n{country}" )); ?>
                </div>
                <div class="col-md-3"></div>
            </div>
            <div class="row m-0 mb-2">
                <div class="col-md-3">
                    <label class="c-label">Mobile phone:</label>
                </div>
                <div class="col-md-6">
                    <a href="tel:<?php echo esc_attr( $billing_address['billing_phone'] ) ?>" class="c-set-text-decoration">
                        <?php echo esc_html( $billing_address['billing_phone'] ) ?>
                    </a>
                </div>
                <div class="col-md-3"></div>
            </div>
            <div class="c-line-dash-settings mb-3"></div>
            <div class="row m-0 mb-2">
                <div class="col-md-3">
                    <label class="c-label">Email:</label>
                </div>
                <div class="col-md-6">
                    <a href="mailto:<?php echo esc_attr( $order->get_billing_email() ) ?>" class="c-set-text-decoration">
						<?php echo esc_html( $order->get_billing_email() ); ?>
					</a>
                </div>
                <div class="col-md-3"></div>
            </div>
            <div class="c-line-dash-settings mb-3"></div>
            <div class="row m-0 mb-2">
                <div class="col-md-3">
                    <label class="c-label">Payment Method:</label>
                </div>
                <div class="col-md-6">
                    <?php echo esc_html(comm_payment_method_label($order_id)); ?>
                </div>
                <div class="col-md-3"></div>
            </div>
            <div class="c-line-dash-settings mb-3"></div>
            <div class="row m-0 mb-2">
                <div class="col-md-6">
                    <label class="c-label">Products:</label>
                </div>
                <div class="col-md-6 text-end">
                    <label class="c-label float-right">Price:</label>
                </div>
            </div>
            <?php
            $get_discount_total = $order->get_discount_total();
//            $grand_total_price = $order->get_total();
            $subTotal =0;
            $order_items = $order->get_order_cart_items();
            if($order_items):
            ?>
			<?php foreach ($order_items as $item ) :

                if ( $order->has_discount() ){
                    $get_discount_total = $order->get_discount_total() * $item->item_order_qty;
                }


                ?>
                <div class="row m-0 mb-3">
                    <div class="col-md-6">
                        <a class="c-link" href="<?php echo esc_url( get_permalink( $item->product_id ) ) ?>" target="_blank">
                            <p class="m-0"><?php echo esc_html( sprintf( "%s (x%s)", $item->item_name, $item->item_order_qty ) ); ?></p>
                        </a>
                        <span>
                            <?php if($item->item_sales_price>0):?>
                                <label class="label-summary-product c-semibold"><?php echo wp_kses_post('<del>' . \Commercioo\Helper::formatted_currency( $item->item_price) . '</del> ');?></label>
                                <label class="label-summary-product c-semibold"><?php echo esc_html(\Commercioo\Helper::formatted_currency($item->item_sales_price)); ?></label>
                            <?php else:?>
                                <label class="label-summary-product c-semibold"><?php echo wp_kses_post(\Commercioo\Helper::formatted_currency( $item->item_price));?></label>
                            <?php endif; ?>
                        </span>
                    </div>
                    <div class="col-md-6 text-end">
                        <span class="float-right">
                            <?php if($item->item_sales_price>0):?>
                            <?php  $prod_price = $item->item_sales_price * $item->item_order_qty;
                            ?>
                                <label class="label-summary-product c-semibold"><?php echo esc_html(\Commercioo\Helper::formatted_currency($prod_price)); ?></label>
                            <?php else:?>
                            <?php  $prod_price = $item->item_price * $item->item_order_qty;
                            ?>
                                <label class="label-summary-product c-semibold"><?php echo wp_kses_post(\Commercioo\Helper::formatted_currency($prod_price));?></label>
                            <?php endif; ?>
                        </span>
                    </div>
                </div>
            <?php endforeach; ?>
            <?php endif; ?>
            <?php do_action( 'commercioo_order_detail_after_items', $order_id ); ?>
            <?php if ( $order->has_fee() ) : ?>
                <div class="row m-0 mt-3 mb-2 order-detail-shipping">
                    <div class="col-md-6">
                        <label class="c-label">
                            <?php esc_html_e( 'Additional Fee', 'commercioo' ) ?>
                        </label>
                    </div>
                    <div class="col-md-6 text-end">
                        <label class="c-label float-right">
                            <?php echo esc_html( \Commercioo\Helper::formatted_currency( $order->get_fee_total() ) ) ?>
                        </label>
                    </div>
                    <tr class="">
                        <th class="c-th-table-detail-order"></th>
                        <th class="c-th-table-detail-order"></th>
                        <th class="c-th-table-detail-order"></th>
                        <th class="c-th-table-detail-order"></th>
                    </tr>
                </div>
            <?php endif; ?>
            <?php
            $this->comm_set_order_sub_total($subTotal);
            $item_total_price = $this->comm_get_order_sub_total();
            $grand_total_price = $item_total_price;
            ?>

            <?php if ( $order->has_shipping() ) : ?>
                <div class="row m-0 mb-2 order-detail-shipping">
                    <div class="col-md-6">
                        <label class="c-label">
                            <?php esc_html_e( 'Shipping', 'commercioo' ) ?> (<?php echo esc_html( $order->get_shipping_method() ) ?>)
                        </label>
                    </div>
                    <div class="col-md-6 text-end">
                        <p class="m-0 float-right">
                            <?php echo esc_html( \Commercioo\Helper::formatted_currency( $order->get_shipping_price() ) ) ?>
                        </p>
                    </div>
                </div>
            <?php endif; ?>
            <?php if ($order->get_unique_code()) : ?>
                <div class="row m-0 mb-2 order-detail-shipping">
                    <div class="col-md-6">
                        <label class="c-label">
                            <?php echo esc_html($order->get_unique_label_code()); ?>
                        </label>
                    </div>
                    <div class="col-md-6 text-end">
                        <p class="m-0 float-right">
                            <?php echo esc_html($order->get_unique_code());?>
                        </p>
                    </div>
                </div>
            <?php endif; ?>
            <?php do_action( 'commercioo_order_detail_before_total', $order_id ); ?>
                <div class="row m-0 mb-2 order-detail-total">
                    <div class="col-md-6">
                        <label class="c-label">
                            <?php esc_html_e( 'Total Order', 'commercioo' ) ?>
                        </label>
                    </div>
                    <div class="col-md-6 text-end">
                        <p class="m-0 float-right">
                            <?php echo esc_html(\Commercioo\Helper::formatted_currency( $order->get_total())); ?>
                        </p>
                    </div>
                </div>
            <div class="c-line-dash-settings mb-3"></div>
            <?php if ( $order->get_status_confirmation_payment() ) : ?>
                <div class="row m-0 mb-2">
                    <div class="col-sm-12">
                        <label class="c-label"> <?php esc_html_e( 'Confirmation Payment Detail', 'commercioo' ) ?></label>
                    </div>
                    <div class="col-md-3"></div>
                </div>
                <div class="row m-0 mb-2">
                    <div class="col-sm-4">
                        <label class="c-label"> <?php esc_html_e( 'Transfer Atas Nama', 'commercioo' ) ?></label>
                    </div>
                    <div class="col-md-6">
                        <?php echo esc_html($order->get_transfer_from_name()) ?>
                    </div>
                    <div class="col-md-3"></div>
                </div>
                <div class="row m-0 mb-2">
                    <div class="col-sm-4">
                        <label class="c-label"> <?php esc_html_e( 'Transfer ke', 'commercioo' ) ?></label>
                    </div>
                    <div class="col-md-6">
                        <?php
                        $get_transfer_to_bank = str_replace(","," - ",$order->get_transfer_to_bank());
                        ?>
                        <?php echo esc_html($get_transfer_to_bank) ?>
                    </div>
                    <div class="col-md-3"></div>
                </div>
                <div class="row m-0 mb-2">
                    <div class="col-sm-4">
                        <label class="c-label"> <?php esc_html_e( 'Nominal Transfer', 'commercioo' ) ?></label>
                    </div>
                    <div class="col-md-6">
                        <?php echo esc_html(\Commercioo\Helper::formatted_currency($order->get_transfer_amount())) ?>
                    </div>
                    <div class="col-md-3"></div>
                </div>
                <div class="row m-0 mb-2">
                    <div class="col-sm-4">
                        <label class="c-label"> <?php esc_html_e( 'Bukti File Transfer', 'commercioo' ) ?></label>
                    </div>
                    <div class="col-md-6">
                        <?php
                        $bukti_transfer_file = $order->get_bukti_transfer_file();
                        $url = wp_get_attachment_image_src(intval($bukti_transfer_file));
                        $url_bukti_transfer_file=null;
                        if($url){
                            $url_bukti_transfer_file = $url[0];
                        }

                        ?>
                        <a target="_blank" title="<?php esc_attr_e("Click to open file","commercioo");?>" href="<?php echo esc_url($url_bukti_transfer_file);?>"><img src="<?php echo esc_url($url_bukti_transfer_file);?>" class="c-img-thumbnail"></a>
                    </div>
                    <div class="col-md-3"></div>
                </div>
                <div class="row m-0 mb-2">
                    <div class="col-sm-4">
                        <label class="c-label"> <?php esc_html_e( 'Tanggal Transfer', 'commercioo' ) ?></label>
                    </div>
                    <div class="col-md-6">
                        <?php echo esc_html($order->get_transfer_date()) ?>
                    </div>
                    <div class="col-md-3"></div>
                </div>
            <?php endif; ?>
			<?php
			$html_content = ob_get_clean();
			
			// send response
            wp_send_json_success( array(
                'status_label'         => $id_label,
                'shipping_number'         => get_post_meta( $order_id, "_shipping_number", true ),
                'detail_order_content' => $html_content,
            ), 200 );
        }
        else {
            wp_send_json_error( array( 'message' => __( 'Invalid order data!', 'commercioo' ) ), 400 );
        }
    }
    
    public function validate_order_id($order_id){
        $is_success = true;
        if(!is_numeric($order_id)){
            return false;
        }
        $listData = get_post( $order_id );

        if (!$listData) {
            return new \WP_Error('comm_error', "could not find original post: " .
                $order_id, array('status' =>
                404));
        }
        return $is_success;
    }
    public function comm_get_order_sub_total()
    {
        return $this->subTotal;
    }

    public function comm_set_order_sub_total($prodTotalPrice)
    {
        $this->subTotal = $prodTotalPrice;
    }

    public function comm_get_price($request)
    {
        global $comm_options;
        $params = $request->get_params();
        $order_id = $params['orderID'];
        $order =new \Commercioo\Models\Order( $order_id);

        $prod_id = $params['prodID'];
        $prod_qty = $params['prodQty'];
        $prodTotalPrice = $params['prodTotalPrice'];

        $item_order = [];
        $subTotal = 0;
        $prod_qty_item = 0;
        $prod_price = 0;
        foreach ($prod_id as $k => $prod) {
            $item_order[] = ["product_id" => $prod, "item_order_qty" => $prod_qty[$k],
                "custom_price" => $prodTotalPrice[$k]];
            $prod_qty_item += $prod_qty[$k];
            $prod_price = $prodTotalPrice[$k] * $prod_qty[$k];
            $subTotal += $prod_price;
        }

        $this->comm_set_order_sub_total($subTotal);
        $item_total_price = $this->comm_get_order_sub_total();

        $grand_total_price = $item_total_price;
        // if has fee
        if ( $order->has_fee() ){
            $grand_total_price  = $grand_total_price + $order->get_fee_total();
        }
        // if has shipping
        if ( $order->has_shipping() ){
            $grand_total_price  = $grand_total_price + $order->get_shipping_price();
        }

        $html = '<div class="row mt-1">
                    <div class="form-group col-md-4 mb-0">
                        <label class="float-right mb-0">' . esc_html("Items Subtotal", "commercioo") . '</label>
                    </div>
                    <div class="form-group col-md-2 mb-0 comm-total-qty">
                        <label class="float-right mb-0">' . esc_html( $prod_qty_item ) . '</label>
                    </div>
                    <div class="form-group col-md-6 mb-0">
                            <label class="comm-grand-total-price">' . \Commercioo\Helper::formatted_currency( $item_total_price ) . '</label>
                    </div>
                </div>';

        if ( $order->has_fee() ) :
        $html .= '<div class="row mt-1">
                    <div class="form-group col-md-4 mb-0">
                        <label class="float-right mb-0">' . esc_html( 'Additional fee', 'commercioo' ) . '</label>
                    </div>
                    <div class="form-group col-md-6 mb-0 offset-md-2">
                            <label class="comm-grand-total-price">' . esc_html( \Commercioo\Helper::formatted_currency( $order->get_fee_total() ) ) . '</label>
                    </div>
                </div>';
        endif;

        if ( $order->has_shipping() ) :
        $html .= '<div class="row mt-1">
                    <div class="form-group col-md-4 mb-0">
                        <label class="float-right mb-0">' . esc_html( 'Shipping', 'commercioo' ) . ' (' . esc_html( $order->get_shipping_method() ) . ')</label>
                    </div>
                    <div class="form-group col-md-6 mb-0 offset-md-2">
                            <label class="comm-grand-total-price">' . \Commercioo\Helper::formatted_currency( $order->get_shipping_price() ) .'</label>
                    </div>
                </div>';
        endif;
        if ( $order->get_unique_code() ) :
        $html .= '<div class="row mt-1">
                    <div class="form-group col-md-4 mb-0">
                        <label class="float-right">' . esc_html( $order->get_unique_label_code()) . '</label>
                    </div>
                    <div class="form-group col-md-6 mb-0 offset-md-2">
                        <label class="comm-grand-total-price">' . $order->get_unique_code() . '</label>
                    </div>
                </div>';
        endif;

        $html .= do_action( 'commercioo_order_detail_before_total', $order_id );

        $html .= '<div class="row mt-1">
                    <div class="form-group col-md-4 mb-0">
                        <label class="float-right mb-0">' . esc_html("Total", "commercioo") . '</label>
                    </div>
                    <div class="form-group col-md-6 mb-0 offset-md-2">
                            <label class="comm-grand-total-price">' . \Commercioo\Helper::formatted_currency( $grand_total_price ) . '</label>
                            <input type="hidden" name="order_total" value="' . $grand_total_price . '">
                    </div>
                </div>
                <div class="row">
                    <div class="col">
                        <button type="button" class="btn btn-primary float-right c-admin-button c-recalculate">' . esc_html("Update Cart", "commercioo") . '</button>
                    </div>
                </div>';

        wp_send_json_success(["result_html" => $html, "plain_price" => $prod_price], 200);
    }

    public function get_rest_field($array_data, $field_name)
    {
        $post_id = $array_data['id'];
        $private_field_name = sprintf("_%s", $field_name);

        // get by field_name
        switch ($field_name) {
            case 'user_id':
                $field = get_post_meta($post_id, $private_field_name, true);
                $field_value = intval($field);
                break;

            case 'billing_address':
                $field = get_post_meta($post_id, $private_field_name, true);
                $field_value = $field;
                break;

            case 'shipping_address':
                $field = get_post_meta($post_id, $private_field_name, true);
                $field_value = $field;
                break;

            case 'billing_email':
                $field = get_post_meta($post_id, $private_field_name, true);
                $field_value = $field;
                break;

            case 'billing_phone':
                $field = get_post_meta($post_id, $private_field_name, true);
                $field_value = $field;
                break;

            case 'payment_method':
                $field = get_post_meta($post_id, $private_field_name, true);
                $field_value = $field;
                break;

            case 'order_label':
                $field = get_post_meta($post_id, $private_field_name, true);
                $field_value = $field;
                break;

            case 'order_notes':
                $field = get_post_meta($post_id, $private_field_name, true);
                $field_value = $field;
                break;

            case 'shipping_method':
                $field = get_post_meta($post_id, $private_field_name, true);
                $field_value = $field;
                break;
            case 'shipping_number':
                $field = get_post_meta($post_id, $private_field_name, true);
                $field_value = $field;
                break;

            case 'order_items':
                $field = $this->get_comm_order_items($post_id);
                $field_value = $field;
                break;

            case 'order_currency':
                $field = get_post_meta($post_id, $private_field_name, true);
                $field_value = $field;
                break;

            case 'order_total':
                $field = get_post_meta($post_id, $private_field_name, true);
                $field_value = intval($field);
                break;

            default:
                $field_value = null;
                break;
        }

        return $field_value;
    }

    public function update_rest_field($field_value, $object_data, $field_name)
    {
        $post_id = $object_data->ID;
        $private_field_name = sprintf("_%s", $field_name);

        // get by field_name
        switch ($field_name) {
            case 'user_id':
                $value = intval($field_value);
                update_post_meta($post_id, $private_field_name, $value);
                break;

            case 'billing_address':
                $value = array_map('esc_html', $field_value);
                update_post_meta($post_id, $private_field_name, $value);
                break;

            case 'shipping_address':
                $value = array_map('esc_html', $field_value);
                update_post_meta($post_id, $private_field_name, $value);
                break;

            case 'billing_email':
                $value = sanitize_email($field_value);
                update_post_meta($post_id, $private_field_name, $value);
                break;

            case 'billing_phone':
                $value = sanitize_text_field($field_value);
                update_post_meta($post_id, $private_field_name, $value);
                break;

            case 'payment_method':
                $value = sanitize_text_field($field_value);
                update_post_meta($post_id, $private_field_name, $value);
                break;

            case 'order_label':
                $value = sanitize_text_field($field_value);
                update_post_meta($post_id, $private_field_name, $value);
                break;

            case 'order_notes':
                $value = sanitize_text_field($field_value);
                update_post_meta($post_id, $private_field_name, $value);
                break;

            case 'shipping_method':
                $value = sanitize_text_field($field_value);
                update_post_meta($post_id, $private_field_name, $value);

                // save shipping price
                // $shipping_method = $value;
                // update_post_meta($post_id, '_shipping_price', apply_filters('comm_shipping_price', 0, $shipping_method));
                break;
            case 'shipping_price':
                $value = sanitize_text_field($field_value);
                update_post_meta($post_id, $private_field_name, $value);
                break;

            case 'shipping_number':
                $value = sanitize_text_field($field_value);
                update_post_meta($post_id, $private_field_name, $value);
                break;

            case 'order_items':
                $value = $field_value;
                $this->set_comm_order_items($post_id, $value);
                break;

            case 'order_currency':
                $value = sanitize_text_field($field_value);
                update_post_meta($post_id, $private_field_name, $value);
                break;

            case 'order_total':
                $value = sanitize_text_field($field_value);
                update_post_meta($post_id, $private_field_name, $value);
                break;

            default:
                // silent is golden
                break;
        }
    }

    public function on_delete_comm_order($order_id)
    {
        // starting stock management, then get the initial product items
        $stock_management = new Comm_Stock_Management($order_id);

        // restock all deleted items
        $stock_management->force_restock_all_items();
    }

    /**
     * Get order items
     * the items are stored on table `commercioo_order_items`
     */
    public function get_comm_order_items($order_id)
    {
        global $wpdb;
        $db_table = $wpdb->prefix . 'commercioo_order_items';

        // get from database
        $order_items = $wpdb->get_results($wpdb->prepare("
            SELECT item_name, item_price, item_order_qty, product_id, variation_id 
            FROM $db_table
            WHERE order_id = %d",
            $order_id
        ));

        // adjust results
        foreach ($order_items as $key => $value) {
            $order_items[$key]->item_price = floatval($order_items[$key]->item_price);
            $order_items[$key]->item_order_qty = intval($order_items[$key]->item_order_qty);
            $order_items[$key]->product_id = intval($order_items[$key]->product_id);
            $order_items[ $key ]->variation_id   = intval( $order_items[ $key ]->variation_id );
            $order_items[ $key ]->is_variation   = ! empty( $order_items[ $key ]->variation_id );
        }

        // result
        return $order_items;
    }

    /**
     * Set order items
     * the items are based on product object that found by requested product_id
     * the items will be stored on table `commercioo_order_items`
     */
    public function set_comm_order_items($order_id, $order_items)
    {
        global $wpdb;

        $order = new \Commercioo\Models\Order( $order_id );

        $db_table = $wpdb->prefix . 'commercioo_order_items';
        $items_total_price = 0;
        $grand_total_price = 0;
        $order_item_ids = array();
        $rejected_items = array();

        // starting stock management, then get the initial product items
        $stock_management = new Comm_Stock_Management($order_id);

        $new_order_items = array();
        $items_order = array();
        foreach ($order_items as $item) {
            $index = array_search($item['product_id'], array_column($new_order_items, 'product_id'));
            if($index !== false){
                // Fix: add missing single quote at index 'item_order_qty' from array
                $new_order_items[$index]['item_order_qty'] += $item['item_order_qty'];
            }else{
                $new_order_items[] = $item;
            }
        }
        foreach ($new_order_items as $item) {
            // validate requested data
            if (
                !isset($item['product_id']) || intval($item['product_id']) <= 0 ||
                !isset($item['item_order_qty']) || intval($item['item_order_qty']) <= 0
            ) {
                // set rejected items and the reason
                $item['rejected_reason'] = __('Invalid product ID or qty', 'commercioo');
                $rejected_items[] = $item;

                // continue to next item
                continue;
            }

            // get args
            $product_id = intval($item['product_id']);
            $item_order_qty = intval($item['item_order_qty']);
            $custom_price = isset($item['custom_price']) ? floatval($item['custom_price']) : false;


            // get product
            $product = get_post($product_id);
            $_product = comm_get_product($product_id);

            if ( 'comm_product_var' === $product->post_type ) {
                $prod_id = $product->post_parent;
                $var_id = $product_id;
            } else {
                $prod_id = $product_id;
                $var_id = 0;
            }

            // validate post & the post type
            if (!$product || ( $product->post_type != 'comm_product' && $product->post_type != 'comm_product_var' ) ) {
                $item['rejected_reason'] = __('Invalid product', 'commercioo');
                $rejected_items[] = $item;

                // continue to next item
                continue;
            }
            $manage_stock = get_post_meta( $product_id, '_manage_stock', true );
            $has_code_pro=false;
            /**
             * Validate stock to continue the order of this item
             * otherwise, set to rejected item
             */
            if($manage_stock && $has_code_pro) {
                if (!$stock_management->validate_stock($prod_id, $var_id, $item_order_qty)) {
                    $item['rejected_reason'] = __('Not enough stock', 'commercioo');
                    $rejected_items[] = $item;

                    // continue to next item
                    continue;
                }
            }
            // get item price
            if ($custom_price) {
                $item_price = $custom_price;
            } else {
                // Fix: Method call is provided 2 parameters, but the method signature uses 1 parameters
                $item_price = $this->get_comm_product_price($product_id);
            }

            // get product args
            $item_name = $_product->get_title();
            $items_total_price = $items_total_price + ($item_price * $item_order_qty);

            // look up the initial (stored) item if exist
            $order_product_item = $stock_management->initial_item_fields_by_product($prod_id, $var_id);


            // if existed do update, otherwise do insert
            if ($order_product_item) {
                $wpdb->update(
                    $db_table,
                    array(
                        'item_name' => $item_name,
                        'item_price' => $item_price,
                        'item_order_qty' => $item_order_qty,
                    ),
                    array(
                        'item_id' => $order_product_item->item_id,
                    ),
                    array('%s', '%d', '%d'),
                    array('%d')
                );

                $order_item_ids[] = $order_product_item->item_id;
            } else {
                $wpdb->insert(
                    $db_table,
                    array(
                        'order_id' => $order_id,
                        'product_id' => $prod_id,
                        'variation_id' => $var_id,
                        'item_name' => $item_name,
                        'item_price' => $item_price,
                        'item_order_qty' => $item_order_qty,
                    ),
                    array('%d', '%d', '%d', '%s', '%d', '%d')
                );

                $order_item_ids[] = $wpdb->insert_id;
            }
            $regular_price = $item_price;
            $sales_price = get_post_meta($prod_id, "_sale_price", true);
            if ($sales_price) {
                $regular_price = $sales_price;
            }
            $items_order[] = array(
                "product_id"=>$prod_id,
                "variation_id"=>$var_id,
                "item_name"=>$item_name,
                "item_price"=>$regular_price,
                "item_order_qty"=>$item_order_qty,
            );
        }
        update_post_meta($order_id, '_order_items',$items_order);
        $grand_total_price = $items_total_price;

        if ( $order->has_shipping() ){
            $grand_total_price  = $grand_total_price + $order->get_shipping_price();
        }


        // save the items_total_price
        update_post_meta($order_id, '_items_total_price', apply_filters('comm_order_items_total_price', $items_total_price, $order_id));
        // The comment line is below because it has been called from the filter hook 'commercioo_update_post_meta'
//update_post_meta($order_id, '_order_total', apply_filters('comm_order_total_price', $grand_total_price, $order_id));
        // restock
        $stock_management->restock();
        // remove unused item ids if any
        $this->remove_unused_item_ids($order_item_ids, $order_id);

        // send rejected items variable to response
        set_query_var('rejected_items', $rejected_items);
    }

    /**
     * Get product price
     * calculated with pro_version and sales price
     */
    protected function get_comm_product_price( $product_id ) {
        $product = comm_get_product( $product_id );
        return apply_filters( 'comm_product_regular_price', $product->get_regular_price(), $product_id );
    }

    /**
     * Remove rejected items
     * some unused data on commercioo_order_items will be deleted
     */
    protected function remove_unused_item_ids($order_item_ids, $order_id)
    {
        if ( empty( $order_item_ids ) ) {
            return;
        }

        global $wpdb;
        $db_table = $wpdb->prefix . 'commercioo_order_items';
        $imploded_ids = implode(',', $order_item_ids);

        $wpdb->query($wpdb->prepare("
            DELETE FROM $db_table
            WHERE order_id = %d
            AND item_id NOT IN ( $imploded_ids )",
            $order_id
        ));
    }
    public function comm_order_list($order) {
        $output['data'] = [];

        if ( $order ) {
            $i = 0;
            
			if ( ! is_array( $order ) || count( $order ) == 1 ) {
                $order_data[] = $order;
            } else {
                $order_data = $order;
            }
            
			$display_name = '';

            foreach ($order_data as $p => $v) {
                $id = $v->ID;
                $status = $v->post_status;

                // create checkbox
                $checkbox = '<div class="table-option"><input type="checkbox" name="order_id" value="' . $id . '">
                                <span class="btn btn-default" type="button" data-bs-toggle="dropdown"><i class="fa fa-angle-down"></i></span>
                                <ul class="dropdown-menu dropdown-menu-right">
                                    ' . ( $status !== 'trash' ? '
                                    <li><a href="#" class="c-edit comm-mark-as ' . ( $status === 'comm_pending' ? 'disabled' : '' ) . '" data-id="' . $id . '" data-action="pending" data-type="single">Set to Pending</a></li>
                                    <li><a href="#" class="c-edit comm-mark-as ' . ( $status === 'comm_processing' ? 'disabled' : '' ) . '" data-id="' . $id . '" data-action="processing" data-type="single">Set to Processing</a></li>
                                    <li><a href="#" class="c-edit comm-mark-as ' . ( $status === 'comm_completed' ? 'disabled' : '' ) . '" data-id="' . $id . '" data-action="completed" data-type="single">Set to Completed</a></li>
                                    <li><a href="#" class="c-edit comm-mark-as ' . ( $status === 'comm_refunded' ? 'disabled' : '' ) . '" data-id="' . $id . '" data-action="refunded" data-type="single">Set to Refunded</a></li>
                                    <li><a href="#" class="c-edit comm-mark-as ' . ( $status === 'comm_abandoned' ? 'disabled' : '' ) . '" data-id="' . $id . '" data-action="abandoned" data-type="single">Set to Abandoned</a></li>
                                    <li><a href="#" class="c-edit comm-mark-as ' . ( $status === 'comm_failed' ? 'disabled' : '' ) . '" data-id="' . $id . '" data-action="failed" data-type="single">Set to Failed</a></li>
                                    ' : '
                                    <li><a href="#" class="c-edit comm-mark-as ' . ( $status !== 'trash' ? 'disabled' : '' ) . '" data-id="' . $id . '" data-action="restore" data-type="single">Restore</a></li>
                                    ' ) . '
                                    <li><hr class="c-hr-line"></li>
                                    ' . ( $status !== 'trash' ? '
                                    <li><a href="' . comm_controller()->comm_dash_page( 'comm_order' ) . '&action=edit&id=' . $id. '" class="c-edit" data-id="' . $id . '">Edit</a></li>
                                    ' : '' ) . '
                                    ' . ( $status !== 'trash' ? '
                                    <li><a href="#" class="c-edit comm-mark-as delete" data-id="' . $id . '" data-action="trash" data-type="single">Trash</a></li>
                                    ' : '' ) . '
                                    ' . ( $status === 'trash' ? '
                                    <li><a href="#" class="c-edit delete c-delete" data-id="' . $id . '" data-action="delete" data-type="single">Delete Permanently</a></li>
                                    ' : '' ) . '
                                </ul>
                            </div>';

                $order = new \Commercioo\Models\Order( $id );

                $order_user_id = get_post_meta($id, "_user_id", true);
                $order_total = get_post_meta($id, "_order_total", true);
                $_billing_email = $order->get_billing_email();
                $bill_addrs = get_post_meta($id, "_billing_address", true);

                $first_name = isset( $bill_addrs['billing_first_name'] ) ? $bill_addrs['billing_first_name'] : '';
                $last_name = isset( $bill_addrs['billing_last_name'] ) ? $bill_addrs['billing_last_name'] : '';
                $comm_billing_phone = isset( $bill_addrs['billing_phone'] ) ? $bill_addrs['billing_phone'] : '';
				
                $status = $v->post_status;

                $order_note = get_post_meta($id, "_order_notes", true);
                $order_label = get_post_meta($id, "_order_label", true);

                if($order_user_id){
                    $user = $order->get_customer()->get_single_customer();
                    $display_name = isset( $user->name ) ? $user->name : '';
                }

                $display_name = $first_name." ".$last_name;
                if(!$order_note){
                    $order_note='';
                }

                if($order_label=="green"){
                    $order_label = "Interested";
                    $class_order_label = "c-note-green";
                }elseif($order_label=="orange"){
                    $order_label = "Need Reminding";
                    $class_order_label = "c-note-orange";
                }else{
                    $order_label = "Not Interested";
                    $class_order_label = "c-note-grey";
                }
                $d = get_the_date( 'M j, Y',$id);
                $dT = get_the_date( 'H:i:s',$id);
                $post_date = "<span class='comm-post-date'>".$d."</span>". " <span class='comm-time'>at " . $dT."</span>";
             

                if ($status == "comm_pending") {
                    $status = "pending";
                    $badge_status = "c-ar-badge-rounded pending";
                } elseif ($status == "comm_processing") {
                    $status = "processing";
                    $badge_status = "c-ar-badge-rounded processing";
                } elseif ($status == "comm_completed") {
                    $status = "completed";
                    $badge_status = "c-ar-badge-rounded complete";
                } elseif ($status == "comm_refunded") {
                    $status = "refunded";
                    $badge_status = "c-ar-badge-rounded refund";
                } elseif ($status == "comm_abandoned") {
                    $status = "abandoned";
                    $badge_status = "c-ar-badge-rounded abandoned";
                }elseif ($status == "comm_failed") {
                    $status = "failed";
                    $badge_status = "c-ar-badge-rounded netral";
                } else {
                    $status = "trash";
                    $badge_status = "c-ar-badge-rounded inactive-status";
                }

                $class_followup_msg = "";
                $followup_msg = "";
                if(is_comm_wa()){
                    $class_followup_msg = "c-show-followup-wa";
                }
                $order_id = "#".$id;

                if ($status == "completed") {
                    $post_title = $order_id;
                }else{
                    $post_title = "<a href='". comm_controller()->comm_dash_page("comm_order") ."&action=edit&id=" . $id. "' class='c-cursor' data-bs-container='body'
                 data-bs-toggle='popover' data-bs-placement='right' data-bs-trigger='hover' data-bs-content='Edit order' data-id='$id'>";
                    $post_title .= $order_id;
                    $post_title .= "</a>";
                }
                $customername = '<span
                                    class="c-cursor c-name-info c-show-detail-order"
                                    data-bs-container="body"
                                    data-bs-toggle="modal"
                                    data-bs-placement="right"
                                    data-bs-trigger="click"
                                    data-bs-content="View detail order"
                                    data-bs-target="#modaldetailorder" data-id='.$id.'
                            ><i class="feather-16" data-feather="info" data-bs-container="body" data-bs-toggle="popover" data-bs-placement="right" data-bs-trigger="hover click"  data-bs-content="View detail order"></i>&nbsp; 
                            </span>';
                $customername.=$display_name;
                $customeremail ='<span
                                    class="c-cursor c-link-color comm-copy-billing-email"
                                    data-bs-container="body"
                                    data-bs-toggle="popover"
                                    data-bs-placement="right"
                                    data-bs-trigger="hover"
                                    data-bs-content="Copy email address">'.$_billing_email.'</span>';
                if(!empty($comm_billing_phone) && is_comm_wa()) {
                    $followup_msg = '<span
                                    class="c-cursor c-link-color ' . $class_followup_msg . '"
                                    data-bs-container="body"
                                    data-bs-toggle="modal"
                                    data-bs-placement="right"
                                    data-bs-trigger="hover"
                                    data-bs-content="Send WhatsAppp followup"
                                    data-bs-target="#modalsendwafolowup"
                            ><i class="fa fa-whatsapp"></i>&nbsp; %s</span>';
                }else{
                   if(!empty($comm_billing_phone)){
                        $followup_msg = '<i class="fa fa-whatsapp"></i>&nbsp; %s';
                    }
                }

                $confirmation_payment_icon='';
                if($status=="pending" && $order->get_status_confirmation_payment()){
                    $confirmation_payment_icon = '<span
                                    class="c-cursor c-show-detail-order c-icon-confirmation-payment"
                                    data-bs-container="body"
                                    data-bs-toggle="modal"
                                    data-bs-placement="right"
                                    data-bs-trigger="hover"
                                    data-bs-content="View payment confirmationr"
                                    data-bs-target="#modaldetailorder" data-id='.$id.'
                            ><span class="iconify" data-icon="bi-cart-check"></span>&nbsp; 
                            </span>';
                }

                $status_badge ='<span class="badge '.$badge_status.' c-badge-status-pending">'.ucfirst($status)
                    .'</span>'.$confirmation_payment_icon;

                $order_notes = '<a
                        href="#"
                        class="%s"
                        data-bs-container="body"
                        data-bs-toggle="popover"
                        data-bs-placement="right"
                        data-bs-trigger="hover"
                        data-bs-content="%s"
                >
                    <i class="fa fa-file-text-o"></i>&nbsp; %s
                </a>';
               if ($status == "pending") {
                    $actionButton = '
                <a
                        href="#"
                        class="btn btn-sm c-btn-processing comm-mark-as mb-2 orange-color"
                        data-bs-container="body"
                        data-bs-toggle="popover"
                        data-bs-placement="top"
                        data-bs-trigger="hover" data-id="' . $id . '"
                        data-bs-content="Mark as processing"
                >
                    <i class="fa fa-refresh"></i>
                </a>
                <a
                        href="#"
                        class="btn btn-success btn-sm c-btn-success comm-mark-as mb-2"
                        data-bs-container="body"
                        data-bs-toggle="popover"
                        data-bs-placement="top"
                        data-bs-trigger="hover" data-id="' . $id . '"
                        data-bs-content="Mark as complete"
                >
                    <i class="fa fa-check"></i>
                </a>
        <a href="#"  data-id="' . $id . '" data-status="trash"  data-bs-container="body" data-bs-toggle="popover" 
        data-bs-placement="top" data-bs-trigger="hover" data-bs-content="Trash" 
        class="c-trash btn btn-sm c-btn-wrap-products mb-2 comm-mark-as">
           <i class="fa fa-trash"></i>
        </a>';
                }elseif ($status == "processing") {
                    $actionButton = '<a
                        href="#"
                        class="btn btn-secondary btn-sm c-btn-pending comm-mark-as mb-2"
                        data-bs-container="body"
                        data-bs-toggle="popover"
                        data-bs-placement="top"
                        data-bs-trigger="hover" data-id="' . $id . '"
                        data-bs-content="Mark as pending"
                >
                    <i class="fa fa-ellipsis-h"></i>
                </a>             
                <a
                        href="#"
                        class="btn btn-success btn-sm c-btn-success comm-mark-as mb-2"
                        data-bs-container="body"
                        data-bs-toggle="popover"
                        data-bs-placement="top"
                        data-bs-trigger="hover" data-id="' . $id . '"
                        data-bs-content="Mark as complete"
                >
                    <i class="fa fa-check"></i>
                </a>
        <a href="#"  data-id="' . $id . '" data-status="trash"  data-bs-container="body" data-bs-toggle="popover" 
        data-bs-placement="top" data-bs-trigger="hover" data-bs-content="Trash" 
        class="c-trash btn btn-sm c-btn-wrap-products mb-2 comm-mark-as">
           <i class="fa fa-trash"></i>
        </a>';
                }elseif ($status == "completed") {
                    $actionButton = '<a
                        href="#"
                        class="btn btn-sm c-btn-processing mb-2 comm-mark-as orange-color"
                        data-bs-container="body"
                        data-bs-toggle="popover"
                        data-bs-placement="top"
                        data-bs-trigger="hover" data-id="' . $id . '"
                        data-bs-content="Mark as processing"
                >
                    <i class="fa fa-refresh"></i>
                </a>                
        <a href="#"  data-id="' . $id . '" data-status="trash"  data-bs-container="body" data-bs-toggle="popover" 
        data-bs-placement="top" data-bs-trigger="hover" data-bs-content="Trash" 
        class="c-trash btn btn-sm c-btn-wrap-products mb-2 comm-mark-as">
           <i class="fa fa-trash"></i>
        </a>';
                }elseif ($status == "refund" || $status == "abandoned" || $status == "failed") {
                    $actionButton = '<a
                        href="#"
                        class="btn btn-secondary btn-sm c-btn-pending comm-mark-as mb-2"
                        data-bs-container="body"
                        data-bs-toggle="popover"
                        data-bs-placement="top"
                        data-bs-trigger="hover" data-id="' . $id . '"
                        data-bs-content="Mark as pending"
                >
                    <i class="fa fa-ellipsis-h"></i>
                </a>
                <a
                        href="#"
                        class="btn btn-sm c-btn-processing mb-2 comm-mark-as orange-color"
                        data-bs-container="body"
                        data-bs-toggle="popover"
                        data-bs-placement="top"
                        data-bs-trigger="hover" data-id="' . $id . '"
                        data-bs-content="Mark as processing"
                >
                    <i class="fa fa-refresh"></i>
                </a>
                <a
                        href="#"
                        class="btn btn-success btn-sm c-btn-success comm-mark-as mb-2"
                        data-bs-container="body"
                        data-bs-toggle="popover"
                        data-bs-placement="top"
                        data-bs-trigger="hover" data-id="' . $id . '"
                        data-bs-content="Mark as complete"
                >
                    <i class="fa fa-check"></i>
                </a>
        <a href="#"  data-id="' . $id . '" data-status="trash"  data-bs-container="body" data-bs-toggle="popover" 
        data-bs-placement="top" data-bs-trigger="hover" data-bs-content="Trash" 
        class="c-trash btn btn-sm c-btn-wrap-products mb-2 comm-mark-as">
           <i class="fa fa-trash"></i>
        </a>';

                } else {
                    $actionButton = '                   
        <a href="#" data-bs-container="body" data-bs-toggle="popover" data-bs-placement="top" data-bs-trigger="hover" data-bs-content="Restore" class="c-restore btn btn-sm c-btn-wrap-products mb-2 comm-mark-as" data-id="' . $id . '" data-status="published">
          <i class="fa fa-refresh"></i>
        </a>
         <a href="#" data-bs-container="body" data-bs-toggle="popover" data-bs-placement="top" data-bs-trigger="hover" 
         data-bs-content="Delete" class="c-delete btn btn-sm c-btn-wrap-products mb-2" data-id="' . $id . '" data-status="del_permanent">
           <i class="fa fa-trash"></i>
        </a>';
                }
                $output['data'][$i] = apply_filters( 'comm_order_list_column', [
                    $checkbox,
                    $post_title,
                    $customername,
                    $customeremail,
                    $comm_billing_phone,
                    \Commercioo\Helper::formatted_currency($order_total),
                    $post_date,
                    $order_note,
                    $status_badge,
                    // $actionButton
                ], $id );
                $i++;
            }

        }
        return $output;
    }

    public function order_action( $request ) {
        $params   = $request->get_params();
        $order_id = $params['id'];
        $tbl      = $params['tbl'];
        $type     = $params['type'];
        $action   = $params['action'];
        $response = '';
        // check if single or array
        if ( is_array( $order_id ) ) {
            foreach ( $order_id as $val ) {
                $single_id = absint( $val );
                if ( $action == 'trash' ) {
                    $response = wp_trash_post( $single_id );
                    if ( $response == null ) {
                        $response = 'Order already in Trash';
                    }
                } elseif ( $action == 'delete' ) {
                    $response = wp_delete_post( $single_id );
                } else {
                    $response = wp_update_post(
                        array(
                            'ID'          => $single_id,
                            'post_status' => $action,
                        )
                    );
                }

                if ( $response == null ) {
                    $response = "error";
                    return new \WP_Error('error_' . $action . '_data', 'Error update data', array(
                        'status' => 404 ));
                }
            }
        } else {
            $single_id = absint( $order_id );

            if ( $action == 'trash' ) {
                $response = wp_trash_post( $single_id );
            } elseif ( $action == 'delete' ) {
                $response = wp_delete_post( $single_id );
            } else {
                $response = wp_update_post(
                    array(
                        'ID'          => $single_id,
                        'post_status' => $action,
                    )
                );
            }
            
            if ( $response == null ) {
                $response = "error";
                return new \WP_Error('error_' . $action . '_data', 'Error update data', array( 'status' => 404 ));
            }
        }
        $response = [ "status" => $response ];
        return rest_ensure_response( $response );
    }
}

//Comm_Order::getInstance();
