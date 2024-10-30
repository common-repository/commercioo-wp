<?php
global $post, $comm_options;
$get_best_seller = apply_filters("comm_calculate_bestSeller", 1);
$badged_best_seller = '';
$term_name = '';
$get_term = get_the_terms($post->ID, 'comm_product_cat');

if ($get_best_seller) {
    if ($post->ID == $get_best_seller['0']->product_id) {
        $badged_best_seller = '<span class="badge badge-best-seller">#1 BEST SELLER</span>';
    }
}

$product = comm_get_product( $post->ID );

$is_commercioo_pro_installed = class_exists('Commercioo_Pro');
$manage_stock = get_post_meta($post->ID, "_manage_stock", true);
$get_stock = get_post_meta($post->ID, "_stock", true);
$get_stock_status = get_post_meta($post->ID, "_stock_status", true);
$sku = get_post_meta($post->ID, "_sku", true);
$sku_html = '<span class="set-font-bold">%s</span>';
if ($sku) {
    $sku = sprintf($sku_html, $sku);
} else {
    $sku = sprintf($sku_html, "-");
}
switch ($get_stock_status) {
    case "instock":
        $stock_status = "In Stock";
        break;
    case "outofstock":
        $stock_status = "Out of Stock";
        break;
    case "onbackorder":
        $stock_status = "Out of Stock";
        break;
    default:
        $stock_status = "In Stock";
        break;
}
$stock = "Out of stock";
$stock_view = '<span class="text-danger set-font-bold">%s</span>';
if ($manage_stock && $is_commercioo_pro_installed) {
    if ($get_stock > 0) {
        $stock = $get_stock;
    }
} else {
    $stock = $stock_status;
}
$get_product_featured = get_post_meta($post->ID, "_product_featured", true);
$feature_url = '';
$is_featured_prod_text = '';
if ($get_product_featured) {
    $url = wp_get_attachment_image_src($get_product_featured,"large",false);
    $feature_url = $url[0];
    $is_featured_prod_text = '<div class="set-line-height"><span class="box-first">FEATURED</span></div>';
} else {
    $feature_url = COMMERCIOO_URL . 'img/commercioo-no-img.png';
}
$get_product_gallery = get_post_meta($post->ID, "_product_gallery", true);

