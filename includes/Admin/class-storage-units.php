<?php
/**
 * Storage Units Admin Class
 *
 * @package RoyalStorage\Admin
 * @since 1.0.0
 */

namespace RoyalStorage\Admin;

/**
 * Storage Units Admin class for managing storage units
 */
class StorageUnits {

	/**
	 * Constructor
	 */
	public function __construct() {
		// Register AJAX handlers
		add_action( 'wp_ajax_royal_storage_save_storage_unit', array( $this, 'ajax_save_storage_unit' ) );
		add_action( 'wp_ajax_royal_storage_delete_storage_unit', array( $this, 'ajax_delete_storage_unit' ) );
		add_action( 'wp_ajax_royal_storage_get_storage_unit', array( $this, 'ajax_get_storage_unit' ) );
	}

	/**
	 * Render storage units page
	 *
	 * @return void
	 */
	public function render_page() {
		// Handle form submissions
		if ( isset( $_POST['action'] ) && wp_verify_nonce( $_POST['_wpnonce'], 'royal_storage_storage_units' ) ) {
			$this->handle_form_submission();
		}

		// Get storage units
		$storage_units = $this->get_storage_units();
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Storage Units', 'royal-storage' ); ?></h1>
			
			<div class="royal-storage-admin-content">
				<div class="royal-storage-header">
					<div class="royal-storage-stats">
						<div class="stat-card">
							<h3><?php echo esc_html( count( $storage_units ) ); ?></h3>
							<p><?php esc_html_e( 'Total Units', 'royal-storage' ); ?></p>
						</div>
						<div class="stat-card">
							<h3><?php echo esc_html( count( array_filter( $storage_units, function( $unit ) { return $unit['status'] === 'available'; } ) ) ); ?></h3>
							<p><?php esc_html_e( 'Available', 'royal-storage' ); ?></p>
						</div>
						<div class="stat-card">
							<h3><?php echo esc_html( count( array_filter( $storage_units, function( $unit ) { return $unit['status'] === 'occupied'; } ) ) ); ?></h3>
							<p><?php esc_html_e( 'Occupied', 'royal-storage' ); ?></p>
						</div>
					</div>
					
					<div class="royal-storage-actions">
						<button type="button" class="button button-primary" id="add-storage-unit">
							<?php esc_html_e( 'Add New Unit', 'royal-storage' ); ?>
						</button>
					</div>
				</div>

				<div class="royal-storage-table-container">
					<table class="wp-list-table widefat fixed striped">
						<thead>
							<tr>
								<th><?php esc_html_e( 'Unit ID', 'royal-storage' ); ?></th>
								<th><?php esc_html_e( 'Size', 'royal-storage' ); ?></th>
								<th><?php esc_html_e( 'Dimensions', 'royal-storage' ); ?></th>
								<th><?php esc_html_e( 'Price', 'royal-storage' ); ?></th>
								<th><?php esc_html_e( 'Status', 'royal-storage' ); ?></th>
								<th><?php esc_html_e( 'Actions', 'royal-storage' ); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php if ( empty( $storage_units ) ) : ?>
								<tr>
									<td colspan="6" class="no-items">
										<?php esc_html_e( 'No storage units found.', 'royal-storage' ); ?>
									</td>
								</tr>
							<?php else : ?>
								<?php foreach ( $storage_units as $unit ) : ?>
									<tr>
										<td><?php echo esc_html( $unit['id'] ); ?></td>
										<td><?php echo esc_html( strtoupper( $unit['size'] ) ); ?></td>
										<td><?php echo esc_html( $unit['dimensions'] ); ?></td>
										<td><?php echo esc_html( number_format( $unit['base_price'], 2 ) ); ?> RSD</td>
										<td>
											<span class="status status-<?php echo esc_attr( $unit['status'] ); ?>">
												<?php echo esc_html( ucfirst( $unit['status'] ) ); ?>
											</span>
										</td>
										<td>
											<button type="button" class="button button-small edit-unit" data-unit-id="<?php echo esc_attr( $unit['id'] ); ?>">
												<?php esc_html_e( 'Edit', 'royal-storage' ); ?>
											</button>
											<button type="button" class="button button-small button-link-delete delete-unit" data-unit-id="<?php echo esc_attr( $unit['id'] ); ?>">
												<?php esc_html_e( 'Delete', 'royal-storage' ); ?>
											</button>
										</td>
									</tr>
								<?php endforeach; ?>
							<?php endif; ?>
						</tbody>
					</table>
				</div>
			</div>
		</div>

		<!-- Add/Edit Unit Modal -->
		<div id="storage-unit-modal" class="royal-storage-modal" style="display: none;">
			<div class="royal-storage-modal-content">
				<div class="royal-storage-modal-header">
					<h3 id="modal-title" class="royal-storage-modal-title"><?php esc_html_e( 'Add Storage Unit', 'royal-storage' ); ?></h3>
					<span class="royal-storage-modal-close">&times;</span>
				</div>
				<div class="royal-storage-modal-body">
					<form id="storage-unit-form">
						<?php wp_nonce_field( 'royal_storage_storage_unit_form', 'storage_unit_nonce' ); ?>
						<input type="hidden" id="unit-id" name="unit_id" value="">

						<div class="royal-storage-form-group">
							<label for="unit-size"><?php esc_html_e( 'Size', 'royal-storage' ); ?> *</label>
							<select id="unit-size" name="size" required>
								<option value="s"><?php esc_html_e( 'Small (S)', 'royal-storage' ); ?></option>
								<option value="m"><?php esc_html_e( 'Medium (M)', 'royal-storage' ); ?></option>
								<option value="l"><?php esc_html_e( 'Large (L)', 'royal-storage' ); ?></option>
								<option value="xl"><?php esc_html_e( 'Extra Large (XL)', 'royal-storage' ); ?></option>
							</select>
						</div>

						<div class="royal-storage-form-group">
							<label for="unit-dimensions"><?php esc_html_e( 'Dimensions', 'royal-storage' ); ?> *</label>
							<input type="text" id="unit-dimensions" name="dimensions" placeholder="e.g., 3x3x3" required>
						</div>

						<div class="royal-storage-form-group">
							<label for="unit-price"><?php esc_html_e( 'Price (RSD)', 'royal-storage' ); ?> *</label>
							<input type="number" id="unit-price" name="price" step="0.01" min="0" required>
						</div>

						<div class="royal-storage-form-group">
							<label for="unit-status"><?php esc_html_e( 'Status', 'royal-storage' ); ?> *</label>
							<select id="unit-status" name="status" required>
								<option value="available"><?php esc_html_e( 'Available', 'royal-storage' ); ?></option>
								<option value="occupied"><?php esc_html_e( 'Occupied', 'royal-storage' ); ?></option>
								<option value="reserved"><?php esc_html_e( 'Reserved', 'royal-storage' ); ?></option>
							</select>
						</div>

						<div class="royal-storage-form-group">
							<label for="unit-position-x"><?php esc_html_e( 'Position X', 'royal-storage' ); ?></label>
							<input type="number" id="unit-position-x" name="position_x" min="0" value="0">
						</div>

						<div class="royal-storage-form-group">
							<label for="unit-position-y"><?php esc_html_e( 'Position Y', 'royal-storage' ); ?></label>
							<input type="number" id="unit-position-y" name="position_y" min="0" value="0">
						</div>

						<div class="royal-storage-form-group">
							<label for="unit-group"><?php esc_html_e( 'Unit Group', 'royal-storage' ); ?></label>
							<input type="text" id="unit-group" name="unit_group" placeholder="e.g., m_boxes">
						</div>

						<div class="royal-storage-form-group">
							<label for="unit-access-code"><?php esc_html_e( 'Access Code', 'royal-storage' ); ?></label>
							<input type="text" id="unit-access-code" name="access_code" placeholder="e.g., ABC123">
						</div>

						<div class="royal-storage-form-group">
							<label for="unit-amenities"><?php esc_html_e( 'Amenities', 'royal-storage' ); ?></label>
							<textarea id="unit-amenities" name="amenities" rows="2" placeholder="e.g., Climate controlled, 24/7 access"></textarea>
						</div>

						<div class="royal-storage-form-group">
							<label for="unit-description"><?php esc_html_e( 'Description', 'royal-storage' ); ?></label>
							<textarea id="unit-description" name="description" rows="3"></textarea>
						</div>
					</form>
				</div>
				<div class="royal-storage-modal-footer">
					<button type="submit" form="storage-unit-form" class="button button-primary royal-storage-btn">
						<?php esc_html_e( 'Save Unit', 'royal-storage' ); ?>
					</button>
					<button type="button" class="button royal-storage-btn royal-storage-btn-secondary royal-storage-modal-close">
						<?php esc_html_e( 'Cancel', 'royal-storage' ); ?>
					</button>
				</div>
			</div>
		</div>

		<style>
		.royal-storage-form-group {
			margin-bottom: 1rem;
		}

		.royal-storage-form-group label {
			display: block;
			margin-bottom: 0.5rem;
			font-weight: 600;
		}

		.royal-storage-admin-content {
			background: #fff;
			padding: 20px;
			border-radius: 8px;
			box-shadow: 0 2px 4px rgba(0,0,0,0.1);
		}

		.royal-storage-header {
			display: flex;
			justify-content: space-between;
			align-items: center;
			margin-bottom: 30px;
			padding-bottom: 20px;
			border-bottom: 1px solid #e1e1e1;
		}

		.royal-storage-stats {
			display: flex;
			gap: 20px;
		}

		.stat-card {
			background: #f8f9fa;
			padding: 20px;
			border-radius: 8px;
			text-align: center;
			min-width: 120px;
		}

		.stat-card h3 {
			margin: 0 0 5px 0;
			font-size: 24px;
			color: #0073aa;
		}

		.stat-card p {
			margin: 0;
			color: #666;
			font-size: 14px;
		}

		.royal-storage-actions {
			display: flex;
			gap: 10px;
		}

		.royal-storage-table-container {
			overflow-x: auto;
		}

		.status {
			padding: 4px 8px;
			border-radius: 4px;
			font-size: 12px;
			font-weight: bold;
			text-transform: uppercase;
		}

		.status-available {
			background: #d4edda;
			color: #155724;
		}

		.status-occupied {
			background: #f8d7da;
			color: #721c24;
		}

		.status-reserved {
			background: #fff3cd;
			color: #856404;
		}
		</style>

		<script>
		var ajaxurl = '<?php echo admin_url( 'admin-ajax.php' ); ?>';
		jQuery(document).ready(function($) {
			// Open modal
			$('#add-storage-unit').on('click', function() {
				$('#modal-title').text('Add Storage Unit');
				$('#storage-unit-form')[0].reset();
				$('#unit-id').val('');
				$('#storage-unit-modal').addClass('show');
			});
			
			// Edit unit
			$('.edit-unit').on('click', function() {
				var unitId = $(this).data('unit-id');

				// Load unit data via AJAX
				$.ajax({
					url: ajaxurl,
					type: 'POST',
					data: {
						action: 'royal_storage_get_storage_unit',
						unit_id: unitId
					},
					success: function(response) {
						if (response.success) {
							var unit = response.data;
							$('#modal-title').text('Edit Storage Unit');
							$('#unit-id').val(unit.id);
							$('#unit-size').val(unit.size);
							$('#unit-dimensions').val(unit.dimensions);
							$('#unit-price').val(unit.base_price);
							$('#unit-status').val(unit.status || 'available');
							$('#unit-position-x').val(unit.position_x || 0);
							$('#unit-position-y').val(unit.position_y || 0);
							$('#unit-group').val(unit.unit_group || '');
							$('#unit-access-code').val(unit.access_code || '');
							$('#unit-amenities').val(unit.amenities || '');
							$('#unit-description').val(unit.description || '');
							$('#storage-unit-modal').addClass('show');
						} else {
							alert('Error loading unit data: ' + response.data.message);
						}
					},
					error: function() {
						alert('An error occurred while loading unit data.');
					}
				});
			});
			
			// Close modal
			$('.royal-storage-modal-close').on('click', function() {
				$('#storage-unit-modal').removeClass('show');
			});
			
			// Close modal on background click
			$('#storage-unit-modal').on('click', function(e) {
				if (e.target === this) {
					$(this).removeClass('show');
				}
			});
			
			// Form submission
			$('#storage-unit-form').on('submit', function(e) {
				e.preventDefault();
				
				var formData = $(this).serialize();
				formData += '&action=royal_storage_save_storage_unit';
				
				$.ajax({
					url: ajaxurl,
					type: 'POST',
					data: formData,
					success: function(response) {
						if (response.success) {
							location.reload();
						} else {
							alert('Error: ' + response.data.message);
						}
					},
					error: function() {
						alert('An error occurred. Please try again.');
					}
				});
			});
			
			// Delete unit
			$('.delete-unit').on('click', function() {
				if (confirm('Are you sure you want to delete this unit?')) {
					var unitId = $(this).data('unit-id');
					
					$.ajax({
						url: ajaxurl,
						type: 'POST',
						data: {
							action: 'royal_storage_delete_storage_unit',
							unit_id: unitId,
							nonce: '<?php echo wp_create_nonce( 'royal_storage_delete_unit' ); ?>'
						},
						success: function(response) {
							if (response.success) {
								location.reload();
							} else {
								alert('Error: ' + response.data.message);
							}
						},
						error: function() {
							alert('An error occurred. Please try again.');
						}
					});
				}
			});
		});
		</script>
		<?php
	}

