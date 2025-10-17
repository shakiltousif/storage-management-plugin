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
		// Add any initialization code here
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
								<th><?php esc_html_e( 'Type', 'royal-storage' ); ?></th>
								<th><?php esc_html_e( 'Location', 'royal-storage' ); ?></th>
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
										<td><?php echo esc_html( $space['type'] ); ?></td>
										<td><?php echo esc_html( $space['location'] ); ?></td>
										<td><?php echo esc_html( number_format( $space['price'], 2 ) ); ?> RSD</td>
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
					<h2 id="modal-title"><?php esc_html_e( 'Add Parking Space', 'royal-storage' ); ?></h2>
					<button type="button" class="royal-storage-modal-close">&times;</button>
				</div>
				<form id="parking-space-form">
					<?php wp_nonce_field( 'royal_storage_parking_space_form', 'parking_space_nonce' ); ?>
					<input type="hidden" id="space-id" name="space_id" value="">
					
					<div class="royal-storage-form-group">
						<label for="space-type"><?php esc_html_e( 'Type', 'royal-storage' ); ?> *</label>
						<select id="space-type" name="type" required>
							<option value="covered"><?php esc_html_e( 'Covered', 'royal-storage' ); ?></option>
							<option value="uncovered"><?php esc_html_e( 'Uncovered', 'royal-storage' ); ?></option>
							<option value="garage"><?php esc_html_e( 'Garage', 'royal-storage' ); ?></option>
						</select>
					</div>
					
					<div class="royal-storage-form-group">
						<label for="space-location"><?php esc_html_e( 'Location', 'royal-storage' ); ?> *</label>
						<input type="text" id="space-location" name="location" placeholder="e.g., Level 1, Section A" required>
					</div>
					
					<div class="royal-storage-form-group">
						<label for="space-price"><?php esc_html_e( 'Price (RSD)', 'royal-storage' ); ?> *</label>
						<input type="number" id="space-price" name="price" step="0.01" min="0" required>
					</div>
					
					<div class="royal-storage-form-group">
						<label for="space-description"><?php esc_html_e( 'Description', 'royal-storage' ); ?></label>
						<textarea id="space-description" name="description" rows="3"></textarea>
					</div>
					
					<div class="royal-storage-form-actions">
						<button type="submit" class="button button-primary">
							<?php esc_html_e( 'Save Space', 'royal-storage' ); ?>
						</button>
						<button type="button" class="button royal-storage-modal-close">
							<?php esc_html_e( 'Cancel', 'royal-storage' ); ?>
						</button>
					</div>
				</form>
			</div>
		</div>

		<style>
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
		
		.royal-storage-modal {
			display: none;
			position: fixed;
			top: 0;
			left: 0;
			width: 100%;
			height: 100%;
			background: rgba(0,0,0,0.5);
			z-index: 9999;
		}
		
		.royal-storage-modal.show {
			display: block !important;
		}
		
		.royal-storage-modal-content {
			position: absolute;
			top: 50%;
			left: 50%;
			transform: translate(-50%, -50%);
			background: white;
			border-radius: 8px;
			width: 90%;
			max-width: 500px;
			max-height: 90vh;
			overflow-y: auto;
		}
		
		.royal-storage-modal-header {
			display: flex;
			justify-content: space-between;
			align-items: center;
			padding: 20px;
			border-bottom: 1px solid #e1e1e1;
		}
		
		.royal-storage-modal-close {
			background: none;
			border: none;
			font-size: 24px;
			cursor: pointer;
		}
		
		.royal-storage-form-group {
			margin-bottom: 20px;
			padding: 0 20px;
		}
		
		.royal-storage-form-group label {
			display: block;
			margin-bottom: 5px;
			font-weight: bold;
		}
		
		.royal-storage-form-group input,
		.royal-storage-form-group select,
		.royal-storage-form-group textarea {
			width: 100%;
			padding: 8px;
			border: 1px solid #ddd;
			border-radius: 4px;
		}
		
		.royal-storage-form-actions {
			padding: 20px;
			border-top: 1px solid #e1e1e1;
			display: flex;
			gap: 10px;
			justify-content: flex-end;
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
				// Load space data and populate form
				$('#modal-title').text('Edit Parking Space');
				$('#space-id').val(spaceId);
				$('#parking-space-modal').addClass('show');
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
		// This would typically come from the database
		// For now, return sample data
		return array(
			array(
				'id' => 1,
				'type' => 'Covered',
				'location' => 'Level 1, Section A',
				'price' => 5000,
				'status' => 'available',
				'description' => 'Covered parking space'
			),
			array(
				'id' => 2,
				'type' => 'Uncovered',
				'location' => 'Level 2, Section B',
				'price' => 3000,
				'status' => 'occupied',
				'description' => 'Uncovered parking space'
			),
			array(
				'id' => 3,
				'type' => 'Garage',
				'location' => 'Level 3, Section C',
				'price' => 8000,
				'status' => 'available',
				'description' => 'Private garage space'
			)
		);
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
}
