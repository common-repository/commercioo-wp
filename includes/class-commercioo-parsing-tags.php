<?php

namespace Commercioo\Parsing;
/**
 * Commercioo for creating Template Parsing Variable
 *
 * Template Parsing Variable are wrapped in { }
 *
 * A few examples:
 *
 * {user_email}
 * {name}
 * {sitename}
 *
 *
 * To replace tags in content, use: comm_do_template_tags( $content, order_id );
 *
 *
 * @package     Commercioo
 * @subpackage  Template Variable Tags
 * @copyright   Copyright (c) 2015, Commercioo
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.9
 * @author      Commercioo
 */

// Exit if accessed directly
if (!defined('ABSPATH')) exit;

class Commercioo_Parsing_Tags
{
    /**
     * Container for storing all tags
     *
     * @since 1.9
     */
    private $tags = array();

    /**
     * Payment ID
     *
     * @since 1.9
     */
    private $order_id;
    private static $instance;

    public function __construct()
    {
        add_action('init', array($this,'comm_load_parsing_tags'),-999);
        add_action('comm_add_parsing_tags', array($this, 'comm_setup_parsing_tags'));
    }

    public static function get_instance()
    {
        if (!isset(self::$instance)) {
            self::$instance = new Commercioo_Parsing_Tags();
        }
        return self::$instance;
    }

    /**
     * Add default EDD email template tags
     *
     * @since 1.9
     */
    public function comm_setup_parsing_tags()
    {
        // Setup default tags array
        $parsing_tags = array(
            array(
                'tag'         => 'order_id',
                'description' => __( 'The unique ID number for this purchase', 'commercioo' ),
                'function'    => 'comm_tag_order_id'
            ),
            array(
                'tag' 		  => 'user_email',
                'description' => __("The buyer's first name", 'commercioo'),
                'function' 	  => 'comm_tag_user_email'
            ),
            array(
                'tag'         => 'date',
                'description' => __( 'The date of the purchase', 'commercioo' ),
                'function'    => 'comm_tag_date'
            ),
            array(
                'tag'         => 'subtotal',
                'description' => __( 'The subtotal of the purchase', 'commercioo' ),
                'function'    => 'comm_tag_subtotal'
            ),
            array(
                'tag'         => 'total',
                'description' => __( 'The Grand Total of the purchase', 'commercioo' ),
                'function'    => 'comm_tag_total'
            ),
            array(
                'tag'         => 'payment_method',
                'description' => __( 'The method of order used for this purchase', 'commercioo' ),
                'function'    => 'comm_tag_payment_method'
            ),
            array(
                'tag'         => 'status_order',
                'description' => __( 'The Status Order of order', 'commercioo' ),
                'function'    => 'comm_tag_status_order'
            ),
            array(
                'tag'         => 'bank',
                'description' => __( 'The bank list of order used for this purchase', 'commercioo' ),
                'function'    => 'comm_tag_bank'
            ),
            array(
                'tag'         => 'bank_info',
                'description' => __( 'The bank list of order used for this purchase', 'commercioo' ),
                'function'    => 'comm_tag_bank_info'
            ),
            array(
                'tag'         => 'order_detail',
                'description' => __( 'The order detail of order used for this purchase', 'commercioo' ),
                'function'    => 'comm_tag_order_detail'
            ),
            array(
                'tag'         => 'user_billing_address',
                'description' => __( 'The user billing address of order used for this purchase', 'commercioo' ),
                'function'    => 'comm_tag_user_billing_address'
            ),
            array(
                'tag'         => 'user_shipping_address',
                'description' => __( 'The user shipping of order used for this purchase', 'commercioo' ),
                'function'    => 'comm_tag_user_shipping_address'
            ),
            array(
                'tag'         => 'user_shipping_billing_address',
                'description' => __( 'The 2 columns of billing and shipping address', 'commercioo' ),
                'function'    => 'comm_tag_user_shipping_billing_address'
            ),
            array(
                'tag'         => 'sitename',
                'description' => __( 'The Site Name of your blog', 'commercioo' ),
                'function'    => 'comm_tag_sitename'
            ),
            array(
                'tag'         => 'username',
                'description' => __( 'The username of order used for this purchase', 'commercioo' ),
                'function'    => 'comm_tag_username'
            ),
            array(
                'tag'         => 'fullname',
                'description' => __( 'The user full name, first and last for this purchase', 'commercioo' ),
                'function'    => 'comm_tag_fullname'
            ),
            array(
                'tag'         => 'name',
                'description' => __( 'The user first name for this purchase', 'commercioo' ),
                'function'    => 'comm_tag_name'
			),
			array(
                'tag'         => 'login_url',
                'description' => __( "The website's login url", 'commercioo' ),
                'function'    => 'comm_tag_login_url',
			),
			array(
                'tag'         => 'admin_name',
                'description' => __( "The website's admin name", 'commercioo' ),
                'function'    => 'comm_tag_admin_name',
			),
			array(
                'tag'         => 'support_email',
                'description' => __( "The website's support email", 'commercioo' ),
                'function'    => 'comm_tag_support_email',
			),
			array(
                'tag'         => 'order_details',
                'description' => __( "The order details of the purchase", 'commercioo' ),
                'function'    => 'comm_tag_order_details',
			),
			array(
                'tag'         => 'user_address',
                'description' => __( "The address of the user's", 'commercioo' ),
                'function'    => 'comm_tag_user_address',
			),
            array(
                'tag'         => 'konfirmasi_pembayaran_bank',
                'description' => __( "Form Konfirmasi Pembayaran Bank Transfer", 'commercioo' ),
                'function'    => 'comm_tag_konfirmasi_pembayaran_bank',
            ),
        );

        // Apply comm_parsing_tags filter
        $parsing_tags = apply_filters('comm_parsing_tags', $parsing_tags);

        // Add email tags
        foreach ($parsing_tags as $parsing_tag) {
            comm_add_parsing_tag($parsing_tag['tag'], $parsing_tag['description'], $parsing_tag['function']);
        }

    }
    /**
     * Load email tags
     *
     * @since 1.9
     */
    function comm_load_parsing_tags()
    {
      do_action( 'comm_add_parsing_tags' );
    }
    /**
     * Add an email tag
     *
     * @since 1.9
     *
     * @param string $tag Email tag to be replace in email
     * @param callable $func Hook to run when email tag is found
     */
    public function add($tag, $description, $func)
    {
        if (is_callable($func)) {
            $this->tags[$tag] = array(
                'tag' => $tag,
                'description' => $description,
                'func' => $func
            );
        }
    }

