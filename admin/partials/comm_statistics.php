<?php
/**
 * Initial statistics ouput
 * Currently the initial output date query is the last 30 days
 */
$statistics   = new \commercioo\admin\Comm_Statistic;
$initial_data = $statistics->get_page_statistics_data_by_date();

?>

<!-- Start Title -->
<div class="c-col-container">
	<div class="d-flex">
		<div class="d-flex flex-row align-items-center flex-grow-1">
			<h2 class="page-title mr-3"><?php esc_html_e( 'Product Statistics', 'commercioo' ); ?></h2>
			<select name="product_id" id="product_id" class="form-control">
				<option value="0" ><?php esc_html_e( 'All Products', 'commercioo' ) ?></option>
				<?php
				$list_prod = comm_controller()->comm_get_result_data(['publish'], "comm_product", 'ID');
				foreach ( $list_prod as $prod ) {
					?><option value="<?php echo esc_attr( $prod->ID ) ?>" ><?php echo esc_html( $prod->post_title ) ?></option><?php
				}
				?>
			</select>
			<a href="javascript:;" class="btn btn-primary py-1 ml-3 c-btn-statistics c-add-statistics" id="get_statistics_data"><?php esc_html_e( 'Apply', 'commercioo' ) ?></a>
			<span class="btn btn-primary ml-3 c-btn-products c-add-text" id="statistics_loading" style='
				display: none;
				background-color: #007bff !important;
				color: #fff !important;
				border-color: #007bff !important'
			><?php esc_html_e( 'Loading..', 'commercioo' ) ?>
			</span>
		</div>
		<div class="c-date-range-statistics c-dr">
			<span></span> <i class="fa fa-caret-down c-dr-order-caret"></i>
		</div>
	</div>
</div>
<!-- End Title -->

<!-- For Statistic -->
<div class="row px-3">
	<div class="col-md-12">
		<div class="row c-one-layout">
			<div class="col-md c-col-container c-box-widget">
				<div class="d-flex">
					<div class="widget-title-wrap">
						<h2 class="widget-title"><?php esc_html_e( 'UNIQUE CHECKOUT VIEWS', 'commercioo' ) ?></h2>
					</div>
				</div>
				<div>
					<h2 class="big-text-second" id="statistics_data_checkout_views"><?php echo esc_html( $initial_data['checkout_views'] ) ?></h2>
				</div>
			</div>
			<div class="col-md c-col-container c-box-widget">
				<div class="d-flex">
					<div class="widget-title-wrap">
						<h2 class="widget-title"><?php esc_html_e( 'ORDERS', 'commercioo' ) ?></h2>
					</div>
					<span class="badge c-badge-success" id="statistics_data_orders_percentage"><?php echo esc_html( $initial_data['orders_percentage'] ) ?></span>
				</div>
				<div>
					<h2 class="big-text-second" id="statistics_data_orders_number"><?php echo esc_html( $initial_data['orders_number'] ) ?></h2>
				</div>
			</div>
			<div class="col-md c-col-container c-box-widget">
				<div class="d-flex">
					<div class="widget-title-wrap">
						<h2 class="widget-title"><?php esc_html_e( 'SALES', 'commercioo' ) ?></h2>
					</div>
					<span class="badge c-badge-success" id="statistics_data_sales_percentage"><?php echo esc_html( $initial_data['sales_percentage'] ) ?></span>
				</div>
				<div>
					<h2 class="big-text-second" id="statistics_data_sales_number"><?php echo esc_html( $initial_data['sales_number'] ) ?></h2>
				</div>
			</div>
		</div>
	</div>
