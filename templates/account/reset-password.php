<div class="section-content">
	<?php comm_print_notices(); ?>
	<div class="row">
		<div class="col-md-4"></div>
		<div class="col-md-4 mb-4">
			<form action="" method="post">
				<h2 class="title-head mt-3 mb-3"><?php esc_html_e( 'SET NEW PASSWORD', 'commercioo' ); ?></h2>
				<div class="form-group">
					<label><?php esc_html_e( 'New Password', 'commercioo' ) ?> <span class="text-danger">*</span></label>
					<input type="password" class="form-control c-form-control" placeholder="<?php esc_attr_e( 'Enter your new password' ) ?>" name="password_1" value="<?php echo sanitize_text_field( ! empty( $_POST['password_1'] )  ?  wp_unslash( $_POST['password_1'] ) : ''); ?>">
				</div>
				<div class="form-group">
					<label><?php esc_html_e( 'Re-Enter New Password', 'commercioo' ) ?> <span class="text-danger">*</span></label>
					<input type="password" class="form-control c-form-control" placeholder="<?php esc_attr_e( 'Re-enter your new password' ) ?>" name="password_2" value="<?php echo sanitize_text_field( ! empty( $_POST['password_2'] )  ?  wp_unslash( $_POST['password_2'] ) : ''); ?>">
				</div>
				<?php wp_nonce_field( 'commercioo_reset', 'comm-action-nonce', true, true ); ?>
				<input type="hidden" name="reset_key" value="<?php echo esc_attr( $reset_key ); ?>" />
				<input type="hidden" name="reset_login" value="<?php echo esc_attr( $reset_login ); ?>" />
				<button type="submit" class="btn btn-login" name="login"><?php esc_html_e( 'SAVE', 'commercioo' ); ?></button>
			</form>
		</div>

	</div>
</div>