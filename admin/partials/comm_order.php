<!-- Start Title -->
<div class="col-md-12 c-col-container">
    <div class="d-flex justify-content-between c-list-orders">
        <div class="d-flex align-items-center">
            <h2 class="page-title"><?php _e("Orders", "Commercioo_title"); ?></h2>
            <span class="desktop-view c-btn-products c-add-orders c-add-text"><?php _e("+ Add", "commercioo"); ?></span>
            <span class="mobile-view c-btn-products c-add-orders"><i class="fa fa-plus"></i></span>
        </div>
        <div class="row float-right c-search-tables">
            <div class="col input-group">
                <input type="text" name="comm_table_search" class="comm-table-search" placeholder="Search...">
                <div class="input-group-append">
                    <span class="input-group-text" id="basic-addon2"><i class="feather-16" data-feather="search"></i></span>
                </div>
            </div>
            <div class="col c-date-range-orders c-dr">
                <span></span> <i class="fa fa-caret-down c-dr-order-caret"></i>
            </div>
        </div>
    </div>
    <div class="c-add-orders">
        <div class="d-flex align-items-center">
            <h2 class="page-title"><?php _e("Add Order", "commercioo"); ?></h2>
        </div>
    </div>
</div>
<!-- End Title -->

<div class="c-list-orders c-filter-wrap">
    <div class="c-list-orders c-general-orders filter-orders">
         <span class="btn c-btn-filter-list comm-filter active pl-0" data-status="any"><?php
             _e("All", "commercioo_order"); ?>
             <span class="comm_count_all">(<?php echo esc_attr(count(comm_controller()->comm_get_result_data
                 (['comm_pending', 'comm_processing', 'comm_completed', 'comm_refunded','comm_abandoned','comm_failed','trash'],
                     "comm_order", 'ID'))); ?>)</span></span>

        <span class="btn c-btn-filter-list c-set-padding-left-1 comm-filter"
              data-status="pending"><?php
            _e("Pending", "commercioo_order"); ?>
            <span class="comm_count_pending">(<?php echo esc_attr(count(comm_controller()->comm_get_result_data
                ('comm_pending', "comm_order", 'ID'))); ?>)</span></span>

        <span class="btn c-btn-filter-list c-set-padding-left-1 comm-filter"
              data-status="processing"><?php
            _e("Processing", "commercioo_order"); ?>
            <span class="comm_count_processing">(<?php echo esc_attr(count(comm_controller()->comm_get_result_data
                ('comm_processing', "comm_order", 'ID'))); ?>)</span></span>

        <span class="btn c-btn-filter-list c-set-padding-left-1 comm-filter"
              data-status="completed"><?php
            _e("Completed", "commercioo_order"); ?>
            <span class="comm_count_completed">(<?php echo esc_attr(count(comm_controller()->comm_get_result_data
                ('comm_completed', "comm_order", 'ID'))) ?>)</span></span>

        <span class="btn c-btn-filter-list c-set-padding-left-1 comm-filter"
              data-status="refunded"><?php
            _e("Refunded", "commercioo_order"); ?>
            <span class="comm_count_refunded">(<?php echo esc_attr(count(comm_controller()->comm_get_result_data
                ('comm_refunded', "comm_order", 'ID'))); ?>)</span></span>
        <span class="btn c-btn-filter-list c-set-padding-left-1 group-grid-wrap-item comm-filter"
              data-status="abandoned"><?php
            _e("Abandoned", "commercioo_order"); ?>
            <span class="comm_count_abandoned">(<?php echo esc_attr(count(comm_controller()->comm_get_result_data
                ('comm_abandoned', "comm_order", 'ID'))); ?>)</span></span>

        <span class="btn c-btn-filter-list c-set-padding-left-1 group-grid-wrap-item comm-filter"
              data-status="failed"><?php
            _e("Failed", "commercioo_order"); ?>
            <span class="comm_count_failed">(<?php echo esc_attr(count(comm_controller()->comm_get_result_data
                ('comm_failed', "comm_order", 'ID'))); ?>)</span></span>

        <span class="btn c-btn-filter-list c-set-padding-left-1 group-grid-wrap-item comm-filter"
              data-status="trash"><?php
            _e("Trash", "commercioo_order"); ?>
            <span class="comm_count_trash">(<?php echo esc_attr(count(comm_controller()->comm_get_result_data
                ('trash', "comm_order", 'ID'))); ?>)</span></span>
    </div>
