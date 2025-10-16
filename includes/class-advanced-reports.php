<?php
/**
 * Advanced Reports Class
 *
 * @package RoyalStorage
 * @since 1.0.0
 */

namespace RoyalStorage;

/**
 * Advanced reports class for analytics and reporting
 */
class AdvancedReports {

	/**
	 * Get revenue report
	 *
	 * @param string $start_date Start date.
	 * @param string $end_date End date.
	 * @return object
	 */
	public function get_revenue_report( $start_date, $end_date ) {
		global $wpdb;

		$bookings_table = $wpdb->prefix . 'royal_bookings';

		$total_revenue = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT SUM(total_price) FROM $bookings_table WHERE payment_status = 'paid' AND created_at BETWEEN %s AND %s",
				$start_date,
				$end_date
			)
		);

		$total_bookings = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM $bookings_table WHERE payment_status = 'paid' AND created_at BETWEEN %s AND %s",
				$start_date,
				$end_date
			)
		);

		$average_booking = $total_bookings > 0 ? floatval( $total_revenue ) / intval( $total_bookings ) : 0;

		return (object) array(
			'total_revenue'     => floatval( $total_revenue ?: 0 ),
			'total_bookings'    => intval( $total_bookings ?: 0 ),
			'average_booking'   => $average_booking,
			'start_date'        => $start_date,
			'end_date'          => $end_date,
		);
	}

	/**
	 * Get occupancy report
	 *
	 * @param string $start_date Start date.
	 * @param string $end_date End date.
	 * @return object
	 */
	public function get_occupancy_report( $start_date, $end_date ) {
		global $wpdb;

		$units_table = $wpdb->prefix . 'royal_storage_units';
		$bookings_table = $wpdb->prefix . 'royal_bookings';

		$total_units = $wpdb->get_var(
			"SELECT COUNT(*) FROM $units_table WHERE status = 'available'"
		);

		$occupied_units = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(DISTINCT unit_id) FROM $bookings_table WHERE unit_type = 'storage' AND status IN ('confirmed', 'active') AND start_date <= %s AND end_date >= %s",
				$end_date,
				$start_date
			)
		);

		$occupancy_rate = $total_units > 0 ? ( intval( $occupied_units ?: 0 ) / intval( $total_units ) ) * 100 : 0;

		return (object) array(
			'total_units'      => intval( $total_units ?: 0 ),
			'occupied_units'   => intval( $occupied_units ?: 0 ),
			'occupancy_rate'   => round( $occupancy_rate, 2 ),
			'start_date'       => $start_date,
			'end_date'         => $end_date,
		);
	}

	/**
	 * Get customer report
	 *
	 * @param string $start_date Start date.
	 * @param string $end_date End date.
	 * @return object
	 */
	public function get_customer_report( $start_date, $end_date ) {
		global $wpdb;

		$bookings_table = $wpdb->prefix . 'royal_bookings';

		$new_customers = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(DISTINCT customer_id) FROM $bookings_table WHERE created_at BETWEEN %s AND %s",
				$start_date,
				$end_date
			)
		);

		$repeat_customers = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(DISTINCT customer_id) FROM $bookings_table WHERE customer_id IN (SELECT customer_id FROM $bookings_table WHERE created_at < %s) AND created_at BETWEEN %s AND %s",
				$start_date,
				$start_date,
				$end_date
			)
		);

		$total_customers = intval( $new_customers ?: 0 ) + intval( $repeat_customers ?: 0 );

		return (object) array(
			'new_customers'    => intval( $new_customers ?: 0 ),
			'repeat_customers' => intval( $repeat_customers ?: 0 ),
			'total_customers'  => $total_customers,
			'start_date'       => $start_date,
			'end_date'         => $end_date,
		);
	}

	/**
	 * Get payment report
	 *
	 * @param string $start_date Start date.
	 * @param string $end_date End date.
	 * @return object
	 */
	public function get_payment_report( $start_date, $end_date ) {
		global $wpdb;

		$bookings_table = $wpdb->prefix . 'royal_bookings';

		$paid = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT SUM(total_price) FROM $bookings_table WHERE payment_status = 'paid' AND created_at BETWEEN %s AND %s",
				$start_date,
				$end_date
			)
		);

		$unpaid = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT SUM(total_price) FROM $bookings_table WHERE payment_status = 'unpaid' AND created_at BETWEEN %s AND %s",
				$start_date,
				$end_date
			)
		);

		$failed = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT SUM(total_price) FROM $bookings_table WHERE payment_status = 'failed' AND created_at BETWEEN %s AND %s",
				$start_date,
				$end_date
			)
		);

		$total = floatval( $paid ?: 0 ) + floatval( $unpaid ?: 0 ) + floatval( $failed ?: 0 );

		return (object) array(
			'paid'      => floatval( $paid ?: 0 ),
			'unpaid'    => floatval( $unpaid ?: 0 ),
			'failed'    => floatval( $failed ?: 0 ),
			'total'     => $total,
			'start_date' => $start_date,
			'end_date'  => $end_date,
		);
	}

	/**
	 * Export report to CSV
	 *
	 * @param string $report_type Report type.
	 * @param string $start_date Start date.
	 * @param string $end_date End date.
	 * @return string
	 */
	public function export_to_csv( $report_type, $start_date, $end_date ) {
		$csv = '';

		switch ( $report_type ) {
			case 'revenue':
				$report = $this->get_revenue_report( $start_date, $end_date );
				$csv = "Revenue Report\n";
				$csv .= "Start Date,End Date,Total Revenue,Total Bookings,Average Booking\n";
				$csv .= $report->start_date . ',' . $report->end_date . ',' . $report->total_revenue . ',' . $report->total_bookings . ',' . $report->average_booking . "\n";
				break;

			case 'occupancy':
				$report = $this->get_occupancy_report( $start_date, $end_date );
				$csv = "Occupancy Report\n";
				$csv .= "Start Date,End Date,Total Units,Occupied Units,Occupancy Rate\n";
				$csv .= $report->start_date . ',' . $report->end_date . ',' . $report->total_units . ',' . $report->occupied_units . ',' . $report->occupancy_rate . "%\n";
				break;

			case 'customer':
				$report = $this->get_customer_report( $start_date, $end_date );
				$csv = "Customer Report\n";
				$csv .= "Start Date,End Date,New Customers,Repeat Customers,Total Customers\n";
				$csv .= $report->start_date . ',' . $report->end_date . ',' . $report->new_customers . ',' . $report->repeat_customers . ',' . $report->total_customers . "\n";
				break;

			case 'payment':
				$report = $this->get_payment_report( $start_date, $end_date );
				$csv = "Payment Report\n";
				$csv .= "Start Date,End Date,Paid,Unpaid,Failed,Total\n";
				$csv .= $report->start_date . ',' . $report->end_date . ',' . $report->paid . ',' . $report->unpaid . ',' . $report->failed . ',' . $report->total . "\n";
				break;
		}

		return $csv;
	}

	/**
	 * Get dashboard metrics
	 *
	 * @return object
	 */
	public function get_dashboard_metrics() {
		global $wpdb;

		$bookings_table = $wpdb->prefix . 'royal_bookings';
		$units_table = $wpdb->prefix . 'royal_storage_units';

		$today = date( 'Y-m-d' );
		$this_month = date( 'Y-m-01' );

		$today_revenue = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT SUM(total_price) FROM $bookings_table WHERE payment_status = 'paid' AND DATE(created_at) = %s",
				$today
			)
		);

		$month_revenue = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT SUM(total_price) FROM $bookings_table WHERE payment_status = 'paid' AND created_at >= %s",
				$this_month
			)
		);

		$active_bookings = $wpdb->get_var(
			"SELECT COUNT(*) FROM $bookings_table WHERE status IN ('confirmed', 'active')"
		);

		$occupancy_rate = $wpdb->get_var(
			"SELECT COUNT(DISTINCT unit_id) FROM $bookings_table WHERE unit_type = 'storage' AND status IN ('confirmed', 'active')"
		);

		$total_units = $wpdb->get_var(
			"SELECT COUNT(*) FROM $units_table WHERE status = 'available'"
		);

		return (object) array(
			'today_revenue'    => floatval( $today_revenue ?: 0 ),
			'month_revenue'    => floatval( $month_revenue ?: 0 ),
			'active_bookings'  => intval( $active_bookings ?: 0 ),
			'occupancy_rate'   => $total_units > 0 ? round( ( intval( $occupancy_rate ?: 0 ) / intval( $total_units ) ) * 100, 2 ) : 0,
		);
	}
}

