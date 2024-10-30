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
echo wp_kses_post(sprintf( '<p>Payment for order %s on %s has failed. The order was as follows:</p>', $order_id, $site_name )); ?>

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
<?php echo wp_kses_post(sprintf( '<p>Please click the following link to fix it and continue to complete your purchase:<br/>%s</p>',
	$contact_url )); ?>
    <p>If you have any questions, please reply to this email.</p>
    <p>Best Regards,</p>
<?php echo wp_kses_post(sprintf( '<p>%s Team</p>', $site_name ));
