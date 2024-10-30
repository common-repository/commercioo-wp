<?php use Commercioo\Helper; ?>
<?php $payment_method = $order->get_payment_method(); ?>
<h2><?php esc_html_e( 'Order Details', 'commercioo' ) ?></h2>

<table role="presentation" border="0" cellpadding="0" cellspacing="0" class="order-details">
	<tr>
		<td>
			<table role="presentation" border="0" cellpadding="0" cellspacing="0" class="order-details-top">
				<tr>
					<td><?php esc_html_e( 'Order ID', 'commercioo' ) ?>:</td>
					<th><?php echo esc_html( $order->get_order_id() ) ?></th>
				</tr>
				<tr>
					<td><?php esc_html_e( 'Date', 'commercioo' ) ?>:</td>
					<th><?php echo esc_html( $order->get_formatted_order_date() ) ?></th>
				</tr>
				<tr>
					<td><?php esc_html_e( 'Payment Method', 'commercioo' ) ?>:</td>
					<th><?php echo esc_html(comm_payment_method_label($order->get_order_id())); ?></th>
				</tr>
                <?php
                 apply_filters("commercioo/display/detail/email/content",$order->get_order_id());
                ?>
				<tr class="no-border">
					<td><?php esc_html_e( 'Status', 'commercioo' ) ?>:</td>
					<th class="text-orange"><?php echo esc_html( $order->get_order_status_label() ) ?></th>
				</tr>
			</table>
		</td>
	</tr>
    <?php
    $order_items = $order->get_order_cart_items();
    if($order_items):
    ?>
	<tr>
		<td>
			<table role="presentation" border="0" cellpadding="0" cellspacing="0" class="order-details-middle">
				<?php foreach ( $order_items as $item ) : ?>
					<tr>
						<th><?php echo esc_html( sprintf( "%s (x%s)", $item->item_name, $item->item_order_qty ) ); ?></th>
						<td>
                            <?php if($item->item_sales_price>0):?>
                                <div class="label-summary-product c-semibold"><?php echo wp_kses_post('<del>' . \Commercioo\Helper::formatted_currency( $item->item_price) . '</del> ');?></div>
                                <div class="label-summary-product c-semibold"><?php echo esc_html(\Commercioo\Helper::formatted_currency($item->item_sales_price)); ?></div>
                            <?php else:?>
                                <label class="label-summary-product c-semibold"><?php echo wp_kses_post(\Commercioo\Helper::formatted_currency( $item->item_price));?></label>
                            <?php endif; ?>
                        </td>
					</tr>
				<?php endforeach ?>

				<tr>
					<th><?php esc_html_e( 'Subtotal', 'commercioo' ) ?></th>
					<td><?php echo esc_html( Helper::formatted_currency( $order->get_subtotal() ) ) ?></td>
				</tr>
				<?php if ( $order->has_fee() ) : ?>
					<?php foreach ( $order->get_fees() as $fee ) : ?>
						<tr>
							<th><?php echo esc_html( $fee['name'] ); ?></th>
							<td><?php echo esc_html( Helper::formatted_currency( $fee['amount'] ) ) ?></td>
						</tr>
					<?php endforeach; ?>
				<?php endif ?>
				<?php if ( $order->has_shipping() ) : ?>
					<tr>
						<th><?php printf( "%s (%s)", __( 'Shipping', 'commercioo' ), $order->get_shipping_method() ) ?></th>
						<td><?php echo esc_html( Helper::formatted_currency( $order->get_shipping_price() ) ) ?></td>
					</tr>
				<?php elseif ( $order->has_free_shipping() ) : ?>
					<tr>
						<th><?php printf( "%s", __( 'Shipping', 'commercioo' ) ) ?></th>
						<td>Free Shipping</td>
					</tr>
				<?php endif ?>
                <?php
                if ($order->get_unique_code()):
                ?>
                <tr>
                    <th><?php echo esc_html($order->get_unique_label_code()); ?></th>
                    <td><?php echo esc_html($order->get_unique_code());?></td>
                </tr>
                <?php endif ?>
			</table>

		</td>
	</tr>
	<tr>
		<td>
			<table role="presentation" border="0" cellpadding="0" cellspacing="0" class="order-details-bottom">
				<tr>
					<th><?php esc_html_e( 'TOTAL', 'commercioo' ) ?></th>
					<th><?php echo esc_html( Helper::formatted_currency( $order->get_total() ) ) ?></th>
				</tr>
			</table>
		</td>
	</tr>
    <?php endif; ?>
</table>
<?php do_action( 'comm_digital_product_email', $order->get_order_id() ); ?>
<table role="presentation" border="0" cellpadding="0" cellspacing="0" class="billing-shipping-address">
	<tr>
		<td>
			<h2><?php esc_html_e( 'Billing Address', 'commercioo' ) ?></h2>
			<p><?php echo wp_kses_post( $order->get_formatted_address() ) ?></p>
		</td>
		<td>
			<h2><?php esc_html_e( 'Shipping Address', 'commercioo' ) ?></h2>
			<p><?php echo wp_kses_post( $order->get_formatted_address( 'shipping' ) ) ?></p>
		</td>
	</tr>
</table>