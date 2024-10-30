<?php

namespace commercioo\admin;
class Comm_Product
{
    // instance
    private static $instance;
    private $product_id, $stock_status;
    private $api;

    // getInstance
    public static function get_instance()
    {
        if (!isset(self::$instance)) {
            self::$instance = new Comm_Product();
        }

        return self::$instance;
    }

    // __construct
    public function __construct()
    {
        $this->api = \Comm_API::get_instance();
    }

    public function comm_stock($stock)
    {
        $stock_status = $this->get_stock_status();
        if (is_comm_pro()) {
            if (!empty($stock)) {
                return $stock_status = $stock_status . $stock;
            } else {
                return $stock_status;
            }
        } else {
            return $stock_status;
        }
    }

    public function comm_sales_price($sales_price)
    {
        if (is_comm_pro()) {
            if (!empty($sales_price)) {
                return $sales_price;
            } else {
                return "0";
            }
        } else {
            return "0";
        }
    }

    public function register_post_type_and_taxonomies()
    {
        // register post type
        $this->api->comm_register_post_type(
            'comm_product', array('comm_product_cat', 'comm_product_tag'),
            true, array('editor', 'thumbnail')
        );

        // register taxonomies
        $this->api->comm_register_taxonomy('comm_product_cat', array('comm_product'));
        $this->api->comm_register_taxonomy('comm_product_tag', array('comm_product'), false);
    }

    public function register_rest_fields()
    {
        /**
         * Field type reference:
         * https://core.trac.wordpress.org/browser/tags/5.4/src/wp-includes/rest-api.php#L116
         */
        $fields = array(
            'product_gallery' => 'string',
            'product_featured' => 'string',
            'is_featured' => 'boolean',
            'regular_price' => 'number',
            'sku' => 'string',
            'stock_status' => 'string',
            'additional_description' => 'string',
            'included_items' => 'array',
            'weight' => 'number',
            'overwrite_thank_you_redirect' => 'boolean',
            'thank_you_redirect' => 'string',
            'free_shipping' => 'boolean',
        );

        // iterate to register the fields
        foreach ($fields as $field_name => $type) {
            register_rest_field('comm_product', $field_name, array(
                'get_callback' => array($this, 'get_rest_field'),
                'update_callback' => array($this, 'update_rest_field'),
                'schema' => array('type' => $type),
            ));
        }
        self::commercioo_product_cat();
        self::commercioo_custom_rest_api();
    }

    public function rest_pre_insert($prepared_post, $request)
    {
        $sku = $request->get_param('sku');
        $id = $request->get_param('id');
        $slug = $request->get_param('slug');
        if (empty($slug)) {
            $slug = sanitize_title_with_dashes($request->get_param('title'));
        }
        $request->set_param('slug', $slug);

        // make sure the SKU is unique
        if ('' !== $sku && $sku != null) {
            $args = array(
                'post_type' => 'comm_product',
                'post_status' => 'any',
                'fields' => 'ids',
                'meta_query' => array(
                    array(
                        'key' => '_sku',
                        'value' => $sku,
                        'compare' => '=',
                    ),
                ),
            );

            // exclude the current id
            if ($id) {
                $args['post__not_in'] = array($id);
            }

            $get_data = get_posts($args);

            if (null != $get_data) {
                return new \WP_Error('error_product', sprintf(__('A SKU %s already exists.'), "&#8220;" . $sku . "&#8221;"), array('status' => 404));
            }
        }

        /**
         * Insert product validation
         * Mainly now used by SaaS to limit the product number
         */
        if (!$id && !apply_filters('commercioo_validate_product_creation', true)) {
            $message = apply_filters('commercioo_product_creation_prohibited_message', __('Sorry, you are prohibited to create more product.', 'commercioo'));
            return new \WP_Error('error_product', $message, array('status' => 404));
        }

        return $prepared_post;
    }