</div>
<div class="row px-3">
	<div class="col-md-12">
		<div class="row c-one-layout">
			<div class="col-md c-col-container c-box-widget">
				<div class="d-flex">
					<div class="widget-title-wrap">
						<h2 class="widget-title"><?php esc_html_e( 'CLOSING RATE', 'commercioo' ) ?></h2>
					</div>
				</div>
				<div>
					<h2 class="big-text-second" id="statistics_data_closing_rate"><?php echo esc_html( $initial_data['closing_rate'] ) ?></h2>
				</div>
			</div>
			<div class="col-md c-col-container c-box-widget">
				<div class="d-flex">
					<div class="widget-title-wrap">
						<h2 class="widget-title"><?php esc_html_e( 'CUSTOMERS', 'commercioo' ) ?></h2>
					</div>
				</div>
				<div>
					<h2 class="big-text-second" id="statistics_data_customers_number"><?php echo esc_html( $initial_data['customers_number'] ) ?></h2>
				</div>
			</div>
			<div class="col-md c-col-container c-box-widget">
				<div class="d-flex">
					<div class="widget-title-wrap">
						<h2 class="widget-title"><?php esc_html_e( 'REVENUE', 'commercioo' ) ?></h2>
					</div>
				</div>
				<div>
					<div class="d-flex">
						<span class="c-small-text"><?php esc_html_e( 'Rp', 'commercioo' ) ?></span>
						<h2 class="big-text-second" id="statistics_data_revenue"><?php echo esc_html( $initial_data['revenue'] ) ?></h2>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<div class="row px-3">
	<div class="col-md-12">
		<div class="row c-one-layout">
			<div class="col-md c-col-container c-box-widget">
				<div class="d-flex">
					<div class="widget-title-wrap">
						<h2 class="widget-title"><?php esc_html_e( 'REFUNDS', 'commercioo' ) ?></h2>
					</div>
					<span class="badge c-badge-danger" id="statistics_data_refund_number_percentage"><?php echo esc_html( $initial_data['refund_number_percentage'] ) ?></span>
				</div>
				<div>
					<h2 class="big-text-second" id="statistics_data_refund_number"><?php echo esc_html( $initial_data['refund_number'] ) ?></h2>
				</div>
			</div>
			<div class="col-md c-col-container c-box-widget">
				<div class="d-flex">
					<div class="widget-title-wrap">
						<h2 class="widget-title"><?php esc_html_e( 'REFUND AMOUNT', 'commercioo' ) ?></h2>
					</div>
					<span class="badge c-badge-danger" id="statistics_data_refund_amount_percentage"><?php echo esc_html( $initial_data['refund_amount_percentage'] ) ?></span>
				</div>
				<div>
					<div class="d-flex">
						<span class="c-small-text"><?php esc_html_e( 'Rp', 'commercioo' ) ?></span>
						<h2 class="big-text-second" id="statistics_data_refund_amount"><?php echo esc_html( $initial_data['refund_amount'] ) ?></h2>
					</div>
				</div>
			</div>
			<div class="col-md c-col-container c-box-widget">
				<div class="d-flex">
					<div class="widget-title-wrap">
						<h2 class="widget-title" id="statistics_data_product_rank_label"><?php esc_html_e( 'PRODUCT RANK BY SALES', 'commercioo' ) ?></h2>
					</div>
	
					<!-- PRO Statistics Functions -->
					<?php if ( class_exists( 'Commercioo_Pro\Statistics' ) ) : ?>
					<div class="context-right">
						<span id="statistics_data_product_rank_selector" class="context-menu"><i class="fa fa-ellipsis-h"></i></span>
					</div>
					<?php endif ?>
				</div>
				<div>
					<div class="d-flex">
						<h2 class="big-text-second" id="statistics_data_product_rank"><?php echo esc_html( $initial_data['product_rank_by_sales'] ) ?></h2>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<!-- SALES SNAPSHOT -->

