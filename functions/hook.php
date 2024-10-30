<?php
// Affiliate view
use Commercioo\Emails;

add_filter("comm_affiliate", "comm_affiliate");
function comm_affiliate($file_affiliate)
{
    if ($file_affiliate != null) {
        if (is_comm_pro()) {
            if(file_exists($file_affiliate)) {
                include_once $file_affiliate;
            }
        }
    }
    return "";
}

// FB Pixel view
add_filter("comm_fb_pixel_event", "comm_fb_pixel_event");
function comm_fb_pixel_event($fb_pixel_event)
{
    if ($fb_pixel_event != null) {
        if (is_comm_pro()) {
            if(file_exists($fb_pixel_event)) {
                include_once $fb_pixel_event;
            }
        }
    }
    return "";
}

add_filter("comm_click_btn_pixel_event", "comm_click_btn_pixel_event");
function comm_click_btn_pixel_event($fb_pixel_event)
{
    if ($fb_pixel_event != null) {
        if (is_comm_wa()) {
            if(file_exists($fb_pixel_event)) {
                include_once $fb_pixel_event;
            }
        }
    }
    return "";
}

add_filter("comm_misc_setting_statistic", "comm_misc_setting_statistic");
function comm_misc_setting_statistic($misc_setting_statistic)
{
    if ($misc_setting_statistic != null) {
        if (is_comm_wa()) {
            if(file_exists($misc_setting_statistic)) {
                include_once $misc_setting_statistic;
            }
        }
    }
    return "";
}

add_filter("comm_followup_msg_page", "comm_followup_msg_page");
function comm_followup_msg_page($comm_followup_msg_page)
{
    if ($comm_followup_msg_page != null) {
        if (is_comm_wa()) {
            if(file_exists($comm_followup_msg_page)) {
                include_once $comm_followup_msg_page;
            }
        }
    }
    return "";
}

/**
 * Fires when a post is transitioned from one status to another.
 * In this case, only applied on post type comm_order
 */
add_action( 'transition_post_status', 'commercioo_transition_order_status', 10, 3 );
if ( ! function_exists( 'commercioo_transition_order_status' ) ) {
	function commercioo_transition_order_status( $new_status, $old_status, $post ) {
		// must be applied on post type comm_order
		if ( $post->post_type !== 'comm_order' ) return;

		/**
		 * This hook evidently also been call on creating order, which is on `wp_insert_post` calls
		 * So we need to prevent that, since all order metas hasn't been created yet at that time
		 * We test it by calling the one of primary meta, which is `_order_total`
		 */
		$order_total = get_post_meta( $post->ID, '_order_total', true );
		if ( empty( $order_total ) ) return;
		
		// on different statuses
		if ( $new_status !== $old_status ) {
			// send the email on change status
			comm_sending_email( $post->ID, $new_status );
			
			// do autoresponder
			if( function_exists( 'do_autoresponder' ) ) {
				do_autoresponder( $post->ID, $new_status );
			}
		}

		// on complete status
		if ( $new_status == 'comm_completed' ) {
            $post_id = $post->ID;
            $isValidCompleted = get_post_meta( $post_id, 'is_complete', true );
            if( ! $isValidCompleted ) {
                update_post_meta( $post_id, 'is_complete', 1 );
                update_post_meta( $post_id, 'is_date_complete', date( 'Y-m-d :H:i:s' ) );
            }
		}		
	}
}

add_action("comm_update_status", "comm_update_status", 10, 2);
if (!function_exists('comm_update_status')) {
    function comm_update_status($id, $status)
    {
        if ($status !== "trash") {
            $post['ID'] = $id;
            $post['post_status'] = $status;
            if ($status == "comm_completed") {
                $isValidCompleted = get_post_meta($id, "is_complete", true);
                if (!$isValidCompleted) {
                    $post['post_modified'] = date("Y-m-d H:i:s");
                    update_post_meta($id, "is_complete", 1);
                    update_post_meta($id, "is_date_complete", date("Y-m-d :H:i:s"));
                }
                if( function_exists( 'do_update_digital_download' ) ) {
                    do_update_digital_download( $id, $status );
                }
            }
            wp_update_post($post);
        } else {
            $response = wp_trash_post($id);
            if ($response == null) {
                return new \WP_Error('comm_error', 'Error trash data', array('status' => 404));
            }
        }
    }
}

