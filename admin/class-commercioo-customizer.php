<?php
/**
 * The WordPress customizer-specific functionality of the plugin.
 *
 * @package    Commercioo
 */
class Commercioo_Customizer {

    /**
     * The ID of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string $commercioo The ID of this plugin.
     */
    private $commercioo;

    /**
     * The version of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string $version The current version of this plugin.
     */
    private $version;
	
	/**
	 * The button colors selection
	 */
    private $all_button_colors;

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     * @param      string $commercioo The name of this plugin.
     * @param      string $version The version of this plugin.
     */
    public function __construct($commercioo, $version) {
        $this->commercioo = $commercioo;
        $this->version 	  = $version;
		
		// all button colors
		$this->all_button_colors = array(
			'#3f51b5,#303f9f' => '#3f51b5,#303f9f',
			'#673ab7,#512da8' => '#673ab7,#512da8',
			'#9c27b0,#7b1fa2' => '#9c27b0,#7b1fa2',
			'#e91e63,#c2185b' => '#e91e63,#c2185b',
			'#f44336,#d32f2f' => '#f44336,#d32f2f',
			'#ff5722,#e64a19' => '#ff5722,#e64a19',
			'#ff9800,#f57c00' => '#ff9800,#f57c00',
			'#ffc107,#ffa000' => '#ffc107,#ffa000',
			'#4caf50,#388e3c' => '#4caf50,#388e3c',
			'#009688,#00796b' => '#009688,#00796b',
			'#00bcd4,#0097a7' => '#00bcd4,#0097a7',
			'#03a9f4,#0288d1' => '#03a9f4,#0288d1',
			'#2196f3,#1976d2' => '#2196f3,#1976d2',
		);
	}

	/**
	 * Enqueue script on customizer
	 */
	public function customize_controls_enqueue_scripts() {
		wp_register_style( 'commercioo-customizer-style', COMMERCIOO_URL . 'admin/css/commercioo-customizer.css', NULL, NULL, 'all' );
		wp_enqueue_style( 'commercioo-customizer-style' );

		$site_colors = apply_filters( 'commercioo_theme_site_colors', false );
		$inline_style = '';

		foreach ( $this->all_button_colors as $key => $colors ) {
			$array_colors  = explode( ',', $colors );
			$inline_style .= sprintf( '
				#customize-control-comm_order_forms_settings-button_color input[value="%1$s"] + label:before {    
					background-color: %2$s;
				}
				#customize-control-comm_order_forms_settings-button_color input[value="%1$s"] + label:after {	
					background-color: %3$s;
				}
			', $key, $array_colors[0], $array_colors[1] );
		}

		$checkout_styling = get_option('comm_order_forms_settings', array());
		$inline_style .= `#commercioo-checkout-standalone .form_wrapper input[type="text"], 
			#commercioo-checkout-standalone .form_wrapper input[type="email"], 
			#commercioo-checkout-standalone .form_wrapper input[type="tel"], 
			#commercioo-checkout-standalone textarea, 
			#commercioo-checkout-standalone .form_wrapper select {
				border-color: ` . $checkout_styling['fields']['border_style'] . ` !important;
			}
			#commercioo-checkout-standalone .form_wrapper input[type="text"]:focus, 
			#commercioo-checkout-standalone .form_wrapper input[type="email"]:focus, 
			#commercioo-checkout-standalone .form_wrapper input[type="tel"]:focus, 
			#commercioo-checkout-standalone textarea:focus, 
			#commercioo-checkout-standalone .form_wrapper select:focus {
				border-color: ` . $checkout_styling['fields']['border_focus_style'] . ` !important;
			}`;

