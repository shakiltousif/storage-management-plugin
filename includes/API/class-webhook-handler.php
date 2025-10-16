<?php
/**
 * Webhook Handler Class
 *
 * @package RoyalStorage\API
 * @since 1.0.0
 */

namespace RoyalStorage\API;

/**
 * Webhook handler class for external integrations
 */
class WebhookHandler {

	/**
	 * Constructor
	 */
	public function __construct() {
		add_action( 'rest_api_init', array( $this, 'register_webhook_routes' ) );
	}

	/**
	 * Register webhook routes
	 *
	 * @return void
	 */
	public function register_webhook_routes() {
		register_rest_route(
			'royal-storage/v1',
			'/webhooks/payment',
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'handle_payment_webhook' ),
				'permission_callback' => array( $this, 'verify_webhook_signature' ),
			)
		);

		register_rest_route(
			'royal-storage/v1',
			'/webhooks/booking',
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'handle_booking_webhook' ),
				'permission_callback' => array( $this, 'verify_webhook_signature' ),
			)
		);
	}

	/**
	 * Verify webhook signature
	 *
	 * @return bool
	 */
	public function verify_webhook_signature() {
		$signature = isset( $_SERVER['HTTP_X_WEBHOOK_SIGNATURE'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_X_WEBHOOK_SIGNATURE'] ) ) : '';
		$secret = get_option( 'royal_storage_webhook_secret' );

		if ( ! $secret || ! $signature ) {
			return false;
		}

		$body = file_get_contents( 'php://input' );
		$expected_signature = hash_hmac( 'sha256', $body, $secret );

		return hash_equals( $signature, $expected_signature );
	}

	/**
	 * Handle payment webhook
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response
	 */
	public function handle_payment_webhook( $request ) {
		$params = $request->get_json_params();

		$event_type = $params['event_type'] ?? '';
		$booking_id = $params['booking_id'] ?? 0;
		$payment_status = $params['payment_status'] ?? '';

		if ( ! $booking_id || ! $event_type ) {
			return new \WP_Error( 'invalid_params', 'Invalid parameters', array( 'status' => 400 ) );
		}

		global $wpdb;
		$bookings_table = $wpdb->prefix . 'royal_bookings';

		switch ( $event_type ) {
			case 'payment_completed':
				$wpdb->update(
					$bookings_table,
					array( 'payment_status' => 'paid' ),
					array( 'id' => $booking_id ),
					array( '%s' ),
					array( '%d' )
				);
				break;

			case 'payment_failed':
				$wpdb->update(
					$bookings_table,
					array( 'payment_status' => 'failed' ),
					array( 'id' => $booking_id ),
					array( '%s' ),
					array( '%d' )
				);
				break;

			case 'payment_refunded':
				$wpdb->update(
					$bookings_table,
					array( 'payment_status' => 'refunded' ),
					array( 'id' => $booking_id ),
					array( '%s' ),
					array( '%d' )
				);
				break;
		}

		return rest_ensure_response( array( 'success' => true ) );
	}

	/**
	 * Handle booking webhook
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response
	 */
	public function handle_booking_webhook( $request ) {
		$params = $request->get_json_params();

		$event_type = $params['event_type'] ?? '';
		$booking_id = $params['booking_id'] ?? 0;
		$booking_status = $params['booking_status'] ?? '';

		if ( ! $booking_id || ! $event_type ) {
			return new \WP_Error( 'invalid_params', 'Invalid parameters', array( 'status' => 400 ) );
		}

		global $wpdb;
		$bookings_table = $wpdb->prefix . 'royal_bookings';

		switch ( $event_type ) {
			case 'booking_confirmed':
				$wpdb->update(
					$bookings_table,
					array( 'status' => 'confirmed' ),
					array( 'id' => $booking_id ),
					array( '%s' ),
					array( '%d' )
				);
				break;

			case 'booking_cancelled':
				$wpdb->update(
					$bookings_table,
					array( 'status' => 'cancelled' ),
					array( 'id' => $booking_id ),
					array( '%s' ),
					array( '%d' )
				);
				break;

			case 'booking_expired':
				$wpdb->update(
					$bookings_table,
					array( 'status' => 'expired' ),
					array( 'id' => $booking_id ),
					array( '%s' ),
					array( '%d' )
				);
				break;
		}

		return rest_ensure_response( array( 'success' => true ) );
	}

	/**
	 * Send webhook
	 *
	 * @param string $event_type Event type.
	 * @param array  $data Event data.
	 * @return bool
	 */
	public static function send_webhook( $event_type, $data ) {
		$webhook_url = get_option( 'royal_storage_webhook_url' );
		$webhook_secret = get_option( 'royal_storage_webhook_secret' );

		if ( ! $webhook_url || ! $webhook_secret ) {
			return false;
		}

		$payload = wp_json_encode( array_merge( array( 'event_type' => $event_type ), $data ) );
		$signature = hash_hmac( 'sha256', $payload, $webhook_secret );

		$response = wp_remote_post(
			$webhook_url,
			array(
				'body'    => $payload,
				'headers' => array(
					'Content-Type'           => 'application/json',
					'X-Webhook-Signature'    => $signature,
				),
			)
		);

		return ! is_wp_error( $response );
	}
}

