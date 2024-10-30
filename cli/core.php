<?php

namespace Commercioo;

class controller
{
    private static $instance;
    private $admin_translation, $public_translation, $currency, $country;

    public function __construct()
    {
        global $comm_options, $comm_country;
        $comm_options = $this->comm_get_settings();
        $comm_country = $this->comm_get_country();

        $this->admin_translation = include(plugin_dir_path(dirname(__FILE__)) . 'includes/data/admin-translation.php');
        $this->public_translation = include(plugin_dir_path(dirname(__FILE__)) . 'includes/data/public-translation.php');
        add_action("init", array($this, "commercioo_set_default_timezone"));
        add_action("init", array($this, "commercioo_cek_permalink_structure"));
    }

    public static function get_instance()
    {
        if (!isset(self::$instance)) {
            self::$instance = new controller();
        }
        return self::$instance;
    }

    public function get_translation($source)
    {
        return ($source == 'admin') ? $this->admin_translation : $this->public_translation;
    }

    public function is_comm_pro()
    {
        $is_condition = class_exists("Commercioo_Pro") ? 1: 0;
        if ($is_condition) {
            return true;
        }
        return false;
    }

    public function is_comm_wa()
    {
        $is_condition = class_exists("Commercioo_Wa") ? 1: 0;
        if ($is_condition) {
            return true;
        }
        return false;
    }

    public function is_comm_wa_followup()
    {
        $is_condition = class_exists("Commercioo_Wa_Followup") ? 1: 0;
        if ($is_condition) {
            return true;
        }
        return false;
    }

    public function is_comm_ar()
    {
        $is_condition = class_exists("Commercioo_Autoresponder") ? 1: 0;
        if ($is_condition) {
            return true;
        }
        return false;
    }

    public function is_comm_ongkir()
    {
        $is_condition = class_exists("Commercioo_Ongkir") ? 1: 0;
        if ($is_condition) {
            return true;
        }
        return false;
    }

    public function is_comm_auto_reg()
    {
        $is_condition = class_exists("Commercioo_Auto_Register") ? 1: 0;
        if ( $is_condition ) {
            return true;
        }
        return false;
    }

    /**
     * Set default timezone
     */
    public function commercioo_set_default_timezone() {
		$timezone_string = get_option( 'timezone_string' );
		
		/**
		 * The `date_default_timezone_set` will only receive the `timezone_string` option
		 * Otherwise this will generate a notice.
		 * 
		 * Accepted timezone string: https://www.php.net/manual/en/timezones.others.php
		 * 
		 * I don't know what the reason behind this `date_default_timezone_set` code here,
		 * because I found that this code should be avoided in WordPress
		 * this will needs a further research and discussions.
		 * 
		 * Reference: https://weston.ruter.net/2013/04/02/do-not-change-the-default-timezone-from-utc-in-wordpress/
		 */
		if ( $timezone_string ) {
			date_default_timezone_set( $timezone_string );
		}
    }

    /**
     * Check Permalink Structure before access dashboard commercioo
     */
    public function commercioo_cek_permalink_structure()
    {
        if ('/%postname%/' !== get_option('permalink_structure')) {
            update_option("permalink_structure", "/%postname%/");
            flush_rewrite_rules();
        }
    }

    public function comm_api()
    {
        $namespace = 'commercioo/v1';
        $route = [
//PROCEDURE STORE DB endpoint
            'get_data' => [
                'method' => 'GET',
                'args' => [
                    'tbl' => ['required' => false],
                    'status' => ['required' => false],
                ]
            ],
        ];
        foreach ($route as $r => $v) {
            register_rest_route($namespace, '/' . $r . '/', [
                'methods' => $v['method'],
                'callback' => [$this, $r],
                'permission_callback' => '__return_true',
                'args' => $v['args']
            ]);
        }

        register_rest_route('commercioo/v1', '/comm_change_status/', array(
            'methods' => \WP_REST_Server::CREATABLE,
            'callback' => array($this, 'comm_change_status'),
            'args' => array(
                'id' => array(
                    'validate_callback' => function ($param, $request, $key) {
                        return is_numeric($param);
                    }
                ),
                'status' => ['required' => false],
            ),
            'permission_callback' => function () {
                return current_user_can('publish_posts');
            }
        ));
    }

