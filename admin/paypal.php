<?php

namespace Commercioo\Admin;
if (!defined('ABSPATH')) {
    exit;
}
if (!class_exists('Commercioo\Admin\Paypal') && class_exists("Commercioo\Payment")) {
    class Paypal extends \Commercioo\Payment
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
        }

        /**
         * Get class instance
         *
         * @return Paypal Class instance.
         */
        public static function get_instance()
        {
            if (!isset(self::$instance)) {
                self::$instance = new Paypal();
            }
            return self::$instance;
        }
        /**
         * Display Content Setting if Success Paypal
         *
         * @return String
         */

        public function paypal_default_success_message()
        {
            global $comm_options;
            $paypal_success_message = isset
            ($comm_options['paypal']['paypal_success_message']) ? $comm_options['paypal']['paypal_success_message'] : "Thank you, we have received your payment.";
            return $paypal_success_message;
        }
        /**
         * Display Content Setting if Failed Paypal
         *
         * @return String
         */
        public function paypal_default_failed_message()
        {
            global $comm_options;
            $paypal_failed_message = isset
            ($comm_options['paypal']['paypal_failed_message']) ? $comm_options['paypal']['paypal_failed_message'] : " Payment failed, please try again or contact our support.";
            return $paypal_failed_message;
        }
    }

    $comm_gateway_paypal = \Commercioo\Admin\Paypal::get_instance();
}