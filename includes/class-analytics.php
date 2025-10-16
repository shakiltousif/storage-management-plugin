<?php
/**
 * Analytics Class
 *
 * @package RoyalStorage
 * @since 1.0.0
 */

namespace RoyalStorage;

/**
 * Analytics class for tracking and analyzing data
 */
class Analytics {

	/**
	 * Track event
	 *
	 * @param string $event_type Event type.
	 * @param int    $customer_id Customer ID.
	 * @param array  $data Event data.
	 * @return int|false
	 */
	public function track_event( $event_type, $customer_id, $data = array() ) {
		global $wpdb;

		$events_table = $wpdb->prefix . 'royal_events';

		$result = $wpdb->insert(
			$events_table,
			array(
				'event_type'  => $event_type,
				'customer_id' => $customer_id,
				'data'        => wp_json_encode( $data ),
				'created_at'  => current_time( 'mysql' ),
			),
			array( '%s', '%d', '%s', '%s' )
		);

		return $result ? $wpdb->insert_id : false;
	}

	/**
	 * Get event analytics
	 *
	 * @param string $event_type Event type.
	 * @param string $start_date Start date.
	 * @param string $end_date End date.
	 * @return array
	 */
	public function get_event_analytics( $event_type, $start_date, $end_date ) {
		global $wpdb;

		$events_table = $wpdb->prefix . 'royal_events';

		return $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM $events_table WHERE event_type = %s AND created_at BETWEEN %s AND %s ORDER BY created_at DESC",
				$event_type,
				$start_date,
				$end_date
			)
		);
	}

	/**
	 * Get customer journey
	 *
	 * @param int $customer_id Customer ID.
	 * @return array
	 */
	public function get_customer_journey( $customer_id ) {
		global $wpdb;

		$events_table = $wpdb->prefix . 'royal_events';

		return $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM $events_table WHERE customer_id = %d ORDER BY created_at ASC",
				$customer_id
			)
		);
	}

	/**
	 * Get conversion funnel
	 *
	 * @param string $start_date Start date.
	 * @param string $end_date End date.
	 * @return object
	 */
	public function get_conversion_funnel( $start_date, $end_date ) {
		global $wpdb;

		$events_table = $wpdb->prefix . 'royal_events';

		$portal_visits = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(DISTINCT customer_id) FROM $events_table WHERE event_type = 'portal_visit' AND created_at BETWEEN %s AND %s",
				$start_date,
				$end_date
			)
		);

		$booking_views = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(DISTINCT customer_id) FROM $events_table WHERE event_type = 'booking_view' AND created_at BETWEEN %s AND %s",
				$start_date,
				$end_date
			)
		);

		$bookings_created = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(DISTINCT customer_id) FROM $events_table WHERE event_type = 'booking_created' AND created_at BETWEEN %s AND %s",
				$start_date,
				$end_date
			)
		);

		$payments_completed = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(DISTINCT customer_id) FROM $events_table WHERE event_type = 'payment_completed' AND created_at BETWEEN %s AND %s",
				$start_date,
				$end_date
			)
		);

		return (object) array(
			'portal_visits'      => intval( $portal_visits ?: 0 ),
			'booking_views'      => intval( $booking_views ?: 0 ),
			'bookings_created'   => intval( $bookings_created ?: 0 ),
			'payments_completed' => intval( $payments_completed ?: 0 ),
		);
	}

	/**
	 * Get top performing units
	 *
	 * @param int $limit Limit.
	 * @return array
	 */
	public function get_top_performing_units( $limit = 10 ) {
		global $wpdb;

		$bookings_table = $wpdb->prefix . 'royal_bookings';

		return $wpdb->get_results(
			$wpdb->prepare(
				"SELECT unit_id, COUNT(*) as booking_count, SUM(total_price) as total_revenue FROM $bookings_table WHERE payment_status = 'paid' GROUP BY unit_id ORDER BY total_revenue DESC LIMIT %d",
				$limit
			)
		);
	}

	/**
	 * Get customer lifetime value
	 *
	 * @param int $customer_id Customer ID.
	 * @return float
	 */
	public function get_customer_lifetime_value( $customer_id ) {
		global $wpdb;

		$bookings_table = $wpdb->prefix . 'royal_bookings';

		$total = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT SUM(total_price) FROM $bookings_table WHERE customer_id = %d AND payment_status = 'paid'",
				$customer_id
			)
		);

		return floatval( $total ?: 0 );
	}

	/**
	 * Get average customer lifetime value
	 *
	 * @return float
	 */
	public function get_average_customer_lifetime_value() {
		global $wpdb;

		$bookings_table = $wpdb->prefix . 'royal_bookings';

		$total_revenue = $wpdb->get_var(
			"SELECT SUM(total_price) FROM $bookings_table WHERE payment_status = 'paid'"
		);

		$total_customers = $wpdb->get_var(
			"SELECT COUNT(DISTINCT customer_id) FROM $bookings_table WHERE payment_status = 'paid'"
		);

		if ( $total_customers == 0 ) {
			return 0;
		}

		return floatval( $total_revenue ) / intval( $total_customers );
	}

	/**
	 * Get churn rate
	 *
	 * @param int $days Days to analyze.
	 * @return float
	 */
	public function get_churn_rate( $days = 30 ) {
		global $wpdb;

		$bookings_table = $wpdb->prefix . 'royal_bookings';

		$previous_period = date( 'Y-m-d', strtotime( "-$days days" ) );
		$current_period = date( 'Y-m-d' );

		$previous_customers = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(DISTINCT customer_id) FROM $bookings_table WHERE created_at < %s",
				$previous_period
			)
		);

		$current_customers = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(DISTINCT customer_id) FROM $bookings_table WHERE created_at BETWEEN %s AND %s",
				$previous_period,
				$current_period
			)
		);

		if ( $previous_customers == 0 ) {
			return 0;
		}

		$churned = intval( $previous_customers ) - intval( $current_customers );
		$churn_rate = ( $churned / intval( $previous_customers ) ) * 100;

		return round( $churn_rate, 2 );
	}

	/**
	 * Get retention rate
	 *
	 * @param int $days Days to analyze.
	 * @return float
	 */
	public function get_retention_rate( $days = 30 ) {
		$churn_rate = $this->get_churn_rate( $days );
		return round( 100 - $churn_rate, 2 );
	}
}

