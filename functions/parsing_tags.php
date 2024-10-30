<?php
if (!function_exists("comm_template_tags")) {
    /**
     * Email Template Tags
     *
     * @since 1.0
     *
     * @param string $message Message with the template tags
     * @param array $payment_data Payment Data
     * @param int $payment_id Payment ID
     * @param bool $admin_notice Whether or not this is a notification email
     *
     * @return string $message Fully formatted message
     */
    function comm_template_tags($message, $payment_data, $payment_id, $admin_notice = false)
    {
        return comm_do_parsing_tags($message, $payment_id);
    }
}
if (!function_exists("comm_add_parsing_tag")) {
    /**
     * Add an email tag
     *
     * @since 1.9
     *
     * @param string $tag Email tag to be replace in email
     * @param callable $func Hook to run when email tag is found
     */
    function comm_add_parsing_tag($tag, $description, $func)
    {
        comm_parsing()->add($tag, $description, $func);
    }
}
if (!function_exists('comm_do_parsing_tags')) {
    /**
     * Search content for email tags and filter email tags through their hooks
     *
     * @param string $content Content to search for parsing tags
     * @param int $order_id The Order id
     *
     * @since 1.9
     *
     * @return string Content with parsing tags filtered out.
     */
    function comm_do_parsing_tags($content, $order_id)
    {
        // Replace all tags
        $content = comm_parsing()->do_tags($content, $order_id);

        // Maintaining backwards compatibility
        $content = apply_filters('comm_template_tags', $content, $order_id);

        // Return content
        return $content;
    }
}
if (!function_exists("comm_tag_user_email")) {
    /**
     * Parsing template tag: name
     * The buyer's first name
     *
     * @param int $order_id
     *
     * @return string name
     */
    function comm_tag_user_email($order_id)
    {
        $order = new \Commercioo\Models\Order($order_id);
        return $order->get_customer()->get_user()->data->user_email;
    }
}
if (!function_exists("comm_tag_date")) {
    /**
     * Parsing template tag: date
     * Date of purchase
     *
     * @param int $order_id
     *
     * @return string date
     */
    function comm_tag_date($order_id)
    {
        return date_i18n(get_option('date_format'), strtotime(get_the_date($order_id)));
    }
}
if (!function_exists("comm_tag_order_id")) {
    /**
     * Parsing template tag: order_id
     * The unique ID number for this purchase
     *
     * @param int $order_id
     *
     * @return int order_id
     */
    function comm_tag_order_id($order_id)
    {
        return $order_id;
    }
}
if (!function_exists("comm_tag_subtotal")) {
    /**
     * Parsing template tag: subtotal
     * Grand Total of purchase
     *
     * @param int $order_id
     *
     * @return string subtotal
     */
    function comm_tag_subtotal($order_id)
    {
		$order = new \Commercioo\Models\Order($order_id);
		
		/**
		 * Should be `get_subtotal()`
		 * but we accidentally already used this tag in another strings
		 * so be it :)
		 */ 
		$subtotal = esc_html(comm_money_format($order->get_total()));
		
        return html_entity_decode($subtotal, ENT_COMPAT, 'UTF-8');
    }
}
if (!function_exists("comm_tag_total")) {
    /**
     * Parsing template tag: total
     * Grand Total of purchase
     *
     * @param int $order_id
     *
     * @return string total
     */
    function comm_tag_total($order_id)
    {
        $order = new \Commercioo\Models\Order($order_id);
        $total = comm_money_without_currency($order->get_total());
        $total_number = $order->get_total();
        $_is_3digit_total = false;

        if($total_number>15000){
            $_is_3digit_total = true;
        }
        $html = '<div class="commercioo_total_typ">';
        if($_is_3digit_total){
            $_3digit_total = substr($total, -3);
            $is_total = substr(esc_html(comm_money_format($order->get_total())), 0, -3);
            $html .= '<span class="c-text-hero c-commercioo-color c-bg-yellow" data-3digit="'.$_3digit_total.'">' . $is_total . '</span>';
        }else{
            $totals = esc_html(comm_money_format($order->get_total()));

            $html .= '<span class="c-text-hero c-commercioo-color">' . $totals . '</span>';
        }

        $html .= '<span class="d-none commercioo_total">' . $order->get_total() . "</span>";
        $html .= '<span class="c-copy-to-clip c-copy-to-clip-total" data-bs-container="body"
                                      data-bs-toggle="popover"
                                      data-bs-placement="top"
                                      data-bs-trigger="hover"
                                      data-bs-content="' . __("Click to copy to clipboard", "commercioo") . '">
                                    <i class="fa fa-clone"></i>
                                </span>';
        $html .= "</div>";
        return html_entity_decode($html, ENT_COMPAT, 'UTF-8');
    }
}
if (!function_exists("comm_tag_payment_method")) {
    /**
     * Parsing template tag: payment_method
     * The method of order used for this purchase
     *
     * @param int $order_id
     *
     * @return string gateway
     */
    function comm_tag_payment_method($order_id)
    {
        return esc_html(comm_payment_method_label($order_id));
    }
}
if (!function_exists("comm_tag_status_order")) {
    /**
     * Parsing template tag: status_order
     * The Status Order
     *
     * @param int $order_id
     *
     * @return string gateway
     */
    function comm_tag_status_order($order_id)
    {
        $status = get_post_status($order_id);
        return esc_html(comm_status_order_label($status));
    }
}
if (!function_exists("comm_tag_bank")) {
    /**
     * Parsing template tag: bank
     * The bank list of order used for this purchase
     *
     * @param int $order_id
     *
     * @return string gateway
     */
    function comm_tag_bank($order_id)
    {
        global $comm_options;
        $order = new \Commercioo\Models\Order($order_id);
        $html = '';

        if ('bacs' === $order->get_payment_method() && 'pending' === $order->get_order_status() && isset($comm_options['bank_transfer']) && !empty($comm_options['bank_transfer'])) :
            $html = '<div><ul>';
            foreach ($comm_options['bank_transfer'] as $bank) :
                $html .= '<li>Bank <strong>' . esc_html($bank['bank_name']) . '&nbsp;' . esc_html($bank['account_number']) . '</strong> - ' . esc_html($bank['account_name']) . '</li>';
            endforeach;
            $html .= '</ul></div>';
        endif;
        return $html;
    }
}
if (!function_exists("comm_tag_order_detail")) {
    /**
     * Parsing template tag: order_detail
     * The order detail of order used for this purchase
     *
     * @param int $order_id
     *
     * @return string gateway
     */
    function comm_tag_order_detail($order_id)
    {
        $order = new \Commercioo\Models\Order($order_id);
        $html = '<div class="mt-4">
        <div class="order-detail-head">ORDER DETAILS</div>
        <table class="table table-border thank-you-general">
            <thead class="thead-details">
            <tr>
                <th scope="col" class="one-column">Product</th>
                <th scope="col" class="two-column">Total</th>
            </tr>
            </thead>
            <tbody>';
        foreach ($order->get_order_items() as $item) :
            $html .= '<tr>
                    <td><a href="' . get_permalink($item->product_id) . '">' . esc_html(get_the_title($item->product_id))
                . ' x ' . esc_html($item->item_order_qty) . '</a></td>
                    <td class="two-column">' . esc_html(comm_money_format($item->item_order_qty * floatval
                    ($item->item_price))) . '</td>
                </tr>';
        endforeach;
        $html .= '</tbody>';
        $html .= '<tfoot>
            <tr>
                <th>Subtotal:</th>
                <td class="two-column">' . esc_html(comm_money_format($order->get_subtotal())) . '</td>
            </tr>';

        $html .= '<tr>
                <th>Payment method:</th>
                <td class="two-column">' . esc_html(comm_payment_method_label($order->get_order_id())) . '</td>
            </tr>
            <tr class="column-total">
                <th>Total:</th>
                <th class="two-column">' . esc_html(comm_money_format($order->get_total())) . '</th>
            </tr>
            </tfoot>
        </table>
    </div>';
        return $html;
    }
}
if (!function_exists("comm_tag_user_billing_address")) {
    /**
     * Parsing template tag: user_billing_address
     * The user billing address of order used for this purchase
     *
     * @param int $order_id
     *
     * @return string gateway
     */
    function comm_tag_user_billing_address($order_id)
    {
		global $comm_country;

        ob_start();
        $order = new \Commercioo\Models\Order($order_id);
        $billing_address = $order->get_billing_address();
        ?>
        <div class="comm-billing-address-line">
            <div class="order-detail-head">BILLING ADDRESS</div>
            <div><?php echo wp_kses_post($order->get_formatted_address( 'billing', '</div><div>' )); ?></div>
        </div>
        <?php
        return ob_get_clean();
    }
}
if (!function_exists("comm_tag_user_shipping_address")) {
	/**
	 * Parsing template tag: user_shipping_address
	 * The user shipping of order used for this purchase
     *
	 * @param int $order_id
     *
	 * @return string gateway
     */
	function comm_tag_user_shipping_address($order_id)
    {
		global $comm_country;
		
        ob_start();
        $order = new \Commercioo\Models\Order($order_id);
        $shipping_address = $order->get_shipping_address();
        ?>
        <div>
            <div class="order-detail-head">SHIPPING ADDRESS</div>
            <div><?php echo wp_kses_post($order->get_formatted_address( 'shipping', '</div><div>' )); ?></div>
        </div>
        <?php
        return ob_get_clean();
    }
}
if (!function_exists("comm_tag_user_shipping_billing_address")) {
	function comm_tag_user_shipping_billing_address( $order_id ) {
		ob_start();
		?>
		<div class="commercioo-checkout-form-grid source-sans-pro">
			<div>
				<?php echo wp_kses_post(comm_tag_user_billing_address( $order_id )) ?>
			</div>
			<div>
				<?php echo wp_kses_post(comm_tag_user_shipping_address( $order_id )) ?>
			</div>
		</div>
		<?php
		return ob_get_clean();
	}
}
if (!function_exists("comm_tag_sitename")) {
    /**
     * Parsing template tag: sitename
     * The Site Name of your blog
     *
     * @param int $order_id
     *
     * @return string gateway
     */
    function comm_tag_sitename( $order_id = null ) {
		$general_settings = get_option( 'comm_general_settings', array() );

		// use the commercioo setting's value or wordpress's 
		if ( isset( $general_settings['store_name'] ) && trim( $general_settings['store_name'] ) != '' ) {
			$sitename = $general_settings['store_name'];
		}
		else {
			$sitename = get_bloginfo( 'name' );
		}

        return $sitename;
    }
}
if (!function_exists("comm_tag_username")) {
    /**
     * Parsing template tag: username
     * The username of order used for this purchase
     *
     * @param int $order_id
     *
     * @return string gateway
     */
    function comm_tag_username($order_id)
    {
        $order = new \Commercioo\Models\Order($order_id);
        return $order->get_customer()->get_user()->data->user_login;
    }
}
if (!function_exists("comm_tag_fullname")) {
    /**
     * Parsing template tag: fullname
     * The user full name, first and last used for this purchase
     *
     * @param int $order_id
     *
     * @return string gateway
     */
    function comm_tag_fullname($order_id)
    {
        $order = new \Commercioo\Models\Order($order_id);
        $user_id = $order->get_customer()->get_user()->data->ID;
        $first_name = get_user_meta($user_id, "first_name", true);
        $last_name = get_user_meta($user_id, "last_name", true);
        return $first_name . "&nbsp;" . $last_name;
    }
}
if (!function_exists("comm_tag_name")) {
    /**
     * Parsing template tag: name
     * The user first name for this purchase
     *
     * @param int $order_id
     *
     * @return string gateway
     */
    function comm_tag_name($order_id) {
        $order = new \Commercioo\Models\Order($order_id);
        $user_id = $order->get_customer()->get_user()->data->ID;
		$first_name = get_user_meta($user_id, "first_name", true);

        return $first_name;
    }
}
if ( ! function_exists( 'comm_tag_login_url' ) ) {
    /**
     * Parsing template tag: login_url
     * The website's login url
     *
     * @return string gateway
     */
    function comm_tag_login_url( $order_id = null ) {
		$page_id   = get_option( 'commercioo_Account_page_id', false );
		$permalink = get_the_permalink( $page_id );		
		$permalink = $permalink ? $permalink : site_url();
		
		return sprintf( '<a href="%1$s">%1$s</a>', $permalink );
    }
}
if ( ! function_exists( 'comm_tag_admin_name' ) ) {
    /**
     * Parsing template tag: admin_name
     * The website's admin name
     *
     * @return string gateway
     */
    function comm_tag_admin_name( $order_id = null ) {
		$users = get_users( 'role=administrator' );
		return $users[0]->display_name;
    }
}
if ( ! function_exists( 'comm_tag_support_email' ) ) {
    /**
     * Parsing template tag: support_email
     * The website's support email
     *
     * @return string gateway
     */
    function comm_tag_support_email( $order_id = null ) {
		$email = get_option( 'admin_email', null );
		return sprintf( '<a href="mailto:%1$s">%1$s</a>', $email );
    }
}
if ( ! function_exists( 'comm_tag_order_details' ) ) {
    /**
     * Parsing template tag: order_details
     * The order details of the purchase
     *
     * @param int $order_id
     *
     * @return string gateway
     */
    function comm_tag_order_details( $order_id ) {
		$order = new \Commercioo\Models\Order( $order_id );
		ob_start();
		?>
		<p>
			<?php
			foreach ( $order->get_order_items() as $item ) :
				echo esc_html( 
					get_the_title( $item->product_id ) . ' x ' . $item->item_order_qty . ': ' . 
					comm_money_format( $item->item_order_qty * floatval( $item->item_price ) ) 
				) . '<br>';
			endforeach;
			?>
			<?php esc_html_e( 'Subtotal', 'commercioo' ) ?>: <?php echo esc_html( comm_money_format( $order->get_subtotal() ) ) ?><br>
			<?php esc_html_e( 'TOTAL', 'commercioo' ) ?>: <strong><?php echo esc_html( comm_money_format( $order->get_subtotal() ) ) ?></strong>
		</p>
		<?php
        return apply_filters( 'commercioo_order_details_tag', ob_get_clean(), $order_id );
    }
}
if ( ! function_exists( 'comm_tag_user_address' ) ) {
    /**
     * Parsing template tag: user_address
     * The address of the user's
     *
     * @param int $order_id
     *
     * @return string gateway
     */
    function comm_tag_user_address( $order_id ) {
        $order 			  = new \Commercioo\Models\Order($order_id);
		$shipping_address = $order->get_shipping_address();
		
		if ( $shipping_address ) {
			$address = comm_tag_user_shipping_address( $order_id );
		}
		else {
			$address = comm_tag_user_billing_address( $order_id );
		}

		return apply_filters( 'commercioo_user_address_tag', $address, $order_id );
    }
}
if (!function_exists("comm_tag_bank_info")) {
    /**
     * Parsing template tag: bank
     * The bank list of order used for this purchase
     *
     * @param int $order_id
     *
     * @return string gateway
     */
    function comm_tag_bank_info($order_id)
    {
        global $comm_options;
        $order = new \Commercioo\Models\Order($order_id);
        $html = '';
        if ('bacs' === $order->get_payment_method() && 'pending' === $order->get_order_status() && isset($comm_options['bank_transfer']) && !empty($comm_options['bank_transfer'])) :
            $html .='<div class="commercioo-thankyou-list-bank-account">';
            foreach ($comm_options['bank_transfer'] as $bank) :
                $bank_name = isset($bank['bank_name']) && !empty($bank['bank_name']) ?esc_html($bank['bank_name']):'';
                $account_number = isset($bank['account_number']) && !empty($bank['account_number']) ?esc_html($bank['account_number']):'';
                $account_name = isset($bank['account_name']) && !empty($bank['account_name']) ?esc_html($bank['account_name']):'';
                $branch_name = isset($bank['branch_name']) && !empty($bank['branch_name']) ?esc_html($bank['branch_name']):'';
                $html .= '<div class="bank-item">';
                $html .= '<p class="commercioo-checkout-description-product bank-name">Bank: <span class="c-semibold">' . $bank_name . '</span></p>';
                $html .= '<p class="commercioo-checkout-description-product account-number">No. rek: <span class="c-semibold commercioo-copy-bank-account">' . $account_number.'</span>';
                $html .= '<span class="c-copy-to-clip c-copy-bank-account" data-bs-container="body"
                                      data-bs-toggle="popover"
                                      data-bs-placement="top"
                                      data-bs-trigger="hover"
                                      data-bs-content="' . __("Click to copy to clipboard", "commercioo") . '">
                                    <i class="fa fa-clone"></i>
                                </span>';
                $html .='</p>';
                $html .= '<p class="commercioo-checkout-description-product owner-name">Atas nama: <span class="c-semibold">' . $account_name . '</span></p>';
                $html .= '<p class="commercioo-checkout-description-product owner-name">Branch: <span class="c-semibold">' . $branch_name . '</span></p>';
                $html .= '</div>';
            endforeach;
            $html .= '</div>';
        endif;
        return $html;
    }
}

