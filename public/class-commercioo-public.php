<?php
/**
 * The public-facing functionality of the plugin.
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    Commercioo
 * @subpackage Commercioo/public
 */

use Commercioo\controller;

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Commercioo
 * @subpackage Commercioo/public
 * @author     Your Name <email@example.com>
 */
class Commercioo_Public
{

    /**
     * The ID of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string $commercioo The ID of this plugin.
     */
    private $commercioo;

    /**
     * The version of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string $version The current version of this plugin.
     */
    private $version;
    private static $theme_support = false;
    private $controller;
    public $settings_shipping;
    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     * @param      string $commercioo The name of the plugin.
     * @param      string $version The version of this plugin.
     */
    public function __construct($commercioo, $version)
    {
        $this->settings_shipping = get_option( 'comm_shipping_settings', array() );
        $this->commercioo = $commercioo;
        $this->version = $version;
        $this->controller = controller::get_instance();
        add_action('init', array($this, 'add_cors_header'));
        add_action('init', array($this, 'comm_handle_add_to_cart'));
        add_action('rest_api_init', array($this, 'comm_public_rest_api_init'));

        add_filter('comm_item_cart_items', array($this, 'comm_item_cart_items'), 10, 1);
        add_filter('comm_content_cart_page', array($this, 'comm_content_cart_page'), 10, 1);
        add_filter('comm_content_get_product_quick_view', array($this, 'comm_content_get_product_quick_view'), 10, 2);
        add_filter('page_template', array($this, 'set_page_template'));
        add_action('template_redirect', array($this, 'comm_handle_item_carts'));
        add_action('wp_ajax_comm_view_add_to_item_cart', array($this, 'comm_view_add_to_item_cart'));
        add_action('wp_ajax_nopriv_comm_view_add_to_item_cart', array($this, 'comm_view_add_to_item_cart'));
    }


    function add_cors_header()
    {
        if(!is_customize_preview()){
            header("Access-Control-Allow-Origin: *");
            header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
        }
    }

    public function comm_public_rest_api_init()
    {
        register_rest_route('commercioo/v1', '/comm_add_to_cart_set_item/', array(
            'methods' => "POST",
            'callback' => array($this, 'comm_add_to_cart_set_item'),
            'permission_callback' => '__return_true',
            'args' => array(
                "prod_id" => ['required' => true],
                "cart_qty" => ['required' => true]),
        ));

        register_rest_route('commercioo/v1', '/comm_add_to_cart_del_item/', array(
            'methods' => "POST",
            'callback' => array($this, 'comm_add_to_cart_del_item'),
            'permission_callback' => '__return_true',
            'args' => array(
                "prod_id" => ['required' => true]),
        ));

        register_rest_route('commercioo/v1', '/comm_quick_view_get_product/', array(
            'methods' => "POST",
            'callback' => array($this, 'comm_quick_view_get_product'),
            'permission_callback' => '__return_true',
            'args' => array(
                "prod_id" => ['required' => true]),
        ));

        register_rest_route('commercioo/v1', '/refresh_order_summary/', array(
            'methods' => "POST",
            'callback' => array( $this, 'refresh_order_summary' ),
            'permission_callback' => '__return_true',
            'args' => array(
                "checkout_data" => ['required' => false ]
            ),
        ));

        register_rest_route('commercioo/v1', '/set_shipping/', array(
            'methods' => "POST",
            'callback' => array( $this, 'set_shipping' ),
            'permission_callback' => '__return_true',
            'args' => array(
                "shipping" => ['required' => true ]
            ),
        ));

    }

    public function comm_product_archive($paged, $params = [])
    {
        ob_start();
        include plugin_dir_path(__FILE__) . '../public/partials/commercioo-content-product-archive.php';
        $content = ob_get_clean();
        return $content;

    }

    public function comm_item_cart_items($cart)
    {
        ob_start();
        include plugin_dir_path(__FILE__) . '../public/partials/comm-item-cart-menu.php';
        $content = ob_get_clean();
        return $content;
    }

    public function comm_content_cart_page($cart)
    {
        ob_start();
        include plugin_dir_path(__FILE__) . '../public/partials/commercioo-content-cart-page.php';
        $content = ob_get_clean();
        return $content;
    }

