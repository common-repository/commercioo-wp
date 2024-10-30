<?php
	/**
     * Check weather customer exist or not
     */
	$id= absint($_GET['id']);
	$customer = new \Commercioo\Models\Customer($id);
	$billing_address = $customer->get_billing_address();
	$shipping_address = $customer->get_shipping_address();
	$first_name_ba = isset( $billing_address['first_name'] ) ? $billing_address['first_name'] :'';
	$last_name_ba = isset( $billing_address['last_name'] ) ? $billing_address['last_name']  : "";
	$email_ba = isset( $billing_address['email'] ) ? $billing_address['email']  : "";
	$phone_ba = isset( $billing_address['phone'] ) ? $billing_address['phone']  : "";
	$company_ba = isset( $billing_address['company'] ) ? $billing_address['company']  : "";
	$state_ba = isset( $billing_address['state'] ) ? $billing_address['state']  : "";
	$city_ba = isset( $billing_address['city'] ) ? $billing_address['city']  : "";
	$zip_ba = isset( $billing_address['zip'] ) ? $billing_address['zip']  : "";
	$street_address_ba = isset( $billing_address['street_address'] ) ? $billing_address['street_address']  : "";

?>
<!-- Start Title -->
<div class="col-md-12 c-col-container">
    <div class="c-list-orders">
        <div class="d-flex align-items-center">
            <h2 class="page-title"><?php _e("Edit Customer", "Commercioo_title"); ?></h2>
        </div>
    </div>
</div>
<!-- End Title -->

