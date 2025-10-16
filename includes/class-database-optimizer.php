<?php
/**
 * Database Optimizer Class
 *
 * @package RoyalStorage
 * @since 1.0.0
 */

namespace RoyalStorage;

/**
 * Database optimizer class for performance optimization
 */
class DatabaseOptimizer {

	/**
	 * Optimize all tables
	 *
	 * @return array
	 */
	public function optimize_all_tables() {
		global $wpdb;

		$tables = array(
			$wpdb->prefix . 'royal_bookings',
			$wpdb->prefix . 'royal_storage_units',
			$wpdb->prefix . 'royal_parking_spaces',
			$wpdb->prefix . 'royal_invoices',
			$wpdb->prefix . 'royal_notifications',
			$wpdb->prefix . 'royal_subscriptions',
			$wpdb->prefix . 'royal_events',
			$wpdb->prefix . 'royal_security_logs',
		);

		$results = array();

		foreach ( $tables as $table ) {
			$result = $wpdb->query( "OPTIMIZE TABLE $table" );
			$results[ $table ] = $result;
		}

		return $results;
	}

	/**
	 * Add indexes to tables
	 *
	 * @return array
	 */
	public function add_indexes() {
		global $wpdb;

		$results = array();

		// Bookings table indexes
		$wpdb->query(
			"ALTER TABLE " . $wpdb->prefix . "royal_bookings ADD INDEX idx_customer_id (customer_id)"
		);
		$results[] = 'Added index to royal_bookings.customer_id';

		$wpdb->query(
			"ALTER TABLE " . $wpdb->prefix . "royal_bookings ADD INDEX idx_unit_id (unit_id)"
		);
		$results[] = 'Added index to royal_bookings.unit_id';

		$wpdb->query(
			"ALTER TABLE " . $wpdb->prefix . "royal_bookings ADD INDEX idx_status (status)"
		);
		$results[] = 'Added index to royal_bookings.status';

		$wpdb->query(
			"ALTER TABLE " . $wpdb->prefix . "royal_bookings ADD INDEX idx_payment_status (payment_status)"
		);
		$results[] = 'Added index to royal_bookings.payment_status';

		// Invoices table indexes
		$wpdb->query(
			"ALTER TABLE " . $wpdb->prefix . "royal_invoices ADD INDEX idx_customer_id (customer_id)"
		);
		$results[] = 'Added index to royal_invoices.customer_id';

		$wpdb->query(
			"ALTER TABLE " . $wpdb->prefix . "royal_invoices ADD INDEX idx_booking_id (booking_id)"
		);
		$results[] = 'Added index to royal_invoices.booking_id';

		// Notifications table indexes
		$wpdb->query(
			"ALTER TABLE " . $wpdb->prefix . "royal_notifications ADD INDEX idx_customer_id (customer_id)"
		);
		$results[] = 'Added index to royal_notifications.customer_id';

		$wpdb->query(
			"ALTER TABLE " . $wpdb->prefix . "royal_notifications ADD INDEX idx_is_read (is_read)"
		);
		$results[] = 'Added index to royal_notifications.is_read';

		return $results;
	}

	/**
	 * Analyze tables
	 *
	 * @return array
	 */
	public function analyze_tables() {
		global $wpdb;

		$tables = array(
			$wpdb->prefix . 'royal_bookings',
			$wpdb->prefix . 'royal_storage_units',
			$wpdb->prefix . 'royal_parking_spaces',
			$wpdb->prefix . 'royal_invoices',
			$wpdb->prefix . 'royal_notifications',
			$wpdb->prefix . 'royal_subscriptions',
			$wpdb->prefix . 'royal_events',
			$wpdb->prefix . 'royal_security_logs',
		);

		$results = array();

		foreach ( $tables as $table ) {
			$result = $wpdb->query( "ANALYZE TABLE $table" );
			$results[ $table ] = $result;
		}

		return $results;
	}

	/**
	 * Get table statistics
	 *
	 * @return array
	 */
	public function get_table_statistics() {
		global $wpdb;

		$tables = array(
			$wpdb->prefix . 'royal_bookings',
			$wpdb->prefix . 'royal_storage_units',
			$wpdb->prefix . 'royal_parking_spaces',
			$wpdb->prefix . 'royal_invoices',
			$wpdb->prefix . 'royal_notifications',
			$wpdb->prefix . 'royal_subscriptions',
			$wpdb->prefix . 'royal_events',
			$wpdb->prefix . 'royal_security_logs',
		);

		$statistics = array();

		foreach ( $tables as $table ) {
			$info = $wpdb->get_row(
				$wpdb->prepare(
					"SELECT TABLE_NAME, TABLE_ROWS, DATA_LENGTH, INDEX_LENGTH FROM information_schema.TABLES WHERE TABLE_SCHEMA = %s AND TABLE_NAME = %s",
					DB_NAME,
					$table
				)
			);

			if ( $info ) {
				$statistics[ $table ] = $info;
			}
		}

		return $statistics;
	}

	/**
	 * Clean old data
	 *
	 * @param int $days Days to keep.
	 * @return int
	 */
	public function clean_old_data( $days = 90 ) {
		global $wpdb;

		$count = 0;

		// Delete old cancelled bookings
		$count += $wpdb->query(
			$wpdb->prepare(
				"DELETE FROM " . $wpdb->prefix . "royal_bookings WHERE status = 'cancelled' AND created_at < DATE_SUB(NOW(), INTERVAL %d DAY)",
				$days
			)
		);

		// Delete old notifications
		$count += $wpdb->query(
			$wpdb->prepare(
				"DELETE FROM " . $wpdb->prefix . "royal_notifications WHERE created_at < DATE_SUB(NOW(), INTERVAL %d DAY)",
				$days
			)
		);

		// Delete old events
		$count += $wpdb->query(
			$wpdb->prepare(
				"DELETE FROM " . $wpdb->prefix . "royal_events WHERE created_at < DATE_SUB(NOW(), INTERVAL %d DAY)",
				$days
			)
		);

		return $count;
	}

	/**
	 * Repair tables
	 *
	 * @return array
	 */
	public function repair_tables() {
		global $wpdb;

		$tables = array(
			$wpdb->prefix . 'royal_bookings',
			$wpdb->prefix . 'royal_storage_units',
			$wpdb->prefix . 'royal_parking_spaces',
			$wpdb->prefix . 'royal_invoices',
			$wpdb->prefix . 'royal_notifications',
			$wpdb->prefix . 'royal_subscriptions',
			$wpdb->prefix . 'royal_events',
			$wpdb->prefix . 'royal_security_logs',
		);

		$results = array();

		foreach ( $tables as $table ) {
			$result = $wpdb->query( "REPAIR TABLE $table" );
			$results[ $table ] = $result;
		}

		return $results;
	}
}