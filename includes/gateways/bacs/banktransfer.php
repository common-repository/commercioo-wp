<?php
namespace Commercioo;
use SendGrid\Mail\HtmlContent;

if(!class_exists("Commercioo\BankTransfer") && class_exists("Commercioo\Payment")){
    final class BankTransfer extends \Commercioo\Payment{
        /**
         * Construction
         */
        public function __construct() {
            $this->key = 'bacs';
            $this->label = __("Bank Transfer","commercioo-payment-method");
            add_filter("comm_check_payment_method",array($this,"comm_check_payment_method"));
            add_filter("commercioo/payment/payment-options/{$this->key}",array($this,"comm_check_payment_method"));
            add_filter("commercioo/display/payment-method/{$this->key}",array($this,"comm_display_payment_method"));
            add_filter("commercioo/display/payment-method-html/{$this->key}",array($this,"comm_display_payment_method_html"));
        }
        /**
         * Check payment method
         * @since   v0.4.1
         * @param array $options
         * @return array $options
         */
        public function comm_check_payment_method($options = array()){
            global $comm_options;
            if((isset($comm_options['payment_option']) && isset($comm_options['payment_option'][$this->key])) && isset($comm_options['bank_transfer']) && !empty($comm_options['bank_transfer'])){
                $options[$this->key] =true;
            }
            return $options;
        }
        /**
         * Display payment method
         * @since   v0.4.1
         * @return array
         */
        public function comm_display_payment_method($content=''){
            global $comm_options;
            $pm[] = array("name"=>$this->key,"value"=>$this->label,"is_tripay"=>false);
            return $pm;
        }
        /**
         * Display payment method HTML
         * @since   v0.4.1
         * @return HtmlContent
         */
        public function comm_display_payment_method_html($content=''){
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
    $BankTransfer = new \Commercioo\BankTransfer();
}