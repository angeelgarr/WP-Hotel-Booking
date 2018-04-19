<?php

/**
 * WP Hotel Booking global functions.
 *
 * @version    2.0
 * @author     ThimPress
 * @package    WP_Hotel_Booking/Functions
 * @category   Functions
 * @author     Thimpress, leehld
 */

/**
 * Prevent loading this file directly
 */
defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'hb_get_max_capacity_of_rooms' ) ) {
	/**
	 * Get max capacity of rooms.
	 *
	 * @return mixed
	 */
	function hb_get_max_capacity_of_rooms() {
		static $max = null;
		$terms = get_terms( 'hb_room_capacity', array( 'hide_empty' => false ) );
		if ( $terms ) {
			foreach ( $terms as $term ) {
				$cap = get_term_meta( $term->term_id, 'hb_max_number_of_adults', true );
				// use term meta
				if ( ! $cap ) {
					$cap = get_option( "hb_taxonomy_capacity_{$term->term_id}" );
				}
				if ( intval( $cap ) > $max ) {
					$max = $cap;
				}
			}
		}
		if ( ! $max ) {
			global $wpdb;
			$results = $wpdb->get_results( "SELECT MAX(meta_value) as max FROM $wpdb->termmeta WHERE meta_key = 'hb_max_number_of_adults'", ARRAY_A );
			$max     = $results[0]['max'];
		}

		return apply_filters( 'get_max_capacity_of_rooms', $max );
	}
}

if ( ! function_exists( 'hb_get_min_capacity_of_rooms' ) ) {
	/**
	 * Get min capacity of rooms.
	 *
	 * @return mixed
	 */
	function hb_get_min_capacity_of_rooms() {
		static $min = null;
		$terms = get_terms( 'hb_room_capacity', array( 'hide_empty' => false ) );
		if ( $terms ) {
			foreach ( $terms as $term ) {
				$cap = get_term_meta( $term->term_id, 'hb_max_number_of_adults', true );
				//use term meta
				if ( ! $cap ) {
					$cap = get_option( "hb_taxonomy_capacity_{$term->term_id}" );
				}
				if ( intval( $cap ) < $min ) {
					$min = $cap;
				}
			}
		}
		if ( ! $min ) {
			global $wpdb;
			$results = $wpdb->get_results( "SELECT MIN(meta_value) as min FROM $wpdb->termmeta WHERE meta_key = 'hb_max_number_of_adults'", ARRAY_A );
			$min     = $results[0]['min'];
		}

		return apply_filters( 'get_min_capacity_of_rooms', $min );
	}
}

if ( ! function_exists( 'hb_get_capacity_of_rooms' ) ) {
	/**
	 * Get all capacities of rooms.
	 *
	 * @return array
	 */
	function hb_get_capacity_of_rooms() {
		$terms  = get_terms( 'hb_room_capacity', array( 'hide_empty' => false ) );
		$return = array();
		if ( $terms ) {
			foreach ( $terms as $term ) {
				$qty = get_term_meta( $term->term_id, 'hb_max_number_of_adults', true );

				// @since  1.1.2, use term meta
				if ( ! $qty ) {
					get_option( 'hb_taxonomy_capacity_' . $term->term_id );
				}
				if ( $qty ) {
					$return[ $qty ] = array(
						'value' => $term->term_id,
						'text'  => $qty
					);
				}
			}
		}

		ksort( $return );

		return $return;
	}
}

if ( ! function_exists( 'wphb_get_location_of_rooms' ) ) {
	/**
	 * Get all room locations.
	 *
	 * @return array
	 */
	function wphb_get_location_of_rooms() {
		$locations = array();
		$terms     = get_terms( 'hb_room_location' );
		foreach ( $terms as $term ) {
			$locations[ $term->term_id ] = $term->name;
		}

		return $locations;
	}
}

if ( ! function_exists( 'hb_dropdown_room_types' ) ) {
	/**
	 * List room types into drop down select.
	 *
	 * @param array $args
	 *
	 * @return string
	 */
	function hb_dropdown_room_types( $args = array() ) {
		$args = wp_parse_args(
			$args, array(
				'echo' => true
			)
		);
		ob_start();
		wp_dropdown_categories(
			array_merge( $args, array(
					'taxonomy'   => 'hb_room_type',
					'hide_empty' => false,
					'name'       => 'hb-room-types',
					'orderby'    => 'term_group',
					'echo'       => true
				)
			)
		);
		$output = ob_get_clean();

		if ( $args['echo'] ) {
			echo sprintf( '%s', $output );
		}

		return $output;
	}
}

if ( ! function_exists( 'hb_dropdown_room_capacities' ) ) {
	/**
	 * List room capacities into drop down select.
	 *
	 * @param array $args
	 *
	 * @return string
	 */
	function hb_dropdown_room_capacities( $args = array() ) {
		$args = wp_parse_args(
			$args, array(
				'echo' => true
			)
		);
		ob_start();
		wp_dropdown_categories(
			array_merge( $args, array(
					'taxonomy'   => 'hb_room_capacity',
					'hide_empty' => false,
					'name'       => 'hb-room-capacities'
				)
			)
		);

		$output = ob_get_clean();
		if ( $args['echo'] ) {
			echo sprintf( '%s', $output );
		}

		return $output;
	}
}

if ( ! function_exists( 'hb_dropdown_room_locations' ) ) {
	/**
	 * Drop down to select location.
	 *
	 * @param array $args
	 */
	function hb_dropdown_room_locations( $args = array() ) {
		$locations = wphb_get_location_of_rooms();
		$args      = wp_parse_args( $args, array(
				'name'              => 'countries',
				'selected'          => '',
				'show_option_none'  => __( 'Location', 'wp-hotel-booking' ),
				'option_none_value' => '',
				'required'          => false
			)
		);
		echo '<select name="' . $args['name'] . '"' . ( ( $args['required'] ) ? 'required' : '' ) . '>';
		if ( $args['show_option_none'] ) {
			echo '<option value="' . $args['option_none_value'] . '">' . $args['show_option_none'] . '</option>';
		}
		foreach ( $locations as $id => $name ) {
			echo '<option value="' . $id . '" ' . selected( $id == $args['selected'] ) . '>' . $name . '</option>';
		}
		echo '</select>';
	}
}

if ( ! function_exists( 'hb_dropdown_rooms' ) ) {
	/**
	 * List room into drop down select.
	 *
	 * @param array $args
	 *
	 * @return string
	 */
	function hb_dropdown_rooms( $args = array( 'selected' => '' ) ) {
		global $wpdb;
		$posts = $wpdb->get_results( $wpdb->prepare(
			"SELECT ID, post_title FROM {$wpdb->posts} WHERE `post_type` = %s AND `post_status` = %s", 'hb_room', 'publish'
		), OBJECT );

		$output                    = '<select name="hb-room" id="hb-room-select">';
		$emptySelected             = new stdClass;
		$emptySelected->ID         = '';
		$emptySelected->post_title = __( 'Select Room', 'wp-hotel-booking' );
		/* filter rooms dropdown list */
		$posts = apply_filters( 'hotel_booking_rooms_dropdown', $posts );
		$posts = array_merge( array( $emptySelected ), $posts );

		if ( $posts && is_array( $posts ) ) {
			foreach ( $posts as $key => $post ) {
				$output .= '<option value="' . $post->ID . '"' . ( $post->ID == $args['selected'] ? ' selected' : '' ) . '>' . $post->post_title . '</option>';
			}
		}
		$output .= '</select>';

		return $output;
	}
}

if ( ! function_exists( 'hb_get_room_capacities' ) ) {
	/**
	 * Get room capacities taxonomy.
	 *
	 * @param array $args
	 *
	 * @return array
	 */
	function hb_get_room_capacities( $args = array() ) {
		$args  = wp_parse_args(
			$args, array(
				'taxonomy'   => 'hb_room_capacity',
				'hide_empty' => 0,
				'orderby'    => 'term_group',
				'map_fields' => null
			)
		);
		$terms = (array) get_terms( 'hb_room_capacity', $args );
		if ( is_array( $args['map_fields'] ) ) {
			$types = array();
			foreach ( $terms as $term ) {
				$type = new stdClass();
				foreach ( $args['map_fields'] as $from => $to ) {
					if ( ! empty( $term->{$from} ) ) {
						$type->{$to} = $term->{$from};
					} else {
						$type->{$to} = null;
					}
				}
				$types[] = $type;
			}
		} else {
			$types = $terms;
		}

		return $types;
	}
}

if ( ! function_exists( 'hb_get_child_per_room' ) ) {
	/**
	 * Get list of child per each room with all available rooms
	 *
	 * @return mixed
	 */
	function hb_get_child_per_room() {
		global $wpdb;
		$query = $wpdb->prepare( "
                SELECT DISTINCT meta_value FROM {$wpdb->postmeta} pm
                INNER JOIN {$wpdb->posts} p ON p.ID = pm.post_id
                WHERE p.post_type=%s
                  AND meta_key=%s
                  AND meta_value <> 0
                ORDER BY meta_value ASC",
			'hb_room', '_hb_max_child_per_room' );

		return $wpdb->get_col( $query );
	}
}

if ( ! function_exists( 'hb_get_max_child_of_rooms' ) ) {
	/**
	 * Get list of child per each room with all available rooms.
	 *
	 * @return int|mixed
	 */
	function hb_get_max_child_of_rooms() {
		$rows = hb_get_child_per_room();
		if ( $rows ) {
			sort( $rows );

			return $rows ? end( $rows ) : - 1;
		}

		return 0;
	}
}

if ( ! function_exists( 'hb_get_children_of_rooms' ) ) {
	/**
	 * Get children of rooms.
	 *
	 * @return array
	 */
	function hb_get_children_of_rooms() {
		$children = hb_get_child_per_room();
		$return   = array();
		if ( $children ) {
			foreach ( $children as $key => $child ) {
				$return[ $key ] = array(
					'value' => $child,
					'text'  => $child
				);
			}
		}

		ksort( $return );

		return $return;
	}
}

