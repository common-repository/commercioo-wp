<?php
/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    Commercioo
 * @subpackage Commercioo/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Commercioo
 * @subpackage Commercioo/includes
 * @author     Your Name <email@example.com>
 */
class Commercioo {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Commercioo_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;
	public $commercioo_payment;

	/**
	 * Query instance.
	 *
	 * @var $query
	 */
	public $query = null;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $commercioo    The string used to uniquely identify this plugin.
	 */
	protected $commercioo;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		if ( defined( 'COMMERCIOO_VERSION' ) ) {
			$this->version = COMMERCIOO_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->commercioo = 'commercioo-wp';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_widget();
		$this->define_admin_hooks();
		$this->define_public_hooks();
		$this->load_assets();
	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Commercioo_Loader. Orchestrates the hooks of the plugin.
	 * - Commercioo_i18n. Defines internationalization functionality.
	 * - Commercioo_Admin. Defines all hooks for the admin area.
	 * - Commercioo_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once COMMERCIOO_PATH . 'includes/class-commercioo-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once COMMERCIOO_PATH . 'includes/class-commercioo-i18n.php';

		/**
		 * Include widget.
		 */
        require_once COMMERCIOO_PATH . 'includes/class-commercioo-widget-newproduct.php';
        require_once COMMERCIOO_PATH . 'includes/class-commercioo-widget-featuredproduct.php';
        require_once COMMERCIOO_PATH . 'includes/class-commercioo-widget-best-seller-product.php';
		/**
		 * Include helper class to render template.
		 */
		require_once COMMERCIOO_PATH . 'includes/class-template.php';
        /**
         * Include parsing tags.
         * example: {user_email}
         */
        require_once COMMERCIOO_PATH . 'includes/class-commercioo-parsing-tags.php';
        require_once COMMERCIOO_PATH . 'functions/parsing_tags.php';

        require_once COMMERCIOO_PATH . 'includes/gateways/payment.php';
        require_once COMMERCIOO_PATH . 'includes/gateways/bacs/banktransfer.php';
        require_once COMMERCIOO_PATH . 'includes/gateways/paypal/paypal_standard.php';

        require_once COMMERCIOO_PATH . 'includes/class-commercioo-cart.php';
        require_once COMMERCIOO_PATH . 'includes/class-commercioo-checkout.php';
        require_once COMMERCIOO_PATH . 'includes/class-commercioo-thank-you.php';

		/**
		 * Include all abstraction classes.
		 */
		require_once COMMERCIOO_PATH . 'includes/abstracts/class-post.php';
		require_once COMMERCIOO_PATH . 'includes/abstracts/class-user.php';
		require_once COMMERCIOO_PATH . 'includes/abstracts/class-mailer.php';
		require_once COMMERCIOO_PATH . 'includes/abstracts/class-order-mailer.php';

		/**
		 * Include model classes.
		 */
		require_once COMMERCIOO_PATH . 'includes/models/class-customer.php';
		require_once COMMERCIOO_PATH . 'includes/models/class-order.php';
		require_once COMMERCIOO_PATH . 'includes/models/class-product.php';

		/**
		 * Include mailer classes.
		 */		
		require_once COMMERCIOO_PATH . 'includes/class-default-emails.php';
		require_once COMMERCIOO_PATH . 'includes/emails/class-new-customer.php';
		require_once COMMERCIOO_PATH . 'includes/emails/class-pending-order.php';
		require_once COMMERCIOO_PATH . 'includes/emails/class-processing-order.php';
		require_once COMMERCIOO_PATH . 'includes/emails/class-completed-order.php';
		require_once COMMERCIOO_PATH . 'includes/emails/class-refunded-order.php';
		require_once COMMERCIOO_PATH . 'includes/emails/class-failed-order.php';
		require_once COMMERCIOO_PATH . 'includes/emails/class-canceled-order.php';
		require_once COMMERCIOO_PATH . 'includes/emails/class-new-cs.php';
		require_once COMMERCIOO_PATH . 'includes/emails/class-new-customer-to-admin.php';
		require_once COMMERCIOO_PATH . 'includes/emails/class-new-order-to-admin.php';
		require_once COMMERCIOO_PATH . 'includes/emails/class-forgot-password.php';

		/**
		 * Include page-views class.
		 */
		require_once COMMERCIOO_PATH . 'includes/class-commercioo-page-views.php';
        /**
         * The class responsible for defining all query endpoint that occur in the public-facing
         * side of the site.
         */
        require_once COMMERCIOO_PATH . 'includes/class-commercioo-query.php';
		/**
		 * Include helper class.
		 */
		require_once COMMERCIOO_PATH . 'includes/class-commercioo-helper.php';

		require_once COMMERCIOO_PATH . 'functions/hook.php';

		require_once COMMERCIOO_PATH . 'cli/cpt/core.php';
		require_once COMMERCIOO_PATH . 'cli/cpt/product.php';
		require_once COMMERCIOO_PATH . 'cli/stock-management.php';
		require_once COMMERCIOO_PATH . 'cli/cpt/order.php';
		require_once COMMERCIOO_PATH . 'cli/cpt/order-logs.php';
		require_once COMMERCIOO_PATH . 'cli/cpt/settings.php';
		require_once COMMERCIOO_PATH . 'cli/cpt/statistic.php';
		require_once COMMERCIOO_PATH . 'cli/cpt/users.php';
		require_once COMMERCIOO_PATH . 'cli/cpt/customer.php';
		require_once COMMERCIOO_PATH . 'functions/helper.php';


		require_once COMMERCIOO_PATH . 'cli/core.php';

		/**
		 * The class responsible for TGM Activation
		 */
		require_once COMMERCIOO_PATH . 'includes/class-tgm-plugin-activation.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once COMMERCIOO_PATH . 'admin/paypal.php';
		require_once COMMERCIOO_PATH . 'admin/class-commercioo-recent-orders.php';
		require_once COMMERCIOO_PATH . 'admin/class-commercioo-dashboard-timespan.php';
		require_once COMMERCIOO_PATH . 'admin/class-commercioo-admin.php';
		require_once COMMERCIOO_PATH . 'admin/class-commercioo-customers.php';
		require_once COMMERCIOO_PATH . 'admin/class-commercioo-customizer.php';
		require_once COMMERCIOO_PATH . 'admin/class-commercioo-notification.php';
		require_once COMMERCIOO_PATH . 'includes/class-commercioo-system-status.php';

        require_once COMMERCIOO_PATH . 'admin/class-license-page.php';
        require_once COMMERCIOO_PATH . 'admin/class-commercioo-onboard.php';
        require_once COMMERCIOO_PATH . 'admin/class-required-plugin-page.php';
		require_once COMMERCIOO_PATH . 'includes/wp-bootstrap-pagination.php';
		require_once COMMERCIOO_PATH . 'includes/class-commercioo-powered-by-label.php';
		
		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */

		require_once COMMERCIOO_PATH . 'public/class-commercioo-form-handler.php';
		require_once COMMERCIOO_PATH . 'public/class-commercioo-public.php';

		/**
		 * The class for asset files
		 */
		require_once COMMERCIOO_PATH . 'includes/class-commercioo-assets.php';

		/**
		 * The main REST API class on this plugin
		 * responsible to another API classes for registering and customizing all API related hooks and codes
		 */
        $parsing = Commercioo\Parsing\Commercioo_Parsing_Tags::get_instance();
		$this->query 	= \Commercioo\Query\Commercioo_Query::get_instance();
        $this->loader 	= new Commercioo_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Commercioo_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Commercioo_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$comm_admin = new Commercioo_Admin( $this->get_commercioo(), $this->get_version() );
		
		$this->loader->add_action( 'admin_menu', $comm_admin, 'comm_admin_menu', 10 );
		
		$this->loader->add_action( 'admin_enqueue_scripts', $comm_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $comm_admin, 'enqueue_scripts' );
		$this->loader->add_filter( 'display_post_states', $comm_admin, 'comm_add_display_post_states',10,2 );

		// remove admin bar for role `comm_customer`
		$this->loader->add_action( 'after_setup_theme', $comm_admin, 'remove_admin_bar_for_comm_customer' );

		// register a tab on admin commercioo tabs
		$this->loader->add_filter( 'commercioo_admin_tabs', $comm_admin, 'register_admin_tabs', 10 );

		// payment settings & content
        $this->loader->add_filter( 'comm_payment_general_setting', $comm_admin, 'comm_payment_general_setting' );
        $this->loader->add_filter( 'comm_bank_transfer_setting', $comm_admin, 'comm_bank_transfer_setting' );
        $this->loader->add_filter( 'comm_paypal_setting', $comm_admin, 'comm_paypal_setting' );

        $comm_core = \Commercioo\controller::get_instance();
        $this->loader->add_action( 'rest_api_init', $comm_core, 'comm_api');
        $this->loader->add_action( 'comm_grid_list_content', $comm_core, 'comm_grid_list_content_view',10,2 );

        $product = \commercioo\admin\Comm_Product::get_instance();
        // register post type and the taxonomies if any
		$this->loader->add_action('init', $product, 'register_post_type_and_taxonomies');
		$this->loader->add_filter('rest_pre_insert_comm_product', $product, 'rest_pre_insert', 10, 2);
		$this->loader->add_filter('pre_insert_term', $product, 'pre_insert_term', 10, 2);
		
		// The class responsibe for TGM Plugin Activation hooks
		// $tgmpa = new Commercioo_TGMPA();
		// $this->loader->add_action( 'tgmpa_register', $tgmpa, 'register_recommended_plugins' );

        // Check Status Stock (Addon Pro)
        $this->loader->add_filter('admin/comm_stock', $product, 'comm_stock');

        // register rest fields
        $this->loader->add_action('rest_api_init', $product, 'register_rest_fields');

        $orders = \commercioo\admin\Comm_Order::get_instance();
        // register post type and the taxonomies if any
        $this->loader->add_action('init', $orders, 'register_post_type_and_taxonomies');
        // register post statuses
        $this->loader->add_action('init', $orders, 'register_order_statuses');
        // include all custom statuses
        $this->loader->add_action('pre_get_posts', $orders, 'include_custom_order_statuses');
        // modify data & request before insert
        $this->loader->add_filter('rest_pre_insert_comm_order', $orders, 'rest_pre_insert', 10, 2);
        // modify the response
        $this->loader->add_filter('rest_prepare_comm_order', $orders, 'rest_prepare', 10, 3);
        // register rest fields
        $this->loader->add_action('rest_api_init', $orders, 'register_rest_fields');
        // action on delete order
        $this->loader->add_action('delete_post', $orders, 'on_delete_comm_order');

        $settings= \commercioo\admin\Comm_Settings::get_instance();
        // register rest fields
        $this->loader->add_action('rest_api_init', $settings, 'endpoint_register');
        $this->loader->add_filter('comm_after_saved_settings', $settings, 'comm_after_saved_settings',10,3);

        $statistic= \commercioo\admin\Comm_Statistic::get_instance();
        // register rest fields
        $this->loader->add_action('rest_api_init', $statistic, 'endpoint_register');

        $users = \commercioo\admin\Comm_Users::get_instance();
        $this->loader->add_action('rest_api_init', $users, 'register_rest_fields');
        $this->loader->add_action('show_user_profile', $users, 'add_customer_meta_fields');
        $this->loader->add_action('edit_user_profile', $users, 'add_customer_meta_fields');
        $this->loader->add_action('personal_options_update', $users, 'save_customer_meta_fields');
        $this->loader->add_action('edit_user_profile_update', $users, 'save_customer_meta_fields');
		$this->loader->add_action('edit_user_profile_update', $users, 'save_customer_meta_fields');

		// checkout customizer
		$comm_customizer = new Commercioo_Customizer( $this->get_commercioo(), $this->get_version() );
		$this->loader->add_action( 'customize_register', $comm_customizer, 'checkout_customizer' );
		$this->loader->add_action( 'customize_register', $comm_customizer, 'button_customizer' );
		$this->loader->add_action( 'admin_bar_menu', $comm_customizer, 'admin_bar_checkout_customizer_url', 100 );
		$this->loader->add_action( 'customize_controls_enqueue_scripts', $comm_customizer, 'customize_controls_enqueue_scripts', 100 );
		$this->loader->add_action( 'customize_controls_print_scripts', $comm_customizer, 'customize_controls_print_scripts', 30 );

		// RECENT ORDERS STAT
        $recent_orders_stat = \commercioo\admin\Comm_Recent_Orders::get_instance();
        $this->loader->add_action( 'admin_enqueue_scripts', $recent_orders_stat, 'enqueue_styles' );
        $this->loader->add_action( 'admin_enqueue_scripts', $recent_orders_stat, 'enqueue_scripts');

        // CUSTOMER
        $comm_customers = \commercioo\admin\Comm_Customers::get_instance();
        $customer = \commercioo\admin\Comm_Customer::get_instance();
        // register rest fields
        $this->loader->add_action('rest_api_init', $customer, 'register_rest_fields');

        // DASHBOARD TIMESPAN
        $dashboard_timespan= \commercioo\admin\Comm_Dashboard_Timepan::get_instance();
        // register rest fields
        $this->loader->add_action('rest_api_init', $dashboard_timespan, 'endpoint_register');
        // AJAX CALL
		$this->loader->add_action( 'wp_ajax_comm_recent_orders', $recent_orders_stat, 'comm_recent_orders');
        $this->loader->add_action( 'wp_ajax_comm_customers', $comm_customers, 'comm_customers');

		// commercioo notification
		$notification = new Commercioo_Notification();
		$this->loader->add_action( 'init', $notification, 'init' );
		$this->loader->add_action( 'admin_notices', $notification, 'render_notification' );
		$this->loader->add_action( 'admin_action_commercioo_manage_page', $notification, 'action_manage_page' );
		$this->loader->add_action( 'admin_init', $notification, 'action_update_changelog_init' );
		$this->loader->add_action( 'admin_action_comm_pro_update_changelog', $notification, 'pro_action_update_changelog' );

		// commercioo system status
		$system_status = new Commercioo_System_Status();
		$this->loader->add_action( 'admin_menu', $system_status, 'register_admin_page', 9 );


        /**
         * License Page
         */
        $license_pages = new Commercioo\Admin\License_Page();
        $this->loader->add_action('admin_enqueue_scripts', $license_pages, 'enqueue_styles');
        $this->loader->add_action('admin_enqueue_scripts', $license_pages, 'enqueue_scripts');
        $this->loader->add_action('admin_menu', $license_pages, 'register_admin_page', 10);
        $this->loader->add_action('commercioo/license/content', $license_pages, 'display_license_content');
//        $this->loader->add_filter('commercioo_admin_tabs', $license_pages, 'register_license_page_admin_tabs', 10);
        $this->loader->add_action('wp_ajax_license_page_check_license', $license_pages, 'ctp_action_check_license');
        $this->loader->add_action('wp_ajax_license_page_do_license', $license_pages, 'license_page_do_license');
        $this->loader->add_action('wp_ajax_ctp_action_check_requirement', $license_pages, 'ctp_action_requirement');

        /**
         * Onboarding
         */
        $onboarding = new Commercioo_Onboard();
        $this->loader->add_action('admin_init', $onboarding, 'maybe_redirect_to_onboarding');
        $this->loader->add_action('admin_menu', $onboarding, 'register_menu', 10);
        $this->loader->add_action('admin_enqueue_scripts', $onboarding, 'enqueue_scripts');
        $this->loader->add_filter('http_request_host_is_external', $onboarding, 'allow_custom_host', 10, 3);

        $this->loader->add_action('wp_ajax_comm_onboard_check_account', $onboarding, 'check_account');
        $this->loader->add_action('wp_ajax_comm_onboard_email', $onboarding, 'set_email');
        $this->loader->add_action('wp_ajax_comm_onboard_license', $onboarding, 'check_license');
        $this->loader->add_action('wp_ajax_comm_onboard_install', $onboarding, 'do_install');
        $this->loader->add_action('wp_ajax_comm_onboard_activate', $onboarding, 'do_activate');

        /**
         * Required / Recommended Plugin
         */
        $required_plugin_page = new \Commercioo\Admin\Required_Plugin_Page();
        $this->loader->add_action('admin_enqueue_scripts', $required_plugin_page, 'enqueue_styles');
        $this->loader->add_action('admin_enqueue_scripts', $required_plugin_page, 'enqueue_scripts');
        $this->loader->add_action('admin_menu', $required_plugin_page, 'register_admin_menu_page',10);
        $this->loader->add_action('tgmpa_register', $required_plugin_page, 'register_recommended_plugins');

        $this->loader->add_filter('commercioo/default/tgmpa', $required_plugin_page, 'register_tgmpa_source', 10);

        $this->loader->add_action('wp_ajax_commercioo_required_plugin_installer', $required_plugin_page, 'commercioo_required_plugin_installer'); // Install plugin
        $this->loader->add_action('wp_ajax_commercioo_required_plugin_activation', $required_plugin_page, 'commercioo_required_plugin_activation'); // Activate
        $this->loader->add_action( 'init', $required_plugin_page, 'set_args');
	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new Commercioo_Public( $this->get_commercioo(), $this->get_version() );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );
//        $this->loader->add_filter("the_content", $plugin_public, "display_the_content");
		$this->loader->add_filter( 'commercioo_order_payment_method_thank_you', $plugin_public, 'commercioo_order_payment_method_thank_you',10,2);
		// Custom hook 'commercioo_standalone_checkout_order_summary' is for view order summary on checkout
		$this->loader->add_filter( 'commercioo_standalone_checkout_order_summary', $plugin_public, 'commercioo_standalone_checkout_order_summary' );
        // End of Custom hook 'commercioo_standalone_checkout_order_summary' is for view order summary on checkout
		$this->loader->add_shortcode( 'commercioo_checkout', $plugin_public, 'comm_checkout_shortcode' );
		$this->loader->add_shortcode( 'commercioo_cart', $plugin_public, 'comm_cart_shortcode' );
		$this->loader->add_shortcode( 'commercioo_product', $plugin_public, 'comm_product_archive_shortcode' );
		$this->loader->add_shortcode( 'commercioo_account', $plugin_public, 'comm_account_shortcode' );
		$this->loader->add_shortcode( 'commercioo_thank_you', $plugin_public, 'commercioo_thank_you');

