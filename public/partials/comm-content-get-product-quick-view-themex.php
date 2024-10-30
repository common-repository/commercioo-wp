<?php
global $comm_options;
$get_best_seller = apply_filters("comm_calculate_bestSeller", 1);
$badged_best_seller = '';
if ($get_best_seller) {
    if ($product_id == $get_best_seller['0']->product_id) {
        $badged_best_seller = '<span class="badge badge-best-seller">#1 BEST SELLER</span>';
    }
}

$product = comm_get_product( $product_id );

$is_commercioo_pro_installed = class_exists('Commercioo_Pro');
$manage_stock = get_post_meta($product_id, "_manage_stock", true);
$get_stock = get_post_meta($product_id, "_stock", true);
$get_stock_status = get_post_meta($product_id, "_stock_status", true);
$sku = get_post_meta($product_id, "_sku", true);
$sku_html = '<span class="product-sku-wrapper set-font-bold">%s</span>';
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
$get_product_featured = get_post_meta($product_id, "_product_featured", true);
$is_featured_prod = '<div class="set-line-height"><span class="box-first">%s</span></div>';
$is_featured_prod_text = '';
$feature_url = '';
if ($get_product_featured) {
    $url = wp_get_attachment_image_src($get_product_featured,"large");
    $feature_url = $url[0];
    $is_featured_prod_text = '<div class="set-line-height"><span class="box-first">FEATURED</span></div>';
}else {
    $feature_url = COMMERCIOO_URL . 'img/commercioo-no-img.png';
}


$get_product_gallery = get_post_meta($product_id, "_product_gallery", true);

if ($get_product_gallery) {
    $get_product_gallery = explode(",", $get_product_gallery);
} else {
    $get_product_gallery = false;
}
$stock = sprintf($stock_view, $stock);
$is_featured_prod_img = $is_featured_prod_text;
$title = get_the_title($product_id);
$thumb_count = 1;
?>
<div class="row px-4 justify-content-center">
    <div class="col-md-7 d-flex">
        <div class="themex-thumb-wrapper">
            <img src="<?php echo esc_url($feature_url); ?>" class="image-thumb-product mb-2" data-carousel-id="0">
            <?php if ($get_product_gallery): ?>
                <?php foreach ($get_product_gallery as $get_product_gallery_v): ?>
                    <?php
                    $url_gallery = wp_get_attachment_image_src($get_product_gallery_v,"thumbnail");
                    $gallery_url = $url_gallery[0];
                    ?>
                    <img src="<?php echo esc_url($gallery_url); ?>" class="image-thumb-product mb-2" data-carousel-id="<?php echo esc_html( $thumb_count ) ?>">
                <?php 
                $thumb_count++;
                endforeach; 
            endif; ?>
        </div>
        <div class="themex-carousel-wrapper">
            <div class="themex-carousel owl-carousel owl-theme">
                <div class="themex-carousel-item pe-2">
                    <img src="<?php echo esc_url($feature_url); ?>" class="image-gallery-product">
                </div>
                <?php if ($get_product_gallery): ?>
                    <?php foreach ($get_product_gallery as $get_product_gallery_v): ?>
                        <?php
                        $url_gallery = wp_get_attachment_image_src($get_product_gallery_v,"large");
                        $gallery_url = $url_gallery[0];
                        ?>
                        <div class="themex-carousel-item pe-2">
                            <img src="<?php echo esc_url($gallery_url); ?>" class="image-gallery-product">
                        </div>
                    <?php endforeach; 
                endif; ?>
            </div>
        </div>
    </div>
    <div class="col-md-5">
        <h2 class="product-name"><?php echo esc_attr($title); ?></h2>
        <div class="col-md-12 row">
            <div>
                <?php echo wp_kses_post($badged_best_seller); ?>
                <?php
                if ( isset( $get_term ) && $get_term ) {
                    $term_name_v = [];
                    $terms_names = '';
                    $term_name = '<span class="list-product-review">in %s</span>';
                    foreach ($get_term as $k => $get_term_v) {
                        $term_id = $get_term_v->term_id;
                        $term_link = get_term_link($term_id);
                        $term_name_v [$k] = '<a href="' . $term_link . '" class="link-categories">'
                            . $get_term_v->name
                            . '</a>';
                        $terms_names = implode(",", $term_name_v);
                    }
                    echo wp_kses_data(sprintf($term_name, $terms_names));
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
                            (<?php echo esc_html(number_format( ( $regular_price - $sale_price ) / $regular_price * 100 )); ?>%)
                        </span>
                    <?php endif; ?>
                <?php else: ?>
                    <span class="list-product-price-detail"><?php echo esc_html($product->get_regular_price_display()); ?></span>
                <?php endif; ?>
            </div>
        </div>
        <div class="col-md-12 row p-0">
            <div class="list-diskon-desc">
                STOCK: <span class="product-stock-wrapper"><?php echo wp_kses_post($stock); ?></span>
                <span class="list-product-review ml-1 mr-1">|</span>
                SKU: <?php echo wp_kses_post($sku); ?>
            </div>
        </div>

        <?php do_action( 'commercioo_before_add_to_cart_button', $product_id ); ?>

        <div class="col-12 row product-actions-wrapper p-0 m-0">
            <div class="row col-12 m-0 mb-3 p-0">
                <div class="col-md-5 mt-2 p-0">
                    <div class="btn-group qty-btn-group <?php echo esc_attr($get_stock_status == 'outofstock' ? 'disabled' :'');?>" role="group" aria-label="Basic example">
                        <span class="btn minus-product comm-minus-qty-cart">
                            <i class="fas fa-minus"></i>
                        </span>
                        <input type="text" class="form-control input-qty comm-cart-qty-input" value="1"/>
                        <span class="btn plus-product comm-plus-qty-cart">
                            <i class="fas fa-plus"></i>
                        </span>
                    </div>
                </div>
                <div class="d-flex col-md-7 mt-2 p-0">
                    <a href="#" class="d-inline btn btn-add-to-cart comm-add-to-cart <?php echo esc_attr($get_stock_status == 'outofstock' ? 'disabled' :'');?>" data-id="<?php echo esc_attr($product_id); ?>">
                        <?php echo esc_html( \Commercioo\Helper::button_label( 'add_to_cart' ) ) ?>
                    </a>
                    <span class="btn btn-favorite-product wishlist-product quick-view" data-bs-container="body" data-bs-toggle="popover-product" data-bs-trigger="hover" data-bs-placement="top" data-bs-content="Add to Wishlist." >
                        <i class="far fa-heart"></i>
                    </span>
                </div>
            </div>
            <a href="#" class="btn btn-buy-now comm-checkout <?php echo esc_attr($get_stock_status == 'outofstock' ? 'disabled' :'');?>" data-id="<?php echo esc_attr($product_id); ?>">
                <?php echo esc_html( \Commercioo\Helper::button_label( 'buy_now' ) ) ?>
            </a>
            <?php do_action( 'commercioo_after_product_buy_now_button', $product_id ) ?>
        </div>
    </div>
</div>