<?php
/**
 * The class responsibe for Admin notification
 *
 * @since      0.2.3
 *
 * @package    Commercioo
 * @subpackage Commercioo/admin
 */

/**
 * Class Commercioo_Notification
 *
 * @package    Commercioo
 * @subpackage Commercioo/admin
 * @author     Commercioo Team
 */
class Commercioo_Notification {

	/**
	 * Notifications to show
	 * 
	 * @since    0.2.3
	 * @var      array
	 */
	public $notifications;
	
	/**
	 * Notification title
	 * 
	 * @since    0.2.3
	 * @var      string
	 */
	public $title;

	/**
	 * Base notification data
	 * 
	 * @since    0.2.3
	 * @var      array
	 */
	public $base_data;

	/**
	 * Commercioo required pages
	 * 
	 * @since    0.2.3
	 * @var      array
	 */
	public $required_pages;

	/**
     * Initialize the class and set its properties.
     *
     * @since    0.2.3
     */
    public function __construct() {
		$this->title          = __( 'Commercioo needs your attention', 'commercioo' );
		$this->notifications  = array();		
		$this->base_data      = $this->get_base_data();
		$this->required_pages = $this->get_required_pages();
    }

	/**
	 * Initialize notification
	 * 
	 * @since    0.2.3
	 */
	public function init() {
		// PHP version < 7.3 notification
		$this->php_version_notification();

		$this->database_version_notification();

		// Required pages setup and existence check
		foreach ( $this->required_pages as $key => $page ) {
			$this->required_pages_notifications( $key, $page );
		}		

		// Gather notifications from apply_filters
		$this->gather_notifications();
	}

	/**
	 * Render the notification
	 * 
	 * @since    0.2.3
	 */
	public function render_notification() {
		// bail out if empty		
		if ( empty( $this->notifications ) ) {
			return false;
		}
		?>

		<div class="notice notice-warning notice-commercioo">
			<div class="notice-commercioo-header"><?php echo esc_html( $this->title ) ?></div>
			
			<?php foreach ( $this->notifications as $notification ) : ?>
			
			<div class="notice-commercioo-items">
				<h3><?php echo esc_html( $notification['title'] ) ?></h3>

				<?php 
				if ( $notification['description'] ) {
					printf( "<p>%s</p>", esc_html( $notification['description'] ) );
				}
				?>

				<?php 
				if ( $notification['button'] && $notification['url'] ) {
					printf( "<a href='%s' class='button commercioo_update_btn'>%s</a><span class='db-update-license-loading d-none'><img class='db-update-loading-img'></span>", esc_attr( $notification['url'] ), esc_html( $notification['button'] ) );
				}
				?>
			</div>

			<?php endforeach; ?>

		</div>

		<?php
	}

	/**
	 * Gather notifications from apply_filters
	 * 
	 * @since    0.2.3
	 */
	public function gather_notifications() {
		$raw_notifications = apply_filters( 'commercioo_notifications', array() );
		$new_notifications = array();

		foreach ( $raw_notifications as $raw_item ) {
			if ( is_array( $raw_item ) ) {
				$new_notifications[] = array_merge( $this->base_data, $raw_item );
			} else {
				$new_notifications[]['title'] = $raw_item;
			} 
		}

		// add to $this->notifications 
		$this->notifications = $this->notifications + $new_notifications;
	}

	/**
	 * Add notification
	 * 
	 * @since    0.2.3
	 * @param    array
	 */
	public function add_notification( $notification ) {
		$new_notification = array_merge( $this->base_data, $notification );
		array_push( $this->notifications, $new_notification );
	}

	/**
	 * PHP version < 7.3 notification
	 * 
	 * @since    0.2.3
	 * @access   private
	 */
	private function php_version_notification() {
		if ( ! defined( 'PHP_VERSION_ID' ) || PHP_VERSION_ID < 70300 ) {
			$this->add_notification( array(
				'title'       => __( 'Your PHP version is below 7.3', 'commercioo' ),
				'description' => __( 'Commercioo require PHP version 7.3 or above to run properly, as it also recommended by WordPress', 'commercioo' ),
				'button'      => __( 'Click here to manage your plugins', 'commercioo' ),
				'url'         => admin_url( 'plugins.php?s=commercioo&plugin_status=all' ),
			) );
		}
	}

