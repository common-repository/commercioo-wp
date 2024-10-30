<?php
global $comm_options;
$customer = new \Commercioo\Models\Customer( $current_user->ID );
$order = new \Commercioo\Models\Order( $param );
?>
<?php if ( false === $order || ! isset( $order->get_customer()->get_user()->ID ) || $order->get_customer()->get_user()->ID != $current_user->ID ) : ?>
	<div class="alert alert-danger">
        Order not found
    </div>
<?php else: ?>
	<div class="content-account-menu">
		<div>
			<ul class="set-padding-left-list">
				<li>Order ID: <b><?php echo esc_html( $order->get_order_id() ) ?></b></li>
				<li>Date: <b><?php echo esc_html( date_i18n( get_option('date_format'), strtotime( $order->get_order_date() ), false ) ) ?></b></li>
				<li>Email: <b><?php echo esc_html( $order->get_billing_email() ) ?></b></li>
				<li>Total: <b><?php echo esc_html( comm_money_format( $order->get_total() ) ) ?></b></li>
				<li>Payment method: <b><?php echo esc_html( comm_payment_method_label( $order->get_order_id()) ) ?></b></li>
				<li>Status: <span class="text-primary"><b><?php echo esc_html( strtoupper( $order->get_order_status() ) ) ?></b></span></li>
            </ul>
            <?php if($order->get_status_confirmation_payment() && $order->get_payment_method() == "bacs"):?>
            <b>Konfirmasi Pembayaran</b>
            <ul class="set-padding-left-list">
                <li>Status: <b><?php _e("Anda telah melakukan Konfirmasi Pembayaran","commercioo");?></b></li>
                <li>Date: <b> <?php echo esc_html($order->get_transfer_date()) ?></b></li>
            </ul>
            <?php endif; ?>
		</div>
		<?php  echo apply_filters("commercioo_order_payment_method_thank_you", $order->get_order_id(), $order->get_payment_method());  ?>
		<div class="mt-4">
			<h2 class="order-detail-head">ORDER DETAILS</h2>
			<table class="table table-border thank-you-general">
			<thead class="thead-details">
				<tr>
				<th scope="col" class="one-column">Product</th>
				<th scope="col" class="two-column">Total</th>
				</tr>
			</thead>
			<tbody>
            <?php
    $order_items = $order->get_order_cart_items();
    if($order_items):
            ?>
				<?php foreach ( $order_items as $item ) : ?>
					<tr>
						<td><a href="<?php the_permalink( $item->product_id ) ?>"><?php echo esc_html( sprintf( "%s (x%s)", $item->item_name, $item->item_order_qty ) ); ?></a></td>
						<td class="two-column">
                            <?php if($item->item_sales_price>0):?>
                                <div class="label-summary-product"><?php echo wp_kses_post('<del>' . \Commercioo\Helper::formatted_currency( $item->item_price) . '</del> ');?></div>
                                <div class="label-summary-product"><?php echo esc_html(\Commercioo\Helper::formatted_currency($item->item_sales_price)); ?></div>
                            <?php else:?>
                                <label class="label-summary-product"><?php echo wp_kses_post(\Commercioo\Helper::formatted_currency( $item->item_price));?></label>
                            <?php endif; ?>
                        </td>
					</tr>
				<?php endforeach; ?>
    <?php endif;?>
			</tbody>
			<tfoot>
				<tr>
					<th>Subtotal:</th>
					<td class="two-column"><?php echo esc_html( comm_money_format( $order->get_subtotal() ) ) ?></td>
				</tr>
                <?php if ( $order->has_fee() ) : ?>
                    <?php foreach ( $order->get_fees() as $fee ) : ?>
                        <tr>
                            <th><?php echo esc_html( $fee['name'] ); ?></th>
                            <td class="two-column"><?php echo esc_html( comm_money_format( $fee['amount'] ) ) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif ?>
				<tr>
					<th>Shipping:</th>
					<?php if ( $order->has_free_shipping() ) { ?>
						<td class="two-column">Free Shipping</td>
					<?php } else { ?>
						<td class="two-column">
							<?php echo esc_html( $order->get_shipping_method() ) ?> - <?php echo esc_html( comm_money_format( $order->get_shipping_price() ) ) ?>
							<br>
							<?php
	                        if (function_exists("comm_get_shipping_number")) {
	                            comm_get_shipping_number( $order );
	                        }
	                        ?>

						</td>
					<?php } ?>
				</tr>
                    <?php
                    if ($order->get_unique_code()):
                    ?>
					<tr>
						<th><?php echo esc_html($order->get_unique_label_code()); ?></th>
						<td class="two-column"><?php echo esc_html($order->get_unique_code());?></td>
					</tr>
                    <?php endif;?>
				<tr>
					<th>Payment method:</th>
					<td class="two-column"><?php echo esc_html( comm_payment_method_label( $order->get_order_id()) ) ?></td>
				</tr>
				<tr class="column-total">
					<th>Total:</th>
					<th class="two-column"><?php echo esc_html( comm_money_format( $order->get_total() ) ) ?></th>
				</tr>
			</tfoot>
			</table>
		</div>

		<?php do_action( 'comm_digital_product', $order->get_order_id() ); ?>

		<div class="row mt-4 mb-5">
			<?php
				$billing_address = $order->get_formatted_address( 'billing' );
				$shipping_address = $order->get_formatted_address( 'shipping' );
			?>
			<div class="col-md-6 mb-3">
				<h2 class="order-detail-head">BILLING ADDRESS</h2>
				<div>
					<?php echo wp_kses_post( $billing_address ) ?>
				</div>
			</div>
			<div class="col-md-6 mb-3">
				<h2 class="order-detail-head">SHIPPING ADDRESS</h2>
				<div>
					<?php echo ! empty( $shipping_address ) ? wp_kses_post( $shipping_address ) : wp_kses_post( $billing_address ) ?>
				</div>
			</div>
		</div>
	</div>
<?php endif; ?>