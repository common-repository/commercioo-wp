<?php
// define default message
$login_register_settings = get_option('comm_login_register_settings', 
	array(
		'login_message_enabled'     => 1,
		'login_message'             => 'Login here by filling you\'re username and password or use your favorite social network account to enter to the site. Site login will simplify the purchase process and allows you to manage your personal account.',
		'register_message_enabled'  => 1,
		'register_message'          => 'Registering for this site allows you to access your order status and history. Just fill in the fields below, and we’ll get a new account set up for you in no time. We will only ask you for information necessary to make the purchase process faster and easier.',
		'agreement_message_enabled' => 1,
		'agreement_message'         => 'Your personal data will be used to support your experience throughout this website, to manage access to your account, and for other purposes described in our <b>Privacy Policy</b>',
		'forgot_message_enabled'	=> 1,
		'forgot_message'			=> 'Forgot your password? Please enter your email address. You will receive a link to create a new password via email.'
	)
);

?>
<div class="section-content">
	<?php comm_print_notices(); ?>
	<div class="row">
		<div class="col-md-4"></div>
		<div class="col-md-4 mb-4">
			<form action="" method="post">
				<?php if ( $login_register_settings['forgot_message_enabled'] ) :
					echo wp_kses( $login_register_settings['forgot_message'], 'post' );
				endif; ?>
				<h2 class="title-head mt-3 mb-3"><?php esc_html_e('FORGOT PASSWORD', 'commercioo'); ?></h2>
				<div class="form-group">
					<label>Email <span class="text-danger">*</span></label>
					<input type="email" class="form-control c-form-control" placeholder="Enter email" name="email" value="<?php echo sanitize_email(( ! empty( $_POST['email'] ) ) ?  wp_unslash( $_POST['email'] ) : ''); ?>">
				</div>
				<?php wp_nonce_field( 'commercioo_forgot', 'comm-action-nonce', true, true ); ?>
				<button type="submit" class="btn btn-login" name="login"><?php esc_html_e( 'RESET PASSWORD', 'commercioo' ); ?></button>
			</form>
		</div>

	</div>
</div>