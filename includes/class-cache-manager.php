<?php
/**
 * Cache Manager Class
 *
 * @package RoyalStorage
 * @since 1.0.0
 */

namespace RoyalStorage;

/**
 * Cache manager class for performance optimization
 */
class CacheManager {

	/**
	 * Cache prefix
	 *
	 * @var string
	 */
	private $prefix = 'royal_storage_';

	/**
	 * Cache expiration time (in seconds)
	 *
	 * @var int
	 */
	private $expiration = 3600;

	/**
	 * Get cached data
	 *
	 * @param string $key Cache key.
	 * @return mixed
	 */
	public function get( $key ) {
		return get_transient( $this->prefix . $key );
	}

	/**
	 * Set cached data
	 *
	 * @param string $key Cache key.
	 * @param mixed  $value Cache value.
	 * @param int    $expiration Expiration time in seconds.
	 * @return bool
	 */
	public function set( $key, $value, $expiration = null ) {
		if ( null === $expiration ) {
			$expiration = $this->expiration;
		}

		return set_transient( $this->prefix . $key, $value, $expiration );
	}

	/**
	 * Delete cached data
	 *
	 * @param string $key Cache key.
	 * @return bool
	 */
	public function delete( $key ) {
		return delete_transient( $this->prefix . $key );
	}

	/**
	 * Clear all cache
	 *
	 * @return void
	 */
	public function clear_all() {
		global $wpdb;

		$wpdb->query(
			$wpdb->prepare(
				"DELETE FROM $wpdb->options WHERE option_name LIKE %s",
				$wpdb->esc_like( '_transient_' . $this->prefix ) . '%'
			)
		);
	}

	/**
	 * Cache booking data
	 *
	 * @param int $booking_id Booking ID.
	 * @return bool
	 */
	public function cache_booking( $booking_id ) {
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

		return $this->set( 'booking_' . $booking_id, $booking, 3600 );
	}

	/**
	 * Get cached booking
	 *
	 * @param int $booking_id Booking ID.
	 * @return object|false
	 */
	public function get_cached_booking( $booking_id ) {
		$cached = $this->get( 'booking_' . $booking_id );

		if ( $cached ) {
			return $cached;
		}

		$this->cache_booking( $booking_id );
		return $this->get( 'booking_' . $booking_id );
	}

	/**
	 * Cache unit availability
	 *
	 * @param int $unit_id Unit ID.
	 * @return bool
	 */
	public function cache_unit_availability( $unit_id ) {
		global $wpdb;

		$units_table = $wpdb->prefix . 'royal_storage_units';
		$unit = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM $units_table WHERE id = %d",
				$unit_id
			)
		);

		if ( ! $unit ) {
			return false;
		}

		return $this->set( 'unit_availability_' . $unit_id, $unit, 1800 );
	}

	/**
	 * Get cached unit availability
	 *
	 * @param int $unit_id Unit ID.
	 * @return object|false
	 */
	public function get_cached_unit_availability( $unit_id ) {
		$cached = $this->get( 'unit_availability_' . $unit_id );

		if ( $cached ) {
			return $cached;
		}

		$this->cache_unit_availability( $unit_id );
		return $this->get( 'unit_availability_' . $unit_id );
	}

	/**
	 * Cache dashboard metrics
	 *
	 * @return bool
	 */
	public function cache_dashboard_metrics() {
		$reports = new AdvancedReports();
		$metrics = $reports->get_dashboard_metrics();

		return $this->set( 'dashboard_metrics', $metrics, 1800 );
	}

	/**
	 * Get cached dashboard metrics
	 *
	 * @return object|false
	 */
	public function get_cached_dashboard_metrics() {
		$cached = $this->get( 'dashboard_metrics' );

		if ( $cached ) {
			return $cached;
		}

		$this->cache_dashboard_metrics();
		return $this->get( 'dashboard_metrics' );
	}

	/**
	 * Cache customer bookings
	 *
	 * @param int $customer_id Customer ID.
	 * @return bool
	 */
	public function cache_customer_bookings( $customer_id ) {
		global $wpdb;

		$bookings_table = $wpdb->prefix . 'royal_bookings';
		$bookings = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM $bookings_table WHERE customer_id = %d ORDER BY created_at DESC",
				$customer_id
			)
		);

		return $this->set( 'customer_bookings_' . $customer_id, $bookings, 3600 );
	}

	/**
	 * Get cached customer bookings
	 *
	 * @param int $customer_id Customer ID.
	 * @return array|false
	 */
	public function get_cached_customer_bookings( $customer_id ) {
		$cached = $this->get( 'customer_bookings_' . $customer_id );

		if ( $cached ) {
			return $cached;
		}

		$this->cache_customer_bookings( $customer_id );
		return $this->get( 'customer_bookings_' . $customer_id );
	}

	/**
	 * Invalidate booking cache
	 *
	 * @param int $booking_id Booking ID.
	 * @return void
	 */
	public function invalidate_booking_cache( $booking_id ) {
		global $wpdb;

		$bookings_table = $wpdb->prefix . 'royal_bookings';
		$booking = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM $bookings_table WHERE id = %d",
				$booking_id
			)
		);

		if ( $booking ) {
			$this->delete( 'booking_' . $booking_id );
			$this->delete( 'customer_bookings_' . $booking->customer_id );
		}
	}

	/**
	 * Invalidate unit cache
	 *
	 * @param int $unit_id Unit ID.
	 * @return void
	 */
	public function invalidate_unit_cache( $unit_id ) {
		$this->delete( 'unit_availability_' . $unit_id );
		$this->delete( 'dashboard_metrics' );
	}
}