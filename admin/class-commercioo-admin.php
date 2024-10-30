<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    Commercioo
 * @subpackage Commercioo/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Commercioo
 * @subpackage Commercioo/admin
 * @author     Commercioo Team
 */
class Commercioo_Admin {

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
    private $controller;

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     * @param      string $commercioo The name of this plugin.
     * @param      string $version The version of this plugin.
     */
    public function __construct($commercioo, $version) {

        $this->commercioo = $commercioo;
        $this->version = $version;
        $this->controller = \Commercioo\controller::get_instance();

        add_action("comm_admin_sub_menu", [$this, "comm_admin_sub_menu_view"], 10, 2);
        add_action('update_database_changelog', array($this, 'update_database_changelog'));
    }

    public function comm_add_display_post_states($post_states, $post) {
        if (get_option('commercioo_Checkout_page_id') == $post->ID) {
            $post_states['commercioo_page_for_checkout'] = __('Commercioo Checkout Page', 'commercioo_page');
        }
        if (get_option('commercioo_Cart_page_id') == $post->ID) {
            $post_states['commercioo_page_for_cart'] = __('Commercioo Cart Page', 'commercioo_page');
        }
        if (get_option('commercioo_Product_page_id') == $post->ID) {
            $post_states['commercioo_page_for_product'] = __('Commercioo Product Archive Page', 'commercioo_page');
        }
        if (get_option('commercioo_Account_page_id') == $post->ID) {
            $post_states['commercioo_page_for_account'] = __('Commercioo Account Page', 'commercioo_page');
        }
        if (get_option('commercioo_thank_you_page_id') == $post->ID) {
            $post_states['commercioo_page_for_thank_you'] = __('Commercioo Thank You Page', 'commercioo_page');
        }
        return $post_states;
    }

    public function comm_admin_sub_menu_view($comm_admin_menu, $comm_admin_menu_addon = []) {
        if (count($comm_admin_menu_addon) > 0) {
            $comm_admin_menu = array_merge_recursive($comm_admin_menu, $comm_admin_menu_addon);
            if (is_array($comm_admin_menu)) {
                foreach ($comm_admin_menu as $k => $vMenu) {
                    add_submenu_page(
                        (isset($vMenu['comm_sub_void']))? $vMenu['comm_sub_void']:'',
                        $vMenu['page_tile'],
                        $vMenu['menu_tile'],
                        $vMenu['capability'],
                        $vMenu['menu_slug'],
                        $vMenu['function']
                    );
                }
            }
        } else {
            if (is_array($comm_admin_menu)) {
                foreach ($comm_admin_menu as $k => $vMenu) {
                    add_submenu_page(
                        (isset($vMenu['comm_sub_void']))? $vMenu['comm_sub_void']:'',
                        $vMenu['page_tile'],
                        $vMenu['menu_tile'],
                        $vMenu['capability'],
                        $vMenu['menu_slug'],
                        $vMenu['function']
                    );
                }
            }
        }

    }

    public function comm_admin_menu() {
		add_submenu_page( 
			'comm-system-status', 
			__( 'Store Dashboard', 'commercioo' ),
            __( 'Store Dashboard', 'commercioo' ),
			'manage_commercioo',
			'comm_dashboard',
			array( $this->controller, 'comm_menu_callback' ),
			20
		);

        $comm_admin_sub_menu = $this->controller->comm_sub_menu_filter();
        do_action( "comm_admin_sub_menu", $comm_admin_sub_menu, array() );		
    }

    /**
     * Register the stylesheets for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_styles() {

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
        if ( $this->controller->is_comm_page() ) {
            /* Commercioo Styles */
            wp_enqueue_style( 'commercioo-main' );

