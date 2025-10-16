<?php
/**
 * Expiry Reminder Email Template
 *
 * @package RoyalStorage
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="UTF-8">
	<title><?php esc_html_e( 'Booking Expiry Reminder', 'royal-storage' ); ?></title>
	<style>
		body {
			font-family: Arial, sans-serif;
			line-height: 1.6;
			color: #333;
		}
		.container {
			max-width: 600px;
			margin: 0 auto;
			padding: 20px;
			border: 1px solid #ddd;
			border-radius: 5px;
		}
		.header {
			background-color: #f39c12;
			color: white;
			padding: 20px;
			text-align: center;
			border-radius: 5px 5px 0 0;
		}
		.content {
			padding: 20px;
		}
		.booking-details {
			background-color: #f5f5f5;
			padding: 15px;
			border-radius: 5px;
			margin: 20px 0;
		}
		.booking-details p {
			margin: 10px 0;
		}
		.label {
			font-weight: bold;
			color: #2c3e50;
		}
		.footer {
			background-color: #f5f5f5;
			padding: 20px;
			text-align: center;
			border-radius: 0 0 5px 5px;
			font-size: 12px;
			color: #666;
		}
		.warning-badge {
			background-color: #f39c12;
			color: white;
			padding: 10px 20px;
			border-radius: 5px;
			display: inline-block;
			margin: 20px 0;
		}
		.action-button {
			background-color: #3498db;
			color: white;
			padding: 10px 20px;
			text-decoration: none;
			border-radius: 5px;
			display: inline-block;
			margin: 20px 0;
		}
	</style>
</head>
<body>
	<div class="container">
		<div class="header">
			<h1><?php esc_html_e( 'Booking Expiry Reminder', 'royal-storage' ); ?></h1>
		</div>

		<div class="content">
			<p><?php esc_html_e( 'Dear Customer,', 'royal-storage' ); ?></p>

			<p><?php esc_html_e( 'This is a reminder that your booking will expire soon.', 'royal-storage' ); ?></p>

			<div class="warning-badge">
				⚠ <?php esc_html_e( 'Expiry in 7 Days', 'royal-storage' ); ?>
			</div>

			<div class="booking-details">
				<p>
					<span class="label"><?php esc_html_e( 'Booking ID:', 'royal-storage' ); ?></span>
					{booking_id}
				</p>
				<p>
					<span class="label"><?php esc_html_e( 'Unit/Space:', 'royal-storage' ); ?></span>
					{unit_name}
				</p>
				<p>
					<span class="label"><?php esc_html_e( 'Expiry Date:', 'royal-storage' ); ?></span>
					{end_date}
				</p>
			</div>

			<p><?php esc_html_e( 'To renew your booking or make any changes, please log in to your account or contact us.', 'royal-storage' ); ?></p>

			<a href="{renewal_link}" class="action-button"><?php esc_html_e( 'Renew Booking', 'royal-storage' ); ?></a>

			<p><?php esc_html_e( 'If you have any questions, please contact us:', 'royal-storage' ); ?></p>
			<p>
				<?php echo esc_html( get_option( 'royal_storage_business_phone' ) ); ?><br>
				<?php echo esc_html( get_option( 'royal_storage_business_email' ) ); ?>
			</p>

			<p><?php esc_html_e( 'Best regards,', 'royal-storage' ); ?><br>
			<?php echo esc_html( get_option( 'royal_storage_business_name', 'Royal Storage' ) ); ?></p>
		</div>

		<div class="footer">
			<p><?php esc_html_e( 'This is an automated email. Please do not reply to this message.', 'royal-storage' ); ?></p>
		</div>
	</div>
</body>
</html>

