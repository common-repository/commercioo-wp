<?php
/**
 * The class responsible for Commercioo System Status
 * 
 * @since    0.2.5
 */

class Commercioo_System_Status {

	/**
	 * Register system status page on admin
	 * Will be located under tools.php menu
	 * 
	 * @since    0.2.5
	 */
	public function register_admin_page() {
		// Commercioo menu
		add_menu_page(
            __( 'Commercioo', 'commercioo' ),
            __( 'Commercioo', 'commercioo' ),
            'manage_commercioo',
            'comm-system-status',
			array( $this, 'system_status_callback' ),
			plugin_dir_url( dirname( __FILE__ ) ) . 'admin/img/logo.png', 
			40
        );

		// Commercioo tools submenu
		add_submenu_page( 
			'comm-system-status', 
			__( 'System Status', 'commercioo' ),
            __( 'System Status', 'commercioo' ),
			'manage_commercioo',
			'comm-system-status',
			array( $this, 'system_status_callback' ),
			0
		);
	}

	/**
	 * Load the system status page
	 * 
	 * @since    0.2.5
	 */
	public function system_status_callback() {
		$environment = $this->get_environment_status( 'formatted' );		
		include_once plugin_dir_path(__FILE__) . '../admin/partials/commercioo-system-status-display.php';
	}

	/**
	 * Get environment status
	 * 
	 * @since    0.2.5
	 * @param    string    $return_type
	 * @return   array     $environment_status
	 */
	public function get_environment_status( $return_type = 'raw' ) {
		$curl_version       = $this->get_curl_version();
		$database_version   = $this->get_database_version();
		$environment_status = apply_filters( 'commercioo_environment_status', array(
			'home_url'                  => get_option( 'home' ),
			'site_url'                  => get_option( 'siteurl' ),
			'version'                   => COMMERCIOO_VERSION,
			'database_version'          => $this->get_current_database_version(),
			'wp_version'                => get_bloginfo( 'version' ),
			'wp_multisite'              => is_multisite(),
			'wp_memory_limit'           => WP_MEMORY_LIMIT,
			'wp_debug_mode'             => ( defined( 'WP_DEBUG' ) && WP_DEBUG ),
			'wp_cron'                   => ! ( defined( 'DISABLE_WP_CRON' ) && DISABLE_WP_CRON ),
			'language'                  => get_locale(),
			'server_info'               => isset( $_SERVER['SERVER_SOFTWARE'] ) ? trim( wp_unslash( $_SERVER['SERVER_SOFTWARE'] ) ) : '',
			'php_version'               => phpversion(),
			'php_post_max_size'         => ini_get( 'post_max_size' ),
			'php_max_execution_time'    => (int) ini_get( 'max_execution_time' ),
			'php_max_input_vars'        => (int) ini_get( 'max_input_vars' ),
			'curl_version'              => $curl_version,
			'suhosin_installed'         => extension_loaded( 'suhosin' ),
			'max_upload_size'           => size_format( wp_max_upload_size() ),
			'mysql_version'             => $database_version['number'],
			'mysql_version_string'      => $database_version['string'],
			'default_timezone'          => date_default_timezone_get(),
			'fsockopen_or_curl_enabled' => ( function_exists( 'fsockopen' ) || function_exists( 'curl_init' ) ),
			'soapclient_enabled'        => class_exists( 'SoapClient' ),
			'domdocument_enabled'       => class_exists( 'DOMDocument' ),
			'gzip_enabled'              => is_callable( 'gzopen' ),
			'mbstring_enabled'          => extension_loaded( 'mbstring' ),
		) );

		// format the data
		if ( 'formatted' === $return_type ) {
			$environment_status = $this->format_status_data( $environment_status );
		}

		return $environment_status;
	}

	/**
	 * Format the status data for display purpose
	 * 
	 * @since    0.2.5
	 * @access   private
	 * @param    array
	 * @return   array
	 */
	private function format_status_data( $data = array() ) {
		foreach ( $data as $key => $value ) {
			// false value
			if ( false === $value ) {
				$value = '<span class="dashicons dashicons-no"></span>';
			}

			// true value
			if ( true === $value ) {
				$value = '<span class="dashicons dashicons-yes"></span>';
			}

			// set the formatted data
			$data[ $key ] = $value;
		}

		return $data;
	}

	/**
	 * Get cURL version
	 * 
	 * @since    0.2.5
	 * @access   private
	 * @return   string
	 */
	private function get_curl_version() {
		$curl_version = '';

		if ( function_exists( 'curl_version' ) ) {
			$curl_version = curl_version();
			$curl_version = $curl_version['version'] . ', ' . $curl_version['ssl_version'];
		} elseif ( extension_loaded( 'curl' ) ) {
			$curl_version = __( 'cURL installed but unable to retrieve version.', 'woocommerce' );
		}

		return $curl_version;
	}

	/**
	 * Get database version
	 * 
	 * @since    0.2.5
	 * @access   private
	 * @return   array
	 */
	private function get_database_version() {
		global $wpdb;
	
		if ( empty( $wpdb->is_mysql ) ) {
			return array(
				'string' => '',
				'number' => '',
			);
		}
	
		if ( $wpdb->use_mysqli ) {
			$server_info = mysqli_get_server_info( $wpdb->dbh );
		} else {
			$server_info = mysql_get_server_info( $wpdb->dbh );
		}
	
		return array(
			'string' => $server_info,
			'number' => preg_replace( '/([^\d.]+).*/', '', $server_info ),
		);
	}

	/**
	 * Get required page status
	 * 
	 * @since    0.2.5
	 * @param    string    $page_key
	 * @return   string    $status
	 */
	public function get_required_page_status( $page_key ) {
		$notification  = new Commercioo_Notification();
		$required_page = $notification->required_pages[ $page_key ];
		$raw_status    = $notification->required_pages_notifications( $page_key, $required_page );

		if ( ! empty( $raw_status ) ) {
			$status    = sprintf( '<span class="dashicons dashicons-warning"></span> %s', $raw_status['title'] );
		}
		else {
			$post_id   = get_option( $required_page['option_name'], null );
			$post_data = get_post( $post_id );
			$permalink = get_the_permalink( $post_data );
			$path      = str_replace( site_url(), '', $permalink );
			$status    = sprintf( '<span class="dashicons dashicons-yes"></span> #%d - <a href="%s" target="_blank">%s</a>', $post_data->ID, $permalink, $path );
		}

		return $status;
	}

	/**
	 * Get current database version
	 * 
	 * @since    0.2.5
	 * @return   string    $version
	 */
	public function get_current_database_version() {
		$database_versions = get_option( 'commercioo_database_version', array() );
		$database_versions = is_array( $database_versions ) ? $database_versions : array( $database_versions );
		
		// get the latest version
		$version = ! empty( $database_versions ) ? end( $database_versions ) : '0.0.1';

		return $version;
	}

	
}
