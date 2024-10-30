<!-- Start Title -->
<div class="col-md-12 c-col-container">
    <div class="list-category category-title">
        <div class="d-flex justify-content-between">
            <div class="d-flex align-items-center">
                <h2 class="page-title"><?php _e("Category", "commercioo"); ?></h2>
                <span class="desktop-view c-btn-products c-btn-add-category c-add-text"><?php _e("+ Add", "commercioo"); ?></span>
                <span class="mobile-view c-btn-products c-btn-add-category"><i class="fa fa-plus"></i></span>
            </div>
            <div class="float-right c-search-tables">
                <div class="input-group">
                <input type="text" name="comm_table_search" class="comm-table-search" placeholder="Search...">
                <div class="input-group-append">
                    <span class="input-group-text" id="basic-addon2"><i class="feather-16" data-feather="search"></i></span>
                </div>
                </div>
            </div>
        </div>
    </div>
    <div class="c-add-category category-title">
        <div class="d-flex align-items-center">
            <h2 class="page-title"><?php _e("Add Category", "commercioo"); ?></h2>
        </div>
    </div>
    <div class="c-edit-category category-title">
        <div class="d-flex align-items-center">
            <h2 class="page-title"><?php _e("Edit Category", "commercioo"); ?> #<span id="id_category">0</span></h2>
        </div>
    </div>
</div>
<!-- End Title -->

<!-- start list category-->
<?php
$data = [
    'order_by'=>'term_id',
    'post_type'=>'comm_product_cat',
];
?>
<div class="table-responsive c-tbl list-category c-list-category c-general-category c-list-table-data" data-tbl="category" data-table='<?php echo esc_html(json_encode($data));?>'>
    <!--Table-->
    <table class="table c-table-list-category">

        <!--Table head-->
        <thead class="c-table-list-category-head">
        <tr>
			<th class="th-lg" data-orderable="false">
                <div class="table-option">
                    <input type="checkbox" name="select-all">
                    <span class="btn btn-default" type="button" data-bs-toggle="dropdown"><i class="fa fa-angle-down"></i></span>
                    <ul class="dropdown-menu dropdown-menu-right">
                        <li><a href="#" class="c-option-edit delete" data-type="bulk" data-action="delete">Delete selected</a></li>
                    </ul>
                </div>
            </th>
            <th class="th-lg">Name</th>
            <th class="th-lg">Description</th>
            <th class="th-lg">Slug</th>
            <th class="th-lg">Parent</th>
            <th class="th-lg">Count</th>
            <!-- <th class="th-lg">Actions</th> -->
        </tr>
        </thead>
        <!--Table head-->

        <!--Table body-->
        <tbody>
        <tr>
            <td>Shoes for Men</td>
            <td>Shoes for Men</td>
            <td>shoes-for-men</td>
            <td>1</td>
            <td>
                <a href="#"
                   class="btn btn-sm c-btn-wrap-category mb-2"
                   data-bs-container="body"
                   data-bs-toggle="popover"
                   data-bs-placement="top"
                   data-bs-trigger="hover"
                   data-bs-content="Edit">
                    <i class="fa fa-edit"></i>
                </a>
                <a
                        href="#"
                        class="btn btn-sm c-btn-wrap-category mb-2"
                        data-bs-container="body"
                        data-bs-toggle="popover"
                        data-bs-placement="top"
                        data-bs-trigger="hover"
                        data-bs-content="Delete">
                    <i class="fa fa-trash"></i>
                </a>
            </td>
        </tr>
        <tr>
            <td><?php echo esc_attr("Shoes"); ?></td>
            <td>Shoes</td>
            <td>shoes</td>
            <td>0</td>
            <td>
                <a href="#"
                   class="btn btn-sm c-btn-wrap-category mb-2"
                   data-bs-container="body"
                   data-bs-toggle="popover"
                   data-bs-placement="top"
                   data-bs-trigger="hover"
                   data-bs-content="Edit">
                    <i class="fa fa-edit"></i>
                </a>
                <a
                        href="#"
                        class="btn btn-sm c-btn-wrap-category mb-2"
                        data-bs-container="body"
                        data-bs-toggle="popover"
                        data-bs-placement="top"
                        data-bs-trigger="hover"
                        data-bs-content="Delete">
                    <i class="fa fa-trash"></i>
                </a>
            </td>
        </tr>
        </tbody>
        <!--Table body-->
        <!--Table head-->
        <tfoot class="c-table-list-category-head">
        <tr>
			<th class="th-lg" data-orderable="false">
                <div class="table-option">
                    <input type="checkbox" name="select-all">
                    <span class="btn btn-default" type="button" data-bs-toggle="dropdown"><i class="fa fa-angle-down"></i></span>
                    <ul class="dropdown-menu dropdown-menu-right">
                        <li><a href="#" class="c-option-edit delete" data-type="bulk" data-action="delete">Delete selected</a></li>
                    </ul>
                </div>
            </th>
            <th class="th-lg">Name</th>
            <th class="th-lg">Description</th>
            <th class="th-lg">Slug</th>
            <th class="th-lg">Parent</th>
            <th class="th-lg">Count</th>
            <!-- <th class="th-lg">Actions</th> -->
        </tr>
        </tfoot>
        <!--Table head-->
    </table>
    <!--Table-->
</div>
<!-- end list list category-->
<!-- start add category -->
<div class="c-add-category c-general-category c-col-container">
    <form class="needs-validation" novalidate data-cond="category">
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label>Category Name *</label>
                    <input type="text" class="form-control c-setting-form-control cs-name c-input-form c-set-cursor-pointer" name="name"
                           placeholder="Category Name"
                           required>
                    <div class="invalid-feedback">
                        Please enter category name
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label>Slug</label>
                    <input type="text" class="form-control c-setting-form-control cs-name c-input-form c-set-cursor-pointer" name="slug"
                           placeholder="Slug">
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-4">
                <div class="form-group">
                    <label>Parent Category</label>
                    <?php

                    /** The taxonomy we want to parse */
                    $taxonomy = "comm_product_cat";
                    $dropdown_args = array(
                        'hide_empty'       => 0,
                        'hide_if_empty'    => false,
                        'taxonomy'         => $taxonomy,
                        'name'             => 'parent',
                        'orderby'          => 'name',
                        'hierarchical'     => true,
                        'show_option_none' => __( 'None' ),
                    );
                    $dropdown_args = apply_filters( 'taxonomy_parent_dropdown_args', $dropdown_args, $taxonomy, 'new' );
                    wp_dropdown_categories( $dropdown_args );
                    ?>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label><?php _e("Description", "commercioo"); ?></label>
                    <textarea class="form-control c-setting-form-control c-input-form c-set-cursor-pointer" name="description" rows="8"
                              placeholder="<?php _e("Description", "commercioo"); ?>"></textarea>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-4">
                <button type="submit" class="btn btn-primary c-save-category">Save</button>
                <button type="button" class="btn btn-primary c-back">Cancel</button>
            </div>
        </div>
    </form>
</div>
<!-- end add category cs -->