<!-- Start Form Address -->
<div class="col-md-12 c-col-container">
    <div class="c-add-orders c-general-orders">
	    <form class="needs-validation" novalidate data-cond="orders" method="post" action="<?php echo admin_url( 'admin-post.php' ); ?>">
	    	<div class="row">
	    		<?php comm_print_notices(); ?>
	    		<div class="col-md-6">
			    	<div class="comm_billing_address">
			        	<h2 class="widget-title-big">Billing Address</h2>
				        <div class="row">
				            <div class="col-md-6">
				                <div class="form-group">
				                    <label>First Name</label>
				                    <input type="text" name="billing_address[billing_first_name]" class="form-control c-setting-form-control c-input-form
				                    c-set-cursor-pointer"
				                           placeholder="First Name" value="<?php echo esc_attr($first_name_ba);?>">
				                    <div class="invalid-feedback">
				                        Please enter the First Name
				                    </div>
				                </div>
				            </div>
				            <div class="col-md-6">
				                <div class="form-group">
				                    <label>Last Name</label>
				                    <input type="text" name="billing_address[billing_last_name]" class="form-control
				                    c-setting-form-control c-input-form c-set-cursor-pointer"
				                           placeholder="Last Name" value="<?php echo esc_attr($last_name_ba) ?>">
				                </div>
				            </div>
				            <div class="col-md-6">
				                <div class="form-group">
				                    <label>Email</label>
				                    <input type="text" name="billing_address[billing_email]" class="form-control
				                    c-setting-form-control c-input-form c-set-cursor-pointer"
				                           placeholder="Email" value="<?php echo esc_attr($email_ba); ?>">
				                    <div class="invalid-feedback">
				                        Please enter Email
				                    </div>
				                </div>
				            </div>
				            <div class="col-md-6">
				                <div class="form-group">
				                    <label>Mobile</label>
				                    <input type="text" name="billing_address[billing_phone]" class="form-control
				                    c-setting-form-control c-input-form c-set-cursor-pointer"
				                           placeholder="Mobile" value="<?php echo esc_attr($phone_ba); ?>">
				                </div>
				            </div>
				            <div class="col-md-6">
				                <div class="form-group">
				                    <label>Company Name</label>
				                    <input type="text" name="billing_address[billing_company]" class="form-control
				                    c-setting-form-control c-input-form c-set-cursor-pointer"
				                           placeholder="Company Name" value="<?php echo esc_attr($company_ba); ?>">
				                </div>
				            </div>
				            <div class="col-md-6">
				                <div class="form-group">
				                    <label>Country</label>
				                    <?php
										$selected = $billing_address['country'];
									?>
				                    <select name="billing_address[billing_country]" class="form-control c-setting-form-control">
				                        <?php foreach ($comm_country as $k => $icountry): ?>
				                            <option value="<?php echo esc_attr( $k ) ?>" <?php echo esc_attr($k === $selected ? 'selected' : ''); ?>><?php echo esc_attr($icountry); ?></option>
				                        <?php endforeach; ?>
				                    </select>
				                </div>
				            </div>

				            <div class="col-md-6">
				                <div class="form-group">
				                    <label>State / Province</label>
				                    <input type="text" name="billing_address[billing_state]" class="form-control
				                    c-setting-form-control c-input-form c-set-cursor-pointer"
				                           placeholder="State / Province" value="<?php echo esc_attr($state_ba); ?>">
				                </div>
				            </div>
				            <div class="col-md-6">
				                <div class="form-group">
				                    <label>Town / City</label>
				                    <input type="text" name="billing_address[billing_city]" class="form-control
				                    c-setting-form-control c-input-form c-set-cursor-pointer"
				                           placeholder="Town/City" value="<?php echo esc_attr($city_ba); ?>">
				                </div>
				            </div>

				            
				            <div class="col-md-6">
				                <div class="form-group">
				                    <label>Postcode / ZIP</label>
				                    <input type="text" name="billing_address[billing_zip]" class="form-control
				                    c-setting-form-control c-input-form c-set-cursor-pointer"
				                           placeholder="Postcode / ZIP" value="<?php echo esc_attr($zip_ba); ?>">
				                </div>
				            </div>
				            <div class="col-md-6">
				                <div class="form-group">
				                    <label>Street address</label>
				                    <input type="text" name="billing_address[billing_street_address]" class="form-control
				                    c-setting-form-control c-input-form c-set-cursor-pointer"
				                           placeholder="Street Address" value="<?php echo esc_attr($street_address_ba); ?>">
				                </div>
				            </div>
				        </div>
				    </div>

				</div>
		        <?php
                $first_name_sa = isset( $shipping_address['first_name'] ) ? $shipping_address['first_name'] :'';
                $last_name_sa = isset( $billing_address['last_name'] ) ? $billing_address['last_name']  : "";
                $email_sa = isset( $billing_address['email'] ) ? $billing_address['email']  : "";
                $phone_sa = isset( $billing_address['phone'] ) ? $billing_address['phone']  : "";
                $company_sa = isset( $billing_address['company'] ) ? $billing_address['company']  : "";
                $state_sa = isset( $billing_address['state'] ) ? $billing_address['state']  : "";
                $city_sa = isset( $billing_address['city'] ) ? $billing_address['city']  : "";
                $zip_sa = isset( $billing_address['zip'] ) ? $billing_address['zip']  : "";
                $street_address_sa = isset( $billing_address['street_address'] ) ? $billing_address['street_address']  : "";
		        ?>
		        <div class="col-md-6">
			        <div class="comm_shipping_address">
			            <h2 class="widget-title-big">Shipping Address</h2>
			            <div class="row">
			                <div class="col-md-6">
			                    <div class="form-group">
			                        <label>First Name</label>
			                        <input type="text" name="shipping_address[shipping_first_name]" class="form-control c-setting-form-control c-input-form
									c-set-cursor-pointer"
			                               placeholder="First Name" value="<?php echo esc_attr($first_name_sa); ?>">
			                        <div class="invalid-feedback">
			                            Please enter the First Name
			                        </div>
			                    </div>
			                </div>
			                <div class="col-md-6">
			                    <div class="form-group">
			                        <label>Last Name</label>
			                        <input type="text" name="shipping_address[shipping_last_name]" class="form-control
									c-setting-form-control c-input-form c-set-cursor-pointer"
			                               placeholder="Last Name" value="<?php echo esc_attr($last_name_sa); ?>">
			                    </div>
			                </div>
			                <div class="col-md-6">
			                    <div class="form-group">
			                        <label>Email</label>
			                        <input type="text" name="shipping_address[shipping_email]" class="form-control
									c-setting-form-control c-input-form c-set-cursor-pointer"
			                               placeholder="Email" value="<?php echo esc_attr($email_sa); ?>">
			                        <div class="invalid-feedback">
			                            Please enter Email
			                        </div>
			                    </div>
			                </div>
			                <div class="col-md-6">
			                    <div class="form-group">
			                        <label>Mobile</label>
			                        <input type="text" name="shipping_address[shipping_phone]" class="form-control
									c-setting-form-control c-input-form c-set-cursor-pointer"
			                               placeholder="Mobile" value="<?php echo esc_attr($phone_sa); ?>">
			                    </div>
			                </div>
			                <div class="col-md-6">
			                    <div class="form-group">
			                        <label>Company Name</label>
			                        <input type="text" name="shipping_address[shipping_company]" class="form-control
									c-setting-form-control c-input-form c-set-cursor-pointer"
			                               placeholder="Company Name" value="<?php echo esc_attr($company_sa); ?>">
			                    </div>
			                </div>
			                <div class="col-md-6">
			                    <div class="form-group">
			                        <label>Country</label>
			                        <?php
										$selected = $shipping_address['country'];
										if ( isset( $_POST['country'] ) ) {
											$selected = sanitize_text_field( wp_unslash( $_POST['country'] ) );
										}
									?>
			                        <select name="shipping_address[shipping_country]" class="form-control c-setting-form-control">
			                            <?php foreach ($comm_country as $k => $icountry): ?>
			                                <option value="<?php echo esc_attr( $k ) ?>" <?php echo $k === $selected ? 'selected' : '' ?>><?php echo esc_attr($icountry); ?></option>
			                            <?php endforeach; ?>
			                        </select>
			                    </div>
			                </div>

			                <div class="col-md-6">
			                    <div class="form-group">
			                        <label>State / Province</label>
			                        <input type="text" name="shipping_address[shipping_state]" class="form-control
									c-setting-form-control c-input-form c-set-cursor-pointer"
			                               placeholder="State / Province" value="<?php echo esc_attr($state_sa); ?>">
			                    </div>
			                </div>

			                <div class="col-md-6">
			                    <div class="form-group">
			                        <label>Town / City</label>
			                        <input type="text" name="shipping_address[shipping_city]" class="form-control
									c-setting-form-control c-input-form c-set-cursor-pointer"
			                               placeholder="Town/City" value="<?php echo esc_attr($city_sa); ?>">
			                    </div>
			                </div>

			                
			                <div class="col-md-6">
			                    <div class="form-group">
			                        <label>Postcode / ZIP</label>
			                        <input type="text" name="shipping_address[shipping_zip]" class="form-control
									c-setting-form-control c-input-form c-set-cursor-pointer"
			                               placeholder="Postcode / ZIP" value="<?php echo esc_attr($zip_sa); ?>">
			                    </div>
			                </div>
			                <div class="col-md-6">
			                    <div class="form-group">
			                        <label>Street address</label>
			                        <input type="text" name="shipping_address[shipping_street_address]" class="form-control
									c-setting-form-control c-input-form c-set-cursor-pointer"
			                               placeholder="Street Address" value="<?php echo esc_attr($street_address_sa); ?>">
			                    </div>
			                </div>
			            </div>
			        </div>
			    </div>
	       
		    </div>
	        <div class="col-md-12 mt-4 mb-4 c-border-line c-border-label"></div>

	        <div class="row">
	            <div class="col-md-4">
	            	<input type="hidden" name="action" value="comm_update_customer">
	            	<input type="hidden" name="user_id" value="<?php echo esc_attr($id);?>">
        			<?php wp_nonce_field( 'comm_update_customer', 'comm-action-nonce', true, true ); ?>
	                <button type="submit" class="btn btn-primary c-save-orders">Save</button>
	                <span onclick="location.href = '<?php echo comm_controller()->comm_dash_page("comm_customers");?>'" class="btn btn-primary c-save-orders">Cancel</span>
	            </div>
	        </div>
	    </form>
	</div>
</div>
<!-- End Form Address -->