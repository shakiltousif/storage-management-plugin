<?php
/**
 * Database Class
 *
 * @package RoyalStorage
 * @since 1.0.0
 */

namespace RoyalStorage;

/**
 * Database class for managing database operations
 */
class Database {

	/**
	 * Constructor
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'create_tables' ) );
	}

	/**
	 * Create custom database tables
	 *
	 * @return void
	 */
	public function create_tables() {
		global $wpdb;

		$charset_collate = $wpdb->get_charset_collate();

		// Storage Units table.
		$storage_units_table = $wpdb->prefix . 'royal_storage_units';
		if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $storage_units_table ) ) !== $storage_units_table ) {
			$sql = "CREATE TABLE $storage_units_table (
				id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
				post_id BIGINT(20) UNSIGNED NOT NULL,
				size VARCHAR(20) NOT NULL,
				dimensions VARCHAR(100),
				amenities LONGTEXT,
				base_price DECIMAL(10, 2) NOT NULL,
				status VARCHAR(20) DEFAULT 'available',
				created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
				updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
				PRIMARY KEY (id),
				UNIQUE KEY post_id (post_id),
				KEY status (status)
			) $charset_collate;";

			require_once ABSPATH . 'wp-admin/includes/upgrade.php';
			dbDelta( $sql );
		}

		// Parking Spaces table.
		$parking_table = $wpdb->prefix . 'royal_parking_spaces';
		if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $parking_table ) ) !== $parking_table ) {
			$sql = "CREATE TABLE $parking_table (
				id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
				post_id BIGINT(20) UNSIGNED NOT NULL,
				spot_number INT(11) NOT NULL,
				height_limit VARCHAR(50),
				base_price DECIMAL(10, 2) NOT NULL,
				status VARCHAR(20) DEFAULT 'available',
				created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
				updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
				PRIMARY KEY (id),
				UNIQUE KEY post_id (post_id),
				UNIQUE KEY spot_number (spot_number),
				KEY status (status)
			) $charset_collate;";

			require_once ABSPATH . 'wp-admin/includes/upgrade.php';
			dbDelta( $sql );
		}

		// Bookings table.
		$bookings_table = $wpdb->prefix . 'royal_bookings';
		if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $bookings_table ) ) !== $bookings_table ) {
			$sql = "CREATE TABLE $bookings_table (
				id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
				post_id BIGINT(20) UNSIGNED NOT NULL,
				customer_id BIGINT(20) UNSIGNED NOT NULL,
				unit_id BIGINT(20) UNSIGNED NOT NULL,
				unit_type VARCHAR(20) NOT NULL,
				start_date DATE NOT NULL,
				end_date DATE NOT NULL,
				total_price DECIMAL(10, 2) NOT NULL,
				vat_amount DECIMAL(10, 2) NOT NULL,
				status VARCHAR(20) DEFAULT 'pending',
				payment_status VARCHAR(20) DEFAULT 'unpaid',
				access_code VARCHAR(50),
				created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
				updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
				PRIMARY KEY (id),
				UNIQUE KEY post_id (post_id),
				KEY customer_id (customer_id),
				KEY unit_id (unit_id),
				KEY status (status),
				KEY payment_status (payment_status),
				KEY start_date (start_date),
				KEY end_date (end_date)
			) $charset_collate;";

			require_once ABSPATH . 'wp-admin/includes/upgrade.php';
			dbDelta( $sql );
		}

		// Invoices table.
		$invoices_table = $wpdb->prefix . 'royal_invoices';
		if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $invoices_table ) ) !== $invoices_table ) {
			$sql = "CREATE TABLE $invoices_table (
				id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
				post_id BIGINT(20) UNSIGNED NOT NULL,
				booking_id BIGINT(20) UNSIGNED NOT NULL,
				invoice_number VARCHAR(50) NOT NULL,
				amount DECIMAL(10, 2) NOT NULL,
				vat_amount DECIMAL(10, 2) NOT NULL,
				status VARCHAR(20) DEFAULT 'draft',
				invoice_type VARCHAR(20) DEFAULT 'invoice',
				created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
				updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
				PRIMARY KEY (id),
				UNIQUE KEY post_id (post_id),
				UNIQUE KEY invoice_number (invoice_number),
				KEY booking_id (booking_id),
				KEY status (status)
			) $charset_collate;";

			require_once ABSPATH . 'wp-admin/includes/upgrade.php';
			dbDelta( $sql );
		}

		// Notifications table.
		$notifications_table = $wpdb->prefix . 'royal_notifications';
		if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $notifications_table ) ) !== $notifications_table ) {
			$sql = "CREATE TABLE $notifications_table (
				id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
				customer_id BIGINT(20) UNSIGNED NOT NULL,
				type VARCHAR(50) NOT NULL,
				title VARCHAR(255) NOT NULL,
				message TEXT NOT NULL,
				is_read TINYINT(1) DEFAULT 0,
				created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
				updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
				PRIMARY KEY (id),
				KEY customer_id (customer_id),
				KEY type (type),
				KEY is_read (is_read),
				KEY created_at (created_at)
			) $charset_collate;";

			require_once ABSPATH . 'wp-admin/includes/upgrade.php';
			dbDelta( $sql );
		}

		// Subscriptions table.
		$subscriptions_table = $wpdb->prefix . 'royal_subscriptions';
		if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $subscriptions_table ) ) !== $subscriptions_table ) {
			$sql = "CREATE TABLE $subscriptions_table (
				id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
				customer_id BIGINT(20) UNSIGNED NOT NULL,
				booking_id BIGINT(20) UNSIGNED NOT NULL,
				status VARCHAR(20) DEFAULT 'active',
				next_billing_date DATE,
				billing_frequency VARCHAR(20) DEFAULT 'monthly',
				created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
				updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
				PRIMARY KEY (id),
				KEY customer_id (customer_id),
				KEY booking_id (booking_id),
				KEY status (status),
				KEY next_billing_date (next_billing_date)
			) $charset_collate;";

			require_once ABSPATH . 'wp-admin/includes/upgrade.php';
			dbDelta( $sql );
		}

		// Events table.
		$events_table = $wpdb->prefix . 'royal_events';
		if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $events_table ) ) !== $events_table ) {
			$sql = "CREATE TABLE $events_table (
				id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
				event_type VARCHAR(50) NOT NULL,
				customer_id BIGINT(20) UNSIGNED NOT NULL,
				booking_id BIGINT(20) UNSIGNED,
				data LONGTEXT,
				created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
				PRIMARY KEY (id),
				KEY event_type (event_type),
				KEY customer_id (customer_id),
				KEY booking_id (booking_id),
				KEY created_at (created_at)
			) $charset_collate;";

			require_once ABSPATH . 'wp-admin/includes/upgrade.php';
			dbDelta( $sql );
		}

		// Security logs table.
		$security_logs_table = $wpdb->prefix . 'royal_security_logs';
		if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $security_logs_table ) ) !== $security_logs_table ) {
			$sql = "CREATE TABLE $security_logs_table (
				id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
				event_type VARCHAR(50) NOT NULL,
				user_id BIGINT(20) UNSIGNED,
				ip_address VARCHAR(45),
				user_agent TEXT,
				details LONGTEXT,
				created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
				PRIMARY KEY (id),
				KEY event_type (event_type),
				KEY user_id (user_id),
				KEY ip_address (ip_address),
				KEY created_at (created_at)
			) $charset_collate;";

			require_once ABSPATH . 'wp-admin/includes/upgrade.php';
			dbDelta( $sql );
		}

		// Payments table.
		$payments_table = $wpdb->prefix . 'royal_payments';
		if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $payments_table ) ) !== $payments_table ) {
			$sql = "CREATE TABLE $payments_table (
				id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
				booking_id BIGINT(20) UNSIGNED NOT NULL,
				amount DECIMAL(10, 2) NOT NULL,
				payment_method VARCHAR(50) NOT NULL,
				payment_status VARCHAR(20) DEFAULT 'pending',
				transaction_id VARCHAR(100),
				gateway_response LONGTEXT,
				created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
				updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
				PRIMARY KEY (id),
				KEY booking_id (booking_id),
				KEY payment_status (payment_status),
				KEY transaction_id (transaction_id),
				KEY created_at (created_at)
			) $charset_collate;";

			require_once ABSPATH . 'wp-admin/includes/upgrade.php';
			dbDelta( $sql );
		}
	}

	/**
	 * Get storage unit by ID
	 *
	 * @param int $unit_id Unit ID.
	 * @return object|null
	 */
	public static function get_storage_unit( $unit_id ) {
		global $wpdb;
		$table = $wpdb->prefix . 'royal_storage_units';
		return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table WHERE id = %d", $unit_id ) );
	}

	/**
	 * Get parking space by ID
	 *
	 * @param int $space_id Space ID.
	 * @return object|null
	 */
	public static function get_parking_space( $space_id ) {
		global $wpdb;
		$table = $wpdb->prefix . 'royal_parking_spaces';
		return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table WHERE id = %d", $space_id ) );
	}

	/**
	 * Get booking by ID
	 *
	 * @param int $booking_id Booking ID.
	 * @return object|null
	 */
	public static function get_booking( $booking_id ) {
		global $wpdb;
		$table = $wpdb->prefix . 'royal_bookings';
		return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table WHERE id = %d", $booking_id ) );
	}

	/**
	 * Get invoice by ID
	 *
	 * @param int $invoice_id Invoice ID.
	 * @return object|null
	 */
	public static function get_invoice( $invoice_id ) {
		global $wpdb;
		$table = $wpdb->prefix . 'royal_invoices';
		return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table WHERE id = %d", $invoice_id ) );
	}

	/**
	 * Get notification by ID
	 *
	 * @param int $notification_id Notification ID.
	 * @return object|null
	 */
	public static function get_notification( $notification_id ) {
		global $wpdb;
		$table = $wpdb->prefix . 'royal_notifications';
		return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table WHERE id = %d", $notification_id ) );
	}

	/**
	 * Get subscription by ID
	 *
	 * @param int $subscription_id Subscription ID.
	 * @return object|null
	 */
	public static function get_subscription( $subscription_id ) {
		global $wpdb;
		$table = $wpdb->prefix . 'royal_subscriptions';
		return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table WHERE id = %d", $subscription_id ) );
	}

	/**
	 * Get event by ID
	 *
	 * @param int $event_id Event ID.
	 * @return object|null
	 */
	public static function get_event( $event_id ) {
		global $wpdb;
		$table = $wpdb->prefix . 'royal_events';
		return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table WHERE id = %d", $event_id ) );
	}

	/**
	 * Get security log by ID
	 *
	 * @param int $log_id Log ID.
	 * @return object|null
	 */
	public static function get_security_log( $log_id ) {
		global $wpdb;
		$table = $wpdb->prefix . 'royal_security_logs';
		return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table WHERE id = %d", $log_id ) );
	}

	/**
	 * Get all storage units
	 *
	 * @return array
	 */
	public static function get_storage_units() {
		global $wpdb;
		$table = $wpdb->prefix . 'royal_storage_units';
		return $wpdb->get_results( "SELECT * FROM $table ORDER BY id ASC" );
	}

	/**
	 * Get all parking spaces
	 *
	 * @return array
	 */
	public static function get_parking_spaces() {
		global $wpdb;
		$table = $wpdb->prefix . 'royal_parking_spaces';
		return $wpdb->get_results( "SELECT * FROM $table ORDER BY id ASC" );
	}

	/**
	 * Get all bookings
	 *
	 * @return array
	 */
	public static function get_bookings() {
		global $wpdb;
		$table = $wpdb->prefix . 'royal_bookings';
		return $wpdb->get_results( "SELECT * FROM $table ORDER BY created_at DESC" );
	}

	/**
	 * Get all invoices
	 *
	 * @return array
	 */
	public static function get_invoices() {
		global $wpdb;
		$table = $wpdb->prefix . 'royal_invoices';
		return $wpdb->get_results( "SELECT * FROM $table ORDER BY created_at DESC" );
	}
}

