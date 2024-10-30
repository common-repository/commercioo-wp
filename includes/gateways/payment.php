<?php
namespace Commercioo;
if(!class_exists("Commercioo\Payment")){
    class Payment{
        public $label;
        public $key;
        /**
         * Class instance
         *
         * @var $instance
         */
        private static $instance;
        /**
         * Get class instance
         *
         * @return Payment Class instance.
         */
        public static function get_instance() {
            if ( ! isset( self::$instance ) ) {
                self::$instance = new Payment();
            }
            return self::$instance;
        }
        /**
         * Get payment key
         * @since   0.4.2
         * @return  void
         */
        public function get_key() {
            return $this->key;
        }
        /**
         * Get payment key
         * @since   1.0.0
         * @return  void
         */
        public function get_label() {
            return $this->label;
        }

        /**
         * Get payment paypal settings
         * @since   v0.4.1
         * @var boolean $ssl_check
         * @return  array
         */
        public function get_paypal_settings($ssl_check = false) {
            $setting_gateway = get_option("comm_gateways_settings",array());
            $protocol = 'http://';
            if ( is_ssl() || ! $ssl_check) {
                $protocol = 'https://';
            }
            $credentials = array(
                'api_endpoint'  => isset($setting_gateway['paypal']) && isset($setting_gateway['paypal']['paypal_sandbox']) ? 'https://api-3t.sandbox.paypal.com/nvp' : 'https://api-3t.paypal.com/nvp',
                'api_endpoint_redirect'  => isset($setting_gateway['paypal']) && isset($setting_gateway['paypal']['paypal_sandbox']) ?  $protocol . 'www.sandbox.paypal.com/cgi-bin/webscr' : $protocol . 'www.paypal.com/cgi-bin/webscr',
                'paypal_account_email'  =>  isset($setting_gateway['paypal']) && isset($setting_gateway['paypal']['paypal_account_email'])?$setting_gateway['paypal']['paypal_account_email']:'',
                'paypal_is_recurring'  =>  isset($setting_gateway['paypal']) && isset($setting_gateway['paypal']['paypal_recurring'])?$setting_gateway['paypal']['paypal_recurring']:false,
                'api_username'  =>  isset($setting_gateway['paypal']) && isset($setting_gateway['paypal']['paypal_api_username'])?$setting_gateway['paypal']['paypal_api_username']:'',
                'api_password'  => isset($setting_gateway['paypal']) && isset($setting_gateway['paypal']['paypal_api_password'])?$setting_gateway['paypal']['paypal_api_password']:'',
                'api_signature' => isset($setting_gateway['paypal']) && isset($setting_gateway['paypal']['paypal_api_signature'])?$setting_gateway['paypal']['paypal_api_signature']:'',
            );
            return $credentials;
        }
    }
    $Payment = \Commercioo\Payment::get_instance();
}