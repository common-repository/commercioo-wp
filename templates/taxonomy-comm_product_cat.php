<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
add_action( 'wp_enqueue_scripts', 'commercioo_single_archive_product_scripts',11 );
if(!function_exists("commercioo_single_archive_product_scripts")){
    function commercioo_single_archive_product_scripts(){
        if(!is_admin()) {
            // enqueue scripts
            wp_enqueue_script('jquery');
            wp_enqueue_script('popper');
            wp_enqueue_script('bootstrap');
            wp_enqueue_script('owl-carousel');
            wp_enqueue_script('feather');
            wp_enqueue_script('commercioo-gallery');
            wp_enqueue_script('commercioo-single-product');
            wp_enqueue_script('commercioo-product-archive');

            // enqueue styles
            wp_enqueue_style('bootstrap');
            wp_enqueue_style('font-awesome');

            wp_enqueue_style('owl-carousel');
            wp_enqueue_style('commercioo-archive');
            wp_enqueue_style('commercioo-product');
        }
    }
}
$current_term = get_queried_object();
$slug_name = $current_term->name;
set_query_var( 'breadcrumb', array(
    'Product' => comm_get_shopping_uri(),
    $slug_name => '',
));
get_header( 'fullwidth' );
?>
    <main id="primary" class="site-main">
        <?php
        comm_get_template_part('content','archive-term');
        ?>
    </main>
<?php
get_footer( 'fullwidth' );