		/** 
		 * Fix the `rest_url` error while ssl on oncertain servers
		 * Related discussion: https://core.trac.wordpress.org/ticket/36451		 * 
		 */
		$this->loader->add_filter( 'rest_url', $plugin_public, 'rest_url_patch_with_ssl_on' );

        // submit actions for checkout form
        $commercioo_checkout = \Commercioo\Checkout::get_instance();
        // Update Post Meta
		$this->loader->add_action( 'commercioo_update_post_meta', $commercioo_checkout, 'commercioo_update_post_meta',9,2 );
        // End of Update Post Meta
		$this->loader->add_action( 'admin_post_nopriv_commercioo_checkout', $commercioo_checkout, 'do_checkout' );
		$this->loader->add_action( 'admin_post_commercioo_checkout', $commercioo_checkout, 'do_checkout' );

		$this->loader->add_filter( 'comm_product_archive', $plugin_public, 'comm_product_archive',10,2 );

		$this->loader->add_action( 'wp_head', $plugin_public, 'comm_wp_head',99 );
		$this->loader->add_action( 'wp_footer', $plugin_public, 'comm_wp_footer',99 );

        $template = \Commercioo\Template::get_instance();
        $this->loader->add_filter('template_include',$template, 'template_loader');
		$this->loader->add_action('after_setup_theme',$template, 'setup_environment');
		
