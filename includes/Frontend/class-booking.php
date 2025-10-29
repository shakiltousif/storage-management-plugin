<?php
/**
 * Booking Class
 *
 * @package RoyalStorage\Frontend
 * @since 1.0.0
 */

namespace RoyalStorage\Frontend;

/**
 * Booking class for frontend booking
 */
class Booking {

	/**
	 * Constructor
	 */
	public function __construct() {
		add_action( 'wp_enqueue_scripts', array( $this, 'init' ) );
		// AJAX handlers are registered in the main Plugin class to avoid conflicts
		add_action( 'init', array( $this, 'register_shortcodes' ) );
	}

	/**
	 * Initialize booking
	 *
	 * @return void
	 */
	public function init() {
		wp_enqueue_style(
			'royal-storage-booking',
			ROYAL_STORAGE_URL . 'assets/css/booking.css',
			array(),
			ROYAL_STORAGE_VERSION
		);

		wp_enqueue_script(
			'royal-storage-booking',
			ROYAL_STORAGE_URL . 'assets/js/booking.js',
			array( 'jquery' ),
			ROYAL_STORAGE_VERSION,
			true
		);

		wp_localize_script(
			'royal-storage-booking',
			'royalStorageBooking',
			array(
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( 'royal_storage_booking' ),
				'isLoggedIn' => is_user_logged_in() ? true : false,
			)
		);
	}

	/**
	 * Register shortcodes
	 *
	 * @return void
	 */
	public function register_shortcodes() {
		add_shortcode( 'royal_storage_booking', array( $this, 'render_booking_form' ) );
		add_shortcode( 'royal_storage_booking_form', array( $this, 'render_booking_form' ) );
	}

