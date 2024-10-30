<!-- START RECENT ORDERS -->
<div class="c-hide-layouts top-product">
    <div class="c-col-container">
        <div class="d-flex align-items-center">
            <div>
                <h2 class="widget-title-big"><?php _e('RECENT ORDERS','commercioo');?></h2>
            </div>
        </div>
    </div>
    <?php
    $data = [
        'orderby'=>'id',
        'order'=>'DESC',
        'post_type'=>'comm_order',
        'numberposts'=>10,
        'post_status'=>json_encode(["comm_pending", "comm_processing", "comm_completed"]),
    ];
    ?>
    <table id="comm-recent-orders" class="table c-list-table-data" data-tbl="orders" data-table='<?php echo esc_html(json_encode($data));?>'>
        <thead class="c-table-top-products-head">
        <tr>
            <?php
            $columns = apply_filters('comm_admin_recent_orders_data_columns', array(
                'Order ID',
                'Name',
                'Email',
                'Product',
                'Total',
                'Status',
            ));
            foreach ($columns as $column) {
                echo wp_kses_post('<th class="th-lg">' . esc_html($column) . '</th>');
            }
            ?>
        </tr>
        </thead>
        <tbody class="c-table-recent-orders-body">
        </tbody>
    </table>
</div>
<!-- END RECENT ORDERS -->