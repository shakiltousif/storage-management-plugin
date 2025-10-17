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
	<h2><?php esc_html_e( 'My Bookings', 'royal-storage' ); ?></h2>

	<?php if ( ! empty( $customer_bookings ) ) : ?>
		<div class="bookings-list">
			<?php foreach ( $customer_bookings as $booking ) : ?>
				<div class="booking-card">
					<div class="booking-header">
						<h3><?php esc_html_e( 'Booking #', 'royal-storage' ); ?><?php echo esc_html( $booking->id ); ?></h3>
						<span class="booking-status status-<?php echo esc_attr( $booking->status ); ?>">
							<?php echo esc_html( \RoyalStorage\Frontend\Bookings::get_status_label( $booking->status ) ); ?>
						</span>
					</div>

					<div class="booking-details">
						<div class="detail-row">
							<span class="label"><?php esc_html_e( 'Unit/Space:', 'royal-storage' ); ?></span>
							<span class="value">
								<?php
								if ( $booking->unit_id > 0 ) {
									echo esc_html( ucfirst( $booking->unit_type ) . ' #' . $booking->unit_id );
								} else {
									echo esc_html( 'N/A' );
								}
								?>
							</span>
						</div>

						<div class="detail-row">
							<span class="label"><?php esc_html_e( 'Start Date:', 'royal-storage' ); ?></span>
							<span class="value"><?php echo esc_html( $booking->start_date ); ?></span>
						</div>

						<div class="detail-row">
							<span class="label"><?php esc_html_e( 'End Date:', 'royal-storage' ); ?></span>
							<span class="value"><?php echo esc_html( $booking->end_date ); ?></span>
						</div>

						<div class="detail-row">
							<span class="label"><?php esc_html_e( 'Total Price:', 'royal-storage' ); ?></span>
							<span class="value"><?php echo esc_html( number_format( $booking->total_price, 2 ) ); ?> RSD</span>
						</div>

						<div class="detail-row">
							<span class="label"><?php esc_html_e( 'Payment Status:', 'royal-storage' ); ?></span>
							<span class="payment-status payment-<?php echo esc_attr( $booking->payment_status ); ?>">
								<?php echo esc_html( \RoyalStorage\Frontend\Bookings::get_payment_status_label( $booking->payment_status ) ); ?>
							</span>
						</div>
					</div>

					<div class="booking-actions">
						<?php if ( 'active' === $booking->status || 'confirmed' === $booking->status ) : ?>
							<button class="btn btn-small btn-primary renew-booking" data-booking-id="<?php echo esc_attr( $booking->id ); ?>">
								<?php esc_html_e( 'Renew', 'royal-storage' ); ?>
							</button>
							<button class="btn btn-small btn-danger cancel-booking" data-booking-id="<?php echo esc_attr( $booking->id ); ?>">
								<?php esc_html_e( 'Cancel', 'royal-storage' ); ?>
							</button>
						<?php endif; ?>
						<?php if ( 'unpaid' === $booking->payment_status ) : ?>
							<a href="<?php echo esc_url( add_query_arg( 'booking_id', $booking->id, home_url( '/checkout/' ) ) ); ?>" class="btn btn-small btn-success">
								<?php esc_html_e( 'Pay Now', 'royal-storage' ); ?>
							</a>
						<?php endif; ?>
					</div>
				</div>
			<?php endforeach; ?>
		</div>
	<?php else : ?>
		<div class="no-bookings">
			<p><?php esc_html_e( 'You have no bookings yet.', 'royal-storage' ); ?></p>
			<a href="<?php echo esc_url( home_url( '/booking/' ) ); ?>" class="btn btn-primary">
				<?php esc_html_e( 'Create a Booking', 'royal-storage' ); ?>
			</a>
		</div>
	<?php endif; ?>
</div>

<style>
	.portal-bookings {
		padding: 20px;
	}

	.portal-bookings h2 {
		margin-top: 0;
	}

	.bookings-list {
		display: grid;
		gap: 20px;
	}

	.booking-card {
		background: white;
		border: 1px solid #e0e0e0;
		border-radius: 8px;
		padding: 20px;
		box-shadow: 0 2px 8px rgba(0,0,0,0.1);
	}

	.booking-header {
		display: flex;
		justify-content: space-between;
		align-items: center;
		margin-bottom: 15px;
		padding-bottom: 15px;
		border-bottom: 1px solid #f0f0f0;
	}

	.booking-header h3 {
		margin: 0;
	}

	.booking-status {
		display: inline-block;
		padding: 5px 10px;
		border-radius: 3px;
		font-size: 12px;
		font-weight: bold;
		color: white;
	}

	.status-pending {
		background: #f39c12;
	}

	.status-confirmed {
		background: #3498db;
	}

	.status-active {
		background: #27ae60;
	}

	.status-cancelled {
		background: #e74c3c;
	}

	.booking-details {
		margin-bottom: 15px;
	}

	.detail-row {
		display: flex;
		justify-content: space-between;
		padding: 8px 0;
		border-bottom: 1px solid #f5f5f5;
	}

	.detail-row .label {
		font-weight: bold;
		color: #666;
	}

	.detail-row .value {
		color: #333;
	}

	.payment-status {
		display: inline-block;
		padding: 3px 8px;
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

	.booking-actions {
		display: flex;
		gap: 10px;
		flex-wrap: wrap;
		margin-top: 15px;
	}

	.btn {
		padding: 8px 15px;
		border: none;
		border-radius: 5px;
		cursor: pointer;
		text-decoration: none;
		font-weight: bold;
		display: inline-block;
		transition: all 0.3s ease;
	}

	.btn-small {
		padding: 6px 12px;
		font-size: 12px;
	}

	.btn-primary {
		background: #667eea;
		color: white;
	}

	.btn-primary:hover {
		background: #5568d3;
	}

	.btn-danger {
		background: #e74c3c;
		color: white;
	}

	.btn-danger:hover {
		background: #c0392b;
	}

	.btn-success {
		background: #27ae60;
		color: white;
	}

	.btn-success:hover {
		background: #229954;
	}

	.no-bookings {
		background: white;
		padding: 40px;
		border-radius: 8px;
		text-align: center;
		box-shadow: 0 2px 8px rgba(0,0,0,0.1);
	}

	.no-bookings p {
		margin: 0 0 20px 0;
		color: #666;
	}
</style>

