<?php
/**
 * Security Manager Class
 *
 * @package RoyalStorage
 * @since 1.0.0
 */

namespace RoyalStorage;

/**
 * Security manager class for security hardening
 */
class SecurityManager {

	/**
	 * Constructor
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'init_security' ) );
	}

	/**
	 * Initialize security
	 *
	 * @return void
	 */
	public function init_security() {
		// Disable file editing
		if ( ! defined( 'DISALLOW_FILE_EDIT' ) ) {
			define( 'DISALLOW_FILE_EDIT', true );
		}

		// Disable file modifications
		if ( ! defined( 'DISALLOW_FILE_MODS' ) ) {
			define( 'DISALLOW_FILE_MODS', true );
		}

		// Add security headers
		$this->add_security_headers();

		// Log security events
		$this->log_security_events();
	}

	/**
	 * Add security headers
	 *
	 * @return void
	 */
	private function add_security_headers() {
		header( 'X-Content-Type-Options: nosniff' );
		header( 'X-Frame-Options: SAMEORIGIN' );
		header( 'X-XSS-Protection: 1; mode=block' );
		header( 'Referrer-Policy: strict-origin-when-cross-origin' );
	}

	/**
	 * Log security events
	 *
	 * @return void
	 */
	private function log_security_events() {
		// Log failed login attempts
		add_action( 'wp_login_failed', array( $this, 'log_failed_login' ) );

		// Log user role changes
		add_action( 'set_user_role', array( $this, 'log_user_role_change' ) );

		// Log plugin activation/deactivation
		add_action( 'activated_plugin', array( $this, 'log_plugin_activation' ) );
		add_action( 'deactivated_plugin', array( $this, 'log_plugin_deactivation' ) );
	}

	/**
	 * Log failed login
	 *
	 * @param string $username Username.
	 * @return void
	 */
	public function log_failed_login( $username ) {
		$this->log_event(
			'failed_login',
			array(
				'username' => $username,
				'ip'       => $this->get_client_ip(),
			)
		);
	}

	/**
	 * Log user role change
	 *
	 * @param int    $user_id User ID.
	 * @param string $role Role.
	 * @return void
	 */
	public function log_user_role_change( $user_id, $role ) {
		$this->log_event(
			'user_role_change',
			array(
				'user_id' => $user_id,
				'role'    => $role,
				'by_user' => get_current_user_id(),
			)
		);
	}

	/**
	 * Log plugin activation
	 *
	 * @param string $plugin Plugin.
	 * @return void
	 */
	public function log_plugin_activation( $plugin ) {
		$this->log_event(
			'plugin_activation',
			array(
				'plugin' => $plugin,
				'by_user' => get_current_user_id(),
			)
		);
	}

	/**
	 * Log plugin deactivation
	 *
	 * @param string $plugin Plugin.
	 * @return void
	 */
	public function log_plugin_deactivation( $plugin ) {
		$this->log_event(
			'plugin_deactivation',
			array(
				'plugin' => $plugin,
				'by_user' => get_current_user_id(),
			)
		);
	}

	/**
	 * Log event
	 *
	 * @param string $event_type Event type.
	 * @param array  $data Event data.
	 * @return int|false
	 */
	private function log_event( $event_type, $data ) {
		global $wpdb;

		$logs_table = $wpdb->prefix . 'royal_security_logs';

		return $wpdb->insert(
			$logs_table,
			array(
				'event_type' => $event_type,
				'user_id'    => get_current_user_id(),
				'ip_address' => $this->get_client_ip(),
				'user_agent' => sanitize_text_field( $_SERVER['HTTP_USER_AGENT'] ?? '' ),
				'details'    => wp_json_encode( $data ),
				'created_at' => current_time( 'mysql' ),
			),
			array( '%s', '%d', '%s', '%s', '%s', '%s' )
		);
	}

	/**
	 * Get client IP
	 *
	 * @return string
	 */
	private function get_client_ip() {
		if ( ! empty( $_SERVER['HTTP_CLIENT_IP'] ) ) {
			$ip = $_SERVER['HTTP_CLIENT_IP'];
		} elseif ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
			$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
		} else {
			$ip = $_SERVER['REMOTE_ADDR'];
		}

		return sanitize_text_field( $ip );
	}

	/**
	 * Get security logs
	 *
	 * @param int $limit Limit.
	 * @return array
	 */
	public function get_security_logs( $limit = 100 ) {
		global $wpdb;

		$logs_table = $wpdb->prefix . 'royal_security_logs';

		return $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM $logs_table ORDER BY created_at DESC LIMIT %d",
				$limit
			)
		);
	}

	/**
	 * Get security report
	 *
	 * @return object
	 */
	public function get_security_report() {
		global $wpdb;

		$logs_table = $wpdb->prefix . 'royal_security_logs';

		$failed_logins = $wpdb->get_var(
			"SELECT COUNT(*) FROM $logs_table WHERE event_type = 'failed_login' AND created_at > DATE_SUB(NOW(), INTERVAL 24 HOUR)"
		);

		$role_changes = $wpdb->get_var(
			"SELECT COUNT(*) FROM $logs_table WHERE event_type = 'user_role_change' AND created_at > DATE_SUB(NOW(), INTERVAL 24 HOUR)"
		);

		$plugin_changes = $wpdb->get_var(
			"SELECT COUNT(*) FROM $logs_table WHERE event_type IN ('plugin_activation', 'plugin_deactivation') AND created_at > DATE_SUB(NOW(), INTERVAL 24 HOUR)"
		);

		return (object) array(
			'failed_logins'  => intval( $failed_logins ?: 0 ),
			'role_changes'   => intval( $role_changes ?: 0 ),
			'plugin_changes' => intval( $plugin_changes ?: 0 ),
		);
	}

	/**
	 * Verify data integrity
	 *
	 * @return array
	 */
	public function verify_data_integrity() {
		global $wpdb;

		$results = array();

		// Check for orphaned bookings
		$orphaned_bookings = $wpdb->get_var(
			"SELECT COUNT(*) FROM " . $wpdb->prefix . "royal_bookings WHERE customer_id NOT IN (SELECT ID FROM " . $wpdb->users . ")"
		);

		$results['orphaned_bookings'] = intval( $orphaned_bookings ?: 0 );

		// Check for orphaned invoices
		$orphaned_invoices = $wpdb->get_var(
			"SELECT COUNT(*) FROM " . $wpdb->prefix . "royal_invoices WHERE booking_id NOT IN (SELECT id FROM " . $wpdb->prefix . "royal_bookings)"
		);

		$results['orphaned_invoices'] = intval( $orphaned_invoices ?: 0 );

		return $results;
	}

	/**
	 * Clean security logs
	 *
	 * @param int $days Days to keep.
	 * @return int
	 */
	public function clean_security_logs( $days = 90 ) {
		global $wpdb;

		$logs_table = $wpdb->prefix . 'royal_security_logs';

		return $wpdb->query(
			$wpdb->prepare(
				"DELETE FROM $logs_table WHERE created_at < DATE_SUB(NOW(), INTERVAL %d DAY)",
				$days
			)
		);
	}
}