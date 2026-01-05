<?php
/**
 * Admin Bookings Template
 *
 * @package RoyalStorage
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$bookings_admin = new \RoyalStorage\Admin\Bookings();
$page = isset( $_GET['paged'] ) ? intval( $_GET['paged'] ) : 1;
$limit = 20;
$offset = ( $page - 1 ) * $limit;
$bookings = $bookings_admin->get_bookings( $limit, $offset );
$total = $bookings_admin->get_bookings_count();
$total_pages = ceil( $total / $limit );
?>

<div class="wrap royal-storage-admin">
	<h1><?php esc_html_e( 'Bookings Management', 'royal-storage' ); ?></h1>

	<?php if ( isset( $_GET['message'] ) ) : ?>
		<div class="notice notice-success is-dismissible">
			<p>
				<?php
				if ( 'created' === $_GET['message'] ) {
					esc_html_e( 'Booking created successfully!', 'royal-storage' );
				} elseif ( 'cancelled' === $_GET['message'] ) {
					esc_html_e( 'Booking cancelled successfully!', 'royal-storage' );
				} else {
					esc_html_e( 'Operation completed!', 'royal-storage' );
				}
				?>
			</p>
		</div>
	<?php endif; ?>

	<div class="bookings-actions">
		<a href="<?php echo esc_url( admin_url( 'admin.php?page=royal-storage-bookings&action=new' ) ); ?>" class="button button-primary">
			<?php esc_html_e( '+ Create New Booking', 'royal-storage' ); ?>
		</a>
	</div>

	<div class="royal-storage-widget">
		<table class="wp-list-table widefat fixed striped">
			<thead>
				<tr>
					<th><?php esc_html_e( 'ID', 'royal-storage' ); ?></th>
					<th><?php esc_html_e( 'Customer', 'royal-storage' ); ?></th>
					<th><?php esc_html_e( 'Unit/Space', 'royal-storage' ); ?></th>
					<th><?php esc_html_e( 'Start Date', 'royal-storage' ); ?></th>
					<th><?php esc_html_e( 'End Date', 'royal-storage' ); ?></th>
					<th><?php esc_html_e( 'Total Price', 'royal-storage' ); ?></th>
					<th><?php esc_html_e( 'Status', 'royal-storage' ); ?></th>
					<th><?php esc_html_e( 'Payment', 'royal-storage' ); ?></th>
					<th><?php esc_html_e( 'Actions', 'royal-storage' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php if ( ! empty( $bookings ) ) : ?>
					<?php foreach ( $bookings as $booking ) : ?>
						<?php $customer = get_user_by( 'id', $booking->customer_id ); ?>
						<tr>
							<td><?php echo esc_html( $booking->id ); ?></td>
							<td>
								<?php if ( $customer ) : ?>
									<a href="<?php echo esc_url( admin_url( 'admin.php?page=royal-storage-customers&customer=' . $booking->customer_id ) ); ?>">
										<?php echo esc_html( $customer->display_name ); ?>
									</a>
								<?php else : ?>
									<?php esc_html_e( 'Unknown', 'royal-storage' ); ?>
								<?php endif; ?>
							</td>
							<td>
								<?php
								if ( $booking->unit_id > 0 ) {
									echo esc_html( 'Unit #' . $booking->unit_id );
								} elseif ( $booking->space_id > 0 ) {
									echo esc_html( 'Space #' . $booking->space_id );
								}
								?>
							</td>
							<td><?php echo esc_html( $booking->start_date ); ?></td>
							<td><?php echo esc_html( $booking->end_date ); ?></td>
							<td><?php echo esc_html( number_format( $booking->total_price, 2 ) ); ?> RSD</td>
							<td>
								<span class="status-badge status-<?php echo esc_attr( $booking->status ); ?>">
									<?php echo esc_html( ucfirst( $booking->status ) ); ?>
								</span>
							</td>
							<td>
								<span class="status-badge status-<?php echo esc_attr( $booking->payment_status ); ?>">
									<?php echo esc_html( ucfirst( $booking->payment_status ) ); ?>
								</span>
							</td>
							<td>
								<button type="button" class="button button-small view-booking" data-booking-id="<?php echo esc_attr( $booking->id ); ?>">
									<?php esc_html_e( 'View', 'royal-storage' ); ?>
								</button>
								<?php if ( in_array( $booking->status, array( 'pending', 'confirmed', 'active' ), true ) ) : ?>
									<button type="button" class="button button-small reassign-unit-btn" data-booking-id="<?php echo esc_attr( $booking->id ); ?>">
										<?php esc_html_e( 'Reassign Unit', 'royal-storage' ); ?>
									</button>
								<?php endif; ?>
								<?php if ( 'pending' === $booking->status ) : ?>
									<button type="button" class="button button-small button-primary approve-booking" data-booking-id="<?php echo esc_attr( $booking->id ); ?>">
										<?php esc_html_e( 'Approve', 'royal-storage' ); ?>
									</button>
								<?php endif; ?>
								<?php if ( 'cancelled' !== $booking->status && 'completed' !== $booking->status ) : ?>
									<button type="button" class="button button-small button-link-delete cancel-booking-btn" data-booking-id="<?php echo esc_attr( $booking->id ); ?>">
										<?php esc_html_e( 'Cancel', 'royal-storage' ); ?>
									</button>
								<?php endif; ?>
							</td>
						</tr>
					<?php endforeach; ?>
				<?php else : ?>
					<tr>
						<td colspan="9" class="no-customers">
							<?php esc_html_e( 'No bookings found.', 'royal-storage' ); ?>
						</td>
					</tr>
				<?php endif; ?>
			</tbody>
		</table>
	</div>

	<?php if ( $total_pages > 1 ) : ?>
		<div class="tablenav bottom">
			<div class="tablenav-pages">
				<?php
				echo wp_kses_post(
					paginate_links(
						array(
							'base'      => admin_url( 'admin.php?page=royal-storage-bookings&paged=%#%' ),
							'format'    => '%#%',
							'prev_text' => __( '&laquo; Previous' ),
							'next_text' => __( 'Next &raquo;' ),
							'total'     => $total_pages,
							'current'   => $page,
						)
					)
				);
				?>
			</div>
		</div>
	<?php endif; ?>

	<!-- Unit Reassignment Modal - Added 2026-01-06 -->
	<div id="reassign-unit-modal" class="royal-storage-modal" style="display: none;">
		<div class="modal-overlay"></div>
		<div class="modal-content">
			<div class="modal-header">
				<h2><?php esc_html_e( 'Reassign Unit', 'royal-storage' ); ?></h2>
				<button type="button" class="modal-close">&times;</button>
			</div>
			<div class="modal-body">
				<div class="reassign-info">
					<p><strong><?php esc_html_e( 'Booking ID:', 'royal-storage' ); ?></strong> <span id="reassign-booking-id"></span></p>
					<p><strong><?php esc_html_e( 'Current Unit:', 'royal-storage' ); ?></strong> <span id="reassign-current-unit"></span></p>
					<p><strong><?php esc_html_e( 'Dates:', 'royal-storage' ); ?></strong> <span id="reassign-dates"></span></p>
				</div>
				<div class="form-group">
					<label for="new-unit-id"><?php esc_html_e( 'Select New Unit:', 'royal-storage' ); ?></label>
					<select id="new-unit-id" name="new_unit_id" required>
						<option value=""><?php esc_html_e( 'Loading available units...', 'royal-storage' ); ?></option>
					</select>
					<p class="description"><?php esc_html_e( 'Only units available for the booking dates are shown.', 'royal-storage' ); ?></p>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="button modal-close"><?php esc_html_e( 'Cancel', 'royal-storage' ); ?></button>
				<button type="button" id="confirm-reassignment" class="button button-primary">
					<?php esc_html_e( 'Reassign Unit', 'royal-storage' ); ?>
				</button>
			</div>
		</div>
	</div>
</div>
