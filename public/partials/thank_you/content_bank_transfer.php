<?php
global $comm_options;
$bank_info_content_message = isset($comm_options['bank_info_content_message']) && !empty($comm_options['bank_info_content_message']) ? $comm_options['bank_info_content_message'] : comm_default_thankyou_message();
global $wp;
ob_start();
$endpoint = \Commercioo\Query\Commercioo_Query::get_instance()->get_current_endpoint(); // all endpoint available in file includes/class-commercioo-query.php
$order_id = null;
if ($endpoint) {
    $order_id = absint($wp->query_vars[$endpoint]);
}
$order = new \Commercioo\Models\Order($order_id);
$status_order = $order->get_order_status();
if ($status_order == "pending") {
    ?>
    <div class="clearfix">
        <div class="commercioo-checkout-description-product">
            <?php echo wp_kses_post(comm_do_parsing_tags($bank_info_content_message, $order_id)); ?>
        </div>
        <?php echo wp_kses_post(comm_do_parsing_tags("{bank_info}", $order_id)); ?>
        <div class="commercioo-checkout-description-product commercioo-thankyou-konfirmasi-pembayaran">
            <?php echo wp_kses_post(comm_do_parsing_tags("{konfirmasi_pembayaran_bank}", $order_id)); ?>
        </div>
        <?php do_action("commercioo_after_section_thank_you_for_another_addon", $order_id); ?>
    </div>
    <?php
} ?>