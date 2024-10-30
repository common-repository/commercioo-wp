<div class="c-wrapper">
	<?php
	include_once plugin_dir_path( __FILE__ ) . 'loader.php';
	include_once plugin_dir_path( __FILE__ ) . 'sidebar.php';
	?>
	<div id="content-wrapper">
		<?php
		// include_once plugin_dir_path(__FILE__) . 'license.php';

		// commercioo notification
		$notification = new Commercioo_Notification();
		$notification->init();
		$notification->render_notification();

		if ( file_exists( comm_controller()->getPartFileContent( plugin_dir_path( __FILE__ ) ) . ".php" ) ) {
			global $comm_options, $comm_country;
			include_once comm_controller()->getPartFileContent( plugin_dir_path( __FILE__ ) ) . ".php";
		}
		?>

		<div class='comm-admin-footer'>
			<div class='left-label'>
				<?php 
				$powered_by_label = new Commercioo_Powered_By_Label();
				$powered_by_label->render_admin_footer();
				?>
			</div>
			<div class='right-label'>
				<?php printf( '%s %s', __( 'Version', 'commercioo' ), COMMERCIOO_VERSION ) ?>
			</div>
		</div>
	</div>
</div>