    public function comm_content_get_product_quick_view($prod_id, $type)
    {
        // Jangan dihapus param: $product_id
        // Karena digunakan di dalam file: comm-content-get-product-quick-view.php
        $product_id = $prod_id;
        ob_start();
        if ( $type === 'elementor' ) {
            include plugin_dir_path(__FILE__) . '../public/partials/comm-content-get-product-quick-view-themex.php';
        } else {
            include plugin_dir_path(__FILE__) . '../public/partials/comm-content-get-product-quick-view.php';
        }
        $content = ob_get_clean();
        return $content;
    }

    public function comm_quick_view_get_product($request)
    {
        $params = $request->get_params();
        $prod_id = isset($params['prod_id'])?absint($params['prod_id']):0;
        $request_type = isset($params['request_type'])?esc_attr($params['request_type']):'';
        $content_view = apply_filters("comm_content_get_product_quick_view", $prod_id, $request_type);
        wp_send_json_success(["result_html" => $content_view], 200);
    }

    /**
     * Handle refresh order summary fragment on checkout
     * 
     * @param object $request Request object.
     */
    public function refresh_order_summary( $request ) {
        $params = $request->get_params();
        ob_start();
        include plugin_dir_path(__FILE__) . '../public/partials/commercioo-checkout-standalone-order-summary.php';
        $content = ob_get_clean();
        wp_send_json_success( ["result_html" => $content], 200);
    }

    /**
     * Handle order summary on checkout
     * @param string $files
     * @return false|string
     */
    public function commercioo_standalone_checkout_order_summary($files=''){
        ob_start();
        include COMMERCIOO_PATH . '/public/partials/commercioo-checkout-standalone-order-summary.php';
        $content = ob_get_clean();
        return $content;
    }
    /**
     * Handle set shipping
     * 
     * @param object $request Request object.
     */
    public function set_shipping( $request ) {
        $params = $request->get_params();
        $shipping = $params['shipping'];
        if ( ! empty( $shipping ) ) {
            $shipping = explode( '|', $shipping );
            if ( isset( $shipping[0] ) && isset( $shipping[1] ) ) {
                if(isset( $params['cart'] )){
                    $cart = json_decode( wp_kses_post(base64_decode( $params['cart'] )), true );
                    $cart = Commercioo\Cart::set_shipping($shipping[0], $shipping[1], $cart );
                    wp_send_json_success( ["cart" => base64_encode( json_encode( $cart ) ),'grandtotal' => esc_html(\Commercioo\Helper::formatted_currency(\Commercioo\Cart::get_total()))], 200);
                }
                Commercioo\Cart::set_shipping($shipping[0], $shipping[1] );
                // Call unique number: must activated Plugin Commercioo Unique Number
                do_action( 'commercioo_unique_number', Commercioo\Cart::get_carts() );
                wp_send_json_success( ["shipping" => Commercioo\Cart::get_shipping(),'grand_total_plain'=>\Commercioo\Cart::get_total(),'grandtotal' => esc_html(\Commercioo\Helper::formatted_currency(\Commercioo\Cart::get_total()))], 200);
            }
        }
        wp_send_json_success( ["shipping" => '','grandtotal' => esc_html(\Commercioo\Helper::formatted_currency(\Commercioo\Cart::get_total()))], 200);
    }

    public function comm_view_add_to_item_cart($request)
    {
        if(\Commercioo\Cart::has_shipping()){
            Commercioo\Cart::remove_shipping();
        }
        $total_cart_view = '<div class="count-cart">
                    <span class="item-cart-value comm-item-cart-tooltips">%s</span>
                  </div>';
        $total_item = Commercioo\Cart::get_items_count();
        $content_view = apply_filters("comm_item_cart_items", array() );
        $content_cart_page_view = apply_filters("comm_content_cart_page", array() );
        // Send a JSON response back to an Ajax request, indicating success.
        wp_send_json_success(["content_cart_page" => $content_cart_page_view, "result_html" => $content_view, "total_item_cart" => $total_item,
            "total_item_html" => sprintf($total_cart_view, $total_item)],
            200);
        wp_die();
    }

