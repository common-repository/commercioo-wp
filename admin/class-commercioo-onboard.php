<?php
/**
 * Class for handling user onboarding
 */
class Commercioo_Onboard extends \Commercioo\Admin\License_Page
{

//    public $store_url;

    /**
     * Construtor
     */
    public function __construct()
    {
        /**
         * On multisite, loading this file will cause network-activation error
         * The possibility is because multisite have its own version of `pluggable.php`
         * So we need to disable this on multisite mode
         */
//        if ( ! is_multisite() ) {
//            require_once ABSPATH . 'wp-includes/pluggable.php';
//        }

        parent::__construct();
    }


    /**
     * Maybe redirect on user onbarding after plugin activation
     */
    public function maybe_redirect_to_onboarding()
    {
        if (false !== get_transient('comm_onboarding')) {
            delete_transient('comm_onboarding');
            wp_safe_redirect(admin_url('admin.php?page=comm_onboard'));
            exit;
        }
    }

    /**
     * Register onboarding page (withput admin menu)
     */
    public function register_menu() {
		add_submenu_page(
			'comm-system-status',
			__('Onboarding', 'commercioo'),
			__('Onboarding', 'commercioo'),
			'manage_options',
			'comm_onboard',
			array( $this, 'render_onboarding' ),
			19
		);
        // add_submenu_page(NULL, __('Commercioo Onboard', 'commercioo'), __('Commercioo Onboard', 'commercioo'), 'manage_options', 'comm_onboard', array($this, 'render_onboarding'));
    }

    /**
     * Render onboarding page
     */
    public function render_onboarding()
    {
        require_once ABSPATH . 'wp-admin/includes/plugin-install.php';
        $email = get_option('comm_onboard_email', '');
        $pass = get_option('comm_onboard_password', '');
        $token = get_option('comm_onboard_token', '');
        include_once COMMERCIOO_PATH . 'admin/partials/comm_onboard.php';
    }

    /**
     * Enqueue style and scripts
     *
     * @param  string $suffix Current admin page.
     */
    public function enqueue_scripts($suffix) {
        if ( "admin_page_comm_onboard" === $suffix || "commercioo_page_comm_onboard" === $suffix) {
            wp_enqueue_style('font-awesome', COMMERCIOO_URL . 'admin/css/vendor/font-awesome/font-awesome.min.css', array(), COMMERCIOO_VERSION, 'all');
            wp_enqueue_style('commercioo-onboard', COMMERCIOO_URL . 'admin/css/commercioo-onboard.css', array(), COMMERCIOO_VERSION, 'all');
            wp_enqueue_script('commercioo-onboard', COMMERCIOO_URL . 'admin/js/commercioo-onboard.js', array(), COMMERCIOO_VERSION);

            wp_localize_script(
                'commercioo-onboard', 'comm_onboard', array(
                    'nonce_set_email' => wp_create_nonce('set_email'),
                    'nonce_check_license' => wp_create_nonce('check_license'),
                    'site_url' => $this->get_url(),
                    'store_url' => $this->store_url,
                )
            );
        }
    }


    public function allow_custom_host($allow, $host, $url)
    {
        if ($host === str_replace(array('http://', 'https://'), '', $this->store_url)) {
            $allow = true;
        }
        if ('premiumwpplugins.s3.amazonaws.com' === $host || 'amazonaws.com' === $host) {
            $allow = true;
        }
        return $allow;
    }

    public function check_account()
    {
        check_ajax_referer('set_email', 'comm_action');
        $email = get_option('comm_onboard_email', '');
        $pass = get_option('comm_onboard_password', '');
        if (!empty($email) && !empty($pass)) {
            wp_send_json(array(
                'status' => true,
                'email' => $email,
                'pass' => $pass
            ));
        } else {
            wp_send_json(array(
                'status' => false,
            ));
        }
    }

