<?php
namespace commercioo\admin;
Class Comm_Users {
	// instance
	private static $instance;

	// getInstance
	public static function get_instance() {
		if( ! isset( self::$instance ) ) {
			self::$instance = new self;
		}

		return self::$instance;
	}
	
	// __construct
	private function __construct() {
	}

	public function comm_insert_user_order( $request ) {		
		$user_id 	   	  = sanitize_text_field($request->get_param( 'user_id' ));
        $billing_address  = sanitize_post($request->get_param( 'billing_address' ));
        $shipping_address = sanitize_text_field($request->get_param( 'shipping_address' ));
        $user_by_email 	  = sanitize_email($billing_address['billing_email']);
		
        if ( $user_id ) {
            $user_data = get_user_by( 'id', $user_id );
		}
		elseif ( is_numeric( $user_by_email ) && !$user_id ) {
            $user_data = get_user_by( 'id', $user_by_email );
		}
		else {
            $user_data = get_user_by( 'email', $user_by_email );
        }

		//  If user by email not found, then we should create it.
        if ( ! $user_data ) {
            $random_password = wp_generate_password( $length = 12, $include_standard_special_chars = false );
            $user_id 		 = wp_create_user( $user_by_email, $random_password, $user_by_email );

			// make sure we have successfully created a new user
            if ( is_wp_error( $user_id ) ) {
                return new \WP_Error( 'comm_error', __( 'Error! cannot create user', 'commercioo' ), array( 'status' => 404 ) );
			}
			else {				
				// update role
				wp_update_user( array( 
					'ID'   => $user_id, 
					'role' => 'comm_customer' 
				) );
				
				// get user data
				$user_data = get_user_by( 'id', $user_id );

				 // sending email notification new user
            	$this->wp_new_user_notification( $user_id, $random_password );
			}
		}
		
		// get user email then add param billing_email into post data.
		$request->set_param( 'user_id', $user_data->ID );
		$request->set_param( 'billing_email', $user_data->user_email );

		// update user's profile
		$this->update_users_billing_and_shipping_address( $user_id, $billing_address, $shipping_address );

		// update_user_meta( $user_id, 'comm_billing_email', $request->get_param( 'billing_email' ) ); // add the meta
		// $display_billing_first_name = '';
		// $display_billing_last_name = '';
		// $billing_address = $request->get_param('billing_address');
		
		// foreach ( $billing_address as $bak => $billing_address_val ) {
		// 	update_user_meta( $user_id, sprintf( "comm_%s", $bak ), $billing_address_val); // add the meta
		// 	if($bak=="billing_first_name"){
		// 		$display_billing_first_name = $billing_address_val;
		// 	}
		// 	if($bak=="billing_last_name"){
		// 		$display_billing_last_name = $billing_address_val;
		// 	}
		// 	if($display_billing_first_name && $display_billing_last_name){
		// 		$display_name = $display_billing_first_name." ".$display_billing_last_name;
		// 		wp_update_user( array ('ID' => $user_id, 'display_name' => $display_name));
		// 	}

		// }

		// $shipping_address = $request->get_param('shipping_address');
		// foreach ($shipping_address as $sak => $shipping_address_val){
		// 	if($sak !=="shipping_customer_note") {
		// 		update_user_meta($user_id, sprintf("comm_%s", $sak), $shipping_address_val); // add the meta
		// 	}
		// }
	}
	
	function update_users_billing_and_shipping_address( $user_id, $billing_address, $shipping_address = null ) {
		// update user's first name
		if ( isset( $billing_address['billing_first_name'] ) && trim( $billing_address['billing_first_name'] ) != '' ) {
			wp_update_user( array(
				'ID' 		   => $user_id, 
				'display_name' => $billing_address['billing_first_name'], 
			) );

			update_user_meta( $user_id, 'first_name', $billing_address['billing_first_name'] );
		}

		// update user's last name
		if ( isset( $billing_address['billing_last_name'] ) && trim( $billing_address['billing_last_name'] ) != '' ) {
			update_user_meta( $user_id, 'last_name', $billing_address['billing_last_name'] );
		}
		
		// update user's billing address
		foreach ( $billing_address as $key => $value ) {
			update_user_meta( $user_id, 'comm_' . $key, $value );
		}

		// update user's shipping address
		if ( $shipping_address ) {
			foreach ( $shipping_address as $key => $value ) {
				update_user_meta( $user_id, 'comm_' . $key, $value );
			}
		}
	}

    function wp_new_user_notification( $user_id, $plaintext_pass = '' ) {
        $user = new \WP_User($user_id);

        $user_login = stripslashes($user->user_login);
        $user_email = stripslashes($user->user_email);

        $blogname = wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES );

        $message  = sprintf(__('New user registration on your blog %s:'), get_option('blogname')) . "rnrn";
        $message .= sprintf(__('Username: %s'), $user_login) . "rnrn";
        $message .= sprintf(__('E-mail: %s'), $user_email) . "rn";

        @wp_mail(get_option('admin_email'), sprintf(__('[%s] New User Registration'), get_option('blogname')), $message);

        if ( empty($plaintext_pass) )
            return;


        // subject line
        $subject = apply_filters( 'comm_auto_register_email_subject', sprintf( __( '[%s] Your username and password',
            'commercioo' ), $blogname ) );

        // get from name and email from EDD options
        $from_name  = get_option( 'from_name', get_bloginfo( 'name' ) );
        $from_email = get_option( 'from_email', get_bloginfo( 'admin_email' ) );

        $headers = "From: " . stripslashes_deep( html_entity_decode( $from_name, ENT_COMPAT, 'UTF-8' ) ) . " <$from_email>\r\n";
        $headers .= "Reply-To: ". $from_email . "\r\n";
        $headers = apply_filters( 'comm_auto_register_headers', $headers );

        $message  = __('Hi there') . ",\n\n";
        $message .= sprintf(__("Welcome to %s! Here's how to log in:"), get_option('blogname')) . "\n\n";
        $message .= wp_login_url() . "\n\n";
        $message .= sprintf(__('Username: %s'), $user_login) . "\n\n";
        $message .= sprintf(__('Password: %s'), $plaintext_pass) . "\n\n";
        $message .= sprintf(__('If you have any problems, please contact me at %s.'), get_option('admin_email')) . "\r\n";

        wp_mail($user_email, $subject, $message, $headers);

    }

    public function get_customer_meta_fields() {
	    global $comm_country;
        $show_fields = apply_filters(
            'commercioo_customer_meta_fields',
            array(
                'billing'  => array(
                    'title'  => __( 'Commercioo Customer billing address', 'commercioo' ),
                    'fields' => array(
                        'comm_billing_first_name' => array(
                            'label'       => __( 'First name', 'commercioo' ),
                            'description' => '',
                        ),
                        'comm_billing_last_name'  => array(
                            'label'       => __( 'Last name', 'commercioo' ),
                            'description' => '',
                        ),
                        'comm_billing_company'    => array(
                            'label'       => __( 'Company', 'commercioo' ),
                            'description' => '',
                        ),
                        'comm_billing_address'  => array(
                            'label'       => __( 'Address', 'commercioo' ),
                            'description' => '',
                        ),
                        'comm_billing_city'       => array(
                            'label'       => __( 'City', 'commercioo' ),
                            'description' => '',
                        ),
                        'comm_billing_postcode'   => array(
                            'label'       => __( 'Postcode / ZIP', 'commercioo' ),
                            'description' => '',
                        ),
                        'comm_billing_country'    => array(
                            'label'       => __( 'Country / Region', 'commercioo' ),
                            'description' => '',
                            'class'       => 'js_field-country',
                            'type'        => 'select',
                            'options'     => array( '' => __( 'Select a country / region&hellip;', 'commercioo' ) ) + $comm_country,
                        ),
                        'comm_billing_state'      => array(
                            'label'       => __( 'State / County', 'commercioo' ),
                            'description' => __( 'State / County or state code', 'commercioo' ),
                            'class'       => 'js_field-state',
                        ),
                        'comm_billing_phone'      => array(
                            'label'       => __( 'Phone', 'commercioo' ),
                            'description' => '',
                        ),
                        'comm_billing_email'      => array(
                            'label'       => __( 'Email address', 'commercioo' ),
                            'description' => '',
                        ),
                    ),
                ),
                'shipping' => array(
                    'title'  => __( 'Commercioo Customer shipping address', 'commercioo' ),
                    'fields' => array(
                        'copy_billing'        => array(
                            'label'       => __( 'Copy from billing address', 'commercioo' ),
                            'description' => '',
                            'class'       => 'comm-js_copy-billing',
                            'type'        => 'button',
                            'text'        => __( 'Copy', 'commercioo' ),
                        ),
                        'comm_shipping_first_name' => array(
                            'label'       => __( 'First name', 'commercioo' ),
                            'description' => '',
                        ),
                        'comm_shipping_last_name'  => array(
                            'label'       => __( 'Last name', 'commercioo' ),
                            'description' => '',
                        ),
                        'comm_shipping_company'    => array(
                            'label'       => __( 'Company', 'commercioo' ),
                            'description' => '',
                        ),
                        'comm_shipping_address'  => array(
                            'label'       => __( 'Address line', 'commercioo' ),
                            'description' => '',
                        ),
                        'comm_shipping_city'       => array(
                            'label'       => __( 'City', 'commercioo' ),
                            'description' => '',
                        ),
                        'comm_shipping_postcode'   => array(
                            'label'       => __( 'Postcode / ZIP', 'commercioo' ),
                            'description' => '',
                        ),
                        'comm_shipping_country'    => array(
                            'label'       => __( 'Country / Region', 'commercioo' ),
                            'description' => '',
                            'class'       => 'js_field-country',
                            'type'        => 'select',
                            'options'     => array( '' => __( 'Select a country / region&hellip;', 'commercioo' ) ) + $comm_country,
                        ),
                        'comm_shipping_state'      => array(
                            'label'       => __( 'State / County', 'commercioo' ),
                            'description' => __( 'State / County or state code', 'commercioo' ),
                            'class'       => 'js_field-state',
                        ),
                    ),
                ),
            )
        );
        return $show_fields;
    }
    public function add_customer_meta_fields( $user ) {
        if ( ! apply_filters( 'commercioo_current_user_can_edit_customer_meta_fields', current_user_can( 'manage_commercioo' ), $user->ID ) ) {
            return;
        }

        $show_fields = $this->get_customer_meta_fields();

        foreach ( $show_fields as $fieldset_key => $fieldset ) :
            ?>
            <h2><?php echo esc_attr($fieldset['title']); ?></h2>
            <table class="form-table" id="<?php echo esc_attr( 'fieldset-' . $fieldset_key ); ?>">
                <?php foreach ( $fieldset['fields'] as $key => $field ) : ?>
                    <tr>
                        <th>
                            <label for="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $field['label'] ); ?></label>
                        </th>
                        <td>
                            <?php if ( ! empty( $field['type'] ) && 'select' === $field['type'] ) : ?>
                                <select name="<?php echo esc_attr( $key ); ?>" id="<?php echo esc_attr( $key ); ?>" class="<?php echo esc_attr( $field['class'] ); ?>" style="width: 25em;">
                                    <?php
                                    $selected = esc_attr( get_user_meta( $user->ID, $key, true ) );
                                    foreach ( $field['options'] as $option_key => $option_value ) :
                                        ?>
                                        <option value="<?php echo esc_attr( $option_key ); ?>" <?php selected( $selected, $option_key, true ); ?>><?php echo esc_html( $option_value ); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            <?php elseif ( ! empty( $field['type'] ) && 'checkbox' === $field['type'] ) : ?>
                                <input type="checkbox" name="<?php echo esc_attr( $key ); ?>" id="<?php echo esc_attr( $key ); ?>" value="1" class="<?php echo esc_attr( $field['class'] ); ?>" <?php checked( (int) get_user_meta( $user->ID, $key, true ), 1, true ); ?> />
                            <?php elseif ( ! empty( $field['type'] ) && 'button' === $field['type'] ) : ?>
                                <button type="button" id="<?php echo esc_attr( $key ); ?>" class="button <?php echo esc_attr( $field['class'] ); ?>"><?php echo esc_html( $field['text'] ); ?></button>
                            <?php else : ?>
                                <input type="text" name="<?php echo esc_attr( $key ); ?>" id="<?php echo esc_attr( $key ); ?>" value="<?php echo esc_attr( $this->get_user_meta( $user->ID, $key ) ); ?>" class="<?php echo esc_attr( ! empty( $field['class'] ) ? esc_attr( $field['class'] ) : 'regular-text' ); ?>" />
                            <?php endif; ?>
                            <p class="description"><?php echo wp_kses_post( $field['description'] ); ?></p>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </table>
        <?php
        endforeach;
    }

    public function save_customer_meta_fields( $user_id ) {
        if ( ! apply_filters( 'commercioo_current_user_can_edit_customer_meta_fields', current_user_can( 'manage_commercioo' ), $user_id ) ) {
            return;
        }

        $save_fields = $this->get_customer_meta_fields();

        foreach ( $save_fields as $fieldset ) {

            foreach ( $fieldset['fields'] as $key => $field ) {

                if ( isset( $field['type'] ) && 'checkbox' === $field['type'] ) {
                    update_user_meta( $user_id, $key, isset( $_POST[ $key ] ) );
                } elseif ( isset( $_POST[ $key ] ) ) {
                    $val  = sanitize_text_field(is_scalar( $_POST[ $key ] ) ?  $_POST[ $key ]: '');
                    update_user_meta( $user_id, $key, $val );
                }
            }
        }
    }

    protected function get_user_meta( $user_id, $key ) {
        $value           = get_user_meta( $user_id, $key, true );
        $existing_fields = array( 'billing_first_name', 'billing_last_name' );
        if ( ! $value && in_array( $key, $existing_fields ) ) {
            $value = get_user_meta( $user_id, str_replace( 'billing_', '', $key ), true );
        } elseif ( ! $value && ( 'billing_email' === $key ) ) {
            $user  = get_userdata( $user_id );
            $value = $user->user_email;
        }

        return $value;
    }

	public function register_rest_fields() {
		/**
		 * Field type reference: 
		 * https://core.trac.wordpress.org/browser/tags/5.4/src/wp-includes/rest-api.php#L116
		 */
		$fields = array(
			'billing_address' => 'object',
			'shipping_address' => 'object',
		);

		// iterate to register the fields
		foreach ( $fields as $field_name => $type ) {
			register_rest_field( 'user', $field_name, array(
	           	'get_callback' => array( $this, 'get_rest_field' ),
				'update_callback' => array( $this, 'update_rest_field' ),
	        	'schema' => array( 'type' => $type ),
	        ) );
		}
	}

	public function get_rest_field( $array_data, $field_name ) {
		$user_id = $array_data[ 'id' ];
		$commercioo_field_name = sprintf( "comm_%s", $field_name );

		// get by field_name
		switch ( $field_name ) {
			case 'billing_address':
				$field = get_user_meta( $user_id, $commercioo_field_name, true );
				$field_value = $field;
				break;

			case 'shipping_address':
				$field = get_user_meta( $user_id, $commercioo_field_name, true );
				$field_value = $field;
				break;

			default:
				$field_value = null;
				break;
		}

		return $field_value;
	}

	public function update_rest_field( $field_value, $object_data, $field_name ) {
		$user_id = $object_data->ID;
		$commercioo_field_name = sprintf( "comm_%s", $field_name );

		// get by field_name
		switch ( $field_name ) {
			case 'billing_address':
				$value = array_map( 'esc_html', $field_value );
				update_user_meta( $user_id, $commercioo_field_name, $value );
				break;

			case 'shipping_address':
				$value = array_map( 'esc_html', $field_value );
				update_user_meta( $user_id, $commercioo_field_name, $value );
				break;

			default:
				// silent is golden
				break;
		}
	}
}

//Comm_Users::getInstance();