    public function comm_add_to_cart_del_item($request)
    {
        $params  = $request->get_params();
        $prod_id = $params['prod_id'];
        Commercioo\Cart::remove_item( $prod_id );
        wp_send_json_success( [ "total_item_cart" => Commercioo\Cart::get_items_count()], 200 );
    }

    /**
     * Get the list of actions that EDD has determined need to be delayed past init.
     *
     * @since 2.9.4
     *
     * @return array
     */
    function comm_delayed_actions_list()
    {
        return (array)apply_filters('comm_delayed_actions', array(
            'add_to_cart'
        ));
    }

    /**
     * Determine if the requested action needs to be delayed or not.
     *
     * @since 2.9.4
     *
     * @param string $action
     *
     * @return bool
     */
    function comm_is_delayed_action( $action = '' )
    {
        return in_array( $action, $this->comm_delayed_actions_list() );
    }

    public function comm_handle_item_carts() {
        global $post;
        if ( $post && $post->ID == get_option( 'commercioo_Checkout_page_id' ) ) {
            if ( ! is_user_logged_in() && ! is_comm_auto_reg() ) {
                $args = [
                    "message" => __('You must login before purchase order. Please Register if you don\'t have account', 'commercioo'),
                    "type"    => 'error',
                ];
                comm_set_transient( "comm_notices_transient_purchase", $args );
                wp_redirect( comm_get_account_uri() );
                die();
            }
            if ( Commercioo\Cart::is_empty() && !is_page_has_elementor() && function_exists("is_page_has_elementor")) {
                if ( is_customize_preview() ) {
                    // load if customizer is being viewed
                } elseif( defined( 'ELEMENTOR_VERSION' ) && \Elementor\Plugin::$instance->preview->is_preview_mode() ) {
                    // load if elementor editor is being viewed
                } else {
                    wp_redirect(comm_get_cart_uri(), 302);
                    die();
                }
            }
        }
    }

    public function comm_handle_add_to_cart()
    {
        if ( isset( $_GET['comm_action'] ) ) {
            $action = sanitize_key(! empty( $_GET['comm_action'] ) ?  $_GET['comm_action'] : false);
            if ($action == "add_to_cart") {
                $page_id_cart = get_option('commercioo_Cart_page_id');
                $is_delayed_action = $this->comm_is_delayed_action($action);

                if ( ! $is_delayed_action ) {
                    return;
                }

                if ( ! empty( $action ) ) {
                    $prod_id = sanitize_text_field(absint($_GET['comm_prod_id']));
                    $args    = array(
                        'post_type'   => 'comm_product',
                        'post_status' => ["publish", "pending"],
                        'p'           => $prod_id,
                    );
                    $get_stock_status = get_post_meta($prod_id, "_stock_status", true);
                    if( $get_stock_status == 'outofstock' ){
                        wp_redirect(get_permalink($page_id_cart));
                    } else {
                        $get_data = get_posts($args);
                        if ( $get_data ) {
                            Commercioo\Cart::add_item( $prod_id );
                            if ( ! Commercioo\Cart::is_empty() ) {
                                wp_redirect( remove_query_arg( array( 'comm_action', 'comm_prod_id' ) ) );
                                die();
                            } else {
                                if(!is_page_has_elementor() && function_exists("is_page_has_elementor")) {
                                    wp_redirect(get_permalink($page_id_cart));
                                }
                                die();
                            }
                        } else {
                            if ( ! Commercioo\Cart::is_empty() ) {
                                wp_redirect( remove_query_arg( array( 'comm_action', 'comm_prod_id' ) ) );
                            } else {
                                if(!is_page_has_elementor() && function_exists("is_page_has_elementor")) {
                                    wp_redirect(get_permalink($page_id_cart));
                                }
                            }
                            die();
                        }
                    }
                }
            }
        }
    }

