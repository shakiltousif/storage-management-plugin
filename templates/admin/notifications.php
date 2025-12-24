<?php
/**
 * Admin Notifications Template
 *
 * @package RoyalStorage
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$notifications = new \RoyalStorage\Admin\Notifications();
$pending = $notifications->get_pending_notifications();
?>

<div class="wrap royal-storage-admin">
	<h1><?php esc_html_e( 'Notifications Management', 'royal-storage' ); ?></h1>

	<?php if ( isset( $_GET['message'] ) && 'sent' === $_GET['message'] ) : ?>
		<div class="notice notice-success is-dismissible">
			<p><?php esc_html_e( 'Notification sent successfully!', 'royal-storage' ); ?></p>
		</div>
	<?php endif; ?>

	<div class="metrics-grid">
		<div class="metric-card">
			<div class="metric-icon">🔔</div>
			<div class="metric-content">
				<h3><?php esc_html_e( 'Pending Notifications', 'royal-storage' ); ?></h3>
				<p class="metric-value <?php echo count($pending) > 0 ? 'alert' : ''; ?>">
					<?php echo esc_html( count( $pending ) ); ?>
				</p>
			</div>
		</div>
	</div>

	<h2><?php esc_html_e( 'Pending Queue', 'royal-storage' ); ?></h2>

	<div class="royal-storage-widget">
		<?php if ( ! empty( $pending ) ) : ?>
			<table class="wp-list-table widefat fixed striped">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Booking ID', 'royal-storage' ); ?></th>
						<th><?php esc_html_e( 'Type', 'royal-storage' ); ?></th>
						<th><?php esc_html_e( 'Customer', 'royal-storage' ); ?></th>
						<th><?php esc_html_e( 'Details', 'royal-storage' ); ?></th>
						<th><?php esc_html_e( 'Actions', 'royal-storage' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $pending as $notification ) : ?>
						<?php
						global $wpdb;
						$bookings_table = $wpdb->prefix . 'royal_bookings';
						$booking = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $bookings_table WHERE id = %d", $notification->id ) );
						$customer = get_user_by( 'id', $booking->customer_id );
						?>
						<tr>
							<td><?php echo esc_html( $booking->id ); ?></td>
							<td>
								<span class="status-badge status-<?php echo esc_attr( $notification->type ); ?>">
									<?php echo esc_html( ucfirst( $notification->type ) ); ?>
								</span>
							</td>
							<td>
								<?php if ( $customer ) : ?>
									<strong><?php echo esc_html( $customer->display_name ); ?></strong>
								<?php else : ?>
									<?php esc_html_e( 'Unknown', 'royal-storage' ); ?>
								<?php endif; ?>
							</td>
							<td>
								<?php if ( 'expiry' === $notification->type ) : ?>
									<span class="rs-text-muted"><?php esc_html_e( 'Expires on:', 'royal-storage' ); ?></span> 
									<strong><?php echo esc_html( $booking->end_date ); ?></strong>
								<?php elseif ( 'overdue' === $notification->type ) : ?>
									<span class="rs-text-muted"><?php esc_html_e( 'Expired on:', 'royal-storage' ); ?></span> 
									<strong class="status-cancelled"><?php echo esc_html( $booking->end_date ); ?></strong>
									<br>
									<span class="rs-text-muted"><?php esc_html_e( 'Amount:', 'royal-storage' ); ?></span> 
									<strong><?php echo esc_html( number_format( $booking->total_price, 2 ) ); ?> RSD</strong>
								<?php endif; ?>
							</td>
							<td>
								<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="display: inline;">
									<input type="hidden" name="action" value="royal_storage_send_notification">
									<input type="hidden" name="booking_id" value="<?php echo esc_attr( $booking->id ); ?>">
									<input type="hidden" name="type" value="<?php echo esc_attr( $notification->type ); ?>">
									<?php wp_nonce_field( 'royal_storage_send_notification', 'nonce' ); ?>
									<button type="submit" class="button button-primary">
										<?php esc_html_e( 'Send Now', 'royal-storage' ); ?>
									</button>
								</form>
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		<?php else : ?>
			<p class="no-customers"><?php esc_html_e( 'No pending notifications.', 'royal-storage' ); ?></p>
		<?php endif; ?>
	</div>

	<h2><?php esc_html_e( 'Automation Settings', 'royal-storage' ); ?></h2>
	<div class="dashboard-section">
		<p><?php esc_html_e( 'Automated notifications are scheduled to run daily:', 'royal-storage' ); ?></p>
		<ul class="rs-list">
			<li><strong><?php esc_html_e( 'Expiry reminders:', 'royal-storage' ); ?></strong> <?php esc_html_e( '7 days before booking expires', 'royal-storage' ); ?></li>
			<li><strong><?php esc_html_e( 'Overdue reminders:', 'royal-storage' ); ?></strong> <?php esc_html_e( 'Daily for overdue bookings', 'royal-storage' ); ?></li>
		</ul>
		<p class="rs-text-muted"><?php esc_html_e( 'You can manually send notifications using the "Send Now" button above.', 'royal-storage' ); ?></p>
	</div>
</div>