	/**
	 * Get storage units
	 *
	 * @return array
	 */
	private function get_storage_units() {
		global $wpdb;
		$table_name = $wpdb->prefix . 'royal_storage_units';

		$results = $wpdb->get_results( "SELECT * FROM $table_name ORDER BY id DESC", ARRAY_A );

		return $results ? $results : array();
	}

	/**
	 * Handle form submission
	 *
	 * @return void
	 */
	private function handle_form_submission() {
		// Handle form submission logic here
		if ( isset( $_POST['add_sample_data'] ) ) {
			// Add sample data logic
		}
	}

	/**
	 * AJAX handler for saving storage unit
	 *
	 * @return void
	 */
	public function ajax_save_storage_unit() {
		// Verify nonce
		if ( ! isset( $_POST['storage_unit_nonce'] ) || ! wp_verify_nonce( $_POST['storage_unit_nonce'], 'royal_storage_storage_unit_form' ) ) {
			wp_send_json_error( array( 'message' => 'Invalid nonce' ) );
		}

		// Validate required fields
		if ( empty( $_POST['size'] ) || empty( $_POST['dimensions'] ) || empty( $_POST['price'] ) ) {
			wp_send_json_error( array( 'message' => 'All required fields must be filled' ) );
		}

		global $wpdb;
		$table_name = $wpdb->prefix . 'royal_storage_units';

		// Check if we're updating or creating
		if ( ! empty( $_POST['unit_id'] ) ) {
			// Update existing unit
			$unit_id = intval( $_POST['unit_id'] );

			// Get the unit to find post_id
			$unit = $wpdb->get_row( $wpdb->prepare( "SELECT post_id FROM $table_name WHERE id = %d", $unit_id ) );

			// Create post if it doesn't exist, or update if it does
			if ( $unit && ! empty( $unit->post_id ) ) {
				wp_update_post( array(
					'ID' => $unit->post_id,
					'post_content' => isset( $_POST['description'] ) ? sanitize_textarea_field( $_POST['description'] ) : ''
				) );
			} else {
				// Create post if it doesn't exist
				$post_data = array(
					'post_title'   => 'Storage Unit ' . sanitize_text_field( $_POST['size'] ),
					'post_content' => isset( $_POST['description'] ) ? sanitize_textarea_field( $_POST['description'] ) : '',
					'post_status'  => 'publish',
					'post_type'    => 'rs_storage_unit'
				);
				$post_id = wp_insert_post( $post_data );

				if ( ! is_wp_error( $post_id ) ) {
					// Update the unit record with the new post_id
					$wpdb->update(
						$table_name,
						array( 'post_id' => $post_id ),
						array( 'id' => $unit_id ),
						array( '%d' ),
						array( '%d' )
					);
				}
			}

			$unit_data = array(
				'size'         => sanitize_text_field( $_POST['size'] ),
				'dimensions'   => sanitize_text_field( $_POST['dimensions'] ),
				'base_price'   => floatval( $_POST['price'] ),
				'status'       => isset( $_POST['status'] ) ? sanitize_text_field( $_POST['status'] ) : 'available',
				'position_x'   => isset( $_POST['position_x'] ) ? intval( $_POST['position_x'] ) : 0,
				'position_y'   => isset( $_POST['position_y'] ) ? intval( $_POST['position_y'] ) : 0,
				'unit_group'   => isset( $_POST['unit_group'] ) ? sanitize_text_field( $_POST['unit_group'] ) : '',
				'access_code'  => isset( $_POST['access_code'] ) ? sanitize_text_field( $_POST['access_code'] ) : '',
				'amenities'    => isset( $_POST['amenities'] ) ? sanitize_textarea_field( $_POST['amenities'] ) : '',
			);

			$result = $wpdb->update(
				$table_name,
				$unit_data,
				array( 'id' => $unit_id ),
				array( '%s', '%s', '%f', '%s', '%d', '%d', '%s', '%s', '%s' ),
				array( '%d' )
			);

			if ( $result !== false ) {
				wp_send_json_success( array( 'message' => 'Unit updated successfully', 'unit_id' => $unit_id ) );
			} else {
				wp_send_json_error( array( 'message' => 'Failed to update unit' ) );
			}
		} else {
			// Create new unit - need to create a post first
			$post_data = array(
				'post_title'   => 'Storage Unit ' . sanitize_text_field( $_POST['size'] ),
				'post_content' => isset( $_POST['description'] ) ? sanitize_textarea_field( $_POST['description'] ) : '',
				'post_status'  => 'publish',
				'post_type'    => 'rs_storage_unit'
			);

			$post_id = wp_insert_post( $post_data );

			if ( is_wp_error( $post_id ) ) {
				wp_send_json_error( array( 'message' => 'Failed to create post' ) );
			}

			$unit_data = array(
				'post_id'      => $post_id,
				'size'         => sanitize_text_field( $_POST['size'] ),
				'dimensions'   => sanitize_text_field( $_POST['dimensions'] ),
				'base_price'   => floatval( $_POST['price'] ),
				'status'       => isset( $_POST['status'] ) ? sanitize_text_field( $_POST['status'] ) : 'available',
				'position_x'   => isset( $_POST['position_x'] ) ? intval( $_POST['position_x'] ) : 0,
				'position_y'   => isset( $_POST['position_y'] ) ? intval( $_POST['position_y'] ) : 0,
				'unit_group'   => isset( $_POST['unit_group'] ) ? sanitize_text_field( $_POST['unit_group'] ) : '',
				'access_code'  => isset( $_POST['access_code'] ) ? sanitize_text_field( $_POST['access_code'] ) : '',
				'amenities'    => isset( $_POST['amenities'] ) ? sanitize_textarea_field( $_POST['amenities'] ) : '',
			);

			$result = $wpdb->insert(
				$table_name,
				$unit_data,
				array( '%d', '%s', '%s', '%f', '%s', '%d', '%d', '%s', '%s', '%s' )
			);

			if ( $result ) {
				wp_send_json_success( array( 'message' => 'Unit created successfully', 'unit_id' => $wpdb->insert_id ) );
			} else {
				// Delete the post if unit creation failed
				wp_delete_post( $post_id, true );
				wp_send_json_error( array( 'message' => 'Failed to create unit: ' . $wpdb->last_error ) );
			}
		}
	}