    public function comm_add_to_cart_set_item($request)
    {
        $params = $request->get_params();
        $prod_id = $params['prod_id'];
        $cart_qty = $params['cart_qty'];
        $get_stock_status = get_post_meta($prod_id, "_stock_status", true);
        if($get_stock_status == 'outofstock'){
            $return = array('message' => __("Product of out of stock",'commercioo'));
            wp_send_json_error($return);
        }else{
            if ( ! empty( $prod_id ) ) {
                if ( is_array( $prod_id ) ) {
                    foreach ( $prod_id as $key => $value ) {
                        Commercioo\Cart::set_item_qty( $value, $cart_qty[ $key ] );
                    }
                } else {
                    Commercioo\Cart::add_item( $prod_id, $cart_qty );
                }
            }
            $content_view = apply_filters("comm_item_cart_items", array());
            wp_send_json_success(["result_html" => $content_view], 200);
        }
    }

    public function comm_wp_footer() {
        global $comm_options;

        if ( isset( $comm_options['footer_code'] ) ) {
            echo wp_kses_post($comm_options['footer_code']);
        }
    }

    public function comm_wp_head() {
        global $comm_options;
        
        if ( isset( $comm_options['header_code'] ) ) {
            echo wp_kses_post($comm_options['header_code']);
        }
    }

    /**
     * Register the stylesheets for the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function enqueue_styles()
    {
        global $post;
        /**
         * This function is provided for demonstration purposes only.
         *
         * An instance of this class should be passed to the run() function
         * defined in Commercioo_Loader as all of the hooks are defined
         * in that particular class.
         *
         * The Commercioo_Loader will then create the relationship
         * between the defined hooks and the functions defined in this
         * class.
         */

