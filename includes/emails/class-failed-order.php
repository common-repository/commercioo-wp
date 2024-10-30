<?php
/**
 * Failed Order email.
 * This email will be sent for customer for failed order.
 *
 * @author Commercioo_Team
 * @package Commercioo
 */

namespace Commercioo\Emails;

use Commercioo\Abstracts\Order_Mailer;
use Commercioo\Template;

if ( ! defined( 'WPINC' ) ) {
	exit;
}

if ( ! class_exists( 'Commercioo\Emails\Failed_Order' ) ) {

	/**
	 * Class Failed_Order
	 *
	 * @package Commercioo\Emails
	 */
	class Failed_Order extends Order_Mailer {

		/**
		 * Failed_Order constructor.
		 *
		 * @param int $order_id object of the order.
		 */
		public function __construct( $order_id ) {
			parent::__construct( $order_id );

			$this->email_subject = 'Your Order is Failed, Here’s How to Fix it...';
		}

		/**
		 * @return string
		 */
		function email_body() {
			$order             = $this->get_order();
			$user_display_name = $order->get_customer()->get_user()->display_name;
			$order_id          = $order->get_order_id();
			$order_items       = $order->get_order_items();
			$site_name         = get_bloginfo( 'name' );
			$contact_url       = home_url( 'contact' );

			return Template::render( 'emails/failed-order', compact( 'user_display_name', 'order_id', 'order_items', 'site_name', 'contact_url' ) );
		}
	}
}