<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
add_action( 'wp_enqueue_scripts', 'commercioo_single_product_scripts',11 );
if( ! function_exists( "commercioo_single_product_scripts" ) ){
    function commercioo_single_product_scripts(){
        if(!is_admin()) {
            // built in
            wp_enqueue_script('jquery');
            // enqueue styles
            wp_enqueue_style('font-awesome');
            wp_enqueue_style('owl-carousel');
            wp_enqueue_style('bootstrap');
            wp_enqueue_style('commercioo-gallery');
            wp_enqueue_style('commercioo-product');
            // enqueue scripts
            wp_enqueue_script('bootstrap');
            wp_enqueue_script('owl-carousel');
            wp_enqueue_script('feather');
            wp_enqueue_script('commercioo-gallery');
            wp_enqueue_script('commercioo-single-product');
            wp_enqueue_script('commercioo-product-archive');
        }
    }
}
$current_term = get_queried_object();
global $post;
set_query_var( 'breadcrumb', array(
    'Product' => comm_get_shopping_uri(),
    $post->post_title => '',
));
get_header("fullwidth");
comm_get_template_part('content','single-comm-product');
get_footer("fullwidth");