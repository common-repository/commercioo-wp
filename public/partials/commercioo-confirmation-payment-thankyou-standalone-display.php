<?php
/**
 * Commercioo Standalone Thankyou Page
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    Commercioo
 * @subpackage Commercioo/includes
 */
if (!defined('ABSPATH')) {
    exit;
}

global $comm_options;
$order = new \Commercioo\Models\Order($order_id);
$shipping_address = $order->get_shipping_address();
$payment_confirmation_message = isset($comm_options['payment_confirmation_message']) && !empty($comm_options['payment_confirmation_message'])? wp_kses_post(comm_do_parsing_tags($comm_options['payment_confirmation_message'], $order_id)):'Konfirmasi pembayaran Anda dengan mengisi form berikut.';
$konfirmasi_button_label = isset($comm_options['bank_info_konfirmasi_button_label']) || !empty($comm_options['bank_info_konfirmasi_button_label']) ? esc_attr($comm_options['bank_info_konfirmasi_button_label']) : 'KONFIRMASI PEMBAYARAN';

$successful_payment_message = isset($comm_options['successful_payment_message']) && !empty($comm_options['successful_payment_message'])? wp_kses_post(comm_do_parsing_tags($comm_options['successful_payment_message'], $order_id)):'Terima kasih, konfirmasi pembayaran Anda sudah kami terima. Untuk melihat status order Anda, silahkan klik tombol berikut untuk ke dashboard akun Anda.';
$successful_payment_button_label = isset($comm_options['successful_payment_button_label']) || !empty($comm_options['successful_payment_button_label']) ? esc_attr($comm_options['successful_payment_button_label']) : 'KE DASHBOARD AKUN';

?>

<div class='commercioo-checkout-container' id="commercioo-checkout-container" data-color="<?php echo esc_attr($colors);?>">
    <div class="commercioo-checkout-layout">
        <div class="wrap-container wrap-content commercioo-thankyou-page">
            <div class="form_wrapper">
                <?php if($endpoint=="commercioo-confirmation-payment"):?>
                <form action="<?php echo esc_url( admin_url('admin-post.php') ); ?>" method="post" id="commercioo-confirmation-payment-form" enctype="multipart/form-data">
                    <!-- submit action -->
                    <input type="hidden" name="action" value="commercioo_confirmation_payment">
                    <input type="hidden" name="commercioo_confirmation_payment_file_size" class="commercioo_confirmation_payment_file_size" value="0">

                    <!-- nonce field -->
                    <?php wp_nonce_field( 'GwJpuj_HVaV604dHE', '_comm_thank_you_nonce' ); ?>
                <div class="thankyou-confirmation">
                    <div class="confirmation-description">
                        <p class="commercioo-checkout-description-product"><?php echo esc_html($payment_confirmation_message);?></p>
                    </div>
                    <div class="confirmation-form">
                        <div class="confirmation-field-wrapper">
                            <label for="">ORDER ID # *</label>
                            <input type="text" name="transfer_order_id" id="" placeholder="17643" value="<?php echo esc_html($order_id); ?>">
                        </div>
                        <div class="confirmation-field-wrapper">
                            <label for="">PENGIRIM TRANSFER ATAS NAMA *</label>
                            <input type="text" name="transfer_from_name" id="" placeholder="Pengirim transfer atas nama">
                        </div>
                        <div class="confirmation-field-wrapper">
                            <label for="">TRANSFER KE *</label>
                            <select name="transfer_to_bank" id="">
                                <?php foreach ($comm_options['bank_transfer'] as $bank) :?>
                                    <?php
                                     $branch_name = (isset($bank['branch_name']) && !empty($bank['branch_name']))? ' - '.esc_html($bank['branch_name']) :'';
                                    ?>
                                <option value="<?php echo esc_html($bank['bank_name']).",".esc_html($bank['account_name']).",".esc_html($bank['account_number']).$branch_name?>"><?php echo esc_html($bank['bank_name'])." - ".esc_html($bank['account_name'])." - ".esc_html($bank['account_number']).$branch_name?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="confirmation-field-wrapper">
                            <label for="">TANGGAL TRANSFER *</label>
                            <input type="text" name="transfer_date" id="transfer_date" placeholder="June 7, 2021">
                        </div>
                        <div class="confirmation-field-wrapper">
                            <label for="">NOMINAL TRANSFER *</label>
                            <input type="text" name="transfer_amount" id="transfer_amount" value="0" placeholder="Isi dengan angka nominal transfer anda">
                        </div>
                        <div class="confirmation-field-wrapper">
                            <label for="">UPLOAD BUKTI TRANSFER *</label>
                            <input type="file" name="transfer_file" id="transfer_file" accept="image/*">
                            <input type="text" name="bukti_transfer_file" id="bukti_transfer_file" placeholder="Pilih file gambar bukti transfer">
                            <button class="icon-action"><i class="fa fa-upload"></i></button>
                        </div>
                        <div>Maximum upload file size: <?php echo wp_kses_post(size_format(wp_max_upload_size()));?></div>
                        <div class="confirmation-action-wrapper">
                            <button type="submit" class="btn-place-confirmation-payment"><?php echo esc_html($konfirmasi_button_label);?></button>
                        </div>
                    </div>
                </div>
                </form>
            <?php else: ?>
                    <div class="thankyou-confirmation">
                        <div class="confirmation-description">
                            <p class="commercioo-checkout-description-product"><?php echo esc_html($successful_payment_message);?></p>
                            <div class="confirmation-action-wrapper">
                                <a href="<?php echo comm_get_account_uri();?>" class="btn-place-confirmation-payment"><?php echo esc_html($successful_payment_button_label);?></a>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>