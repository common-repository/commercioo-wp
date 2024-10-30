<!-- Start form add products -->
<div class="c-add-products c-col-container c-general-products">
	<form class="needs-validation c-add-products-validation" novalidate data-cond="product">
		<div class="row">
			<div class="col-md-6">

				<div class="form-group mb-2">
					<label><?php esc_html_e( 'Product Name *', 'commercioo' ); ?></label>
					<input type="text" class="form-control c-setting-form-control product-name c-input-form c-set-cursor-pointer" name="title" placeholder="Product Name" required>
					<div class="invalid-feedback"><?php esc_html_e( 'Please enter product name', 'commercioo' ) ?></div>
				</div>
				<div class="form-group mb-2">
					<label><?php esc_html_e( 'Product Slug', 'commercioo' ); ?></label>
					<input type="text" class="form-control c-setting-form-control product-name c-input-form c-set-cursor-pointer" name="slug" placeholder="Product Slug">
					<div class="invalid-feedback"><?php esc_html_e( 'Please enter product slug', 'commercioo' ) ?></div>
				</div>

				<div class="row mb-2">
					<div class="form-group col-md-6">
						<label><?php esc_html_e( 'Featured', 'commercioo' ); ?></label>
						<div class="c-input-checkbox">
							<input class="form-check-input c-input-form c-set-cursor-pointer is_featured" type="checkbox" id="_is_featured" name="is_featured">
							<label class="form-check-label m-0" for="_is_featured"><?php esc_html_e( 'Check Featured', 'commercioo' ); ?></label>
						</div>
					</div>
				</div>

				<div class="row mb-2">
					<div class="form-group col-md-6">
						<label>
							<?php esc_html_e( 'SKU', 'commercioo' ); ?>
							<i class="fa fa-info-circle c-cursor" data-bs-container="body" data-bs-toggle="popover" data-bs-placement="top" data-bs-trigger="click hover" data-bs-content="<?php esc_attr_e("SKU refers to a Stock-keeping unit, a unique identifier for each distinct product and service that can be purchased.", "commercioo"); ?>">
							</i>
						</label>
						<input type="text" class="form-control c-setting-form-control sku c-input-form c-set-cursor-pointer" name="sku" placeholder="<?php esc_attr_e( 'SKU', 'commercioo'); ?>">
						<span class="text-danger status-sku"></span>
					</div>
					<div class="form-group col-md-6">
						<label>
							<?php esc_html_e( 'Stock status', 'commercioo' ); ?>
							<i class="fa fa-info-circle c-cursor" data-bs-container="body" data-bs-toggle="popover" data-bs-placement="top" data-bs-trigger="click hover" data-bs-content="<?php esc_attr_e("Controls whether or not the product is listed as 'in stock' or
							'out of stock' on the frontend.", "commercioo"); ?>">
							</i>
						</label>
						<select class="form-control c-setting-form-control stock_status" name="stock_status">
							<option value="instock"><?php esc_html_e( 'In stock', 'commercioo' ); ?></option>
							<option value="outofstock"><?php esc_html_e( 'Out of stock', 'commercioo' ); ?></option>
						</select>
					</div>
				</div>

				<?php ob_start(); ?>
				<div class="form-group mb-2">
					<label><?php esc_html_e( 'Price', 'commercioo' ); ?></label>
					<div class="input-group mb-3">
						<div class="input-group-prepend c-setting-form-control">
							<div class="input-group-text c-setting-form-control">Rp</div>
						</div>
						<input type="number" class="form-control c-setting-form-control price c-input-form c-set-cursor-pointer" placeholder="200000" name="regular_price" value="0">
					</div>
					<span class="text-danger status-price"></span>
				</div>
				<?php echo apply_filters( 'commercioo_product_price_field', ob_get_clean() ); ?>
				<div class="row mb-2">
					<div class="form-group col-md-6">
					<div class="c-input-checkbox">
						<input class="form-check-input c-input-form c-set-cursor-pointer" type="checkbox" id="free_shipping" name="free_shipping">
						<label class="form-check-label m-0" for="free_shipping"><?php esc_html_e( 'Free Shipping', 'commercioo' ); ?></label>
					</div>
					</div>
				</div>
				<?php //echo apply_filters( 'commercioo_product_fee_field', ob_get_clean() ); ?>

				<div class="mt-4 mb-3 ml-3 c-line-settings c-set-width-setting"></div>

				<div class="form-group mb-2">
					<label><?php esc_html_e( 'Description', 'commercioo'); ?></label>
					<textarea class="form-control c-setting-form-control c-form-with-bg-copy c-input-form c-set-cursor-pointer" id="content" rows="8" name="content"></textarea>
				</div>

				<div class="mt-4 mb-3 ml-3 c-line-settings c-set-width-setting"></div>

				<div class="form-group mb-2">
					<label><?php esc_html_e( 'Additional Description', 'commercioo' ); ?></label>
					<textarea class="form-control c-setting-form-control c-form-with-bg-copy c-input-form c-set-cursor-pointer" id="additional_description" rows="8" name="additional_description"></textarea>
				</div>

				<div class="mt-4 mb-3 ml-3 c-line-settings c-set-width-setting"></div>

				<?php if ( is_comm_pro() ) : ?>
					<div class="form-group mb-2">
						<label><?php esc_html_e( 'Included Items', 'commercioo' ); ?></label>
						<div class="c-products-cloneit">
							<div class="row mb-2 c-products-form product-form-pqp form-orders-multi">
								<div class="form-group col-md-10">
									<input type="text" class="form-control c-setting-form-control included_items" placeholder="Product Item">
									<span class="text-danger status-order-product"></span>
								</div>
								<div class="form-group col-md-2 position-relative">
									<div class="form-group col-md-2 c-btn-pm-orders-wrap">
										<span class="c-orders-plus add-orders">
											<i class="feather-16" data-feather="plus"></i>
										</span>
										<span class="c-orders-minus remove-orders">
											<i class="feather-16" data-feather="minus"></i>
										</span>
									</div>
								</div>
							</div>
						</div>
						<div class="c-products-clone"></div>
						<div class="multi-product-form-pqp"></div>
					</div>
				<?php endif; ?>

				<div class="mt-4 mb-3 ml-3 c-line-settings c-set-width-setting"></div>

				<?php if ( is_comm_wa() ) : ?>
					<div class="form-group mb-2">
						<label><?php esc_html_e( 'WhatsApp Order Message', 'commercioo' ); ?></label>
						<textarea class="form-control c-setting-form-control wa_form_integration" name="whatsapp_order_msg" rows="8"></textarea>
						<div class="c-form-products-desc">
							<div><?php esc_html_e( 'Dynamic variable:', 'commercioo' ); ?></div>
							<div>{product_price} - <?php esc_html_e( "List of product in the order with the price.", 'commercioo' ); ?></div>
							<div>{total_price} - <?php esc_html_e( "Total price must to pay by user.", 'commercioo' ); ?></div>
							<div>{name} - <?php esc_html_e( "The buyer's first name", 'commercioo' ); ?></div>
							<div>{fullname} - <?php esc_html_e( "The buyer's full name, first and last", 'commercioo' ); ?></div>
							<div>{user_email} - <?php esc_html_e( "The buyer's email address", 'commercioo' ); ?></div>
							<div>{user_billing_address} - <?php esc_html_e( "The buyer's billing address", 'commercioo' ); ?></div>
							<div>{user_shipping_address} - <?php esc_html_e( "The buyer's shipping address", 'commercioo' ); ?></div>
							<div>{user_phone} - <?php esc_html_e( "The buyer's phone number", 'commercioo' ); ?></div>
							<div>{sitename} - <?php esc_html_e( "Your site name", 'commercioo' ); ?></div>
							<div>Formatting you can use: bold, italic, strikethrough.</div>
						</div>
					</div>
					<div class="mt-4 mb-3 ml-3 c-line-settings c-set-width-setting"></div>
				<?php endif; ?>

			</div>
			<div class="col-md-6">

				<div class="form-group mb-2">
					<label><?php esc_html_e( 'Product Photo (Featured)', 'commercioo' ) ?></label>
					<div class="input-group">
						<input type="hidden" class="c-image-featured" value="" name="product_featured">
						<div class="input-group-append">
							<button type="button" class="browse btn btn-primary c-setting-form-control mb-0">
								<i class="fa fa-upload" aria-hidden="true"></i> <?php esc_html_e( 'Upload', 'commercioo' ); ?>
							</button>
						</div>
					</div>
					<div class="comm-clear c-form-products-desc">
						<?php esc_html_e( 'Recommended image size:', 'commercioo' ) ?>
						<br/>
						<?php esc_html_e( 'minimun 490 x 490 pixel, maximum 1440 x 1440 pixel.', 'commercioo' ) ?>
					</div>
					<div class="row c-set-image">
						<div class="c-photo c-list-preview-image"></div>
					</div>
				</div>

				<div class="form-group mb-2">
					<label class="label-product-photo"><?php esc_html_e( 'Product Photo (Preview Gallery)', 'commercioo' ); ?></label>
					<div class="input-group">
						<input type="hidden" class="c-image-gallery" value="" name="product_gallery">
						<div class="input-group-append">
							<button type="button" class="browse-gallery btn btn-primary c-setting-form-control mb-0">
								<i class="fa fa-upload" aria-hidden="true"></i> <?php esc_html_e( 'Upload', 'commercioo' ); ?>
							</button>
						</div>
					</div>
					<div class="comm-clear c-form-products-desc">
						<?php esc_html_e( 'Recommended image size:', 'commercioo' ) ?>
						<br/>
						<?php esc_html_e( 'minimun 490 x 490 pixel, maximum 1440 x 1440 pixel.', 'commercioo' ) ?>
					</div>
					<div class="row c-set-image">
						<div class="col-md-12 c-photo set-gallery-image"></div>
					</div>
				</div>

				<div class="mt-4 mb-3 ml-3 c-line-settings c-set-width-setting"></div>

				<?php if ( is_comm_pro() ) : ?>
					<div class="row mb-2">
						<div class="form-group col-md-6">
							<label><?php esc_html_e( 'Product Data', 'commercioo' ); ?></label>
							<select class="form-control c-setting-form-control" name="product_data">
								<option value="standard"><?php esc_html_e( 'Standard', 'commercioo' ); ?></option>
								<option value="variable"><?php esc_html_e( 'Variable', 'commercioo' ); ?></option>
							</select>
						</div>
						<div class="form-group col-md-6">
							<label><?php esc_html_e( 'Product Type', 'commercioo' ); ?></label>
							<select class="form-control c-setting-form-control" name="product_type">
								<option value="physical"><?php esc_html_e( 'Physical Product', 'commercioo' ); ?></option>
								<option value="digital"><?php esc_html_e( 'Digital Product', 'commercioo' ); ?></option>
							</select>
						</div>
					</div>
					<?php
	                if (function_exists("comm_do_pro_digital_product")) {
	                    comm_do_pro_digital_product();
	                }
	                ?>
					<div class="product-data product-data-variable product-type-physical">
						<div class="form-group">
							<label>
								<?php esc_html_e( 'Variations', 'commercioo' ); ?>
								<span class="c-btn-add-attributes c-add-text">+ <?php esc_html_e( 'Add', 'commercioo' ); ?></span>
							</label>
							<table class="product-attributes">
							</table>
							<table class="product-variations">
								<thead>
									<td>
										<a href="#" class="edit-product-variation">
											<i class="fa fa-edit"></i>
											<?php esc_html_e( 'Edit', 'commercioo' ) ?>
										</a>
									</td>
									<td><?php esc_html_e( 'Price', 'commercioo' ); ?></td>
									<td><?php esc_html_e( 'Stock', 'commercioo' ); ?></td>
									<td><?php esc_html_e( 'Weight', 'commercioo' ); ?></td>
									<td><?php esc_html_e( 'SKU', 'commercioo' ); ?></td>
								</thead>
								<tbody>
								</tbody>
							</table>
						</div>
					</div>

					<div class="product-data product-data-standard product-type-physical">
						<div class="form-group">
							<label><?php esc_html_e( 'Weight (kg)', 'commercioo' ); ?></label>
							<input type="number" class="form-control c-setting-form-control weight c-input-form c-set-cursor-pointer" value="0" min="0" step="0.01" data-number-to-fixed="2" name="weight">
						</div>
					</div>

				<?php else: ?>
					<div class="form-group mb-2">
						<label><?php esc_html_e( 'Weight (kg)', 'commercioo' ); ?></label>
						<input type="number" class="form-control c-setting-form-control weight c-input-form c-set-cursor-pointer" value="0" min="0" step="0.01" data-number-to-fixed="2" name="weight">
					</div>
				<?php endif; ?>

				<div class="mt-4 mb-3 ml-3 c-line-settings c-set-width-setting"></div>

				<div class="row mb-2">
					<div class="form-group col-md-6">
						<label><?php esc_html_e( 'Category', 'commercioo' ); ?></label>
						<?php
						$taxonomies = get_terms( 'comm_product_cat', array(
							'hide_empty' => 0
						) );
						?>
						<select name="comm_product_cat[]" class="form-control c-setting-form-control c-select2" multiple>
							<?php foreach ($taxonomies as $category) : ?>
								<option value="<?php echo esc_attr( $category->term_id ); ?>"><?php echo esc_html( $category->name ) ?></option>';
							<?php endforeach; ?>
						</select>
					</div>
					<div class="form-group col-md-6">
						<label><?php esc_html_e( 'Tag', 'commercioo' ); ?></label>
						<?php
						$taxonomies = get_terms( 'comm_product_tag', array(
							'hide_empty' => 0
						) );
						?>
						<select name="comm_product_tag[]" class="form-control c-setting-form-control c-select2" multiple>
							<?php foreach ( $taxonomies as $tag ) : ?>
								<option value="<?php echo esc_attr( $tag->term_id ); ?>"><?php echo esc_html( $tag->name ) ?></option>';
							<?php endforeach; ?>
						</select>
					</div>
				</div>

				<div class="row mb-2">
					<div class="form-group col-md-6">
						<label class="m-0"><?php esc_html_e( 'Checkout Redirect to', 'commercioo' ); ?> <i class="fa fa-info-circle c-cursor" data-bs-container="body" data-bs-toggle="popover" data-bs-placement="top" data-bs-trigger="click hover" data-bs-content="<?php esc_attr_e("Check to enable overwrite setting of Checkout Redirect to. When enable, this product will use the setting below.", "commercioo"); ?>"></i></label>
						<div class="c-input-checkbox">
							<input class="form-check-input c-input-form c-set-cursor-pointer overwrite_thank_you_redirect" type="checkbox" id="_overwrite_thank_you_redirect" name="overwrite_thank_you_redirect">
							<label class="form-check-label" for="_overwrite_thank_you_redirect"><?php esc_html_e( 'Enable Overwite Setting', 'commercioo' ); ?></label>
						</div>
						<select class="form-control c-setting-form-control c-overwrite_thank_you_redirect" name="thank_you_redirect">
							<?php if (is_comm_wa()): ?>
								<option value="wa"><?php esc_html_e( 'WhatsApp', 'commercioo' ); ?></option>
							<?php endif; ?>
							<option value="page"><?php esc_html_e( 'Page', 'commercioo' ); ?></option>
						</select>
					</div>
					<div class="form-group col-md-6">
						<label><?php esc_html_e( 'Status', 'commercioo' ); ?></label>
						<select class="form-control c-setting-form-control" name="status">
                            <option value="publish"><?php esc_html_e( 'Published', 'commercioo' ); ?></option>
							<option value="draft"><?php esc_html_e( 'Draft', 'commercioo' ); ?></option>
						</select>
					</div>
				</div>
				
				<?php if ( is_comm_ar() ): ?>
				<div class="row mb-2">
					<div class="form-group col-md-6">
						<label><?php esc_html_e( 'Autoresponder Form Integration', 'commercioo' ); ?></label>
						<select class="form-control c-setting-form-control c-select2 ar_form_integration" name="ar_form_integration[]" multiple>
							<?php
							$args = array(
								'post_type' => 'comm_ar',
								'post_status' => ['publish', 'draft'],
								'posts_per_page' => "-1", // -1 mean show all data
							);

							$get_data = get_posts($args);
							if ($get_data):
								foreach ($get_data as $get_data_ar):
									$title = $get_data_ar->post_title;
									$id = $get_data_ar->ID;
									if ($title):
										?>
										<option value="<?php echo intval($id); ?>"><?php echo esc_attr
											($title); ?>
										</option>
									<?php endif; ?>
								<?php endforeach; ?>
							<?php endif; ?>
						</select>
					</div>
				</div>
				<?php endif; ?>

				<div class="mt-4 mb-3 ml-3 c-line-settings c-set-width-setting"></div>

				<div class="c-editable">
					<div class="form-group">
						<label><?php esc_html_e( 'Checkout URL', 'commercioo' ) ?></label>
						<input type="text" class="form-control comm-order-form-url c-setting-form-control c-form-with-bg-copy c-set-cursor-pointer" placeholder="https://" readonly>
						<span class="c-copy-to-clip" data-bs-container="body" data-bs-toggle="popover" data-bs-placement="top" data-bs-trigger="hover" data-bs-content="<?php esc_attr_e("Click to copy to clipboard", "commercioo"); ?>">
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

				<div class="mt-4 mb-3 ml-3 c-line-settings c-set-width-setting"></div>
				
			</div>
		</div>
		<div class="row">
			<div class="col-md-4">
				<button type="submit" class="btn btn-primary c-save-products"><?php esc_html_e( 'Save', 'commercioo' ); ?></button>
				<button type="button" class="btn btn-primary c-back"><?php esc_html_e( 'Cancel', 'commercioo' ); ?></button>
			</div>
		</div>
	</form>
</div>
<!-- End form add products -->

<?php do_action( 'commercioo_after_edit_product' ); ?>