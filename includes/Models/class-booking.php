<?php
/**
 * Booking Model Class
 *
 * @package RoyalStorage
 * @since 1.0.0
 */

namespace RoyalStorage\Models;

/**
 * Booking Model
 */
class Booking {

	/**
	 * Booking ID
	 *
	 * @var int
	 */
	private $id;

	/**
	 * Customer ID
	 *
	 * @var int
	 */
	private $customer_id;

	/**
	 * Unit ID
	 *
	 * @var int
	 */
	private $unit_id;

	/**
	 * Space ID
	 *
	 * @var int
	 */
	private $space_id;

	/**
	 * Start date
	 *
	 * @var string
	 */
	private $start_date;

	/**
	 * End date
	 *
	 * @var string
	 */
	private $end_date;

	/**
	 * Booking status
	 *
	 * @var string
	 */
	private $status;

	/**
	 * Total price
	 *
	 * @var float
	 */
	private $total_price;

	/**
	 * Payment status
	 *
	 * @var string
	 */
	private $payment_status;

	/**
	 * Access code
	 *
	 * @var string
	 */
	private $access_code;

	/**
	 * Constructor
	 *
	 * @param int $id Booking ID.
	 */
	public function __construct( $id = 0 ) {
		$this->id = $id;
		if ( $id > 0 ) {
			$this->load();
		}
	}

	/**
	 * Load booking data from database
	 *
	 * @return void
	 */
	private function load() {
		global $wpdb;
		$table   = $wpdb->prefix . 'royal_storage_bookings';
		$booking = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$table} WHERE id = %d", $this->id ) );

		if ( $booking ) {
			$this->customer_id   = $booking->customer_id;
			$this->unit_id       = $booking->unit_id;
			$this->space_id      = $booking->space_id;
			$this->start_date    = $booking->start_date;
			$this->end_date      = $booking->end_date;
			$this->status        = $booking->status;
			$this->total_price   = $booking->total_price;
			$this->payment_status = $booking->payment_status;
			$this->access_code   = $booking->access_code;
		}
	}

	/**
	 * Get booking ID
	 *
	 * @return int
	 */
	public function get_id() {
		return $this->id;
	}

	/**
	 * Get customer ID
	 *
	 * @return int
	 */
	public function get_customer_id() {
		return $this->customer_id;
	}

	/**
	 * Set customer ID
	 *
	 * @param int $customer_id Customer ID.
	 * @return void
	 */
	public function set_customer_id( $customer_id ) {
		$this->customer_id = $customer_id;
	}

	/**
	 * Get unit ID
	 *
	 * @return int
	 */
	public function get_unit_id() {
		return $this->unit_id;
	}

	/**
	 * Set unit ID
	 *
	 * @param int $unit_id Unit ID.
	 * @return void
	 */
	public function set_unit_id( $unit_id ) {
		$this->unit_id = $unit_id;
	}

	/**
	 * Get space ID
	 *
	 * @return int
	 */
	public function get_space_id() {
		return $this->space_id;
	}

	/**
	 * Set space ID
	 *
	 * @param int $space_id Space ID.
	 * @return void
	 */
	public function set_space_id( $space_id ) {
		$this->space_id = $space_id;
	}

	/**
	 * Get start date
	 *
	 * @return string
	 */
	public function get_start_date() {
		return $this->start_date;
	}

	/**
	 * Set start date
	 *
	 * @param string $start_date Start date (Y-m-d).
	 * @return void
	 */
	public function set_start_date( $start_date ) {
		$this->start_date = $start_date;
	}

	/**
	 * Get end date
	 *
	 * @return string
	 */
	public function get_end_date() {
		return $this->end_date;
	}

	/**
	 * Set end date
	 *
	 * @param string $end_date End date (Y-m-d).
	 * @return void
	 */
	public function set_end_date( $end_date ) {
		$this->end_date = $end_date;
	}

	/**
	 * Get booking status
	 *
	 * @return string
	 */
	public function get_status() {
		return $this->status;
	}

	/**
	 * Set booking status
	 *
	 * @param string $status Booking status.
	 * @return void
	 */
	public function set_status( $status ) {
		$this->status = $status;
	}

	/**
	 * Get total price
	 *
	 * @return float
	 */
	public function get_total_price() {
		return $this->total_price;
	}

	/**
	 * Set total price
	 *
	 * @param float $total_price Total price.
	 * @return void
	 */
	public function set_total_price( $total_price ) {
		$this->total_price = $total_price;
	}

	/**
	 * Get payment status
	 *
	 * @return string
	 */
	public function get_payment_status() {
		return $this->payment_status;
	}

	/**
	 * Set payment status
	 *
	 * @param string $payment_status Payment status.
	 * @return void
	 */
	public function set_payment_status( $payment_status ) {
		$this->payment_status = $payment_status;
	}

	/**
	 * Get access code
	 *
	 * @return string
	 */
	public function get_access_code() {
		return $this->access_code;
	}

	/**
	 * Set access code
	 *
	 * @param string $access_code Access code.
	 * @return void
	 */
	public function set_access_code( $access_code ) {
		$this->access_code = $access_code;
	}

	/**
	 * Save booking to database
	 *
	 * @return bool
	 */
	public function save() {
		global $wpdb;
		$table = $wpdb->prefix . 'royal_storage_bookings';

		$data = array(
			'customer_id'    => $this->customer_id,
			'unit_id'        => $this->unit_id,
			'space_id'       => $this->space_id,
			'start_date'     => $this->start_date,
			'end_date'       => $this->end_date,
			'status'         => $this->status,
			'total_price'    => $this->total_price,
			'payment_status' => $this->payment_status,
			'access_code'    => $this->access_code,
		);

		if ( $this->id > 0 ) {
			return $wpdb->update( $table, $data, array( 'id' => $this->id ) );
		} else {
			$result = $wpdb->insert( $table, $data );
			if ( $result ) {
				$this->id = $wpdb->insert_id;
			}
			return $result;
		}
	}

	/**
	 * Delete booking from database
	 *
	 * @return bool
	 */
	public function delete() {
		global $wpdb;
		$table = $wpdb->prefix . 'royal_storage_bookings';
		return $wpdb->delete( $table, array( 'id' => $this->id ) );
	}

	/**
	 * Calculate number of days
	 *
	 * @return int
	 */
	public function get_days() {
		$start = new \DateTime( $this->start_date );
		$end   = new \DateTime( $this->end_date );
		$diff  = $end->diff( $start );
		return $diff->days;
	}
}

