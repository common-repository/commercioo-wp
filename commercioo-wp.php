<?php
/**
 * @wordpress-plugin
 * Plugin Name:       Commercioo WP
 * Plugin URI:        https://commercioo.com
 * Description:       Seamless ecommerce solution for your business
 * Version:           0.4.9
 * Author:            Commercioo
 * Author URI:        https://profiles.wordpress.org/commercioo
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       commercioo
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'COMMERCIOO_VERSION', '0.4.9' );

/**
 * Define constants to retrieve plugin folder location.
 */
define( 'COMMERCIOO_FILE', __FILE__ );
define( 'COMMERCIOO_PATH', plugin_dir_path( __FILE__ ) );
define( 'COMMERCIOO_URL', plugin_dir_url( __FILE__ ) );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-commercioo-activator.php
 */
function activate_commercioo() {
    require_once plugin_dir_path(__FILE__) . 'includes/class-commercioo-activator.php';
    Commercioo_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-commercioo-deactivator.php
 */
function deactivate_commercioo() {
    require_once plugin_dir_path(__FILE__) . 'includes/class-commercioo-deactivator.php';
    Commercioo_Deactivator::deactivate();
}

register_activation_hook(__FILE__, 'activate_commercioo');
register_deactivation_hook(__FILE__, 'deactivate_commercioo');

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path(__FILE__) . 'includes/class-commercioo.php';

require plugin_dir_path(__FILE__) . 'includes/class-commercioo-changelog.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_commercioo() {
    $plugin = new Commercioo();
    $plugin->run();
}

run_commercioo();