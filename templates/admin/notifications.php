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

	<div class="notification-stats">
		<div class="stat-card">
			<h3><?php esc_html_e( 'Pending Notifications', 'royal-storage' ); ?></h3>
			<p class="stat-value"><?php echo esc_html( count( $pending ) ); ?></p>
		</div>
	</div>

	<h2><?php esc_html_e( 'Pending Notifications', 'royal-storage' ); ?></h2>

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
							<span class="notification-badge notification-<?php echo esc_attr( $notification->type ); ?>">
								<?php echo esc_html( ucfirst( $notification->type ) ); ?>
							</span>
						</td>
						<td>
							<?php if ( $customer ) : ?>
								<?php echo esc_html( $customer->display_name ); ?>
							<?php else : ?>
								<?php esc_html_e( 'Unknown', 'royal-storage' ); ?>
							<?php endif; ?>
						</td>
						<td>
							<?php if ( 'expiry' === $notification->type ) : ?>
								<?php esc_html_e( 'Expires on:', 'royal-storage' ); ?> <?php echo esc_html( $booking->end_date ); ?>
							<?php elseif ( 'overdue' === $notification->type ) : ?>
								<?php esc_html_e( 'Expired on:', 'royal-storage' ); ?> <?php echo esc_html( $booking->end_date ); ?>
								<br>
								<?php esc_html_e( 'Amount:', 'royal-storage' ); ?> <?php echo esc_html( number_format( $booking->total_price, 2 ) ); ?> RSD
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
		<div class="notice notice-info">
			<p><?php esc_html_e( 'No pending notifications.', 'royal-storage' ); ?></p>
		</div>
	<?php endif; ?>

	<h2><?php esc_html_e( 'Notification Settings', 'royal-storage' ); ?></h2>
	<div class="notification-settings">
		<p><?php esc_html_e( 'Automated notifications are scheduled to run daily:', 'royal-storage' ); ?></p>
		<ul>
			<li><?php esc_html_e( 'Expiry reminders: 7 days before booking expires', 'royal-storage' ); ?></li>
			<li><?php esc_html_e( 'Overdue reminders: Daily for overdue bookings', 'royal-storage' ); ?></li>
		</ul>
		<p><?php esc_html_e( 'You can manually send notifications using the "Send Now" button above.', 'royal-storage' ); ?></p>
	</div>
</div>

<style>
	.notification-stats {
		display: grid;
		grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
		gap: 15px;
		margin: 20px 0;
	}

	.stat-card {
		background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
		color: white;
		padding: 20px;
		border-radius: 5px;
		text-align: center;
	}

	.stat-card h3 {
		margin: 0 0 10px 0;
		font-size: 14px;
	}

	.stat-value {
		margin: 0;
		font-size: 32px;
		font-weight: bold;
	}

	.notification-badge {
		display: inline-block;
		padding: 5px 10px;
		border-radius: 3px;
		font-size: 12px;
		font-weight: bold;
		color: white;
	}

	.notification-expiry {
		background-color: #f39c12;
	}

	.notification-overdue {
		background-color: #e74c3c;
	}

	.notification-settings {
		background: white;
		padding: 20px;
		border-radius: 5px;
		box-shadow: 0 1px 3px rgba(0,0,0,0.1);
	}

	.notification-settings ul {
		margin: 10px 0;
		padding-left: 20px;
	}

	.notification-settings li {
		margin: 5px 0;
	}
</style>