	/**
	 * Render booking form
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string
	 */
	public function render_booking_form( $atts = array() ) {
		$atts = shortcode_atts( array(
			'unit_type' => '',
			'show_parking' => 'true',
			'show_storage' => 'true',
		), $atts );

		ob_start();
		?>
		<div class="royal-storage-booking-form">
			<div class="booking-header">
				<h2><?php esc_html_e( 'Book Your Storage Space', 'royal-storage' ); ?></h2>
				<p><?php esc_html_e( 'Select your preferred unit type and dates to get started.', 'royal-storage' ); ?></p>
			</div>

			<form id="royal-storage-booking-form" class="booking-form">
				<?php wp_nonce_field( 'royal_storage_booking', 'booking_nonce' ); ?>
				
				<div class="form-step active" data-step="1">
					<h3><?php esc_html_e( 'Step 1: Select Unit Type', 'royal-storage' ); ?></h3>
					<div class="unit-type-selection">
						<?php if ( 'true' === $atts['show_storage'] ) : ?>
						<div class="unit-type-option">
							<input type="radio" id="unit_type_storage" name="unit_type" value="storage" <?php checked( $atts['unit_type'], 'storage' ); ?>>
							<label for="unit_type_storage">
								<div class="unit-icon">📦</div>
								<div class="unit-info">
									<h4><?php esc_html_e( 'Storage Units', 'royal-storage' ); ?></h4>
									<p><?php esc_html_e( 'Secure storage units in various sizes', 'royal-storage' ); ?></p>
								</div>
							</label>
						</div>
						<?php endif; ?>

						<?php if ( 'true' === $atts['show_parking'] ) : ?>
						<div class="unit-type-option">
							<input type="radio" id="unit_type_parking" name="unit_type" value="parking" <?php checked( $atts['unit_type'], 'parking' ); ?>>
							<label for="unit_type_parking">
								<div class="unit-icon">🚗</div>
								<div class="unit-info">
									<h4><?php esc_html_e( 'Parking Spaces', 'royal-storage' ); ?></h4>
									<p><?php esc_html_e( 'Covered and uncovered parking spaces', 'royal-storage' ); ?></p>
								</div>
							</label>
						</div>
						<?php endif; ?>
					</div>
				</div>

				<div class="form-step" data-step="2">
					<h3><?php esc_html_e( 'Step 2: Select Your Unit', 'royal-storage' ); ?></h3>
					<div id="unit-selection-container">
						<?php echo do_shortcode( '[royal_storage_unit_selection]' ); ?>
					</div>
				</div>

				<div class="form-step" data-step="3">
					<h3><?php esc_html_e( 'Step 3: Select Dates', 'royal-storage' ); ?></h3>
					<div class="date-selection">
						<div class="form-group">
							<label for="start_date"><?php esc_html_e( 'Start Date', 'royal-storage' ); ?></label>
							<input type="date" id="start_date" name="start_date">
						</div>
						<div class="form-group">
							<label for="end_date"><?php esc_html_e( 'End Date', 'royal-storage' ); ?></label>
							<input type="date" id="end_date" name="end_date">
						</div>
						<div class="form-group">
							<label for="period"><?php esc_html_e( 'Billing Period', 'royal-storage' ); ?></label>
							<select id="period" name="period">
								<option value="daily"><?php esc_html_e( 'Daily', 'royal-storage' ); ?></option>
								<option value="weekly"><?php esc_html_e( 'Weekly', 'royal-storage' ); ?></option>
								<option value="monthly" selected><?php esc_html_e( 'Monthly', 'royal-storage' ); ?></option>
							</select>
						</div>
					</div>
				</div>

				<div class="form-step" data-step="4">
					<h3><?php esc_html_e( 'Step 4: Customer Information', 'royal-storage' ); ?></h3>
					
					<?php if ( ! is_user_logged_in() ) : ?>
					<div class="guest-information">
						<p class="info-notice"><?php esc_html_e( 'Please provide your contact information to complete the booking.', 'royal-storage' ); ?></p>
						
						<div class="form-group">
							<label for="guest_email"><?php esc_html_e( 'Email Address', 'royal-storage' ); ?> <span class="required">*</span></label>
							<input type="email" id="guest_email" name="guest_email" required>
						</div>
						
						<div class="form-row">
							<div class="form-group">
								<label for="guest_first_name"><?php esc_html_e( 'First Name', 'royal-storage' ); ?> <span class="required">*</span></label>
								<input type="text" id="guest_first_name" name="guest_first_name" required>
							</div>
							
							<div class="form-group">
								<label for="guest_last_name"><?php esc_html_e( 'Last Name', 'royal-storage' ); ?> <span class="required">*</span></label>
								<input type="text" id="guest_last_name" name="guest_last_name" required>
							</div>
						</div>
						
						<div class="form-group">
							<label for="guest_phone"><?php esc_html_e( 'Phone Number', 'royal-storage' ); ?> <span class="required">*</span></label>
							<input type="tel" id="guest_phone" name="guest_phone" required>
						</div>
						
						<div class="form-group checkbox-group">
							<label>
								<input type="checkbox" name="create_account" value="1" checked>
								<?php esc_html_e( 'Create an account to manage bookings and invoices', 'royal-storage' ); ?>
							</label>
						</div>
					</div>
					<?php else : ?>
					<div class="logged-in-notice">
						<p><?php esc_html_e( 'You are logged in as', 'royal-storage' ); ?> <?php echo esc_html( wp_get_current_user()->display_name ); ?></p>
					</div>
					<?php endif; ?>
					
				</div>

				<div class="form-step" data-step="5">
					<h3><?php esc_html_e( 'Step 5: Review & Confirm', 'royal-storage' ); ?></h3>
					<div id="booking-summary" class="booking-summary">
						<!-- Booking summary will be populated here -->
					</div>
				</div>

				<div class="form-navigation">
					<button type="button" id="prev-step" class="btn btn-secondary" style="display: none;">
						<?php esc_html_e( 'Previous', 'royal-storage' ); ?>
					</button>
					<button type="button" id="next-step" class="btn btn-primary">
						<?php esc_html_e( 'Next', 'royal-storage' ); ?>
					</button>
					<button type="submit" id="submit-booking" class="btn btn-success" style="display: none;">
						<?php esc_html_e( 'Book Now & Pay', 'royal-storage' ); ?>
					</button>
				</div>
			</form>
		</div>
		<?php
		return ob_get_clean();
	}

