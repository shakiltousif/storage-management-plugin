<?php
/**
 * Payment Processor Class
 *
 * @package RoyalStorage
 * @since 1.0.0
 */

namespace RoyalStorage;

/**
 * Payment processor class
 */
class PaymentProcessor {

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
		$this->wc_integration = new WooCommerceIntegration();
		// AJAX handlers are registered in the main Plugin class to avoid conflicts
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

		// Get or create WooCommerce order
		$order = $this->wc_integration->get_order_by_booking( $booking_id );

		if ( ! $order ) {
			$customer_id = get_current_user_id();
			$order_id = $this->wc_integration->create_order( $booking_id, $customer_id, $amount );

			if ( ! $order_id ) {
				wp_send_json_error( array( 'message' => __( 'Failed to create order', 'royal-storage' ) ) );
			}

			$order = wc_get_order( $order_id );
		}

		// Process payment
		$result = $this->process_payment( $order, $payment_method );

		if ( $result['success'] ) {
			wp_send_json_success( $result );
		} else {
			wp_send_json_error( $result );
		}
	}

	/**
	 * Process payment
	 *
	 * @param \WC_Order $order Order.
	 * @param string    $payment_method Payment method.
	 * @return array
	 */
	public function process_payment( $order, $payment_method = 'card' ) {
		// Get payment gateway
		$gateways = WC()->payment_gateways->get_available_payment_gateways();

		if ( ! isset( $gateways[ $payment_method ] ) ) {
			return array(
				'success' => false,
				'message' => __( 'Payment method not available', 'royal-storage' ),
			);
		}

		$gateway = $gateways[ $payment_method ];

		// Process payment through gateway
		$result = $gateway->process_payment( $order->get_id() );

		return array(
			'success'  => isset( $result['result'] ) && 'success' === $result['result'],
			'message'  => $result['messages'] ?? __( 'Payment processed', 'royal-storage' ),
			'redirect' => $result['redirect'] ?? '',
		);
	}

	/**
	 * Create payment record
	 *
	 * @param int    $booking_id Booking ID.
	 * @param float  $amount Amount.
	 * @param string $method Payment method.
	 * @param string $status Payment status.
	 * @return int|false
	 */
	public function create_payment_record( $booking_id, $amount, $method, $status = 'pending' ) {
		global $wpdb;

		$payments_table = $wpdb->prefix . 'royal_payments';

		$result = $wpdb->insert(
			$payments_table,
			array(
				'booking_id'      => $booking_id,
				'amount'          => $amount,
				'payment_method'  => $method,
				'payment_status'  => $status,
				'created_at'      => current_time( 'mysql' ),
			),
			array( '%d', '%f', '%s', '%s', '%s' )
		);

		return $result ? $wpdb->insert_id : false;
	}

	/**
	 * Get payment records
	 *
	 * @param int $booking_id Booking ID.
	 * @return array
	 */
	public function get_payment_records( $booking_id ) {
		global $wpdb;

		$payments_table = $wpdb->prefix . 'royal_payments';

		return $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM $payments_table WHERE booking_id = %d ORDER BY created_at DESC",
				$booking_id
			)
		);
	}

	/**
	 * Get total paid amount
	 *
	 * @param int $booking_id Booking ID.
	 * @return float
	 */
	public function get_total_paid( $booking_id ) {
		global $wpdb;

		$payments_table = $wpdb->prefix . 'royal_payments';

		$total = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT SUM(amount) FROM $payments_table WHERE booking_id = %d AND payment_status = 'completed'",
				$booking_id
			)
		);

		return floatval( $total ?: 0 );
	}

	/**
	 * Get remaining balance
	 *
	 * @param int $booking_id Booking ID.
	 * @return float
	 */
	public function get_remaining_balance( $booking_id ) {
		global $wpdb;

		$bookings_table = $wpdb->prefix . 'royal_bookings';

		$booking = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT total_price FROM $bookings_table WHERE id = %d",
				$booking_id
			)
		);

		if ( ! $booking ) {
			return 0;
		}

		$paid = $this->get_total_paid( $booking_id );

		return max( 0, floatval( $booking->total_price ) - $paid );
	}

	/**
	 * Refund payment
	 *
	 * @param int    $payment_id Payment ID.
	 * @param float  $amount Amount to refund.
	 * @return bool
	 */
	public function refund_payment( $payment_id, $amount = null ) {
		global $wpdb;

		$payments_table = $wpdb->prefix . 'royal_payments';

		$payment = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM $payments_table WHERE id = %d",
				$payment_id
			)
		);

		if ( ! $payment ) {
			return false;
		}

		$refund_amount = $amount ?? $payment->amount;

		return $wpdb->update(
			$payments_table,
			array( 'payment_status' => 'refunded' ),
			array( 'id' => $payment_id ),
			array( '%s' ),
			array( '%d' )
		);
	}
}