if ( ! function_exists( 'hb_dropdown_child_per_room' ) ) {
	/**
	 * List child of room into drop down select
	 *
	 * @param array $args
	 */
	function hb_dropdown_child_per_room( $args = array() ) {
		$args      = wp_parse_args(
			$args, array(
				'name'     => '',
				'selected' => ''
			)
		);
		$max_child = hb_get_max_child_of_rooms();
		$output    = '<select name="' . $args['name'] . '">';
		$output    .= '<option value="0">' . __( 'Select', 'wp-hotel-booking' ) . '</option>';
		if ( $max_child > 0 ) {
			for ( $i = 1; $i <= $max_child; $i ++ ) {
				$output .= sprintf( '<option value="%1$d"%2$s>%1$d</option>', $i, $args['selected'] == $i ? ' selected="selected"' : '' );
			}
		}
		$output .= '</select>';
		echo sprintf( '%s', $output );
	}
}

if ( ! function_exists( 'hb_get_room_type_capacities' ) ) {
	/**
	 * Get capacity of a room type.
	 *
	 * @param $type_id
	 *
	 * @return int
	 */
	function hb_get_room_type_capacities( $type_id ) {
		return intval( get_option( "hb_taxonomy_capacity_{$type_id}" ) );
	}
}

if ( ! function_exists( 'hb_parse_request' ) ) {
	/**
	 * Parse params from request has encoded in search room page.
	 */
	function hb_parse_request() {
		$params = hb_get_request( 'hotel-booking-params' );
		if ( $params ) {
			$params = maybe_unserialize( base64_decode( $params ) );
			if ( $params && is_array( $params ) ) {
				foreach ( $params as $k => $v ) {
					$_GET[ $k ]     = sanitize_text_field( $v );
					$_POST[ $k ]    = sanitize_text_field( $v );
					$_REQUEST[ $k ] = sanitize_text_field( $v );
				}
			}
			if ( isset( $_GET['hotel-booking-params'] ) ) {
				unset( $_GET['hotel-booking-params'] );
			}
			if ( isset( $_POST['hotel-booking-params'] ) ) {
				unset( $_POST['hotel-booking-params'] );
			}
			if ( isset( $_REQUEST['hotel-booking-params'] ) ) {
				unset( $_REQUEST['hotel-booking-params'] );
			}
		}
	}
}

if ( ! function_exists( 'hb_payment_currencies' ) ) {
	/**
	 * Get the list of common currencies.
	 *
	 * @return mixed
	 */
	function hb_payment_currencies() {
		$currencies = array(
			'AED' => 'United Arab Emirates Dirham (د.إ)',
			'AUD' => 'Australian Dollars ($)',
			'BDT' => 'Bangladeshi Taka (৳&nbsp;)',
			'BRL' => 'Brazilian Real (R$)',
			'BGN' => 'Bulgarian Lev (лв.)',
			'CAD' => 'Canadian Dollars ($)',
			'CLP' => 'Chilean Peso ($)',
			'CNY' => 'Chinese Yuan (¥)',
			'COP' => 'Colombian Peso ($)',
			'CZK' => 'Czech Koruna (Kč)',
			'DKK' => 'Danish Krone (kr.)',
			'DOP' => 'Dominican Peso (RD$)',
			'EUR' => 'Euros (€)',
			'HKD' => 'Hong Kong Dollar ($)',
			'HRK' => 'Croatia kuna (Kn)',
			'HUF' => 'Hungarian Forint (Ft)',
			'ISK' => 'Icelandic krona (Kr.)',
			'IDR' => 'Indonesia Rupiah (Rp)',
			'INR' => 'Indian Rupee (Rs.)',
			'NPR' => 'Nepali Rupee (Rs.)',
			'ILS' => 'Israeli Shekel (₪)',
			'JPY' => 'Japanese Yen (¥)',
			'KIP' => 'Lao Kip (₭)',
			'KRW' => 'South Korean Won (₩)',
			'MYR' => 'Malaysian Ringgits (RM)',
			'MXN' => 'Mexican Peso ($)',
			'NGN' => 'Nigerian Naira (₦)',
			'NOK' => 'Norwegian Krone (kr)',
			'NZD' => 'New Zealand Dollar ($)',
			'PYG' => 'Paraguayan Guaraní (₲)',
			'PHP' => 'Philippine Pesos (₱)',
			'PLN' => 'Polish Zloty (zł)',
			'GBP' => 'Pounds Sterling (£)',
			'RON' => 'Romanian Leu (lei)',
			'RUB' => 'Russian Ruble (руб.)',
			'SGD' => 'Singapore Dollar ($)',
			'ZAR' => 'South African rand (R)',
			'SEK' => 'Swedish Krona (kr)',
			'CHF' => 'Swiss Franc (CHF)',
			'TWD' => 'Taiwan New Dollars (NT$)',
			'THB' => 'Thai Baht (฿)',
			'TRY' => 'Turkish Lira (₺)',
			'USD' => 'US Dollars ($)',
			'VND' => 'Vietnamese Dong (₫)',
			'EGP' => 'Egyptian Pound (EGP)'
		);

		return apply_filters( 'hb_payment_currencies', $currencies );
	}
}

if ( ! function_exists( 'hb_get_request' ) ) {
	/**
	 * Get a variable from request.
	 *
	 * @param $name
	 * @param null $default
	 * @param string $var
	 *
	 * @return null|string
	 */
	function hb_get_request( $name, $default = null, $var = '' ) {
		$return = $default;
		switch ( strtolower( $var ) ) {
			case 'post':
				$var = $_POST;
				break;
			case 'get':
				$var = $_GET;
				break;
			default:
				$var = $_REQUEST;
		}
		if ( ! empty( $var[ $name ] ) ) {
			$return = $var[ $name ];
		}
		if ( is_string( $return ) ) {
			$return = sanitize_text_field( $return );
		}

		return $return;
	}
}

if ( ! function_exists( 'hb_count_nights_two_dates' ) ) {
	/**
	 * Calculate the nights between to dates.
	 *
	 * @param null $end
	 * @param $start
	 *
	 * @return float
	 */
	function hb_count_nights_two_dates( $end = null, $start ) {
		if ( ! $end ) {
			$end = time();
		} else if ( is_numeric( $end ) ) {
		} else if ( is_string( $end ) ) {
			$end = @strtotime( $end );
		}

		if ( is_numeric( $start ) ) {
		} else if ( is_string( $start ) ) {
			$start = strtotime( $start );
		}

		$diff = $end - $start;

		return floor( $diff / ( 60 * 60 * 24 ) );
	}
}

if ( ! function_exists( 'hb_date_names' ) ) {
	/**
	 * @return mixed
	 */
	function hb_date_names() {
		$date_names = array(
			__( 'Sun', 'wp-hotel-booking' ),
			__( 'Mon', 'wp-hotel-booking' ),
			__( 'Tue', 'wp-hotel-booking' ),
			__( 'Wed', 'wp-hotel-booking' ),
			__( 'Thu', 'wp-hotel-booking' ),
			__( 'Fri', 'wp-hotel-booking' ),
			__( 'Sat', 'wp-hotel-booking' )
		);

		return apply_filters( 'hb_date_names', $date_names );
	}
}

if ( ! function_exists( 'hb_start_of_week_order' ) ) {
	/**
	 * Reorder date of week bases on 'Start of Week' option.
	 *
	 * @return array
	 */
	function hb_start_of_week_order() {
		$start = get_option( 'start_of_week' );

		$order = array();

		for ( $i = (int) $start; $i < 7; $i ++ ) {
			$order[] = $i;
		}

		for ( $j = 0; $j < $start; $j ++ ) {
			$order[] = $j;
		}

		return $order;
	}
}

if ( ! function_exists( 'hb_date_to_name' ) ) {
	/**
	 * @param $date
	 *
	 * @return mixed
	 */
	function hb_date_to_name( $date ) {
		$date_names = hb_date_names();

		return $date_names[ $date ];
	}
}

if ( ! function_exists( 'hb_get_common_titles' ) ) {
	/**
	 * @return mixed
	 */
	function hb_get_common_titles() {
		$titles = apply_filters( 'hb_customer_titles', array(
				'mr'   => __( 'Mr.', 'wp-hotel-booking' ),
				'ms'   => __( 'Ms.', 'wp-hotel-booking' ),
				'mrs'  => __( 'Mrs.', 'wp-hotel-booking' ),
				'miss' => __( 'Miss.', 'wp-hotel-booking' ),
				'dr'   => __( 'Dr.', 'wp-hotel-booking' ),
				'prof' => __( 'Prof.', 'wp-hotel-booking' )
			)
		);

		return is_array( $titles ) ? $titles : array();
	}
}

if ( ! function_exists( 'hb_get_title_by_slug' ) ) {
	/**
	 * @param $slug
	 *
	 * @return string
	 */
	function hb_get_title_by_slug( $slug ) {
		$titles = hb_get_common_titles();

		return ! empty( $titles[ $slug ] ) ? $titles[ $slug ] : '';
	}
}

if ( ! function_exists( 'hb_dropdown_titles' ) ) {
	/**
	 * @param array $args
	 *
	 * @return string
	 */
	function hb_dropdown_titles( $args = array() ) {
		$args              = wp_parse_args(
			$args, array(
				'name'              => 'title',
				'selected'          => '',
				'show_option_none'  => __( 'Select', 'wp-hotel-booking' ),
				'option_none_value' => '',
				'echo'              => true,
				'required'          => false
			)
		);
		$name              = '';
		$selected          = '';
		$echo              = false;
		$required          = false;
		$show_option_none  = false;
		$option_none_value = '';
		extract( $args );
		$titles = hb_get_common_titles();
		$output = '<select name="' . $name . '" ' . ( $required ? 'required' : '' ) . '>';
		if ( $show_option_none ) {
			$output .= sprintf( '<option value="%s">%s</option>', $option_none_value, $show_option_none );
		}
		if ( $titles ) {
			foreach ( $titles as $slug => $title ) {
				$output .= sprintf( '<option value="%s"%s>%s</option>', $slug, $slug == $selected ? ' selected="selected"' : '', $title );
			}
		}
		$output .= '</select>';
		if ( $echo ) {
			echo sprintf( '%s', $output );
		}

		return $output;
	}
}