    /**
     * AJAX set email
     */
    public function set_email()
    {
        check_ajax_referer('set_email', 'comm_action');

        $email = sanitize_email(isset($_POST['email']) ? wp_unslash($_POST['email']) : ''); // Input var okay.
        $password = sanitize_text_field(isset($_POST['password']) ? wp_unslash($_POST['password']) : ''); // Input var okay.
        $sendDatas = sanitize_post(isset($_POST['sendData_plugin']) ? $_POST['sendData_plugin'] : '');
        // Input var okay.

        if (!empty($sendDatas)) {
         // save email.
            update_option('comm_onboard_email', $email);
            update_option('comm_onboard_password', $password);

            delete_option('comm_onboard_all_items');
            delete_option('comm_onboard_items');
            delete_option('comm_onboard_main_items');
            delete_option('comm_onboard_token');

            $body = (object)$sendDatas;
            $data_all = array();
            $data = array();
            $data_m = array();
            $data_main = array();

            foreach ($body->data as $k => $dt) {
                $installed = false;
                foreach ($dt['files'] as $val){
                    if ($val['name'] !=='' && 'Commercioo' !== $dt['name']) {
                        $data[$k] = $dt;
                        $data_all [$k]=$dt;
                    }
                }

                $files = $dt['files'];
                $type = $dt['category'];
                foreach ($files as $fp) {
                    $name = $fp['name'];
                    $plugin_name = $name . '/' . $name . '.php';
                    if ('plugin' === $type) {
                        if ($this->is_plugin_installed($plugin_name)) {
                            $installed = true;
                        }
                    } elseif ('theme' === $type) {
                        if ($this->is_theme_installed($name)) {
                            $installed = true;
                        }
                    }
                }
            }

            if ($body->status) {
                update_option('comm_onboard_all_items', $data_all);
                update_option('comm_onboard_items', $data);
                update_option('comm_onboard_main_items', $data_main);
                update_option('comm_onboard_token', $body->token);
                wp_send_json(array(
                    'status' => true,
                    'data' => !empty($data_all) ? $data_all : false,
                    'data_main' => !empty($data_m) ? $data_m : false,
                    'data_main_plugin' => !empty($data_main) ? $data_main : false,
                    'email' => $email,
                    'token' => $body->token,
                    'avatar' => get_avatar_url($email, array(
                        'size' => 46
                    ))
                ));
            } else {
                wp_send_json(array(
                    'status' => false,
                    'message' => $body->message
                ));
            }
        }
    }

    /**
     * AJAX check license
     */
    public function check_license()
    {
        check_ajax_referer('check_license', 'comm_action');

        $licenses =  array(); // Input var okay.
        if(isset($_POST['licenses'])){
            $licenses = sanitize_text_field(wp_unslash($_POST['licenses']));
        }
        $url = $this->get_url();

        if (empty($licenses)) {
            wp_send_json(array(
                'status' => false,
                'message' => 'Licenses can not empty'
            ));
        }

        // check licenses.
        $license_errors = array();
        foreach ($licenses as $item_id => $license) {
            $response = wp_remote_get(add_query_arg(array(
                'edd_action' => 'check_license',
                'item_id' => $item_id,
                'license' => $license,
                'url' => $url
            ), $this->store_url));
            if (is_wp_error($response)) {
                wp_send_json(array(
                    'status' => false,
                    'message' => $response->get_error_message()
                ));
            }

            if (200 == ($response_code = wp_remote_retrieve_response_code($response))) {
                $body = wp_remote_retrieve_body($response);
                $body = json_decode($body);

                if ($body->success) {
                    if ('valid' !== $body->license && 'inactive' !== $body->license) {
                        $license_errors[] = (isset($body->item_name) ? $body->item_name : $item_id);
                    }
                } else {
                    $license_errors[] = (isset($body->item_name) ? $body->item_name : $item_id);
                }
            } else {
                wp_send_json(array(
                    'status' => false,
                    'message' => 'Error ' . $response_code
                ));
            }
        }

        if (!empty($license_errors)) {
            wp_send_json(array(
                'status' => false,
                'message' => 'License for ' . implode(', ', $license_errors) . ' is invalid.'
            ));
        }

        wp_send_json(array(
            'status' => true,
            'data' => array_keys($licenses)
        ));
    }

    /**
     * AJAX install plugin/theme
     */
    public function do_install()
    {
        $dataPlugin = get_option('comm_onboard_items', true);
        $ids_plugin = sanitize_text_field(isset($_POST['ids_plugin']) ? $_POST['ids_plugin'] : ''); // Input var okay.
        $filename = '';
        $name = '';
        if (empty($ids_plugin)) {
            wp_send_json(array(
                'status' => false,
                'message' => 'invalid id plugin'
            ));
        }

        if (!empty($ids_plugin)) {
            foreach ($dataPlugin as $k => $vp) {
                if ($k == $ids_plugin) {
                    $files = $vp['files'];
                    $type = $vp['category'];
                    foreach ($files as $fp) {
                        $filename = $fp['file'];
                        $name = $fp['name'];
                        if (empty($name) || empty($filename)) {
                            wp_send_json(array(
                                'status' => false,
                                'message' => 'Name or file url can not empty'
                            ));
                        }
                        $plugin_name = $name . '/' . $name . '.php';

                        $installed = false;
                        if ('plugin' === $type) {
                            if ($this->is_plugin_installed($plugin_name)) {
                                $installed = true;
                            } else {
                                $installed = $this->install_plugin($filename, $name);
                            }
                        } elseif ('theme' === $type) {
                            if ($this->is_theme_installed($name)) {
                                $installed = true;
                            } else {
                                $installed = $this->install_theme($filename, $name);
                            }
                        }

                        if (is_wp_error($installed)) {
                            wp_send_json(array(
                                'status' => false,
                                'item_id' => $ids_plugin,
                                'message' => 'Can not install the plugin'
                            ));
                        }

                        if (!$installed) {
                            wp_send_json(array(
                                'status' => false,
                                'item_id' => $ids_plugin,
                                'message' => 'Can not install the plugin'
                            ));
                        }
                    }
                }
            }
        }
        wp_send_json(array(
            'status' => true,
            'filename' => $filename,
            'name' => $name,
            'item_id' => $ids_plugin,
        ));
    }

