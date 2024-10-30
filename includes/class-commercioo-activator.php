<?php
/**
 * Fired during plugin activation
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    Commercioo
 * @subpackage Commercioo/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Commercioo
 * @subpackage Commercioo/includes
 * @author     Your Name <email@example.com>
 */
class Commercioo_Activator
{

    /**
     * Short Description. (use period)
     *
     * Long Description.
     *
     * @since    1.0.0
     */
    public static function activate()
    {
        // create table commercioo_order_items
        self::create_commercioo_order_items_table();

        //PRO CHECKER, WHETHER PRO PLUGIN ACTIVATED OR NOT
        self::comm_check_pro();
        self::comm_check_pro_wa();
        self::comm_check_pro_ar();

        // create table commercioo_order_logs
        self::create_commercioo_order_logs_table();

        // create table commercioo_customer
        self::create_commercioo_customers_table();

        //alter table commercioo_customer
        self::alter_commercioo_customers_table();
        // add commercioo's custom user role(s)
        self::add_user_roles();

        self::set_settings();
        self::create_page_checkout();
        // add_action( 'create_cart_page', array( 'create_page_cart' ) );
        self::create_page_cart();
        // add_action( 'create_product_page', array( 'create_page_product' ) );
        self::create_page_product();
        self::create_page_account();
		self::create_page_thank_you();
		
		// create table commercioo_page_views
		$page_views = new \Commercioo\Page_Views;		
		$page_views->create_db_table();

        set_transient( 'comm_onboarding', 'active', HOUR_IN_SECONDS );
    }

    private static function create_commercioo_order_items_table()
    {
        global $wpdb;
        $db_name = $wpdb->prefix . 'commercioo_order_items';
        $charset_collate = $wpdb->get_charset_collate();

        if ($wpdb->get_var("SHOW TABLES LIKE '$db_name'") !== $db_name) {
            $sql = 'CREATE TABLE ' . $db_name . " 
					( `item_id` int(20) UNSIGNED NOT NULL AUTO_INCREMENT, 
					  `order_id` int(20) UNSIGNED NOT NULL, 
					  `product_id` int(20) UNSIGNED NOT NULL, 
                      `variation_id` int(20) UNSIGNED NOT NULL DEFAULT 0, 
					  `item_name` text NOT NULL, 
					  `item_price` decimal(20,2) NOT NULL, 
					  `item_order_qty` int(20) NOT NULL,
					  PRIMARY KEY (`item_id`),
					  INDEX (`order_id`,`product_id`)
				  	) $charset_collate;";

            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
            dbDelta($sql);
        }

        // add variation_id if column not exists
        $row = $wpdb->get_results(  "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name = '{$db_name}' AND column_name = 'variation_id'"  );
        if ( empty( $row ) ) {
            $wpdb->query("ALTER TABLE {$db_name} ADD variation_id INT(20) NOT NULL DEFAULT 0");
        }
    }

    public static function comm_check_pro()
    {
        include_once(ABSPATH . 'wp-admin/includes/plugin.php');
        $key = 'Y29tbWVyY2lvby1wcm8vY29tbWVyY2lvby1wcm8ucGhw';
        $index = 'aXNfY29tbV9wcm8=';
        update_option(comm_url_decode($index), comm_url_encode(wp_json_encode([comm_url_encode(get_bloginfo('name'))
        => is_plugin_active(comm_url_decode($key))])));
    }

    public static function comm_check_pro_wa()
    {
        include_once(ABSPATH . 'wp-admin/includes/plugin.php');
        $key = 'Y29tbWVyY2lvby13YS9jb21tZXJjaW9vLXdhLnBocA==';
        $index = 'aXNfY29tbV93YQ==';
        update_option(comm_url_decode($index), comm_url_encode(wp_json_encode([comm_url_encode(get_bloginfo('name'))
        => is_plugin_active(comm_url_decode($key))])));
    }

