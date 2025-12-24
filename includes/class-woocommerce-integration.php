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
		
		// Display booking details on thank you page (only once)
		add_action( 'woocommerce_thankyou', array( $this, 'display_booking_details_on_thankyou' ), 20 );
		
		// Display booking details in My Account order view (different context)
		add_action( 'woocommerce_order_details_after_order_table', array( $this, 'display_booking_details_in_order' ), 10, 1 );
		
		// Save booking ID to order items when order is created
		add_action( 'woocommerce_checkout_create_order_line_item', array( $this, 'save_booking_id_to_order_item' ), 10, 4 );
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
		// If booking ID is already in cart item data (from session), use it
		if ( isset( $cart_item_data['royal_storage_booking_id'] ) ) {
			return $cart_item_data;
		}

		$product = wc_get_product( $product_id );
		
		if ( $product ) {
			$booking_id = $product->get_meta( 'royal_storage_booking_id' );
			if ( $booking_id ) {
				$cart_item_data['royal_storage_booking_id'] = $booking_id;
				error_log( 'Royal Storage: Added booking ID ' . $booking_id . ' to cart item for product #' . $product_id );
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
			
			// If not in item meta, try to get from product meta
			if ( ! $booking_id ) {
				$product = wc_get_product( $product_id );
				if ( $product ) {
					$booking_id = $product->get_meta( 'royal_storage_booking_id' );
				}
			}
			
			if ( $booking_id ) {
				// Add booking ID as order meta
				$order->update_meta_data( 'booking_id', $booking_id );
				$order->update_meta_data( '_royal_storage_booking', $booking_id );
				
				// Also save to order item meta if not already there
				if ( ! $item->get_meta( 'royal_storage_booking_id' ) ) {
					$item->update_meta_data( 'royal_storage_booking_id', $booking_id );
					$item->save();
				}
				
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
			'max_payment'  => floatval( get_option( 'royal_storage_max_payment', 999999999 ) ),
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

	/**
	 * Display booking details on thank you page
	 *
	 * @param int $order_id Order ID.
	 * @return void
	 */
	public function display_booking_details_on_thankyou( $order_id ) {
		// Prevent duplicate display - use static flag
		static $displayed_orders = array();
		
		if ( in_array( $order_id, $displayed_orders, true ) ) {
			return; // Already displayed for this order
		}
		
		if ( ! function_exists( 'wc_get_order' ) ) {
			return;
		}

		$order = wc_get_order( $order_id );
		
		if ( ! $order ) {
			return;
		}

		// Get booking ID from order meta
		$booking_id = $order->get_meta( '_royal_storage_booking' );
		
		// Also try alternative meta key
		if ( ! $booking_id ) {
			$booking_id = $order->get_meta( 'booking_id' );
		}
		
		// If not found in order meta, try to get from order items
		if ( ! $booking_id ) {
			foreach ( $order->get_items() as $item_id => $item ) {
				if ( ! is_a( $item, 'WC_Order_Item_Product' ) ) {
					continue;
				}
				$booking_id = $item->get_meta( 'royal_storage_booking_id' );
				if ( $booking_id ) {
					break;
				}
			}
		}

		// If still not found, try to get from product meta
		if ( ! $booking_id ) {
			foreach ( $order->get_items() as $item ) {
				if ( ! is_a( $item, 'WC_Order_Item_Product' ) ) {
					continue;
				}
				$product_id = $item->get_product_id();
				$product = wc_get_product( $product_id );
				if ( $product ) {
					$booking_id = $product->get_meta( 'royal_storage_booking_id' );
					if ( $booking_id ) {
						// Save it to order meta for future use
						$order->update_meta_data( '_royal_storage_booking', $booking_id );
						$order->save();
						break;
					}
				}
			}
		}

		if ( ! $booking_id ) {
			return;
		}
		
		// Mark this order as displayed
		$displayed_orders[] = $order_id;

		// Get booking details
		global $wpdb;
		$bookings_table = $wpdb->prefix . 'royal_bookings';
		$booking = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM $bookings_table WHERE id = %d",
				$booking_id
			)
		);

		if ( ! $booking ) {
			return;
		}

		// Get unit details
		$unit_name = $this->get_unit_name( $booking->unit_id, $booking->unit_type );

		?>
		<section class="woocommerce-order-details royal-storage-booking-details">
			<h2 class="woocommerce-order-details__title">
				<?php esc_html_e( 'Booking Details', 'royal-storage' ); ?>
			</h2>
			
			<table class="woocommerce-table woocommerce-table--booking-details shop_table booking_details">
				<tbody>
					<tr>
						<th><?php esc_html_e( 'Booking ID:', 'royal-storage' ); ?></th>
						<td><strong>#<?php echo esc_html( $booking->id ); ?></strong></td>
					</tr>
					<tr>
						<th><?php esc_html_e( 'Unit/Space:', 'royal-storage' ); ?></th>
						<td><?php echo esc_html( $unit_name ); ?></td>
					</tr>
					<tr>
						<th><?php esc_html_e( 'Unit Type:', 'royal-storage' ); ?></th>
						<td><?php echo esc_html( ucfirst( $booking->unit_type ) ); ?></td>
					</tr>
					<tr>
						<th><?php esc_html_e( 'Start Date:', 'royal-storage' ); ?></th>
						<td><?php echo esc_html( date_i18n( get_option( 'date_format' ), strtotime( $booking->start_date ) ) ); ?></td>
					</tr>
					<tr>
						<th><?php esc_html_e( 'End Date:', 'royal-storage' ); ?></th>
						<td><?php echo esc_html( date_i18n( get_option( 'date_format' ), strtotime( $booking->end_date ) ) ); ?></td>
					</tr>
					<?php if ( ! empty( $booking->access_code ) ) : ?>
					<tr>
						<th><?php esc_html_e( 'Access Code:', 'royal-storage' ); ?></th>
						<td><strong style="font-size: 18px; color: #007cba;"><?php echo esc_html( $booking->access_code ); ?></strong></td>
					</tr>
					<?php endif; ?>
					<tr>
						<th><?php esc_html_e( 'Booking Status:', 'royal-storage' ); ?></th>
						<td>
							<span class="booking-status status-<?php echo esc_attr( $booking->status ); ?>">
								<?php echo esc_html( ucfirst( $booking->status ) ); ?>
							</span>
						</td>
					</tr>
					<tr>
						<th><?php esc_html_e( 'Payment Status:', 'royal-storage' ); ?></th>
						<td>
							<?php
							// Get payment status from order if available, otherwise from booking
							$payment_status = 'pending'; // Default value
							$payment_status_class = 'pending'; // Default value
							
							if ( $order && is_a( $order, 'WC_Order' ) ) {
								$order_status = $order->get_status();
								$needs_payment = $order->needs_payment();
								
								if ( $needs_payment ) {
									$payment_status = 'pending';
									$payment_status_class = 'pending';
								} elseif ( in_array( $order_status, array( 'processing', 'completed' ), true ) ) {
									$payment_status = 'paid';
									$payment_status_class = 'paid';
								} elseif ( $order_status === 'on-hold' ) {
									$payment_status = 'on-hold';
									$payment_status_class = 'on-hold';
								} elseif ( $order_status === 'failed' ) {
									$payment_status = 'failed';
									$payment_status_class = 'failed';
								} elseif ( $order_status === 'refunded' ) {
									$payment_status = 'refunded';
									$payment_status_class = 'refunded';
								} elseif ( $order_status === 'cancelled' ) {
									$payment_status = 'cancelled';
									$payment_status_class = 'cancelled';
								} else {
									// Use order status as payment status
									$payment_status = $order_status;
									$payment_status_class = $order_status;
								}
							} elseif ( ! empty( $booking->payment_status ) ) {
								$payment_status = $booking->payment_status;
								$payment_status_class = $booking->payment_status;
							}
							
							// Ensure we always have valid values
							if ( empty( $payment_status ) ) {
								$payment_status = 'pending';
								$payment_status_class = 'pending';
							}
							?>
							<span class="payment-status status-<?php echo esc_attr( $payment_status_class ); ?>">
								<?php echo esc_html( ucfirst( $payment_status ) ); ?>
							</span>
						</td>
					</tr>
				</tbody>
			</table>
			
			<?php if ( ! empty( $booking->access_code ) ) : ?>
			<div class="royal-storage-access-code-notice" style="background: #f0f8ff; border-left: 4px solid #007cba; padding: 15px; margin-top: 20px;">
				<p style="margin: 0;">
					<strong><?php esc_html_e( 'Important:', 'royal-storage' ); ?></strong>
					<?php esc_html_e( 'Please save your access code. You will need it to access your storage unit or parking space.', 'royal-storage' ); ?>
				</p>
			</div>
			<?php endif; ?>
		</section>
		<?php
	}

	/**
	 * Display booking details in order details (My Account)
	 * Only show if not on thank you page to avoid duplicates
	 *
	 * @param \WC_Order $order Order object.
	 * @return void
	 */
	public function display_booking_details_in_order( $order ) {
		// Don't show on thank you page (already shown by woocommerce_thankyou hook)
		// Check if we're on the order received/thank you page
		if ( isset( $_GET['key'] ) && isset( $_GET['order'] ) ) {
			return; // This is the thank you page
		}
		
		$order_id = $order->get_id();
		$this->display_booking_details_on_thankyou( $order_id );
	}

	/**
	 * Save booking ID to order item when order is created
	 *
	 * @param \WC_Order_Item_Product $item Order item.
	 * @param string                 $cart_item_key Cart item key.
	 * @param array                  $values Cart item values.
	 * @param \WC_Order              $order Order object.
	 * @return void
	 */
	public function save_booking_id_to_order_item( $item, $cart_item_key, $values, $order ) {
		$booking_id = null;
		
		// Try to get from cart item data first
		if ( isset( $values['royal_storage_booking_id'] ) ) {
			$booking_id = $values['royal_storage_booking_id'];
		}
		
		// If not in cart item, try to get from product meta
		if ( ! $booking_id ) {
			$product_id = $item->get_product_id();
			$product = wc_get_product( $product_id );
			if ( $product ) {
				$booking_id = $product->get_meta( 'royal_storage_booking_id' );
			}
		}
		
		// If still not found, try session
		if ( ! $booking_id && function_exists( 'WC' ) && WC()->session ) {
			$booking_id = WC()->session->get( 'royal_storage_booking_id' );
		}
		
		if ( $booking_id ) {
			$item->update_meta_data( 'royal_storage_booking_id', $booking_id );
			
			// Also save to order meta
			$order->update_meta_data( '_royal_storage_booking', $booking_id );
			$order->update_meta_data( 'booking_id', $booking_id );
			
			error_log( 'Royal Storage: Saved booking ID ' . $booking_id . ' to order #' . $order->get_id() );
		} else {
			error_log( 'Royal Storage: Could not find booking ID for order item. Cart item data: ' . print_r( $values, true ) );
		}
	}

	/**
	 * Get unit name by ID and type
	 *
	 * @param int    $unit_id Unit ID.
	 * @param string $unit_type Unit type.
	 * @return string
	 */
	private function get_unit_name( $unit_id, $unit_type ) {
		global $wpdb;

		if ( 'parking' === $unit_type ) {
			$table = $wpdb->prefix . 'royal_parking_spaces';
			$unit = $wpdb->get_row(
				$wpdb->prepare(
					"SELECT spot_number FROM $table WHERE id = %d",
					$unit_id
				)
			);
			return $unit ? sprintf( __( 'Parking Space #%d', 'royal-storage' ), $unit->spot_number ) : sprintf( __( 'Parking Space #%d', 'royal-storage' ), $unit_id );
		} else {
			$table = $wpdb->prefix . 'royal_storage_units';
			$unit = $wpdb->get_row(
				$wpdb->prepare(
					"SELECT size FROM $table WHERE id = %d",
					$unit_id
				)
			);
			return $unit ? sprintf( __( 'Storage Unit #%d (%s)', 'royal-storage' ), $unit_id, $unit->size ) : sprintf( __( 'Storage Unit #%d', 'royal-storage' ), $unit_id );
		}
	}
}

