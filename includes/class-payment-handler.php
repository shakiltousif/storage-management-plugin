<?php
/**
 * Payment Handler Class
 *
 * @package RoyalStorage
 * @since 1.0.0
 */

namespace RoyalStorage;

use RoyalStorage\Models\Booking;

/**
 * Payment Handler
 */
class PaymentHandler {

	/**
	 * Process payment
	 *
	 * @param int    $booking_id Booking ID.
	 * @param string $payment_method Payment method (online, pay_later).
	 * @return bool
	 */
	public function process_payment( $booking_id, $payment_method = 'online' ) {
		$booking = new Booking( $booking_id );

		if ( 'pay_later' === $payment_method ) {
			// Mark as pending payment
			$booking->set_payment_status( 'pending' );
			$booking->set_status( 'confirmed' );
			$booking->save();

			// Send payment reminder email
			$this->send_payment_reminder( $booking_id );

			return true;
		}

		if ( 'online' === $payment_method ) {
			// Create WooCommerce order
			$order_id = $this->create_woocommerce_order( $booking_id );

			if ( $order_id ) {
				// Update booking with order ID
				update_post_meta( $booking_id, '_wc_order_id', $order_id );
				return true;
			}
		}

		return false;
	}

	/**
	 * Create WooCommerce order
	 *
	 * @param int $booking_id Booking ID.
	 * @return int|false
	 */
	private function create_woocommerce_order( $booking_id ) {
		$booking = new Booking( $booking_id );
		$customer_id = $booking->get_customer_id();

		// Create order
		$order = wc_create_order( array( 'customer_id' => $customer_id ) );

		if ( is_wp_error( $order ) ) {
			return false;
		}

		// Add order item
		$order->add_product(
			wc_get_product( 1 ), // Dummy product
			1,
			array(
				'subtotal' => $booking->get_total_price(),
				'total'    => $booking->get_total_price(),
			)
		);

		// Set order total
		$order->set_total( $booking->get_total_price() );

		// Save order
		$order->save();

		return $order->get_id();
	}

	/**
	 * Confirm payment
	 *
	 * @param int $booking_id Booking ID.
	 * @return bool
	 */
	public function confirm_payment( $booking_id ) {
		$booking = new Booking( $booking_id );
		$booking->set_payment_status( 'paid' );
		$booking->set_status( 'confirmed' );
		$booking->save();

		// Send confirmation email
		$this->send_payment_confirmation( $booking_id );

		// Create invoice
		$this->create_invoice_for_booking( $booking_id );

		return true;
	}

	/**
	 * Handle payment failure
	 *
	 * @param int    $booking_id Booking ID.
	 * @param string $reason     Failure reason.
	 * @return bool
	 */
	public function handle_payment_failure( $booking_id, $reason = '' ) {
		$booking = new Booking( $booking_id );
		$booking->set_payment_status( 'failed' );
		$booking->save();

		// Send failure notification
		$this->send_payment_failure_notification( $booking_id, $reason );

		return true;
	}

	/**
	 * Send payment reminder email
	 *
	 * @param int $booking_id Booking ID.
	 * @return bool
	 */
	private function send_payment_reminder( $booking_id ) {
		$booking = new Booking( $booking_id );
		$customer = get_user_by( 'id', $booking->get_customer_id() );

		if ( ! $customer ) {
			return false;
		}

		$subject = __( 'Payment Reminder - Royal Storage', 'royal-storage' );
		$message = sprintf(
			__( 'Dear %s, please complete your payment for booking #%d. Total amount: %s RSD', 'royal-storage' ),
			$customer->display_name,
			$booking_id,
			$booking->get_total_price()
		);

		return wp_mail( $customer->user_email, $subject, $message );
	}

	/**
	 * Send payment confirmation email
	 *
	 * @param int $booking_id Booking ID.
	 * @return bool
	 */
	private function send_payment_confirmation( $booking_id ) {
		$booking = new Booking( $booking_id );
		$customer = get_user_by( 'id', $booking->get_customer_id() );

		if ( ! $customer ) {
			return false;
		}

		$subject = __( 'Payment Confirmed - Royal Storage', 'royal-storage' );
		$message = sprintf(
			__( 'Dear %s, your payment for booking #%d has been confirmed. Your access code is: %s', 'royal-storage' ),
			$customer->display_name,
			$booking_id,
			$booking->get_access_code()
		);

		return wp_mail( $customer->user_email, $subject, $message );
	}

	/**
	 * Send payment failure notification
	 *
	 * @param int    $booking_id Booking ID.
	 * @param string $reason     Failure reason.
	 * @return bool
	 */
	private function send_payment_failure_notification( $booking_id, $reason = '' ) {
		$booking = new Booking( $booking_id );
		$customer = get_user_by( 'id', $booking->get_customer_id() );

		if ( ! $customer ) {
			return false;
		}

		$subject = __( 'Payment Failed - Royal Storage', 'royal-storage' );
		$message = sprintf(
			__( 'Dear %s, your payment for booking #%d failed. Reason: %s. Please try again.', 'royal-storage' ),
			$customer->display_name,
			$booking_id,
			$reason
		);

		return wp_mail( $customer->user_email, $subject, $message );
	}

	/**
	 * Create invoice for booking
	 *
	 * @param int $booking_id Booking ID.
	 * @return int|false
	 */
	private function create_invoice_for_booking( $booking_id ) {
		$booking = new Booking( $booking_id );
		$invoice_generator = new InvoiceGenerator();

		$pricing_engine = new PricingEngine();
		$price_data = $pricing_engine->calculate_price(
			$booking->get_total_price(),
			$booking->get_start_date(),
			$booking->get_end_date(),
			'monthly'
		);

		return $invoice_generator->create_invoice( array(
			'booking_id'   => $booking_id,
			'customer_id'  => $booking->get_customer_id(),
			'amount'       => $price_data['subtotal'],
			'vat_amount'   => $price_data['vat'],
			'total_amount' => $price_data['total'],
			'status'       => 'paid',
		) );
	}

	/**
	 * Get payment status
	 *
	 * @param int $booking_id Booking ID.
	 * @return string
	 */
	public function get_payment_status( $booking_id ) {
		$booking = new Booking( $booking_id );
		return $booking->get_payment_status();
	}

	/**
	 * Refund payment
	 *
	 * @param int $booking_id Booking ID.
	 * @return bool
	 */
	public function refund_payment( $booking_id ) {
		$booking = new Booking( $booking_id );
		$booking->set_payment_status( 'refunded' );
		$booking->save();

		// Send refund notification
		$this->send_refund_notification( $booking_id );

		return true;
	}

	/**
	 * Send refund notification
	 *
	 * @param int $booking_id Booking ID.
	 * @return bool
	 */
	private function send_refund_notification( $booking_id ) {
		$booking = new Booking( $booking_id );
		$customer = get_user_by( 'id', $booking->get_customer_id() );

		if ( ! $customer ) {
			return false;
		}

		$subject = __( 'Refund Processed - Royal Storage', 'royal-storage' );
		$message = sprintf(
			__( 'Dear %s, your refund for booking #%d has been processed. Amount: %s RSD', 'royal-storage' ),
			$customer->display_name,
			$booking_id,
			$booking->get_total_price()
		);

		return wp_mail( $customer->user_email, $subject, $message );
	}
}