    public function pre_insert_term($term, $taxonomy)
    {
        if ($taxonomy == "comm_product_tag" || $taxonomy == "comm_product_cat") {
            $tag_name = 'category';
            if ($taxonomy == "comm_product_tag") {
                $tag_name = "tags";
            } else if ($taxonomy == "comm_product_cat") {
                $tag_name = "category";
            }
            $term_cek = term_exists($term, $taxonomy);
            if ($term_cek !== 0 && $term_cek !== null) {
                return new \WP_Error($tag_name . '_term_exists', "A term " . $term . " with the name provided already exists with this 
                parent.", array('status' =>
                    404));
            }
            return $term;
        }

        return $term;
    }

    public function commercioo_product_cat()
    {
        $fields = array(
            'featured_media' => 'integer',
        );
        // iterate to register the fields
        foreach ($fields as $field_name => $type) {
            register_rest_field('comm_product_cat', $field_name, array(
                'get_callback' => array($this, 'get_rest_field_cat'),
                'update_callback' => array($this, 'update_rest_field_cat'),
                'schema' => array('type' => $type),
            ));
        }
    }

    public function get_rest_field_cat($array_data, $field_name)
    {
        $term_id = $array_data['id'];

        // get by field_name
        switch ($field_name) {
            case 'featured_media':
                $field = get_term_meta($term_id, $field_name, true);
                $field_value = intval($field);
                break;

            default:
                $field_value = null;
                break;
        }

        return $field_value;
    }

    public function update_rest_field_cat($field_value, $object_data, $field_name)
    {
        $term_id = $object_data->term_id;

        // get by field_name
        switch ($field_name) {
            case 'featured_media':
                $field = intval($field_value);
                update_term_meta($term_id, $field_name, $field);
                break;

            default:
                // silent is golden
                break;
        }
    }

    public function get_rest_field($array_data, $field_name)
    {
        $post_id = $array_data['id'];
        $private_field_name = sprintf("_%s", $field_name);

        // get by field_name
        switch ($field_name) {
            case 'product_gallery':
                $field = get_post_meta($post_id, $private_field_name, true);
                $field_value = $field;
                break;
            case 'product_featured':
                $field = get_post_meta($post_id, $private_field_name, true);
                $field_value = floatval($field);
                break;
            case 'is_featured':
                $field = get_post_meta($post_id, $private_field_name, true);
                $field_value = intval($field);
                break;
            case 'free_shipping':
                $field = get_post_meta($post_id, $private_field_name, true);
                $field_value = boolval($field);
                break;
            case 'regular_price':
                $field = get_post_meta($post_id, $private_field_name, true);
                $field_value = floatval($field);
                break;
            case 'sku':
                $field = get_post_meta($post_id, $private_field_name, true);
                $field_value = $field;
                break;
            case 'stock_status':
                $field = get_post_meta($post_id, $private_field_name, true);
                $field_value = $field;
                if(empty($field_value)){
                    $field_value = "instock";
                }
                break;
            case 'additional_description':
                $field = get_post_meta($post_id, $private_field_name, true);
                $field_value = $field;
                break;
            case 'included_items':
                $field = get_post_meta($post_id, $private_field_name, true);
                $field_value = $field;
                break;
            case 'weight':
                $field = get_post_meta($post_id, $private_field_name, true);
                $field_value = floatval($field);
                break;
            case 'overwrite_thank_you_redirect':
                $field = get_post_meta($post_id, $private_field_name, true);
                $field_value = intval($field);
                break;
            case 'thank_you_redirect':
                $field = get_post_meta($post_id, $private_field_name, true);
                $field_value = $field;
                break;
            default:
                $field_value = null;
                break;
        }

        return $field_value;
    }

    public function update_rest_field($field_value, $object_data, $field_name)
    {
        $post_id = $object_data->ID;
        $private_field_name = sprintf("_%s", $field_name);

        // get by field_name
        switch ($field_name) {
            case 'product_gallery':
                $value = sanitize_text_field($field_value);
                update_post_meta($post_id, $private_field_name, $value);
                break;

            case 'product_featured':
                $value = floatval($field_value);
                update_post_meta($post_id, $private_field_name, $value);
                break;

            case 'is_featured':
                $value = intval($field_value);
                update_post_meta($post_id, $private_field_name, $value);
                break;

            case 'regular_price':
                $value = floatval($field_value);
                update_post_meta($post_id, $private_field_name, $value);
                break;

            case 'free_shipping':
                $value = boolval($field_value);
                update_post_meta($post_id, $private_field_name, $value);
                break;

            case 'sku':
                $value = sanitize_text_field($field_value);
                update_post_meta($post_id, $private_field_name, $value);
                break;

            case 'stock_status':
                $value = sanitize_text_field($field_value);
                update_post_meta($post_id, $private_field_name, $value);
                break;

            case 'additional_description':
                $value = $field_value;
                update_post_meta($post_id, $private_field_name, $value);
                break;

            case 'included_items':
                $value = array_map('esc_html', $field_value);
                update_post_meta($post_id, $private_field_name, $value);
                break;

            case 'weight':
                $value = floatval($field_value);
                update_post_meta($post_id, $private_field_name, $value);
                break;

            case 'overwrite_thank_you_redirect':
                $value = intval($field_value);
                update_post_meta($post_id, $private_field_name, $value);
                break;

            case 'thank_you_redirect':
                $value = $field_value;
                update_post_meta($post_id, $private_field_name, $value);
                break;

            default:
                // silent is golden
                break;
        }
    }

    private function commercioo_custom_rest_api()
    {
        register_rest_route('commercioo/v1', '/comm_getParent/', array(
            'methods' => \WP_REST_Server::READABLE,
            'callback' => array($this, 'comm_getParent'),
            'args' => array(),
            'permission_callback' => function () {
                return current_user_can('publish_posts');
            }
        ));

// Product Trash
        register_rest_route('commercioo/v1', '/comm_product_trash/(?P<id>[a-zA-Z0-9-]+)', array(
            'methods' => \WP_REST_Server::DELETABLE,
            'callback' => array($this, 'comm_product_trash'),
            'args' => array(
                'id' => array(
                    'validate_callback' => function ($param, $request, $key) {
                        return is_numeric($param);
                    }
                ),
                'tbl' => ['required' => false],
                'status' => ['required' => false],
            ),
            'permission_callback' => function () {
                return current_user_can('publish_posts');
            }
        ));
        // Product Clone
        register_rest_route('commercioo/v1', '/comm_product_clone/(?P<id>[a-zA-Z0-9-]+)', array(
            'methods' => \WP_REST_Server::CREATABLE,
            'callback' => array($this, 'comm_product_clone'),
            'args' => array(
                'id' => array(
                    'validate_callback' => function ($param, $request, $key) {
                        return is_numeric($param);
                    }
                ),
            ),
            'permission_callback' => function () {
                return current_user_can('publish_posts');
            }
        ));
        // Product Restore
        register_rest_route('commercioo/v1', '/comm_product_restore/(?P<id>[a-zA-Z0-9-]+)', array(
            'methods' => \WP_REST_Server::CREATABLE,
            'callback' => array($this, 'comm_product_restore'),
            'args' => array(
                'id' => array(
                    'validate_callback' => function ($param, $request, $key) {
                        return is_numeric($param);
                    }
                ),
            ),
            'permission_callback' => function () {
                return current_user_can('publish_posts');
            }
        ));

        // Empty ALL Product
        register_rest_route('commercioo/v1', '/comm_product_empty_trash/', array(
            'methods' => \WP_REST_Server::CREATABLE,
            'callback' => array($this, 'comm_product_empty_trash'),
            'permission_callback' => function () {
                return current_user_can('publish_posts');
            }
        ));

        // Bulk Action Category
        register_rest_route('commercioo/v1', '/comm_product_term_action', array(
            'methods' => \WP_REST_Server::EDITABLE,
            'callback' => array($this, 'comm_product_term_action'),
            'permission_callback' => function () {
                return current_user_can('publish_posts');
            }
        ));
    }

    public function comm_product_empty_trash($request)
    {
        $params = $request->get_params();
        $data = get_posts(array(
            'post_type' => "comm_product",
            'numberposts' => -1,
            'post_status' => "trash",
            'orderby' => 'ID',
            'order' => 'DESC',
        ));
        if ($data) {
            foreach ($data as $product) {
                // Delete all products.
                wp_delete_post($product->ID, true); // Set to False if you want to send them to Trash.
            }
            $response = ["message" => "success"];
        } else {
            $response = ["message" => "nodata"];
        }

        return rest_ensure_response($response);
    }

    public function comm_product_restore($request)
    {
        $params = $request->get_params();
        $post_id = sanitize_text_field(isset($params['id']) ? absint($params['id']) : null);
        if (isset($post_id) && $post_id != null) {
            $post = array('ID' => $post_id, 'post_status' => "publish");
            wp_update_post($post);

            $response = ["id" => $post_id];
            return rest_ensure_response($response);
        } else {
            return new \WP_Error('error_restore_data', "Restore Post failed, could not find original post: " .
                $post_id, array('status' => 404));
        }
    }

    public function comm_product_clone($request)
    {
        $params = $request->get_params();
        global $wpdb;
        $post_id = sanitize_text_field(isset($params['id']) ? absint($params['id']) : absint($params['id']));

        $post = get_post($post_id);
        $current_user = wp_get_current_user();
        $new_post_author = $current_user->ID;
        if (isset($post) && $post != null) {

            $args = array(
                'comment_status' => $post->comment_status,
                'ping_status' => $post->ping_status,
                'post_author' => $new_post_author,
                'post_content' => $post->post_content,
                'post_excerpt' => $post->post_excerpt,
                'post_name' => $post->post_name,
                'post_parent' => $post->post_parent,
                'post_password' => $post->post_password,
                'post_status' => 'draft',
                'post_title' => $post->post_title . "-copy",
                'post_type' => $post->post_type,
                'to_ping' => $post->to_ping,
                'menu_order' => $post->menu_order
            );

            $new_post_id = wp_insert_post($args);

            $taxonomies = get_object_taxonomies($post->post_type); // returns array of taxonomy names for post type, ex array("category", "post_tag");
            foreach ($taxonomies as $taxonomy) {
                $post_terms = wp_get_object_terms($post_id, $taxonomy, array('fields' => 'slugs'));
                wp_set_object_terms($new_post_id, $post_terms, $taxonomy, false);
            }

            $post_meta_infos = $wpdb->get_results("SELECT meta_key, meta_value FROM $wpdb->postmeta WHERE post_id=$post_id");
            if (count($post_meta_infos) != 0) {
                $sql_query = "INSERT INTO $wpdb->postmeta (post_id, meta_key, meta_value) ";
                foreach ($post_meta_infos as $meta_info) {
                    $meta_key = $meta_info->meta_key;
                    if ($meta_key == '_wp_old_slug') continue;
                    $meta_value = addslashes($meta_info->meta_value);
                    $sql_query_sel[] = "SELECT $new_post_id, '$meta_key', '$meta_value'";
                }
                $sql_query .= implode(" UNION ALL ", $sql_query_sel);
                $wpdb->query($sql_query);
            }

            $response = ["id" => $new_post_id];
            return rest_ensure_response($response);
        } else {
            return new \WP_Error('error_clone_data', "Post creation failed, could not find original post: " .
                $post_id, array('status' => 404));
        }
    }

    public function comm_product_trash($request)
    {
        $params = $request->get_params();
        $prod_id = intval($params['id']);
        $tbl = sanitize_text_field($params['tbl']);
        $status = sanitize_text_field($params['status']);
        $response = wp_trash_post($prod_id);
        if ($response == null) {
            $response = "error";
            return new \WP_Error('error_trash_data', 'Error trash data', array('status' => 404));
        }
        $response = ["status" => $response];
        return rest_ensure_response($response);
    }

    public function comm_product_term_action($request)
    {
        $params = $request->get_params();
        $category_id = sanitize_post($params['id']);
        $tbl = sanitize_text_field($params['tbl']);
        $status = sanitize_text_field($params['status']);

        foreach ($category_id as $key => $val) {
            $response = wp_delete_term(intval($val), $tbl);
            if ($response == null) {
                $response = "error";
                return new \WP_Error('error_trash_data', 'Error trash data', array('status' => 404));
            }
        }

        $response = ["status" => $response];
        return rest_ensure_response($response);
    }

    public function comm_getParent($request)
    {
        $taxonomy = "comm_product_cat";
        $dropdown_args = array(
            'hide_empty' => 0,
            'hide_if_empty' => false,
            'taxonomy' => $taxonomy,
            'name' => 'parent',
            'orderby' => 'name',
            'hierarchical' => true,
            'show_option_none' => __('None'),
        );
        $dropdown_args = apply_filters('taxonomy_parent_dropdown_args', $dropdown_args, $taxonomy, 'new');
        return wp_dropdown_categories($dropdown_args);
    }

    public function comm_product_cat($taxonomy, $type)
    {
        $output['data'] = [];

        if ($taxonomy) {
            $taxonomy_data = [];

            if (!is_array($taxonomy) || count($taxonomy) == 1) {
                $taxonomy_data[] = $taxonomy;
            } else {
                $taxonomy_data = $taxonomy;
            }
            foreach ($taxonomy_data as $term) {
                $checkbox = '<div class="table-option"><input type="checkbox" name="category_id" value="' . $term->term_id . '">
                                <span class="btn btn-default" type="button" data-bs-toggle="dropdown"><i class="fa fa-angle-down"></i></span>
                                <ul class="dropdown-menu dropdown-menu-right">
                                    <li><a href="#" class="c-edit" data-id="' . $term->term_id . '" data-action="edit" data-type="single">Edit</a></li>
                                    <li><a href="#" class="c-delete delete" data-id="' . $term->term_id . '" data-action="delete" data-type="single">Delete</a></li>
                                </ul>
                            </div>';

                if ($type == 'comm_product_cat') {
                    // checkbox

                    $parent = get_term($term->parent, $term->taxonomy); // get parent term
                    $children = get_term_children($term->term_id, $term->taxonomy); // get children

                    // if no parent term
                    $dont_have_parent = !$parent || is_wp_error($parent);

                    if ($dont_have_parent && sizeof($children) == 0) {
                        // has parent and child
                        /* $actionButton = '<a href="#"
                           class="btn btn-sm c-btn-wrap-category c-edit mb-2"
                           data-bs-container="body"
                           data-bs-toggle="popover"
                           data-bs-placement="top"
                           data-bs-trigger="hover"
                           data-bs-content="Edit" data-id="' . $term->term_id . '">
                            <i class="fa fa-edit"></i>
                        </a>
                        <a
                                href="#"
                                class="btn btn-sm c-btn-wrap-category c-delete mb-2"
                                data-bs-container="body"
                                data-bs-toggle="popover"
                                data-bs-placement="top"
                                data-bs-trigger="hover"
                                data-bs-content="Delete" data-id="' . $term->term_id . '">
                            <i class="fa fa-trash"></i>
                        </a>'; */
                        $output ['data'][] = [
                            $checkbox,
                            $term->name,
                            $term->description,
                            $term->slug,
                            "N/A ",
                            $term->count,
                            /* $actionButton */
                        ];

                    } elseif ($dont_have_parent && sizeof($children) > 0) {
                        // no parent, has child
                        /* $actionButton = '<a href="#"
                           class="btn btn-sm c-btn-wrap-category c-edit mb-2"
                           data-bs-container="body"
                           data-bs-toggle="popover"
                           data-bs-placement="top"
                           data-bs-trigger="hover"
                           data-bs-content="Edit" data-id="' . $term->term_id . '">
                            <i class="fa fa-edit"></i>
                        </a>
                        <a
                                href="#"
                                class="btn btn-sm c-btn-wrap-category c-delete mb-2"
                                data-bs-container="body"
                                data-bs-toggle="popover"
                                data-bs-placement="top"
                                data-bs-trigger="hover"
                                data-bs-content="Delete" data-id="' . $term->term_id . '">
                            <i class="fa fa-trash"></i>
                        </a>'; */
                        $output ['data'][] = [
                            $checkbox,
                            $term->name,
                            $term->description,
                            $term->slug,
                            "N/A ",
                            $term->count
                        ];
                        foreach ($children as $child) {
                            $parentSub = get_term($child, "comm_product_cat"); // get parent term
                            $term_subs = get_term_by('id', $parentSub->parent, "comm_product_cat");
                            $name = $term_subs->name;
                            /* $actionButton = '<a href="#"
                           class="btn btn-sm c-btn-wrap-category c-edit mb-2"
                           data-bs-container="body"
                           data-bs-toggle="popover"
                           data-bs-placement="top"
                           data-bs-trigger="hover"
                           data-bs-content="Edit" data-id="' . $child . '">
                            <i class="fa fa-edit"></i>
                        </a>
                        <a
                                href="#"
                                class="btn btn-sm c-btn-wrap-category c-delete mb-2"
                                data-bs-container="body"
                                data-bs-toggle="popover"
                                data-bs-placement="top"
                                data-bs-trigger="hover"
                                data-bs-content="Delete" data-id="' . $child . '">
                            <i class="fa fa-trash"></i>
                        </a>'; */
                            // checkbox
                            $checkbox = '<div class="table-option"><input type="checkbox" name="category_id" value="' . $child . '">
                                            <span class="btn btn-default" type="button" data-bs-toggle="dropdown"><i class="fa fa-angle-down"></i></span>
                                            <ul class="dropdown-menu dropdown-menu-right">
                                                <li><a href="#" class="c-edit" data-id="' . $child . '" data-action="edit" data-type="single">Edit</a></li>
                                                <li><a href="#" class="c-delete delete" data-id="' . $child . '" data-action="delete" data-type="single">Delete</a></li>
                                            </ul>
                                        </div>';

                            $output['data'][] = [
                                $checkbox,
                                esc_attr($parentSub->name),
                                $parentSub->description,
                                $parentSub->slug,
                                $dont_have_parent && (sizeof($children) > 0) ? $name : "N/A",
                                $parentSub->count
                            ];
                        }
                    }

                } else {
                    $actionButton = '<a href="#"
                           class="btn btn-sm c-btn-wrap-category c-edit mb-2"
                           data-bs-container="body"
                           data-bs-toggle="popover"
                           data-bs-placement="top"
                           data-bs-trigger="hover"
                           data-bs-content="Edit" data-id="' . $term->term_id . '">
                            <i class="fa fa-edit"></i>
                        </a>
                        <a
                                href="#"
                                class="btn btn-sm c-btn-wrap-category c-delete mb-2"
                                data-bs-container="body"
                                data-bs-toggle="popover"
                                data-bs-placement="top"
                                data-bs-trigger="hover"
                                data-bs-content="Delete" data-id="' . $term->term_id . '">
                            <i class="fa fa-trash"></i>
                        </a>';
                    $output ['data'][] = [
                        $checkbox,
                        $term->name,
                        $term->description,
                        $term->slug,
                        $term->count
                    ];
                }
            }
        }
        return $output;
    }

    public function comm_product_list($product)
    {
        $output['data'] = [];
        if ($product) {
            $i = 0;
            if ( ! is_array( $product ) || count( $product ) == 1 ) {
                $product_data[] = $product;
            } else {
                $product_data = $product;
            }

            $product_order = false;
            if (!function_exists("comm_calculate_orders_sales_product")) {
                $product_order = comm_calculate_order_sales_product();
            }

            foreach ($product_data as $p => $v) {
                // Begin Fix Closing Rate admin products - if the product has not been purchased then column Order, Sales and Closing.R the value should be 0
                // since 0.3.4
                $sales_list = 0;
                $closing_rate = 0;
                $total_order_cr_list = 0;
                // End Fix Closing Rate admin products - if the product has not been purchased then column Order, Sales and Closing.R the value should be 0
                $id = $v->ID;
                if ($product_order) {
                    foreach ($product_order as $prod_order) {
                        if ($prod_order->product_id == $id) {
                            $order_list = $prod_order->orders;
                            $sales_list = $prod_order->sales;
                            $total_order_cr_list = $prod_order->torders;
                            if ($sales_list != 0 && $total_order_cr_list != 0) {
                                if (has_filter("comm_calculated_closing_rate")) {
                                    $closing_rate = apply_filters("comm_calculated_closing_rate", $sales_list, $total_order_cr_list);
                                }
                            }
                        }

                    }
                }

                $_product = comm_get_product($id);

                if ($_product->is_featured()) {
                    $featured = __("Yes", "commercioo");
                } else {
                    $featured = __("No", "commercioo");
                }
                $this->product_id = $id;

                $data_wa_url = '';
                $data_wa_info = '';
                if (is_comm_wa()) {
                    if (method_exists("Commercioo_WA_API", 'commercioo_get_wa_checkout_url')) {
                        $Commercioo_WA_API = \Commercioo_WA_API::get_instance();
                        $commercioo_get_wa_checkout_url = $Commercioo_WA_API->commercioo_get_wa_checkout_url($id);
                        $data_wa_url = 'data-wa-url="' . $commercioo_get_wa_checkout_url['url'] . '"';
                        $data_wa_info = 'data-wa-info="' . $commercioo_get_wa_checkout_url['info'] . '"';
                    }
                }
                $thumbnail = '<img src=' . $_product->get_image_url() . ' class="img-thumbnail" alt="Responsive image">';
                $check_product_type = comm_chec_product_type($id);
                $product_type_name = sprintf("<b>%s</b>", __("— Fisik", "commercioo_title"));

                if ($check_product_type == "digital") {
                    $product_type_name = sprintf("<b>%s</b>", __("— Non Fisik", "commercioo_title"));
                }

                $price = '<div class="comm-price">';
                $price .= $_product->get_price_display();
                $price .= '</div>';
                if ($_product->get_status() == "publish") {
                    $post_title = "<a href='" . get_permalink($id) . "' target='_blank' class='comm_title'>";
//                $post_title .= $_product->get_title() . " " . $product_type_name;
                    $post_title .= $_product->get_title();
                    $post_title .= "</a>";
                    $post_title .= '<div class="c-icon-products">
                                <div class="mt-3 desktop-view">
                                    <span 
                                        class="c-product-icon-wrap-link mt-1 c-show-order-form"
                                        data-bs-container="body" 
                                        data-bs-toggle="modal" 
                                        data-bs-placement="top" 
                                        data-bs-trigger="hover"  data-bs-target=".comm-order-form-product"
                                        data-bs-content="Order form" data-id=' . $id . ' ' . $data_wa_url . ' ' . $data_wa_info . ' data-slug="' . get_permalink($id) . '"
                                        >
                                        <i data-bs-container="body" 
                                        data-bs-toggle="popover" 
                                        data-bs-placement="top" 
                                        data-bs-trigger="hover"  data-bs-content="Order form" class="fa fa-link" style="transform: scaleX(-1);"></i>
                                    </span>
                                    <!--<a 
                                        href="#" 
                                        class="c-product-icon-wrap-stats mt-1"
                                        data-bs-container="body" 
                                        data-bs-toggle="popover" 
                                        data-bs-placement="top" 
                                        data-bs-trigger="hover"
                                        data-bs-content="Detail statistics" 
                                    >
                                        <i class="fa fa-line-chart"></i>
                                    </a>-->
                                </div>                              
                            </div>';
                }else{
                    $post_title = $_product->get_title();
                }

                if ($_product->get_status() !== "trash") {
                    $actionButton = '
                   <a href="#" data-bs-container="body" data-bs-toggle="popover" data-bs-placement="top" data-bs-trigger="hover" data-bs-content="Edit"  class="c-edit btn btn-sm c-btn-wrap-products mb-2" data-id="' . $id . '">
                         <i class="fa fa-edit"></i>
                        </a>
                        <a href="#" data-bs-container="body" data-bs-toggle="popover" data-bs-placement="top" data-bs-trigger="hover" 
                        data-bs-content="Clone"  data-status="clone" class="c-clone btn btn-sm c-btn-wrap-products mb-2 ccioo_action" data-id="' . $id . '">
                           <i class="fa fa-clone"></i> 
                           </a>
                        <a href="#"  data-id="' . $id . '" data-status="trash"  data-bs-container="body" data-bs-toggle="popover" 
                        data-bs-placement="top" data-bs-trigger="hover" data-bs-content="Trash" 
                        class="c-trash btn btn-sm c-btn-wrap-products mb-2 ccioo_action">
                           <i class="fa fa-trash"></i>
                        </a>';
                } else {
                    $actionButton = '
                    <a href="#" data-bs-container="body" data-bs-toggle="popover" data-bs-placement="top" data-bs-trigger="hover" data-bs-content="Restore" class="c-restore btn btn-sm c-btn-wrap-products mb-2 ccioo_action" data-id="' . $id . '" data-status="published">
                      <i class="fa fa-refresh"></i>
                    </a>
                     <a href="#" data-bs-container="body" data-bs-toggle="popover" data-bs-placement="top" data-bs-trigger="hover" 
                     data-bs-content="Delete" class="c-delete btn btn-sm c-btn-wrap-products mb-2 ccioo_action" data-id="' . $id . '" data-status="del_permanent">
                       <i class="fa fa-trash"></i>
                    </a>';
                }
                $is2step = (method_exists("Commercioo\Models\Product", 'is_2_step')) ? $_product->is_2_step() : null;
                $context_2_step = (function_exists("comm_do_2_step_menu")) ? comm_do_2_step_menu($id, $is2step) : null;
                $checkout_url = '';
                if ($_product->get_status() == "publish") {
                    $checkout_url = '<li><a href="#" class="mt-1 c-show-order-form"
                                        data-bs-container="body" 
                                        data-bs-toggle="modal" 
                                        data-bs-placement="top" 
                                        data-bs-trigger="hover"  data-bs-target=".comm-order-form-product"
                                        data-bs-content="Order form" data-id=' . $id . ' ' . $data_wa_url . ' ' . $data_wa_info . '">Checkout URL</a></li><hr>';
                }
                $checkbox = '<div class="table-option"><input type="checkbox" name="user_id" value="' . $id . '">';
                $checkbox .= '<span class="btn btn-default" type="button" data-bs-toggle="dropdown">';
                $checkbox .= '<i class="fa fa-angle-down"></i></span>';
                $checkbox .= '<ul class="dropdown-menu dropdown-menu-right">' . $context_2_step . $checkout_url;
                if ($_product->get_status() !== "trash") {
                    $checkbox .= '<li><a href="#" data-bs-container="body" class="c-edit" data-id="' . $id . '">Edit</a></li>';
                }
                if ($_product->get_status() == "publish") {
                    $checkbox .= '<li><a href="#" data-bs-container="body" data-status="clone" class="c-clone" data-id="' . $id . '">Clone</a></li>';
                }
                if ($_product->get_status() !== "trash") {
                    $checkbox .= '<li class="delete"><a href="#"  data-id="' . $id . '" data-status="trash"  data-bs-container="body" class="c-trash">Delete</a></li>';
                }
                if ($_product->get_status() === "trash") {
                    $checkbox .= '<li><a href="#"  data-id="' . $id . '" data-status="restore"  data-bs-container="body" class="c-restore">Restore</a></li>';
                    $checkbox .= '<li class="delete"><a href="#"  data-id="' . $id . '" data-status="delete"  data-bs-container="body" class="c-delete">Delete Permanent</a></li>';
                }
                $checkbox .= '</ul></div>';
                $output['data'][$i] = [
                    $checkbox,
                    $thumbnail,
                    $post_title,
                    $_product->get_sku(),
                    $_product->get_stock_status_label(),
                    $price,
                    $_product->get_status_label(),
                    ucfirst($featured),
                    $total_order_cr_list,
                    $sales_list,
                    $closing_rate . "%",
                ];
                $i++;
            }

        }
        return $output;
    }

    // GET Commercioo Stock

    public function get_stock_status()
    {
        return $this->stock_status;
    }

    // GET Product ID

    public function get_the_id()
    {
        return $this->product_id;
    }
}
//Comm_Product::getInstance();