add_filter("comm_calculated_closing_rate", "comm_calculate_closing_rate", 10, 2);
if (!function_exists('comm_calculate_closing_rate')) {
    function comm_calculate_closing_rate($sales, $order)
    {
        $closing_rate = ($sales / $order) * 100;
        $closing_rate = round($closing_rate, 2);
        return $closing_rate;
    }
}

add_filter("comm_get_product_price", "comm_get_product_price", 10, 1);
if (!function_exists('comm_get_product_price')) {
    function comm_get_product_price( $product_id ) {
        $product = comm_get_product( $product_id );
        return apply_filters( 'comm_product_price', $product->get_price(), $product_id );
    }
}

add_action("commercioo_after_creating_order", "commercioo_after_creating_order", 10, 2);
if (!function_exists('commercioo_after_creating_order')) {
    function commercioo_after_creating_order($order_id,$order_status)
    {
        // remove cart data
        if(!\Commercioo\Cart::is_empty()){
            if(!is_page_has_elementor() && function_exists("is_page_has_elementor")) {
                \Commercioo\Cart::empty_cart();
            }
        }

		// send the email on new order
		comm_sending_email( $order_id, $order_status );

		// send new order notification to admin
		$mailer = new \Commercioo\Emails\New_Order_To_Admin( $order_id );
		$mailer->send();
    }
}


// Change email content type.
add_filter('wp_mail_content_type', function () {
    return 'text/html';
});

// Change email content for newly created customer.
add_filter('wp_new_user_notification_email', 'commercioo_new_customer_email', 10, 3);
if (!function_exists('commercioo_new_customer_email')) {

    /**
     * Filters the contents of the new user notification email sent to the site admin.
     *
     * @param array $wp_new_user_notification_email array of email notification.
     * @param WP_User $user User object for new user.
     * @param string $blogname The site title.
     *
     * @return array
     */
    function commercioo_new_customer_email($wp_new_user_notification_email, $user, $blogname)
    {

        // Make sure current user is Commercioo subscriber.
        if ('comm_customer' === $user->roles[0] && !empty($user->roles)) {

            // Change email subject.
            $wp_new_user_notification_email['subject'] = "Welcome to {$blogname}, Here’s Your Account Details";

            // Defined required vars.
            $user_display_name = $user->display_name;
            $user_login = $user->user_login;
            $user_key = get_password_reset_key($user);

            // Change email body.
            $wp_new_user_notification_email['message'] = \Commercioo\Template::render('emails/new-customer-notification',
                compact('user_display_name', 'user_login', 'user_key'));
        }

        return $wp_new_user_notification_email;
    }
}

// Change email subject for resetting password.
add_filter('retrieve_password_title', 'commercioo_reset_password_title', 10, 3);
if (!function_exists('commercioo_reset_password_title')) {

    /**
     * Filters the subject of the password reset email.
     *
     * @param string $title Default email title.
     * @param string $user_login The username for the user.
     * @param WP_User $user_data WP_User object.
     *
     * @return string
     */
    function commercioo_reset_password_title($title, $user_login, $user_data)
    {
        return "Important, Your New Password on " . get_bloginfo('name');
    }
}

// Change email body for resetting password.
add_filter('retrieve_password_message', 'commercioo_reset_password_body', 10, 4);
if (!function_exists('commercioo_reset_password_body')) {

    /**
     * Filters the message body of the password reset mail.
     *
     * If the filtered message is empty, the password reset email will not be sent.
     *
     * @param string $message Default mail message.
     * @param string $key The activation key.
     * @param string $user_login The username for the user.
     * @param WP_User $user_data WP_User object.
     *
     * @return string
     */
    function commercioo_reset_password_body($message, $key, $user_login, $user_data)
    {

        // Defined required vars.
        $user_key = $key;
        $user_display_name = $user_data->display_name;

        return \Commercioo\Template::render('emails/reset-password',
            compact('user_display_name', 'user_login', 'user_key'));
    }
}

