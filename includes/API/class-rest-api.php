<?php
/**
 * REST API Class
 *
 * @package RoyalStorage\API
 * @since 1.0.0
 */

namespace RoyalStorage\API;

/**
 * REST API class for external integrations
 */
class RestAPI {

	/**
	 * API namespace
	 *
	 * @var string
	 */
	private $namespace = 'royal-storage/v1';

	/**
	 * Constructor
	 */
	public function __construct() {
		add_action( 'rest_api_init', array( $this, 'register_routes' ) );
	}

	/**
	 * Register API routes
	 *
	 * @return void
	 */
	public function register_routes() {
		// Bookings endpoints
		register_rest_route(
			$this->namespace,
			'/bookings',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'get_bookings' ),
				'permission_callback' => array( $this, 'check_permission' ),
			)
		);

		register_rest_route(
			$this->namespace,
			'/bookings/(?P<id>\d+)',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'get_booking' ),
				'permission_callback' => array( $this, 'check_permission' ),
			)
		);

		register_rest_route(
			$this->namespace,
			'/bookings',
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'create_booking' ),
				'permission_callback' => array( $this, 'check_permission' ),
			)
		);

		// Invoices endpoints
		register_rest_route(
			$this->namespace,
			'/invoices',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'get_invoices' ),
				'permission_callback' => array( $this, 'check_permission' ),
			)
		);

		register_rest_route(
			$this->namespace,
			'/invoices/(?P<id>\d+)',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'get_invoice' ),
				'permission_callback' => array( $this, 'check_permission' ),
			)
		);

		// Reports endpoints
		register_rest_route(
			$this->namespace,
			'/reports/revenue',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'get_revenue_report' ),
				'permission_callback' => array( $this, 'check_admin_permission' ),
			)
		);

		register_rest_route(
			$this->namespace,
			'/reports/occupancy',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'get_occupancy_report' ),
				'permission_callback' => array( $this, 'check_admin_permission' ),
			)
		);

		// Analytics endpoints
		register_rest_route(
			$this->namespace,
			'/analytics/dashboard',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'get_dashboard_metrics' ),
				'permission_callback' => array( $this, 'check_admin_permission' ),
			)
		);
	}

	/**
	 * Check permission
	 *
	 * @return bool
	 */
	public function check_permission() {
		return is_user_logged_in();
	}

	/**
	 * Check admin permission
	 *
	 * @return bool
	 */
	public function check_admin_permission() {
		return current_user_can( 'manage_options' );
	}

	/**
	 * Get bookings
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response
	 */
	public function get_bookings( $request ) {
		global $wpdb;

		$customer_id = get_current_user_id();
		$bookings_table = $wpdb->prefix . 'royal_bookings';

		$bookings = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM $bookings_table WHERE customer_id = %d ORDER BY created_at DESC",
				$customer_id
			)
		);

		return rest_ensure_response( $bookings );
	}

	/**
	 * Get booking
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response
	 */
	public function get_booking( $request ) {
		global $wpdb;

		$booking_id = $request['id'];
		$customer_id = get_current_user_id();
		$bookings_table = $wpdb->prefix . 'royal_bookings';

		$booking = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM $bookings_table WHERE id = %d AND customer_id = %d",
				$booking_id,
				$customer_id
			)
		);

		if ( ! $booking ) {
			return new \WP_Error( 'not_found', 'Booking not found', array( 'status' => 404 ) );
		}

		return rest_ensure_response( $booking );
	}

	/**
	 * Create booking
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response
	 */
	public function create_booking( $request ) {
		$params = $request->get_json_params();

		$booking_engine = new \RoyalStorage\BookingEngine();
		$booking_id = $booking_engine->create_booking( $params );

		if ( ! $booking_id ) {
			return new \WP_Error( 'creation_failed', 'Failed to create booking', array( 'status' => 400 ) );
		}

		return rest_ensure_response( array( 'booking_id' => $booking_id ) );
	}

	/**
	 * Get invoices
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response
	 */
	public function get_invoices( $request ) {
		global $wpdb;

		$customer_id = get_current_user_id();
		$invoices_table = $wpdb->prefix . 'royal_invoices';

		$invoices = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM $invoices_table WHERE customer_id = %d ORDER BY created_at DESC",
				$customer_id
			)
		);

		return rest_ensure_response( $invoices );
	}

	/**
	 * Get invoice
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response
	 */
	public function get_invoice( $request ) {
		global $wpdb;

		$invoice_id = $request['id'];
		$customer_id = get_current_user_id();
		$invoices_table = $wpdb->prefix . 'royal_invoices';

		$invoice = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM $invoices_table WHERE id = %d AND customer_id = %d",
				$invoice_id,
				$customer_id
			)
		);

		if ( ! $invoice ) {
			return new \WP_Error( 'not_found', 'Invoice not found', array( 'status' => 404 ) );
		}

		return rest_ensure_response( $invoice );
	}

	/**
	 * Get revenue report
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response
	 */
	public function get_revenue_report( $request ) {
		$start_date = $request->get_param( 'start_date' ) ?: date( 'Y-m-01' );
		$end_date = $request->get_param( 'end_date' ) ?: date( 'Y-m-d' );

		$reports = new \RoyalStorage\AdvancedReports();
		$report = $reports->get_revenue_report( $start_date, $end_date );

		return rest_ensure_response( $report );
	}

	/**
	 * Get occupancy report
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response
	 */
	public function get_occupancy_report( $request ) {
		$start_date = $request->get_param( 'start_date' ) ?: date( 'Y-m-01' );
		$end_date = $request->get_param( 'end_date' ) ?: date( 'Y-m-d' );

		$reports = new \RoyalStorage\AdvancedReports();
		$report = $reports->get_occupancy_report( $start_date, $end_date );

		return rest_ensure_response( $report );
	}

	/**
	 * Get dashboard metrics
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response
	 */
	public function get_dashboard_metrics( $request ) {
		$reports = new \RoyalStorage\AdvancedReports();
		$metrics = $reports->get_dashboard_metrics();

		return rest_ensure_response( $metrics );
	}
}

