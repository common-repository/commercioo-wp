<?php
/**
 * Canceled Order email.
 * This email will be sent for customer for canceled order.
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

if ( ! class_exists( 'Commercioo\Emails\Canceled_Order' ) ) {

	/**
	 * Class Canceled_Order
	 *
	 * @package Commercioo\Emails
	 */
	class Canceled_Order extends Order_Mailer {

		/**
		 * Canceled_Order constructor.
		 *
		 * @param int $order_id object of the order.
		 */
		public function __construct( $order_id ) {
			parent::__construct( $order_id );

			$this->email_subject = sprintf( 'Your Order %s has been Canceled', $order_id );
		}

		/**
		 * @return string
		 */
		function email_body() {
			$order             = $this->get_order();
			$user_display_name = $order->get_customer()->get_user()->display_name;
			$order_id          = $order->get_order_id();
			$order_items       = $order->get_order_items();

			return Template::render( 'emails/canceled-order',
				compact( 'user_display_name', 'order_id', 'order_items' ) );
		}
	}
}