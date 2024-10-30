<?php
/**
 * Commercioo model class for product.
 *
 * @author Commercioo Team
 * @package Commercioo
 */

namespace Commercioo\Models;

use Commercioo\Abstracts\Post;

if ( ! defined( 'WPINC' ) ) {
	exit;
}

if ( ! class_exists( 'Commercioo\Models\Product' ) ) {

	/**
	 * Class Product
	 *
	 * @package Commercioo\Models
	 */
	class Product extends Post {

		/**
		 * Product ID
		 * 
		 * @var integer.
		 */
		public $product_id;

		/**
		 * Product constructor.
		 *
		 * @param int $product_id product id.
		 */
		public function __construct( $product_id ) {
			parent::__construct( $product_id );
			$this->product_id = $product_id;
		}

		/**
		 * Get product id.
		 *
		 * @return int
		 */
		public function get_product_id() {
			return $this->product_id;
		}

		/**
		 * Get product id.
		 *
		 * @return String
		 */
		public function get_product_name() {
			return $this->post->post_title;
		}

		/**
		 * Get product data
		 * 
		 * @return string Product data.
		 */
		public function get_product_data() {
			return 'standard';
		}

		/**
		 * Get product parent ID.
		 * 
		 * @return integer Parent post ID.
		 */
		public function get_parent_id() {
			return $this->post->post_parent;
		}

		/**
		 * Get product description
		 * 
		 * @return string Post content.
		 */
		public function get_description() {
			return $this->post->post_content;
		}
		
		/**
		 * Get product permalink
		 *
		 * @return String
		 */
		public function get_product_link() {
			return get_permalink( $this->product_id );
		}

		/**
		 * Get product regular price
		 * 
		 * @return float Product regular price.
		 */
		public function get_regular_price() {
			return apply_filters( 'commercioo_product_regular_price', floatval( $this->get_meta( '_regular_price', true ) ), $this->product_id );
		}

		/**
		 * Get product sale price
		 * 
		 * @return float Product sale price.
		 */
		public function get_sale_price() {
			if ( ! is_comm_pro() ) {
				return $this->get_regular_price();
			}
			return apply_filters( 'commercioo_product_sale_price', floatval( $this->get_meta( '_sale_price', true ) ), $this->product_id );
		}

		/**
		 * Get product price
		 * 
		 * @return float Product price.
		 */
		public function get_price() {
			if ( $this->is_on_sale() ) {
				return $this->get_sale_price();
			}
			return $this->get_regular_price();
		}

		/**
		 * Check whether product is on sale or not
		 * 
		 * @return boolean
		 */
		public function is_on_sale() {
			$regular_price = $this->get_regular_price();
			$sale_price    = $this->get_sale_price();
			return ! empty( $sale_price ) && $sale_price < $regular_price;
		}

		/**
		 * Get formatted regular price display
		 * 
		 * @param  string $open_tag  HTML open tag.
		 * @param  string $close_tag HTML close tag.
		 * @return string            Formatted regular price HTML.
		 */
		public function get_regular_price_display( $open_tag = '', $close_tag = '' ) {
			return $open_tag . \Commercioo\Helper::formatted_currency( $this->get_regular_price() ) . $close_tag;
		}

		/**
		 * Get formatted sale price display
		 * 
		 * @param  string $open_tag  HTML open tag.
		 * @param  string $close_tag HTML close tag.
		 * @return string            Formatted sale price HTML.
		 */
		public function get_sale_price_display( $open_tag = '', $close_tag = '' ) {
			return $open_tag . \Commercioo\Helper::formatted_currency( $this->get_sale_price() ) . $close_tag;
		}

		/**
		 * Get price display html
		 * 
		 * @return string
		 */
		public function get_price_display() {
			$html = '';
			if ( $this->is_on_sale() ) {
				$html .= $this->get_regular_price_display( '<del>', '</del> ' );
				$html .= $this->get_sale_price_display();
			} else {
				$html .= $this->get_regular_price_display();
			}
			return $html;
		}

		/**
		 * Get product SKU
		 * 
		 * @return string Product SKU.
		 */
		public function get_sku() {
			return apply_filters( 'commercioo_product_sku', $this->get_meta( '_sku', true ), $this->product_id );
		}

		/**
		 * Get product product gallery
		 * 
		 * @return string Product gallery.
		 */
		public function get_product_gallery() {
			return $this->get_meta( '_product_gallery', true );
		}

		/**
		 * Get image gallery
		 * 
		 * @return string Product gallery.
		 */
		public function get_image_gallery() {
			$gallery = $this->get_meta( '_product_gallery', true );
			if ( $gallery ) {
				$gallery_dummy = explode( ',', $gallery );
				$gallery = array();
				foreach ( $gallery_dummy as $value ) {
					$gallery[] = wp_get_attachment_url( $value );
				}
			}

			return $gallery;
		}

		/**
		 * Get product product featured
		 * 
		 * @return integer Product featured.
		 */
		public function get_product_featured() {
			return $this->get_meta( '_product_featured', true );
		}

		/**
		 * Get product image url
		 * 
		 * @param  string $size Image size.
		 * @return string Product image url if available.
		 */
		public function get_image_url( $size = 'thumbnail' ) {
			$image_id = $this->get_product_featured();
			if ( ! empty( $image_id ) ) {
				return wp_get_attachment_image_url( $image_id, $size );
			}
			return COMMERCIOO_URL . 'img/commercioo-no-img.png';
		}
		
		/**
		 * Get Categories
		 *
		 * @return false|\WP_Error|\WP_Term[]
		 */
		public function get_categories() {
			return get_the_terms( $this->product_id, 'comm_product_cat' );
		}

		/**
		 * Has Categories
		 *
		 * @return bool
         */
		public function has_categories( $category = '' ) {
			return has_term( $category, 'comm_product_cat', $this->product_id );
		}

		/**
		 * Is product featured
		 * 
		 * @return boolean Is product featured
		 */
		public function is_featured() {
			return boolval( $this->get_meta( '_is_featured', true ) );
		}

		/**
		 * Get product stock status
		 * 
		 * @return string Stock status.
		 */
		public function get_stock_status() {
			return apply_filters( 'commercioo_product_stock_status', $this->get_meta( '_stock_status', true ), $this->product_id );
		}

		/**
		 * Get product stock status label
		 * 
		 * @return string Stock status label.
		 */
		public function get_stock_status_label() {
			switch ( $this->get_stock_status() ) {
				case "instock":
					$label = __( "In Stock", "commercioo" );
					break;
				case "outofstock":
					$label = __( "Out of Stock", "commercioo" );
					break;
				case "onbackorder":
					$label = __( "Out of Stock", "commercioo" );
					break;
				default:
					$label = __( "In Stock", "commercioo" );
					break;
			}
			return $label;
		}

		/**
		 * Get product status label
		 * 
		 * @return string Status label.
		 */
		public function get_status_label() {
			switch ( $this->get_status() ) {
				case "publish":
					$label = __( 'Publish', 'commercioo' );
					break;
				case "draft":
					$label = __( 'Draft', 'commercioo' );
					break;
				case "trash":
					$label = __( 'Trash', 'commercioo' );
					break;
				default:
					$label = __( 'Publish', 'commercioo' );
					break;
			}
			return $label;
		}

		/**
		 * Get product additional description
		 * 
		 * @return string Additional description.
		 */
		public function get_additional_description() {
			return $this->get_meta( '_additional_description', true );
		}

		/**
		 * Get product included items
		 * 
		 * @return array Inlcuded items.
		 */
		public function get_included_items() {
			return $this->get_meta( '_included_items', true );
		}

		/**
		 * Get product weight
		 * 
		 * @return float Product weight.
		 */
		public function get_weight() {
			return apply_filters( 'commercioo_product_weight', floatval( $this->get_meta( '_weight', true ) ), $this->product_id );
		}

		/**
		 * Get product thank you redirect
		 * 
		 * @return string Thank you redirect.
		 */
		public function get_thank_you_redirect() {
			return $this->get_meta( '_thank_you_redirect', true );
		}

		/**
		 * Get product typ_msg
		 * 
		 * @return string Typ msg.
		 */
		public function get_typ_msg() {
			return $this->get_meta( '_typ_msg', true );
		}

		/**
		 * Is 2-step activated
		 * 
		 * @return boolean Is 2-step activated
		 */
		public function is_2_step() {
			return boolval( $this->get_meta( '_active_2_step', true ) );
		}

		/**
		 *  Get product step-1 field
		 * 
		 * @return string message
		 */
		public function step_1_field() {
			return $this->get_meta( '_step_1_field', true );
		}

		/**
		 *  Get product step-1 display
		 * 
		 * @return string message
		 */
		public function step_1_display() {
			return $this->get_meta( '_step_1_display', true );
		}

		/**
		 *  Get product step-1 message
		 * 
		 * @return string message
		 */
		public function step_1_message() {
			return $this->get_meta( '_step_1_message', true );
		}

		/**
		 *  Get product step-2 message
		 * 
		 * @return string message
		 */
		public function step_2_message() {
			return $this->get_meta( '_step_2_message', true );
		}
		/** Is overwrite checkout redirect
		 * 
		 * @return boolean overwrite checkout redirect
		 */
		public function overwrite_thank_you_redirect() {
			return boolval( $this->get_meta( '_overwrite_thank_you_redirect', true ) );
		}

	}
}