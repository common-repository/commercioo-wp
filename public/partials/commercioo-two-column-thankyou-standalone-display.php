<?php
/**
 * Commercioo Standalone Thankyou Page
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

global $comm_options;

// shop logo
if (isset($comm_options['store_logo'])) {
    $thumb_id = intval($comm_options['store_logo']);
    $thumb = wp_get_attachment_image_src($thumb_id, 'full');
    $logo_url = $thumb ? $thumb[0] : COMMERCIOO_URL . 'img/commercioo-logo.svg';
} else {
    $logo_url = COMMERCIOO_URL . 'img/commercioo-logo.svg';
}
$order = new \Commercioo\Models\Order($order_id);
$shipping_address = $order->get_shipping_address();
?>

<div class='commercioo-checkout-container' id="commercioo-checkout-container" data-color="<?php echo esc_attr($colors);?>">
    <div class="commercioo-checkout-layout">
        <div class="wrap-container wrap-content commercioo-thankyou-page">
            <div class="form_wrapper">
                    <!-- bank info-->
                    <?php echo apply_filters("commercioo_order_payment_method_thank_you", $order_id, $payment_method); ?>
                    <!--end bank info-->

                    <!--address-->
                    <div class="commercioo-thankyou-list-customer-address">
                        <div class="address-item">
                            <div class="label-title">BILLING ADDRESS</div>
                            <div class="commercioo-checkout-description-product-medium">
                                <?php echo wp_kses_post($order->get_formatted_address('billing', '</div><div>')); ?>
                            </div>
                        </div>
                        <div class="address-item">
                            <div class="label-title">SHIPPING ADDRESS</div>
                            <div class="commercioo-checkout-description-product-medium">
                                <?php echo wp_kses_post($order->get_formatted_address('shipping', '</div><div>')); ?>
                            </div>
                        </div>
                    </div>
                    <!-- end address-->
            </div>
            <!-- order detail-->
            <div class="form_wrapper">
                <div class="form_container">
                    <div class="clearfix">
                        <div>
                            <div class="commercioo-checkout-title-product">
                                <?php esc_html_e('ORDER DETAILS', 'commercioo'); ?>
                            </div>
                        </div>
                    </div>
                    <div class="clearfix cc-ml0-mr-0">
                        <div class="commercioo-checkout-order-summary-item tyfirst">
                            <div class="container-item-product-first">
                                <div>
                                    <label class="label-summary-product thank-you"><?php esc_html_e('Order ID', 'commercioo'); ?></label>
                                </div>
                                <div class="set-text-align-right">
                                    <label class="label-summary-product c-semibold"><?php echo esc_html($order_id); ?></label>
                                </div>
                            </div>
                            <div class="container-item-product-first">
                                <div>
                                    <label class="label-summary-product thank-you"><?php esc_html_e('Date', 'commercioo'); ?></label>
                                </div>
                                <div class="set-text-align-right">
                                    <label class="label-summary-product c-semibold"><?php echo esc_html(comm_tag_date($order_id)); ?></label>
                                </div>
                            </div>
                            <div class="container-item-product-first">
                                <div>
                                    <label class="label-summary-product thank-you"><?php esc_html_e('Payment Method', 'commercioo'); ?></label>
                                </div>
                                <div class="set-text-align-right">
                                    <label class="label-summary-product c-semibold"><?php echo esc_html(comm_payment_method_label($order_id)); ?></label>
                                </div>
                            </div>
                            <div class="container-item-product-first">
                                <div>
                                    <label class="label-summary-product thank-you"><?php esc_html_e('Status', 'commercioo'); ?></label>
                                </div>
                                <div class="set-text-align-right">
                                    <label class="label-summary-product c-semibold cc-set-color-red"><?php echo esc_html(comm_tag_status_order($order_id)); ?></label>
                                </div>
                            </div>
                        </div>
                        <?php  if($order_items):?>
                        <div class="commercioo-checkout-order-summary-item tysecond">
                            <?php foreach ($order_items as $item) : ?>
                                <?php $item_total_price = $item->item_price * $item->item_order_qty; ?>
                                <div class="container-item-product-first">
                                    <div>
                                        <label class="label-summary-product thank-you"><?php echo esc_html($item->item_name); ?>
                                            <span class="product-quantity"><?php echo esc_html($item->item_order_qty); ?></span></label>
                                    </div>
                                    <div class="set-text-align-right">
                                        <?php if($item->item_sales_price>0):?>
                                            <label class="label-summary-product c-semibold"><?php echo wp_kses_post('<del>' . \Commercioo\Helper::formatted_currency( $item->item_price) . '</del> ');?></label>
                                            <label class="label-summary-product c-semibold"><?php echo esc_html(\Commercioo\Helper::formatted_currency($item->item_sales_price)); ?></label>
                                        <?php else:?>
                                            <label class="label-summary-product c-semibold"><?php echo wp_kses_post(\Commercioo\Helper::formatted_currency( $item->item_price));?></label>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach ?>
                        </div>
                        <?php endif; ?>
                        <div class="commercioo-checkout-order-summary-item tysecond">
                            <div class="container-item-product-first">
                                <div>
                                    <label class="label-summary-product thank-you"><?php esc_html_e('Subtotal', 'commercioo'); ?></label>
                                </div>
                                <div class="set-text-align-right">
                                    <label class="label-summary-product c-semibold"><?php echo esc_html(comm_money_format($order->get_subtotal())); ?></label>
                                </div>
                            </div>
                            <?php if ($order->has_fee()) : ?>
                                <?php foreach ($order->get_fees() as $fee) : ?>
                                    <div class="container-item-product-first">
                                        <div>
                                            <label class="label-summary-product thank-you"><?php echo esc_html($fee['name']); ?></label>
                                        </div>
                                        <div class="set-text-align-right">
                                            <label class="label-summary-product c-semibold"><?php echo esc_html(\Commercioo\Helper::formatted_currency($fee['amount'])); ?></label>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif ?>
                            <?php if ($order->has_shipping()) : ?>
                                <div class="container-item-product-first">
                                    <div>
                                        <label class="label-summary-product thank-you"><?php printf("%s (%s)", __('Shipping', 'commercioo'), $order->get_shipping_method()); ?></label>
                                    </div>
                                    <div class="set-text-align-right">
                                        <?php if ($order->has_free_shipping()) { ?>
                                            <label class="label-summary-product c-semibold">Free Shipping</label>
                                        <?php } else { ?>
                                            <label class="label-summary-product c-semibold"><?php echo esc_html(\Commercioo\Helper::formatted_currency($order->get_shipping_price())); ?></label>
                                        <?php } ?>
                                    </div>
                                </div>
                            <?php elseif ($order->has_free_shipping()) : ?>
                                <div class="container-item-product-first">
                                    <div>
                                        <label class="label-summary-product thank-you"><?php printf("%s", __('Shipping', 'commercioo')); ?></label>
                                    </div>
                                    <div class="set-text-align-right">
                                        <label class="label-summary-product c-semibold">Free Shipping</label>
                                    </div>
                                </div>
                            <?php endif ?>
                            <?php if ($order->get_unique_code()): ?>
                                <div class="container-item-product-first">
                                    <div>
                                        <label class="label-summary-product thank-you"><?php echo esc_html($order->get_unique_label_code()); ?></label>
                                    </div>
                                    <div class="set-text-align-right">
                                        <label class="label-summary-product c-semibold"><?php echo esc_html($order->get_unique_code()); ?></label>
                                    </div>
                                </div>
                            <?php endif ?>
                        </div>
                        <div class="commercioo-checkout-order-summary-item tylast">
                            <div class="container-item-product-first">
                                <div class="container-item-total">
                                    <label class="label-summary-product thank-you cc-set-color-red c-semibold"><?php esc_html_e('TOTAL', 'commercioo'); ?></label>
                                </div>
                                <div class="set-text-align-right container-item-total">
                                    <label class="label-summary-product cc-set-color-red c-semibold"><?php echo esc_html(comm_money_format($order->get_total())); ?></label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- end order detail-->
        </div>
    </div>
    <div id="commercioo-thankyou-standalone">
    <!-- display the powered by label -->
    <?php new Commercioo_Powered_By_Label(true); ?>
    </div>

</div>