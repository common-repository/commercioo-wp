<?php
/**
 * Refunded Order email.
 * This email will be sent for customer for refunded order.
 *
 * @author Commercioo_Team
 * @package Commercioo
 */

namespace Commercioo\Emails;

use Commercioo\Abstracts\Order_Mailer;
use Commercioo\Template;
use Commercioo\Helper;

if ( ! defined( 'WPINC' ) ) {
	exit;
}

if ( ! class_exists( 'Commercioo\Emails\Refunded_Order' ) ) {

	/**
	 * Class Refunded_Order
	 *
	 * @package Commercioo\Emails
	 */
	class Refunded_Order extends Order_Mailer {

		/**
		 * Refunded_Order constructor.
		 *
		 * @param int $order_id object of the order.
		 */
		public function __construct( $order_id ) {
			parent::__construct( $order_id );
			
			// get email content
			$emails  = get_option( 'comm_emails_settings', array() );
			$subject = isset( $emails['mail_refund_order']['subject'] ) ? $emails['mail_refund_order']['subject'] : null;
			$content = isset( $emails['mail_refund_order']['content'] ) ? $emails['mail_refund_order']['content'] : null;
			
			// set subject
			$this->email_subject = comm_do_parsing_tags( $subject, $order_id );

			// set content
			$parsed_content      = $this->parse_order_details( $content, $order_id );
			$this->email_content = comm_do_parsing_tags( $parsed_content, $order_id );
		}

		/**
		 * @return string
		 */
		function email_body() {
			$title         = __( 'Order Refunded', 'commercioo' );
			$content       = $this->email_content;
			$settings      = get_option( 'comm_general_settings', array() );
			$store_address = isset( $settings['store_address'] ) ? $settings['store_address'] : '';
			$store_logo    = Helper::store_logo_url();

			return Template::render( 'emails/commercioo-emails', compact( 'title', 'content', 'store_address', 'store_logo' ) );
		}
	}
}