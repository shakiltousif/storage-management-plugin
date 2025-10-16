<?php
/**
 * Customers Class
 *
 * @package RoyalStorage\Admin
 * @since 1.0.0
 */

namespace RoyalStorage\Admin;

/**
 * Customers class for admin customer management
 */
class Customers {

	/**
	 * Constructor
	 */
	public function __construct() {
		add_action( 'admin_init', array( $this, 'init' ) );
		add_action( 'admin_post_royal_storage_export_customers_csv', array( $this, 'handle_export_csv' ) );
	}

	/**
	 * Initialize customers
	 *
	 * @return void
	 */
	public function init() {
		// Customers initialization code.
	}

	/**
	 * Get all customers
	 *
	 * @param int $limit Limit.
	 * @param int $offset Offset.
	 * @return array
	 */
	public function get_customers( $limit = 20, $offset = 0 ) {
		global $wpdb;

		$bookings_table = $wpdb->prefix . 'royal_bookings';

		return $wpdb->get_results(
			$wpdb->prepare(
				"SELECT DISTINCT customer_id FROM $bookings_table ORDER BY customer_id DESC LIMIT %d OFFSET %d",
				$limit,
				$offset
			)
		);
	}

	/**
	 * Get customer bookings
	 *
	 * @param int $customer_id Customer ID.
	 * @return array
	 */
	public function get_customer_bookings( $customer_id ) {
		global $wpdb;

		$bookings_table = $wpdb->prefix . 'royal_bookings';

		return $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM $bookings_table WHERE customer_id = %d ORDER BY created_at DESC",
				$customer_id
			)
		);
	}

	/**
	 * Get customer total spent
	 *
	 * @param int $customer_id Customer ID.
	 * @return float
	 */
	public function get_customer_total_spent( $customer_id ) {
		global $wpdb;

		$bookings_table = $wpdb->prefix . 'royal_bookings';

		$result = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT SUM(total_price) FROM $bookings_table WHERE customer_id = %d AND payment_status = 'paid'",
				$customer_id
			)
		);

		return $result ? floatval( $result ) : 0;
	}

	/**
	 * Get customer overdue amount
	 *
	 * @param int $customer_id Customer ID.
	 * @return float
	 */
	public function get_customer_overdue_amount( $customer_id ) {
		global $wpdb;

		$bookings_table = $wpdb->prefix . 'royal_bookings';

		$result = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT SUM(total_price) FROM $bookings_table WHERE customer_id = %d AND payment_status IN ('unpaid', 'pending') AND end_date < CURDATE()",
				$customer_id
			)
		);

		return $result ? floatval( $result ) : 0;
	}

	/**
	 * Get customer active bookings count
	 *
	 * @param int $customer_id Customer ID.
	 * @return int
	 */
	public function get_customer_active_bookings_count( $customer_id ) {
		global $wpdb;

		$bookings_table = $wpdb->prefix . 'royal_bookings';

		return (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM $bookings_table WHERE customer_id = %d AND status IN ('confirmed', 'active')",
				$customer_id
			)
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
			'id'              => $user->ID,
			'name'            => $user->display_name,
			'email'           => $user->user_email,
			'phone'           => get_user_meta( $user->ID, 'phone', true ),
			'total_spent'     => $this->get_customer_total_spent( $customer_id ),
			'overdue_amount'  => $this->get_customer_overdue_amount( $customer_id ),
			'active_bookings' => $this->get_customer_active_bookings_count( $customer_id ),
		);
	}

	/**
	 * Get total customers count
	 *
	 * @return int
	 */
	public function get_customers_count() {
		global $wpdb;

		$bookings_table = $wpdb->prefix . 'royal_bookings';

		return (int) $wpdb->get_var( "SELECT COUNT(DISTINCT customer_id) FROM $bookings_table" );
	}

	/**
	 * Handle CSV export
	 *
	 * @return void
	 */
	public function handle_export_csv() {
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'royal_storage_export_customers_csv' ) ) {
			wp_die( esc_html__( 'Security check failed', 'royal-storage' ) );
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Unauthorized', 'royal-storage' ) );
		}

		$csv = $this->export_customers_csv();

		header( 'Content-Type: text/csv; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename="customers-' . gmdate( 'Y-m-d' ) . '.csv"' );
		echo $csv;
		exit;
	}

	/**
	 * Export customers to CSV
	 *
	 * @return string CSV content
	 */
	public function export_customers_csv() {
		global $wpdb;

		$bookings_table = $wpdb->prefix . 'royal_bookings';
		$users_table = $wpdb->users;
		$usermeta_table = $wpdb->usermeta;

		$query = "
			SELECT DISTINCT b.customer_id, u.display_name, u.user_email, u.user_registered,
				(SELECT meta_value FROM $usermeta_table WHERE user_id = b.customer_id AND meta_key = 'phone' LIMIT 1) as phone
			FROM $bookings_table b
			INNER JOIN $users_table u ON b.customer_id = u.ID
			ORDER BY b.customer_id DESC
		";

		$customers = $wpdb->get_results( $query );

		$csv = "Customer ID,Name,Email,Phone,Total Spent,Overdue Amount,Active Bookings,Total Bookings,Member Since\n";

		foreach ( $customers as $customer ) {
			$total_spent = $this->get_customer_total_spent( $customer->customer_id );
			$overdue_amount = $this->get_customer_overdue_amount( $customer->customer_id );
			$active_bookings = $this->get_customer_active_bookings_count( $customer->customer_id );
			$total_bookings = count( $this->get_customer_bookings( $customer->customer_id ) );

			$csv .= sprintf(
				"%d,%s,%s,%s,%.2f,%.2f,%d,%d,%s\n",
				$customer->customer_id,
				$customer->display_name,
				$customer->user_email,
				$customer->phone ?: '',
				$total_spent,
				$overdue_amount,
				$active_bookings,
				$total_bookings,
				date( 'Y-m-d', strtotime( $customer->user_registered ) )
			);
		}

		return $csv;
	}
}

