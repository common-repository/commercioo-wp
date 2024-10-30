<?php

namespace Commercioo\Admin;
if (!class_exists("Commercioo\Admin\License_Page")) {
    class License_Page
    {
        public $store_url = 'https://commercioo.com';
        public $is_mode = false;
        public function __construct()
        {
            $this->store_url = ($this->is_mode) ? 'http://wp_multisite.test' :'https://commercioo.com';
        }
        public function ctp_action_requirement()
        {
            $text_check = '<b>Requirement as follows:</b>';
            $text_check .= '<ul class="sr-check">';
            if (class_exists("ZipArchive")):
                $text_check .= '<li class="checkist">';
                $text_check .= '<img src="' . COMMERCIOO_URL . 'admin/license/img/installed/checklist.png' . '">';
                $text_check .= 'ZipArchive is <b>(OK)</b>';
                $text_check .= '</li>';
            else:
                $text_check .= '<li class="cross">';
                $text_check .= '<img src="' . COMMERCIOO_URL . 'admin/license/img/installed/cross.png' . '">';
                $text_check .= 'ZipArchive
            has not been installed on your web server. Please contact your hosting provider for
            installed it';
                $text_check .= '</li>';
            endif;
            $hostname = $_SERVER['SERVER_NAME'];
            $ip_hostname = $_SERVER['SERVER_ADDR'];
            $dnsRecords = checkdnsrr($hostname);
            if ($dnsRecords == TRUE):
                $text_check .= '<li class="checkist">';
                $text_check .= '<img src="' . COMMERCIOO_URL . 'admin/license/img/installed/checklist.png' . '">';
                $text_check .= 'Your DNS web server with Domain ' . $hostname . ' is <b>(OK)</b>';
                $text_check .= '</li>';
            else:
                $text_check .= '<li class="cross">';
                $text_check .= '<img src="' . COMMERCIOO_URL . 'admin/license/img/installed/cross.png' . '">';
                $text_check .= 'Your DNS Server with Domain ' . $hostname . ' and IP Address ' . $ip_hostname . ' has been failed. Please contact your hosting provider for change your DNS web server.';
                $text_check .= '</li>';
            endif;
            if (in_array('curl', get_loaded_extensions())) :
                $text_check .= '<li class="checkist">';
                $text_check .= '<img src="' . COMMERCIOO_URL . 'admin/license/img/installed/checklist.png' . '">';
                $text_check .= 'CURL is available on your web server is <b>(OK)</b>';
                $text_check .= '</li>';
            else:
                $text_check .= '<li class="cross">';
                $text_check .= '<img src="' . COMMERCIOO_URL . 'admin/license/img/installed/cross.png' . '">';
                $text_check .= 'CURL has not been installed or has not been enabled on your web server. Please contact your hosting provider to install or enabled it.';
                $text_check .= '</li>';
            endif;
            $text_check .= '</ul>';
            echo wp_kses_post($text_check);
            wp_die();
        }
        public function license_page_do_license()
        {
            $response = "";
            $cmm_error_msg = "";
            $data = sanitize_post($_POST['data']);

            $ctp_event = sanitize_text_field(isset($_POST['ctp_event'])?$_POST['ctp_event']:'');
            $ctp_license_key = sanitize_text_field(isset($_POST['ctp_license_key'])?$_POST['ctp_license_key']:null);
            $link = admin_url("admin.php?page=comm-license");
            $admin_slug_plugin = sanitize_text_field(isset($_POST['data_slug'])?$_POST['data_slug']:null);

            if($admin_slug_plugin) {
                switch ($ctp_event) {
                    case "activated":
                        if (isset($data['expires'])) {
                            update_option($admin_slug_plugin . '_license_expire', $data['expires']);
                        }
                        if (isset($data['license_limit'])) {
                            update_option($admin_slug_plugin . '_license_limit', $data['license_limit']);
                        }
                        if (isset($data['site_count'])) {
                            update_option($admin_slug_plugin . '_site_count', $data['site_count']);
                        }
                        if (isset($data['activations_left'])) {
                            update_option($admin_slug_plugin . '_activations_left', $data['activations_left']);
                        }
                        if (isset($data['license'])) {
                            update_option($admin_slug_plugin . '_license_status', $data['license']);
                        }

                        update_option($admin_slug_plugin . '_license_key', $ctp_license_key);
                        break;

                    default:
                        if (isset($data['expires'])) {
                            update_option($admin_slug_plugin . '_license_expire', $data['expires']);
                        }
                        if (isset($data['license_limit'])) {
                            update_option($admin_slug_plugin . '_license_limit', $data['license_limit']);
                        }
                        if (isset($data['site_count'])) {
                            update_option($admin_slug_plugin . '_site_count', $data['site_count']);
                        }
                        if (isset($data['activations_left'])) {
                            update_option($admin_slug_plugin . '_activations_left', $data['activations_left']);
                        }
                        if (isset($data['license'])) {
                            update_option($admin_slug_plugin . '_license_status', $data['license']);
                        }

                        break;
                }
                    $expires = get_option($admin_slug_plugin . '_license_expire');
                    $license_limit = get_option($admin_slug_plugin . '_license_limit');
                    $site_count = get_option($admin_slug_plugin . '_site_count');
                    $activations_left = get_option($admin_slug_plugin . '_activations_left');
                    $license_status = get_option($admin_slug_plugin . '_license_status');

                    switch ($license_status) {
                        case "expired":
                            $cmm_error_msg = 'Your License key has been expired at ' . $expires . '. Please contact us for Renew License Key';
                            $is_valid = false;
                            break;
                        case "inactive":
                            $cmm_error_msg = 'License key Inactive';
                            $is_valid = true;
                            break;
                        case "valid":
                            $cmm_error_msg = 'License key valid';
                            $is_valid = true;
                            break;
                        case "deactivated":
                            $cmm_error_msg = 'License key has been deactivated';
                            $is_valid = true;
                            break;
                        case 'no_activations_left':
                            $cmm_error_msg = __('Your license key has reached its activation limit.');
                            $is_valid = false;
                            break;
                        case 'site_inactive' :
                            if (is_numeric($activations_left)) {
                                if ($activations_left == 0) {
                                    $cmm_error_msg = __('Your license key has reached its activation limit.');
                                } else {
                                    $cmm_error_msg = __('Your license is not active for this Website.');
                                }
                            } else {
                                $cmm_error_msg = __('Your license is not active for this Website.');
                            }
                            $is_valid = true;
                            break;
                        case 'item_name_mismatch' :
                            $cmm_error_msg = sprintf(__('This appears to be an invalid license key for %s.'), $this->get_var('item_name'));
                            $is_valid = false;
                            break;
                        default:
                            $cmm_error_msg = 'License key do not match / Invalid';
                            $is_valid = false;
                            break;
                    }
                    if (is_numeric($activations_left)) {
                        if ($activations_left == 0) {
                            $cmm_error_msg = __('Your license key has reached its activation limit.');
                        }
                    }

                    $html = '<span style="font-style:italic">' . $cmm_error_msg . '</span>';
                    $html .= '<div class="comm-clear"></div>';
                    if (is_numeric($activations_left)) {
                        $html .= '<span style="font-style:italic"> You have ' . $site_count . ' / ' . $license_limit . ' sites activated.</span>';

                    } else {
                        $html .= '<span style="font-style:italic"> You have ' . $site_count . " / " . $activations_left . ' sites activated.</span>';

                    }
                    $response = json_encode(array("is_valid"=>$is_valid,"pesan" => $cmm_error_msg, 'links' => $link, 'data_view_license' => $html));
            }
            echo wp_kses_data($response);
            wp_die();
        }
        public function ctp_action_check_license()
        {
            if (isset($_POST)) {
                check_ajax_referer('license_edd', 'nonce_license_edd');
                $admin_slug_plugin = sanitize_text_field(isset($_POST['data_slug'])?$_POST['data_slug']:null);
                $data = sanitize_post($_POST['data']);
                $html = '';
                $is_valid = true;
                if($admin_slug_plugin) {
                    if (isset($data['expires'])) {
                        update_option($admin_slug_plugin . '_license_expire', $data['expires']);
                    }
                    if (isset($data['license_limit'])) {
                        update_option($admin_slug_plugin . '_license_limit', $data['license_limit']);
                    }
                    if (isset($data['site_count'])) {
                        update_option($admin_slug_plugin . '_site_count', $data['site_count']);
                    }
                    if (isset($data['activations_left'])) {
                        update_option($admin_slug_plugin . '_activations_left', $data['activations_left']);
                    }
                    if (isset($data['license'])) {
                        update_option($admin_slug_plugin . '_license_status', $data['license']);
                    }
                    if (isset($_POST['license_key'])) {
                        update_option($admin_slug_plugin . '_license_key', sanitize_title(urldecode($_POST['license_key'])));
                    }

                    $expires = get_option($admin_slug_plugin . '_license_expire', null);
                    $license_limit = get_option($admin_slug_plugin . '_license_limit', null);
                    $site_count = get_option($admin_slug_plugin . '_site_count', null);
                    $activations_left = get_option($admin_slug_plugin . '_activations_left', null);
                    $license_status = get_option($admin_slug_plugin . '_license_status', null);

                    switch ($license_status) {
                        case "expired":
                            $cmm_error_msg = 'Your License key has been expired at ' . $expires . '. Please contact us for Renew License Key';
                            $is_valid = false;
                            break;
                        case "inactive":
                            $cmm_error_msg = 'License key Inactive';
                            $is_valid = true;
                            break;
                        case "valid":
                            $cmm_error_msg = 'License key valid';
                            $is_valid = true;
                            break;
                        case "deactivated":
                            $cmm_error_msg = 'License key has been deactivated';
                            $is_valid = true;
                            break;
                        case 'no_activations_left':
                            $cmm_error_msg = __('Your license key has reached its activation limit.');
                            $is_valid = false;
                            break;
                        case 'site_inactive' :
                            if (is_numeric($activations_left)) {
                                if ($activations_left == 0) {
                                    $cmm_error_msg = __('Your license key has reached its activation limit.');
                                } else {
                                    $cmm_error_msg = __('Your license is not active for this Website.');
                                }
                            } else {
                                $cmm_error_msg = __('Your license is not active for this Website.');
                            }
                            $is_valid = true;
                            break;
                        case 'item_name_mismatch' :
                            $cmm_error_msg = sprintf(__('This appears to be an invalid license key for %s.'), $this->get_var('item_name'));
                            $is_valid = false;
                            break;
                        default:
                            $cmm_error_msg = 'License key do not match / Invalid';
                            $is_valid = false;
                            break;
                    }

                    $license = get_option($admin_slug_plugin . '_license_key', '');
                    if (!empty($license)):
                        $html = '<span style="font-style:italic">' . $cmm_error_msg . '</span>';
                        $html .= '<div class="comm-clear"></div>';
                        if ($license_status !== "invalid") {
                            if (is_numeric($activations_left)) {
                                $html .= '<span style="font-style:italic"> You have ' . $site_count . ' / ' . $license_limit . ' sites activated.</span>';

                            } else {
                                $html .= '<span style="font-style:italic"> You have ' . $site_count . " / " . $activations_left . ' sites activated.</span>';

                            }
                        }
                    endif;
                }
                $response = json_encode(array("is_valid"=>$is_valid,"data" => $data, 'msg' => $html));
                echo wp_kses_data($response);
                wp_die();
            }
        }
        /**
         * Register system status page on admin
         * Will be located under tools.php menu
         *
         * @since    0.2.5
         */
        public function register_admin_page()
        {
            $content = array();
            if(has_filter("commercioo/license/plugins/check-content") ){
                $content = apply_filters("commercioo/license/plugins/check-content",array());
            }
            if(has_filter("commercioo/license/theme/check-content")){
                $content = apply_filters("commercioo/license/theme/check-content",array());
            }
            if($content) {
                if (current_user_can("administrator")) {
                    // Commercioo tools submenu
                    add_submenu_page(
                        'comm-system-status',
                        __('Licenses', 'commercioo'),
                        __('Licenses', 'commercioo'),
                        'manage_commercioo',
                        'comm-license',
                        array($this, 'license_page_callback'),
                        15
                    );
                }
            }
        }

        /**
         * Load the commercioo agency page
         *
         * @since    1.0.0
         */
        public function license_page_callback()
        {
            include_once COMMERCIOO_PATH . 'admin/license/commercioo-license-page-admin-display.php';
        }

        public function display_license_content()
        {

        }

        /**
         * Register the stylesheets for the admin area.
         * @param $suffix
         * @since    0.4.8
         */
        public function enqueue_styles($suffix)
        {
            $screen = get_current_screen();
            if ('commercioo_page_comm-license' === $suffix) {
                wp_enqueue_style('main-commercioo_plugin-license-page', COMMERCIOO_URL . 'admin/license/css/commercioo-license-interface.css', array(), COMMERCIOO_VERSION, 'all');
                wp_register_style('commercioo_license-page-system_requirement', COMMERCIOO_URL . 'admin/license/css/system_requirement.css', array(), COMMERCIOO_VERSION, 'all');
                wp_register_style('commercioo_license-page-style', COMMERCIOO_URL . 'admin/license/css/license-style.css', array(), '', 'all');
                wp_enqueue_style('commercioo_license-page-style');
                wp_enqueue_style('commercioo_license-page-system_requirement');
            }
        }

        /**
         * Register the JavaScript for the admin area.
         * @param $suffix
         * @since    0.4.8
         */
        public function enqueue_scripts($suffix)
        {
            $screen = get_current_screen();

            /**
             * load script only on Agency Settings page
             */
            if ('commercioo_page_comm-license' === $suffix) {
                wp_register_script('commercioo_license-page_script_license', COMMERCIOO_URL . 'admin/license/js/license.js', array('jquery'), NULL, true); //DASH
                wp_enqueue_script('commercioo_license-page_script_license');

                wp_localize_script('commercioo_license-page_script_license', 'commercioo_license_page_ajax_obj',
                    array(
                        'ajaxurl' => admin_url('admin-ajax.php'),
                        'site_url' => site_url(),
                        'store_url' => $this->store_url,
                        'nonce_license' => wp_create_nonce('license_edd'),
                        'path_img_url' => dirname(__FILE__),
                    )
                );
            }
        }
    }
}