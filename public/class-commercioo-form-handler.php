<?php
/**
 * Handle frontend forms
 *
 * @version 1.0.0
 * @package Commercioo\public
 */

defined( 'ABSPATH' ) || exit;

class Commercioo_Form_Handler {

	/**
	 * Hook in methods.
	 */
	public static function init() {
		add_action( 'template_redirect', array( __CLASS__, 'update_account' ) );
		add_action( 'template_redirect', array( __CLASS__, 'update_address' ) );
		add_action( 'template_redirect', array( __CLASS__, 'customer_logout' )  );
		add_action( 'template_redirect', array( __CLASS__, 'process_login' ), 20 );
		add_action( 'template_redirect', array( __CLASS__, 'process_registration' ), 20 );
		add_action( 'template_redirect', array( __CLASS__, 'process_forgot_password' ), 20 );
		add_action( 'template_redirect', array( __CLASS__, 'process_reset_password' ), 20 );
	}

	/**
	 * Save the password/account details and redirect back to the my account page.
	 */
	public static function update_account() {
		if ( ! isset( $_REQUEST['comm-action-nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['comm-action-nonce'] ) ), 'update_account' ) ) {
			return;
		}

		if ( empty( $_POST['action'] ) || 'update_account' !== $_POST['action'] ) {
			return;
		}

		$user_id = get_current_user_id();

		if ( $user_id <= 0 ) {
			return;
		}

		$account_first_name   = sanitize_text_field(! empty( $_POST['account_first_name'] ) ?  wp_unslash( $_POST['account_first_name'] ) : '');
		$account_last_name    = sanitize_text_field(! empty( $_POST['account_last_name'] ) ? wp_unslash( $_POST['account_last_name'] ) : '');
		$account_display_name = sanitize_text_field(! empty( $_POST['account_display_name'] ) ?  wp_unslash( $_POST['account_display_name'] ) : '');
		$account_email        = sanitize_email(! empty( $_POST['account_email'] ) ?  wp_unslash( $_POST['account_email'] ) : '');
		$pass_cur             = sanitize_text_field(! empty( $_POST['password_current'] ) ? $_POST['password_current'] : ''); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.MissingUnslash
		$pass1                = sanitize_text_field(! empty( $_POST['password_1'] ) ? $_POST['password_1'] : ''); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.MissingUnslash
		$pass2                = sanitize_text_field(! empty( $_POST['password_2'] ) ? $_POST['password_2']: ''); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.MissingUnslash
		$save_pass            = true;

		// Current user data.
		$current_user       = get_user_by( 'id', $user_id );
		$current_first_name = $current_user->first_name;
		$current_last_name  = $current_user->last_name;
		$current_email      = $current_user->user_email;

		// New user data.
		$user               = new stdClass();
		$user->ID           = $user_id;
		$user->first_name   = $account_first_name;
		$user->last_name    = $account_last_name;
		$user->display_name = $account_display_name;

		// Prevent display name to be changed to email.
		if ( is_email( $account_display_name ) ) {
			comm_add_notice(__('Display name cannot be changed to email address due to privacy concern.', 'commercioo'), 'error');
		}

		// Handle required fields.
		$required_fields = apply_filters(
			'comm_account_required_fields',
			array(
				'account_first_name'   => __( 'First name', 'commercioo' ),
				'account_last_name'    => __( 'Last name', 'commercioo' ),
				'account_display_name' => __( 'Display name', 'commercioo' ),
				'account_email'        => __( 'Email address', 'commercioo' ),
			)
		);

		foreach ( $required_fields as $field_key => $field_name ) {
			if ( empty( $_POST[ $field_key ] ) ) {
				/* translators: %s: Field name. */
				comm_add_notice(sprintf(__('%s is a required field.', 'commercioo'), '<strong>' . esc_html($field_name) . '</strong>'), 'error');
			}
		}

		if ( $account_email ) {
			$account_email = sanitize_email( $account_email );
			if ( ! is_email( $account_email ) ) {
				comm_add_notice(__('Please provide a valid email address.', 'commercioo'), 'error');
			} elseif ( email_exists( $account_email ) && $account_email !== $current_user->user_email ) {
				comm_add_notice(__('This email address is already registered.', 'commercioo'), 'error');
			}
			$user->user_email = $account_email;
		}

		if ( ! empty( $pass_cur ) && empty( $pass1 ) && empty( $pass2 ) ) {
			comm_add_notice(__('Please fill out all password fields.', 'commercioo'), 'error');
			$save_pass = false;
		} elseif ( ! empty( $pass1 ) && empty( $pass_cur ) ) {
			comm_add_notice(__('Please enter your current password.', 'commercioo'), 'error');
			$save_pass = false;
		} elseif ( ! empty( $pass1 ) && empty( $pass2 ) ) {
			comm_add_notice(__('Please re-enter your password.', 'commercioo'), 'error');
			$save_pass = false;
		} elseif ( ( ! empty( $pass1 ) || ! empty( $pass2 ) ) && $pass1 !== $pass2 ) {
			comm_add_notice(__('New passwords do not match.', 'commercioo'), 'error');
			$save_pass = false;
		} elseif ( ! empty( $pass1 ) && ! wp_check_password( $pass_cur, $current_user->user_pass, $current_user->ID ) ) {
			comm_add_notice(__('Your current password is incorrect.', 'commercioo'), 'error');
			$save_pass = false;
		}

		if ( $pass1 && $save_pass ) {
			$user->user_pass = $pass1;
		}

		if ( comm_notice_count( 'error' ) === 0 ) {
			wp_update_user( $user );
			
			update_user_meta( $user->ID, 'comm_billing_email', $user->user_email );
			update_user_meta( $user->ID, 'comm_billing_first_name', $user->first_name );
			update_user_meta( $user->ID, 'comm_billing_last_name', $user->last_name );

			comm_add_notice(__( 'Account details changed successfully.', 'commercioo' ));

			do_action( 'comm_update_account_details', $user->ID );

			// wp_safe_redirect( isset( $_REQUEST['_wp_http_referer'] ) ? esc_url( $_REQUEST['_wp_http_referer'] ) : comm_get_account_uri() );
			// exit;
		}
	}

	/**
	 * Save and and update a billing or shipping address if the
	 * form was submitted through the user account page.
	 */
	public static function update_address() {
		global $wp;

		if ( ! isset( $_REQUEST['comm-action-nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['comm-action-nonce'] ) ), 'update_address' ) ) {
			return;
		}

		if ( empty( $_POST['action'] ) || 'update_address' !== $_POST['action'] ) {
			return;
		}

		$user_id = get_current_user_id();

		if ( $user_id <= 0 ) {
			return;
		}

		$customer = new \Commercioo\Models\Customer( $user_id );

		if ( ! $customer ) {
			return;
		}

		$load_address = isset( $wp->query_vars['addresses'] ) ? sanitize_title( $wp->query_vars['addresses'] ) : 'billing';

		if ( ! isset( $_POST[ 'country' ] ) ) {
			return;
		}

		$checkout = Commercioo\Checkout::get_instance();
		$fields   = $checkout->get_default_fields( $load_address );
		$required = array( 'first_name', 'last_name', 'country', 'state', 'city', 'street_address', 'zip', 'phone', 'email' );
		$values  = array();

		foreach ( $fields as $key => $field ) {

			// Get Value.
			$value = sanitize_text_field(isset( $_POST[ $key ] ) ?  wp_unslash( $_POST[ $key ] ) : '');

			// Validation: Required fields  .
			if ( $field['required'] && empty( $value ) ) {
				comm_add_notice(sprintf(__('%s is a required field.', 'commercioo'), $key), 'error');
			}

			if ( ! empty( $value ) ) {
				switch ( $key ) {
					case 'zip':
						if ( strlen( trim( preg_replace( '/[\s\-A-Za-z0-9]/', '', $value ) ) ) > 0 ) {
							comm_add_notice(__('Please enter a valid postcode / ZIP.', 'commercioo'), 'error');
						}
						break;
					case 'phone':
						if ( 0 < strlen( trim( preg_replace( '/[\s\#0-9_\-\+\/\(\)\.]/', '', $value ) ) ) ) {
							comm_add_notice(sprintf(__('%s is not a valid phone number.', 'commercioo'), $value), 'error');
						}
						break;
					case 'email':
						$value = strtolower( $value );

						if ( ! is_email( $value ) ) {
							/* translators: %s: Email address. */
							comm_add_notice(sprintf(__('%s is not a valid email address.', 'woocommerce'), $value), 'error');
						}
						break;
				}
			}
			$values[ $key ] = $value;
		}

		/**
		 * Hook: commercioo_after_save_address_validation.
		 *
		 * Allow developers to add custom validation logic and throw an error to prevent save.
		 *
		 * @param int         $user_id User ID being saved.
		 * @param string      $load_address Type of address e.g. billing or shipping.
		 * @param array       $address The address fields.
		 * @param WC_Customer $customer The customer object being saved. @since 3.6.0
		 */
		do_action( 'commercioo_after_save_address_validation', $user_id, $load_address, null, $customer );

		if ( 0 < comm_notice_count( 'error' ) ) {
			return;
		}

		foreach ( $values as $field => $value ) {
			update_user_meta( $user_id, 'comm_' . $load_address . '_' . $field, $value );
		}

		comm_add_notice(__( 'Address changed successfully.', 'commercioo' ));

		do_action( 'commercioo_customer_save_address', $user_id, $load_address );

		wp_safe_redirect( comm_get_account_uri( 'addresses' ) );
		exit;
	}

	/**
	 * Process the login form.
	 *
	 * @throws Exception On login error.
	 */
	public static function process_login() {

		if ( isset( $_POST['login'], $_POST['username'], $_POST['password'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['comm-action-nonce'] ) ), 'commercioo_login' ) ) {
			try {
				$creds = array(
					'user_login'    => sanitize_user(trim( wp_unslash( $_POST['username'] ) )), // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
					'user_password' => sanitize_text_field($_POST['password']), // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.MissingUnslash
					'remember'      => sanitize_text_field(isset( $_POST['rememberme'] )), // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
				);

				$validation_error = new WP_Error();
				$validation_error = apply_filters( 'commercioo_process_login_errors', $validation_error, $creds['user_login'], $creds['user_password'] );

				if ( $validation_error->get_error_code() ) {
					throw new Exception( $validation_error->get_error_message() );
				}

				if ( empty( $creds['user_login'] ) ) {
					throw new Exception( __( 'Username is required.', 'commercioo' ) );
				}

				// On multisite, ensure user exists on current site, if not add them before allowing login.
				if ( is_multisite() ) {
					$user_data = get_user_by( is_email( $creds['user_login'] ) ? 'email' : 'login', $creds['user_login'] );

					if ( $user_data && ! is_user_member_of_blog( $user_data->ID, get_current_blog_id() ) ) {
						add_user_to_blog( get_current_blog_id(), $user_data->ID, 'customer' );
					}
				}

				// Perform the login.
				$user = wp_signon( apply_filters( 'commercioo_login_credentials', $creds ), is_ssl() );
				if ( is_wp_error( $user ) ) {
					$message = $user->get_error_message();
					if ( 'incorrect_password' == $user->get_error_code() ) {
						$message = sprintf( __( 'The password you entered for %s is incorrect.', 'commercioo' ), $creds['user_login'] );
					}
					throw new Exception( $message );
				} else {

					if ( ! empty( $_POST['redirect'] ) ) {
						$redirect = wp_sanitize_redirect(wp_unslash( $_POST['redirect'] )); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
					} elseif (! empty( $_POST['_wp_http_referer'] ) ) {
						$redirect = wp_sanitize_redirect(wp_unslash( $_POST['_wp_http_referer'] )); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
					} else {
						$redirect = comm_get_account_uri();
					}

					wp_redirect( wp_validate_redirect( apply_filters( 'commercioo_login_redirect', $redirect, $user ), comm_get_account_uri() ) ); // phpcs:ignore
					exit;
				}
			} catch ( Exception $e ) {
				comm_add_notice(apply_filters('login_errors', $e->getMessage()), 'error');
				do_action( 'commercioo_login_failed' );
			}
		}
	}

	/**
	 * Process the registration form.
	 *
	 * @throws Exception On registration error.
	 */
	public static function process_registration() {
		if ( isset( $_POST['register'], $_POST['email'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['comm-action-nonce'] ) ), 'commercioo_register' ) ) {
			$username = sanitize_text_field(isset( $_POST['username'] ) ? wp_unslash( $_POST['username'] ) : ''); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			$password = sanitize_text_field(isset( $_POST['password'] ) ? $_POST['password'] : ''); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.MissingUnslash
			$email    = sanitize_email(wp_unslash( $_POST['email'])); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

			try {
				$validation_error  = new WP_Error();
				$validation_error  = apply_filters( 'commercioo_process_registration_errors', $validation_error, $username, $password, $email );
				$validation_errors = $validation_error->get_error_messages();

				if ( 1 === count( $validation_errors ) ) {
					throw new Exception( $validation_error->get_error_message() );
				} elseif ( $validation_errors ) {
					foreach ( $validation_errors as $message ) {
						comm_add_notice($message, 'error');
					}
					throw new Exception();
				}

				if ( empty( $email ) || ! is_email( $email ) ) {
					throw new Exception( __( 'Please provide a valid email address.', 'commercioo' ) );
				}

				if ( email_exists( $email ) ) {
					throw new Exception( __( 'An account is already registered with your email address. Please log in.', 'commercioo' ) );
				}

				$username = sanitize_user( $username );

				if ( empty( $username ) || ! validate_username( $username ) ) {
					throw new Exception( __( 'Please enter a valid account username.', 'commercioo' ) );
				}

				if ( username_exists( $username ) ) {
					throw new Exception( __( 'An account is already registered with that username. Please choose another.', 'commercioo' ) );
				}

				if ( empty( $password ) ) {
					throw new Exception( __( 'Please enter an account password.', 'commercioo' ) );
				}

				$new_customer_data = apply_filters(
					'commercioo_new_customer_data',
					array(
						'user_login' => $username,
						'user_pass'  => $password,
						'user_email' => $email,
						'role'       => 'comm_customer',
					)
				);

				$new_customer = wp_insert_user( $new_customer_data );

				if ( is_wp_error( $new_customer ) ) {
					throw new Exception( $new_customer->get_error_message() );
				}

				update_user_meta( $new_customer, 'comm_billing_email', $email );

				// Send welcome email immediately.
				$mailer = new \Commercioo\Emails\New_Customer( $new_customer );
				$mailer->send();

				// Send notification to admin immediately.
				$mailer = new \Commercioo\Emails\New_Customer_To_Admin( $new_customer );
				$mailer->send();

				if ( 'yes' === get_option( 'commercioo_registration_generate_password' ) ) {
					comm_add_notice(__( 'Your account was created successfully and a password has been sent to your email address.', 'commercioo' ));
				} else {
					comm_add_notice(__( 'Your account was created successfully. Your login details have been sent to your email address.', 'commercioo' ));
				}

				// Only redirect after a forced login - otherwise output a success notice.
				if ( apply_filters( 'commercioo_registration_auth_new_customer', true, $new_customer ) ) {
					wp_set_current_user( $new_customer );
					wp_set_auth_cookie( $new_customer, true );

					if ( ! empty( $_POST['redirect'] ) ) {
						$redirect = wp_sanitize_redirect( wp_unslash( $_POST['redirect'] ) ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
					} elseif ( ! empty( $_POST['_wp_http_referer'] ) ) {
						$redirect = wp_sanitize_redirect( wp_unslash( $_POST['_wp_http_referer'] ) ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
					} else {
						$redirect = comm_get_account_uri();
					}

					wp_redirect( wp_validate_redirect( apply_filters( 'commercioo_registration_redirect', $redirect ), comm_get_account_uri() ) ); //phpcs:ignore WordPress.Security.SafeRedirect.wp_redirect_wp_redirect
					exit;
				}
			} catch ( Exception $e ) {
				if ( $e->getMessage() ) {
					comm_add_notice($e->getMessage(), 'error');
				}
			}
		}
	}

	/**
	 * Process forgot password.
	 *
	 * @throws Exception On login error.
	 */
	public static function process_forgot_password() {

		if ( isset( $_POST['login'], $_POST['email'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['comm-action-nonce'] ) ), 'commercioo_forgot' ) ) {
			try {

				$user_email = sanitize_email(trim( wp_unslash( $_POST['email'] ) ));

				if ( ! is_email( $user_email ) ) {
					throw new Exception( __( 'Please insert email.', 'commercioo' ) );
				}

				$user_data = get_user_by( 'email', $user_email );

				if ( ! $user_data ) {
					throw new Exception( __( 'Email is not registered on our site.', 'commercioo' ) );
				}

				// Get password reset key (function introduced in WordPress 4.4).
				$key = get_password_reset_key( $user_data );

				// Send welcome email immediately.
				$mailer = new \Commercioo\Emails\Forgot_Password($user_data, $key);
				$mailer->send();

				comm_add_notice(__( 'Link has been sent to your email.', 'commercioo' ));

			} catch ( Exception $e ) {
				comm_add_notice(apply_filters('forgotpassword_errors', $e->getMessage()), 'error');
				do_action( 'commercioo_forgotpassword_failed' );
			}
		}
	}

	/**
	 * Process reset password.
	 *
	 * @throws Exception On login error.
	 */
	public static function process_reset_password() {

		if ( isset( $_POST['login'], $_POST['reset_key'], $_POST['reset_login'], $_POST['password_1'], $_POST['password_2'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['comm-action-nonce'] ) ), 'commercioo_reset' ) ) {
			try {

				$user = check_password_reset_key( sanitize_text_field( wp_unslash( $_POST['reset_key'] ) ), sanitize_text_field( wp_unslash( $_POST['reset_login'] ) ) );

				if ( is_wp_error( $user ) ) {
					throw new Exception( $user->get_error_message() );
				}

				if ( empty( $_POST['password_1'] ) ) {
					throw new Exception( __( 'Please enter your password.', 'commercioo' ) );
				}

				if ( sanitize_text_field( wp_unslash( $_POST['password_1'] ) ) !== sanitize_text_field( wp_unslash( $_POST['password_2'] ) ) ) {
					throw new Exception( __( 'Passwords do not match.', 'commercioo' ) );
				}

				wp_set_password( sanitize_text_field( wp_unslash( $_POST['password_1'] ) ), $user->ID );

				comm_add_notice(sprintf( __( 'Your password has been updated. <a href="%s">Click here</a> to login.', 'commercioo' ), comm_get_account_uri() ));

			} catch ( Exception $e ) {
				comm_add_notice(apply_filters('forgotpassword_errors', $e->getMessage()), 'error');
				do_action( 'commercioo_forgotpassword_failed' );
			}
		}
	}

	/**
	 * Handle customer logout
	 */
	public static function customer_logout() {
		global $wp_query, $wp;

		// Redirect to the correct logout endpoint.
		if ( isset( $wp->query_vars['logout'] ) && ! empty( $_REQUEST['_wpnonce'] ) && wp_verify_nonce( sanitize_key( $_REQUEST['_wpnonce'] ), 'customer-logout' ) ) {
			wp_safe_redirect( str_replace( '&amp;', '&', wp_logout_url( comm_get_account_uri() ) ) );
			exit;
		}
	}

}

Commercioo_Form_Handler::init();