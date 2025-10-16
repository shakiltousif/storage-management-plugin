<?php
/**
 * WooCommerce Integration Class
 *
 * @package RoyalStorage
 * @since 1.0.0
 */

namespace RoyalStorage;

/**
 * WooCommerce integration class
 */
class WooCommerceIntegration {

	/**
	 * Constructor
	 */
	public function __construct() {
		add_action( 'woocommerce_order_status_completed', array( $this, 'handle_order_completed' ) );
		add_action( 'woocommerce_order_status_failed', array( $this, 'handle_order_failed' ) );
		add_action( 'woocommerce_order_status_refunded', array( $this, 'handle_order_refunded' ) );
	}

	/**
	 * Create WooCommerce product for booking
	 *
	 * @param int   $booking_id Booking ID.
	 * @param float $price Price.
	 * @return int|false
	 */
	public function create_product( $booking_id, $price ) {
		$product = new \WC_Product_Simple();
		$product->set_name( 'Booking #' . $booking_id );
		$product->set_description( 'Storage booking #' . $booking_id );
		$product->set_price( $price );
		$product->set_regular_price( $price );
		$product->set_manage_stock( false );
		$product->set_status( 'publish' );

		return $product->save();
	}

	/**
	 * Create WooCommerce order for booking
	 *
	 * @param int   $booking_id Booking ID.
	 * @param int   $customer_id Customer ID.
	 * @param float $total_price Total price.
	 * @return int|false
	 */
	public function create_order( $booking_id, $customer_id, $total_price ) {
		$order = wc_create_order( array( 'customer_id' => $customer_id ) );

		if ( is_wp_error( $order ) ) {
			return false;
		}

		// Add product to order
		$product_id = $this->create_product( $booking_id, $total_price );

		if ( $product_id ) {
			$order->add_product( wc_get_product( $product_id ), 1 );
		}

		// Set order total
		$order->set_total( $total_price );
		$order->save();

		// Add booking ID as order meta
		$order->update_meta_data( 'booking_id', $booking_id );
		$order->save();

		return $order->get_id();
	}

	/**
	 * Handle order completed
	 *
	 * @param int $order_id Order ID.
	 * @return void
	 */
	public function handle_order_completed( $order_id ) {
		$order = wc_get_order( $order_id );
		$booking_id = $order->get_meta( 'booking_id' );

		if ( ! $booking_id ) {
			return;
		}

		// Update booking payment status
		global $wpdb;
		$bookings_table = $wpdb->prefix . 'royal_bookings';

		$wpdb->update(
			$bookings_table,
			array( 'payment_status' => 'paid' ),
			array( 'id' => $booking_id ),
			array( '%s' ),
			array( '%d' )
		);

		// Update invoice payment status
		$invoices_table = $wpdb->prefix . 'royal_invoices';
		$wpdb->update(
			$invoices_table,
			array( 'payment_status' => 'paid' ),
			array( 'booking_id' => $booking_id ),
			array( '%s' ),
			array( '%d' )
		);

		// Send payment confirmation email
		$email_manager = new EmailManager();
		$email_manager->send_payment_confirmation( $booking_id );
	}

	/**
	 * Handle order failed
	 *
	 * @param int $order_id Order ID.
	 * @return void
	 */
	public function handle_order_failed( $order_id ) {
		$order = wc_get_order( $order_id );
		$booking_id = $order->get_meta( 'booking_id' );

		if ( ! $booking_id ) {
			return;
		}

		// Update booking payment status
		global $wpdb;
		$bookings_table = $wpdb->prefix . 'royal_bookings';

		$wpdb->update(
			$bookings_table,
			array( 'payment_status' => 'failed' ),
			array( 'id' => $booking_id ),
			array( '%s' ),
			array( '%d' )
		);
	}

	/**
	 * Handle order refunded
	 *
	 * @param int $order_id Order ID.
	 * @return void
	 */
	public function handle_order_refunded( $order_id ) {
		$order = wc_get_order( $order_id );
		$booking_id = $order->get_meta( 'booking_id' );

		if ( ! $booking_id ) {
			return;
		}

		// Update booking payment status
		global $wpdb;
		$bookings_table = $wpdb->prefix . 'royal_bookings';

		$wpdb->update(
			$bookings_table,
			array( 'payment_status' => 'refunded' ),
			array( 'id' => $booking_id ),
			array( '%s' ),
			array( '%d' )
		);

		// Update invoice payment status
		$invoices_table = $wpdb->prefix . 'royal_invoices';
		$wpdb->update(
			$invoices_table,
			array( 'payment_status' => 'refunded' ),
			array( 'booking_id' => $booking_id ),
			array( '%s' ),
			array( '%d' )
		);
	}

	/**
	 * Get order by booking ID
	 *
	 * @param int $booking_id Booking ID.
	 * @return \WC_Order|false
	 */
	public function get_order_by_booking( $booking_id ) {
		$args = array(
			'meta_key'   => 'booking_id',
			'meta_value' => $booking_id,
			'limit'      => 1,
		);

		$orders = wc_get_orders( $args );

		return ! empty( $orders ) ? $orders[0] : false;
	}

	/**
	 * Get payment gateway settings
	 *
	 * @return array
	 */
	public function get_payment_gateway_settings() {
		return array(
			'enabled'      => get_option( 'royal_storage_payment_enabled', 'yes' ),
			'gateway'      => get_option( 'royal_storage_payment_gateway', 'stripe' ),
			'currency'     => get_option( 'woocommerce_currency', 'RSD' ),
			'vat_rate'     => floatval( get_option( 'royal_storage_vat_rate', 20 ) ),
			'min_payment'  => floatval( get_option( 'royal_storage_min_payment', 0 ) ),
			'max_payment'  => floatval( get_option( 'royal_storage_max_payment', 999999 ) ),
		);
	}

	/**
	 * Validate payment amount
	 *
	 * @param float $amount Amount.
	 * @return bool
	 */
	public function validate_payment_amount( $amount ) {
		$settings = $this->get_payment_gateway_settings();

		return $amount >= $settings['min_payment'] && $amount <= $settings['max_payment'];
	}
}

