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
				<p class="stat-value"><?php echo esc_html( number_format( $stats->unpaid_amount, 2 ) ); ?> RSD</p>
			</div>
		</div>
	</div>

	<div class="dashboard-actions">
		<h2><?php esc_html_e( 'Quick Actions', 'royal-storage' ); ?></h2>
		<div class="action-buttons">
			<a href="<?php echo esc_url( $base_url . '?tab=bookings' ); ?>" class="btn btn-primary">
				<?php esc_html_e( 'View My Bookings', 'royal-storage' ); ?>
			</a>
			<a href="<?php echo esc_url( $base_url . '?tab=invoices' ); ?>" class="btn btn-primary">
				<?php esc_html_e( 'View Invoices', 'royal-storage' ); ?>
			</a>
			<a href="<?php echo esc_url( $base_url . '?tab=account' ); ?>" class="btn btn-secondary">
				<?php esc_html_e( 'Edit Account', 'royal-storage' ); ?>
			</a>
		</div>
	</div>

	<div class="dashboard-info">
		<h2><?php esc_html_e( 'Welcome to Your Storage Account', 'royal-storage' ); ?></h2>
		<p><?php esc_html_e( 'Manage your storage bookings, view invoices, and update your account information from this portal.', 'royal-storage' ); ?></p>
		<ul>
			<li><?php esc_html_e( 'View and manage your active bookings', 'royal-storage' ); ?></li>
			<li><?php esc_html_e( 'Renew or cancel bookings', 'royal-storage' ); ?></li>
			<li><?php esc_html_e( 'Download and pay invoices', 'royal-storage' ); ?></li>
			<li><?php esc_html_e( 'Update your profile information', 'royal-storage' ); ?></li>
		</ul>
	</div>
</div>

<style>
	.portal-dashboard {
		padding: 20px;
	}

	.dashboard-stats {
		display: grid;
		grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
		gap: 20px;
		margin-bottom: 40px;
	}

	.stat-card {
		background: white;
		padding: 20px;
		border-radius: 8px;
		box-shadow: 0 2px 8px rgba(0,0,0,0.1);
		display: flex;
		align-items: center;
		gap: 15px;
	}

	.stat-icon {
		font-size: 32px;
	}

	.stat-content h3 {
		margin: 0 0 5px 0;
		font-size: 14px;
		color: #666;
	}

	.stat-value {
		margin: 0;
		font-size: 24px;
		font-weight: bold;
		color: #2c3e50;
	}

	.dashboard-actions {
		background: white;
		padding: 20px;
		border-radius: 8px;
		box-shadow: 0 2px 8px rgba(0,0,0,0.1);
		margin-bottom: 40px;
	}

	.dashboard-actions h2 {
		margin-top: 0;
	}

	.action-buttons {
		display: flex;
		gap: 10px;
		flex-wrap: wrap;
	}

	.btn {
		padding: 10px 20px;
		border-radius: 5px;
		text-decoration: none;
		font-weight: bold;
		display: inline-block;
		transition: all 0.3s ease;
	}

	.btn-primary {
		background: #667eea;
		color: white;
	}

	.btn-primary:hover {
		background: #5568d3;
	}

	.btn-secondary {
		background: #f0f0f0;
		color: #333;
	}

	.btn-secondary:hover {
		background: #e0e0e0;
	}

	.dashboard-info {
		background: white;
		padding: 20px;
		border-radius: 8px;
		box-shadow: 0 2px 8px rgba(0,0,0,0.1);
	}

	.dashboard-info h2 {
		margin-top: 0;
	}

	.dashboard-info ul {
		margin: 15px 0;
		padding-left: 20px;
	}

	.dashboard-info li {
		margin: 8px 0;
	}
</style>

