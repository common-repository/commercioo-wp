<?php
/**
 * Commercioo abstract class to send email.
 *
 * @author Commecioo Team
 * @package Commercioo
 */

namespace Commercioo\Abstracts;

if ( ! defined( 'WPINC' ) ) {
	exit;
}

if ( ! class_exists( 'Commercioo\Abstracts\Mailer' ) ) {

	/**
	 * Class Mailer
	 *
	 * @package Commercioo\Abstracts
	 */
	abstract class Mailer {

		/**
		 * Email recipients variable.
		 *
		 * @var array|string
		 */
		protected $email_recipients;

		/**
		 * Email subject variable.
		 *
		 * @var string
		 */
		protected $email_subject;

        /**
         * Email recipients variable.
         *
         * @var array|string
         */
        protected $email_content;

		/**
		 * Email custom header variable.
		 *
		 * @var array
		 */
		protected $email_header;

		/**
		 * Email attachment variable.
		 *
		 * @var array
		 */
		protected $email_attachment;
        /**
         * Holds the from name
         *
         * @since 2.1
         */
        private $from_name;
        /**
         * Holds the from address
         *
         * @since 2.1
         */
        private $from_address;
        /**
         * Holds the email content type
         *
         * @since 2.1
         */
        private $content_type;
        /**
         * Whether to send email in HTML
         *
         * @since 2.1
         */
        private $html = true;
		/**
		 * Get email body
		 *
		 * @return string plain string of the email body.
		 */
		abstract function email_body();

        /**
         * Get the email headers
         *
         * @since 2.1
         */
        public function get_headers() {
            if ( ! $this->email_header ) {
                $this->email_header  = "From: {$this->get_from_name()} <{$this->get_from_address()}>\r\n";
                $this->email_header .= "Reply-To: {$this->get_from_address()}\r\n";
                $this->email_header .= "Content-Type: {$this->get_content_type(true)}; charset=utf-8\r\n";
            }

            return apply_filters( 'comm_email_headers', $this->email_header, $this );
        }

        /**
         * @param bool $html
         * @return mixed|void
         */
        public function get_content_type($html=false) {
            if ( ! $this->content_type && $html ) {
                $this->content_type = apply_filters( 'edd_email_default_content_type', 'text/html', $this );
            } else if ( ! $html ) {
                $this->content_type = 'text/plain';
            }

            return apply_filters( 'comm_email_content_type', $this->content_type, $this );
		}
		
        /**
         * Get the email from address
         *
         * @since 2.1
         */
        public function get_from_address() {
            if ( ! $this->from_address ) {
                $this->from_address = get_option( 'from_email' );
            }

            if( empty( $this->from_address ) || ! is_email( $this->from_address ) ) {
                $this->from_address = get_option( 'admin_email' );
            }

            return apply_filters( 'comm_email_from_address', $this->from_address, $this );
		}
		
        /**
         * Get the email from name
         *
         * @since 2.1
         */
        public function get_from_name() {
            if ( ! $this->from_name ) {
                $this->from_name = get_option( 'from_name', get_bloginfo( 'name' ) );
            }

            return apply_filters( 'comm_email_from_name', wp_specialchars_decode( $this->from_name ), $this );
        }

		/**
		 * Perform send email.
		 *
		 * @return bool whether email successfully sent or not.
		 */
		public function send() {
			// Manually send email.
			return wp_mail( $this->email_recipients, $this->email_subject, $this->email_body(), $this->get_headers(), $this->email_attachment );
		}

		public function parse_order_details( $string, $order_id ) {
			$order         = new \Commercioo\Models\Order( $order_id );
			$order_details = \Commercioo\Template::render( 'emails/order-details', compact( 'order' ) );

			return str_replace( '{order_details}', $order_details, $string );
		}
	}
}