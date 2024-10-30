<?php
/**
 * Commercioo Helper
 * The static methods of helpers
 *
 * @author Commercioo_Team
 * @package Commercioo
 */

namespace Commercioo;

if ( ! defined( 'WPINC' ) ) {
	exit;
}

if ( ! class_exists( 'Commercioo\Helper' ) ) {

	/**
	 * Class Helpers
	 *
	 * @package Commercioo
	 */
	Class Helper {

		/**
		 * Get store logo url
		 * 
		 * @return mixed string|null
		 */
		public static function store_logo_url() {
			$settings   = get_option( 'comm_general_settings', array() );
			$store_logo = null;

			if ( isset( $settings['store_logo'] ) && intval( $settings['store_logo'] ) > 0 ) {				
				$thumb_id = intval( $settings['store_logo'] );
				$thumb    = wp_get_attachment_image_src( $thumb_id, 'medium' );

				if ( $thumb ) {
					$store_logo = $thumb[0];
				}
			}

			return $store_logo;
		}

		/**
		 * Get currency pattern
		 * 
		 * @return array
		 */
		public static function currency_pattern() {
			$settings = get_option( 'comm_general_settings', array() );
			
			return array(
				'prefix'        => $settings['currency_position'] == 'prefix' ? $settings['currency_symbol'] : '',
				'suffix'        => $settings['currency_position'] == 'suffix' ? $settings['currency_symbol'] : '',
				'position'      => $settings['currency_position'] == 'suffix' ? $settings['currency_symbol'] : 'prefix',
				'thousand'      => isset( $settings['currency_thousand'] ) ? $settings['currency_thousand'] : '.',
				'decimal'       => isset( $settings['currency_decimal'] ) ? $settings['currency_decimal'] : ',',
				'decimal_limit' => isset( $settings['currency_decimal_limit'] ) ? $settings['currency_decimal_limit'] : '0',
			);
		}

		/**
		 * Get formatted currency
		 * 
		 * @param  mixed string|int
		 * @return string
		 */
		public static function formatted_currency( $number ) {
			$pattern       = self::currency_pattern();
			$number        = floatval( $number );
			$prefix        = $pattern['prefix'];
			$suffix        = $pattern['suffix'];
			$decimal       = $pattern['decimal'];
			$decimal_limit = intval( $pattern['decimal_limit'] );
			$thousand      = $pattern['thousand'];
			$currency      = number_format( $number, $decimal_limit, $decimal, $thousand );

			if ( null != $suffix ) {
				return sprintf( '%s %s', $currency, $suffix );
			}			
			else {
				return sprintf( '%s %s', $prefix, $currency );
			}
		}

		/**
		 * Get payment method label
		 * 
		 * @param  string
		 * @return string
		 */
		public static function payment_method_label( $method ) {
			$methods = array( 
				'bacs' => 'Direct Bank Transfer',
			);

			return isset( $methods[ $method ] ) ? $methods[ $method ] : null;
		}

		/**
		 * Get frontend button labels
		 * 
		 * @param    string    $type
		 * @return   string
		 */
		public static function button_label( $type = null ) {
			global $comm_options;
			$options = self::get_options();
			$default_options = self::get_default_options();
			switch ($type) {
				case 'buy_now':
					$key = 'buy_now_button_label';
					if ($options[$key]) {
						$label = $options[$key];
					} else {
						if (empty($options[$key])) {
							$label = $default_options['buy_now_button_label'];
						} else {
							return null;
						}
					}
					break;
				case 'add_to_cart':
					$key = 'add_to_cart_button_label';
					if ($options[$key]) {
						$label = $options[$key];
					} else {
						if (empty($options[$key])) {
							$label = $default_options['add_to_cart_button_label'];
						} else {
							return null;
						}
					}
					break;
		
				default:
					$label = null;
					break;
			}
			return apply_filters('commercioo_button_labels', $label, $type);
		}

		/**
		 * Get buttons options
		 *
		 * @return   array
		 * @since    0.0.1
		 */
		public static function get_options() {
			$options = get_option('comm_misc_settings', array());
			$options = array_merge(self::get_default_options(), $options);
			return $options;
		}

		/**
		 * Get default buttons options
		 *
		 * @return   array
		 * @since    0.0.1
		 */
		public static function get_default_options()
		{
			return array(
				'buy_now_button_label' => __('Buy Now'),
				'add_to_cart_button_label' => __('Add to Cart'),
			);
		}

        /**
         * Get endpoint URL.
         *
         * Gets the URL for an endpoint, which varies depending on permalink settings.
         *
         * @since 0.3.8
         * @param  string $endpoint  Endpoint slug.
         * @param  string $value     Query param value.
         * @param  string $permalink Permalink.
         *
         * @return string
         */
        public static function commercioo_get_endpoint_url( $endpoint, $value = '', $permalink = '' ) {
            if ( ! $permalink ) {
                $permalink = get_permalink();
            }

            // Map endpoint to options.
            $query_vars =  \Commercioo\Query\Commercioo_Query::get_instance()->get_query_vars();
            $endpoint   = ! empty( $query_vars[ $endpoint ] ) ? $query_vars[ $endpoint ] : $endpoint;

            if ( get_option( 'permalink_structure' ) ) {
                if ( strstr( $permalink, '?' ) ) {
                    $query_string = '?' . wp_parse_url( $permalink, PHP_URL_QUERY );
                    $permalink    = current( explode( '?', $permalink ) );
                } else {
                    $query_string = '';
                }
                $url = trailingslashit( $permalink );

                if ( $value ) {
                    $url .= trailingslashit( $endpoint ) . user_trailingslashit( $value );
                } else {
                    $url .= user_trailingslashit( $endpoint );
                }

                $url .= $query_string;
            } else {
                $url = add_query_arg( $endpoint, $value, $permalink );
            }

            return apply_filters( 'commercioo_get_endpoint_url', $url, $endpoint, $value, $permalink );
        }
        /**
         * Generate an order key with prefix.
         *
         * @since 0.3.8
         * @param string $key Order key without a prefix. By default generates a 13 digit secret.
         * @return string The order key.
         */
        public static function commercioo_generate_order_key( $key = '' ) {
            if ( '' === $key ) {
                $key = wp_generate_password( 13, false );
            }

            return 'commercioo_' . apply_filters( 'commercioo_generate_order_key', 'order_' . $key );
        }
        /**
         * Defined menu sub tabs payment settings and .
         *
         * @since 0.3.8
         * @return array The order key.
         */
        public static function comm_registered_payment_sub_tab_menu_settings(){
            global $comm_options;
            $bank_transfer_status= isset($comm_options['payment_option']['bacs']) ? 'active' : '';
            $paypal_status= isset($comm_options['payment_option']['paypal']) ? 'active' : '';
            $gn[] = array(
                array(
                    'label'   => __('General Settings', 'commercioo' ),
                    'target'   => 'general-payment',
                    'is_active'=> (has_action("comm_content_general_payment_setting"))?'active':'',
                    'is_tab'   => true,
                    'icon'   => '',
                    "content"=> array( 'content' => (has_action("comm_content_general_payment_setting"))?apply_filters( "comm_payment_general_setting", '' ):'')
                ));
            $bt[]=array(
                array(
                    'label'   => __('Bank Transfer', 'commercioo' ),
                    'target'   => 'bank-transfer-payment',
                    'is_active'=> (!has_action("comm_content_general_payment_setting"))?'active':'',
                    'is_tab'   => true,
                    'icon'   => '<i class="fa fa-check-circle commerioo-icon-sbu-tabs bacs '.$bank_transfer_status.'" aria-hidden="true"></i>',
                    "content"=> array( 'content' => apply_filters( "comm_bank_transfer_setting", '' ))
                ));
            $paypal[]=array(
                array(
                    'label'   => __('Paypal Standard', 'commercioo' ),
                    'target'   => 'paypal-payment',
                    'is_active'=> '',
                    'is_tab'   => true,
                    'icon'   => '<i class="fa fa-check-circle commerioo-icon-sbu-tabs paypal '.$paypal_status.'" aria-hidden="true"></i>',
                    "content"=> array( 'content' => apply_filters( "comm_paypal_setting", '' ))
                ));
            $settings = array_merge($gn,$bt,$paypal);

            $settings= apply_filters('commercioo_settings_sub_tab_sections_general',$settings);
            return $settings;
        }
	}
}