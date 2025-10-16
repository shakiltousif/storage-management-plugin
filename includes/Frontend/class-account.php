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
		add_action( 'wp_ajax_royal_storage_update_profile', array( $this, 'handle_update_profile' ) );
		add_action( 'wp_ajax_royal_storage_change_password', array( $this, 'handle_change_password' ) );
	}

	/**
	 * Get account stats
	 *
	 * @param int $customer_id Customer ID.
	 * @return object
	 */
	public function get_account_stats( $customer_id ) {
		global $wpdb;

		$bookings_table = $wpdb->prefix . 'royal_bookings';
		$invoices_table = $wpdb->prefix . 'royal_invoices';

		$active_bookings = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM $bookings_table WHERE customer_id = %d AND status IN ('confirmed', 'active')",
				$customer_id
			)
		);

		$unpaid_invoices = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM $invoices_table WHERE customer_id = %d AND payment_status = 'unpaid'",
				$customer_id
			)
		);

		$total_spent = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT SUM(total_price) FROM $bookings_table WHERE customer_id = %d AND payment_status = 'paid'",
				$customer_id
			)
		);

		$unpaid_amount = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT SUM(total_amount) FROM $invoices_table WHERE customer_id = %d AND payment_status = 'unpaid'",
				$customer_id
			)
		);

		return (object) array(
			'active_bookings'  => $active_bookings,
			'unpaid_invoices'  => $unpaid_invoices,
			'total_spent'      => floatval( $total_spent ?: 0 ),
			'unpaid_amount'    => floatval( $unpaid_amount ?: 0 ),
		);
	}

	/**
	 * Get customer info
	 *
	 * @param int $customer_id Customer ID.
	 * @return object|null
	 */
	public function get_customer_info( $customer_id ) {
		$user = get_user_by( 'id', $customer_id );

		if ( ! $user ) {
			return null;
		}

		return (object) array(
			'id'           => $user->ID,
			'name'         => $user->display_name,
			'email'        => $user->user_email,
			'phone'        => get_user_meta( $user->ID, 'phone', true ),
			'address'      => get_user_meta( $user->ID, 'address', true ),
			'city'         => get_user_meta( $user->ID, 'city', true ),
			'postal_code'  => get_user_meta( $user->ID, 'postal_code', true ),
			'country'      => get_user_meta( $user->ID, 'country', true ),
			'company'      => get_user_meta( $user->ID, 'company', true ),
			'tax_id'       => get_user_meta( $user->ID, 'tax_id', true ),
		);
	}

	/**
	 * Update customer profile
	 *
	 * @param int   $customer_id Customer ID.
	 * @param array $data Profile data.
	 * @return bool
	 */
	public function update_profile( $customer_id, $data ) {
		$user_data = array(
			'ID'           => $customer_id,
			'display_name' => $data['name'] ?? '',
		);

		$result = wp_update_user( $user_data );

		if ( is_wp_error( $result ) ) {
			return false;
		}

		// Update user meta
		update_user_meta( $customer_id, 'phone', $data['phone'] ?? '' );
		update_user_meta( $customer_id, 'address', $data['address'] ?? '' );
		update_user_meta( $customer_id, 'city', $data['city'] ?? '' );
		update_user_meta( $customer_id, 'postal_code', $data['postal_code'] ?? '' );
		update_user_meta( $customer_id, 'country', $data['country'] ?? '' );
		update_user_meta( $customer_id, 'company', $data['company'] ?? '' );
		update_user_meta( $customer_id, 'tax_id', $data['tax_id'] ?? '' );

		return true;
	}

	/**
	 * Change password
	 *
	 * @param int    $customer_id Customer ID.
	 * @param string $new_password New password.
	 * @return bool
	 */
	public function change_password( $customer_id, $new_password ) {
		wp_set_password( $new_password, $customer_id );
		return true;
	}

	/**
	 * Handle update profile AJAX
	 *
	 * @return void
	 */
	public function handle_update_profile() {
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'royal_storage_portal' ) ) {
			wp_send_json_error( array( 'message' => __( 'Security check failed', 'royal-storage' ) ) );
		}

		if ( ! is_user_logged_in() ) {
			wp_send_json_error( array( 'message' => __( 'Not logged in', 'royal-storage' ) ) );
		}

		$customer_id = get_current_user_id();
		$data = array(
			'name'        => isset( $_POST['name'] ) ? sanitize_text_field( wp_unslash( $_POST['name'] ) ) : '',
			'phone'       => isset( $_POST['phone'] ) ? sanitize_text_field( wp_unslash( $_POST['phone'] ) ) : '',
			'address'     => isset( $_POST['address'] ) ? sanitize_text_field( wp_unslash( $_POST['address'] ) ) : '',
			'city'        => isset( $_POST['city'] ) ? sanitize_text_field( wp_unslash( $_POST['city'] ) ) : '',
			'postal_code' => isset( $_POST['postal_code'] ) ? sanitize_text_field( wp_unslash( $_POST['postal_code'] ) ) : '',
			'country'     => isset( $_POST['country'] ) ? sanitize_text_field( wp_unslash( $_POST['country'] ) ) : '',
			'company'     => isset( $_POST['company'] ) ? sanitize_text_field( wp_unslash( $_POST['company'] ) ) : '',
			'tax_id'      => isset( $_POST['tax_id'] ) ? sanitize_text_field( wp_unslash( $_POST['tax_id'] ) ) : '',
		);

		if ( $this->update_profile( $customer_id, $data ) ) {
			wp_send_json_success( array( 'message' => __( 'Profile updated successfully', 'royal-storage' ) ) );
		} else {
			wp_send_json_error( array( 'message' => __( 'Failed to update profile', 'royal-storage' ) ) );
		}
	}

	/**
	 * Handle change password AJAX
	 *
	 * @return void
	 */
	public function handle_change_password() {
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'royal_storage_portal' ) ) {
			wp_send_json_error( array( 'message' => __( 'Security check failed', 'royal-storage' ) ) );
		}

		if ( ! is_user_logged_in() ) {
			wp_send_json_error( array( 'message' => __( 'Not logged in', 'royal-storage' ) ) );
		}

		$customer_id = get_current_user_id();
		$current_password = isset( $_POST['current_password'] ) ? sanitize_text_field( wp_unslash( $_POST['current_password'] ) ) : '';
		$new_password = isset( $_POST['new_password'] ) ? sanitize_text_field( wp_unslash( $_POST['new_password'] ) ) : '';

		$user = get_user_by( 'id', $customer_id );

		if ( ! wp_check_password( $current_password, $user->user_pass, $customer_id ) ) {
			wp_send_json_error( array( 'message' => __( 'Current password is incorrect', 'royal-storage' ) ) );
		}

		if ( $this->change_password( $customer_id, $new_password ) ) {
			wp_send_json_success( array( 'message' => __( 'Password changed successfully', 'royal-storage' ) ) );
		} else {
			wp_send_json_error( array( 'message' => __( 'Failed to change password', 'royal-storage' ) ) );
		}
	}
}