    /**
     * localize_script Get Proses Data
     */
    public function process_data()
    {
        global $comm_options;
        $is_2step= false;
        if(class_exists('Commercioo_2_Step')){
            $is_2step= true;
        }
        $is_available_payment_method = apply_filters("comm_check_payment_method",array());
        $is_available_payment_method = array_filter($is_available_payment_method);
        if (is_customize_preview() || defined( 'ELEMENTOR_VERSION' ) && \Elementor\Plugin::$instance->preview->is_preview_mode()) {
            $is_customize_preview = true;
        } else {
            $is_customize_preview = false;
        }
        $data = [
            'api_url' => site_url('/wp-json/wp/v2/'),
            'root' => esc_url_raw(rest_url()),
            'site_url' => site_url(),
            'second_root' => esc_url_raw(rest_url()) . "commercioo/v1/",
            'nonce' => wp_create_nonce('wp_rest'),
            'get_list_data' => esc_url_raw(rest_url()) . "commercioo/v1/get_data?",
            'admin_url' => admin_url(),
            'admin_ajax_url' => admin_url('admin-ajax.php'),
            'is_comm_pro' => is_comm_pro(),
            'is_comm_wa' => is_comm_wa(),
            'is_comm_ar' => is_comm_ar(),
            'is_comm_auto_reg' => is_comm_auto_reg(),
            'is_available_payment_method' => ($is_available_payment_method)?true:false,
            'is_2step' => $is_2step,
            'is_user_login' => is_user_logged_in(),
            'checkout_url' => comm_get_checkout_uri(),
            'cart_url' => comm_get_cart_uri(),
            'is_customize_preview' => $is_customize_preview,
            'shopping_url' => comm_get_shopping_uri(),
            'tinymce_config' => [
                'tinymce' => [
                    'wpautop' => true,
                    'skin' => 'lightgray',
                    'languag' => 'en',
                    'formats' => [
                        'alignleft' => [
                            ['selector' => 'p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li', 'styles' => ['textAlign' => 'left']],
                            ['selector' => 'img,table,dl.wp-caption', 'classes' => 'alignleft']
                        ],
                        'aligncenter' => [
                            ['selector' => 'p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li', 'styles' => ['textAlign' => 'center']],
                            ['selector' => 'img,table,dl.wp-caption', 'classes' => 'aligncenter']
                        ],
                        'alignright' => [
                            ['selector' => 'p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li', 'styles' => ['textAlign' => 'right']],
                            ['selector' => 'img,table,dl.wp-caption', 'classes' => 'alignright']
                        ],
                        'strikethrough' => ['inline' => 'del']
                    ],
                    'relative_urls' => false,
                    'remove_script_host' => false,
                    'convert_urls' => false,
                    'browser_spellcheck' => true,
                    'fix_list_elements' => true,
                    // entities            => '38,amp,60,lt,62,gt',
                    // entity_encoding     => 'raw',
                    'keep_styles' => false,
                    'paste_webkit_styles' => 'font-weight font-style color',
                    'preview_styles' => 'font-family font-size font-weight font-style text-decoration text-transform',
                    'tabfocus_elements' => ':prev,:next',
                    'plugins' => 'charmap,hr,media,paste,tabfocus,textcolor,fullscreen,wordpress,wpeditimage,wpgallery,wplink,wpdialogs,wpview,lists',
                    'resize' => 'vertical',
                    'menubar' => false,
                    'indent' => false,
//                    'toolbar1' => 'bold,italic,strikethrough,bullist,numlist,blockquote,hr,alignleft,aligncenter,alignright,link,unlink,wp_more,spellchecker,fullscreen,wp_adv',
//                    'toolbar2' => 'formatselect,underline,bullist,numlist,alignjustify,forecolor,pastetext,removeformat,charmap,outdent,indent,undo,redo,wp_help',
                    'toolbar1' => 'formatselect,bold,italic,strikethrough',
                    'toolbar3' => '',
                    'toolbar4' => '',
                    'body_class' => 'id post-type-post post-status-publish post-format-standard',
                    'wpeditimage_disable_captions' => false,
                    'wpeditimage_html5_captions' => false

                ],
                'quicktags' => true,
                'mediaButtons' => false,
                'teeny' => true,

            ],
            'comm_options' => $comm_options,
            'currency_symbol' => $comm_options['currency_symbol'],
            'currency_pattern' => comm_get_currency_pattern(),
            'currency_zero' => comm_money_format(0),
            'post_id' => get_the_ID(),
            'is_checkout_page' => is_checkout_page(),
        ];

        return $data;
    }

    public function comm_change_status($request)
    {
        $params = $request->get_params();
        $id = $params['id'];
        $status = $params['status'];

        $listData = get_post($id);
        if (!$listData) {
            return new \WP_Error('comm_error', "could not find original post: " .
                $id, array('status' =>
                404));
        }
        if (has_action('comm_update_status')) {
            do_action("comm_update_status", $id, $status);
            $response = ["id" => $id, "success" => true];
            return rest_ensure_response($response);
        } else {
            return new \WP_Error('comm_error', "Could not find existence of Action Hook", array('status' =>
                404));
        }
    }

    // PROCEDURE GET DATA FROM API ENDPOINT
    public function get_data($request) {
        $params    = $request->get_params();
        $tbl       = null;
        $status    = null;
        $startDate = null;
        $endDate   = null;
		$order_by  = sanitize_text_field(strtolower( $params['order_by'] ));
        $post_type = sanitize_text_field(strtolower( $params['post_type'] ));
		
		if ( isset( $params['tbl'] ) && $params['tbl'] ) {
            $tbl = sanitize_text_field($params['tbl']);
        }

		if ( isset( $params['status'] ) && $params['status'] ) {
            $status = sanitize_text_field(strtolower( $params['status'] ));
        }

        if ( isset( $params['startDate'] ) && $params['startDate'] ) {
            $startDate = sanitize_text_field(strtolower( $params['startDate'] ));
        }

        if ( isset( $params['enddate'] ) && $params['enddate'] ) {
            $endDate = sanitize_text_field(strtolower( $params['enddate'] ));
        }

        if ( ! $endDate ) {
            $endDate = $startDate;
        }

        if ( isset( $params['meta'] ) ) {
            $meta_query = json_decode( wp_kses_data($params['meta']), true );
        } else {
            $meta_query = array();
        }

        do_action( 'comm_before_get_data', $params );

        $data = $this->comm_get_result_data($status, $post_type, $order_by, $startDate, $endDate, $meta_query );
        comm_controller()->comm_result_date($post_type, $data);
    }

