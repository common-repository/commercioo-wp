<?php

namespace Commercioo;
if (!defined('ABSPATH')) {
    exit;
}
if (!class_exists('Commercioo\Paypal_Standard') && class_exists("Commercioo\Payment")) {
    class Paypal_Standard extends \Commercioo\Payment
    {
        /**
         * Class instance
         *
         * @var $instance
         */
        private static $instance;

        /**
         * Payment gateway constructor.
         *
         */
        public function __construct()
        {
            $this->key = 'paypal';
            $this->label = __("Paypal", "commercioo-payment-method");
            add_filter("comm_check_payment_method", array($this, "comm_check_payment_method"));
            add_filter("commercioo/payment/payment-options/{$this->key}", array($this, "comm_check_payment_method"));
            add_filter("commercioo/display/payment-method/{$this->key}", array($this, "comm_display_payment_method"));
            add_filter("commercioo/display/payment-method-html/{$this->key}", array($this, "comm_display_payment_method_html"));
            add_action("commercioo/process/{$this->key}", array($this, "do_commercioo_process_paypal"), 10, 2);

            //Paypay IPN
            add_action('init', array($this, 'listen_for_paypal_ipn'));
            add_action('commercioo_verify_paypal_ipn', array($this, 'process_paypal_ipn'));
        }

        /**
         * Get class instance
         *
         * @return Paypal_Standard Class instance.
         */
        public static function get_instance()
        {
            if (!isset(self::$instance)) {
                self::$instance = new Paypal_Standard();
            }
            return self::$instance;
        }

        /**
         * Process to paypal
         * @param int $order_id order id.
         * @param array $order_data data order.
         * @since   v0.4.1
         */
        public function do_commercioo_process_paypal($order_id = 0, $order_data = array())
        {
            $paypal_settings = $this->get_paypal_settings();
            $order = new \Commercioo\Models\Order($order_id);
            $listener_url = add_query_arg('commercioo-paypal-listener', 'IPN', home_url('index.php'));
            $paypal_email = $paypal_settings['paypal_account_email'];
            $paypal_redirect = trailingslashit($paypal_settings['api_endpoint_redirect']) . '?';

            $paypal_args = array(
                'business' => $paypal_email,
                'email' => $order_data['member']['email'],
                'first_name' => $order_data['member']['first_name'],
                'last_name' => $order_data['member']['last_name'],
                'invoice' => $order_data['invoice'],
                'no_shipping' => '1',
                'shipping' => '0',
                'no_note' => '1',
                'currency_code' => $order_data['currency_code'],
                'charset' => get_bloginfo('charset'),
                'custom' => $order_data['order_id'],
                'rm' => '2',
                'return' => $order_data['redirect_thank_you_page'],
                'cancel_return' => $order_data['cancel_return'],
                'notify_url' => $listener_url,
                'cbt' => get_bloginfo('name'),
                'bn' => 'COMMERCIOO_SP',
            );

            $paypal_extra_args = [
                'cmd' => '_cart',
                'upload' => '1'
            ];

            $paypal_args = array_merge($paypal_extra_args, $paypal_args);
            $i = 1;
            $product = $order_data['product'];

            // if not recurring
            $paypal_sum = 0;
            foreach ($product as $k => $val) {
                $regular_price = $val['price'];
                $paypal_args["item_name_" . $i] = stripslashes_deep(html_entity_decode($val['name'], ENT_COMPAT, 'UTF-8'));
                $paypal_args["quantity_" . $i] = $val['qty'];

                $paypal_args["amount_" . $i] = $regular_price;
                $paypal_sum += ($regular_price * $val['qty']);
                $i++;
            }
            // Add taxes to the cart
            $fees_amount = 0.00;
            if (!empty($order_data['cart']['fees'])) {
                $i = empty($i) ? 1 : $i;
                foreach ($order_data['cart']['fees'] as $fees) {
                    if (floatval($fees['amount']) > '0') {
// this is a positive fee
                        $paypal_args['item_name_' . $i] = stripslashes_deep(html_entity_decode(wp_strip_all_tags($fees['name']), ENT_COMPAT, 'UTF-8'));
                        $paypal_args['quantity_' . $i] = '1';
                        $paypal_args['amount_' . $i] = $fees['amount'];
                        $i++;
                    } else if (empty($fees['product'])) {

                        // This is a negative fee (discount) not assigned to a specific Product
                        $fees_amount += abs($fees['amount']);
                    }
                }
            }
            if (!empty($order_data['cart']['discounts'])) {
                foreach ($order_data['cart']['discounts'] as $fees) {
                    if (floatval($fees['amount']) > '0') {
                        $paypal_args['discount_amount_cart'] += $fees['amount'];
                    }
                }
            }
            $paypal_args = apply_filters('commercioo_paypal_redirect_args', $paypal_args, $order_data);
            // Build query
            $paypal_redirect .= http_build_query($paypal_args);
            $paypal_redirect = str_replace('&amp;', '&', $paypal_redirect);
            // Redirect to PayPal
            wp_redirect($paypal_redirect);
            exit;
        }

        /**
         * Listens for a PayPal IPN requests and then sends to the processing function
         * Check Notify from paypal if any requests IPN
         * @since   v0.4.1
         */
        public function listen_for_paypal_ipn()
        {
            // Regular PayPal IPN
            if (isset($_GET['commercioo-paypal-listener']) && 'ipn' === strtolower($_GET['commercioo-paypal-listener'])) {

                /**
                 * This is necessary to delay execution of PayPal PDT and to avoid a race condition causing the order status
                 * updates to be triggered twice.
                 *
                 * @since 2.9.4
                 * @see https://github.com/easydigitaldownloads/easy-digital-downloads/issues/6605
                 */
                do_action('commercioo_verify_paypal_ipn');
            }
        }

        /**
         * Process PayPal IPN
         * Check Type txn from paypal and call hook action
         * This action for type paypal is recurring payment
         * @since   v0.4.1
         */
        public function process_paypal_ipn()
        {
            /*DON'T REMOVE UNTIL COMPLETED TESTING - BEGIN OF DEBUG FOR DATA IPN PAYPAL*/
//            com_write_log($_GET);
//            com_write_log($_POST);
//            com_write_log($_SERVER);
            /*END OF DEBUG FOR DATA IPN PAYPAL*/

            // Check the request method is POST
            if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] != 'POST') {
                return;
            }

            // Set initial post data to empty string
            $post_data = '';

            // Fallback just in case post_max_size is lower than needed
            if (ini_get('allow_url_fopen')) {
                $post_data = file_get_contents('php://input');
            } else {
                // If allow_url_fopen is not enabled, then make sure that post_max_size is large enough
                ini_set('post_max_size', '12M');
            }

            // Start the encoded data collection with notification command
            $encoded_data = 'cmd=_notify-validate';
            // Get current arg separator
            $arg_separator = comm_get_php_arg_separator_output();

            // Verify there is a post_data
            if ($post_data || strlen($post_data) > 0) {
                // Append the data
                $encoded_data .= $arg_separator . $post_data;
            } else {
                // Check if POST is empty
                if (empty($_POST)) {
                    // Nothing to do
                    return;
                } else {
                    // Loop through each POST
                    foreach ($_POST as $key => $value) {
                        // Encode the value and append the data
                        $encoded_data .= $arg_separator . "$key=" . urlencode($value);
                    }
                }
            }

            // Convert collected post data to an array
            parse_str($encoded_data, $encoded_data_array);

            foreach ($encoded_data_array as $key => $value) {
                if (false !== strpos($key, 'amp;')) {
                    $new_key = str_replace('&amp;', '&', $key);
                    $new_key = str_replace('amp;', '&', $new_key);

                    unset($encoded_data_array[$key]);
                    $encoded_data_array[$new_key] = $value;
                }

            }
            $encoded_data_array = apply_filters('commercioo_process_paypal_ipn_data', $encoded_data_array);

            // Check if $post_data_array has been populated
            if (!is_array($encoded_data_array) && !empty($encoded_data_array)) {
                return;
            }

            $defaults = [
                'txn_type' => '',
                'payment_status' => ''
            ];

            $encoded_data_array = wp_parse_args($encoded_data_array, $defaults);
            /*DON'T REMOVE UNTIL COMPLETED TESTING - BEGIN OF DEBUG FOR DATA IPN PAYPAL*/
//            com_write_log($encoded_data_array);
            /*END OF DEBUG FOR DATA IPN PAYPAL*/

            $payment_id = !empty($encoded_data_array['custom']) ? absint($encoded_data_array['custom']) : 0;
            $payment_status = $encoded_data_array['payment_status'];
            if ($payment_status == "Refunded") {
                do_action("comm_update_status", $payment_id, 'comm_refunded');
            }
            if (has_action('commercioo_paypal_' . $encoded_data_array['txn_type'])) {
                // Allow PayPal IPN types to be processed separately
                do_action('commercioo_paypal_' . $encoded_data_array['txn_type'], $encoded_data_array, $payment_id);
            }

            exit;
        }

        /**
         * Check payment method
         * @param array $options
         * @return array $options
         * @since   v0.4.1
         */
        public function comm_check_payment_method($options = array())
        {
            global $comm_options;
            if ((isset($comm_options['payment_option']) && isset($comm_options['payment_option'][$this->key]))) {
                $options[$this->key] = true;
            }
            return $options;
        }

        /**
         * Display payment method
         * @return array
         * @since   v0.4.1
         */
        public function comm_display_payment_method($content = '')
        {
            global $comm_options;
            $pm = array();
            if ('ID' == $comm_options['store_country']) {
                if ('IDR' !== $comm_options['currency']) {
                    $pm[] = array("name" => $this->key, "value" => $this->label, "is_tripay" => false);
                }
            } else {
                if ('IDR' !== $comm_options['currency']) {
                    $pm[] = array("name" => $this->key, "value" => $this->label, "is_tripay" => false);
                }
            }
            return $pm;
        }

        /**
         * Display payment method HTML
         * @return array
         * @since   v0.4.1
         */
        public function comm_display_payment_method_html($content = '')
        {
            global $comm_options;
            $paypal_settings = $this->get_paypal_settings();
            $paypal_email = $paypal_settings['paypal_account_email'];
            if ($paypal_email) {
                if ('ID' == $comm_options['store_country']) {
                    if ('IDR' !== $comm_options['currency']) {
                        ?>
                        <div class="direct-bank-wrap">
                            <input type="radio" class="radio-payment radio-show-direct-bank" name="payment_method"
                                   id="payment_method_<?php echo esc_attr($this->key); ?>"
                                   value="<?php echo esc_attr($this->key); ?>">
                            <input type="hidden" name="payment_method_name[<?php echo esc_attr($this->key); ?>]"
                                   value="<?php echo esc_attr($this->label); ?>">
                            <label class="label-shipping"
                                   for="payment_method_<?php echo esc_attr($this->key); ?>"><?php echo esc_attr($this->label); ?></label>
                        </div>
                        <?php
                    }
                }
            }
        }
    }

    $comm_gateway_paypal = \Commercioo\Paypal_Standard::get_instance();
}