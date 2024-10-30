<?php
namespace commercioo\admin;
Class Comm_Stock_Management {

	// The ID of order that currently manage
	public $order_id;
	
	// Initial items before update, its will be empty on insert calls
	public $initial_items = array();

	// Array of stock remain after updated by order
	public $last_stock = array();
    // instance
    private static $instance;

    // getInstance
    public static function get_instance() {
        if( ! isset( self::$instance ) ) {
            self::$instance = new Comm_Stock_Management(null);
        }

        return self::$instance;
    }

	/**
	 * Items that will be removed form commercioo_order_items
	 * Rejected because of the order qty is bigger than the stock
	 */
	public $rejected_item_ids = array();

	// Get the current items or class initiation
	function __construct( $order_id ) {
	    if($order_id != null){
            $this->order_id = $order_id;
        }
		$this->get_initial_items();
	}

	public function get_initial_items() {
		global $wpdb;
		$db_table = $wpdb->prefix . 'commercioo_order_items';

		// get from database
        $this->initial_items = $wpdb->get_results( $wpdb->prepare( "
            SELECT item_id, product_id, variation_id, item_order_qty  
            FROM $db_table
            WHERE order_id = %d", 
            $this->order_id
        ) );
	}

	public function update_latest_stock( $product_id, $stock ) {
		$this->last_stock[ $product_id ] = $stock;
	}

	public function update_rejected_item_ids( $item_id ) {
		$this->rejected_item_ids[] = $item_id;
	}

	private function is_commercio_pro_installed() {
    	return class_exists( 'Commercioo_Pro' );
    }

    public function initial_item_fields_by_product( $product_id, $variation_id ) {
        foreach ( $this->initial_items as $initial_item ) {
            if ( $product_id == $initial_item->product_id && $variation_id == $initial_item->variation_id ) {
                return $initial_item;
            }
            return false;
        }
    }

	/**
     * Validate product's stock
     * affected by variable: stock_status, manage_stock, and stock
     * also we must check the already stored number if any
     * so we will know that the total_stock = stock + already_stored_number
     */
    public function validate_stock( $prod_id, $var_id, $item_order_qty ) {
        global $wpdb;
        $db_table = $wpdb->prefix . 'commercioo_order_items';

        if ( ! empty( $var_id ) ) {
            $product_id = $var_id;
        } else {
            $product_id = $prod_id;
        }

        // commercioo pro variables
        $manage_stock = get_post_meta( $product_id, '_manage_stock', true );        
        $final_stock_status = false;

        // validate stock
        if ( $this->is_commercio_pro_installed() && $manage_stock ) {
            $stock = intval( get_post_meta( $product_id, '_stock', true ) );

            // get already stored number if any
            $already_stored_number = $this->initial_item_fields_by_product( $prod_id, $var_id )->item_order_qty;
            $total_stock = $stock + intval( $already_stored_number );
            $last_stock = $total_stock - $item_order_qty;

            // check the total stock will be enough or not
            if ( $last_stock >= 0 ) {
                $final_stock_status = true;     

                // add to last stock
                $this->update_latest_stock( $product_id, $last_stock );
            }
            else {
            	/**
            	 * This item will be rejected
            	 * so, return already ordered items to stock
            	 */
                $this->update_latest_stock( $product_id, $total_stock );
            }
        }
        else {
            $stock_status = get_post_meta( $product_id, '_stock_status', true );
            $final_stock_status = ( $stock_status == 'in_stock' ) ? true : false;
        }

        return apply_filters( 'comm_validate_stock_status', $final_stock_status, $product_id );
    }

    /**
     * Restock the rest of stocks remains because of order
     * Restock rejected items
     */
    public function restock() {
    	foreach ( $this->last_stock as $product_id => $stock ) {
    		update_post_meta( $product_id, '_stock', $stock );
    	}
    }

    /**
     * Restock all initial order items
     * Mainly because of order deletion
     */
    public function force_restock_all_items() {
    	foreach ( $this->initial_items as $item ) {
    		// let us manage the stock?
    		$manage_stock = get_post_meta( $item->product_id, '_manage_stock', true );

    		if ( $this->is_commercio_pro_installed() && $manage_stock ) {
	    		// get current stock
	    		$stock = intval( get_post_meta( $item->product_id, '_stock', true ) );
	    		$total_stock = $stock + intval( $item->item_order_qty );

	    		// update stock
	    		update_post_meta( $item->product_id, '_stock', $total_stock );
	    	}
            global $wpdb;
            $db_table = $wpdb->prefix . 'commercioo_order_items';
            $wpdb->delete( $db_table, array( 'item_id' => $item->item_id ) );
    	}
    }
}