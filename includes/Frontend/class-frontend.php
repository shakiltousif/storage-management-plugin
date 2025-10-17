<?php
/**
 * Frontend Class
 *
 * @package RoyalStorage\Frontend
 * @since 1.0.0
 */

namespace RoyalStorage\Frontend;

/**
 * Frontend class for handling frontend functionality
 */
class Frontend {

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->init();
	}

	/**
	 * Initialize frontend functionality
	 *
	 * @return void
	 */
	public function init() {
		// Load frontend components.
		new Portal();
		new Booking();
		new Checkout();
		new UnitSelection();

		// Register hooks.
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_frontend_assets' ) );
		add_action( 'wp_footer', array( $this, 'add_frontend_scripts' ) );

		// Register shortcodes.
		add_shortcode( 'royal_storage_booking', array( $this, 'render_booking_shortcode' ) );
		add_shortcode( 'royal_storage_portal', array( $this, 'render_portal_shortcode' ) );
	}

	/**
	 * Enqueue frontend assets
	 *
	 * @return void
	 */
	public function enqueue_frontend_assets() {
		wp_enqueue_style( 'royal-storage-frontend' );
		wp_enqueue_script( 'royal-storage-frontend' );
	}

	/**
	 * Add frontend scripts
	 *
	 * @return void
	 */
	public function add_frontend_scripts() {
		// Additional frontend scripts can be added here.
	}

	/**
	 * Render booking shortcode
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string
	 */
	public function render_booking_shortcode( $atts ) {
		ob_start();
		?>
		<div class="royal-storage-booking">
			<h2><?php esc_html_e( 'Book Your Storage Unit', 'royal-storage' ); ?></h2>
			<p><?php esc_html_e( 'Select your preferred unit type and dates to get started.', 'royal-storage' ); ?></p>
			<!-- Booking form will be rendered here -->
		</div>
		<?php
		return ob_get_clean();
	}

	/**
	 * Render portal shortcode
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string
	 */
	public function render_portal_shortcode( $atts ) {
		if ( ! is_user_logged_in() ) {
			return '<p>' . esc_html__( 'Please log in to access your portal.', 'royal-storage' ) . '</p>';
		}

		ob_start();
		?>
		<div class="royal-storage-portal">
			<h2><?php esc_html_e( 'My Bookings', 'royal-storage' ); ?></h2>
			<!-- Portal content will be rendered here -->
		</div>
		<?php
		return ob_get_clean();
	}
}

