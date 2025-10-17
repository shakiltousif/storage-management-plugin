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
		add_action( 'wp_ajax_get_available_units', array( $this, 'get_available_units' ) );
		add_action( 'wp_ajax_nopriv_get_available_units', array( $this, 'get_available_units' ) );
		add_action( 'wp_ajax_calculate_booking_price', array( $this, 'ajax_calculate_booking_price' ) );
		add_action( 'wp_ajax_nopriv_calculate_booking_price', array( $this, 'ajax_calculate_booking_price' ) );
		add_action( 'wp_ajax_create_booking', array( $this, 'create_booking' ) );
		add_action( 'wp_ajax_nopriv_create_booking', array( $this, 'create_booking' ) );
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
					<h3><?php esc_html_e( 'Step 2: Select Dates', 'royal-storage' ); ?></h3>
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

				<div class="form-step" data-step="3">
					<h3><?php esc_html_e( 'Step 3: Select Unit', 'royal-storage' ); ?></h3>
					<div id="available-units" class="available-units">
						<p class="loading"><?php esc_html_e( 'Loading available units...', 'royal-storage' ); ?></p>
					</div>
				</div>

				<div class="form-step" data-step="4">
					<h3><?php esc_html_e( 'Step 4: Review & Confirm', 'royal-storage' ); ?></h3>
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
						<?php esc_html_e( 'Create Booking', 'royal-storage' ); ?>
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

		// Check if user is logged in
		if ( ! is_user_logged_in() ) {
			wp_send_json_error( array( 'message' => __( 'Please log in to create a booking', 'royal-storage' ) ) );
		}

		$customer_id = get_current_user_id();
		$base_price = $this->get_unit_base_price( $unit_id, $unit_type );
		$pricing = $this->calculate_booking_price( $base_price, $start_date, $end_date, $period );

		// Create booking
		$booking_id = $this->create_booking_record( $customer_id, $unit_id, $unit_type, $start_date, $end_date, $pricing );

		if ( $booking_id ) {
			wp_send_json_success( array( 
				'message' => __( 'Booking created successfully', 'royal-storage' ),
				'booking_id' => $booking_id,
				'redirect_url' => home_url( '/customer-portal-test/?tab=bookings' )
			) );
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