<div class="row px-3 c-double-container">
    <div class="col-md-12 c-col-container mt-0">
        <div class="d-flex align-items-center flex-grow-1">
            <div>
                <h2 class="widget-title-big"><?php esc_html_e( 'SALES SNAPSHOT', 'commercioo' ) ?></h2>
            </div>
        </div>
    </div>
    <div class="col-md-12 c-col-container">
        <div class="row c-subdouble-wrap">
            <div class="col-md c-subdouble-container">
                <div class="text-center">
                    <canvas id="snapshot" class="line-chart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- MOST CONVERTING DAYS -->
<div class="col-md-12" style="display: none">
    <div class="row c-two-layout">
		<div class="col-md-8 c-double-container">
				<div class="col-md-12 c-col-container mt-0">
					<div class="d-flex align-items-center">
						<div>
							<h2 class="widget-title-big">MOST CONVERTING DAYS</h2>
						</div>
					</div>
				</div>
				<div class="col-md-12 c-col-container mb-0">
					<div class="row c-subdouble-wrap">
						<div class="col-md c-subdouble-container">
							<div class="text-center">
								<span class="c-small-text-second">Orders</span>
								<img src="<?php echo esc_url( COMMERCIOO_URL . 'admin/img/arrow_long.svg' ) ?>" alt="" class="img-fluid" >
								<span class="c-small-text-second">Sales</span>
							</div>
							<div class="text-center">
								<span class="badge c-text-badge-pending">Pending</span>
								<img src="<?php echo esc_url( COMMERCIOO_URL . 'admin/img/arrow_short.svg' ) ?>" alt="" class="img-fluid" >
								<span class="badge c-text-badge-processing">Processing</span>
								<img src="<?php echo esc_url( COMMERCIOO_URL . 'admin/img/arrow_short.svg' ) ?>" alt="" class="img-fluid" >
								<span class="badge c-text-badge-completed">Completed</span>
							</div>
							<div class="table-responsive">
							<table class="table text-center c-table-stat-wrap">
								<tbody>
									<tr>
										<td class="set-border-td">1</td>
										<td class="set-border-td">2</td>
										<td class="set-border-td">3</td>
										<td class="set-border-td">4</td>
										<td class="set-border-td">5</td>
										<td class="set-border-td">6</td>
										<td class="set-border-td">7</td>
										<td class="set-border-td">8</td>
									</tr>
									<tr>
										<td class="set-border-td"><span class="badge c-badge-secondary">0.00%</span></td>
										<td class="set-border-td"><span class="badge c-badge-secondary">0.00%</span></td>
										<td class="set-border-td"><span class="badge c-badge-secondary">0.00%</span></td>
										<td class="set-border-td"><span class="badge c-badge-secondary">0.00%</span></td>
										<td class="set-border-td"><span class="badge c-badge-secondary">0.00%</span></td>
										<td class="set-border-td"><span class="badge c-badge-secondary">0.00%</span></td>
										<td class="set-border-td"><span class="badge c-badge-secondary">0.00%</span></td>
										<td class="set-border-td"><span class="badge c-badge-secondary">0.00%</span></td>
									</tr>
								</tbody>
							</table>
							</div>
							<div class="c-small-text-second text-center">
								SALES PERCENTACE
							</div>
						</div>
					</div>
				</div>
		</div>
		<div class="col-md-4">
			<div class="row ml-0 c-two-sublayout">
				<div class="col-md-12 c-col-container c-box-widget">
					<div class="d-flex">
						<div class="widget-title-wrap">
							<h2 class="widget-title"><?php esc_html_e( 'ARPU', 'commercioo' ) ?></h2>
						</div>
					</div>
					<div>
						<div class="d-flex">
							<span class="c-small-text"><?php esc_html_e( 'Rp', 'commercioo' ) ?></span>
							<h2 class="big-text-second" id="statistics_data_arpu"><?php echo esc_html( $initial_data['arpu'] ) ?></h2>
						</div>
					</div>
				</div>
				<div class="col-md-12 c-col-container c-box-widget">
					<div class="d-flex">
						<div class="widget-title-wrap">
							<h2 class="widget-title"><?php esc_html_e( 'ASPU', 'commercioo' ) ?></h2>
						</div>
					</div>
					<div>
						<div class="d-flex">
							<h2 class="big-text-second" id="statistics_data_aspu"><?php echo esc_html( $initial_data['aspu'] ) ?></h2>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<!-- DAYS # BECOMES SALES -->
