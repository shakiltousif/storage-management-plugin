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
	<h2 style="margin-bottom: 2rem;"><?php esc_html_e( 'Invoices', 'royal-storage' ); ?></h2>

	<?php if ( ! empty( $customer_invoices ) ) : ?>
		<div style="background: #fff; border-radius: var(--rs-radius-md); border: 1px solid var(--rs-border); overflow: hidden;">
			<table class="invoices-table">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Invoice #', 'royal-storage' ); ?></th>
						<th><?php esc_html_e( 'Date', 'royal-storage' ); ?></th>
						<th><?php esc_html_e( 'Amount', 'royal-storage' ); ?></th>
						<th><?php esc_html_e( 'Status', 'royal-storage' ); ?></th>
						<th style="text-align: right;"><?php esc_html_e( 'Actions', 'royal-storage' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $customer_invoices as $invoice ) : ?>
						<tr>
							<td style="font-weight: 700; color: var(--rs-text-main);"><?php echo esc_html( $invoice->invoice_number ); ?></td>
							<td class="rs-text-muted"><?php echo esc_html( $invoice->created_at ); ?></td>
							<td style="font-weight: 700;"><?php echo esc_html( number_format( $invoice->total_amount, 2 ) ); ?> RSD</td>
							<td>
								<span class="status-badge status-<?php echo esc_attr( $invoice->status ); ?>">
									<?php echo esc_html( \RoyalStorage\Frontend\Invoices::get_status_label( $invoice->status ) ); ?>
								</span>
							</td>
							<td style="text-align: right;">
								<div class="invoice-actions" style="display: inline-flex; gap: 0.5rem; justify-content: flex-end;">
									<button class="royal-storage-btn download-invoice" data-invoice-id="<?php echo esc_attr( $invoice->id ); ?>" style="width: auto; padding: 0.4rem 1rem; font-size: 0.75rem;">
										📥 <?php esc_html_e( 'PDF', 'royal-storage' ); ?>
									</button>
									<?php if ( 'unpaid' === $invoice->payment_status ) : ?>
										<button class="royal-storage-btn pay-invoice" data-invoice-id="<?php echo esc_attr( $invoice->id ); ?>" style="width: auto; padding: 0.4rem 1rem; font-size: 0.75rem; background: var(--rs-success);">
											💳 <?php esc_html_e( 'Pay', 'royal-storage' ); ?>
										</button>
									<?php endif; ?>
								</div>
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		</div>
	<?php else : ?>
		<div class="no-bookings" style="text-align: center; padding: 4rem 2rem; background: var(--rs-bg-main); border-radius: var(--rs-radius-lg); border: 2px dashed var(--rs-border);">
			<p style="font-size: 1.125rem;"><?php esc_html_e( 'You have no invoices yet.', 'royal-storage' ); ?></p>
		</div>
	<?php endif; ?>
</div>
