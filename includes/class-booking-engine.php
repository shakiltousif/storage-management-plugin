<?php
/**
 * Booking Engine Class
 *
 * @package RoyalStorage
 * @since 1.0.0
 */

namespace RoyalStorage;

use RoyalStorage\Models\Booking;
use RoyalStorage\Models\StorageUnit;
use RoyalStorage\Models\ParkingSpace;

/**
 * Booking Engine
 */
class BookingEngine {

	/**
	 * Pricing engine instance
	 *
	 * @var PricingEngine
	 */
	private $pricing_engine;

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->pricing_engine = new PricingEngine();
	}

	/**
	 * Create booking
	 *
	 * @param array $booking_data Booking data.
	 * @return Booking|false
	 */
	public function create_booking( $booking_data ) {
		// Validate booking data
		if ( ! $this->validate_booking_data( $booking_data ) ) {
			return false;
		}

		// Check availability
		if ( ! $this->check_availability( $booking_data ) ) {
			return false;
		}

		// Create booking object
		$booking = new Booking();
		$booking->set_customer_id( $booking_data['customer_id'] );
		$booking->set_unit_id( $booking_data['unit_id'] ?? 0 );
		$booking->set_space_id( $booking_data['space_id'] ?? 0 );
		$booking->set_start_date( $booking_data['start_date'] );
		$booking->set_end_date( $booking_data['end_date'] );
		$booking->set_status( 'pending' );
		$booking->set_payment_status( 'unpaid' );
		$booking->set_access_code( $this->generate_access_code() );

		// Calculate price
		$price_data = $this->calculate_booking_price( $booking_data );
		$booking->set_total_price( $price_data['total'] );

		// Save booking
		if ( $booking->save() ) {
			return $booking;
		}

		return false;
	}

	/**
	 * Validate booking data
	 *
	 * @param array $booking_data Booking data.
	 * @return bool
	 */
	private function validate_booking_data( $booking_data ) {
		// Check required fields
		if ( empty( $booking_data['customer_id'] ) || empty( $booking_data['start_date'] ) || empty( $booking_data['end_date'] ) ) {
			return false;
		}

		// Check dates
		$start = new \DateTime( $booking_data['start_date'] );
		$end   = new \DateTime( $booking_data['end_date'] );

		if ( $start >= $end ) {
			return false;
		}

		// Check minimum 1 month rental
		$days = $end->diff( $start )->days;
		if ( $days < 30 ) {
			return false;
		}

		return true;
	}

	/**
	 * Check availability
	 *
	 * @param array $booking_data Booking data.
	 * @return bool
	 */
	private function check_availability( $booking_data ) {
		if ( ! empty( $booking_data['unit_id'] ) ) {
			$unit = new StorageUnit( $booking_data['unit_id'] );
			return $unit->is_available( $booking_data['start_date'], $booking_data['end_date'] );
		}

		if ( ! empty( $booking_data['space_id'] ) ) {
			$space = new ParkingSpace( $booking_data['space_id'] );
			return $space->is_available( $booking_data['start_date'], $booking_data['end_date'] );
		}

		return false;
	}

	/**
	 * Calculate booking price
	 *
	 * @param array $booking_data Booking data.
	 * @return array
	 */
	public function calculate_booking_price( $booking_data ) {
		$base_price = 0;
		$period     = $booking_data['period'] ?? 'monthly';

		if ( ! empty( $booking_data['unit_id'] ) ) {
			$unit       = new StorageUnit( $booking_data['unit_id'] );
			$base_price = $unit->get_price_per_month();
		} elseif ( ! empty( $booking_data['space_id'] ) ) {
			$space      = new ParkingSpace( $booking_data['space_id'] );
			$base_price = $space->get_price_per_month();
		}

		$price_data = $this->pricing_engine->calculate_price(
			$base_price,
			$booking_data['start_date'],
			$booking_data['end_date'],
			$period
		);

		return $price_data;
	}

	/**
	 * Generate access code
	 *
	 * @return string
	 */
	private function generate_access_code() {
		$characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$code       = '';
		for ( $i = 0; $i < 8; $i++ ) {
			$code .= $characters[ wp_rand( 0, strlen( $characters ) - 1 ) ];
		}
		return $code;
	}

	/**
	 * Get available units
	 *
	 * @param string $start_date Start date (Y-m-d).
	 * @param string $end_date   End date (Y-m-d).
	 * @return array
	 */
	public function get_available_units( $start_date, $end_date ) {
		global $wpdb;
		$table = $wpdb->prefix . 'royal_storage_units';

		$query = $wpdb->prepare(
			"SELECT * FROM {$table} 
			WHERE id NOT IN (
				SELECT unit_id FROM {$wpdb->prefix}royal_storage_bookings 
				WHERE status != 'cancelled'
				AND (
					(start_date <= %s AND end_date >= %s) OR
					(start_date <= %s AND end_date >= %s) OR
					(start_date >= %s AND end_date <= %s)
				)
			)
			AND status = 'available'",
			$end_date,
			$start_date,
			$end_date,
			$start_date,
			$start_date,
			$end_date
		);

		return $wpdb->get_results( $query );
	}

	/**
	 * Get available parking spaces
	 *
	 * @param string $start_date Start date (Y-m-d).
	 * @param string $end_date   End date (Y-m-d).
	 * @return array
	 */
	public function get_available_spaces( $start_date, $end_date ) {
		global $wpdb;
		$table = $wpdb->prefix . 'royal_parking_spaces';

		$query = $wpdb->prepare(
			"SELECT * FROM {$table} 
			WHERE id NOT IN (
				SELECT space_id FROM {$wpdb->prefix}royal_storage_bookings 
				WHERE status != 'cancelled'
				AND (
					(start_date <= %s AND end_date >= %s) OR
					(start_date <= %s AND end_date >= %s) OR
					(start_date >= %s AND end_date <= %s)
				)
			)
			AND status = 'available'",
			$end_date,
			$start_date,
			$end_date,
			$start_date,
			$start_date,
			$end_date
		);

		return $wpdb->get_results( $query );
	}

	/**
	 * Cancel booking
	 *
	 * @param int $booking_id Booking ID.
	 * @return bool
	 */
	public function cancel_booking( $booking_id ) {
		$booking = new Booking( $booking_id );
		$booking->set_status( 'cancelled' );
		return $booking->save();
	}

	/**
	 * Renew booking
	 *
	 * @param int    $booking_id Booking ID.
	 * @param string $new_end_date New end date (Y-m-d).
	 * @return bool
	 */
	public function renew_booking( $booking_id, $new_end_date ) {
		$booking = new Booking( $booking_id );
		$booking->set_end_date( $new_end_date );
		return $booking->save();
	}
}