if ( ! function_exists( 'hb_create_empty_post' ) ) {
	/**
	 * Create an empty object with all fields as a WP_Post object
	 *
	 * @param array $args
	 *
	 * @return stdClass
	 */
	function hb_create_empty_post( $args = array() ) {
		$posts = get_posts(
			array(
				'post_type'      => 'any',
				'posts_per_page' => 1
			)
		);

		if ( $posts ) {
			foreach ( get_object_vars( $posts[0] ) as $key => $value ) {
				if ( ! in_array( $key, $args ) ) {
					$posts[0]->{$key} = null;
				} else {
					$posts[0]->{$key} = $args[ $key ];
				}
			}

			return $posts[0];
		}

		return new stdClass();
	}
}

if ( ! function_exists( 'hb_i18n' ) ) {
	/**
	 * Localize script for front-end.
	 *
	 * @return mixed
	 */
	function hb_i18n() {
		$translation = apply_filters( 'hb_i18n', array(
			'no_customer_exist'              => __( 'No customer exist.', 'wp-hotel-booking' ),
			'no_payment_method_selected'     => __( 'Please select your payment method.', 'wp-hotel-booking' ),
			'confirm_tos'                    => __( 'Please accept our Terms and Conditions.', 'wp-hotel-booking' ),
			'no_rooms_selected'              => __( 'Please select at least one the room.', 'wp-hotel-booking' ),
			'empty_customer_title'           => __( 'Please select your title.', 'wp-hotel-booking' ),
			'empty_customer_first_name'      => __( 'Please enter your first name.', 'wp-hotel-booking' ),
			'empty_customer_last_name'       => __( 'Please enter your last name.', 'wp-hotel-booking' ),
			'empty_customer_address'         => __( 'Please enter your address.', 'wp-hotel-booking' ),
			'empty_customer_city'            => __( 'Please enter your city name.', 'wp-hotel-booking' ),
			'empty_customer_state'           => __( 'Please enter your state.', 'wp-hotel-booking' ),
			'empty_customer_postal_code'     => __( 'Please enter your postal code.', 'wp-hotel-booking' ),
			'empty_customer_country'         => __( 'Please select your country.', 'wp-hotel-booking' ),
			'empty_customer_phone'           => __( 'Please enter your phone number.', 'wp-hotel-booking' ),
			'customer_email_invalid'         => __( 'Your email is invalid.', 'wp-hotel-booking' ),
			'customer_email_not_match'       => __( 'Your email does not match with existing email! Ok to create a new customer information.', 'wp-hotel-booking' ),
			'empty_check_in_date'            => __( 'Please select check in date.', 'wp-hotel-booking' ),
			'empty_check_out_date'           => __( 'Please select check out date.', 'wp-hotel-booking' ),
			'check_in_date_must_be_greater'  => __( 'Check in date must be greater than the current.', 'wp-hotel-booking' ),
			'check_out_date_must_be_greater' => __( 'Check out date must be greater than the check in.', 'wp-hotel-booking' ),
			'enter_coupon_code'              => __( 'Please enter coupon code.', 'wp-hotel-booking' ),
			'review_rating_required'         => __( 'Please select a rating.', 'wp-hotel-booking' ),
			'warning'                        => array(
				'room_select' => __( 'Please select room number.', 'wp-hotel-booking' ),
				'try_again'   => __( 'Please try again!', 'wp-hotel-booking' )
			),
			'date_time_format'               => hb_date_format_js(),
			'monthNames'                     => hb_month_name_js(),
			'monthNamesShort'                => hb_month_name_short_js(),
			'dayNames'                       => hb_day_name_js(),
			'dayNamesShort'                  => hb_day_name_short_js(),
			'dayNamesMin'                    => hb_day_name_min_js(),
			'date_start'                     => get_option( 'start_of_week' ),
			'view_cart'                      => __( 'View Cart', 'wp-hotel-booking' ),
			'cart_url'                       => hb_get_cart_url()
		) );

		return is_array( $translation ) ? $translation : array();
	}
}

if ( ! function_exists( 'hb_date_format_js' ) ) {
	/**
	 * Set date format js.
	 *
	 * @return string
	 */
	function hb_date_format_js() {
		$dateFormat = hb_get_date_format();

		switch ( $dateFormat ) {
			case 'Y-m-d':
				$return = 'yy-mm-dd';
				break;
			case 'Y/m/d':
				$return = 'yy/mm/dd';
				break;
			case 'd/m/Y':
				$return = 'dd/mm/yy';
				break;
			case 'd-m-Y':
				$return = 'dd-mm-yy';
				break;
			case 'm/d/Y':
				$return = 'mm/dd/yy';
				break;
			case 'm-d-Y':
				$return = 'mm-dd-yy';
				break;
			case 'F j, Y':
				$return = 'MM dd, yy';
				break;
			case 'd.m.Y':
				$return = 'dd.mm.yy';
				break;
			default:
				$return = 'mm/dd/yy';
				break;
		}

		return $return;
	}
}

if ( ! function_exists( 'hb_month_name_js' ) ) {
	/**
	 * @return array
	 */
	function hb_month_name_js() {
		$month = apply_filters( 'hotel_booking_month_name_js', array(
			__( 'January', 'wp-hotel-booking' ),
			__( 'February', 'wp-hotel-booking' ),
			__( 'March', 'wp-hotel-booking' ),
			__( 'April', 'wp-hotel-booking' ),
			__( 'May', 'wp-hotel-booking' ),
			__( 'June', 'wp-hotel-booking' ),
			__( 'July', 'wp-hotel-booking' ),
			__( 'August', 'wp-hotel-booking' ),
			__( 'September', 'wp-hotel-booking' ),
			__( 'October', 'wp-hotel-booking' ),
			__( 'November', 'wp-hotel-booking' ),
			__( 'December', 'wp-hotel-booking' )
		) );

		return is_array( $month ) ? $month : array();
	}
}

if ( ! function_exists( 'hb_month_name_short_js' ) ) {
	/**
	 * @return array
	 */
	function hb_month_name_short_js() {
		$month = apply_filters( 'hotel_booking_month_name_short_js', array(
			__( 'Jan', 'wp-hotel-booking' ),
			__( 'Feb', 'wp-hotel-booking' ),
			__( 'Mar', 'wp-hotel-booking' ),
			__( 'Apr', 'wp-hotel-booking' ),
			__( 'Maj', 'wp-hotel-booking' ),
			__( 'Jun', 'wp-hotel-booking' ),
			__( 'Jul', 'wp-hotel-booking' ),
			__( 'Aug', 'wp-hotel-booking' ),
			__( 'Sep', 'wp-hotel-booking' ),
			__( 'Oct', 'wp-hotel-booking' ),
			__( 'Nov', 'wp-hotel-booking' ),
			__( 'Dec', 'wp-hotel-booking' )
		) );

		return is_array( $month ) ? $month : array();
	}
}

if ( ! function_exists( 'hb_day_name_js' ) ) {
	/**
	 * @return array
	 */
	function hb_day_name_js() {
		$day = apply_filters( 'hotel_booking_day_name_js', array(
			__( 'Sunday', 'wp-hotel-booking' ),
			__( 'Monday', 'wp-hotel-booking' ),
			__( 'Tuesday', 'wp-hotel-booking' ),
			__( 'Wednesday', 'wp-hotel-booking' ),
			__( 'Thursday', 'wp-hotel-booking' ),
			__( 'Friday', 'wp-hotel-booking' ),
			__( 'Saturday', 'wp-hotel-booking' )
		) );

		return is_array( $day ) ? $day : array();
	}
}

if ( ! function_exists( 'hb_day_name_short_js' ) ) {
	/**
	 * @return mixed
	 */
	function hb_day_name_short_js() {
		return apply_filters( 'hotel_booking_day_name_short_js', hb_date_names() );
	}
}

if ( ! function_exists( 'hb_day_name_min_js' ) ) {
	/**
	 * @return array
	 */
	function hb_day_name_min_js() {
		$day = apply_filters( 'hotel_booking_day_name_min_js', array(
			__( 'Su', 'wp-hotel-booking' ),
			__( 'Mo', 'wp-hotel-booking' ),
			__( 'Tu', 'wp-hotel-booking' ),
			__( 'We', 'wp-hotel-booking' ),
			__( 'Th', 'wp-hotel-booking' ),
			__( 'Fr', 'wp-hotel-booking' ),
			__( 'Sa', 'wp-hotel-booking' )
		) );

		return is_array( $day ) ? $day : array();
	}
}

if ( ! function_exists( 'hb_get_tax_settings' ) ) {
	/**
	 * Get tax setting value.
	 *
	 * @return float|int|mixed
	 */
	function hb_get_tax_settings() {

		$settings = hb_settings();
		if ( $settings->get( 'price_including_tax' ) && $tax = $settings->get( 'tax' ) ) {
			$tax = (float) ( $tax / 100 );
		} else {
			$tax = 0;
		}

		return $tax;
	}
}

if ( ! function_exists( 'hb_price_including_tax' ) ) {
	/**
	 * @param bool $cart
	 *
	 * @return mixed
	 */
	function hb_price_including_tax( $cart = false ) {
		$settings = WPHB_Settings::instance();

		return apply_filters( 'hb_price_including_tax', $settings->get( 'price_including_tax' ), $cart );
	}
}

