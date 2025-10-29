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
		add_action( 'woocommerce_checkout_order_processed', array( $this, 'handle_order_processed' ) );
		add_filter( 'woocommerce_add_cart_item_data', array( $this, 'add_booking_meta_to_cart_item' ), 10, 3 );
	}

	/**
	 * Create WooCommerce product for booking
	 *
	 * @param int   $booking_id Booking ID.
	 * @param float $price Price.
	 * @return int|false
	 */
	public function create_product( $booking_id, $price ) {
		// Get booking details for better product description
		global $wpdb;
		$bookings_table = $wpdb->prefix . 'royal_bookings';
		$booking = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $bookings_table WHERE id = %d", $booking_id ) );
		
		if ( ! $booking ) {
			return false;
		}

		$product = new \WC_Product_Simple();
		$product->set_name( sprintf( 'Storage Booking #%d - %s', $booking_id, $booking->unit_type ) );
		$product->set_description( $this->get_booking_description( $booking ) );
		$product->set_short_description( sprintf( 'Storage unit booking from %s to %s', 
			date( 'M j, Y', strtotime( $booking->start_date ) ),
			date( 'M j, Y', strtotime( $booking->end_date ) )
		) );
		$product->set_price( $price );
		$product->set_regular_price( $price );
		$product->set_manage_stock( false );
		$product->set_status( 'publish' );
		$product->set_virtual( true ); // Virtual product since it's a service
		$product->set_downloadable( false );
		
		// Add booking meta
		$product->add_meta_data( 'booking_id', $booking_id );
		$product->add_meta_data( 'unit_type', $booking->unit_type );
		$product->add_meta_data( 'start_date', $booking->start_date );
		$product->add_meta_data( 'end_date', $booking->end_date );
		$product->add_meta_data( 'royal_storage_booking_id', $booking_id );

		return $product->save();
	}

	/**
	 * Get booking description for product
	 *
	 * @param object $booking Booking object.
	 * @return string
	 */
	private function get_booking_description( $booking ) {
		$description = sprintf(
			'Storage unit booking details:
			
Booking ID: #%d
Unit Type: %s
Start Date: %s
End Date: %s
Duration: %d days
Base Price: %s RSD
Total Price: %s RSD

This is a virtual product for your storage booking. Payment will be processed through our secure payment gateway.',
			$booking->id,
			ucfirst( $booking->unit_type ),
			date( 'F j, Y', strtotime( $booking->start_date ) ),
			date( 'F j, Y', strtotime( $booking->end_date ) ),
			$booking->duration,
			number_format( $booking->base_price, 2 ),
			number_format( $booking->total_price, 2 )
		);

		return $description;
	}

	/**
	 * Create WooCommerce product and add to cart for booking
	 *
	 * @param int   $booking_id Booking ID.
	 * @param int   $customer_id Customer ID.
	 * @param float $total_price Total price.
	 * @return int|false
	 */
	public function create_order( $booking_id, $customer_id, $total_price ) {
		// Ensure WooCommerce and cart are available
		if ( ! function_exists( 'WC' ) || ! WC()->cart ) {
			return false;
		}

		// Create product for the booking
		$product_id = $this->create_product( $booking_id, $total_price );

		if ( ! $product_id ) {
			return false;
		}

		// Clear any existing cart items
		WC()->cart->empty_cart();

		// Add product to cart
		$cart_item_key = WC()->cart->add_to_cart( $product_id, 1 );

		if ( ! $cart_item_key ) {
			return false;
		}

		// Add booking meta to cart item
		WC()->cart->cart_contents[ $cart_item_key ]['royal_storage_booking_id'] = $booking_id;

		// Update cart totals
		WC()->cart->calculate_totals();

		// Verify cart has items before returning
		if ( WC()->cart->is_empty() ) {
			return false;
		}

		// Store booking ID in session for verification
		WC()->session->set( 'royal_storage_booking_id', $booking_id );

		return $product_id;
	}

	/**
	 * Set customer billing information for order
	 *
	 * @param \WC_Order $order Order object.
	 * @param int       $customer_id Customer ID.
	 * @return void
	 */
	private function set_customer_billing_info( $order, $customer_id ) {
		$customer = new \WC_Customer( $customer_id );
		
		if ( $customer->get_id() ) {
			$order->set_billing_first_name( $customer->get_first_name() );
			$order->set_billing_last_name( $customer->get_last_name() );
			$order->set_billing_email( $customer->get_email() );
			$order->set_billing_phone( $customer->get_billing_phone() );
			$order->set_billing_address_1( $customer->get_billing_address_1() );
			$order->set_billing_address_2( $customer->get_billing_address_2() );
			$order->set_billing_city( $customer->get_billing_city() );
			$order->set_billing_state( $customer->get_billing_state() );
			$order->set_billing_postcode( $customer->get_billing_postcode() );
			$order->set_billing_country( $customer->get_billing_country() );
		}
	}

	/**
	 * Add booking meta to cart item data
	 *
	 * @param array $cart_item_data Cart item data.
	 * @param int   $product_id Product ID.
	 * @param int   $variation_id Variation ID.
	 * @return array
	 */
	public function add_booking_meta_to_cart_item( $cart_item_data, $product_id, $variation_id ) {
		$product = wc_get_product( $product_id );
		
		if ( $product ) {
			$booking_id = $product->get_meta( 'royal_storage_booking_id' );
			if ( $booking_id ) {
				$cart_item_data['royal_storage_booking_id'] = $booking_id;
			}
		}
		
		return $cart_item_data;
	}

	/**
	 * Handle order processed (when order is created)
	 *
	 * @param int $order_id Order ID.
	 * @return void
	 */
	public function handle_order_processed( $order_id ) {
		$order = wc_get_order( $order_id );
		
		// Check if this order contains a Royal Storage booking
		foreach ( $order->get_items() as $item ) {
			if ( ! is_a( $item, 'WC_Order_Item_Product' ) ) {
				continue;
			}
			
			$product_id = $item->get_product_id();
			$booking_id = $item->get_meta( 'royal_storage_booking_id' );
			
			if ( $booking_id ) {
				// Add booking ID as order meta
				$order->update_meta_data( 'booking_id', $booking_id );
				$order->update_meta_data( '_royal_storage_booking', $booking_id );
				$order->save();
				
				// Update booking status
				global $wpdb;
				$bookings_table = $wpdb->prefix . 'royal_bookings';
				$wpdb->update(
					$bookings_table,
					array( 'status' => 'confirmed' ),
					array( 'id' => $booking_id ),
					array( '%s' ),
					array( '%d' )
				);
				break;
			}
		}
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

