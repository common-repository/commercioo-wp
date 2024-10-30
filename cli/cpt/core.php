<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
// main API class
Class Comm_API {
    private static $instance;
    function __construct() {
        // silent is golden
    }
    public static function get_instance()
    {
        if (!isset(self::$instance)) {
            self::$instance = new Comm_API();
        }
        return self::$instance;
    }
    /**
     * Register post type
     * uniform the custom post type args that used by APIs
     */
    public function comm_register_post_type( $name, $taxonomies = array(), $public = true, $supports = array() ) {
        $args = array(
            'labels' => array(
                'name' => $name,
                'singular_name' => $name,
            ),
            'public' => $public,
            'has_archive' => true,
            'show_ui' => false,
            'show_in_nav_menus' => false,
            'show_in_rest' => true,
            'supports' => array_merge( array( 'title' ), $supports ) ,
            'rest_controller_class' => 'WP_REST_Posts_Controller',
            'rest_base' => $name,
            'taxonomies' => $taxonomies,
        );

        register_post_type( $name, $args );
    }

    /**
     * Register taxonnomy
     * uniform the custom taxonnomy args that used by APIs
     */
    public function comm_register_taxonomy( $name, $post_types, $hierarchical = true ) {
        $args = array(
            'labels' => array(
                'name' => $name,
                'singular_name' => $name,
            ),
            'hierarchical' => $hierarchical,
            'show_ui' => false,
            'show_in_rest' => true,
            'rest_controller_class' => 'WP_REST_Terms_Controller',
            'rest_base' => $name,
            'query_var'             => true,
            'rewrite' => array( 'slug' => $name ),
        );

        register_taxonomy( $name, $post_types, $args );
    }
}