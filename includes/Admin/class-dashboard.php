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
		<div class="royal-storage-admin" style="padding: 0; background: transparent;">
			
			<!-- Key Metrics Grid -->
			<div class="metrics-grid">
				<!-- Total Units -->
				<div class="metric-card">
					<div class="metric-icon" style="color: var(--rs-primary);">
						<span class="dashicons dashicons-store"></span>
					</div>
					<div class="metric-content">
						<h3><?php esc_html_e( 'Total Units', 'royal-storage' ); ?></h3>
						<p class="metric-value">
							<a href="<?php echo esc_url( admin_url( 'admin.php?page=royal-storage-units' ) ); ?>">
								<?php echo esc_html( $this->format_number( $this->get_total_units() ) ); ?>
							</a>
						</p>
					</div>
				</div>

				<!-- Occupied Units -->
				<div class="metric-card">
					<div class="metric-icon" style="color: var(--rs-info);">
						<span class="dashicons dashicons-lock"></span>
					</div>
					<div class="metric-content">
						<h3><?php esc_html_e( 'Occupied', 'royal-storage' ); ?></h3>
						<p class="metric-value">
							<a href="<?php echo esc_url( admin_url( 'admin.php?page=royal-storage-bookings' ) ); ?>">
								<?php echo esc_html( $this->format_number( $this->get_occupied_units() ) ); ?>
							</a>
						</p>
					</div>
				</div>

				<!-- Available Units -->
				<div class="metric-card">
					<div class="metric-icon" style="color: var(--rs-success);">
						<span class="dashicons dashicons-unlock"></span>
					</div>
					<div class="metric-content">
						<h3><?php esc_html_e( 'Available', 'royal-storage' ); ?></h3>
						<p class="metric-value"><?php echo esc_html( $this->format_number( $this->get_available_units() ) ); ?></p>
					</div>
				</div>

				<!-- Occupancy Rate -->
				<div class="metric-card">
					<div class="metric-icon" style="color: var(--rs-secondary);">
						<span class="dashicons dashicons-chart-pie"></span>
					</div>
					<div class="metric-content" style="width: 100%;">
						<div style="display: flex; justify-content: space-between; align-items: baseline;">
							<h3><?php esc_html_e( 'Occupancy', 'royal-storage' ); ?></h3>
							<span style="font-weight: 700; color: var(--rs-text-main);"><?php echo esc_html( round( $this->get_occupancy_rate(), 1 ) ); ?>%</span>
						</div>
						<div class="occupancy-bar" style="margin-top: 8px;">
							<div style="width:<?php echo esc_attr( min(100, max(0, $this->get_occupancy_rate()) ) ); ?>%;"></div>
						</div>
					</div>
				</div>
			</div>

			<!-- Revenue Section -->
			<div style="margin-bottom: 2rem;">
				<div class="revenue-card">
					<div class="revenue-icon">
						<span class="dashicons dashicons-money-alt"></span>
					</div>
					<div class="revenue-content">
						<h3><?php esc_html_e( 'Monthly Revenue', 'royal-storage' ); ?></h3>
						<p class="revenue-value"><?php echo $this->format_currency( $this->get_monthly_revenue() ); ?></p>
						<a href="<?php echo esc_url( admin_url( 'admin.php?page=royal-storage-reports' ) ); ?>" class="revenue-link">
							<?php esc_html_e( 'View Reports', 'royal-storage' ); ?> &rarr;
						</a>
					</div>
				</div>
			</div>

			<!-- Alerts & Actions Grid -->
			<div class="alerts-grid">
				<!-- Overdue Bookings -->
				<div class="alert-card alert-danger">
					<div class="alert-icon">
						<span class="dashicons dashicons-warning"></span>
					</div>
					<div class="alert-content">
						<h3><?php esc_html_e( 'Overdue Bookings', 'royal-storage' ); ?></h3>
						<p class="alert-value">
							<?php echo esc_html( $this->format_number( $this->get_overdue_bookings_count() ) ); ?>
						</p>
						<?php if ( $this->get_overdue_bookings_count() > 0 ) : ?>
							<a href="<?php echo esc_url( admin_url( 'admin.php?page=royal-storage-bookings&filter=overdue' ) ); ?>" class="alert-link">
								<?php esc_html_e( 'Review Now', 'royal-storage' ); ?>
							</a>
						<?php else : ?>
							<span style="font-size: 0.8rem; opacity: 0.7;"><?php esc_html_e( 'All clear', 'royal-storage' ); ?></span>
						<?php endif; ?>
					</div>
				</div>

				<!-- Expiring Soon -->
				<div class="alert-card alert-warning">
					<div class="alert-icon">
						<span class="dashicons dashicons-calendar-alt"></span>
					</div>
					<div class="alert-content">
						<h3><?php esc_html_e( 'Expiring Soon (7 Days)', 'royal-storage' ); ?></h3>
						<p class="alert-value">
							<?php echo esc_html( $this->format_number( count( $this->get_upcoming_expiries( 7 ) ) ) ); ?>
						</p>
						<a href="<?php echo esc_url( admin_url( 'admin.php?page=royal-storage-bookings' ) ); ?>" class="alert-link">
							<?php esc_html_e( 'Check Bookings', 'royal-storage' ); ?>
						</a>
					</div>
				</div>

				<!-- Pending Payments -->
				<div class="alert-card alert-info">
					<div class="alert-icon">
						<span class="dashicons dashicons-clock"></span>
					</div>
					<div class="alert-content">
						<h3><?php esc_html_e( 'Pending Payments', 'royal-storage' ); ?></h3>
						<p class="alert-value">
							<?php echo esc_html( $this->format_number( $this->get_pending_payments_count() ) ); ?>
						</p>
						<a href="<?php echo esc_url( admin_url( 'admin.php?page=royal-storage-payments&status=pending' ) ); ?>" class="alert-link">
							<?php esc_html_e( 'Manage Payments', 'royal-storage' ); ?>
						</a>
					</div>
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
		return (int) $this->get_cached_metric( 'royal_total_units', function() {
			global $wpdb;
			$storage_table = $wpdb->prefix . 'royal_storage_units';
			return (int) $wpdb->get_var( "SELECT COUNT(*) FROM $storage_table" );
		}, 300 );
	}

	/**
	 * Get occupied units
	 *
	 * @return int
	 */
	public function get_occupied_units() {
		return (int) $this->get_cached_metric( 'royal_occupied_units', function() {
			global $wpdb;
			$bookings_table = $wpdb->prefix . 'royal_bookings';
			return (int) $wpdb->get_var( "SELECT COUNT(DISTINCT unit_id) FROM $bookings_table WHERE status IN ('confirmed', 'active') AND unit_id > 0" );
		}, 300 );
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
		return $this->get_cached_metric( 'royal_occupancy_rate', function() {
			$total = $this->get_total_units();
			if ( $total === 0 ) {
				return 0;
			}
			return ( $this->get_occupied_units() / $total ) * 100;
		}, 300 );
	}

	/**
	 * Get overdue bookings count
	 *
	 * @return int
	 */
	public function get_overdue_bookings_count() {
		return (int) $this->get_cached_metric( 'royal_overdue_bookings', function() {
			global $wpdb;
			$bookings_table = $wpdb->prefix . 'royal_bookings';
			return (int) $wpdb->get_var( "SELECT COUNT(*) FROM $bookings_table WHERE end_date < CURDATE() AND status != 'cancelled' AND payment_status != 'paid'" );
		}, 300 );
	}

	/**
	 * Get upcoming expiries
	 *
	 * @param int $days Days ahead to check.
	 * @return array
	 */
	public function get_upcoming_expiries( $days = 7 ) {
		// Not cached because caller may vary $days
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
		return $this->get_cached_metric( 'royal_monthly_revenue', function() {
			global $wpdb;
			$bookings_table = $wpdb->prefix . 'royal_bookings';
			$result = $wpdb->get_var(
				"SELECT SUM(total_price) FROM $bookings_table WHERE MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE()) AND payment_status = 'paid'"
			);
			return (float) $result;
		}, 300 );
	}

	/**
	 * Get pending payments count
	 *
	 * @return int
	 */
	public function get_pending_payments_count() {
		return (int) $this->get_cached_metric( 'royal_pending_payments', function() {
			global $wpdb;
			$bookings_table = $wpdb->prefix . 'royal_bookings';
			return (int) $wpdb->get_var( "SELECT COUNT(*) FROM $bookings_table WHERE payment_status = 'pending' OR payment_status = 'unpaid'" );
		}, 300 );
	}

	/**
	 * Cached metric helper
	 *
	 * @param string $key
	 * @param callable $callback
	 * @param int $ttl
	 * @return mixed
	 */
	private function get_cached_metric( $key, $callback, $ttl = 300 ) {
		$value = get_transient( $key );
		if ( $value === false ) {
			$value = call_user_func( $callback );
			set_transient( $key, $value, $ttl );
		}
		return $value;
	}

	/**
	 * Format integer numbers for display
	 *
	 * @param int $num
	 * @return string
	 */
	private function format_number( $num ) {
		return number_format_i18n( (int) $num );
	}

	/**
	 * Format currency using wc_price if available, otherwise fallback
	 *
	 * @param float $amount
	 * @return string
	 */
	private function format_currency( $amount ) {
		$amount = (float) $amount;
		if ( function_exists( 'wc_price' ) ) {
			return wc_price( $amount );
		}
		return esc_html( number_format_i18n( $amount, 2 ) ) . ' RSD';
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