	/**
	 * AJAX handler for deleting storage unit
	 *
	 * @return void
	 */
	public function ajax_delete_storage_unit() {
		// Verify nonce
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'royal_storage_delete_unit' ) ) {
			wp_send_json_error( array( 'message' => 'Invalid nonce' ) );
		}

		// Validate unit ID
		if ( empty( $_POST['unit_id'] ) ) {
			wp_send_json_error( array( 'message' => 'Unit ID is required' ) );
		}

		global $wpdb;
		$table_name = $wpdb->prefix . 'royal_storage_units';
		$unit_id = intval( $_POST['unit_id'] );

		// Check if unit is occupied
		$unit = $wpdb->get_row( $wpdb->prepare( "SELECT status FROM $table_name WHERE id = %d", $unit_id ) );

		if ( ! $unit ) {
			wp_send_json_error( array( 'message' => 'Unit not found' ) );
		}

		if ( $unit->status === 'occupied' ) {
			wp_send_json_error( array( 'message' => 'Cannot delete an occupied unit' ) );
		}

		// Delete the unit
		$result = $wpdb->delete( $table_name, array( 'id' => $unit_id ), array( '%d' ) );

		if ( $result ) {
			wp_send_json_success( array( 'message' => 'Unit deleted successfully' ) );
		} else {
			wp_send_json_error( array( 'message' => 'Failed to delete unit' ) );
		}
	}

	/**
	 * AJAX handler for getting storage unit data
	 *
	 * @return void
	 */
	public function ajax_get_storage_unit() {
		// Validate unit ID
		if ( empty( $_POST['unit_id'] ) ) {
			wp_send_json_error( array( 'message' => 'Unit ID is required' ) );
		}

		global $wpdb;
		$table_name = $wpdb->prefix . 'royal_storage_units';
		$unit_id = intval( $_POST['unit_id'] );

		$unit = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table_name WHERE id = %d", $unit_id ), ARRAY_A );

		if ( ! $unit ) {
			wp_send_json_error( array( 'message' => 'Unit not found' ) );
		}

		// Get description from post content if post_id exists
		$unit['description'] = '';
		if ( ! empty( $unit['post_id'] ) ) {
			$post = get_post( $unit['post_id'] );
			if ( $post ) {
				$unit['description'] = $post->post_content;
			}
		}

		wp_send_json_success( $unit );
	}
}