<?php
/**
 * Commercioo Thank_You.
 *
 * @author Commercioo Team
 * @package Commercioo
 */

namespace Commercioo;

if ( ! defined( 'WPINC' ) ) {
	exit;
}

if ( ! class_exists( 'Commercioo\Thank_You' ) ) {

	/**
	 * Class Thank_You
	 */
	class Thank_You {

		/**
		 * Default fields
		 * 
		 * @var array
		 */
		private $default_fields = array();

		/**
		 * Class instance
		 * 
		 */
		private static $instance;

		/**
		 * Order constructor.
		 *
		 * @param int $order_id order id.
		 */
		public function __construct() {
		}

		/**
		 * Get class instance
		 * 
		 * @return Thank_You Class instance.
		 */
		public static function get_instance() {
			if ( ! isset( self::$instance ) ) {
				self::$instance = new Thank_You();
			}
			return self::$instance;
		}

		/**
		 * Submit actions for Thank_You form.
		 */
		public static function do_confirmation_payment() {
			global $comm_options;

			// check nonce
			check_admin_referer('GwJpuj_HVaV604dHE', '_comm_thank_you_nonce');
            self::validate_thank_you_data();
            if ( !function_exists( 'wp_handle_upload' ) ) {
                require_once( ABSPATH . 'wp-admin/includes/file.php' );
            }

            // Check the type of file. We'll use this as the 'post_mime_type'.
            $filename = sanitize_post($_FILES['transfer_file']['name']);
            $tmp_name = sanitize_post($_FILES['transfer_file']['tmp_name']);
            $filetype = wp_check_filetype( basename( $filename ), null );

            // Get the path to the upload directory.
            $wp_upload_dir = wp_upload_dir();
            $upload_path = str_replace('/', DIRECTORY_SEPARATOR, $wp_upload_dir['path']) . DIRECTORY_SEPARATOR;
            // The ID of the post this attachment is for.
            $order_id = sanitize_text_field(absint($_POST['transfer_order_id']));

            // Prepare an array of post data for the attachment.
            $file_attachment = array(
                'guid'           => $wp_upload_dir['url'] . '/' . basename( $filename ),
                'post_mime_type' => $filetype['type'],
                'post_title'     => preg_replace( '/\.[^.]+$/', '', basename( $filename ) ),
                'post_content'   => '',
                'post_status'    => 'publish'
            );
            $attachments = get_posts( array(
                'post_type' => 'attachment',
                'post_parent' => $order_id,
                'numberposts' =>-1,
            ));
            if ($attachments) {
                foreach ($attachments as $attachment){
                    $parent_id = $attachment->post_parent;
                    if ( 'comm_order' == get_post_type($parent_id) ) {
                        $attachmentID = $attachment->ID;
                        $attachment_path = get_attached_file( $attachmentID);
                        //Delete attachment from database only, not file
                        $delete_attachment = wp_delete_attachment($attachmentID, true);
                        //Delete attachment file from disk
                        if ( file_exists( $attachment_path ) ) {
                            $delete_file = unlink($attachment_path);
                        }
                    }
                }
            }

            // Insert the attachment.
            $attach_id = wp_insert_attachment( $file_attachment, $wp_upload_dir['subdir']. '/' .$filename,$order_id);
            if($attach_id){
                $targetpath        =   $upload_path.$filename;
                move_uploaded_file($tmp_name, $targetpath);
            }
            // General parameter value for update into post meta
            $args = array(
                '_transfer_from_name'=>sanitize_text_field($_POST['transfer_from_name']),
                '_transfer_to_bank'=>sanitize_text_field($_POST['transfer_to_bank']),
                '_transfer_date'=>sanitize_text_field($_POST['transfer_date']),
                '_transfer_amount'=>sanitize_text_field($_POST['transfer_amount']),
                '_bukti_transfer_file'=>intval($attach_id),
                '_status_confirmation_payment'=>1,
                '_date_confirmation_payment'=>date("F d, Y h:i:s"),
            );
            // Process update into post meta
            // already available: discount, fee and unique number
            do_action("commercioo_update_post_meta",$args,$order_id);

			// redirect after order
            $return_url = \Commercioo\Helper::commercioo_get_endpoint_url( 'commercioo-thank-you-confirmation-payment', $order_id, comm_get_thank_you_uri() );
			wp_redirect(apply_filters('commercioo_redirect_url_after_order', $return_url, $order_id));
			exit;
		}

		/**
		 * Mainly used in `do_thank_you` method
		 */
		private static function validate_thank_you_data() {
			global $comm_options;

			// args
			$error_messages = array();
            $filesize = intval($_POST['commercioo_confirmation_payment_file_size']);
            $wp_max_upload_size = wp_max_upload_size();
            $order_id = absint($_POST['transfer_order_id']);
            $transfer_from_name = sanitize_text_field($_POST['transfer_from_name']);
            $transfer_amount = sanitize_text_field($_POST['transfer_amount']);
            if ( $filesize <= 0 || $filesize > $wp_max_upload_size) {
                $error_messages[] = sprintf(__("Maximum 'file upload size' is %s. You're file upload size is %s", 'commercioo'),size_format($wp_max_upload_size),size_format($filesize));
            }

            if(!$order_id){
                $error_messages[] = __('Invalid Order ID', 'commercioo');
            }

            if(!isset($transfer_from_name) || strlen(trim($transfer_from_name)) < 2){
                $error_messages[] = __('Minimum "Pengirim transfer atas nama" length is 2', 'commercioo');
            }

			// bail out for any errors found
			if (count($error_messages) > 0) {
				$wp_die_html = '<h1>' . esc_html__('Errors Found!', 'commercioo') . '</h1>';
				$wp_die_html .= '<p>' . esc_html__('Unfortunately, there are some errors occurred:', 'commercioo') . '</p>';
				$wp_die_html .= '<ul>';

				foreach ($error_messages as $message) {
					$wp_die_html .= '<li>' . esc_html($message) . '</li>';
				}

				$wp_die_html .= '</ul>';

				wp_die($wp_die_html, __('Error!', 'commercioo'), array(
					'back_link' => true,
				));
			}

			return true;
		}
	}
}