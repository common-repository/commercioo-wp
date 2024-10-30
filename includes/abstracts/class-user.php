<?php
/**
 * Commercioo abstract class for user.
 *
 * @author Commercioo Team
 * @package Commercioo
 */

namespace Commercioo\Abstracts;

use WP_User;

if ( ! defined( 'WPINC' ) ) {
	exit;
}

if ( ! class_exists( 'Commercioo\Abstracts\User' ) ) {

	/**
	 * Class User
	 *
	 * @package Commercioo\Abstracts
	 */
	abstract class User {

		/**
		 * User object variable
		 *
		 * @var false|WP_User
		 */
		private $user;

		/**
		 * User constructor.
		 *
		 * @param int $user_id user id.
		 */
		protected function __construct( $user_id ) {

			// Save user properties.
			$this->user = get_user_by( 'ID', $user_id );
		}

		/**
		 * Get user object.
		 *
		 * @return false|WP_User
		 */
		public function get_user() {
			return $this->user;
		}

		/**
		 * Get user object.
		 *
		 * @return false|WP_User
		 */
		public function get_customer() {
			global $wpdb;
	        $db_table = $wpdb->prefix . 'commercioo_customers';

	        // get from database
	        $customer = $wpdb->get_row($wpdb->prepare("
	            SELECT * 
	            FROM $db_table
	            WHERE user_id = %d",
	            $this->get_user()->ID
	        ));

	        // result
	        return $customer;
		}

		/**
		 * Get user meta.
		 *
		 * @param string $key meta key.
		 * @param bool $is_single whether display user meta as single string or array.
		 *
		 * @return mixed
		 */
		protected function get_meta( $key, $is_single = true ) {
			return isset($this->get_user()->ID)?get_user_meta( $this->get_user()->ID, $key, $is_single ):null;
		}

		/**
		 * Update user meta.
		 *
		 * @param string $key meta key.
		 * @param mixed $value new meta value.
		 *
		 * @return bool|int
		 */
		protected function update_meta( $key, $value ) {
			return update_user_meta( $this->get_user()->ID, $key, $value );
		}

		/**
		 * Add user meta.
		 *
		 * @param string $key meta key.
		 * @param mixed $value new meta value.
		 * @param false $is_unique whether meta is unique or not.
		 *
		 * @return false|int
		 */
		protected function add_meta( $key, $value, $is_unique = false ) {
			return add_user_meta( $this->get_user()->ID, $key, $value, $is_unique );
		}
	}
}