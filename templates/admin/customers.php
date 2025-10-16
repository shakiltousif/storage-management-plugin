<?php
/**
 * Admin Customers Template
 *
 * @package RoyalStorage
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$customers_admin = new \RoyalStorage\Admin\Customers();
$page = isset( $_GET['paged'] ) ? intval( $_GET['paged'] ) : 1;
$limit = 20;
$offset = ( $page - 1 ) * $limit;
$customers = $customers_admin->get_customers( $limit, $offset );
$total = $customers_admin->get_customers_count();
$total_pages = ceil( $total / $limit );

// Check if viewing single customer
$customer_id = isset( $_GET['customer'] ) ? intval( $_GET['customer'] ) : 0;
if ( $customer_id > 0 ) {
	$customer_info = $customers_admin->get_customer_info( $customer_id );
	$customer_bookings = $customers_admin->get_customer_bookings( $customer_id );
	?>
	<div class="wrap royal-storage-admin">
		<h1><?php esc_html_e( 'Customer Details', 'royal-storage' ); ?></h1>

		<a href="<?php echo esc_url( admin_url( 'admin.php?page=royal-storage-customers' ) ); ?>" class="button">
			<?php esc_html_e( '← Back to Customers', 'royal-storage' ); ?>
		</a>

		<?php if ( $customer_info ) : ?>
			<div class="customer-details">
				<div class="customer-card">
					<h2><?php echo esc_html( $customer_info->name ); ?></h2>
					<p><strong><?php esc_html_e( 'Email:', 'royal-storage' ); ?></strong> <?php echo esc_html( $customer_info->email ); ?></p>
					<p><strong><?php esc_html_e( 'Phone:', 'royal-storage' ); ?></strong> <?php echo esc_html( $customer_info->phone ?: __( 'Not provided', 'royal-storage' ) ); ?></p>
				</div>

				<div class="customer-stats">
					<div class="stat-card">
						<h3><?php esc_html_e( 'Total Spent', 'royal-storage' ); ?></h3>
						<p class="stat-value"><?php echo esc_html( number_format( $customer_info->total_spent, 2 ) ); ?> RSD</p>
					</div>
					<div class="stat-card">
						<h3><?php esc_html_e( 'Overdue Amount', 'royal-storage' ); ?></h3>
						<p class="stat-value"><?php echo esc_html( number_format( $customer_info->overdue_amount, 2 ) ); ?> RSD</p>
					</div>
					<div class="stat-card">
						<h3><?php esc_html_e( 'Active Bookings', 'royal-storage' ); ?></h3>
						<p class="stat-value"><?php echo esc_html( $customer_info->active_bookings ); ?></p>
					</div>
				</div>

				<h3><?php esc_html_e( 'Booking History', 'royal-storage' ); ?></h3>
				<table class="wp-list-table widefat fixed striped">
					<thead>
						<tr>
							<th><?php esc_html_e( 'ID', 'royal-storage' ); ?></th>
							<th><?php esc_html_e( 'Start Date', 'royal-storage' ); ?></th>
							<th><?php esc_html_e( 'End Date', 'royal-storage' ); ?></th>
							<th><?php esc_html_e( 'Total Price', 'royal-storage' ); ?></th>
							<th><?php esc_html_e( 'Status', 'royal-storage' ); ?></th>
							<th><?php esc_html_e( 'Payment', 'royal-storage' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php if ( ! empty( $customer_bookings ) ) : ?>
							<?php foreach ( $customer_bookings as $booking ) : ?>
								<tr>
									<td><?php echo esc_html( $booking->id ); ?></td>
									<td><?php echo esc_html( $booking->start_date ); ?></td>
									<td><?php echo esc_html( $booking->end_date ); ?></td>
									<td><?php echo esc_html( number_format( $booking->total_price, 2 ) ); ?> RSD</td>
									<td>
										<span class="status-badge status-<?php echo esc_attr( $booking->status ); ?>">
											<?php echo esc_html( ucfirst( $booking->status ) ); ?>
										</span>
									</td>
									<td>
										<span class="payment-badge payment-<?php echo esc_attr( $booking->payment_status ); ?>">
											<?php echo esc_html( ucfirst( $booking->payment_status ) ); ?>
										</span>
									</td>
								</tr>
							<?php endforeach; ?>
						<?php else : ?>
							<tr>
								<td colspan="6" style="text-align: center; padding: 20px;">
									<?php esc_html_e( 'No bookings found.', 'royal-storage' ); ?>
								</td>
							</tr>
						<?php endif; ?>
					</tbody>
				</table>
			</div>
		<?php else : ?>
			<div class="notice notice-error">
				<p><?php esc_html_e( 'Customer not found.', 'royal-storage' ); ?></p>
			</div>
		<?php endif; ?>
	</div>
	<?php
} else {
	// List all customers
	?>
	<div class="wrap royal-storage-admin">
		<h1><?php esc_html_e( 'Customers', 'royal-storage' ); ?></h1>

		<table class="wp-list-table widefat fixed striped">
			<thead>
				<tr>
					<th><?php esc_html_e( 'Customer ID', 'royal-storage' ); ?></th>
					<th><?php esc_html_e( 'Name', 'royal-storage' ); ?></th>
					<th><?php esc_html_e( 'Email', 'royal-storage' ); ?></th>
					<th><?php esc_html_e( 'Total Spent', 'royal-storage' ); ?></th>
					<th><?php esc_html_e( 'Overdue Amount', 'royal-storage' ); ?></th>
					<th><?php esc_html_e( 'Active Bookings', 'royal-storage' ); ?></th>
					<th><?php esc_html_e( 'Actions', 'royal-storage' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php if ( ! empty( $customers ) ) : ?>
					<?php foreach ( $customers as $customer_row ) : ?>
						<?php $customer_info = $customers_admin->get_customer_info( $customer_row->customer_id ); ?>
						<?php if ( $customer_info ) : ?>
							<tr>
								<td><?php echo esc_html( $customer_info->id ); ?></td>
								<td><?php echo esc_html( $customer_info->name ); ?></td>
								<td><?php echo esc_html( $customer_info->email ); ?></td>
								<td><?php echo esc_html( number_format( $customer_info->total_spent, 2 ) ); ?> RSD</td>
								<td>
									<?php if ( $customer_info->overdue_amount > 0 ) : ?>
										<span style="color: #c33; font-weight: bold;">
											<?php echo esc_html( number_format( $customer_info->overdue_amount, 2 ) ); ?> RSD
										</span>
									<?php else : ?>
										<?php echo esc_html( number_format( $customer_info->overdue_amount, 2 ) ); ?> RSD
									<?php endif; ?>
								</td>
								<td><?php echo esc_html( $customer_info->active_bookings ); ?></td>
								<td>
									<a href="<?php echo esc_url( admin_url( 'admin.php?page=royal-storage-customers&customer=' . $customer_info->id ) ); ?>" class="button button-small">
										<?php esc_html_e( 'View', 'royal-storage' ); ?>
									</a>
								</td>
							</tr>
						<?php endif; ?>
					<?php endforeach; ?>
				<?php else : ?>
					<tr>
						<td colspan="7" style="text-align: center; padding: 20px;">
							<?php esc_html_e( 'No customers found.', 'royal-storage' ); ?>
						</td>
					</tr>
				<?php endif; ?>
			</tbody>
		</table>

		<?php if ( $total_pages > 1 ) : ?>
			<div class="tablenav bottom">
				<div class="tablenav-pages">
					<?php
					echo wp_kses_post(
						paginate_links(
							array(
								'base'      => admin_url( 'admin.php?page=royal-storage-customers&paged=%#%' ),
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
	</div>
	<?php
}
?>

<style>
	.customer-details {
		margin-top: 20px;
	}

	.customer-card {
		background: white;
		padding: 20px;
		margin-bottom: 20px;
		border-radius: 5px;
		box-shadow: 0 1px 3px rgba(0,0,0,0.1);
	}

	.customer-card h2 {
		margin-top: 0;
	}

	.customer-stats {
		display: grid;
		grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
		gap: 15px;
		margin-bottom: 20px;
	}

	.stat-card {
		background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
		color: white;
		padding: 20px;
		border-radius: 5px;
		text-align: center;
	}

	.stat-card h3 {
		margin: 0 0 10px 0;
		font-size: 14px;
	}

	.stat-value {
		margin: 0;
		font-size: 20px;
		font-weight: bold;
	}

	.status-badge,
	.payment-badge {
		display: inline-block;
		padding: 5px 10px;
		border-radius: 3px;
		font-size: 12px;
		font-weight: bold;
		color: white;
	}

	.status-pending {
		background-color: #f39c12;
	}

	.status-confirmed {
		background-color: #3498db;
	}

	.status-active {
		background-color: #27ae60;
	}

	.status-cancelled {
		background-color: #e74c3c;
	}

	.payment-pending,
	.payment-unpaid {
		background-color: #e74c3c;
	}

	.payment-paid {
		background-color: #27ae60;
	}
</style>

