<?php
/**
 * Pay Later Payment Gateway
 *
 * @package RoyalStorage
 * @since 1.0.0
 */

namespace RoyalStorage;

/**
 * Pay Later Payment Gateway Class
 */
class PayLaterGateway extends \WC_Payment_Gateway {

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->id                 = 'royal_storage_pay_later';
		$this->icon               = '';
		$this->has_fields         = true;
		$this->method_title       = __( 'Royal Storage - Pay Later', 'royal-storage' );
		$this->method_description = __( 'Allow customers to book storage units and pay later. Invoice will be sent via email.', 'royal-storage' );

		// Load the settings
		$this->init_form_fields();
		$this->init_settings();

		// Define user set variables
		$this->title              = $this->get_option( 'title' );
		$this->description        = $this->get_option( 'description' );
		$this->instructions       = $this->get_option( 'instructions' );
		$this->enable_for_methods = $this->get_option( 'enable_for_methods', array() );

		// Actions
		add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
		add_action( 'woocommerce_thankyou_' . $this->id, array( $this, 'thankyou_page' ) );

		// Customer Emails
		add_action( 'woocommerce_email_before_order_table', array( $this, 'email_instructions' ), 10, 3 );
	}

	/**
	 * Initialize Gateway Settings Form Fields
	 */
	public function init_form_fields() {
		$this->form_fields = array(
			'enabled'      => array(
				'title'   => __( 'Enable/Disable', 'royal-storage' ),
				'type'    => 'checkbox',
				'label'   => __( 'Enable Pay Later Payment', 'royal-storage' ),
				'default' => 'no',
			),
			'title'        => array(
				'title'       => __( 'Title', 'royal-storage' ),
				'type'        => 'text',
				'description' => __( 'This controls the title which the user sees during checkout.', 'royal-storage' ),
				'default'     => __( 'Pay Later', 'royal-storage' ),
				'desc_tip'    => true,
			),
			'description'  => array(
				'title'       => __( 'Description', 'royal-storage' ),
				'type'        => 'textarea',
				'description' => __( 'Payment method description that the customer will see on your checkout.', 'royal-storage' ),
				'default'     => __( 'Reserve your storage unit now and pay later. An invoice will be sent to your email.', 'royal-storage' ),
				'desc_tip'    => true,
			),
			'instructions' => array(
				'title'       => __( 'Instructions', 'royal-storage' ),
				'type'        => 'textarea',
				'description' => __( 'Instructions that will be added to the thank you page and emails.', 'royal-storage' ),
				'default'     => __( 'Thank you for your booking! An invoice has been sent to your email address. Please make payment within 7 days to confirm your reservation.', 'royal-storage' ),
				'desc_tip'    => true,
			),
			'payment_due_days' => array(
				'title'       => __( 'Payment Due Days', 'royal-storage' ),
				'type'        => 'number',
				'description' => __( 'Number of days customer has to make payment.', 'royal-storage' ),
				'default'     => '7',
				'desc_tip'    => true,
			),
		);
	}

	/**
	 * Process the payment and return the result
	 *
	 * @param int $order_id Order ID.
	 * @return array
	 */
	public function process_payment( $order_id ) {
		$order = wc_get_order( $order_id );

		// Mark as on-hold (we're awaiting the payment)
		$order->update_status( 'on-hold', __( 'Awaiting payment - Pay Later', 'royal-storage' ) );

		// Add order note
		$payment_due_days = $this->get_option( 'payment_due_days', '7' );
		$due_date = date( 'F j, Y', strtotime( '+' . $payment_due_days . ' days' ) );
		$order->add_order_note( sprintf(
			__( 'Payment pending. Invoice sent to customer. Payment due by %s', 'royal-storage' ),
			$due_date
		) );

		// Store payment due date in order meta
		$order->update_meta_data( '_payment_due_date', date( 'Y-m-d', strtotime( '+' . $payment_due_days . ' days' ) ) );
		$order->update_meta_data( '_payment_method_title', $this->title );
		$order->save();

		// Send invoice email
		$this->send_invoice_email( $order );

		// Reduce stock levels
		wc_reduce_stock_levels( $order_id );

		// Remove cart
		WC()->cart->empty_cart();

		// Return thankyou redirect
		return array(
			'result'   => 'success',
			'redirect' => $this->get_return_url( $order ),
		);
	}

	/**
	 * Send invoice email to customer
	 *
	 * @param \WC_Order $order Order object.
	 * @return bool
	 */
	private function send_invoice_email( $order ) {
		// Get customer email
		$customer_email = $order->get_billing_email();

		if ( empty( $customer_email ) ) {
			return false;
		}

		// Email subject
		$subject = sprintf(
			__( 'Invoice for Order #%s - Royal Storage', 'royal-storage' ),
			$order->get_order_number()
		);

		// Get payment due date
		$payment_due_date = $order->get_meta( '_payment_due_date' );
		$due_date_formatted = $payment_due_date ? date( 'F j, Y', strtotime( $payment_due_date ) ) : 'N/A';

		// Build email content
		$message = $this->get_invoice_email_content( $order, $due_date_formatted );

		// Email headers
		$headers = array(
			'Content-Type: text/html; charset=UTF-8',
			'From: ' . get_bloginfo( 'name' ) . ' <' . get_option( 'admin_email' ) . '>',
		);

		// Send email
		$sent = wp_mail( $customer_email, $subject, $message, $headers );

		if ( $sent ) {
			$order->add_order_note( __( 'Invoice email sent to customer.', 'royal-storage' ) );
		} else {
			$order->add_order_note( __( 'Failed to send invoice email to customer.', 'royal-storage' ) );
		}

		return $sent;
	}

	/**
	 * Get invoice email content
	 *
	 * @param \WC_Order $order Order object.
	 * @param string    $due_date_formatted Formatted due date.
	 * @return string
	 */
	private function get_invoice_email_content( $order, $due_date_formatted ) {
		ob_start();
		?>
		<!DOCTYPE html>
		<html>
		<head>
			<meta charset="UTF-8">
			<title><?php echo esc_html( sprintf( __( 'Invoice for Order #%s', 'royal-storage' ), $order->get_order_number() ) ); ?></title>
			<style>
				body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
				.container { max-width: 600px; margin: 0 auto; padding: 20px; }
				.header { background: #0073aa; color: white; padding: 20px; text-align: center; }
				.content { background: #f9f9f9; padding: 20px; }
				.invoice-details { background: white; padding: 15px; margin: 20px 0; border-left: 4px solid #0073aa; }
				.invoice-table { width: 100%; border-collapse: collapse; margin: 20px 0; }
				.invoice-table th, .invoice-table td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
				.invoice-table th { background: #f0f0f0; font-weight: bold; }
				.total-row { font-weight: bold; font-size: 1.2em; background: #f9f9f9; }
				.payment-info { background: #fff3cd; border-left: 4px solid #ffc107; padding: 15px; margin: 20px 0; }
				.footer { text-align: center; padding: 20px; color: #666; font-size: 0.9em; }
			</style>
		</head>
		<body>
			<div class="container">
				<div class="header">
					<h1><?php echo esc_html( get_bloginfo( 'name' ) ); ?></h1>
					<p><?php esc_html_e( 'Storage Unit Booking Invoice', 'royal-storage' ); ?></p>
				</div>

				<div class="content">
					<h2><?php esc_html_e( 'Thank you for your booking!', 'royal-storage' ); ?></h2>
					<p><?php esc_html_e( 'Your storage unit has been reserved. Please find your invoice details below:', 'royal-storage' ); ?></p>

					<div class="invoice-details">
						<p><strong><?php esc_html_e( 'Invoice Number:', 'royal-storage' ); ?></strong> <?php echo esc_html( $order->get_order_number() ); ?></p>
						<p><strong><?php esc_html_e( 'Order Date:', 'royal-storage' ); ?></strong> <?php echo esc_html( $order->get_date_created()->format( 'F j, Y' ) ); ?></p>
						<p><strong><?php esc_html_e( 'Customer Name:', 'royal-storage' ); ?></strong> <?php echo esc_html( $order->get_billing_first_name() . ' ' . $order->get_billing_last_name() ); ?></p>
						<p><strong><?php esc_html_e( 'Email:', 'royal-storage' ); ?></strong> <?php echo esc_html( $order->get_billing_email() ); ?></p>
					</div>

					<table class="invoice-table">
						<thead>
							<tr>
								<th><?php esc_html_e( 'Item', 'royal-storage' ); ?></th>
								<th><?php esc_html_e( 'Quantity', 'royal-storage' ); ?></th>
								<th><?php esc_html_e( 'Price', 'royal-storage' ); ?></th>
								<th><?php esc_html_e( 'Total', 'royal-storage' ); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ( $order->get_items() as $item_id => $item ) : ?>
								<tr>
									<td><?php echo esc_html( $item->get_name() ); ?></td>
									<td><?php echo esc_html( $item->get_quantity() ); ?></td>
									<td><?php echo wp_kses_post( wc_price( $item->get_subtotal() / $item->get_quantity(), array( 'currency' => $order->get_currency() ) ) ); ?></td>
									<td><?php echo wp_kses_post( wc_price( $item->get_subtotal(), array( 'currency' => $order->get_currency() ) ) ); ?></td>
								</tr>
							<?php endforeach; ?>

							<tr>
								<td colspan="3" style="text-align: right;"><strong><?php esc_html_e( 'Subtotal:', 'royal-storage' ); ?></strong></td>
								<td><?php echo wp_kses_post( wc_price( $order->get_subtotal(), array( 'currency' => $order->get_currency() ) ) ); ?></td>
							</tr>

							<?php if ( $order->get_total_tax() > 0 ) : ?>
							<tr>
								<td colspan="3" style="text-align: right;"><strong><?php esc_html_e( 'Tax:', 'royal-storage' ); ?></strong></td>
								<td><?php echo wp_kses_post( wc_price( $order->get_total_tax(), array( 'currency' => $order->get_currency() ) ) ); ?></td>
							</tr>
							<?php endif; ?>

							<tr class="total-row">
								<td colspan="3" style="text-align: right;"><strong><?php esc_html_e( 'Total Amount:', 'royal-storage' ); ?></strong></td>
								<td><?php echo wp_kses_post( wc_price( $order->get_total(), array( 'currency' => $order->get_currency() ) ) ); ?></td>
							</tr>
						</tbody>
					</table>

					<div class="payment-info">
						<h3><?php esc_html_e( 'Payment Information', 'royal-storage' ); ?></h3>
						<p><strong><?php esc_html_e( 'Payment Due Date:', 'royal-storage' ); ?></strong> <?php echo esc_html( $due_date_formatted ); ?></p>
						<p><?php esc_html_e( 'Please make payment by the due date to confirm your reservation.', 'royal-storage' ); ?></p>
						<p>
							<strong><?php esc_html_e( 'Payment Link:', 'royal-storage' ); ?></strong><br>
							<a href="<?php echo esc_url( $order->get_checkout_payment_url() ); ?>" style="color: #0073aa;">
								<?php echo esc_url( $order->get_checkout_payment_url() ); ?>
							</a>
						</p>
					</div>

					<p><?php esc_html_e( 'If you have any questions about this invoice, please contact us.', 'royal-storage' ); ?></p>
				</div>

				<div class="footer">
					<p>&copy; <?php echo esc_html( date( 'Y' ) . ' ' . get_bloginfo( 'name' ) ); ?>. <?php esc_html_e( 'All rights reserved.', 'royal-storage' ); ?></p>
					<p><?php echo esc_html( get_bloginfo( 'url' ) ); ?></p>
				</div>
			</div>
		</body>
		</html>
		<?php
		return ob_get_clean();
	}

	/**
	 * Output for the order received page
	 *
	 * @param int $order_id Order ID.
	 */
	public function thankyou_page( $order_id ) {
		if ( $this->instructions ) {
			echo wp_kses_post( wpautop( wptexturize( $this->instructions ) ) );
		}

		$order = wc_get_order( $order_id );
		$payment_due_date = $order->get_meta( '_payment_due_date' );

		if ( $payment_due_date ) {
			echo '<p><strong>' . esc_html__( 'Payment Due Date:', 'royal-storage' ) . '</strong> ' . esc_html( date( 'F j, Y', strtotime( $payment_due_date ) ) ) . '</p>';
		}

		echo '<p><a href="' . esc_url( $order->get_checkout_payment_url() ) . '" class="button">' . esc_html__( 'Make Payment Now', 'royal-storage' ) . '</a></p>';
	}

	/**
	 * Add content to the WC emails
	 *
	 * @param \WC_Order $order Order object.
	 * @param bool      $sent_to_admin Sent to admin.
	 * @param bool      $plain_text Email format: plain text or HTML.
	 */
	public function email_instructions( $order, $sent_to_admin, $plain_text = false ) {
		if ( $this->instructions && ! $sent_to_admin && $this->id === $order->get_payment_method() ) {
			echo wp_kses_post( wpautop( wptexturize( $this->instructions ) ) . PHP_EOL );
		}
	}

	/**
	 * Payment fields
	 */
	public function payment_fields() {
		if ( $this->description ) {
			echo wpautop( wptexturize( $this->description ) );
		}

		$payment_due_days = $this->get_option( 'payment_due_days', '7' );
		echo '<p>' . sprintf(
			esc_html__( 'You will have %s days to complete your payment after booking.', 'royal-storage' ),
			'<strong>' . esc_html( $payment_due_days ) . '</strong>'
		) . '</p>';
		echo '<p>' . esc_html__( 'An invoice with payment details will be sent to your email address.', 'royal-storage' ) . '</p>';
	}
}
