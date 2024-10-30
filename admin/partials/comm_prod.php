<!-- Start Title -->
<div class="col-md-12 c-col-container">
    <div class="c-list-products product-title">
        <div class="d-flex justify-content-between">
            <div class="d-flex align-items-center">
                <h2 class="page-title"><?php _e("Products", "Commercioo_title"); ?></h2>
                <span class="desktop-view c-btn-products c-btn-add-products c-add-text"><?php _e("+ Add", "commercioo"); ?></span>
                <span class="mobile-view c-btn-products c-btn-add-products"><i class="fa fa-plus"></i></span>
            </div>
            <div class="float-right c-search-tables">
                <div class="input-group">
                <input type="text" name="comm_table_search" class="comm-table-search" placeholder="Search...">
                <div class="input-group-append">
                    <span class="input-group-text" id="basic-addon2"><i class="feather-16" data-feather="search"></i></span>
                </div>
                </div>
            </div>
        </div>
    </div>
    <div class="c-add-products product-title">
        <div class="d-flex align-items-center">
            <h2 class="page-title"><?php _e("Add Product", "commercioo"); ?></h2>
        </div>
    </div>
    <div class="c-edit-products product-title">
        <div class="d-flex align-items-center">
            <h2 class="page-title"><?php _e("Edit Product", "commercioo"); ?> #<span id="id_product">0</span></h2>
        </div>
    </div>
</div>
<!-- End Title -->

<div class="c-filter-wrap c-list-products">
    <div class="btn-group c-list-products c-general-products c-btn-group-grid" role="group">
        <span class="btn c-btn-filter-list comm-filter active c-btn-group-grid-item pl-0"
              data-status="any"><?php _e("All", "commercioo"); ?>
            <span class="comm_count_all">(<?php echo esc_attr(count(comm_controller()->comm_get_result_data
                (['publish', 'pending', 'draft', 'auto-draft', 'future', 'private', 'inherit', 'trash'], "comm_product", 'ID'))); ?>
                )</span></span>
        <span class="btn c-btn-filter-list comm-filter c-set-padding-left-1 c-btn-group-grid-item"
              data-status="publish"><?php _e("Published",
                "commercioo"); ?>
            <span class="comm_count_publish">(<?php echo esc_attr(count(comm_controller()->comm_get_result_data
                ("publish", "comm_product", 'ID'))); ?>)</span></span>
        <span class="btn c-btn-filter-list comm-filter c-set-padding-left-1 c-btn-group-grid-item"
              data-status="draft"><?php _e("Draft", "commercioo"); ?>
            <span class="comm_count_draft">(<?php echo esc_attr(count(comm_controller()->comm_get_result_data
                ("draft", "comm_product", 'ID'))); ?>)</span></span>
        <span class="btn c-btn-filter-list comm-filter c-set-padding-left-1 c-btn-group-grid-item"
              data-status="trash"><?php _e("Trash", "commercioo"); ?>
            <span class="comm_count_trash">(<?php echo esc_attr(count(comm_controller()->comm_get_result_data
                ("trash", "comm_product", 'ID'))) ?>)</span></span>
    </div>
</div>


