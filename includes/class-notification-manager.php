<?php
/**
 * Notification Manager Class
 *
 * @package RoyalStorage
 * @since 1.0.0
 */

namespace RoyalStorage;

/**
 * Notification manager class for email and SMS notifications
 */
class NotificationManager {

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
		add_action( 'wp_scheduled_event_royal_storage_notifications', array( $this, 'process_pending_notifications' ) );
	}

	/**
	 * Send booking confirmation
	 *
	 * @param int $booking_id Booking ID.
	 * @return bool
	 */
	public function send_booking_confirmation( $booking_id ) {
		global $wpdb;

		$bookings_table = $wpdb->prefix . 'royal_bookings';
		$booking = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM $bookings_table WHERE id = %d",
				$booking_id
			)
		);

		if ( ! $booking ) {
			return false;
		}

		$user = get_user_by( 'id', $booking->customer_id );

		if ( ! $user ) {
			return false;
		}

		// Send email
		$this->email_manager->send_booking_confirmation( $booking_id );

		// Create notification record
		$this->create_notification(
			$booking->customer_id,
			'booking_confirmation',
			'Booking Confirmation',
			'Your booking #' . $booking_id . ' has been confirmed.',
			$booking_id
		);

		return true;
	}

	/**
	 * Send payment reminder
	 *
	 * @param int $booking_id Booking ID.
	 * @return bool
	 */
	public function send_payment_reminder( $booking_id ) {
		global $wpdb;

		$bookings_table = $wpdb->prefix . 'royal_bookings';
		$booking = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM $bookings_table WHERE id = %d AND payment_status = 'unpaid'",
				$booking_id
			)
		);

		if ( ! $booking ) {
			return false;
		}

		$user = get_user_by( 'id', $booking->customer_id );

		if ( ! $user ) {
			return false;
		}

		// Send email
		$this->email_manager->send_payment_reminder( $booking_id );

		// Create notification record
		$this->create_notification(
			$booking->customer_id,
			'payment_reminder',
			'Payment Reminder',
			'Please complete payment for booking #' . $booking_id . '.',
			$booking_id
		);

		return true;
	}

	/**
	 * Send booking expiry warning
	 *
	 * @param int $booking_id Booking ID.
	 * @return bool
	 */
	public function send_expiry_warning( $booking_id ) {
		global $wpdb;

		$bookings_table = $wpdb->prefix . 'royal_bookings';
		$booking = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM $bookings_table WHERE id = %d",
				$booking_id
			)
		);

		if ( ! $booking ) {
			return false;
		}

		$user = get_user_by( 'id', $booking->customer_id );

		if ( ! $user ) {
			return false;
		}

		// Send email
		$this->email_manager->send_expiry_warning( $booking_id );

		// Create notification record
		$this->create_notification(
			$booking->customer_id,
			'expiry_warning',
			'Booking Expiry Warning',
			'Your booking #' . $booking_id . ' is expiring soon.',
			$booking_id
		);

		return true;
	}

	/**
	 * Create notification record
	 *
	 * @param int    $customer_id Customer ID.
	 * @param string $type Notification type.
	 * @param string $title Notification title.
	 * @param string $message Notification message.
	 * @param int    $booking_id Booking ID.
	 * @return int|false
	 */
	public function create_notification( $customer_id, $type, $title, $message, $booking_id = 0 ) {
		global $wpdb;

		$notifications_table = $wpdb->prefix . 'royal_notifications';

		$result = $wpdb->insert(
			$notifications_table,
			array(
				'customer_id' => $customer_id,
				'type'        => $type,
				'title'       => $title,
				'message'     => $message,
				'booking_id'  => $booking_id,
				'is_read'     => 0,
				'created_at'  => current_time( 'mysql' ),
			),
			array( '%d', '%s', '%s', '%s', '%d', '%d', '%s' )
		);

		return $result ? $wpdb->insert_id : false;
	}

	/**
	 * Get customer notifications
	 *
	 * @param int $customer_id Customer ID.
	 * @param int $limit Limit.
	 * @return array
	 */
	public function get_customer_notifications( $customer_id, $limit = 20 ) {
		global $wpdb;

		$notifications_table = $wpdb->prefix . 'royal_notifications';

		return $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM $notifications_table WHERE customer_id = %d ORDER BY created_at DESC LIMIT %d",
				$customer_id,
				$limit
			)
		);
	}

	/**
	 * Get unread notifications count
	 *
	 * @param int $customer_id Customer ID.
	 * @return int
	 */
	public function get_unread_count( $customer_id ) {
		global $wpdb;

		$notifications_table = $wpdb->prefix . 'royal_notifications';

		return intval(
			$wpdb->get_var(
				$wpdb->prepare(
					"SELECT COUNT(*) FROM $notifications_table WHERE customer_id = %d AND is_read = 0",
					$customer_id
				)
			)
		);
	}

	/**
	 * Mark notification as read
	 *
	 * @param int $notification_id Notification ID.
	 * @return bool
	 */
	public function mark_as_read( $notification_id ) {
		global $wpdb;

		$notifications_table = $wpdb->prefix . 'royal_notifications';

		return $wpdb->update(
			$notifications_table,
			array( 'is_read' => 1 ),
			array( 'id' => $notification_id ),
			array( '%d' ),
			array( '%d' )
		);
	}

	/**
	 * Process pending notifications
	 *
	 * @return void
	 */
	public function process_pending_notifications() {
		global $wpdb;

		$bookings_table = $wpdb->prefix . 'royal_bookings';

		// Send payment reminders for unpaid bookings
		$unpaid_bookings = $wpdb->get_results(
			"SELECT * FROM $bookings_table WHERE payment_status = 'unpaid' AND created_at < DATE_SUB(NOW(), INTERVAL 3 DAY)"
		);

		foreach ( $unpaid_bookings as $booking ) {
			$this->send_payment_reminder( $booking->id );
		}

		// Send expiry warnings for bookings expiring soon
		$expiring_bookings = $wpdb->get_results(
			"SELECT * FROM $bookings_table WHERE status IN ('confirmed', 'active') AND end_date = DATE_ADD(CURDATE(), INTERVAL 7 DAY)"
		);

		foreach ( $expiring_bookings as $booking ) {
			$this->send_expiry_warning( $booking->id );
		}
	}

	/**
	 * Delete old notifications
	 *
	 * @param int $days Days to keep.
	 * @return int
	 */
	public function delete_old_notifications( $days = 30 ) {
		global $wpdb;

		$notifications_table = $wpdb->prefix . 'royal_notifications';

		return $wpdb->query(
			$wpdb->prepare(
				"DELETE FROM $notifications_table WHERE created_at < DATE_SUB(NOW(), INTERVAL %d DAY)",
				$days
			)
		);
	}
}