    /**
     * AJAX install plugin/theme
     */
    public function do_activate()
    {
        $types = sanitize_text_field(isset($_POST['types']) ? $_POST['types'] : ''); // Input var okay.

        $dataPlugin = get_option('comm_onboard_items', true);
        $ids_plugin = sanitize_text_field(isset($_POST['item_id']) ? $_POST['item_id'] : ''); // Input var okay.
        $response = sanitize_post(isset($_POST['response']) ? $_POST['response'] : ''); // Input var okay.
        $data = array();
        $result='';

        // Include required libs for activation
        require_once( ABSPATH . 'wp-admin/includes/plugin-install.php' );
        require_once( ABSPATH . 'wp-admin/includes/class-wp-upgrader.php' );
        require_once( ABSPATH . 'wp-admin/includes/class-plugin-upgrader.php' );

        if (!empty($ids_plugin)) {
            foreach ($dataPlugin as $k => $vp) {
                if ($k == $ids_plugin) {
                    $data[$k] = $vp;
                }
            }
            if (array_key_exists($ids_plugin, $data)) {
                $result = $data[$ids_plugin];
                $type = $result['category'];

                $product = $result['name'];
                $files = $result['files'];
                $license = $result['license'];
                $name = $files[0]['name'];
                if($types=="second") {
                    if (empty($name)) {
                        wp_send_json(array(
                            'status' => false,
                            'message' => 'Name or file url can not empty'
                        ));
                    }
                    $plugin_name = $name . '/' . $name . '.php';
                    if ('plugin' === $type) {
                        if ($this->is_plugin_installed($plugin_name)) {
                            activate_plugin($plugin_name);
                        }
                    } elseif ('theme' === $type) {
                        if ($this->is_theme_installed($name)) {
                            switch_theme($name);
                        }
                    }
                }
                $success = $response['success'];
                if (!$success) {
                    if (isset($response['error'])) {
                        if ("missing" === $response['error']) {
                            $message = "License for " . $product . " doesn't exist";
                        } elseif ("missing_url" === $response['error']) {
                            $message = "URL not provided";
                        } elseif ("license_not_activable" === $response['error']) {
                            $message = "Attempting to activate a bundle's parent license";
                        } elseif ("disabled" === $response['error']) {
                            $message = "License key for " . $product . " revoked";
                        } elseif ("no_activations_left" === $response['error']) {
                            $message = "No activations left for " . $product;
                        } elseif ("expired" === $response['error']) {
                            $message = "License for " . $product . " has expired";
                        } elseif ("key_mismatch" === $response['error']) {
                            $message = "License for " . $product . " is not valid\\";
                        } elseif ("invalid_item_id" === $response['error']) {
                            $message = "Invalid Item ID for " . $product;
                        } else {
                            $message = "License for " . $product . " is not valid";
                        }
                    } else {
                        $message = 'Failed to activate ' . $product;
                    }

                    update_option($name . '_license_status', "invalid");
                    update_option($name . '-' . $type . '_license_status', "invalid");

                    wp_send_json(array(
                        'status' => false,
                        'message' => $message
                    ));
                } else {
                    if ('plugin' === $type) {
                        update_option($name . '-' . $type . '_license_status', $response['license']);
                        update_option($name . '-' . $type . '_license_expire', $response['expires']);
                        update_option($name . '-' . $type . '_activations_left', $response['activations_left']);
                        update_option($name . '-' . $type . '_license_limit', $response['license_limit']);
                        update_option($name . '-' . $type . '_site_count', $response['site_count']);
                        update_option($name . '-' . $type . '_license_key', $license);
                    } else {
                        update_option($name . '-slug_license_key_status', $response['license']);
                        update_option($name . '-slug_license_expire', $response['expires']);
                        update_option($name . '-slug_license_key', $license);
                    }
                }
            }

            wp_send_json(array(
                'status' => true
            ));
        }else{
            $message = 'Failed to activate';
            wp_send_json(array(
                'status' => false,
                'message' => $message
            ));
        }
    }

