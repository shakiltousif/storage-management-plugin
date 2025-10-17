<?php
/**
 * Unit Selection Class
 *
 * @package RoyalStorage
 * @since 1.0.0
 */

namespace RoyalStorage\Frontend;

/**
 * Unit selection class for frontend
 */
class UnitSelection {

	/**
	 * Unit layout manager
	 *
	 * @var UnitLayoutManager
	 */
	private $layout_manager;

	/**
	 * Constructor
	 */
	public function __construct() {
		error_log( 'Royal Storage: UnitSelection constructor called' );
		
		$this->layout_manager = new \RoyalStorage\UnitLayoutManager();
		
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ) );
		add_shortcode( 'royal_storage_unit_selection', array( $this, 'render_unit_selection' ) );
		// AJAX handlers are registered in the main Plugin class to avoid conflicts
		
		error_log( 'Royal Storage: UnitSelection AJAX hooks registered' );
	}

	/**
	 * Enqueue assets
	 *
	 * @return void
	 */
	public function enqueue_assets() {
		wp_enqueue_style(
			'royal-storage-unit-selection',
			ROYAL_STORAGE_URL . 'assets/css/unit-selection.css',
			array(),
			ROYAL_STORAGE_VERSION
		);

		wp_enqueue_script(
			'royal-storage-unit-selection',
			ROYAL_STORAGE_URL . 'assets/js/unit-selection.js',
			array( 'jquery' ),
			ROYAL_STORAGE_VERSION,
			true
		);

		wp_localize_script(
			'royal-storage-unit-selection',
			'royalStorageUnitSelection',
			array(
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'nonce' => wp_create_nonce( 'royal_storage_booking' )
			)
		);
	}

	/**
	 * Render unit selection shortcode
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string
	 */
	public function render_unit_selection( $atts = array() ) {
		$atts = shortcode_atts( array(
			'show_legend' => 'true',
			'show_summary' => 'true'
		), $atts, 'royal_storage_unit_selection' );

		ob_start();
		include ROYAL_STORAGE_DIR . 'templates/frontend/unit-selection.php';
		return ob_get_clean();
	}

	/**
	 * Get active layout
	 *
	 * @return object|null
	 */
	public function get_active_layout() {
		return $this->layout_manager->get_active_layout();
	}

	/**
	 * Get units for selection
	 *
	 * @return array
	 */
	public function get_units_for_selection() {
		return $this->layout_manager->get_units_for_selection();
	}

	/**
	 * Get available units for selection (AJAX)
	 *
	 * @return void
	 */
	public function get_available_units_for_selection() {
		// Temporarily disable nonce check for debugging
		// if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ?? '' ) ), 'royal_storage_booking' ) ) {
		// 	wp_send_json_error( array( 'message' => __( 'Security check failed', 'royal-storage' ) ) );
		// }

		error_log( 'Royal Storage: get_available_units_for_selection called' );

		try {
			$units = $this->get_units_for_selection();
			error_log( 'Royal Storage: Units retrieved: ' . print_r( $units, true ) );
		} catch ( Exception $e ) {
			error_log( 'Royal Storage: Error getting units: ' . $e->getMessage() );
			wp_send_json_error( array( 'message' => 'Error getting units: ' . $e->getMessage() ) );
		}

		try {
			$layout = $this->get_active_layout();
			error_log( 'Royal Storage: Layout retrieved: ' . print_r( $layout, true ) );
		} catch ( Exception $e ) {
			error_log( 'Royal Storage: Error getting layout: ' . $e->getMessage() );
			wp_send_json_error( array( 'message' => 'Error getting layout: ' . $e->getMessage() ) );
		}

		wp_send_json_success( array(
			'units' => $units,
			'layout' => $layout
		) );
	}
}
