<?php

/**
 * WP Hotel Booking user class.
 *
 * @class       WPHB_User
 * @version     2.0
 * @package     WP_Hotel_Booking/Classes
 * @category    Class
 * @author      Thimpress, leehld
 */

/**
 * Prevent loading this file directly
 */
defined( 'ABSPATH' ) || exit;


if ( ! class_exists( 'WPHB_User' ) ) {

	/**
	 * Class WPHB_User.
	 *
	 * @since 2.0
	 */
	class WPHB_User extends WPHB_Abstract_User {

		/**
		 * @var null
		 */
		static $users = null;

		// get user

		/**
		 * Get user.
		 *
		 * @since 2.0
		 *
		 * @param null $user_id
		 *
		 * @return WPHB_User
		 */
		public static function get_user( $user_id = null ) {
			if ( ! empty( self::$users[ $user_id ] ) ) {
				return self::$users[ $user_id ];
			}

			return self::$users[ $user_id ] = new self( $user_id );
		}

		/**
		 * Get current user.
		 *
		 * @since 2.0
		 *
		 * @return WPHB_User
		 */
		public static function get_current_user() {
			$user_id = get_current_user_id();

			return self::get_user( $user_id );
		}

	}

}