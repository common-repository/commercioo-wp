<!-- basic modal -->
<div class="modal fade comm-quick-view-product" id="comm-quick-view-product" tabindex="-1" role="dialog"
     aria-labelledby="comm-quick-view-product" aria-hidden="true">
    <div class="modal-dialog view-dialog">
        <div class="modal-content">
            <div class="modal-body view-modal-body">
            <span type="button" class="close view-close" data-bs-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </span>
                <div class="row comm-modal-quick-product">

                </div>
            </div>
        </div>
    </div>
</div>
        <?php if(get_query_var('search') && get_query_var('post_type')=="comm_product" ):?>
        <h3>Search results: "<?php echo get_query_var('search');?>"</h3>
        <?php endif; ?>
        <div class="row">
            <?php
            $term_id = 0;
            $product_args = [];
            if (is_comm_product_taxonomy()) {
                if (is_tax('comm_product_cat') || is_tax('comm_product_tag')) {
                    $current_term = get_queried_object();
                    $term_id = $current_term->term_id;
                    $product_args['tax_query'] = [
                        [
                            'taxonomy' => 'comm_product_cat',
                            'field' => 'slug',
                            'terms' => $current_term->slug,
                            'include_children' => false
                        ]
                    ];
                }
            }
            ?>
            <?php
            apply_filters("comm_product_category", $term_id);
            ?>
            <div class="col-md-9 comm-product-content-archive">
                <?php echo apply_filters("comm_product_archive", 1, $product_args); ?>
            </div>
        </div>
