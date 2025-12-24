<?php
/**
 * Admin Dashboard Template
 *
 * @package RoyalStorage
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$dashboard = new \RoyalStorage\Admin\Dashboard();
?>

<div class="wrap royal-storage-admin">
	<h1><?php esc_html_e( 'Royal Storage Dashboard', 'royal-storage' ); ?></h1>

	<div class="dashboard-container">
		<!-- Key Metrics -->
		<div class="dashboard-section">
			<h2><?php esc_html_e( 'Key Metrics', 'royal-storage' ); ?></h2>
			
			<div class="metrics-grid">
				<div class="metric-card">
					<div class="metric-icon">📦</div>
					<div class="metric-content">
						<h3><?php esc_html_e( 'Total Units', 'royal-storage' ); ?></h3>
						<p class="metric-value"><?php echo esc_html( $dashboard->get_total_units() ); ?></p>
					</div>
				</div>

				<div class="metric-card">
					<div class="metric-icon">✓</div>
					<div class="metric-content">
						<h3><?php esc_html_e( 'Occupied', 'royal-storage' ); ?></h3>
						<p class="metric-value"><?php echo esc_html( $dashboard->get_occupied_units() ); ?></p>
					</div>
				</div>

				<div class="metric-card">
					<div class="metric-icon">○</div>
					<div class="metric-content">
						<h3><?php esc_html_e( 'Available', 'royal-storage' ); ?></h3>
						<p class="metric-value"><?php echo esc_html( $dashboard->get_available_units() ); ?></p>
					</div>
				</div>

				<div class="metric-card">
					<div class="metric-icon">%</div>
					<div class="metric-content">
						<h3><?php esc_html_e( 'Occupancy Rate', 'royal-storage' ); ?></h3>
						<p class="metric-value"><?php echo esc_html( round( $dashboard->get_occupancy_rate(), 2 ) ); ?>%</p>
					</div>
				</div>
			</div>
		</div>

		<!-- Alerts & Warnings -->
		<div class="dashboard-section">
			<h2><?php esc_html_e( 'Alerts & Warnings', 'royal-storage' ); ?></h2>
			
			<div class="alerts-grid">
				<div class="alert-card alert-danger">
					<div class="alert-icon">⚠</div>
					<div class="alert-content">
						<h3><?php esc_html_e( 'Overdue Bookings', 'royal-storage' ); ?></h3>
						<p class="alert-value"><?php echo esc_html( $dashboard->get_overdue_bookings_count() ); ?></p>
						<a href="<?php echo esc_url( admin_url( 'admin.php?page=royal-storage-bookings&filter=overdue' ) ); ?>" class="alert-link">
							<?php esc_html_e( 'View Details', 'royal-storage' ); ?>
						</a>
					</div>
				</div>

				<div class="alert-card alert-warning">
					<div class="alert-icon">🔔</div>
					<div class="alert-content">
						<h3><?php esc_html_e( 'Expiring Soon', 'royal-storage' ); ?></h3>
						<p class="alert-value"><?php echo esc_html( count( $dashboard->get_upcoming_expiries( 7 ) ) ); ?></p>
						<a href="<?php echo esc_url( admin_url( 'admin.php?page=royal-storage-bookings&filter=expiring' ) ); ?>" class="alert-link">
							<?php esc_html_e( 'View Details', 'royal-storage' ); ?>
						</a>
					</div>
				</div>

				<div class="alert-card alert-info">
					<div class="alert-icon">💰</div>
					<div class="alert-content">
						<h3><?php esc_html_e( 'Pending Payments', 'royal-storage' ); ?></h3>
						<p class="alert-value"><?php echo esc_html( $dashboard->get_pending_payments_count() ); ?></p>
						<a href="<?php echo esc_url( admin_url( 'admin.php?page=royal-storage-bookings&filter=pending' ) ); ?>" class="alert-link">
							<?php esc_html_e( 'View Details', 'royal-storage' ); ?>
						</a>
					</div>
				</div>
			</div>
		</div>

		<!-- Revenue -->
		<div class="dashboard-section">
			<h2><?php esc_html_e( 'Revenue', 'royal-storage' ); ?></h2>
			
			<div class="revenue-card">
				<div class="revenue-icon">💵</div>
				<div class="revenue-content">
					<h3><?php esc_html_e( 'Monthly Revenue', 'royal-storage' ); ?></h3>
					<p class="revenue-value"><?php echo esc_html( $dashboard->get_monthly_revenue() ); ?> RSD</p>
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=royal-storage-reports' ) ); ?>" class="revenue-link">
						<?php esc_html_e( 'View Reports', 'royal-storage' ); ?>
					</a>
				</div>
			</div>
		</div>

		<!-- Quick Actions -->
		<div class="dashboard-section">
			<h2><?php esc_html_e( 'Quick Actions', 'royal-storage' ); ?></h2>
			
			<div class="quick-actions">
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=royal-storage-bookings&action=new' ) ); ?>" class="button button-primary">
					<?php esc_html_e( '+ Create Booking', 'royal-storage' ); ?>
				</a>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=royal-storage-reports' ) ); ?>" class="button">
					<?php esc_html_e( 'View Reports', 'royal-storage' ); ?>
				</a>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=royal-storage-customers' ) ); ?>" class="button">
					<?php esc_html_e( 'Manage Customers', 'royal-storage' ); ?>
				</a>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=royal-storage-settings' ) ); ?>" class="button">
					<?php esc_html_e( 'Settings', 'royal-storage' ); ?>
				</a>
			</div>
		</div>
	</div>
</div>
