<?php
/**
 * New Customer email.
 * This email will be sent for a newly created customer.
 *
 * @author Commercioo_Team
 * @package Commercioo
 */

namespace Commercioo\Emails;

use Commercioo\Abstracts\Mailer;
use Commercioo\Template;
use Commercioo\Helper;
use WP_User;

if ( ! defined( 'WPINC' ) ) {
	exit;
}

if ( ! class_exists( 'Commercioo\Emails\New_Customer' ) ) {

	/**
	 * Class New_Customer
	 *
	 * @package Commercioo\Emails
	 */
	class New_Customer extends Mailer {

        /**
         * New_Customer constructor.
         * @param $new_user
         * @param $pass
         */
		public function __construct( $user, $pass = null ) {			
			// get email content
			$emails  = get_option( 'comm_emails_settings', array() );
			$subject = isset( $emails['mail_new_account_customer']['subject'] ) ? $emails['mail_new_account_customer']['subject'] : null;
			$content = isset( $emails['mail_new_account_customer']['content'] ) ? $emails['mail_new_account_customer']['content'] : null;
			$user 	 = is_integer( $user ) ? get_user_by( 'ID', $user ) : $user; 
			$pass 	 = $pass ? $pass : __( 'Your submitted password', 'commercioo' );
						
			// set subject, content and recipient
			$this->email_subject    = $this->parsing_tags( $subject, $user, $pass );
			$this->email_content    = $this->parsing_tags( $content, $user, $pass );
			$this->email_recipients = $user->user_email;
		}

		/**
		 * @return string
		 */
		function email_body() {
			$title         = sprintf( __( 'Welcome to %s', 'commercioo' ), comm_tag_sitename() );
			$content       = $this->email_content;
			$settings      = get_option( 'comm_general_settings', array() );
			$store_address = isset( $settings['store_address'] ) ? $settings['store_address'] : '';
			$store_logo    = Helper::store_logo_url();

			return Template::render( 'emails/commercioo-emails', compact( 'title', 'content', 'store_address', 'store_logo' ) );
		}

		private function parsing_tags( $string, $user, $pass ) {
			$string = str_replace( '{name}', $user->display_name, $string );
			$string = str_replace( '{sitename}', comm_tag_sitename(), $string );
			$string = str_replace( '{login_url}', comm_tag_login_url(), $string );
			$string = str_replace( '{username}', $user->user_login, $string );
			$string = str_replace( '{password}', $pass, $string );

			return $string;
		}
	}
}