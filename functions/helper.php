<?php
function comm_controller()
{
    return \Commercioo\controller::get_instance();
}

function comm_parsing()
{
    return \Commercioo\Parsing\Commercioo_Parsing_Tags::get_instance();
}

function comm_get_id_product()
{
    $product = \commercioo\admin\Comm_Product::get_instance();
    return $product->get_the_id();
}

function is_comm_pro()
{
    return comm_controller()->is_comm_pro();
}

function is_comm_wa()
{
    return comm_controller()->is_comm_wa();
}

function is_comm_wa_followup()
{
    return comm_controller()->is_comm_wa_followup();
}

function is_comm_ar()
{
    return comm_controller()->is_comm_ar();
}

function is_comm_ongkir()
{
    return comm_controller()->is_comm_ongkir();
}

function is_comm_auto_reg()
{
    return comm_controller()->is_comm_auto_reg();
}

function comm_get_option($key, $index, $mixed = false)
{
    if (!empty($index)) {
        $option = get_option('comm_' . $key . '_settings',$mixed);
        return (is_array($option) && !empty($option) && isset($option[$index]) && $option[$index] != '') ? $option[$index] : '';
    } else {
        $option = get_option($key, $mixed);
        return $option;
    }
}

//Encode string utulity
function comm_url_encode($string)
{
    if (!is_array($string)) {
        return urlencode(base64_encode($string));
    }
}

//Decode string utility
function comm_url_decode($string)
{
    if (!is_array($string)) {
        return base64_decode(urldecode($string));
    }

}

//Get currency pattern based on general settingss
function comm_get_currency_pattern()
{
    global $comm_options;
    return [
        'prefix' => ($comm_options['currency_position'] == 'prefix') ? $comm_options['currency_symbol']
            : '',
        'suffix' => ($comm_options['currency_position'] == 'suffix') ? $comm_options['currency_symbol'] : '',
        'position' => ($comm_options['currency_position'] == 'suffix') ? $comm_options['currency_symbol'] : 'prefix',
        'thousand' => (isset($comm_options['currency_thousand'])) ? $comm_options['currency_thousand'] : '.',
        'decimal' => (isset($comm_options['currency_decimal'])) ? $comm_options['currency_decimal'] : ',',
        'decimal_limit' => (isset($comm_options['currency_decimal_limit'])) ? $comm_options['currency_decimal_limit']
            : '0',
    ];
}

function comm_get_space_currency()
{
    return "&nbsp;";
}

//Setup currency format
function comm_money_format($number) {
    $pattern = comm_get_currency_pattern();

    if ($pattern['prefix']) {
        return html_entity_decode($pattern['prefix']) . ' ' . number_format((float)$number, (int)$pattern['decimal_limit'], $pattern['decimal'],
                $pattern['thousand']);
    } elseif ($pattern['suffix']) {
        return number_format((float)$number, (int)$pattern['decimal_limit'], $pattern['decimal'],
                $pattern['thousand']) . ' ' . html_entity_decode($pattern['suffix']);
    } else {
        return html_entity_decode($pattern['prefix']) . ' ' . number_format((float)$number, (int)$pattern['decimal_limit'], $pattern['decimal'],
                $pattern['thousand']);
    }
}

function comm_money_without_currency($number) {
    $pattern = comm_get_currency_pattern();

    return number_format((float)$number, (int)$pattern['decimal_limit'], $pattern['decimal'], $pattern['thousand']);
}

//Get timezone based on GMT offset
function comm_get_timezone() {
    $timezone_string = get_option( 'timezone_string' );

    if ( ! empty( $timezone_string ) ) {
        return $timezone_string;
    }
	
    $offset  = get_option( 'gmt_offset' );
    $hours   = ( int ) $offset;
    $minutes = abs( ( $offset - ( int ) $offset ) * 60 );
    $offset  = sprintf( '%+03d:%02d', $hours, $minutes );
    
	if ( is_string( $offset ) ) {
        $offset = "UTC" . $offset;
    }

    return $offset;
}