    public function comm_get_result_data($status, $post_type, $order_by, $startDate = null, $endDate = null, $meta_query = array() )
    {
        $attr['orderby'] = $order_by;
        $attr['order'] = 'DESC';
        if ($startDate && $endDate) {
            $startDate = date("Y-m-d", strtotime($startDate));
            $endDate = date("Y-m-d", strtotime($endDate));
            $attr['date_query'] = array(
                array(
                    'after' => $startDate,
                    'before' => $endDate,
                    'inclusive' => true,
                ),
            );
        }

        if ( ! empty( $meta_query ) ) {
            $attr['meta_query'] = $meta_query;
        }

        if ($status == null) {
            $attr['hide_empty'] = 0;
            $data = get_terms($post_type, $attr);
        } else {
            $attr['post_type'] = $post_type;
            $attr['numberposts'] = -1;
            $attr['post_status'] = $status;
            $data = get_posts($attr);
        }
        return $data;
    }

    public function comm_grid_list_content_view($data, $post_type) {
        switch ($post_type) {
            case "comm_product":
                $product = \commercioo\admin\Comm_Product::get_instance();
				wp_send_json( $product->comm_product_list( $data ) );
                break;
            case "comm_product_cat":
                $product = \commercioo\admin\Comm_Product::get_instance();
                wp_send_json( $product->comm_product_cat( $data, $post_type ) );
                break;
            case "comm_product_tag":
                $product = \commercioo\admin\Comm_Product::get_instance();
                wp_send_json( $product->comm_product_cat( $data, $post_type ) );
                break;
            case "comm_order":
                $order = \commercioo\admin\Comm_Order::get_instance();
                wp_send_json( $order->comm_order_list( $data ) );
                break;
        }
    }

    public function comm_result_date($post_type, $data)
    {
        do_action("comm_grid_list_content", $data, $post_type);
    }

    public function is_comm_page($page = [])
    {
        $screen = get_current_screen();
        $pages = ($page == []) ? apply_filters("do_is_comm_page",[
            'commercioo_page_comm_dashboard',
            'admin_page_comm_dashboard',
            'admin_page_comm_prod',
            'admin_page_comm_category',
            'admin_page_comm_tags',
            'admin_page_comm_coupon',
            'admin_page_comm_order',
            'admin_page_comm_manage_cs',
            'admin_page_comm_wa_followup',
            'admin_page_comm_autoresponder',
            'admin_page_comm_settings',
            'admin_page_comm_customers',
            'admin_page_comm_statistics',
        ]) : $page;
        return in_array((isset($screen->id))?$screen->id:'', $pages);
    }

    public function getPartFileContent($path_location = '')
    {
        if ($this->is_comm_page(['admin_page_comm_prod'])) {
            $part = "comm_prod";
        } elseif ($this->is_comm_page(['admin_page_comm_category'])) {
            $part = "comm_category";
        } elseif ($this->is_comm_page(['admin_page_comm_tags'])) {
            $part = "comm_tags";
        } elseif ($this->is_comm_page(['admin_page_comm_coupon'])) {
            $path_location = COMMERCIOO_PRO_DIR_PATH_PARTIALS;
            $part = "comm_coupon";
        } elseif ($this->is_comm_page(['admin_page_comm_order'])) {
            if(isset( $_GET['action'] ) && isset( $_GET['id'] ) && $_GET['action'] == 'edit'){
                $part = "comm_order_edit";
            }else{
                $part = "comm_order";
            }
        } elseif ($this->is_comm_page(['admin_page_comm_manage_cs'])) {
            $path_location = COMMERCIOO_WA_DIR_PATH_PARTIALS;
            $part = "comm_manage_cs";
        } elseif ($this->is_comm_page(['admin_page_comm_wa_followup'])) {
            $path_location = COMMERCIOO_WA_FOLLOWUP_DIR_PATH_PARTIALS;
            $part = "comm_wa_followup";
        } elseif ($this->is_comm_page(['admin_page_comm_autoresponder'])) {
            $path_location = COMMERCIOO_AUTORESPONDER_DIR_PATH_PARTIALS;
            if( isset( $_GET['action'] ) && isset( $_GET['id'] ) && $_GET['action'] == 'edit' ){
                $part = 'comm_autoresponder_edit';
            } else {
                $part = "comm_autoresponder";
            }
        } elseif ($this->is_comm_page(['admin_page_comm_settings'])) {
            $part = "comm_settings";
        }elseif ($this->is_comm_page(['admin_page_comm_customers'])) {
            if(isset( $_GET['action'] ) && isset( $_GET['id'] ) && $_GET['action'] == 'edit'){
                $part = "comm_customer_edit";
            }else{
                $part = "comm_customers";
            }
        } elseif ($this->is_comm_page(['admin_page_comm_statistics'])) {
            $part = "comm_statistics";
        } else {
            $part = "comm_dashboard";
        }

        $files = apply_filters("commercioo_part_file_content",$path_location . $part);
        return $files;
    }

