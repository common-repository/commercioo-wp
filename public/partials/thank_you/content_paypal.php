<?php
global $comm_options;
global $wp;
$endpoint       = \Commercioo\Query\Commercioo_Query::get_instance()->get_current_endpoint(); // all endpoint available in file includes/class-commercioo-query.php
$order_id = null;
if($endpoint){
    $order_id  = absint( $wp->query_vars[$endpoint] );
}
$Paypal = \Commercioo\Admin\Paypal::get_instance();
$content = $Paypal->paypal_default_success_message();
if($endpoint=="commercioo-payal-failed"){
    do_action("comm_update_status", $order_id, 'comm_failed');
    $content = $Paypal->paypal_default_failed_message();
}elseif($endpoint=="commercioo-payal"){
    do_action("comm_update_status", $order_id, 'comm_completed');
}else{
    return;
}
?>
<div class="clearfix">
    <div class="commercioo-checkout-description-product">
        <?php echo esc_html($content);?>
    </div>
    <?php do_action("commercioo_after_section_thank_you_for_another_addon",$order_id);?>
</div>