if (!function_exists('comm_get_fb_fixel_event')) {
    /*========*/
    function comm_get_fb_fixel_event()
    {
        $settings = [
            [
                'name' => __("Add Payment Info", "commercioo_settings"),
                'value' => __("AddPaymentInfo", "commercioo_settings"),
            ],
            [
                'name' => __("Add To Cart", "commercioo_settings"),
                'value' => __("AddToCart", "commercioo_settings"),
            ],
            [
                'name' => __("Add To Wishlist", "commercioo_settings"),
                'value' => __("AddToWishlist", "commercioo_settings"),
            ],
            [
                'name' => __("Contact", "commercioo_settings"),
                'value' => __("Contact", "commercioo_settings"),
            ],
            [
                'name' => __("Customize Product", "commercioo_settings"),
                'value' => __("CustomizeProduct", "commercioo_settings"),
            ],
            [
                'name' => __("Donate", "commercioo_settings"),
                'value' => __("Donate", "commercioo_settings"),
            ],
            [
                'name' => __("Find Location", "commercioo_settings"),
                'value' => __("FindLocation", "commercioo_settings"),
            ],
            [
                'name' => __("Initiate Checkout", "commercioo_settings"),
                'value' => __("InitiateCheckout", "commercioo_settings"),
            ],
            [
                'name' => __("Lead", "commercioo_settings"),
                'value' => __("Lead", "commercioo_settings"),
            ],
            [
                'name' => __("Page View", "commercioo_settings"),
                'value' => __("PageView", "commercioo_settings"),
            ],
            [
                'name' => __("Purchase", "commercioo_settings"),
                'value' => __("Purchase", "commercioo_settings"),
            ],
            [
                'name' => __("Schedule", "commercioo_settings"),
                'value' => __("Schedule", "commercioo_settings"),
            ],
            [
                'name' => __("Search", "commercioo_settings"),
                'value' => __("Search", "commercioo_settings"),
            ],
            [
                'name' => __("Start Trial", "commercioo_settings"),
                'value' => __("StartTrial", "commercioo_settings"),
            ],
            [
                'name' => __("Submit Application", "commercioo_settings"),
                'value' => __("SubmitApplication", "commercioo_settings"),
            ],
            [
                'name' => __("Subscribe", "commercioo_settings"),
                'value' => __("Subscribe", "commercioo_settings"),
            ],
            [
                'name' => __("View Content", "commercioo_settings"),
                'value' => __("ViewContent", "commercioo_settings"),
            ],
        ];

        return $settings;
    }
}

