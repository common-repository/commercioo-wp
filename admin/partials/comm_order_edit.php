<?php
	/**
     * Get order data
     */
	global $comm_options;
	$id = absint($_GET['id']);
	$order =new \Commercioo\Models\Order( $id );
    $billing_address = $order->get_billing_address();
    $shipping_address = $order->get_shipping_address();
    $order_notes = get_post_meta($id, '_order_notes', true);
    $payment_method = $order->get_payment_method();
    $order_status = $order->get_order_status();
    $order_items = $order->get_order_cart_items();
    $sub_total = $order->get_subtotal();
    $total = $order->get_total();
    $tripay_payment_channel_list = isset($comm_options['tripay_payment_channel'])?$comm_options['tripay_payment_channel']:null;
    $tripay_payment_channel_name = isset($comm_options['tripay_payment_channel_name'])?$comm_options['tripay_payment_channel_name']:null;
    $tripay_payment_channel = null;
?>
<!-- Start Title -->
<div class="col-md-12 c-col-container">
    <div class="c-list-orders">
        <div class="d-flex align-items-center">
            <h2 class="page-title"><?php _e("Edit Order", "Commercioo_title"); ?> #<span id="id_order"><?php echo esc_attr($id); ?></span></h2>
        </div>
    </div>
</div>
<!-- End Title -->
<!-- Start form add orders -->
<div class="c-general-orders add c-col-container pt-3">
    <form class="needs-validation" novalidate data-cond="orders">
        <div class="row">
            <div class="col-md-6">
                <div class="row">
                    <!-- First name -->
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="c-label"><?php _e("First Name", "commercioo"); ?><span class="c-orders-star">*</span></label>
                            <input type="text" name="billing_address[billing_first_name]" class="form-control c-setting-form-control c-input-form
                            c-set-cursor-pointer" placeholder="<?php _e("First Name", "commercioo"); ?>" required value="<?php echo esc_attr( wp_unslash( $billing_address['billing_first_name'] ) ); ?>" readonly>
                            <div class="invalid-feedback">
                                <?php _e("Please enter the First Name", "commercioo"); ?>
                            </div>
                        </div>
                    </div>
                    <!-- Last name -->
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="c-label"><?php _e("Last Name", "commercioo"); ?></label>
                            <input type="text" name="billing_address[billing_last_name]" class="form-control
                            c-setting-form-control c-input-form c-set-cursor-pointer" placeholder="<?php _e("Last Name", "commercioo"); ?>" value="<?php echo esc_attr( wp_unslash( $billing_address['billing_last_name'] ) ); ?>" readonly>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <!-- Email -->
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="c-label"><?php _e("Email", "commercioo"); ?><span class="c-orders-star">*</span></label>
                            <input type="text" name="billing_address[billing_email]" class="form-control c-setting-form-control c-input-form c-set-cursor-pointer" placeholder="<?php _e("Email", "commercioo"); ?>" value="<?php echo esc_attr( wp_unslash( $billing_address['billing_email'] ) ); ?>" readonly>
                            <div class="invalid-feedback">
                                <?php _e("Please enter Email", "commercioo"); ?>
                            </div>
                        </div>
                    </div>
                    <!-- Mobile phone -->
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="c-label"><?php _e("Mobile", "commercioo"); ?><span class="c-orders-star">*</span></label>
                            <input type="text" name="billing_address[billing_phone]" class="form-control
                            c-setting-form-control c-input-form c-set-cursor-pointer" placeholder="<?php _e("Phone", "commercioo"); ?>" value="<?php echo esc_attr( wp_unslash( $billing_address['billing_phone']) ); ?>" readonly>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <!-- Company name -->
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="c-label"><?php _e("Company Name", "commercioo"); ?></label>
                            <input type="text" name="billing_address[billing_company]" class="form-control
                            c-setting-form-control c-input-form c-set-cursor-pointer" placeholder="<?php _e("Company", "commercioo"); ?>" value="<?php echo esc_attr( wp_unslash( $billing_address['billing_company'] ) ); ?>" readonly>
                        </div>
                    </div>
                    <!-- Country -->
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="c-label"><?php _e("Country", "commercioo"); ?></label>
                            <?php $selected = isset( $billing_address['billing_country'] ) ? $billing_address['billing_country'] : ''; ?>
                            <select name="billing_address[billing_country]" class="form-control c-setting-form-control" disabled="disabled">
                                <?php foreach ($comm_country as $k => $icountry): ?>
                                    <option value="<?php echo esc_attr( $k ) ?>" <?php echo esc_attr($k === $selected ? 'selected' : ''); ?>><?php echo esc_attr($icountry); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <!-- State -->
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="c-label"><?php _e("State", "commercioo"); ?></label>
                            <input type="text" name="billing_address[billing_state]" class="form-control
                            c-setting-form-control c-input-form c-set-cursor-pointer" placeholder="<?php _e("State", "commercioo"); ?>" value="<?php echo esc_attr( wp_unslash( $billing_address['billing_state'] ) ); ?>" readonly>
                        </div>
                    </div>
                    <!-- City -->
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="c-label"><?php _e("Town", "commercioo"); ?> / <?php _e("City", "commercioo"); ?></label>
                            <input type="text" name="billing_address[billing_city]" class="form-control
                            c-setting-form-control c-input-form c-set-cursor-pointer" placeholder="<?php _e("Town", "commercioo"); ?> / <?php _e("City", "commercioo"); ?>" value="<?php echo esc_attr( wp_unslash( $billing_address['billing_city'] ) ); ?>" readonly>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <!-- Zip code -->
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="c-label"><?php _e("Zip code", "commercioo"); ?></label>
                            <input type="text" name="billing_address[billing_zip]" class="form-control
                            c-setting-form-control c-input-form c-set-cursor-pointer" placeholder="<?php _e("Zip code", "commercioo"); ?>" value="<?php echo esc_attr( wp_unslash( $billing_address['billing_zip'] ) ); ?>" readonly>
                        </div>
                    </div>
                    <!-- Street address -->
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="c-label"><?php _e("Street address", "commercioo"); ?></label>
                            <input type="text" name="billing_address[billing_street_address]" class="form-control
                            c-setting-form-control c-input-form c-set-cursor-pointer" placeholder="<?php _e("Street address", "commercioo"); ?>" value="<?php echo esc_attr( wp_unslash( $billing_address['billing_street_address'] ) ); ?>" readonly>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div class="c-line-dash-settings"></div>
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="form-group col-md-4 mb-1">
                        <label><?php _e( 'Product', 'commercioo' ) ?> <span class="c-orders-star">*</span></label>
                    </div>
                    <div class="form-group col-md-2 quantity-wrap pr-0 mb-1">
                        <label><?php _e( 'Quantity', 'commercioo' ) ?> <span class="c-orders-star">*</span></label>
                    </div>
                    <div class="form-group col-md-5 mb-1">
                        <label><?php _e( 'Item(s) Price', 'commercioo' ) ?> <span class="c-orders-star">*</span></label>
                    </div>
                    <div class="c-products-cloneit">
                        <div class="row c-products-form">
                            <div class="form-group col-md-4">
                                <select name="product_id" class="form-control c-setting-form-control comm-select2 order-product c-input-form c-set-cursor-pointer">
                                    <?php
                                    $list_prod = comm_controller()->comm_get_result_data
                                    (['publish'], "comm_product", 'ID');
                                    $output ='';
                                    foreach ($list_prod as $k => $prod) {
                                        $product = comm_get_product( $prod->ID );
                                        ?>
                                        <option value="<?php esc_attr_e($prod->ID);?>" data-price="<?php esc_html_e($product->get_price());?>">
                                            <?php esc_attr_e($prod->post_title);?></option>
                                        <?php
                                    }
                                    ?>
                                </select>
                                <div class="invalid-feedback">
                                    <?php _e("Please enter product name", "commercioo"); ?>
                                </div>
                            </div>
                            <div class="form-group col-md-2">
                                <input type="number" class="form-control c-setting-form-control item_order_qty c-input-form c-set-cursor-pointer" placeholder="0" name="item_order_qty">
                                <div class="invalid-feedback">
                                    <?php _e("Please enter quantity", "commercioo"); ?>
                                </div>
                            </div>
                            <div class="form-group col-md-4">
                                <div class="input-group">
                                    <div class="input-group-prepend c-setting-form-control">
                                        <div class="input-group-text c-setting-form-control"><?php echo esc_attr($comm_options['currency_symbol']); ?></div>
                                    </div>
                                    <input type="number" class="form-control c-setting-form-control item-price c-input-form c-set-cursor-pointer" name="custom_price" placeholder="200000">
                                    <div class="invalid-feedback">
                                        <?php _e("Please enter price", "commercioo"); ?>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group d-flex col-md-2">
                                <span class="c-orders-plus add-orders"><i class="feather-16" data-feather="plus"></i></span>
                                <span class="c-orders-minus remove-orders"><i class="feather-16" data-feather="minus"></i></span>
                            </div>
                        </div>
                    </div>
                    <?php if($order_items):?>
                    <div class="c-products-clone">
                        <?php
                            $qty = 0;
                            $i = 1;
                            foreach ($order_items as $keys => $item) {
                                $product_id = $item->product_id;
                                $qty += intval( $item->item_order_qty );
                                $price = $item->item_price;
                                if($item->item_sales_price>0){
                                    $price = $item->item_sales_price;
                                }
                                ?>
                        <div class="row c-products-form">
                            <div class="form-group col-md-4">
                                <select name="order_items[<?php echo esc_attr($i);?>][product_id]" class="form-control c-setting-form-control comm-select2 order-product c-input-form c-set-cursor-pointer">
                                    <?php
                                    $list_prod = comm_controller()->comm_get_result_data
                                    (['publish'], "comm_product", 'ID');
                                    $output = '';
                                    foreach ($list_prod as $k => $prod) {
                                        $product = comm_get_product( $prod->ID );
//                                        $price_option = $product->get_regular_price();
//                                        if($product->is_on_sale()){
//                                            $price_option = $product->get_regular_price();
//                                        }
                                        ?>
                                        <option value="<?php esc_attr_e($prod->ID);?>" data-price="<?php esc_html_e($product->get_price());?>" <?php echo selected($prod->ID,$product_id);?>>
                                         <?php esc_attr_e($prod->post_title);?></option>
                                    <?php
                                    }
                                    ?>
                                </select>
                                <div class="invalid-feedback">
                                    <?php _e("Please enter product name", "commercioo"); ?>
                                </div>
                            </div>
                            <div class="form-group col-md-2">
                                <input type="number" class="form-control c-setting-form-control item_order_qty c-input-form c-set-cursor-pointer" placeholder="0" value="<?php echo esc_attr(isset( $_POST['item_order_qty'] ) ?  wp_unslash( $_POST['item_order_qty'] ) :  wp_unslash( $item->item_order_qty ) ); ?>" required name="order_items[<?php echo esc_attr($i);?>][item_order_qty]">
                                <div class="invalid-feedback">
                                    <?php _e("Please enter quantity", "commercioo"); ?>
                                </div>
                            </div>
                            <div class="form-group col-md-4">
                                <div class="input-group">
                                    <div class="input-group-prepend c-setting-form-control">
                                        <div class="input-group-text c-setting-form-control"><?php echo esc_attr($comm_options['currency_symbol']); ?></div>
                                    </div>
                                    <input type="number" class="form-control c-setting-form-control item-price c-input-form c-set-cursor-pointer" name="order_items[<?php echo esc_attr($i);?>][custom_price]" placeholder="200000" value="<?php echo esc_attr(isset( $_POST['custom_price'] ) ? wp_unslash( $_POST['custom_price'] ) : sanitize_text_field(wp_unslash($price))); ?>" required>
                                    <div class="invalid-feedback">
                                        <?php _e("Please enter price", "commercioo"); ?>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group d-flex col-md-2">
                                <span class="c-orders-plus add-orders"><i class="feather-16" data-feather="plus"></i></span>
                                <span class="c-orders-minus remove-orders"><i class="feather-16" data-feather="minus"></i></span>
                            </div>
                        </div>
                        <?php $i++; };?>
                    </div>
                    <?php endif;?>
                    <div class="multi-product-form-pqp"></div>
                </div>
                <div class="comm-detail-item-order">
                    <div class="row mt-1">
                        <div class="form-group col-md-4 mb-0">
                            <label class="float-right mb-0"><?php _e("Items Subtotal", "commercioo"); ?></label>
                        </div>
                        <div class="form-group col-md-2 mb-0 comm-total-qty">
                            <label class="float-right mb-0"><?php esc_html_e( $qty ); ?></label>
                        </div>
                        <div class="form-group col-md-6 mb-0">
                                <label class="comm-grand-total-price"><?php echo esc_html($comm_options['currency_symbol']); ?> <?php echo esc_html(comm_money_without_currency($sub_total));?></label>
                        </div>
                    </div>
                    <?php if ( $order->has_fee() ) : ?>
                    <div class="row mt-1">
                        <div class="form-group col-md-4 mb-0">
                            <label class="float-right mb-0"><?php esc_html_e( 'Additional fee', 'commercioo' ) ?></label>
                        </div>
                        <div class="form-group col-md-6 mb-0 offset-md-2">
                                <label class="comm-grand-total-price"><?php esc_html_e( \Commercioo\Helper::formatted_currency( $order->get_fee_total() ) ) ?></label>
                        </div>
                    </div>
                    <?php endif; ?>

                    <?php if ( $order->has_shipping() ) : ?>
                    <div class="row mt-1">
                        <div class="form-group col-md-4 mb-0">
                            <label class="float-right mb-0"><?php esc_html_e( 'Shipping', 'commercioo' ) ?> (<?php echo esc_html( $order->get_shipping_method() ) ?>)</label>
                        </div>
                        <div class="form-group col-md-6 mb-0 offset-md-2">
                                <label class="comm-grand-total-price"><?php echo esc_html( \Commercioo\Helper::formatted_currency( $order->get_shipping_price() ) ) ?></label>
                        </div>
                    </div>
                    <?php endif;
                    if ($order->get_unique_code()):
                    ?>
                    <div class="row mt-1">
                        <div class="form-group col-md-4 mb-0">
                            <label class="float-right mb-0"><?php echo esc_html($order->get_unique_label_code()); ?></label>
                        </div>
                        <div class="form-group col-md-6 mb-0 offset-md-2">
                                <label class="comm-grand-total-price"><?php echo esc_html($order->get_unique_code());?></label>
                        </div>
                    </div>
                    <?php endif;
                    do_action( 'commercioo_order_detail_before_total', $id ); ?>
                    <div class="row mt-1">
                        <div class="form-group col-md-4 mb-0">
                            <label class="float-right mb-0"><?php _e("Total", "commercioo"); ?></label>
                        </div>
                        <div class="form-group col-md-6 mb-0 offset-md-2">
                                <label class="comm-grand-total-price"><?php echo esc_html($comm_options['currency_symbol']); ?> <?php echo esc_html(comm_money_without_currency($total));?></label>
                                <input type="hidden" name="order_total" value="<?php esc_html_e( $total ) ?>">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col">
                            <button type="button" class="btn btn-primary float-right c-admin-button c-recalculate"><?php _e("Update Cart", "commercioo"); ?></button>
                        </div>
                    </div>
                </div>
                <div class="row my-2">
                    <div class="col-md-12">
                        <div class="c-line-dash-settings mt-2"></div>
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label><?php _e("Payment Method", "commercioo"); ?></label>
                            <?php
                                $selected = $payment_method;
                            ?>
                            <select name="payment_method" class="form-control c-setting-form-control">
                                <option value="bacs" <?php echo 'bacs' === $selected ? 'selected' : '' ?>>Bank Transfer</option>
                                <option value="paypal" <?php echo 'paypal' === $selected ? 'selected' : '' ?>>Paypal</option>
                                <?php
                                if(isset($tripay_payment_channel_name) && is_array($tripay_payment_channel_name)):?>
                                 <?php foreach ($tripay_payment_channel_name as $k => $tval): $val_tripay = "TRIPAY_".$k;?>
                                    <option value="<?php echo esc_attr($val_tripay);?>" <?php echo esc_attr($val_tripay === $selected ? 'selected' : '') ?>><?php echo esc_attr($tval);?></option>
                                <?php
                                  endforeach;
                                else:
                                  if(strpos($selected, "TRIPAY") !== false):?>
                                    <option value="<?php echo esc_attr($selected);?>" <?php echo esc_attr($selected === $selected ? 'selected' : '') ?>>Tripay</option>
                                <?php endif; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label><?php _e("Status", "commercioo"); ?></label>
                            <?php
                                $selected = 'comm_'.$order_status;
                            ?>
                            <select name="status" class="form-control c-setting-form-control">
                                <option value="comm_pending" <?php echo esc_attr('comm_pending' === $selected ? 'selected' : '') ?>>Pending</option>
                                <option value="comm_processing" <?php echo esc_attr('comm_processing' === $selected ? 'selected' : ''); ?>>Processing</option>
                                <option value="comm_completed" <?php echo esc_attr('comm_completed' === $selected ? 'selected' : ''); ?>>Complete</option>
                                <option value="comm_refunded" <?php echo esc_attr('comm_refunded' === $selected ? 'selected' : '') ?>>Refunded</option>
                                <option value="comm_abandoned" <?php echo esc_attr('comm_abandoned' === $selected ? 'selected' : ''); ?>>Abandoned</option>
                                <option value="comm_failed" <?php echo esc_attr('comm_failed' === $selected ? 'selected' : ''); ?>>Failed</option>
                            </select>
                        </div>
                    </div>
                    <?php do_action( 'comm_edit_order', $id ); ?>
                </div>
                <div class="row mb-3">
                    <div class="col-md-12">
                        <div class="c-line-dash-settings"></div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <?php
                $order_forms = get_option('comm_order_forms_settings', array());
                // billing settings
                $billing = isset($order_forms['billing_address']) ? $order_forms['billing_address'] : array();
                ?>
                <div class="row">
                    <!-- First name -->
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="c-label"><?php _e("First Name", "commercioo"); ?><span class="c-orders-star">*</span></label>
                            <input type="text" name="shipping_address[shipping_first_name]" class="form-control c-setting-form-control c-input-form
                            c-set-cursor-pointer" placeholder="<?php _e("First Name", "commercioo"); ?>" required value="<?php echo esc_attr( wp_unslash( $shipping_address['shipping_first_name'] ) ); ?>" readonly>
                            <div class="invalid-feedback">
                                <?php _e("Please enter the First Name", "commercioo"); ?>
                            </div>
                        </div>
                    </div>
                    <!-- Last name -->
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="c-label"><?php _e("Last Name", "commercioo"); ?></label>
                            <input type="text" name="shipping_address[shipping_last_name]" class="form-control
                            c-setting-form-control c-input-form c-set-cursor-pointer" placeholder="<?php _e("Last Name", "commercioo"); ?>" value="<?php echo esc_attr( wp_unslash( $shipping_address['shipping_last_name'] ) ); ?>" readonly>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <!-- Email -->
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="c-label"><?php _e("Email", "commercioo"); ?><span class="c-orders-star">*</span></label>
                            <input type="text" name="shipping_address[shipping_email]" class="form-control
                            c-setting-form-control c-input-form c-set-cursor-pointer" placeholder="<?php _e("Email", "commercioo"); ?>" value="<?php echo esc_attr( wp_unslash( $shipping_address['shipping_email'] ) ); ?>" readonly>
                            <div class="invalid-feedback">
                                <?php _e("Please enter Email", "commercioo"); ?>
                            </div>
                        </div>
                    </div>
                    <!-- Mobile -->
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="c-label"><?php _e("Mobile", "commercioo"); ?><span class="c-orders-star">*</span></label>
                            <input type="text" name="shipping_address[shipping_phone]" class="form-control
                            c-setting-form-control c-input-form c-set-cursor-pointer" placeholder="<?php _e("Mobile", "commercioo"); ?>" value="<?php echo esc_attr( wp_unslash( $shipping_address['shipping_phone'] ) ); ?>" readonly>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <!-- Company -->
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="c-label"><?php _e("Company Name", "commercioo"); ?></label>
                            <input type="text" name="shipping_address[shipping_company]" class="form-control
                            c-setting-form-control c-input-form c-set-cursor-pointer" placeholder="<?php _e("Company", "commercioo"); ?>" value="<?php echo esc_attr( wp_unslash( $shipping_address['shipping_company'] ) ); ?>" readonly>
                        </div>
                    </div>
                    <!-- Country -->
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="c-label"><?php _e("Country", "commercioo"); ?></label>
                            <?php $selected = isset( $shipping_address['shipping_country'] ) ? $shipping_address['shipping_country'] : ''; ?>
                            <select name="shipping_address[shipping_country]" class="form-control c-setting-form-control" disabled="disabled">
                                <?php foreach ($comm_country as $k => $icountry): ?>
                                    <option value="<?php echo esc_attr( $k ) ?>" <?php echo esc_attr($k === $selected ? 'selected' : ''); ?>><?php echo esc_attr($icountry); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <!-- State or province -->
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="c-label"><?php _e("State / Province", "commercioo"); ?></label>
                            <input type="text" name="shipping_address[shipping_state]" class="form-control
                            c-setting-form-control c-input-form c-set-cursor-pointer" placeholder="<?php _e("State", "commercioo"); ?>" value="<?php echo esc_attr( wp_unslash( $shipping_address['shipping_state'] ) ); ?>" readonly>
                        </div>
                    </div>
                    <!-- City -->
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="c-label"><?php _e("Town / City", "commercioo"); ?></label>
                            <input type="text" name="shipping_address[shipping_city]" class="form-control
                            c-setting-form-control c-input-form c-set-cursor-pointer" placeholder="<?php _e("Town / City", "commercioo"); ?>" value="<?php echo esc_attr(isset( $_POST['shipping_city'] ) ? wp_unslash( $_POST['shipping_city'] ) : wp_unslash( $shipping_address['shipping_city'] ) ); ?>" readonly>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <!-- Zip code -->
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="c-label"><?php _e("Zip code", "commercioo"); ?></label>
                            <input type="text" name="shipping_address[shipping_zip]" class="form-control
                            c-setting-form-control c-input-form c-set-cursor-pointer" placeholder="<?php _e("Zip code", "commercioo"); ?>" value="<?php echo esc_attr( wp_unslash( $shipping_address['shipping_zip'] ) ); ?>" readonly>
                        </div>
                    </div>
                    <!-- Street address -->
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="c-label"><?php _e("Street address", "commercioo"); ?></label>
                            <input type="text" name="shipping_address[shipping_street_address]" class="form-control
                            c-setting-form-control c-input-form c-set-cursor-pointer" placeholder="<?php _e("Street address", "commercioo"); ?>" value="<?php echo esc_attr( wp_unslash( $shipping_address['shipping_street_address'] ) ); ?>" readonly>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label class="c-label"><?php _e("Order Notes", "commercioo"); ?></label>
                            <textarea class="form-control c-setting-form-control" name="order_notes"><?php echo esc_attr( wp_unslash( $order_notes ) ); ?></textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- <div class="row">
            <div class="col-md-3">
                <div class="form-group">
                    <div class="form-check">
                        <input type="checkbox" id="comm_shipping_status" name="shipping_address[shipping_status]"
                               class="form-checkbox-different">
                        <label class="form-check-label" for="comm_shipping_status">SHIP TO DIFFERENT ADDRESS</label>
                    </div>
                </div>
            </div>
        </div> -->

        <div class="row">
            <div class="col-md-4">
                <input type="hidden" name="order_currency" value="<?php echo esc_html($comm_options['currency']); ?>">
                <?php if ( $order->has_shipping() ) : ?>
                    <input type="hidden" name="shipping_method" value="<?php echo esc_html( $order->get_shipping_method() ) ?>">
                    <input type="hidden" name="shipping_price" value="<?php echo esc_html( $order->get_shipping_price() ) ?>">
                <?php else:?>
                    <input type="hidden" name="shipping_method" value="flat">
                <?php endif; ?>
                <button type="submit" class="btn btn-primary c-admin-button"><?php _e("Save", "commercioo"); ?></button>
                <button type="button" onclick="location.href = '<?php echo esc_url(comm_controller()->comm_dash_page( 'comm_order' )); ?>'" class="btn btn-primary c-admin-button c-back"><?php _e( "Cancel", "commercioo" ); ?></button>
            </div>
        </div>
    </form>
</div>
<!-- End form add orders -->