	/**
	 * Database version
	 * 
	 * @since    0.2.3
	 * @access   private
	 */
	private function database_version_notification() {
		$changelog = new Commercioo_Changelog(COMMERCIOO_VERSION);
    	$isupdated = $changelog->comm_compare_version();
    	$function ='';
        $item_name ='';
		if ( $isupdated ) {
			foreach ($isupdated as $version) {
				$version_message[] = isset($changelog->changelog[ $version ]['pesan'])?$changelog->changelog[ $version ]['pesan']:$changelog->changelog[ $version ];
                $function = isset($changelog->changelog[ $version ]['function'])?$changelog->changelog[ $version ]['function']:'';
                $item_name = isset($changelog->changelog[ $version ]['item_name'])?$changelog->changelog[ $version ]['item_name']:'';
			}
			$url_with_nonce = wp_nonce_url( admin_url( 'admin.php' ), 'DlTp6QHNXCobw' );
			$origin_url     = isset( $_SERVER['REQUEST_URI'] ) ? home_url( $_SERVER['REQUEST_URI'] ) : admin_url( 'index.php' );
			$action_url     = add_query_arg( array(
				'action' => 'comm_update_changelog',
				'hook_action_function' => $function,
				'item_name' => $item_name,
				'doing_update' => 1,
				'origin' => $origin_url,
			), $url_with_nonce );
			$this->add_notification( array(
				'title'       => __( 'The Commercioo database is need to update', 'commercioo' ),
				'description' => __( implode(", ", $version_message), 'commercioo' ),
				'button'      => __( 'Click here to update', 'commercioo' ),
				'url'         => $action_url,
			) );
		}

		if ( class_exists( 'Commercioo_Pro_Changelog' ) ){
			$changelog = new Commercioo_Pro_Changelog(COMMERCIOO_PRO_VERSION);
			$isupdated = $changelog->comm_pro_compare_version();
			if ( $isupdated ) {
				foreach ($isupdated as $version) {
					$version_message_pro[] = $changelog->changelog[ $version ];
				}
				$url_with_nonce = wp_nonce_url( admin_url( 'admin.php' ), 'DlTp6QHNXCobw' );
				$origin_url     = isset( $_SERVER['REQUEST_URI'] ) ? home_url( $_SERVER['REQUEST_URI'] ) : admin_url( 'index.php' );
				$action_url     = add_query_arg( array(
					'action' => 'comm_pro_update_changelog',
					'origin' => $origin_url,
				), $url_with_nonce );
				$this->add_notification( array(
					'title'       => __( 'The Commercioo Pro database is need to update', 'commercioo' ),
					'description' => __( implode(", ", $version_message_pro), 'commercioo' ),
					'button'      => __( 'Click here to update', 'commercioo' ),
					'url'         => $action_url,
				) );
			}
		}
	}

	/**
	 * Required pages setup and existence check
	 * 
	 * @since    0.2.3
	 * @access   private
	 */
	public function required_pages_notifications( $key, $page ) {
		$post_id      = get_option( $page['option_name'], null );
		$notification = array();
		$post_data    = get_post( $post_id );
        $shortcode = 'commercioo_thank_you';
		if ( $post_data && 'page' === $post_data->post_type ) {
			$page_template = get_post_meta( $post_id, '_wp_page_template', true );
			
			// check post status
			if ( 'publish' !== $post_data->post_status ) {
				$notification['description'] = sprintf( __( 'Your %s page is not published', 'commercioo' ), $page['label'] );
			}

			// check post content
			if ( false === strpos( $post_data->post_content, $page['shortcode'] ) && $page['label']!="Thank You") {
				if ( ! defined( 'ELEMENTOR_VERSION' ) || ! \Elementor\Plugin::$instance->documents->get( $post_id )->is_built_with_elementor() ) {
					$notification['description'] = sprintf( __( 'Your %s content page is not valid', 'commercioo' ), $page['label'] );
				}
			}
            if (($page['label']=="Thank You") && (!has_shortcode($post_data->post_content, $shortcode))) {
                $notification['description'] = sprintf( __( 'Your %s content page is not valid', 'commercioo' ), $page['label'] );
            }
			// check page_template
			if ( $page_template && ! empty( $page_template ) && 'default' !== $page_template ) {
				if ( ! defined( 'ELEMENTOR_VERSION' ) || ! \Elementor\Plugin::$instance->documents->get( $post_id )->is_built_with_elementor() ) {
					$notification['description'] = sprintf( __( 'Your %s page template is not valid', 'commercioo' ), $page['label'] );
				}
			}
		} else {
			$notification = array(
				'title'       => sprintf( __( '%s page is not exist', 'commercioo' ), $page['label'] ),
				'description' => sprintf( __( 'The %s page is required to make your Commercioo website runs properly', 'commercioo' ), $page['label'] ),
				'button'      => sprintf( __( 'Click here to ceate the %s page', 'commercioo' ), $page['label'] ),
			);
		}

		// if we finally found any page issues
		if ( ! empty( $notification ) ) {
			$url_with_nonce = wp_nonce_url( admin_url( 'admin.php' ), 'DlTp6QHNXCobw' );
			$origin_url     = isset( $_SERVER['REQUEST_URI'] ) ? home_url( $_SERVER['REQUEST_URI'] ) : admin_url( 'index.php' );
			$action_url     = add_query_arg( array(
				'action' => 'commercioo_manage_page',
				'type'   => $key,
				'origin' => $origin_url,
			), $url_with_nonce );

			// add a notification
			$notification = array_merge( 
				array(
					'title'       => sprintf( __( 'The %s page is not properly configured', 'commercioo' ), $page['label'] ),
					'description' => null,
					'button'      => sprintf( __( 'Click here to fix the %s page', 'commercioo' ), $page['label'] ),
					'url'         => $action_url,
				), 
				$notification
			);

			$this->add_notification( $notification );
		}

		return $notification;
	}