</div>
<?php
$data = [
    'order_by' => 'ID',
    'post_type' => 'comm_order',
    'status' => json_encode(["comm_pending","comm_processing","comm_completed","comm_refunded","comm_abandoned","comm_failed","trash"])
];
$data = apply_filters( 'comm_admin_orders_data_params', $data );
?>
<!-- start list orders -->
<div class="table-responsive c-tbl c-list-orders c-general-orders c-list-table-data position-relative" data-tbl="orders"
     data-table='<?php echo esc_html(json_encode($data)); ?>'>
    <!--Table-->
    <table class="table c-table-list-orders">

        <!--Table head-->
        <thead class="c-table-list-orders-head">
            <tr>
                <th class="th-lg" data-orderable="false">
                    <div class="table-option">
                        <input type="checkbox" name="select-all">
                        <span class="btn btn-default" type="button" data-bs-toggle="dropdown"><i class="fa fa-angle-down"></i></span>
                        <ul class="dropdown-menu dropdown-menu-right">
                            <li><a href="#" class="c-bulk-edit comm-mark-as" data-action="comm_pending" data-type="bulk">Set to Pending</a></li>
                            <li><a href="#" class="c-bulk-edit comm-mark-as" data-action="comm_processing" data-type="bulk">Set to Processing</a></li>
                            <li><a href="#" class="c-bulk-edit comm-mark-as" data-action="comm_completed" data-type="bulk">Set to Completed</a></li>
                            <li><a href="#" class="c-bulk-edit comm-mark-as" data-action="comm_refunded" data-type="bulk">Set to Refunded</a></li>
                            <li><a href="#" class="c-bulk-edit comm-mark-as" data-action="comm_abandoned" data-type="bulk">Set to Abandoned</a></li>
                            <li><a href="#" class="c-bulk-edit comm-mark-as" data-action="comm_failed" data-type="bulk">Set to Failed</a></li>
                            <li><hr class="c-hr-line"></li>
                            <li><a href="#" class="c-bulk-edit delete delete-trash" data-type="bulk" data-action="trash">Delete selected</a></li>
                            <li style="display:none"><a href="#" class="c-bulk-edit delete delete-permanent" data-type="bulk" data-action="delete">Delete Permanently</a></li>
                        </ul>
                    </div>
                </th>
                <?php
                    $columns = apply_filters( 'comm_admin_orders_data_columns', array(
                        'Order ID',
                        'Name',
                        'Email',
                        'Mobile',
                        'Total',
                        'Date',
                        'Order Notes',
                        'Status',
                        // 'Actions',
                    ) );
                    foreach ( $columns as $column ) {
                        echo '<th class="th-lg">' . esc_html( $column ) . '</th>';
                    }
                ?>
            </tr>
        </thead>
        <!--Table head-->

        <!--Table body-->
        <tbody>
        </tbody>
        <!--Table head-->
        <tfoot class="c-table-list-orders-head">
            <tr>
            <th class="th-lg" data-orderable="false">
                    <div class="table-option">
                        <input type="checkbox" name="select-all">
                        <span class="btn btn-default" type="button" data-bs-toggle="dropdown"><i class="fa fa-angle-down"></i></span>
                        <ul class="dropdown-menu dropdown-menu-right">
                            <li><a href="#" class="c-bulk-edit delete delete-trash" data-type="bulk" data-action="trash">Delete selected</a></li>
                            <li style="display:none"><a href="#" class="c-bulk-edit delete delete-permanent" data-type="bulk" data-action="delete">Delete Permanently</a></li>
                        </ul>
                    </div>
                </th>
                <?php
                    $columns = apply_filters( 'comm_admin_orders_data_columns', array(
                        'Order ID',
                        'Name',
                        'Email',
                        'Mobile',
                        'Total',
                        'Date',
                        'Order Notes',
                        'Status',
                        // 'Actions',
                    ) );
                    foreach ( $columns as $column ) {
                        echo wp_kses_post('<th class="th-lg">' . esc_html( $column ) . '</th>');
                    }
                ?>
            </tr>
        </tfoot>
        <!--Table body-->
    </table>
    <!--Table-->
    <div class="c-list-orders c-filter-wrap-bottom">
        <div class="c-list-orders c-general-orders filter-orders">
           <span class="btn c-btn-filter-list comm-filter active pl-0" data-status="any"><?php
               _e("All", "commercioo_order"); ?>
             <span class="comm_count_all">(<?php echo esc_attr(count(comm_controller()->comm_get_result_data
                 (['comm_pending', 'comm_processing', 'comm_completed', 'comm_refunded','comm_abandoned','comm_failed','trash'],
                     "comm_order", 'ID'))); ?>)</span></span>

            <span class="btn c-btn-filter-list c-set-padding-left-1 comm-filter"
                  data-status="pending"><?php
                _e("Pending", "commercioo_order"); ?>
            <span class="comm_count_pending">(<?php echo esc_attr(count(comm_controller()->comm_get_result_data
                ('comm_pending', "comm_order", 'ID'))); ?>)</span></span>

            <span class="btn c-btn-filter-list c-set-padding-left-1 comm-filter"
                  data-status="processing"><?php
                _e("Processing", "commercioo_order"); ?>
            <span class="comm_count_processing">(<?php echo esc_attr(count(comm_controller()->comm_get_result_data
                ('comm_processing', "comm_order", 'ID'))); ?>)</span></span>

            <span class="btn c-btn-filter-list c-set-padding-left-1 comm-filter"
                  data-status="completed"><?php
                _e("Completed", "commercioo_order"); ?>
            <span class="comm_count_completed">(<?php echo esc_attr(count(comm_controller()->comm_get_result_data
                ('comm_completed', "comm_order", 'ID'))) ?>)</span></span>

            <span class="btn c-btn-filter-list c-set-padding-left-1 comm-filter"
                  data-status="refunded"><?php
                _e("Refunded", "commercioo_order"); ?>
            <span class="comm_count_refunded">(<?php echo esc_attr(count(comm_controller()->comm_get_result_data
                ('comm_refunded', "comm_order", 'ID'))); ?>)</span></span>
            <span class="btn c-btn-filter-list c-set-padding-left-1 group-grid-wrap-item comm-filter"
                  data-status="abandoned"><?php
                _e("Abandoned", "commercioo_order"); ?>
            <span class="comm_count_abandoned">(<?php echo esc_attr(count(comm_controller()->comm_get_result_data
                ('comm_abandoned', "comm_order", 'ID'))); ?>)</span></span>

            <span class="btn c-btn-filter-list c-set-padding-left-1 group-grid-wrap-item comm-filter"
                  data-status="failed"><?php
                _e("Failed", "commercioo_order"); ?>
            <span class="comm_count_failed">(<?php echo esc_attr(count(comm_controller()->comm_get_result_data
                ('comm_failed', "comm_order", 'ID'))); ?>)</span></span>

            <span class="btn c-btn-filter-list c-set-padding-left-1 group-grid-wrap-item comm-filter"
                  data-status="trash"><?php
                _e("Trash", "commercioo_order"); ?>
            <span class="comm_count_trash">(<?php echo esc_attr(count(comm_controller()->comm_get_result_data
                ('trash', "comm_order", 'ID'))); ?>)</span></span>
        </div>
    </div>
