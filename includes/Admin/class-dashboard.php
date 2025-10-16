<?php
/**
 * Dashboard Class
 *
 * @package RoyalStorage\Admin
 * @since 1.0.0
 */

namespace RoyalStorage\Admin;

use RoyalStorage\Models\StorageUnit;
use RoyalStorage\Models\ParkingSpace;
use RoyalStorage\Models\Booking;
use RoyalStorage\PricingEngine;

/**
 * Dashboard class for admin dashboard
 */
class Dashboard {

	/**
	 * Constructor
	 */
	public function __construct() {
		add_action( 'admin_init', array( $this, 'init' ) );
		add_action( 'wp_dashboard_setup', array( $this, 'add_dashboard_widgets' ) );
	}

	/**
	 * Initialize dashboard
	 *
	 * @return void
	 */
	public function init() {
		// Dashboard initialization code.
	}

	/**
	 * Add dashboard widgets
	 *
	 * @return void
	 */
	public function add_dashboard_widgets() {
		wp_add_dashboard_widget(
			'royal_storage_dashboard',
			__( 'Royal Storage Overview', 'royal-storage' ),
			array( $this, 'render_dashboard_widget' )
		);
	}

	/**
	 * Render dashboard widget
	 *
	 * @return void
	 */
	public function render_dashboard_widget() {
		?>
		<div class="royal-storage-dashboard">
			<div class="dashboard-row">
				<div class="dashboard-card">
					<h3><?php esc_html_e( 'Total Units', 'royal-storage' ); ?></h3>
					<p class="dashboard-number"><?php echo esc_html( $this->get_total_units() ); ?></p>
				</div>
				<div class="dashboard-card">
					<h3><?php esc_html_e( 'Occupied Units', 'royal-storage' ); ?></h3>
					<p class="dashboard-number"><?php echo esc_html( $this->get_occupied_units() ); ?></p>
				</div>
				<div class="dashboard-card">
					<h3><?php esc_html_e( 'Available Units', 'royal-storage' ); ?></h3>
					<p class="dashboard-number"><?php echo esc_html( $this->get_available_units() ); ?></p>
				</div>
				<div class="dashboard-card">
					<h3><?php esc_html_e( 'Occupancy Rate', 'royal-storage' ); ?></h3>
					<p class="dashboard-number"><?php echo esc_html( round( $this->get_occupancy_rate(), 2 ) ); ?>%</p>
				</div>
			</div>

			<div class="dashboard-row">
				<div class="dashboard-card">
					<h3><?php esc_html_e( 'Overdue Bookings', 'royal-storage' ); ?></h3>
					<p class="dashboard-number alert"><?php echo esc_html( $this->get_overdue_bookings_count() ); ?></p>
				</div>
				<div class="dashboard-card">
					<h3><?php esc_html_e( 'Expiring Soon', 'royal-storage' ); ?></h3>
					<p class="dashboard-number warning"><?php echo esc_html( count( $this->get_upcoming_expiries( 7 ) ) ); ?></p>
				</div>
				<div class="dashboard-card">
					<h3><?php esc_html_e( 'Total Revenue (Month)', 'royal-storage' ); ?></h3>
					<p class="dashboard-number"><?php echo esc_html( $this->get_monthly_revenue() ); ?> RSD</p>
				</div>
				<div class="dashboard-card">
					<h3><?php esc_html_e( 'Pending Payments', 'royal-storage' ); ?></h3>
					<p class="dashboard-number"><?php echo esc_html( $this->get_pending_payments_count() ); ?></p>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Get total units
	 *
	 * @return int
	 */
	public function get_total_units() {
		global $wpdb;
		$storage_table = $wpdb->prefix . 'royal_storage_units';
		return (int) $wpdb->get_var( "SELECT COUNT(*) FROM $storage_table" );
	}

	/**
	 * Get occupied units
	 *
	 * @return int
	 */
	public function get_occupied_units() {
		global $wpdb;
		$bookings_table = $wpdb->prefix . 'royal_bookings';
		return (int) $wpdb->get_var( "SELECT COUNT(DISTINCT unit_id) FROM $bookings_table WHERE status IN ('confirmed', 'active') AND unit_id > 0" );
	}

	/**
	 * Get available units
	 *
	 * @return int
	 */
	public function get_available_units() {
		return $this->get_total_units() - $this->get_occupied_units();
	}

	/**
	 * Get occupancy rate
	 *
	 * @return float
	 */
	public function get_occupancy_rate() {
		$total = $this->get_total_units();
		if ( $total === 0 ) {
			return 0;
		}
		return ( $this->get_occupied_units() / $total ) * 100;
	}

	/**
	 * Get overdue bookings count
	 *
	 * @return int
	 */
	public function get_overdue_bookings_count() {
		global $wpdb;
		$bookings_table = $wpdb->prefix . 'royal_bookings';
		return (int) $wpdb->get_var( "SELECT COUNT(*) FROM $bookings_table WHERE end_date < CURDATE() AND status != 'cancelled' AND payment_status != 'paid'" );
	}

	/**
	 * Get upcoming expiries
	 *
	 * @param int $days Days ahead to check.
	 * @return array
	 */
	public function get_upcoming_expiries( $days = 7 ) {
		global $wpdb;
		$bookings_table = $wpdb->prefix . 'royal_bookings';
		return $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM $bookings_table WHERE end_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL %d DAY) AND status IN ('confirmed', 'active')",
				$days
			)
		);
	}

	/**
	 * Get monthly revenue
	 *
	 * @return string
	 */
	public function get_monthly_revenue() {
		global $wpdb;
		$bookings_table = $wpdb->prefix . 'royal_bookings';
		$result = $wpdb->get_var(
			"SELECT SUM(total_price) FROM $bookings_table WHERE MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE()) AND payment_status = 'paid'"
		);
		return number_format( (float) $result, 2, '.', ',' );
	}

	/**
	 * Get pending payments count
	 *
	 * @return int
	 */
	public function get_pending_payments_count() {
		global $wpdb;
		$bookings_table = $wpdb->prefix . 'royal_bookings';
		return (int) $wpdb->get_var( "SELECT COUNT(*) FROM $bookings_table WHERE payment_status = 'pending' OR payment_status = 'unpaid'" );
	}

	/**
	 * Add sample data for demonstration
	 *
	 * @return void
	 */
	public function add_sample_data() {
		global $wpdb;

		// Check if we already have data
		$storage_table = $wpdb->prefix . 'royal_storage_units';
		$existing_units = $wpdb->get_var( "SELECT COUNT(*) FROM $storage_table" );
		
		if ( $existing_units > 0 ) {
			return; // Don't add sample data if we already have data
		}

		// Add sample storage units
		$sample_units = array(
			array( 'post_id' => 0, 'size' => 'M', 'dimensions' => '3x3x3', 'base_price' => 10000, 'status' => 'available' ),
			array( 'post_id' => 0, 'size' => 'L', 'dimensions' => '4x4x4', 'base_price' => 18000, 'status' => 'available' ),
			array( 'post_id' => 0, 'size' => 'XL', 'dimensions' => '5x5x5', 'base_price' => 25000, 'status' => 'available' ),
			array( 'post_id' => 0, 'size' => 'M', 'dimensions' => '3x3x3', 'base_price' => 10000, 'status' => 'available' ),
			array( 'post_id' => 0, 'size' => 'L', 'dimensions' => '4x4x4', 'base_price' => 18000, 'status' => 'available' ),
		);

		foreach ( $sample_units as $unit ) {
			$wpdb->insert(
				$storage_table,
				array(
					'post_id' => $unit['post_id'],
					'size' => $unit['size'],
					'dimensions' => $unit['dimensions'],
					'base_price' => $unit['base_price'],
					'status' => $unit['status'],
					'created_at' => current_time( 'mysql' ),
					'updated_at' => current_time( 'mysql' ),
				),
				array( '%d', '%s', '%s', '%f', '%s', '%s', '%s' )
			);
		}

		// Add sample parking spaces
		$parking_table = $wpdb->prefix . 'royal_parking_spaces';
		$sample_spaces = array(
			array( 'post_id' => 0, 'spot_number' => 1, 'height_limit' => '2.5m', 'base_price' => 5000, 'status' => 'available' ),
			array( 'post_id' => 0, 'spot_number' => 2, 'height_limit' => '2.5m', 'base_price' => 5000, 'status' => 'available' ),
			array( 'post_id' => 0, 'spot_number' => 3, 'height_limit' => '2.5m', 'base_price' => 5000, 'status' => 'available' ),
		);

		foreach ( $sample_spaces as $space ) {
			$wpdb->insert(
				$parking_table,
				array(
					'post_id' => $space['post_id'],
					'spot_number' => $space['spot_number'],
					'height_limit' => $space['height_limit'],
					'base_price' => $space['base_price'],
					'status' => $space['status'],
					'created_at' => current_time( 'mysql' ),
					'updated_at' => current_time( 'mysql' ),
				),
				array( '%d', '%d', '%s', '%f', '%s', '%s', '%s' )
			);
		}

		// Add sample bookings
		$bookings_table = $wpdb->prefix . 'royal_bookings';
		$sample_bookings = array(
			array(
				'post_id' => 0,
				'customer_id' => 1,
				'unit_id' => 1,
				'unit_type' => 'storage',
				'start_date' => date( 'Y-m-d', strtotime( '-10 days' ) ),
				'end_date' => date( 'Y-m-d', strtotime( '+20 days' ) ),
				'total_price' => 10000,
				'vat_amount' => 2000,
				'status' => 'active',
				'payment_status' => 'paid',
				'access_code' => 'RS' . wp_generate_password( 6, false ),
				'created_at' => current_time( 'mysql' ),
				'updated_at' => current_time( 'mysql' ),
			),
			array(
				'post_id' => 0,
				'customer_id' => 1,
				'unit_id' => 2,
				'unit_type' => 'storage',
				'start_date' => date( 'Y-m-d', strtotime( '-5 days' ) ),
				'end_date' => date( 'Y-m-d', strtotime( '+25 days' ) ),
				'total_price' => 18000,
				'vat_amount' => 3600,
				'status' => 'active',
				'payment_status' => 'paid',
				'access_code' => 'RS' . wp_generate_password( 6, false ),
				'created_at' => current_time( 'mysql' ),
				'updated_at' => current_time( 'mysql' ),
			),
			array(
				'post_id' => 0,
				'customer_id' => 1,
				'unit_id' => 1,
				'unit_type' => 'parking',
				'start_date' => date( 'Y-m-d', strtotime( '-3 days' ) ),
				'end_date' => date( 'Y-m-d', strtotime( '+27 days' ) ),
				'total_price' => 5000,
				'vat_amount' => 1000,
				'status' => 'active',
				'payment_status' => 'paid',
				'access_code' => 'RS' . wp_generate_password( 6, false ),
				'created_at' => current_time( 'mysql' ),
				'updated_at' => current_time( 'mysql' ),
			),
			array(
				'post_id' => 0,
				'customer_id' => 1,
				'unit_id' => 3,
				'unit_type' => 'storage',
				'start_date' => date( 'Y-m-d', strtotime( '+2 days' ) ),
				'end_date' => date( 'Y-m-d', strtotime( '+32 days' ) ),
				'total_price' => 25000,
				'vat_amount' => 5000,
				'status' => 'confirmed',
				'payment_status' => 'pending',
				'access_code' => 'RS' . wp_generate_password( 6, false ),
				'created_at' => current_time( 'mysql' ),
				'updated_at' => current_time( 'mysql' ),
			),
		);

		foreach ( $sample_bookings as $booking ) {
			$wpdb->insert(
				$bookings_table,
				$booking,
				array( '%d', '%d', '%d', '%s', '%s', '%s', '%f', '%f', '%s', '%s', '%s', '%s', '%s' )
			);
		}
	}
}