            if ( $this->controller->is_comm_page( ['admin_page_comm_dashboard', 'commercioo_page_comm_dashboard'] ) ) {
                wp_enqueue_style('commercioo-statistics' );
            }
            if ( $this->controller->is_comm_page( ['admin_page_comm_prod'] ) ) {
                wp_enqueue_style( 'commercioo-products' );
            }
            if ( $this->controller->is_comm_page( ['admin_page_comm_category'] ) ) {
                wp_enqueue_style( 'commercioo-category' );
            }
            if ( $this->controller->is_comm_page( ['admin_page_comm_tags'] ) ) {
                wp_enqueue_style( 'commercioo-tag' );
            }
            if ( $this->controller->is_comm_page( ['admin_page_comm_order'] ) ) {
                wp_enqueue_style( 'commercioo-orders' );
            }
            if ( $this->controller->is_comm_page( ['admin_page_comm_customers'] ) ) {
                wp_enqueue_style( 'commercioo-customer' );
            }
            if ( $this->controller->is_comm_page( ['admin_page_comm_settings'] ) ) {
                wp_enqueue_style( 'commercioo-settings' );
            }
            if ( $this->controller->is_comm_page( ['admin_page_comm_statistics'] ) ) {
                wp_enqueue_style( 'commercioo-statistics' );
            }
        }
        wp_enqueue_style( $this->commercioo, COMMERCIOO_URL . 'admin/css/commercioo-admin.css', array(), $this->version, 'all' );


    }

    /**
     * Register the JavaScript for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts() {

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
        wp_enqueue_script( $this->commercioo, COMMERCIOO_URL . 'admin/js/commercioo-admin.js', array('jquery'), $this->version, false );

        if ( $this->controller->is_comm_page() ) {
            /* Built in */
            wp_enqueue_editor();
            wp_enqueue_media();
            wp_localize_script( 'jquery', 'commlang', $this->controller->get_translation( 'admin' ) );
            /* Commercioo Scripts */
            wp_enqueue_script( 'commercioo-main' );

            if ( $this->controller->is_comm_page( ['admin_page_comm_dashboard', 'commercioo_page_comm_dashboard'] ) ) {
                 wp_enqueue_script( 'commercioo-dashboard-stats' );
            }
            if ( $this->controller->is_comm_page( ['admin_page_comm_prod'] ) ) {
                wp_enqueue_script( 'commercioo-product' );
            }
            if ( $this->controller->is_comm_page( ['admin_page_comm_category'] ) ) {
                wp_enqueue_script( 'commercioo-category' );
            }
            if ( $this->controller->is_comm_page( ['admin_page_comm_tags'] ) ) {
                wp_enqueue_script( 'commercioo-tag', COMMERCIOO_URL . 'admin/js/commercioo-tag.js', array( 'jquery' ), $this->version, true );
            }
            if ($this->controller->is_comm_page(['admin_page_comm_order'])) {
                wp_enqueue_script( 'commercioo-orders' );
            }
            if ( $this->controller->is_comm_page( ['admin_page_comm_customers'] ) ) {
                wp_enqueue_script( 'commercioo-customer' );
            }
            if ( $this->controller->is_comm_page( ['admin_page_comm_settings'] ) ) {
                wp_enqueue_script( 'commercioo-settings' );
            }
            if ( $this->controller->is_comm_page( ['admin_page_comm_statistics'] ) ) {
                wp_enqueue_script( 'commercioo-statistics' );
            }

            // add inline script
            wp_localize_script( 'commercioo-form', 'cApiSettings', $this->controller->process_data() );
        }
    }   

	/**
	 * Remove admin bar for role `comm_customer`
	 * 
	 * @since    0.2.0
	 */
	public function remove_admin_bar_for_comm_customer() {
		if ( current_user_can( 'comm_customer' ) && ! is_admin() ) {
			show_admin_bar(false);
		}
	}

	/**
	 * Register a tab on admin commercioo tabs
	 * 
	 * @since    1.0.0
	 */
	public function register_admin_tabs( $tabs ) {
        $content = array();
        if(has_filter("commercioo/license/plugins/check-content")){
            $content = apply_filters("commercioo/license/plugins/check-content",array());
        }
        if(has_filter("commercioo/license/theme/check-content")){
            $content = apply_filters("commercioo/license/theme/check-content",array());
        }

        $tabs[] = array(
            'url'     => admin_url( 'admin.php?page=comm_dashboard' ),
            'label'   => __( 'Store Dashboard', 'commercioo' ),
            'page_id' => null,
        );

	    if(current_user_can("administrator")) {
            if($content) {
                $tabs[] = array(
                    'url' => admin_url('admin.php?page=comm-license'),
                    'label' => __('Licenses', 'commercioo'),
                    'page_id' => 'commercioo_page_comm-license',
                );
            }
            $tabs[] = array(
                'url' => admin_url('admin.php?page=comm_onboard'),
                'label' => __('Onboarding', 'commercioo'),
                'page_id' => 'commercioo_page_comm_onboard',
            );
            $tabs[] = array(
                'url' => admin_url('admin.php?page=comm_required_plugin'),
                'label' => __('Required / Recommended Plugins', 'commercioo'),
                'page_id' => 'commercioo_page_comm_required_plugin',
            );
        }
		return $tabs;
	}
    /**
     * Render setting section for general setting on setting payment tab
     */
    public function comm_payment_general_setting($comm_options)
    {
        ob_start();
        include COMMERCIOO_PATH . "/admin/partials/payment/content-general-settings.php";
        $content = ob_get_clean();
        return $content;
    }
    /**
     * Render setting section for bank transfer on setting payment tab
     */
    public function comm_bank_transfer_setting($comm_options)
    {
        ob_start();
        include COMMERCIOO_PATH . "/admin/partials/payment/content-bank-transfer.php";
        $content = ob_get_clean();
        return $content;
    }
    /**
     * Render setting section for bank transfer on setting payment tab
     */
    public function comm_paypal_setting($comm_options)
    {
        ob_start();
        include COMMERCIOO_PATH . "/admin/partials/payment/content-paypal.php";
        $content = ob_get_clean();
        return $content;
    }

    /**
     * Update Database Changelog
     * @param string $item_name
     */
    public function update_database_changelog($item_name=''){
        global $wpdb;

        $orders = comm_controller()->comm_get_result_data
        (['comm_pending', 'comm_processing', 'comm_completed', 'comm_refunded','comm_abandoned','comm_failed','trash'],
            "comm_order", 'ID');

        // Make sure query has result.
        if (!empty($orders)) {
            foreach ($orders as $k_order => $data_order){
                $order_id = $data_order->ID;
                $order_list = get_post_meta($order_id,"_order_items",true);
                $order_items = array();
                if(!empty($order_list)){
                    foreach ($order_list as $value_item) {
                        $product_id = $value_item['product_id'];
                        $order_items['items'][$product_id] = array(
                            'item_name' => $value_item['item_name']
                        );
                        if(isset($value_item['price'])){
                            $order_items['items'][$product_id]['price'] = $value_item['price'];
                        }
                        if(isset($value_item['item_price'])){
                            $order_items['items'][$product_id]['price'] = $value_item['item_price'];
                        }
                        $order_items['items'][$product_id]['item_order_qty'] =$value_item['item_order_qty'];
                        $order_items['items'][$product_id]['product_id'] =$product_id;
                        $order_items['items'][$product_id]['sales_price'] =0;
                        if(isset($value_item['sales_price'])) {
                            if ($value_item['sales_price'] > 0){
                                $order_items['items'][$product_id]['sales_price'] = $value_item['sales_price'];
                            }
                        }
                        $order_items['items'][$product_id]['variation_id'] =$value_item['variation_id'];
                        $order_items['items'][$product_id]['is_variation'] =0;
                        if($value_item['variation_id']>0){
                            $order_items['items'][$product_id]['is_variation'] =1;
                        }
                    }
                    update_post_meta($order_id,'_order_cart',$order_items);
                }
            }
        }
    }
}
