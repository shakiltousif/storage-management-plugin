<?php
/**
 * Bookings Class
 *
 * @package RoyalStorage\Admin
 * @since 1.0.0
 */

namespace RoyalStorage\Admin;

use RoyalStorage\BookingEngine;
use RoyalStorage\EmailManager;

/**
 * Bookings class for admin booking management
 */
class Bookings {

	/**
	 * Booking engine instance
	 *
	 * @var BookingEngine
	 */
	private $booking_engine;

	/**
	 * Email manager instance
	 *
	 * @var EmailManager
	 */
	private $email_manager;

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->booking_engine = new BookingEngine();
		$this->email_manager = new EmailManager();
		add_action( 'admin_init', array( $this, 'init' ) );
		add_action( 'admin_post_royal_storage_create_booking', array( $this, 'handle_create_booking' ) );
		add_action( 'admin_post_royal_storage_cancel_booking', array( $this, 'handle_cancel_booking' ) );
		add_action( 'admin_post_royal_storage_update_booking_status', array( $this, 'handle_update_booking_status' ) );
		add_action( 'wp_ajax_get_booking_details', array( $this, 'ajax_get_booking_details' ) );
		add_action( 'wp_ajax_approve_booking', array( $this, 'ajax_approve_booking' ) );
		add_action( 'wp_ajax_cancel_booking_ajax', array( $this, 'ajax_cancel_booking' ) );
		// NEW 2026-01-06: Reassignment AJAX handlers
		add_action( 'wp_ajax_get_available_units_for_reassignment', array( $this, 'ajax_get_available_units_for_reassignment' ) );
		add_action( 'wp_ajax_reassign_booking_unit', array( $this, 'ajax_reassign_booking_unit' ) );
		// NEW 2026-01-06: Mark as paid handler
		add_action( 'wp_ajax_mark_booking_paid', array( $this, 'ajax_mark_booking_paid' ) );
	}

	/**
	 * Initialize bookings
	 *
	 * @return void
	 */
	public function init() {
		// Bookings initialization code.
	}

	/**
	 * Handle create booking from admin
	 *
	 * @return void
	 */
	public function handle_create_booking() {
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'royal_storage_create_booking' ) ) {
			wp_die( esc_html__( 'Security check failed', 'royal-storage' ) );
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Unauthorized', 'royal-storage' ) );
		}

		$booking_data = array(
			'customer_id' => isset( $_POST['customer_id'] ) ? intval( $_POST['customer_id'] ) : 0,
			'unit_id'     => isset( $_POST['unit_id'] ) ? intval( $_POST['unit_id'] ) : 0,
			'space_id'    => isset( $_POST['space_id'] ) ? intval( $_POST['space_id'] ) : 0,
			'start_date'  => isset( $_POST['start_date'] ) ? sanitize_text_field( wp_unslash( $_POST['start_date'] ) ) : '',
			'end_date'    => isset( $_POST['end_date'] ) ? sanitize_text_field( wp_unslash( $_POST['end_date'] ) ) : '',
			'period'      => isset( $_POST['period'] ) ? sanitize_text_field( wp_unslash( $_POST['period'] ) ) : 'monthly',
		);

		$booking = $this->booking_engine->create_booking( $booking_data );

		if ( $booking ) {
			$this->email_manager->send_booking_confirmation( $booking->get_id() );
			wp_safe_remote_post(
				admin_url( 'admin-ajax.php' ),
				array(
					'blocking' => false,
					'sslverify' => apply_filters( 'https_local_ssl_verify', false ),
				)
			);
			wp_redirect( admin_url( 'admin.php?page=royal-storage-bookings&message=created' ) );
			exit;
		}

		wp_redirect( admin_url( 'admin.php?page=royal-storage-bookings&message=error' ) );
		exit;
	}

	/**
	 * Handle cancel booking from admin
	 *
	 * @return void
	 */
	public function handle_cancel_booking() {
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'royal_storage_cancel_booking' ) ) {
			wp_die( esc_html__( 'Security check failed', 'royal-storage' ) );
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Unauthorized', 'royal-storage' ) );
		}

		$booking_id = isset( $_POST['booking_id'] ) ? intval( $_POST['booking_id'] ) : 0;

		if ( $this->booking_engine->cancel_booking( $booking_id ) ) {
			wp_redirect( admin_url( 'admin.php?page=royal-storage-bookings&message=cancelled' ) );
			exit;
		}

		wp_redirect( admin_url( 'admin.php?page=royal-storage-bookings&message=error' ) );
		exit;
	}

	/**
	 * Get all bookings
	 *
	 * @param int $limit Limit.
	 * @param int $offset Offset.
	 * @return array
	 */
	public function get_bookings( $limit = 20, $offset = 0 ) {
		global $wpdb;
		$bookings_table = $wpdb->prefix . 'royal_bookings';
		return $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM $bookings_table ORDER BY created_at DESC LIMIT %d OFFSET %d",
				$limit,
				$offset
			)
		);
	}

	/**
	 * Get booking by ID
	 *
	 * @param int $booking_id Booking ID.
	 * @return object|null
	 */
	public function get_booking( $booking_id ) {
		global $wpdb;
		$bookings_table = $wpdb->prefix . 'royal_bookings';
		return $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM $bookings_table WHERE id = %d",
				$booking_id
			)
		);
	}

	/**
	 * Update booking
	 *
	 * @param int   $booking_id Booking ID.
	 * @param array $booking_data Booking data.
	 * @return bool
	 */
	public function update_booking( $booking_id, $booking_data ) {
		global $wpdb;
		$bookings_table = $wpdb->prefix . 'royal_bookings';
		return $wpdb->update(
			$bookings_table,
			$booking_data,
			array( 'id' => $booking_id ),
			array_fill( 0, count( $booking_data ), '%s' ),
			array( '%d' )
		);
	}

	/**
	 * Cancel booking
	 *
	 * @param int $booking_id Booking ID.
	 * @return bool
	 */
	public function cancel_booking( $booking_id ) {
		return $this->update_booking( $booking_id, array( 'status' => 'cancelled' ) );
	}

	/**
	 * Get bookings count
	 *
	 * @return int
	 */
	public function get_bookings_count() {
		global $wpdb;
		$bookings_table = $wpdb->prefix . 'royal_bookings';
		return (int) $wpdb->get_var( "SELECT COUNT(*) FROM $bookings_table" );
	}

	/**
	 * Handle booking status update
	 *
	 * @return void
	 */
	public function handle_update_booking_status() {
		// Verify nonce
		if ( ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ), 'royal_storage_update_booking_status' ) ) {
			wp_die( esc_html__( 'Security check failed', 'royal-storage' ) );
		}

		// Check permissions
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have permission to perform this action', 'royal-storage' ) );
		}

		$booking_id = isset( $_POST['booking_id'] ) ? intval( $_POST['booking_id'] ) : 0;
		$new_status = isset( $_POST['new_status'] ) ? sanitize_text_field( wp_unslash( $_POST['new_status'] ) ) : '';

		if ( empty( $booking_id ) || empty( $new_status ) ) {
			wp_die( esc_html__( 'Invalid booking ID or status', 'royal-storage' ) );
		}

		// Update booking status
		$result = $this->update_booking( $booking_id, array( 'status' => $new_status ) );

		if ( $result ) {
			// Also update payment status if booking is confirmed
			if ( $new_status === 'confirmed' ) {
				$this->update_booking( $booking_id, array( 'payment_status' => 'paid' ) );
			}

			wp_redirect( admin_url( 'admin.php?page=royal-storage-bookings&updated=1' ) );
			exit;
		} else {
			wp_redirect( admin_url( 'admin.php?page=royal-storage-bookings&error=1' ) );
			exit;
		}
	}

	/**
	 * AJAX handler to get booking details
	 *
	 * @return void
	 */
	public function ajax_get_booking_details() {
		check_ajax_referer( 'royal_storage_admin', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Unauthorized', 'royal-storage' ) ) );
		}

		$booking_id = isset( $_POST['booking_id'] ) ? intval( $_POST['booking_id'] ) : 0;

		if ( ! $booking_id ) {
			wp_send_json_error( array( 'message' => __( 'Invalid booking ID', 'royal-storage' ) ) );
		}

		$booking = $this->get_booking( $booking_id );

		if ( ! $booking ) {
			wp_send_json_error( array( 'message' => __( 'Booking not found', 'royal-storage' ) ) );
		}

		// Get customer details
		$customer = get_user_by( 'id', $booking->customer_id );

		// Get unit or space details
		$unit_details = '';
		if ( $booking->unit_id > 0 ) {
			global $wpdb;
			$unit = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}royal_storage_units WHERE id = %d", $booking->unit_id ) );
			$unit_details = $unit ? sprintf( 'Unit #%d - Size: %s, Dimensions: %s', $unit->id, $unit->size, $unit->dimensions ) : 'Unit #' . $booking->unit_id;
		} elseif ( $booking->space_id > 0 ) {
			global $wpdb;
			$space = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}royal_parking_spaces WHERE id = %d", $booking->space_id ) );
			$unit_details = $space ? sprintf( 'Parking Space #%d - %s', $space->id, $space->space_number ) : 'Space #' . $booking->space_id;
		}

		$booking_data = array(
			'id'              => $booking->id,
			'customer_name'   => $customer ? $customer->display_name : __( 'Unknown', 'royal-storage' ),
			'customer_email'  => $customer ? $customer->user_email : '',
			'unit_details'    => $unit_details,
			'start_date'      => $booking->start_date,
			'end_date'        => $booking->end_date,
			'total_price'     => number_format( $booking->total_price, 2 ),
			'status'          => ucfirst( $booking->status ),
			'payment_status'  => ucfirst( $booking->payment_status ),
			'created_at'      => $booking->created_at,
			'period'          => isset( $booking->period ) ? $booking->period : 'monthly',
		);

		wp_send_json_success( array( 'booking' => $booking_data ) );
	}

	/**
	 * AJAX handler to approve booking
	 *
	 * @return void
	 */
	public function ajax_approve_booking() {
		check_ajax_referer( 'royal_storage_admin', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Unauthorized', 'royal-storage' ) ) );
		}

		$booking_id = isset( $_POST['booking_id'] ) ? intval( $_POST['booking_id'] ) : 0;

		if ( ! $booking_id ) {
			wp_send_json_error( array( 'message' => __( 'Invalid booking ID', 'royal-storage' ) ) );
		}

		// Update booking status to confirmed
		$result = $this->update_booking( $booking_id, array( 'status' => 'confirmed' ) );

		if ( $result !== false ) {
			// Send confirmation email
			$this->email_manager->send_booking_confirmation( $booking_id );

			wp_send_json_success( array( 'message' => __( 'Booking approved successfully', 'royal-storage' ) ) );
		} else {
			wp_send_json_error( array( 'message' => __( 'Failed to approve booking', 'royal-storage' ) ) );
		}
	}

	/**
	 * AJAX handler to cancel booking
	 *
	 * @return void
	 */
	public function ajax_cancel_booking() {
		check_ajax_referer( 'royal_storage_admin', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Unauthorized', 'royal-storage' ) ) );
		}

		$booking_id = isset( $_POST['booking_id'] ) ? intval( $_POST['booking_id'] ) : 0;

		if ( ! $booking_id ) {
			wp_send_json_error( array( 'message' => __( 'Invalid booking ID', 'royal-storage' ) ) );
		}

		$result = $this->booking_engine->cancel_booking( $booking_id );

		if ( $result ) {
			wp_send_json_success( array( 'message' => __( 'Booking cancelled successfully', 'royal-storage' ) ) );
		} else {
			wp_send_json_error( array( 'message' => __( 'Failed to cancel booking', 'royal-storage' ) ) );
		}
	}

	/**
	 * AJAX handler to get available units for reassignment
	 * Added: 2026-01-06
	 *
	 * @return void
	 */
	public function ajax_get_available_units_for_reassignment() {
		check_ajax_referer( 'royal_storage_admin', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Unauthorized', 'royal-storage' ) ) );
		}

		$booking_id = isset( $_POST['booking_id'] ) ? intval( $_POST['booking_id'] ) : 0;
		$unit_type = isset( $_POST['unit_type'] ) ? sanitize_text_field( wp_unslash( $_POST['unit_type'] ) ) : 'storage';
		$start_date = isset( $_POST['start_date'] ) ? sanitize_text_field( wp_unslash( $_POST['start_date'] ) ) : '';
		$end_date = isset( $_POST['end_date'] ) ? sanitize_text_field( wp_unslash( $_POST['end_date'] ) ) : '';

		if ( ! $booking_id || ! $start_date || ! $end_date ) {
			wp_send_json_error( array( 'message' => __( 'Invalid parameters', 'royal-storage' ) ) );
		}

		// Get current booking to determine unit size
		$booking = $this->get_booking( $booking_id );
		if ( ! $booking ) {
			wp_send_json_error( array( 'message' => __( 'Booking not found', 'royal-storage' ) ) );
		}

		global $wpdb;

		// Get unit size from current booking
		if ( 'parking' === $unit_type ) {
			$table = $wpdb->prefix . 'royal_parking_spaces';
			$size_condition = '';
		} else {
			$table = $wpdb->prefix . 'royal_storage_units';

			// Get size of current unit
			$current_unit = $wpdb->get_row(
				$wpdb->prepare( "SELECT size FROM $table WHERE id = %d", $booking->unit_id )
			);

			if ( $current_unit && $current_unit->size ) {
				$size_condition = $wpdb->prepare( 'AND size = %s', $current_unit->size );
			} else {
				$size_condition = '';
			}
		}

		$bookings_table = $wpdb->prefix . 'royal_bookings';

		// Get all units of same size
		$all_units_query = "SELECT id, size, dimensions, base_price, status
		                    FROM $table
		                    WHERE status = 'available'
		                    $size_condition
		                    ORDER BY id ASC";
		$all_units = $wpdb->get_results( $all_units_query );

		// Get booked units for the date range (excluding current booking)
		$booked_units = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT DISTINCT unit_id
				 FROM $bookings_table
				 WHERE unit_type = %s
				 AND id != %d
				 AND status IN ('confirmed', 'active', 'pending')
				 AND start_date < %s
				 AND end_date > %s",
				$unit_type,
				$booking_id,
				$end_date,
				$start_date
			)
		);

		$booked_unit_ids = wp_list_pluck( $booked_units, 'unit_id' );

		// Filter available units
		$available = array_filter(
			$all_units,
			function( $unit ) use ( $booked_unit_ids ) {
				return ! in_array( $unit->id, $booked_unit_ids, true );
			}
		);

		wp_send_json_success( array( 'units' => array_values( $available ) ) );
	}

	/**
	 * AJAX handler to reassign booking unit
	 * Added: 2026-01-06
	 *
	 * @return void
	 */
	public function ajax_reassign_booking_unit() {
		check_ajax_referer( 'royal_storage_admin', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Unauthorized', 'royal-storage' ) ) );
		}

		$booking_id = isset( $_POST['booking_id'] ) ? intval( $_POST['booking_id'] ) : 0;
		$new_unit_id = isset( $_POST['new_unit_id'] ) ? intval( $_POST['new_unit_id'] ) : 0;

		if ( ! $booking_id || ! $new_unit_id ) {
			wp_send_json_error( array( 'message' => __( 'Invalid parameters', 'royal-storage' ) ) );
		}

		// Get booking details
		$booking = $this->get_booking( $booking_id );
		if ( ! $booking ) {
			wp_send_json_error( array( 'message' => __( 'Booking not found', 'royal-storage' ) ) );
		}

		// Verify new unit is available for the booking dates
		global $wpdb;
		$bookings_table = $wpdb->prefix . 'royal_bookings';

		$conflicts = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*)
				 FROM $bookings_table
				 WHERE unit_id = %d
				 AND id != %d
				 AND status IN ('confirmed', 'active', 'pending')
				 AND start_date < %s
				 AND end_date > %s",
				$new_unit_id,
				$booking_id,
				$booking->end_date,
				$booking->start_date
			)
		);

		if ( $conflicts > 0 ) {
			wp_send_json_error( array( 'message' => __( 'Selected unit is not available for the booking dates', 'royal-storage' ) ) );
		}

		// Update booking with new unit
		$result = $this->update_booking(
			$booking_id,
			array( 'unit_id' => $new_unit_id )
		);

		if ( $result !== false ) {
			// Log the reassignment
			error_log( sprintf(
				'Royal Storage: Booking #%d reassigned from unit #%d to unit #%d by user #%d',
				$booking_id,
				$booking->unit_id,
				$new_unit_id,
				get_current_user_id()
			) );

			wp_send_json_success( array(
				'message' => __( 'Unit reassigned successfully', 'royal-storage' ),
				'new_unit_id' => $new_unit_id,
			) );
		} else {
			wp_send_json_error( array( 'message' => __( 'Failed to update booking', 'royal-storage' ) ) );
		}
	}

	/**
	 * AJAX handler to mark booking as paid
	 * Added: 2026-01-06
	 */
	public function ajax_mark_booking_paid() {
		// Verify nonce
		if ( ! check_ajax_referer( 'royal_storage_admin', 'nonce', false ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid security token', 'royal-storage' ) ) );
		}

		// Check user permissions
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Insufficient permissions', 'royal-storage' ) ) );
		}

		// Get booking ID
		$booking_id = isset( $_POST['booking_id'] ) ? intval( $_POST['booking_id'] ) : 0;
		$payment_notes = isset( $_POST['payment_notes'] ) ? sanitize_textarea_field( wp_unslash( $_POST['payment_notes'] ) ) : '';

		if ( ! $booking_id ) {
			wp_send_json_error( array( 'message' => __( 'Invalid booking ID', 'royal-storage' ) ) );
		}

		global $wpdb;
		$table_name = $wpdb->prefix . 'royal_bookings';

		// Get current booking
		$booking = $wpdb->get_row( $wpdb->prepare(
			"SELECT * FROM {$table_name} WHERE id = %d",
			$booking_id
		) );

		if ( ! $booking ) {
			wp_send_json_error( array( 'message' => __( 'Booking not found', 'royal-storage' ) ) );
		}

		// Update payment status to 'paid'
		$result = $wpdb->update(
			$table_name,
			array( 'payment_status' => 'paid' ),
			array( 'id' => $booking_id ),
			array( '%s' ),
			array( '%d' )
		);

		if ( $result !== false ) {
			// If payment notes provided, add them to order notes or booking meta
			if ( ! empty( $payment_notes ) ) {
				// Try to add note to WooCommerce order if exists
				if ( $booking->order_id ) {
					$order = wc_get_order( $booking->order_id );
					if ( $order ) {
						$order->add_order_note( sprintf(
							__( 'Payment marked as complete by admin. Notes: %s', 'royal-storage' ),
							$payment_notes
						) );
					}
				}

				// Log the action
				error_log( sprintf(
					'Royal Storage: Booking #%d marked as PAID by user #%d. Notes: %s',
					$booking_id,
					get_current_user_id(),
					$payment_notes
				) );
			} else {
				// Log without notes
				error_log( sprintf(
					'Royal Storage: Booking #%d marked as PAID by user #%d',
					$booking_id,
					get_current_user_id()
				) );
			}

			wp_send_json_success( array(
				'message' => __( 'Booking marked as paid successfully', 'royal-storage' ),
			) );
		} else {
			wp_send_json_error( array( 'message' => __( 'Failed to update payment status', 'royal-storage' ) ) );
		}
	}
}

