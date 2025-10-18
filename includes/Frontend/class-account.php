<?php
/**
 * Account Class
 *
 * @package RoyalStorage\Frontend
 * @since 1.0.0
 */

namespace RoyalStorage\Frontend;

/**
 * Account class for customer account management
 */
class Account {

	/**
	 * Constructor
	 */
	public function __construct() {
		// Account initialization
	}

	/**
	 * Get customer data
	 *
	 * @param int $user_id User ID.
	 * @return object
	 */
	public function get_customer_data( $user_id ) {
		$user = get_user_by( 'id', $user_id );
		
		if ( ! $user ) {
			return new \stdClass();
		}

		$customer_data = new \stdClass();
		$customer_data->display_name = $user->display_name;
		$customer_data->first_name = get_user_meta( $user_id, 'first_name', true );
		$customer_data->last_name = get_user_meta( $user_id, 'last_name', true );
		$customer_data->email = $user->user_email;
		$customer_data->phone = get_user_meta( $user_id, 'billing_phone', true );
		$customer_data->address = get_user_meta( $user_id, 'billing_address_1', true );
		$customer_data->city = get_user_meta( $user_id, 'billing_city', true );
		$customer_data->postcode = get_user_meta( $user_id, 'billing_postcode', true );
		$customer_data->country = get_user_meta( $user_id, 'billing_country', true );
		$customer_data->company = get_user_meta( $user_id, 'billing_company', true );
		$customer_data->tax_id = get_user_meta( $user_id, 'billing_vat_number', true );
		$customer_data->registration_date = $user->user_registered;
		$customer_data->last_login = get_user_meta( $user_id, 'last_login', true );

		// Get booking statistics
		$customer_data->total_bookings = $this->get_total_bookings( $user_id );
		$customer_data->active_bookings = $this->get_active_bookings( $user_id );
		$customer_data->total_spent = $this->get_total_spent( $user_id );

		return $customer_data;
	}

	/**
	 * Get total bookings count
	 *
	 * @param int $user_id User ID.
	 * @return int
	 */
	private function get_total_bookings( $user_id ) {
		global $wpdb;
		$bookings_table = $wpdb->prefix . 'royal_bookings';
		
		return (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$bookings_table} WHERE customer_id = %d",
				$user_id
			)
		);
	}

	/**
	 * Get active bookings count
	 *
	 * @param int $user_id User ID.
	 * @return int
	 */
	private function get_active_bookings( $user_id ) {
		global $wpdb;
		$bookings_table = $wpdb->prefix . 'royal_bookings';
		
		return (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$bookings_table} WHERE customer_id = %d AND status IN ('active', 'confirmed', 'pending')",
				$user_id
			)
		);
	}

	/**
	 * Get total amount spent
	 *
	 * @param int $user_id User ID.
	 * @return float
	 */
	private function get_total_spent( $user_id ) {
		global $wpdb;
		$bookings_table = $wpdb->prefix . 'royal_bookings';
		
		$total = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT SUM(total_price) FROM {$bookings_table} WHERE customer_id = %d AND payment_status = 'paid'",
				$user_id
			)
		);

		return $total ? (float) $total : 0.0;
	}

	/**
	 * Update customer profile
	 *
	 * @param int   $user_id User ID.
	 * @param array $data Profile data.
	 * @return bool
	 */
	public function update_profile( $user_id, $data ) {
		$user = get_user_by( 'id', $user_id );
		
		if ( ! $user ) {
			return false;
		}

		// Update user basic info
		if ( isset( $data['first_name'] ) ) {
			update_user_meta( $user_id, 'first_name', sanitize_text_field( $data['first_name'] ) );
		}
		
		if ( isset( $data['last_name'] ) ) {
			update_user_meta( $user_id, 'last_name', sanitize_text_field( $data['last_name'] ) );
		}
		
		if ( isset( $data['display_name'] ) ) {
			wp_update_user( array(
				'ID' => $user_id,
				'display_name' => sanitize_text_field( $data['display_name'] )
			) );
		}

		// Update billing information
		if ( isset( $data['phone'] ) ) {
			update_user_meta( $user_id, 'billing_phone', sanitize_text_field( $data['phone'] ) );
		}
		
		if ( isset( $data['address'] ) ) {
			update_user_meta( $user_id, 'billing_address_1', sanitize_text_field( $data['address'] ) );
		}
		
		if ( isset( $data['city'] ) ) {
			update_user_meta( $user_id, 'billing_city', sanitize_text_field( $data['city'] ) );
		}
		
		if ( isset( $data['postcode'] ) ) {
			update_user_meta( $user_id, 'billing_postcode', sanitize_text_field( $data['postcode'] ) );
		}
		
		if ( isset( $data['country'] ) ) {
			update_user_meta( $user_id, 'billing_country', sanitize_text_field( $data['country'] ) );
		}
		
		if ( isset( $data['company'] ) ) {
			update_user_meta( $user_id, 'billing_company', sanitize_text_field( $data['company'] ) );
		}
		
		if ( isset( $data['tax_id'] ) ) {
			update_user_meta( $user_id, 'billing_vat_number', sanitize_text_field( $data['tax_id'] ) );
		}

		return true;
	}

	/**
	 * Change password
	 *
	 * @param int    $user_id User ID.
	 * @param string $new_password New password.
	 * @return bool
	 */
	public function change_password( $user_id, $new_password ) {
		$user = get_user_by( 'id', $user_id );
		
		if ( ! $user ) {
			return false;
		}

		wp_set_password( $new_password, $user_id );
		
		// Log out user after password change
		wp_logout();
		
		return true;
	}
}