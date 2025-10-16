<?php
/**
 * Bookings Class
 *
 * @package RoyalStorage\Frontend
 * @since 1.0.0
 */

namespace RoyalStorage\Frontend;

/**
 * Bookings class for customer portal bookings management
 */
class Bookings {

	/**
	 * Constructor
	 */
	public function __construct() {
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
	}

	/**
	 * Enqueue scripts
	 *
	 * @return void
	 */
	public function enqueue_scripts() {
		wp_enqueue_script(
			'royal-storage-bookings',
			ROYAL_STORAGE_URL . 'assets/js/bookings.js',
			array( 'jquery' ),
			ROYAL_STORAGE_VERSION,
			true
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
	 * Get customer active bookings
	 *
	 * @param int $customer_id Customer ID.
	 * @return array
	 */
	public function get_customer_active_bookings( $customer_id ) {
		global $wpdb;

		$bookings_table = $wpdb->prefix . 'royal_bookings';

		return $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM $bookings_table WHERE customer_id = %d AND status IN ('confirmed', 'active') ORDER BY start_date ASC",
				$customer_id
			)
		);
	}

	/**
	 * Get customer stats
	 *
	 * @param int $customer_id Customer ID.
	 * @return object
	 */
	public function get_customer_stats( $customer_id ) {
		global $wpdb;

		$bookings_table = $wpdb->prefix . 'royal_bookings';

		$active = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM $bookings_table WHERE customer_id = %d AND status IN ('confirmed', 'active')",
				$customer_id
			)
		);

		$total = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM $bookings_table WHERE customer_id = %d",
				$customer_id
			)
		);

		$spent = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT SUM(total_price) FROM $bookings_table WHERE customer_id = %d AND payment_status = 'paid'",
				$customer_id
			)
		);

		return (object) array(
			'active_bookings' => $active,
			'total_bookings'  => $total,
			'total_spent'     => $spent ?: 0,
		);
	}

	/**
	 * Get booking details
	 *
	 * @param int $booking_id Booking ID.
	 * @return object|null
	 */
	public function get_booking( $booking_id ) {
		global $wpdb;

		$bookings_table = $wpdb->prefix . 'royal_bookings';

		return $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM $bookings_table WHERE id = %d",
				$booking_id
			)
		);
	}

	/**
	 * Renew booking
	 *
	 * @param int $booking_id Booking ID.
	 * @param int $days Number of days to renew.
	 * @return bool
	 */
	public function renew_booking( $booking_id, $days ) {
		global $wpdb;

		$bookings_table = $wpdb->prefix . 'royal_bookings';

		$booking = $this->get_booking( $booking_id );

		if ( ! $booking ) {
			return false;
		}

		$new_end_date = date( 'Y-m-d', strtotime( $booking->end_date . ' +' . $days . ' days' ) );

		return $wpdb->update(
			$bookings_table,
			array( 'end_date' => $new_end_date ),
			array( 'id' => $booking_id ),
			array( '%s' ),
			array( '%d' )
		);
	}

	/**
	 * Cancel booking
	 *
	 * @param int $booking_id Booking ID.
	 * @return bool
	 */
	public function cancel_booking( $booking_id ) {
		global $wpdb;

		$bookings_table = $wpdb->prefix . 'royal_bookings';

		return $wpdb->update(
			$bookings_table,
			array( 'status' => 'cancelled' ),
			array( 'id' => $booking_id ),
			array( '%s' ),
			array( '%d' )
		);
	}

	/**
	 * Get booking status label
	 *
	 * @param string $status Status.
	 * @return string
	 */
	public static function get_status_label( $status ) {
		$labels = array(
			'pending'   => __( 'Pending', 'royal-storage' ),
			'confirmed' => __( 'Confirmed', 'royal-storage' ),
			'active'    => __( 'Active', 'royal-storage' ),
			'cancelled' => __( 'Cancelled', 'royal-storage' ),
			'expired'   => __( 'Expired', 'royal-storage' ),
		);

		return $labels[ $status ] ?? ucfirst( $status );
	}

	/**
	 * Get payment status label
	 *
	 * @param string $status Status.
	 * @return string
	 */
	public static function get_payment_status_label( $status ) {
		$labels = array(
			'paid'     => __( 'Paid', 'royal-storage' ),
			'unpaid'   => __( 'Unpaid', 'royal-storage' ),
			'pending'  => __( 'Pending', 'royal-storage' ),
			'failed'   => __( 'Failed', 'royal-storage' ),
			'refunded' => __( 'Refunded', 'royal-storage' ),
		);

		return $labels[ $status ] ?? ucfirst( $status );
	}
}

