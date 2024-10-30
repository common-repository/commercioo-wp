<?php
wp_enqueue_script('commercioo-owl.carousel.min', COMMERCIOO_URL . 'public/js/vendor/owl.carousel.min.js');
wp_enqueue_script('commercioo-feather.min', COMMERCIOO_URL . 'public/js/vendor/feather.min.js');
wp_enqueue_script('commercioo-gallery', COMMERCIOO_URL . 'public/js/product/gallery.js');
wp_enqueue_script('commercioo-single-product', COMMERCIOO_URL . 'public/js/product/single-product.js');
wp_enqueue_script('commercioo-product-archive', COMMERCIOO_URL . 'public/js/product/product-archive.js');
wp_enqueue_script('commercioo-cart-page', COMMERCIOO_URL . 'public/js/cart/cart-page.js',array(),COMMERCIOO_VERSION);

wp_enqueue_style('commercioo-owl.carousel.min', COMMERCIOO_URL . 'public/css/vendor/owl.carousel.min.css', array(), COMMERCIOO_VERSION,'all');
wp_enqueue_style('commercioo-gallery', COMMERCIOO_URL . 'public/css/gallery.css', array(), COMMERCIOO_VERSION,'all');
wp_enqueue_style('commercioo-product', COMMERCIOO_URL . 'public/css/commercio-product.css', array(), COMMERCIOO_VERSION, 'all');

global $comm_options;
$get_best_seller = apply_filters("comm_calculate_bestSeller", 1);
$badged_best_seller = '';
$term_name = '';
$posts = new WP_Query($args);
if ($posts->have_posts()):
    while ($posts->have_posts()):
        $posts->the_post();
        $post_id = get_the_ID();
        $product = comm_get_product( $post_id );
        $get_term = get_the_terms($post_id, 'comm_product_cat');
        if ($get_best_seller) {
            if ($post_id == $get_best_seller['0']->product_id) {
                $badged_best_seller = '<span class="badge badge-best-seller">#1 BEST SELLER</span>';
            }
        }
        
        $is_commercioo_pro_installed = class_exists('Commercioo_Pro');
        $manage_stock = get_post_meta($post_id, "_manage_stock", true);
        $get_stock = get_post_meta($post_id, "_stock", true);
        $get_stock_status = get_post_meta($post_id, "_stock_status", true);
        $sku = get_post_meta($post_id, "_sku", true);
        $sku_html = '<span class="set-font-bold">%s</span>';
        if ($sku) {
            $sku = sprintf($sku_html, $sku);
        } else {
            $sku = sprintf($sku_html, "-");;
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
        $get_product_featured = get_post_meta($post_id, "_product_featured", true);
        $feature_url = '';
        $is_featured_prod_text = '';
        if ($get_product_featured) {
            $url = wp_get_attachment_image_src($get_product_featured);
            $feature_url = $url[0];
            $is_featured_prod_text = '<div class="set-line-height"><span class="box-first">FEATURED</span></div>';
        } else {
            $feature_url = COMMERCIOO_URL . 'img/commercioo-no-img.png';
        }
        $get_product_gallery = get_post_meta($post_id, "_product_gallery", true);

        if ($get_product_gallery) {
            $get_product_gallery = explode(",", $get_product_gallery);
        } else {
            $get_product_gallery = false;
        }
        $stock = sprintf($stock_view, $stock);
        $is_featured_prod_img = $is_featured_prod_text;
        $post_content = $post->post_content;
        $get_additional_description = get_post_meta($post_id, "_additional_description", true);
        if (!$get_additional_description) {
            $get_additional_description = "";
        }
        ?>
        <section class="section-content">
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
                                    $url_gallery = wp_get_attachment_image_src($get_product_gallery_v,"large");
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
                        <div>
                            <span class="list-product-price-detail"><?php echo esc_html(Commercioo\Helper::formatted_currency( $product->get_price())); ?></span>
                            <?php if ( $product->is_on_sale() ) : ?>
                                <?php
                                    $regular_price = $product->get_regular_price();
                                    $sale_price    = $product->get_sale_price();
                                ?>
                                <span class="list-product-price-special text-line-product"><?php echo esc_html(Commercioo\Helper::formatted_currency( $product->get_regular_price())); ?></span>
                                <span class="list-product-review ml-1 mr-1">|</span>
                                <span class="list-product-price-special">
                                    <?php esc_html_e( 'SAVE', 'commercioo' ); ?>
                                    <?php echo esc_html(Commercioo\Helper::formatted_currency( $regular_price - $sale_price )); ?>
                                    (<?php echo esc_html(number_format( ( $regular_price - $sale_price ) / $regular_price * 100 )); ?>%)
                                </span> 
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="col-md-12 row">
                        <div class="p-0 list-diskon-desc">
                            STOCK: <?php echo wp_kses_post($stock); ?>
                            <span class="list-product-review ml-1 mr-1">|</span>
                            SKU: <?php echo wp_kses_post($sku); ?>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-3 col-md-3 mt-2" style="padding-right: 0;">
                            <div class="btn-group" role="group" aria-label="Basic example" style="height: 39px;">
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
                            <a href="#" class="btn btn-add-to-cart comm-add-to-cart" data-id="<?php echo esc_attr($post_id); ?>">ADD
                                TO
                                CART</a>
                        </div>
                        <div class="col-sm-1 col-md-1 mt-2">
							<span
									class="btn btn-favorite-product wishlist-product"
									data-bs-container="body"
									data-bs-toggle="popover-product"
									data-bs-trigger="hover"
									data-bs-placement="top"
									data-bs-content="Add to Wishlist."
							>
								<i class="fa fa-heart-o"></i>
							</span>
                        </div>
                    </div>
					<a href="#" class="btn btn-buy-now comm-checkout" data-id="<?php echo esc_attr($post_id); ?>">
						<?php echo esc_html( \Commercioo\Helper::button_label( 'buy_now' ) ) ?>
					</a>
                </div>
                <!-- End Right Product -->
                <div class="row">
                    <div class="col-md-12">
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
                                <?php echo esc_html($post_content); ?>
                            </div>
                            <div class="tab-pane fade content-tab-description" id="tabs-additional-information"
                                 role="tabpanel"
                                 aria-labelledby="pills-additional-information-tab">
                                <?php echo esc_html($get_additional_description); ?>
                            </div>
                        </div>
                    </div>
                </div>
        </section>
    <?php
    endwhile;
endif;