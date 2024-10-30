<?php
/**
 * Simple helper class to render php file into output buffer
 *
 * @author  Commercioo Team
 * @package Commercioo
 */

namespace Commercioo;

if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('Commercioo\Template')) {

    /**
     * Class Template
     *
     * @package Wacara
     */
    class Template
    {

        /**
         * Folder name variable
         *
         * @var string
         */
        private static $folder = '';
        protected static $_instance = null;
        public static function get_instance() {
            if ( is_null( self::$_instance ) ) {
                self::$_instance = new self();
            }
            return self::$_instance;
        }

        public static function template_loader($template)
        {
            global $wp;
            if (is_embed()) {
                return $template;
            }
            self::set_default_folder();
            $default_file = self::get_template_loader_default_file();

            // check if page is account page
            if ( is_page( get_option( 'commercioo_Account_page_id' ) ) && ! self::is_template_override_exists( $default_file, true ) ) {
                return $template;
            }
            
            if ($default_file) {
                /**
                 * Filter hook to choose which files to find before Commercioo does it's own logic.
                 *
                 * @since 3.0.0
                 * @var array
                 */
                $search_files = self::get_template_loader_files($default_file);
                $template = locate_template($search_files);
                
                if (!$template) {
                    $template = self::$folder . '/' . $default_file;
                }
            }
            return $template;
        }
        
        /**
         * Ensure theme and server variable compatibility and setup image sizes.
         */
        public function setup_environment() {
            /**
             * COMM_TEMPLATE_PATH constant.
             *
             * @deprecated 2.2 Use comm_template_path() instead.
             */
            $this->define( 'COMM_TEMPLATE_PATH', comm_template_path());
        }

        /**
         * Define constant if not already set.
         *
         * @param string      $name  Constant name.
         * @param string|bool $value Constant value.
         */
        private function define( $name, $value ) {
            if ( ! defined( $name ) ) {
                define( $name, $value );
            }
        }

        /**
         * Get an array of filenames to search for a given template.
         *
         * @since  3.0.0
         * @param  string $default_file The default file name.
         * @return string[]
         */
        private static function get_template_loader_files($default_file)
        {
            $templates = apply_filters('commercioo_template_loader_files', array(), $default_file);

            if (is_page_template()) {
                $page_template = get_page_template_slug();

                if ($page_template) {
                    $validated_file = validate_file($page_template);
                    if (0 === $validated_file) {
                        $templates[] = $page_template;
                    } else {
                        error_log("Commercioo: Unable to validate template path: \"$page_template\". Error Code: $validated_file.");
                    }
                }
            }

            if (is_singular('comm_product')) {
                $object = get_queried_object();
                $name_decoded = urldecode($object->post_name);
                if ($name_decoded !== $object->post_name) {
                    $templates[] = "single-product-{$name_decoded}.php";
                }
                $templates[] = "single-product-{$object->post_name}.php";
            }

            if (is_comm_product_taxonomy()) {
                $object = get_queried_object();
                $templates[] = 'taxonomy-' . $object->taxonomy . '-' . $object->slug . '.php';
                $templates[] = comm_template_path() . 'taxonomy-' . $object->taxonomy . '-' . $object->slug . '.php';
                $templates[] = 'taxonomy-' . $object->taxonomy . '.php';
                $templates[] = comm_template_path() . 'taxonomy-' . $object->taxonomy . '.php';
            }

            $templates[] = $default_file;
            $templates[] = comm_template_path() . $default_file;
            
            return array_unique($templates);
        }

        /**
         * Get the default filename for a template.
         *
         * @since  3.0.0
         * @return string
         */
        private static function get_template_loader_default_file()
        {
            if (is_singular('comm_product')) {
                $default_file = 'single-comm_product.php';
            } elseif (is_comm_product_taxonomy()) {
                $object = get_queried_object();
                if (is_tax('comm_product_cat') || is_tax('comm_product_tag')) {
                    $default_file = 'taxonomy-' . $object->taxonomy . '.php';
                } else {
                    $default_file = 'archive-comm_product.php';
                }
            } elseif (is_post_type_archive('comm_product') || is_page(get_option('commercioo_Product_page_id'))) {
                $default_file = 'archive-comm_product.php';
            } elseif ( is_page( get_option('commercioo_Cart_page_id') ) && self::is_template_override_exists( 'cart' ) ) {
                $default_file = 'cart.php';
            } elseif ( is_page( get_option( 'commercioo_Account_page_id' ) ) ) {
                global $wp;
                // get sub menus
                $submenus = comm_get_account_menus();
                foreach ( $submenus as $subpage ) {
                    if ( isset( $wp->query_vars ) && isset( $wp->query_vars[$subpage] ) ) {
                        $default_file = "account/{$subpage}.php";
                        break;
                    } else {
                        $default_file = 'account/account.php';
                    }
                }
            } else {
                $default_file = '';
            }
            return $default_file;
        }


        private static function write_log($log) {
            if (true === WP_DEBUG) {
                if (is_array($log) || is_object($log)) {
                    error_log(print_r($log, true));
                } else {
                    error_log($log);
                }
            }
        }

        /**
         * Set template folder
         */
        private static function set_default_folder()
        {
            $folder = COMMERCIOO_PATH . '/templates';

            if (!self::$folder) {

                self::do_set_folder($folder);
            }
        }

        /**
         * Save folder path.
         *
         * @param string $path the folder path.
         */
        private static function do_set_folder($path)
        {
            self::$folder = rtrim($path, '/');
        }

        /**
         * Check template file existence
         *
         * @param string $file_name template file name.
         *
         * @return bool|string
         */
        private static function find_template($file_name)
        {
            $found = false;

            // Set default folder.
            self::set_default_folder();

            // Check file in plugin.
            $file = self::$folder . "/{$file_name}.php";
            if (file_exists($file)) {
                $found = $file;
            }

            return $found;
        }

        /**
         * Render the template
         *
         * @param string $template template file path.
         * @param array $variables variables that will be injected into template file.
         *
         * @return string
         */
        private static function render_template($template, $variables = array()) {
            ob_start();
            foreach ($variables as $key => $value) {
                ${$key} = $value;
            }
            include $template;

            return ob_get_clean();
        }

        /**
         * Render the template
         *
         * @param string $file_name template file name.
         * @param array $variables variables that will be injected into template file.
         * @param bool $echo whether display as variable or display in browser.
         *
         * @return void|string
         */
        public static function render($file_name, $variables = array(), $echo = false)
        {			
            $template = self::find_template($file_name);
            $output = '';
            if ($template) {
                $output = self::render_template($template, $variables);
            }

            if ($echo) {
                echo $output; // phpcs:ignore
            } else {
                return $output;
            }
        }

        /**
         * Set template folder
         *
         * @param bool|string $file file path.
         */
        public static function override_folder($file = false)
        {
            $folder = COMMERCIOO_PATH . '/templates';

            if ($file) {
                $folder = plugin_dir_path($file);
                $folder .= '/templates';
            }

            if ($folder) {
                self::do_set_folder($folder);
            }
        }

        /**
         * Reset the template folder.
         */
        public static function reset_folder()
        {
            self::do_set_folder('');
        }

        /**
         * Check existance file on theme
         * @return bool
         */
        public static function is_template_override_exists( $file, $with_file_extension = false ) {
            $root_theme = get_template_directory();
            $file_existance = '';
            $status = false;

            if ( $file ) {
                if ( $with_file_extension ) {
                    $file_existance = "{$root_theme}/commercioo/{$file}";
                } else {
                    $file_existance = "{$root_theme}/commercioo/{$file}.php";
                }
                if ( file_exists( $file_existance ) ) {
                    $status = true;
                }
            }

            return $status;
        }
    }
}