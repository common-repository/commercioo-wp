<?php
/**
 * Commercioo abstract class for post.
 *
 * @author Commercioo Team
 * @package Commercioo
 */

namespace Commercioo\Abstracts;

use WP_Post;

if ( ! defined( 'WPINC' ) ) {
	exit;
}


if ( ! class_exists( 'Commercioo\Abstracts\Post' ) ) {

	/**
	 * Class Post
	 *
	 * @package Commercioo\Abstracts
	 */
	abstract class Post {

		/**
		 * Post object variable.
		 *
		 * @var WP_Post
		 */
		protected $post;

		/**
		 * Post constructor.
		 *
		 * @param int $post_id post id.
		 */
		protected function __construct( $post_id ) {

			// Save post properties.
			$this->post = get_post( $post_id );
		}

		/**
		 * Get post object.
		 *
		 * @return array|WP_Post|null
		 */
		public function get_post() {
			return $this->post;
		}

		/**
		 * Get post title
		 * 
		 * @return string
		 */
		public function get_title() {
			return $this->post->post_title;
		}

		/**
		 * Get post status
		 * 
		 * @return string
		 */
		public function get_status() {
			return $this->post->post_status;
		}

		/**
		 * Get post type
		 * 
		 * @return string Post type.
		 */
		public function get_post_type() {
			return $this->post->post_type;
		}

		/**
		 * Get post meta.
		 *
		 * @param string $key meta key.
		 * @param bool $is_single whether display post meta as single string or array.
		 *
		 * @return mixed
		 */
		protected function get_meta( $key, $is_single = true ) {
			return get_post_meta( $this->get_post()->ID, $key, $is_single );
		}

		/**
		 * Update post meta.
		 *
		 * @param string $key meta key.
		 * @param mixed $value new meta value.
		 *
		 * @return bool|int
		 */
		protected function update_meta( $key, $value ) {
			return update_post_meta( $this->get_post()->ID, $key, $value );
		}

		/**
		 * Add post meta.
		 *
		 * @param string $key meta key.
		 * @param mixed $value new meta value.
		 * @param false $is_unique whether meta is unique or not.
		 *
		 * @return false|int
		 */
		protected function add_meta( $key, $value, $is_unique = false ) {
			return add_post_meta( $this->get_post()->ID, $key, $value, $is_unique );
		}

		/**
		 * Check whether a meta is exist.
		 *
		 * @param string $key meta key.
		 *
		 * @return bool
		 */
		protected function has_meta( $key ) {
			return metadata_exists( 'post', $this->get_post()->ID, $key );
		}
	}
}