        wp_enqueue_style( 'commercioo-order-status', plugin_dir_url(__FILE__) . 'css/commercioo-order-status.css', array(), $this->version, 'all' );
        wp_enqueue_style( 'commercioo-toast-css', plugin_dir_url(__FILE__) . 'css/vendor/toast/jquery.toast.css', array(), $this->version, 'all' );
        wp_enqueue_style( 'commercioo-global', plugin_dir_url(__FILE__) . 'css/commercioo-global.css', array(), $this->version, 'all' );        
    }

    /**
     * Register the JavaScript for the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts() {
        global $post;
        /**
         * This function is provided for demonstration purposes only.
         *
         * An instance of this class should be passed to the run() function
         * defined in Commercioo_Loader as all of the hooks are defined
         * in that particular class.
         *
         * The Commercioo_Loader will then create the relationship
         * between the defined hooks and the functions defined in this
         * class.
         */
        wp_localize_script( 'jquery', 'comm_lang_Public', comm_controller()->get_translation( 'public' ) );
        wp_enqueue_script( 'jquery-toast' );
        wp_enqueue_script( 'jquery-block' );
        wp_enqueue_script( 'commercioo-public' );
        wp_localize_script( 'commercioo-public', 'cApiSettingsPublic', $this->controller->process_data() );
    }

    /**
     * Checkout page shortcode.
     *
     * @param array $atts Attributes.
     * @var         $content include content checkout page.
     * @return string
     */
    public function comm_checkout_shortcode( $atts, $content ) {
        global $post, $comm_options, $comm_country;
        if( $post ) {
            if ( get_option('commercioo_Checkout_page_id') == $post->ID ) {
                /**
                 * There are 2 available checkout templates:
                 * 1. for commercioo theme
                 * 2. for non commercioo themes, in this case we name it `standalone`
                 * BUT meanwhile, we will use the `standalone` theme to all themes
                 */

                // action to run before the loading the template
                do_action( 'commercioo_before_load_standalone_checkout_template' );

                wp_enqueue_style('bootstrap');
                wp_enqueue_script('bootstrap');
                // load style and script

                wp_enqueue_style( 'semantic-ui' );
                wp_enqueue_script( 'jsrender');
                wp_enqueue_script( 'semantic-ui' );

                wp_enqueue_style( 'commercioo-checkout-container' );
                wp_enqueue_style( 'commercioo-checkout' );
                wp_enqueue_script( 'commercioo-checkout' );

                // get some vars
                $order_forms = get_option('comm_order_forms_settings', array());

                // billing settings
                $billing = isset($order_forms['billing_address']) ? $order_forms['billing_address'] : array();

                // get fields.
                $checkout = Commercioo\Checkout::get_instance();
                // Fix: PHP Fatal error:  Uncaught Error: Call to undefined function is_user_logged_in() related with
                // plugin Commercioo Ongkir
                $default_fields = $checkout->default_fields();
                $checkout->set_default_fields();
                // End of Fix: PHP Fatal error:  Uncaught Error: Call to undefined function is_user_logged_in()
                // related with plugin Commercioo Ongkir

                // Get fields
                $billing_fields   = $checkout->get_billing_fields();
                $shipping_fields  = $checkout->get_shipping_fields();
                $order_note_field = $checkout->get_order_note_field();

                // button settings
                $button_class    = isset( $order_forms['button_style'] ) ? $order_forms['button_style'] : 'c-button-rounded';
                $button_color    = isset( $order_forms['button_color'] ) ? explode(',', $order_forms['button_color']) : array('#3f51b5', '#303f9f');
                $button_bg       = isset( $button_color[0] ) ? $button_color[0] : '#3f51b5';
                $button_bg_hover = isset( $button_color[1] ) ? $button_color[1] : '#303f9f';

                $label_field_color = isset( $order_forms['fields'], $order_forms['fields']['label_style'] ) ? $order_forms['fields']['label_style'] : '#586469';
                $text_field_color  = isset( $order_forms['fields'], $order_forms['fields']['text_style'] ) ? $order_forms['fields']['text_style'] : '#757575';
                $border_field_normal = isset( $order_forms['fields'], $order_forms['fields']['border_style'] ) ? $order_forms['fields']['border_style'] : '#ccc';
                $border_field_focus  = isset( $order_forms['fields'], $order_forms['fields']['border_focus_style'] ) ? $order_forms['fields']['border_focus_style'] : '#F15A29';

                // cart data
                $items    = Commercioo\Cart::get_items();
                $product_id = 0;
                if($items){
                    foreach ($items as $k => $prod_id){
                        $get_stock_status = get_post_meta(intval($k), "_stock_status", true);
                        if($get_stock_status == 'outofstock'){
                            $product_id = $k;
                        }
                    }
                }
                $subtotal = Commercioo\Cart::get_subtotal();
                $total    = Commercioo\Cart::get_total();

                // get customer
                $user_id           = get_current_user_id();
                $customer          = new \Commercioo\Models\Customer( $user_id );
                $customer_billing  = $customer->get_billing_address();
                $customer_shipping = $customer->get_shipping_address();

                // user info
                $user_info = get_userdata( $user_id );

                // shop logo
                if ( isset( $comm_options['store_logo'] ) ) {
                    $thumb_id = intval( $comm_options['store_logo'] );
                    $thumb    = wp_get_attachment_image_src( $thumb_id, 'full' );
                    $logo_url = $thumb ? $thumb[0] : COMMERCIOO_URL . 'img/commercioo-logo.svg';
                } else {
                    $logo_url = COMMERCIOO_URL . 'img/commercioo-logo.svg';
                }
                // payment method
                $bank_transfer_method   = isset( $comm_options['payment_option'] ) ? $comm_options['payment_option'] : false;
                $bank_transfer_accounts = isset( $comm_options['bank_transfer'] ) ? $comm_options['bank_transfer'] : array();

                // when this form should be disabled
                if ( ! $bank_transfer_method || count($comm_options['payment_option']) == 1 && isset($comm_options['payment_option']['bacs']) && !isset($comm_options['bank_transfer']) ) {
                    $disable_form = true;
                } else {
                    $disable_form = false;
                }
                // load template
                ob_start();
                include apply_filters('commercioo_standalone_checkout_template',
                    plugin_dir_path( __FILE__ ) . '../public/partials/commercioo-checkout-standalone-display.php'
                );

                $content .= ob_get_clean();
            }
        }
        return $content;
    }

    /**
     * Checkout page shortcode.
     *
     * @param array $atts Attributes.
     * @var         $content include content checkout page.
     * @return string
     */
    public function comm_cart_shortcode( $atts, $content )
    {
        global $post;
        if( $post ) {
            if ( get_option( 'commercioo_Cart_page_id' ) == $post->ID ) {
                ob_start();
                wp_enqueue_style( 'commercioo-cart' );
                wp_enqueue_script('commercioo-cart' );

                include plugin_dir_path(__FILE__) . '../public/partials/commercioo-cart-display.php';
                $content .= ob_get_clean();
            }
        }
        return $content;
    }

    /**
     * Checkout page shortcode.
     *
     * @param array $atts Attributes.
     * @var         $content.
     * @return string|mixed
     */
    public function comm_product_archive_shortcode($atts, $content)
    {
        extract(shortcode_atts(array(
            'id' => null,
        ), $atts));

        $args = array(
            'post_type' => 'comm_product',
            'numberposts' => -1
        );

        $product_args = [];
        if (is_comm_product_taxonomy()) {
            if (is_tax('comm_product_cat') || is_tax('comm_product_tag')) {
                $current_term = get_queried_object();
                $product_args['tax_query'] = [
                    [
                        'taxonomy' => 'comm_product_cat',
                        'field' => 'slug',
                        'terms' => $current_term->slug,
                        'include_children' => false
                    ]
                ];
            }
        }

        if (isset($atts['id'])) {
            return $content;
        } else {
            if (!empty($product_args)) {
                ob_start();
                include plugin_dir_path(__FILE__) . '../public/partials/commercioo-product-display.php';
                $content .= ob_get_clean();

            }
        }
        return $content;
    }

    /**
     * Account page shortcode.
     *
     * @param array $atts Attributes.
     * @var         $content include content checkout page.
     * @return string
     */
    public function comm_account_shortcode($atts, $content) {
        global $post, $wp;

        $subpages = comm_get_account_menus();

        if( $post ) {
            if ( intval( get_option( 'commercioo_Account_page_id' ) ) === $post->ID ) {
                ob_start();
                // Begin of Master Asset (JS & CSS) Account
                wp_enqueue_style( 'bootstrap' );
                wp_enqueue_style( 'commercioo-cart' );
                wp_enqueue_style( 'commercioo-thank-you' );

                wp_enqueue_script( 'bootstrap' );
                wp_enqueue_script( 'commercioo-cart' );
                wp_enqueue_script( 'commercioo-product-archive' );
                wp_enqueue_script( 'commercioo-thank-you' );
                // End of Master Asset (JS & CSS) Product Cart

                $active_subpage = 'dashboard';
                foreach ( $subpages as $subpage ) {
                    if ( isset( $wp->query_vars ) && isset( $wp->query_vars[$subpage] ) ) {
                        $active_subpage = $subpage;
                        $param = $wp->query_vars[$subpage];
                        break;
                    }
                }
                if ( is_user_logged_in() ) {
                    \Commercioo\Template::render( 'account/account', array(
                        'current_user' => get_user_by('id', get_current_user_id()),
                        'subpage'      => $active_subpage,
                        'param'        => isset($param) ? $param: '',
                        'base_url'     => get_permalink($post->ID)
                    ), true );
                } elseif ( 'forgot-password' === $active_subpage ) {
                    wp_enqueue_style('commercioo-login' );
                    if ( isset( $_GET['key'], $_GET['id'] ) && ! empty( $_GET['key'] ) && ! empty( $_GET['id'] ) ) {
                        $userdata = get_userdata( sanitize_text_field(absint( $_GET['id'] )));
                        $login    = $userdata ? $userdata->user_login : '';
                        $user = check_password_reset_key( sanitize_text_field( wp_unslash( $_GET['key'] ) ), $login );
                        if ( ! is_wp_error( $user ) ) {
                            \Commercioo\Template::render('account/reset-password', array(
                                'reset_key'   => sanitize_text_field( wp_unslash( $_GET['key'] ) ),
                                'reset_login' => $login
                            ), true);
                        } else {
                            \Commercioo\Template::render('account/forgot-password', array(), true );
                        }
                    } else {
                        \Commercioo\Template::render('account/forgot-password', array(), true );
                    }
                } else {
                    wp_enqueue_style( 'commercioo-login' );
                    \Commercioo\Template::render('account/login', array(), true );
                }
                $content .= ob_get_clean();
            }
        }
        return $content;
    }

    /**
     * Set page template to fullwidth if available
     *
     * @param  string $page_template Default page template.
     * @return string                Full width page template.
     */
    public function set_page_template($page_template)
    {
        global $post;
        if($post) {
            if ($post->ID === intval(get_option('commercioo_Account_page_id'))) {
                if (file_exists(get_stylesheet_directory() . '/page-fullwidth.php')) {
                    $page_template = get_stylesheet_directory() . '/page-fullwidth.php';
                }
            }
            if ($post->ID === intval(get_option('commercioo_Cart_page_id'))) {
                if (file_exists(get_stylesheet_directory() . '/page-fullwidth.php')) {
                    $page_template = get_stylesheet_directory() . '/page-fullwidth.php';
                }
            }
            if ($post->ID === intval(get_option('commercioo_thank_you_page_id'))) {
                if (file_exists(get_stylesheet_directory() . '/page-fullwidth.php')) {
                    $page_template = get_stylesheet_directory() . '/page-fullwidth.php';
                }
            }
        }
        return $page_template;
    }

    public function commercioo_order_payment_method_thank_you($order_id,$payment_method="bacs",$files=''){
        ob_start();
        if($payment_method=="bacs"){
            include COMMERCIOO_PATH . 'public/partials/thank_you/content_bank_transfer.php';
        }elseif ($payment_method=="paypal"){
            include COMMERCIOO_PATH . 'public/partials/thank_you/content_paypal.php';
        }
        $html = ob_get_clean();
        return $html;
    }
    public function commercioo_thank_you($atts, $content){
        global $wp,$comm_options;
        ob_start();
        $args = shortcode_atts( array(
            'col' => '1',
            'color' => '#F15A29',
        ), $atts );

        $column = isset($args['col']) || !empty($args['col'])?$args['col']:'1';
        $colors = isset($args['color']) || !empty($args['color'])?$args['color']:'#F15A29';
        // load style and script
        wp_enqueue_style('bootstrap');
        wp_enqueue_script('bootstrap');
        wp_enqueue_style( 'commercioo-thank-you' );
        wp_enqueue_script( 'commercioo-thank-you' );
        
        if($column=='2'){
            wp_enqueue_style( 'commercioo-thank-you-two-column' );
        } else{
            wp_enqueue_style( 'commercioo-thank-you-one-column' );
        }

        $endpoint       = \Commercioo\Query\Commercioo_Query::get_instance()->get_current_endpoint(); // all endpoint available in file includes/class-commercioo-query.php
        $order_id = null;
        if($endpoint){
            $order_id  = absint( $wp->query_vars[$endpoint] );
        }
        if($order_id){
            $key = sanitize_text_field(isset($_GET['key'])?wp_unslash($_GET['key']):'');
            // get some vars
            $order_forms        = get_option( 'comm_order_forms_settings', array() );
            $order              = new \Commercioo\Models\Order( $order_id );
            $order_key = wp_unslash( $key ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
          if($endpoint=="commercioo-confirmation-payment" || $endpoint=="commercioo-thank-you-confirmation-payment"){
              wp_enqueue_style('commercioo-daterangepicker', COMMERCIOO_URL . 'admin/css/vendor/daterangepicker.css', array(), $this->version, 'all');
              wp_enqueue_script('commercioo-moment.min', COMMERCIOO_URL . 'admin/js/vendor/moment.min.js', array('jquery'), $this->version, true);
              wp_enqueue_script('commercioo-daterangepicker', COMMERCIOO_URL . 'admin/js/vendor/daterangepicker.js', array('jquery'), $this->version, true);
              wp_enqueue_script('commercioo-thankyou-confirmation-payment', COMMERCIOO_URL . 'public/js/thank_you/thankyou-confirmation-payment-standalone.js', array('jquery'), $this->version, true);
                include apply_filters('commercioo_standalone_thankyou_template',
                    plugin_dir_path(__FILE__) . '../public/partials/commercioo-confirmation-payment-thankyou-standalone-display.php'
                );
          }else{
            if ($order_id === $order->get_order_id() && hash_equals( $order->get_order_key(), $order_key )) {
                $order_items = $order->get_order_cart_items();
                $order_form_setting = get_option('comm_order_forms_settings');
                $payment_method = $order->get_payment_method();
                $order_status= $order->get_order_status();

                $pixel_event= isset($comm_options['typ_pixel_event'])? esc_attr($comm_options['typ_pixel_event']):null;
                $fb_pixel_id = isset($comm_options['fb_pixel_id'])? esc_attr($comm_options['fb_pixel_id']):null;
                do_action("do_commercioo_fb_fixel",$fb_pixel_id,$pixel_event);

                // load template
                if ($column == '2') {
                    include apply_filters('commercioo_standalone_thankyou_template',
                        plugin_dir_path(__FILE__) . '../public/partials/commercioo-two-column-thankyou-standalone-display.php'
                    );
                } else {
                    include apply_filters('commercioo_standalone_thankyou_template',
                        plugin_dir_path(__FILE__) . '../public/partials/commercioo-one-column-thankyou-standalone-display.php'
                    );
                }
            }else{
                ?>
                <div class="row">
                    <div class="col-md-9">
                        <table class="table">
                            <tbody class="tbody-wishlist">
                            <tr>
                                <td class="c-no-border">
                                    <div class="col-md-9 mb-2">
                                        <span class="desc-empty"><?php esc_html_e( "You don't have any order.", "commercioo" ) ?></span>
                                    </div>
                                    <div class="col-md-3">
                                        <button class="btn btn-blue comm-shop-now"><?php esc_html_e( 'SHOP NOW', 'commercioo' ) ?></button>
                                    </div>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="col-md-3">
                        <?php if ( class_exists( 'Commercioo_Store_Pro' ) ) echo do_shortcode( '[commercioo_recently_viewed_products]' ); ?>
                    </div>
                </div>
                <?php
              }
          }
        }else{
            ?>
            <div class="row">
                <div class="col-md-9">
                    <table class="table">
                        <tbody class="tbody-wishlist">
                        <tr>
                            <td class="c-no-border">
                                <div class="col-md-9 mb-2">
                                    <span class="desc-empty"><?php esc_html_e( "You don't have any order.", "commercioo" ) ?></span>
                                </div>
                                <div class="col-md-3">
                                    <button class="btn btn-blue comm-shop-now"><?php esc_html_e( 'SHOP NOW', 'commercioo' ) ?></button>
                                </div>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>
                <div class="col-md-3">
                    <?php if ( class_exists( 'Commercioo_Store_Pro' ) ) echo do_shortcode( '[commercioo_recently_viewed_products]' ); ?>
                </div>
            </div>
            <?php
        }
        $content .= ob_get_clean();
        return $content;
    }

    /**
     * Fix the `rest_url` error while ssl on oncertain servers
     * Related discussion: https://core.trac.wordpress.org/ticket/36451
     * 
     * @since    0.2.3
     * @param    string
     */
    public function rest_url_patch_with_ssl_on( $url ) {
        if ( is_ssl() ) {
            $url = set_url_scheme( $url, 'https' );
            return $url;
        }

        return $url;
    }

    /** 
     * Show payment method on checkout page
     * 
     * @since    0.3.7
     */
    public function payment_method(){
        global $comm_options;
        $is_available = array();
        if(isset($comm_options['payment_option']) && count($comm_options['payment_option'])>0){
            $payment_option = $comm_options['payment_option'];
            foreach ($payment_option as $key_po => $val){
                $is_available[$key_po] = apply_filters("commercioo/payment/payment-options/{$key_po}",array());
            }
        }
        $is_available = array_filter($is_available);
        if(!$is_available){
            ?>
            <div class="commercioo-checkout-alert">
                <?php echo esc_html( 'Sorry, it seems that there are no available payment methods for your state. Please contact us if you require assistance or wish to make alternate arrangements.');?>
            </div>
<?php
        }else{
            foreach ($is_available as $k => $payment_method){
                if(has_filter("commercioo/display/payment-method-html/$k")){
                    apply_filters("commercioo/display/payment-method-html/$k",'');
                }
            }
        }
    }
}
