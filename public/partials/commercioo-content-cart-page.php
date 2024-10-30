<?php
/**
 * Commercioo Cart Form
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    Commercioo
 * @subpackage Commercioo/includes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<?php if ( ! Commercioo\Cart::is_empty() ) : ?>
    <div class="row">
        <div class="col-md-9">
            <table class="table">
                <thead>
                    <tr>
                        <th scope="col" class="wishlist-image-head c-no-border c-set-padding-left-0"><?php esc_html_e( 'Shopping Cart', 'commercioo' ) ?></th>
                        <th scope="col" class="wishlist-head c-no-border"><?php esc_html_e( 'PRICE', 'commercioo' ) ?></th>
                        <th scope="col" class="wishlist-head c-no-border"><?php esc_html_e( 'QUANTITY', 'commercioo' ) ?></th>
                        <th scope="col" class="wishlist-head c-no-border"><?php esc_html_e( 'SUB TOTAL', 'commercioo' ) ?></th>
                    </tr>
                </thead>
                <tbody class="tbody-wishlist tbody-wishlist-border-bottom ">

                    <?php foreach ( Commercioo\Cart::get_items() as $product_id => $item ) : ?>
                        <?php $product = comm_get_product( $product_id ); ?>

                        <tr data-id="<?php echo esc_attr($product_id); ?>" class="comm-parent-cart">
                            <td class="c-no-border">
                                <div class="row">
                                    <img src="<?php echo esc_url( $product->get_image_url() ); ?>" alt="<?php echo esc_attr( $product->get_title() ) ?>" class="img-fluid wishlist-image">
                                    <div class="ml-4 cart-content">
                                        <h2 class="wishlist-title"><?php echo esc_attr( $product->get_title() ); ?></h2>
                                        <span class="wishlist-desc mr-3">
                                            <a href="#" class="wishlist-desc comm-remove-item-cart" data-id="<?php echo esc_attr( $product_id ); ?>">
                                                <i class="feather-12 feather-close" data-feather="x"></i> <?php esc_html_e( 'Remove', 'commercioo' ) ?>
                                            </a>
                                        </span>
                                        <span class="wishlist-desc wishlist-product">
                                            <a href="#" class="wishlist-desc">
                                                <i class="feather-12 feather-close" data-feather="heart"></i> <?php esc_html_e( 'Add to wishlist', 'commercioo' ) ?>
                                            </a>
                                        </span>
                                    </div>
                                </div>
                            </td>
                            <td class="c-no-border wishlist-price">
                                <?php
                                if ( $product->is_on_sale() ) {
                                    echo wp_kses_post('<del>' . Commercioo\Helper::formatted_currency( $product->get_regular_price() ) . '</del> ');
                                }
                                echo esc_html(Commercioo\Helper::formatted_currency( $product->get_price()));
                                ?>
                            </td>
                            <td class="c-no-border">
                                <div class="btn-group" role="group" aria-label="Basic example">
                                    <span class="btn minus comm-minus-qty-cart">
                                        <i class="feather-16 feather-close" data-feather="minus"></i>
                                    </span>
                                    <input type="text" class="form-control form-qty comm-cart-qty-input" value="<?php echo esc_attr( $item['qty'] ); ?>" />
                                    <span class="btn plus comm-plus-qty-cart">
                                        <i class="feather-16 feather-close" data-feather="plus"></i>
                                    </span>
                                </div>
                            </td>
                            <td class="c-no-border wishlist-total">
                                <?php
                                if ( $product->is_on_sale() ) {
                                    echo wp_kses_post('<del>' . Commercioo\Helper::formatted_currency( $product->get_regular_price() * $item['qty'] ) . '</del> ');
                                }
                                echo esc_html(Commercioo\Helper::formatted_currency( $product->get_price() * $item['qty'] ));
                                ?>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="4" class="c-no-border">
                                <div class="set-border-bottom"></div>
                            </td>
                        </tr>

                    <?php endforeach;?>

                    <!-- Footer -->
                    <tr>
                        <td class="c-no-border" colspan="4">
                            <div class="row">
                                <div class="col-md-4 mb-2">
                                </div>
                                <div class="col-md-3">
                                </div>                    
                                <div class="col-md-2"></div>
                                <div class="col-md-3">
                                    <button class="btn btn-blue comm-add-to-cart"><?php esc_html_e( "UPDATE CART", 'commercioo' ) ?></button>
                                </div>
                            </div>
                        </td>
                    </tr>
                    <!-- End Footer -->

                </tbody>
            </table>
        </div>
        <div class="col-md-3">
            <!-- Shopping cart -->
            <div class="shopping-wrap">
                <table class="table">
                    <thead>
                        <tr>
                            <th scope="col" class="shopping-total">
                                <?php esc_html_e( 'CART TOTALS', 'commercioo' ) ?>
                            </th>
                        </tr>
                    </thead>
                    <?php include_once COMMERCIOO_PATH ."public/partials/commercioo-list-shopping-cart-total.php"; ?>
                </table>
                <a href="#" class="btn btn-checkout comm-checkout"><?php esc_html_e( 'PROCEED TO CHECKOUT', 'commercioo' ) ?></a>
            </div>
            <!-- End shopping cart -->

			<?php 
			if ( class_exists( 'Commercioo_Store_Pro' ) ) {
				echo do_shortcode( '[commercioo_recently_viewed_products]' );
				echo do_shortcode( '[commercioo_related_cart]' );
			}				
			?>
        </div>
    </div>
<?php else: ?>
    <div class="row">
        <div class="col-md-9">
            <table class="table">
                <thead>
                    <tr>
                        <th scope="col" class="wishlist-image-head c-no-border c-set-padding-left-0"><?php esc_html_e( 'Shopping Cart', 'commercioo' ) ?></th>
                    </tr>
                </thead>
                <tbody class="tbody-wishlist">
                    <tr>
                        <td class="c-no-border">
                            <div class="col-md-9 mb-2">
                                <span class="desc-empty"><?php esc_html_e( 'Cart is empty, go to shop page now.', 'commercioo' ) ?></span>
                            </div>
                            <div class="col-md-3">
                                <button class="btn btn-blue comm-shop-now"><?php esc_html_e( 'SHOP NOW', 'commercioo' ) ?></button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="col-md-3">
            <?php if ( class_exists( 'Commercioo_Store_Pro' ) ) echo do_shortcode( '[commercioo_recently_viewed_products]' ); ?>
        </div>
    </div>
<?php endif;?>