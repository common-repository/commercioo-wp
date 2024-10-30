<?php
/**
 * Default Emails.
 * The default emails subjects and contents
 *
 * @author Commercioo_Team
 * @package Commercioo
 */

namespace Commercioo\Emails;

if ( ! defined( 'WPINC' ) ) {
	exit;
}

if ( ! class_exists( 'Commercioo\Emails\Default_Emails' ) ) {

	/**
	 * Class Default_Emails
	 *
	 * @package Commercioo\Emails
	 */
	class Default_Emails {

		// default hardcoded settings
		public $default_settings;

		/** 
		 * the current emails settings from database
		 * if empty on any particular key, use the default value instead
		 */
		public $current_settings;

		public function __construct() {
			$this->default_settings = $this->get_default_email_settings();
			$this->current_settings = $this->get_current_email_settings();
		}

		private function get_current_email_settings() {
			$email_settings = $this->get_default_email_settings();
			$from_database  = get_option( 'comm_emails_settings', array() );

			// replace the hardcoded one with the data from database if any
			foreach ( $email_settings as $key => $value ) {
				if ( isset( $from_database[ $key ] ) ) {
					$email_settings[ $key ] = $from_database[ $key ];
				}
			}

			return $email_settings;
		}

		private function get_default_email_settings() {
			return array(
				'mail_new_account_cs' => array(
					'subject' => 'You have been registered on {sitename}',
					'content' => $this->get_default_email_content( 'mail_new_account_cs' ),
				),
				'mail_new_account_customer' => array(
					'subject' => 'Welcome to {sitename}, here’s your account details',
					'content' => $this->get_default_email_content( 'mail_new_account_customer' ),
				),
				'mail_new_auto_registered_account_customer' => array(
					'subject' => 'Welcome to {sitename}, here’s your account details',
					'content' => $this->get_default_email_content( 'mail_new_auto_registered_account_customer' ),
				),
				'mail_reset_password' => array(
					'subject' => 'Important, your new password on {sitename}',
					'content' => $this->get_default_email_content( 'mail_reset_password' ),
				),
				'mail_pending_order' => array(
					'subject' => '{name}, your order details on {sitename}',
					'content' => $this->get_default_email_content( 'mail_pending_order' ),
				),
				'mail_processing_order' => array(
					'subject' => '{name}, we’re processing your order with ID #{order_id}',
					'content' => $this->get_default_email_content( 'mail_processing_order' ),
				),
				'mail_completed_order' => array(
					'subject' => 'Your order with ID #{order_id} is now complete',
					'content' => $this->get_default_email_content( 'mail_completed_order' ),
				),
				'mail_refund_order' => array(
					'subject' => 'We’ve refunded your order with ID #{order_id}',
					'content' => $this->get_default_email_content( 'mail_refund_order' ),
				),
				'mail_failed_order' => array(
					'subject' => 'Your order failed, here’s how to fix it...',
					'content' => $this->get_default_email_content( 'mail_failed_order' ),
				),
				'mail_cancelled_order' => array(
					'subject' => 'Your order with ID #{order_id} has been canceled',
					'content' => $this->get_default_email_content( 'mail_cancelled_order' ),
				),
				'mail_admin_new_order_notification' => array(
					'subject' => 'New order on {sitename}',
					'content' => $this->get_default_email_content( 'mail_admin_new_order_notification' ),
				),
				'mail_admin_new_customer_account_notification' => array(
					'subject' => 'New customer account on {sitename}',
					'content' => $this->get_default_email_content( 'mail_admin_new_customer_account_notification' ),
				),
			);
		}
		
		private function get_default_email_content( $type ) {
			switch ( $type ) {
				case 'mail_new_account_cs':
					ob_start();
					?>
					<p>
						Admin of {sitename} has registered you as a staff account. Please use the following info to login.
					</p>
					<p>
						Username: {username}<br>
						Password: <a href="{create_password_url}">Click Here</a> to create your password.
					</p>
					<p>
						Cheers,<br>
						{admin_name}
					</p>
					<?php
					$content = ob_get_clean();
					break;

				case 'mail_new_account_customer':
					ob_start();
					?>
					<p>
						Dear {name},
					</p>
					<p>
						Congratulations and welcome to {sitename}, your new account has been created. Please use the following info to login:
					</p>
					<p>
						Site URL: {login_url}<br>
						Username: {username}<br>
						Password: {password}
					</p>
					<p>
						Best Regards,<br>
						{sitename} Team
					</p>
					<?php
					$content = ob_get_clean();
					break;

				case 'mail_new_auto_registered_account_customer':
					ob_start();
					?>
					<p>
						Dear {name},
					</p>
					<p>
						Congratulations and welcome to {sitename}, your new account has been created. Please use the following info to login:
					</p>
					<p>					
						Username: {username}<br>
						Password: <a href="{create_password_url}">Click Here</a> to create your password.
					</p>
					<p>
						Best Regards,<br>
						{sitename} Team
					</p>
					<?php
					$content = ob_get_clean();
					break;

				case 'mail_reset_password':
					ob_start();
					?>
					<p>
						Hi {name},
					</p>
					<p>
						Click the link below to reset your account password:
					</p>
					<p>
						{reset_password_url}
					</p>
					<p>
						If you didn't request a new password, you can safely delete or ignore this email.
					</p>
					<p>
						Got any questions? Feel free to contact us at {support_email}.
					</p>
					<p>
						Thanks,<br>
						{sitename} Team
					</p>
					<?php
					$content = ob_get_clean();
					break;

				case 'mail_pending_order':
					ob_start();
					?>
					<p>
						Hello {name},
					</p>
					<p>
						Thank you for placing an order on {sitename}. We’re glad to inform you that we’ve received your order and now it’s still pending.
					</p>
					<p>
						Please complete your order by making a payment to one of the following bank accounts:
					</p>
					<p>
						{bank}
					</p>
					<p>
						{order_details}
					</p>
					<p>
						Thank you again for choosing {sitename} for your purchase.
					</p>
					<p>
						Best regards,<br>
						{sitename} Team
					</p>
					<?php
					$content = ob_get_clean();
					break;

				case 'mail_processing_order':
					ob_start();
					?>
					<p>
						Hello {name},
					</p>
					<p>
						Great news! Your order is on its way.
					</p>
					<p>
						This email is to notify you that your order {order_id} has successfully shipped.
					</p>
					<p>
						{order_details}
					</p>
					<p>
						Thanks again for choosing us for your purchase. If you have any questions on the product, please contact us at {support_email}.
					</p>
					<p>
						Best regards,<br>
						{sitename} Team
					</p>
					<?php
					$content = ob_get_clean();
					break;

				case 'mail_completed_order':
					ob_start();
					?>
					<p>
						Dear {name},
					</p>
					<p>
						Your package has been completed and delivered to your address.
					</p>
					<p>
						{order_details}
					</p>
					<p>
						Thanks again for choosing us for your purchase. If you have any questions or comments on the product, please contact us at {support_email}.
					</p>
					<p>
						Best regards,<br>
						{sitename} Team
					</p>
					<?php
					$content = ob_get_clean();
					break;

				case 'mail_refund_order':
					ob_start();
					?>
					<p>
						Hi {name},
					</p>
					<p>
						We hope this message finds you well.
					</p>
					<p>
						We are reaching to inform you that we have just refunded your order {order_id} with total amount {subtotal}.
					</p>
					<p>
						{order_details}
					</p>
					<p>
						Please feel welcome to reply to this email with any questions you may have and we would be more than happy to help.
					</p>
					<p>
						Thanks,<br>
						{sitename} Team
					</p>
					<?php
					$content = ob_get_clean();
					break;

				case 'mail_failed_order':
					ob_start();
					?>
					<p>
						Payment for order {order_id} on {sitename} has failed. The order was as follows:
					</p>
					<p>
						{order_details}
					</p>
					<p>
						Please click the following link to fix it and continue to complete your purchase:
					</p>
					<p>
						{complete_purchase_url}
					</p>
					<p>
						If you have any questions, please reply to this email.
					</p>
					<p>
						Cheers,<br>
						{sitename} Team
					</p>
					<?php
					$content = ob_get_clean();
					break;

				case 'mail_cancelled_order':
					ob_start();
					?>
					<p>
						Hello {name},
					</p>
					<p>
						We have received your cancelation request for order {order_id} with details as follow: 
					</p>
					<p>
						{order_details}
					</p>
					<p>
						We are sending this email to let you know that we have canceled your order.
					</p>
					<p>
						There will be no need for any further action from your end.
					</p>
					<p>
						If you have any further questions, please reply to this email.
					</p>
					<p>
						Wishing you all the best,<br>
						{sitename} Team
					</p>
					<?php
					$content = ob_get_clean();
					break;

				case 'mail_admin_new_order_notification':
					ob_start();
					?>
					<p>
						There is a new order on {sitename} from {name} with order ID {order_id}
					</p>
					<p>
						{order_details}
					</p>
					<p>
						Best regards,<br>
						The Commercioo Team
					</p>
					<?php
					$content = ob_get_clean();
					break;
					
				case 'mail_admin_new_customer_account_notification':
					ob_start();
					?>
					<p>
						There is a new customer account on {sitename}
					</p>
					<p>
						Below are the account details:
					</p>
					<p>
						Name: {name}<br>
						Email: {customer_email}<br>
						Username: {username}
					</p>
					<p>
						Best regards,<br>
						The Commercioo Team
					</p>
					<?php
					$content = ob_get_clean();
					break;
				
				default:
					$content = null;
					break;
			}

			return $content;
		}
	}
}