if ( ! function_exists( 'hb_dropdown_numbers' ) ) {
	/**
	 * Drop down number.
	 *
	 * @param array $args
	 *
	 * @return string
	 */
	function hb_dropdown_numbers( $args = array() ) {
		$args              = wp_parse_args(
			$args, array(
				'min'               => 0,
				'max'               => 100,
				'selected'          => 0,
				'name'              => '',
				'class'             => '',
				'echo'              => true,
				'show_option_none'  => '',
				'option_none_value' => '',
				'options'           => array()
			)
		);
		$min               = 0;
		$max               = 100;
		$selected          = 0;
		$name              = '';
		$id                = '';
		$class             = '';
		$echo              = true;
		$show_option_none  = false;
		$option_none_value = '';

		extract( $args );

		$id     = ! empty( $id ) ? $id : '';
		$output = '<select name="' . $name . '" ' . ( $id ? 'id="' . $id . '"' : '' ) . '' . ( $class ? ' class="' . $class . '"' : '' ) . '>';
		if ( $show_option_none ) {
			$output .= '<option value="' . $option_none_value . '">' . $show_option_none . '</option>';
		}
		if ( empty( $options ) ) {
			for ( $i = $min; $i <= $max; $i ++ ) {
				$output .= sprintf( '<option value="%1$d"%2$s>%1$d</option>', $i, $selected == $i ? ' selected="selected"' : '' );
			}
		} else {
			foreach ( $options as $option ) {
				$output .= sprintf( '<option value="%1$d"%2$s>%3$d</option>', $option['value'], $selected == $option['value'] ? ' selected="selected"' : '', $option['text'] );
			}
		}

		$output .= '</select>';
		if ( $echo ) {
			echo sprintf( '%s', $output );
		}

		return $output;
	}
}

if ( ! function_exists( 'hb_send_json' ) ) {
	/**
	 * @param $data
	 */
	function hb_send_json( $data ) {
		echo '<!-- HB_AJAX_START -->';
		@header( 'Content-Type: application/json; charset=' . get_option( 'blog_charset' ) );
		echo wp_json_encode( $data );
		echo '<!-- HB_AJAX_END -->';
		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
			wp_die();
		} else {
			die;
		}
	}
}

if ( ! function_exists( 'hb_is_ajax' ) ) {
	/**
	 * @return bool
	 */
	function hb_is_ajax() {
		return defined( 'DOING_AJAX' ) && DOING_AJAX;
	}
}

if ( ! function_exists( 'hb_get_currency' ) ) {
	/**
	 * @return mixed
	 */
	function hb_get_currency() {
		$currencies     = hb_payment_currencies();
		$currency_codes = array_keys( $currencies );
		$currency       = reset( $currency_codes );

		return apply_filters( 'hb_currency', WPHB_Settings::instance()->get( 'currency', $currency ) );
	}
}

if ( ! function_exists( 'hb_get_currency_symbol' ) ) {
	/**
	 * @param string $currency
	 *
	 * @return mixed
	 */
	function hb_get_currency_symbol( $currency = '' ) {
		if ( ! $currency ) {
			$currency = hb_get_currency();
		}

		switch ( $currency ) {
			case 'AED' :
				$currency_symbol = 'د.إ';
				break;
			case 'AUD' :
			case 'CAD' :
			case 'CLP' :
			case 'COP' :
			case 'HKD' :
			case 'MXN' :
			case 'NZD' :
			case 'SGD' :
			case 'USD' :
				$currency_symbol = '$';
				break;
			case 'BDT':
				$currency_symbol = '৳';
				break;
			case 'BGN' :
				$currency_symbol = 'лв.';
				break;
			case 'BRL' :
				$currency_symbol = 'R$';
				break;
			case 'CHF' :
				$currency_symbol = 'CHF';
				break;
			case 'CNY' :
			case 'JPY' :
			case 'RMB' :
				$currency_symbol = '¥';
				break;
			case 'CZK' :
				$currency_symbol = 'Kč';
				break;
			case 'DKK' :
				$currency_symbol = 'kr.';
				break;
			case 'DOP' :
				$currency_symbol = 'RD$';
				break;
			case 'EGP' :
				$currency_symbol = 'EGP';
				break;
			case 'EUR' :
				$currency_symbol = '€';
				break;
			case 'GBP' :
				$currency_symbol = '£';
				break;
			case 'HRK' :
				$currency_symbol = 'Kn';
				break;
			case 'HUF' :
				$currency_symbol = 'Ft';
				break;
			case 'IDR' :
				$currency_symbol = 'Rp';
				break;
			case 'ILS' :
				$currency_symbol = '₪';
				break;
			case 'INR' :
				$currency_symbol = 'Rs.';
				break;
			case 'ISK' :
				$currency_symbol = 'Kr.';
				break;
			case 'KIP' :
				$currency_symbol = '₭';
				break;
			case 'KRW' :
				$currency_symbol = '₩';
				break;
			case 'MYR' :
				$currency_symbol = 'RM';
				break;
			case 'NGN' :
				$currency_symbol = '₦';
				break;
			case 'NOK' :
				$currency_symbol = 'kr';
				break;
			case 'NPR' :
				$currency_symbol = 'Rs.';
				break;
			case 'PHP' :
				$currency_symbol = '₱';
				break;
			case 'PLN' :
				$currency_symbol = '&#122;&#322;';
				break;
			case 'PYG' :
				$currency_symbol = 'zł';
				break;
			case 'RON' :
				$currency_symbol = 'lei';
				break;
			case 'RUB' :
				$currency_symbol = 'руб.';
				break;
			case 'SEK' :
				$currency_symbol = 'kr';
				break;
			case 'THB' :
				$currency_symbol = '&#3647;';
				break;
			case 'TRY' :
				$currency_symbol = '฿';
				break;
			case 'TWD' :
				$currency_symbol = 'NT$';
				break;
			case 'UAH' :
				$currency_symbol = '₴';
				break;
			case 'VND' :
				$currency_symbol = '₫';
				break;
			case 'ZAR' :
				$currency_symbol = 'R';
				break;
			default :
				$currency_symbol = $currency;
				break;
		}

		return apply_filters( 'hb_currency_symbol', $currency_symbol, $currency );
	}
}

if ( ! function_exists( 'hb_format_price' ) ) {
	/**
	 * @param $price
	 * @param bool $with_currency
	 *
	 * @return mixed
	 */
	function hb_format_price( $price, $with_currency = true ) {
		$settings                  = WPHB_Settings::instance();
		$position                  = $settings->get( 'price_currency_position' );
		$price_thousands_separator = $settings->get( 'price_thousands_separator' );
		$price_decimals_separator  = $settings->get( 'price_decimals_separator' );
		$price_number_of_decimal   = $settings->get( 'price_number_of_decimal' );
		if ( ! is_numeric( $price ) ) {
			$price = 0;
		}

		$price  = apply_filters( 'hotel_booking_price_switcher', $price );
		$before = $after = '';
		if ( $with_currency ) {
			if ( gettype( $with_currency ) != 'string' ) {
				$currency = hb_get_currency_symbol();
			} else {
				$currency = $with_currency;
			}

			switch ( $position ) {
				default:
					$before = $currency;
					break;
				case 'left_with_space':
					$before = $currency . ' ';
					break;
				case 'right':
					$after = $currency;
					break;
				case 'right_with_space':
					$after = ' ' . $currency;
			}
		}

		$price_format = $before . number_format( $price, $price_number_of_decimal, $price_decimals_separator, $price_thousands_separator ) . $after;

		return apply_filters( 'hb_price_format', $price_format, $price, $with_currency );
	}
}

if ( ! function_exists( 'hb_get_payment_gateways' ) ) {
	/**
	 * @param array $args
	 *
	 * @return array|mixed
	 */
	function hb_get_payment_gateways( $args = array() ) {
		static $payment_gateways = array();
		if ( ! $payment_gateways ) {
			$defaults         = array(
				'offline-payment' => new WPHB_Payment_Gateway_Offline_Payment(),
				'paypal'          => new WPHB_Payment_Gateway_Paypal()
			);
			$payment_gateways = apply_filters( 'hb_payment_gateways', $defaults );
		}

		$args = wp_parse_args(
			$args, array(
				'enable' => false
			)
		);

		if ( $args['enable'] ) {
			$gateways = array();
			foreach ( $payment_gateways as $k => $gateway ) {
				$is_enable = is_callable( array( $gateway, 'is_enable' ) ) && $gateway->is_enable();
				if ( apply_filters( 'hb_payment_gateway_enable', $is_enable, $gateway ) ) {
					$gateways[ $k ] = $gateway;
				}
			}
		} else {
			$gateways = $payment_gateways;
		}

		return $gateways;
	}
}

if ( ! function_exists( 'hb_get_user_payment_method' ) ) {
	/**
	 * @param $slug
	 *
	 * @return bool|mixed
	 */
	function hb_get_user_payment_method( $slug ) {
		$methods = hb_get_payment_gateways( array( 'enable' => true ) );
		$method  = false;
		if ( $methods && ! empty( $methods[ $slug ] ) ) {
			$method = $methods[ $slug ];
		}

		return $method;
	}
}

if ( ! function_exists( 'hb_get_page_id' ) ) {
	/**
	 * Get page id.
	 *
	 * @param $name
	 *
	 * @return mixed
	 */
	function hb_get_page_id( $name ) {
		$settings = hb_settings();

		return apply_filters( 'hb_get_page_id', $settings->get( "{$name}_page_id" ) );
	}
}

if ( ! function_exists( 'hb_get_page_permalink' ) ) {
	/**
	 * @param $name
	 *
	 * @return false|string
	 */
	function hb_get_page_permalink( $name ) {
		return get_the_permalink( hb_get_page_id( $name ) );
	}
}

