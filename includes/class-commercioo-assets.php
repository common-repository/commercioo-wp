<?php
/**
 * Commercioo model class for order.
 *
 * @author Commercioo Team
 * @package Commercioo
 */

namespace Commercioo;

if ( ! defined( 'WPINC' ) ) {
	exit;
}

if ( ! class_exists( 'Commercioo\Assets' ) ) {

	/**
	 * Class Cart
	 *
	 * @package Commercioo
	 */
	class Assets {

        /**
         * Bootstrap version
         *
         * @var string
         */
        private static $bootstrap_version = '';

        /**
         * Owl Carousel version
         *
         * @var string
         */
        private static $owl_carousel_version = '';

        /**
         * Feather Icon version
         *
         * @var string
         */
        private static $eather_version = '';

        /**
         * Popper version
         *
         * @var string
         */
        private static $popper_version = '';

        /**
         * Register assets for front end
         *
         * @return void
         */
        public function register_public_assets() {
            // vendor scripts
            wp_register_script( 'owl-carousel', COMMERCIOO_URL . 'public/js/vendor/owl.carousel.min.js', array(), '2.3.4' );
            wp_register_script( 'feather', COMMERCIOO_URL . 'public/js/vendor/feather.min.js',array(),COMMERCIOO_VERSION );
            wp_register_script( 'bootstrap', COMMERCIOO_URL . 'public/js/vendor/bs5/bootstrap.bundle.min.js',array(), '5.1.1' );
            wp_register_script( 'jquery-block', COMMERCIOO_URL . 'public/js/vendor/jquery.blockUI.js', array( 'jquery' ), COMMERCIOO_VERSION, true );
            wp_register_script( 'jquery-toast', COMMERCIOO_URL . 'public/js/vendor/toast/jquery.toast.js', array( 'jquery' ), COMMERCIOO_VERSION, true );
            wp_register_script( 'semantic-ui', COMMERCIOO_URL . 'public/js/vendor/semantic/semantic.min.js', array( 'jquery' ), COMMERCIOO_VERSION, true );

            // vendor styles
            wp_register_style( 'font-awesome', COMMERCIOO_URL . 'public/css/vendor/font-awesome.css', array(), COMMERCIOO_VERSION,'all' );
            wp_register_style( 'owl-carousel', COMMERCIOO_URL . 'public/css/vendor/owl.carousel.min.css', array(), '2.3.4','all' );
            wp_register_style( 'bootstrap', COMMERCIOO_URL . 'public/css/vendor/bs5/bootstrap.min.css', array(), '5.1.1', 'all' );
            wp_register_style( 'jquery-toast', COMMERCIOO_URL . 'public/css/vendor/toast/jquery.toast.css', array(), COMMERCIOO_VERSION, 'all');
            wp_register_style( 'semantic-ui', COMMERCIOO_URL . 'public/css/vendor/semantic/semantic.min.css', array(), COMMERCIOO_VERSION, 'all');

            // Commercioo Script
            wp_register_script( 'commercioo-gallery', COMMERCIOO_URL . 'public/js/product/gallery.js',array(),COMMERCIOO_VERSION );
            wp_register_script( 'commercioo-single-product', COMMERCIOO_URL . 'public/js/product/single-product.js',array(),COMMERCIOO_VERSION );
            wp_register_script( 'commercioo-product-archive', COMMERCIOO_URL . 'public/js/product/product-archive.js',array(),COMMERCIOO_VERSION );
            wp_register_script( 'commercioo-checkout', COMMERCIOO_URL . 'public/js/checkout/checkout-standalone.js', array( 'jquery' ), COMMERCIOO_VERSION, true );
            wp_register_script( 'commercioo-cart', COMMERCIOO_URL . 'public/js/cart/cart-page.js', array( 'jquery', 'feather' ), COMMERCIOO_VERSION );
            wp_register_script( 'commercioo-thank-you', COMMERCIOO_URL . 'public/js/thank_you/thankyou-standalone.js', array( 'jquery' ), COMMERCIOO_VERSION, true );
            wp_register_script( 'commercioo-public', COMMERCIOO_URL . 'public/js/commercioo-public.js', array( 'jquery', 'commercioo-cart' ), COMMERCIOO_VERSION, true );

            // Commercioo Styles
            wp_register_style( 'commercioo-archive', COMMERCIOO_URL . 'public/css/gallery-archive.css', array(), COMMERCIOO_VERSION, 'all' );
            wp_register_style( 'commercioo-product', COMMERCIOO_URL . 'public/css/commercio-product.css', array(), COMMERCIOO_VERSION, 'all' );
            wp_register_style( 'commercioo-gallery', COMMERCIOO_URL . 'public/css/gallery.css', array(), COMMERCIOO_VERSION, 'all' );
            wp_register_style( 'commercioo-checkout-container', COMMERCIOO_URL . 'public/css/commercioo-checkout-container.css', array( 'font-awesome' ), COMMERCIOO_VERSION, 'all' );
            wp_register_style( 'commercioo-checkout', COMMERCIOO_URL . 'public/css/commercioo-checkout-standalone.css', array( 'font-awesome' ), COMMERCIOO_VERSION, 'all' );
            wp_register_style( 'commercioo-cart', COMMERCIOO_URL . 'public/css/commercioo-wishlist.css', array(), COMMERCIOO_VERSION, 'all' );
            wp_register_style( 'commercioo-login', COMMERCIOO_URL . 'public/css/commercioo-login.css', array(), COMMERCIOO_VERSION, 'all' );
            wp_register_style( 'commercioo-thank-you-billing-shipping', COMMERCIOO_URL . 'public/css/thank_you/thankyou-billing-shipping.css', array(), COMMERCIOO_VERSION, 'all' );
            wp_register_style( 'commercioo-thank-you-information', COMMERCIOO_URL . 'public/css/thank_you/thankyou-information.css', array(), COMMERCIOO_VERSION, 'all' );
            wp_register_style( 'commercioo-thank-you-order-summary', COMMERCIOO_URL . 'public/css/thank_you/thankyou-order-summary.css', array(), COMMERCIOO_VERSION, 'all' );
            wp_register_style( 'commercioo-thank-you-confirmation', COMMERCIOO_URL . 'public/css/thank_you/thankyou-confirmation.css', array(), COMMERCIOO_VERSION, 'all' );
            wp_register_style( 'commercioo-thank-you-two-column', COMMERCIOO_URL . 'public/css/thank_you/thank-you-two-column.css', array(), COMMERCIOO_VERSION, 'all' );
            wp_register_style( 'commercioo-thank-you-one-column', COMMERCIOO_URL . 'public/css/thank_you/thank-you-one-column.css', array(), COMMERCIOO_VERSION, 'all' );
            wp_register_style( 'commercioo-thank-you', COMMERCIOO_URL . 'public/css/commercioo-thankyou-standalone.css', 
                array( 'font-awesome', 
                    'commercioo-thank-you-confirmation', 
                    'commercioo-thank-you-order-summary', 
                    'commercioo-thank-you-information', 
                    'commercioo-thank-you-billing-shipping' 
                ), COMMERCIOO_VERSION, 'all' );
        // register style powered-by-label
        wp_register_style( 'commercioo-powered-by-label', COMMERCIOO_URL. 'public/css/powered-by-label.css', array(), COMMERCIOO_VERSION, 'all' );
        }

        /**
         * Register assets for back end
         *
         * @return void
         */
        public function register_admin_assets() {
            /** Vendor assets */
            wp_register_style( 'bootstrap', COMMERCIOO_URL . 'public/css/vendor/bs5/bootstrap.min.css', array(), '5.1.1', 'all' );
            wp_register_style( 'bootstrap-colorpicker', COMMERCIOO_URL . 'admin/css/vendor/bootstrap-colorpicker.min.css', array(), COMMERCIOO_VERSION, 'all' );
            wp_register_style( 'jquery-toast', COMMERCIOO_URL . 'public/css/vendor/toast/jquery.toast.css', array(), COMMERCIOO_VERSION, 'all');
            wp_register_style( 'datatable', COMMERCIOO_URL . 'admin/css/vendor/datatables.css', array(), COMMERCIOO_VERSION, 'all');
            wp_register_style( 'font-awesome', COMMERCIOO_URL . 'public/css/vendor/font-awesome.css', array(), COMMERCIOO_VERSION, 'all' );
            wp_register_style( 'sweetalert2', COMMERCIOO_URL . 'admin/css/vendor/sweetalert2.css', array(), COMMERCIOO_VERSION, 'all' );
            wp_register_style( 'select2', COMMERCIOO_URL . 'admin/css/vendor/select2.min.css', array(), COMMERCIOO_VERSION, 'all');
            wp_register_style( 'daterangepicker', COMMERCIOO_URL . 'admin/css/vendor/daterangepicker.css', array(), COMMERCIOO_VERSION, 'all' );
            wp_register_style( 'contextmenu', COMMERCIOO_URL . 'admin/css/vendor/contextmenu/jquery.contextMenu.min.css', array(), COMMERCIOO_VERSION, 'all' );

            /** Commercioo Styles */
            wp_register_style( 'commercioo-order-status', COMMERCIOO_URL . 'public/css/commercioo-order-status.css', array(), COMMERCIOO_VERSION, 'all' );
            wp_register_style( 'commercioo-component', COMMERCIOO_URL . 'admin/css/component.css', array(), COMMERCIOO_VERSION, 'all' );
            wp_register_style( 'commercioo-global', COMMERCIOO_URL . 'admin/css/commercioo-global.css', array(), COMMERCIOO_VERSION, 'all' );
            wp_register_style( 'commercioo-main', COMMERCIOO_URL . 'admin/css/commercioo-main.css', 
                array( 'wp-color-picker', 'bootstrap', 'jquery-toast', 'datatable', 'font-awesome', 'sweetalert2', 'select2', 'daterangepicker', 'contextmenu', 
                'commercioo-component', 'commercioo-global', 'commercioo-order-status' ), 
            COMMERCIOO_VERSION, 'all' );

            /** Page related styles */
            wp_register_style( 'commercioo-statistics', COMMERCIOO_URL . 'admin/css/commercioo-statistics.css', array(), COMMERCIOO_VERSION, 'all' );
            wp_register_style( 'commercioo-products', COMMERCIOO_URL . 'admin/css/commercioo-products.css', array(), COMMERCIOO_VERSION, 'all' );
            wp_register_style( 'commercioo-category', COMMERCIOO_URL . 'admin/css/commercioo-category.css', array(), COMMERCIOO_VERSION, 'all' );
            wp_register_style( 'commercioo-tag', COMMERCIOO_URL . 'admin/css/commercioo-tag.css', array(), COMMERCIOO_VERSION, 'all' );
            wp_register_style( 'commercioo-orders', COMMERCIOO_URL . 'admin/css/commercioo-orders.css', array(), COMMERCIOO_VERSION, 'all' );
            wp_register_style( 'commercioo-recent-orders', COMMERCIOO_URL . 'admin/css/commercioo-recent-orders.css', array(), COMMERCIOO_VERSION, 'all' );
            wp_register_style( 'commercioo-customer', COMMERCIOO_URL . 'admin/css/commercioo-customer.css', array( 'commercioo-recent-orders' ), COMMERCIOO_VERSION, 'all' );
            wp_register_style( 'commercioo-settings', COMMERCIOO_URL . 'admin/css/commercioo-settings.css', array( 'commercioo-orders', 'bootstrap-colorpicker' ), COMMERCIOO_VERSION, 'all' );

            /* Vendor scripts */
            wp_register_script( 'popper', COMMERCIOO_URL . 'admin/js/vendor/popper.min.js', array( 'jquery' ), COMMERCIOO_VERSION, true );
            wp_register_script( 'bootstrap', COMMERCIOO_URL . 'public/js/vendor/bs5/bootstrap.bundle.min.js', array( 'jquery', 'popper' ), COMMERCIOO_VERSION, true );
            wp_register_script( 'jquery-toast', COMMERCIOO_URL . 'public/js/vendor/toast/jquery.toast.js', array( 'jquery' ), COMMERCIOO_VERSION, true );
            wp_register_script( 'datatable', COMMERCIOO_URL . 'admin/js/vendor/datatables.js', array( 'jquery' ), COMMERCIOO_VERSION, true );
            wp_register_script( 'sweetalert2', COMMERCIOO_URL . 'admin/js/vendor/sweetalert2/sweetalert2.all.min.js', array( 'jquery' ), COMMERCIOO_VERSION, true );
            wp_register_script( 'feather', COMMERCIOO_URL . 'admin/js/vendor/feather.min.js', array( 'jquery' ), COMMERCIOO_VERSION, true );
            wp_register_script( 'select2', COMMERCIOO_URL . 'admin/js/vendor/select2.full.min.js', array( 'jquery' ), COMMERCIOO_VERSION, true );
            wp_register_script( 'moment', COMMERCIOO_URL . 'admin/js/vendor/moment.min.js', array( 'jquery' ), COMMERCIOO_VERSION, true );
            wp_register_script( 'daterangepicker', COMMERCIOO_URL . 'admin/js/vendor/daterangepicker.js', array( 'jquery' ), COMMERCIOO_VERSION, true );
            wp_register_script( 'chart', COMMERCIOO_URL . 'admin/js/vendor/chartjs/chart.min.js', array( 'jquery' ), COMMERCIOO_VERSION, true );
            wp_register_script( 'ui-position', COMMERCIOO_URL . 'admin/js/vendor/contextmenu/jquery.ui.position.min.js', array( 'jquery' ), COMMERCIOO_VERSION, true );
            wp_register_script( 'contextmenu', COMMERCIOO_URL . 'admin/js/vendor/contextmenu/jquery.contextMenu.min.js', array( 'jquery', 'ui-position' ), COMMERCIOO_VERSION, true );
            wp_register_script( 'iconify', COMMERCIOO_URL . 'admin/js/vendor/iconify.min.js', array( 'jquery' ), COMMERCIOO_VERSION, true );

            /* Commercioo Scripts */
            wp_register_script( 'commercioo-form', COMMERCIOO_URL . 'admin/js/commercioo-form.js', array( 'jquery' ), COMMERCIOO_VERSION, true );
            wp_register_script( 'commercioo-main', COMMERCIOO_URL . 'admin/js/commercioo-main.js', array( 
                'jquery', 'bootstrap', 'jquery-toast', 'datatable', 'sweetalert2', 'feather', 'select2', 'moment', 'daterangepicker','chart','contextmenu', 'commercioo-form' ), COMMERCIOO_VERSION, true );

            /** Page related Scripts */
            wp_register_script( 'commercioo-dashboard-stats', COMMERCIOO_URL . 'admin/js/commercioo-dashboard-timespan.js', array( 'jquery' ), COMMERCIOO_VERSION, true );
            wp_register_script( 'commercioo-product', COMMERCIOO_URL . 'admin/js/commercioo-products.js',
                array( 'jquery' ), COMMERCIOO_VERSION, true );
            wp_register_script( 'commercioo-category', COMMERCIOO_URL . 'admin/js/commercioo-category.js',
                array( 'jquery' ), COMMERCIOO_VERSION, true);
            wp_register_script( 'commercioo-tag', COMMERCIOO_URL . 'admin/js/commercioo-tag.js', array( 'jquery' ), COMMERCIOO_VERSION, true );
            wp_register_script( 'commercioo-orders', COMMERCIOO_URL . 'admin/js/commercioo-orders.js', array( 'jquery', 'iconify' ), COMMERCIOO_VERSION, true );
            wp_register_script( 'commercioo-customer', COMMERCIOO_URL . 'admin/js/commercioo-customers.js', array( 'jquery' ), COMMERCIOO_VERSION, true );
            wp_register_script( 'commercioo-settings', COMMERCIOO_URL . 'admin/js/commercioo-settings.js', array( 'wp-color-picker', 'jquery' ), COMMERCIOO_VERSION, true );
            wp_register_script( 'commercioo-statistics', COMMERCIOO_URL . 'admin/js/commercioo-statistics-page.js', array( 'jquery' ), COMMERCIOO_VERSION, true );
        }
    }
}