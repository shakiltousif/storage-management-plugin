<?php
/**
 * Storage Unit Model Class
 *
 * @package RoyalStorage
 * @since 1.0.0
 */

namespace RoyalStorage\Models;

/**
 * Storage Unit Model
 */
class StorageUnit {

	/**
	 * Unit ID
	 *
	 * @var int
	 */
	private $id;

	/**
	 * Unit size (M, L, XL)
	 *
	 * @var string
	 */
	private $size;

	/**
	 * Unit status
	 *
	 * @var string
	 */
	private $status;

	/**
	 * Unit price per day
	 *
	 * @var float
	 */
	private $price_per_day;

	/**
	 * Unit price per week
	 *
	 * @var float
	 */
	private $price_per_week;

	/**
	 * Unit price per month
	 *
	 * @var float
	 */
	private $price_per_month;

	/**
	 * Constructor
	 *
	 * @param int $id Unit ID.
	 */
	public function __construct( $id = 0 ) {
		$this->id = $id;
		if ( $id > 0 ) {
			$this->load();
		}
	}

	/**
	 * Load unit data from database
	 *
	 * @return void
	 */
	private function load() {
		global $wpdb;
		$table = $wpdb->prefix . 'royal_storage_units';
		$unit  = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$table} WHERE id = %d", $this->id ) );

		if ( $unit ) {
			$this->size            = $unit->size;
			$this->status          = $unit->status;
			$this->price_per_day   = $unit->price_per_day;
			$this->price_per_week  = $unit->price_per_week;
			$this->price_per_month = $unit->price_per_month;
		}
	}

	/**
	 * Get unit ID
	 *
	 * @return int
	 */
	public function get_id() {
		return $this->id;
	}

	/**
	 * Get unit size
	 *
	 * @return string
	 */
	public function get_size() {
		return $this->size;
	}

	/**
	 * Set unit size
	 *
	 * @param string $size Unit size.
	 * @return void
	 */
	public function set_size( $size ) {
		$this->size = $size;
	}

	/**
	 * Get unit status
	 *
	 * @return string
	 */
	public function get_status() {
		return $this->status;
	}

	/**
	 * Set unit status
	 *
	 * @param string $status Unit status.
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
	 * Save unit to database
	 *
	 * @return bool
	 */
	public function save() {
		global $wpdb;
		$table = $wpdb->prefix . 'royal_storage_units';

		$data = array(
			'size'            => $this->size,
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
	 * Delete unit from database
	 *
	 * @return bool
	 */
	public function delete() {
		global $wpdb;
		$table = $wpdb->prefix . 'royal_storage_units';
		return $wpdb->delete( $table, array( 'id' => $this->id ) );
	}

	/**
	 * Check if unit is available for dates
	 *
	 * @param string $start_date Start date (Y-m-d).
	 * @param string $end_date   End date (Y-m-d).
	 * @return bool
	 */
	public function is_available( $start_date, $end_date ) {
		global $wpdb;
		$bookings_table = $wpdb->prefix . 'royal_bookings';

		$query = $wpdb->prepare(
			"SELECT COUNT(*) FROM {$bookings_table} 
			WHERE unit_id = %d 
			AND unit_type = 'storage'
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

