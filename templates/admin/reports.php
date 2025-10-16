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
	<h1><?php esc_html_e( 'Reports', 'royal-storage' ); ?></h1>

	<!-- Date Filter -->
	<div class="report-filters">
		<form method="get" action="">
			<input type="hidden" name="page" value="royal-storage-reports">
			<label><?php esc_html_e( 'Start Date:', 'royal-storage' ); ?></label>
			<input type="date" name="start_date" value="<?php echo esc_attr( $start_date ); ?>">
			
			<label><?php esc_html_e( 'End Date:', 'royal-storage' ); ?></label>
			<input type="date" name="end_date" value="<?php echo esc_attr( $end_date ); ?>">
			
			<button type="submit" class="button button-primary"><?php esc_html_e( 'Filter', 'royal-storage' ); ?></button>
		</form>
	</div>

	<!-- Revenue Summary -->
	<div class="report-section">
		<h2><?php esc_html_e( 'Revenue Summary', 'royal-storage' ); ?></h2>
		<div class="revenue-summary">
			<div class="summary-card">
				<h3><?php esc_html_e( 'Total Revenue', 'royal-storage' ); ?></h3>
				<p class="summary-value"><?php echo esc_html( number_format( $total_revenue, 2 ) ); ?> RSD</p>
			</div>
			<div class="summary-card">
				<h3><?php esc_html_e( 'Period', 'royal-storage' ); ?></h3>
				<p class="summary-value"><?php echo esc_html( $start_date ); ?> - <?php echo esc_html( $end_date ); ?></p>
			</div>
		</div>
	</div>

	<!-- Revenue by Date -->
	<div class="report-section">
		<h2><?php esc_html_e( 'Revenue by Date', 'royal-storage' ); ?></h2>
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
							<td><?php echo esc_html( $row->bookings ); ?></td>
							<td><?php echo esc_html( number_format( $row->revenue, 2 ) ); ?> RSD</td>
						</tr>
					<?php endforeach; ?>
				<?php else : ?>
					<tr>
						<td colspan="3" style="text-align: center; padding: 20px;">
							<?php esc_html_e( 'No data available.', 'royal-storage' ); ?>
						</td>
					</tr>
				<?php endif; ?>
			</tbody>
		</table>
	</div>

	<!-- Payment Status Report -->
	<div class="report-section">
		<h2><?php esc_html_e( 'Payment Status', 'royal-storage' ); ?></h2>
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
								<span class="payment-badge payment-<?php echo esc_attr( $row->payment_status ); ?>">
									<?php echo esc_html( ucfirst( $row->payment_status ) ); ?>
								</span>
							</td>
							<td><?php echo esc_html( $row->count ); ?></td>
							<td><?php echo esc_html( number_format( $row->total, 2 ) ); ?> RSD</td>
						</tr>
					<?php endforeach; ?>
				<?php else : ?>
					<tr>
						<td colspan="3" style="text-align: center; padding: 20px;">
							<?php esc_html_e( 'No data available.', 'royal-storage' ); ?>
						</td>
					</tr>
				<?php endif; ?>
			</tbody>
		</table>
	</div>

	<!-- Export -->
	<div class="report-section">
		<h2><?php esc_html_e( 'Export', 'royal-storage' ); ?></h2>
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<input type="hidden" name="action" value="royal_storage_export_csv">
			<input type="hidden" name="start_date" value="<?php echo esc_attr( $start_date ); ?>">
			<input type="hidden" name="end_date" value="<?php echo esc_attr( $end_date ); ?>">
			<?php wp_nonce_field( 'royal_storage_export_csv', 'nonce' ); ?>
			<button type="submit" class="button button-primary">
				<?php esc_html_e( 'Export to CSV', 'royal-storage' ); ?>
			</button>
		</form>
	</div>
</div>

<style>
	.report-filters {
		background: white;
		padding: 20px;
		margin: 20px 0;
		border-radius: 5px;
		box-shadow: 0 1px 3px rgba(0,0,0,0.1);
	}

	.report-filters form {
		display: flex;
		gap: 15px;
		align-items: center;
		flex-wrap: wrap;
	}

	.report-filters label {
		font-weight: bold;
	}

	.report-filters input[type="date"] {
		padding: 5px 10px;
		border: 1px solid #ddd;
		border-radius: 3px;
	}

	.report-section {
		background: white;
		padding: 20px;
		margin: 20px 0;
		border-radius: 5px;
		box-shadow: 0 1px 3px rgba(0,0,0,0.1);
	}

	.report-section h2 {
		margin-top: 0;
		margin-bottom: 20px;
		border-bottom: 2px solid #f0f0f0;
		padding-bottom: 10px;
	}

	.revenue-summary {
		display: grid;
		grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
		gap: 15px;
	}

	.summary-card {
		padding: 20px;
		background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
		color: white;
		border-radius: 5px;
		text-align: center;
	}

	.summary-card h3 {
		margin: 0 0 10px 0;
		font-size: 14px;
	}

	.summary-value {
		margin: 0;
		font-size: 24px;
		font-weight: bold;
	}

	.payment-badge {
		display: inline-block;
		padding: 5px 10px;
		border-radius: 3px;
		font-size: 12px;
		font-weight: bold;
		color: white;
	}

	.payment-pending,
	.payment-unpaid {
		background-color: #e74c3c;
	}

	.payment-paid {
		background-color: #27ae60;
	}

	.payment-failed {
		background-color: #c0392b;
	}

	.payment-refunded {
		background-color: #95a5a6;
	}
</style>

