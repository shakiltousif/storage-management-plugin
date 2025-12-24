<?php
/**
 * Admin Reports Template
 *
 * @package RoyalStorage
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$reports = new \RoyalStorage\Admin\Reports();
$start_date = isset( $_GET['start_date'] ) ? sanitize_text_field( wp_unslash( $_GET['start_date'] ) ) : gmdate( 'Y-m-01' );
$end_date = isset( $_GET['end_date'] ) ? sanitize_text_field( wp_unslash( $_GET['end_date'] ) ) : gmdate( 'Y-m-d' );

$revenue_report = $reports->get_revenue_report( $start_date, $end_date );
$payment_report = $reports->get_payment_report( $start_date, $end_date );
$total_revenue = $reports->get_total_revenue( $start_date, $end_date );
?>

<div class="wrap royal-storage-admin">
	<h1><?php esc_html_e( 'Financial Reports', 'royal-storage' ); ?></h1>

	<!-- Date Filter -->
	<div class="dashboard-section">
		<form method="get" action="" class="royal-storage-reports-filters">
			<input type="hidden" name="page" value="royal-storage-reports">
			<div class="filter-group">
				<label><?php esc_html_e( 'Start Date', 'royal-storage' ); ?></label>
				<input type="date" name="start_date" value="<?php echo esc_attr( $start_date ); ?>">
			</div>
			
			<div class="filter-group">
				<label><?php esc_html_e( 'End Date', 'royal-storage' ); ?></label>
				<input type="date" name="end_date" value="<?php echo esc_attr( $end_date ); ?>">
			</div>
			
			<button type="submit" class="button button-primary"><?php esc_html_e( 'Generate Report', 'royal-storage' ); ?></button>
		</form>
	</div>

	<!-- Revenue Summary -->
	<div class="metrics-grid">
		<div class="revenue-card">
			<div class="revenue-icon">💰</div>
			<div class="revenue-content">
				<h3><?php esc_html_e( 'Total Revenue', 'royal-storage' ); ?></h3>
				<p class="revenue-value"><?php echo esc_html( number_format( $total_revenue, 2 ) ); ?> RSD</p>
				<span class="revenue-link"><?php echo esc_html( $start_date ); ?> - <?php echo esc_html( $end_date ); ?></span>
			</div>
		</div>
	</div>

	<div class="metrics-row">
		<!-- Revenue by Date -->
		<div class="royal-storage-widget">
			<h2><?php esc_html_e( 'Revenue Trend', 'royal-storage' ); ?></h2>
			<table class="wp-list-table widefat fixed striped">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Date', 'royal-storage' ); ?></th>
						<th><?php esc_html_e( 'Bookings', 'royal-storage' ); ?></th>
						<th><?php esc_html_e( 'Revenue', 'royal-storage' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php if ( ! empty( $revenue_report ) ) : ?>
						<?php foreach ( $revenue_report as $row ) : ?>
							<tr>
								<td><?php echo esc_html( $row->date ); ?></td>
								<td><strong><?php echo esc_html( $row->bookings ); ?></strong></td>
								<td><?php echo esc_html( number_format( $row->revenue, 2 ) ); ?> RSD</td>
							</tr>
						<?php endforeach; ?>
					<?php else : ?>
						<tr>
							<td colspan="3" class="no-customers"><?php esc_html_e( 'No data available.', 'royal-storage' ); ?></td>
						</tr>
					<?php endif; ?>
				</tbody>
			</table>
		</div>

		<!-- Payment Status Report -->
		<div class="royal-storage-widget">
			<h2><?php esc_html_e( 'Payment Breakdown', 'royal-storage' ); ?></h2>
			<table class="wp-list-table widefat fixed striped">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Status', 'royal-storage' ); ?></th>
						<th><?php esc_html_e( 'Count', 'royal-storage' ); ?></th>
						<th><?php esc_html_e( 'Total Amount', 'royal-storage' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php if ( ! empty( $payment_report ) ) : ?>
						<?php foreach ( $payment_report as $row ) : ?>
							<tr>
								<td>
									<span class="status-badge status-<?php echo esc_attr( $row->payment_status ); ?>">
										<?php echo esc_html( ucfirst( $row->payment_status ) ); ?>
									</span>
								</td>
								<td><strong><?php echo esc_html( $row->count ); ?></strong></td>
								<td><?php echo esc_html( number_format( $row->total, 2 ) ); ?> RSD</td>
							</tr>
						<?php endforeach; ?>
					<?php else : ?>
						<tr>
							<td colspan="3" class="no-customers"><?php esc_html_e( 'No data available.', 'royal-storage' ); ?></td>
						</tr>
					<?php endif; ?>
				</tbody>
			</table>
		</div>
	</div>

	<!-- Export -->
	<div class="dashboard-section" style="text-align: center;">
		<h2><?php esc_html_e( 'Export Data', 'royal-storage' ); ?></h2>
		<p class="rs-text-muted"><?php esc_html_e( 'Download the current report as a CSV file for further analysis.', 'royal-storage' ); ?></p>
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<input type="hidden" name="action" value="royal_storage_export_csv">
			<input type="hidden" name="start_date" value="<?php echo esc_attr( $start_date ); ?>">
			<input type="hidden" name="end_date" value="<?php echo esc_attr( $end_date ); ?>">
			<?php wp_nonce_field( 'royal_storage_export_csv', 'nonce' ); ?>
			<button type="submit" class="button button-primary">
				📥 <?php esc_html_e( 'Download CSV Report', 'royal-storage' ); ?>
			</button>
		</form>
	</div>
</div>
