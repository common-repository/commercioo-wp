<div class="wrap commercioo-wrapper">

	<h2 id='commerciooo-admin-title'><img src='<?php echo COMMERCIOO_URL . 'admin/img/logo.png' ?>'> <span>Commercioo</span></h2>

	<!-- updated notification -->
	<?php if( isset( $updated ) && $updated ) : ?>
	
	<div class="updated notice">
		<p><?php esc_html_e( 'Your settings has been successfully updated!', 'commercioo-agency' ) ?></p>
	</div>

	<?php endif ?>

	<nav class="nav-tab-wrapper comm-nav-tab-wrapper">	
		
		<?php
		$screen   = get_current_screen();
		$page_id  = $screen->id;
		$tab_menu = apply_filters( 'commercioo_admin_tabs', array( 
			array(
				'url'     => admin_url( 'admin.php?page=comm-system-status' ), 
				'label'   => __( 'System Status', 'commercioo-agency' ),
				'page_id' => 'toplevel_page_comm-system-status',
			),			
		) );

		// print the tabs
		foreach ( $tab_menu as $menu ) {
			$active_status = ( $menu['page_id'] === $page_id ) ? 'nav-tab-active' : '';
			printf( '<a href="%s" class="nav-tab %s">%s</a>', esc_url( $menu['url'] ), esc_attr( $active_status ), esc_html( $menu['label'] ) );
		}

		?>

	</nav>

	<div class="commercioo-onboard">
		<div class="commercioo-onboard-header">
			<img class="logo" src="<?php echo esc_url( COMMERCIOO_URL . 'admin/img/logo-commercioo.png' ) ?>" alt="Commercioo" />
			<ul class="steps">
				<li class="step-email active"><span class="number">1</span><span class="check"></span><span class="text">Email</span></li>
				<li class="step-license"><span class="number">2</span><span class="check"></span><span class="text">License Key</span></li>
				<li class="step-install"><span class="number">3</span><span class="check"></span><span class="text">Install</span></li>
			</ul>
		</div>
		<div class="commercioo-onboard-content">
			<div class="onboard-content show" id="step-email">
				<div class="onboard-content-container">
					<h2>Onboarding</h2>
					<p>Enter your registered email and password when you purchase Commercioo.</p>
					<div class="onboard-inner">
						<div class="block"></div>
						<div class="onboard-inner-content">
                            <?php
                            $condition = !empty( $email ) && !empty( $pass )  ? '':'hide';
                            ?>
							<div class="email-container filled <?php echo esc_attr($condition); ?>">
									<?php echo wp_kses_post(get_avatar( $email, 80 )); ?>
									<h3><?php echo esc_html( $email ); ?></h3>
									<button class="switch-account">Switch Account</button>
                                    <input type="hidden" id="comm-onboarding-account-email" value="<?php echo esc_attr(!empty(
                                            $email ) ? esc_attr( $email ) : ''); ?>">
                                    <input type="hidden" id="comm-onboarding-account-pass" value="<?php echo esc_attr(!empty(
                                    $pass ) ? esc_attr( $pass ) : ''); ?>">
							</div>
							<div class="email-container form <?php echo esc_attr(empty( $email ) || empty( $pass ) ? '' : 'hide'); ?>">
								<label>Your Email</label>
								<input type="email" placeholder="Ex: commercioo@mail.com" id="input-email" value="<?php echo esc_attr(!empty( $email ) ? esc_attr( $email ) : ''); ?>">
								<br>
								<label>Your Password</label>
								<input type="password" id="input-password">
								<input type="hidden" value="<?php echo esc_attr(!empty( $token ) ? esc_attr( $token ) : ''); ?>" id="input-token">
							</div>
						</div>
						<div class="onboard-inner-footer">
							<button type="button" class="onboard-button onboard-button-primary" data-bs-target="license">Next</button>
						</div>
					</div>
				</div>
				<div class="error-container">
				</div>
			</div>
			<div class="onboard-content" id="step-license">
				<div class="onboard-content-container">
					<h2>Enter your license key</h2>
					<p>Enter the license key for each of the following products you want to install, and click the checkbox on the product you want to install.</p>
					<div class="onboard-inner">
						<div class="block"></div>
						<div class="onboard-inner-header">
							<img alt="" src="" class="avatar avatar-46 photo" loading="lazy" width="46" height="46">
							<h4></h4>
						</div>
						<div class="onboard-inner-content">
							<ul class="onboard-plugins">
							</ul>
						</div>
						<div class="onboard-inner-footer">
							<button type="button" class="onboard-button" data-bs-target="email">Prev</button>
							<button type="button" class="onboard-button onboard-button-primary" data-bs-target="install">Next</button>
						</div>
					</div>
				</div>
				<div class="error-container">
				</div>
			</div>
			<div class="onboard-content" id="step-install">
				<div class="onboard-content-container">
					<h2>Begin installation</h2>
					<p>You are almost done! Wait until the installation is complete.</p>
					<div class="onboard-inner">
						<div class="block"></div>
						<div class="onboard-inner-header">
							<img alt="" src="" class="avatar avatar-46 photo" loading="lazy" width="46" height="46">
							<h4></h4>
						</div>
						<div class="onboard-inner-content">
							<ul class="onboard-install">
							</ul>
							<div class="onboard-install-progress">
								<div class="percentage">0%</div>
								<div class="bar">
									<div></div>
								</div>
							</div>
						</div>
						<div class="onboard-inner-footer">
							<button type="button" class="onboard-button" data-bs-target="license" disabled>Prev</button>
							<button type="button" class="onboard-button onboard-button-primary" data-bs-target="activate" disabled>Activate All</button>
						</div>
					</div>
				</div>
				<div class="error-container">
				</div>
			</div>
			<div class="onboard-content" id="step-activate">
				<div class="onboard-content-container">
					<h2>Activating Product</h2>
					<p>Wait until the activation is complete.</p>
					<div class="onboard-inner">
						<div class="block"></div>
						<div class="onboard-inner-header">
							<img alt="" src="" class="avatar avatar-46 photo" loading="lazy" width="46" height="46">
							<h4></h4>
						</div>
						<div class="onboard-inner-content">
							<ul class="onboard-activate">
							</ul>
						</div>
						<div class="onboard-inner-footer">
							<a class="onboard-button onboard-button-primary" href="<?php echo esc_url(admin_url( 'admin.php?page=comm_prod' )); ?>">Go to Commercioo Dashboard</a>
						</div>
					</div>
				</div>
				<div class="error-container">
				</div>
			</div>
		</div>
	</div>
</div>