if (!function_exists('comm_get_sub_menu_filter')) {
    function comm_get_sub_menu_filter()
    {
        return comm_controller()->comm_sub_menu_filter();
    }
}
if (!function_exists('comm_get_checkout_uri')) {
    function comm_get_checkout_uri()
    {
        $id = get_option('commercioo_Checkout_page_id');
        return esc_url(get_permalink($id));
    }
}
if (!function_exists('comm_get_cart_uri')) {
    function comm_get_cart_uri()
    {
        $id = get_option('commercioo_Cart_page_id');
        return esc_url(get_permalink($id));
    }
}
if (!function_exists('comm_get_shopping_uri')) {
    function comm_get_shopping_uri()
    {
        $id = get_option('commercioo_Product_page_id');
        return esc_url(get_permalink($id));
    }
}
if (!function_exists('comm_get_account_uri')) {
    function comm_get_account_uri($endpoint = '')
    {
        $id = get_option('commercioo_Account_page_id');
        return esc_url(get_permalink($id) . $endpoint);
    }
}
if (!function_exists('comm_get_thank_you_uri')) {
    function comm_get_thank_you_uri($endpoint = '')
    {
        $id = apply_filters('commercioo_thank_you_page_id', get_option('commercioo_thank_you_page_id'));
        return esc_url(get_permalink($id) . $endpoint);
    }
}
if (!function_exists('comm_add_notice')) {
    /**
     * Add notice
     *
     * @param string $message Notice message.
     * @param string $type Notice type.
     * @param array $field_key Field key.
     */
    function comm_add_notice($message, $type = 'success', $field_key=array())
    {
        $notices = get_query_var('comm_notices', array());
        $notices[] = array(
            'message' => $message,
            'type' => $type
        );
        set_query_var('comm_notices', $notices);
    }
}
if (!function_exists('comm_notice_count')) {
    /**
     * Get notice count
     *
     * @param  string $type Notice type.
     * @return integer       Notice count.
     */
    function comm_notice_count($type = '')
    {
        $notices = get_query_var('comm_notices', array());
        if (empty($type)) {
            return count($notices);
        }
        $count = 0;
        foreach ($notices as $notice) {
            if ($type === $notice['type']) {
                $count++;
            }
        }
        return $count;
    }
}
if (!function_exists('comm_print_notices')) {
    /**
     * Print all notices
     */
    function comm_print_notices()
    {
        $notices = (get_query_var('comm_notices', array())) ? get_query_var('comm_notices', array()) : comm_get_transient("comm_notices_transient_purchase");
        if (is_array($notices)) {
            foreach ($notices as $notice) {
                $type = 'error' === $notice['type'] ? 'danger' : $notice['type'];
                ?>
                <div class="alert alert-<?php echo esc_attr($type); ?>">
                    <span type="button" class="close c-btn-alert-close" data-bs-dismiss="alert" aria-hidden="true">×</span>
                    <?php echo wp_kses_post($notice['message']); ?>
                </div>
                <?php
            }
        }
    }
}
if (!function_exists('comm_get_transient')) {
    /**
     * get_transient: comm_notices_transient
     * @param $transient
     * @return mixed
     */
    function comm_get_transient($transient)
    {
        $notices = get_transient($transient);
        if ($notices) {
            if (is_array($notices)) {
                return $notices;
            }
        }
    }
}
if (!function_exists('comm_set_transient')) {
    /**
     * comm_set_transient
     * @param $args
     * @param $transient
     * @param int $expiration : default 10 second
     */
    function comm_set_transient($transient, $args, $expiration = 10)
    {
        $notices = get_transient($transient);
        $notices[] = $args;
        set_transient($transient, $notices, $expiration);
    }
}
if (!function_exists('comm_payment_method_label')) {
    /**
     * Get peyment method label
     *
     * @param  string $method Payment method code.
     * @return string         Payment method label.
     */
    function comm_payment_method_label($order_id)
    {
        $order = new \Commercioo\Models\Order($order_id);
        $method = $order->get_payment_method();
        if ('bacs' === $method) {
            $method = "Bank Transfer";
        } elseif ('paypal' === $method) {
            $method = "Paypal";
        }else{
            $method = $order->get_payment_method_name();
        }
        return esc_html($method);
    }
}

if (!function_exists('comm_template_path')) {
    /**
     * Get the template path.
     *
     * @return string
     */
    function comm_template_path()
    {
        return apply_filters('commercioo_template_path', 'commercioo/');
    }
}
if (!function_exists('is_comm_product_taxonomy')) {
    /**
     * Is_product_taxonomy - Returns true when viewing a product taxonomy archive.
     *
     * @return bool
     */
    function is_comm_product_taxonomy()
    {
        return is_tax(get_object_taxonomies('comm_product'));
    }
}

if (!function_exists('comm_get_template_part')) {
    /**
     * Get template part (for templates like the shop-loop).
     *
     * COMM_TEMPLATE_DEBUG_MODE will prevent overrides in themes from taking priority.
     *
     * @param mixed $slug Template slug.
     * @param string $name Template name (default: '').
     */
    function comm_get_template_part($slug, $name = '')
    {
        $cache_key = sanitize_key(implode('-', array('template-part', $slug, $name, COMMERCIOO_VERSION)));
        $template = (string)wp_cache_get($cache_key, 'commercioo');

        if (!$template || $template == "") {

            if ($name) {
                $template = locate_template(
                    array(
                        "{$slug}-{$name}.php",
                        comm_template_path() . "{$slug}-{$name}.php",
                    )
                );

                if (!$template) {
                    $fallback = untrailingslashit(COMMERCIOO_PATH) . "/templates/{$slug}-{$name}.php";
                    $template = file_exists($fallback) ? $fallback : '';
                }
            }

            if (!$template) {
                // If template file doesn't exist, look in yourtheme/slug.php and yourtheme/commercioo/slug.php.
                $template = locate_template(
                    array(
                        "{$slug}.php",
                        comm_template_path() . "{$slug}.php",
                    )
                );
            }

            wp_cache_set($cache_key, $template, 'commercioo');
        }

        // Allow 3rd party plugins to filter template file from their plugin.
        $template = apply_filters('comm_get_template_part', $template, $slug, $name);

        if ($template) {
            load_template($template, false);
        }
    }
}
if (!function_exists('comm_status_order_label')) {
    /**
     * Get peyment method label
     *
     * @param  string $method Payment method code.
     * @return string         Payment method label.
     */
    function comm_status_order_label($status)
    {

        if ($status == "comm_pending") {
            $status = "Pending";
        } elseif ($status == "comm_processing") {
            $status = "Processing";
        } elseif ($status == "comm_completed") {
            $status = "Completed";
        } elseif ($status == "comm_refunded") {
            $status = "Refund";
        } elseif ($status == "comm_failed") {
            $status = "Failed";
        } elseif ($status == "comm_abandoned") {
            $status = "Abandoned";
        } else {
            $status = "Pending";
        }
        return $status;
    }
}