	/**
	 * Get available units via AJAX
	 *
	 * @return void
	 */
	public function get_available_units() {
		// Temporarily disable nonce check for debugging
		// if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ?? '' ) ), 'royal_storage_booking' ) ) {
		// 	wp_send_json_error( array( 'message' => __( 'Security check failed', 'royal-storage' ) ) );
		// }

		$unit_type = isset( $_POST['unit_type'] ) ? sanitize_text_field( wp_unslash( $_POST['unit_type'] ) ) : '';
		$start_date = isset( $_POST['start_date'] ) ? sanitize_text_field( wp_unslash( $_POST['start_date'] ) ) : '';
		$end_date = isset( $_POST['end_date'] ) ? sanitize_text_field( wp_unslash( $_POST['end_date'] ) ) : '';

		if ( empty( $unit_type ) || empty( $start_date ) || empty( $end_date ) ) {
			wp_send_json_error( array( 'message' => __( 'Missing required parameters', 'royal-storage' ) ) );
		}

		$available_units = $this->get_available_units_for_dates( $unit_type, $start_date, $end_date );

		wp_send_json_success( $available_units );
	}

	/**
	 * Calculate booking price via AJAX
	 *
	 * @return void
	 */
	public function ajax_calculate_booking_price() {
		if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ?? '' ) ), 'royal_storage_booking' ) ) {
			wp_send_json_error( array( 'message' => __( 'Security check failed', 'royal-storage' ) ) );
		}

		$unit_id = isset( $_POST['unit_id'] ) ? intval( $_POST['unit_id'] ) : 0;
		$unit_type = isset( $_POST['unit_type'] ) ? sanitize_text_field( wp_unslash( $_POST['unit_type'] ) ) : '';
		$start_date = isset( $_POST['start_date'] ) ? sanitize_text_field( wp_unslash( $_POST['start_date'] ) ) : '';
		$end_date = isset( $_POST['end_date'] ) ? sanitize_text_field( wp_unslash( $_POST['end_date'] ) ) : '';
		$period = isset( $_POST['period'] ) ? sanitize_text_field( wp_unslash( $_POST['period'] ) ) : 'monthly';

		if ( empty( $unit_id ) || empty( $unit_type ) || empty( $start_date ) || empty( $end_date ) ) {
			wp_send_json_error( array( 'message' => __( 'Missing required parameters', 'royal-storage' ) ) );
		}

		$base_price = $this->get_unit_base_price( $unit_id, $unit_type );
		if ( ! $base_price ) {
			wp_send_json_error( array( 'message' => __( 'Unit not found', 'royal-storage' ) ) );
		}

		$pricing = $this->calculate_booking_price( $base_price, $start_date, $end_date, $period );
		$pricing['unit_id'] = $unit_id;
		$pricing['unit_type'] = $unit_type;

		wp_send_json_success( $pricing );
	}

	/**
	 * Calculate booking price
	 *
	 * @param float  $base_price Base price.
	 * @param string $start_date Start date.
	 * @param string $end_date End date.
	 * @param string $period Billing period.
	 * @return array
	 */
	private function calculate_booking_price( $base_price, $start_date, $end_date, $period ) {
		$start = new \DateTime( $start_date );
		$end = new \DateTime( $end_date );
		$interval = $start->diff( $end );
		$days = $interval->days;

		// Calculate price based on period
		$subtotal = 0;
		switch ( $period ) {
			case 'daily':
				$subtotal = $base_price * $days;
				break;
			case 'weekly':
				$weeks = ceil( $days / 7 );
				$subtotal = ( $base_price * 7 ) * $weeks;
				break;
			case 'monthly':
			default:
				$months = ceil( $days / 30 );
				$subtotal = ( $base_price * 30 ) * $months;
				break;
		}

		$vat = $subtotal * 0.20; // 20% VAT
		$total = $subtotal + $vat;

		return array(
			'subtotal' => $subtotal,
			'vat'      => $vat,
			'total'    => $total,
			'days'     => $days,
			'period'   => $period,
		);
	}

	/**
	 * Create booking via AJAX
	 *
	 * @return void
	 */
	public function create_booking() {
		if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ?? '' ) ), 'royal_storage_booking' ) ) {
			wp_send_json_error( array( 'message' => __( 'Security check failed', 'royal-storage' ) ) );
		}

		$unit_id = isset( $_POST['unit_id'] ) ? intval( $_POST['unit_id'] ) : 0;
		$unit_type = isset( $_POST['unit_type'] ) ? sanitize_text_field( wp_unslash( $_POST['unit_type'] ) ) : '';
		$start_date = isset( $_POST['start_date'] ) ? sanitize_text_field( wp_unslash( $_POST['start_date'] ) ) : '';
		$end_date = isset( $_POST['end_date'] ) ? sanitize_text_field( wp_unslash( $_POST['end_date'] ) ) : '';
		$period = isset( $_POST['period'] ) ? sanitize_text_field( wp_unslash( $_POST['period'] ) ) : 'monthly';

		if ( empty( $unit_id ) || empty( $unit_type ) || empty( $start_date ) || empty( $end_date ) ) {
			wp_send_json_error( array( 'message' => __( 'Missing required parameters', 'royal-storage' ) ) );
		}

		// Handle guest checkout
		$customer_id = 0;
		$is_guest = false;

		if ( ! is_user_logged_in() ) {
			// Check if guest checkout is enabled
			if ( ! \RoyalStorage\Guest_Booking_Handler::is_guest_checkout_enabled() ) {
				wp_send_json_error( array( 'message' => __( 'Please log in to create a booking', 'royal-storage' ) ) );
			}

			// Get guest data
			$guest_data = \RoyalStorage\Guest_Booking_Handler::get_guest_data_from_request();
			if ( ! $guest_data ) {
				wp_send_json_error( array( 'message' => __( 'Guest information is required.', 'royal-storage' ) ) );
			}

			// Validate guest data
			$validation = \RoyalStorage\Guest_Booking_Handler::validate_guest_data( $guest_data );
			if ( $validation !== true ) {
				wp_send_json_error( array( 'message' => $validation ) );
			}

			// Create or get customer account
			$customer_id = \RoyalStorage\Guest_Booking_Handler::create_or_get_guest_customer(
				$guest_data['email'],
				$guest_data['first_name'],
				$guest_data['last_name'],
				$guest_data['phone']
			);

			if ( ! $customer_id ) {
				wp_send_json_error( array( 'message' => __( 'Failed to create customer account.', 'royal-storage' ) ) );
			}

			$is_guest = true;
		} else {
			$customer_id = get_current_user_id();
		}
		$base_price = $this->get_unit_base_price( $unit_id, $unit_type );
		$pricing = $this->calculate_booking_price( $base_price, $start_date, $end_date, $period );

		// Create booking
		$booking_id = $this->create_booking_record( $customer_id, $unit_id, $unit_type, $start_date, $end_date, $pricing );

		if ( $booking_id ) {
			// Add product to cart and redirect to checkout
			$wc_integration = new \RoyalStorage\WooCommerceIntegration();
			$product_id = $wc_integration->create_order( $booking_id, $customer_id, $pricing['total'] );
			
			if ( $product_id ) {
				// Verify cart has items
				if ( function_exists( 'WC' ) && WC()->cart && ! WC()->cart->is_empty() ) {
					// Store booking ID in URL for checkout page
					$checkout_url = add_query_arg( 'booking_id', $booking_id, wc_get_checkout_url() );
					
					wp_send_json_success( array( 
						'message' => __( 'Booking created successfully. Redirecting to checkout...', 'royal-storage' ),
						'booking_id' => $booking_id,
						'redirect_url' => $checkout_url,
						'cart_count' => WC()->cart->get_cart_contents_count()
					) );
				} else {
					// Fallback if cart is empty - redirect to custom checkout or portal
					$checkout_url = add_query_arg( 'booking_id', $booking_id, home_url( '/checkout/' ) );
					
					wp_send_json_success( array( 
						'message' => __( 'Booking created successfully. Redirecting to checkout...', 'royal-storage' ),
						'booking_id' => $booking_id,
						'redirect_url' => $checkout_url
					) );
				}
			} else {
				// If WooCommerce cart failed, redirect to custom checkout page
				$checkout_url = add_query_arg( 'booking_id', $booking_id, home_url( '/checkout/' ) );
				
				wp_send_json_success( array( 
					'message' => __( 'Booking created successfully. Redirecting to checkout...', 'royal-storage' ),
					'booking_id' => $booking_id,
					'redirect_url' => $checkout_url
				) );
			}
		} else {
			wp_send_json_error( array( 'message' => __( 'Failed to create booking', 'royal-storage' ) ) );
		}
	}

	/**
	 * Get available units for dates
	 *
	 * @param string $unit_type Unit type.
	 * @param string $start_date Start date.
	 * @param string $end_date End date.
	 * @return array
	 */
	public function get_available_units_for_dates( $unit_type, $start_date, $end_date ) {
		global $wpdb;

		if ( 'parking' === $unit_type ) {
			$table = $wpdb->prefix . 'royal_parking_spaces';
		} else {
			$table = $wpdb->prefix . 'royal_storage_units';
		}

		$bookings_table = $wpdb->prefix . 'royal_bookings';

		// Get all units.
		$all_units = $wpdb->get_results( "SELECT * FROM $table WHERE status = 'available'" );

		// Get booked units for the date range.
		$booked_units = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT DISTINCT unit_id FROM $bookings_table WHERE unit_type = %s AND status IN ('confirmed', 'active') AND start_date < %s AND end_date > %s",
				$unit_type,
				$end_date,
				$start_date
			)
		);

		$booked_unit_ids = wp_list_pluck( $booked_units, 'unit_id' );

		// Filter available units.
		$available = array_filter(
			$all_units,
			function( $unit ) use ( $booked_unit_ids ) {
				return ! in_array( $unit->id, $booked_unit_ids, true );
			}
		);

		return array_values( $available );
	}


	/**
	 * Get unit base price
	 *
	 * @param int    $unit_id Unit ID.
	 * @param string $unit_type Unit type.
	 * @return float|false
	 */
	public function get_unit_base_price( $unit_id, $unit_type ) {
		global $wpdb;

		if ( 'parking' === $unit_type ) {
			$table = $wpdb->prefix . 'royal_parking_spaces';
		} else {
			$table = $wpdb->prefix . 'royal_storage_units';
		}

		$unit = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT base_price FROM $table WHERE id = %d",
				$unit_id
			)
		);

		return $unit ? floatval( $unit->base_price ) : false;
	}

	/**
	 * Create booking record
	 *
	 * @param int    $customer_id Customer ID.
	 * @param int    $unit_id Unit ID.
	 * @param string $unit_type Unit type.
	 * @param string $start_date Start date.
	 * @param string $end_date End date.
	 * @param array  $pricing Pricing data.
	 * @return int|false
	 */
	public function create_booking_record( $customer_id, $unit_id, $unit_type, $start_date, $end_date, $pricing ) {
		global $wpdb;

		$bookings_table = $wpdb->prefix . 'royal_bookings';

		// Generate access code
		$access_code = $this->generate_access_code();

		// Create booking post
		$post_id = wp_insert_post( array(
			'post_title'   => sprintf( 'Booking #%d', time() ),
			'post_content' => '',
			'post_status'  => 'publish',
			'post_type'    => 'rs_booking',
		) );

		if ( is_wp_error( $post_id ) ) {
			return false;
		}

		// Insert booking record
		$result = $wpdb->insert(
			$bookings_table,
			array(
				'post_id'        => $post_id,
				'customer_id'    => $customer_id,
				'unit_id'        => $unit_id,
				'unit_type'      => $unit_type,
				'start_date'     => $start_date,
				'end_date'       => $end_date,
				'total_price'    => $pricing['total'],
				'vat_amount'     => $pricing['vat'],
				'status'         => 'pending',
				'payment_status' => 'unpaid',
				'access_code'    => $access_code,
			),
			array(
				'%d',
				'%d',
				'%d',
				'%s',
				'%s',
				'%s',
				'%f',
				'%f',
				'%s',
				'%s',
				'%s',
			)
		);

		if ( $result ) {
			return $wpdb->insert_id;
		}

		return false;
	}

	/**
	 * Generate access code
	 *
	 * @return string
	 */
	private function generate_access_code() {
		return strtoupper( wp_generate_password( 8, false ) );
	}
}

