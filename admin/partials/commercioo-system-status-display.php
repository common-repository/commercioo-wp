<div class='wrap commercioo'>
	<h2 id='commerciooo-admin-title'><img src='<?php echo COMMERCIOO_URL . 'admin/img/logo.png' ?>'> <span>Commercioo</span></h2>

	<nav class="nav-tab-wrapper comm-nav-tab-wrapper">	
		
		<?php
		$screen   = get_current_screen();
		$page_id  = $screen->id;
		$tab_menu = apply_filters( 'commercioo_admin_tabs', array( 
			array(
				'url'     => admin_url( 'admin.php?page=comm-system-status' ), 
				'label'   => __( 'System Status', 'commercioo' ),
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

	<table class="commercioo-status-table widefat" cellspacing="0" id="status">
		<thead>
			<tr>
				<th colspan="3"><h2><?php esc_html_e( 'WordPress Environment', 'commercioo' ) ?></h2></th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td><?php esc_html_e( 'WordPress address (URL)', 'commercioo' ) ?>:</td>
				<td><?php echo wp_kses_post( $environment['home_url'] ) ?></td>
			</tr>
			<tr>
				<td><?php esc_html_e( 'Site address (URL)', 'commercioo' ) ?>:</td>
				<td><?php echo wp_kses_post( $environment['site_url'] ) ?></td>
			</tr>
			<tr>
				<td><?php esc_html_e( 'Commercioo version', 'commercioo' ) ?>:</td>
				<td><?php echo wp_kses_post( $environment['version'] ) ?></td>
			</tr>
			<tr>
				<td><?php esc_html_e( 'Database version', 'commercioo' ) ?>:</td>
				<td><?php echo wp_kses_post( $environment['database_version'] ) ?></td>
			</tr>
			<tr>
				<td><?php esc_html_e( 'WordPress version', 'commercioo' ) ?>:</td>
				<td><?php echo wp_kses_post( $environment['wp_version'] ) ?></td>
			</tr>
			<tr>
				<td><?php esc_html_e( 'WordPress multisite', 'commercioo' ) ?>:</td>
				<td><?php echo wp_kses_post( $environment['wp_multisite'] ) ?></td>
			</tr>
			<tr>
				<td><?php esc_html_e( 'WordPress memory limit', 'commercioo' ) ?>:</td>
				<td><?php echo wp_kses_post( $environment['wp_memory_limit'] ) ?></td>
			</tr>
			<tr>
				<td><?php esc_html_e( 'WordPress debug mode', 'commercioo' ) ?>:</td>
				<td><?php echo wp_kses_post( $environment['wp_debug_mode'] ) ?></td>
			</tr>
			<tr>
				<td><?php esc_html_e( 'WordPress cron', 'commercioo' ) ?>:</td>
				<td><?php echo wp_kses_post( $environment['wp_cron'] ) ?></td>
			</tr>
			<tr>
				<td><?php esc_html_e( 'Language', 'commercioo' ) ?>:</td>
				<td><?php echo wp_kses_post( $environment['language'] ) ?></td>
			</tr>
		</tbody>
	</table>
	
	<table class="commercioo-status-table widefat" cellspacing="0">
		<thead>
			<tr>
				<th colspan="3" data-export-label="<?php esc_attr_e( 'Server Environment', 'commercioo' ) ?>"><h2><?php esc_html_e( 'Server Environment', 'commercioo' ) ?></h2></th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td><?php esc_html_e( 'Server info', 'commercioo' ) ?>:</td>
				<td><?php echo wp_kses_post( $environment['server_info'] ) ?></td>
			</tr>
			<tr>
				<td><?php esc_html_e( 'PHP version', 'commercioo' ) ?>:</td>
				<td><?php echo wp_kses_post( $environment['php_version'] ) ?></td>
			</tr>
			<tr>
				<td><?php esc_html_e( 'PHP post max size', 'commercioo' ) ?>:</td>
				<td><?php echo wp_kses_post( $environment['php_post_max_size'] ) ?></td>
			</tr>
			<tr>
				<td><?php esc_html_e( 'PHP time limit', 'commercioo' ) ?>:</td>
				<td><?php echo wp_kses_post( $environment['php_max_execution_time'] ) ?></td>
			</tr>
			<tr>
				<td><?php esc_html_e( 'PHP max input vars', 'commercioo' ) ?>:</td>
				<td><?php echo wp_kses_post( $environment['php_max_input_vars'] ) ?></td>
			</tr>
			<tr>
				<td><?php esc_html_e( 'cURL version', 'commercioo' ) ?>:</td>
				<td><?php echo wp_kses_post( $environment['curl_version'] ) ?></td>
			</tr>	
			<tr>
				<td><?php esc_html_e( 'MySQL version', 'commercioo' ) ?>:</td>
				<td><?php echo wp_kses_post( $environment['mysql_version_string'] ) ?></td>
			</tr>
			<tr>
				<td><?php esc_html_e( 'Max upload size', 'commercioo' ) ?>:</td>
				<td><?php echo wp_kses_post( $environment['max_upload_size'] ) ?></td>
			</tr>
			<tr>
				<td><?php esc_html_e( 'Default timezone is UTC', 'commercioo' ) ?>:</td>
				<td><?php echo wp_kses_post( $environment['default_timezone'] ) ?></td>
			</tr>		
		</tbody>
	</table>

	<table class="commercioo-status-table widefat" cellspacing="0">
		<thead>
			<tr>
				<th colspan="3"><h2><?php esc_html_e( 'Commercioo Pages', 'commercioo' ) ?></h2></th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td>Checkout Page:</td>
				<td><?php echo wp_kses_post( $this->get_required_page_status( 'checkout' ) ) ?></td>
			</tr>
			<tr>
				<td>Cart Page:</td>
				<td><?php echo wp_kses_post( $this->get_required_page_status( 'cart' ) ) ?></td>
			</tr>
			<tr>
				<td>Product Page:</td>
				<td><?php echo wp_kses_post( $this->get_required_page_status( 'product' ) ) ?></td>
			</tr>
			<tr>
				<td>Account Page:</td>
				<td><?php echo wp_kses_post( $this->get_required_page_status( 'account' ) ) ?></td>
			</tr>
			<tr>
				<td>Thank You Page:</td>
				<td><?php echo wp_kses_post( $this->get_required_page_status( 'thank_you' ) ) ?></td>
			</tr>
		</tbody>
	</table>

</div>