    /**
     * Check if plugins is already installed.
     *
     * @param  string $plugin_name Plugin slug.
     * @return boolean              Installation status.
     */
    public function is_plugin_installed($plugin_name)
    {
        require_once ABSPATH . 'wp-admin/includes/plugin.php';
        $all_plugins = get_plugins();
        return !empty($all_plugins[$plugin_name]);
    }

    /**
     * Check if theme is already installed.
     *
     * @param  string $name Theme slug.
     * @return boolean       Installation status.
     */
    public function is_theme_installed($name)
    {
        require_once ABSPATH . 'wp-admin/includes/theme.php';
        $themes = wp_get_themes();
        return isset($themes[$name]);
    }

    /**
     * Do install plugin
     *
     * @param  string $file Plugin ZIP File URL.
     * @return boolean       Install status.
     */
    public function install_plugin($file, $slug)
    {
        $defaults = array(
            'overwrite_package' => true,
        );
        $plugin_information = array(
            'slug' => $slug,
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
            )
        );
        $args = wp_parse_args($plugin_information, $defaults);

        // Include required libs for installation
        require_once( ABSPATH . 'wp-admin/includes/plugin-install.php' );
        require_once( ABSPATH . 'wp-admin/includes/class-wp-upgrader.php' );
        require_once( ABSPATH . 'wp-admin/includes/class-wp-ajax-upgrader-skin.php' );
        require_once( ABSPATH . 'wp-admin/includes/class-plugin-upgrader.php' );

        $api = plugins_api(
            'plugin_information',
            $args
        );
        $skin     = new WP_Ajax_Upgrader_Skin(array('api' => $api));
        $upgrader = new Plugin_Upgrader( $skin );
        $installed = $upgrader->install($file);
        return $installed;
    }

    /**
     * Do install theme
     *
     * @param  string $file Theme ZIP File URL.
     * @return boolean       Install status.
     */
    public function install_theme($file, $slug)
    {
        require_once ABSPATH . 'wp-admin/includes/theme-install.php';
        require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
        require_once( ABSPATH . 'wp-admin/includes/class-wp-ajax-upgrader-skin.php' );

        $api = themes_api(
            'theme_information',
            array(
                'slug' => $slug,
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
        $skin = new WP_Ajax_Upgrader_Skin(array('api' => $api));

        $upgrader = new Theme_Upgrader($skin);
        // $upgrader = new Theme_Upgrader();
        $installed = $upgrader->install($file);

        return $installed;
    }
    public function install_themeX($file, $slug)
    {
        require_once ABSPATH . 'wp-admin/includes/theme-install.php';
        include_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';

        $api = themes_api(
            'theme_information',
            array(
                'slug'   => $slug,
                'fields' => array(
                    'sections' => false,
                    'downloaded' => false,
                    'downloadlink' => $file,
                ),
            )
        );

        if ( is_wp_error( $api ) ) {
            $status['errorMessage'] = $api->get_error_message();
            wp_send_json_error( $status );
        }

        $skin     = new WP_Ajax_Upgrader_Skin();
        $upgrader = new Theme_Upgrader( $skin );
        $result   = $upgrader->install( $api->download_link );

        if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
            $status['debug'] = $skin->get_upgrade_messages();
        }

        if ( is_wp_error( $result ) ) {
            $status['errorCode']    = $result->get_error_code();
            $status['errorMessage'] = $result->get_error_message();
            wp_send_json_error( $status );
        } elseif ( is_wp_error( $skin->result ) ) {
            $status['errorCode']    = $skin->result->get_error_code();
            $status['errorMessage'] = $skin->result->get_error_message();
            wp_send_json_error( $status );
        } elseif ( $skin->get_errors()->has_errors() ) {
            $status['errorMessage'] = $skin->get_error_messages();
            wp_send_json_error( $status );
        } elseif ( is_null( $result ) ) {
            global $wp_filesystem;

            $status['errorCode']    = 'unable_to_connect_to_filesystem';
            $status['errorMessage'] = __( 'Unable to connect to the filesystem. Please confirm your credentials.' );

            // Pass through the error from WP_Filesystem if one was raised.
            if ( $wp_filesystem instanceof WP_Filesystem_Base && is_wp_error( $wp_filesystem->errors ) && $wp_filesystem->errors->has_errors() ) {
                $status['errorMessage'] = esc_html( $wp_filesystem->errors->get_error_message() );
            }

            wp_send_json_error( $status );
        }
    }

    /**
     * Get current web base URL
     *
     * @return string URL.
     */
    public function get_url()
    {
        preg_match_all('#^.+?[^\/:](?=[?\/]|$)#', get_site_url(), $matches);
        // return $matches[0][0];
        return home_url();
    }

}
$GLOBALS['commercioo-onboard'] = new Commercioo_Onboard();