</div>
<!-- end start orders -->

<!-- Modal detail order -->
<div class="modal fade detail-order-modal" id="modaldetailorder" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog set-top-80" role="document">
        <div class="modal-content">
            <div class="modal-header set-pt-pb-15 align-items-center">
                <h2 class="modal-heading set-font-size-16 comm-detail-order-header">
                </h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="modal-content comm-detail-order-content">
                </div>
            </div>
        </div>
    </div>
</div>
<!-- End modal detail order -->


<!-- start form parsing order -->
<div class="c-parsing-orders c-general-orders">
    <form class="needs-validation" novalidate data-cond="orders">
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label>Data Order</label>
                    <textarea class="form-control c-setting-form-control c-input-form c-set-cursor-pointer"
                              name="address-shipping" rows="8"
                              placeholder="Siska Arum &#10;Jl. Kampung Nelayan no.19, Kel. Perahu, Kec. Dayung, Kota Sungai, 44278&#10;6281208120812 &#10;email@example.com &#10;1234, 1x, 199000 &#10;1235, 2x, 179000
                        "></textarea>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label>Format Order</label>
                    <textarea id="c-textarea-copy-keyboard"
                              class="form-control c-setting-form-control c-input-form c-set-cursor-pointer"
                              name="address-shipping" rows="8"
                              readonly>Siska Arum &#10;Jl. Kampung Nelayan no.19, Kel. Perahu, Kec. Dayung, Kota Sungai, 44278&#10;6281208120812 &#10;email@example.com &#10;1234, 1x, 199000 &#10;1235, 2x, 179000</textarea>
                    <span class="c-copy-to-clip" data-tippy-content="Click to copy to clipboard">
                            <i class="fa fa-clone"></i>
                        </span>
                    <div class="c-form-orders-desc">
                        <div>Baris ke-1 = nama penerima</div>
                        <div>Baris ke-2 = alamat penerima</div>
                        <div>Baris ke-3 = nomor handphone penerima</div>
                        <div>Baris ke-4 = email penerima</div>
                        <div>Baris ke-5, ke-6, dan seterusnya = produk ID yang dibeli diikuti jumlah dan harga (dipisah
                            dengan tanda koma). 1x sama dengan 1 buah, 2x sama dengan 2 buah, dan seterusnya. Harga
                            adalah harga satuan produk tersebut.
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-12 mt-4 mb-2 c-border-line"></div>

        <div class="row">
            <div class="col-md-4">
                <div class="form-group">
                    <label>CS Name <span class="c-orders-star">*</span></label>
                    <input type="text"
                           class="form-control c-setting-form-control cs-name-parsing c-input-form c-set-cursor-pointer"
                           placeholder="Nadia" required>
                    <div class="invalid-feedback">
                        Please enter cs name
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-12 mt-2 mb-4 c-border-line"></div>

        <div class="row">
            <div class="col-md-4">
                <button type="submit" class="btn btn-primary c-save-parsing">Save</button>
                <button type="button" class="btn btn-primary c-back">Cancel</button>
            </div>
        </div>
    </form>
</div>

<?php
if (function_exists("comm_do_update_shipping")) {
    comm_do_update_shipping();
}
?>
<?php do_action( 'comm_order_page' ); ?>