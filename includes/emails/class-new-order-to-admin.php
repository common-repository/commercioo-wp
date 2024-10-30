<?php
/**
 * New Order to Admin.
 * This email will be sent to admin on new order.
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

if ( ! class_exists( 'Commercioo\Emails\New_Order_To_Admin' ) ) {

	/**
	 * Class New_Order_To_Admin
	 *
	 * @package Commercioo\Emails
	 */
	class New_Order_To_Admin extends Order_Mailer {

		/**
		 * New_Order_To_Admin constructor.
		 *
		 * @param int $order_id object of the order.
		 */
		public function __construct( $order_id ) {
			parent::__construct( $order_id );

			// get email content
			$emails  = get_option( 'comm_emails_settings', array() );
			$subject = isset( $emails['mail_admin_new_order_notification']['subject'] ) ? $emails['mail_admin_new_order_notification']['subject'] : null;
			$content = isset( $emails['mail_admin_new_order_notification']['content'] ) ? $emails['mail_admin_new_order_notification']['content'] : null;
			
			// set subject & recipients
			$this->email_subject    = comm_do_parsing_tags( $subject, $order_id );
			$this->email_recipients = get_option( 'admin_email', null );

			// set content
			$parsed_content      = $this->parse_order_details( $content, $order_id );
			$this->email_content = comm_do_parsing_tags( $parsed_content, $order_id );
		}

		/**
		 * @return string
		 */
		function email_body() {
			$title         = sprintf( __( 'New Order On %s', 'commercioo' ), comm_tag_sitename() );
			$content       = $this->email_content;
			$settings      = get_option( 'comm_general_settings', array() );
			$store_address = isset( $settings['store_address'] ) ? $settings['store_address'] : '';
			$store_logo    = Helper::store_logo_url();

			return Template::render( 'emails/commercioo-emails', compact( 'title', 'content', 'store_address', 'store_logo' ) );
		}
	}
}