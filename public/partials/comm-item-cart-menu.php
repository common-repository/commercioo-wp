<div class="col-md-12">
    <div class="popover-shopping-wrap" style="background: #FFFFFF;">
        <table class="table tableBodyScroll">
            <?php if ( ! Commercioo\Cart::is_empty() ): ?>
                <thead class="shopping-thead">
                    <tr>
                        <th scope="col" class="shopping-head">
                            <?php echo esc_html( Commercioo\Cart::get_items_count() ); ?> <?php esc_html_e( 'ITEMS', 'commercioo' ) ?>
                            <a href="<?php echo comm_get_cart_uri(); ?>" class="link-item-cart">
                                <span>VIEW CART</span>
                            </a>
                        </th>
                    </tr>
                </thead>
                <tbody class="shopping-tbody">
                    <?php foreach ( Commercioo\Cart::get_items() as $product_id => $item ) : ?>
                        <?php $product = comm_get_product( $product_id ); ?>

                        <tr class="shoopping-tr">
                            <td class="c-no-border">
                                <div class="row row-shopping-cart">
                                    <div>
                                        <img src="<?php echo esc_url( $product->get_image_url() ); ?>" alt="<?php echo esc_attr( $product->get_title() ) ?>" class="img-fluid wishlist-shopping">
                                    </div>
                                    <div class="ml-2 mr-3">
                                        <div class="row-shopping-cart-item">
                                            <div>
                                                <h2 class="shopping-title"><?php echo esc_html( mb_strimwidth($product->get_title(), 0, 23, '...')) ?><span class="c-set-color-gray"> (x<?php echo esc_html( $item['qty'] ); ?>)</span></h2>
                                                <div class="shopping-price">
                                                    <?php
                                                    if ( $product->is_on_sale() ) {
                                                        echo wp_kses_post('<del>' . Commercioo\Helper::formatted_currency( $product->get_regular_price() * $item['qty'] ) . '</del> ');
                                                    }
                                                    ?>
                                                </div>
                                                <div class="shopping-price">
                                                    <?php  echo esc_html(Commercioo\Helper::formatted_currency( $product->get_price() * $item['qty'] )); ?>
                                                </div>
                                            </div>
                                            <div class="header-remove-cart-wrapper">
                                                <a href="#" class="close-shopping comm-remove-item-cart" data-id="<?php echo esc_attr( $product_id ); ?>"><i class="feather-16" data-feather="x"></i></a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot class="shopping-tfoot">
                    <tr>
                        <th scope="col" class="shopping-head">
                            <?php esc_html_e( 'SUBTOTAL', 'commercioo' ) ?> :
                            <span class="subtotal-right"><?php echo esc_html(Commercioo\Helper::formatted_currency(Commercioo\Cart::get_total() - Commercioo\Cart::get_fee_total() - Commercioo\Cart::get_unique_number())); ?></span>
                        </th>
                    </tr>
                </tfoot>
            <?php else: ?>
                <tr>
                    <td class="c-no-border empty-shopping-title">
                        <?php esc_html_e( "Your cart is empty","commercioo" );?>
                    </td>
                </tr>
            <?php endif; ?>
        </table>
        <?php if ( ! Commercioo\Cart::is_empty() ): ?>
            <a href="#" class="btn btn-checkout comm-checkout"><?php esc_html_e( 'CHECKOUT', 'commercioo' ) ?></a>
        <?php else: ?>
            <button class="btn btn-blue comm-shop-now"><?php esc_html_e( 'SHOP NOW', 'commercioo' ) ?></button>
        <?php endif; ?>
    </div>
</div>
<!-- End Show cart -->