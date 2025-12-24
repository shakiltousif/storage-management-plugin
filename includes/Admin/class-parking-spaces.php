<?php
/**
 * Parking Spaces Admin Class
 *
 * @package RoyalStorage\Admin
 * @since 1.0.0
 */

namespace RoyalStorage\Admin;

/**
 * Parking Spaces Admin class for managing parking spaces
 */
class ParkingSpaces {

	/**
	 * Constructor
	 */
	public function __construct() {
		// Register AJAX handlers
		add_action( 'wp_ajax_royal_storage_save_parking_space', array( $this, 'ajax_save_parking_space' ) );
		add_action( 'wp_ajax_royal_storage_delete_parking_space', array( $this, 'ajax_delete_parking_space' ) );
		add_action( 'wp_ajax_royal_storage_get_parking_space', array( $this, 'ajax_get_parking_space' ) );
	}

	/**
	 * Render parking spaces page
	 *
	 * @return void
	 */
	public function render_page() {
		// Handle form submissions
		if ( isset( $_POST['action'] ) && wp_verify_nonce( $_POST['_wpnonce'], 'royal_storage_parking_spaces' ) ) {
			$this->handle_form_submission();
		}

		// Get parking spaces
		$parking_spaces = $this->get_parking_spaces();
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Parking Spaces', 'royal-storage' ); ?></h1>
			
			<div class="royal-storage-admin-content">
				<div class="royal-storage-header">
					<div class="royal-storage-stats">
						<div class="stat-card">
							<h3><?php echo esc_html( count( $parking_spaces ) ); ?></h3>
							<p><?php esc_html_e( 'Total Spaces', 'royal-storage' ); ?></p>
						</div>
						<div class="stat-card">
							<h3><?php echo esc_html( count( array_filter( $parking_spaces, function( $space ) { return $space['status'] === 'available'; } ) ) ); ?></h3>
							<p><?php esc_html_e( 'Available', 'royal-storage' ); ?></p>
						</div>
						<div class="stat-card">
							<h3><?php echo esc_html( count( array_filter( $parking_spaces, function( $space ) { return $space['status'] === 'occupied'; } ) ) ); ?></h3>
							<p><?php esc_html_e( 'Occupied', 'royal-storage' ); ?></p>
						</div>
					</div>
					
					<div class="royal-storage-actions">
						<button type="button" class="button button-primary" id="add-parking-space">
							<?php esc_html_e( 'Add New Space', 'royal-storage' ); ?>
						</button>
					</div>
				</div>

				<div class="royal-storage-table-container">
					<table class="wp-list-table widefat fixed striped">
						<thead>
							<tr>
								<th><?php esc_html_e( 'Space ID', 'royal-storage' ); ?></th>
								<th><?php esc_html_e( 'Spot Number', 'royal-storage' ); ?></th>
								<th><?php esc_html_e( 'Height Limit', 'royal-storage' ); ?></th>
								<th><?php esc_html_e( 'Price', 'royal-storage' ); ?></th>
								<th><?php esc_html_e( 'Status', 'royal-storage' ); ?></th>
								<th><?php esc_html_e( 'Actions', 'royal-storage' ); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php if ( empty( $parking_spaces ) ) : ?>
								<tr>
									<td colspan="6" class="no-items">
										<?php esc_html_e( 'No parking spaces found.', 'royal-storage' ); ?>
									</td>
								</tr>
							<?php else : ?>
								<?php foreach ( $parking_spaces as $space ) : ?>
									<tr>
										<td><?php echo esc_html( $space['id'] ); ?></td>
										<td><?php echo esc_html( $space['spot_number'] ); ?></td>
										<td><?php echo esc_html( $space['height_limit'] ? $space['height_limit'] : 'N/A' ); ?></td>
										<td><?php echo esc_html( number_format( $space['base_price'], 2 ) ); ?> RSD</td>
										<td>
											<span class="status status-<?php echo esc_attr( $space['status'] ); ?>">
												<?php echo esc_html( ucfirst( $space['status'] ) ); ?>
											</span>
										</td>
										<td>
											<button type="button" class="button button-small edit-space" data-space-id="<?php echo esc_attr( $space['id'] ); ?>">
												<?php esc_html_e( 'Edit', 'royal-storage' ); ?>
											</button>
											<button type="button" class="button button-small button-link-delete delete-space" data-space-id="<?php echo esc_attr( $space['id'] ); ?>">
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

		<!-- Add/Edit Space Modal -->
		<div id="parking-space-modal" class="royal-storage-modal" style="display: none;">
			<div class="royal-storage-modal-content">
				<div class="royal-storage-modal-header">
					<h3 id="modal-title" class="royal-storage-modal-title"><?php esc_html_e( 'Add Parking Space', 'royal-storage' ); ?></h3>
					<span class="royal-storage-modal-close">&times;</span>
				</div>
				<div class="royal-storage-modal-body">
					<form id="parking-space-form">
						<?php wp_nonce_field( 'royal_storage_parking_space_form', 'parking_space_nonce' ); ?>
						<input type="hidden" id="space-id" name="space_id" value="">

						<div class="royal-storage-form-group">
							<label for="space-spot-number"><?php esc_html_e( 'Spot Number', 'royal-storage' ); ?> *</label>
							<input type="number" id="space-spot-number" name="spot_number" min="1" required>
						</div>

						<div class="royal-storage-form-group">
							<label for="space-height-limit"><?php esc_html_e( 'Height Limit', 'royal-storage' ); ?></label>
							<input type="text" id="space-height-limit" name="height_limit" placeholder="e.g., 2.5m">
						</div>

						<div class="royal-storage-form-group">
							<label for="space-price"><?php esc_html_e( 'Price (RSD)', 'royal-storage' ); ?> *</label>
							<input type="number" id="space-price" name="price" step="0.01" min="0" required>
						</div>

						<div class="royal-storage-form-group">
							<label for="space-description"><?php esc_html_e( 'Description', 'royal-storage' ); ?></label>
							<textarea id="space-description" name="description" rows="3"></textarea>
						</div>
					</form>
				</div>
				<div class="royal-storage-modal-footer">
					<button type="submit" form="parking-space-form" class="button button-primary royal-storage-btn">
						<?php esc_html_e( 'Save Space', 'royal-storage' ); ?>
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
			$('#add-parking-space').on('click', function() {
				$('#modal-title').text('Add Parking Space');
				$('#parking-space-form')[0].reset();
				$('#space-id').val('');
				$('#parking-space-modal').addClass('show');
			});
			
			// Edit space
			$('.edit-space').on('click', function() {
				var spaceId = $(this).data('space-id');

				// Load space data via AJAX
				$.ajax({
					url: ajaxurl,
					type: 'POST',
					data: {
						action: 'royal_storage_get_parking_space',
						space_id: spaceId
					},
					success: function(response) {
						if (response.success) {
							var space = response.data;
							$('#modal-title').text('Edit Parking Space');
							$('#space-id').val(space.id);
							$('#space-spot-number').val(space.spot_number);
							$('#space-height-limit').val(space.height_limit || '');
							$('#space-price').val(space.base_price);
							$('#space-description').val(space.description || '');
							$('#parking-space-modal').addClass('show');
						} else {
							alert('Error loading space data: ' + response.data.message);
						}
					},
					error: function() {
						alert('An error occurred while loading space data.');
					}
				});
			});
			
			// Close modal
			$('.royal-storage-modal-close').on('click', function() {
				$('#parking-space-modal').removeClass('show');
			});
			
			// Close modal on background click
			$('#parking-space-modal').on('click', function(e) {
				if (e.target === this) {
					$(this).removeClass('show');
				}
			});
			
			// Form submission
			$('#parking-space-form').on('submit', function(e) {
				e.preventDefault();
				
				var formData = $(this).serialize();
				formData += '&action=royal_storage_save_parking_space';
				
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
			
			// Delete space
			$('.delete-space').on('click', function() {
				if (confirm('Are you sure you want to delete this space?')) {
					var spaceId = $(this).data('space-id');
					
					$.ajax({
						url: ajaxurl,
						type: 'POST',
						data: {
							action: 'royal_storage_delete_parking_space',
							space_id: spaceId,
							nonce: '<?php echo wp_create_nonce( 'royal_storage_delete_space' ); ?>'
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
	 * Get parking spaces
	 *
	 * @return array
	 */
	private function get_parking_spaces() {
		global $wpdb;
		$table_name = $wpdb->prefix . 'royal_parking_spaces';

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
	 * AJAX handler for saving parking space
	 *
	 * @return void
	 */
	public function ajax_save_parking_space() {
		// Verify nonce
		if ( ! isset( $_POST['parking_space_nonce'] ) || ! wp_verify_nonce( $_POST['parking_space_nonce'], 'royal_storage_parking_space_form' ) ) {
			wp_send_json_error( array( 'message' => 'Invalid nonce' ) );
		}

		// Validate required fields
		if ( empty( $_POST['spot_number'] ) || empty( $_POST['price'] ) ) {
			wp_send_json_error( array( 'message' => 'All required fields must be filled' ) );
		}

		global $wpdb;
		$table_name = $wpdb->prefix . 'royal_parking_spaces';

		// Check if we're updating or creating
		if ( ! empty( $_POST['space_id'] ) ) {
			// Update existing space
			$space_id = intval( $_POST['space_id'] );

			// Get the space to find post_id
			$space = $wpdb->get_row( $wpdb->prepare( "SELECT post_id FROM $table_name WHERE id = %d", $space_id ) );

			// Create post if it doesn't exist, or update if it does
			if ( $space && ! empty( $space->post_id ) ) {
				wp_update_post( array(
					'ID' => $space->post_id,
					'post_content' => isset( $_POST['description'] ) ? sanitize_textarea_field( $_POST['description'] ) : ''
				) );
			} else {
				// Create post if it doesn't exist
				$post_data = array(
					'post_title'   => 'Parking Space #' . intval( $_POST['spot_number'] ),
					'post_content' => isset( $_POST['description'] ) ? sanitize_textarea_field( $_POST['description'] ) : '',
					'post_status'  => 'publish',
					'post_type'    => 'rs_parking_space'
				);
				$post_id = wp_insert_post( $post_data );

				if ( ! is_wp_error( $post_id ) ) {
					// Update the parking space record with the new post_id
					$wpdb->update(
						$table_name,
						array( 'post_id' => $post_id ),
						array( 'id' => $space_id ),
						array( '%d' ),
						array( '%d' )
					);
				}
			}

			$space_data = array(
				'spot_number' => intval( $_POST['spot_number'] ),
				'height_limit' => isset( $_POST['height_limit'] ) ? sanitize_text_field( $_POST['height_limit'] ) : null,
				'base_price' => floatval( $_POST['price'] ),
				'status' => 'available'
			);

			$result = $wpdb->update(
				$table_name,
				$space_data,
				array( 'id' => $space_id ),
				array( '%d', '%s', '%f', '%s' ),
				array( '%d' )
			);

			if ( $result !== false ) {
				wp_send_json_success( array( 'message' => 'Space updated successfully', 'space_id' => $space_id ) );
			} else {
				wp_send_json_error( array( 'message' => 'Failed to update space' ) );
			}
		} else {
			// Check if spot number already exists
			$existing = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM $table_name WHERE spot_number = %d", intval( $_POST['spot_number'] ) ) );

			if ( $existing ) {
				wp_send_json_error( array( 'message' => 'Spot number already exists' ) );
			}

			// Create new space - need to create a post first
			$post_data = array(
				'post_title'   => 'Parking Space #' . intval( $_POST['spot_number'] ),
				'post_content' => isset( $_POST['description'] ) ? sanitize_textarea_field( $_POST['description'] ) : '',
				'post_status'  => 'publish',
				'post_type'    => 'rs_parking_space'
			);

			$post_id = wp_insert_post( $post_data );

			if ( is_wp_error( $post_id ) ) {
				wp_send_json_error( array( 'message' => 'Failed to create post' ) );
			}

			$space_data = array(
				'post_id' => $post_id,
				'spot_number' => intval( $_POST['spot_number'] ),
				'height_limit' => isset( $_POST['height_limit'] ) ? sanitize_text_field( $_POST['height_limit'] ) : null,
				'base_price' => floatval( $_POST['price'] ),
				'status' => 'available'
			);

			$result = $wpdb->insert(
				$table_name,
				$space_data,
				array( '%d', '%d', '%s', '%f', '%s' )
			);

			if ( $result ) {
				wp_send_json_success( array( 'message' => 'Space created successfully', 'space_id' => $wpdb->insert_id ) );
			} else {
				// Delete the post if space creation failed
				wp_delete_post( $post_id, true );
				wp_send_json_error( array( 'message' => 'Failed to create space: ' . $wpdb->last_error ) );
			}
		}
	}

	/**
	 * AJAX handler for deleting parking space
	 *
	 * @return void
	 */
	public function ajax_delete_parking_space() {
		// Verify nonce
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'royal_storage_delete_space' ) ) {
			wp_send_json_error( array( 'message' => 'Invalid nonce' ) );
		}

		// Validate space ID
		if ( empty( $_POST['space_id'] ) ) {
			wp_send_json_error( array( 'message' => 'Space ID is required' ) );
		}

		global $wpdb;
		$table_name = $wpdb->prefix . 'royal_parking_spaces';
		$space_id = intval( $_POST['space_id'] );

		// Check if space is occupied
		$space = $wpdb->get_row( $wpdb->prepare( "SELECT status FROM $table_name WHERE id = %d", $space_id ) );

		if ( ! $space ) {
			wp_send_json_error( array( 'message' => 'Space not found' ) );
		}

		if ( $space->status === 'occupied' ) {
			wp_send_json_error( array( 'message' => 'Cannot delete an occupied space' ) );
		}

		// Delete the space
		$result = $wpdb->delete( $table_name, array( 'id' => $space_id ), array( '%d' ) );

		if ( $result ) {
			wp_send_json_success( array( 'message' => 'Space deleted successfully' ) );
		} else {
			wp_send_json_error( array( 'message' => 'Failed to delete space' ) );
		}
	}

	/**
	 * AJAX handler for getting parking space data
	 *
	 * @return void
	 */
	public function ajax_get_parking_space() {
		// Validate space ID
		if ( empty( $_POST['space_id'] ) ) {
			wp_send_json_error( array( 'message' => 'Space ID is required' ) );
		}

		global $wpdb;
		$table_name = $wpdb->prefix . 'royal_parking_spaces';
		$space_id = intval( $_POST['space_id'] );

		$space = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table_name WHERE id = %d", $space_id ), ARRAY_A );

		if ( ! $space ) {
			wp_send_json_error( array( 'message' => 'Space not found' ) );
		}

		// Get description from post content if post_id exists
		$space['description'] = '';
		if ( ! empty( $space['post_id'] ) ) {
			$post = get_post( $space['post_id'] );
			if ( $post ) {
				$space['description'] = $post->post_content;
			}
		}

		wp_send_json_success( $space );
	}
}
