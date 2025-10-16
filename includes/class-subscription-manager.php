<?php
/**
 * Subscription Manager Class
 *
 * @package RoyalStorage
 * @since 1.0.0
 */

namespace RoyalStorage;

/**
 * Subscription manager class for recurring bookings
 */
class SubscriptionManager {

	/**
	 * Constructor
	 */
	public function __construct() {
		add_action( 'wp_scheduled_event_royal_storage_subscriptions', array( $this, 'process_subscriptions' ) );
	}

	/**
	 * Create subscription
	 *
	 * @param int    $customer_id Customer ID.
	 * @param int    $booking_id Booking ID.
	 * @param string $frequency Frequency (monthly, quarterly, yearly).
	 * @return int|false
	 */
	public function create_subscription( $customer_id, $booking_id, $frequency = 'monthly' ) {
		global $wpdb;

		$subscriptions_table = $wpdb->prefix . 'royal_subscriptions';

		$result = $wpdb->insert(
			$subscriptions_table,
			array(
				'customer_id'        => $customer_id,
				'booking_id'         => $booking_id,
				'status'             => 'active',
				'next_billing_date'  => $this->get_next_billing_date( $frequency ),
				'billing_frequency'  => $frequency,
				'created_at'         => current_time( 'mysql' ),
			),
			array( '%d', '%d', '%s', '%s', '%s', '%s' )
		);

		return $result ? $wpdb->insert_id : false;
	}

	/**
	 * Get next billing date
	 *
	 * @param string $frequency Frequency.
	 * @return string
	 */
	private function get_next_billing_date( $frequency ) {
		$date = new \DateTime();

		switch ( $frequency ) {
			case 'quarterly':
				$date->add( new \DateInterval( 'P3M' ) );
				break;

			case 'yearly':
				$date->add( new \DateInterval( 'P1Y' ) );
				break;

			case 'monthly':
			default:
				$date->add( new \DateInterval( 'P1M' ) );
				break;
		}

		return $date->format( 'Y-m-d' );
	}

	/**
	 * Get customer subscriptions
	 *
	 * @param int $customer_id Customer ID.
	 * @return array
	 */
	public function get_customer_subscriptions( $customer_id ) {
		global $wpdb;

		$subscriptions_table = $wpdb->prefix . 'royal_subscriptions';

		return $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM $subscriptions_table WHERE customer_id = %d ORDER BY created_at DESC",
				$customer_id
			)
		);
	}

	/**
	 * Get active subscriptions
	 *
	 * @return array
	 */
	public function get_active_subscriptions() {
		global $wpdb;

		$subscriptions_table = $wpdb->prefix . 'royal_subscriptions';

		return $wpdb->get_results(
			"SELECT * FROM $subscriptions_table WHERE status = 'active' AND next_billing_date <= CURDATE()"
		);
	}

	/**
	 * Cancel subscription
	 *
	 * @param int $subscription_id Subscription ID.
	 * @return bool
	 */
	public function cancel_subscription( $subscription_id ) {
		global $wpdb;

		$subscriptions_table = $wpdb->prefix . 'royal_subscriptions';

		return $wpdb->update(
			$subscriptions_table,
			array( 'status' => 'cancelled' ),
			array( 'id' => $subscription_id ),
			array( '%s' ),
			array( '%d' )
		);
	}

	/**
	 * Process subscriptions
	 *
	 * @return void
	 */
	public function process_subscriptions() {
		$subscriptions = $this->get_active_subscriptions();

		foreach ( $subscriptions as $subscription ) {
			$this->process_subscription_billing( $subscription );
		}
	}

	/**
	 * Process subscription billing
	 *
	 * @param object $subscription Subscription object.
	 * @return bool
	 */
	private function process_subscription_billing( $subscription ) {
		global $wpdb;

		// Get the original booking to create a new one
		$bookings_table = $wpdb->prefix . 'royal_bookings';
		$original_booking = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM $bookings_table WHERE id = %d",
				$subscription->booking_id
			)
		);

		if ( ! $original_booking ) {
			return false;
		}

		// Create new booking
		$booking_engine = new BookingEngine();
		$start_date = date( 'Y-m-d' );
		$end_date = date( 'Y-m-d', strtotime( $this->get_frequency_days( $subscription->billing_frequency ) ) );

		$booking_id = $booking_engine->create_booking(
			array(
				'customer_id'    => $subscription->customer_id,
				'unit_id'        => $original_booking->unit_id,
				'unit_type'      => $original_booking->unit_type,
				'start_date'     => $start_date,
				'end_date'       => $end_date,
				'total_price'    => $original_booking->total_price,
				'payment_method' => 'subscription',
			)
		);

		if ( ! $booking_id ) {
			return false;
		}

		// Update next billing date
		$subscriptions_table = $wpdb->prefix . 'royal_subscriptions';
		$wpdb->update(
			$subscriptions_table,
			array( 'next_billing_date' => $this->get_next_billing_date( $subscription->billing_frequency ) ),
			array( 'id' => $subscription->id ),
			array( '%s' ),
			array( '%d' )
		);

		// Send notification
		$notification_manager = new NotificationManager();
		$notification_manager->create_notification(
			$subscription->customer_id,
			'subscription_billing',
			'Subscription Billing',
			'Your subscription has been billed. Booking #' . $booking_id . ' created.',
			$booking_id
		);

		return true;
	}

	/**
	 * Get frequency days
	 *
	 * @param string $frequency Frequency.
	 * @return string
	 */
	private function get_frequency_days( $frequency ) {
		switch ( $frequency ) {
			case 'quarterly':
				return '+3 months';

			case 'yearly':
				return '+1 year';

			case 'monthly':
			default:
				return '+1 month';
		}
	}

	/**
	 * Get subscription stats
	 *
	 * @return object
	 */
	public function get_subscription_stats() {
		global $wpdb;

		$subscriptions_table = $wpdb->prefix . 'royal_subscriptions';
		$bookings_table = $wpdb->prefix . 'royal_bookings';

		$active = $wpdb->get_var(
			"SELECT COUNT(*) FROM $subscriptions_table WHERE status = 'active'"
		);

		// Get total revenue from active subscriptions' bookings
		$total_revenue = $wpdb->get_var(
			"SELECT SUM(b.total_price) FROM $subscriptions_table s 
			 JOIN $bookings_table b ON s.booking_id = b.id 
			 WHERE s.status = 'active'"
		);

		return (object) array(
			'active_subscriptions' => intval( $active ?: 0 ),
			'monthly_revenue'      => floatval( $total_revenue ?: 0 ),
		);
	}
}

