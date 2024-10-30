<?php

namespace commercioo\admin;
class Comm_Settings
{
    // instance
    private static $instance;
    private $setting_prefix = 'comm_setting';

    // getInstance
    public static function get_instance()
    {
        if (!isset(self::$instance)) {
            self::$instance = new self;
        }

        return self::$instance;
    }

    // __construct
    public function __construct()
    {
    }

    public function endpoint_register()
    {
        // insert or update settings
        register_rest_route('commercioo/v1', '/settings', array(
            'methods' => \WP_REST_Server::CREATABLE,
            'callback' => array($this, 'insert_update_setting'),
            'permission_callback' => function () {
                return current_user_can('publish_posts');
            }
        ));

        // read all settings
        register_rest_route('commercioo/v1', '/settings', array(
            'methods' => \WP_REST_Server::READABLE,
            'callback' => array($this, 'get_all_settings'),
            'permission_callback' => function () {
                return current_user_can('publish_posts');
            }
        ));
    }

    public function insert_update_setting($request)
    {
        $params = $request->get_params();
        $key = sanitize_post($params['comm_key']);
        $settings = get_option('comm_' . $key . '_settings', []);
        switch ($key) {
            case 'gateways':
                if (isset($params['comm_sub_key'])) {
                    if(isset($settings['payment_option']) && isset($params['payment_option'])){
                        $payment_option = array_merge($settings['payment_option'],$params['payment_option']);
                        $params['payment_option'] = $payment_option;
                        unset($settings['payment_option']);
                    }
                    $payment_method = isset($params['payment_method'])?$params['payment_method']:'';
                    if(isset($params['paypal'])){
                        $payment_method = isset($params['paypal']['payment_method'])?$params['paypal']['payment_method']:'';
                    }

                    if(!isset($params['payment_option'][$payment_method])){
                        if(isset($settings['payment_option'])){
                            unset($settings['payment_option'][$payment_method]);
                        }
                    }
                    if ($params['comm_sub_key'] == "gateways_general_settings"){
                        if (!isset($params['additional_fee_status'])) {
                          if (isset($settings['additional_fee_status'])) {
                            unset($settings['additional_fee_status']);
                          }
                        }
                    }
                    if ($params['comm_sub_key'] == "gateways_tripay" || $params['comm_sub_key'] == "gateways_bacs") {
                        if (isset($params['bank_transfer'])) {
                            $bank_transfer = array_map('array_filter', $params['bank_transfer']);
                            $bank_transfer = array_filter($bank_transfer);
                            $bank_transfer = array_values($bank_transfer);
                            if (isset($settings['bank_transfer']) && $params['bank_transfer']) {
                                unset($settings['bank_transfer']);
                                $params['bank_transfer'] = $bank_transfer;
                            }

                        }
                        if(isset($settings['payment_option']['commercioo-tripay'])){
                            unset($settings['payment_option']['commercioo-tripay']);
                        }

                        if (!isset($params['tripay_payment_channel'])) {
                            unset($settings['tripay_payment_channel']);
                        } else {
                            if(isset($settings['tripay_payment_channel'])){
                                if (!in_array($params['tripay_payment_channel'], $settings['tripay_payment_channel'])) {
                                    unset($settings['tripay_payment_channel']);
                                }
                            }
                        }
                        if (!isset($params['tripay_payment_channel_name'])) {
                          if (isset($settings['tripay_payment_channel_name'])) {
                              unset($settings['tripay_payment_channel_name']);
                          }
                        }

                    } elseif ($params['comm_sub_key'] == "gateways_paypal") {
                        if(!isset($params['paypal'])){
                            if(isset($settings['paypal']['paypal_sandbox'])){
                                unset($settings['paypal']['paypal_sandbox']);
                            }
                        }
                        $params = array_merge($settings, $params);
                    }
                }

                break;
            case 'emails':
                //get default email settings
                $default_emails = new \Commercioo\Emails\Default_Emails();
                $emails_settings = $default_emails->current_settings;
                // replace with the new one
                $email_key = $params['comm_settings_email_name'];
                $emails_settings[$email_key] = $params[$email_key];
                // unset email key
                unset($params['comm_settings_email_name']);

                $params = $emails_settings;
                break;
            case 'misc':
                if (isset($params['currency_name'])) {
                    unset($params['currency_name']);
                }
                if (isset($params['currency_symbol'])) {
                    unset($params['currency_symbol']);
                }
                if (isset($settings['currency_name'])) {
                    unset($settings['currency_name']);
                }
                if (isset($settings['currency_symbol'])) {
                    unset($settings['currency_symbol']);
                }
                break;

            case 'login_register':
                if (isset($params['currency_name'])) {
                    unset($params['currency_name']);
                }
                if (isset($params['currency_symbol'])) {
                    unset($params['currency_symbol']);
                }
                if (!isset($params['login_message_enabled'])) {
                    unset($settings['login_message_enabled']);
                }
                if (!isset($params['register_message_enabled'])) {
                    unset($settings['register_message_enabled']);
                }
                if (!isset($params['agreement_message_enabled'])) {
                    unset($settings['agreement_message_enabled']);
                }
                if (!isset($params['forgot_message_enabled'])) {
                    unset($settings['forgot_message_enabled']);
                }
                if (isset($settings['currency_name'])) {
                    unset($settings['currency_name']);
                }
                if (isset($settings['currency_symbol'])) {
                    unset($settings['currency_symbol']);
                }
                break;
            case 'shipping':

                if (!isset($params['shipping_option'])) {
                    if (isset($settings['shipping_option'])) {
                        unset($settings['shipping_option']);
                    }
                }
//                $result = get_option('comm_' . $key . '_settings', []);
//                return rest_ensure_response($result);
                break;
            case 'order_forms':
                if (isset($params['currency_name'])) {
                    unset($params['currency_name']);
                }
                if (isset($params['currency_symbol'])) {
                    unset($params['currency_symbol']);
                }
                if (isset($settings['currency_name'])) {
                    unset($settings['currency_name']);
                }
                if (isset($settings['currency_symbol'])) {
                    unset($settings['currency_symbol']);
                }
                if (isset($params['def_wa_msg'])) {
                    $params['def_wa_msg'] = strip_tags($params['def_wa_msg']);
                }
                $params = array_merge($settings, $params);

                $checkout_page_id = get_option('commercioo_Checkout_page_id');
                if (defined('ELEMENTOR_VERSION') && isset($params['checkout_elementor_status']) && boolval($params['checkout_elementor_status'])) {
                    if (isset($params['restore_to_shortcode'])) {
                        if ($params['restore_to_shortcode'] == "elementor") {
                            if (!\Elementor\Plugin::$instance->documents->get($checkout_page_id)->is_built_with_elementor()) {
                                wp_update_post([
                                    'ID' => $checkout_page_id,
                                    'post_content' => '',
                                ]);

                                $checkout_page = \Elementor\Plugin::$instance->documents->get($checkout_page_id);
                                $elementor_data = $checkout_page->convert_to_elementor();

                                $checkout_page->set_is_built_with_elementor(true);
                                $checkout_page->save($elementor_data);
                            } else {
                                wp_update_post([
                                    'ID' => $checkout_page_id,
                                    'post_content' => '<!-- wp:shortcode -->[' . apply_filters('commercioo_checkout_shortcode_tag', 'commercioo_checkout') . ']<!-- /wp:shortcode -->',
                                ]);
                                $checkout_page = \Elementor\Plugin::$instance->documents->get($checkout_page_id);
                                $checkout_page->set_is_built_with_elementor(null);
                            }
                        } elseif ($params['restore_to_shortcode'] == "restore") {
                            wp_update_post([
                                'ID' => $checkout_page_id,
                                'post_content' => '<!-- wp:shortcode -->[' . apply_filters('commercioo_checkout_shortcode_tag', 'commercioo_checkout') . ']<!-- /wp:shortcode -->',
                            ]);
                            $checkout_page = \Elementor\Plugin::$instance->documents->get($checkout_page_id);
                            $checkout_page->set_is_built_with_elementor(null);
                        }
                    }
                    // check page is elementor or not
                }
                // unset elementor status prevent saved to database
                if (isset($params['checkout_elementor_status'])) {
                    unset($params['checkout_elementor_status']);
                }
                if (isset($params['checkout_elementor_url'])) {
                    unset($params['checkout_elementor_url']);
                }
                if (isset($params['restore_to_shortcode'])) {
                    unset($params['restore_to_shortcode']);
                }
                break;
            default:
                // General Settings
                $params['currency_symbol'] = html_entity_decode(comm_controller()->comm_get_currency_symbol($params['currency']),ENT_COMPAT, 'UTF-8');
                $params['currency_name'] = comm_controller()->comm_get_currency_name($params['currency']);
                if(isset($params['timezone_string']) && !preg_match( '/^UTC[+-]/', $params['timezone_string'] )){
                    update_option("timezone_string",$params['timezone_string']);
                    update_option("gmt_offset",null);
                }elseif (isset($params['timezone_string']) && preg_match( '/^UTC[+-]/', $params['timezone_string'] )){
                    $gmt_offset = preg_replace( '/UTC\+?/', '', $params['timezone_string'] );
                    update_option("timezone_string",null);
                    update_option("gmt_offset",$gmt_offset);
                }
                break;
        }

//        if (isset($params['comm_key'])) {
//            unset($params['comm_key']);
//        }
//        if (isset($params['comm_sub_key'])) {
//            unset($params['comm_sub_key']);
//        }
//        $settings = array_merge($old_settings, $params);
//        if (count($settings) > 0) {
        $settings = apply_filters("comm_after_saved_settings", $settings, $key,$params);
        if (isset($settings['comm_key'])) {
            unset($settings['comm_key']);
        }
        if (isset($settings['comm_sub_key'])) {
            unset($settings['comm_sub_key']);
        }
        update_option('comm_' . $key . '_settings', $settings);
        $result = get_option('comm_' . $key . '_settings', []);
//        } else {
//            $result = get_option('comm_' . $key . '_settings', []);
//        }

        // result all settings
        return rest_ensure_response($result);
    }