<div class="col-md-12" style="display: none">
    <div class="row c-two-layout">
		<div class="col-md-8 c-double-container">
				<div class="col-md-12 c-col-container">
					<div class="d-flex align-items-center">
						<div>
							<h2 class="widget-title-big">DAYS # BECOME SALES</h2>
						</div>
					</div>
				</div>
				<div class="col-md-12 c-col-container mb-0">
					<div class="row c-subdouble-wrap">
						<div class="col-md c-subdouble-container">
							<div class="text-center">
								<span class="c-small-text-second">Orders</span>
								<img src="<?php echo esc_url( COMMERCIOO_URL . 'admin/img/arrow_long.svg' ) ?>" alt="" class="img-fluid" >
								<span class="c-small-text-second">Sales</span>
							</div>
							<div class="text-center">
								<span class="badge c-text-badge-pending">Pending</span>
								<img src="<?php echo esc_url( COMMERCIOO_URL . 'admin/img/arrow_short.svg' ) ?>" alt="" class="img-fluid" >
								<span class="badge c-text-badge-processing">Processing</span>
								<img src="<?php echo esc_url( COMMERCIOO_URL . 'admin/img/arrow_short.svg' ) ?>" alt="" class="img-fluid" >
								<span class="badge c-text-badge-completed">Completed</span>
							</div>
							<div class="table-responsive">
							<table class="table text-center c-table-stat-wrap">
								<tbody>
									<tr>
										<td class="set-border-td">1</td>
										<td class="set-border-td">2</td>
										<td class="set-border-td">3</td>
										<td class="set-border-td">4</td>
										<td class="set-border-td">5</td>
										<td class="set-border-td">6</td>
										<td class="set-border-td">7</td>
										<td class="set-border-td">8</td>
									</tr>
									<tr>
										<td class="set-border-td"><span class="badge c-badge-secondary">0.00%</span></td>
										<td class="set-border-td"><span class="badge c-badge-secondary">0.00%</span></td>
										<td class="set-border-td"><span class="badge c-badge-secondary">0.00%</span></td>
										<td class="set-border-td"><span class="badge c-badge-secondary">0.00%</span></td>
										<td class="set-border-td"><span class="badge c-badge-secondary">0.00%</span></td>
										<td class="set-border-td"><span class="badge c-badge-secondary">0.00%</span></td>
										<td class="set-border-td"><span class="badge c-badge-secondary">0.00%</span></td>
										<td class="set-border-td"><span class="badge c-badge-secondary">0.00%</span></td>
									</tr>
								</tbody>
							</table>
							</div>
							<div class="c-small-text-second text-center">
								SALES PERCENTACE
							</div>
						</div>
					</div>
				</div>
		</div>
		<div class="col-md-4">
			<div class="row ml-0">
				<div class="col-md-12 c-col-container c-box-widget">
					<div class="d-flex">
						<div class="widget-title-wrap">
							<h2 class="widget-title"><?php esc_html_e( 'CUSTOMERS', 'commercioo' ) ?></h2>
						</div>
					</div>
					<div>
						<canvas id="pie-customer" class="pie-chart"></canvas>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<!-- FOLLOWUP # BECOME SALES -->
