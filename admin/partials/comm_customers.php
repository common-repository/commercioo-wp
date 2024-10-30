<?php
    /**
     * Check if there's unscync data
     */
    $comm_customers = new \commercioo\admin\Comm_Customer;
    $orders = $comm_customers->get_unsync_order();

    $changelog = new Commercioo_Changelog(COMMERCIOO_VERSION);
    $is_updated = true;
    $isupdated = $changelog->comm_compare_version();
    if ( $isupdated ) {
        $is_updated = false;
    }
?>
<!-- Start Title -->
<div class="col-md-12 c-col-container">
    <div class="d-flex">
        <div class="d-flex flex-row align-items-center flex-grow-1">
            <h2 class="page-title"><?php _e("Customers", "Commercioo_title"); ?></h2>
            <?php if(count($orders) > 0 && $is_updated){
                echo '<span class="btn btn-primary ml-3 c-btn-products c-add-text" id="sync_customer">'.__( 'Sync', 'commercioo' ).'</span>';
            }?>
        </div>
        <div class="c-search-customer c-dr">
            <input type="text" class="c-customer-search" name="search" placeholder="<?php esc_attr_e('Search...','commercioo');?>"> <i class="fa fa-search c-dr-order-caret"></i>
        </div>
    </div>
</div>
<!-- End Title -->

<?php
$data = [
    'order_by' => 'ID',
    'post_type' => 'comm_user',
    'status' => json_encode(["comm_pending", "comm_processing", "comm_completed", "comm_refunded", "trash"])
];
$data = apply_filters( 'comm_admin_orders_data_params', $data );
?>
<!-- start list customers -->
<div class="table-responsive c-list-customers c-general-customers c-list-table-data" data-tbl="customers"
     data-table='<?php echo esc_html(json_encode($data)); ?>'>
    <!--Table-->
    <table id="comm-customers" class="table c-table-list-customers">

        <!--Table head-->
        <thead class="c-table-list-customers-head">
        <tr>
            <?php
                $columns = apply_filters( 'comm_admin_customers_data_columns', array(
                    'checkall',
                    'ID',
                    'Name',
                    'Email',
                    'Phone',
                    'All Orders',
                    'Sales',
                    'Total Spent',
                    'Last Order',
                    'Registration Date',
                ) );
                foreach ( $columns as $column ) {
                    if($column == 'checkall'){
                        echo wp_kses_post('<th class="th-lg" data-orderable="false">
                                <div class="table-option">
                                    <input type="checkbox" name="select-all" id="select-all">
                                    <span class="btn btn-default" type="button" data-bs-toggle="dropdown"><i class="fa fa-angle-down"></i></span>
                                    <ul class="dropdown-menu dropdown-menu-right">
                                      <li class="delete"><a class="delete-selected">Delete selected</a></li>
                                    </ul>
                                </div>
                            </th>');
                    }else{
                        echo wp_kses_post('<th class="th-lg">' . esc_html( $column ) . '</th>');
                    }
                }
            ?>
        </tr>
        </thead>
        <!--Table head-->

        <!--Table body-->
        <tbody>
        </tbody>
        <!--Table body-->
    </table>
    <!--Table-->
</div>
<!-- end start customers -->

<!-- Modal detail order -->
<div class="modal fade" id="modaldetailcustomer" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg set-top-80" role="document">
        <div class="modal-content">
            <div class="modal-header set-pt-pb-15">
                <h2 class="modal-heading set-font-size-16 comm-detail-customer-header">
                </h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="modal-content comm-detail-customer-content">
                </div>
            </div>
        </div>
    </div>
</div>
<!-- End modal detail order -->

<?php do_action( 'comm_customer_page' ); ?>