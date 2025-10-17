<?php
/**
 * Checkout Class
 *
 * @package RoyalStorage\Frontend
 * @since 1.0.0
 */

namespace RoyalStorage\Frontend;

use RoyalStorage\PaymentProcessor;
use RoyalStorage\WooCommerceIntegration;

/**
 * Checkout class for frontend checkout and payment processing
 */
class Checkout {

	/**
	 * Payment processor instance
	 *
	 * @var PaymentProcessor
	 */
	private $payment_processor;

	/**
	 * WooCommerce integration instance
	 *
	 * @var WooCommerceIntegration
	 */
	private $wc_integration;

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->payment_processor = new PaymentProcessor();
		$this->wc_integration = new WooCommerceIntegration();

		add_action( 'wp_enqueue_scripts', array( $this, 'init' ) );
		add_action( 'wp_ajax_process_booking', array( $this, 'process_booking' ) );
		add_action( 'wp_ajax_nopriv_process_booking', array( $this, 'process_booking' ) );
		add_shortcode( 'royal_storage_checkout', array( $this, 'render_checkout' ) );
	}

	/**
	 * Initialize checkout
	 *
	 * @return void
	 */
	public function init() {
		// Enqueue checkout assets
		wp_enqueue_script( 'royal-storage-checkout', ROYAL_STORAGE_URL . 'assets/js/checkout.js', array( 'jquery' ), ROYAL_STORAGE_VERSION, true );
		
		// Localize script for AJAX
		wp_localize_script( 'royal-storage-checkout', 'royalStorageCheckout', array(
			'ajaxUrl' => admin_url( 'admin-ajax.php' ),
			'portalUrl' => home_url( '/customer-portal-test/' ),
			'nonce' => wp_create_nonce( 'royal_storage_payment' )
		) );
	}

	/**
	 * Process booking via AJAX
	 *
	 * @return void
	 */
	public function process_booking() {
		check_ajax_referer( 'royal-storage-nonce', 'nonce' );

		if ( ! is_user_logged_in() ) {
			wp_send_json_error( array( 'message' => __( 'Please log in to book.', 'royal-storage' ) ) );
		}

		$unit_id = isset( $_POST['unit_id'] ) ? intval( $_POST['unit_id'] ) : 0;
		$unit_type = isset( $_POST['unit_type'] ) ? sanitize_text_field( wp_unslash( $_POST['unit_type'] ) ) : '';
		$start_date = isset( $_POST['start_date'] ) ? sanitize_text_field( wp_unslash( $_POST['start_date'] ) ) : '';
		$end_date = isset( $_POST['end_date'] ) ? sanitize_text_field( wp_unslash( $_POST['end_date'] ) ) : '';
		$payment_method = isset( $_POST['payment_method'] ) ? sanitize_text_field( wp_unslash( $_POST['payment_method'] ) ) : 'online';

		if ( ! $unit_id || ! $unit_type || ! $start_date || ! $end_date ) {
			wp_send_json_error( array( 'message' => __( 'Missing required fields.', 'royal-storage' ) ) );
		}

		$customer_id = get_current_user_id();

		// Create booking.
		$booking_id = $this->create_booking(
			array(
				'customer_id'    => $customer_id,
				'unit_id'        => $unit_id,
				'unit_type'      => $unit_type,
				'start_date'     => $start_date,
				'end_date'       => $end_date,
				'payment_method' => $payment_method,
			)
		);

		if ( ! $booking_id ) {
			wp_send_json_error( array( 'message' => __( 'Failed to create booking.', 'royal-storage' ) ) );
		}

		wp_send_json_success(
			array(
				'booking_id' => $booking_id,
				'message'    => __( 'Booking created successfully.', 'royal-storage' ),
			)
		);
	}

	/**
	 * Create booking
	 *
	 * @param array $booking_data Booking data.
	 * @return int|false Booking ID or false on failure
	 */
	private function create_booking( $booking_data ) {
		global $wpdb;

		$bookings_table = $wpdb->prefix . 'royal_bookings';

		// Calculate price.
		$booking_obj = new Booking();
		$price_data = $booking_obj->calculate_booking_price(
			100, // Base price - should be fetched from unit.
			$booking_data['start_date'],
			$booking_data['end_date'],
			'daily'
		);

		// Generate access code.
		$access_code = $this->generate_access_code();

		$result = $wpdb->insert(
			$bookings_table,
			array(
				'customer_id'    => $booking_data['customer_id'],
				'unit_id'        => $booking_data['unit_id'],
				'unit_type'      => $booking_data['unit_type'],
				'start_date'     => $booking_data['start_date'],
				'end_date'       => $booking_data['end_date'],
				'total_price'    => $price_data['total'],
				'vat_amount'     => $price_data['vat'],
				'status'         => 'pending',
				'payment_status' => 'unpaid',
				'access_code'    => $access_code,
			),
			array( '%d', '%d', '%s', '%s', '%s', '%f', '%f', '%s', '%s', '%s' )
		);

		return $result ? $wpdb->insert_id : false;
	}

	/**
	 * Generate access code
	 *
	 * @return string
	 */
	private function generate_access_code() {
		return strtoupper( substr( md5( uniqid( rand(), true ) ), 0, 8 ) );
	}

	/**
	 * Handle payment AJAX
	 *
	 * @return void
	 */
	public function handle_payment() {
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'royal_storage_payment' ) ) {
			wp_send_json_error( array( 'message' => __( 'Security check failed', 'royal-storage' ) ) );
		}

		$booking_id = isset( $_POST['booking_id'] ) ? intval( $_POST['booking_id'] ) : 0;
		$amount = isset( $_POST['amount'] ) ? floatval( $_POST['amount'] ) : 0;
		$payment_method = isset( $_POST['payment_method'] ) ? sanitize_text_field( wp_unslash( $_POST['payment_method'] ) ) : 'card';

		if ( ! $this->wc_integration->validate_payment_amount( $amount ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid payment amount', 'royal-storage' ) ) );
		}

		// Add product to cart and redirect to checkout
		$customer_id = get_current_user_id();
		$product_id = $this->wc_integration->create_order( $booking_id, $customer_id, $amount );

		if ( ! $product_id ) {
			wp_send_json_error( array( 'message' => __( 'Failed to add product to cart', 'royal-storage' ) ) );
		}

		// Create payment record
		$payment_id = $this->payment_processor->create_payment_record( $booking_id, $amount, $payment_method, 'pending' );

		if ( ! $payment_id ) {
			wp_send_json_error( array( 'message' => __( 'Failed to create payment record', 'royal-storage' ) ) );
		}

		// Redirect to WooCommerce checkout
		$checkout_url = $this->get_woocommerce_checkout_url();

		wp_send_json_success(
			array(
				'message'     => __( 'Redirecting to checkout...', 'royal-storage' ),
				'product_id'  => $product_id,
				'payment_id'  => $payment_id,
				'checkout_url' => $checkout_url,
				'redirect'    => true
			)
		);
	}

	/**
	 * Get WooCommerce checkout URL
	 *
	 * @return string
	 */
	private function get_woocommerce_checkout_url() {
		// Get WooCommerce checkout page URL
		return wc_get_checkout_url();
	}

	/**
	 * Simulate payment completion for development/testing
	 *
	 * @param int $booking_id Booking ID.
	 * @param object $order WooCommerce order.
	 * @param int $payment_id Payment ID.
	 * @return void
	 */
	private function simulate_payment_completion( $booking_id, $order, $payment_id ) {
		global $wpdb;

		// Update WooCommerce order status to completed
		$order->set_status( 'completed' );
		$order->save();

		// Update booking payment status
		$bookings_table = $wpdb->prefix . 'royal_bookings';
		$wpdb->update(
			$bookings_table,
			array( 
				'payment_status' => 'paid',
				'status' => 'confirmed'
			),
			array( 'id' => $booking_id ),
			array( '%s', '%s' ),
			array( '%d' )
		);

		// Update payment record status
		$payments_table = $wpdb->prefix . 'royal_payments';
		$wpdb->update(
			$payments_table,
			array( 
				'payment_status' => 'completed',
				'transaction_id' => 'TXN_' . $payment_id . '_' . time()
			),
			array( 'id' => $payment_id ),
			array( '%s', '%s' ),
			array( '%d' )
		);
	}

	/**
	 * Render checkout page
	 *
	 * @return string
	 */
	public function render_checkout() {
		if ( ! is_user_logged_in() ) {
			return '<p>' . esc_html__( 'Please log in to proceed with checkout.', 'royal-storage' ) . '</p>';
		}

		$booking_id = isset( $_GET['booking_id'] ) ? intval( $_GET['booking_id'] ) : 0;
		$invoice_id = isset( $_GET['invoice_id'] ) ? intval( $_GET['invoice_id'] ) : 0;

		ob_start();
		?>
		<div class="royal-storage-checkout">
			<div class="checkout-container">
				<div class="checkout-summary">
					<?php
					if ( $booking_id > 0 ) {
						$this->render_booking_summary( $booking_id );
					} elseif ( $invoice_id > 0 ) {
						$this->render_invoice_summary( $invoice_id );
					}
					?>
				</div>

				<div class="checkout-form">
					<h2><?php esc_html_e( 'Payment Details', 'royal-storage' ); ?></h2>
					<?php $this->render_payment_form( $booking_id, $invoice_id ); ?>
				</div>
			</div>
		</div>
		<?php
		return ob_get_clean();
	}

	/**
	 * Render booking summary
	 *
	 * @param int $booking_id Booking ID.
	 * @return void
	 */
	private function render_booking_summary( $booking_id ) {
		global $wpdb;

		$bookings_table = $wpdb->prefix . 'royal_bookings';
		$booking = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM $bookings_table WHERE id = %d",
				$booking_id
			)
		);

		if ( ! $booking ) {
			echo '<p>' . esc_html__( 'Booking not found.', 'royal-storage' ) . '</p>';
			return;
		}

		$remaining = $this->payment_processor->get_remaining_balance( $booking_id );
		?>
		<div class="summary-card">
			<h3><?php esc_html_e( 'Booking Summary', 'royal-storage' ); ?></h3>
			<div class="summary-row">
				<span><?php esc_html_e( 'Booking ID:', 'royal-storage' ); ?></span>
				<span><?php echo esc_html( $booking->id ); ?></span>
			</div>
			<div class="summary-row">
				<span><?php esc_html_e( 'Start Date:', 'royal-storage' ); ?></span>
				<span><?php echo esc_html( $booking->start_date ); ?></span>
			</div>
			<div class="summary-row">
				<span><?php esc_html_e( 'End Date:', 'royal-storage' ); ?></span>
				<span><?php echo esc_html( $booking->end_date ); ?></span>
			</div>
			<div class="summary-row">
				<span><?php esc_html_e( 'Total Price:', 'royal-storage' ); ?></span>
				<span><?php echo esc_html( number_format( $booking->total_price, 2 ) ); ?> RSD</span>
			</div>
			<div class="summary-row total">
				<span><?php esc_html_e( 'Amount Due:', 'royal-storage' ); ?></span>
				<span><?php echo esc_html( number_format( $remaining, 2 ) ); ?> RSD</span>
			</div>
		</div>
		<?php
	}

	/**
	 * Render invoice summary
	 *
	 * @param int $invoice_id Invoice ID.
	 * @return void
	 */
	private function render_invoice_summary( $invoice_id ) {
		global $wpdb;

		$invoices_table = $wpdb->prefix . 'royal_invoices';
		$invoice = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM $invoices_table WHERE id = %d",
				$invoice_id
			)
		);

		if ( ! $invoice ) {
			echo '<p>' . esc_html__( 'Invoice not found.', 'royal-storage' ) . '</p>';
			return;
		}
		?>
		<div class="summary-card">
			<h3><?php esc_html_e( 'Invoice Summary', 'royal-storage' ); ?></h3>
			<div class="summary-row">
				<span><?php esc_html_e( 'Invoice #:', 'royal-storage' ); ?></span>
				<span><?php echo esc_html( $invoice->invoice_number ); ?></span>
			</div>
			<div class="summary-row">
				<span><?php esc_html_e( 'Due Date:', 'royal-storage' ); ?></span>
				<span><?php echo esc_html( $invoice->due_date ); ?></span>
			</div>
			<div class="summary-row total">
				<span><?php esc_html_e( 'Amount Due:', 'royal-storage' ); ?></span>
				<span><?php echo esc_html( number_format( $invoice->total_amount, 2 ) ); ?> RSD</span>
			</div>
		</div>
		<?php
	}

	/**
	 * Render payment form
	 *
	 * @param int $booking_id Booking ID.
	 * @param int $invoice_id Invoice ID.
	 * @return void
	 */
	private function render_payment_form( $booking_id, $invoice_id ) {
		$amount = 0;

		if ( $booking_id > 0 ) {
			$amount = $this->payment_processor->get_remaining_balance( $booking_id );
		} elseif ( $invoice_id > 0 ) {
			global $wpdb;
			$invoices_table = $wpdb->prefix . 'royal_invoices';
			$invoice = $wpdb->get_row(
				$wpdb->prepare(
					"SELECT total_amount FROM $invoices_table WHERE id = %d",
					$invoice_id
				)
			);
			$amount = $invoice ? $invoice->total_amount : 0;
		}
		?>
		<form id="payment-form" class="payment-form">
			<input type="hidden" name="booking_id" value="<?php echo esc_attr( $booking_id ); ?>">
			<input type="hidden" name="invoice_id" value="<?php echo esc_attr( $invoice_id ); ?>">
			<input type="hidden" name="nonce" value="<?php echo esc_attr( wp_create_nonce( 'royal_storage_payment' ) ); ?>">

			<div class="form-group">
				<label><?php esc_html_e( 'Amount', 'royal-storage' ); ?></label>
				<input type="text" value="<?php echo esc_attr( number_format( $amount, 2 ) ); ?> RSD" disabled>
				<input type="hidden" name="amount" value="<?php echo esc_attr( $amount ); ?>">
			</div>

			<div class="form-group">
				<label for="payment_method"><?php esc_html_e( 'Payment Method', 'royal-storage' ); ?></label>
				<select id="payment_method" name="payment_method" required>
					<option value="card"><?php esc_html_e( 'Credit/Debit Card', 'royal-storage' ); ?></option>
					<option value="bank_transfer"><?php esc_html_e( 'Bank Transfer', 'royal-storage' ); ?></option>
				</select>
			</div>

			<button type="submit" class="btn btn-primary">
				<?php esc_html_e( 'Proceed to Payment', 'royal-storage' ); ?>
			</button>
		</form>
		<?php
	}
}