    public function check_payment_option_setting($old_settings,$params){
        if(isset($old_settings['payment_option']) && isset($params['payment_option'])){
            if(isset($old_settings['payment_option']['commercioo-tripay'])){
                unset($old_settings['payment_option']['commercioo-tripay']);
            }
            $payment_option = array_merge($old_settings['payment_option'],$params['payment_option']);
            $params['payment_option'] = $payment_option;
            unset($old_settings['payment_option']);
        }
        if(!isset($params['payment_option'])){
            $payment_method = isset($params['payment_method'])?$params['payment_method']:'';
            if(isset($old_settings['payment_option'][$payment_method])){
                unset($old_settings['payment_option'][$payment_method]);
            }
        }
    }
    public function get_single_setting($request)
    {
        $params = $request->get_params();
        $setting_name = sanitize_text_field($params['name']);
        $actual_name = sprintf("%s_%s", $this->setting_prefix, $setting_name);

        // insert setting
        $setting_value = get_option($actual_name, $setting_name);

        // result
        $results = array(
            'name' => $setting_name,
            'actual_name' => $actual_name,
            'value' => $setting_value,
        );

        return rest_ensure_response($results);
    }

    public function get_all_settings($request)
    {
        $params = $request->get_params();
        $setting_name = sanitize_text_field($params['name']);
        $actual_name = sprintf("%s_settings", $setting_name);

        switch ($setting_name) {
            case 'emails':
                $default_emails = new \Commercioo\Emails\Default_Emails();
                $emails_settings = $default_emails->current_settings;
                // get email text by key
                $email_key = sanitize_text_field($params['email_name']);
                $results = $emails_settings[$email_key];
                $results['name'] = $setting_name;
                $results['email_name'] = $email_key;

                break;

            default:
                # code...
                break;
        }

        return rest_ensure_response($results);
    }

    private function filter_comm_settings($setting)
    {
        $value = sanitize_text_field($setting->option_value);
        $data = @unserialize($value);

        // whether the data is serialized-able or not
        if ($data !== false || $value === 'b:0;') {
            $value = unserialize($value);
        }

        // filter the setting
        $filtered_setting = array(
            'name' => str_replace(sprintf("%s_", $this->setting_prefix), '', $setting->option_name),
            'actual_name' => $setting->option_name,
            'value' => $value,
        );

        return $filtered_setting;
    }

    public function comm_after_saved_settings($settings, $key,$params)
    {

//        if (isset($params['comm_key'])) {
//            unset($params['comm_key']);
//        }
//        if (isset($params['comm_sub_key'])) {
//            unset($params['comm_sub_key']);
//        }
        $settings = array_merge($settings, $params);
        return $settings;
    }
}

//Comm_Settings::getInstance();