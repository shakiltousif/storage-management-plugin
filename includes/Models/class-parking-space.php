<?php
/**
 * Parking Space Model Class
 *
 * @package RoyalStorage
 * @since 1.0.0
 */

namespace RoyalStorage\Models;

/**
 * Parking Space Model
 */
class ParkingSpace {

	/**
	 * Space ID
	 *
	 * @var int
	 */
	private $id;

	/**
	 * Spot number
	 *
	 * @var string
	 */
	private $spot_number;

	/**
	 * Space status
	 *
	 * @var string
	 */
	private $status;

	/**
	 * Price per day
	 *
	 * @var float
	 */
	private $price_per_day;

	/**
	 * Price per week
	 *
	 * @var float
	 */
	private $price_per_week;

	/**
	 * Price per month
	 *
	 * @var float
	 */
	private $price_per_month;

	/**
	 * Constructor
	 *
	 * @param int $id Space ID.
	 */
	public function __construct( $id = 0 ) {
		$this->id = $id;
		if ( $id > 0 ) {
			$this->load();
		}
	}

	/**
	 * Load space data from database
	 *
	 * @return void
	 */
	private function load() {
		global $wpdb;
		$table = $wpdb->prefix . 'royal_parking_spaces';
		$space = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$table} WHERE id = %d", $this->id ) );

		if ( $space ) {
			$this->spot_number     = $space->spot_number;
			$this->status          = $space->status;
			$this->price_per_day   = $space->price_per_day;
			$this->price_per_week  = $space->price_per_week;
			$this->price_per_month = $space->price_per_month;
		}
	}

	/**
	 * Get space ID
	 *
	 * @return int
	 */
	public function get_id() {
		return $this->id;
	}

	/**
	 * Get spot number
	 *
	 * @return string
	 */
	public function get_spot_number() {
		return $this->spot_number;
	}

	/**
	 * Set spot number
	 *
	 * @param string $spot_number Spot number.
	 * @return void
	 */
	public function set_spot_number( $spot_number ) {
		$this->spot_number = $spot_number;
	}

	/**
	 * Get space status
	 *
	 * @return string
	 */
	public function get_status() {
		return $this->status;
	}

	/**
	 * Set space status
	 *
	 * @param string $status Space status.
	 * @return void
	 */
	public function set_status( $status ) {
		$this->status = $status;
	}

	/**
	 * Get price per day
	 *
	 * @return float
	 */
	public function get_price_per_day() {
		return $this->price_per_day;
	}

	/**
	 * Set price per day
	 *
	 * @param float $price Price per day.
	 * @return void
	 */
	public function set_price_per_day( $price ) {
		$this->price_per_day = $price;
	}

	/**
	 * Get price per week
	 *
	 * @return float
	 */
	public function get_price_per_week() {
		return $this->price_per_week;
	}

	/**
	 * Set price per week
	 *
	 * @param float $price Price per week.
	 * @return void
	 */
	public function set_price_per_week( $price ) {
		$this->price_per_week = $price;
	}

	/**
	 * Get price per month
	 *
	 * @return float
	 */
	public function get_price_per_month() {
		return $this->price_per_month;
	}

	/**
	 * Set price per month
	 *
	 * @param float $price Price per month.
	 * @return void
	 */
	public function set_price_per_month( $price ) {
		$this->price_per_month = $price;
	}

	/**
	 * Save space to database
	 *
	 * @return bool
	 */
	public function save() {
		global $wpdb;
		$table = $wpdb->prefix . 'royal_parking_spaces';

		$data = array(
			'spot_number'     => $this->spot_number,
			'status'          => $this->status,
			'price_per_day'   => $this->price_per_day,
			'price_per_week'  => $this->price_per_week,
			'price_per_month' => $this->price_per_month,
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
	 * Delete space from database
	 *
	 * @return bool
	 */
	public function delete() {
		global $wpdb;
		$table = $wpdb->prefix . 'royal_parking_spaces';
		return $wpdb->delete( $table, array( 'id' => $this->id ) );
	}

	/**
	 * Check if space is available for dates
	 *
	 * @param string $start_date Start date (Y-m-d).
	 * @param string $end_date   End date (Y-m-d).
	 * @return bool
	 */
	public function is_available( $start_date, $end_date ) {
		global $wpdb;
		$bookings_table = $wpdb->prefix . 'royal_storage_bookings';

		$query = $wpdb->prepare(
			"SELECT COUNT(*) FROM {$bookings_table} 
			WHERE space_id = %d 
			AND status != 'cancelled'
			AND (
				(start_date <= %s AND end_date >= %s) OR
				(start_date <= %s AND end_date >= %s) OR
				(start_date >= %s AND end_date <= %s)
			)",
			$this->id,
			$end_date,
			$start_date,
			$end_date,
			$start_date,
			$start_date,
			$end_date
		);

		return 0 === (int) $wpdb->get_var( $query );
	}
}