<?php
$data = [
    'order_by' => 'ID',
    'post_type' => 'comm_product',
    'status' => json_encode(["publish", "pending", "draft", "auto-draft", "future", "private", "inherit", "trash"]),
];
?>
<!-- start list products -->
<div class="table-responsive c-tbl c-list-products c-general-products c-list-table-data" data-tbl="product"
     data-table='<?php echo esc_html(json_encode($data)); ?>'>
    <!--Table-->
    <table class="table c-table-list-products">

        <!--Table head-->
        <thead class="c-table-list-products-head">
        <tr>
            <th class="th-lg" data-orderable="false">
                <div class="table-option">
                    <input type="checkbox" name="select-all" id="select-all">
                    <span class="btn btn-default" type="button" data-bs-toggle="dropdown"><i class="fa fa-angle-down"></i></span>
                    <ul class="dropdown-menu dropdown-menu-right">
                      <li class="delete"><a class="delete-selected">Delete selected</a></li>
                    </ul>
                </div>
            </th>
            <th class="th-lg"><?php _e("Photo", "commercioo"); ?></th>
            <th class="th-lg"><?php _e("Name", "commercioo"); ?></th>
            <th class="th-lg"><?php _e("SKU", "commercioo"); ?></th>
            <th class="th-lg"><?php _e("Stock", "commercioo"); ?></th>
            <th class="th-lg"><?php _e("Price", "commercioo"); ?></th>
            <th class="th-lg"><?php _e("Status", "commercioo"); ?></th>
            <th class="th-lg"><?php _e("Featured", "commercioo"); ?></th>
            <th class="th-lg"><?php _e("Orders", "commercioo"); ?></th>
            <th class="th-lg"><?php _e("Sales", "commercioo"); ?></th>
            <th class="th-lg"><?php _e("Closing R.", "commercioo"); ?></th>
        </tr>
        </thead>
        <!--Table head-->

        <!--Table body-->
        <tbody>
        </tbody>
        <!--Table body-->
        <tfoot class="c-table-list-products-head">
        <tr>
            <th class="th-lg" data-orderable="false">
                <div class="table-option">
                    <input type="checkbox" name="select-all" id="select-all">
                    <span class="btn btn-default" type="button" data-bs-toggle="dropdown"><i class="fa fa-angle-down"></i></span>
                    <ul class="dropdown-menu dropdown-menu-right">
                      <li class="delete"><a class="delete-selected">Delete selected</a></li>
                    </ul>
                </div>
            </th>
            <th class="th-lg"><?php _e("Photo", "commercioo"); ?></th>
            <th class="th-lg"><?php _e("Name", "commercioo"); ?></th>
            <th class="th-lg"><?php _e("SKU", "commercioo"); ?></th>
            <th class="th-lg"><?php _e("Stock", "commercioo"); ?></th>
            <th class="th-lg"><?php _e("Price", "commercioo"); ?></th>
            <th class="th-lg"><?php _e("Status", "commercioo"); ?></th>
            <th class="th-lg"><?php _e("Featured", "commercioo"); ?></th>
            <th class="th-lg"><?php _e("Orders", "commercioo"); ?></th>
            <th class="th-lg"><?php _e("Sales", "commercioo"); ?></th>
            <th class="th-lg"><?php _e("Closing R.", "commercioo"); ?></th>
        </tr>
        </tfoot>
    </table>
    <!--Table-->
</div>
<!-- end start products -->

<!-- Modal -->
<div class="modal fade comm-order-form-product" tabindex="-1" role="dialog" aria-labelledby="comm-order-form-product"
     aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header set-pt-pb-15">
                <h2 class="modal-heading set-font-size-16"><?php _e("Order Form", "commercioo"); ?></h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="modal-content">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label><b><?php esc_html_e( 'Checkout URL', 'commercioo' ) ?></b></label>
                                <input type="text" class="form-control comm-order-form-url c-setting-form-control
                                c-form-with-bg-copy"
                                       id="inlineFormInputGroup" placeholder="https://yoursite.com/simpleorder/45/"
                                       readonly>
                                <span class="c-copy-to-clip" data-bs-container="body"
                                      data-bs-toggle="popover"
                                      data-bs-placement="top"
                                      data-bs-trigger="hover"
                                      data-bs-content="<?php _e("Click to copy to clipboard", "commercioo"); ?>">
                                    <i class="fa fa-clone"></i>
                                </span>
                                <div class="c-form-products-desc">
                                    <?php 
									echo wp_kses_post( 
										__( 'Receive order through <b>checkout page</b>. Use the URL / link above to send visitors to the checkout page.', 'commercioo' ) 
									); 
									?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php if ( is_comm_wa() ) : ?>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label><b><?php esc_html_e( "WhatsApp Checkout URL", "commercioo" ); ?></b></label>
                                    <input type="text" class="form-control comm-order-whatsapp-url c-setting-form-control c-form-with-bg-copy" readonly>
                                    <span class="c-copy-to-clip" data-bs-container="body" data-bs-toggle="popover" data-bs-placement="top" data-bs-trigger="hover" data-bs-content="<?php _e("Click to copy to clipboard", "commercioo"); ?>">
                                        <i class="fa fa-clone"></i>
                                    </span>
                                    <div class="c-form-products-desc c-form-wa-redirect-desc">
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                    <?php do_action("comm_product_form") ;?>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- End Modal -->
<?php
if (function_exists("comm_do_2_step_modal")) {
    comm_do_2_step_modal();
}
?>
<?php include COMMERCIOO_PATH . 'admin/partials/comm_prod_edit.php'; ?>
