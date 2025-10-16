<?php
/**
 * Invoice Generator Class
 *
 * @package RoyalStorage
 * @since 1.0.0
 */

namespace RoyalStorage;

/**
 * Invoice generator class for creating and managing invoices
 */
class InvoiceGenerator {

	/**
	 * Create invoice
	 *
	 * @param array $data Invoice data.
	 * @return int|false
	 */
	public function create_invoice( $data ) {
		global $wpdb;

		$invoices_table = $wpdb->prefix . 'royal_invoices';

		$invoice_number = $this->generate_invoice_number();

		$result = $wpdb->insert(
			$invoices_table,
			array(
				'booking_id'      => $data['booking_id'],
				'customer_id'     => $data['customer_id'],
				'invoice_number'  => $invoice_number,
				'amount'          => $data['amount'],
				'vat_amount'      => $data['vat_amount'],
				'total_amount'    => $data['total_amount'],
				'status'          => $data['status'] ?? 'pending',
				'created_at'      => current_time( 'mysql' ),
			),
			array( '%d', '%d', '%s', '%f', '%f', '%f', '%s', '%s' )
		);

		return $result ? $wpdb->insert_id : false;
	}

	/**
	 * Generate invoice number
	 *
	 * @return string
	 */
	private function generate_invoice_number() {
		$year = date( 'Y' );
		$prefix = "RS-{$year}-";
		
		global $wpdb;
		$invoices_table = $wpdb->prefix . 'royal_invoices';
		
		$last_invoice = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT invoice_number FROM $invoices_table WHERE invoice_number LIKE %s ORDER BY invoice_number DESC LIMIT 1",
				$prefix . '%'
			)
		);

		if ( $last_invoice ) {
			$last_number = intval( str_replace( $prefix, '', $last_invoice ) );
			$new_number = $last_number + 1;
		} else {
			$new_number = 1;
		}

		return $prefix . str_pad( $new_number, 6, '0', STR_PAD_LEFT );
	}

	/**
	 * Get invoice by ID
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
	 * Update invoice status
	 *
	 * @param int    $invoice_id Invoice ID.
	 * @param string $status Status.
	 * @return bool
	 */
	public function update_invoice_status( $invoice_id, $status ) {
		global $wpdb;
		$invoices_table = $wpdb->prefix . 'royal_invoices';

		return $wpdb->update(
			$invoices_table,
			array( 'status' => $status ),
			array( 'id' => $invoice_id ),
			array( '%s' ),
			array( '%d' )
		);
	}

	/**
	 * Generate invoice HTML
	 *
	 * @param int $invoice_id Invoice ID.
	 * @return string
	 */
	public function generate_invoice_html( $invoice_id ) {
		$invoice = $this->get_invoice( $invoice_id );
		
		if ( ! $invoice ) {
			return '';
		}

		$html = '<div class="royal-storage-invoice">';
		$html .= '<h2>Invoice #' . esc_html( $invoice->invoice_number ) . '</h2>';
		$html .= '<p>Amount: ' . esc_html( $invoice->amount ) . ' RSD</p>';
		$html .= '<p>VAT: ' . esc_html( $invoice->vat_amount ) . ' RSD</p>';
		$html .= '<p>Total: ' . esc_html( $invoice->total_amount ) . ' RSD</p>';
		$html .= '<p>Status: ' . esc_html( $invoice->status ) . '</p>';
		$html .= '</div>';

		return $html;
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
	 * Get invoices by status
	 *
	 * @param string $status Status.
	 * @return array
	 */
	public function get_invoices_by_status( $status ) {
		global $wpdb;
		$invoices_table = $wpdb->prefix . 'royal_invoices';

		return $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM $invoices_table WHERE status = %s ORDER BY created_at DESC",
				$status
			)
		);
	}
}