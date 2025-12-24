<?php
/**
 * Portal Bookings Template
 *
 * @package RoyalStorage
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div class="portal-bookings">
	<h2 style="margin-bottom: 2rem;"><?php esc_html_e( 'My Bookings', 'royal-storage' ); ?></h2>

	<?php if ( ! empty( $customer_bookings ) ) : ?>
		<div class="bookings-list">
			<?php foreach ( $customer_bookings as $booking ) : ?>
				<div class="booking-card">
					<div class="booking-header">
						<h3 style="margin: 0;"><?php esc_html_e( 'Booking #', 'royal-storage' ); ?><?php echo esc_html( $booking->id ); ?></h3>
						<span class="status-badge status-<?php echo esc_attr( $booking->status ); ?>">
							<?php echo esc_html( \RoyalStorage\Frontend\Bookings::get_status_label( $booking->status ) ); ?>
						</span>
					</div>

					<div class="booking-details" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem; margin: 1.5rem 0;">
						<div class="detail-item">
							<span class="rs-text-muted" style="display: block; font-size: 0.75rem; text-transform: uppercase; font-weight: 700;"><?php esc_html_e( 'Unit/Space', 'royal-storage' ); ?></span>
							<span style="font-weight: 600;">
								<?php
								if ( $booking->unit_id > 0 ) {
									echo esc_html( ucfirst( $booking->unit_type ) . ' #' . $booking->unit_id );
								} else {
									echo esc_html( 'N/A' );
								}
								?>
							</span>
						</div>

						<div class="detail-item">
							<span class="rs-text-muted" style="display: block; font-size: 0.75rem; text-transform: uppercase; font-weight: 700;"><?php esc_html_e( 'Duration', 'royal-storage' ); ?></span>
							<span style="font-weight: 600;"><?php echo esc_html( $booking->start_date ); ?> - <?php echo esc_html( $booking->end_date ); ?></span>
						</div>

						<div class="detail-item">
							<span class="rs-text-muted" style="display: block; font-size: 0.75rem; text-transform: uppercase; font-weight: 700;"><?php esc_html_e( 'Total Price', 'royal-storage' ); ?></span>
							<span style="font-weight: 800; color: var(--rs-primary);"><?php echo esc_html( number_format( $booking->total_price, 2 ) ); ?> RSD</span>
						</div>

						<div class="detail-item">
							<span class="rs-text-muted" style="display: block; font-size: 0.75rem; text-transform: uppercase; font-weight: 700;"><?php esc_html_e( 'Payment', 'royal-storage' ); ?></span>
							<span class="status-badge status-<?php echo esc_attr( $booking->payment_status ); ?>" style="margin-top: 0.25rem;">
								<?php echo esc_html( \RoyalStorage\Frontend\Bookings::get_payment_status_label( $booking->payment_status ) ); ?>
							</span>
						</div>
					</div>

					<div class="booking-actions" style="display: flex; gap: 0.75rem; justify-content: flex-end; border-top: 1px solid var(--rs-border); pt: 1.25rem; margin-top: 1.25rem; padding-top: 1.25rem;">
						<?php if ( 'active' === $booking->status || 'confirmed' === $booking->status ) : ?>
							<button class="royal-storage-btn renew-booking" data-booking-id="<?php echo esc_attr( $booking->id ); ?>" style="width: auto; padding: 0.5rem 1.25rem; font-size: 0.875rem;">
								<?php esc_html_e( 'Renew', 'royal-storage' ); ?>
							</button>
							<button class="royal-storage-btn royal-storage-btn-secondary cancel-booking" data-booking-id="<?php echo esc_attr( $booking->id ); ?>" style="width: auto; padding: 0.5rem 1.25rem; font-size: 0.875rem;">
								<?php esc_html_e( 'Cancel', 'royal-storage' ); ?>
							</button>
						<?php endif; ?>
						<?php if ( 'unpaid' === $booking->payment_status ) : ?>
							<button class="royal-storage-btn pay-now-btn" data-booking-id="<?php echo esc_attr( $booking->id ); ?>" data-amount="<?php echo esc_attr( $booking->total_price ); ?>" style="width: auto; padding: 0.5rem 1.25rem; font-size: 0.875rem; background: var(--rs-success);">
								<?php esc_html_e( 'Pay Now', 'royal-storage' ); ?>
							</button>
						<?php endif; ?>
					</div>
				</div>
			<?php endforeach; ?>
		</div>
	<?php else : ?>
		<div class="no-bookings" style="text-align: center; padding: 4rem 2rem; background: var(--rs-bg-main); border-radius: var(--rs-radius-lg); border: 2px dashed var(--rs-border);">
			<p style="font-size: 1.125rem; margin-bottom: 1.5rem;"><?php esc_html_e( 'You have no bookings yet.', 'royal-storage' ); ?></p>
			<a href="<?php echo esc_url( home_url( '/booking/' ) ); ?>" class="royal-storage-btn" style="width: auto;">
				<?php esc_html_e( 'Create your first booking', 'royal-storage' ); ?>
			</a>
		</div>
	<?php endif; ?>
</div>

<script>
jQuery(document).ready(function($) {
	$('.pay-now-btn').on('click', function(e) {
		e.preventDefault();
		const $btn = $(this);
		const bookingId = $btn.data('booking-id');
		const amount = $btn.data('amount');
		$btn.prop('disabled', true).text('Processing...');
		$.ajax({
			url: '<?php echo admin_url('admin-ajax.php'); ?>',
			type: 'POST',
			data: {
				action: 'royal_storage_process_payment',
				nonce: '<?php echo wp_create_nonce('royal_storage_payment'); ?>',
				booking_id: bookingId,
				amount: amount,
				payment_method: 'card'
			},
			success: function(response) {
				if (response.success && response.data.redirect && response.data.checkout_url) {
					window.location.href = response.data.checkout_url;
				} else {
					alert('Failed to process payment. Please try again.');
					$btn.prop('disabled', false).text('Pay Now');
				}
			},
			error: function() {
				alert('Failed to process payment. Please try again.');
				$btn.prop('disabled', false).text('Pay Now');
			}
		});
	});
});
</script>
