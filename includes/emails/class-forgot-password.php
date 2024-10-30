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

if ( ! class_exists( 'Commercioo\Emails\Forgot_Password' ) ) {

	/**
	 * Class Forgot_Password
	 *
	 * @package Commercioo\Emails
	 */
	class Forgot_Password extends Mailer {

        /**
         * Forgot_Password constructor.
         * @param $new_user
         * @param $pass
         */
		public function __construct( $user, $key ) {			
			// get email content
			$emails  = get_option( 'comm_emails_settings', array() );
			$subject = isset( $emails['mail_reset_password']['subject'] ) ? $emails['mail_reset_password']['subject'] : null;
			$content = isset( $emails['mail_reset_password']['content'] ) ? $emails['mail_reset_password']['content'] : null;
			$user 	 = is_integer( $user ) ? get_user_by( 'ID', $user ) : $user; 
						
			// set subject, content and recipient
			$this->email_subject    = $this->parsing_tags( $subject, $user );
			$this->email_content    = $this->parsing_tags( $content, $user, $key );
			$this->email_recipients = $user->user_email;
		}

		/**
		 * @return string
		 */
		function email_body() {
			$title         = sprintf( __( 'Reset Password on %s', 'commercioo' ), comm_tag_sitename() );
			$content       = $this->email_content;
			$settings      = get_option( 'comm_general_settings', array() );
			$store_address = isset( $settings['store_address'] ) ? $settings['store_address'] : '';
			$store_logo    = Helper::store_logo_url();

			return Template::render( 'emails/commercioo-emails', compact( 'title', 'content', 'store_address', 'store_logo' ) );
		}

		private function parsing_tags( $string, $user, $key = '' ) {
			$string = str_replace( '{name}', $user->display_name, $string );
			$string = str_replace( '{sitename}', comm_tag_sitename(), $string );
			$string = str_replace( '{reset_password_url}', add_query_arg( array( 'key' => $key, 'id' => $user->ID ), site_url( 'account/forgot-password/' ) ), $string );
			$string = str_replace( '{username}', $user->user_login, $string );

			return $string;
		}
	}
}