if (!function_exists("comm_tag_konfirmasi_pembayaran_bank")) {
    function comm_tag_konfirmasi_pembayaran_bank($order_id)
    {
        global $comm_options;
        $order = new \Commercioo\Models\Order($order_id);
        $html = '';
        $return_url = \Commercioo\Helper::commercioo_get_endpoint_url( 'commercioo-confirmation-payment', $order_id, comm_get_thank_you_uri() );
        ob_start();
        if ('bacs' === $order->get_payment_method() && 'pending' === $order->get_order_status() && isset($comm_options['bank_transfer']) && !empty($comm_options['bank_transfer'])) :
            ?>
            <div class="commercioo-checkout-description-product">
                <?php echo esc_html(isset($comm_options['bank_info_konfirmasi_message']) || !empty($comm_options['bank_info_konfirmasi_message']) ? $comm_options['bank_info_konfirmasi_message'] : 'Untuk melakukan konfirmasi pembayaran Anda, silahkan klik tombol berikut:'); ?>
            </div>
            <a href="<?php echo esc_url($return_url);?>" class="c-btn-payment-confirmation"><?php echo esc_html(isset($comm_options['bank_info_konfirmasi_button_label']) || !empty($comm_options['bank_info_konfirmasi_button_label']) ? $comm_options['bank_info_konfirmasi_button_label'] : 'KONFIRMASI PEMBAYARAN'); ?></a>
        <?php
        endif;
        return ob_get_clean();
    }
}