<div class="row px-3">
	<div class="col-md-12">
		<div class="row c-two-layout">
			<div class="col-md-8 c-double-container">
				<?php if ( is_comm_wa_followup() ) : ?>
					<div class="col-md-12 mt-0 c-col-container">
						<div class="d-flex align-items-center">
							<div>
								<h2 class="widget-title-big">FOLLOWUP # BECOME SALES</h2>
							</div>
						</div>
					</div>
					<div class="col-md-12 c-col-container mb-0">
						<div class="row c-subdouble-wrap">
							<div class="col-md c-subdouble-container">
								<div class="text-center position-relative">
									<span class="c-statistics-description">How many followups?</span>
									<span class="c-small-text-second">Orders</span>
									<img src="<?php echo esc_url( COMMERCIOO_URL . 'admin/img/arrow_long.svg' ) ?>" alt="" class="img-fluid c-arrow-statistics" >
									<span class="c-small-text-second">Sales</span>
								</div>
								<div class="text-center">
									<span class="badge c-text-badge-pending">Pending</span>
									<img src="<?php echo esc_url( COMMERCIOO_URL . 'admin/img/arrow_short.svg' ) ?>" alt="" class="img-fluid c-arrow-statistics" >
									<span class="badge c-text-badge-processing">Processing</span>
									<img src="<?php echo esc_url( COMMERCIOO_URL . 'admin/img/arrow_short.svg' ) ?>" alt="" class="img-fluid c-arrow-statistics" >
									<span class="badge c-text-badge-completed">Completed</span>
								</div>
								<div class="table-responsive">
								<table class="table text-center c-table-stat-wrap" id="followup-wa-statistic">
									<tbody>
										<tr>
											<td class="set-border-td"></td>
											<td class="set-border-td">0</td>
											<td class="set-border-td">1st</td>
											<td class="set-border-td">2nd</td>
											<td class="set-border-td">3rd</td>
											<td class="set-border-td">4th</td>
											<td class="set-border-td">5th</td>
											<td class="set-border-td">6th</td>
										</tr>
										<tr>
											<td class="set-border-td"><span class="c-small-text-second">SALES PERCENTAGE</span></td>
											<td class="set-border-td"><span class="badge c-badge-secondary" data-followup="0">0.00%</span></td>
											<td class="set-border-td"><span class="badge c-badge-secondary" data-followup="1">0.00%</span></td>
											<td class="set-border-td"><span class="badge c-badge-secondary" data-followup="2">0.00%</span></td>
											<td class="set-border-td"><span class="badge c-badge-secondary" data-followup="3">0.00%</span></td>
											<td class="set-border-td"><span class="badge c-badge-secondary" data-followup="4">0.00%</span></td>
											<td class="set-border-td"><span class="badge c-badge-secondary" data-followup="5">0.00%</span></td>
											<td class="set-border-td"><span class="badge c-badge-secondary" data-followup="6">0.00%</span></td>
										</tr>
									</tbody>
								</table>
								</div>
							</div>
						</div>
					</div>
				<?php endif; ?>
			</div>
			<div class="col-md-4">
				<div class="row ml-0">
					<div class="col-md-12 mt-0 c-col-container c-box-widget">
						<div class="d-flex justify-content-center mb-3">
							<div class="widget-title-wrap">
								<h2 class="widget-title"><?php esc_html_e( 'RATIO BY', 'commercioo' ) ?> <span class="ratio-type"></span></h2>
							</div>
						</div>
						<div>
							<canvas id="pie-ratio" class="pie-chart"></canvas>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<!-- ORDER STATUS RATIO -->
<div class="row px-3">

	<div class="col-md-12">
		<div class="row c-two-layout">
			<div class="col-md-8 c-double-container">
				<!-- none to see here -->
			</div>
			<div class="col-md-4">
				<div class="row ml-0">
					<div class="col-md-12 mt-0 c-col-container c-box-widget">
						<div class="d-flex">
							<div class="widget-title-wrap">
								<h2 class="widget-title"><?php esc_html_e( 'ORDER STATUS RATIO', 'commercioo' ) ?></h2>
							</div>
						</div>
						<div>
							<canvas id="pie-order" class="pie-chart"></canvas>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>