<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
add_action( 'wp_enqueue_scripts', 'commercioo_archive_product_scripts',11 );
if(!function_exists("commercioo_archive_product_scripts")){
    function commercioo_archive_product_scripts(){
        // enqueue scripts
        wp_enqueue_script( 'jquery' );
        wp_enqueue_script( 'jquery-toast' );
        wp_enqueue_script( 'popper' );
        wp_enqueue_script( 'bootstrap' );
        wp_enqueue_script( 'owl-carousel' );
        wp_enqueue_script( 'feather' );
        wp_enqueue_script( 'commercioo-gallery' );
        wp_enqueue_script( 'commercioo-single-product' );
        wp_enqueue_script( 'commercioo-product-archive' );

        // enqueue styles
        wp_enqueue_style( 'font-awesome' );
        wp_enqueue_style( 'jquery-toast' );
        wp_enqueue_style( 'bootstrap' );
        wp_enqueue_style( 'owl-carousel' );
        wp_enqueue_style( 'commercioo-archive' );
        wp_enqueue_style( 'commercioo-product' );
    }
}
set_query_var( 'breadcrumb', array(
    'Product' => "",
));
get_header( 'fullwidth' );
?>
        <?php
        the_content();
        comm_get_template_part('content','archive-term');
        ?>
<?php
get_footer( 'fullwidth' );