if ( ! function_exists( 'hb_get_endpoint_url' ) ) {
	/**
	 * @param $endpoint
	 * @param string $value
	 * @param string $permalink
	 *
	 * @return mixed
	 */
	function hb_get_endpoint_url( $endpoint, $value = '', $permalink = '' ) {
		if ( ! $permalink ) {
			$permalink = get_permalink();
		}

		if ( get_option( 'permalink_structure' ) ) {
			if ( strstr( $permalink, '?' ) ) {
				$query_string = '?' . parse_url( $permalink, PHP_URL_QUERY );
				$permalink    = current( explode( '?', $permalink ) );
			} else {
				$query_string = '';
			}
			$url = trailingslashit( $permalink ) . $endpoint . '/' . $value . $query_string;
		} else {
			$url = add_query_arg( $endpoint, $value, $permalink );
		}

		return apply_filters( 'hb_get_endpoint_url', $url, $endpoint, $value, $permalink );
	}
}

if ( ! function_exists( 'hb_get_advance_payment' ) ) {
	/**
	 * Get advance payment.
	 *
	 * @return mixed
	 */
	function hb_get_advance_payment() {
		$settings        = hb_settings();
		$advance_payment = $settings->get( 'advance_payment' );

		return apply_filters( 'hb_advance_payment', $advance_payment );
	}
}

if ( ! function_exists( 'hb_do_transaction' ) ) {
	/**
	 * @param $method
	 * @param bool $transaction
	 */
	function hb_do_transaction( $method, $transaction = false ) {
		do_action( 'hb_do_transaction_' . $method, $transaction );
	}
}

if ( ! function_exists( 'hb_maybe_modify_page_content' ) ) {
	/**
	 * @param $content
	 *
	 * @return string
	 */
	function hb_maybe_modify_page_content( $content ) {
		global $post;
		if ( is_page() && ( $post->ID == hb_get_page_id( 'search' ) || has_shortcode( $content, 'hotel_booking' ) ) ) {

			// params search result
			$page       = hb_get_request( 'hotel-booking' );
			$start_date = hb_get_request( 'check_in_date' );
			$end_date   = hb_get_request( 'check_out_date' );
			$adults     = hb_get_request( 'adults' );
			$max_child  = hb_get_request( 'max_child' );

			$content = '[hotel_booking page="' . $page . '" check_in_date="' . $start_date . '" check_in_date="' . $end_date . '" adults="' . $adults . '" max_child="' . $max_child . '"]';
		}

		return $content;
	}
}

if ( ! function_exists( 'hb_format_order_number' ) ) {
	/**
	 * @param $order_number
	 *
	 * @return string
	 */
	function hb_format_order_number( $order_number ) {
		return '#' . sprintf( "%d", $order_number );
	}
}