		// add inline style
		wp_add_inline_style( 'commercioo-customizer-style', $inline_style );
	}
	
	/**
	 * Register WP Customizer for chckout page
	 * only visible on frontend checkout page
	 */
	public function checkout_customizer( $wp_customize ) {
		// main chekcout page customizer panel
		$wp_customize->add_panel( 'commercioo_customize_checkout_settings', array(
			'priority'        => 160,
			'capability'      => 'edit_theme_options',
			'theme_supports'  => '',
			'title'           => __( 'Commercioo Checkout', 'commercioo' ),
			'description'     => __( 'Settings Options for Commercioo Checkout', 'commercioo' ),
		) );

		// include text editor custom control
		require_once COMMERCIOO_PATH . 'includes/class-text-editor-custom-control.php';

        $checkout       = Commercioo\Checkout::get_instance();
        $default_fields = $checkout->default_fields();
        $checkout->set_default_fields();

		$this->commercioo_customize_checkout_other_settings( $wp_customize );
		// check if elementor installed and checkout page is elementor page
		$checkout_page_id = get_option('commercioo_Checkout_page_id');
		if ( defined( 'ELEMENTOR_VERSION' ) && \Elementor\Plugin::$instance->documents->get( $checkout_page_id )->is_built_with_elementor() ) {
			return;
		} else {
			// load customizer sections & settings
			$this->commercioo_customize_checkout_fields_label( $wp_customize );
			$this->commercioo_customize_checkout_fields_visibility( $wp_customize );
			$this->commercioo_customize_checkout_field_style( $wp_customize );
			$this->commercioo_customize_checkout_button_text_and_style( $wp_customize );
			$this->commercioo_customize_checkout_button_colors( $wp_customize );
			$this->commercioo_customize_checkout_redirection( $wp_customize );
		}

	}

	public function button_customizer( $wp_customize ) {
		$button_customizer_args = array(
			'title'    => __( 'Buttons', 'commercioo' ),
			'priority' => 21,
		);
		if ( defined('COMMERCIOO_THEMEX_VERSION') ) {
			$button_customizer_args['panel'] = 'commercioo_customize_content_panel';
		}
		$wp_customize->add_section( 'commercioo_customize_button_settings', $button_customizer_args );

		// buy now button label
		$option_key = 'comm_misc_settings[buy_now_button_label]';
		$wp_customize->add_setting( $option_key, array(
			'default' 	 => 'Buy Now',
			'type' 		 => 'option', // you can also use 'theme_mod'
			'capability' => 'edit_theme_options'
		) );
		$wp_customize->add_control( new WP_Customize_Control( $wp_customize, $option_key, array(
			'label'    => __( '"Buy Now" Button Label', 'commercioo' ),
			'settings' => $option_key,
			'section'  => 'commercioo_customize_button_settings',
			'type'	   => 'text',
		) ) );	

		// add to cart button label
		$option_key = 'comm_misc_settings[add_to_cart_button_label]';
		$wp_customize->add_setting( $option_key, array(
			'default' 	 => 'Add to Cart',
			'type' 		 => 'option', // you can also use 'theme_mod'
			'capability' => 'edit_theme_options'
		) );
		$wp_customize->add_control( new WP_Customize_Control( $wp_customize, $option_key, array(
			'label'    => __( '"Add to Cart" Button Label', 'commercioo' ),
			'settings' => $option_key,
			'section'  => 'commercioo_customize_button_settings',
			'type'	   => 'text',
		) ) );
	}

	private function commercioo_customize_checkout_fields_label( $wp_customize ) {
		// add section
		$wp_customize->add_section( 'commercioo_customize_checkout_fields_label' , array(
			'title'	   => __( 'Address Fields Label', 'commercioo' ),
			'priority' => 160,
			'panel'	   => 'commercioo_customize_checkout_settings',			
		) );

		$checkout       = Commercioo\Checkout::get_instance();
		$billing_fields = $checkout->get_default_fields('billing');
		
		// create edit field for each label
		foreach ( $billing_fields as $key => $field ) {
			$option_key = "comm_order_forms_settings[billing_address][billing_$key]";

			$wp_customize->add_setting( $option_key, array(
				'default' 	 => $field['label'],
				'type' 		 => 'option', // you can also use 'theme_mod'
				'capability' => 'edit_theme_options'
			) );

			$wp_customize->add_control( new WP_Customize_Control( $wp_customize, $option_key, array(
				'label'    => $field['label'],
				'settings' => $option_key,
				'section'  => 'commercioo_customize_checkout_fields_label',
				'type'	   => 'text',
			) ) );
		}
	}

	private function commercioo_customize_checkout_fields_visibility( $wp_customize ) {
		// add section
		$wp_customize->add_section( 'commercioo_customize_checkout_fields_visibility' , array(
			'title'	   => __( 'Address Fields Visibility', 'commercioo' ),
			'priority' => 160,
			'panel'	   => 'commercioo_customize_checkout_settings',			
		) );

		$checkout       = Commercioo\Checkout::get_instance();
		$billing_fields = $checkout->get_default_fields('billing');
		
		// create edit field for each label
		foreach ( $billing_fields as $key => $field ) {
			if ( $field['required'] ) {
				continue;
			}

			$option_key = "comm_order_forms_settings[billing_address][billing_{$key}_visibility]";

			$wp_customize->add_setting( $option_key, array(
				'default' 	 => 'required',
				'type' 		 => 'option', // you can also use 'theme_mod'
				'capability' => 'edit_theme_options'
			) );

			$wp_customize->add_control( new WP_Customize_Control( $wp_customize, $option_key, array(
				'label'    => $field['label'],
				'settings' => $option_key,
				'section'  => 'commercioo_customize_checkout_fields_visibility',
				'type'	   => 'select',
				'choices'  => array(
					'required' => 'Required',
					'optional' => 'Optional',
					'hidden'   => 'Hidden',
				),
			) ) );
		}
	}

	private function commercioo_customize_checkout_field_style( $wp_customize ) {
		// add section
		$wp_customize->add_section( 'commercioo_customize_checkout_fields_style' , array(
			'title'	   => __( 'Address Fields Style', 'commercioo' ),
			'priority' => 160,
			'panel'	   => 'commercioo_customize_checkout_settings',			
		) );

        // option: commercioo_checkout_label_color_setting
		$option_key = 'comm_order_forms_settings[fields][label_style]';
        $wp_customize->add_setting( $option_key, array(
            'default' 	 => '#586469',
            'type' 		 => 'option', // you can also use 'option'
            'capability' => 'edit_theme_options'
        ) );
        $wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, $option_key, array(
            'label' 	  => __( 'Label Color', 'commercioo' ),
            'description' => __( 'Change your field label colors', 'commercioo' ),
            'settings' 	  => $option_key,
            'priority' 	  => 160,
            'section' 	  => 'commercioo_customize_checkout_fields_style',
        ) ) );
        // option: commercioo_checkout_text_field_color_setting
		$option_key = 'comm_order_forms_settings[fields][text_style]';
        $wp_customize->add_setting( $option_key, array(
            'default' 	 => '#757575',
            'type' 		 => 'option', // you can also use 'option'
            'capability' => 'edit_theme_options'
        ) );
        $wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, $option_key, array(
            'label' 	  => __( 'Text field Color', 'commercioo' ),
            'description' => __( 'Change your text field colors', 'commercioo' ),
            'settings' 	  => $option_key,
            'priority' 	  => 160,
            'section' 	  => 'commercioo_customize_checkout_fields_style',
        ) ) );
        // option: commercioo_checkout_border_field_color_setting
		$option_key = 'comm_order_forms_settings[fields][border_style]';
        $wp_customize->add_setting( $option_key, array(
            'default' 	 => '#ccc',
            'type' 		 => 'option', // you can also use 'option'
            'capability' => 'edit_theme_options'
        ) );
        $wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, $option_key, array(
            'label' 	  => __( 'Border Color', 'commercioo' ),
            'description' => __( 'Change your border field colors', 'commercioo' ),
            'settings' 	  => $option_key,
            'priority' 	  => 160,
            'section' 	  => 'commercioo_customize_checkout_fields_style',
        ) ) );
        // option: commercioo_checkout_border_field_color_focus_setting
		$option_key = 'comm_order_forms_settings[fields][border_focus_style]';
        $wp_customize->add_setting( $option_key, array(
            'default' 	 => '#F15A29',
            'type' 		 => 'option', // you can also use 'option'
            'capability' => 'edit_theme_options'
        ) );
        $wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, $option_key, array(
            'label' 	  => __( 'Border Focus Color', 'commercioo' ),
            'description' => __( 'Change your border field colors when focused', 'commercioo' ),
            'settings' 	  => $option_key,
            'priority' 	  => 160,
            'section' 	  => 'commercioo_customize_checkout_fields_style',
        ) ) );
	}

	private function commercioo_customize_checkout_button_text_and_style( $wp_customize ) {
		// add section
		$wp_customize->add_section( 'commercioo_customize_checkout_button_text_and_style' , array(
			'title'	   => __( 'Button Text & Style', 'commercioo' ),
			'priority' => 160,
			'panel'	   => 'commercioo_customize_checkout_settings',			
		) );
		
		// button text
		$option_key = 'comm_order_forms_settings[button_text]';
		$wp_customize->add_setting( $option_key, array(
			'default' 	 => 'Purchase Now',
			'type' 		 => 'option', // you can also use 'theme_mod'
			'capability' => 'edit_theme_options'
		) );
		$wp_customize->add_control( new WP_Customize_Control( $wp_customize, $option_key, array(
			'label'    => __( 'Button Text', 'commercioo' ),
			'settings' => $option_key,
			'section'  => 'commercioo_customize_checkout_button_text_and_style',
			'type'	   => 'text',
		) ) );

		// button style
		$option_key = 'comm_order_forms_settings[button_style]';
		$wp_customize->add_setting( $option_key, array(
			'default' 	 => 'c-button-rounded',
			'type' 		 => 'option', // you can also use 'theme_mod'
			'capability' => 'edit_theme_options'
		) );
		$wp_customize->add_control( new WP_Customize_Control( $wp_customize, $option_key, array(
			'label'    => __( 'Button Style', 'commercioo' ),
			'settings' => $option_key,
			'section'  => 'commercioo_customize_checkout_button_text_and_style',
			'type'	   => 'radio',
			'choices'  => array(
				'c-button-rounded'	   => 'Rounded',
				'c-button-big-rounded' => 'Big Rounded',
				'c-button-pill'   	   => 'Pill',
				'c-button-flat'        => 'Flat',
			),
		) ) );
	}

	private function commercioo_customize_checkout_button_colors( $wp_customize ) {
		// add section
		$wp_customize->add_section( 'commercioo_customize_checkout_button_colors' , array(
			'title'	   => __( 'Button Colors', 'commercioo' ),
			'priority' => 160,
			'panel'	   => 'commercioo_customize_checkout_settings',			
		) );

		// button colors selections
		$option_key = 'comm_order_forms_settings[button_color]';
		$wp_customize->add_setting( $option_key, array(
			'default' 	 => 'c-button-rounded',
			'type' 		 => 'option', // you can also use 'theme_mod'
			'capability' => 'edit_theme_options'
		) );
		$wp_customize->add_control( new WP_Customize_Control( $wp_customize, $option_key, array(
			'label'       => __( 'Button Colors', 'commercioo' ),
			'description' => __( 'Select your preferred button colors', 'commercioo' ),
			'settings'	  => $option_key,
			'section'	  => 'commercioo_customize_checkout_button_colors',
			'type'		  => 'radio',
			'choices'	  => $this->all_button_colors,
		) ) );
	}

	private function commercioo_customize_checkout_redirection( $wp_customize ) {
		// add section
		$wp_customize->add_section( 'commercioo_customize_checkout_redirection' , array(
			'title'	   => __( 'Checkout Redirection', 'commercioo' ),
			'priority' => 160,
			'panel'	   => 'commercioo_customize_checkout_settings',			
		) );
		
		// default thank_you_redirect
		$option_key = 'comm_order_forms_settings[thank_you_redirect]';
		$wp_customize->add_setting( $option_key, array(
			'default' 	 => 'page',
			'type' 		 => 'option', // you can also use 'theme_mod'
			'capability' => 'edit_theme_options'
		) );
		$wp_customize->add_control( new WP_Customize_Control( $wp_customize, $option_key, array(
			'label'    => __( 'Redirect Type After Submit', 'commercioo' ),
			'settings' => $option_key,
			'section'  => 'commercioo_customize_checkout_redirection',
			'type'	   => 'select',
			'choices'  => apply_filters( 'commercioo_customizer_checkout_redirect_choices', array(
				'page' => 'Thank You Page'
			) ),
		) ) );
	}

	private function commercioo_customize_checkout_other_settings( $wp_customize ) {
		// add section
		$wp_customize->add_section( 'commercioo_customize_checkout_other_settings' , array(
			'title'	   => __( 'Other Settings', 'commercioo' ),
			'priority' => 180,
			'panel'	   => 'commercioo_customize_checkout_settings',			
		) );

		// comm_page_views_checkout_lifetime
		$option_key = 'comm_page_views_checkout_lifetime';		
		$wp_customize->add_setting( $option_key, array(
			'default' 	 => 30,
			'type' 		 => 'option', // you can also use 'theme_mod'
			'capability' => 'edit_theme_options'
		) );
		$wp_customize->add_control( new WP_Customize_Control( $wp_customize, $option_key, array(
			'label'       => __( 'Checkout Views Cookies Length', 'commercioo' ),
			'description' => __( 'Enter cookies length for checkout views (in days)', 'commercioo' ),
			'settings'    => $option_key,
			'section'     => 'commercioo_customize_checkout_other_settings',
			'type'        => 'number',
		) ) );

		$checkout_page_id = get_option('commercioo_Checkout_page_id');
		if ( defined( 'ELEMENTOR_VERSION' ) && \Elementor\Plugin::$instance->documents->get( $checkout_page_id )->is_built_with_elementor() ) {
			return;
		} else {
			// ship_to_different_address_label
			$option_key = 'comm_order_forms_settings[ship_to_different_address_label]';
			$wp_customize->add_setting( $option_key, array(
				'default' 	 => 'Ship To Different Address',
				'type' 		 => 'option', // you can also use 'theme_mod'
				'capability' => 'edit_theme_options'
			) );
			$wp_customize->add_control( new WP_Customize_Control( $wp_customize, $option_key, array(
				'label'    => __( 'Ship to Different Address Label', 'commercioo' ),
				'settings' => $option_key,
				'section'  => 'commercioo_customize_checkout_other_settings',
				'type'	   => 'text',
			) ) );
	
			// ship_to_different_address_visibility
			$option_key = 'comm_order_forms_settings[ship_to_different_address_visibility]';
			$wp_customize->add_setting( $option_key, array(
				'default' 	 => true,
				'type' 		 => 'option', // you can also use 'theme_mod'
				'capability' => 'edit_theme_options'
			) );
			$wp_customize->add_control( new WP_Customize_Control( $wp_customize, $option_key, array(
				'label'    => __( 'Show Ship to Different Address Option', 'commercioo' ),
				'settings' => $option_key,
				'section'  => 'commercioo_customize_checkout_other_settings',
				'type'	   => 'checkbox',
			) ) );
	
			// order_note_label
			$option_key = 'comm_order_forms_settings[order_note_label]';
			$wp_customize->add_setting( $option_key, array(
				'default' 	 => 'Order Notes',
				'type' 		 => 'option', // you can also use 'theme_mod'
				'capability' => 'edit_theme_options'
			) );
			$wp_customize->add_control( new WP_Customize_Control( $wp_customize, $option_key, array(
				'label'    => __( 'Order Note Label', 'commercioo' ),
				'settings' => $option_key,
				'section'  => 'commercioo_customize_checkout_other_settings',
				'type'	   => 'text',
			) ) );	
	
			// order_note_visibility
			$option_key = 'comm_order_forms_settings[order_note_visibility]';
			$wp_customize->add_setting( $option_key, array(
				'default' 	 => 'visible',
				'type' 		 => 'option', // you can also use 'theme_mod'
				'capability' => 'edit_theme_options'
			) );
			$wp_customize->add_control( new WP_Customize_Control( $wp_customize, $option_key, array(
				'label'    => __( 'Show Order Note Option', 'commercioo' ),
				'settings' => $option_key,
				'section'  => 'commercioo_customize_checkout_other_settings',
				'type'	   => 'checkbox',
			) ) );	
		}
	}

	/**
	 * This hook will modify the customizer url on frontend checkout page
	 * to become auto-open the checkout panel on click
	 */
	public function admin_bar_checkout_customizer_url( $wp_admin_bar ) {
		$checkout_page_id = get_option( 'commercioo_Checkout_page_id' );
		$current_page_id  = get_queried_object_id();
		
		// must be on the checkout page
		if ( intval( $checkout_page_id ) !== $current_page_id ) return;

		// get all nodes then do iterate
		$all_nodes = $wp_admin_bar->get_nodes();

        foreach( $all_nodes as $key => $val ) {
			if ( $key !== 'customize' ) continue;

			// get desired node then remove it from the record
            $current_node = $all_nodes[ $key ];
            $wp_admin_bar->remove_node( $key );

			// edit the href key
			$current_node->href .= '&autofocus[panel]=commercioo_customize_checkout_settings';
			
			// re add the node
            $wp_admin_bar->add_node( $current_node );
        }
	}

	/**
	 * Preview the checkout page on accessing the checkout section
	 * 
	 * @since    0.2.3
	 */
	public function customize_controls_print_scripts() {
		?>
		<script type='text/javascript'>
			jQuery( function( $ ) {
				wp.customize.panel( 'commercioo_customize_checkout_settings', function( section ) {
					section.expanded.bind( function( isExpanded ) {
						if ( isExpanded ) {
							wp.customize.previewer.previewUrl.set( '<?php echo esc_js( comm_get_checkout_uri() ) ?>' );
						} else {
							wp.customize.previewer.previewUrl.set( '<?php echo esc_js( home_url() ) ?>' );
						}
					} )
				} );
			} );
		</script>
		<?php
	}
}
