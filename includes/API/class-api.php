<?php
/**
 * API Class
 *
 * @package RoyalStorage\API
 * @since 1.0.0
 */

namespace RoyalStorage\API;

/**
 * API class for REST API endpoints
 */
class API {

	/**
	 * Constructor
	 */
	public function __construct() {
		add_action( 'rest_api_init', array( $this, 'register_routes' ) );
	}

	/**
	 * Register REST API routes
	 *
	 * @return void
	 */
	public function register_routes() {
		// Register storage units endpoint.
		register_rest_route(
			'royal-storage/v1',
			'/units',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'get_units' ),
				'permission_callback' => '__return_true',
			)
		);

		// Register bookings endpoint.
		register_rest_route(
			'royal-storage/v1',
			'/bookings',
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'create_booking' ),
				'permission_callback' => array( $this, 'check_user_permission' ),
			)
		);

		// Register availability endpoint.
		register_rest_route(
			'royal-storage/v1',
			'/availability',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'get_availability' ),
				'permission_callback' => '__return_true',
			)
		);
	}

	/**
	 * Get units
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response
	 */
	public function get_units( $request ) {
		$unit_type = $request->get_param( 'type' );

		if ( 'parking' === $unit_type ) {
			$args = array(
				'post_type'      => 'rs_parking_space',
				'posts_per_page' => -1,
			);
		} else {
			$args = array(
				'post_type'      => 'rs_storage_unit',
				'posts_per_page' => -1,
			);
		}

		$units = get_posts( $args );

		return rest_ensure_response( $units );
	}

	/**
	 * Create booking
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response
	 */
	public function create_booking( $request ) {
		$params = $request->get_json_params();

		global $wpdb;
		$bookings_table = $wpdb->prefix . 'royal_bookings';

		$result = $wpdb->insert(
			$bookings_table,
			array(
				'customer_id'    => get_current_user_id(),
				'unit_id'        => $params['unit_id'],
				'unit_type'      => $params['unit_type'],
				'start_date'     => $params['start_date'],
				'end_date'       => $params['end_date'],
				'total_price'    => $params['total_price'],
				'vat_amount'     => $params['vat_amount'],
				'status'         => 'pending',
				'payment_status' => 'unpaid',
			),
			array( '%d', '%d', '%s', '%s', '%s', '%f', '%f', '%s', '%s' )
		);

		if ( $result ) {
			return rest_ensure_response(
				array(
					'success'    => true,
					'booking_id' => $wpdb->insert_id,
				)
			);
		}

		return new \WP_Error( 'booking_failed', __( 'Failed to create booking', 'royal-storage' ), array( 'status' => 400 ) );
	}

	/**
	 * Get availability
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response
	 */
	public function get_availability( $request ) {
		$unit_type = $request->get_param( 'type' );
		$start_date = $request->get_param( 'start_date' );
		$end_date = $request->get_param( 'end_date' );

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

		return rest_ensure_response(
			array(
				'total'     => count( $all_units ),
				'available' => count( $available ),
				'booked'    => count( $booked_units ),
			)
		);
	}

	/**
	 * Check user permission
	 *
	 * @return bool
	 */
	public function check_user_permission() {
		return is_user_logged_in();
	}
}

