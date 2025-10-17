<?php
/**
 * Unit Layout Admin Class
 *
 * @package RoyalStorage
 * @since 1.0.0
 */

namespace RoyalStorage\Admin;

/**
 * Unit layout admin class
 */
class UnitLayoutAdmin {

	/**
	 * Constructor
	 */
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );
		add_action( 'wp_ajax_save_unit_layout', array( $this, 'save_unit_layout' ) );
		add_action( 'wp_ajax_get_unit_layout', array( $this, 'get_unit_layout' ) );
		add_action( 'wp_ajax_create_sample_units', array( $this, 'create_sample_units' ) );
	}

	/**
	 * Add admin menu
	 *
	 * @return void
	 */
	public function add_admin_menu() {
		add_submenu_page(
			'royal-storage',
			__( 'Unit Layouts', 'royal-storage' ),
			__( 'Unit Layouts', 'royal-storage' ),
			'manage_options',
			'royal-storage-layouts',
			array( $this, 'render_layout_page' )
		);
	}

	/**
	 * Enqueue admin assets
	 *
	 * @param string $hook Current admin page hook.
	 * @return void
	 */
	public function enqueue_admin_assets( $hook ) {
		if ( 'royal-storage_page_royal-storage-layouts' !== $hook ) {
			return;
		}

		wp_enqueue_style(
			'royal-storage-layout-admin',
			ROYAL_STORAGE_URL . 'assets/css/layout-admin.css',
			array(),
			ROYAL_STORAGE_VERSION
		);

		wp_enqueue_script(
			'royal-storage-layout-admin',
			ROYAL_STORAGE_URL . 'assets/js/layout-admin.js',
			array( 'jquery' ),
			ROYAL_STORAGE_VERSION,
			true
		);

		wp_localize_script(
			'royal-storage-layout-admin',
			'royalStorageLayoutAdmin',
			array(
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'nonce' => wp_create_nonce( 'royal_storage_layout_admin' ),
				'strings' => array(
					'save_success' => __( 'Layout saved successfully!', 'royal-storage' ),
					'save_error' => __( 'Failed to save layout.', 'royal-storage' ),
					'confirm_delete' => __( 'Are you sure you want to delete this layout?', 'royal-storage' ),
					'confirm_reset' => __( 'Are you sure you want to reset to default layout? This will delete all existing units.', 'royal-storage' )
				)
			)
		);
	}

	/**
	 * Render layout page
	 *
	 * @return void
	 */
	public function render_layout_page() {
		$layout_manager = new \RoyalStorage\UnitLayoutManager();
		$current_layout = $layout_manager->get_active_layout();
		$units = $layout_manager->get_units_for_selection();

		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Unit Layout Management', 'royal-storage' ); ?></h1>
			
			<div class="layout-admin-container">
				<div class="layout-controls">
					<div class="control-group">
						<label for="facility-name"><?php esc_html_e( 'Facility Name:', 'royal-storage' ); ?></label>
						<input type="text" id="facility-name" value="<?php echo esc_attr( $current_layout ? $current_layout->facility_name : 'Main Storage Facility' ); ?>">
					</div>
					
					<div class="control-group">
						<label for="grid-width"><?php esc_html_e( 'Grid Width:', 'royal-storage' ); ?></label>
						<input type="number" id="grid-width" value="<?php echo esc_attr( $current_layout ? $current_layout->grid_width : 10 ); ?>" min="5" max="20">
					</div>
					
					<div class="control-group">
						<label for="grid-height"><?php esc_html_e( 'Grid Height:', 'royal-storage' ); ?></label>
						<input type="number" id="grid-height" value="<?php echo esc_attr( $current_layout ? $current_layout->grid_height : 8 ); ?>" min="5" max="20">
					</div>
					
					<div class="control-group">
						<label for="unit-size"><?php esc_html_e( 'Unit Size (px):', 'royal-storage' ); ?></label>
						<input type="number" id="unit-size" value="<?php echo esc_attr( $current_layout ? $current_layout->unit_size : 50 ); ?>" min="30" max="100">
					</div>
				</div>

				<div class="layout-actions">
					<button type="button" id="save-layout" class="button button-primary">
						<?php esc_html_e( 'Save Layout', 'royal-storage' ); ?>
					</button>
					<button type="button" id="reset-layout" class="button button-secondary">
						<?php esc_html_e( 'Reset to Default', 'royal-storage' ); ?>
					</button>
					<button type="button" id="create-sample-units" class="button button-secondary">
						<?php esc_html_e( 'Create Sample Units', 'royal-storage' ); ?>
					</button>
				</div>

				<div class="layout-preview">
					<h3><?php esc_html_e( 'Layout Preview', 'royal-storage' ); ?></h3>
					<div id="layout-grid" class="layout-grid">
						<!-- Grid will be rendered here -->
					</div>
				</div>

				<div class="units-list">
					<h3><?php esc_html_e( 'Units List', 'royal-storage' ); ?></h3>
					<div class="units-table-container">
						<table class="wp-list-table widefat fixed striped">
							<thead>
								<tr>
									<th><?php esc_html_e( 'ID', 'royal-storage' ); ?></th>
									<th><?php esc_html_e( 'Type', 'royal-storage' ); ?></th>
									<th><?php esc_html_e( 'Position', 'royal-storage' ); ?></th>
									<th><?php esc_html_e( 'Group', 'royal-storage' ); ?></th>
									<th><?php esc_html_e( 'Status', 'royal-storage' ); ?></th>
									<th><?php esc_html_e( 'Price', 'royal-storage' ); ?></th>
									<th><?php esc_html_e( 'Access Code', 'royal-storage' ); ?></th>
									<th><?php esc_html_e( 'Actions', 'royal-storage' ); ?></th>
								</tr>
							</thead>
							<tbody>
								<?php foreach ( $units as $unit ) : ?>
									<tr>
										<td><?php echo esc_html( $unit->id ); ?></td>
										<td><?php echo esc_html( $unit->size ); ?></td>
										<td><?php echo esc_html( $unit->position_x . ', ' . $unit->position_y ); ?></td>
										<td><?php echo esc_html( $unit->unit_group ); ?></td>
										<td>
											<span class="status status-<?php echo esc_attr( $unit->status ); ?>">
												<?php echo esc_html( ucfirst( $unit->status ) ); ?>
											</span>
										</td>
										<td><?php echo esc_html( number_format( $unit->base_price, 2 ) ); ?> RSD</td>
										<td><?php echo esc_html( $unit->access_code ); ?></td>
										<td>
											<button type="button" class="button button-small edit-unit" data-unit-id="<?php echo esc_attr( $unit->id ); ?>">
												<?php esc_html_e( 'Edit', 'royal-storage' ); ?>
											</button>
										</td>
									</tr>
								<?php endforeach; ?>
							</tbody>
						</table>
					</div>
				</div>
			</div>
		</div>
		<?php
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

		$layout_manager = new \RoyalStorage\UnitLayoutManager();
		$result = $layout_manager->save_unit_layout();

		if ( $result ) {
			wp_send_json_success( array( 'message' => __( 'Layout saved successfully', 'royal-storage' ) ) );
		} else {
			wp_send_json_error( array( 'message' => __( 'Failed to save layout', 'royal-storage' ) ) );
		}
	}

	/**
	 * Get unit layout (AJAX)
	 *
	 * @return void
	 */
	public function get_unit_layout() {
		$layout_manager = new \RoyalStorage\UnitLayoutManager();
		$layout = $layout_manager->get_active_layout();
		$units = $layout_manager->get_units_for_selection();

		wp_send_json_success( array(
			'layout' => $layout,
			'units' => $units
		) );
	}

	/**
	 * Create sample units (AJAX)
	 *
	 * @return void
	 */
	public function create_sample_units() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Insufficient permissions', 'royal-storage' ) ) );
		}

		$layout_manager = new \RoyalStorage\UnitLayoutManager();
		$result = $layout_manager->create_default_layout();

		if ( $result ) {
			wp_send_json_success( array( 'message' => __( 'Sample units created successfully', 'royal-storage' ) ) );
		} else {
			wp_send_json_error( array( 'message' => __( 'Failed to create sample units', 'royal-storage' ) ) );
		}
	}
}