if ($get_product_gallery) {
    $get_product_gallery = explode(",", $get_product_gallery);
} else {
    $get_product_gallery = false;
}
$stock = sprintf($stock_view, $stock);
$is_featured_prod_img = $is_featured_prod_text;
$post_content = $post->post_content;
$get_additional_description = get_post_meta($post->ID, "_additional_description", true);
if (!$get_additional_description) {
    $get_additional_description = "";
}
?>
<div class="set-max-width-theme">
        <div class="row">
            <div class="col-md-1 set-column-multi-gallery mb-4">
                <div class="desktop-view">
                    <div id="sync2" class="comm-owl-carousel-thumb owl-carousel owl-theme">
                        <div class="item">
                            <img src="<?php echo esc_url($feature_url); ?>" class="image-gallery-product">
                        </div>
                        <?php if ($get_product_gallery): ?>
                            <?php foreach ($get_product_gallery as $get_product_gallery_v): ?>
                                <?php
                                $url_gallery = wp_get_attachment_image_src($get_product_gallery_v,"large",false);
                                $gallery_url = $url_gallery[0];
                                ?>
                                <div class="item">
                                    <img src="<?php echo esc_url($gallery_url); ?>" class="image-gallery-product">
                                </div>
                            <?php endforeach; endif; ?>
                    </div>
                </div>
            </div>
            <div class="col-md-5 set-column-one-gallery mb-4">
				<div class="box-gallery-product">
					<?php echo wp_kses_post($is_featured_prod_img); ?>
				</div>
                <div id="sync1" class="comm-owl-carousel owl-carousel owl-theme">
                    <div class="item">                        
                        <img src="<?php echo esc_url($feature_url); ?>" class="image-gallery-product-show">
                    </div>
                    <?php if ($get_product_gallery): ?>
                        <?php foreach ($get_product_gallery as $get_product_gallery_v): ?>
                            <?php
                            $url_gallery = wp_get_attachment_image_src($get_product_gallery_v,"large");
                            $gallery_url = $url_gallery[0];
                            ?>
                            <div class="item">
                                <img src="<?php echo esc_url($gallery_url); ?>" class="image-gallery-product-show">
                            </div>
                        <?php endforeach; endif; ?>
                </div>
            </div>

            <!-- Right Product -->
            <div class="col-md-5 mb-4">
                <h2 class="product-name"><?php echo esc_attr($post->post_title); ?></h2>
                <div class="col-md-12 row">
                    <div class="p-0 mx-0">
                        <?php echo wp_kses_post($badged_best_seller); ?>
                        <?php
                        if ($get_term) {
                            $term_name_v = [];
                            $terms_names = '';
                            $term_name = '<span class="mx-0 list-product-review">in %s</span>';
                            foreach ($get_term as $k => $get_term_v) {
                                $term_id = $get_term_v->term_id;
                                $term_link = get_term_link($term_id);
                                $term_name_v [$k] = '<a href="' . $term_link . '" class="link-categories">'
                                    . $get_term_v->name
                                    . '</a>';
                                $terms_names = implode(",", $term_name_v);
                            }
                            echo wp_kses_post(sprintf($term_name, $terms_names));
                        }
                        ?>
                    </div>
                </div>
                <div class="col-md-12 row">
                    <div class="product-price-wrapper p-0">
                        <?php if ( $product->is_on_sale() ) : ?>
                            <span class="list-product-price-detail"><?php echo esc_html($product->get_sale_price_display()); ?></span>
                            <span class="list-product-price-special text-line-product"><?php echo esc_html($product->get_regular_price_display()); ?></span>
                            <?php if ( 'standard' === $product->get_product_data() ) : ?>
                                <?php
                                    $regular_price = $product->get_regular_price();
                                    $sale_price    = $product->get_sale_price();
                                ?>
                                <span class="list-product-review ml-1 mr-1">|</span>
                                <span class="list-product-price-special">
                                    <?php esc_html_e( 'SAVE', 'commercioo' ); ?>
                                    <?php echo esc_html(Commercioo\Helper::formatted_currency( $regular_price - $sale_price )); ?>
                                    (<?php echo esc_html(number_format( ( $regular_price - $sale_price ) / $regular_price * 100 )) ?>%)
                                </span>
                            <?php endif; ?>
                        <?php else: ?>
                            <span class="list-product-price-detail"><?php echo esc_html($product->get_regular_price_display()); ?></span>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="col-md-12 row">
                    <div class="p-0 list-diskon-desc">
                        STOCK: <span class="product-stock-wrapper"><?php echo wp_kses_post($stock); ?></span>
                        <span class="list-product-review ml-1 mr-1">|</span>
                        SKU: <?php echo wp_kses_post($sku); ?>
                    </div>
                </div>

                <?php do_action( 'commercioo_before_add_to_cart_button', $product->get_product_id() ); ?>

                <div class="product-actions-wrapper">
                    <div class="row">
                        <div class="col-sm-3 col-md-3 mt-2" style="padding-right: 0;">
                            <div class="btn-group <?php echo esc_attr($get_stock_status == 'outofstock' ? 'disabled' :'');?>" role="group" aria-label="Basic example" style="height: 39px;">
    							<span class="btn minus-product comm-minus-qty-cart">
    								<i class="feather-16 set-mt-6" data-feather="minus"></i>
    							</span>
    							<input type="text" class="form-control input-qty comm-cart-qty-input" value="1"/>
    							<span class="btn plus-product comm-plus-qty-cart">
    								<i class="feather-16 set-mt-6" data-feather="plus"></i>
    							</span>
    						</div>
    					</div>
    					<div class="col-sm-8 col-md-8 mt-2">
    						<a href="#" class="btn btn-add-to-cart comm-add-to-cart <?php echo esc_attr($get_stock_status == 'outofstock' ? 'disabled' :'');?>" data-id="<?php echo esc_attr($post->ID); ?>">
    							<?php echo esc_html( \Commercioo\Helper::button_label( 'add_to_cart' ) ) ?>
    						</a>
    					</div>
    					<div class="col-sm-1 col-md-1 mt-2">
    						<!-- <span
    								class="btn btn-favorite-product wishlist-product"
    								data-bs-container="body"
    								data-bs-toggle="popover-product"
    								data-bs-trigger="hover"
    								data-bs-placement="top"
    								data-bs-content="Add to Wishlist."
    						>
    							<i class="fa fa-heart-o"></i>
    						</span> -->
                        </div>
                    </div>
                    <?php
                    if(class_exists('Commercioo_2_Step') && $product->is_2_step() && $product->step_1_display() == 'form'){
                        echo do_shortcode( '[comm_2_step_checkout prod_id="'. $post->ID .'"]' );
                    }else{?>
        				<a href="#" class="btn btn-buy-now <?php echo esc_attr(function_exists('comm_public_2_step') && $product->is_2_step() ? 'comm-2-step' : 'comm-checkout');?>" data-id="<?php echo esc_attr($post->ID); ?>">
        					<?php echo esc_html( \Commercioo\Helper::button_label( 'buy_now' ) ) ?>
        				</a>
                    <?php };?>
    				<?php do_action( 'commercioo_after_product_buy_now_button', $post->ID ) ?>
                </div>
            </div>
            <!-- End Right Product -->
        </div>
        <div class="row">
            <div class="col-md-9">
                <ul class="nav nav-pills mb-3" id="pills-tab" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link tab-item-link active" id="pills-description-tab" data-bs-toggle="pill"
                           href="#tabs-description" role="tab" aria-controls="tabs-description"
                           aria-selected="true">DESCRIPTION</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link tab-item-link" id="pills-additional-information-tab" data-bs-toggle="pill"
                           href="#tabs-additional-information" role="tab"
                           aria-controls="tabs-additional-information"
                           aria-selected="false">ADDITIONAL INFORMATION</a>
                    </li>
                </ul>
                <div class="tab-content" id="pills-tabContent">
                    <div class="tab-pane fade show active content-tab-description" id="tabs-description"
                         role="tabpanel"
                         aria-labelledby="pills-description-tab">
                        <?php echo wp_kses_post($post_content); ?>
                    </div>
                    <div class="tab-pane fade content-tab-description" id="tabs-additional-information"
                         role="tabpanel"
                         aria-labelledby="pills-additional-information-tab">
                        <?php echo wp_kses_post($get_additional_description); ?>
                    </div>
                </div>
            </div>
			<div class="col-md-3">
				<?php if ( class_exists( 'Commercioo_Store_Pro' ) ) echo do_shortcode( '[commercioo_related_products]' ) ?>
			</div>
        </div>
</div>
<?php
if (function_exists("comm_public_2_step")) {
    comm_public_2_step($post->ID);
}
?>