    public static function comm_check_pro_ar()
    {
        include_once(ABSPATH . 'wp-admin/includes/plugin.php');
        $key = 'Y29tbWVyY2lvby1hdXRvcmVzcG9uZGVyL2NvbW1lcmNpb28tYXV0b3Jlc3BvbmRlci5waHA=';
        $index = 'aXNfY29tbV9hcg==';
        update_option(comm_url_decode($index), comm_url_encode(wp_json_encode([comm_url_encode(get_bloginfo('name'))
        => is_plugin_active(comm_url_decode($key))])));
    }

    private static function create_commercioo_order_logs_table()
    {
        global $wpdb;
        $db_name = $wpdb->prefix . 'commercioo_order_logs';
        $charset_collate = $wpdb->get_charset_collate();

        if ($wpdb->get_var("SHOW TABLES LIKE '$db_name'") !== $db_name) {
            $sql = 'CREATE TABLE ' . $db_name . " 
					( `log_id` int(20) UNSIGNED NOT NULL AUTO_INCREMENT, 
					  `order_id` int(20) UNSIGNED NOT NULL, 
					  `log_name` varchar(191) NOT NULL, 
					  `log_description` text NOT NULL, 
					  `log_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00', 
					  PRIMARY KEY (`log_id`),
					  INDEX (`order_id`)
				  	) $charset_collate;";

            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
            dbDelta($sql);
        }
    }

    private static function create_commercioo_customers_table()
    {
        global $wpdb;
        $db_name = $wpdb->prefix . 'commercioo_customers';
        $charset_collate = $wpdb->get_charset_collate();

        if ($wpdb->get_var("SHOW TABLES LIKE '$db_name'") !== $db_name) {
            $sql = 'CREATE TABLE ' . $db_name . " 
                    ( `customer_id` int(20) UNSIGNED NOT NULL AUTO_INCREMENT, 
                      `user_id` int(20) UNSIGNED NOT NULL, 
                      `name` text NOT NULL, 
                      `phone` text NOT NULL,
                      `email` text NOT NULL,
                      `address` text NOT NULL,
                      `date_registered` text NOT NULL,
                      PRIMARY KEY (`customer_id`),
                      INDEX (`user_id`)
                    ) $charset_collate;";

            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
            dbDelta($sql);
        }
    }

        private static function alter_commercioo_customers_table()
    {
        global $wpdb;
        $db_name = $wpdb->prefix . 'commercioo_customers';
        $charset_collate = $wpdb->get_charset_collate();

        if ($wpdb->get_var("SHOW TABLES LIKE '$db_name'") === $db_name) {
            $row = $wpdb->get_results(  "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name = '$db_name' AND column_name = 'token'"  );
            $sql = 'ALTER TABLE '. $db_name .' MODIFY user_id INT NULL';
            $wpdb->query($sql);
            $sql = 'ALTER TABLE '. $db_name .' MODIFY name text NULL';
            $wpdb->query($sql);
            $sql = 'ALTER TABLE '. $db_name .' MODIFY phone text NULL';
            $wpdb->query($sql);
            $sql = 'ALTER TABLE '. $db_name .' MODIFY email text NULL';
            $wpdb->query($sql);
            $sql = 'ALTER TABLE '. $db_name .' MODIFY address text NULL';
            $wpdb->query($sql);
            if(empty($row)){
                $sql = 'ALTER TABLE '. $db_name .' ADD token text NULL';
                $wpdb->query($sql);
            }

        }
    }

    private static function add_user_roles()
    {
        global $wp_roles;

        if (!class_exists('WP_Roles')) {
            return;
        }

        if (!isset($wp_roles)) {
            $wp_roles = new WP_Roles(); // @codingStandardsIgnoreLine
        }

        // add customer role
        add_role('comm_customer', 'Commercioo Customer', array(
            'read' => true,
            'level_0' => true,
        ));

        $capabilities = self::get_core_capabilities();

        foreach ($capabilities as $cap_group) {
            foreach ($cap_group as $cap) {
				/**
				 * Dunno why the previous code was `$wp_roles->add_cap('comm_customer', $cap);`
				 * that makes the `comm_customer` role can access some restricted admin pages
				 * so now we need to remove those caps from `comm_customer`
				 */				
                $wp_roles->remove_cap('comm_customer', $cap);
            }
		}

        // add manage_commercioo to admin
        $wp_roles->add_cap( 'administrator', 'manage_commercioo' );

    }

    private static function get_core_capabilities()
    {
        $capabilities = array();

        $capabilities['core'] = array(
            'manage_commercioo',
            'view_commercioo_reports',
        );
        return $capabilities;
    }

    private static function set_settings()
    {
        $settings['store_name'] = '';
        $settings['store_logo'] = '';
        $settings['store_address'] = '';
        $settings['product_archive_posts_per_page'] = '9';
        $settings['currency'] = 'IDR';
        $settings['currency_symbol'] = 'Rp';
        $settings['currency_name'] = 'Indonesian rupiah';
        $settings['currency_decimal'] = ',';
        $settings['currency_position'] = 'prefix';
        $settings['currency_thousand'] = '.';
        $settings['currency_decimal_limit'] = intval(0);

        $comm_general_settings = get_option('comm_general_settings', []);
        if (!$comm_general_settings) {
            update_option('comm_general_settings', $settings);
        }

        // Form Field : Billing Section
        $order_forms['billing_address']['billing_first_name'] = 'First Name';
        $order_forms['billing_address']['billing_last_name_visibility'] = 'required';
        $order_forms['billing_address']['billing_last_name'] = 'Last Name';
		$order_forms['billing_address']['billing_email'] = 'Email';
		$order_forms['billing_address']['billing_phone'] = 'WhatsApp Number'; // billing_phone
        $order_forms['billing_address']['billing_company_visibility'] = 'required';
        $order_forms['billing_address']['billing_company_show'] = '1';
        $order_forms['billing_address']['billing_company'] = 'Company name';
        $order_forms['billing_address']['billing_country_visibility'] = 'required';
        $order_forms['billing_address']['billing_country'] = 'Country';
        $order_forms['billing_address']['billing_street_address_visibility'] = 'required';
        $order_forms['billing_address']['billing_street_address'] = 'Street address';
        $order_forms['billing_address']['billing_city_visibility'] = 'required'; // billing_city_visibility
        $order_forms['billing_address']['billing_city'] = 'Town / City'; // billing_city
        $order_forms['billing_address']['billing_state_visibility'] = 'required';
        $order_forms['billing_address']['billing_state'] = 'State';
        $order_forms['billing_address']['billing_zip_visibility'] = 'required';
        $order_forms['billing_address']['billing_zip'] = 'Zip code';

		// Form Field : etc
		$order_forms['ship_to_different_address_visibility'] = true;
		$order_forms['ship_to_different_address_label'] = 'Ship To Different Address';
		$order_forms['order_note_visibility'] = true;
		$order_forms['order_note_label'] = 'Order Notes';
		$order_forms['order_powered_by_visibility'] = true;
		$order_forms['order_powered_by_url'] = '';
        $order_forms['message_above'] = 'Saya ingin pesan';
        $order_forms['form_required_note'] = 'Isian dengan tanda bintang * harus diisi';
        $order_forms['checkout_header_text'] = '<b>Hubungi Kami</b> jika butuh bantuan dengan pesanan Anda.';
        $order_forms['checkout_layout'] = 'default';
        $order_forms['button_text'] = 'Purchase Now';

        //Style Color : Button Style
        $order_forms['button_style'] = 'c-button-rounded';

        //Style Color : Button Color
        $order_forms['button_color'] = '#3f51b5,#303f9f';

        //Style Color : Button Custom Color
        $order_forms['background_color'] = '#3F51B5';
        $order_forms['background_color'] = '#3F51B5';
        $order_forms['border_color'] = '#3F51B5';
        $order_forms['font_color'] = '#FFFFFF';

        //Style Color : Button Custom Color Hover
        $order_forms['background_hover_color'] = '#3F51B5';
        $order_forms['border_hover_color'] = '#3F51B5';
		$order_forms['font_hover_color'] = '#FFFFFF';
		
		$order_forms['thank_you_redirect'] = 'page';
		$order_forms['def_typ_msg'] = comm_default_thankyou_message();
		$order_forms['def_wa_msg'] = '';

        $comm_order_form_settings = get_option('comm_order_forms_settings', []);
        if (!$comm_order_form_settings) {
            update_option('comm_order_forms_settings', $order_forms);
		}
		
		// transactional emails settings
		$default_emails  = new \Commercioo\Emails\Default_Emails();
		$emails_settings = $default_emails->default_settings;

		// update option if not exist
        if ( ! get_option( 'comm_emails_settings', false ) ) {
            update_option( 'comm_emails_settings', $emails_settings );
		}
	}

    /**
     * Create a variable to specify the details of page.
     *
     * @since     1.0.0
     */
    public static function create_page_checkout()
    {
        $pages = [
            'post_type' => [
                'content' => [
                    [
                        'value' => 'post',
                        'text' => 'post'
                    ]
                ]
            ],
            'checkout_page' => [
                'title' => 'Checkout',
                'content' => '[commercioo_checkout]',
                'custom_page' => true
            ],
        ];
        $args = [
            'post_status' => 'publish',
            'post_author' => 1,
            'post_type' => 'page',
        ];

        //CREATE PAGE PROGRAMATICALLY THEN SET THE CONTENT AND PAGE TEMPLATE
        $post_id = get_option('commercioo_Checkout_page_id');
        $is_page = get_post($post_id);

        if($is_page){
            $data_post['ID'] = $post_id;
            $data_post['post_status'] = "publish";
            $data_post['post_content'] = "[commercioo_checkout]";
            wp_update_post($data_post);

			// set page template to default
			update_post_meta( $post_id, '_wp_page_template', 'default' );
        }else{
            if (!isset($post_id) || empty($post_id) || $post_id == 'none' || $is_page == NULL) {
                foreach ($pages as $p => $v) {
                    if ($p != 'post_type') {
                        $args['post_title'] = $v['title'];
                        $args['post_content'] = $v['content'];
                        $options[$p] = strval(wp_insert_post($args));
                        update_option('commercioo_' . $v['title'] . '_page_id', $options[$p]);
                    }
                }
            }
        }
    }

	/**
	* Create a variable to specify the details of page.
	*
	* @since     1.0.0
	*/
	public static function create_page_cart() {
        $pages = [
            'post_type' => [
                'content' => [
                    [
                        'value' => 'post',
                        'text' => 'post'
                    ]
                ]
            ],
            'cart_page' => [
                'title' => 'Cart',
                'content' => '[commercioo_cart]',
                'custom_page' => true
            ],
        ];
        $args = [
            'post_status' => 'publish',
            'post_author' => 1,
            'post_type' => 'page',
        ];

        //CREATE PAGE PROGRAMATICALLY THEN SET THE CONTENT AND PAGE TEMPLATE
        $post_id = get_option('commercioo_Cart_page_id');
        $is_page = get_post($post_id);

        
        if($is_page){
            $data_post['ID'] = $post_id;
            $data_post['post_status'] = "publish";
            $data_post['post_content'] = "[commercioo_cart]";
            wp_update_post($data_post);
			
			// set page template to default
			update_post_meta( $post_id, '_wp_page_template', 'default' );
        }else{
            
            if (!isset($post_id) || empty($post_id) || $post_id == 'none' || $is_page == NULL) {              
                foreach ($pages as $p => $v) {
                    if ($p != 'post_type') {
                        $args['post_title'] = $v['title'];
                        $args['post_content'] = $v['content'];
                        $options[$p] = strval(wp_insert_post($args));
                        update_option('commercioo_' . $v['title'] . '_page_id', $options[$p]);
                    }
                }
            }
        }
	}

	/**
	* Create a variable to specify the details of page.
	*
	* @since     1.0.0
	*/
	public static function create_page_product() {
        $pages = [
            'post_type' => [
                'content' => [
                    [
                        'value' => 'post',
                        'text' => 'post'
                    ]
                ]
            ],
            'checkout_page' => [
                'title' => 'Product',
                'content' => '[commercioo_product]',
                'custom_page' => true
            ],
        ];
        $args = [
            'post_status' => 'publish',
            'post_author' => 1,
            'post_type' => 'page',
        ];

        //CREATE PAGE PROGRAMATICALLY THEN SET THE CONTENT AND PAGE TEMPLATE
        $post_id = get_option('commercioo_Product_page_id');
        $is_page = get_post($post_id);

        if($is_page){
            $data_post['ID'] = $post_id;
            $data_post['post_status'] = "publish";
            $data_post['post_content'] = "[commercioo_product]";
            wp_update_post($data_post);

			// set page template to default
			update_post_meta( $post_id, '_wp_page_template', 'default' );
        }else{
            if (!isset($post_id) || empty($post_id) || $post_id == 'none' || $is_page == NULL) {
                foreach ($pages as $p => $v) {
                    if ($p != 'post_type') {
                        $args['post_title'] = $v['title'];
                        $args['post_content'] = $v['content'];
                        $options[$p] = strval(wp_insert_post($args));
                        update_option('commercioo_' . $v['title'] . '_page_id', $options[$p]);
                    }
                }
            }
        }
	}

    /**
    * Create a variable to specify the details of page.
    *
    * @since     1.0.0
    */
    public static function create_page_account() {
        $pages = [
            'post_type' => [
                'content' => [
                    [
                        'value' => 'post',
                        'text' => 'post'
                    ]
                ]
            ],
            'account_page' => [
                'title' => 'Account',
                'content' => '[commercioo_account]',
                'custom_page' => true
            ],
        ];
        $args = [
            'post_status' => 'publish',
            'post_author' => 1,
            'post_type' => 'page',
        ];

        //CREATE PAGE PROGRAMATICALLY THEN SET THE CONTENT AND PAGE TEMPLATE
        $post_id = get_option('commercioo_Account_page_id');
        $is_page = get_post($post_id);

        if($is_page){
            $data_post['ID'] = $post_id;
            $data_post['post_status'] = "publish";
            $data_post['post_content'] = "[commercioo_account]";
            wp_update_post($data_post);

			// set page template to default
			update_post_meta( $post_id, '_wp_page_template', 'default' );
        }else{
            if (!isset($post_id) || empty($post_id) || $post_id == 'none' || $is_page == NULL) {
                foreach ($pages as $p => $v) {
                    if ($p != 'post_type') {
                        $args['post_title'] = $v['title'];
                        $args['post_content'] = $v['content'];
                        $options[$p] = strval(wp_insert_post($args));
                        update_option('commercioo_' . $v['title'] . '_page_id', $options[$p]);
                    }
                }
            }
        }
    }

    /**
     * Create a variable to specify the details of page.
     * Create Thank You Page
     * @content Shortcode: [commercioo_thank_you]
     * @since     1.0.0
     */
    public static function create_page_thank_you() {
        $pages = [
            'post_type' => [
                'content' => [
                    [
                        'value' => 'post',
                        'text' => 'post'
                    ]
                ]
            ],
            'thank_you_page' => [
                'title' => 'Thank You Page',
                'content' => '<!-- wp:shortcode -->[' . apply_filters( 'commercioo_thank_you_shortcode_tag', 'commercioo_thank_you' ) . ']<!-- /wp:shortcode -->',
                'custom_page' => true
            ],
        ];
        $args = [
            'post_status' => 'publish',
            'post_author' => 1,
            'post_type' => 'page',
        ];

        //CREATE PAGE PROGRAMATICALLY THEN SET THE CONTENT AND PAGE TEMPLATE
        $post_id = get_option('commercioo_thank_you_page_id');
        $is_page = get_post($post_id);

        if($is_page){
            $data_post['ID'] = $post_id;
            $data_post['post_status'] = "publish";
            $data_post['post_content'] = $pages['thank_you_page']['content'];
            wp_update_post($data_post);

			// set page template to default
			update_post_meta( $post_id, '_wp_page_template', 'default' );
        }else{
            if (!isset($post_id) || empty($post_id) || $post_id == 'none' || $is_page == NULL) {
                foreach ($pages as $p => $v) {
                    if ($p != 'post_type') {
                        $args['post_title'] = $v['title'];
                        $args['post_content'] = $v['content'];
                        $options[$p] = strval(wp_insert_post($args));
                        update_option('commercioo_thank_you_page_id', $options[$p]);
                    }
                }
            }
        }
    }
}
