<?php
/**
 * Commercioo Product
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    Commercioo
 * @subpackage Commercioo/includes
 */

if (!defined('ABSPATH')) {
    exit;
}
wp_enqueue_script('commercioo-owl.carousel.min', COMMERCIOO_URL . 'public/js/vendor/owl.carousel.min.js');
wp_enqueue_script('commercioo-feather.min', COMMERCIOO_URL . 'public/js/vendor/feather.min.js');
wp_enqueue_script('commercioo-gallery', COMMERCIOO_URL . 'public/js/product/gallery.js');
wp_enqueue_script('commercioo-single-product', COMMERCIOO_URL . 'public/js/product/single-product.js');
wp_enqueue_script('commercioo-product-archive', COMMERCIOO_URL . 'public/js/product/product-archive.js');

wp_enqueue_style('commercioo-owl.carousel.min', COMMERCIOO_URL . 'public/css/vendor/owl.carousel.min.css', array(), COMMERCIOO_VERSION,'all');
wp_enqueue_style('commercioo-archive', COMMERCIOO_URL . 'public/css/gallery-archive.css', array(), COMMERCIOO_VERSION, 'all');
wp_enqueue_style('commercioo-product', COMMERCIOO_URL . 'public/css/commercio-product.css', array(), COMMERCIOO_VERSION, 'all');

comm_get_template_part('content','archive-term');