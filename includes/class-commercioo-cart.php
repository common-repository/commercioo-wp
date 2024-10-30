<?php
/**
 * Commercioo model class for order.
 *
 * @author Commercioo Team
 * @package Commercioo
 */

namespace Commercioo;

if ( ! defined( 'WPINC' ) ) {
    exit;
}

if ( ! class_exists( 'Commercioo\Cart' ) ) {

    /**
     * Class Cart
     *
     * @package Commercioo
     */
    class Cart {

        /**
         * Cookie name
         *
         * @var string
         */
        private static $cookie_name = 'commercioo-cart';

        /**
         * Cookie value
         *
         * @var array
         */
        private static $cart = array();

        /**
         * Class instance
         *
         * @var Cart
         */
        private static $instance;

        /**
         * Order constructor.
         *
         * @param int $order_id order id.
         */
        public function __construct() {
        }

        /**
         * Get class instance
         *
         * @return Cart Class instance.
         */
        public static function get_instance() {
            if ( ! isset( self::$instance ) ) {
                // Fix: PHP Warning class 'Commercioo_Cart()' should be the class name is Cart()
                self::$instance = new Cart();
            }
            return self::$instance;
        }

        /**
         * Get cart value as array from the cookie
         *
         * @return array Cart value.
         */
        public static function get_cart() {
//            if ( empty( self::$cart ) && isset( $_COOKIE[ self::$cookie_name ] ) && !is_page_has_elementor()) {
//                $value = json_decode( wp_kses_post(base64_decode( $_COOKIE[ self::$cookie_name ] )), true );
//                self::$cart = (array) $value;
//            }else{
//                $product_to_checkout = apply_filters('commercioo/elementor/checkout/get_elementor_data_value', array());
//                if($product_to_checkout){
//                    self::$cart['items'] = $product_to_checkout;
//                }else{
//                    //self::$cart = self::get_carts();
//                }
//            }
            $product_to_checkout = apply_filters('commercioo/elementor/checkout/get_elementor_data_value', array());
            if($product_to_checkout && is_page_has_elementor() && function_exists("is_page_has_elementor")){
                self::$cart['items'] = $product_to_checkout;
            }else{
                if ( empty( self::$cart )){
                    self::$cart = self::get_carts();
                }

            }
        }

        /**
         * Get cart value as array from the cookie
         *
         * @return string Cart value.
         */
        public static function get_cart_base64() {
            return base64_encode( json_encode( self::$cart ) );
        }

        /**
         * Save current cart value to the cookie
         */
        public static function save_cart() {
            $value = base64_encode(json_encode(self::$cart));
            if (self::get_items_count() > 0) {
                $product_to_checkout = apply_filters('commercioo/elementor/checkout/get_elementor_data_value', array());
                if(!$product_to_checkout && !is_page_has_elementor() && function_exists("is_page_has_elementor")){
                    setcookie(self::$cookie_name, $value, time() + 86400, COOKIEPATH ? COOKIEPATH : '/', COOKIE_DOMAIN);
                }
            } else {
                // remove cart data
                self::empty_cart();
            }
        }

        /**
         * Add item to the cart
         *
         * @param integer $product_id Product ID.
         * @param integer $qty        Quantity.
         */
        public static function add_item( $product_id = 0, $qty = 1 ) {
            self::get_cart();

            $product = comm_get_product( $product_id );
            if ( ! isset( self::$cart['items'] ) ) {
                self::$cart['items'] = array();
            }

            if ( isset( self::$cart['items'][ $product_id ] ) ) {
                self::$cart['items'][ $product_id ]['qty'] += $qty;
            } else {
                self::$cart['items'][ $product_id ] = array(
                    'name'  => $product->get_title(),
                    'price' => $product->get_regular_price(),
                    'qty'   => $qty
                );
            }

//            if ( $product->is_on_sale() ) {
//                $discount_name = sprintf( __( '%s discount', 'commercioo' ), $product->get_title() );
//                if(!empty(self::$cart)){
//                    self::remove_discount( $discount_name );
//                    self::add_discount( $discount_name, ( self::$cart['items'][ $product_id ]['qty'] * ( $product->get_regular_price() - $product->get_sale_price() ) ), $product_id );
//                }
//            }


            self::save_cart();

            do_action( 'commercioo_cart_after_add_item', $product_id, $qty );
            // Call unique number
            // note: required Plugin Commercioo Unique Number

            do_action( 'commercioo_unique_number', self::get_carts() );
        }

        /**
         * Remove item from the cart
         *
         * @param integer $product_id Product ID.
         */
        public static function remove_item( $product_id ) {
            self::get_cart();

            if ( isset( self::$cart['items'][ $product_id ] ) ) {
                unset( self::$cart['items'][ $product_id ] );
                self::remove_related_fees( $product_id );
                self::remove_related_discounts( $product_id );

            }
            self::save_cart();
            do_action( 'commercioo_cart_after_remove_item', $product_id );
        }

        /**
         * Set item qty
         *
         * @param integer $product_id Product ID.
         * @param integer $qty        Quantity.
         */
        public static function set_item_qty( $product_id = 0, $qty = 1 ) {
            self::get_cart();

            $product = comm_get_product( $product_id );

            if ( isset( self::$cart['items'][ $product_id ] ) ) {
                self::$cart['items'][ $product_id ]['qty'] = $qty;
            }

//            if ( $product->is_on_sale() ) {
//                $discount_name = sprintf( __( '%s discount', 'commercioo' ), $product->get_title() );
//                self::remove_discount( $discount_name );
//                self::add_discount( $discount_name, ( self::$cart['items'][ $product_id ]['qty'] * ( $product->get_regular_price() - $product->get_sale_price() ) ), $product_id );
//            }

            self::save_cart();

            do_action( 'commercioo_cart_after_set_item', $product_id, $qty );
        }

        /**
         * Check whether product is already in cart
         *
         * @param  integer $product_id Product ID.
         * @return boolean             Is product already in cart.
         */
        public static function is_in_cart( $product_id ) {
            self::get_cart();
            return isset( self::$cart['items'][ $product_id ] );
        }

        /**
         * Get cart items
         *
         * @return array Array of cart items.
         */
        public static function get_items($cart = null) {
            if ($cart) {
                self::$cart = $cart;
            }else{
                self::get_cart();
            }

            return isset( self::$cart['items'] ) ? self::$cart['items'] : array();
        }

        /**
         * Get total items count on the cart
         *
         * @return integer Items count.
         */
        public static function get_items_count() {
            self::get_cart();
            $product_to_checkout = apply_filters('commercioo/elementor/checkout/get_elementor_data_value', array());
           if($product_to_checkout && is_page_has_elementor() && function_exists("is_page_has_elementor")){
               self::$cart['items'] = $product_to_checkout;
           }
            $total_qty = 0;
            if ( isset( self::$cart['items'] ) ) {
                foreach ( self::$cart['items'] as $product_id => $item ) {
                    $total_qty += $item['qty'];
                }
            }
            return $total_qty;
        }

        /**
         * Empty the cart
         */
        public static function empty_cart() {
            unset( $_COOKIE[ self::$cookie_name ] );
            setcookie( self::$cookie_name, null, time() - 3600, COOKIEPATH ? COOKIEPATH : '/', COOKIE_DOMAIN );
        }

        /**
         * Check whether cart is empty
         *
         * @return boolean
         */
        public static function is_empty() {
            self::get_cart();
            return ! isset( self::$cart['items'] ) || empty( self::$cart['items'] );
        }

        /**
         * Check whether cart has fees
         *
         * @return boolean
         */
        public static function has_fees() {
            self::get_cart();
            return isset( self::$cart['fees'] ) && ! empty( self::$cart['fees'] );
        }

        /**
         * Add additional fee to the cart
         *
         * @param string  $fee_name        Fee name.
         * @param float   $amount          Fee amount/price.
         * @param integer $related_product Product ID (if the fee is related to a specific product).
         */
        public static function add_fee( $fee_name, $amount, $related_product = 0 ) {
            self::get_cart();

            if ( ! isset( self::$cart['fees'] ) ) {
                self::$cart['fees'] = array();
            }

            self::$cart['fees'][] = array(
                'name'    => $fee_name,
                'amount'  => $amount,
                'product' => $related_product
            );

            self::save_cart();
        }

        /**
         * Remove fee by fee name
         *
         * @param string  $fee_name   Fee name.
         * @param integer $product_id Product ID (optional).
         */
        public static function remove_fee( $fee_name, $product_id = 0 ) {
            self::get_cart();

            if ( isset( self::$cart['fees'] ) ) {
                foreach ( self::$cart['fees'] as $key => $fee ) {
                    if ( ! empty( $product_id ) ) {
                        if ( $fee['name'] === $fee_name && $fee['product'] == $product_id ) {
                            unset( self::$cart['fees'][ $key ] );
                        }
                    } else {
                        if ( $fee['name'] === $fee_name ) {
                            unset( self::$cart['fees'][ $key ] );
                        }
                    }
                }
            }

            self::save_cart();
        }

        /**
         * Remove all fees that related to specific product
         *
         * @param  integer $product_id Product ID.
         */
        public static function remove_related_fees( $product_id ) {
            self::get_cart();

            if ( isset( self::$cart['fees'] ) ) {
                foreach ( self::$cart['fees'] as $key => $fee ) {
                    if ( $fee['product'] === $product_id ) {
                        unset( self::$cart['fees'][ $key ] );
                    }
                }
            }
            self::save_cart();
        }

        /**
         * Get all fees
         *
         * @return array Array of fees.
         */
        public static function get_fees() {
            self::get_cart();
            return isset( self::$cart['fees'] ) ? self::$cart['fees'] : array();
        }

        /**
         * Get total amount of fees
         *
         * @return float Total amount of fees.
         */
        public static function get_fee_total() {
            $total = apply_filters("commercioo/checkout/calculate/fees",0);

            return $total;
        }

        /**
         * Check whether cart has discounts
         *
         * @return boolean
         */
        public static function has_discounts() {
            self::get_cart();
            return isset( self::$cart['discounts'] ) && ! empty( self::$cart['discounts'] );
        }

        /**
         * Add additional discount to the cart
         *
         * @param string  $discount_name   Discount name.
         * @param float   $amount          Discount amount/price.
         * @param integer $related_product Product ID (if the discount is related to a specific product).
         */
        public static function add_discount( $discount_name, $amount, $related_product = 0 ) {
            self::get_cart();

            if ( ! isset( self::$cart['discounts'] ) ) {
                self::$cart['discounts'] = array();
            }

            self::$cart['discounts'][] = array(
                'name'    => $discount_name,
                'amount'  => $amount,
                'product' => $related_product
            );

            self::save_cart();
        }

        /**
         * Remove discount by discount name
         *
         * @param  string $discount_name Discount name.
         */
        public static function remove_discount( $discount_name ) {
            self::get_cart();

            if ( isset( self::$cart['discounts'] ) ) {
                foreach ( self::$cart['discounts'] as $key => $discount ) {
                    if ( $discount['name'] === $discount_name ) {
                        unset( self::$cart['discounts'][ $key ] );
                    }
                }
            }

            self::save_cart();
        }

        /**
         * Remove all discounts that related to specific product
         *
         * @param  integer $product_id Product ID.
         */
        public static function remove_related_discounts( $product_id ) {
            self::get_cart();


            if ( isset( self::$cart['discounts'] ) ) {
                foreach ( self::$cart['discounts'] as $key => $discount ) {
                    if ( $discount['product'] === $product_id ) {
                        unset( self::$cart['discounts'][ $key ] );
                    }
                }
            }

            self::save_cart();
        }

        /**
         * Get all discounts
         *
         * @return array Array of discounts.
         */
        public static function get_discounts() {
            self::get_cart();
            return isset( self::$cart['discounts'] ) ? self::$cart['discounts'] : array();
        }

        /**
         * Get total amount of discounts
         *
         * @return float Total amount of discounts.
         */
        public static function get_discount_total() {
            self::get_cart();
            $total = 0;
            if ( isset( self::$cart['discounts'] ) && defined("COMMERCIOO_PRO_PATH") ) {
                foreach ( self::$cart['discounts'] as $discount ) {
                    $total += floatval( $discount['amount'] );
                }
            }
            return $total;
        }
        /**
         * Get total amount of product discounts
         *
         * @return float Total amount of discounts.
         */
        public static function get_product_discount_total() {
            $total = apply_filters("commercioo/checkout/calculate/product/discount",0);
            return $total;
        }
        /**
         * Get cart subtotal (total items)
         *
         * @return float Cart subtotal.
         */
        public static function get_subtotal() {
            $subtotal = 0;
            $subtotal = apply_filters('commercioo/checkout/order/sub_total', $subtotal);
            $subtotal = $subtotal - self::get_product_discount_total();
            return $subtotal;
        }

        /**
         * Get cart total
         *
         * @return float Cart total.
         */
        public static function get_total() {
            self::get_cart();
            $total = self::get_subtotal();
            $total = $total + self::get_fee_total();
//            $total = $total - self::get_discount_total();
//            $total = $total - self::get_product_discount_total();
            $total = $total + self::get_shipping_price();
            // Get Unique Number Price for recalculate total price
            $total = $total + self::get_unique_number();
            return $total;
        }

        /**
         * Check whether cart has free shipping
         *
         * @return boolean
         */
        public static function has_free_shipping() {
            $free_shipping = array();
            self::get_cart();
            if (isset(self::$cart['items']) && !is_page_has_elementor() && function_exists("is_page_has_elementor")) {
                foreach (self::$cart['items'] as $product_id => $item) {
                    $free_shipping[] = get_post_meta($product_id, '_free_shipping', true);
                }
            }else{
                $product_to_checkout = apply_filters('commercioo/elementor/checkout/get_elementor_data_value', array());
                if($product_to_checkout) {
                    foreach ($product_to_checkout as $product_id => $item) {
                        $free_shipping[] = get_post_meta($product_id, '_free_shipping', true);
                    }
                }else{
                    if (isset(self::$cart['items'])) {
                        foreach (self::$cart['items'] as $product_id => $item) {
                            $free_shipping[] = get_post_meta($product_id, '_free_shipping', true);
                        }
                    }
                }
           }

            return count( array_keys( $free_shipping, true ) ) == count( $free_shipping );
        }

        /**
         * Check whether cart has shipping
         *
         * @return boolean
         */
        public static function has_shipping() {
            self::get_cart();
            return isset( self::$cart['shipping_method'] ) && isset( self::$cart['shipping_price'] );
        }

        /**
         * Set shipping to the cart
         *
         * @param string $shipping_method Shipping method.
         * @param float  $shipping_price  Shipping price.
         */
        public static function set_shipping( $shipping_method, $shipping_price, $cart = null ) {
            if ($cart) {
                self::$cart = $cart;
            }else{
                self::get_cart();
            }
            self::$cart['shipping_method'] = $shipping_method;
            self::$cart['shipping_price']  = $shipping_price;

            self::save_cart();
            return self::$cart;
        }

        /**
         * Remove shipping from the cart
         */
        public static function remove_shipping() {
            self::get_cart();

            if ( isset( self::$cart['shipping_method'] ) ) {
                unset( self::$cart['shipping_method'] );
            }
            if ( isset( self::$cart['shipping_price'] ) ) {
                unset( self::$cart['shipping_price'] );
            }

            self::save_cart();
        }

        /**
         * Get shipping method
         *
         * @return string Shipping method.
         */
        public static function get_shipping($cart = null) {
            if ($cart) {
                self::$cart = $cart;
            }else{
                self::get_cart();
            }
            return isset( self::$cart['shipping_method'] ) ? self::$cart['shipping_method'] : false;
        }

        /**
         * Get shipping total price
         *
         * @return float Shipping price.
         */
        public static function get_shipping_total() {
            if ( ! self::is_shipping_available() ) {
                return 0;
            }
            $options = self::shipping_options();
            if ( empty( $options ) ) {
                return 0;
            }
            self::get_cart();
            return isset( self::$cart['shipping_price'] ) ? floatval( self::$cart['shipping_price'] ) : 0;
        }

        /**
         * Check whether if shipping available
         *
         * @param  array   $checkout_data Checkout data.
         * @return boolean
         */
        public static function is_shipping_available() {
            return apply_filters( 'commercioo_shipping_available', false, self::parse_checkout_data() );
        }

        /**
         * Get shipping options
         *
         * @param  array   $checkout_data Checkout data.
         * @return array
         */
        public static function shipping_options() {
            return apply_filters( 'commercioo_shipping_options', array(), self::parse_checkout_data() );
        }

        /**
         * Parse checkout data
         *
         * @return array
         */
        public static function parse_checkout_data() {
            $checkout_data = array();
            if ( isset( $_POST['checkout_data'] ) ) {
                parse_str( urldecode( $_POST['checkout_data'] ), $checkout_data );
                if ( isset( $_POST['cookie_cart'] ) ) {
                    $checkout_data['cart'] = $_POST['cookie_cart'];
                }
            } else {
                $checkout_data = wp_unslash( $_POST );
            }
            return $checkout_data;
        }

        /**
         * Get public static cart value as array from the cookie
         *
         * @return array Cart value.
         */
        public static function get_carts() {
//            self::get_cart();
//            return isset( self::$cart) ? self::$cart : array();

            if (isset( $_COOKIE[ self::$cookie_name ] ) ) {
                $value = json_decode(wp_kses_post(base64_decode($_COOKIE[self::$cookie_name])), true);
                self::$cart = (array)$value;
            }
            return isset(self::$cart) ? self::$cart : array();
        }

        /**
         * Get all data unique number from cart
         *
         * @return array Array of unique number.
         */
        public static function get_data_unique_number() {
            self::get_cart();
            return isset( self::$cart['unique_code'] ) ? self::$cart['unique_code'] : array();
        }
        /**
         * Set unique number to the cart
         *
         * @param int  $unique_number  unique number.
         */
        public static function set_unique_number($unique_number) {
            $comm_unique_number_type = (!empty(get_option('comm_unique_number_type')))?get_option('comm_unique_number_type'):"increase";
            $comm_unique_number_label = (!empty(get_option('comm_unique_number_label')))?get_option('comm_unique_number_label'):"Kode Unik";
            if($comm_unique_number_type == "decrease"){
                $unique_number_price = 1000 - $unique_number;
                $unique_number_price = (int)-$unique_number_price;
            }else{
                $unique_number_price = $unique_number;
            }

            self::get_cart();
            self::$cart['unique_code']['name'] = $comm_unique_number_label;
            self::$cart['unique_code']['type'] = $comm_unique_number_type;
            self::$cart['unique_code']['amount'] = $unique_number;
            self::$cart['unique_number_price'] = $unique_number_price;
            self::save_cart();
        }

        /**
         * Get unique number
         *
         * @return float unique number.
         */
        public static function get_unique_number() {
            $comm_unique_number_visibility = get_option('comm_unique_number_visibility');
            self::get_cart();
            return (isset( self::$cart['unique_number_price'] ) && $comm_unique_number_visibility == 'active' && defined("COMMERCIOO_UNIQUE_NUMBER_PATH"))?
                floatval( self::$cart['unique_number_price'] ) : 0;
        }

        /**
         * Check whether cart has unique number
         *
         * @return boolean
         */
        public static function has_unique_number() {
            self::get_cart();
            return isset( self::$cart['unique_code'] ) && ! empty( self::$cart['unique_code'] );
        }

        /**
         * Get shipping price
         *
         * @return float Shipping price.
         */
        public static function get_shipping_price() {
            if(is_cart_page()){
                return;
            }
//            self::get_cart();
//            return isset( self::$cart['shipping_price'] ) ? floatval( self::$cart['shipping_price'] ) : 0;
            return isset( $_POST['shipping_price'] ) ? floatval( base64_decode(sanitize_text_field($_POST['shipping_price']))) : 0;
        }
    }
}