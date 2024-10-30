<?php
/**
 * Fired during plugin init
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    Commercioo
 * @subpackage Commercioo/includes
 */

/**
 *
 * This class use to check version update.
 *
 * @since      1.0.0
 * @package    Commercioo
 * @subpackage Commercioo/includes
 * @author     Your Name <email@example.com>
 */
class Commercioo_Changelog
{

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	public $version;

	/**
	 * The current version changelog
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      array $changelog List of changelog
	 */
	public $changelog;

	public function __construct($version) {
		$this->version = $version;
		$this->changelog = include(plugin_dir_path(dirname(__FILE__)) . 'includes/data/database-changelog.php');

	}

	public function comm_get_current_database_version(){
		$database_versions     = get_option( 'commercioo_database_version', array() );
		$database_versions     = is_array( $database_versions ) ? $database_versions : array( $database_versions );
		return $database_versions;
	}

	public function comm_set_current_database_version(){
		$database_versions = $this->comm_get_current_database_version();
    	$database_versions = array_merge($database_versions, array_keys( $this->changelog ));
    	$database_versions = array_filter($database_versions);
        $database_versions = array_unique((array)$database_versions);
        $database_versions = array_values($database_versions);
    	update_option( 'commercioo_database_version', $database_versions );
    	delete_transient('comm_onboarding');
	}

	public function comm_compare_version(){
		$database_versions     = $this->comm_get_current_database_version();
		$un_updated_db_version = array_diff( array_keys( $this->changelog ), $database_versions );
		//has un_updated_db_version or not
		if ( !empty( $un_updated_db_version ) ) {
			return $un_updated_db_version;
		}else{
			return false;
		}
	}

}