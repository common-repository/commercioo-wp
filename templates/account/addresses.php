<?php
	$customer = new \Commercioo\Models\Customer( $current_user->ID );
?>
<div class="content-account-menu">
	<p>The following addresses will be used on the checkout page by default.</p>
	<div class="row">
		<div class="col-md-6">
			<header class="title">
				<h3>Billing Address</h3>
				<a href="<?php echo esc_url( comm_get_account_uri( 'addresses/billing' ) ); ?>" class="edit">Edit</a>
			</header>
			<address>
				<?php
					$address = $customer->get_formatted_address( 'billing' );
					echo wp_kses_post($address ?  $address  : __( 'You have not set up this type of address yet.', 'commercioo' ));
				?>
			</address>
		</div>
		<div class="col-md-6">
			<header class="title">
				<h3>Shipping Address</h3>
				<a href="<?php echo esc_url( comm_get_account_uri( 'addresses/shipping' ) ); ?>" class="edit">Edit</a>
			</header>
			<address>
				<?php
					$address = $customer->get_formatted_address( 'shipping' );
					echo wp_kses_post($address ?  $address  : __( 'You have not set up this type of address yet.', 'commercioo' ));
				?>
			</address>
		</div>
	</div>
</div>