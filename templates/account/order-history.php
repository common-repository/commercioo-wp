<?php
$customer = new \Commercioo\Models\Customer( $current_user->ID );
$orders = $customer->get_orders( array(
	'per_page'	=> 10,
	'paged'		=> $param
) );
?>
<div class="content-account-menu content-account-orders">
	<?php if ( 0 < $orders->found_posts ) : ?>
		<table class="table orders-table">
			<thead>
				<tr>
					<th class="orders-item">ORDER ID</th>
					<th class="orders-item">DATE</th>
					<th class="orders-item">STATUS</th>
					<th class="orders-item">TOTAL</th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $orders->posts as $order ) : ?>
					<?php $order_detail = new \Commercioo\Models\Order( $order->ID ); ?>
					<tr>
						<td class="orders-item"><a href="<?php echo comm_get_account_uri( 'order-detail/' . $order->ID ) ?>" class="btn-show-detail-orders">#<?php echo esc_html( $order->ID ) ?></a></td>
						<td class="orders-item"><?php echo esc_html( date_i18n( get_option('date_format'), strtotime( $order_detail->get_order_date() ), false ) ) ?></td>
						<td class="orders-item"><?php echo esc_html( ucfirst( $order_detail->get_order_status() ) ) ?></td>
						<td class="orders-item"><?php echo esc_html( comm_money_format( $order_detail->get_total() ) ) ?></td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
		<div class="d-flex justify-content-center">
			<div class="pagination">
			    <?php 
			        echo paginate_links( array(
			            'base'         => comm_get_account_uri( 'order-history/%#%' ),
			            'total'        => $orders->max_num_pages,
			            'current'      => max( 1, get_query_var( 'order-history' ) ),
			            'format'       => '?order-history=%#%',
			            'show_all'     => false,
			            'type'         => 'plain',
			            'end_size'     => 1,
			            'mid_size'     => 1,
			            'prev_next'    => false,
			            'add_args'     => false,
			            'add_fragment' => '',
			        ) );
			    ?>
			</div>
		</div>

	<?php else: ?>
		<div class="alert alert-info">
	        <?php esc_html_e( 'No orders to display', 'commercioo' ); ?>
	    </div>
	<?php endif; ?>
</div>