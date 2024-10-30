<!-- Start Title -->
<div class="col-md-12 c-col-container">
    <div class="list-tag tag-title">
        <div class="d-flex justify-content-between">
            <div class="d-flex align-items-center">
                <h2 class="page-title"><?php _e("Tags", "Commercioo_title"); ?></h2>
                <span class="desktop-view c-btn-products c-btn-add-tag c-add-text"><?php _e("+ Add", "commercioo"); ?></span>
                <span class="mobile-view c-btn-products c-btn-add-tag"><i class="fa fa-plus"></i></span>
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
    <div class="c-add-tag tag-title">
        <div class="d-flex align-items-center">
            <h2 class="page-title"><?php _e("Add Tag", "commercioo"); ?></h2>
        </div>
    </div>
    <div class="c-edit-tag tag-title">
        <div class="d-flex align-items-center">
            <h2 class="page-title"><?php _e("Edit Tag", "commercioo"); ?> #<span id="id_tag">0</span></h2>
        </div>
    </div>
</div>
<!-- End Title -->

<!-- start list tag-->
<?php
$data = [
    'order_by'=>'term_id',
    'post_type'=>'comm_product_tag',
];
?>
<div class="table-responsive c-tbl list-tag c-list-tag c-general-tag c-list-table-data" data-tbl="tags" data-table='<?php echo esc_html(json_encode($data));?>'>
    <!--Table-->
    <table class="table c-table-list-tag">

        <!--Table head-->
        <thead class="c-table-list-tag-head">
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
            <th class="th-lg">Count</th>
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
                   class="btn btn-sm c-btn-wrap-tag mb-2"
                   data-bs-container="body"
                   data-bs-toggle="popover"
                   data-bs-placement="top"
                   data-bs-trigger="hover"
                   data-bs-content="Edit">
                    <i class="fa fa-edit"></i>
                </a>
                <a
                    href="#"
                    class="btn btn-sm c-btn-wrap-tag mb-2"
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
            <td><?php echo esc_attr("Shoes");?></td>
            <td>Shoes</td>
            <td>shoes</td>
            <td>0</td>
            <td>
                <a href="#"
                   class="btn btn-sm c-btn-wrap-tag mb-2"
                   data-bs-container="body"
                   data-bs-toggle="popover"
                   data-bs-placement="top"
                   data-bs-trigger="hover"
                   data-bs-content="Edit">
                    <i class="fa fa-edit"></i>
                </a>
                <a
                    href="#"
                    class="btn btn-sm c-btn-wrap-tag mb-2"
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
        <!--Table footer-->
        <tfoot class="c-table-list-tag-head">
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
                <th class="th-lg">Count</th>
            </tr>
        </tfoot>
        <!--Table footer-->
    </table>
    <!--Table-->
</div>
<!-- end list list tag-->
<!-- start add tags -->
<div class="c-add-tag c-general-tag c-col-container">
    <form class="needs-validation" novalidate data-cond="tags">
    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label>Tag Name *</label>
                <input type="text" class="form-control c-setting-form-control cs-name c-input-form c-set-cursor-pointer" placeholder="Enter Tag Name"
                       name="name"  required>
                <div class="invalid-feedback">
                    Please enter tag name
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
            <button type="submit" class="btn btn-primary c-save-tag">Save</button>
            <button type="button" class="btn btn-primary c-back">Cancel</button>
        </div>
    </div>
    </form>
</div>
<!-- end add tags -->