    public function comm_sub_menu_filter()
    {
        $comm_admin_sub_menu = apply_filters("commercioo_sub_menu",[
            // DASHBOARD PAGE
            [
                'parent_slug' => 'comm_sub_void',
                'page_tile' => __('Dashboard', 'commercioo'),
                'menu_tile' => __('Dashboard', 'commercioo'),
                'capability' => 'manage_options',
                'menu_slug' => 'comm_dashboard',
                'function' => [comm_controller(), 'comm_menu_callback']
            ],
            // PRODUCTS PAGE
            [
                'parent_slug' => 'comm_sub_void',
                'page_tile' => __('Products', 'commercioo'),
                'menu_tile' => __('Products', 'commercioo'),
                'capability' => 'manage_options',
                'menu_slug' => 'comm_prod',
                'function' => [comm_controller(), 'comm_menu_callback']
            ],
            // CATEGORY PAGE
            [
                'parent_slug' => 'comm_sub_void',
                'page_tile' => __('Category', 'commercioo'),
                'menu_tile' => __('Category', 'commercioo'),
                'capability' => 'manage_options',
                'menu_slug' => 'comm_category',
                'function' => [comm_controller(), 'comm_menu_callback']
            ],
            // TAGS PAGE
            [
                'parent_slug' => 'comm_sub_void',
                'page_tile' => __('Tags', 'commercioo'),
                'menu_tile' => __('Tags', 'commercioo'),
                'capability' => 'manage_options',
                'menu_slug' => 'comm_tags',
                'function' => [comm_controller(), 'comm_menu_callback']
            ],
            // ORDER PAGE
            [
                'parent_slug' => 'comm_sub_void',
                'page_tile' => __('Orders', 'commercioo'),
                'menu_tile' => __('Orders', 'commercioo'),
                'capability' => 'manage_commercioo',
                'menu_slug' => 'comm_order',
                'function' => [comm_controller(), 'comm_menu_callback']
            ],
            // CUSTOMERS PAGE
            [
                'parent_slug' => 'comm_sub_void',
                'page_tile' => __('Customers', 'commercioo'),
                'menu_tile' => __('Customers', 'commercioo'),
                'capability' => 'manage_commercioo',
                'menu_slug' => 'comm_customers',
                'function' => [comm_controller(), 'comm_menu_callback']
            ],
            // SETTINGS PAGE
            [
                'parent_slug' => 'comm_sub_void',
                'page_tile' => __('Settings', 'commercioo'),
                'menu_tile' => __('Settings', 'commercioo'),
                'capability' => 'manage_options',
                'menu_slug' => 'comm_settings',
                'function' => [comm_controller(), 'comm_menu_callback']
            ],
            // STATISTICS PAGE
            [
                'parent_slug' => 'comm_sub_void',
                'page_tile' => __('Statistics', 'commercioo'),
                'menu_tile' => __('Statistics', 'commercioo'),
                'capability' => 'manage_options',
                'menu_slug' => 'comm_statistics',
                'function' => [comm_controller(), 'comm_menu_callback']
            ],
        ]);

        return $comm_admin_sub_menu;
    }

    public function comm_menu_callback()
    {
        include_once plugin_dir_path(__FILE__) . '../admin/partials/commercioo-admin-display.php';
    }

    function getCurrentMenuURL()
    {
        global $wp;
        $current_url = admin_url('admin.php' . add_query_arg([$_GET], $wp->request));
        return $current_url;
    }

    function comm_dash_page($page = '',$id='')
    {
        if (!empty($page) && !empty($id)){
            $url = admin_url("admin.php?page=$page"."&id=".$id);
        }else if (!empty($page)) {
            $url = admin_url("admin.php?page=$page");
        } else {
            $url = admin_url();
        }
        return $url;
    }

    /**
     * Get Settings
     *
     * Retrieves all plugin settings
     *
     * @since 1.0
     * @return array comm settings
     */
    public function comm_get_settings()
    {
        $settings = array();
        // Update old settings with new single option
        $settings['comm_general_settings'] = is_array(get_option('comm_general_settings', [])) ? get_option('comm_general_settings', []) : array();
        $settings['comm_gateways_settings'] = is_array(get_option('comm_gateways_settings', [])) ? get_option('comm_gateways_settings', [])
            : array();
        $settings['comm_shipping_settings'] = is_array(get_option('comm_shipping_settings')) ? get_option('comm_shipping_settings') : array();
        $settings['comm_shipping_jne_settings'] = is_array(get_option('comm_shipping_jne_settings')) ? get_option('comm_shipping_jne_settings') : array();
        $settings['comm_misc_settings'] = is_array(get_option('comm_misc_settings')) ? get_option('comm_misc_settings') : array();
        $settings['comm_order_forms_settings'] = is_array(get_option('comm_order_forms_settings')) ? get_option('comm_order_forms_settings') : array();
        $settings['comm_login_register_settings'] = is_array(get_option('comm_login_register_settings')) ? get_option('comm_login_register_settings') : array();

        foreach ($settings as $k => $val) {
            $settings = get_option($k);
            if (empty($settings) && !empty($val)) {
                update_option($k, $val);
            }
        }

        $general_settings = is_array(get_option('comm_general_settings', [])) ? get_option('comm_general_settings', []) : array();
        $gateway_settings = is_array(get_option('comm_gateways_settings', [])) ? get_option('comm_gateways_settings', []) : array();
        $shipping_settings = is_array(get_option('comm_shipping_settings')) ? get_option('comm_shipping_settings') : array();
        $shipping_jne_settings = is_array(get_option('comm_shipping_jne_settings')) ? get_option('comm_shipping_jne_settings') : array();
        $misc_settings = is_array(get_option('comm_misc_settings')) ? get_option('comm_misc_settings') : array();
        $order_form_settings = is_array(get_option('comm_order_forms_settings')) ? get_option('comm_order_forms_settings') : array();
        $login_register_settings = get_option('comm_login_register_settings', 
            array(
                'login_message_enabled'     => 1,
                'login_message'             => 'Login here by filling you\'re username and password or use your favorite social network account to enter to the site. Site login will simplify the purchase process and allows you to manage your personal account.',
                'register_message_enabled'  => 1,
                'register_message'          => 'Registering for this site allows you to access your order status and history. Just fill in the fields below, and we’ll get a new account set up for you in no time. We will only ask you for information necessary to make the purchase process faster and easier.',
                'agreement_message_enabled' => 1,
                'agreement_message'         => 'Your personal data will be used to support your experience throughout this website, to manage access to your account, and for other purposes described in our <b>Privacy Policy</b>',
                'forgot_message_enabled'    => 1,
                'forgot_message'            => 'Forgot your password? Please enter your email address. You will receive a link to create a new password via email.'
            )
        );
        
        $settings = array_merge(
            $general_settings,
            $gateway_settings,
            $shipping_settings,
            $shipping_jne_settings,
            $misc_settings,
            $order_form_settings,
            $login_register_settings
        );

        return apply_filters('comm_get_settings', $settings);
    }