if ( ! function_exists( 'hb_get_countries' ) ) {
	/**
	 * Get countries.
	 *
	 * @return array
	 */
	function hb_get_countries() {
		$countries = array(
			'AF' => __( 'Afghanistan', 'wp-hotel-booking' ),
			'AX' => __( '&#197;land Islands', 'wp-hotel-booking' ),
			'AL' => __( 'Albania', 'wp-hotel-booking' ),
			'DZ' => __( 'Algeria', 'wp-hotel-booking' ),
			'AD' => __( 'Andorra', 'wp-hotel-booking' ),
			'AO' => __( 'Angola', 'wp-hotel-booking' ),
			'AI' => __( 'Anguilla', 'wp-hotel-booking' ),
			'AQ' => __( 'Antarctica', 'wp-hotel-booking' ),
			'AG' => __( 'Antigua and Barbuda', 'wp-hotel-booking' ),
			'AR' => __( 'Argentina', 'wp-hotel-booking' ),
			'AM' => __( 'Armenia', 'wp-hotel-booking' ),
			'AW' => __( 'Aruba', 'wp-hotel-booking' ),
			'AU' => __( 'Australia', 'wp-hotel-booking' ),
			'AT' => __( 'Austria', 'wp-hotel-booking' ),
			'AZ' => __( 'Azerbaijan', 'wp-hotel-booking' ),
			'BS' => __( 'Bahamas', 'wp-hotel-booking' ),
			'BH' => __( 'Bahrain', 'wp-hotel-booking' ),
			'BD' => __( 'Bangladesh', 'wp-hotel-booking' ),
			'BB' => __( 'Barbados', 'wp-hotel-booking' ),
			'BY' => __( 'Belarus', 'wp-hotel-booking' ),
			'BE' => __( 'Belgium', 'wp-hotel-booking' ),
			'PW' => __( 'Belau', 'wp-hotel-booking' ),
			'BZ' => __( 'Belize', 'wp-hotel-booking' ),
			'BJ' => __( 'Benin', 'wp-hotel-booking' ),
			'BM' => __( 'Bermuda', 'wp-hotel-booking' ),
			'BT' => __( 'Bhutan', 'wp-hotel-booking' ),
			'BO' => __( 'Bolivia', 'wp-hotel-booking' ),
			'BQ' => __( 'Bonaire, Saint Eustatius and Saba', 'wp-hotel-booking' ),
			'BA' => __( 'Bosnia and Herzegovina', 'wp-hotel-booking' ),
			'BW' => __( 'Botswana', 'wp-hotel-booking' ),
			'BV' => __( 'Bouvet Island', 'wp-hotel-booking' ),
			'BR' => __( 'Brazil', 'wp-hotel-booking' ),
			'IO' => __( 'British Indian Ocean Territory', 'wp-hotel-booking' ),
			'VG' => __( 'British Virgin Islands', 'wp-hotel-booking' ),
			'BN' => __( 'Brunei', 'wp-hotel-booking' ),
			'BG' => __( 'Bulgaria', 'wp-hotel-booking' ),
			'BF' => __( 'Burkina Faso', 'wp-hotel-booking' ),
			'BI' => __( 'Burundi', 'wp-hotel-booking' ),
			'KH' => __( 'Cambodia', 'wp-hotel-booking' ),
			'CM' => __( 'Cameroon', 'wp-hotel-booking' ),
			'CA' => __( 'Canada', 'wp-hotel-booking' ),
			'CV' => __( 'Cape Verde', 'wp-hotel-booking' ),
			'KY' => __( 'Cayman Islands', 'wp-hotel-booking' ),
			'CF' => __( 'Central African Republic', 'wp-hotel-booking' ),
			'TD' => __( 'Chad', 'wp-hotel-booking' ),
			'CL' => __( 'Chile', 'wp-hotel-booking' ),
			'CN' => __( 'China', 'wp-hotel-booking' ),
			'CX' => __( 'Christmas Island', 'wp-hotel-booking' ),
			'CC' => __( 'Cocos (Keeling) Islands', 'wp-hotel-booking' ),
			'CO' => __( 'Colombia', 'wp-hotel-booking' ),
			'KM' => __( 'Comoros', 'wp-hotel-booking' ),
			'CG' => __( 'Congo (Brazzaville)', 'wp-hotel-booking' ),
			'CD' => __( 'Congo (Kinshasa)', 'wp-hotel-booking' ),
			'CK' => __( 'Cook Islands', 'wp-hotel-booking' ),
			'CR' => __( 'Costa Rica', 'wp-hotel-booking' ),
			'HR' => __( 'Croatia', 'wp-hotel-booking' ),
			'CU' => __( 'Cuba', 'wp-hotel-booking' ),
			'CW' => __( 'Cura&Ccedil;ao', 'wp-hotel-booking' ),
			'CY' => __( 'Cyprus', 'wp-hotel-booking' ),
			'CZ' => __( 'Czech Republic', 'wp-hotel-booking' ),
			'DK' => __( 'Denmark', 'wp-hotel-booking' ),
			'DJ' => __( 'Djibouti', 'wp-hotel-booking' ),
			'DM' => __( 'Dominica', 'wp-hotel-booking' ),
			'DO' => __( 'Dominican Republic', 'wp-hotel-booking' ),
			'EC' => __( 'Ecuador', 'wp-hotel-booking' ),
			'EG' => __( 'Egypt', 'wp-hotel-booking' ),
			'SV' => __( 'El Salvador', 'wp-hotel-booking' ),
			'GQ' => __( 'Equatorial Guinea', 'wp-hotel-booking' ),
			'ER' => __( 'Eritrea', 'wp-hotel-booking' ),
			'EE' => __( 'Estonia', 'wp-hotel-booking' ),
			'ET' => __( 'Ethiopia', 'wp-hotel-booking' ),
			'FK' => __( 'Falkland Islands', 'wp-hotel-booking' ),
			'FO' => __( 'Faroe Islands', 'wp-hotel-booking' ),
			'FJ' => __( 'Fiji', 'wp-hotel-booking' ),
			'FI' => __( 'Finland', 'wp-hotel-booking' ),
			'FR' => __( 'France', 'wp-hotel-booking' ),
			'GF' => __( 'French Guiana', 'wp-hotel-booking' ),
			'PF' => __( 'French Polynesia', 'wp-hotel-booking' ),
			'TF' => __( 'French Southern Territories', 'wp-hotel-booking' ),
			'GA' => __( 'Gabon', 'wp-hotel-booking' ),
			'GM' => __( 'Gambia', 'wp-hotel-booking' ),
			'GE' => __( 'Georgia', 'wp-hotel-booking' ),
			'DE' => __( 'Germany', 'wp-hotel-booking' ),
			'GH' => __( 'Ghana', 'wp-hotel-booking' ),
			'GI' => __( 'Gibraltar', 'wp-hotel-booking' ),
			'GR' => __( 'Greece', 'wp-hotel-booking' ),
			'GL' => __( 'Greenland', 'wp-hotel-booking' ),
			'GD' => __( 'Grenada', 'wp-hotel-booking' ),
			'GP' => __( 'Guadeloupe', 'wp-hotel-booking' ),
			'GT' => __( 'Guatemala', 'wp-hotel-booking' ),
			'GG' => __( 'Guernsey', 'wp-hotel-booking' ),
			'GN' => __( 'Guinea', 'wp-hotel-booking' ),
			'GW' => __( 'Guinea-Bissau', 'wp-hotel-booking' ),
			'GY' => __( 'Guyana', 'wp-hotel-booking' ),
			'HT' => __( 'Haiti', 'wp-hotel-booking' ),
			'HM' => __( 'Heard Island and McDonald Islands', 'wp-hotel-booking' ),
			'HN' => __( 'Honduras', 'wp-hotel-booking' ),
			'HK' => __( 'Hong Kong', 'wp-hotel-booking' ),
			'HU' => __( 'Hungary', 'wp-hotel-booking' ),
			'IS' => __( 'Iceland', 'wp-hotel-booking' ),
			'IN' => __( 'India', 'wp-hotel-booking' ),
			'ID' => __( 'Indonesia', 'wp-hotel-booking' ),
			'IR' => __( 'Iran', 'wp-hotel-booking' ),
			'IQ' => __( 'Iraq', 'wp-hotel-booking' ),
			'IE' => __( 'Republic of Ireland', 'wp-hotel-booking' ),
			'IM' => __( 'Isle of Man', 'wp-hotel-booking' ),
			'IL' => __( 'Israel', 'wp-hotel-booking' ),
			'IT' => __( 'Italy', 'wp-hotel-booking' ),
			'CI' => __( 'Ivory Coast', 'wp-hotel-booking' ),
			'JM' => __( 'Jamaica', 'wp-hotel-booking' ),
			'JP' => __( 'Japan', 'wp-hotel-booking' ),
			'JE' => __( 'Jersey', 'wp-hotel-booking' ),
			'JO' => __( 'Jordan', 'wp-hotel-booking' ),
			'KZ' => __( 'Kazakhstan', 'wp-hotel-booking' ),
			'KE' => __( 'Kenya', 'wp-hotel-booking' ),
			'KI' => __( 'Kiribati', 'wp-hotel-booking' ),
			'KW' => __( 'Kuwait', 'wp-hotel-booking' ),
			'KG' => __( 'Kyrgyzstan', 'wp-hotel-booking' ),
			'LA' => __( 'Laos', 'wp-hotel-booking' ),
			'LV' => __( 'Latvia', 'wp-hotel-booking' ),
			'LB' => __( 'Lebanon', 'wp-hotel-booking' ),
			'LS' => __( 'Lesotho', 'wp-hotel-booking' ),
			'LR' => __( 'Liberia', 'wp-hotel-booking' ),
			'LY' => __( 'Libya', 'wp-hotel-booking' ),
			'LI' => __( 'Liechtenstein', 'wp-hotel-booking' ),
			'LT' => __( 'Lithuania', 'wp-hotel-booking' ),
			'LU' => __( 'Luxembourg', 'wp-hotel-booking' ),
			'MO' => __( 'Macao S.A.R., China', 'wp-hotel-booking' ),
			'MK' => __( 'Macedonia', 'wp-hotel-booking' ),
			'MG' => __( 'Madagascar', 'wp-hotel-booking' ),
			'MW' => __( 'Malawi', 'wp-hotel-booking' ),
			'MY' => __( 'Malaysia', 'wp-hotel-booking' ),
			'MV' => __( 'Maldives', 'wp-hotel-booking' ),
			'ML' => __( 'Mali', 'wp-hotel-booking' ),
			'MT' => __( 'Malta', 'wp-hotel-booking' ),
			'MH' => __( 'Marshall Islands', 'wp-hotel-booking' ),
			'MQ' => __( 'Martinique', 'wp-hotel-booking' ),
			'MR' => __( 'Mauritania', 'wp-hotel-booking' ),
			'MU' => __( 'Mauritius', 'wp-hotel-booking' ),
			'YT' => __( 'Mayotte', 'wp-hotel-booking' ),
			'MX' => __( 'Mexico', 'wp-hotel-booking' ),
			'FM' => __( 'Micronesia', 'wp-hotel-booking' ),
			'MD' => __( 'Moldova', 'wp-hotel-booking' ),
			'MC' => __( 'Monaco', 'wp-hotel-booking' ),
			'MN' => __( 'Mongolia', 'wp-hotel-booking' ),
			'ME' => __( 'Montenegro', 'wp-hotel-booking' ),
			'MS' => __( 'Montserrat', 'wp-hotel-booking' ),
			'MA' => __( 'Morocco', 'wp-hotel-booking' ),
			'MZ' => __( 'Mozambique', 'wp-hotel-booking' ),
			'MM' => __( 'Myanmar', 'wp-hotel-booking' ),
			'NA' => __( 'Namibia', 'wp-hotel-booking' ),
			'NR' => __( 'Nauru', 'wp-hotel-booking' ),
			'NP' => __( 'Nepal', 'wp-hotel-booking' ),
			'NL' => __( 'Netherlands', 'wp-hotel-booking' ),
			'AN' => __( 'Netherlands Antilles', 'wp-hotel-booking' ),
			'NC' => __( 'New Caledonia', 'wp-hotel-booking' ),
			'NZ' => __( 'New Zealand', 'wp-hotel-booking' ),
			'NI' => __( 'Nicaragua', 'wp-hotel-booking' ),
			'NE' => __( 'Niger', 'wp-hotel-booking' ),
			'NG' => __( 'Nigeria', 'wp-hotel-booking' ),
			'NU' => __( 'Niue', 'wp-hotel-booking' ),
			'NF' => __( 'Norfolk Island', 'wp-hotel-booking' ),
			'KP' => __( 'North Korea', 'wp-hotel-booking' ),
			'NO' => __( 'Norway', 'wp-hotel-booking' ),
			'OM' => __( 'Oman', 'wp-hotel-booking' ),
			'PK' => __( 'Pakistan', 'wp-hotel-booking' ),
			'PS' => __( 'Palestinian Territory', 'wp-hotel-booking' ),
			'PA' => __( 'Panama', 'wp-hotel-booking' ),
			'PG' => __( 'Papua New Guinea', 'wp-hotel-booking' ),
			'PY' => __( 'Paraguay', 'wp-hotel-booking' ),
			'PE' => __( 'Peru', 'wp-hotel-booking' ),
			'PH' => __( 'Philippines', 'wp-hotel-booking' ),
			'PN' => __( 'Pitcairn', 'wp-hotel-booking' ),
			'PL' => __( 'Poland', 'wp-hotel-booking' ),
			'PT' => __( 'Portugal', 'wp-hotel-booking' ),
			'QA' => __( 'Qatar', 'wp-hotel-booking' ),
			'RE' => __( 'Reunion', 'wp-hotel-booking' ),
			'RO' => __( 'Romania', 'wp-hotel-booking' ),
			'RU' => __( 'Russia', 'wp-hotel-booking' ),
			'RW' => __( 'Rwanda', 'wp-hotel-booking' ),
			'BL' => __( 'Saint Barth&eacute;lemy', 'wp-hotel-booking' ),
			'SH' => __( 'Saint Helena', 'wp-hotel-booking' ),
			'KN' => __( 'Saint Kitts and Nevis', 'wp-hotel-booking' ),
			'LC' => __( 'Saint Lucia', 'wp-hotel-booking' ),
			'MF' => __( 'Saint Martin (French part)', 'wp-hotel-booking' ),
			'SX' => __( 'Saint Martin (Dutch part)', 'wp-hotel-booking' ),
			'PM' => __( 'Saint Pierre and Miquelon', 'wp-hotel-booking' ),
			'VC' => __( 'Saint Vincent and the Grenadines', 'wp-hotel-booking' ),
			'SM' => __( 'San Marino', 'wp-hotel-booking' ),
			'ST' => __( 'S&atilde;o Tom&eacute; and Pr&iacute;ncipe', 'wp-hotel-booking' ),
			'SA' => __( 'Saudi Arabia', 'wp-hotel-booking' ),
			'SN' => __( 'Senegal', 'wp-hotel-booking' ),
			'RS' => __( 'Serbia', 'wp-hotel-booking' ),
			'SC' => __( 'Seychelles', 'wp-hotel-booking' ),
			'SL' => __( 'Sierra Leone', 'wp-hotel-booking' ),
			'SG' => __( 'Singapore', 'wp-hotel-booking' ),
			'SK' => __( 'Slovakia', 'wp-hotel-booking' ),
			'SI' => __( 'Slovenia', 'wp-hotel-booking' ),
			'SB' => __( 'Solomon Islands', 'wp-hotel-booking' ),
			'SO' => __( 'Somalia', 'wp-hotel-booking' ),
			'ZA' => __( 'South Africa', 'wp-hotel-booking' ),
			'GS' => __( 'South Georgia/Sandwich Islands', 'wp-hotel-booking' ),
			'KR' => __( 'South Korea', 'wp-hotel-booking' ),
			'SS' => __( 'South Sudan', 'wp-hotel-booking' ),
			'ES' => __( 'Spain', 'wp-hotel-booking' ),
			'LK' => __( 'Sri Lanka', 'wp-hotel-booking' ),
			'SD' => __( 'Sudan', 'wp-hotel-booking' ),
			'SR' => __( 'Suriname', 'wp-hotel-booking' ),
			'SJ' => __( 'Svalbard and Jan Mayen', 'wp-hotel-booking' ),
			'SZ' => __( 'Swaziland', 'wp-hotel-booking' ),
			'SE' => __( 'Sweden', 'wp-hotel-booking' ),
			'CH' => __( 'Switzerland', 'wp-hotel-booking' ),
			'SY' => __( 'Syria', 'wp-hotel-booking' ),
			'TW' => __( 'Taiwan', 'wp-hotel-booking' ),
			'TJ' => __( 'Tajikistan', 'wp-hotel-booking' ),
			'TZ' => __( 'Tanzania', 'wp-hotel-booking' ),
			'TH' => __( 'Thailand', 'wp-hotel-booking' ),
			'TL' => __( 'Timor-Leste', 'wp-hotel-booking' ),
			'TG' => __( 'Togo', 'wp-hotel-booking' ),
			'TK' => __( 'Tokelau', 'wp-hotel-booking' ),
			'TO' => __( 'Tonga', 'wp-hotel-booking' ),
			'TT' => __( 'Trinidad and Tobago', 'wp-hotel-booking' ),
			'TN' => __( 'Tunisia', 'wp-hotel-booking' ),
			'TR' => __( 'Turkey', 'wp-hotel-booking' ),
			'TM' => __( 'Turkmenistan', 'wp-hotel-booking' ),
			'TC' => __( 'Turks and Caicos Islands', 'wp-hotel-booking' ),
			'TV' => __( 'Tuvalu', 'wp-hotel-booking' ),
			'UG' => __( 'Uganda', 'wp-hotel-booking' ),
			'UA' => __( 'Ukraine', 'wp-hotel-booking' ),
			'AE' => __( 'United Arab Emirates', 'wp-hotel-booking' ),
			'GB' => __( 'United Kingdom (UK)', 'wp-hotel-booking' ),
			'US' => __( 'United States (US)', 'wp-hotel-booking' ),
			'UY' => __( 'Uruguay', 'wp-hotel-booking' ),
			'UZ' => __( 'Uzbekistan', 'wp-hotel-booking' ),
			'VU' => __( 'Vanuatu', 'wp-hotel-booking' ),
			'VA' => __( 'Vatican', 'wp-hotel-booking' ),
			'VE' => __( 'Venezuela', 'wp-hotel-booking' ),
			'VN' => __( 'Vietnam', 'wp-hotel-booking' ),
			'WF' => __( 'Wallis and Futuna', 'wp-hotel-booking' ),
			'EH' => __( 'Western Sahara', 'wp-hotel-booking' ),
			'WS' => __( 'Western Samoa', 'wp-hotel-booking' ),
			'YE' => __( 'Yemen', 'wp-hotel-booking' ),
			'ZM' => __( 'Zambia', 'wp-hotel-booking' ),
			'ZW' => __( 'Zimbabwe', 'wp-hotel-booking' )
		);

		return $countries;
	}
}

