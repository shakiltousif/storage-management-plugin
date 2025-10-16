<?php
/**
 * Settings Class
 *
 * @package RoyalStorage\Admin
 * @since 1.0.0
 */

namespace RoyalStorage\Admin;

/**
 * Settings class for admin settings
 */
class Settings {

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
		// Business settings.
		register_setting( 'royal_storage_business', 'royal_storage_business_name' );
		register_setting( 'royal_storage_business', 'royal_storage_business_phone' );
		register_setting( 'royal_storage_business', 'royal_storage_business_email' );
		register_setting( 'royal_storage_business', 'royal_storage_business_address' );

		// Pricing settings.
		register_setting( 'royal_storage_pricing', 'royal_storage_currency' );
		register_setting( 'royal_storage_pricing', 'royal_storage_vat_rate' );
		register_setting( 'royal_storage_pricing', 'royal_storage_daily_rate' );
		register_setting( 'royal_storage_pricing', 'royal_storage_weekly_rate' );
		register_setting( 'royal_storage_pricing', 'royal_storage_monthly_rate' );

		// Email settings.
		register_setting( 'royal_storage_email', 'royal_storage_smtp_host' );
		register_setting( 'royal_storage_email', 'royal_storage_smtp_port' );
		register_setting( 'royal_storage_email', 'royal_storage_smtp_username' );
		register_setting( 'royal_storage_email', 'royal_storage_smtp_password' );
		register_setting( 'royal_storage_email', 'royal_storage_from_email' );
		register_setting( 'royal_storage_email', 'royal_storage_from_name' );

		// Payment settings.
		register_setting( 'royal_storage_payment', 'royal_storage_bank_plugin_key' );
		register_setting( 'royal_storage_payment', 'royal_storage_bank_plugin_secret' );
	}

	/**
	 * Get setting value
	 *
	 * @param string $setting Setting name.
	 * @param mixed  $default Default value.
	 * @return mixed
	 */
	public static function get_setting( $setting, $default = '' ) {
		return get_option( $setting, $default );
	}

	/**
	 * Update setting value
	 *
	 * @param string $setting Setting name.
	 * @param mixed  $value Setting value.
	 * @return bool
	 */
	public static function update_setting( $setting, $value ) {
		return update_option( $setting, $value );
	}
}

