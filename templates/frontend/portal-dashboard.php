<?php
/**
 * Portal Dashboard Template
 *
 * @package RoyalStorage
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div class="portal-dashboard">
	<div class="dashboard-stats">
		<div class="stat-card">
			<div class="stat-icon">📦</div>
			<div class="stat-content">
				<h3><?php esc_html_e( 'Active Bookings', 'royal-storage' ); ?></h3>
				<p class="stat-value"><?php echo esc_html( $stats->active_bookings ); ?></p>
			</div>
		</div>

		<div class="stat-card">
			<div class="stat-icon">💰</div>
			<div class="stat-content">
				<h3><?php esc_html_e( 'Total Spent', 'royal-storage' ); ?></h3>
				<p class="stat-value"><?php echo esc_html( number_format( $stats->total_spent, 2 ) ); ?> RSD</p>
			</div>
		</div>

		<div class="stat-card">
			<div class="stat-icon">📄</div>
			<div class="stat-content">
				<h3><?php esc_html_e( 'Unpaid Invoices', 'royal-storage' ); ?></h3>
				<p class="stat-value"><?php echo esc_html( $stats->unpaid_invoices ); ?></p>
			</div>
		</div>

		<div class="stat-card">
			<div class="stat-icon">⚠️</div>
			<div class="stat-content">
				<h3><?php esc_html_e( 'Unpaid Amount', 'royal-storage' ); ?></h3>
				<p class="stat-value status-cancelled"><?php echo esc_html( number_format( $stats->unpaid_amount, 2 ) ); ?> RSD</p>
			</div>
		</div>
	</div>

	<div class="portal-bookings">
		<h2><?php esc_html_e( 'Quick Actions', 'royal-storage' ); ?></h2>
		<div class="action-buttons" style="display: flex; gap: 1rem; margin-bottom: 2.5rem;">
			<a href="<?php echo esc_url( $base_url . '?tab=bookings' ); ?>" class="royal-storage-btn" style="width: auto;">
				<?php esc_html_e( 'View My Bookings', 'royal-storage' ); ?>
			</a>
			<a href="<?php echo esc_url( $base_url . '?tab=invoices' ); ?>" class="royal-storage-btn" style="width: auto;">
				<?php esc_html_e( 'View Invoices', 'royal-storage' ); ?>
			</a>
			<a href="<?php echo esc_url( $base_url . '?tab=account' ); ?>" class="royal-storage-btn royal-storage-btn-secondary" style="width: auto;">
				<?php esc_html_e( 'Edit Account', 'royal-storage' ); ?>
			</a>
		</div>

		<div class="booking-card" style="background: var(--rs-bg-main); border-style: dashed;">
			<h2><?php esc_html_e( 'Welcome to Your Storage Account', 'royal-storage' ); ?></h2>
			<p><?php esc_html_e( 'Manage your storage bookings, view invoices, and update your account information from this portal.', 'royal-storage' ); ?></p>
			<ul style="margin: 1.5rem 0; padding-left: 1.5rem;">
				<li><?php esc_html_e( 'View and manage your active bookings', 'royal-storage' ); ?></li>
				<li><?php esc_html_e( 'Renew or cancel bookings', 'royal-storage' ); ?></li>
				<li><?php esc_html_e( 'Download and pay invoices', 'royal-storage' ); ?></li>
				<li><?php esc_html_e( 'Update your profile information', 'royal-storage' ); ?></li>
			</ul>
		</div>
	</div>
</div>
