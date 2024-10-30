<?php
global $comm_options;
?>
<div class="tab-pane <?php echo (!has_action("comm_content_general_payment_setting"))?'active':''?>" id="bank-transfer-payment" role="tabpanel">
<form class="needs-validation" novalidate>
    <div class="row">
        <div class="col-md-12">
            <input type="checkbox" class="c-set-cursor-pointer check_status" id="bank_transfer_status"
                   name="payment_option[bacs]" id="banktransfer"
                   value="1" <?php checked(isset($comm_options['payment_option']['bacs']) ? $comm_options['payment_option']['bacs'] : '', 1); ?>>
            <label class="form-check-label c-label" for="bank_transfer_status"><?php esc_html_e('Bank Transfer', 'commercioo'); ?></label>
        </div>
        <div class="col-md-12"><?php esc_html_e('Check to enable Bank Transfer.', 'commercioo'); ?></div>
    </div>
    <?php if (isset($comm_options['bank_transfer'])) : ?>
        <?php if (is_array($comm_options['bank_transfer']) && count($comm_options['bank_transfer']) > 0): ?>
            <div class="col-md-12 c-payments-bank-item c-payments-bank-item-wrap">
                <?php foreach ($comm_options['bank_transfer'] as $k => $bank_val): ?>
                    <div class="form-bank-account c-bank-account">
                        <div class="row">
                            <div class="form-group col-md-2 c-bank-name-wrap">
                                <label class="c-label"><?php esc_html_e('Bank Name', 'commercioo'); ?></label>
                                <input type="text"
                                       class="form-control c-setting-form-control bank_name c-input-form c-set-cursor-pointer"
                                       placeholder="Bank Name"
                                       name="bank_transfer[<?php echo esc_attr($k); ?>][bank_name]"
                                       value="<?php echo esc_attr(isset
                                       ($bank_val['bank_name']) ? esc_attr($bank_val['bank_name']) : ''); ?>"
                                       id="bank_transfer_<?php echo esc_attr($k); ?>_bank_name">
                            </div>
                            <div class="form-group col-md-2">
                                <label class="c-label"><?php esc_html_e('Account Number', 'commercioo') ?></label>
                                <input type="text"
                                       class="form-control c-setting-form-control bank_account_number c-input-form c-set-cursor-pointer"
                                       placeholder="Account Number"
                                       name="bank_transfer[<?php echo esc_attr($k); ?>][account_number]"
                                       value="<?php echo esc_attr(isset($bank_val['account_number']) ? esc_attr($bank_val['account_number']) : ''); ?>"
                                       id="bank_transfer_<?php echo esc_attr($k); ?>_account_number">
                            </div>
                            <div class="form-group col-md-2">
                                <label class="c-label"><?php esc_html_e('Account Name', 'commercioo'); ?></label>
                                <input type="text"
                                       class="form-control c-setting-form-control bank_account_name c-input-form c-set-cursor-pointer"
                                       placeholder="Budiman"
                                       name="bank_transfer[<?php echo esc_attr($k); ?>][account_name]"
                                       value="<?php echo esc_attr(isset($bank_val['account_name']) ? esc_attr($bank_val['account_name']) : ''); ?>"
                                       id="bank_transfer_<?php echo esc_attr($k); ?>_account_name">
                            </div>
                            <div class="form-group col-md-2">
                                <label class="c-label"><?php esc_html_e('Branch', 'commercioo'); ?></label>
                                <input type="text"
                                       class="form-control c-setting-form-control bank_branch_name c-input-form c-set-cursor-pointer"
                                       placeholder="Cabang Ahmad Yani Bandung"
                                       name="bank_transfer[<?php echo esc_attr($k); ?>][branch_name]"
                                       value="<?php echo esc_attr(isset($bank_val['branch_name']) ? esc_attr($bank_val['branch_name']) : ''); ?>"
                                       id="bank_transfer_<?php echo esc_attr($k); ?>_branch_name">
                            </div>
                            <div class="form-group col-md-2 d-flex align-items-end">
                                        <span class="increase-decrease-payment add-bank-account">
                                            <i class="feather-16" data-feather="plus"></i>
                                        </span>
                                <span class="increase-decrease-payment remove-bank-account">
                                            <i class="feather-16" data-feather="minus"></i>
                                        </span>
                            </div>
                        </div>
                        <div class="form-group col-md-2 float-right c-btn-account-wrap mb-5">
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="col-md-12 c-payments-bank-item c-payments-bank-item-wrap">
                <div class="form-bank-account c-bank-account">
                    <div class="row">
                        <div class="form-group col-md-2 c-bank-name-wrap">
                            <label><?php esc_html_e('Bank Name', 'commercioo'); ?></label>
                            <input type="text"
                                   class="form-control c-setting-form-control bank_name c-input-form c-set-cursor-pointer"
                                   placeholder="Bank Name"
                                   name="bank_transfer[0][bank_name]" id="bank_transfer_0_bank_name">
                        </div>
                        <div class="form-group col-md-2">
                            <label><?php esc_html_e('Account Number', 'commercioo'); ?></label>
                            <input type="text"
                                   class="form-control c-setting-form-control bank_account_number c-input-form c-set-cursor-pointer"
                                   placeholder="Account Number" name="bank_transfer[0][account_number]"
                                   id="bank_transfer_0_account_number">
                        </div>
                        <div class="form-group col-md-2">
                            <label><?php esc_html_e('Account Name', 'commercioo'); ?></label>
                            <input type="text"
                                   class="form-control c-setting-form-control bank_account_name c-input-form c-set-cursor-pointer"
                                   placeholder="Budiman"
                                   name="bank_transfer[0][account_name]" id="bank_transfer_0_account_name">
                        </div>
                        <div class="form-group col-md-2">
                            <label><?php esc_html_e('Branch', 'commercioo'); ?></label>
                            <input type="text"
                                   class="form-control c-setting-form-control bank_branch_name c-input-form c-set-cursor-pointer"
                                   placeholder="Cabang Ahmad Yani Bandung"
                                   name="bank_transfer[0][branch_name]" id="bank_transfer_0_branch_name">
                        </div>
                        <div class="form-group col-md-2 d-flex align-items-end">
                                    <span class="increase-decrease-payment add-bank-account">
                                        <i class="feather-16" data-feather="plus"></i>
                                    </span>
                            <span class="increase-decrease-payment remove-bank-account">
                                        <i class="feather-16" data-feather="minus"></i>
                                    </span>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    <?php else: ?>
        <div class="col-md-12 c-payments-bank-item c-payments-bank-item-wrap">
            <div class="form-bank-account c-bank-account">
                <div class="row">
                    <div class="form-group col-md-2 c-bank-name-wrap">
                        <label><?php esc_html_e('Bank Name', 'commercioo'); ?></label>
                        <input type="text"
                               class="form-control c-setting-form-control bank_name c-input-form c-set-cursor-pointer"
                               placeholder="Bank Name"
                               name="bank_transfer[0][bank_name]" id="bank_transfer_0_bank_name">
                    </div>
                    <div class="form-group col-md-2">
                        <label><?php esc_html_e('Account Number', 'commercioo'); ?></label>
                        <input type="text"
                               class="form-control c-setting-form-control bank_account_number c-input-form c-set-cursor-pointer"
                               placeholder="Account Number" name="bank_transfer[0][account_number]"
                               id="bank_transfer_0_account_number">
                    </div>
                    <div class="form-group col-md-2">
                        <label><?php esc_html_e('Account Name', 'commercioo'); ?></label>
                        <input type="text"
                               class="form-control c-setting-form-control bank_account_name c-input-form c-set-cursor-pointer"
                               placeholder="Budiman"
                               name="bank_transfer[0][account_name]" id="bank_transfer_0_account_name">
                    </div>
                    <div class="form-group col-md-2">
                        <label><?php esc_html_e('Branch', 'commercioo'); ?></label>
                        <input type="text"
                               class="form-control c-setting-form-control bank_branch_name c-input-form c-set-cursor-pointer"
                               placeholder="Cabang Ahmad Yani Bandung"
                               name="bank_transfer[0][branch_name]" id="bank_transfer_0_branch_name">
                    </div>
                    <div class="form-group col-md-2 d-flex align-items-end">
                                <span class="increase-decrease-payment add-bank-account">
                                    <i class="feather-16" data-feather="plus"></i>
                                </span>
                        <span class="increase-decrease-payment remove-bank-account">
                                    <i class="feather-16" data-feather="minus"></i>
                                </span>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
    <div class="col-md-7 mt-4 mb-4 ml-3 c-line-dash-settings"></div>
    <div class="row">
        <div class="col-md-8">
            <label class="c-label c-cursor">
                <?php esc_html_e('Order Received Content', 'commercioo'); ?>
            </label>
        </div>
        <div class="col-md-8">
                    <textarea cols="4" rows="6" class="form-control c-setting-form-control c-input-form c-set-cursor-pointer"
                              name="bank_info_content_message" id="bank_info_content_message"><?php echo esc_html(isset
                        ($comm_options['bank_info_content_message']) ? $comm_options['bank_info_content_message'] : comm_default_thankyou_message()); ?></textarea>
            <div>
                <div>Dynamic variables you can use</div>
                <div><code>{total}</code> - The total price of the purchase</div>
            </div>
        </div>
    </div>
    <div class="col-md-7 mt-4 mb-4 ml-3 c-line-dash-settings"></div>
    <div class="row">
        <div class="col-md-8">
            <label class="c-label c-cursor">
                <?php esc_html_e('Payment Confirmation Instruction', 'commercioo'); ?>
            </label>
        </div>
        <div class="col-md-8">
            <input type="text" class="form-control c-setting-form-control c-input-form c-set-cursor-pointer"
                   name="bank_info_konfirmasi_message" id="bank_info_konfirmasi_message" value="<?php echo esc_html(isset
            ($comm_options['bank_info_konfirmasi_message']) ? $comm_options['bank_info_konfirmasi_message'] : "Untuk melakukan konfirmasi pembayaran Anda, silahkan klik tombol berikut:"); ?>">
        </div>
    </div>
    <div class="row">
        <div class="col-md-8">
            <label class="c-label c-cursor">
                <?php esc_html_e('Button Label', 'commercioo'); ?>
            </label>
        </div>
        <div class="col-md-8">
            <input type="text" class="form-control c-setting-form-control c-input-form c-set-cursor-pointer"
                   name="bank_info_konfirmasi_button_label" id="bank_info_konfirmasi_button_label"
                   value="<?php echo esc_html(isset
                   ($comm_options['bank_info_konfirmasi_button_label']) ? $comm_options['bank_info_konfirmasi_button_label'] : "KONFIRMASI PEMBAYARAN"); ?>">
        </div>
    </div>
    <div class="col-md-7 mt-4 mb-4 ml-3 c-line-dash-settings"></div>
    <div class="row">
        <div class="col-md-8">
            <label class="c-label c-cursor">
                <?php esc_html_e('Form Instruction', 'commercioo'); ?>
            </label>
        </div>
        <div class="col-md-8">
            <input type="text" class="form-control c-setting-form-control c-input-form c-set-cursor-pointer"
                   name="payment_confirmation_message" id="payment_confirmation_message" value="<?php echo esc_html(isset
            ($comm_options['payment_confirmation_message']) ? $comm_options['payment_confirmation_message'] : "Konfirmasi pembayaran Anda dengan mengisi form berikut."); ?>">
        </div>
    </div>
    <div class="row">
        <div class="col-md-8">
            <label class="c-label c-cursor">
                <?php esc_html_e('Submitted Form Content', 'commercioo'); ?>
            </label>
        </div>
        <div class="col-md-8">
            <textarea cols="4" rows="4" class="form-control c-setting-form-control c-input-form c-set-cursor-pointer" name="successful_payment_message" id="successful_payment_message"><?php echo esc_html(isset
                ($comm_options['successful_payment_message']) ? $comm_options['successful_payment_message'] : "Terima kasih, konfirmasi pembayaran Anda sudah kami terima. Untuk melihat status order Anda, silahkan klik tombol berikut untuk ke dashboard akun Anda."); ?></textarea>
        </div>
    </div>
    <div class="row">
        <div class="col-md-8">
            <label class="c-label c-cursor c-semibold">
                <?php esc_html_e('Go to Dashboard Button Label', 'commercioo'); ?>
            </label>
        </div>
        <div class="col-md-8">
            <input type="text" class="form-control c-setting-form-control c-input-form c-set-cursor-pointer"
                   name="successful_payment_button_label" id="successful_payment_button_label"
                   value="<?php echo esc_html(isset
                   ($comm_options['successful_payment_button_label']) ? $comm_options['successful_payment_button_label'] : "KE DASHBOARD AKUN"); ?>">
        </div>
    </div>
    <div class="col-md-7 mt-4 mb-4 ml-3 c-line-dash-settings"></div>
    <div class="col-md-6 set-margin-bottom-20 ms-4">
        <input type="hidden" name="payment_method" value="bacs">
        <button type="submit"
                class="btn btn-primary c-save-settings"><?php esc_html_e('Save', 'commercioo') ?></button>
        <input type="hidden" name="comm_key" value="gateways">
        <input type="hidden" name="comm_sub_key" value="gateways_bacs">
    </div>
</form>
</div>