if ( ! function_exists( 'hb_dropdown_countries' ) ) {
	/**
	 * Drop down to select country.
	 *
	 * @param array $args
	 */
	function hb_dropdown_countries( $args = array() ) {
		$countries = hb_get_countries();
		$args      = wp_parse_args( $args, array(
				'name'              => 'countries',
				'selected'          => '',
				'show_option_none'  => false,
				'option_none_value' => '',
				'required'          => false
			)
		);
		echo '<select name="' . $args['name'] . '"' . ( ( $args['required'] ) ? 'required' : '' ) . '>';
		if ( $args['show_option_none'] ) {
			echo '<option value="' . $args['option_none_value'] . '">' . $args['show_option_none'] . '</option>';
		}
		foreach ( $countries as $code => $name ) {
			echo '<option value="' . $name . '" ' . selected( $name == $args['selected'] ) . '>' . $name . '</option>';
		}
		echo '</select>';
	}
}

if ( ! function_exists( 'hb_add_message' ) ) {
	/**
	 * Add message to show in wrapper shortcodes start.
	 *
	 * @param $message
	 * @param string $type
	 */
	function hb_add_message( $message, $type = 'message' ) {
		$messages = get_transient( 'hb_message_' . session_id() );
		if ( empty( $messages ) ) {
			$messages = array();
		}
		$messages[] = array(
			'type'    => $type,
			'message' => $message
		);
		// hold in transient for 3 minutes
		set_transient( 'hb_message_' . session_id(), $messages, MINUTE_IN_SECONDS * 3 );
	}
}

if ( ! function_exists( 'hb_display_message' ) ) {
	/**
	 * Show message in wrapper shortcodes start.
	 */
	function hb_display_message() {
		if ( $messages = get_transient( 'hb_message_' . session_id() ) ) {
			foreach ( $messages as $message ) {
				?>
                <div class="hb-message <?php echo esc_attr( $message['type'] ); ?>">
                    <div class="hb-message-content">
						<?php echo esc_html( $message['message'] ); ?>
                    </div>
                </div>
				<?php
			}
		}
		delete_transient( 'hb_message_' . session_id() );
	}
}

if ( ! function_exists( 'hb_get_customer_fullname' ) ) {
	/**
	 * @param null $booking_id
	 * @param bool $with_title
	 *
	 * @return string
	 */
	function hb_get_customer_fullname( $booking_id = null, $with_title = false ) {
		if ( $booking_id ) {
			$booking = WPHB_Booking::instance( $booking_id );

			$first_name = $last_name = '';
			if ( $booking->customer_first_name ) {
				$first_name = $booking->customer_first_name;
				$last_name  = $booking->customer_last_name;
			} else if ( $booking->user_id ) {
				$user       = WPHB_User::get_user( $booking->user_id );
				$first_name = $user->first_name;
				$last_name  = $user->last_name;
			}

			if ( $with_title ) {
				$title = hb_get_title_by_slug( $booking->customer_title );
			} else {
				$title = '';
			}

			return sprintf( '%s%s %s', $title ? $title . ' ' : '', $first_name, $last_name );
		}

		return '';
	}
}

if ( ! function_exists( 'is_room_category' ) ) {
	/**
	 * Returns true when viewing a room category.
	 *
	 * @param string $term | The term slug your checking for. Leave blank to return true on any.
	 *
	 * @return bool
	 */
	function is_room_category( $term = '' ) {
		return is_tax( 'hb_room', $term );
	}
}

if ( ! function_exists( 'is_room_taxonomy' ) ) {
	/**
	 * Returns true when viewing a room taxonomy archive.
	 *
	 * @return bool
	 */
	function is_room_taxonomy() {
		return is_tax( get_object_taxonomies( 'hb_room' ) );
	}
}

if ( ! function_exists( 'hb_date_format' ) ) {
	/**
	 * @return mixed
	 */
	function hb_date_format() {
		return apply_filters( 'hb_date_format', 'd M Y' );
	}
}

if ( ! function_exists( 'is_room' ) ) {
	/**
	 * @return bool
	 */
	function is_room() {
		return is_singular( array( 'hb_room' ) );
	}
}

if ( ! function_exists( 'hb_get_url' ) ) {
	/**
	 * @param array $params
	 *
	 * @return mixed
	 */
	function hb_get_url( $params = array() ) {
		$query_str = '';
		if ( ! empty( $params ) ) {
			$query_str = '?hotel-booking-params=' . base64_encode( serialize( $params ) );
		}

		return apply_filters( 'hb_get_url', hb_get_page_permalink( 'search' ) . $query_str, hb_get_page_id( 'search' ), $params );
	}
}

if ( ! function_exists( 'hb_get_cart_url' ) ) {
	/**
	 * @return mixed
	 */
	function hb_get_cart_url() {
		$id = hb_get_page_id( 'cart' );

		$url = home_url();
		if ( $id ) {
			$url = get_the_permalink( $id );
		}

		return apply_filters( 'hb_cart_url', $url );
	}
}

if ( ! function_exists( 'hb_get_checkout_url' ) ) {
	/**
	 * @return mixed
	 */
	function hb_get_checkout_url() {
		$id = hb_get_page_id( 'checkout' );

		$url = home_url();
		if ( $id ) {
			$url = get_the_permalink( $id );
		}

		return apply_filters( 'hb_checkout_url', $url );
	}
}

if ( ! function_exists( 'hb_get_account_url' ) ) {
	/**
	 * @return mixed
	 */
	function hb_get_account_url() {
		$id = hb_get_page_id( 'account' );

		$url = home_url();
		if ( $id ) {
			$url = get_the_permalink( $id );
		}

		return apply_filters( 'hb_account_url', $url );
	}
}

if ( ! function_exists( 'hb_get_thank_you_url' ) ) {
	/**
	 * @param string $booking_id
	 * @param string $booking_key
	 *
	 * @return bool|mixed
	 */
	function hb_get_thank_you_url( $booking_id = '', $booking_key = '' ) {

		if ( ! ( $booking_id && $booking_key ) ) {
			return false;
		}

		$id = hb_get_page_id( 'thankyou' );

		$url = home_url();
		if ( $id ) {
			$url = get_the_permalink( $id );
		}

		return apply_filters( 'hb_thank_you_url', add_query_arg( array(
			'booking' => $booking_id,
			'key'     => $booking_key
		), $url ), $url, $id, $booking_id, $booking_key );
	}
}

if ( ! function_exists( 'hb_random_color_part' ) ) {
	/**
	 * Generate random color part.
	 *
	 * @return string
	 */
	function hb_random_color_part() {
		return str_pad( dechex( mt_rand( 0, 255 ) ), 2, '0', STR_PAD_LEFT );
	}
}

if ( ! function_exists( 'hb_random_color' ) ) {
	/**
	 * Generate random color.
	 *
	 * @return string
	 */
	function hb_random_color() {
		return '#' . hb_random_color_part() . hb_random_color_part() . hb_random_color_part();
	}
}

if ( ! function_exists( 'hb_get_post_id_meta' ) ) {
	/**
	 * @param $key
	 * @param $value
	 *
	 * @return bool
	 */
	function hb_get_post_id_meta( $key, $value ) {
		global $wpdb;
		$meta = $wpdb->get_results( "SELECT * FROM `" . $wpdb->postmeta . "` WHERE meta_key='" . esc_sql( $key ) . "' AND meta_value='" . esc_sql( $value ) . "'" );
		if ( is_array( $meta ) && ! empty( $meta ) && isset( $meta[0] ) ) {
			$meta = $meta[0];
		}
		if ( is_object( $meta ) ) {
			return $meta->post_id;
		} else {
			return false;
		}
	}
}

