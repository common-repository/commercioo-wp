<?php
/**
 * Commercioo Account Page
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
?>
<div class="section-content">
	<?php comm_print_notices(); ?>
	<div class="row">
		<div class="col-md-3 mb-3">
			<ul class="list-group account-menu">
				<a href="<?php echo esc_url( $base_url ) ?>" class="list-group-item list-account-menu <?php echo esc_attr('dashboard' === $subpage ? 'active' : '') ?>">Dashboard</a>
				<a href="<?php echo esc_url( untrailingslashit( $base_url ) . '/order-history' ) ?>" class="list-group-item list-account-menu <?php echo esc_attr('order-history' === $subpage || 'order-detail' === $subpage ? 'active' : '') ?>">Orders</a>
				<a href="<?php echo esc_url( untrailingslashit( $base_url ) . '/addresses' ) ?>" class="list-group-item list-account-menu <?php echo esc_attr('addresses' === $subpage || 'edit-address' === $subpage ? 'active' : '') ?>">Addresses</a>
				<a href="<?php echo esc_url( untrailingslashit( $base_url ) ) . '/edit-profile' ?>" class="list-group-item list-account-menu <?php echo esc_attr('edit-profile' === $subpage ? 'active' : '') ?>">Edit Account</a>
				<a href="<?php echo esc_url( wp_nonce_url( untrailingslashit( $base_url ) . '/logout', 'customer-logout' ) ) ?>" class="list-group-item list-account-menu <?php echo esc_attr('logout' === $subpage ? 'active' : '') ?>">Logout</a>
			</ul>
		</div>

		<!-- Content  -->
		<div class="col-md-6 mb-3">
			<?php
				if ( 'addresses' === $subpage && in_array( $param, array( 'billing', 'shipping' ) ) ) {
					$subpage = 'edit-address';
				}
				\Commercioo\Template::render( 'account/' . $subpage, compact( 'current_user', 'param', 'base_url' ), true );
			?>
		</div>
		<!-- End Content  -->

		<div class="col-md-3 mb-3">
			<!-- Shopping cart -->
			<div class="shopping-wrap-account show-cart">
				<div class="row detail-show-cart"></div>
			</div>
			<!-- End shopping cart -->
		</div>
	</div>
</div>