// Send email after a new user is registered.
if (!function_exists('commercioo_after_creating_user')) {
    /**
     * Fires immediately after a new user is registered.
     *
     * @param $user_id
     * @param $user_pass
     */
    function commercioo_after_creating_user($user_id,$user_pass)
    {

        // Fetch user detail
        $user = get_user_by('ID', $user_id);

        // Make sure cure current user is a customer.
        if ('comm_customer' === $user->roles[0] && !empty($user->roles)) {

            // Send welcome email immediately.
            $mailer = new \Commercioo\Emails\New_Customer($user,$user_pass);
            $mailer->send();
			
			// Send notification to admin immediately.
            $mailer = new \Commercioo\Emails\New_Customer_To_Admin($user,$user_pass);
            $mailer->send();
        }
    }
}

// Action when order status being changed.
add_action('comm_sending_email', 'comm_sending_email', 10, 2);
if (!function_exists('comm_sending_email')) {
    /**
     * Commercioo after order status updated
     *
     * @param int $id order id.
     * @param string $status order status.
     */
    function comm_sending_email($id, $status)
    {

        // Switch order status.
        switch ($status) {
            case 'comm_pending':
                // Instance pending order email.
                $mailer = new Emails\Pending_Order($id);
                $mailer->send();
                break;
            case 'comm_processing':

                // Instance processing order email.
                $mailer = new Emails\Processing_Order($id);
                $mailer->send();
                break;
            case 'comm_completed':

                // Instance completed order email.
                $mailer = new Emails\Completed_Order($id);
                $mailer->send();
                break;
            case 'comm_refunded':

                // Instance refunded order email.
                $mailer = new Emails\Refunded_Order($id);
                $mailer->send();
                break;
            case 'comm_failed':

                // Instance failed order email.
                $mailer = new Emails\Failed_Order($id);
                $mailer->send();
                break;
            case 'comm_canceled':

                // Instance canceled order email.
                $mailer = new Emails\Canceled_Order($id);
                $mailer->send();
				break;
				
			default:
				// silent is a golden
				break;
		}
    }
}

add_filter("comm_calculate_bestSeller", "comm_calculate_bestSeller", 10, 1);
function comm_calculate_bestSeller( $limit = 4 ) {
    global $wpdb;
    $posts = $wpdb->prefix . 'posts';
    $commercioo_order_items = $wpdb->prefix . 'commercioo_order_items';
    $order_items = $wpdb->get_results(
		$wpdb->prepare("
			SELECT k1.`product_id`, k1.item_name, SUM(k1.item_order_qty) AS total 
			FROM $commercioo_order_items AS k1 
			JOIN $posts k2 ON k1.order_id = k2 .ID 
			WHERE k2.post_type = %s 
				AND k2.post_status in (%s) 
			GROUP BY k1.item_name, k1.product_id order by SUM(k1.item_order_qty) DESC 
			LIMIT 0, %d",
			'comm_order', 'comm_completed', intval($limit)
		)
	);
    
	if ( $order_items ) {
        return $order_items;
    } else {
        return false;
    }
}

add_filter("comm_calculate_featured_product", "comm_calculate_featured_product", 10, 1);
function comm_calculate_featured_product($limit = 4)
{
    $args = array(
        'post_type' 	 => 'comm_product',
        'posts_per_page' => $limit, // -1 mean show all data
        'post_status' 	 => 'publish',
        'meta_query' 	 => array(
            array(
                'key'     => '_is_featured',
                'value'   => 1,
                'compare' => '=',
            ),
        ),
    );
    $order_items = get_posts( $args );
    if ($order_items) {
        return $order_items;
    } else {
        return false;
    }
}

add_filter("comm_calculate_new_product", "comm_calculate_new_product", 10, 1);
function comm_calculate_new_product($limit = 4)
{
    $args = array(
        'post_type' 	 => 'comm_product',
        'posts_per_page' => $limit, // -1 mean show all data
        'post_status' 	 => 'publish',
        'meta_query' 	 => array(
            array(
                'key'     => '_is_featured',
                'value'   => 1,
                'compare' => '!=',
            ),
        ),
    );

    $order_items = get_posts( $args );

    if ($order_items) {
        return $order_items;
    } else {
        return false;
    }

}

