<?php
/**
 * Pricing Engine Class
 *
 * @package RoyalStorage
 * @since 1.0.0
 */

namespace RoyalStorage;

/**
 * Pricing Engine
 */
class PricingEngine {

	/**
	 * VAT rate (20% for Serbia)
	 *
	 * @var float
	 */
	private $vat_rate = 0.20;

	/**
	 * Calculate price for booking
	 *
	 * @param float  $base_price Base price.
	 * @param string $start_date Start date (Y-m-d).
	 * @param string $end_date   End date (Y-m-d).
	 * @param string $period     Period type (daily, weekly, monthly).
	 * @return array
	 */
	public function calculate_price( $base_price, $start_date, $end_date, $period = 'daily' ) {
		$start = new \DateTime( $start_date );
		$end   = new \DateTime( $end_date );
		$days  = $end->diff( $start )->days;

		$subtotal = 0;

		switch ( $period ) {
			case 'daily':
				$subtotal = $base_price * $days;
				break;
			case 'weekly':
				$weeks    = floor( $days / 7 );
				$remaining_days = $days % 7;
				$daily_rate = $base_price / 7;
				$subtotal   = ( $weeks * $base_price ) + ( $remaining_days * $daily_rate );
				break;
			case 'monthly':
				$months = floor( $days / 30 );
				$remaining_days = $days % 30;
				$daily_rate = $base_price / 30;
				$subtotal   = ( $months * $base_price ) + ( $remaining_days * $daily_rate );
				break;
		}

		$vat   = $subtotal * $this->vat_rate;
		$total = $subtotal + $vat;

		return array(
			'subtotal' => round( $subtotal, 2 ),
			'vat'      => round( $vat, 2 ),
			'total'    => round( $total, 2 ),
			'days'     => $days,
		);
	}

	/**
	 * Apply discount
	 *
	 * @param float $price    Original price.
	 * @param float $discount Discount percentage (0-100).
	 * @return float
	 */
	public function apply_discount( $price, $discount ) {
		$discount_amount = ( $price * $discount ) / 100;
		return round( $price - $discount_amount, 2 );
	}

	/**
	 * Calculate late fee
	 *
	 * @param float $daily_rate Daily rate.
	 * @param int   $days_overdue Days overdue.
	 * @return float
	 */
	public function calculate_late_fee( $daily_rate, $days_overdue ) {
		// 50% surcharge on daily rate for overdue days
		$late_fee_rate = $daily_rate * 1.5;
		return round( $late_fee_rate * $days_overdue, 2 );
	}

	/**
	 * Get VAT rate
	 *
	 * @return float
	 */
	public function get_vat_rate() {
		return $this->vat_rate;
	}

	/**
	 * Set VAT rate
	 *
	 * @param float $rate VAT rate.
	 * @return void
	 */
	public function set_vat_rate( $rate ) {
		$this->vat_rate = $rate;
	}

	/**
	 * Calculate prorated price for partial month
	 *
	 * @param float  $monthly_rate Monthly rate.
	 * @param string $start_date   Start date (Y-m-d).
	 * @param string $end_date     End date (Y-m-d).
	 * @return float
	 */
	public function calculate_prorated_price( $monthly_rate, $start_date, $end_date ) {
		$start = new \DateTime( $start_date );
		$end   = new \DateTime( $end_date );
		$days  = $end->diff( $start )->days;

		// Daily rate = monthly rate / 30
		$daily_rate = $monthly_rate / 30;
		$prorated   = $daily_rate * $days;

		return round( $prorated, 2 );
	}

	/**
	 * Format price for display
	 *
	 * @param float $price Price.
	 * @return string
	 */
	public function format_price( $price ) {
		return number_format( $price, 2, '.', ',' ) . ' RSD';
	}

	/**
	 * Get price breakdown
	 *
	 * @param float  $base_price Base price.
	 * @param string $start_date Start date (Y-m-d).
	 * @param string $end_date   End date (Y-m-d).
	 * @param string $period     Period type.
	 * @return array
	 */
	public function get_price_breakdown( $base_price, $start_date, $end_date, $period = 'daily' ) {
		$calculation = $this->calculate_price( $base_price, $start_date, $end_date, $period );

		return array(
			'base_price'  => $base_price,
			'period'      => $period,
			'days'        => $calculation['days'],
			'subtotal'    => $this->format_price( $calculation['subtotal'] ),
			'vat_rate'    => $this->vat_rate * 100 . '%',
			'vat_amount'  => $this->format_price( $calculation['vat'] ),
			'total'       => $this->format_price( $calculation['total'] ),
			'total_raw'   => $calculation['total'],
		);
	}
}

