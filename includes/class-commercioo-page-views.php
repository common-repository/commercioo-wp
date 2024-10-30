<?php
/**
 * Commercioo Page Views
 * The default emails subjects and contents
 *
 * @author Commercioo_Team
 * @package Commercioo
 */

namespace Commercioo;

if ( ! defined( 'WPINC' ) ) {
	exit;
}

if ( ! class_exists( 'Commercioo\Page_Views' ) ) {

	/**
	 * Class Page_Views
	 *
	 * @package Commercioo
	 */
	Class Page_Views {
		
		public $table_name;
		public $cookie_name;
		public $cookie_lifetime;
		public $record_lifetime;
		public $page_views_cookie;

		public function __construct() {
			$this->table_name      = 'commercioo_page_views';
			$this->cookie_name     = 'comm_visited_page';
			$this->cookie_lifetime = '+1 year'; // as long as possible
			$this->record_lifetime = '+1 day'; // recorded page_views lifetime
		}

		public function get_recorded( $page_key, $date_from, $date_to, $use_like = false ) {
			global $wpdb;
			$table_name         = $wpdb->prefix . $this->table_name;
			$sanitized_page_key	= sanitize_textarea_field( $page_key );
			$comparison         = $use_like ? 'LIKE' : '=';

			$page_views = $wpdb->get_var( $wpdb->prepare( "
				SELECT SUM(page_views) as total_page_views
				FROM $table_name
				WHERE (date BETWEEN %s AND %s) 
				AND page_key " . $comparison . " %s", 
				$date_from, $date_to, $sanitized_page_key
			) );

			return intval( $page_views );
		}

		public function page_views_recorder() {
			global $post;
			$page_key = false;

			// record the `checkout` page
			if ( $post && $post->ID == get_option( 'commercioo_Checkout_page_id' ) ) {
				$product_ids = array();				
				if ( ! Cart::is_empty() ) {
					$page_key    = 'checkout-';
					$items       = Cart::get_items();
					$product_ids = array_keys( $items );

					// sort asc
					sort( $product_ids );

					foreach ( $product_ids as $product_id ) {
						$page_key .= "{$product_id}-";
					}
				}
			}

			$this->do_count_page_views( $page_key );
		}

		public function do_count_page_views( $page_key = false ) {	
			// page_key must be initialized
			if ( ! $page_key ) return;
			
			// get page_views cookie
			$this->get_page_views_cookie();
			
			// check the cookie is existed or not
			if ( ! isset( $this->page_views_cookie[ $page_key ] ) ) {
				$this->set_cookie( $page_key );
				$this->insert_record( $page_key );
			}
			else {
				return; // do nothing if exist
			}
		}

		private function get_page_views_cookie() {
			$cookie_data = array();

			// check the cookie is existed or not
			if ( isset( $_COOKIE[ $this->cookie_name ] ) ) {
				$cookie_data = array_map('sanitize_text_field', (array) json_decode( stripslashes( $_COOKIE[ $this->cookie_name ] ) ));
			}
			// clean the cookie then set it to a variable
			$this->page_views_cookie = $this->page_view_cookie_cleaner( $cookie_data );

			// it may be useful to return a variable
			return $this->page_views_cookie;
		}

		private function page_view_cookie_cleaner( $cookie_data ) {
			$record_lifetime          = $this->record_lifetime;
			$checkout_record_lifetime = get_option( 'comm_page_views_checkout_lifetime', 30 );

			// must be an array and not null
			if ( ! is_array( $cookie_data ) || is_null( $cookie_data ) ) return array();

			foreach ( $cookie_data as $page_key => $date ) {
				// checkout page has a different record_lifetime
				if ( false !== strpos( $page_key, 'checkout-' ) ) {
					$record_lifetime = sprintf( "+%d days", intval( $checkout_record_lifetime ) );
				}

				// convert to `time`, for comparing purpose
				$now_time = strtotime( 'now' );
				$lifetime = strtotime( $date . ' ' . $record_lifetime );
				
				// remove the cookie item that has no lifetime left
				if ( $now_time > $lifetime ) {
					unset( $cookie_data[ $page_key ] );
				}
			}

			return $cookie_data;
		}

		private function set_cookie( $page_key ) {
			$cookie_name     = $this->cookie_name;
			$cookie_lifetime = $this->cookie_lifetime;
			$cookie_value    = $this->page_views_cookie;

			// set new cookie value for this page_key
			$cookie_value[ $page_key ] = date( 'Y-m-d' );
			$json_cookie_value         = json_encode( $cookie_value );

			if ( ! defined( 'PHP_VERSION_ID' ) ) {
				$version = explode( '.', PHP_VERSION );		
				define( 'PHP_VERSION_ID', ( $version[0] * 10000 + $version[1] * 100 + $version[2] ) );
			}
			
			// PHP 7.3, 5, etc
			if ( 70300 <= PHP_VERSION_ID ) {
				$cookie_options = array(
					'expires'  => strtotime( $cookie_lifetime ),
					'path'     => '/',
					'secure'   => false,	// set to `true` to allow only on https
					'httponly' => true,		// set to `true` to allow only on http/https protocol
					'samesite' => 'Lax', 	// None, Lax or Strict
				);

				setcookie( $cookie_name, $json_cookie_value, $cookie_options );
			}

			// PHP > 7.3
			if ( 70300 > PHP_VERSION_ID ) {
				setcookie( $cookie_name, $json_cookie_value, strtotime( $cookie_lifetime ), '/' );
			}
		}

		private function insert_record( $page_key ) {
			global $wpdb;
			$table_name         = $wpdb->prefix . $this->table_name;
			$current_date       = date( "Y-m-d" );
			$sanitized_page_key	= sanitize_textarea_field( $page_key );

			// getting the current data
			$current_data = $wpdb->get_row( $wpdb->prepare( "
				SELECT ID, page_views
				FROM $table_name
				WHERE date = %s 
				AND page_key = %s", 
				$current_date, $sanitized_page_key
			) );

			// whether to insert or update
			if ( $current_data === null ) {
				$response = $wpdb->insert( $table_name, array(
					'date'       => $current_date,
					'page_key'   => $sanitized_page_key,
					'page_views' => 1,
				) );
			}
			else {
				$response = $wpdb->update(
					$table_name,
					array( 'page_views' => intval( $current_data->page_views ) + 1 ),
					array( 'ID' => $current_data->ID ),
					array( '%d' ),
					array( '%d' )
				);
			}

			// return false on failed insert or update
			return $response;
		}		

		public function create_db_table() {
			global $wpdb;
			$table_name      = $wpdb->prefix . $this->table_name;
			$charset_collate = $wpdb->get_charset_collate();

			if ( $wpdb->get_var( "SHOW TABLES LIKE '$table_name'" ) !== $table_name ) {
				$sql = "CREATE TABLE $table_name ( 
					`ID` int(20) UNSIGNED NOT NULL AUTO_INCREMENT, 
					`date` date NOT NULL DEFAULT '0000-00-00', 
					`page_key` text NOT NULL, 
					`page_views` int(20) NOT NULL DEFAULT 0,
					PRIMARY KEY  (`ID`) 
				) $charset_collate;";

				require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
				dbDelta( $sql );
			}
		}

	}
}