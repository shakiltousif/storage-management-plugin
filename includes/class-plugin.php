<?php
/**
 * Main Plugin Class
 *
 * @package RoyalStorage
 * @since 1.0.0
 */

namespace RoyalStorage;

/**
 * Main plugin class
 */
class Plugin {

	/**
	 * Plugin instance
	 *
	 * @var Plugin
	 */
	private static $instance = null;

	/**
	 * Get plugin instance
	 *
	 * @return Plugin
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Initialize the plugin
	 *
	 * @return void
	 */
	public function init() {
		// Load plugin components.
		$this->load_components();

		// Register hooks.
		$this->register_hooks();

		// Enqueue assets.
		$this->enqueue_assets();
	}

	/**
	 * Load plugin components
	 *
	 * @return void
	 */
	private function load_components() {
		// Load database class.
		new Database();

		// Load custom post types.
		new PostTypes();

		// Load WooCommerce integration (must be loaded early for hooks to work)
		if ( class_exists( 'WooCommerce' ) ) {
			new WooCommerceIntegration();
		}

		// Load admin class.
		if ( is_admin() ) {
			new Admin\Admin();
		}

		// Load frontend class.
		if ( ! is_admin() ) {
			new Frontend\Frontend();
		}

		// Load API class.
		new API\API();
	}

	/**
	 * Register hooks
	 *
	 * @return void
	 */
	private function register_hooks() {
		// Admin menu is now handled by the Admin class
		// add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );

		// Add settings link on plugins page.
		add_filter( 'plugin_action_links_' . ROYAL_STORAGE_BASENAME, array( $this, 'add_settings_link' ) );

		// Register AJAX hooks for booking functionality
		add_action( 'wp_ajax_get_available_units', array( $this, 'ajax_get_available_units' ) );
		add_action( 'wp_ajax_nopriv_get_available_units', array( $this, 'ajax_get_available_units' ) );
		add_action( 'wp_ajax_calculate_booking_price', array( $this, 'ajax_calculate_booking_price' ) );
		add_action( 'wp_ajax_nopriv_calculate_booking_price', array( $this, 'ajax_calculate_booking_price' ) );
		add_action( 'wp_ajax_create_booking', array( $this, 'ajax_create_booking' ) );
		add_action( 'wp_ajax_nopriv_create_booking', array( $this, 'ajax_create_booking' ) );
		
		// Register AJAX hooks for payment functionality
		add_action( 'wp_ajax_royal_storage_process_payment', array( $this, 'ajax_process_payment' ) );
		add_action( 'wp_ajax_nopriv_royal_storage_process_payment', array( $this, 'ajax_process_payment' ) );
	}

	/**
	 * Enqueue assets
	 *
	 * @return void
	 */
	private function enqueue_assets() {
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_frontend_assets' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );
	}

	/**
	 * Enqueue frontend assets
	 *
	 * @return void
	 */
	public function enqueue_frontend_assets() {
		// Enqueue frontend CSS.
		wp_enqueue_style(
			'royal-storage-frontend',
			ROYAL_STORAGE_URL . 'assets/css/frontend.css',
			array(),
			ROYAL_STORAGE_VERSION
		);

		// Enqueue frontend JS.
		wp_enqueue_script(
			'royal-storage-frontend',
			ROYAL_STORAGE_URL . 'assets/js/frontend.js',
			array( 'jquery' ),
			ROYAL_STORAGE_VERSION,
			true
		);

		// Localize script.
		wp_localize_script(
			'royal-storage-frontend',
			'royalStorageData',
			array(
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( 'royal-storage-nonce' ),
			)
		);
	}

	/**
	 * Enqueue admin assets
	 *
	 * @return void
	 */
	public function enqueue_admin_assets() {
		// Enqueue admin CSS.
		wp_enqueue_style(
			'royal-storage-admin',
			ROYAL_STORAGE_URL . 'assets/css/admin.css',
			array(),
			ROYAL_STORAGE_VERSION
		);

		// Enqueue admin JS.
		wp_enqueue_script(
			'royal-storage-admin',
			ROYAL_STORAGE_URL . 'assets/js/admin.js',
			array( 'jquery' ),
			ROYAL_STORAGE_VERSION,
			true
		);

		// Localize script.
		wp_localize_script(
			'royal-storage-admin',
			'royalStorageAdmin',
			array(
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( 'royal-storage-admin-nonce' ),
			)
		);
	}

	// Admin menu methods removed - now handled by Admin class

	/**
	 * Add settings link on plugins page
	 *
	 * @param array $links Plugin action links.
	 * @return array
	 */
	public function add_settings_link( $links ) {
		$settings_link = '<a href="' . admin_url( 'admin.php?page=royal-storage-settings' ) . '">' . esc_html__( 'Settings', 'royal-storage' ) . '</a>';
		array_unshift( $links, $settings_link );
		return $links;
	}

	/**
	 * AJAX handler for getting available units
	 *
	 * @return void
	 */
	public function ajax_get_available_units() {
		// Check if this is a unit selection request (no unit_type parameter)
		if ( ! isset( $_POST['unit_type'] ) || empty( $_POST['unit_type'] ) ) {
			// This is a unit selection request - use UnitSelection class
			$unit_selection = new \RoyalStorage\Frontend\UnitSelection();
			$unit_selection->get_available_units_for_selection();
		} else {
			// This is a booking request - use Booking class
			$booking = new \RoyalStorage\Frontend\Booking();
			$booking->get_available_units();
		}
	}

	/**
	 * AJAX handler for calculating booking price
	 *
	 * @return void
	 */
	public function ajax_calculate_booking_price() {
		// Create a temporary Booking instance to handle the request
		$booking = new \RoyalStorage\Frontend\Booking();
		$booking->ajax_calculate_booking_price();
	}

	/**
	 * AJAX handler for creating booking
	 *
	 * @return void
	 */
	public function ajax_create_booking() {
		// Create a temporary Booking instance to handle the request
		$booking = new \RoyalStorage\Frontend\Booking();
		$booking->create_booking();
	}

	/**
	 * AJAX handler for processing payment
	 *
	 * @return void
	 */
	public function ajax_process_payment() {
		// Create a temporary Checkout instance to handle the request
		$checkout = new \RoyalStorage\Frontend\Checkout();
		$checkout->handle_payment();
	}
}

