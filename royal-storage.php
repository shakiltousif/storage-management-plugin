<?php
/**
 * Plugin Name: Royal Storage Management
 * Plugin URI: https://royalstorage.rs
 * Description: Complete storage box and parking space rental management system for Royal Storage
 * Version: 1.0.1
 * Author: Shakil Ahamed
 * Author URI: https://royalstorage.rs
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: royal-storage
 * Domain Path: /languages
 * Requires at least: 6.0
 * Requires PHP: 8.0
 * Requires Plugins: woocommerce
 *
 * @package RoyalStorage
 * @since 1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Define plugin constants.
define( 'ROYAL_STORAGE_VERSION', '1.0.0' );
define( 'ROYAL_STORAGE_FILE', __FILE__ );
define( 'ROYAL_STORAGE_DIR', plugin_dir_path( __FILE__ ) );
define( 'ROYAL_STORAGE_URL', plugin_dir_url( __FILE__ ) );
define( 'ROYAL_STORAGE_BASENAME', plugin_basename( __FILE__ ) );

// Check if WooCommerce is active.
if ( ! function_exists( 'is_plugin_active' ) ) {
	require_once ABSPATH . 'wp-admin/includes/plugin.php';
}

if ( ! is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
	add_action( 'admin_notices', function() {
		echo '<div class="notice notice-error"><p>';
		echo esc_html__( 'Royal Storage Management requires WooCommerce to be installed and activated.', 'royal-storage' );
		echo '</p></div>';
	});
	return;
}

// Load the autoloader.
require_once ROYAL_STORAGE_DIR . 'includes/class-autoloader.php';

// Load core classes manually to ensure they're available
require_once ROYAL_STORAGE_DIR . 'includes/class-database.php';
require_once ROYAL_STORAGE_DIR . 'includes/class-post-types.php';
require_once ROYAL_STORAGE_DIR . 'includes/class-pricing-engine.php';
require_once ROYAL_STORAGE_DIR . 'includes/class-booking-engine.php';
require_once ROYAL_STORAGE_DIR . 'includes/class-email-manager.php';
require_once ROYAL_STORAGE_DIR . 'includes/class-payment-processor.php';
require_once ROYAL_STORAGE_DIR . 'includes/class-woocommerce-integration.php';
require_once ROYAL_STORAGE_DIR . 'includes/class-advanced-reports.php';
require_once ROYAL_STORAGE_DIR . 'includes/class-plugin.php';

// Load model classes
require_once ROYAL_STORAGE_DIR . 'includes/Models/class-storage-unit.php';
require_once ROYAL_STORAGE_DIR . 'includes/Models/class-parking-space.php';
require_once ROYAL_STORAGE_DIR . 'includes/Models/class-booking.php';

// Load admin classes
require_once ROYAL_STORAGE_DIR . 'includes/Admin/class-admin.php';
require_once ROYAL_STORAGE_DIR . 'includes/Admin/class-dashboard.php';
require_once ROYAL_STORAGE_DIR . 'includes/Admin/class-settings.php';
require_once ROYAL_STORAGE_DIR . 'includes/Admin/class-reports.php';
require_once ROYAL_STORAGE_DIR . 'includes/Admin/class-bookings.php';
require_once ROYAL_STORAGE_DIR . 'includes/Admin/class-customers.php';
require_once ROYAL_STORAGE_DIR . 'includes/Admin/class-notifications.php';

// Load frontend classes
require_once ROYAL_STORAGE_DIR . 'includes/Frontend/class-frontend.php';
require_once ROYAL_STORAGE_DIR . 'includes/Frontend/class-portal.php';
require_once ROYAL_STORAGE_DIR . 'includes/Frontend/class-booking.php';
require_once ROYAL_STORAGE_DIR . 'includes/Frontend/class-bookings.php';
require_once ROYAL_STORAGE_DIR . 'includes/Frontend/class-invoices.php';
require_once ROYAL_STORAGE_DIR . 'includes/Frontend/class-checkout.php';

// Load API classes
require_once ROYAL_STORAGE_DIR . 'includes/API/class-api.php';
require_once ROYAL_STORAGE_DIR . 'includes/API/class-rest-api.php';
require_once ROYAL_STORAGE_DIR . 'includes/API/class-webhook-handler.php';

// Load additional classes
require_once ROYAL_STORAGE_DIR . 'includes/class-invoice-generator.php';
require_once ROYAL_STORAGE_DIR . 'includes/class-notification-manager.php';
require_once ROYAL_STORAGE_DIR . 'includes/class-subscription-manager.php';
require_once ROYAL_STORAGE_DIR . 'includes/class-analytics.php';
require_once ROYAL_STORAGE_DIR . 'includes/class-cache-manager.php';
require_once ROYAL_STORAGE_DIR . 'includes/class-database-optimizer.php';
require_once ROYAL_STORAGE_DIR . 'includes/class-security-manager.php';
require_once ROYAL_STORAGE_DIR . 'includes/class-deployment-manager.php';

// Initialize the plugin.
add_action( 'plugins_loaded', function() {
	// Load text domain for translations.
	load_plugin_textdomain( 'royal-storage', false, dirname( ROYAL_STORAGE_BASENAME ) . '/languages' );

	// Load the main plugin class.
	$plugin = new \RoyalStorage\Plugin();
	$plugin->init();
}, 10 );

// Activation hook.
register_activation_hook( __FILE__, function() {
	require_once ROYAL_STORAGE_DIR . 'includes/class-activator.php';
	\RoyalStorage\Activator::activate();
});

// Deactivation hook.
register_deactivation_hook( __FILE__, function() {
	require_once ROYAL_STORAGE_DIR . 'includes/class-deactivator.php';
	\RoyalStorage\Deactivator::deactivate();
});