// Change email subject for resetting password.
add_filter('comm_product_category', 'comm_product_category', 10, 1);
if (!function_exists('comm_product_category')) {
    function comm_product_category($term_id)
    {
        if($term_id==0){
            $class_comm_menu_active = 'comm-menu-active';
        }else{
            $class_comm_menu_active = '';
        }
        $title = __('Product Categories', "commercioo_categories");
        $category = comm_get_taxonomy_hierarchy("comm_product_cat", 0, "name",$term_id);
        $output = '<div class="col-md-3 d-md-block wrap-category-product">';
        $output .= '<ul class="list-group title-category-product"><li class="list-group-item d-flex justify-content-between align-items-center active">' . $title . "</li></ul>";
        $output .= "<ul class='list-group scroll-category-product comm-list-category custom-scroll-category'>";
        if ($category) {
            $output .= "<li class='li-no-dropdown'>";
            $output .= "<a href='".get_permalink(get_option('commercioo_Product_page_id'))."' class='product-list-cat 
            comm-menu' data-id='0'>";
            $output .= '<div class="comm-menu-cat-wrap '.$class_comm_menu_active.'">';
            $output .= 'Show All';
            $output .= '</div>';
            $output .= '</a>';
            $output .= '</li>';
            foreach ($category as $cat) {
                $term_link = get_term_link($cat->term_id);
                if($cat->children_class==$term_id && $term_id!=0){
                    $class_comm_menu_active = 'comm-menu-active';
                }else{
                    $class_comm_menu_active ='';
                }

                $children = $cat->children;
                if (count($children) > 0) {
                    $output .= "<li class='li-dropdown'>";
                    $output .= "<a href='".esc_url($term_link)."' data-id='" . $cat->term_id . "' data-slug='" . $cat->slug . "'>";
                    $output .= '<div class="comm-menu-cat-wrap '.$class_comm_menu_active.'">';
                    $output .= $cat->name;
                    $output .= '</div>';
                    $output .= '</a>';
                    $output .= "<ul class='ul-dropdown'>";
                    $output .= '<div class="ul-div-dropdown">';
                    foreach ($children as $children_v) {
                    if($children_v->children_class==$children_v->term_id && $term_id!=0){
                        $class_comm_menu_active = 'comm-menu-active';
                    }else{
                        $class_comm_menu_active ='';
                    }
                    $term_child_link = get_term_link($children_v->term_id);
                    $output .= '<li class="li-item-dropdown">';
                    $output .= "<a href='".esc_url($term_child_link)."' data-id='" . $children_v->term_id . "' data-slug='" . $children_v->slug . "'>";
                    $output .= '<div class="comm-menu-cat-wrap '.$class_comm_menu_active.'">';
                    $output .= "- ".$children_v->name;
                    $output .= '</div>';
                    $output .= '</a>';
                    $output .= '</li>';
                    }
                    $output .= '</div>';
                    $output .= '</ul>';
                    $output .= '</li>';

                } else {
                    $output .= "<li class='li-no-dropdown'>";
                    $output .= "<a href='".esc_url($term_link)."' data-id='" . $cat->term_id . "' data-slug='" . $cat->slug . "'>";
                    $output .= '<div class="comm-menu-cat-wrap '.$class_comm_menu_active.'">';
                    $output .= $cat->name;
                    $output .= '</div>';
                    $output .= '</a>';
                    $output .= '</li>';

                }
            }
        }
        $output .= '</ul>';
        $output .= "</div>";
        echo wp_kses_post($output);
    }

    function comm_get_taxonomy_hierarchy($taxonomy, $parent = 0, $orderby = 'name',$term_id=0)
    {
        $children = array();
        if(!is_admin()) {
            // only 1 taxonomy
            $taxonomy = is_array($taxonomy) ? array_shift($taxonomy) : $taxonomy;
            // get all direct decendants of the $parent
            $terms = get_terms($taxonomy, array('parent' => $parent, 'hide_empty' => 0, 'orderby' => $orderby, 'order' => 'ASC'));
            // prepare a new array.  these are the children of $parent
            // we'll ultimately copy all the $terms into this new array, but only after they
            // go through all the direct decendants of $parent, and gather their children
            foreach ($terms as $term) {
                // recurse to get the direct decendants of "this" term

                $term->children = comm_get_taxonomy_hierarchy($taxonomy, $term->term_id, 'name', $term_id);
                if ($term_id == $term->term_id) {
                    $term->children_class = $term->term_id;
                } else {
                    $term->children_class = 0;
                }
                // add the term to our new array
                $children[$term->term_id] = $term;
            }
        }
        // send the results back to the caller
        return $children;
    }
}


