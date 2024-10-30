<?php
global $comm_options;
$Paypal = \Commercioo\Admin\Paypal::get_instance();
$paypal_success_message = $Paypal->paypal_default_success_message();
$paypal_failed_message = $Paypal->paypal_default_failed_message();
?>
<div class="tab-pane" id="paypal-payment" role="tabpanel">
<form class="needs-validation" novalidate>
    <div class="row">
        <div class="col-md-12">
            <input type="checkbox" class="c-set-cursor-pointer check_status" id="paypal_status"
                   name="payment_option[paypal]" id="paypal"
                   value="1" <?php checked(isset($comm_options['payment_option']['paypal']) ? $comm_options['payment_option']['paypal'] : '', 1); ?>>
            <label class="form-check-label c-label" for="paypal_status"><?php esc_html_e('Paypal', 'commercioo'); ?></label>
        </div>
        <div class="col-md-12"><?php esc_html_e('Check to enable Paypal.', 'commercioo'); ?></div>
    </div>

    <div class="col-md-7 mt-4 mb-4 ml-3 c-line-dash-settings"></div>
    <div class="row">
        <div class="col-md-8">
            <label class="c-label c-cursor">
                <?php esc_html_e('Paypal Account Email', 'commercioo'); ?>
            </label>
        </div>
        <div class="col-md-8">
            <input type="text" class="form-control c-setting-form-control c-input-form c-set-cursor-pointer"
                   name="paypal[paypal_account_email]" placeholder="you@youremail.com" value="<?php echo esc_html(isset
            ($comm_options['paypal']['paypal_account_email']) ? $comm_options['paypal']['paypal_account_email'] : ""); ?>">
            <div class="c-form-orders-desc"><?php esc_html_e("Enter your PayPal account's email. This is needed in order to take payment.","comercioo");?></div>
        </div>
    </div>
    <div class="col-md-7 mt-4 mb-4 ml-3 c-line-dash-settings"></div>
    <div class="row">
        <div class="col-md-12">
            <input type="checkbox" class="c-set-cursor-pointer" id="paypal_sandbox"
                   name="paypal[paypal_sandbox]"
                   value="1" <?php checked(isset($comm_options['paypal']['paypal_sandbox']) ? $comm_options['paypal']['paypal_sandbox'] : '', 1); ?>>
            <label class="form-check-label c-label" for="paypal_sandbox"><?php esc_html_e('Paypal Sandbox', 'commercioo'); ?></label>
        </div>
        <div class="col-md-12"><?php echo wp_kses_post(sprintf(__("Check to enable Paypal Sandbox. Only use Sandbox if you're on testing or development mode, click %s to sign up a developer account.", 'commercioo'),"<a href='https://developer.paypal.com/' target='_blank'>".__("here","commercioo")."</a>")); ?></div>
    </div>
    <div class="col-md-7 mt-4 mb-4 ml-3 c-line-dash-settings"></div>
    <div class="row">
        <div class="col-md-8">
            <label class="c-label c-cursor">
                <?php esc_html_e('API credentials', 'commercioo'); ?>
            </label>
        </div>
        <div class="col-md-12"><?php echo wp_kses_post(sprintf(__("Enter your PayPal API credentials to process refunds via PayPal. Learn how to access your %s.", 'commercioo'),"<a href='https://developer.paypal.com/webapps/developer/docs/classic/api/apiCredentials/#create-an-api-signature' target='_blank'>".__("PayPal API Credentials","commercioo")."</a>")); ?></div>
    </div>
    <div class="row">
        <div class="col-md-8">
            <label class="c-label c-cursor">
                <span class="c-form-orders-desc commercioo_paypal_is_mode_sandbox d-none"><?php esc_html_e("Sandbox - ","comercioo");?></span>
                <?php esc_html_e('API Username', 'commercioo'); ?>

            </label>
        </div>
        <div class="col-md-8">
            <input type="text" class="form-control c-setting-form-control c-input-form c-set-cursor-pointer"
                   name="paypal[paypal_api_username]" placeholder="Optional" value="<?php echo esc_attr(isset
            ($comm_options['paypal']['paypal_api_username']) ? $comm_options['paypal']['paypal_api_username'] : ""); ?>">
        </div>
    </div>
    <div class="row">
        <div class="col-md-8">
            <label class="c-label c-cursor">
                <span class="c-form-orders-desc commercioo_paypal_is_mode_sandbox d-none"><?php esc_html_e("Sandbox - ","comercioo");?></span>
                <?php esc_html_e('API Password', 'commercioo'); ?>
            </label>
        </div>
        <div class="col-md-8">
            <input type="text" class="form-control c-setting-form-control c-input-form c-set-cursor-pointer"
                   name="paypal[paypal_api_password]" placeholder="Optional" value="<?php echo esc_attr(isset
            ($comm_options['paypal']['paypal_api_password']) ? $comm_options['paypal']['paypal_api_password'] : ""); ?>">
        </div>
    </div>
    <div class="row">
        <div class="col-md-8">
            <label class="c-label c-cursor">
                <span class="c-form-orders-desc commercioo_paypal_is_mode_sandbox d-none"><?php esc_html_e("Sandbox - ","comercioo");?></span>
                <?php esc_html_e('API Signature', 'commercioo'); ?>
            </label>
        </div>
        <div class="col-md-8">
            <input type="text" class="form-control c-setting-form-control c-input-form c-set-cursor-pointer"
                   name="paypal[paypal_api_signature]" placeholder="Optional" value="<?php echo esc_attr(isset
            ($comm_options['paypal']['paypal_api_signature']) ? $comm_options['paypal']['paypal_api_signature'] : ""); ?>">
        </div>
    </div>
    <div class="col-md-7 mt-4 mb-4 ml-3 c-line-dash-settings"></div>
    <div class="row">
        <div class="col-md-8">
            <label class="c-label c-cursor">
                <?php esc_html_e('Payment Success Message', 'commercioo'); ?>
            </label>
        </div>
        <div class="col-md-8">
            <textarea cols="4" rows="4" class="form-control c-setting-form-control c-input-form c-set-cursor-pointer" name="paypal[paypal_success_message]" id="paypal_success_message"><?php echo esc_html($paypal_success_message); ?></textarea>
        </div>
    </div>
    <div class="row">
        <div class="col-md-8">
            <label class="c-label c-cursor">
                <?php esc_html_e('Payment Failed Message', 'commercioo'); ?>
            </label>
        </div>
        <div class="col-md-8">
            <textarea cols="4" rows="4" class="form-control c-setting-form-control c-input-form c-set-cursor-pointer" name="paypal[paypal_failed_message]" id="paypal_failed_message"><?php echo esc_html($paypal_failed_message); ?></textarea>
        </div>
    </div>
    <div class="col-md-7 mt-4 mb-4 ml-3 c-line-dash-settings"></div>
    <div class="col-md-6 set-margin-bottom-20 ms-4">
        <input type="hidden" name="paypal[payment_method]" value="paypal">
        <button type="submit"
                class="btn btn-primary c-save-settings"><?php esc_html_e('Save', 'commercioo') ?></button>
        <input type="hidden" name="comm_key" value="gateways">
        <input type="hidden" name="comm_sub_key" value="gateways_paypal">
    </div>
</form>
</div>