		// page-views recorder
		$page_views = new \Commercioo\Page_Views;
		$this->loader->add_action( 'template_redirect', $page_views, 'page_views_recorder' );

		//Payment method
		$this->loader->add_action( 'comm_payment_method', $plugin_public, 'payment_method' );
		$this->loader->add_filter( 'comm_payment_method', $plugin_public, 'payment_method' );
        $commercioo_thank_you = \Commercioo\Thank_You::get_instance();
        $this->loader->add_action( 'admin_post_nopriv_commercioo_confirmation_payment', $commercioo_thank_you, 'do_confirmation_payment' );
        $this->loader->add_action( 'admin_post_commercioo_confirmation_payment', $commercioo_thank_you, 'do_confirmation_payment' );
    }

    /**
     * Register all of the hooks related to the widget area functionality
     * of the plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_widget() {
        Commercioo_Widget_New_Product::instance();
        Commercioo_Widget_Featured_Product::instance();
        Commercioo_Widget_Best_Seller_Product::instance();
    }

	private function load_assets() {
		$assets = new \Commercioo\Assets;
		// let this plugin load assets first before other addon
		$this->loader->add_action( 'wp_enqueue_scripts', $assets, 'register_public_assets', 5 );
		$this->loader->add_action( 'admin_enqueue_scripts', $assets, 'register_admin_assets', 5 );
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_commercioo() {
		return $this->commercioo;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Commercioo_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

}