    /**
     * Check if $tag is a registered parsing tag
     *
     * @since 1.9
     *
     * @param string $tag Parsing tag that will be searched
     *
     * @return bool
     */
    public function parsing_tag_exists($tag)
    {
        return array_key_exists($tag, $this->tags);
    }

    /**
     * Returns a list of all parsing tags
     *
     * @since 1.9
     *
     * @return array
     */
    public function get_tags()
    {
        return $this->tags;
    }

    /**
     * Search content for email tags and filter email tags through their hooks
     *
     * @param string $content Content to search for email tags
     * @param int $payment_id The payment id
     *
     * @since 1.9
     *
     * @return string Content with email tags filtered out.
     */
    public function do_tags($content, $order_id)
    {
        // Check if there is atleast one tag added
        if (empty($this->tags) || !is_array($this->tags)) {
            return $content;
        }
        $this->order_id = $order_id;


        $new_content = preg_replace_callback("/{([A-z0-9\-\_]+)}/s", array($this, 'do_tag'), $content);

        $this->order_id = null;

        return $new_content;
    }

    /**
     * Do a specific tag, this function should not be used. Please use comm_do_parsing_tags instead.
     *
     * @since 1.9
     *
     * @param $m
     *
     * @return mixed
     */
    public function do_tag($m)
    {
        // Get tag
        $tag = $m[1];
        // Return tag if tag not set
        if (!$this->parsing_tag_exists($tag)) {
            return $m[0];
        }

        return call_user_func($this->tags[$tag]['func'], $this->order_id, $tag);
    }
}