<?php
/**
 * Payment Settings Admin Class
 *
 * @package RoyalStorage\Admin
 * @since 1.0.0
 */

namespace RoyalStorage\Admin;

/**
 * Payment settings admin class
 */
class PaymentSettings {

	/**
	 * Constructor
	 */
	public function __construct() {
		add_action( 'admin_init', array( $this, 'register_settings' ) );
	}


	/**
	 * Register settings
	 *
	 * @return void
	 */
	public function register_settings() {
		// Payment Gateway Settings
		register_setting( 'royal_storage_payment', 'royal_storage_payment_enabled' );
		register_setting( 'royal_storage_payment', 'royal_storage_payment_gateway' );
		register_setting( 'royal_storage_payment', 'royal_storage_currency' );
		register_setting( 'royal_storage_payment', 'royal_storage_vat_rate' );
		register_setting( 'royal_storage_payment', 'royal_storage_min_payment' );
		register_setting( 'royal_storage_payment', 'royal_storage_max_payment' );

		// Stripe Settings
		register_setting( 'royal_storage_payment', 'royal_storage_stripe_publishable_key' );
		register_setting( 'royal_storage_payment', 'royal_storage_stripe_secret_key' );
		register_setting( 'royal_storage_payment', 'royal_storage_stripe_webhook_secret' );

		// PayPal Settings
		register_setting( 'royal_storage_payment', 'royal_storage_paypal_client_id' );
		register_setting( 'royal_storage_payment', 'royal_storage_paypal_client_secret' );
		register_setting( 'royal_storage_payment', 'royal_storage_paypal_sandbox' );
	}

