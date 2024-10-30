<?php
/**
 * Commercioo model class for customer.
 *
 * @author Commercioo Team
 * @package Commercioo
 */

namespace Commercioo\Models;

use Commercioo\Abstracts\User;

if ( ! defined( 'WPINC' ) ) {
	exit;
}

if ( ! class_exists( 'Commercioo\Models\Customer' ) ) {

	/**
	 * Class Customer
	 *
	 * @package Commercioo\Models
	 */
	class Customer extends User {

		/**
		 * Common fields variable.
		 *
		 * @var string[]
		 */
		private $common_fields = array(
			'first_name',
			'last_name',
			'company',
			'street_address',
			'city',
			'zip',
			'state',
			'country',
			'phone',
			'email',
		);

		/**
		 * User id variable
		 *
		 * @var false|WP_User
		 */
		private $user_id;

		/**
		 * Customer id variable
		 *
		 * @var false|WP_User
		 */
		private $customer_id;

		/**

		/**
		 * Customer constructor.
		 *
		 * @param int $user_id customer id.
		 */
		public function __construct( $user_id=null, $customer_id = null ) {
			parent::__construct( $user_id );
			if($user_id){
                $this->user_id = $user_id;
            }

			if($customer_id){
				$this->customer_id = $customer_id;
			}
		}

		public function get_user_id() {
			return $this->user_id;
		}

		/**
		 * Get customer address fields
		 * 
		 * @return array Address fields.
		 */
		public function get_fields() {
			return apply_filters( 'commercioo_customer_address_fields', $this->common_fields );
		}


		/**
		 * Delete customer
		 *
		 * @return bool|int|object
		 */
		public function delete_customer() {
			global $wpdb;
	        $db_table = $wpdb->prefix . 'commercioo_customers';

	        // get from database
	        $result = $wpdb->delete( $db_table, array( 'customer_id' =>  $this->customer_id ) );

	        // result
	        return $result;
		}

		/**
		 * Get customer billing address.
		 *
		 * @return array
		 */
		public function get_billing_address() {
			$results = array();

			// Loop common fields.
			foreach ( $this->get_fields() as $field ) {
				$results[ $field ] = $this->get_meta( 'comm_billing_' . $field );
			}

			return $results;
		}

		/**
		 * Get customer billing email.
		 * Check on the user_meta first, if null then the user's email 
		 *
		 * @return string
		 */
		public function get_billing_email() {
			$billing_address = $this->get_billing_address();
				
			if ( isset( $billing_address['email'] ) && is_email( $billing_address['email'] ) ) {
				$billing_email = $billing_address['email'];
			}
			else {
				$billing_email = $this->get_user()->user_email;
			}

			return $billing_email;
		}

		/**
		 * Get customer shipping address.
		 *
		 * @return array
		 */
		public function get_shipping_address() {
			$results = array();

			// Loop common fields.
			foreach ( $this->get_fields() as $field ) {
				$results[ $field ] = $this->get_meta( 'comm_shipping_' . $field );
			}

			return $results;
		}

		/**
		 * Get formatted customer address.
		 * 
		 * @param  string $type      Address type.
		 * @param  string $separator Address separator.
		 * @return string            Formatted address.
		 */
		public function get_formatted_address( $type, $separator = '<br/>' ) {
			$format = apply_filters( "commercioo_address_format", "{first_name} {last_name}\n{company}\n{street_address}\n{city}\n{state}\n{zip}\n{country}", $type );

			if ( 'shipping' == $type ) {
				$address = $this->get_shipping_address();
			} else {
				$address = $this->get_billing_address();
			}

			$formatted_address = $format;

			// Loop common fields.
			foreach ( $this->get_fields() as $field ) {
				if ( ! isset( $address[ $field ] ) ) continue;

				if ( 'country' === $field ) {
					global $comm_country;
					$country_id = $address[ $field ];
					$address[ $field ] = isset( $comm_country[ $country_id ] ) ? $comm_country[ $country_id ] : $country_id;
				}
				$formatted_address = str_replace( "{" . $field . "}", $address[ $field ], $formatted_address );
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
		 * Get customer orders
		 * 
		 * @param  array  $args Arguments.
		 * @return array        Orders.
		 */
		public function get_orders( $args = array() ) {
			$args = wp_parse_args( $args, array(
				'status'	=> comm_get_all_status_order(),
				'per_page'	=> -1,
				'paged'		=> 1
			) );
			$user = $this->get_user();
            $result = array();
			$query = new \WP_Query( array(
				'post_type'		=> 'comm_order',
				'meta_key'		=> '_user_id',
				'meta_value'	=> $user->ID,
				'post_status'	=> $args['status'],
				'posts_per_page' => $args['per_page'],
				'paged'			=> $args['paged']
			) );
			if($query->have_posts()){
                $result = $query;
            }
			return $result;
		}

		/**
		 * Get customer get total order count
		 * 
		 * @return int Order count.
		 */
		public function get_order_count() {
			$user = $this->get_user();
			$query = new \WP_Query( array(
				'post_type'		=> 'comm_order',
				'author'		=> $user->ID,
				'post_status'	=> array( 'comm_pending', 'comm_processing', 'comm_completed', 'comm_refunded' ),
				'posts_per_page' => -1
			) );
			return $query->found_posts;
		}

		public function is_shipped_to_different_address() {

		}

		/**
		 * Get customer note.
		 *
		 * @return mixed
		 */
		public function get_note() {
			return $this->get_meta( 'comm_shipping_customer_note' );
		}

		/**
		 * Get sinle customer
		 *
		 * @return object
		 */
		public function get_single_customer() {
			global $wpdb;
	        $db_table = $wpdb->prefix . 'commercioo_customers';
	        // get from database
	        $result = $wpdb->get_row($wpdb->prepare("
	            SELECT * 
	            FROM $db_table
	            WHERE user_id = %d",
	            $this->user_id
	        ));

	        // result
	        return $result;
		}

		/**
		 * Get sinle customer
		 *
		 * @return object
		 */
		public function get_customer_by_id($customer_id) {
			global $wpdb;
	        $db_table = $wpdb->prefix . 'commercioo_customers';

	        // get from database
	        $result = $wpdb->get_row($wpdb->prepare("
	            SELECT * 
	            FROM $db_table
	            WHERE customer_id = %d",
	            $customer_id
	        ));

	        // result
	        return $result;
		}
		
		/**
		 * Get single customer
		 *
		 * @return object
		 */
		public function get_customer_by_field($field,$value) {
			global $wpdb;
	        $db_table = $wpdb->prefix . 'commercioo_customers';

	        // get from database
	        $result = $wpdb->get_row($wpdb->prepare("
	            SELECT * 
	            FROM $db_table
	            WHERE %s = %s",
	            $field,
	            $value
	        ));

	        // result
	        return $result;
		}

		/**
		 * Get single customer
		 *
		 * @return object
		 */
		public function get_customer_by_phone_or_email($phone,$email) {
			global $wpdb;
	        $db_table = $wpdb->prefix . 'commercioo_customers';

	        // get from database
	        $result = $wpdb->get_row($wpdb->prepare("
	            SELECT * 
	            FROM $db_table
	            WHERE phone = %s or email = %s",
	            $phone,
	            $email
	        ));

	        // result
	        return $result;
		}

		/**
		 * Get list customer's order 
		 * 
		 * @param  string $status      Order status.
		 * @param  string $order_by   Field to order.
		 * @param  string $startDate   Start date.
		 * @param  string $endDate   End date.
		 * @return array           List order.
		 */
		public function get_customer_orders($status, $order_by, $startDate = null, $endDate = null)
	    {
	    	if($this->customer_id){
	    		$meta_query = array(
		    		array( 
					    'key' => '_customer_id', 
					    'value' => $this->customer_id, 
					    'compare' => '='
					)
				);
		    	
		    }else{
		    	$meta_query = array(
		    		array( 
					    'key' => '_user_id', 
					    'value' => $this->user_id, 
					    'compare' => '='
					)
				);
		    }
	        $result = comm_controller()->comm_get_result_data($status, "comm_order", $order_by, $startDate, $endDate, $meta_query);
	        return $result;
	    }

		/**
		 * Set customer.
		 *
		 * @return customer id
		 */
		public function set_customer($billing_address=array()) {
	        global $wpdb;

	        $db_table = $wpdb->prefix . 'commercioo_customers';
	        if($this->customer_id){
                $customer_exist = $this->get_customer_by_id($this->customer_id);
            }else{
                $customer_exist = $this->get_single_customer();
            }


            $billing_city = (isset($billing_address['billing_city'])) ? $billing_address['billing_city']: '';
            $billing_state = (isset($billing_address['billing_state'])) ? $billing_address['billing_state']: '';
            $billing_zip = (isset($billing_address['billing_zip'])) ? $billing_address['billing_zip']: '';
            $billing_country = (isset($billing_address['billing_country'])) ? $billing_address['billing_country']: '';
            $billing_street_address = (isset($billing_address['billing_street_address'])) ? $billing_address['billing_street_address'] : '';
            $first_name = (isset($billing_address['billing_first_name'])) ? $billing_address['billing_first_name'] : null;
            $billing_phone = (isset($billing_address['billing_phone'])) ? sanitize_text_field($billing_address['billing_phone']) : null;

            $billing_email = (isset($billing_address['billing_email'])) ? $billing_address['billing_email'] : null;

            $customer_name = ($first_name)?$first_name:null;
            $billing_address_gabungan = array($billing_street_address,$billing_city,$billing_state,$billing_zip,$billing_country);
            $billing_address_save = implode(', ', array_filter($billing_address_gabungan));
            $address = $billing_address_save;

            $data_insert = array(
                'user_id' => $this->user_id,
                'name' => $customer_name,
                'phone' => $billing_phone,
                'email' => $billing_email,
                'address' => $address,
                'date_registered' => date('Y-m-d H:i:s'),
            );

            $data_db = array_filter($data_insert);
            if ($customer_exist){
	            $wpdb->update(
	                $db_table,
                    $data_db,
	                array('customer_id' => $customer_exist->customer_id),
	                array('%s', '%s', '%s', '%s', '%s')
	            );
	            $customer_id = $customer_exist->customer_id;
	        }else{
	            if($this->user_id){
		            $wpdb->insert(
		                $db_table,
                        $data_db,
		                array('%d', '%s', '%s', '%s', '%s', '%s')
		            );
		           	$customer_id = $wpdb->insert_id;
		        }else{
		        	$detail_customer = $this->get_customer_by_phone_or_email($billing_phone,$billing_email);
		        	if($detail_customer){
			            $wpdb->update(
			                $db_table,
                            $data_db,
			                array('customer_id' => $detail_customer->customer_id),
			                array('%s', '%s', '%s', '%s', '%s')
			            );
			            $customer_id = $detail_customer->customer_id;
		        	}else{
			        	$wpdb->insert(
			                $db_table,
                            $data_db,
			                array('%s', '%s', '%s', '%s', '%s')
			            );
	            		$customer_id = $wpdb->insert_id;
			        }
	        	}
	        }
	        return $customer_id;
		}
	}
}