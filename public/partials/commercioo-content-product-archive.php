<?php
global $post;
?>
    <div class="row">
        <?php
        global $comm_options;

        if (get_query_var('paged')) {
            $paged = get_query_var('paged');
        } elseif (get_query_var('page')) { // 'page' is used instead of 'paged' on Static Front Page
            $paged = get_query_var('page');
        } else {
            $paged = 1;
        }

        $posts_per_page = isset( $comm_options['product_archive_posts_per_page'] ) ? absint( $comm_options['product_archive_posts_per_page'] ) : '9';

        $product_args = array_merge([
            'post_type' => 'comm_product',
            'posts_per_page' => $posts_per_page,
            'paged' => $paged,
            'post_status' => 'publish',
            'order' => 'DESC', // 'ASC'
            'orderby' => 'date' // modified | title | name | ID | rand
        ], $params);

        if(get_query_var('search') && get_query_var('post_type')=="comm_product" ){
            $product_args['s']=get_query_var('search');
        }

        $get_product = new WP_Query($product_args);
        
		if ($get_product->have_posts()) {
            while ($get_product->have_posts()) {
                $get_product->the_post();
                $product = comm_get_product( get_the_ID() );
                $title_permalink = get_permalink(get_the_ID());
                $product_featured = get_post_meta(get_the_ID(), "_product_featured", true);
                if ($product_featured) {
                    $url = wp_get_attachment_image_src($product_featured, 'full', true);
                    $thumb_url = $url[0];
                } else {
                    $thumb_url = COMMERCIOO_URL . 'img/commercioo-no-img.png';
                }
                $get_term = get_the_terms(get_the_ID(), 'comm_product_cat');
                $get_stock_status = get_post_meta( get_the_ID() , "_stock_status", true);
                ?>

                <div class="col-md-4 col-sm-4 mb-5">
                    <div class="card list-product-card">
                        <div class="list-product-image-wrap">
                            <div class="list-product-icon">
                            <span class="btn icon-product-image fa fa-cart-plus list-icon-cart comm-add-to-cart-product-archive  <?php echo $get_stock_status == 'outofstock' ? 'disabled' :'';?>"
                                  data-bs-container="body"
                                  data-bs-toggle="popover-product" data-bs-placement="top" data-bs-trigger="hover"
                                  data-id="<?php echo esc_attr($post->ID); ?>" data-qty="1"
                                  data-bs-content="Add to cart" data-original-title="" title="">
                            </span>
                                <!-- <span class="btn icon-product-image wishlist-product fa fa-heart-o list-icon-heart"
                                      data-bs-container="body" data-bs-toggle="popover-product" data-bs-placement="top"
                                      data-bs-trigger="hover" data-bs-content="Add to wishlist" data-original-title=""
                                      title=""> -->
                            </span>
                            </div>
                            <div class="list-product-quick" data-bs-toggle="modal" data-bs-target="
                                    .comm-quick-view-product" data-id="<?php echo get_the_ID(); ?>">
                                <i class="fa fa-eye"></i> QUICK VIEW
                            </div>
                            <a href="<?php echo esc_url($title_permalink); ?>" class="set-color-general">
                            <img class="card-img-top list-product-image"
                                 src="<?php echo esc_url($thumb_url); ?>">
                            </a>
                        </div>
                    </div>
                    <div class="card-body list-product-card-body text-center">
                        <?php
                        if ($get_term) {
                            $terms_names = '';
                            $term_name_v = [];
                            $term_name = '<span class="list-title-product-category">%s</span>';
                            foreach ($get_term as $k => $get_term_v) {
                                if(isset($get_term_v->term_id)) {
                                    $term_id = $get_term_v->term_id;
                                    $term_link = get_term_link($term_id);
                                    $term_name_v [$k] = '<a href="' . $term_link . '" class="link-categories">'
                                        . $get_term_v->name
                                        . '</a>';
                                    $terms_names = implode(", ", $term_name_v);
                                }
                            }
                            echo wp_kses_data(sprintf($term_name, $terms_names));
                        }
                        ?>
                        <h5 class="list-title-product-name">
                            <a href="<?php echo esc_url($title_permalink); ?>" class="set-color-general"><?php echo get_the_title(); ?></a>
                        </h5>
                        <?php
                        if ( $product->is_on_sale() ) {
                            echo wp_kses_post($product->get_regular_price_display( '<span class="list-title-product-price-normal">', '</span> ' ));
                            echo wp_kses_post($product->get_sale_price_display( '<span class="list-title-product-price-special">', '</span> ' ));
                        } else {
                            echo wp_kses_post($product->get_regular_price_display( '<span class="list-title-product-price-special">', '</span> ' ));
                        }
                        ?>
                    </div>
                </div>
            <?php } ?>
            <?php wp_reset_postdata(); ?>
        <?php } else {
            $part = 'part/content-none.php';
            comm_get_template_part('part/content','none');
            ?>
        <?php } ?>
    </div>
<?php echo comm_bootstrap_pagination($get_product); ?>