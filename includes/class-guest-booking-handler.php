<?php
/**
 * Guest Booking Handler Class
 *
 * @package RoyalStorage
 * @since 1.0.1
 */

namespace RoyalStorage;

/**
 * Guest Booking Handler class
 */
class Guest_Booking_Handler {

	/**
	 * Check if guest checkout is enabled
	 *
	 * @return bool
	 */
	public static function is_guest_checkout_enabled() {
		return get_option( 'royal_storage_guest_checkout', 'yes' ) === 'yes';
	}

	/**
	 * Check if auto account creation is enabled
	 *
	 * @return bool
	 */
	public static function is_auto_account_creation_enabled() {
		return get_option( 'royal_storage_auto_create_account', 'yes' ) === 'yes';
	}

	/**
	 * Create or get guest customer account
	 *
	 * @param string $email Email address.
	 * @param string $first_name First name.
	 * @param string $last_name Last name.
	 * @param string $phone Phone number.
	 * @return int|false User ID or false on failure.
	 */
	public static function create_or_get_guest_customer( $email, $first_name = '', $last_name = '', $phone = '' ) {
		// Sanitize email
		$email = sanitize_email( $email );

		if ( ! is_email( $email ) ) {
			return false;
		}

		// Check if user already exists
		$existing_user = get_user_by( 'email', $email );

		if ( $existing_user ) {
			// Update user meta with provided information
			if ( ! empty( $first_name ) ) {
				update_user_meta( $existing_user->ID, 'first_name', sanitize_text_field( $first_name ) );
			}
			if ( ! empty( $last_name ) ) {
				update_user_meta( $existing_user->ID, 'last_name', sanitize_text_field( $last_name ) );
			}
			if ( ! empty( $phone ) ) {
				update_user_meta( $existing_user->ID, 'billing_phone', sanitize_text_field( $phone ) );
			}
			return $existing_user->ID;
		}

		// Check if auto account creation is enabled
		if ( ! self::is_auto_account_creation_enabled() ) {
			return false;
		}

		// Create new WordPress user
		$username = self::generate_username( $email );
		$password = wp_generate_password( 12, true, true );

		$user_data = array(
			'user_login'    => $username,
			'user_email'    => $email,
			'user_pass'     => $password,
			'role'          => 'customer',
			'first_name'    => sanitize_text_field( $first_name ),
			'last_name'     => sanitize_text_field( $last_name ),
		);

		$user_id = wp_insert_user( $user_data );

		if ( is_wp_error( $user_id ) ) {
			return false;
		}

		// Add user meta
		if ( ! empty( $phone ) ) {
			update_user_meta( $user_id, 'billing_phone', sanitize_text_field( $phone ) );
		}

		// Mark as guest customer
		update_user_meta( $user_id, '_royal_storage_is_guest_customer', 'yes' );
		update_user_meta( $user_id, '_royal_storage_account_created_at', current_time( 'mysql' ) );

		// Auto-login the new user for seamless checkout
		wp_set_current_user( $user_id );
		wp_set_auth_cookie( $user_id );

		// Send account notification email
		self::send_guest_account_notification( $user_id, $password );

		return $user_id;
	}

	/**
	 * Generate unique username from email
	 *
	 * @param string $email Email address.
	 * @return string
	 */
	private static function generate_username( $email ) {
		// Get email prefix
		$username = sanitize_user( current( explode( '@', $email ) ), true );

		// If username already exists, append numbers
		$original_username = $username;
	reserved_names:
		$username = $original_username;
		$counter = 0;
		while ( username_exists( $username ) && $counter < 100 ) {
			$counter++;
			$username = $original_username . $counter;
		}

		// Fallback to email if all else fails
		if ( $counter >= 100 ) {
			$username = $email;
		}

		return $username;
	}

	/**
	 * Send guest account notification email
	 *
	 * @param int    $user_id User ID.
	 * @param string $password Password.
	 * @return bool
	 */
	public static function send_guest_account_notification( $user_id, $password ) {
		$send_credentials = get_option( 'royal_storage_send_account_credentials', 'yes' ) === 'yes';

		if ( ! $send_credentials ) {
			return true; // Not a failure, just disabled
		}

		$user = get_user_by( 'id', $user_id );
		if ( ! $user ) {
			return false;
		}

		$email_manager = new EmailManager();
		return $email_manager->send_guest_account_created_email( $user_id, $password );
	}

	/**
	 * Get guest customer data from POST request
	 *
	 * @return array|false
	 */
	public static function get_guest_data_from_request() {
		$email = isset( $_POST['guest_email'] ) ? sanitize_email( $_POST['guest_email'] ) : '';
		$first_name = isset( $_POST['guest_first_name'] ) ? sanitize_text_field( $_POST['guest_first_name'] ) : '';
		$last_name = isset( $_POST['guest_last_name'] ) ? sanitize_text_field( $_POST['guest_last_name'] ) : '';
		$phone = isset( $_POST['guest_phone'] ) ? sanitize_text_field( $_POST['guest_phone'] ) : '';

		if ( empty( $email ) ) {
			return false;
		}

		return array(
			'email'      => $email,
			'first_name' => $first_name,
			'last_name'  => $last_name,
			'phone'      => $phone,
		);
	}

	/**
	 * Validate guest data
	 *
	 * @param array $guest_data Guest data.
	 * @return bool|string True if valid, error message if invalid.
	 */
	public static function validate_guest_data( $guest_data ) {
		if ( empty( $guest_data['email'] ) ) {
			return __( 'Email address is required.', 'royal-storage' );
		}

		if ( ! is_email( $guest_data['email'] ) ) {
			return __( 'Invalid email address.', 'royal-storage' );
		}

		if ( empty( $guest_data['first_name'] ) || empty( $guest_data['last_name'] ) ) {
			return __( 'First name and last name are required.', 'royal-storage' );
		}

		if ( empty( $guest_data['phone'] ) ) {
			return __( 'Phone number is required.', 'royal-storage' );
		}

		return true;
	}
}


