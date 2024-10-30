<?php

/**
 * Register all widget for the plugin
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    Commercioo
 * @subpackage Commercioo/includes
 */

/**
 * Register all widget for the plugin.
 *
 * Maintain a list of all hooks that are registered throughout
 * the plugin, and register them with the WordPress API. Call the
 * run function to execute the list of actions and filters.
 *
 * @package    Commercioo
 * @subpackage Commercioo/includes
 * @author     Your Name <email@example.com>
 */
class Commercioo_Widget_New_Product extends WP_Widget {

	/**
	 * The array of instance registered with WordPress.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      array    $_instance    The instance call widget when the plugin loads.
	 */
	protected static $_instance = null;

	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}
	function __construct() {
        $widget_ops = array(
            'classname'                   => 'commercioo_widget_new_product',
            'description'                 => __( 'This is Commercioo New Product Widget', 'wpb_widget_domain' ),
            'customize_selective_refresh' => true,
        );

		parent::__construct (
			'Commercioo_Widget_New_Product', 
			__('New Products by Commercioo', 'Commercioo_Widget_New_Product_domain'),
            $widget_ops
		);
		$this->init();
	}

	public function init() {
		add_action( 'widgets_init', array( $this, 'wpb_load_widget' ) );
	}
		// Creating widget front-end
	public function widget( $args, $instance ) {
        if ( ! isset( $args['widget_id'] ) ) {
            $args['widget_id'] = $this->id;
        }

        $output = '';

        $default_title = __( 'New Product' );

        $title         = ( ! empty( $instance['title'] ) ) ? $instance['title'] : $default_title;
        /** This filter is documented in wp-includes/widgets/class-wp-widget-pages.php */
        $title = apply_filters( 'widget_title', $title, $instance, $this->id_base );

        $number = ( ! empty( $instance['number_new_prod'] ) ) ? absint( $instance['number_new_prod'] ) : 4;
        if ( ! $number ) {
            $number = 4;
        }
        $prod_new = apply_filters("comm_calculate_new_product",$number);
        $output .= $args['before_widget'];
        $output .= "<div class='row'>";

        if ( $title ) {
            $output .= "<div class='col-md-12'><h1 class='product-conten-title'><a href='#' class='head-products'>".$title ."</a></h1>";
        }
        $i = 1;
        $maxProd = 4;
        $output .= "<div class='row'>";
        if(is_array($prod_new) && $prod_new){
            foreach ($prod_new as $pVal) {
                $product = comm_get_product( $pVal->ID );
                $link_prod = get_permalink($pVal->ID);
                $product_featured = get_post_meta($pVal->ID, "_product_featured", true);
                $output .="<div class='col-md-6 col-sm-6 set-display-flex mb-4'>";
                if ($product_featured){
                    $url = wp_get_attachment_image_src($product_featured, 'thumbnail-size', true);
                    $thumb_url = $url[0];
                }else{
                    $thumb_url = plugin_dir_url( dirname( __FILE__ ) ) . 'img/commercioo-no-img.png';
                }

                $output .="<img src='".esc_url($thumb_url)."' class='img-fluid image-product'>";
                $output .="<div class='ml-3'>
						<h5><a href='$link_prod' target='_blank' class='list-product-title'> 
						$pVal->post_title </a></h5>
							<div class='set-display-flex'> 
							</div>";
                if ( $product->is_on_sale() ) {
                    $output .= "<div class='card-text list-product-price'>" . $product->get_sale_price_display() . " </div>";
                    $output .= "<div class='card-text list-product-price'><del>" . $product->get_regular_price_display() . "</del> "."(".number_format( ( $product->get_regular_price() - $product->get_sale_price() ) / $product->get_regular_price() * 100 )."%)</div>";
                } else {
                    $output .= "<p class='card-text list-product-price'> " . $product->get_regular_price_display() . " </p>";
                }
                $output .= "</div>
				</div>";
                if ( $i++ == $maxProd ) break;
            }
        }
        $output .= "</div>";
        $output .= "</div>";
        $output .= "</div>";
        $output .= $args['after_widget'];
        echo wp_kses_post($output);
	}
	// Widget Backend 
	public function form( $instance ) {
        $title  = isset( $instance['title'] ) ? $instance['title'] : 'New Product';
        $number = isset( $instance['number_new_prod'] ) ? absint( $instance['number_new_prod'] ) : 4;
        ?>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id( 'title' )); ?>"><?php _e( 'Title:' ); ?></label>
            <input class="widefat" id="<?php echo esc_attr($this->get_field_id( 'title' )); ?>" name="<?php echo esc_attr($this->get_field_name( 'title' )); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
        </p>

        <p>
            <label for="<?php echo esc_attr($this->get_field_id( 'number_new_prod' )); ?>"><?php _e( 'Number of product(s) to show:' );
                ?></label>
            <input class="tiny-text" id="<?php echo esc_attr($this->get_field_id( 'number_new_prod' )); ?>" name="<?php echo
            esc_attr($this->get_field_name( 'number_new_prod' )); ?>" type="number" step="1" min="1" max="4" value="<?php echo esc_attr($number);
            ?>" size="3" />
        </p>
        <?php
	}

	// Updating widget replacing old instances with new
	public function update( $new_instance, $old_instance ) {
        $instance           = $old_instance;
        $instance['title']  = sanitize_text_field( $new_instance['title'] );
        $instance['number_new_prod'] = absint( $new_instance['number_new_prod'] );
        return $instance;
	}

    public function flush_widget_cache() {
        _deprecated_function( __METHOD__, '4.4.0' );
    }

	public function wpb_load_widget() {
		register_widget( 'Commercioo_Widget_New_Product' );
	}

}