if (!function_exists('comm_calculate_order_sales_product')) {
    function comm_calculate_order_sales_product()
    {
        global $wpdb;
        $commercioo_order_items = $wpdb->prefix . 'commercioo_order_items';
        $posts = $wpdb->prefix . 'posts';

        $order_items = $wpdb->get_results("SELECT 
	wc.product_id,
    (SELECT post_title from $posts WHERE ID = wc.product_id) as post_title,    
   (SELECT COUNT($posts.ID) FROM $commercioo_order_items, $posts where $commercioo_order_items.order_id = $posts.ID and $commercioo_order_items.product_id = wc.product_id and $posts.post_status in ('comm_pending','comm_processing')) as orders,
(SELECT COUNT($posts.ID) FROM $commercioo_order_items, $posts where $commercioo_order_items.order_id = $posts.ID and $posts.post_status = 'comm_completed' and $commercioo_order_items.product_id = wc.product_id) as sales,
(SELECT COUNT($posts.ID) FROM $commercioo_order_items, $posts where $commercioo_order_items.order_id = $posts.ID and $commercioo_order_items.product_id = wc.product_id and $posts.post_status in ('comm_pending','comm_processing','comm_completed','comm_refunded')) as torders,
((SELECT COUNT($posts.ID) FROM $commercioo_order_items, $posts where $commercioo_order_items.order_id = $posts.ID and $posts.post_status = 'comm_completed' and $commercioo_order_items.product_id = wc.product_id) / (SELECT COUNT($posts.ID) FROM $commercioo_order_items, $posts where $commercioo_order_items.order_id = $posts.ID and $commercioo_order_items.product_id = wc.product_id and $posts.post_status in ('comm_pending','comm_processing','comm_completed','comm_refunded')) *100) as rate,
 (SELECT count($posts.post_author)  FROM $commercioo_order_items, $posts where $commercioo_order_items.order_id = $posts.ID and $posts.post_status = 'comm_completed' and $commercioo_order_items.product_id = wc.product_id) as customer, 
 (SELECT COALESCE(sum($commercioo_order_items.item_order_qty * $commercioo_order_items.item_price),0)  FROM $commercioo_order_items, $posts where $commercioo_order_items.order_id = $posts.ID and $posts.post_status = 'comm_completed' and $commercioo_order_items.product_id = wc.product_id) as revenue  
FROM $commercioo_order_items wc
    INNER JOIN $posts wp ON wc.order_id = wp.ID
    INNER JOIN $posts wps ON wc.product_id  = wps.ID
GROUP BY wc.product_id
order by orders desc"
        );
        if ($order_items) {
            return $order_items;
        } else {
            return false;
        }
    }
}

if ( ! function_exists( 'comm_default_thankyou_message' ) ) {
	function comm_default_thankyou_message() {
		// default thankyou message
		ob_start();
		?>
		<p><?php esc_html_e( 'Terima kasih, pesanan Anda sudah kami terima. Silakan transfer sejumlah total:', 'commercioo' ); ?></p>
        <p>{total}</p>
        <p><?php esc_html_e( 'PENTING! Harap transfer sesuai sampai dengan 3 digit terakhir.', 'commercioo' ); ?></p>
        <p><?php esc_html_e( 'ke salah satu bank kami berikut agar kami segera mengirimkan pesanan Anda:', 'commercioo' ); ?></p>
		<?php
		return ob_get_clean();
	}
}

function comm_get_product( $product_id ) {
    return apply_filters( 'comm_get_product', new \Commercioo\Models\Product( $product_id ), $product_id );
}

function comm_get_order( $order_id ) {
    return new \Commercioo\Models\Order( $order_id );
}

