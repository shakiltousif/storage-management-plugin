<?php
/**
 * Unit Layout Manager Class
 *
 * @package RoyalStorage
 * @since 1.0.0
 */

namespace RoyalStorage;

/**
 * Unit layout manager class for handling visual unit layouts
 */
class UnitLayoutManager {

	/**
	 * Constructor
	 */
	public function __construct() {
		add_action( 'wp_ajax_save_unit_layout', array( $this, 'save_unit_layout' ) );
		add_action( 'wp_ajax_get_unit_layout', array( $this, 'get_unit_layout' ) );
	}

	/**
	 * Get default layout configuration
	 *
	 * @return array
	 */
	public function get_default_layout() {
		return array(
			'facility_name' => 'Main Storage Facility',
			'grid_width' => 10,
			'grid_height' => 8,
			'unit_size' => 50,
			'layout_data' => array(
				'units' => array(),
				'groups' => array(
					'm_boxes' => array(
						'name' => 'M Size Boxes',
						'color' => '#4CAF50',
						'icon' => 'single-rect',
						'start_x' => 0,
						'start_y' => 0,
						'width' => 3,
						'height' => 4
					),
					'l_boxes' => array(
						'name' => 'L Size Boxes',
						'color' => '#2196F3',
						'icon' => 'double-rect',
						'start_x' => 4,
						'start_y' => 0,
						'width' => 3,
						'height' => 4
					),
					'xl_boxes' => array(
						'name' => 'XL Size Boxes',
						'color' => '#FF9800',
						'icon' => 'triple-rect',
						'start_x' => 0,
						'start_y' => 5,
						'width' => 3,
						'height' => 3
					),
					'parking' => array(
						'name' => 'Parking Spaces',
						'color' => '#9C27B0',
						'icon' => 'parking-p',
						'start_x' => 4,
						'start_y' => 5,
						'width' => 6,
						'height' => 3
					)
				)
			)
		);
	}

	/**
	 * Create default layout
	 *
	 * @return int Layout ID
	 */
	public function create_default_layout() {
		global $wpdb;

		$layouts_table = $wpdb->prefix . 'royal_unit_layouts';
		$default_layout = $this->get_default_layout();

		$result = $wpdb->insert(
			$layouts_table,
			array(
				'facility_name' => $default_layout['facility_name'],
				'layout_data' => wp_json_encode( $default_layout['layout_data'] ),
				'grid_width' => $default_layout['grid_width'],
				'grid_height' => $default_layout['grid_height'],
				'unit_size' => $default_layout['unit_size'],
				'is_active' => 1
			),
			array( '%s', '%s', '%d', '%d', '%d', '%d' )
		);

		if ( $result ) {
			$layout_id = $wpdb->insert_id;
			$this->create_sample_units( $layout_id );
			return $layout_id;
		}

		return false;
	}

	/**
	 * Create sample units for the layout
	 *
	 * @param int $layout_id Layout ID.
	 * @return void
	 */
	private function create_sample_units( $layout_id ) {
		global $wpdb;

		$storage_units_table = $wpdb->prefix . 'royal_storage_units';
		$default_layout = $this->get_default_layout();
		$groups = $default_layout['layout_data']['groups'];

		$unit_counter = 1;

		foreach ( $groups as $group_key => $group ) {
			$unit_type = str_replace( '_boxes', '', $group_key );
			if ( $unit_type === 'parking' ) {
				$unit_type = 'parking';
			}

			// Create units for this group
			for ( $y = $group['start_y']; $y < $group['start_y'] + $group['height']; $y++ ) {
				for ( $x = $group['start_x']; $x < $group['start_x'] + $group['width']; $x++ ) {
					$unit_data = array(
						'post_id' => 0,
						'size' => strtoupper( $unit_type ),
						'dimensions' => $this->get_unit_dimensions( $unit_type ),
						'amenities' => '',
						'base_price' => $this->get_unit_base_price( $unit_type ),
						'status' => 'available',
						'position_x' => $x,
						'position_y' => $y,
						'unit_group' => $group_key,
						'access_code' => $this->generate_access_code(),
						'visual_properties' => wp_json_encode( array(
							'color' => $group['color'],
							'icon' => $group['icon'],
							'unit_number' => $unit_counter
						) )
					);

					$wpdb->insert( $storage_units_table, $unit_data );
					$unit_counter++;
				}
			}
		}
	}

