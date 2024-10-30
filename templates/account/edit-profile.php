<div class="content-account-menu">
	<form action="" method="post">
		<div class="row">
            <div class="form-group col-md-6">
                <label>First Name <span class="text-danger">*</span></label>
                <input type="text" name="account_first_name" class="form-control c-form-control" placeholder="First Name" value="<?php echo esc_attr( get_user_meta( $current_user->ID, 'first_name', true ) ); ?>">
            </div>
            <div class="form-group col-md-6">
                <label>Last Name <span class="text-danger">*</span></label>
                <input type="text" name="account_last_name" class="form-control c-form-control" placeholder="Last Name" value="<?php echo esc_attr( get_user_meta( $current_user->ID, 'last_name', true ) ); ?>">
            </div>
        </div>
		<div class="row">
            <div class="form-group col-md-12">
                <label>Display Name <span class="text-danger">*</span></label>
                <input type="text" name="account_display_name" class="form-control c-form-control" placeholder="First Name" value="<?php echo esc_attr( $current_user->display_name ); ?>">
            </div>
        </div>
		<div class="row">
            <div class="form-group col-md-12">
                <label>Email <span class="text-danger">*</span></label>
                <input type="email" name="account_email" class="form-control c-form-control" placeholder="Email" value="<?php echo esc_attr( $current_user->user_email ); ?>">
            </div>
        </div>
        <div class="password-fields">
			<div class="row">
	            <div class="form-group col-md-12">
	                <label>Current Password </label>
	                <input type="password" name="password_current" class="form-control c-form-control" placeholder="leave blank to leave unchanged">
	            </div>
	        </div>
			<div class="row">
	            <div class="form-group col-md-12">
	                <label>New Password </label>
	                <input type="password" name="password_1" class="form-control c-form-control" placeholder="leave blank to leave unchanged">
	            </div>
	        </div>
			<div class="row">
	            <div class="form-group col-md-12">
	                <label>Confirm New Password </label>
	                <input type="password" name="password_2" class="form-control c-form-control" placeholder="leave blank to leave unchanged">
	            </div>
	        </div>
        </div>
		<div class="row">
            <div class="form-group col-md-12">
            	<input type="hidden" name="action" value="update_account">
            	<?php wp_nonce_field( 'update_account', 'comm-action-nonce', true, true ); ?>
                <input type="submit" value="Update Account" class="btn btn-blue">
            </div>
        </div>
	</form>
</div>