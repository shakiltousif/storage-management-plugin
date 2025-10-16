<?php
/**
 * Invoices Class
 *
 * @package RoyalStorage\Frontend
 * @since 1.0.0
 */

namespace RoyalStorage\Frontend;

/**
 * Invoices class for customer portal invoices management
 */
class Invoices {

	/**
	 * Constructor
	 */
	public function __construct() {
		add_action( 'wp_ajax_royal_storage_download_invoice', array( $this, 'handle_download_invoice' ) );
		add_action( 'wp_ajax_royal_storage_pay_invoice', array( $this, 'handle_pay_invoice' ) );
	}

	/**
	 * Get customer invoices
	 *
	 * @param int $customer_id Customer ID.
	 * @return array
	 */
	public function get_customer_invoices( $customer_id ) {
		global $wpdb;

		$invoices_table = $wpdb->prefix . 'royal_invoices';

		return $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM $invoices_table WHERE customer_id = %d ORDER BY created_at DESC",
				$customer_id
			)
		);
	}

	/**
	 * Get unpaid invoices
	 *
	 * @param int $customer_id Customer ID.
	 * @return array
	 */
	public function get_unpaid_invoices( $customer_id ) {
		global $wpdb;

		$invoices_table = $wpdb->prefix . 'royal_invoices';

		return $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM $invoices_table WHERE customer_id = %d AND payment_status = 'unpaid' ORDER BY due_date ASC",
				$customer_id
			)
		);
	}

	/**
	 * Get invoice details
	 *
	 * @param int $invoice_id Invoice ID.
	 * @return object|null
	 */
	public function get_invoice( $invoice_id ) {
		global $wpdb;

		$invoices_table = $wpdb->prefix . 'royal_invoices';

		return $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM $invoices_table WHERE id = %d",
				$invoice_id
			)
		);
	}

	/**
	 * Get invoice items
	 *
	 * @param int $invoice_id Invoice ID.
	 * @return array
	 */
	public function get_invoice_items( $invoice_id ) {
		global $wpdb;

		$items_table = $wpdb->prefix . 'royal_invoice_items';

		return $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM $items_table WHERE invoice_id = %d",
				$invoice_id
			)
		);
	}

	/**
	 * Get total unpaid amount
	 *
	 * @param int $customer_id Customer ID.
	 * @return float
	 */
	public function get_total_unpaid( $customer_id ) {
		global $wpdb;

		$invoices_table = $wpdb->prefix . 'royal_invoices';

		$total = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT SUM(total_amount) FROM $invoices_table WHERE customer_id = %d AND payment_status = 'unpaid'",
				$customer_id
			)
		);

		return floatval( $total ?: 0 );
	}

	/**
	 * Handle download invoice AJAX
	 *
	 * @return void
	 */
	public function handle_download_invoice() {
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'royal_storage_portal' ) ) {
			wp_send_json_error( array( 'message' => __( 'Security check failed', 'royal-storage' ) ) );
		}

		if ( ! is_user_logged_in() ) {
			wp_send_json_error( array( 'message' => __( 'Not logged in', 'royal-storage' ) ) );
		}

		$invoice_id = isset( $_POST['invoice_id'] ) ? intval( $_POST['invoice_id'] ) : 0;
		$invoice = $this->get_invoice( $invoice_id );

		if ( ! $invoice || $invoice->customer_id !== get_current_user_id() ) {
			wp_send_json_error( array( 'message' => __( 'Invoice not found', 'royal-storage' ) ) );
		}

		// Generate PDF and send download
		wp_send_json_success( array( 'message' => __( 'Invoice download started', 'royal-storage' ) ) );
	}

	/**
	 * Handle pay invoice AJAX
	 *
	 * @return void
	 */
	public function handle_pay_invoice() {
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'royal_storage_portal' ) ) {
			wp_send_json_error( array( 'message' => __( 'Security check failed', 'royal-storage' ) ) );
		}

		if ( ! is_user_logged_in() ) {
			wp_send_json_error( array( 'message' => __( 'Not logged in', 'royal-storage' ) ) );
		}

		$invoice_id = isset( $_POST['invoice_id'] ) ? intval( $_POST['invoice_id'] ) : 0;
		$invoice = $this->get_invoice( $invoice_id );

		if ( ! $invoice || $invoice->customer_id !== get_current_user_id() ) {
			wp_send_json_error( array( 'message' => __( 'Invoice not found', 'royal-storage' ) ) );
		}

		// Redirect to payment page
		wp_send_json_success( array( 'redirect' => add_query_arg( 'invoice_id', $invoice_id, home_url( '/checkout/' ) ) ) );
	}

	/**
	 * Get invoice status label
	 *
	 * @param string $status Status.
	 * @return string
	 */
	public static function get_status_label( $status ) {
		$labels = array(
			'draft'     => __( 'Draft', 'royal-storage' ),
			'sent'      => __( 'Sent', 'royal-storage' ),
			'viewed'    => __( 'Viewed', 'royal-storage' ),
			'paid'      => __( 'Paid', 'royal-storage' ),
			'overdue'   => __( 'Overdue', 'royal-storage' ),
			'cancelled' => __( 'Cancelled', 'royal-storage' ),
		);

		return $labels[ $status ] ?? ucfirst( $status );
	}

	/**
	 * Get payment status label
	 *
	 * @param string $status Status.
	 * @return string
	 */
	public static function get_payment_status_label( $status ) {
		$labels = array(
			'paid'     => __( 'Paid', 'royal-storage' ),
			'unpaid'   => __( 'Unpaid', 'royal-storage' ),
			'pending'  => __( 'Pending', 'royal-storage' ),
			'failed'   => __( 'Failed', 'royal-storage' ),
			'refunded' => __( 'Refunded', 'royal-storage' ),
		);

		return $labels[ $status ] ?? ucfirst( $status );
	}
}

