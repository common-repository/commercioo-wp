<?php

namespace Commercioo\Admin;
if (!class_exists("Commercioo\Admin\Required_Plugin_Page")) {

    class Required_Plugin_Page
    {
        public $TGM_Plugin_Activation;
        /**
         * Holds arrays of plugin details.
         *
         * @since 1.0.0
         * @since 2.5.0 the array has the plugin slug as an associative key.
         *
         * @var array
         */
        public $plugins = array();
        /**
         * Default absolute path to folder containing bundled plugin zip files.
         *
         * @since 2.0.0
         *
         * @var string Absolute path prefix to zip file location for bundled plugins. Default is empty string.
         */
        public $default_path = '';
        /**
         * Holds configurable array of strings.
         *
         * Default values are added in the constructor.
         *
         * @since 2.0.0
         *
         * @var array
         */
        public $strings = array();
        /**
         * Regular expression to test if a URL is a WP plugin repo URL.
         *
         * @const string Regex.
         *
         * @since 2.5.0
         */
        const WP_REPO_REGEX = '|^http[s]?://wordpress\.org/(?:extend/)?plugins/|';

        /**
         * Arbitrary regular expression to test if a string starts with a URL.
         *
         * @const string Regex.
         *
         * @since 2.5.0
         */
        const IS_URL_REGEX = '|^http[s]?://|';
        /**
         * Holds the version of WordPress.
         *
         * @since 2.4.0
         *
         * @var int
         */
        public $wp_version;
        public function __construct()
        {
            $this->TGM_Plugin_Activation = new \TGM_Plugin_Activation();
            $this->wp_version = $GLOBALS['wp_version'];
        }
        /**
         * Check if a plugin can be updated, i.e. if we have information on the minimum WP version required
         * available, check whether the current install meets them.
         *
         * @since 2.5.0
         *
         * @param string $slug Plugin slug.
         * @return bool True if OK to update, false otherwise.
         */
        public function can_plugin_update( $slug ) {
            // We currently can't get reliable info on non-WP-repo plugins - issue #380.
            if ( 'repo' !== $this->plugins[ $slug ]['source_type'] ) {
                return true;
            }

            $api = $this->get_plugins_api( $slug );

            if ( false !== $api && isset( $api->requires ) ) {
                return version_compare( $this->wp_version, $api->requires, '>=' );
            }

            // No usable info received from the plugins API, presume we can update.
            return true;
        }
        /**
         * Inject information into the 'update_plugins' site transient as WP checks that before running an update.
         *
         * @since 2.5.0
         *
         * @param array $plugins The plugin information for the plugins which are to be updated.
         */
        public function inject_update_info( $plugins ) {
            $repo_updates = get_site_transient( 'update_plugins' );

            if ( ! is_object( $repo_updates ) ) {
                $repo_updates = new \stdClass;
            }

            foreach ( $plugins as $slug => $plugin ) {
                $file_path = $plugin['file_path'];
                if ( empty( $repo_updates->response[ $file_path ] ) ) {
                    $repo_updates->response[ $file_path ] = new \stdClass;
                }
                if($this->can_plugin_update( $slug )) {
                    // We only really need to set package, but let's do all we can in case WP changes something.
                    $repo_updates->response[$file_path]->slug = $slug;
                    $repo_updates->response[$file_path]->plugin = $file_path;
                    $repo_updates->response[$file_path]->new_version = $plugin['version'];
                    $repo_updates->response[$file_path]->package = $plugin['source'];
                    if (empty($repo_updates->response[$file_path]->url) && !empty($plugin['external_url'])) {
                        $repo_updates->response[$file_path]->url = $plugin['external_url'];
                    }
                }
            }
            set_site_transient( 'update_plugins', $repo_updates );
        }
        /**
         * Retrieve the download URL for a package.
         *
         * @since 2.5.0
         *
         * @param string $slug Plugin slug.
         * @return string Plugin download URL or path to local file or empty string if undetermined.
         */
        public function get_download_url( $slug ) {
            $dl_source = '';

            switch ( $this->plugins[ $slug ]['source_type'] ) {
                case 'repo':
                    return $this->get_wp_repo_download_url( $slug );
                case 'external':
                    return $this->plugins[ $slug ]['source'];
                case 'bundled':
                    return $this->default_path . $this->plugins[ $slug ]['source'];
            }

            return $dl_source; // Should never happen.
        }
        /**
         * Retrieve the download URL for a WP repo package.
         *
         * @since 2.5.0
         *
         * @param string $slug Plugin slug.
         * @return string Plugin download URL.
         */
        protected function get_wp_repo_download_url( $slug ) {
            $source = '';
            $api    = $this->get_plugins_api( $slug );

            if ( false !== $api && isset( $api->download_link ) ) {
                $source = $api->download_link;
            }

            return $source;
        }
        /**
         * Try to grab information from WordPress API.
         *
         * @since 2.5.0
         *
         * @param string $slug Plugin slug.
         * @return object Plugins_api response object on success, WP_Error on failure.
         */
        protected function get_plugins_api( $slug ) {
            static $api = array(); // Cache received responses.

            if ( ! isset( $api[ $slug ] ) ) {
                if ( ! function_exists( 'plugins_api' ) ) {
                    require_once ABSPATH . 'wp-admin/includes/plugin-install.php';
                }

                $response = plugins_api( 'plugin_information', array( 'slug' => $slug, 'fields' => array( 'sections' => false ) ) );

                $api[ $slug ] = false;

                if ( is_wp_error( $response ) ) {
                    wp_die( esc_html( $this->strings['oops'] ) );
                } else {
                    $api[ $slug ] = $response;
                }
            }

            return $api[ $slug ];
        }
        /**
         * commercioo_required_plugin_installer
         * An Ajax method for installing plugin.
         *
         * @return $json
         *
         * @since 1.0
         */
        public function commercioo_required_plugin_installer()
        {
            if (!current_user_can('install_plugins')){
                wp_die(__('Sorry, you are not allowed to install plugins on this site.', 'commercioo'));
            }
            if (!current_user_can('update_plugins')){
                wp_die(__('Sorry, you are not allowed to update plugins on this site.', 'commercioo'));
            }

            $nonce = sanitize_title($_POST["nonce"]);
            $plugin = sanitize_title($_POST["plugin"]);
            $is_update = sanitize_title($_POST["is_update"]);
            $source        = $this->get_download_url( $plugin );
            $button_classes = 'install button';
            $button_text = __('Install Now', 'commercioo');
            $main_plugin_file = self::get_plugin_file($plugin); // Get main plugin file

            $is_plugin_active = false;
            if (self::check_file_extension($main_plugin_file)) { // check file extension
                if (is_plugin_active($main_plugin_file)) {
                    // plugin activation confirmed
                    $is_plugin_active = true;
                    $button_classes = 'button disabled';
                    $button_text = __('Activated', 'commercioo');
                } else {
                    // It's installed, let's activate it
                    $button_classes = 'activate button button-primary';
                    $button_text = __('Activate', 'commercioo');
                }
            }
            // Check our nonce, if they don't match then bounce!
            if (!wp_verify_nonce($nonce, 'commercioo_required_plugin_installer_nonce'))
                wp_die(__('Error - unable to verify nonce, please try again.', 'commercioo'));


            // Include required libs for installation
            require_once(ABSPATH . 'wp-admin/includes/plugin-install.php');
            require_once(ABSPATH . 'wp-admin/includes/class-wp-upgrader.php');
            require_once(ABSPATH . 'wp-admin/includes/class-wp-ajax-upgrader-skin.php');
            require_once(ABSPATH . 'wp-admin/includes/class-plugin-upgrader.php');

            $skin_args =   array(
                'slug' => $plugin,
                'fields' => array(
                    'short_description' => false,
                    'sections' => false,
                    'requires' => false,
                    'rating' => false,
                    'ratings' => false,
                    'downloaded' => false,
                    'last_updated' => false,
                    'added' => false,
                    'tags' => false,
                    'compatibility' => false,
                    'homepage' => false,
                    'donate_link' => false,
                ),
            );
            // Get Plugin Info
            $api = plugins_api('plugin_information',
                $skin_args
            );
//            $main_plugin_file = self::get_plugin_file($plugin); // Get main plugin file
//            $skin = new \WP_Ajax_Upgrader_Skin();

            if("update"===$is_update){
                $url = add_query_arg(
                    array(
                        'action' => $is_update . '-plugin',
                        'plugin' => urlencode( $plugin ),
                    ),
                    'update.php'
                );
                $api           = ( 'repo' === $this->plugins[ $plugin ]['source_type'] ) ? $this->get_plugins_api( $plugin ) : null;
                $extra         = array();
                $extra['slug'] = $plugin; // Needed for potentially renaming of directory name.
                $title     = ('update' === $is_update ) ? $this->strings['updating'] : $this->strings['installing'];
                $skin_args = array(
                    'type'   => ( 'bundled' !== $this->plugins[ $plugin ]['source_type'] ) ? 'web' : 'upload',
                    'title'  => sprintf( $title, $this->plugins[ $plugin ]['name'] ),
                    'url'    => esc_url_raw( $url ),
                    'nonce'  => $is_update . '-plugin_' . $plugin,
                    'plugin' => '',
                    'api'    => $api,
                    'extra'  => $extra,
                );
                unset( $title );
                $skin_args['plugin'] = $this->plugins[ $plugin ]['file_path'];
                $skin                = new \WP_Ajax_Upgrader_Skin( $skin_args );
                $upgrader = new \Plugin_Upgrader($skin);
                // Inject our info into the update transient.
                $to_inject                    = array( $plugin => $this->plugins[ $plugin ] );
                $to_inject[ $plugin ]['source'] = $source;
                $this->inject_update_info( $to_inject );
                $upgrader->upgrade($this->plugins[ $plugin ]['file_path']);
            }else{
                $skin = new \Plugin_Installer_Skin( $skin_args );
                $upgrader = new \Plugin_Upgrader($skin);
                $upgrader->install($api->download_link);
            }

            if ($api->name) {
                $status = 'success';
                if("update"===$is_update){
                    $update_text = "updated";
                }else{
                    $update_text = "installed";
                }
                $msg = $api->name . " successfully $update_text.";
            } else {
                $status = 'failed';
                if("update"===$is_update){
                    $update_text = "updating";
                }else{
                    $update_text = "installing";
                }
                $msg = "There was an error $update_text " . $api->name . '.';
            }

            if($is_plugin_active){
                activate_plugin($main_plugin_file);
            }
            $json = array(
                'status' => $status,
                'button_class'=>$button_classes,
                'button_text'=>$button_text,
                'msg' => $msg,
            );
            wp_send_json($json);
        }

        /**
         * commercioo_required_plugin_activation
         * Activate plugin via Ajax.
         *
         * @return $json
         *
         * @since 1.0
         */
        public function commercioo_required_plugin_activation()
        {
            if (!current_user_can('install_plugins'))
                wp_die(__('Sorry, you are not allowed to activate plugins on this site.', 'commercioo'));

            $nonce = sanitize_text_field($_POST["nonce"]);
            $plugin_slug = sanitize_title(isset($_POST["plugin"])?$_POST["plugin"]:'');
            $msg ='';
            // Check our nonce, if they don't match then bounce!
            if (!wp_verify_nonce($nonce, 'commercioo_required_plugin_installer_nonce')){
                $status = 'failed';
                $msg = __('Error - unable to verify nonce, please try again.', 'commercioo');
                $json = array(
                    'status' => $status,
                    'msg' => $msg,
                );

                wp_send_json($json);
            }

            // Include required libs for activation
            require_once(ABSPATH . 'wp-admin/includes/plugin-install.php');
            require_once(ABSPATH . 'wp-admin/includes/class-wp-upgrader.php');
            require_once(ABSPATH . 'wp-admin/includes/class-plugin-upgrader.php');


            // Get Plugin Info
            $api = plugins_api('plugin_information',
                array(
                    'slug' => $plugin_slug,
                    'fields' => array(
                        'short_description' => false,
                        'sections' => false,
                        'requires' => false,
                        'rating' => false,
                        'ratings' => false,
                        'downloaded' => false,
                        'last_updated' => false,
                        'added' => false,
                        'tags' => false,
                        'compatibility' => false,
                        'homepage' => false,
                        'donate_link' => false,
                    ),
                )
            );


            if ($api->name) {
                $main_plugin_file = self::get_plugin_file($plugin_slug);
                $status = 'success';
                if ($main_plugin_file) {
                    activate_plugin($main_plugin_file);
                    $msg = $api->name . ' successfully activated.';
                }
            } else {
                $status = 'failed';
                $msg = 'There was an error activating ' . $api->name . '.';
            }

            $json = array(
                'status' => $status,
                'msg' => $msg,
            );

            wp_send_json($json);

        }

        /**
         * init
         * Initialize the display of the plugins.
         *
         * @since 1.0
         */
        public function init_display()
        {
            $plugins = apply_filters("commercioo/default/tgmpa",array());
            ?>

            <div class="commercioo-required-plugin-installer">
                <?php
                require_once(ABSPATH . 'wp-admin/includes/plugin-install.php');

                foreach ($plugins as $plugin) :

                    $button_classes = 'install button';
                    $button_text = __('Install Now', 'commercioo');

                    $api = plugins_api('plugin_information',
                        array(
                            'slug' => sanitize_file_name($plugin['slug']),
                            'fields' => array(
                                'short_description' => true,
                                'sections' => false,
                                'requires' => false,
                                'downloaded' => true,
                                'last_updated' => false,
                                'added' => false,
                                'tags' => false,
                                'compatibility' => false,
                                'homepage' => false,
                                'donate_link' => false,
                                'icons' => true,
                                'banners' => true,
                            ),
                        )
                    );

                    if (!is_wp_error($api)) { // confirm error free

                        $main_plugin_file = self::get_plugin_file($plugin['slug']); // Get main plugin file
                        //echo $main_plugin_file;
                        if (self::check_file_extension($main_plugin_file)) { // check file extension
                            if (is_plugin_active($main_plugin_file)) {
                                // plugin activation confirmed
                                $button_classes = 'button disabled';
                                $button_text = __('Activated', 'commercioo');
                            } else {
                                // It's installed, let's activate it
                                $button_classes = 'activate button button-primary';
                                $button_text = __('Activate', 'commercioo');
                            }
                        }

                        // Send plugin data to template
                        self::render_template($plugin, $api, $button_text, $button_classes);

                    }
                endforeach;
                ?>
            </div>
            <?php
        }
        /**
         * Check whether a plugin complies with the minimum version requirements.
         *
         * @since 2.5.0
         *
         * @param string $slug Plugin slug.
         * @return bool True when a plugin needs to be updated, otherwise false.
         */
        public function does_plugin_require_update( $slug ) {
            $installed_version = $this->get_installed_version( $slug );
            $minimum_version   = $this->plugins[ $slug ]['version'];

            return version_compare( $minimum_version, $installed_version, '>' );
        }
        /**
         * Retrieve the version number of an installed plugin.
         *
         * @since 2.5.0
         *
         * @param string $slug Plugin slug.
         * @return string Version number as string or an empty string if the plugin is not installed
         *                or version unknown (plugins which don't comply with the plugin header standard).
         */
        public function get_installed_version( $slug,$plugin_name='' ) {
            $installed_plugins = $this->get_plugins(); // Retrieve a list of all installed plugins (WP cached).
            if($this->is_plugin_installed($plugin_name)) {
                if (!empty($installed_plugins[$this->plugins[$slug]['file_path']]['Version'])) {
                    return $installed_plugins[$this->plugins[$slug]['file_path']]['Version'];
                }
            }

            return '';
        }
        /**
         * Check if a plugin is installed. Does not take must-use plugins into account.
         *
         * @since 2.5.0
         *
         * @param string $slug Plugin slug.
         * @return bool True if installed, false otherwise.
         */
        public function is_plugin_installed( $slug ) {

            if (!function_exists('get_plugins')) {
                require_once ABSPATH . 'wp-admin/includes/plugin.php';
            }
            $all_plugins = get_plugins();
            return !empty($all_plugins[$slug]);

        }
        /**
         * render_template
         * Render display template for each plugin.
         *
         *
         * @param $plugin            Array - Original data passed to init()
         * @param $api               Array - Results from plugins_api
         * @param $button_text       String - text for the button
         * @param $button_classes    String - classnames for the button
         *
         * @since 1.0
         */
        public  function render_template($plugin, $api, $button_text, $button_classes)
        {
            $status      = 'install';

            $url         = false;
            $is_update         = false;

            if ( 'install' === $status ) {
                if ( current_user_can( 'install_plugins' ) ) {
                    $url = wp_nonce_url( self_admin_url( 'update.php?action=install-plugin&plugin=' . esc_attr($api->slug)), 'install-plugin_' . $api->slug );
                }
            }
            add_thickbox();
            ?>
            <div class="plugin">
                <div class="plugin-wrap">
                    <img src="<?php echo esc_attr($api->icons['1x']); ?>" alt="">
                    <h2><?php echo esc_attr($api->name); ?></h2>
                    <p><?php echo esc_attr($api->short_description); ?></p>

                    <p class="plugin-author"><?php _e('By ', 'commercioo'); ?><?php echo wp_kses_post($api->author); ?></p>
                </div>
                <ul class="plugin-card activation-row">
                    <li>
                        <a class="<?php echo esc_attr($button_classes); ?>"
                           data-update="<?php echo ($is_update)?'update':'install'?>"
                           data-slug="<?php echo esc_attr($api->slug); ?>"
                           data-name="<?php echo esc_attr($api->name); ?>"
                           href="<?php echo esc_url($url);?>">
                            <?php echo esc_attr($button_text); ?>
                        </a>
                    </li>
                    <li>
                        <a href="<?php echo get_admin_url(); ?>plugin-install.php?tab=plugin-information&plugin=<?php echo esc_attr($api->slug); ?>&TB_iframe=true&width=772&height=612"
                           class="thickbox open-plugin-details-modal">
                            <?php _e('More Details', 'commercioo'); ?>
                        </a>
                    </li>
                </ul>
            </div>
            <?php
        }

        /**
         * check_file_extension
         * A helper to check file extension
         *
         *
         * @param $filename    String - The filename of the plugin
         * @return boolean
         *
         * @since 1.0
         */
        public static function check_file_extension($filename)
        {
            if (substr(strrchr($filename, '.'), 1) === 'php') {
                // has .php exension
                return true;
            } else {
                // ./wp-content/plugins
                return false;
            }
        }

        /**
         * get_plugin_file
         * A method to get the main plugin file.
         *
         *
         * @param  $plugin_slug    String - The slug of the plugin
         * @return $plugin_file
         *
         * @since 1.0
         */

        public static function get_plugin_file($plugin_slug)
        {
            require_once(ABSPATH . '/wp-admin/includes/plugin.php'); // Load plugin lib
            $plugins = get_plugins();

            foreach ($plugins as $plugin_file => $plugin_info) {

                // Get the basename of the plugin e.g. [askismet]/askismet.php
                $slug = dirname(plugin_basename($plugin_file));

                if ($slug) {
                    if ($slug == $plugin_slug) {
                        return $plugin_file; // If $slug = $plugin_name
                    }
                }
            }
            return null;
        }
        /**
         * Sanitizes a string key.
         *
         * Near duplicate of WP Core `sanitize_key()`. The difference is that uppercase characters *are*
         * allowed, so as not to break upgrade paths from non-standard bundled plugins using uppercase
         * characters in the plugin directory path/slug. Silly them.
         *
         * @see https://developer.wordpress.org/reference/hooks/sanitize_key/
         *
         * @since 2.5.0
         *
         * @param string $key String key.
         * @return string Sanitized key
         */
        public function sanitize_key( $key ) {
            $raw_key = $key;
            $key     = preg_replace( '`[^A-Za-z0-9_-]`', '', $key );

            /**
             * Filter a sanitized key string.
             *
             * @since 2.5.0
             *
             * @param string $key     Sanitized key.
             * @param string $raw_key The key prior to sanitization.
             */
            return apply_filters( 'tgmpa_sanitize_key', $key, $raw_key );
        }
        public static function register_tgmpa_source($plugins=array())
        {
            $plugins = apply_filters("commercioo/register/tgmpa",array(
                    array(
                        'name' => 'Post SMTP Mailer/Email Log',
                        'slug' => 'post-smtp',
                        'required' => false,
                    )
            ));
            return $plugins;
        }

        public function set_args(){
            /**
             * Array of plugin arrays. Required keys are name and slug.
             * If the source is NOT from the .org repo, then source is also required.
             */
            $plugins = apply_filters("commercioo/default/tgmpa",array());
            $defaults = array(
                'name'               => '',      // String
                'slug'               => '',      // String
                'source'             => 'repo',  // String
                'required'           => false,   // Boolean
                'version'            => '',      // String
                'force_activation'   => false,   // Boolean
                'force_deactivation' => false,   // Boolean
                'external_url'       => '',      // String
                'is_callable'        => '',      // String|Array.
            );
            $this->plugins = array();
            foreach ( $plugins as $data_plugin ) {
                $plugin = wp_parse_args( $data_plugin, $defaults );
                // Standardize the received slug.
                $plugin['slug'] = $this->sanitize_key( $plugin['slug'] );

                // Forgive users for using string versions of booleans or floats for version number.
                $plugin['version']            = (string) $plugin['version'];
                $plugin['source']             = empty( $plugin['source'] ) ? 'repo' : $plugin['source'];
                $plugin['required']           = \TGMPA_Utils::validate_bool( $plugin['required'] );
                $plugin['force_activation']   = \TGMPA_Utils::validate_bool( $plugin['force_activation'] );
                $plugin['force_deactivation'] = \TGMPA_Utils::validate_bool( $plugin['force_deactivation'] );

                // Enrich the received data.
                $plugin['file_path']   = self::_get_plugin_basename_from_slug( $plugin['slug'] );
                $plugin['source_type'] =self::get_plugin_source_type( $plugin['source'] );

                // Set the class properties.
                $this->plugins[ $plugin['slug'] ]    = $plugin;
//                $to_inject                    = array( $plugin => $this->plugins[ $plugin['slug'] ] );
//                $this->inject_update_info( $to_inject );
            }
//            install_plugin_information();
// Load class strings.
            $this->strings = array(
                'page_title'                      => __( 'Install Required Plugins', 'tgmpa' ),
                'menu_title'                      => __( 'Install Plugins', 'tgmpa' ),
                /* translators: %s: plugin name. */
                'installing'                      => __( 'Installing Plugin: %s', 'tgmpa' ),
                /* translators: %s: plugin name. */
                'updating'                        => __( 'Updating Plugin: %s', 'tgmpa' ),
                'oops'                            => __( 'Something went wrong with the plugin API.', 'tgmpa' ),
                'notice_can_install_required'     => _n_noop(
                /* translators: 1: plugin name(s). */
                    'This theme requires the following plugin: %1$s.',
                    'This theme requires the following plugins: %1$s.',
                    'tgmpa'
                ),
                'notice_can_install_recommended'  => _n_noop(
                /* translators: 1: plugin name(s). */
                    'This theme recommends the following plugin: %1$s.',
                    'This theme recommends the following plugins: %1$s.',
                    'tgmpa'
                ),
                'notice_ask_to_update'            => _n_noop(
                /* translators: 1: plugin name(s). */
                    'The following plugin needs to be updated to its latest version to ensure maximum compatibility with this theme: %1$s.',
                    'The following plugins need to be updated to their latest version to ensure maximum compatibility with this theme: %1$s.',
                    'tgmpa'
                ),
                'notice_ask_to_update_maybe'      => _n_noop(
                /* translators: 1: plugin name(s). */
                    'There is an update available for: %1$s.',
                    'There are updates available for the following plugins: %1$s.',
                    'tgmpa'
                ),
                'notice_can_activate_required'    => _n_noop(
                /* translators: 1: plugin name(s). */
                    'The following required plugin is currently inactive: %1$s.',
                    'The following required plugins are currently inactive: %1$s.',
                    'tgmpa'
                ),
                'notice_can_activate_recommended' => _n_noop(
                /* translators: 1: plugin name(s). */
                    'The following recommended plugin is currently inactive: %1$s.',
                    'The following recommended plugins are currently inactive: %1$s.',
                    'tgmpa'
                ),
                'install_link'                    => _n_noop(
                    'Begin installing plugin',
                    'Begin installing plugins',
                    'tgmpa'
                ),
                'update_link'                     => _n_noop(
                    'Begin updating plugin',
                    'Begin updating plugins',
                    'tgmpa'
                ),
                'activate_link'                   => _n_noop(
                    'Begin activating plugin',
                    'Begin activating plugins',
                    'tgmpa'
                ),
                'return'                          => __( 'Return to Required Plugins Installer', 'tgmpa' ),
                'dashboard'                       => __( 'Return to the Dashboard', 'tgmpa' ),
                'plugin_activated'                => __( 'Plugin activated successfully.', 'tgmpa' ),
                'activated_successfully'          => __( 'The following plugin was activated successfully:', 'tgmpa' ),
                /* translators: 1: plugin name. */
                'plugin_already_active'           => __( 'No action taken. Plugin %1$s was already active.', 'tgmpa' ),
                /* translators: 1: plugin name. */
                'plugin_needs_higher_version'     => __( 'Plugin not activated. A higher version of %s is needed for this theme. Please update the plugin.', 'tgmpa' ),
                /* translators: 1: dashboard link. */
                'complete'                        => __( 'All plugins installed and activated successfully. %1$s', 'tgmpa' ),
                'dismiss'                         => __( 'Dismiss this notice', 'tgmpa' ),
                'notice_cannot_install_activate'  => __( 'There are one or more required or recommended plugins to install, update or activate.', 'tgmpa' ),
                'contact_admin'                   => __( 'Please contact the administrator of this site for help.', 'tgmpa' ),
            );

            do_action( 'tgmpa_register' );
            return $this->plugins;
        }
        /**
         * Register required plugins
         *
         * @since    0.2.3
         */
        public function register_recommended_plugins()
        {
            /**
             * Array of plugin arrays. Required keys are name and slug.
             * If the source is NOT from the .org repo, then source is also required.
             */
            $plugins = apply_filters("commercioo/default/tgmpa",array());

            /**
             * Array of configuration settings. Amend each line as needed.
             *
             * TGMPA will start providing localized text strings soon. If you already have translations of our standard
             * strings available, please help us make TGMPA even better by giving us access to these translations or by
             * sending in a pull-request with .po file(s) with the translations.
             *
             * Only uncomment the strings in the config array if you want to customize the strings.
             */
            $config = array(
                'id' => 'commercioo_page_comm_required_plugin',            // Unique ID for hashing notices for multiple instances of TGMPA.
//			'id'           => 'commercioo',            // Unique ID for hashing notices for multiple instances of TGMPA.
                'default_path' => '',                      // Default absolute path to bundled plugins.
                'menu' => 'comm_required_plugin', // Menu slug.
//                'menu'         => 'tgmpa-install-plugins', // Menu slug.
                'parent_slug' => '',           // Parent menu slug.
//            'parent_slug'  => 'comm-system-status',           // Parent menu slug.
//                'parent_slug'  => 'plugins.php',           // Parent menu slug.
                'capability' => 'manage_options',        // Capability needed to view plugin install page, should be a capability associated with the parent menu used.
                'has_notices' => true,                    // Show admin notices or not.
                'dismissable' => true,                    // If false, a user cannot dismiss the nag message.
                'dismiss_msg' => '',                      // If 'dismissable' is false, this message will be output at top of nag.
                'is_automatic' => false,                   // Automatically activate plugins after installation or not.
                'message' => '',                      // Message to output right before the plugins table.
                'strings' => array(
                    'page_title' => __('Install Required Plugins', 'commercioo'),
                    'menu_title' => __('Install Plugins', 'commercioo'),
                    'installing' => __('Installing Plugin: %s', 'commercioo'),
                    'updating' => __('Updating Plugin: %s', 'commercioo'),
                    'oops' => __('Something went wrong with the plugin API.', 'commercioo'),
                    'notice_can_install_required' => _n_noop(
                        'Commercioo requires the following plugin: %1$s.',
                        'Commercioo requires the following plugins: %1$s.',
                        'commercioo'
                    ),
                    'notice_can_install_recommended' => _n_noop(
                        'Commercioo recommends the following plugin: %1$s.',
                        'Commercioo recommends the following plugins: %1$s.',
                        'commercioo'
                    ),
                    'notice_ask_to_update' => _n_noop(
                        'The following plugin needs to be updated to its latest version to ensure maximum compatibility with Commercioo: %1$s.',
                        'The following plugins need to be updated to their latest version to ensure maximum compatibility with Commercioo: %1$s.',
                        'commercioo'
                    ),
                ),
            );

            tgmpa($plugins, $config);

            // Prepare the received data.

        }
        /**
         * Determine what type of source the plugin comes from.
         *
         * @since 2.5.0
         *
         * @param string $source The source of the plugin as provided, either empty (= WP repo), a file path
         *                       (= bundled) or an external URL.
         * @return string 'repo', 'external', or 'bundled'
         */
        protected function get_plugin_source_type( $source ) {
            if ( 'repo' === $source || preg_match( $this::WP_REPO_REGEX, $source ) ) {
                return 'repo';
            } elseif ( preg_match( $this::IS_URL_REGEX, $source ) ) {
                return 'external';
            } else {
                return 'bundled';
            }
        }
        /**
         * Helper function to extract the file path of the plugin file from the
         * plugin slug, if the plugin is installed.
         *
         * @since 2.0.0
         *
         * @param string $slug Plugin slug (typically folder name) as provided by the developer.
         * @return string Either file path for plugin if installed, or just the plugin slug.
         */
        protected function _get_plugin_basename_from_slug( $slug ) {
            $keys = array_keys( $this->get_plugins() );

            foreach ( $keys as $key ) {
                if ( preg_match( '|^' . $slug . '/|', $key ) ) {
                    return $key;
                }
            }

            return $slug;
        }
        /**
         * Wrapper around the core WP get_plugins function, making sure it's actually available.
         *
         * @since 2.5.0
         *
         * @param string $plugin_folder Optional. Relative path to single plugin folder.
         * @return array Array of installed plugins with plugin information.
         */
        public function get_plugins( $plugin_folder = '' ) {
            if ( ! function_exists( 'get_plugins' ) ) {
                require_once ABSPATH . 'wp-admin/includes/plugin.php';
            }

            return get_plugins( $plugin_folder );
        }
        /**
         * Register system status page on admin
         * Will be located under tools.php menu
         *
         * @since    0.2.5
         */
        public function register_admin_menu_page()
        {
            // Make sure privileges are correct to see the page.
            if (current_user_can("administrator")) {
                // Commercioo tools submenu
                add_submenu_page(
                    'comm-system-status',
                    __('Recommended Plugins', 'commercioo'),
                    __('Recommended Plugins', 'commercioo'),
                    'manage_commercioo',
                    'comm_required_plugin',
                    array($this, 'comm_required_plugin_callback'),
                    5
                );
            }
        }


        /**
         * Load the commercioo agency page
         *
         * @since    1.0.0
         */
        public function comm_required_plugin_callback()
        {
            $TGM_Plugin_Activation = __CLASS__;
            include_once COMMERCIOO_PATH . 'admin/required_plugin/commercioo-required_plugin-admin-display.php';
        }


        /**
         * Register the stylesheets for the admin area.
         *
         * @since    1.0.0
         */
        public function enqueue_styles()
        {
            $screen = get_current_screen();
            if ('commercioo_page_comm_required_plugin' === $screen->id) {
                wp_enqueue_style('required-plugin-page', COMMERCIOO_URL . 'admin/required_plugin/required-plugin.css', array(), COMMERCIOO_VERSION, 'all');
            }
        }

        /**
         * Register the JavaScript for the admin area.
         *
         * @since    1.0.0
         */
        public function enqueue_scripts()
        {
            $screen = get_current_screen();

            /**
             * load script only on Agency Settings page
             */
            if ('commercioo_page_comm_required_plugin' === $screen->id) {
                wp_register_script('commercioo_required_plugin', COMMERCIOO_URL . 'admin/required_plugin/installer.js', array('jquery'), NULL, true); //DASH
                wp_enqueue_script('commercioo_required_plugin');

                wp_localize_script('commercioo_required_plugin', 'commercioo_required_plugin_ajax_obj',
                    array(
                        'ajax_url' => admin_url('admin-ajax.php'),
                        'admin_nonce' => wp_create_nonce('commercioo_required_plugin_installer_nonce'),
                        'install_now' => __('Are you sure you want to install this plugin?', 'commercioo'),
                        'update_now' => __('Are you sure you want to update this plugin?', 'commercioo'),
                        'install_btn' => __('Install Now', 'commercioo'),
                        'activate_btn' => __('Activate', 'commercioo'),
                        'installed_btn' => __('Activated', 'commercioo'),
                        'please_wait_btn' => __('Please wait...', 'commercioo')
                    )
                );
            }
        }
    }

    new \Commercioo\Admin\Required_Plugin_Page();
}