    public function comm_get_currency_list()
    {
        //LIST ALL CURRENCY
        static $currencies;

        if (!isset($currencies)) {
            $currencies = array_unique(
                apply_filters(
                    'commercioo_currencies',
                    array(
                        'AED' => __('United Arab Emirates dirham', 'commercioo_currency'),
                        'AFN' => __('Afghan afghani', 'commercioo_currency'),
                        'ALL' => __('Albanian lek', 'commercioo_currency'),
                        'AMD' => __('Armenian dram', 'commercioo_currency'),
                        'ANG' => __('Netherlands Antillean guilder', 'commercioo_currency'),
                        'AOA' => __('Angolan kwanza', 'commercioo_currency'),
                        'ARS' => __('Argentine peso', 'commercioo_currency'),
                        'AUD' => __('Australian dollar', 'commercioo_currency'),
                        'AWG' => __('Aruban florin', 'commercioo_currency'),
                        'AZN' => __('Azerbaijani manat', 'commercioo_currency'),
                        'BAM' => __('Bosnia and Herzegovina convertible mark', 'commercioo_currency'),
                        'BBD' => __('Barbadian dollar', 'commercioo_currency'),
                        'BDT' => __('Bangladeshi taka', 'commercioo_currency'),
                        'BGN' => __('Bulgarian lev', 'commercioo_currency'),
                        'BHD' => __('Bahraini dinar', 'commercioo_currency'),
                        'BIF' => __('Burundian franc', 'commercioo_currency'),
                        'BMD' => __('Bermudian dollar', 'commercioo_currency'),
                        'BND' => __('Brunei dollar', 'commercioo_currency'),
                        'BOB' => __('Bolivian boliviano', 'commercioo_currency'),
                        'BRL' => __('Brazilian real', 'commercioo_currency'),
                        'BSD' => __('Bahamian dollar', 'commercioo_currency'),
                        'BTC' => __('Bitcoin', 'commercioo_currency'),
                        'BTN' => __('Bhutanese ngultrum', 'commercioo_currency'),
                        'BWP' => __('Botswana pula', 'commercioo_currency'),
                        'BYR' => __('Belarusian ruble (old)', 'commercioo_currency'),
                        'BYN' => __('Belarusian ruble', 'commercioo_currency'),
                        'BZD' => __('Belize dollar', 'commercioo_currency'),
                        'CAD' => __('Canadian dollar', 'commercioo_currency'),
                        'CDF' => __('Congolese franc', 'commercioo_currency'),
                        'CHF' => __('Swiss franc', 'commercioo_currency'),
                        'CLP' => __('Chilean peso', 'commercioo_currency'),
                        'CNY' => __('Chinese yuan', 'commercioo_currency'),
                        'COP' => __('Colombian peso', 'commercioo_currency'),
                        'CRC' => __('Costa Rican col&oacute;n', 'commercioo_currency'),
                        'CUC' => __('Cuban convertible peso', 'commercioo_currency'),
                        'CUP' => __('Cuban peso', 'commercioo_currency'),
                        'CVE' => __('Cape Verdean escudo', 'commercioo_currency'),
                        'CZK' => __('Czech koruna', 'commercioo_currency'),
                        'DJF' => __('Djiboutian franc', 'commercioo_currency'),
                        'DKK' => __('Danish krone', 'commercioo_currency'),
                        'DOP' => __('Dominican peso', 'commercioo_currency'),
                        'DZD' => __('Algerian dinar', 'commercioo_currency'),
                        'EGP' => __('Egyptian pound', 'commercioo_currency'),
                        'ERN' => __('Eritrean nakfa', 'commercioo_currency'),
                        'ETB' => __('Ethiopian birr', 'commercioo_currency'),
                        'EUR' => __('Euro', 'commercioo_currency'),
                        'FJD' => __('Fijian dollar', 'commercioo_currency'),
                        'FKP' => __('Falkland Islands pound', 'commercioo_currency'),
                        'GBP' => __('Pound sterling', 'commercioo_currency'),
                        'GEL' => __('Georgian lari', 'commercioo_currency'),
                        'GGP' => __('Guernsey pound', 'commercioo_currency'),
                        'GHS' => __('Ghana cedi', 'commercioo_currency'),
                        'GIP' => __('Gibraltar pound', 'commercioo_currency'),
                        'GMD' => __('Gambian dalasi', 'commercioo_currency'),
                        'GNF' => __('Guinean franc', 'commercioo_currency'),
                        'GTQ' => __('Guatemalan quetzal', 'commercioo_currency'),
                        'GYD' => __('Guyanese dollar', 'commercioo_currency'),
                        'HKD' => __('Hong Kong dollar', 'commercioo_currency'),
                        'HNL' => __('Honduran lempira', 'commercioo_currency'),
                        'HRK' => __('Croatian kuna', 'commercioo_currency'),
                        'HTG' => __('Haitian gourde', 'commercioo_currency'),
                        'HUF' => __('Hungarian forint', 'commercioo_currency'),
                        'IDR' => __('Indonesian rupiah', 'commercioo_currency'),
                        'ILS' => __('Israeli new shekel', 'commercioo_currency'),
                        'IMP' => __('Manx pound', 'commercioo_currency'),
                        'INR' => __('Indian rupee', 'commercioo_currency'),
                        'IQD' => __('Iraqi dinar', 'commercioo_currency'),
                        'IRR' => __('Iranian rial', 'commercioo_currency'),
                        'IRT' => __('Iranian toman', 'commercioo_currency'),
                        'ISK' => __('Icelandic kr&oacute;na', 'commercioo_currency'),
                        'JEP' => __('Jersey pound', 'commercioo_currency'),
                        'JMD' => __('Jamaican dollar', 'commercioo_currency'),
                        'JOD' => __('Jordanian dinar', 'commercioo_currency'),
                        'JPY' => __('Japanese yen', 'commercioo_currency'),
                        'KES' => __('Kenyan shilling', 'commercioo_currency'),
                        'KGS' => __('Kyrgyzstani som', 'commercioo_currency'),
                        'KHR' => __('Cambodian riel', 'commercioo_currency'),
                        'KMF' => __('Comorian franc', 'commercioo_currency'),
                        'KPW' => __('North Korean won', 'commercioo_currency'),
                        'KRW' => __('South Korean won', 'commercioo_currency'),
                        'KWD' => __('Kuwaiti dinar', 'commercioo_currency'),
                        'KYD' => __('Cayman Islands dollar', 'commercioo_currency'),
                        'KZT' => __('Kazakhstani tenge', 'commercioo_currency'),
                        'LAK' => __('Lao kip', 'commercioo_currency'),
                        'LBP' => __('Lebanese pound', 'commercioo_currency'),
                        'LKR' => __('Sri Lankan rupee', 'commercioo_currency'),
                        'LRD' => __('Liberian dollar', 'commercioo_currency'),
                        'LSL' => __('Lesotho loti', 'commercioo_currency'),
                        'LYD' => __('Libyan dinar', 'commercioo_currency'),
                        'MAD' => __('Moroccan dirham', 'commercioo_currency'),
                        'MDL' => __('Moldovan leu', 'commercioo_currency'),
                        'MGA' => __('Malagasy ariary', 'commercioo_currency'),
                        'MKD' => __('Macedonian denar', 'commercioo_currency'),
                        'MMK' => __('Burmese kyat', 'commercioo_currency'),
                        'MNT' => __('Mongolian t&ouml;gr&ouml;g', 'commercioo_currency'),
                        'MOP' => __('Macanese pataca', 'commercioo_currency'),
                        'MRU' => __('Mauritanian ouguiya', 'commercioo_currency'),
                        'MUR' => __('Mauritian rupee', 'commercioo_currency'),
                        'MVR' => __('Maldivian rufiyaa', 'commercioo_currency'),
                        'MWK' => __('Malawian kwacha', 'commercioo_currency'),
                        'MXN' => __('Mexican peso', 'commercioo_currency'),
                        'MYR' => __('Malaysian ringgit', 'commercioo_currency'),
                        'MZN' => __('Mozambican metical', 'commercioo_currency'),
                        'NAD' => __('Namibian dollar', 'commercioo_currency'),
                        'NGN' => __('Nigerian naira', 'commercioo_currency'),
                        'NIO' => __('Nicaraguan c&oacute;rdoba', 'commercioo_currency'),
                        'NOK' => __('Norwegian krone', 'commercioo_currency'),
                        'NPR' => __('Nepalese rupee', 'commercioo_currency'),
                        'NZD' => __('New Zealand dollar', 'commercioo_currency'),
                        'OMR' => __('Omani rial', 'commercioo_currency'),
                        'PAB' => __('Panamanian balboa', 'commercioo_currency'),
                        'PEN' => __('Sol', 'commercioo_currency'),
                        'PGK' => __('Papua New Guinean kina', 'commercioo_currency'),
                        'PHP' => __('Philippine peso', 'commercioo_currency'),
                        'PKR' => __('Pakistani rupee', 'commercioo_currency'),
                        'PLN' => __('Polish z&#x142;oty', 'commercioo_currency'),
                        'PRB' => __('Transnistrian ruble', 'commercioo_currency'),
                        'PYG' => __('Paraguayan guaran&iacute;', 'commercioo_currency'),
                        'QAR' => __('Qatari riyal', 'commercioo_currency'),
                        'RON' => __('Romanian leu', 'commercioo_currency'),
                        'RSD' => __('Serbian dinar', 'commercioo_currency'),
                        'RUB' => __('Russian ruble', 'commercioo_currency'),
                        'RWF' => __('Rwandan franc', 'commercioo_currency'),
                        'SAR' => __('Saudi riyal', 'commercioo_currency'),
                        'SBD' => __('Solomon Islands dollar', 'commercioo_currency'),
                        'SCR' => __('Seychellois rupee', 'commercioo_currency'),
                        'SDG' => __('Sudanese pound', 'commercioo_currency'),
                        'SEK' => __('Swedish krona', 'commercioo_currency'),
                        'SGD' => __('Singapore dollar', 'commercioo_currency'),
                        'SHP' => __('Saint Helena pound', 'commercioo_currency'),
                        'SLL' => __('Sierra Leonean leone', 'commercioo_currency'),
                        'SOS' => __('Somali shilling', 'commercioo_currency'),
                        'SRD' => __('Surinamese dollar', 'commercioo_currency'),
                        'SSP' => __('South Sudanese pound', 'commercioo_currency'),
                        'STN' => __('S&atilde;o Tom&eacute; and Pr&iacute;ncipe dobra', 'commercioo_currency'),
                        'SYP' => __('Syrian pound', 'commercioo_currency'),
                        'SZL' => __('Swazi lilangeni', 'commercioo_currency'),
                        'THB' => __('Thai baht', 'commercioo_currency'),
                        'TJS' => __('Tajikistani somoni', 'commercioo_currency'),
                        'TMT' => __('Turkmenistan manat', 'commercioo_currency'),
                        'TND' => __('Tunisian dinar', 'commercioo_currency'),
                        'TOP' => __('Tongan pa&#x2bb;anga', 'commercioo_currency'),
                        'TRY' => __('Turkish lira', 'commercioo_currency'),
                        'TTD' => __('Trinidad and Tobago dollar', 'commercioo_currency'),
                        'TWD' => __('New Taiwan dollar', 'commercioo_currency'),
                        'TZS' => __('Tanzanian shilling', 'commercioo_currency'),
                        'UAH' => __('Ukrainian hryvnia', 'commercioo_currency'),
                        'UGX' => __('Ugandan shilling', 'commercioo_currency'),
                        'USD' => __('United States (US) dollar', 'commercioo_currency'),
                        'UYU' => __('Uruguayan peso', 'commercioo_currency'),
                        'UZS' => __('Uzbekistani som', 'commercioo_currency'),
                        'VEF' => __('Venezuelan bol&iacute;var', 'commercioo_currency'),
                        'VES' => __('Bol&iacute;var soberano', 'commercioo_currency'),
                        'VND' => __('Vietnamese &#x111;&#x1ed3;ng', 'commercioo_currency'),
                        'VUV' => __('Vanuatu vatu', 'commercioo_currency'),
                        'WST' => __('Samoan t&#x101;l&#x101;', 'commercioo_currency'),
                        'XAF' => __('Central African CFA franc', 'commercioo_currency'),
                        'XCD' => __('East Caribbean dollar', 'commercioo_currency'),
                        'XOF' => __('West African CFA franc', 'commercioo_currency'),
                        'XPF' => __('CFP franc', 'commercioo_currency'),
                        'YER' => __('Yemeni rial', 'commercioo_currency'),
                        'ZAR' => __('South African rand', 'commercioo_currency'),
                        'ZMW' => __('Zambian kwacha', 'commercioo_currency'),
                    )
                )
            );
        }

        $this->currency = $currencies;
        return $this->currency;
    }

