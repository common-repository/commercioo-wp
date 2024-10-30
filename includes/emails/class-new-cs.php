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
use WP_User;

if ( ! defined( 'WPINC' ) ) {
	exit;
}

if ( ! class_exists( 'Commercioo\Emails\New_CS' ) ) {

	/**
	 * Class New_CS
	 *
	 * @package Commercioo\Emails
	 */
	class New_CS extends Mailer {

        /**
         * New_CS constructor.
         * @param $new_user
         * @param $pass
         */
		public function __construct( $user, $pass = null ) {
			// get email content
			$emails  = get_option( 'comm_emails_settings', array() );
			$subject = isset( $emails['mail_new_account_cs']['subject'] ) ? $emails['mail_new_account_cs']['subject'] : null;
			$content = isset( $emails['mail_new_account_cs']['content'] ) ? $emails['mail_new_account_cs']['content'] : null;
			$user 	 = is_integer( $user ) ? get_user_by( 'ID', $user ) : $user; 
			$pass 	 = $pass ? $pass : __( 'Your submitted password', 'commercioo' );
						
			// set subject, content and recipient
			$this->email_subject    = $this->parsing_tags( $subject, $user, $pass );
			$this->email_content    = $this->parsing_tags( $content, $user, $pass );
			$this->email_recipients = $user->user_email;

			// wp_die();
		}

		/**
		 * @return string
		 */
		function email_body() {
			return $this->email_content;
		}

		private function parsing_tags( $string, $user, $pass ) {
			$string = str_replace( '{name}', $user->display_name, $string );
			$string = str_replace( '{sitename}', comm_tag_sitename(), $string );
			$string = str_replace( '{login_url}', comm_tag_login_url(), $string );
			$string = str_replace( '{username}', $user->user_login, $string );
			$string = str_replace( '{admin_name}', comm_tag_admin_name(), $string );
			$string = str_replace( '{create_password_url}', site_url( 'wp-login.php?action=rp&key=' . get_password_reset_key( $user ) . '&login=' . rawurlencode( $user->user_login ) ), $string );

			return $string;
		}
	}
}