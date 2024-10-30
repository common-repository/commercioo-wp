<?php
$cart_content = apply_filters("commercioo/cart/fetch/get_items",array());
$product_id = apply_filters("commercioo/checkout/display/product/item",$cart_content);
?>

<div class="commercioo-checkout-summary-all-wrapper">
    <div class="commercioo-checkout-summary-item-wrapper">
        <div class="commercioo-checkout-summary-item">
            <div class="summary-item-single">
                <div class="subtotal-label">
                    <label class="label-item-product"><?php esc_html_e( 'Subtotal', 'commercioo' ) ?></label>
                </div>
                <div class="subtotal-price">
                    <input type="hidden" name="product_subtotal" value="<?php echo esc_attr(\Commercioo\Cart::get_subtotal());?>">
                    <label class="label-item-product price-label">
                        <?php echo \Commercioo\Helper::formatted_currency( \Commercioo\Cart::get_subtotal() ); ?>
                    </label>
                </div>
            </div>
        </div>
    </div>
    <?php do_action("commercioo/checkout/display/product/discount");?>
    <?php do_action("commercioo/checkout/display/fees");?>
    <div class="commercioo-checkout-summary-item-wrapper">
        <div class="commercioo-checkout-summary-item produk-shipping">
        </div>
    </div>
    <?php do_action("commercioo_view_unique_number");?>
</div>

<!--Display Order Total - Checkout-->
<?php do_action("commercioo/checkout/field/order/total"); ?>