	/**
	 * WordPress action to manage commercioo pages
	 * This 'manage' means create or edit 
	 * 
	 * @since    0.2.3
	 */
	public function action_manage_page() {
		
		// check nonce
		check_admin_referer( 'DlTp6QHNXCobw' );
		
		// get type
		$type       = sanitize_text_field( $_GET['type'] );
		$origin_url = esc_url_raw( $_GET['origin'] );
		
		if ( isset( $this->required_pages[ $type ] ) ) {
			// load class activator
			require_once COMMERCIOO_PATH . 'includes/class-commercioo-activator.php';

			/**
			 * Run the function
			 * Commercioo_Activator::$function
			 */
			$function = $this->required_pages[ $type ]['function'];
			call_user_func( array( 'Commercioo_Activator', $function ) );
            delete_transient('comm_onboarding');
		}

		// redirect
		wp_redirect( $origin_url );
		exit();
	}

	/**
	 * WordPress action to update database version
	 * 
	 * @since    0.2.3
	 */
	public function action_update_changelog_init() {
	    if(is_admin() && isset($_GET['_wpnonce']) && isset($_GET['doing_update'])) {
            $_wpnonce = sanitize_post($_GET['_wpnonce']);
            $_function = sanitize_post($_GET['hook_action_function']);
            $_item_name = sanitize_post($_GET['item_name']);
            wp_verify_nonce($_wpnonce, 'DlTp6QHNXCobw');
            require_once COMMERCIOO_PATH . 'includes/data/commercioo-function-update.php';
            call_user_func(array('Commercioo\Update\commercioo_do_update', 'update_database_changelog'), $_function,$_item_name);

            $origin_url = esc_url_raw($_GET['origin']);
            $changelog = new Commercioo_Changelog(COMMERCIOO_VERSION);
            $changelog->comm_set_current_database_version();
            // redirect
            wp_redirect($origin_url);
            exit();
        }
    }
    public function commercioo_do_update(){
        update_option("alfan_up","update");
    }

	/**
	 * WordPress action to update database pro version
	 * 
	 * @since    0.2.3
	 */
	public function pro_action_update_changelog() {
		
		// check nonce
		check_admin_referer( 'DlTp6QHNXCobw' );
		
		// get type
		$origin_url = esc_url_raw( $_GET['origin'] );
		
    	if ( class_exists( 'Commercioo_Pro_Changelog' ) ){
    		$changelog = new Commercioo_Pro_Changelog(COMMERCIOO_PRO_VERSION);
    		$changelog->comm_pro_update_changelog();
    	}
		// redirect
		wp_redirect( $origin_url );
		exit();
	}

	/**
	 * Get base notification data
	 * 
	 * @since    0.2.3
	 * @access   private
	 * @return   array
	 */
	private function get_base_data() {
		return array(
			'title'       => null,
			'description' => null,
			'button'      => null,
			'url'         => null,
		);
	}

	/**
	 * Get Commercioo required pages
	 * 
	 * @since    0.2.3
	 * @access   private
	 * @return   array
	 */
	private function get_required_pages() {
		return array(
			'checkout' => array(
				'label'       => 'Checkout',
				'option_name' => 'commercioo_Checkout_page_id',
				'shortcode'   => '[commercioo_checkout]',
				'function'    => 'create_page_checkout',
			),
			'cart' => array(
				'label'       => 'Cart',
				'option_name' => 'commercioo_Cart_page_id',
				'shortcode'   => '[commercioo_cart]',
				'function'    => 'create_page_cart',
			),
			'product' => array(
				'label'       => 'Product',
				'option_name' => 'commercioo_Product_page_id',
				'shortcode'   => '[commercioo_product]',
				'function'    => 'create_page_product',
			),
			'account' => array(
				'label'       => 'Account',
				'option_name' => 'commercioo_Account_page_id',
				'shortcode'   => '[commercioo_account]',
				'function'    => 'create_page_account',
			),
			'thank_you' => array(
				'label'       => 'Thank You',
				'option_name' => 'commercioo_thank_you_page_id',
				'shortcode'   => '[commercioo_thank_you]',
				'function'    => 'create_page_thank_you',
			),
		);
	}


}