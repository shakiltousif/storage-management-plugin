<?php
/**
 * Overdue Reminder Email Template
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
	<title><?php esc_html_e( 'Overdue Payment Reminder', 'royal-storage' ); ?></title>
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
			background-color: #e74c3c;
			color: white;
			padding: 20px;
			text-align: center;
			border-radius: 5px 5px 0 0;
		}
		.content {
			padding: 20px;
		}
		.payment-details {
			background-color: #f5f5f5;
			padding: 15px;
			border-radius: 5px;
			margin: 20px 0;
		}
		.payment-details p {
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
		.alert-badge {
			background-color: #e74c3c;
			color: white;
			padding: 10px 20px;
			border-radius: 5px;
			display: inline-block;
			margin: 20px 0;
		}
		.action-button {
			background-color: #27ae60;
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
			<h1><?php esc_html_e( 'Overdue Payment Reminder', 'royal-storage' ); ?></h1>
		</div>

		<div class="content">
			<p><?php esc_html_e( 'Dear Customer,', 'royal-storage' ); ?></p>

			<p><?php esc_html_e( 'Your booking has expired and payment is now overdue. Please settle your account immediately to avoid additional charges.', 'royal-storage' ); ?></p>

			<div class="alert-badge">
				🚨 <?php esc_html_e( 'Payment Overdue', 'royal-storage' ); ?>
			</div>

			<div class="payment-details">
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
				<p>
					<span class="label"><?php esc_html_e( 'Outstanding Amount:', 'royal-storage' ); ?></span>
					{outstanding_amount} RSD
				</p>
				<p>
					<span class="label"><?php esc_html_e( 'Days Overdue:', 'royal-storage' ); ?></span>
					{days_overdue}
				</p>
				<p>
					<span class="label"><?php esc_html_e( 'Late Fee:', 'royal-storage' ); ?></span>
					{late_fee} RSD
				</p>
			</div>

			<p><?php esc_html_e( 'Please pay the outstanding amount immediately to avoid further action.', 'royal-storage' ); ?></p>

			<a href="{payment_link}" class="action-button"><?php esc_html_e( 'Pay Now', 'royal-storage' ); ?></a>

			<p><?php esc_html_e( 'If you have already made the payment, please disregard this notice. If you have any questions, please contact us immediately:', 'royal-storage' ); ?></p>
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

