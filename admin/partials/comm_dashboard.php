<!-- Start Title -->
<div class="col-md-12 c-col-container">
    <div class="d-flex align-items-center">
        <h2 class="page-title"><?php _e("Dashboard", "Commercioo_title"); ?></h2>
<!--        <img src="--><?php //echo esc_url(COMMERCIOO_URL . 'admin/img/icon_info.svg') ?><!--" class="icon-page-title icon-home"/>-->
    </div>
</div>
<!-- End Title -->

<div class="c-double-container">
    <div class="col-md-12 c-col-container">
        <div class="d-flex align-items-center">
            <div>
                <h2 class="widget-title-big comm-time-title">MONTHLY</h2>
            </div>
            <span class="context-menu"><i class="fa fa-ellipsis-h"></i></span>
            <span class="btn btn-primary ml-3 c-btn-products c-add-text" id="timespan_loading" style='display:none;'><?php esc_html_e( 'Loading..', 'commercioo' ) ?></span>
        </div>
    </div>
    <div class="col-md-12 c-col-container">
        <div class="row c-subdouble-wrap">
            <div class="col-md c-subdouble-container">
                <div class="d-flex box-order">
                    <div class="widget-title-wrap">
                        <h2 class="widget-title"><a href="#">ORDERS</a></h2>
                    </div>
                    <span class="badge c-badge-danger"></span>
                </div>
                <div>
                    <h2 class="big-text-second comm-total-order">0</h2>
                </div>
            </div>
            <div class="col-md c-subdouble-container">
                <div class="d-flex box-sales">
                    <div class="widget-title-wrap">
                        <h2 class="widget-title"><a href="#">SALES</a></h2>
                    </div>
                    <span class="badge c-badge-secondary"></span>
                </div>
                <div>
                    <h2 class="big-text-second comm-sales">0</h2>
                </div>
            </div>
            <div class="col-md c-subdouble-container">
                <div class="d-flex box-customer">
                    <div class="widget-title-wrap">
                        <h2 class="widget-title"><a href="#">CUSTOMERS</a></h2>
                    </div>
                    <span class="badge c-badge-success"></span>
                </div>
                <div>
                    <h2 class="big-text-second comm-customer">0</h2>
                </div>
            </div>
            <div class="col-md c-subdouble-container">
                <div class="d-flex box-revenue">
                    <div class="widget-title-wrap">
                        <h2 class="widget-title"><a href="#">REVENUE</a></h2>
                    </div>
                    <span class="badge c-badge-success"></span>
                </div>
                <div>
                    <div class="d-flex">
                        <span class="c-small-text">Rp</span>
                        <h2 class="big-text-second comm-data-revenue">0</h2>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recent Orders -->
<?php
include COMMERCIOO_PATH . 'admin/partials/commercioo-recent-orders-admin-display.php';
?>

<!-- For Statistic -->
<?php
if (function_exists("comm_do_pro_top_products")) {
    comm_do_pro_top_products();
}
?>
