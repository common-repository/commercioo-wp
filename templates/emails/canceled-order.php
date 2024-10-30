<?php
/**
 * Processing order email notification body.
 *
 * @author Commercioo Team
 * @package Commercioo
 */

if ( ! defined( 'WPINC' ) ) {
	exit;
}

echo wp_kses_post(sprintf( '<p>Dear %s,</p>', $user_display_name ));
echo wp_kses_post(sprintf( '<p>We have received your cancellation request for order %s with details as follow:</p>', $order_id )); ?>

<p><strong>Your order details:</strong></p>
<p>
	<?php
	$total_price = 0;
	foreach ( $order_items as $order_item ) {
		echo wp_kses_post(sprintf( 'Product: %s<br/>', $order_item->item_name ));
		echo wp_kses_post(sprintf( 'Price: %s<br/>', $order_item->item_price ));
		echo wp_kses_post(sprintf( 'Quantity: %s<br/>', $order_item->item_order_qty ));

		$total_price += $order_item->item_price;
	}
	echo wp_kses_post(sprintf( 'Total: <strong>%s</strong>', $total_price ));
	?>
</p>

<p>We are sending this email to let you know that we have canceled your order.<br/>
    There will be no need for any further action from your end.</p>
<p>If you have any further questions, please reply to this email.</p>
<p>Best Regards,</p>
<p><?php bloginfo( 'name' ) ?> Team</p>
