<?php
global $comm_country;
$customer = new \Commercioo\Models\Customer( $current_user->ID );
if ( 'shipping' == $param ) {
	$address = $customer->get_shipping_address();
	$type = 'shipping';
} else {
	$address = $customer->get_billing_address();
	$type = 'billing';
}
$checkout = Commercioo\Checkout::get_instance();
$fields   = $checkout->get_default_fields( $type );
?>
<div class="content-account-menu" data-type="<?php echo esc_attr( $type ) ?>">
	<form method="post" action="">
		<?php foreach ( $fields as $key => $field ) : ?>
			<div class="form-group comm-field-<?php echo $key ?>">
				<label class="label-form-<?php echo $type; ?>">
					<?php echo esc_html( $field['label'] ); ?>
					<?php if ( $field['required'] ) : ?>
						<span class="text-danger">*</span>
					<?php endif; ?>
				</label>
				<?php
				$field['attrs']['class'] = 'form-control c-form-control';
				$value = esc_attr(isset( $address[ $key ] ) ?  wp_unslash( $address[ $key ] ) : '');
				Commercioo\Checkout::render_field( $field, $key, $value );
				?>
			</div>
		<?php endforeach; ?>
        <div class="form-group">
        	<input type="hidden" name="action" value="update_address">
        	<?php wp_nonce_field( 'update_address', 'comm-action-nonce', true, true ); ?>
            <input type="submit" value="Update Address" class="btn btn-blue">
        </div>
	</form>
</div>