    public function comm_get_currency_symbols()
    {

        $symbols = apply_filters(
            'commercioo_currency_symbols',
            array(
                'AED' => '&#x62f;.&#x625;',
                'AFN' => '&#x60b;',
                'ALL' => 'L',
                'AMD' => 'AMD',
                'ANG' => '&fnof;',
                'AOA' => 'Kz',
                'ARS' => '&#36;',
                'AUD' => '&#36;',
                'AWG' => 'Afl.',
                'AZN' => 'AZN',
                'BAM' => 'KM',
                'BBD' => '&#36;',
                'BDT' => '&#2547;&nbsp;',
                'BGN' => '&#1083;&#1074;.',
                'BHD' => '.&#x62f;.&#x628;',
                'BIF' => 'Fr',
                'BMD' => '&#36;',
                'BND' => '&#36;',
                'BOB' => 'Bs.',
                'BRL' => '&#82;&#36;',
                'BSD' => '&#36;',
                'BTC' => '&#3647;',
                'BTN' => 'Nu.',
                'BWP' => 'P',
                'BYR' => 'Br',
                'BYN' => 'Br',
                'BZD' => '&#36;',
                'CAD' => '&#36;',
                'CDF' => 'Fr',
                'CHF' => '&#67;&#72;&#70;',
                'CLP' => '&#36;',
                'CNY' => '&yen;',
                'COP' => '&#36;',
                'CRC' => '&#x20a1;',
                'CUC' => '&#36;',
                'CUP' => '&#36;',
                'CVE' => '&#36;',
                'CZK' => '&#75;&#269;',
                'DJF' => 'Fr',
                'DKK' => 'DKK',
                'DOP' => 'RD&#36;',
                'DZD' => '&#x62f;.&#x62c;',
                'EGP' => 'EGP',
                'ERN' => 'Nfk',
                'ETB' => 'Br',
                'EUR' => '&euro;',
                'FJD' => '&#36;',
                'FKP' => '&pound;',
                'GBP' => '&pound;',
                'GEL' => '&#x20be;',
                'GGP' => '&pound;',
                'GHS' => '&#x20b5;',
                'GIP' => '&pound;',
                'GMD' => 'D',
                'GNF' => 'Fr',
                'GTQ' => 'Q',
                'GYD' => '&#36;',
                'HKD' => '&#36;',
                'HNL' => 'L',
                'HRK' => 'kn',
                'HTG' => 'G',
                'HUF' => '&#70;&#116;',
                'IDR' => 'Rp',
                'ILS' => '&#8362;',
                'IMP' => '&pound;',
                'INR' => '&#8377;',
                'IQD' => '&#x639;.&#x62f;',
                'IRR' => '&#xfdfc;',
                'IRT' => '&#x062A;&#x0648;&#x0645;&#x0627;&#x0646;',
                'ISK' => 'kr.',
                'JEP' => '&pound;',
                'JMD' => '&#36;',
                'JOD' => '&#x62f;.&#x627;',
                'JPY' => '&yen;',
                'KES' => 'KSh',
                'KGS' => '&#x441;&#x43e;&#x43c;',
                'KHR' => '&#x17db;',
                'KMF' => 'Fr',
                'KPW' => '&#x20a9;',
                'KRW' => '&#8361;',
                'KWD' => '&#x62f;.&#x643;',
                'KYD' => '&#36;',
                'KZT' => '&#8376;',
                'LAK' => '&#8365;',
                'LBP' => '&#x644;.&#x644;',
                'LKR' => '&#xdbb;&#xdd4;',
                'LRD' => '&#36;',
                'LSL' => 'L',
                'LYD' => '&#x644;.&#x62f;',
                'MAD' => '&#x62f;.&#x645;.',
                'MDL' => 'MDL',
                'MGA' => 'Ar',
                'MKD' => '&#x434;&#x435;&#x43d;',
                'MMK' => 'Ks',
                'MNT' => '&#x20ae;',
                'MOP' => 'P',
                'MRU' => 'UM',
                'MUR' => '&#x20a8;',
                'MVR' => '.&#x783;',
                'MWK' => 'MK',
                'MXN' => '&#36;',
                'MYR' => '&#82;&#77;',
                'MZN' => 'MT',
                'NAD' => 'N&#36;',
                'NGN' => '&#8358;',
                'NIO' => 'C&#36;',
                'NOK' => '&#107;&#114;',
                'NPR' => '&#8360;',
                'NZD' => '&#36;',
                'OMR' => '&#x631;.&#x639;.',
                'PAB' => 'B/.',
                'PEN' => 'S/',
                'PGK' => 'K',
                'PHP' => '&#8369;',
                'PKR' => '&#8360;',
                'PLN' => '&#122;&#322;',
                'PRB' => '&#x440;.',
                'PYG' => '&#8370;',
                'QAR' => '&#x631;.&#x642;',
                'RMB' => '&yen;',
                'RON' => 'lei',
                'RSD' => '&#1088;&#1089;&#1076;',
                'RUB' => '&#8381;',
                'RWF' => 'Fr',
                'SAR' => '&#x631;.&#x633;',
                'SBD' => '&#36;',
                'SCR' => '&#x20a8;',
                'SDG' => '&#x62c;.&#x633;.',
                'SEK' => '&#107;&#114;',
                'SGD' => '&#36;',
                'SHP' => '&pound;',
                'SLL' => 'Le',
                'SOS' => 'Sh',
                'SRD' => '&#36;',
                'SSP' => '&pound;',
                'STN' => 'Db',
                'SYP' => '&#x644;.&#x633;',
                'SZL' => 'L',
                'THB' => '&#3647;',
                'TJS' => '&#x405;&#x41c;',
                'TMT' => 'm',
                'TND' => '&#x62f;.&#x62a;',
                'TOP' => 'T&#36;',
                'TRY' => '&#8378;',
                'TTD' => '&#36;',
                'TWD' => '&#78;&#84;&#36;',
                'TZS' => 'Sh',
                'UAH' => '&#8372;',
                'UGX' => 'UGX',
                'USD' => '&#36;',
                'UYU' => '&#36;',
                'UZS' => 'UZS',
                'VEF' => 'Bs F',
                'VES' => 'Bs.S',
                'VND' => '&#8363;',
                'VUV' => 'Vt',
                'WST' => 'T',
                'XAF' => 'CFA',
                'XCD' => '&#36;',
                'XOF' => 'CFA',
                'XPF' => 'Fr',
                'YER' => '&#xfdfc;',
                'ZAR' => '&#82;',
                'ZMW' => 'ZK',
            )
        );

        return $symbols;
    }