	/**
	 * Render settings page
	 *
	 * @return void
	 */
	public function render_settings_page() {
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Payment Settings', 'royal-storage' ); ?></h1>
			
			<form method="post" action="options.php">
				<?php
				settings_fields( 'royal_storage_payment' );
				do_settings_sections( 'royal_storage_payment' );
				?>

				<table class="form-table">
					<tr>
						<th scope="row"><?php esc_html_e( 'Enable Payments', 'royal-storage' ); ?></th>
						<td>
							<label>
								<input type="checkbox" name="royal_storage_payment_enabled" value="yes" 
									<?php checked( get_option( 'royal_storage_payment_enabled', 'yes' ), 'yes' ); ?>>
								<?php esc_html_e( 'Enable payment processing', 'royal-storage' ); ?>
							</label>
						</td>
					</tr>

					<tr>
						<th scope="row"><?php esc_html_e( 'Payment Gateway', 'royal-storage' ); ?></th>
						<td>
							<select name="royal_storage_payment_gateway">
								<option value="stripe" <?php selected( get_option( 'royal_storage_payment_gateway', 'stripe' ), 'stripe' ); ?>>
									<?php esc_html_e( 'Stripe', 'royal-storage' ); ?>
								</option>
								<option value="paypal" <?php selected( get_option( 'royal_storage_payment_gateway', 'stripe' ), 'paypal' ); ?>>
									<?php esc_html_e( 'PayPal', 'royal-storage' ); ?>
								</option>
								<option value="bank_transfer" <?php selected( get_option( 'royal_storage_payment_gateway', 'stripe' ), 'bank_transfer' ); ?>>
									<?php esc_html_e( 'Bank Transfer', 'royal-storage' ); ?>
								</option>
							</select>
						</td>
					</tr>

					<tr>
						<th scope="row"><?php esc_html_e( 'Currency', 'royal-storage' ); ?></th>
						<td>
							<select name="royal_storage_currency">
								<option value="RSD" <?php selected( get_option( 'royal_storage_currency', 'RSD' ), 'RSD' ); ?>>
									RSD - Serbian Dinar
								</option>
								<option value="EUR" <?php selected( get_option( 'royal_storage_currency', 'RSD' ), 'EUR' ); ?>>
									EUR - Euro
								</option>
								<option value="USD" <?php selected( get_option( 'royal_storage_currency', 'RSD' ), 'USD' ); ?>>
									USD - US Dollar
								</option>
							</select>
						</td>
					</tr>

					<tr>
						<th scope="row"><?php esc_html_e( 'VAT Rate (%)', 'royal-storage' ); ?></th>
						<td>
							<input type="number" name="royal_storage_vat_rate" 
								value="<?php echo esc_attr( get_option( 'royal_storage_vat_rate', 20 ) ); ?>" 
								min="0" max="100" step="0.1">
							<p class="description"><?php esc_html_e( 'VAT rate for Serbian market (default: 20%)', 'royal-storage' ); ?></p>
						</td>
					</tr>

					<tr>
						<th scope="row"><?php esc_html_e( 'Minimum Payment', 'royal-storage' ); ?></th>
						<td>
							<input type="number" name="royal_storage_min_payment" 
								value="<?php echo esc_attr( get_option( 'royal_storage_min_payment', 0 ) ); ?>" 
								min="0" step="0.01">
							<p class="description"><?php esc_html_e( 'Minimum payment amount', 'royal-storage' ); ?></p>
						</td>
					</tr>

					<tr>
						<th scope="row"><?php esc_html_e( 'Maximum Payment', 'royal-storage' ); ?></th>
						<td>
							<input type="number" name="royal_storage_max_payment" 
								value="<?php echo esc_attr( get_option( 'royal_storage_max_payment', 999999 ) ); ?>" 
								min="0" step="0.01">
							<p class="description"><?php esc_html_e( 'Maximum payment amount', 'royal-storage' ); ?></p>
						</td>
					</tr>
				</table>

				<h2><?php esc_html_e( 'Stripe Settings', 'royal-storage' ); ?></h2>
				<table class="form-table">
					<tr>
						<th scope="row"><?php esc_html_e( 'Publishable Key', 'royal-storage' ); ?></th>
						<td>
							<input type="text" name="royal_storage_stripe_publishable_key" 
								value="<?php echo esc_attr( get_option( 'royal_storage_stripe_publishable_key' ) ); ?>" 
								class="regular-text">
							<p class="description"><?php esc_html_e( 'Your Stripe publishable key', 'royal-storage' ); ?></p>
						</td>
					</tr>

					<tr>
						<th scope="row"><?php esc_html_e( 'Secret Key', 'royal-storage' ); ?></th>
						<td>
							<input type="password" name="royal_storage_stripe_secret_key" 
								value="<?php echo esc_attr( get_option( 'royal_storage_stripe_secret_key' ) ); ?>" 
								class="regular-text">
							<p class="description"><?php esc_html_e( 'Your Stripe secret key', 'royal-storage' ); ?></p>
						</td>
					</tr>

					<tr>
						<th scope="row"><?php esc_html_e( 'Webhook Secret', 'royal-storage' ); ?></th>
						<td>
							<input type="password" name="royal_storage_stripe_webhook_secret" 
								value="<?php echo esc_attr( get_option( 'royal_storage_stripe_webhook_secret' ) ); ?>" 
								class="regular-text">
							<p class="description"><?php esc_html_e( 'Stripe webhook secret for payment confirmation', 'royal-storage' ); ?></p>
						</td>
					</tr>
				</table>

				<h2><?php esc_html_e( 'PayPal Settings', 'royal-storage' ); ?></h2>
				<table class="form-table">
					<tr>
						<th scope="row"><?php esc_html_e( 'Client ID', 'royal-storage' ); ?></th>
						<td>
							<input type="text" name="royal_storage_paypal_client_id" 
								value="<?php echo esc_attr( get_option( 'royal_storage_paypal_client_id' ) ); ?>" 
								class="regular-text">
							<p class="description"><?php esc_html_e( 'Your PayPal client ID', 'royal-storage' ); ?></p>
						</td>
					</tr>

					<tr>
						<th scope="row"><?php esc_html_e( 'Client Secret', 'royal-storage' ); ?></th>
						<td>
							<input type="password" name="royal_storage_paypal_client_secret" 
								value="<?php echo esc_attr( get_option( 'royal_storage_paypal_client_secret' ) ); ?>" 
								class="regular-text">
							<p class="description"><?php esc_html_e( 'Your PayPal client secret', 'royal-storage' ); ?></p>
						</td>
					</tr>

					<tr>
						<th scope="row"><?php esc_html_e( 'Sandbox Mode', 'royal-storage' ); ?></th>
						<td>
							<label>
								<input type="checkbox" name="royal_storage_paypal_sandbox" value="yes" 
									<?php checked( get_option( 'royal_storage_paypal_sandbox', 'yes' ), 'yes' ); ?>>
								<?php esc_html_e( 'Enable sandbox mode for testing', 'royal-storage' ); ?>
							</label>
						</td>
					</tr>
				</table>

				<?php submit_button(); ?>
			</form>

			<div class="notice notice-info">
				<p><strong><?php esc_html_e( 'Payment Gateway Setup:', 'royal-storage' ); ?></strong></p>
				<ul>
					<li><?php esc_html_e( 'For Stripe: Get your keys from stripe.com dashboard', 'royal-storage' ); ?></li>
					<li><?php esc_html_e( 'For PayPal: Get your credentials from developer.paypal.com', 'royal-storage' ); ?></li>
					<li><?php esc_html_e( 'Make sure WooCommerce is installed and configured', 'royal-storage' ); ?></li>
					<li><?php esc_html_e( 'Enable the payment gateway in WooCommerce settings', 'royal-storage' ); ?></li>
				</ul>
			</div>
		</div>
		<?php
	}
}
