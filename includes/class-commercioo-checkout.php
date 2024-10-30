<?php
/**
 * Commercioo checkout.
 *
 * @author Commercioo Team
 * @package Commercioo
 */

namespace Commercioo;

if (!defined('WPINC')) {
    exit;
}

if (!class_exists('Commercioo\Checkout')) {

    /**
     * Class Checkout
     */
    class Checkout
    {

        /**
         * Default fields
         *
         * @var array
         */
        private $default_fields = array();

        /**
         * Class instance
         *
         */
        private static $instance;

        /**
         * Order constructor.
         *
         * @param int $order_id order id.
         */
        public function __construct()
        {
            add_action("wp_ajax_get_product_checkout", array($this, "get_product_checkout"));
            add_action("wp_ajax_nopriv_get_product_checkout", array($this, "get_product_checkout"));

            add_action("wp_ajax_get_product_grandTotal", array($this, "get_product_grandTotal"));
            add_action("wp_ajax_nopriv_get_product_grandTotal", array($this, "get_product_grandTotal"));

            add_action("wp_ajax_get_payment_method_checkout", array($this, "get_payment_method_checkout"));
            add_action("wp_ajax_nopriv_get_payment_method_checkout", array($this, "get_payment_method_checkout"));

            // For Checkout Field Section
            add_filter('commercioo/cart/fetch/get_items', array($this, 'commercioo_get_item_cart'), 10);

            add_action("commercioo/checkout/before/form", array($this, "before_checkout_form"));
            add_action("commercioo/checkout/header", array($this, "checkout_header"), 10);
            add_action("commercioo/checkout/field/form/billing", array($this, "checkout_billing_field"), 10, 2);
            add_action("commercioo/checkout/field/before/shipping", array($this, "checkout_before_shipping"));
            add_action("commercioo/checkout/field/form/shipping", array($this, "checkout_shipping_field"), 10);
            add_action("commercioo/checkout/field/order_note", array($this, "checkout_order_note"), 10, 2);

            add_filter("commercioo/checkout/display/expedition/shipping", array($this, "checkout_shipping_list"), 10, 2);
            add_filter("commercioo/checkout/display/product/item", array($this, "checkout_product_item"), 10);
            add_filter("commercioo/checkout/get/product_id", array($this, "checkout_get_product_id"), 10);
            add_action("commercioo/checkout/field/order/total", array($this, "checkout_display_order_total"), 10);
            add_filter("commercioo/checkout/order/sub_total", array($this, "checkout_order_sub_total"), 10);

            add_filter("commercioo/checkout/get/cart", array($this, "checkout_get_cart"), 10);


        }

        /**
         * Get Valid Product ID
         * @param array $cart_content
         * @return array|array[]|int|string
         */
        public function checkout_get_product_id($cart_content = array())
        {
            $is_valid = 0;
            if ($cart_content) {
                foreach ($cart_content as $k => $item) {
                    $get_stock_status = get_post_meta(intval($k), "_stock_status", true);
                    if ($get_stock_status == 'outofstock') {
                        $is_valid = 0;
                    } else {
                        $is_valid = $k;
                    }
                }
            }
            return $is_valid;
        }

        /**
         * Get Cart before add order
         * @param array $item_cart
         */
        public function checkout_get_cart($item_cart=array()){
            if(isset($_POST)){
                $product_item = array_map("sanitize_post",isset($_POST['product'])?$_POST['product']:'');
                if($product_item){
                    foreach ($product_item as $prod_id => $items){
                        if (preg_match('/[\|\|\'^£$%&*()}{@#~?><>,|=_+¬-]/', $items)) {
                            $data_item=explode("|",$items);
                            $product_id = $data_item[0];
                            $product = comm_get_product( $product_id);
                            $product_qty = $data_item[1];
                            $product_post = get_post($product_id);
                            if ( 'comm_product_var' === $product_post->post_type ) {
                                $prod_id = $product_post->post_parent;
                                $var_id = $product_id;
                                $is_variation = 1;
                            } else {
                                $prod_id = $product_id;
                                $var_id = 0;
                                $is_variation = 0;
                            }
                            $item_cart['items'][$product_id] = array(
                                'item_name' => $product->get_title(),
                                'price' => $product->get_regular_price(),
                                'sales_price' => $product->get_sale_price(),
                                'item_order_qty' => $product_qty,
                                'product_id' => $prod_id,
                                "variation_id"=>$var_id,
                                "is_variation"=>$is_variation
                            );
                        }
                    }
                    $item_cart['subtotal']= \Commercioo\Cart::get_subtotal();
                    $item_cart['grand_total']= \Commercioo\Cart::get_total();
                }
            }
            return $item_cart;
        }
        /**
         * Display Product Item
         * @param array $cart_content
         * @return array|array[]|int|string
         */
        public function checkout_product_item($cart_content = array())
        {
            if ($cart_content):
                ?>
                <div class="commercioo-checkout-summary-item-wrapper">
                    <div class="commercioo-checkout-summary-item">
                        <?php foreach ($cart_content as $k => $item) : ?>
                            <?php $product = comm_get_product($k); ?>
                            <?php $get_stock_status = get_post_meta(intval($k), "_stock_status", true);
                            if ($get_stock_status == 'outofstock') {
                                return $k;
                            } ?>
                            <div class="summary-item-single">
                                <div class="product-label">
                                    <input type="hidden" name="product[]" value="<?php echo esc_html($k."|".$item['qty']);?>">
                                    <label class="label-item-product"><?php echo esc_html($product->get_title()); ?></label>
                                    <span class="product-quantity"><?php echo esc_html($item['qty']); ?></span>
                                </div>
                                <div class="price-label">
                                    <?php
                                     if ( $product->is_on_sale() ) {
                                         ?>
                                    <label class="label-item-product">
                                        <?php echo wp_kses_post('<del>' . \Commercioo\Helper::formatted_currency( $product->get_regular_price() * $item['qty'] ) . '</del> ');?>
                                    </label>
                                         <label class="label-item-product">
                                             <?php echo esc_html(\Commercioo\Helper::formatted_currency( $product->get_price() * $item['qty'] )); ?>
                                         </label>
                                             <?php
                                     }else{
                                    ?>
                                    <label class="label-item-product">
                                        <?php echo esc_html(\Commercioo\Helper::formatted_currency( $product->get_regular_price() * $item['qty'] )); ?>
                                    </label>
                                     <?php
                                     }
                                     ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php
            endif;
        }

        /**
         * Get item cart
         * @param array $params
         * @param array $content
         * @return array|array[]
         */
        public function commercioo_get_item_cart($content=array())
        {

            if(!is_page_has_elementor() && function_exists("is_page_has_elementor")){
                $content = \Commercioo\Cart::get_items();
            }else{
                $product_to_checkout = apply_filters('commercioo/elementor/checkout/get_elementor_data_value', array());

                if($product_to_checkout){
                    $content = $product_to_checkout;
                }else{
                    $content = \Commercioo\Cart::get_items();
                }
            }
            return $content;
        }

        /**
         * Hook Action: commercioo/checkout/order/sub_total
         * Display Order Total - Checkout
         * @param int $subtotal
         * @return float|int
         */
        public function checkout_order_sub_total($subtotal=0)
        {
            $cart_content = apply_filters("commercioo/cart/fetch/get_items",array());
            if($cart_content){
                foreach ($cart_content as $k => $val){
                    $subtotal += $val['qty'] * $val['price'];
                }
            }
            return $subtotal;
        }
        /**
         * Hook Action: commercioo/checkout/field/order/total
         * Display HTML Order Total - Checkout
         */
        public function checkout_display_order_total()
        {
            ?>
            <div class="commercioo-checkout-summary-item-wrapper produk-grandtotal-wrapper total">
                <div class="commercioo-checkout-summary-item produk-grandtotal">
                    <div class="summary-item-single">
                        <div class="total-label">
                            <label class="label-item-product"><?php esc_html_e('TOTAL', 'commercioo') ?></label>
                        </div>
                        <div class="total-price">
                            <input type="hidden" name="product_total" value="<?php echo esc_attr(\Commercioo\Cart::get_total());?>">
                            <label class="label-item-product grand_total">
                                <?php echo esc_html(esc_html(\Commercioo\Helper::formatted_currency(\Commercioo\Cart::get_total()))); ?>
                            </label>
                        </div>
                    </div>
                </div>
            </div>
            <?php
        }

        /**
         * Display Shipping List
         * @param array $shipping_option
         * @param bool $free_shipping
         */
        public function checkout_shipping_list($shipping_option = array(), $free_shipping = false)
        {
            ob_start();
            if ($free_shipping) {
                ?>
                <div class="commercioo-checkout-summary-item-wrapper">
                    <div class="commercioo-checkout-summary-item">
                        <div class="summary-item-single">
                            <div class="shipping-label">
                                <label class="label-item-product"><?php esc_html_e('Shipping', 'commercioo') ?></label>
                            </div>
                            <div class="shipping-price" id="checkout-shipping-options">
                                <label class="d-flex align-items-center shipping-option">
                                    <input name='shipping_cost' type='radio' checked data-price="0"
                                           value='<?php echo esc_attr('Free Shipping|0') ?>'> <span
                                            class="shipping-text"><?php echo esc_html('Free Shipping') ?></span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                <?php
            } else {
                ?>
                <div class="summary-item-single">
                    <div class="shipping-label">
                        <label class="label-item-product"><?php esc_html_e('Shipping', 'commercioo') ?></label>
                    </div>
                    <div class="shipping-price" id="checkout-shipping-options">
                        <?php if ($shipping_option): ?>
                            <?php foreach ($shipping_option as $k => $c): ?>
                                <label class='comm-cost-option'>
                                    <input name='shipping_cost' type='radio' data-price="<?php echo esc_attr(base64_encode($c['amount']));?>"
                                           value='<?php echo esc_attr($c['name'] . '|' . $c['amount']) ?>'> <span
                                            class="shipping-text"><?php echo esc_html($c['name']) ?>: </span><span><?php echo esc_html($c['amount_html']) ?></span>
                                </label>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <?php \Commercioo\Cart::remove_shipping(); ?>
                            <label><?php esc_html_e('Complete the address to get shipping options', 'commercioo') ?></label>
                        <?php endif; ?>
                    </div>
                </div>
                <?php

            }
            $content = ob_get_clean();
            return $content;
        }

        /**
         * Display Order Note - Checkout Form
         * @param array $order_note_field
         * @param array $settings
         */
        public function checkout_order_note($order_note_field = array(), $settings = array())
        {
            if (isset($settings['checkout_form_note_visibility']) && $settings['checkout_form_note_visibility'] != 'hidden') {
                ?>
                <div class="clearfix mt-3 d-flex">
                    <div class="notes-wrap">
                        <label class="notes-title">
                            <?php echo esc_html($settings['checkout_form_note_label']); ?>
                            <?php if ($settings['checkout_form_note_visibility'] === 'required') :
                                $order_note_field ['required'] = true;
                                ?>
                                <span class="text-danger-custom">*</span>
                            <?php else: ?>
                                <?php $order_note_field ['required'] = false; ?>
                            <?php endif; ?>
                        </label>
                        <div class="textarea_field">
                            <?php \Commercioo\Checkout::render_field($order_note_field, 'order_notes', ''); ?>
                        </div>
                    </div>
                </div>
                <?php
            } else if (isset($settings['order_note_visibility']) && $settings['order_note_visibility'] == true) {
                ?>
                <div class="row clearfix">
                    <div style="margin-top: 20px;">
                        <div class="label-title list-column-right">
                            <!-- label -->
                            <?php
                            if (isset($settings['order_note_label'])) {
                                $label = $settings['order_note_label'];
                            } else {
                                $label = __('ORDER NOTES', 'commercioo');
                            }

                            echo esc_html($label);
                            ?>
                        </div>
                        <div class="textarea_field">
                            <?php \Commercioo\Checkout::render_field($order_note_field, 'order_notes', ''); ?>
                        </div>
                    </div>
                </div>
                <?php
            }
        }

        /**
         * Display ship_to_different_address_visibility checkbox
         * @param array $settings
         */
        public function checkout_before_shipping($settings = array())
        {
            if (!isset($settings['ship_to_different_address_visibility']) || $settings['ship_to_different_address_visibility'] == true) :
                if (isset($settings['ship_to_different_address_visibility']) && $settings['ship_to_different_address_visibility'] == 'visible') {
                    ?>
                    <div class="clearfix">
                        <div class="shipping-wrap">
                            <input type="checkbox" class="checkbox-shipping" id="checkbox_ship_different"
                                   name="ship_to_different_address" value='on'>
                            <label class="label-shipping" for="checkbox_ship_different">
                                <?php
                                if (isset($settings['checkout_form_shipping_title'])) {
                                    $label = $settings['checkout_form_shipping_title'];
                                } else {
                                    if (isset($settings['ship_to_different_address_label'])) {
                                        $label = $settings['ship_to_different_address_label'];
                                    } else {
                                        $label = __('SHIP TO DIFFERENT ADDRESS', 'commercioo');
                                    }

                                }
                                echo esc_html($label);
                                ?>
                            </label>
                        </div>
                    </div>
                    <?php
                }
            endif;
        }

        /**
         * Display the notification if have no cart items, for customizer view
         */
        public function before_checkout_form()
        {
            if (\Commercioo\Cart::is_empty()) : ?>
                <div class='checkout-has-no-cart-notification'><?php esc_html_e('Please add any product to cart to see the checkout page in action', 'commercioo') ?></div>
            <?php endif; ?>
            <?php
            if (isset($_GET['msg']) && !empty($_GET['msg'])) {
                echo wp_kses_post("<div class='checkout-has-no-cart-notification'>" . sanitize_text_field($_GET['msg']) . "</div>");
            }
        }

        /**
         * Display header content
         */
        public function checkout_header()
        {
            global $comm_options;
            // shop logo
            if (isset($comm_options['store_logo'])) {
                $thumb_id = intval($comm_options['store_logo']);
                $thumb = wp_get_attachment_image_src($thumb_id, 'full');
                $logo_url = $thumb ? $thumb[0] : COMMERCIOO_URL . 'img/commercioo-logo.svg';
            } else {
                $logo_url = COMMERCIOO_URL . 'img/commercioo-logo.svg';
            }
            if ($logo_url) {
                ?>
                <div class="commercioo-checkout-logo">
                    <img src="<?php echo esc_url($logo_url) ?>" alt="<?php esc_attr_e('Commercioo', 'commercioo') ?>">
                </div>
                <?php
            }
        }

        /**
         * Display shipping field - Checkout Form
         */
        public function checkout_shipping_field()
        {
            ?>
            <div class='show-form-ship-different' id="show-form-ship-different"></div>
            <?php
        }

        /**
         * Display billing field - Checkout Form
         * @param array $billing_fields
         * @param array $settings
         */
        public function checkout_billing_field($billing_fields = array(), $settings = array())
        {
            global $comm_options;
            $user_id = get_current_user_id();
            $customer = new \Commercioo\Models\Customer($user_id);
            $customer_billing = $customer->get_billing_address();
            if ($billing_fields) {
                foreach ($billing_fields as $key => $field) :
                    if (!$settings) {
                        ?>
                        <div class="comm-checkout-billing-<?php echo esc_attr($key) ?>">
                            <label>
                                <?php echo esc_html($field['label']); ?>
                                <?php if ($field['required']) : ?>
                                    <span class="text-danger-custom">*</span>
                                <?php endif; ?>
                            </label>
                            <div class="input_field">
                                <?php
                                $value = sanitize_text_field(isset($customer_billing[$key]) ? $customer_billing[$key] : '');
                                if (empty($value)) {
                                    if (!empty($user_id) && ('first_name' === $key || 'last_name' === $key)) {
                                        $value = get_user_meta($user_id, $key, true);
                                    } else if ('country' === $key && isset($comm_options['store_country'])) {
                                        $value = sanitize_text_field($comm_options['store_country']);
                                    }
                                }
                                $value = apply_filters('commercioo_checkout_field_value', $value, 'billing_' . $key);
                                \Commercioo\Checkout::render_field($field, "billing_address[billing_{$key}]", $value);
                                ?>
                            </div>
                        </div>
                    <?php } else { ?>
                        <?php if (!\Elementor\Plugin::$instance->editor->is_edit_mode() || isset($settings['checkout_form_' . $key . '_visibility'])) {
                            if (!isset($settings['checkout_form_' . $key . '_visibility']) || $settings['checkout_form_' . $key . '_visibility'] != 'hidden') : ?>
                                <div class="comm-checkout-billing-<?php echo esc_attr($key) ?>">
                                    <label>
                                        <?php
                                        $field_label = sanitize_text_field((isset($settings['checkout_form_' . $key . '_label'])) ? $settings['checkout_form_' . $key . '_label'] : $settings['checkout_form_' . $key . '_id_label']);
                                        echo esc_html($field_label);
                                        if (isset($settings['checkout_form_' . $key . '_visibility']) && $settings['checkout_form_' . $key . '_visibility'] == 'required') : ?>
                                            <span class="text-danger-custom">*</span>
                                        <?php endif; ?>
                                    </label>
                                    <div class="input_field">
                                        <?php
                                        $value = sanitize_text_field(isset($customer_billing[$key]) ? $customer_billing[$key] : '');
                                        if (empty($value)) {
                                            if (!empty($user_id) && ('first_name' === $key || 'last_name' === $key)) {
                                                $value = get_user_meta($user_id, $key, true);
                                            } else if ('country' === $key && isset($comm_options['store_country'])) {
                                                $value = sanitize_text_field($comm_options['store_country']);
                                            }
                                        }
                                        $value = apply_filters('commercioo_checkout_field_value', $value, 'billing_' . $key);
                                        \Commercioo\Checkout::render_field($field, "billing_address[billing_{$key}]", $value);
                                        ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                        <?php } ?>
                    <?php } ?>
                <?php endforeach;
            }
        }

        public function get_payment_method_checkout()
        {
            global $comm_options;
            $available = true;
            $is_available = array();
            $response = [];
            $payment_method_list = [];
            $result = [];
            if (isset($_GET)) {
                check_ajax_referer('wp_rest', 'commercioo_nonce');
                if (isset($comm_options['payment_option']) && count($comm_options['payment_option']) > 0) {
                    $payment_option = $comm_options['payment_option'];
                    foreach ($payment_option as $key_po => $val) {
                        $is_available[$key_po] = apply_filters("commercioo/payment/payment-options/{$key_po}", array());
                    }
                }
                $is_available = array_filter($is_available);
                if (!$is_available) {
                    $available = false;
                }

                foreach ($is_available as $k => $payment_method) {
                    if (has_filter("commercioo/display/payment-method/$k")) {
                        $payment_method_list = array_merge($payment_method_list, apply_filters("commercioo/display/payment-method/$k", ''));
                    }
                }
            }
            $response = json_encode(array("is_available" => $available, "list_payment_method" => $payment_method_list
            ));
            echo wp_kses_data($response);
            wp_die();
        }

        public function get_product_grandTotal()
        {
            $response = array();
            if (isset($_POST)) {
                $response = json_encode(array(
                    'grandtotal' => esc_html(\Commercioo\Helper::formatted_currency(\Commercioo\Cart::get_total()))
                ));
            }
            echo wp_kses_data($response);
            wp_die();
        }

        public function get_product_checkout()
        {
            $product = array();
            $fees = array();
            if (isset($_POST)) {
                check_ajax_referer('wp_rest', 'commercioo_nonce');
                if (isset($_POST['cart']) && !empty($_POST['cart'])) {
                    $cart = sanitize_post(json_decode(base64_decode($_POST['cart']), true));
                    $cart_content = \Commercioo\Cart::get_items($cart);
                } else {
                    if (defined('ELEMENTOR_VERSION') && \Elementor\Plugin::$instance->editor->is_edit_mode()) {
                        $args = array(
                            'post_type' => 'comm_product',
                            'post_status' => 'publish',
                            'post_per_page' => 2,
                        );


                        $cart_content = array();
                        $the_product = new \WP_Query($args);
                        while ($the_product->have_posts()) {
                            $the_product->the_post();

                            $single_product = comm_get_product(get_the_ID());
                            $cart_content = array(
                                get_the_ID() => array(
                                    'name' => get_the_title(),
                                    'qty' => 1,
                                    'price' => $single_product->get_price()
                                ),
                            );
                        }
                    } else {
                        $cart_content = \Commercioo\Cart::get_items();
                    }
                }

                foreach ($cart_content as $product_id => $item) {
                    $list_product = comm_get_product($product_id);
                    $product[] = array('product_id' => esc_attr($list_product->get_product_id()), 'product_title' => esc_html($list_product->get_title()), 'product_qty' => esc_html($item['qty']), "product_price" => esc_html(\Commercioo\Helper::formatted_currency($item['qty'] * $list_product->get_regular_price())));
                }
                if (\Commercioo\Cart::has_fees() && defined("COMMERCIOO_PRO_PATH")) {
                    foreach (\Commercioo\Cart::get_fees() as $fee) {
                        $fees[] = array(
                            'name' => esc_html($fee['name']),
                            'amount' => esc_html(\Commercioo\Helper::formatted_currency($fee['amount'])),
                        );
                    }
                }


            }
            $response = json_encode(array("product" => $product,
                'subtotal' => esc_html(\Commercioo\Helper::formatted_currency(\Commercioo\Cart::get_subtotal())),
                'grandtotal' => esc_html(\Commercioo\Helper::formatted_currency(\Commercioo\Cart::get_total())),
                'fees' => $fees,
                'discount' => (\Commercioo\Cart::has_discounts() && defined("COMMERCIOO_PRO_PATH") ? esc_html(\Commercioo\Helper::formatted_currency(\Commercioo\Cart::get_discount_total())) : '')
            ));
            echo wp_kses_data($response);
            wp_die();
        }

        /**
         * Get class instance
         *
         * @return Checkout Class instance.
         */
        public static function get_instance()
        {
            if (!isset(self::$instance)) {
                self::$instance = new Checkout();
            }
            return self::$instance;
        }

        /**
         * Set default fields
         */
        public function set_default_fields()
        {
            $field = apply_filters("commercioo_ongkir_checkout_default_fields", $this->default_fields());
            $this->default_fields = $field;
        }

        /**
         * Defined default fields
         */
        public function default_fields()
        {
            global $comm_country;
            $default_fields = array(
                'billing' => array(
                    'first_name' => array(
                        'label' => __('First Name', 'commercioo'),
                        'type' => 'text',
                        'required' => true,
                        'priority' => 10,
                        'attrs' => array(
                            'minlength' => 2
                        )
                    ),
                    'last_name' => array(
                        'label' => __('Last Name', 'commercioo'),
                        'type' => 'text',
                        'required' => false,
                        'priority' => 20
                    ),
                    'email' => array(
                        'label' => __('Email Address', 'commercioo'),
                        'type' => 'email',
                        'required' => true,
                        'priority' => 30
                    ),
                    'phone' => array(
                        'label' => __('Phone Number', 'commercioo'),
                        'type' => 'text',
                        'required' => true,
                        'priority' => 40,
                        'attrs' => array(
                            'minlength' => 5
                        )
                    ),
                    'company' => array(
                        'label' => __('Company', 'commercioo'),
                        'type' => 'text',
                        'required' => false,
                        'priority' => 50
                    ),
                    'country' => array(
                        'label' => __('Country', 'commercioo'),
                        'type' => 'select',
                        'options' => $comm_country,
                        'required' => false,
                        'priority' => 60
                    ),
                    'state' => array(
                        'label' => __('State / Province', 'commercioo'),
                        'type' => 'text',
                        'required' => false,
                        'priority' => 70
                    ),
                    'city' => array(
                        'label' => __('Town / City', 'commercioo'),
                        'type' => 'text',
                        'required' => false,
                        'priority' => 80
                    ),
                    'zip' => array(
                        'label' => __('Postcode / ZIP', 'commercioo'),
                        'type' => 'text',
                        'required' => false,
                        'priority' => 90
                    ),
                    'street_address' => array(
                        'label' => __('Street Address', 'commercioo'),
                        'type' => 'text',
                        'required' => false,
                        'priority' => 100
                    ),
                ),
                'shipping' => array(
                    'first_name' => array(
                        'label' => __('First Name', 'commercioo'),
                        'type' => 'text',
                        'required' => true,
                        'priority' => 10,
                        'attrs' => array(
                            'minlength' => 2
                        )
                    ),
                    'last_name' => array(
                        'label' => __('Last Name', 'commercioo'),
                        'type' => 'text',
                        'required' => false,
                        'priority' => 20
                    ),
                    'email' => array(
                        'label' => __('Email Address', 'commercioo'),
                        'type' => 'email',
                        'required' => true,
                        'priority' => 30
                    ),
                    'phone' => array(
                        'label' => __('Phone Number', 'commercioo'),
                        'type' => 'text',
                        'required' => true,
                        'priority' => 40,
                        'attrs' => array(
                            'minlength' => 5
                        )
                    ),
                    'company' => array(
                        'label' => __('Company', 'commercioo'),
                        'type' => 'text',
                        'required' => false,
                        'priority' => 50
                    ),
                    'country' => array(
                        'label' => __('Country', 'commercioo'),
                        'type' => 'select',
                        'options' => $comm_country,
                        'required' => false,
                        'priority' => 60
                    ),
                    'state' => array(
                        'label' => __('State / Province', 'commercioo'),
                        'type' => 'text',
                        'required' => false,
                        'priority' => 70
                    ),
                    'city' => array(
                        'label' => __('Town / City', 'commercioo'),
                        'type' => 'text',
                        'required' => false,
                        'priority' => 80
                    ),
                    'zip' => array(
                        'label' => __('Postcode / ZIP', 'commercioo'),
                        'type' => 'text',
                        'required' => false,
                        'priority' => 90
                    ),
                    'street_address' => array(
                        'label' => __('Street Address', 'commercioo'),
                        'type' => 'text',
                        'required' => false,
                        'priority' => 100
                    ),
                ),
                'ship_to_different_address' => array(
                    'label' => __('SHIP TO DIFFERENT ADDRESS', 'commercioo'),
                    'type' => 'checkbox',
                    'required' => false,
                    'attrs' => array(
                        'class' => 'checkbox-shipping',
                        'id' => 'checkbox_ship_different'
                    )
                ),
                'order_notes' => array(
                    'label' => __('ORDER NOTES', 'commercioo'),
                    'type' => 'textarea',
                    'required' => false,
                    'attrs' => array(
                        'rows' => 3
                    )
                )
            );
            return $default_fields;
        }

        /**
         * Get default fields
         *
         * @param boolean|string $index Default index.
         * @return array                 Field default.
         */
        public function get_default_fields($index = false)
        {
            $this->default_fields();
            $this->set_default_fields();
            if ($index && isset($this->default_fields[$index])) {
                if ('billing' === $index || 'shipping' === $index) {
                    uasort($this->default_fields[$index], array($this, 'sort_field_by_priority'));
                }
                return $this->default_fields[$index];
            }
            return $this->default_fields;
        }

        /**
         * Get billing fields
         *
         * @return array Default billing fields.
         */
        public function get_default_billing_fields()
        {
            $fields = array();

            // sort field based on priority.
            uasort($this->default_fields['billing'], array($this, 'sort_field_by_priority'));

            // get settings.
            $order_forms = get_option('comm_order_forms_settings', array());
            $billing = isset($order_forms['billing_address']) ? $order_forms['billing_address'] : array();

            foreach ($this->default_fields['billing'] as $key => $field) {
                $fields[$key] = $field;
            }

            return $fields;
        }

        /**
         * Get shipping fields
         *
         * @return array Default shipping fields.
         */
        public function get_default_shipping_fields()
        {
            $fields = array();

            // sort field based on priority.
            uasort($this->default_fields['shipping'], array($this, 'sort_field_by_priority'));

            // get settings.
            $order_forms = get_option('comm_order_forms_settings', array());
            $billing = isset($order_forms['billing_address']) ? $order_forms['billing_address'] : array();

            foreach ($this->default_fields['shipping'] as $key => $field) {
                $fields[$key] = $field;
            }

            return $fields;
        }

        /**
         * Get billing fields
         *
         * @return array Active billing fields.
         */
        public function get_billing_fields()
        {
            $fields = array();

            // sort field based on priority.
            uasort($this->default_fields['billing'], array($this, 'sort_field_by_priority'));

            // get settings.
            $order_forms = get_option('comm_order_forms_settings', array());
            $billing = isset($order_forms['billing_address']) ? $order_forms['billing_address'] : array();

            foreach ($this->default_fields['billing'] as $key => $field) {
                $visibility = isset($billing["billing_{$key}_visibility"]) ? $billing["billing_{$key}_visibility"] : 'required';
                if ($field['required'] || 'hidden' !== $visibility) {
                    $fields[$key] = $field;

                    // override label from setting.
                    if (isset($billing["billing_{$key}"]) && !empty($billing["billing_{$key}"])) {
                        $fields[$key]['label'] = $billing["billing_{$key}"];
                    }

                    // override required from setting.
                    $fields[$key]['required'] = $field['required'] || 'required' === $visibility;
                }
            }

            return $fields;
        }

        /**
         * Get shipping fields
         *
         * @return array Active shipping fields.
         */
        public function get_shipping_fields()
        {
            $fields = array();

            // sort field based on priority.
            uasort($this->default_fields['shipping'], array($this, 'sort_field_by_priority'));

            // get settings.
            $order_forms = get_option('comm_order_forms_settings', array());
            $billing = isset($order_forms['billing_address']) ? $order_forms['billing_address'] : array();

            foreach ($this->default_fields['shipping'] as $key => $field) {
                $visibility = isset($billing["billing_{$key}_visibility"]) ? $billing["billing_{$key}_visibility"] : 'required';
                if ($field['required'] || 'hidden' !== $visibility) {
                    $fields[$key] = $field;

                    // override label from setting.
                    if (isset($billing["billing_{$key}"]) && !empty($billing["billing_{$key}"])) {
                        $fields[$key]['label'] = $billing["billing_{$key}"];
                    }

                    // override required from setting.
                    $fields[$key]['required'] = $field['required'] || 'required' === $visibility;
                }
            }

            return $fields;
        }

        /**
         * Get order note field
         *
         * @return array Order note field.
         */
        public function get_order_note_field()
        {
            $field = $this->default_fields['order_notes'];

            // get settings.
            $order_forms = get_option('comm_order_forms_settings', array());

            $visibility = isset($order_forms["order_note_visibility"]) ? $order_forms["order_note_visibility"] : 'optional';
            if ($field['required'] || 'hidden' !== $visibility) {
                // override label from setting.
                if (isset($order_forms["order_note_label"]) && !empty($order_forms["order_note_label"])) {
                    $field['label'] = $order_forms["order_note_label"];
                }

                // override required from setting.
                $field['required'] = $field['required'] || 'required' === $visibility;
            } else {
                return false;
            }

            return $field;
        }

        /**
         * Render the field
         *
         * @param array $args Field arguments.
         * @param string $name Field name.
         * @param string $value Field value.
         */
        public static function render_field($args, $name = '', $value = '')
        {
            switch ($args['type']) {
                case 'text':
                case 'email':
                case 'url':
                case 'number':
                case 'password':
                    echo '<input type="' . esc_attr($args['type']) . '" name="' . esc_attr($name) . '" placeholder="' . esc_attr($args['label']) . '" ';
                    if (isset($args['attrs']) && is_array($args['attrs'])) {
                        foreach ($args['attrs'] as $attr_key => $attr_value) {
                            echo wp_kses_post($attr_key . '="' . esc_attr($attr_value) . '" ');
                        }
                    }
                    if ($value) {
                        echo wp_kses_post('value="' . esc_attr($value) . '" ');
                    } elseif (isset($args['value'])) {
                        echo wp_kses_post('value="' . esc_attr($args['value']) . '" ');
                    }
                    if ($args['required']) {
                        echo wp_kses_post('required ');
                    }
                    echo '/>';
                    break;

                case 'textarea':
                    echo '<textarea name="' . esc_attr($name) . '" placeholder="' . esc_attr($args['label']) . '" ';
                    if (isset($args['attrs']) && is_array($args['attrs'])) {
                        foreach ($args['attrs'] as $attr_key => $attr_value) {
                            echo wp_kses_post($attr_key . '="' . esc_attr($attr_value) . '" ');
                        }
                    }
                    if ($args['required']) {
                        echo wp_kses_post('required ');
                    }
                    echo '>';
                    if ($value) {
                        echo 'value="' . esc_textarea($value) . '" ';
                    } elseif (isset($args['value'])) {
                        echo 'value="' . esc_textarea($args['value']) . '" ';
                    }
                    echo '</textarea>';
                    break;

                case 'select':
                    echo '<select name="' . esc_attr($name) . '" ';
                    if (isset($args['attrs']) && is_array($args['attrs'])) {
                        foreach ($args['attrs'] as $attr_key => $attr_value) {
                            echo wp_kses_post($attr_key . '="' . esc_attr($attr_value) . '" ');
                        }
                    }
                    if ($args['required']) {
                        echo 'required ';
                    }
                    echo '>';
                    if (empty($value) && isset($args['value'])) {
                        $value = $args['value'];
                    }
                    if (isset($args['options']) && is_array($args['options'])) {
                        foreach ($args['options'] as $option_key => $option_label) {
                            ?>
                            <option value="<?php echo esc_attr($option_key); ?>" <?php selected($option_key, $value); ?>><?php echo esc_html($option_label); ?></option>
                            <?php
                        }
                    }
                    echo '</select>';
                    break;
            }
        }

        /**
         * Submit actions for checkout form.
         */
        public static function do_checkout()
        {
            global $comm_options;

            // check nonce
            check_admin_referer('GwJpuj_HVaV604dHE', '_comm_checkout_nonce');

            /**
             * Checkout validation
             * Mainly now used by SaaS to limit the checkout number per month
             */
            do_action('commercioo_validate_checkout');

            // validate fields
            self::validate_checkout_data();

            /**
             * Billing and shipping addresses
             * ba as billing_address
             * sa as shipping_address
             */
            $ba = array();
            if (isset($_POST['billing_address']) && is_array($_POST['billing_address'])) {
                $ba = sanitize_post($_POST['billing_address']);
            }
            $sa = array();
            if (isset($_POST['shipping_address']) && is_array($_POST['shipping_address'])) {
                $sa = sanitize_post($_POST['shipping_address']);
            }
            // get billing address
            $billing_address = array(
                'billing_first_name' => sanitize_text_field(isset($ba['billing_first_name']) ? $ba['billing_first_name'] : null),
                'billing_last_name' => sanitize_text_field(isset($ba['billing_last_name']) ? $ba['billing_last_name'] : null),
                'billing_email' => sanitize_email(isset($ba['billing_email']) ? $ba['billing_email'] : null),
                'billing_phone' => sanitize_text_field(isset($ba['billing_phone']) ? $ba['billing_phone'] : null),
                'billing_company' => sanitize_text_field(isset($ba['billing_company']) ? $ba['billing_company'] : null),
                'billing_country' => sanitize_text_field(isset($ba['billing_country']) ? $ba['billing_country'] : null),
                'billing_street_address' => sanitize_text_field(isset($ba['billing_street_address']) ? $ba['billing_street_address'] : null),
                'billing_city' => sanitize_text_field(isset($ba['billing_city']) ? $ba['billing_city'] : null),
                'billing_state' => sanitize_text_field(isset($ba['billing_state']) ? $ba['billing_state'] : null),
                'billing_zip' => sanitize_text_field(isset($ba['billing_zip']) ? $ba['billing_zip'] : null),
            );
            $shipping_address = array();
            // get shipping address
            if (isset($_POST['ship_to_different_address']) && $_POST['ship_to_different_address'] == 'on') {
                $shipping_address = array(
                    'shipping_first_name' => sanitize_text_field(isset($sa['shipping_first_name']) ? $sa['shipping_first_name'] : null),
                    'shipping_last_name' => sanitize_text_field(isset($sa['shipping_last_name']) ? $sa['shipping_last_name'] : null),
                    'shipping_email' => sanitize_email(isset($sa['shipping_email']) ? $sa['shipping_email'] : null),
                    'shipping_phone' => sanitize_text_field(isset($sa['shipping_phone']) ? $sa['shipping_phone'] : null),
                    'shipping_company' => sanitize_text_field(isset($sa['shipping_company']) ? $sa['shipping_company'] : null),
                    'shipping_country' => sanitize_text_field(isset($sa['shipping_country']) ? $sa['shipping_country'] : null),
                    'shipping_street_address' => sanitize_text_field(isset($sa['shipping_street_address']) ? $sa['shipping_street_address'] : null),
                    'shipping_city' => sanitize_text_field(isset($sa['shipping_city']) ? $sa['shipping_city'] : null),
                    'shipping_state' => sanitize_text_field(isset($sa['shipping_state']) ? $sa['shipping_state'] : null),
                    'shipping_zip' => sanitize_text_field(isset($sa['shipping_zip']) ? $sa['shipping_zip'] : null),
                );
            } else {
                /**
                 * Copy the billing address to shipping address
                 * but we need to replace keys billing to shipping
                 */
                foreach ($billing_address as $key => $value) {
                    $shipping_key = str_replace('billing_', 'shipping_', $key);
                    $shipping_address[$shipping_key] = $value;
                }
            }

            $product_item = apply_filters("commercioo/checkout/get/cart",array());

            if(!$product_item){
                wp_die(__('Your cart have no item to checkout', 'commercioo'), __('Error!', 'commercioo'), array(
                    'back_link' => false,
                    'link_url' => comm_get_shopping_uri(),
                    'link_text' => __('Go to Shop now', "commercioo")
                ));
            }

            // order_items
            $order_items = array();
            // normalize cart data structure as REST API needed
            foreach ($product_item['items'] as $prod_id => $item) {
                $product_id = $prod_id;
                $product_qty = $item['item_order_qty'];
                $order_items[] = array(
                    'product_id' => intval($product_id),
                    'item_order_qty' => intval($product_qty),
                );
                $get_stock_status = get_post_meta(intval($product_id), "_stock_status", true);
                if ($get_stock_status == 'outofstock') {
                    wp_die(get_the_title(intval($product_id)) . '. ' . __('This item is out of stock', 'commercioo'), __('Error!', 'commercioo'), array(
                        'back_link' => true,
                    ));
                }
            }

            // get current user_id
            if (!$user_id = get_current_user_id()) {
                if (function_exists('comm_do_auto_register')) {
                    $user_id = comm_do_auto_register($ba, $sa, 'comm_pending');
                } else {
                    wp_die(
                        __('Sorry, but you must be logged-in to create an order', 'commercioo'),
                        __('Error!', 'commercioo'), array(
                            'back_link' => true,
                        )
                    );
                }
            }
            if ($_POST['payment_method'] == "paypal" && sanitize_text_field($comm_options['currency']) == "IDR") {
                wp_die(
                    __('Sorry, your current currency IDR does not support to process Paypal', 'commercioo'),
                    __('Error!', 'commercioo'), array(
                        'back_link' => true,
                    )
                );
            }
            $customers_id = sanitize_text_field(isset($_POST['customers_id']) ? intval($_POST['customers_id']) : null);
            $customer = new \Commercioo\Models\Customer($user_id, $customers_id);
            $customer_id = $customer->set_customer($billing_address);
            // set order data to save
            $payment_method = sanitize_post(isset($_POST['payment_method']) ? $_POST['payment_method'] : 'bacs');
            $payment_method_for_name = $payment_method;
            $post_title_name = "bacs";
            if (strpos($payment_method, 'TRIPAY') !== false) {
                $payment_method_for_name = str_replace("TRIPAY_", "", $payment_method);
                $post_title_name = "tripay";
            }
            $order_data = apply_filters('commercioo_order_data_to_submit', array(
                'user_id' => $user_id,
                'status' => 'comm_pending',
                'billing_address' => $billing_address,
                'shipping_address' => $shipping_address,
                'payment_method' => $payment_method,
                'payment_method_name' => sanitize_post(isset($_POST['payment_method_name'][$payment_method_for_name]) ? $_POST['payment_method_name'][$payment_method_for_name] : 'bacs'),
                'order_notes' => sanitize_textarea_field(isset($_POST['order_notes']) ? $_POST['order_notes'] : ''),
                'order_items' => $product_item['items'],
                'order_currency' => $comm_options['currency'],
            ));

            $arg_order ['post_author'] = $user_id;
            if ($payment_method == "paypal") {
                $post_title_name = "paypal";
            }
            $arg_order ['post_title'] = sprintf("comm_order_{$post_title_name}_%s", uniqid());
            $arg_order ['post_excerpt'] = $order_data['order_notes'];
            $arg_order ['post_status'] = $order_data['status'];
            $arg_order ['post_type'] = 'comm_order';

            if (isset($_POST['order_id']) && !empty($_POST['order_id'])) {
                $order_id = sanitize_text_field(absint($_POST['order_id']));
                $arg_order ['ID'] = $order_id;
                wp_update_post($arg_order);
            } else {
                // create order
                $order_id = wp_insert_post($arg_order);
            }
            // bail out on error
            if (is_wp_error($order_id)) {
                $message = $order_id->get_error_message();
                wp_die(sprintf(__('An error occurred: %s', 'commercioo'), $message), __('Error!', 'commercioo'), array(
                    'back_link' => true,
                ));
            }

            $item_cart = $product_item;

            $grand_total = 0;
            if($product_item){
                $grand_total = $product_item['grand_total'] + $product_item['shipping_price'];
            }
            $item_cart['grand_total']=$grand_total;
            // General parameter value for update into post meta
            $args = array(
                '_user_id' => intval($order_data['user_id']),
                '_customer_id' => intval($customer_id),
                '_billing_address' => array_map('esc_html', $order_data['billing_address']),
                '_shipping_address' => array_map('esc_html', $order_data['shipping_address']),
                '_payment_method' => sanitize_text_field($order_data['payment_method']),
                '_payment_method_name' => sanitize_text_field($order_data['payment_method_name']),
                '_order_currency' => sanitize_text_field($order_data['order_currency']),
                '_order_notes' => sanitize_textarea_field($order_data['order_notes']),
                '_order_key' => sanitize_text_field(\Commercioo\Helper::commercioo_generate_order_key()),
                '_order_cart' => $item_cart,
                '_order_total' => apply_filters('comm_order_total_price', $item_cart['grand_total'], $order_id),
                '_order_sub_total' =>  $product_item['subtotal']
            );


            // Process update into post meta
            // already available: discount, fee and unique number
            do_action("commercioo_update_post_meta", $args, $order_id);

            // set order items
            $comm_order = \commercioo\admin\Comm_Order::get_instance();
            $comm_order->set_comm_order_items($order_id, $order_data['order_items']);

            // update user's profile
            $comm_users = \commercioo\admin\Comm_Users::get_instance();

            // update the shipping address just if it has been defined
            if (isset($_POST['ship_to_different_address']) && $_POST['ship_to_different_address'] == 'on') {
                $comm_users->update_users_billing_and_shipping_address(
                    $order_data['user_id'],
                    $order_data['billing_address'],
                    $order_data['shipping_address']
                );
            } else {
                $comm_users->update_users_billing_and_shipping_address(
                    $order_data['user_id'],
                    $order_data['billing_address'],
                    null
                );
            }

            // action after create order
            do_action('commercioo_after_creating_order', $order_id, $order_data['status']);

            // redirect after order
            $order = new \Commercioo\Models\Order($order_id);
            if ($payment_method == "paypal") {
                $return_url = \Commercioo\Helper::commercioo_get_endpoint_url('commercioo-payal', $order_id, comm_get_thank_you_uri());
                $return_url = add_query_arg('key', $order->get_order_key(), $return_url);

                $cancel_return_url = \Commercioo\Helper::commercioo_get_endpoint_url('commercioo-payal-failed', $order_id, comm_get_thank_you_uri());
                $cancel_return_url = add_query_arg('key', $order->get_order_key(), $cancel_return_url);

                $purchase_data['order_id'] = $order_id;
                foreach ($item_cart['items'] as $product_id => $item) {
                    $price = ($item['sales_price']>0)?$item['sales_price']:$item['price'];
                    $purchase_data['product'][$product_id] = array(
                        'name' => sanitize_text_field($item['item_name']),
                        'qty' => intval($item['item_order_qty']),
                        'price' => intval($price),
                    );
                }
                $purchase_data['cart'] = $item_cart;
                $purchase_data['currency_code'] = $comm_options['currency'];

                $purchase_data['redirect_thank_you_page'] = esc_url($return_url);
                $purchase_data['cancel_return'] = esc_url($cancel_return_url);

                $purchase_data['invoice'] = $order->get_order_key();
                $purchase_data['frequency'] = 1;
                $purchase_data['member'] = [
                    'user_id' => $order_data['user_id'],
                    'first_name' => isset($billing_address['billing_first_name']) ? $billing_address['billing_first_name'] : '',
                    'last_name' => '',
                    'email' => isset($billing_address['billing_email']) ? $billing_address['billing_email'] : '',
                ];
                do_action("commercioo/process/{$payment_method}", $order_id, $purchase_data);
            } else {
                $return_url = \Commercioo\Helper::commercioo_get_endpoint_url('commercioo-order-received', $order_id, comm_get_thank_you_uri());
                $return_url = $order_received_url = add_query_arg('key', $order->get_order_key(), $return_url);
                $return_url = apply_filters('commercioo_redirect_url_after_order', $return_url, $order_id);
                wp_redirect($return_url);
                exit;
            }
        }

        /**
         * Mainly used in `do_checkout` method
         */
        private static function validate_checkout_data()
        {
            global $comm_options;

            // args
            $error_messages = array();
            $has_shipping_address = sanitize_text_field(isset($_POST['ship_to_different_address']) && $_POST['ship_to_different_address'] == 'on');
            $billing_fields_status = array('last_name', 'company', 'country', 'street_address', 'city', 'state', 'zip');

            /**
             * Billing and shipping addresses
             * ba as billing_address
             * sa as shipping_address
             * opt_ba as $comm_options['billing_address']
             */
            $ba = array_map("sanitize_text_field", isset($_POST['billing_address']) && is_array($_POST['billing_address']) ? $_POST['billing_address'] : array());
            $sa = array_map("sanitize_text_field", isset($_POST['shipping_address']) && is_array($_POST['shipping_address']) ? $_POST['shipping_address'] : array());
            $opt_ba = array_map("sanitize_text_field", isset($comm_options['billing_address']) && is_array($comm_options['billing_address']) ? $comm_options['billing_address'] : array());

            // validate first_name
            if (
                !isset($ba['billing_first_name']) || strlen(trim($ba['billing_first_name'])) < 2
                || ($has_shipping_address && (!isset($sa['shipping_first_name']) || strlen(trim($sa['shipping_first_name'])) < 2))
            ) {
                $error_messages[] = __('Minimum "First Name" length is 2', 'commercioo');
            }

            // validate email
            if (
                !isset($ba['billing_email']) || !is_email($ba['billing_email'])
                || ($has_shipping_address && (!isset($sa['shipping_email']) || !is_email($sa['shipping_email'])))
            ) {
                $error_messages[] = __('Invalid email address', 'commercioo');
            }

            // validate phone number
            if (
                !isset($ba['billing_phone']) || strlen(trim($ba['billing_phone'])) < 5
                || ($has_shipping_address && (!isset($sa['shipping_phone']) || strlen(trim($sa['shipping_phone'])) < 5))
            ) {
                $error_messages[] = __('Minimum "Phone Number" length is 5', 'commercioo');
            }

            // validate payment method
            if (!isset($_POST['payment_method']) || $_POST['payment_method'] == null) {
                $error_messages[] = __('Invalid payment method', 'commercioo');
            }

            // some fields has their `required` status
            foreach ($billing_fields_status as $field) {
                if (
                    (!isset($opt_ba["billing_{$field}_visibility"]) || $opt_ba["billing_{$field}_visibility"] == 'required')
                    && (
                        !isset($ba["billing_{$field}"]) || strlen(trim($ba["billing_{$field}"])) == 0
                        || ($has_shipping_address && (!isset($sa["shipping_{$field}"]) || strlen(trim($sa["shipping_{$field}"])) == 0))
                    )
                ) {
                    $error_messages[] = sprintf(__('"%s" is required', 'commercioo'), $opt_ba["billing_{$field}"]);
                }
            }

            // bail out for any errors found
            if (count($error_messages) > 0) {
                $wp_die_html = '<h1>' . esc_html__('Errors Found!', 'commercioo') . '</h1>';
                $wp_die_html .= '<p>' . esc_html__('Unfortunately, there are some errors occurred:', 'commercioo') . '</p>';
                $wp_die_html .= '<ul>';

                foreach ($error_messages as $message) {
                    $wp_die_html .= '<li>' . esc_html($message) . '</li>';
                }

                $wp_die_html .= '</ul>';

                wp_die($wp_die_html, __('Error!', 'commercioo'), array(
                    'back_link' => true,
                ));
            }

            return true;
        }

        /**
         * Sort fields based on priority.
         *
         * @param array $x Field.
         * @param array $y Field.
         * @return int      Diff.
         */
        private function sort_field_by_priority($x, $y)
        {
            return (isset($x['priority']) ? $x['priority'] : 50) - (isset($y['priority']) ? $y['priority'] : 50);
        }

        /**
         * Save datas into post_meta table.
         *
         * @param array $args value with columns key.
         * @param int $order_id order_id.
         */
        public function commercioo_update_post_meta($args = array(), $order_id = 0)
        {
            if ($args) {
                foreach ($args as $key => $v) {
                    update_post_meta($order_id, $key, $v);
                }
            }
        }
    }
}