function commercioo_locate_template( $template_name, $template_path = '', $default_path = '' ) {
    if ( ! $template_path ) {
        $template_path = COMMERCIOO_PATH . '/templates/';
    }

    if ( ! $default_path ) {
        $default_path = COMMERCIOO_PATH . '/templates/';
    }

    // Look within passed path within the theme - this is priority.
    $template = locate_template(
        array(
            trailingslashit( $template_path ) . $template_name,
            $template_name,
        )
    );

    // Get default template/.
    if ( ! $template) {
        $template = $default_path . $template_name;
    }

    // Return what we found.
    return apply_filters( 'commercioo_locate_template', $template, $template_name, $template_path );
}

/**
 * Wrapper for _doing_it_wrong().
 *
 * @since  3.0.0
 * @param string $function Function used.
 * @param string $message Message to log.
 * @param string $version Version the message was added in.
 */
function commercioo_doing_it_wrong( $function, $message, $version ) {
    // @codingStandardsIgnoreStart
    $message .= ' Backtrace: ' . wp_debug_backtrace_summary();

    if ( is_ajax()) {
        do_action( 'doing_it_wrong_run', $function, $message, $version );
        error_log( "{$function} was called incorrectly. {$message}. This message was added in version {$version}." );
    } else {
        _doing_it_wrong( $function, $message, $version );
    }
    // @codingStandardsIgnoreEnd
}

/**
 * Get other templates (e.g. product attributes) passing attributes and including the file.
 *
 * @param string $template_name Template name.
 * @param array  $args          Arguments. (default: array).
 * @param string $template_path Template path. (default: '').
 * @param string $default_path  Default path. (default: '').
 */
function comm_get_template( $template_name, $args = array(), $template_path = '', $default_path = '' ) {
    $cache_key = sanitize_key( implode( '-', array( 'template', $template_name, $template_path, $default_path, COMMERCIOO_VERSION ) ) );
    $template  = (string) wp_cache_get( $cache_key, 'commercioo' );
    if ( ! $template ) {
        $template = commercioo_locate_template( $template_name, $template_path, $default_path );
        wp_cache_set( $cache_key, $template, 'commercioo' );
    }

    // Allow 3rd party plugin filter template file from their plugin.
    $filter_template = apply_filters( 'commercioo_get_template', $template, $template_name, $args, $template_path, $default_path );

    if ( $filter_template !== $template ) {
        if ( ! file_exists( $filter_template ) ) {
            /* translators: %s template */
            commercioo_doing_it_wrong( __FUNCTION__, sprintf( __( '%s does not exist.', 'commercioo' ), '<code>' . $template . '</code>' ), '2.1' );
            return;
        }
        $template = $filter_template;
    }

    $action_args = array(
        'template_name' => $template_name,
        'template_path' => $template_path,
        'located'       => $template,
        'args'          => $args,
    );

    if ( ! empty( $args ) && is_array( $args ) ) {
        if ( isset( $args['action_args'] ) ) {
            commercioo_doing_it_wrong(
                __FUNCTION__,
                __( 'action_args should not be overwritten when calling commercioo_get_template.', 'commercioo' ),
                '3.6.0'
            );
            unset( $args['action_args'] );
        }
        extract( $args ); // @codingStandardsIgnoreLine
    }

    do_action( 'commercioo_before_template_part', $action_args['template_name'], $action_args['template_path'], $action_args['located'], $action_args['args'] );

    include $action_args['located'];

    do_action( 'commercioo_after_template_part', $action_args['template_name'], $action_args['template_path'], $action_args['located'], $action_args['args'] );
}