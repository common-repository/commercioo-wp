<div class="content-account-menu">
	<div>
		Hello <strong><?php echo esc_html( $current_user->display_name ) ?></strong>, not <strong><?php echo esc_html( $current_user->display_name ) ?></strong>? <a href="<?php echo esc_url( wp_nonce_url( untrailingslashit( $base_url ) . '/logout', 'customer-logout' ) ) ?>">Log out</a>.
	</div>
	<div class="mt-4">
		From your account dashboard you can view your <a href="<?php echo esc_url( untrailingslashit( $base_url ) . '/order-history' ) ?>">recent orders</a>, manage your <a href="<?php echo esc_url( untrailingslashit( $base_url ) . '/addresses' ) ?>">shipping and billing addresses</a>, and <a href="<?php echo esc_url( untrailingslashit( $base_url ) . '/edit-profile' ) ?>">edit your password and account details</a>.
	</div>
</div>