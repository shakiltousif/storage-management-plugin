<?php
/**
 * Account Class
 *
 * @package RoyalStorage\Frontend
 * @since 1.0.0
 */

namespace RoyalStorage\Frontend;

/**
 * Account class for frontend account management
 */
class Account {

	/**
	 * Constructor
	 */
	public function __construct() {
		// Account functionality initialization
	}

	/**
	 * Get customer account data
	 *
	 * @param int $customer_id Customer ID.
	 * @return object
	 */
	public function get_customer_data( $customer_id ) {
		$user = get_user_by( 'id', $customer_id );
		
		if ( ! $user ) {
			return null;
		}

		return (object) array(
			'id'           => $user->ID,
			'display_name' => $user->display_name,
			'email'        => $user->user_email,
			'first_name'   => $user->first_name,
			'last_name'    => $user->last_name,
			'phone'        => get_user_meta( $customer_id, 'phone', true ),
			'address'      => get_user_meta( $customer_id, 'address', true ),
			'city'         => get_user_meta( $customer_id, 'city', true ),
			'postcode'     => get_user_meta( $customer_id, 'postcode', true ),
			'country'      => get_user_meta( $customer_id, 'country', true ),
		);
	}

	/**
	 * Update customer account data
	 *
	 * @param int   $customer_id Customer ID.
	 * @param array $data Account data.
	 * @return bool
	 */
	public function update_customer_data( $customer_id, $data ) {
		$user_data = array();
		
		if ( isset( $data['display_name'] ) ) {
			$user_data['display_name'] = sanitize_text_field( $data['display_name'] );
		}
		
		if ( isset( $data['email'] ) ) {
			$user_data['user_email'] = sanitize_email( $data['email'] );
		}
		
		if ( isset( $data['first_name'] ) ) {
			$user_data['first_name'] = sanitize_text_field( $data['first_name'] );
		}
		
		if ( isset( $data['last_name'] ) ) {
			$user_data['last_name'] = sanitize_text_field( $data['last_name'] );
		}

		// Update user data
		if ( ! empty( $user_data ) ) {
			$user_data['ID'] = $customer_id;
			$result = wp_update_user( $user_data );
			
			if ( is_wp_error( $result ) ) {
				return false;
			}
		}

		// Update user meta
		$meta_fields = array( 'phone', 'address', 'city', 'postcode', 'country' );
		
		foreach ( $meta_fields as $field ) {
			if ( isset( $data[ $field ] ) ) {
				update_user_meta( $customer_id, $field, sanitize_text_field( $data[ $field ] ) );
			}
		}

		return true;
	}

	/**
	 * Get customer statistics
	 *
	 * @param int $customer_id Customer ID.
	 * @return object
	 */
	public function get_customer_stats( $customer_id ) {
		global $wpdb;

		$bookings_table = $wpdb->prefix . 'royal_bookings';
		$invoices_table = $wpdb->prefix . 'royal_invoices';

		// Get booking statistics
		$total_bookings = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM $bookings_table WHERE customer_id = %d",
				$customer_id
			)
		);

		$active_bookings = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM $bookings_table WHERE customer_id = %d AND status = 'active'",
				$customer_id
			)
		);

		$total_spent = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT SUM(total_price) FROM $bookings_table WHERE customer_id = %d AND payment_status = 'paid'",
				$customer_id
			)
		);

		// Get invoice statistics
		$total_invoices = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM $invoices_table i 
				JOIN $bookings_table b ON i.booking_id = b.id 
				WHERE b.customer_id = %d",
				$customer_id
			)
		);

		$unpaid_invoices = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM $invoices_table i 
				JOIN $bookings_table b ON i.booking_id = b.id 
				WHERE b.customer_id = %d AND i.status = 'unpaid'",
				$customer_id
			)
		);

		return (object) array(
			'total_bookings'   => intval( $total_bookings ?: 0 ),
			'active_bookings'  => intval( $active_bookings ?: 0 ),
			'total_spent'      => floatval( $total_spent ?: 0 ),
			'total_invoices'   => intval( $total_invoices ?: 0 ),
			'unpaid_invoices'  => intval( $unpaid_invoices ?: 0 ),
		);
	}
}