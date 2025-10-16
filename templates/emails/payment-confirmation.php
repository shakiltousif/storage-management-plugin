<?php
/**
 * Payment Confirmation Email Template
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
	<title><?php esc_html_e( 'Payment Confirmation', 'royal-storage' ); ?></title>
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
			background-color: #27ae60;
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
		.success-badge {
			background-color: #27ae60;
			color: white;
			padding: 10px 20px;
			border-radius: 5px;
			display: inline-block;
			margin: 20px 0;
		}
	</style>
</head>
<body>
	<div class="container">
		<div class="header">
			<h1><?php esc_html_e( 'Payment Confirmed', 'royal-storage' ); ?></h1>
		</div>

		<div class="content">
			<p><?php esc_html_e( 'Dear Customer,', 'royal-storage' ); ?></p>

			<p><?php esc_html_e( 'Your payment has been successfully processed. Thank you for your business!', 'royal-storage' ); ?></p>

			<div class="success-badge">
				✓ <?php esc_html_e( 'Payment Successful', 'royal-storage' ); ?>
			</div>

			<div class="payment-details">
				<p>
					<span class="label"><?php esc_html_e( 'Booking ID:', 'royal-storage' ); ?></span>
					{booking_id}
				</p>
				<p>
					<span class="label"><?php esc_html_e( 'Payment Amount:', 'royal-storage' ); ?></span>
					{payment_amount} RSD
				</p>
				<p>
					<span class="label"><?php esc_html_e( 'Payment Date:', 'royal-storage' ); ?></span>
					{payment_date}
				</p>
				<p>
					<span class="label"><?php esc_html_e( 'Payment Method:', 'royal-storage' ); ?></span>
					{payment_method}
				</p>
				<p>
					<span class="label"><?php esc_html_e( 'Transaction ID:', 'royal-storage' ); ?></span>
					{transaction_id}
				</p>
			</div>

			<p><?php esc_html_e( 'Your booking is now confirmed and active. You can access your storage unit or parking space using your access code.', 'royal-storage' ); ?></p>

			<p><?php esc_html_e( 'Access Code:', 'royal-storage' ); ?> <strong>{access_code}</strong></p>

			<p><?php esc_html_e( 'If you have any questions or need assistance, please contact us:', 'royal-storage' ); ?></p>
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

