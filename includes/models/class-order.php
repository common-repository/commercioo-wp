<?php
/**
 * Commercioo model class for order.
 *
 * @author Commercioo Team
 * @package Commercioo
 */

namespace Commercioo\Models;

use Commercioo\Abstracts\Post;

if ( ! defined( 'WPINC' ) ) {
	exit;
}

if ( ! class_exists( 'Commercioo\Models\Order' ) ) {

	/**
	 * Class Order
	 *
	 * @package Commercioo\Models
	 */
	class Order extends Post {

		/**
		 * Common fields variable.
		 *
		 * @var string[]
		 */
		private $address_fields = array(
			'first_name',
			'last_name',
			'company',
			'street_address',
			'city',
			'state',
			'zip',
			'country',
			'phone',
			'email',
		);

		/**
		 * Order constructor.
		 *
		 * @param int $order_id order id.
		 */
		public function __construct( $order_id ) {
			parent::__construct( $order_id );

			// replace address item `country` to contry label
			add_filter( 'commercioo_address_item', array( $this, 'address_item_country' ) , 3, 10 );
		}

		/**
		 * Get order address fields
		 * 
		 * @return array Address fields.
		 */
		protected function get_address_fields() {
			return apply_filters( 'commercioo_order_address_fields', $this->address_fields );
		}

		/**
		 * Get order customer.
		 *
		 * @return Customer
		 */
		public function get_customer() {
			$user_id = $this->get_user_id();
			return $user_id ? new Customer( $user_id ) : null;
		}

		/**
		 * Get order id.
		 *
		 * @return int
		 */
		public function get_order_id() {
			return ($this->get_post())?$this->get_post()->ID:0;
		}

		/**
		 * Get order date.
		 *
		 * @return string
		 */
		public function get_order_date() {
			return $this->get_post()->post_date;
		}

		/**
		 * Get formatted order date.
		 *
		 * @return string
		 */
		public function get_formatted_order_date() {
			$order_date     = $this->get_order_date();
			$wp_date_format = get_option( 'date_format', 'F j, Y' );

			return date( $wp_date_format, strtotime( $order_date ) );
		}

		/**
		 * Get order items.
		 *
		 * @return array
		 */
		public function get_order_items() {
			global $wpdb;
			$db_table = $wpdb->prefix . 'commercioo_order_items';

			// Initiate result variable.
			$order_items = array();

			// Get from database
			$items_query = $wpdb->get_results( $wpdb->prepare( "
            SELECT item_name, item_price, item_order_qty, product_id, variation_id 
            FROM {$db_table}
            WHERE order_id = %d",
				$this->get_order_id()
			) );

			// Make sure query has result.
			if ( ! empty( $items_query ) ) {
				foreach ( $items_query as $key => $value ) {
					if ( ! isset( $order_items[ $key ] ) ) {
						$order_items[ $key ] = new \stdclass();
					}
					
					// vars
					$price          = floatval( $items_query[ $key ]->item_price );
					$qty            = intval( $items_query[ $key ]->item_order_qty );
					$items_subtotal = $price * $qty;

					// set to array
					$order_items[ $key ]->item_name      = $items_query[ $key ]->item_name;
					$order_items[ $key ]->item_price     = $price;
					$order_items[ $key ]->item_order_qty = $qty;
					$order_items[ $key ]->items_subtotal = $items_subtotal;
					$order_items[ $key ]->product_id     = intval( $items_query[ $key ]->product_id );
					$order_items[ $key ]->variation_id   = intval( $items_query[ $key ]->variation_id );
					$order_items[ $key ]->is_variation   = ! empty( $order_items[ $key ]->variation_id );
				}
			}

			return $order_items;
		}

		/**
		 * Get order billing email
		 * Will be check on these fields
		 * - backward compability `_billing_email` post meta
		 * - order's billing_address
		 * - user's billing_address
		 * - user's email
		 * 
		 * @return string Billing email
		 */
		public function get_billing_email() {
			// backward compability when `_billing_email` still a single meta
			if ( $email = $this->get_meta( '_billing_email', true ) ) {
				return $email;
			}

			// get email from billing address on order meta
			$billing_address = $this->get_billing_address();
			if ( isset( $billing_address['billing_email'] ) && is_email( $billing_address['billing_email'] ) ) {
				return $billing_address['billing_email'];
			}
			
			// at last, get it from user's meta or user's email
			$customer = $this->get_customer();
			if ( $customer ) {
				return $customer->get_billing_email();
			}

			return null;
		}

		/**
		 * Get order billing address
		 * 
		 * @return array Billing address
		 */
		public function get_billing_address() {
			return maybe_unserialize( $this->get_meta( '_billing_address', true ) );
		}

		/**
		 * Get order shipping address
		 * 
		 * @return array Shipping address
		 */
		public function get_shipping_address() {
			return maybe_unserialize( $this->get_meta( '_shipping_address', true ) );
		}

		/**
		 * Get order User id
		 * 
		 * @return string User id
		 */
		public function get_user_id() {
			$user_from_meta = $this->get_meta( '_user_id', true );
			return absint( $user_from_meta ) > 0 ? $user_from_meta : $this->post->post_author;
		}
        /**
         * Get order cart items
         *
         * @return string order_cart_items
         */
        public function get_order_cart() {
            return $this->get_meta( '_order_cart', true );
        }
        /**
         * Get order cart items
         *
         * @return array order_cart_items
         */
        public function get_order_cart_items() {
            $order_item = $this->get_meta( '_order_cart', true );
            $items = array();
            if($order_item) {
                foreach ($order_item['items'] as $key => $value) {
                    if (!isset($items[$key])) {
                        $items[$key] = new \stdclass();
                        // vars
                        $price = 0;
                        $sales_price = 0;
                        $qty = 0;
                        $product_id = 0;
                        $variation_id = 0;
                        $item_name = '';
                        if(isset($value['price'])){
                            $price = floatval($value['price']);
                        }
                        if(isset($value['sales_price'])){
                            $sales_price = floatval($value['sales_price']);
                        }
                        if(isset($value['item_order_qty'])){
                            $qty = intval($value['item_order_qty']);
                        }
                        if(isset($value['item_name'])){
                            $item_name = sanitize_text_field($value['item_name']);
                        }
                        if(isset($value['product_id'])){
                            $product_id = intval($value['product_id']);
                        }
                        if(isset($value['variation_id'])){
                            $variation_id = intval($value['variation_id']);
                        }
                        // set to array
                        $items[$key]->item_name = $item_name;
                        $items[$key]->item_price = $price;
                        $items[$key]->item_sales_price = $sales_price;
                        $items[$key]->item_order_qty = $qty;
                        $items[$key]->items_subtotal = $this->get_subtotal();
                        $items[$key]->product_id = $product_id;
                        $items[$key]->variation_id = $variation_id;
                        $items[$key]->is_variation = !empty($variation_id);
                    }
                }
            }

            return $items;
        }

		/**
		 * Get order payment method
		 * 
		 * @return string Payment method
		 */
		public function get_payment_method() {
			return $this->get_meta( '_payment_method', true );
		}
        /**
         * Get order payment method label
         *
         * @return string Payment method label
         */
        public function get_payment_method_name() {
            return $this->get_meta( '_payment_method_name', true );
        }
		/**
		 * Get order subtotal
		 * 
		 * @return float Order subtotal
		 */
		public function get_subtotal() {
            $order_item = $this->get_meta( '_order_cart', true );
            $sub_total = 0;
            if($order_item) {
                foreach ($order_item['items'] as $key => $value) {
                    $price = 0;
                    $sales_price = 0;
                    $qty = 0;
                    if(isset($value['price'])){
                        $price = floatval($value['price']);
                    }
                    if(isset($value['sales_price'])){
                        $sales_price = floatval($value['sales_price']);
                    }
                    if(isset($value['item_order_qty'])){
                        $qty = intval($value['item_order_qty']);
                    }

                    if($sales_price>0){
                        $price = $sales_price;
                    }
                    $sub_total += $price*$qty;
                }
            }else{

            }
            return floatval($sub_total);
//			return floatval( $this->get_meta( '_order_sub_total', true ) );
		}

		/**
		 * Get order total
		 * 
		 * @return float Order total
		 */
		public function get_total() {
			return floatval( $this->get_meta( '_order_total', true ) );
		}

		/**
		 * Get shipping price
		 * 
		 * @return float Shipping price
		 */
		public function get_shipping_price() {
			return floatval( $this->get_meta( '_shipping_price', true ) );
		}

		/**
		 * Get order shipping method
		 * 
		 * @return string Shipping method
		 */
		public function get_shipping_method() {
			return $this->get_meta( '_shipping_method', true );
		}
		
		/**
		 * Get order shipping method
		 * 
		 * @return string Shipping method
		 */
		public function get_shipping_number() {
			return $this->get_meta( '_shipping_number', true );
		}

		/**
		 * Get order status name without `comm_` prefix
		 * 
		 * @return string Order status
		 */
		public function get_order_status() {
			return str_replace( 'comm_', '', $this->get_post()->post_status );
		}

		/**
		 * Get order status label
		 * 
		 * @return string Order status
		 */
		public function get_order_status_label() {
			$comm_order   = \commercioo\admin\Comm_Order::get_instance();
			$statuses     = $comm_order->order_statuses;
			$status_key   = $this->get_post()->post_status;
			$status_label = isset( $statuses[ $status_key ] ) ? $statuses[ $status_key ] : 'Pending';
			
			return $status_label;
		}

		/**
		 * Get order unique code
		 * 
		 * @return int Unique code
		 */
		public function get_unique_code() {
			return $this->get_meta( '_unique_code', true );
		}
        /**
         * Get order unique type code
         *
         * @return string Unique type code
         */
        public function get_unique_type_code() {
            return $this->get_meta( '_unique_type_code', true );
        }
        /**
         * Get order unique label code
         *
         * @return string Unique label code
         */
        public function get_unique_label_code() {
            return $this->get_meta( '_unique_label_code', true );
        }
		/**
		 * Get formatted order address.
		 * 
		 * @param  string $type      Address type.
		 * @param  string $separator Address separator.
		 * @return string            Formatted address.
		 */
		public function get_formatted_address( $type = 'billing', $separator = '<br/>', $format = '' ) {
			if ( empty( $format ) ) {
				$format = "{first_name} {last_name}\n{street_address}\n{city}\n{state}\n{zip}\n{country}\n{email}";
			}
			$format = apply_filters( "commercioo_address_format", $format, $type );

			if ( 'shipping' == $type ) {
				$address = $this->get_shipping_address();
			} else {
				$address = $this->get_billing_address();
			}

			$formatted_address = $format;

			// Loop common fields.
			foreach ( $this->get_address_fields() as $field ) {
			    // Fix: PHP Notice:  Undefined index: billing_country & shipping_country
                // Must add conditions (if) before the next process
			    if(isset($address[ $type . '_' . $field ])) {
                    if ('country' === $field) {
                        global $comm_country;
                        $address[$type . '_' . $field] = $comm_country[$address[$type . '_' . $field]];
                    }
                    if (isset($address[$type . '_' . $field])) {
                        $address_item = apply_filters('commercioo_address_item', $address[$type . '_' . $field], $field, $type);
                        $formatted_address = str_replace("{" . $field . "}", $address[$type . '_' . $field], $formatted_address);
                    } else {
                        $formatted_address = str_replace("{" . $field . "}", '', $formatted_address);
                    }
                }
			}

			// Clean up white space.
			$formatted_address = preg_replace( '/  +/', ' ', trim( $formatted_address ) );
			$formatted_address = preg_replace( '/\n\n+/', "\n", $formatted_address );

			// Break newlines apart and remove empty lines/trim commas and white space.
			$formatted_address = array_filter( array_map( array( $this, 'trim_formatted_address_line' ), explode( "\n", $formatted_address ) ) );

			// Add html breaks.
			$formatted_address = implode( $separator, $formatted_address );

			// We're done!
			return $formatted_address;
		}
		
		/**
		 * Trim white space and commas off a line.
		 *
		 * @param  string $line Line.
		 * @return string
		 */
		private function trim_formatted_address_line( $line ) {
			return trim( $line, ', ' );
		}
		
		/**
		 * Update order customer id
		 * 
		 * @return bool|int
		 */
		public function update_customer_id ($customer_id ) {
			return $this->update_meta( '_customer_id', $customer_id );
		}
		
		/**
		 * Replace address item `country` to contry label
		 *
		 * @param  string $value Origin value
		 * @param  string $field Item field
		 * @param  string $type  Whether billing or shipping
		 * 
		 * @return string
		 */
		public function address_item_country( $value, $field, $type ) {
			if ( 'country' === $field ) {
				global $comm_country;
				$value = isset( $comm_country[ $value ] ) ? $comm_country[ $value ] : $value;
			}
			
			return $value;
		}

		/**
		 * Check whether the order has a shipping item.
		 *
		 * @return bool
		 */
		public function has_shipping() {
			return $this->has_meta( '_shipping_method' );
		}

		/**
		 * Check whether the order has a free shipping.
		 * return true if in array has free shipping all. otherwise it return false
		 * @return bool
		 */
		public function has_free_shipping() {
			$free_shipping = array();
			foreach ( $this->get_order_items() as $product ) {
				$free_shipping[] = get_post_meta( $product->product_id, '_free_shipping', true );
			}
			return count( array_keys( $free_shipping, true ) ) == count( $free_shipping );
		}

		/**
		 * Check whether the order has discounts.
		 *
		 * @return bool
		 */
		public function has_discount() {
			return $this->has_meta( '_discounts' );
		}

		/**
		 * Get discounts.
		 *
		 * @return array
		 */
		public function get_discounts() {
			return $this->get_meta( '_discounts' );
		}

		/**
		 * Get discounts= total.
		 *
		 * @return float
		 */
		public function get_discount_total() {
			return floatval( $this->get_meta( '_total_discounts' ) );
		}

		/**
		 * Check whether the order has fees.
		 *
		 * @return bool
		 */
		public function has_fee() {
			return $this->has_meta( '_fees' );
		}

		/**
		 * Get fees.
		 *
		 * @return array
		 */
		public function get_fees() {
			return $this->get_meta( '_fees' );
		}

		/**
		 * Get fees= total.
		 *
		 * @return float
		 */
		public function get_fee_total() {
			return floatval( $this->get_meta( '_total_fees' ) );
		}
        /**
         * Get Order Key.
         *
         * @return array
         */
        public function get_order_key() {
            return $this->get_meta( '_order_key' );
        }
        /**
         * Get Status confirmation payment.
         *
         * @return integer
         */
        public function get_status_confirmation_payment() {
            return $this->get_meta( '_status_confirmation_payment');
        }
        /**
         * Get bukti_transfer_file.
         *
         * @return string
         */
        public function get_bukti_transfer_file() {
            return $this->get_meta( '_bukti_transfer_file');
        }
        /**
         * Get transfer_amount.
         *
         * @return string
         */
        public function get_transfer_amount() {
            return $this->get_meta( '_transfer_amount');
        }
        /**
         * Get transfer_date.
         *
         * @return string
         */
        public function get_transfer_date() {
            return $this->get_meta( '_transfer_date');
        }
        /**
         * Get transfer_to_bank.
         *
         * @return string
         */
        public function get_transfer_to_bank() {
            return $this->get_meta( '_transfer_to_bank');
        }
        /**
         * Get transfer_from_name.
         *
         * @return string
         */
        public function get_transfer_from_name() {
            return $this->get_meta( '_transfer_from_name');
        }
        /**
         * Get date_confirmation_payment.
         *
         * @return string
         */
        public function get_date_confirmation_payment() {
            return $this->get_meta( '_date_confirmation_payment');
        }

        public function renew_create_order($order_id,$order_status='comm_pending'){
            global $wpdb;
            $post = get_post($order_id);
            $order_data = get_post_meta($order_id,"_order_items",true);
            $sql_query_sel=array();
            if (isset($post) && $post != null) {
                $new_order_id = wp_insert_post(array(
                    'post_author' => $post->post_author,
                    'post_title' =>  sprintf("{$post->post_title}_%s", uniqid()),
                    'post_excerpt' => $post->post_excerpt,
                    'post_status' => $order_status,
                    'post_type' => 'comm_order',
                ));
                // bail out on error
                if (is_wp_error($new_order_id)) {
                    return false;
                }
                $post_meta_infos = $wpdb->get_results("SELECT meta_key, meta_value FROM $wpdb->postmeta WHERE post_id='$new_order_id'");

                if (count($post_meta_infos) != 0) {
                    $sql_query = "INSERT INTO $wpdb->postmeta (post_id, meta_key, meta_value) ";
                    foreach ($post_meta_infos as $meta_info) {
                        $meta_key = $meta_info->meta_key;
                        if ($meta_key == '_wp_old_slug') continue;
                        $meta_value = addslashes($meta_info->meta_value);
                        $sql_query_sel[] = "SELECT $new_order_id, '$meta_key', '$meta_value'";
                    }
                    $sql_query .= implode(" UNION ALL ", $sql_query_sel);
                    $wpdb->query($sql_query);
                }

                $order = \commercioo\admin\Comm_Order::get_instance();
                $order->set_comm_order_items($new_order_id, $order_data['order_items']);
                // send the email on new order
                comm_sending_email($new_order_id, $order_status);
            }
            return true;
        }
	}
}