	/**
	 * Get unit dimensions based on type
	 *
	 * @param string $unit_type Unit type.
	 * @return string
	 */
	private function get_unit_dimensions( $unit_type ) {
		$dimensions = array(
			'm' => '3x3x3',
			'l' => '4x4x4',
			'xl' => '5x5x5',
			'parking' => '2.5x5'
		);

		return $dimensions[ $unit_type ] ?? '3x3x3';
	}

	/**
	 * Get unit base price based on type
	 *
	 * @param string $unit_type Unit type.
	 * @return float
	 */
	private function get_unit_base_price( $unit_type ) {
		$prices = array(
			'm' => 10000.00,
			'l' => 15000.00,
			'xl' => 20000.00,
			'parking' => 5000.00
		);

		return $prices[ $unit_type ] ?? 10000.00;
	}

	/**
	 * Generate access code
	 *
	 * @return string
	 */
	private function generate_access_code() {
		return str_pad( rand( 1000, 9999 ), 4, '0', STR_PAD_LEFT ) . '#';
	}

	/**
	 * Get active layout
	 *
	 * @return object|null
	 */
	public function get_active_layout() {
		global $wpdb;

		$layouts_table = $wpdb->prefix . 'royal_unit_layouts';
		$layout = $wpdb->get_row( "SELECT * FROM $layouts_table WHERE is_active = 1 ORDER BY id DESC LIMIT 1" );

		if ( ! $layout ) {
			// Create default layout if none exists
			$layout_id = $this->create_default_layout();
			if ( $layout_id ) {
				$layout = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $layouts_table WHERE id = %d", $layout_id ) );
			}
		}

		if ( $layout ) {
			$layout->layout_data = json_decode( $layout->layout_data, true );
		}

		return $layout;
	}

	/**
	 * Get all units for visual selection
	 *
	 * @return array
	 */
	public function get_units_for_selection() {
		global $wpdb;

		$storage_units_table = $wpdb->prefix . 'royal_storage_units';
		$units = $wpdb->get_results( "SELECT * FROM $storage_units_table ORDER BY unit_group, position_y, position_x" );

		// If no units exist, create sample units
		if ( empty( $units ) ) {
			$this->create_default_layout();
			$units = $wpdb->get_results( "SELECT * FROM $storage_units_table ORDER BY unit_group, position_y, position_x" );
		}

		foreach ( $units as $unit ) {
			$unit->visual_properties = json_decode( $unit->visual_properties, true );
		}

		return $units;
	}


	/**
	 * Save unit layout (AJAX)
	 *
	 * @return void
	 */
	public function save_unit_layout() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Insufficient permissions', 'royal-storage' ) ) );
		}

		$layout_data = isset( $_POST['layout_data'] ) ? wp_unslash( $_POST['layout_data'] ) : '';
		$facility_name = isset( $_POST['facility_name'] ) ? sanitize_text_field( wp_unslash( $_POST['facility_name'] ) ) : '';

		if ( empty( $layout_data ) || empty( $facility_name ) ) {
			wp_send_json_error( array( 'message' => __( 'Missing required data', 'royal-storage' ) ) );
		}

		global $wpdb;
		$layouts_table = $wpdb->prefix . 'royal_unit_layouts';

		$result = $wpdb->insert(
			$layouts_table,
			array(
				'facility_name' => $facility_name,
				'layout_data' => $layout_data,
				'grid_width' => intval( $_POST['grid_width'] ?? 10 ),
				'grid_height' => intval( $_POST['grid_height'] ?? 8 ),
				'unit_size' => intval( $_POST['unit_size'] ?? 50 ),
				'is_active' => 1
			),
			array( '%s', '%s', '%d', '%d', '%d', '%d' )
		);

		if ( $result ) {
			wp_send_json_success( array( 'message' => __( 'Layout saved successfully', 'royal-storage' ) ) );
		} else {
			wp_send_json_error( array( 'message' => __( 'Failed to save layout', 'royal-storage' ) ) );
		}
	}
}