if ( ! function_exists( 'comm_get_account_menus' ) ) {
    function comm_get_account_menus() {
        return apply_filters( 
            'comm_account_subpages',
            array( 
                'dashboard', 
                'order-history', 
                'order-detail', 
                'addresses', 
                'edit-address', 
                'edit-profile', 
                'forgot-password', 
                'logout' 
            ) 
        );
    }
}

/**
 * Function that check if page is archive product page
 * @return Bool
 */
if ( ! function_exists( 'is_comm_archive_product_page' ) ) {
    function is_comm_archive_product_page() {
        return get_the_ID() == get_option( 'commercioo_Product_page_id' ) ? true : false;
    }
}

/**
 * Function that check if page is cart product page
 * @return Bool
 */
if ( ! function_exists( 'is_comm_cart_page' ) ) {
    function is_comm_cart_page() {
        return get_the_ID() == get_option( 'commercioo_Cart_page_id' ) ? true : false;
    }
}

/**
 * Function that check if page is checkout page
 * @return Bool
 */
if ( ! function_exists( 'is_comm_checkout_page' ) ) {
    function is_comm_checkout_page() {
        return get_the_ID() == get_option( 'commercioo_Checkout_page_id' ) ? true : false;
    }
}

/**
 * Function that check if page is account product page
 * @return Bool
 */
if ( ! function_exists( 'is_comm_account_page' ) ) {
    function is_comm_account_page()
    {
        return get_the_ID() == get_option('commercioo_Account_page_id') ? true : false;
    }
}
if (!function_exists('comm_get_all_status_order')) {
    /**
     * Get all status orders
     *
     * @param  array  $status_order Arguments.
     * @return array Status Orders.
     */
    function comm_get_all_status_order(){
        $status_order = array(
            'comm_pending', 'comm_processing', 'comm_completed', 'comm_refunded','comm_failed','comm_abandoned'
        );
        return $status_order;
    }
}

function comm_get_php_arg_separator_output() {
    return ini_get( 'arg_separator.output' );
}
/**
 * Test debug and record into WP DEBUG Log File
 */
function com_write_log ( $log )  {
    if ( true === WP_DEBUG ) {
        if ( is_array( $log ) || is_object( $log ) ) {
            error_log( print_r( $log, true ) );
        } else {
            error_log( $log );
        }
    }
}
function comm_chec_product_type($product_id=0){
     $_product_type = get_post_meta($product_id,"_product_type",true);
     return isset($_product_type)?$_product_type:'physical';
}
if(!function_exists("is_cart_page")){
    function is_cart_page(){
        $id = get_option('commercioo_Cart_page_id');
        if(isset($_POST['post_id'])){
            if($id ===$_POST['post_id']){
                return true;
            }
        }
        if(isset($_GET['post_id'])){
            if($id ===$_GET['post_id']){
                return true;
            }
        }
        $is_valid = false;
        if(is_page($id)){
            $is_valid = true;
        }
        return $is_valid;
    }
}
if(!function_exists("is_checkout_page")){
    function is_checkout_page(){
        $id = get_option('commercioo_Checkout_page_id');
        if(isset($_POST['post_id'])){
            if($id ===$_POST['post_id']){
                return true;
            }
        }
        $is_valid = false;
        if(is_page($id)){
            $is_valid = true;
        }
        return $is_valid;
    }
}
if(!function_exists("is_page_has_elementor")) {
    function is_page_has_elementor()
    {
        $post_id = get_the_ID();
        if (isset($_POST['post_id'])) {
            $post_id = $_POST['post_id'];
        }
        if (isset($_GET['post_id'])) {
            $post_id = $_GET['post_id'];
        }

        if(!empty($post_id) && defined('ELEMENTOR_VERSION') && \Elementor\Plugin::$instance->documents->get($post_id)->is_built_with_elementor()){
            return true;
        }else{
            return false;
        }
    }
}
if(!function_exists("array_keys_exist")){
    /**
     * Checks if multiple keys exist in an array
     *
     * @param array|string $keys
     * @param array $arr
     * @return bool
     */
    function array_keys_exist(array $keys, array $arr) {
        return !array_diff_key(array_flip($keys), $arr);
    }
}

if ( ! function_exists( 'is_comm_product_single' ) ) {
    function is_comm_product_single() {
        return is_singular( 'comm_product' );
    }
}