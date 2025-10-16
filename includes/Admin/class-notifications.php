<?php
/**
 * Notifications Class
 *
 * @package RoyalStorage\Admin
 * @since 1.0.0
 */

namespace RoyalStorage\Admin;

use RoyalStorage\EmailManager;

/**
 * Notifications class for admin notification management
 */
class Notifications {

	/**
	 * Email manager instance
	 *
	 * @var EmailManager
	 */
	private $email_manager;

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->email_manager = new EmailManager();
		add_action( 'admin_init', array( $this, 'init' ) );
	}

	/**
	 * Initialize notifications
	 *
	 * @return void
	 */
	public function init() {
		// Notifications initialization code.
		add_action( 'royal_storage_send_expiry_reminders', array( $this, 'send_expiry_reminders' ) );
		add_action( 'royal_storage_send_overdue_reminders', array( $this, 'send_overdue_reminders' ) );
		add_action( 'admin_post_royal_storage_send_notification', array( $this, 'handle_send_notification' ) );
	}

	/**
	 * Handle send notification from admin
	 *
	 * @return void
	 */
	public function handle_send_notification() {
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'royal_storage_send_notification' ) ) {
			wp_die( esc_html__( 'Security check failed', 'royal-storage' ) );
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Unauthorized', 'royal-storage' ) );
		}

		$booking_id = isset( $_POST['booking_id'] ) ? intval( $_POST['booking_id'] ) : 0;
		$type = isset( $_POST['type'] ) ? sanitize_text_field( wp_unslash( $_POST['type'] ) ) : '';

		if ( 'expiry' === $type ) {
			$this->email_manager->send_expiry_reminder( $booking_id );
		} elseif ( 'overdue' === $type ) {
			$this->email_manager->send_overdue_reminder( $booking_id );
		}

		wp_redirect( admin_url( 'admin.php?page=royal-storage-notifications&message=sent' ) );
		exit;
	}

	/**
	 * Send expiry reminders
	 *
	 * @return void
	 */
	public function send_expiry_reminders() {
		global $wpdb;

		$bookings_table = $wpdb->prefix . 'royal_bookings';

		// Get bookings expiring in 7 days.
		$bookings = $wpdb->get_results(
			"SELECT * FROM $bookings_table WHERE end_date = DATE_ADD(CURDATE(), INTERVAL 7 DAY) AND status IN ('confirmed', 'active')"
		);

		foreach ( $bookings as $booking ) {
			$this->email_manager->send_expiry_reminder( $booking->id );
		}
	}

	/**
	 * Send overdue reminders
	 *
	 * @return void
	 */
	public function send_overdue_reminders() {
		global $wpdb;

		$bookings_table = $wpdb->prefix . 'royal_bookings';

		// Get overdue bookings.
		$bookings = $wpdb->get_results(
			"SELECT * FROM $bookings_table WHERE end_date < CURDATE() AND status != 'cancelled' AND payment_status IN ('unpaid', 'pending')"
		);

		foreach ( $bookings as $booking ) {
			$this->email_manager->send_overdue_reminder( $booking->id );
		}
	}

	/**
	 * Get pending notifications
	 *
	 * @return array
	 */
	public function get_pending_notifications() {
		global $wpdb;

		$bookings_table = $wpdb->prefix . 'royal_bookings';

		$expiring = $wpdb->get_results(
			"SELECT id, 'expiry' as type FROM $bookings_table WHERE end_date = DATE_ADD(CURDATE(), INTERVAL 7 DAY) AND status IN ('confirmed', 'active')"
		);

		$overdue = $wpdb->get_results(
			"SELECT id, 'overdue' as type FROM $bookings_table WHERE end_date < CURDATE() AND status != 'cancelled' AND payment_status IN ('unpaid', 'pending')"
		);

		return array_merge( $expiring, $overdue );
	}

	/**
	 * Get pending notifications count
	 *
	 * @return int
	 */
	public function get_pending_notifications_count() {
		return count( $this->get_pending_notifications() );
	}

	/**
	 * Schedule notification cron jobs
	 *
	 * @return void
	 */
	public function schedule_cron_jobs() {
		if ( ! wp_next_scheduled( 'royal_storage_send_expiry_reminders' ) ) {
			wp_schedule_event( time(), 'daily', 'royal_storage_send_expiry_reminders' );
		}

		if ( ! wp_next_scheduled( 'royal_storage_send_overdue_reminders' ) ) {
			wp_schedule_event( time(), 'daily', 'royal_storage_send_overdue_reminders' );
		}
	}

	/**
	 * Clear notification cron jobs
	 *
	 * @return void
	 */
	public function clear_cron_jobs() {
		wp_clear_scheduled_hook( 'royal_storage_send_expiry_reminders' );
		wp_clear_scheduled_hook( 'royal_storage_send_overdue_reminders' );
	}
}

