<?php
/**
 * Admin Class
 *
 * @package RoyalStorage\Admin
 * @since 1.0.0
 */

namespace RoyalStorage\Admin;

use RoyalStorage\Admin\Dashboard;
use RoyalStorage\Admin\Settings;
use RoyalStorage\Admin\Reports;
use RoyalStorage\Admin\Bookings;
use RoyalStorage\Admin\Customers;
use RoyalStorage\Admin\Notifications;
use RoyalStorage\Admin\UnitLayoutAdmin;
use RoyalStorage\Admin\PaymentSettings;
use RoyalStorage\Admin\StorageUnits;
use RoyalStorage\Admin\ParkingSpaces;

/**
 * Admin class for handling admin functionality
 */
class Admin {

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->init();
	}

	/**
	 * Initialize admin functionality
	 *
	 * @return void
	 */
	public function init() {
		// Load admin components.
		new Dashboard();
		new Settings();
		new Reports();
		new Bookings();
		new Customers();
		new Notifications();
		new UnitLayoutAdmin();
		new PaymentSettings();
		new StorageUnits();
		new ParkingSpaces();

		// Register hooks.
		add_action( 'admin_init', array( $this, 'register_admin_hooks' ) );
		add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
	}

	/**
	 * Register admin hooks
	 *
	 * @return void
	 */
	public function register_admin_hooks() {
		// Add admin styles and scripts.
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );

		// Add admin columns.
		add_filter( 'manage_rs_storage_unit_posts_columns', array( $this, 'add_storage_unit_columns' ) );
		add_filter( 'manage_rs_parking_space_posts_columns', array( $this, 'add_parking_space_columns' ) );
		add_filter( 'manage_rs_booking_posts_columns', array( $this, 'add_booking_columns' ) );
		add_filter( 'manage_rs_invoice_posts_columns', array( $this, 'add_invoice_columns' ) );
	}

	/**
	 * Add admin menu
	 *
	 * @return void
	 */
	public function add_admin_menu() {
		add_menu_page(
			__( 'Royal Storage', 'royal-storage' ),
			__( 'Royal Storage', 'royal-storage' ),
			'manage_options',
			'royal-storage',
			array( $this, 'render_dashboard_page' ),
			'dashicons-store',
			30
		);

		add_submenu_page(
			'royal-storage',
			__( 'Dashboard', 'royal-storage' ),
			__( 'Dashboard', 'royal-storage' ),
			'manage_options',
			'royal-storage',
			array( $this, 'render_dashboard_page' )
		);

		add_submenu_page(
			'royal-storage',
			__( 'Bookings', 'royal-storage' ),
			__( 'Bookings', 'royal-storage' ),
			'manage_options',
			'royal-storage-bookings',
			array( $this, 'render_bookings_page' )
		);

		add_submenu_page(
			'royal-storage',
			__( 'Reports', 'royal-storage' ),
			__( 'Reports', 'royal-storage' ),
			'manage_options',
			'royal-storage-reports',
			array( $this, 'render_reports_page' )
		);

		add_submenu_page(
			'royal-storage',
			__( 'Customers', 'royal-storage' ),
			__( 'Customers', 'royal-storage' ),
			'manage_options',
			'royal-storage-customers',
			array( $this, 'render_customers_page' )
		);

		add_submenu_page(
			'royal-storage',
			__( 'Storage Units', 'royal-storage' ),
			__( 'Storage Units', 'royal-storage' ),
			'manage_options',
			'royal-storage-units',
			array( $this, 'render_storage_units_page' )
		);

		add_submenu_page(
			'royal-storage',
			__( 'Parking Spaces', 'royal-storage' ),
			__( 'Parking Spaces', 'royal-storage' ),
			'manage_options',
			'royal-storage-parking',
			array( $this, 'render_parking_spaces_page' )
		);

		add_submenu_page(
			'royal-storage',
			__( 'Unit Layouts', 'royal-storage' ),
			__( 'Unit Layouts', 'royal-storage' ),
			'manage_options',
			'royal-storage-layouts',
			array( $this, 'render_unit_layouts_page' )
		);

		add_submenu_page(
			'royal-storage',
			__( 'Payment Settings', 'royal-storage' ),
			__( 'Payment Settings', 'royal-storage' ),
			'manage_options',
			'royal-storage-payment-settings',
			array( $this, 'render_payment_settings_page' )
		);

		add_submenu_page(
			'royal-storage',
			__( 'Settings', 'royal-storage' ),
			__( 'Settings', 'royal-storage' ),
			'manage_options',
			'royal-storage-settings',
			array( $this, 'render_settings_page' )
		);
	}

	/**
	 * Render dashboard page
	 *
	 * @return void
	 */
	public function render_dashboard_page() {
		$dashboard = new Dashboard();
		
		// Add sample data if no data exists
		$dashboard->add_sample_data();
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Royal Storage Dashboard', 'royal-storage' ); ?></h1>
			
			<?php
			// Handle sample data request
			if ( isset( $_GET['add_sample_data'] ) && wp_verify_nonce( $_GET['_wpnonce'], 'add_sample_data' ) ) {
				$dashboard->add_sample_data();
				echo '<div class="notice notice-success"><p>' . esc_html__( 'Sample data added successfully!', 'royal-storage' ) . '</p></div>';
			}
			?>
			
			<div class="royal-storage-dashboard-actions">
				<a href="<?php echo esc_url( wp_nonce_url( add_query_arg( 'add_sample_data', '1' ), 'add_sample_data' ) ); ?>" class="button button-primary">
					<?php esc_html_e( 'Add Sample Data', 'royal-storage' ); ?>
				</a>
			</div>
			
			<div class="royal-storage-dashboard-container">
				<?php $this->render_dashboard_metrics( $dashboard ); ?>
				<?php $this->render_recent_bookings( $dashboard ); ?>
				<?php $this->render_upcoming_expiries( $dashboard ); ?>
			</div>
		</div>
		<?php
	}

	/**
	 * Render dashboard metrics
	 *
	 * @param Dashboard $dashboard Dashboard instance.
	 * @return void
	 */
	private function render_dashboard_metrics( $dashboard ) {
		?>
		<div class="royal-storage-metrics">
			<div class="metrics-row">
				<div class="metric-card">
					<div class="metric-icon">📦</div>
					<div class="metric-content">
						<h3><?php esc_html_e( 'Total Units', 'royal-storage' ); ?></h3>
						<div class="metric-number"><?php echo esc_html( $dashboard->get_total_units() ); ?></div>
					</div>
				</div>
				<div class="metric-card">
					<div class="metric-icon">✅</div>
					<div class="metric-content">
						<h3><?php esc_html_e( 'Occupied Units', 'royal-storage' ); ?></h3>
						<div class="metric-number"><?php echo esc_html( $dashboard->get_occupied_units() ); ?></div>
					</div>
				</div>
				<div class="metric-card">
					<div class="metric-icon">🆓</div>
					<div class="metric-content">
						<h3><?php esc_html_e( 'Available Units', 'royal-storage' ); ?></h3>
						<div class="metric-number"><?php echo esc_html( $dashboard->get_available_units() ); ?></div>
					</div>
				</div>
				<div class="metric-card">
					<div class="metric-icon">📊</div>
					<div class="metric-content">
						<h3><?php esc_html_e( 'Occupancy Rate', 'royal-storage' ); ?></h3>
						<div class="metric-number"><?php echo esc_html( round( $dashboard->get_occupancy_rate(), 1 ) ); ?>%</div>
					</div>
				</div>
			</div>
			<div class="metrics-row">
				<div class="metric-card">
					<div class="metric-icon">⚠️</div>
					<div class="metric-content">
						<h3><?php esc_html_e( 'Overdue Bookings', 'royal-storage' ); ?></h3>
						<div class="metric-number alert"><?php echo esc_html( $dashboard->get_overdue_bookings_count() ); ?></div>
					</div>
				</div>
				<div class="metric-card">
					<div class="metric-icon">⏰</div>
					<div class="metric-content">
						<h3><?php esc_html_e( 'Expiring Soon', 'royal-storage' ); ?></h3>
						<div class="metric-number warning"><?php echo esc_html( count( $dashboard->get_upcoming_expiries( 7 ) ) ); ?></div>
					</div>
				</div>
				<div class="metric-card">
					<div class="metric-icon">💰</div>
					<div class="metric-content">
						<h3><?php esc_html_e( 'Monthly Revenue', 'royal-storage' ); ?></h3>
						<div class="metric-number"><?php echo esc_html( $dashboard->get_monthly_revenue() ); ?> RSD</div>
					</div>
				</div>
				<div class="metric-card">
					<div class="metric-icon">💳</div>
					<div class="metric-content">
						<h3><?php esc_html_e( 'Pending Payments', 'royal-storage' ); ?></h3>
						<div class="metric-number"><?php echo esc_html( $dashboard->get_pending_payments_count() ); ?></div>
					</div>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Render recent bookings
	 *
	 * @param Dashboard $dashboard Dashboard instance.
	 * @return void
	 */
	private function render_recent_bookings( $dashboard ) {
		global $wpdb;
		$bookings_table = $wpdb->prefix . 'royal_bookings';
		$recent_bookings = $wpdb->get_results(
			"SELECT * FROM $bookings_table ORDER BY created_at DESC LIMIT 5"
		);
		?>
		<div class="royal-storage-widget">
			<h2><?php esc_html_e( 'Recent Bookings', 'royal-storage' ); ?></h2>
			<?php if ( ! empty( $recent_bookings ) ) : ?>
				<table class="wp-list-table widefat fixed striped">
					<thead>
						<tr>
							<th><?php esc_html_e( 'ID', 'royal-storage' ); ?></th>
							<th><?php esc_html_e( 'Customer', 'royal-storage' ); ?></th>
							<th><?php esc_html_e( 'Unit', 'royal-storage' ); ?></th>
							<th><?php esc_html_e( 'Dates', 'royal-storage' ); ?></th>
							<th><?php esc_html_e( 'Status', 'royal-storage' ); ?></th>
							<th><?php esc_html_e( 'Amount', 'royal-storage' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( $recent_bookings as $booking ) : ?>
							<tr>
								<td><?php echo esc_html( $booking->id ); ?></td>
								<td><?php echo esc_html( get_userdata( $booking->customer_id )->display_name ?? 'N/A' ); ?></td>
								<td><?php echo esc_html( $booking->unit_id ? 'Unit ' . $booking->unit_id : 'Space ' . $booking->space_id ); ?></td>
								<td><?php echo esc_html( $booking->start_date . ' - ' . $booking->end_date ); ?></td>
								<td>
									<span class="status-<?php echo esc_attr( $booking->status ); ?>">
										<?php echo esc_html( ucfirst( $booking->status ) ); ?>
									</span>
								</td>
								<td><?php echo esc_html( number_format( $booking->total_price, 2 ) ); ?> RSD</td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			<?php else : ?>
				<p><?php esc_html_e( 'No bookings found.', 'royal-storage' ); ?></p>
			<?php endif; ?>
		</div>
		<?php
	}

	/**
	 * Render upcoming expiries
	 *
	 * @param Dashboard $dashboard Dashboard instance.
	 * @return void
	 */
	private function render_upcoming_expiries( $dashboard ) {
		$upcoming_expiries = $dashboard->get_upcoming_expiries( 7 );
		?>
		<div class="royal-storage-widget">
			<h2><?php esc_html_e( 'Expiring Soon (Next 7 Days)', 'royal-storage' ); ?></h2>
			<?php if ( ! empty( $upcoming_expiries ) ) : ?>
				<table class="wp-list-table widefat fixed striped">
					<thead>
						<tr>
							<th><?php esc_html_e( 'Booking ID', 'royal-storage' ); ?></th>
							<th><?php esc_html_e( 'Customer', 'royal-storage' ); ?></th>
							<th><?php esc_html_e( 'Unit', 'royal-storage' ); ?></th>
							<th><?php esc_html_e( 'Expiry Date', 'royal-storage' ); ?></th>
							<th><?php esc_html_e( 'Days Left', 'royal-storage' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( $upcoming_expiries as $booking ) : ?>
							<?php
							$days_left = ( strtotime( $booking->end_date ) - time() ) / ( 60 * 60 * 24 );
							$days_left = max( 0, floor( $days_left ) );
							?>
							<tr>
								<td><?php echo esc_html( $booking->id ); ?></td>
								<td><?php echo esc_html( get_userdata( $booking->customer_id )->display_name ?? 'N/A' ); ?></td>
								<td><?php echo esc_html( $booking->unit_id ? 'Unit ' . $booking->unit_id : 'Space ' . $booking->space_id ); ?></td>
								<td><?php echo esc_html( $booking->end_date ); ?></td>
								<td>
									<span class="days-left <?php echo $days_left <= 2 ? 'urgent' : ( $days_left <= 4 ? 'warning' : '' ); ?>">
										<?php echo esc_html( $days_left ); ?> days
									</span>
								</td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			<?php else : ?>
				<p><?php esc_html_e( 'No bookings expiring in the next 7 days.', 'royal-storage' ); ?></p>
			<?php endif; ?>
		</div>
		<?php
	}

	/**
	 * Render bookings page
	 *
	 * @return void
	 */
	public function render_bookings_page() {
		$bookings = new Bookings();
		
		// Get pagination parameters
		$page = isset( $_GET['paged'] ) ? max( 1, intval( $_GET['paged'] ) ) : 1;
		$per_page = 20;
		$offset = ( $page - 1 ) * $per_page;
		
		// Get search and filter parameters
		$search = isset( $_GET['s'] ) ? sanitize_text_field( $_GET['s'] ) : '';
		$status_filter = isset( $_GET['status'] ) ? sanitize_text_field( $_GET['status'] ) : '';
		
		// Get bookings data
		$bookings_data = $this->get_bookings_with_details( $bookings, $per_page, $offset, $search, $status_filter );
		$total_bookings = $bookings->get_bookings_count();
		$total_pages = ceil( $total_bookings / $per_page );
		
		// Handle messages
		$message = isset( $_GET['message'] ) ? sanitize_text_field( $_GET['message'] ) : '';
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Bookings', 'royal-storage' ); ?></h1>
			<p><?php esc_html_e( 'Manage all bookings and reservations.', 'royal-storage' ); ?></p>

			<?php if ( $message ) : ?>
				<div class="notice notice-<?php echo esc_attr( $message === 'error' ? 'error' : 'success' ); ?>">
					<p>
						<?php
						switch ( $message ) {
							case 'created':
								esc_html_e( 'Booking created successfully!', 'royal-storage' );
								break;
							case 'cancelled':
								esc_html_e( 'Booking cancelled successfully!', 'royal-storage' );
								break;
							case 'error':
								esc_html_e( 'An error occurred. Please try again.', 'royal-storage' );
								break;
						}
						?>
					</p>
				</div>
			<?php endif; ?>

			<!-- Search and Filters -->
			<div class="royal-storage-bookings-filters">
				<form method="get" action="">
					<input type="hidden" name="page" value="royal-storage-bookings" />
					<div class="search-box">
						<label for="booking-search"><?php esc_html_e( 'Search Bookings:', 'royal-storage' ); ?></label>
						<input type="search" id="booking-search" name="s" value="<?php echo esc_attr( $search ); ?>" placeholder="<?php esc_attr_e( 'Search by customer, unit, or booking ID...', 'royal-storage' ); ?>" />
					</div>
					<div class="filter-group">
						<label for="status-filter"><?php esc_html_e( 'Status:', 'royal-storage' ); ?></label>
						<select id="status-filter" name="status">
							<option value=""><?php esc_html_e( 'All Statuses', 'royal-storage' ); ?></option>
							<option value="pending" <?php selected( $status_filter, 'pending' ); ?>><?php esc_html_e( 'Pending', 'royal-storage' ); ?></option>
							<option value="confirmed" <?php selected( $status_filter, 'confirmed' ); ?>><?php esc_html_e( 'Confirmed', 'royal-storage' ); ?></option>
							<option value="active" <?php selected( $status_filter, 'active' ); ?>><?php esc_html_e( 'Active', 'royal-storage' ); ?></option>
							<option value="cancelled" <?php selected( $status_filter, 'cancelled' ); ?>><?php esc_html_e( 'Cancelled', 'royal-storage' ); ?></option>
							<option value="expired" <?php selected( $status_filter, 'expired' ); ?>><?php esc_html_e( 'Expired', 'royal-storage' ); ?></option>
						</select>
					</div>
					<div class="filter-group">
						<button type="submit" class="button"><?php esc_html_e( 'Filter', 'royal-storage' ); ?></button>
						<?php if ( $search || $status_filter ) : ?>
							<a href="<?php echo esc_url( admin_url( 'admin.php?page=royal-storage-bookings' ) ); ?>" class="button"><?php esc_html_e( 'Clear', 'royal-storage' ); ?></a>
						<?php endif; ?>
					</div>
				</form>
			</div>

			<!-- Bookings Table -->
			<div class="royal-storage-bookings-table">
				<?php $this->render_bookings_table( $bookings_data, $search, $status_filter ); ?>
			</div>

			<!-- Pagination -->
			<?php if ( $total_pages > 1 ) : ?>
				<div class="royal-storage-bookings-pagination">
					<?php $this->render_bookings_pagination( $page, $total_pages, $search, $status_filter ); ?>
				</div>
			<?php endif; ?>
		</div>
		<?php
	}

	/**
	 * Render reports page
	 *
	 * @return void
	 */
	public function render_reports_page() {
		$reports = new Reports();
		
		// Get date range from URL parameters or use default
		$start_date = isset( $_GET['start_date'] ) ? sanitize_text_field( $_GET['start_date'] ) : date( 'Y-m-01' );
		$end_date = isset( $_GET['end_date'] ) ? sanitize_text_field( $_GET['end_date'] ) : date( 'Y-m-d' );
		
		// Get report data
		$revenue_data = $reports->get_revenue_report( $start_date, $end_date );
		$occupancy_data = $reports->get_occupancy_report( $start_date, $end_date );
		$payment_data = $reports->get_payment_report( $start_date, $end_date );
		$total_revenue = $reports->get_total_revenue( $start_date, $end_date );
		
		// Calculate additional metrics
		$total_bookings = count( $revenue_data );
		$avg_revenue_per_booking = $total_bookings > 0 ? $total_revenue / $total_bookings : 0;
		$occupancy_rate = $occupancy_data['total_units'] > 0 ? ( count( $occupancy_data['occupied'] ) / $occupancy_data['total_units'] ) * 100 : 0;
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Royal Storage Reports', 'royal-storage' ); ?></h1>
			<p><?php esc_html_e( 'Comprehensive analytics and reporting for your storage business.', 'royal-storage' ); ?></p>

			<!-- Date Range Filter -->
			<div class="royal-storage-reports-filters">
				<form method="get" action="">
					<input type="hidden" name="page" value="royal-storage-reports" />
					<div class="filter-group">
						<label for="start_date"><?php esc_html_e( 'Start Date:', 'royal-storage' ); ?></label>
						<input type="date" id="start_date" name="start_date" value="<?php echo esc_attr( $start_date ); ?>" />
					</div>
					<div class="filter-group">
						<label for="end_date"><?php esc_html_e( 'End Date:', 'royal-storage' ); ?></label>
						<input type="date" id="end_date" name="end_date" value="<?php echo esc_attr( $end_date ); ?>" />
					</div>
					<div class="filter-group">
						<button type="submit" class="button button-primary"><?php esc_html_e( 'Update Report', 'royal-storage' ); ?></button>
					</div>
				</form>
			</div>

			<!-- Export Options -->
			<div class="royal-storage-reports-export">
				<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
					<input type="hidden" name="action" value="royal_storage_export_csv" />
					<input type="hidden" name="nonce" value="<?php echo esc_attr( wp_create_nonce( 'royal_storage_export_csv' ) ); ?>" />
					<input type="hidden" name="start_date" value="<?php echo esc_attr( $start_date ); ?>" />
					<input type="hidden" name="end_date" value="<?php echo esc_attr( $end_date ); ?>" />
					<button type="submit" class="button"><?php esc_html_e( 'Export CSV', 'royal-storage' ); ?></button>
				</form>
			</div>

			<!-- Key Metrics -->
			<div class="royal-storage-reports-metrics">
				<?php $this->render_reports_metrics( $total_revenue, $total_bookings, $avg_revenue_per_booking, $occupancy_rate ); ?>
			</div>

			<!-- Revenue Chart -->
			<div class="royal-storage-reports-section">
				<?php $this->render_revenue_chart( $revenue_data ); ?>
			</div>

			<!-- Occupancy Report -->
			<div class="royal-storage-reports-section">
				<?php $this->render_occupancy_report( $occupancy_data ); ?>
			</div>

			<!-- Payment Status Report -->
			<div class="royal-storage-reports-section">
				<?php $this->render_payment_status_report( $payment_data ); ?>
			</div>

			<!-- Recent Bookings -->
			<div class="royal-storage-reports-section">
				<?php $this->render_recent_bookings_report(); ?>
			</div>
		</div>
		<?php
	}

	/**
	 * Render reports metrics
	 *
	 * @param float $total_revenue Total revenue.
	 * @param int   $total_bookings Total bookings.
	 * @param float $avg_revenue_per_booking Average revenue per booking.
	 * @param float $occupancy_rate Occupancy rate.
	 * @return void
	 */
	private function render_reports_metrics( $total_revenue, $total_bookings, $avg_revenue_per_booking, $occupancy_rate ) {
		?>
		<div class="royal-storage-metrics">
			<div class="metrics-row">
				<div class="metric-card">
					<div class="metric-icon">💰</div>
					<div class="metric-content">
						<h3><?php esc_html_e( 'Total Revenue', 'royal-storage' ); ?></h3>
						<div class="metric-number"><?php echo esc_html( number_format( $total_revenue, 2 ) ); ?> RSD</div>
					</div>
				</div>
				<div class="metric-card">
					<div class="metric-icon">📊</div>
					<div class="metric-content">
						<h3><?php esc_html_e( 'Total Bookings', 'royal-storage' ); ?></h3>
						<div class="metric-number"><?php echo esc_html( $total_bookings ); ?></div>
					</div>
				</div>
				<div class="metric-card">
					<div class="metric-icon">📈</div>
					<div class="metric-content">
						<h3><?php esc_html_e( 'Avg Revenue/Booking', 'royal-storage' ); ?></h3>
						<div class="metric-number"><?php echo esc_html( number_format( $avg_revenue_per_booking, 2 ) ); ?> RSD</div>
					</div>
				</div>
				<div class="metric-card">
					<div class="metric-icon">🏠</div>
					<div class="metric-content">
						<h3><?php esc_html_e( 'Occupancy Rate', 'royal-storage' ); ?></h3>
						<div class="metric-number"><?php echo esc_html( number_format( $occupancy_rate, 1 ) ); ?>%</div>
					</div>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Render revenue chart
	 *
	 * @param array $revenue_data Revenue data.
	 * @return void
	 */
	private function render_revenue_chart( $revenue_data ) {
		?>
		<div class="royal-storage-widget">
			<h2><?php esc_html_e( 'Revenue Trend', 'royal-storage' ); ?></h2>
			<div class="revenue-chart-container">
				<?php if ( ! empty( $revenue_data ) ) : ?>
					<div class="chart-placeholder">
						<table class="wp-list-table widefat fixed striped">
							<thead>
								<tr>
									<th><?php esc_html_e( 'Date', 'royal-storage' ); ?></th>
									<th><?php esc_html_e( 'Revenue', 'royal-storage' ); ?></th>
									<th><?php esc_html_e( 'Bookings', 'royal-storage' ); ?></th>
									<th><?php esc_html_e( 'Avg/Booking', 'royal-storage' ); ?></th>
								</tr>
							</thead>
							<tbody>
								<?php foreach ( $revenue_data as $day ) : ?>
									<tr>
										<td><?php echo esc_html( date( 'M j, Y', strtotime( $day->date ) ) ); ?></td>
										<td><?php echo esc_html( number_format( $day->revenue, 2 ) ); ?> RSD</td>
										<td><?php echo esc_html( $day->bookings ); ?></td>
										<td><?php echo esc_html( number_format( $day->revenue / $day->bookings, 2 ) ); ?> RSD</td>
									</tr>
								<?php endforeach; ?>
							</tbody>
						</table>
					</div>
				<?php else : ?>
					<p><?php esc_html_e( 'No revenue data available for the selected period.', 'royal-storage' ); ?></p>
				<?php endif; ?>
			</div>
		</div>
		<?php
	}

	/**
	 * Render occupancy report
	 *
	 * @param array $occupancy_data Occupancy data.
	 * @return void
	 */
	private function render_occupancy_report( $occupancy_data ) {
		?>
		<div class="royal-storage-widget">
			<h2><?php esc_html_e( 'Occupancy Report', 'royal-storage' ); ?></h2>
			<div class="occupancy-stats">
				<div class="occupancy-metric">
					<span class="label"><?php esc_html_e( 'Total Units:', 'royal-storage' ); ?></span>
					<span class="value"><?php echo esc_html( $occupancy_data['total_units'] ); ?></span>
				</div>
				<div class="occupancy-metric">
					<span class="label"><?php esc_html_e( 'Currently Occupied:', 'royal-storage' ); ?></span>
					<span class="value"><?php echo esc_html( count( $occupancy_data['occupied'] ) ); ?></span>
				</div>
				<div class="occupancy-metric">
					<span class="label"><?php esc_html_e( 'Available Units:', 'royal-storage' ); ?></span>
					<span class="value"><?php echo esc_html( $occupancy_data['total_units'] - count( $occupancy_data['occupied'] ) ); ?></span>
				</div>
			</div>
			<?php if ( ! empty( $occupancy_data['occupied'] ) ) : ?>
				<div class="occupancy-details">
					<h3><?php esc_html_e( 'Daily Occupancy', 'royal-storage' ); ?></h3>
					<table class="wp-list-table widefat fixed striped">
						<thead>
							<tr>
								<th><?php esc_html_e( 'Date', 'royal-storage' ); ?></th>
								<th><?php esc_html_e( 'Occupied Units', 'royal-storage' ); ?></th>
								<th><?php esc_html_e( 'Occupancy Rate', 'royal-storage' ); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ( $occupancy_data['occupied'] as $day ) : ?>
								<tr>
									<td><?php echo esc_html( date( 'M j, Y', strtotime( $day->date ) ) ); ?></td>
									<td><?php echo esc_html( $day->occupied ); ?></td>
									<td><?php echo esc_html( number_format( ( $day->occupied / $occupancy_data['total_units'] ) * 100, 1 ) ); ?>%</td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				</div>
			<?php endif; ?>
		</div>
		<?php
	}

	/**
	 * Render payment status report
	 *
	 * @param array $payment_data Payment data.
	 * @return void
	 */
	private function render_payment_status_report( $payment_data ) {
		?>
		<div class="royal-storage-widget">
			<h2><?php esc_html_e( 'Payment Status Report', 'royal-storage' ); ?></h2>
			<?php if ( ! empty( $payment_data ) ) : ?>
				<table class="wp-list-table widefat fixed striped">
					<thead>
						<tr>
							<th><?php esc_html_e( 'Payment Status', 'royal-storage' ); ?></th>
							<th><?php esc_html_e( 'Count', 'royal-storage' ); ?></th>
							<th><?php esc_html_e( 'Total Amount', 'royal-storage' ); ?></th>
							<th><?php esc_html_e( 'Percentage', 'royal-storage' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php 
						$total_count = array_sum( wp_list_pluck( $payment_data, 'count' ) );
						$total_amount = array_sum( wp_list_pluck( $payment_data, 'total' ) );
						foreach ( $payment_data as $status ) : 
							$percentage = $total_count > 0 ? ( $status->count / $total_count ) * 100 : 0;
						?>
							<tr>
								<td>
									<span class="status-badge status-<?php echo esc_attr( $status->payment_status ); ?>">
										<?php echo esc_html( ucfirst( $status->payment_status ) ); ?>
									</span>
								</td>
								<td><?php echo esc_html( $status->count ); ?></td>
								<td><?php echo esc_html( number_format( $status->total, 2 ) ); ?> RSD</td>
								<td><?php echo esc_html( number_format( $percentage, 1 ) ); ?>%</td>
							</tr>
						<?php endforeach; ?>
					</tbody>
					<tfoot>
						<tr>
							<th><?php esc_html_e( 'Total', 'royal-storage' ); ?></th>
							<th><?php echo esc_html( $total_count ); ?></th>
							<th><?php echo esc_html( number_format( $total_amount, 2 ) ); ?> RSD</th>
							<th>100%</th>
						</tr>
					</tfoot>
				</table>
			<?php else : ?>
				<p><?php esc_html_e( 'No payment data available for the selected period.', 'royal-storage' ); ?></p>
			<?php endif; ?>
		</div>
		<?php
	}

	/**
	 * Render recent bookings report
	 *
	 * @return void
	 */
	private function render_recent_bookings_report() {
		global $wpdb;
		$bookings_table = $wpdb->prefix . 'royal_bookings';
		$recent_bookings = $wpdb->get_results(
			"SELECT * FROM $bookings_table ORDER BY created_at DESC LIMIT 10"
		);
		?>
		<div class="royal-storage-widget">
			<h2><?php esc_html_e( 'Recent Bookings', 'royal-storage' ); ?></h2>
			<?php if ( ! empty( $recent_bookings ) ) : ?>
				<table class="wp-list-table widefat fixed striped">
					<thead>
						<tr>
							<th><?php esc_html_e( 'ID', 'royal-storage' ); ?></th>
							<th><?php esc_html_e( 'Customer', 'royal-storage' ); ?></th>
							<th><?php esc_html_e( 'Unit', 'royal-storage' ); ?></th>
							<th><?php esc_html_e( 'Start Date', 'royal-storage' ); ?></th>
							<th><?php esc_html_e( 'End Date', 'royal-storage' ); ?></th>
							<th><?php esc_html_e( 'Amount', 'royal-storage' ); ?></th>
							<th><?php esc_html_e( 'Status', 'royal-storage' ); ?></th>
							<th><?php esc_html_e( 'Payment', 'royal-storage' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( $recent_bookings as $booking ) : ?>
							<tr>
								<td><?php echo esc_html( $booking->id ); ?></td>
								<td>
									<?php 
									$customer = get_user_by( 'id', $booking->customer_id );
									echo $customer ? esc_html( $customer->display_name ) : esc_html( 'Unknown' );
									?>
								</td>
								<td>
									<?php 
									if ( $booking->unit_type === 'storage' ) {
										echo esc_html( 'Storage #' . $booking->unit_id );
									} else {
										echo esc_html( 'Parking #' . $booking->unit_id );
									}
									?>
								</td>
								<td><?php echo esc_html( date( 'M j, Y', strtotime( $booking->start_date ) ) ); ?></td>
								<td><?php echo esc_html( date( 'M j, Y', strtotime( $booking->end_date ) ) ); ?></td>
								<td><?php echo esc_html( number_format( $booking->total_price, 2 ) ); ?> RSD</td>
								<td>
									<span class="status-badge status-<?php echo esc_attr( $booking->status ); ?>">
										<?php echo esc_html( ucfirst( $booking->status ) ); ?>
									</span>
								</td>
								<td>
									<span class="status-badge status-<?php echo esc_attr( $booking->payment_status ); ?>">
										<?php echo esc_html( ucfirst( $booking->payment_status ) ); ?>
									</span>
								</td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			<?php else : ?>
				<p><?php esc_html_e( 'No recent bookings found.', 'royal-storage' ); ?></p>
			<?php endif; ?>
		</div>
		<?php
	}

	/**
	 * Render customers page
	 *
	 * @return void
	 */
	public function render_customers_page() {
		$customers = new Customers();
		
		// Get pagination parameters
		$page = isset( $_GET['paged'] ) ? max( 1, intval( $_GET['paged'] ) ) : 1;
		$per_page = 20;
		$offset = ( $page - 1 ) * $per_page;
		
		// Get search parameter
		$search = isset( $_GET['s'] ) ? sanitize_text_field( $_GET['s'] ) : '';
		
		// Get customers data
		$customers_data = $this->get_customers_with_details( $customers, $per_page, $offset, $search );
		$total_customers = $customers->get_customers_count();
		$total_pages = ceil( $total_customers / $per_page );
		
		// Calculate summary statistics
		$summary_stats = $this->get_customers_summary_stats( $customers );
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Royal Storage Customers', 'royal-storage' ); ?></h1>
			<p><?php esc_html_e( 'Manage customer information, bookings, and payment history.', 'royal-storage' ); ?></p>

			<!-- Summary Statistics -->
			<div class="royal-storage-customers-summary">
				<?php $this->render_customers_summary( $summary_stats ); ?>
			</div>

			<!-- Search and Filters -->
			<div class="royal-storage-customers-filters">
				<form method="get" action="">
					<input type="hidden" name="page" value="royal-storage-customers" />
					<div class="search-box">
						<label for="customer-search"><?php esc_html_e( 'Search Customers:', 'royal-storage' ); ?></label>
						<input type="search" id="customer-search" name="s" value="<?php echo esc_attr( $search ); ?>" placeholder="<?php esc_attr_e( 'Search by name, email, or phone...', 'royal-storage' ); ?>" />
						<button type="submit" class="button"><?php esc_html_e( 'Search', 'royal-storage' ); ?></button>
						<?php if ( $search ) : ?>
							<a href="<?php echo esc_url( admin_url( 'admin.php?page=royal-storage-customers' ) ); ?>" class="button"><?php esc_html_e( 'Clear', 'royal-storage' ); ?></a>
						<?php endif; ?>
					</div>
				</form>
			</div>

			<!-- Export Options -->
			<div class="royal-storage-customers-export">
				<button type="button" class="button" onclick="exportCustomersCSV()"><?php esc_html_e( 'Export CSV', 'royal-storage' ); ?></button>
			</div>

			<!-- Customers Table -->
			<div class="royal-storage-customers-table">
				<?php $this->render_customers_table( $customers_data, $search ); ?>
			</div>

			<!-- Pagination -->
			<?php if ( $total_pages > 1 ) : ?>
				<div class="royal-storage-customers-pagination">
					<?php $this->render_customers_pagination( $page, $total_pages, $search ); ?>
				</div>
			<?php endif; ?>
		</div>

		<script>
		function exportCustomersCSV() {
			var form = document.createElement('form');
			form.method = 'POST';
			form.action = '<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>';
			
			var action = document.createElement('input');
			action.type = 'hidden';
			action.name = 'action';
			action.value = 'royal_storage_export_customers_csv';
			form.appendChild(action);
			
			var nonce = document.createElement('input');
			nonce.type = 'hidden';
			nonce.name = 'nonce';
			nonce.value = '<?php echo esc_js( wp_create_nonce( 'royal_storage_export_customers_csv' ) ); ?>';
			form.appendChild(nonce);
			
			document.body.appendChild(form);
			form.submit();
			document.body.removeChild(form);
		}
		</script>
		<?php
	}

	/**
	 * Get customers with details
	 *
	 * @param Customers $customers Customers instance.
	 * @param int       $per_page Per page limit.
	 * @param int       $offset Offset.
	 * @param string    $search Search term.
	 * @return array
	 */
	private function get_customers_with_details( $customers, $per_page, $offset, $search ) {
		global $wpdb;
		
		$bookings_table = $wpdb->prefix . 'royal_bookings';
		$users_table = $wpdb->users;
		$usermeta_table = $wpdb->usermeta;
		
		$query = "
			SELECT DISTINCT b.customer_id, u.display_name, u.user_email, u.user_registered,
				(SELECT meta_value FROM $usermeta_table WHERE user_id = b.customer_id AND meta_key = 'phone' LIMIT 1) as phone
			FROM $bookings_table b
			INNER JOIN $users_table u ON b.customer_id = u.ID
		";
		
		$where_conditions = array();
		$where_values = array();
		
		if ( $search ) {
			$where_conditions[] = "(u.display_name LIKE %s OR u.user_email LIKE %s OR (SELECT meta_value FROM $usermeta_table WHERE user_id = b.customer_id AND meta_key = 'phone' LIMIT 1) LIKE %s)";
			$search_term = '%' . $wpdb->esc_like( $search ) . '%';
			$where_values[] = $search_term;
			$where_values[] = $search_term;
			$where_values[] = $search_term;
		}
		
		if ( ! empty( $where_conditions ) ) {
			$query .= ' WHERE ' . implode( ' AND ', $where_conditions );
		}
		
		$query .= " ORDER BY b.customer_id DESC LIMIT %d OFFSET %d";
		$where_values[] = $per_page;
		$where_values[] = $offset;
		
		$customers_data = $wpdb->get_results( $wpdb->prepare( $query, $where_values ) );
		
		// Add additional details for each customer
		foreach ( $customers_data as $customer ) {
			$customer->total_spent = $customers->get_customer_total_spent( $customer->customer_id );
			$customer->overdue_amount = $customers->get_customer_overdue_amount( $customer->customer_id );
			$customer->active_bookings = $customers->get_customer_active_bookings_count( $customer->customer_id );
			$customer->total_bookings = count( $customers->get_customer_bookings( $customer->customer_id ) );
		}
		
		return $customers_data;
	}

	/**
	 * Get customers summary statistics
	 *
	 * @param Customers $customers Customers instance.
	 * @return array
	 */
	private function get_customers_summary_stats( $customers ) {
		global $wpdb;
		$bookings_table = $wpdb->prefix . 'royal_bookings';
		
		$total_customers = $customers->get_customers_count();
		
		$total_revenue = $wpdb->get_var(
			"SELECT SUM(total_price) FROM $bookings_table WHERE payment_status = 'paid'"
		);
		
		$active_customers = $wpdb->get_var(
			"SELECT COUNT(DISTINCT customer_id) FROM $bookings_table WHERE status IN ('confirmed', 'active')"
		);
		
		$overdue_customers = $wpdb->get_var(
			"SELECT COUNT(DISTINCT customer_id) FROM $bookings_table WHERE payment_status IN ('unpaid', 'pending') AND end_date < CURDATE()"
		);
		
		return array(
			'total_customers' => $total_customers,
			'total_revenue' => $total_revenue ? floatval( $total_revenue ) : 0,
			'active_customers' => $active_customers ? intval( $active_customers ) : 0,
			'overdue_customers' => $overdue_customers ? intval( $overdue_customers ) : 0,
		);
	}

	/**
	 * Render customers summary
	 *
	 * @param array $stats Summary statistics.
	 * @return void
	 */
	private function render_customers_summary( $stats ) {
		?>
		<div class="royal-storage-metrics">
			<div class="metrics-row">
				<div class="metric-card">
					<div class="metric-icon">👥</div>
					<div class="metric-content">
						<h3><?php esc_html_e( 'Total Customers', 'royal-storage' ); ?></h3>
						<div class="metric-number"><?php echo esc_html( $stats['total_customers'] ); ?></div>
					</div>
				</div>
				<div class="metric-card">
					<div class="metric-icon">💰</div>
					<div class="metric-content">
						<h3><?php esc_html_e( 'Total Revenue', 'royal-storage' ); ?></h3>
						<div class="metric-number"><?php echo esc_html( number_format( $stats['total_revenue'], 2 ) ); ?> RSD</div>
					</div>
				</div>
				<div class="metric-card">
					<div class="metric-icon">✅</div>
					<div class="metric-content">
						<h3><?php esc_html_e( 'Active Customers', 'royal-storage' ); ?></h3>
						<div class="metric-number"><?php echo esc_html( $stats['active_customers'] ); ?></div>
					</div>
				</div>
				<div class="metric-card">
					<div class="metric-icon">⚠️</div>
					<div class="metric-content">
						<h3><?php esc_html_e( 'Overdue Customers', 'royal-storage' ); ?></h3>
						<div class="metric-number"><?php echo esc_html( $stats['overdue_customers'] ); ?></div>
					</div>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Render customers table
	 *
	 * @param array  $customers_data Customers data.
	 * @param string $search Search term.
	 * @return void
	 */
	private function render_customers_table( $customers_data, $search ) {
		?>
		<div class="royal-storage-widget">
			<h2>
				<?php esc_html_e( 'Customer List', 'royal-storage' ); ?>
				<?php if ( $search ) : ?>
					<span class="search-results"><?php printf( esc_html__( 'Search results for "%s"', 'royal-storage' ), esc_html( $search ) ); ?></span>
				<?php endif; ?>
			</h2>
			
			<?php if ( ! empty( $customers_data ) ) : ?>
				<table class="wp-list-table widefat fixed striped">
					<thead>
						<tr>
							<th><?php esc_html_e( 'Customer', 'royal-storage' ); ?></th>
							<th><?php esc_html_e( 'Contact', 'royal-storage' ); ?></th>
							<th><?php esc_html_e( 'Total Spent', 'royal-storage' ); ?></th>
							<th><?php esc_html_e( 'Overdue', 'royal-storage' ); ?></th>
							<th><?php esc_html_e( 'Active Bookings', 'royal-storage' ); ?></th>
							<th><?php esc_html_e( 'Total Bookings', 'royal-storage' ); ?></th>
							<th><?php esc_html_e( 'Member Since', 'royal-storage' ); ?></th>
							<th><?php esc_html_e( 'Actions', 'royal-storage' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( $customers_data as $customer ) : ?>
							<tr>
								<td>
									<strong><?php echo esc_html( $customer->display_name ); ?></strong>
									<br>
									<small>ID: <?php echo esc_html( $customer->customer_id ); ?></small>
								</td>
								<td>
									<?php echo esc_html( $customer->user_email ); ?>
									<?php if ( $customer->phone ) : ?>
										<br><small><?php echo esc_html( $customer->phone ); ?></small>
									<?php endif; ?>
								</td>
								<td>
									<strong><?php echo esc_html( number_format( $customer->total_spent, 2 ) ); ?> RSD</strong>
								</td>
								<td>
									<?php if ( $customer->overdue_amount > 0 ) : ?>
										<span class="overdue-amount"><?php echo esc_html( number_format( $customer->overdue_amount, 2 ) ); ?> RSD</span>
									<?php else : ?>
										<span class="no-overdue"><?php esc_html_e( 'None', 'royal-storage' ); ?></span>
									<?php endif; ?>
								</td>
								<td>
									<span class="active-bookings-count"><?php echo esc_html( $customer->active_bookings ); ?></span>
								</td>
								<td>
									<span class="total-bookings-count"><?php echo esc_html( $customer->total_bookings ); ?></span>
								</td>
								<td>
									<?php echo esc_html( date( 'M j, Y', strtotime( $customer->user_registered ) ) ); ?>
								</td>
								<td>
									<div class="customer-actions">
										<a href="<?php echo esc_url( admin_url( 'admin.php?page=royal-storage-customers&action=view&customer_id=' . $customer->customer_id ) ); ?>" class="button button-small"><?php esc_html_e( 'View', 'royal-storage' ); ?></a>
										<a href="<?php echo esc_url( admin_url( 'admin.php?page=royal-storage-customers&action=bookings&customer_id=' . $customer->customer_id ) ); ?>" class="button button-small"><?php esc_html_e( 'Bookings', 'royal-storage' ); ?></a>
										<a href="mailto:<?php echo esc_attr( $customer->user_email ); ?>" class="button button-small"><?php esc_html_e( 'Email', 'royal-storage' ); ?></a>
									</div>
								</td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			<?php else : ?>
				<div class="no-customers">
					<p><?php esc_html_e( 'No customers found.', 'royal-storage' ); ?></p>
					<?php if ( $search ) : ?>
						<p><?php esc_html_e( 'Try adjusting your search criteria.', 'royal-storage' ); ?></p>
					<?php endif; ?>
				</div>
			<?php endif; ?>
		</div>
		<?php
	}

	/**
	 * Render customers pagination
	 *
	 * @param int    $current_page Current page.
	 * @param int    $total_pages Total pages.
	 * @param string $search Search term.
	 * @return void
	 */
	private function render_customers_pagination( $current_page, $total_pages, $search ) {
		$base_url = admin_url( 'admin.php?page=royal-storage-customers' );
		if ( $search ) {
			$base_url .= '&s=' . urlencode( $search );
		}
		?>
		<div class="tablenav">
			<div class="tablenav-pages">
				<span class="displaying-num"><?php printf( esc_html__( '%d items', 'royal-storage' ), $total_pages * 20 ); ?></span>
				
				<?php if ( $current_page > 1 ) : ?>
					<a class="first-page button" href="<?php echo esc_url( $base_url . '&paged=1' ); ?>">«</a>
					<a class="prev-page button" href="<?php echo esc_url( $base_url . '&paged=' . ( $current_page - 1 ) ); ?>">‹</a>
				<?php endif; ?>
				
				<span class="paging-input">
					<label for="current-page-selector" class="screen-reader-text"><?php esc_html_e( 'Current page', 'royal-storage' ); ?></label>
					<input class="current-page" id="current-page-selector" type="text" name="paged" value="<?php echo esc_attr( $current_page ); ?>" size="3" aria-describedby="table-paging" />
					<span class="tablenav-paging-text"> of <span class="total-pages"><?php echo esc_html( $total_pages ); ?></span></span>
				</span>
				
				<?php if ( $current_page < $total_pages ) : ?>
					<a class="next-page button" href="<?php echo esc_url( $base_url . '&paged=' . ( $current_page + 1 ) ); ?>">›</a>
					<a class="last-page button" href="<?php echo esc_url( $base_url . '&paged=' . $total_pages ); ?>">»</a>
				<?php endif; ?>
			</div>
		</div>
		<?php
	}

	/**
	 * Render settings page
	 *
	 * @return void
	 */
	public function render_settings_page() {
		// Handle page recreation
		if ( isset( $_GET['recreate_pages'] ) && wp_verify_nonce( $_GET['_wpnonce'], 'recreate_pages' ) ) {
			\RoyalStorage\Activator::create_pages();
			echo '<div class="notice notice-success"><p>' . esc_html__( 'Pages recreated successfully!', 'royal-storage' ) . '</p></div>';
		}

		// Handle form submission
		if ( isset( $_POST['submit'] ) && wp_verify_nonce( $_POST['royal_storage_settings_nonce'], 'royal_storage_settings' ) ) {
			$this->save_settings();
		}

		$settings = new Settings();
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Royal Storage Settings', 'royal-storage' ); ?></h1>
			<p><?php esc_html_e( 'Configure your Royal Storage plugin settings and preferences.', 'royal-storage' ); ?></p>

			<form method="post" action="">
				<?php wp_nonce_field( 'royal_storage_settings', 'royal_storage_settings_nonce' ); ?>

				<div class="royal-storage-settings-container">
					<?php $this->render_page_settings(); ?>
					<?php $this->render_business_settings( $settings ); ?>
					<?php $this->render_pricing_settings( $settings ); ?>
					<?php $this->render_guest_checkout_settings( $settings ); ?>
					<?php $this->render_email_settings( $settings ); ?>
					<?php $this->render_payment_settings( $settings ); ?>
					<?php $this->render_notification_settings( $settings ); ?>
					<?php $this->render_advanced_settings( $settings ); ?>
				</div>

				<?php submit_button( __( 'Save Settings', 'royal-storage' ) ); ?>
			</form>
		</div>
		<?php
	}

	/**
	 * Save settings
	 *
	 * @return void
	 */
	private function save_settings() {
		$settings = new Settings();

		// Business settings
		$settings->update_setting( 'royal_storage_business_name', sanitize_text_field( $_POST['royal_storage_business_name'] ?? '' ) );
		$settings->update_setting( 'royal_storage_business_phone', sanitize_text_field( $_POST['royal_storage_business_phone'] ?? '' ) );
		$settings->update_setting( 'royal_storage_business_email', sanitize_email( $_POST['royal_storage_business_email'] ?? '' ) );
		$settings->update_setting( 'royal_storage_business_address', sanitize_textarea_field( $_POST['royal_storage_business_address'] ?? '' ) );

		// Pricing settings
		$settings->update_setting( 'royal_storage_currency', sanitize_text_field( $_POST['royal_storage_currency'] ?? 'RSD' ) );
		$settings->update_setting( 'royal_storage_vat_rate', floatval( $_POST['royal_storage_vat_rate'] ?? 20 ) );
		$settings->update_setting( 'royal_storage_daily_rate', floatval( $_POST['royal_storage_daily_rate'] ?? 1000 ) );
		$settings->update_setting( 'royal_storage_weekly_rate', floatval( $_POST['royal_storage_weekly_rate'] ?? 6000 ) );
		$settings->update_setting( 'royal_storage_monthly_rate', floatval( $_POST['royal_storage_monthly_rate'] ?? 20000 ) );

		// Email settings
		$settings->update_setting( 'royal_storage_smtp_host', sanitize_text_field( $_POST['royal_storage_smtp_host'] ?? '' ) );
		$settings->update_setting( 'royal_storage_smtp_port', intval( $_POST['royal_storage_smtp_port'] ?? 587 ) );
		$settings->update_setting( 'royal_storage_smtp_username', sanitize_text_field( $_POST['royal_storage_smtp_username'] ?? '' ) );
		$settings->update_setting( 'royal_storage_smtp_password', sanitize_text_field( $_POST['royal_storage_smtp_password'] ?? '' ) );
		$settings->update_setting( 'royal_storage_from_email', sanitize_email( $_POST['royal_storage_from_email'] ?? '' ) );
		$settings->update_setting( 'royal_storage_from_name', sanitize_text_field( $_POST['royal_storage_from_name'] ?? '' ) );

		// Payment settings
		$settings->update_setting( 'royal_storage_bank_plugin_key', sanitize_text_field( $_POST['royal_storage_bank_plugin_key'] ?? '' ) );
		$settings->update_setting( 'royal_storage_bank_plugin_secret', sanitize_text_field( $_POST['royal_storage_bank_plugin_secret'] ?? '' ) );

		// Notification settings
		$settings->update_setting( 'royal_storage_notify_expiry_days', intval( $_POST['royal_storage_notify_expiry_days'] ?? 7 ) );
		$settings->update_setting( 'royal_storage_notify_overdue', sanitize_text_field( $_POST['royal_storage_notify_overdue'] ?? 'yes' ) );
		$settings->update_setting( 'royal_storage_notify_new_booking', sanitize_text_field( $_POST['royal_storage_notify_new_booking'] ?? 'yes' ) );

		// Guest checkout settings
		$settings->update_setting( 'royal_storage_guest_checkout', sanitize_text_field( $_POST['royal_storage_guest_checkout'] ?? 'yes' ) );
		$settings->update_setting( 'royal_storage_auto_create_account', sanitize_text_field( $_POST['royal_storage_auto_create_account'] ?? 'yes' ) );
		$settings->update_setting( 'royal_storage_send_account_credentials', sanitize_text_field( $_POST['royal_storage_send_account_credentials'] ?? 'yes' ) );

		// Advanced settings
		$settings->update_setting( 'royal_storage_auto_cleanup_days', intval( $_POST['royal_storage_auto_cleanup_days'] ?? 90 ) );
		$settings->update_setting( 'royal_storage_cache_expiry', intval( $_POST['royal_storage_cache_expiry'] ?? 3600 ) );
		$settings->update_setting( 'royal_storage_debug_mode', sanitize_text_field( $_POST['royal_storage_debug_mode'] ?? 'no' ) );

		echo '<div class="notice notice-success"><p>' . esc_html__( 'Settings saved successfully!', 'royal-storage' ) . '</p></div>';
	}

	/**
	 * Render business settings section
	 *
	 * @param Settings $settings Settings instance.
	 * @return void
	 */
	private function render_business_settings( $settings ) {
		?>
		<div class="royal-storage-settings-section">
			<h2><?php esc_html_e( 'Business Information', 'royal-storage' ); ?></h2>
			<table class="form-table">
				<tr>
					<th scope="row">
						<label for="royal_storage_business_name"><?php esc_html_e( 'Business Name', 'royal-storage' ); ?></label>
					</th>
					<td>
						<input type="text" id="royal_storage_business_name" name="royal_storage_business_name" 
							   value="<?php echo esc_attr( $settings->get_setting( 'royal_storage_business_name', 'Royal Storage' ) ); ?>" 
							   class="regular-text" />
						<p class="description"><?php esc_html_e( 'Your business name as it appears on invoices and communications.', 'royal-storage' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row">
						<label for="royal_storage_business_phone"><?php esc_html_e( 'Phone Number', 'royal-storage' ); ?></label>
					</th>
					<td>
						<input type="text" id="royal_storage_business_phone" name="royal_storage_business_phone" 
							   value="<?php echo esc_attr( $settings->get_setting( 'royal_storage_business_phone' ) ); ?>" 
							   class="regular-text" />
					</td>
				</tr>
				<tr>
					<th scope="row">
						<label for="royal_storage_business_email"><?php esc_html_e( 'Email Address', 'royal-storage' ); ?></label>
					</th>
					<td>
						<input type="email" id="royal_storage_business_email" name="royal_storage_business_email" 
							   value="<?php echo esc_attr( $settings->get_setting( 'royal_storage_business_email' ) ); ?>" 
							   class="regular-text" />
					</td>
				</tr>
				<tr>
					<th scope="row">
						<label for="royal_storage_business_address"><?php esc_html_e( 'Business Address', 'royal-storage' ); ?></label>
					</th>
					<td>
						<textarea id="royal_storage_business_address" name="royal_storage_business_address" 
								  rows="3" cols="50" class="large-text"><?php echo esc_textarea( $settings->get_setting( 'royal_storage_business_address' ) ); ?></textarea>
					</td>
				</tr>
			</table>
		</div>
		<?php
	}

	/**
	 * Render guest checkout settings section
	 *
	 * @param Settings $settings Settings instance.
	 * @return void
	 */
	private function render_guest_checkout_settings( $settings ) {
		?>
		<div class="royal-storage-settings-section">
			<h2><?php esc_html_e( 'Guest Checkout Settings', 'royal-storage' ); ?></h2>
			<table class="form-table">
				<tr>
					<th scope="row"><?php esc_html_e( 'Enable Guest Checkout', 'royal-storage' ); ?></th>
					<td>
						<fieldset>
							<label>
								<input type="checkbox" name="royal_storage_guest_checkout" value="yes" 
									   <?php checked( $settings->get_setting( 'royal_storage_guest_checkout', 'yes' ), 'yes' ); ?> />
								<?php esc_html_e( 'Allow customers to book without creating an account first', 'royal-storage' ); ?>
							</label>
							<p class="description"><?php esc_html_e( 'When enabled, customers can complete bookings without logging in. An account will be automatically created for them.', 'royal-storage' ); ?></p>
						</fieldset>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Auto Create Account', 'royal-storage' ); ?></th>
					<td>
						<fieldset>
							<label>
								<input type="checkbox" name="royal_storage_auto_create_account" value="yes" 
									   <?php checked( $settings->get_setting( 'royal_storage_auto_create_account', 'yes' ), 'yes' ); ?> />
								<?php esc_html_e( 'Automatically create WordPress account for guest customers', 'royal-storage' ); ?>
							</label>
							<p class="description"><?php esc_html_e( 'If enabled, a WordPress user account will be automatically created when a guest makes a booking. This allows them to access their booking history later.', 'royal-storage' ); ?></p>
						</fieldset>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Send Account Credentials', 'royal-storage' ); ?></th>
					<td>
						<fieldset>
							<label>
								<input type="checkbox" name="royal_storage_send_account_credentials" value="yes" 
									   <?php checked( $settings->get_setting( 'royal_storage_send_account_credentials', 'yes' ), 'yes' ); ?> />
								<?php esc_html_e( 'Email account login credentials to new guest customers', 'royal-storage' ); ?>
							</label>
							<p class="description"><?php esc_html_e( 'When enabled, new guest customers will receive an email with their username and password after account creation.', 'royal-storage' ); ?></p>
						</fieldset>
					</td>
				</tr>
			</table>
		</div>
		<?php
	}

	/**
	 * Render pricing settings section
	 *
	 * @param Settings $settings Settings instance.
	 * @return void
	 */
	private function render_pricing_settings( $settings ) {
		?>
		<div class="royal-storage-settings-section">
			<h2><?php esc_html_e( 'Pricing Configuration', 'royal-storage' ); ?></h2>
			<table class="form-table">
				<tr>
					<th scope="row">
						<label for="royal_storage_currency"><?php esc_html_e( 'Currency', 'royal-storage' ); ?></label>
					</th>
					<td>
						<select id="royal_storage_currency" name="royal_storage_currency">
							<option value="RSD" <?php selected( $settings->get_setting( 'royal_storage_currency', 'RSD' ), 'RSD' ); ?>>RSD (Serbian Dinar)</option>
							<option value="EUR" <?php selected( $settings->get_setting( 'royal_storage_currency', 'RSD' ), 'EUR' ); ?>>EUR (Euro)</option>
							<option value="USD" <?php selected( $settings->get_setting( 'royal_storage_currency', 'RSD' ), 'USD' ); ?>>USD (US Dollar)</option>
						</select>
					</td>
				</tr>
				<tr>
					<th scope="row">
						<label for="royal_storage_vat_rate"><?php esc_html_e( 'VAT Rate (%)', 'royal-storage' ); ?></label>
					</th>
					<td>
						<input type="number" id="royal_storage_vat_rate" name="royal_storage_vat_rate" 
							   value="<?php echo esc_attr( $settings->get_setting( 'royal_storage_vat_rate', 20 ) ); ?>" 
							   min="0" max="100" step="0.01" class="small-text" />
						<p class="description"><?php esc_html_e( 'VAT rate as a percentage (e.g., 20 for 20%).', 'royal-storage' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row">
						<label for="royal_storage_daily_rate"><?php esc_html_e( 'Default Daily Rate', 'royal-storage' ); ?></label>
					</th>
					<td>
						<input type="number" id="royal_storage_daily_rate" name="royal_storage_daily_rate"
							   value="<?php echo esc_attr( $settings->get_setting( 'royal_storage_daily_rate', 1000 ) ); ?>"
							   placeholder="1000"
							   min="0" step="0.01" class="small-text" />
						<p class="description"><?php esc_html_e( 'Default daily rate for storage units.', 'royal-storage' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row">
						<label for="royal_storage_weekly_rate"><?php esc_html_e( 'Default Weekly Rate', 'royal-storage' ); ?></label>
					</th>
					<td>
						<input type="number" id="royal_storage_weekly_rate" name="royal_storage_weekly_rate"
							   value="<?php echo esc_attr( $settings->get_setting( 'royal_storage_weekly_rate', 6000 ) ); ?>"
							   placeholder="6000"
							   min="0" step="0.01" class="small-text" />
						<p class="description"><?php esc_html_e( 'Default weekly rate for storage units.', 'royal-storage' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row">
						<label for="royal_storage_monthly_rate"><?php esc_html_e( 'Default Monthly Rate', 'royal-storage' ); ?></label>
					</th>
					<td>
						<input type="number" id="royal_storage_monthly_rate" name="royal_storage_monthly_rate"
							   value="<?php echo esc_attr( $settings->get_setting( 'royal_storage_monthly_rate', 20000 ) ); ?>"
							   placeholder="20000"
							   min="0" step="0.01" class="small-text" />
						<p class="description"><?php esc_html_e( 'Default monthly rate for storage units.', 'royal-storage' ); ?></p>
					</td>
				</tr>
			</table>
		</div>
		<?php
	}

	/**
	 * Render email settings section
	 *
	 * @param Settings $settings Settings instance.
	 * @return void
	 */
	private function render_email_settings( $settings ) {
		?>
		<div class="royal-storage-settings-section">
			<h2><?php esc_html_e( 'Email Configuration', 'royal-storage' ); ?></h2>
			<table class="form-table">
				<tr>
					<th scope="row">
						<label for="royal_storage_from_name"><?php esc_html_e( 'From Name', 'royal-storage' ); ?></label>
					</th>
					<td>
						<input type="text" id="royal_storage_from_name" name="royal_storage_from_name" 
							   value="<?php echo esc_attr( $settings->get_setting( 'royal_storage_from_name', get_bloginfo( 'name' ) ) ); ?>" 
							   class="regular-text" />
					</td>
				</tr>
				<tr>
					<th scope="row">
						<label for="royal_storage_from_email"><?php esc_html_e( 'From Email', 'royal-storage' ); ?></label>
					</th>
					<td>
						<input type="email" id="royal_storage_from_email" name="royal_storage_from_email" 
							   value="<?php echo esc_attr( $settings->get_setting( 'royal_storage_from_email', get_option( 'admin_email' ) ) ); ?>" 
							   class="regular-text" />
					</td>
				</tr>
				<tr>
					<th scope="row">
						<label for="royal_storage_smtp_host"><?php esc_html_e( 'SMTP Host', 'royal-storage' ); ?></label>
					</th>
					<td>
						<input type="text" id="royal_storage_smtp_host" name="royal_storage_smtp_host" 
							   value="<?php echo esc_attr( $settings->get_setting( 'royal_storage_smtp_host' ) ); ?>" 
							   class="regular-text" />
						<p class="description"><?php esc_html_e( 'SMTP server hostname (e.g., smtp.gmail.com).', 'royal-storage' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row">
						<label for="royal_storage_smtp_port"><?php esc_html_e( 'SMTP Port', 'royal-storage' ); ?></label>
					</th>
					<td>
						<input type="number" id="royal_storage_smtp_port" name="royal_storage_smtp_port" 
							   value="<?php echo esc_attr( $settings->get_setting( 'royal_storage_smtp_port', 587 ) ); ?>" 
							   min="1" max="65535" class="small-text" />
					</td>
				</tr>
				<tr>
					<th scope="row">
						<label for="royal_storage_smtp_username"><?php esc_html_e( 'SMTP Username', 'royal-storage' ); ?></label>
					</th>
					<td>
						<input type="text" id="royal_storage_smtp_username" name="royal_storage_smtp_username" 
							   value="<?php echo esc_attr( $settings->get_setting( 'royal_storage_smtp_username' ) ); ?>" 
							   class="regular-text" />
					</td>
				</tr>
				<tr>
					<th scope="row">
						<label for="royal_storage_smtp_password"><?php esc_html_e( 'SMTP Password', 'royal-storage' ); ?></label>
					</th>
					<td>
						<input type="password" id="royal_storage_smtp_password" name="royal_storage_smtp_password" 
							   value="<?php echo esc_attr( $settings->get_setting( 'royal_storage_smtp_password' ) ); ?>" 
							   class="regular-text" />
					</td>
				</tr>
			</table>
		</div>
		<?php
	}

	/**
	 * Render payment settings section
	 *
	 * @param Settings $settings Settings instance.
	 * @return void
	 */
	private function render_payment_settings( $settings ) {
		?>
		<div class="royal-storage-settings-section">
			<h2><?php esc_html_e( 'Payment Configuration', 'royal-storage' ); ?></h2>
			<table class="form-table">
				<tr>
					<th scope="row">
						<label for="royal_storage_bank_plugin_key"><?php esc_html_e( 'Bank Plugin API Key', 'royal-storage' ); ?></label>
					</th>
					<td>
						<input type="text" id="royal_storage_bank_plugin_key" name="royal_storage_bank_plugin_key" 
							   value="<?php echo esc_attr( $settings->get_setting( 'royal_storage_bank_plugin_key' ) ); ?>" 
							   class="regular-text" />
						<p class="description"><?php esc_html_e( 'API key for bank payment integration.', 'royal-storage' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row">
						<label for="royal_storage_bank_plugin_secret"><?php esc_html_e( 'Bank Plugin Secret', 'royal-storage' ); ?></label>
					</th>
					<td>
						<input type="password" id="royal_storage_bank_plugin_secret" name="royal_storage_bank_plugin_secret" 
							   value="<?php echo esc_attr( $settings->get_setting( 'royal_storage_bank_plugin_secret' ) ); ?>" 
							   class="regular-text" />
					</td>
				</tr>
			</table>
		</div>
		<?php
	}

	/**
	 * Render notification settings section
	 *
	 * @param Settings $settings Settings instance.
	 * @return void
	 */
	private function render_notification_settings( $settings ) {
		?>
		<div class="royal-storage-settings-section">
			<h2><?php esc_html_e( 'Notification Settings', 'royal-storage' ); ?></h2>
			<table class="form-table">
				<tr>
					<th scope="row">
						<label for="royal_storage_notify_expiry_days"><?php esc_html_e( 'Expiry Notification Days', 'royal-storage' ); ?></label>
					</th>
					<td>
						<input type="number" id="royal_storage_notify_expiry_days" name="royal_storage_notify_expiry_days" 
							   value="<?php echo esc_attr( $settings->get_setting( 'royal_storage_notify_expiry_days', 7 ) ); ?>" 
							   min="1" max="30" class="small-text" />
						<p class="description"><?php esc_html_e( 'Number of days before expiry to send notification.', 'royal-storage' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Email Notifications', 'royal-storage' ); ?></th>
					<td>
						<fieldset>
							<label>
								<input type="checkbox" name="royal_storage_notify_overdue" value="yes" 
									   <?php checked( $settings->get_setting( 'royal_storage_notify_overdue', 'yes' ), 'yes' ); ?> />
								<?php esc_html_e( 'Notify about overdue bookings', 'royal-storage' ); ?>
							</label><br>
							<label>
								<input type="checkbox" name="royal_storage_notify_new_booking" value="yes" 
									   <?php checked( $settings->get_setting( 'royal_storage_notify_new_booking', 'yes' ), 'yes' ); ?> />
								<?php esc_html_e( 'Notify about new bookings', 'royal-storage' ); ?>
							</label>
						</fieldset>
					</td>
				</tr>
			</table>
		</div>
		<?php
	}

	/**
	 * Render page settings section
	 *
	 * @return void
	 */
	private function render_page_settings() {
		$pages = array(
			'booking' => array(
				'option' => 'royal_storage_booking_page_id',
				'title'  => __( 'Booking Page', 'royal-storage' ),
				'slug'   => 'book-storage',
				'shortcode' => '[royal_storage_booking]',
			),
			'portal' => array(
				'option' => 'royal_storage_portal_page_id',
				'title'  => __( 'Customer Portal Page', 'royal-storage' ),
				'slug'   => 'customer-portal',
				'shortcode' => '[royal_storage_portal]',
			),
			'login' => array(
				'option' => 'royal_storage_login_page_id',
				'title'  => __( 'Login Page', 'royal-storage' ),
				'slug'   => 'storage-login',
				'shortcode' => '[royal_storage_login]',
			),
			'checkout' => array(
				'option' => 'royal_storage_checkout_page_id',
				'title'  => __( 'Checkout Page', 'royal-storage' ),
				'slug'   => 'checkout',
				'shortcode' => '[royal_storage_checkout]',
			),
		);
		?>
		<div class="royal-storage-settings-section">
			<h2><?php esc_html_e( 'Page Management', 'royal-storage' ); ?></h2>
			<p class="description"><?php esc_html_e( 'These pages are automatically created when the plugin is activated. You can recreate them if needed.', 'royal-storage' ); ?></p>
			
			<table class="form-table">
				<?php foreach ( $pages as $key => $page ) : 
					$page_id = get_option( $page['option'] );
					$page_obj = $page_id ? get_post( $page_id ) : null;
					$page_url = $page_obj ? get_permalink( $page_obj->ID ) : '';
					$edit_url = $page_obj ? admin_url( 'post.php?post=' . $page_obj->ID . '&action=edit' ) : '';
				?>
				<tr>
					<th scope="row"><?php echo esc_html( $page['title'] ); ?></th>
					<td>
						<?php if ( $page_obj && 'trash' !== $page_obj->post_status ) : ?>
							<p>
								<strong><?php echo esc_html( $page_obj->post_title ); ?></strong><br>
								<code><?php echo esc_html( $page['shortcode'] ); ?></code><br>
								<?php if ( $page_url ) : ?>
									<a href="<?php echo esc_url( $page_url ); ?>" target="_blank" class="button button-small">
										<?php esc_html_e( 'View Page', 'royal-storage' ); ?>
									</a>
								<?php endif; ?>
								<?php if ( $edit_url ) : ?>
									<a href="<?php echo esc_url( $edit_url ); ?>" class="button button-small">
										<?php esc_html_e( 'Edit Page', 'royal-storage' ); ?>
									</a>
								<?php endif; ?>
							</p>
						<?php else : ?>
							<p class="description" style="color: #d63638;">
								<?php esc_html_e( 'Page not found. Click "Recreate Pages" below to create it.', 'royal-storage' ); ?>
							</p>
						<?php endif; ?>
					</td>
				</tr>
				<?php endforeach; ?>
			</table>
			
			<p>
				<a href="<?php echo esc_url( wp_nonce_url( add_query_arg( 'recreate_pages', '1' ), 'recreate_pages' ) ); ?>" class="button button-secondary">
					<?php esc_html_e( 'Recreate Pages', 'royal-storage' ); ?>
				</a>
				<span class="description"><?php esc_html_e( 'This will recreate all plugin pages if they are missing or deleted.', 'royal-storage' ); ?></span>
			</p>
		</div>
		<?php
	}

	/**
	 * Render advanced settings section
	 *
	 * @param Settings $settings Settings instance.
	 * @return void
	 */
	private function render_advanced_settings( $settings ) {
		?>
		<div class="royal-storage-settings-section">
			<h2><?php esc_html_e( 'Advanced Settings', 'royal-storage' ); ?></h2>
			<table class="form-table">
				<tr>
					<th scope="row">
						<label for="royal_storage_auto_cleanup_days"><?php esc_html_e( 'Auto Cleanup Days', 'royal-storage' ); ?></label>
					</th>
					<td>
						<input type="number" id="royal_storage_auto_cleanup_days" name="royal_storage_auto_cleanup_days"
							   value="<?php echo esc_attr( $settings->get_setting( 'royal_storage_auto_cleanup_days', 90 ) ); ?>"
							   min="0" class="small-text" />
						<p class="description"><?php esc_html_e( 'Days to keep old data before auto-cleanup. Set to 0 to disable auto-cleanup.', 'royal-storage' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row">
						<label for="royal_storage_cache_expiry"><?php esc_html_e( 'Cache Expiry (seconds)', 'royal-storage' ); ?></label>
					</th>
					<td>
						<input type="number" id="royal_storage_cache_expiry" name="royal_storage_cache_expiry" 
							   value="<?php echo esc_attr( $settings->get_setting( 'royal_storage_cache_expiry', 3600 ) ); ?>" 
							   min="300" max="86400" class="small-text" />
						<p class="description"><?php esc_html_e( 'How long to cache data (300-86400 seconds).', 'royal-storage' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Debug Mode', 'royal-storage' ); ?></th>
					<td>
						<fieldset>
							<label>
								<input type="checkbox" name="royal_storage_debug_mode" value="yes" 
									   <?php checked( $settings->get_setting( 'royal_storage_debug_mode', 'no' ), 'yes' ); ?> />
								<?php esc_html_e( 'Enable debug mode for troubleshooting', 'royal-storage' ); ?>
							</label>
						</fieldset>
					</td>
				</tr>
			</table>
		</div>
		<?php
	}

	/**
	 * Enqueue admin assets
	 *
	 * @return void
	 */
	public function enqueue_admin_assets() {
		// Enqueue admin styles
		wp_enqueue_style(
			'royal-storage-admin',
			ROYAL_STORAGE_URL . 'assets/css/admin.css',
			array(),
			ROYAL_STORAGE_VERSION
		);

		// Enqueue utilities script (required by other scripts)
		wp_enqueue_script(
			'royal-storage-utils',
			ROYAL_STORAGE_URL . 'assets/js/royal-storage-utils.js',
			array( 'jquery' ),
			ROYAL_STORAGE_VERSION,
			true
		);

		// Enqueue admin script
		wp_enqueue_script(
			'royal-storage-admin',
			ROYAL_STORAGE_URL . 'assets/js/admin.js',
			array( 'jquery', 'royal-storage-utils' ),
			ROYAL_STORAGE_VERSION,
			true
		);

		// Enqueue bookings script
		wp_enqueue_script(
			'royal-storage-bookings',
			ROYAL_STORAGE_URL . 'assets/js/bookings.js',
			array( 'jquery', 'royal-storage-utils' ),
			ROYAL_STORAGE_VERSION,
			true
		);

		// Localize script
		wp_localize_script(
			'royal-storage-admin',
			'royalStorageAdmin',
			array(
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( 'royal_storage_admin' ),
			)
		);
	}

	/**
	 * Add storage unit columns
	 *
	 * @param array $columns Columns.
	 * @return array
	 */
	public function add_storage_unit_columns( $columns ) {
		$columns['size']   = __( 'Size', 'royal-storage' );
		$columns['price']  = __( 'Price', 'royal-storage' );
		$columns['status'] = __( 'Status', 'royal-storage' );
		return $columns;
	}

	/**
	 * Add parking space columns
	 *
	 * @param array $columns Columns.
	 * @return array
	 */
	public function add_parking_space_columns( $columns ) {
		$columns['spot']   = __( 'Spot Number', 'royal-storage' );
		$columns['price']  = __( 'Price', 'royal-storage' );
		$columns['status'] = __( 'Status', 'royal-storage' );
		return $columns;
	}

	/**
	 * Add booking columns
	 *
	 * @param array $columns Columns.
	 * @return array
	 */
	public function add_booking_columns( $columns ) {
		$columns['customer']        = __( 'Customer', 'royal-storage' );
		$columns['unit']            = __( 'Unit', 'royal-storage' );
		$columns['dates']           = __( 'Dates', 'royal-storage' );
		$columns['payment_status']  = __( 'Payment Status', 'royal-storage' );
		$columns['booking_status']  = __( 'Booking Status', 'royal-storage' );
		return $columns;
	}

	/**
	 * Add invoice columns
	 *
	 * @param array $columns Columns.
	 * @return array
	 */
	public function add_invoice_columns( $columns ) {
		$columns['invoice_number'] = __( 'Invoice Number', 'royal-storage' );
		$columns['amount']         = __( 'Amount', 'royal-storage' );
		$columns['status']         = __( 'Status', 'royal-storage' );
		$columns['date']           = __( 'Date', 'royal-storage' );
		return $columns;
	}

	/**
	 * Get bookings with details
	 *
	 * @param Bookings $bookings Bookings instance.
	 * @param int      $per_page Per page limit.
	 * @param int      $offset Offset.
	 * @param string   $search Search term.
	 * @param string   $status_filter Status filter.
	 * @return array
	 */
	private function get_bookings_with_details( $bookings, $per_page, $offset, $search = '', $status_filter = '' ) {
		global $wpdb;

		$bookings_table = $wpdb->prefix . 'royal_bookings';
		$users_table = $wpdb->users;
		$units_table = $wpdb->prefix . 'royal_storage_units';
		$spaces_table = $wpdb->prefix . 'royal_parking_spaces';

		$query = "
			SELECT b.*, u.display_name as customer_name, u.user_email as customer_email,
				COALESCE(su.size, sp.spot_number) as unit_info,
				COALESCE(su.dimensions, sp.height_limit) as unit_details
			FROM $bookings_table b
			LEFT JOIN $users_table u ON b.customer_id = u.ID
			LEFT JOIN $units_table su ON b.unit_id = su.id AND b.unit_type = 'storage'
			LEFT JOIN $spaces_table sp ON b.unit_id = sp.id AND b.unit_type = 'parking'
			WHERE 1=1
		";

		$params = array();

		if ( ! empty( $search ) ) {
			$query .= " AND (b.id LIKE %s OR u.display_name LIKE %s OR u.user_email LIKE %s OR b.access_code LIKE %s)";
			$search_term = '%' . $wpdb->esc_like( $search ) . '%';
			$params = array_merge( $params, array( $search_term, $search_term, $search_term, $search_term ) );
		}

		if ( ! empty( $status_filter ) ) {
			$query .= " AND b.status = %s";
			$params[] = $status_filter;
		}

		$query .= " ORDER BY b.created_at DESC LIMIT %d OFFSET %d";
		$params[] = $per_page;
		$params[] = $offset;

		if ( ! empty( $params ) ) {
			return $wpdb->get_results( $wpdb->prepare( $query, $params ) );
		} else {
			return $wpdb->get_results( $query );
		}
	}

	/**
	 * Render bookings table
	 *
	 * @param array  $bookings_data Bookings data.
	 * @param string $search Search term.
	 * @param string $status_filter Status filter.
	 * @return void
	 */
	private function render_bookings_table( $bookings_data, $search, $status_filter ) {
		?>
		<table class="wp-list-table widefat fixed striped">
			<thead>
				<tr>
					<th><?php esc_html_e( 'ID', 'royal-storage' ); ?></th>
					<th><?php esc_html_e( 'Customer', 'royal-storage' ); ?></th>
					<th><?php esc_html_e( 'Unit', 'royal-storage' ); ?></th>
					<th><?php esc_html_e( 'Type', 'royal-storage' ); ?></th>
					<th><?php esc_html_e( 'Start Date', 'royal-storage' ); ?></th>
					<th><?php esc_html_e( 'End Date', 'royal-storage' ); ?></th>
					<th><?php esc_html_e( 'Total Price', 'royal-storage' ); ?></th>
					<th><?php esc_html_e( 'Status', 'royal-storage' ); ?></th>
					<th><?php esc_html_e( 'Payment', 'royal-storage' ); ?></th>
					<th><?php esc_html_e( 'Actions', 'royal-storage' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php if ( ! empty( $bookings_data ) ) : ?>
					<?php foreach ( $bookings_data as $booking ) : ?>
						<tr>
							<td><?php echo esc_html( $booking->id ); ?></td>
							<td>
								<strong><?php echo esc_html( $booking->customer_name ?: 'Unknown' ); ?></strong><br>
								<small><?php echo esc_html( $booking->customer_email ?: 'No email' ); ?></small>
							</td>
							<td>
								<?php echo esc_html( $booking->unit_info ?: 'N/A' ); ?><br>
								<small><?php echo esc_html( $booking->unit_details ?: '' ); ?></small>
							</td>
							<td>
								<span class="unit-type-<?php echo esc_attr( $booking->unit_type ); ?>">
									<?php echo esc_html( ucfirst( $booking->unit_type ) ); ?>
								</span>
							</td>
							<td><?php echo esc_html( date( 'M j, Y', strtotime( $booking->start_date ) ) ); ?></td>
							<td><?php echo esc_html( date( 'M j, Y', strtotime( $booking->end_date ) ) ); ?></td>
							<td><?php echo esc_html( number_format( $booking->total_price, 2 ) ); ?> RSD</td>
							<td>
								<span class="status-<?php echo esc_attr( $booking->status ); ?>">
									<?php echo esc_html( ucfirst( $booking->status ) ); ?>
								</span>
							</td>
							<td>
								<span class="payment-<?php echo esc_attr( $booking->payment_status ); ?>">
									<?php echo esc_html( ucfirst( $booking->payment_status ) ); ?>
								</span>
							</td>
							<td>
								<div class="booking-actions">
									<button type="button" class="button button-small view-booking" data-booking-id="<?php echo esc_attr( $booking->id ); ?>">
										<?php esc_html_e( 'View', 'royal-storage' ); ?>
									</button>
									<?php if ( $booking->status === 'pending' ) : ?>
										<button type="button" class="button button-primary button-small approve-booking" data-booking-id="<?php echo esc_attr( $booking->id ); ?>">
											<?php esc_html_e( 'Approve', 'royal-storage' ); ?>
										</button>
									<?php endif; ?>
									<?php if ( $booking->status !== 'cancelled' && $booking->status !== 'expired' && $booking->status !== 'completed' ) : ?>
										<button type="button" class="button button-small button-link-delete cancel-booking-btn" data-booking-id="<?php echo esc_attr( $booking->id ); ?>">
											<?php esc_html_e( 'Cancel', 'royal-storage' ); ?>
										</button>
									<?php endif; ?>
								</div>
							</td>
						</tr>
					<?php endforeach; ?>
				<?php else : ?>
					<tr>
						<td colspan="10" class="no-items">
							<?php if ( $search || $status_filter ) : ?>
								<?php esc_html_e( 'No bookings found matching your criteria.', 'royal-storage' ); ?>
							<?php else : ?>
								<?php esc_html_e( 'No bookings found. Create your first booking to get started!', 'royal-storage' ); ?>
							<?php endif; ?>
						</td>
					</tr>
				<?php endif; ?>
			</tbody>
		</table>
		<?php
	}

	/**
	 * Render bookings pagination
	 *
	 * @param int    $current_page Current page.
	 * @param int    $total_pages Total pages.
	 * @param string $search Search term.
	 * @param string $status_filter Status filter.
	 * @return void
	 */
	private function render_bookings_pagination( $current_page, $total_pages, $search, $status_filter ) {
		$base_url = admin_url( 'admin.php?page=royal-storage-bookings' );
		$query_params = array();

		if ( $search ) {
			$query_params['s'] = $search;
		}
		if ( $status_filter ) {
			$query_params['status'] = $status_filter;
		}

		if ( ! empty( $query_params ) ) {
			$base_url .= '&' . http_build_query( $query_params );
		}

		echo '<div class="tablenav-pages">';
		echo '<span class="displaying-num">' . sprintf( esc_html( _n( '%s item', '%s items', $total_pages, 'royal-storage' ) ), number_format_i18n( $total_pages ) ) . '</span>';

		if ( $total_pages > 1 ) {
			echo '<span class="pagination-links">';

			// Previous page
			if ( $current_page > 1 ) {
				$prev_url = $base_url . '&paged=' . ( $current_page - 1 );
				echo '<a class="prev-page button" href="' . esc_url( $prev_url ) . '">&laquo;</a>';
			}

			// Page numbers
			$start_page = max( 1, $current_page - 2 );
			$end_page = min( $total_pages, $current_page + 2 );

			for ( $i = $start_page; $i <= $end_page; $i++ ) {
				if ( $i === $current_page ) {
					echo '<span class="current-page">' . $i . '</span>';
				} else {
					$page_url = $base_url . '&paged=' . $i;
					echo '<a class="page-numbers" href="' . esc_url( $page_url ) . '">' . $i . '</a>';
				}
			}

			// Next page
			if ( $current_page < $total_pages ) {
				$next_url = $base_url . '&paged=' . ( $current_page + 1 );
				echo '<a class="next-page button" href="' . esc_url( $next_url ) . '">&raquo;</a>';
			}

			echo '</span>';
		}

		echo '</div>';
	}

	/**
	 * Render storage units page
	 *
	 * @return void
	 */
	public function render_storage_units_page() {
		$storage_units = new StorageUnits();
		$storage_units->render_page();
	}

	/**
	 * Render parking spaces page
	 *
	 * @return void
	 */
	public function render_parking_spaces_page() {
		$parking_spaces = new ParkingSpaces();
		$parking_spaces->render_page();
	}

	/**
	 * Render unit layouts page
	 *
	 * @return void
	 */
	public function render_unit_layouts_page() {
		$unit_layout_admin = new UnitLayoutAdmin();
		$unit_layout_admin->render_layout_page();
	}

	/**
	 * Render payment settings page
	 *
	 * @return void
	 */
	public function render_payment_settings_page() {
		$payment_settings = new PaymentSettings();
		$payment_settings->render_settings_page();
	}
}

