<?php
/**
 * Booking Class
 *
 * @package RoyalStorage\Frontend
 * @since 1.0.0
 */

namespace RoyalStorage\Frontend;

/**
 * Booking class for frontend booking
 */
class Booking {

	/**
	 * Constructor
	 */
	public function __construct() {
		add_action( 'wp_enqueue_scripts', array( $this, 'init' ) );
		add_action( 'wp_ajax_get_available_units', array( $this, 'get_available_units' ) );
		add_action( 'wp_ajax_nopriv_get_available_units', array( $this, 'get_available_units' ) );
	}

	/**
	 * Initialize booking
	 *
	 * @return void
	 */
	public function init() {
		// Booking initialization code will go here.
	}

	/**
	 * Get available units via AJAX
	 *
	 * @return void
	 */
	public function get_available_units() {
		check_ajax_referer( 'royal-storage-nonce', 'nonce' );

		$unit_type = isset( $_POST['unit_type'] ) ? sanitize_text_field( wp_unslash( $_POST['unit_type'] ) ) : '';
		$start_date = isset( $_POST['start_date'] ) ? sanitize_text_field( wp_unslash( $_POST['start_date'] ) ) : '';
		$end_date = isset( $_POST['end_date'] ) ? sanitize_text_field( wp_unslash( $_POST['end_date'] ) ) : '';

		$available_units = $this->get_available_units_for_dates( $unit_type, $start_date, $end_date );

		wp_send_json_success( $available_units );
	}

	/**
	 * Get available units for dates
	 *
	 * @param string $unit_type Unit type.
	 * @param string $start_date Start date.
	 * @param string $end_date End date.
	 * @return array
	 */
	public function get_available_units_for_dates( $unit_type, $start_date, $end_date ) {
		global $wpdb;

		if ( 'parking' === $unit_type ) {
			$table = $wpdb->prefix . 'royal_parking_spaces';
		} else {
			$table = $wpdb->prefix . 'royal_storage_units';
		}

		$bookings_table = $wpdb->prefix . 'royal_bookings';

		// Get all units.
		$all_units = $wpdb->get_results( "SELECT * FROM $table WHERE status = 'available'" );

		// Get booked units for the date range.
		$booked_units = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT DISTINCT unit_id FROM $bookings_table WHERE unit_type = %s AND status IN ('confirmed', 'active') AND start_date < %s AND end_date > %s",
				$unit_type,
				$end_date,
				$start_date
			)
		);

		$booked_unit_ids = wp_list_pluck( $booked_units, 'unit_id' );

		// Filter available units.
		$available = array_filter(
			$all_units,
			function( $unit ) use ( $booked_unit_ids ) {
				return ! in_array( $unit->id, $booked_unit_ids, true );
			}
		);

		return array_values( $available );
	}

	/**
	 * Calculate booking price
	 *
	 * @param float  $base_price Base price.
	 * @param string $start_date Start date.
	 * @param string $end_date End date.
	 * @param string $period Period (daily, weekly, monthly).
	 * @return array
	 */
	public function calculate_booking_price( $base_price, $start_date, $end_date, $period = 'daily' ) {
		$start = new \DateTime( $start_date );
		$end = new \DateTime( $end_date );
		$days = $end->diff( $start )->days;

		$subtotal = 0;

		switch ( $period ) {
			case 'weekly':
				$weeks = floor( $days / 7 );
				$remaining_days = $days % 7;
				$subtotal = ( $weeks * $base_price ) + ( $remaining_days * ( $base_price / 7 ) );
				break;

			case 'monthly':
				$months = floor( $days / 30 );
				$remaining_days = $days % 30;
				$subtotal = ( $months * $base_price ) + ( $remaining_days * ( $base_price / 30 ) );
				break;

			case 'daily':
			default:
				$subtotal = $days * $base_price;
				break;
		}

		$vat_rate = floatval( get_option( 'royal_storage_vat_rate', 20 ) );
		$vat = $subtotal * ( $vat_rate / 100 );
		$total = $subtotal + $vat;

		return array(
			'subtotal' => $subtotal,
			'vat'      => $vat,
			'total'    => $total,
		);
	}
}