    function comm_get_currency_symbol($currency = '')
    {
        if (!$currency) {
            $currency = $this->comm_get_currency_list();
        }

        $symbols = $this->comm_get_currency_symbols();

        $currency_symbol = isset($symbols[$currency]) ? $symbols[$currency] : '';

        return apply_filters('commercioo_currency_symbol', $currency_symbol, $currency);
    }

    function comm_get_currency_name($currency_code = '')
    {

        $name = $this->comm_get_currency_list();

        $currency = isset($name[$currency_code]) ? $name[$currency_code] : '';

        return apply_filters('commercioo_currency_code', $currency, $currency_code);
    }

    public function comm_get_country()
    {
        $this->country = include plugin_dir_path(dirname(__FILE__)) . 'includes/i18n/countries.php';
        return $this->country;
    }

    public function comm_get_products( $status = 'publish', $order_by = 'post_title' ){
        $attr['orderby'] = $order_by;
        $attr['order'] = 'ASC';
        $attr['post_type'] = 'comm_product';
        $attr['numberposts'] = -1;
        $attr['post_status'] = $status;
        $data = get_posts($attr);

        if ( is_comm_pro() ) {
            $attr['orderby'] = $order_by;
            $attr['order'] = 'ASC';
            $attr['post_type'] = 'comm_product_var';
            $attr['numberposts'] = -1;
            $attr['post_status'] = $status;
            $vars = get_posts($attr);
            foreach ( $vars as $key => $var ) {
                if ( $status != get_post_status( $var->post_parent ) ) {
                    unset( $vars[ $key ] );
                    continue;
                }
                $data[] = $vars[ $key ];
            }
        }

        foreach ( $data as $key => $post ) {
            if ( 'comm_product_var' === $post->post_type ) {
                $data[ $key ]->post_title = get_the_title( $data[ $key ]->post_parent ) . ' - ' . $data[ $key ]->post_title;
            }
            if ( 'comm_product' === $post->post_type && 'variable' === get_post_meta( $post->ID, '_product_data', true ) ) {
                unset( $data[ $key ] );
            }
        }

        if ( 'post_title' === $order_by ) {
            usort( $data, function($a, $b) {return strcmp($a->post_title, $b->post_title);});
        }

        return $data;
    }
}
