<?php
namespace Commercioo\Query;
/**
 * Contains the query functions for Coomercioo which alter the front-end post queries and loops
 *
 * @version 1.0.0
 * @package Commercioo\includes
 */

defined( 'ABSPATH' ) || exit;

class Commercioo_Query {

	/**
	 * Query vars to add to wp.
	 *
	 * @var array
	 */
	public $query_vars = array();
    private static $instance;
    public static function get_instance()
    {
        if (!isset(self::$instance)) {
            self::$instance = new Commercioo_Query();
        }
        return self::$instance;
    }
	/**
	 * Constructor for the query class. Hooks in methods.
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'add_endpoints' ) );
		add_action( 'init', array( $this, 'maybe_flush_rules' ) );
		if ( ! is_admin() ) {
			add_action( 'parse_request', array( $this, 'parse_request' ), 0 );
		}
		$this->init_query_vars();
	}

	/**
	 * Add endpoints for query vars.
	 */
	public function add_endpoints() {
		foreach ( $this->get_query_vars() as $key => $var ) {
			if ( ! empty( $var ) ) {
				add_rewrite_endpoint( $var, EP_ALL );
			}
		}
	}

	/**
	 * Init query vars by loading options.
	 */
	public function init_query_vars() {
		// Query vars to add to WP.
		$this->query_vars = array(
			// My account actions.
			'order-history' => 'order-history',
			'order-detail'  => 'order-detail',
			'edit-profile'  => 'edit-profile',
			'addresses'     => 'addresses',
			'edit-address'  => 'edit-address',
			'forgot-password' => 'forgot-password',
			'logout'        => 'logout',
			'commercioo-order-received' => 'commercioo-order-received',
			'commercioo-confirmation-payment' => 'commercioo-confirmation-payment',
			'commercioo-thank-you-confirmation-payment' => 'commercioo-thank-you-confirmation-payment',
			'commercioo-checkout-wa' => 'commercioo-checkout-wa',
			'commercioo-payal' => 'commercioo-payal',
			'commercioo-payal-failed' => 'commercioo-payal-failed',
		);
	}

	/**
	 * Get query vars.
	 *
	 * @return array
	 */
	public function get_query_vars() {
		return apply_filters( 'comm_get_query_vars', $this->query_vars );
	}
    /**
     * Get query current active query var.
     *
     * @return string
     */
    public function get_current_endpoint() {
        global $wp;

        foreach ( $this->get_query_vars() as $key => $value ) {
            if ( isset( $wp->query_vars[ $key ] ) ) {
                return $key;
            }
        }
        return '';
    }
	/**
	 * Parse the request and look for query vars - endpoints may not be supported.
	 */
	public function parse_request() {
		global $wp;

		// phpcs:disable WordPress.Security.NonceVerification.Recommended
		// Map query vars to their keys, or get them if endpoints are not supported.
		foreach ( $this->get_query_vars() as $key => $var ) {
			if ( isset( $_GET[ $var ] ) ) {
				$wp->query_vars[ $key ] = sanitize_text_field( wp_unslash( $_GET[ $var ] ) );
			} elseif ( isset( $wp->query_vars[ $var ] ) ) {
				$wp->query_vars[ $key ] = $wp->query_vars[ $var ];
			}
		}
		// phpcs:enable WordPress.Security.NonceVerification.Recommended
	}

	/**
	 * Flush rewrite rules on if hash has changed
	 */
	public function maybe_flush_rules() {
		$query_vars = $this->get_query_vars();
		$hash = md5( serialize( $query_vars ) );
		if ( $hash !== get_option( 'commercioo_routes_hash' ) ) {
			flush_rewrite_rules();
			update_option( 'commercioo_routes_hash', $hash );
		}
	}
}