<?php
/**
 * Commercioo Standalone Checkout Form
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    Commercioo
 * @subpackage Commercioo/includes
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>

<style type='text/css'>
	#commercioo-checkout-standalone input[type=submit] {
		background-color: <?php esc_attr_e( $button_bg ) ?> !important;
		border-color: <?php esc_attr_e( $button_bg ) ?> !important;
	}

	#commercioo-checkout-standalone input[type=submit]:hover {
		background-color: <?php esc_attr_e( $button_bg_hover ) ?> !important;
		border-color: <?php esc_attr_e( $button_bg_hover ) ?> !important;
	}
	#commercioo-checkout-standalone .form_wrapper input[type="text"], 
	#commercioo-checkout-standalone .form_wrapper input[type="email"], 
	#commercioo-checkout-standalone .form_wrapper input[type="tel"], 
	#commercioo-checkout-standalone textarea,
	#commercioo-checkout-standalone .form_wrapper select {
		border-color: <?php esc_attr_e( $border_field_normal ) ?> !important;
	}
	#commercioo-checkout-standalone .form_wrapper input[type="text"]:focus, 
	#commercioo-checkout-standalone .form_wrapper input[type="email"]:focus, 
	#commercioo-checkout-standalone .form_wrapper input[type="tel"]:focus, 
	#commercioo-checkout-standalone textarea:focus, 
	#commercioo-checkout-standalone .form_wrapper select:focus {
		border-color: <?php esc_attr_e( $border_field_focus ) ?> !important;
	}
	#commercioo-checkout-standalone .form_wrapper input[type="text"], 
	#commercioo-checkout-standalone .form_wrapper input[type="email"], 
	#commercioo-checkout-standalone .form_wrapper input[type="tel"], 
	#commercioo-checkout-standalone textarea,
	#commercioo-checkout-standalone .form_wrapper select {
		color: <?php esc_attr_e( $text_field_color ) ?> !important;
	}
	#commercioo-checkout-standalone .form_wrapper .commercioo-checkout-form-grid label {
		color: <?php esc_attr_e( $label_field_color ) ?> !important;
	}
</style>

<div class='commercioo-checkout-standalone' id="commercioo-checkout-standalone">

<!-- display the notification if have no cart items, for customizer view -->
<?php
do_action("commercioo/checkout/before/form");
?>
<form action="<?php echo esc_url( admin_url('admin-post.php') ); ?>" method="post" id="commercioo-checkout-form">
	<!-- submit action -->
	<input type="hidden" name="action" value="commercioo_checkout">

	<!-- nonce field -->
	<?php wp_nonce_field( 'GwJpuj_HVaV604dHE', '_comm_checkout_nonce' ); ?>

	<div class='commercioo-checkout-container' id="commercioo-checkout-container">
		<div class="commercioo-checkout-layout">
            <div class="commercioo-checkout-header">
            <?php
            do_action("commercioo/checkout/header");
            ?>
            </div>
			<div class="wrap-container wrap-content commercioo-checkout-form">
				<div class="form_wrapper">
					<div class="">
						<div class="label-title list-column-right"><?php esc_html_e( 'CONTACT INFORMATION', 'commercioo' ) ?></div>
						<div class="commercioo-checkout-form-grid source-sans-pro">
                            <!-- billing_fields -->
                            <?php do_action( 'commercioo/checkout/field/form/billing',$billing_fields ) ?>
						</div>

						<!-- after_billing_fields -->
						<?php do_action( 'commercioo_checkout_after_billing_fields' ) ?>

						<!-- ship_to_different_address begin -->
                        <?php do_action("commercioo/checkout/field/before/shipping",$order_forms);?>
                        <?php do_action("commercioo/checkout/field/form/shipping");?>
                        <!-- order_note -->
                        <?php do_action("commercioo/checkout/field/order_note",$order_note_field,$order_forms);?>
					</div>
				</div>
				<div class="form_wrapper">
					<div class="form_container">
					<div class="row clearfix">
                        <div>
                            <div class="label-title list-column-right"><?php esc_html_e( 'PAYMENT METHODS', 'commercioo' ) ?></div>
                                <?php do_action("comm_payment_method") ;?>
							</div>
						</div>

						<div class="row clearfix">
							<div>
								<div class="label-title list-column-right"><?php esc_html_e( 'ORDER SUMMARY', 'commercioo' ) ?></div>
							</div>
						</div>

						<div class="row clearfix cc-ml0-mr-0 commercioo-checkout-order-summary commercioo-checkout-summary-wrapper">
                            <?php echo apply_filters('commercioo_standalone_checkout_order_summary',COMMERCIOO_PATH . '/public/partials/commercioo-checkout-standalone-order-summary.php');?>
						</div>

						<?php
						if( class_exists('Commercioo_2_Step') ){
							echo '<input type="hidden" name="order_id" id="order_id_2_step">';
						}?>
                        <input
                                type="submit"
                                class="button btn-place-order <?php
                                if ( isset( $order_forms[ 'button_style' ] ) ) {
                                    $class = $order_forms[ 'button_style' ];
                                }
                                else {
                                    $class = '';
                                }

                                echo esc_attr( $class );
                                ?>"
                                value="<?php
                                if ( isset( $order_forms[ 'button_text' ] ) ) {
                                    $label = $order_forms[ 'button_text' ];
                                }
                                else {
                                    $label = __( 'PURCHASE NOW', 'commercioo' );
                                }

                                echo esc_attr( $label );
                                ?>"/>

					</div>
				</div>
				<?php
	        	// function for commercioo-sass
	        	if( class_exists('Commercioo_Saas_Limit_Order')) {
	        		$limit = new Commercioo_Saas_Limit_Order();
	        		if ( $limit->is_limit() ) {
	        			echo wp_kses_post('<div class="blocker"><p>'.$limit->order_creation_prohibited_message( __( 'Sorry, this store is prohibited to receive any order.' , 'commercioo' )).'</p></div>');
	        		}
	        	}
	        	?>
			</div>

			<div class="commercioo-checkout-header">
				<div class="commercioo-checkout-footer">
					<div class="source-sans-pro commercioo-checkout-text-help">
						<b><?php echo esc_html( $comm_options['store_name'] ) ?></b> - <?php echo esc_html( $comm_options['store_address'] ) ?>
					</div>
				</div>
			</div>

		</div>
		<!-- display the powered by label -->
		<?php new Commercioo_Powered_By_Label( true ); ?>
	</div>
</form>

<!-- the field must be excluded from the form if the user wanted to, so we need to move the fields outside the form tag -->
<?php if( ! isset( $order_forms[ 'ship_to_different_address_visibility' ] ) || $order_forms[ 'ship_to_different_address_visibility' ] == true ) : ?>

<div id="the-content-of-show-form-ship-different" style='display:none;'>
	<div class="commercioo-checkout-form-grid source-sans-pro">
		<?php foreach ( $shipping_fields as $key => $field ) : ?>
			<div class="comm-checkout-shipping-<?php echo $key ?>">
				<label>
					<?php echo esc_html( $field['label'] ); ?>
					<?php if ( $field['required'] ) : ?>
						<span class="text-danger-custom">*</span>
					<?php endif; ?>
				</label>
				<div class="input_field">
					<?php
					$value = isset( $customer_shipping[ $key ] ) ? $customer_shipping[ $key ] : '';
					if ( empty( $value ) ) {
						if ( ! empty( $user_id ) && ( 'first_name' === $key || 'last_name' === $key ) ) {
							$value = get_user_meta( $user_id, $key, true );
						} else if ( 'country' === $key && isset( $comm_options['store_country'] ) ) {
							$value = $comm_options['store_country'];
						}
					}
					$value = apply_filters( 'commercioo_checkout_field_value', $value, 'shipping_' . $key );
					Commercioo\Checkout::render_field( $field, "shipping_address[shipping_{$key}]", $value );
					?>
				</div>
			</div>
		<?php endforeach; ?>

	</div>
</div>

<?php endif ?>
<?php if(isset($product_id ) ):?>
<?php $get_stock_status = get_post_meta(intval($product_id), "_stock_status", true);
if($get_stock_status == 'outofstock'){
	echo wp_kses_post('<div class="blocker"><p>'. esc_attr('Sorry, You can\'t buy this product. This product is currently out of stock or has been disabled by admin store.','commercioo').'</p></div>');
} ?>
<?php endif; ?>
</div>