if ( ! function_exists( 'hb_get_date_format' ) ) {
	/**
	 * Get system date format.
	 *
	 * @return mixed
	 */
	function hb_get_date_format() {
		$date_format = get_option( 'date_format' );

		$custom = get_option( 'date_format_custom' );
		if ( ! $date_format && $custom ) {
			$date_format = $custom;
		}

		return $date_format;
	}
}

if ( ! function_exists( 'hb_get_time_format' ) ) {
	/**
	 * Get system time format.
	 *
	 * @return mixed
	 */
	function hb_get_time_format() {
		$time_format = get_option( 'time_format' );

		$custom = get_option( 'time_format_custom' );
		if ( ! $time_format && $custom ) {
			$time_format = $custom;
		}

		return $time_format;
	}
}

if ( ! function_exists( 'hb_get_pages' ) ) {
	/**
	 * @return mixed
	 */
	function hb_get_pages() {
		global $wpdb;
		$sql   = $wpdb->prepare( "
				SELECT ID, post_title FROM $wpdb->posts
				WHERE $wpdb->posts.post_type = %s AND $wpdb->posts.post_status = %s
				GROUP BY $wpdb->posts.post_name
			", 'page', 'publish' );
		$pages = $wpdb->get_results( $sql );

		return apply_filters( 'hb_get_pages', $pages );
	}

}

if ( ! function_exists( 'hb_dropdown_pages' ) ) {
	/**
	 * @param array $args
	 */
	function hb_dropdown_pages( $args = array() ) {
		$args = wp_parse_args( $args, array(
			'show_option_none'  => __( 'Select page', 'wp-hotel-booking' ),
			'option_none_value' => 0,
			'name'              => '',
			'selected'          => ''
		) );

		$args  = apply_filters( 'hb_dropdown_pages_args', $args );
		$pages = hb_get_pages();

		$html   = array();
		$html[] = '<select name="' . esc_attr( $args['name'] ) . '" >';
		$html[] = '<option value="">' . esc_html( $args['show_option_none'] ) . '</option>';
		foreach ( $pages as $page ) {
			$html[] = '<option value="' . esc_attr( $page->ID ) . '"' . selected( $args['selected'], $page->ID, false ) . '>' . esc_html( $page->post_title ) . '</option>';
		}
		$html[] = '</select>';
		echo implode( '', $html );
	}

}

if ( ! function_exists( 'hb_footer_advertisement' ) ) {
	/**
	 *Footer advertisement.
	 */
	function hb_footer_advertisement() {

		$post_types = apply_filters( 'hb_post_types_footer_advertisement', array(
			'hb_room',
			'hb_booking'
		) );

		$pages = apply_filters( 'hb_pages_footer_advertisement', array(
			'wp-hotel-booking_page_wphb-settings',
			'wp-hotel-booking_page_wphb-about',
			'wp-hotel-booking_page_wphb-about',
			'wp-hotel-booking_page_wphb-addition-packages',
			'wp-hotel-booking_page_wphb-pricing-table'
		) );

		if ( ! $screen = get_current_screen() ) {
			return;
		}

		if ( ! ( ( in_array( $screen->post_type, $post_types ) && $screen->base === 'edit' ) || ( in_array( $screen->id, $pages ) ) ) ) {
			return;
		}

		$current_theme = wp_get_theme();

		// Get items
		$list_themes = (array) WPHB_Helper_Plugins::get_related_themes();
		if ( empty ( $list_themes ) ) {
			return;
		}

		if ( false !== ( $key = array_search( $current_theme->name, array_keys( $list_themes ), true ) ) ) {
			unset( $list_themes[ $key ] );
		}

		shuffle( $list_themes ); ?>

		<?php if ( $list_themes ) { ?>
            <div id="wphb-advertisement" class="wphb-advertisement-slider">
				<?php foreach ( $list_themes as $theme ) {
					if ( empty( $theme['url'] ) ) {
						continue;
					}
					$full_description  = hb_trim_content( $theme['description'] );
					$short_description = hb_trim_content( $theme['description'], 75 );
					$url_demo          = $theme['attributes'][4]['value']; ?>

                    <div id="thimpress-<?php echo esc_attr( $theme['id'] ); ?>" class="slide-item">
                        <div class="slide-thumbnail">
                            <a target="_blank" href="<?php echo esc_url( $theme['url'] ); ?>">
                                <img src="<?php echo esc_url( $theme['previews']['landscape_preview']['landscape_url'] ) ?>"/>
                            </a>
                        </div>

                        <div class="slide-detail">
                            <h2><a href="<?php echo esc_url( $theme['url'] ); ?>"><?php echo $theme['name']; ?></a></h2>
                            <p class="slide-description description-full">
								<?php echo wp_kses_post( $full_description ); ?>
                            </p>
                            <p class="slide-description description-short">
								<?php echo wp_kses_post( $short_description ); ?>
                            </p>
                            <p class="slide-controls">
                                <a href="<?php echo esc_url( $theme['url'] ); ?>" class="button button-primary"
                                   target="_blank"><?php _e( 'Get it now', 'wp-hotel-booking' ); ?></a>
                                <a href="<?php echo esc_url( $url_demo ); ?>" class="button"
                                   target="_blank"><?php _e( 'View Demo', 'wp-hotel-booking' ); ?></a>
                            </p>
                        </div>

                    </div>
				<?php } ?>
            </div>
		<?php }
	}
}

if ( ! function_exists( 'hb_trim_content' ) ) {
	/**
	 * @param $content
	 * @param int $count
	 *
	 * @return array|mixed|null|string|string[]
	 */
	function hb_trim_content( $content, $count = 0 ) {
		$content = preg_replace( '/(?<=\S,)(?=\S)/', ' ', $content );
		$content = str_replace( "\n", ' ', $content );
		$content = explode( " ", $content );

		$count = $count > 0 ? $count : sizeof( $content ) - 1;
		$full  = $count >= sizeof( $content ) - 1;

		$content = array_slice( $content, 0, $count );
		$content = implode( " ", $content );
		if ( ! $full ) {
			$content .= '...';
		}

		return $content;
	}
}

if ( ! function_exists( 'hb_request_query' ) ) {
	/**
	 * @param array $vars
	 *
	 * @return array
	 */
	function hb_request_query( $vars = array() ) {
		global $typenow, $wp_query, $wp_post_statuses;

		if ( 'hb_booking' === $typenow ) {
			// Status
			if ( ! isset( $vars['post_status'] ) ) {
				$post_statuses = hb_get_booking_statuses();

				foreach ( $post_statuses as $status => $value ) {
					if ( isset( $wp_post_statuses[ $status ] ) && false === $wp_post_statuses[ $status ]->show_in_admin_all_list ) {
						unset( $post_statuses[ $status ] );
					}
				}

				$vars['post_status'] = array_keys( $post_statuses );
			}
		}

		return $vars;
	}
}

if ( ! function_exists( 'hb_edit_post_change_title_in_list' ) ) {
	/**
	 * Add hook to change booking title in admin archive.
	 */
	function hb_edit_post_change_title_in_list() {
		add_filter( 'the_title', 'hb_edit_post_new_title_in_list', 100, 2 );
	}
}

if ( ! function_exists( 'hb_edit_post_new_title_in_list' ) ) {
	/**
	 * @param $title
	 * @param $post_id
	 *
	 * @return string
	 */
	function hb_edit_post_new_title_in_list( $title, $post_id ) {
		global $post_type;
		if ( $post_type == 'hb_booking' ) {
			$title = hb_format_order_number( $post_id );
		}

		return $title;
	}
}

if ( ! function_exists( 'hb_remove_revolution_slider_meta_boxes' ) ) {
	/**
	 * Remove revolution slider meta boxes.
	 */
	function hb_remove_revolution_slider_meta_boxes() {
		if ( is_admin() ) {
			remove_meta_box( 'mymetabox_revslider_0', 'hb_room', 'normal' );
			remove_meta_box( 'mymetabox_revslider_0', 'hb_booking', 'normal' );
			remove_meta_box( 'submitdiv', 'hb_booking', 'side' );
		}
	}
}

if ( ! function_exists( 'hb_time_to_seconds' ) ) {
	/**
	 * @param $time
	 * @param bool $origin
	 *
	 * @return float|int
	 */
	function hb_time_to_seconds( $time, $origin = false ) {
		$hour    = substr( $time, 0, 2 );
		$session = substr( $time, - 2 );
		$seconds = $hour * 60 * 60;

		if ( $session === 'PM' ) {
			$seconds += 12 * 60 * 60;
		}

		return ( $origin ) ? $seconds : $seconds - 1;
	}
}

if ( ! function_exists( 'hb_get_rooms_price' ) ) {
	/**
	 * Get min max rooms price.
	 */
	function hb_get_rooms_price() {
		$rooms = WPHB_Room_CURD::get_rooms();
		$price = array();
		if ( is_array( $rooms ) ) { ?>
			<?php foreach ( $rooms as $room ) {
				$plan               = hb_room_get_selected_plan( $room->ID );
				$price[ $room->ID ] = array(
					'min' => min( $plan->prices ),
					'max' => max( $plan->prices )
				);
			} ?>
		<?php }

		return $price;
	}
}

if ( ! function_exists( 'hb_get_min_max_rooms_price' ) ) {
	/**
	 * Get min max room price.
	 */
	function hb_get_min_max_rooms_price() {
		$prices = hb_get_rooms_price();
		if ( $prices ) {
			$min = reset( $prices )['min'];
			$max = reset( $prices )['max'];
			foreach ( $prices as $room_id => $price ) {
				if ( $min > $price['min'] ) {
					$min = $price['min'];
				}
				if ( $max < $price['max'] ) {
					$max = $price['max'];
				}
			}
		} else {
			$min = $max = 0;
		}

		return array( 'min' => $min, 'max' => $max );
	}
}