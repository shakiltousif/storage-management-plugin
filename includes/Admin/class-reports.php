<?php
/**
 * Reports Class
 *
 * @package RoyalStorage\Admin
 * @since 1.0.0
 */

namespace RoyalStorage\Admin;

/**
 * Reports class for admin reports
 */
class Reports {

	/**
	 * Constructor
	 */
	public function __construct() {
		add_action( 'admin_init', array( $this, 'init' ) );
		add_action( 'admin_post_royal_storage_export_csv', array( $this, 'handle_export_csv' ) );
	}

	/**
	 * Initialize reports
	 *
	 * @return void
	 */
	public function init() {
		// Reports initialization code.
	}

	/**
	 * Handle CSV export
	 *
	 * @return void
	 */
	public function handle_export_csv() {
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'royal_storage_export_csv' ) ) {
			wp_die( esc_html__( 'Security check failed', 'royal-storage' ) );
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Unauthorized', 'royal-storage' ) );
		}

		$filters = array(
			'start_date' => isset( $_POST['start_date'] ) ? sanitize_text_field( wp_unslash( $_POST['start_date'] ) ) : '',
			'end_date'   => isset( $_POST['end_date'] ) ? sanitize_text_field( wp_unslash( $_POST['end_date'] ) ) : '',
		);

		$csv = $this->export_bookings_csv( $filters );

		header( 'Content-Type: text/csv; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename="bookings-' . gmdate( 'Y-m-d' ) . '.csv"' );
		echo $csv;
		exit;
	}

	/**
	 * Export bookings to CSV
	 *
	 * @param array $filters Filters for the report.
	 * @return string CSV content
	 */
	public function export_bookings_csv( $filters = array() ) {
		global $wpdb;

		$bookings_table = $wpdb->prefix . 'royal_bookings';

		$query = "SELECT * FROM $bookings_table WHERE 1=1";

		if ( ! empty( $filters['start_date'] ) ) {
			$query .= $wpdb->prepare( ' AND start_date >= %s', $filters['start_date'] );
		}

		if ( ! empty( $filters['end_date'] ) ) {
			$query .= $wpdb->prepare( ' AND end_date <= %s', $filters['end_date'] );
		}

		$bookings = $wpdb->get_results( $query );

		$csv = "ID,Customer ID,Unit ID,Start Date,End Date,Total Price,VAT,Status,Payment Status\n";

		foreach ( $bookings as $booking ) {
			$csv .= "{$booking->id},{$booking->customer_id},{$booking->unit_id},{$booking->start_date},{$booking->end_date},{$booking->total_price},{$booking->vat_amount},{$booking->status},{$booking->payment_status}\n";
		}

		return $csv;
	}

	/**
	 * Get revenue report
	 *
	 * @param string $start_date Start date.
	 * @param string $end_date End date.
	 * @return array
	 */
	public function get_revenue_report( $start_date, $end_date ) {
		global $wpdb;

		$bookings_table = $wpdb->prefix . 'royal_bookings';

		return $wpdb->get_results(
			$wpdb->prepare(
				"SELECT DATE(created_at) as date, SUM(total_price) as revenue, COUNT(*) as bookings FROM $bookings_table WHERE created_at BETWEEN %s AND %s AND payment_status = 'paid' GROUP BY DATE(created_at)",
				$start_date,
				$end_date
			)
		);
	}

	/**
	 * Get occupancy report
	 *
	 * @param string $start_date Start date.
	 * @param string $end_date End date.
	 * @return array
	 */
	public function get_occupancy_report( $start_date, $end_date ) {
		global $wpdb;

		$bookings_table = $wpdb->prefix . 'royal_bookings';
		$units_table = $wpdb->prefix . 'royal_storage_units';

		$total_units = $wpdb->get_var( "SELECT COUNT(*) FROM $units_table" );

		$occupied = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT DATE(start_date) as date, COUNT(DISTINCT unit_id) as occupied FROM $bookings_table WHERE start_date BETWEEN %s AND %s AND status IN ('confirmed', 'active') GROUP BY DATE(start_date)",
				$start_date,
				$end_date
			)
		);

		return array(
			'total_units' => $total_units,
			'occupied'    => $occupied,
		);
	}

	/**
	 * Get payment report
	 *
	 * @param string $start_date Start date.
	 * @param string $end_date End date.
	 * @return array
	 */
	public function get_payment_report( $start_date, $end_date ) {
		global $wpdb;

		$bookings_table = $wpdb->prefix . 'royal_bookings';

		return $wpdb->get_results(
			$wpdb->prepare(
				"SELECT payment_status, COUNT(*) as count, SUM(total_price) as total FROM $bookings_table WHERE created_at BETWEEN %s AND %s GROUP BY payment_status",
				$start_date,
				$end_date
			)
		);
	}

	/**
	 * Get total revenue
	 *
	 * @param string $start_date Start date.
	 * @param string $end_date End date.
	 * @return float
	 */
	public function get_total_revenue( $start_date, $end_date ) {
		global $wpdb;

		$bookings_table = $wpdb->prefix . 'royal_bookings';

		$result = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT SUM(total_price) FROM $bookings_table WHERE created_at BETWEEN %s AND %s AND payment_status = 'paid'",
				$start_date,
				$end_date
			)
		);

		return (float) $result;
	}
}

