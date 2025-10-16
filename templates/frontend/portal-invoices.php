<?php
/**
 * Portal Invoices Template
 *
 * @package RoyalStorage
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div class="portal-invoices">
	<h2><?php esc_html_e( 'Invoices', 'royal-storage' ); ?></h2>

	<?php if ( ! empty( $customer_invoices ) ) : ?>
		<table class="invoices-table">
			<thead>
				<tr>
					<th><?php esc_html_e( 'Invoice #', 'royal-storage' ); ?></th>
					<th><?php esc_html_e( 'Date', 'royal-storage' ); ?></th>
					<th><?php esc_html_e( 'Due Date', 'royal-storage' ); ?></th>
					<th><?php esc_html_e( 'Amount', 'royal-storage' ); ?></th>
					<th><?php esc_html_e( 'Status', 'royal-storage' ); ?></th>
					<th><?php esc_html_e( 'Payment', 'royal-storage' ); ?></th>
					<th><?php esc_html_e( 'Actions', 'royal-storage' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $customer_invoices as $invoice ) : ?>
					<tr>
						<td><?php echo esc_html( $invoice->invoice_number ); ?></td>
						<td><?php echo esc_html( $invoice->created_at ); ?></td>
						<td><?php echo esc_html( $invoice->due_date ); ?></td>
						<td><?php echo esc_html( number_format( $invoice->total_amount, 2 ) ); ?> RSD</td>
						<td>
							<span class="invoice-status status-<?php echo esc_attr( $invoice->status ); ?>">
								<?php echo esc_html( \RoyalStorage\Frontend\Invoices::get_status_label( $invoice->status ) ); ?>
							</span>
						</td>
						<td>
							<span class="payment-status payment-<?php echo esc_attr( $invoice->payment_status ); ?>">
								<?php echo esc_html( \RoyalStorage\Frontend\Invoices::get_payment_status_label( $invoice->payment_status ) ); ?>
							</span>
						</td>
						<td>
							<div class="invoice-actions">
								<button class="btn btn-small btn-primary download-invoice" data-invoice-id="<?php echo esc_attr( $invoice->id ); ?>">
									<?php esc_html_e( 'Download', 'royal-storage' ); ?>
								</button>
								<?php if ( 'unpaid' === $invoice->payment_status ) : ?>
									<button class="btn btn-small btn-success pay-invoice" data-invoice-id="<?php echo esc_attr( $invoice->id ); ?>">
										<?php esc_html_e( 'Pay', 'royal-storage' ); ?>
									</button>
								<?php endif; ?>
							</div>
						</td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	<?php else : ?>
		<div class="no-invoices">
			<p><?php esc_html_e( 'You have no invoices yet.', 'royal-storage' ); ?></p>
		</div>
	<?php endif; ?>
</div>

<style>
	.portal-invoices {
		padding: 20px;
	}

	.portal-invoices h2 {
		margin-top: 0;
	}

	.invoices-table {
		width: 100%;
		border-collapse: collapse;
		background: white;
		border-radius: 8px;
		overflow: hidden;
		box-shadow: 0 2px 8px rgba(0,0,0,0.1);
	}

	.invoices-table thead {
		background: #f5f5f5;
		border-bottom: 2px solid #e0e0e0;
	}

	.invoices-table th {
		padding: 15px;
		text-align: left;
		font-weight: bold;
		color: #333;
	}

	.invoices-table td {
		padding: 15px;
		border-bottom: 1px solid #f0f0f0;
	}

	.invoices-table tbody tr:hover {
		background: #f9f9f9;
	}

	.invoice-status {
		display: inline-block;
		padding: 5px 10px;
		border-radius: 3px;
		font-size: 12px;
		font-weight: bold;
		color: white;
	}

	.status-draft {
		background: #95a5a6;
	}

	.status-sent {
		background: #3498db;
	}

	.status-viewed {
		background: #2980b9;
	}

	.status-paid {
		background: #27ae60;
	}

	.status-overdue {
		background: #e74c3c;
	}

	.status-cancelled {
		background: #7f8c8d;
	}

	.payment-status {
		display: inline-block;
		padding: 5px 10px;
		border-radius: 3px;
		font-size: 12px;
		font-weight: bold;
		color: white;
	}

	.payment-paid {
		background: #27ae60;
	}

	.payment-unpaid {
		background: #e74c3c;
	}

	.payment-pending {
		background: #f39c12;
	}

	.payment-failed {
		background: #c0392b;
	}

	.payment-refunded {
		background: #95a5a6;
	}

	.invoice-actions {
		display: flex;
		gap: 5px;
	}

	.btn {
		padding: 6px 12px;
		border: none;
		border-radius: 5px;
		cursor: pointer;
		text-decoration: none;
		font-weight: bold;
		font-size: 12px;
		display: inline-block;
		transition: all 0.3s ease;
	}

	.btn-small {
		padding: 5px 10px;
		font-size: 11px;
	}

	.btn-primary {
		background: #667eea;
		color: white;
	}

	.btn-primary:hover {
		background: #5568d3;
	}

	.btn-success {
		background: #27ae60;
		color: white;
	}

	.btn-success:hover {
		background: #229954;
	}

	.no-invoices {
		background: white;
		padding: 40px;
		border-radius: 8px;
		text-align: center;
		box-shadow: 0 2px 8px rgba(0,0,0,0.1);
	}

	.no-invoices p {
		margin: 0;
		color: #666;
	}

	@media (max-width: 768px) {
		.invoices-table {
			font-size: 12px;
		}

		.invoices-table th,
		.invoices-table td {
			padding: 10px;
		}

		.invoice-actions {
			flex-direction: column;
		}
	}
</style>

