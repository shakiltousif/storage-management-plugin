<?php
/**
 * Booking Confirmation Email Template
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
	<title><?php esc_html_e( 'Booking Confirmation', 'royal-storage' ); ?></title>
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
			background-color: #2c3e50;
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
		.access-code {
			background-color: #27ae60;
			color: white;
			padding: 15px;
			text-align: center;
			border-radius: 5px;
			font-size: 24px;
			font-weight: bold;
			margin: 20px 0;
		}
	</style>
</head>
<body>
	<div class="container">
		<div class="header">
			<h1><?php esc_html_e( 'Booking Confirmation', 'royal-storage' ); ?></h1>
		</div>

		<div class="content">
			<p><?php esc_html_e( 'Dear Customer,', 'royal-storage' ); ?></p>

			<p><?php esc_html_e( 'Thank you for your booking with Royal Storage. Your reservation has been confirmed.', 'royal-storage' ); ?></p>

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
					<span class="label"><?php esc_html_e( 'Check-in Date:', 'royal-storage' ); ?></span>
					{start_date}
				</p>
				<p>
					<span class="label"><?php esc_html_e( 'Check-out Date:', 'royal-storage' ); ?></span>
					{end_date}
				</p>
				<p>
					<span class="label"><?php esc_html_e( 'Total Amount:', 'royal-storage' ); ?></span>
					{total_price} RSD
				</p>
			</div>

			<p><?php esc_html_e( 'Your Access Code:', 'royal-storage' ); ?></p>
			<div class="access-code">{access_code}</div>

			<p><?php esc_html_e( 'Please keep this code safe. You will need it to access your storage unit or parking space.', 'royal-storage' ); ?></p>

			<p><?php esc_html_e( 'If you have any questions, please contact us at:', 'royal-storage' ); ?></p>
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

