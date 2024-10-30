<?php
/**
 * Commercioo abstract class to send email related to order.
 *
 * @author Commecioo Team
 * @package Commercioo
 */

namespace Commercioo\Abstracts;

use Commercioo\Models\Customer;
use Commercioo\Models\Order;

if ( ! defined( 'WPINC' ) ) {
	exit;
}

if ( ! class_exists( 'Commercioo\Abstracts\Order_Mailer' ) ) {

	/**
	 * Class Mailer
	 *
	 * @package Commercioo\Abstracts
	 */
	abstract class Order_Mailer extends Mailer {

		/**
		 * Object of the order.
		 *
		 * @var Order
		 */
		private $order;

		/**
		 * Object of order's customer.
		 *
		 * @var Customer
		 */
		private $customer;

		/**
		 * Order_Mailer constructor.
		 *
		 * @param int $order_id order id.
		 */
		public function __construct( $order_id ) {
			$this->order            = new Order( $order_id );
			$this->customer         = $this->order->get_customer();
			$this->email_recipients = $this->order->get_billing_email();
		}

		/**
		 * Get order object.
		 *
		 * @return Order
		 */
		public function get_order() {
			return $this->order;
		}

		/**
		 * Get customer object.
		 *
		 * @return Customer
		 */
		public function get_customer() {
			return $this->customer;
		}
	}
}