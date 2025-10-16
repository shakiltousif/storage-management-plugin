<?php
/**
 * Email Manager Class
 *
 * @package RoyalStorage
 * @since 1.0.0
 */

namespace RoyalStorage;

/**
 * Email Manager
 */
class EmailManager {

	/**
	 * Send booking confirmation email
	 *
	 * @param int $booking_id Booking ID.
	 * @return bool
	 */
	public function send_booking_confirmation( $booking_id ) {
		$booking = new Models\Booking( $booking_id );
		$customer = get_user_by( 'id', $booking->get_customer_id() );

		if ( ! $customer ) {
			return false;
		}

		$subject = __( 'Booking Confirmation - Royal Storage', 'royal-storage' );
		$template_file = ROYAL_STORAGE_DIR . 'templates/emails/booking-confirmation.php';

		$message = $this->render_template( $template_file, array(
			'booking_id'   => $booking_id,
			'unit_name'    => $this->get_unit_name( $booking ),
			'start_date'   => $booking->get_start_date(),
			'end_date'     => $booking->get_end_date(),
			'total_price'  => $booking->get_total_price(),
			'access_code'  => $booking->get_access_code(),
		) );

		return wp_mail( $customer->user_email, $subject, $message, $this->get_email_headers() );
	}

	/**
	 * Send payment confirmation email
	 *
	 * @param int $booking_id Booking ID.
	 * @return bool
	 */
	public function send_payment_confirmation( $booking_id ) {
		$booking = new Models\Booking( $booking_id );
		$customer = get_user_by( 'id', $booking->get_customer_id() );

		if ( ! $customer ) {
			return false;
		}

		$subject = __( 'Payment Confirmed - Royal Storage', 'royal-storage' );
		$template_file = ROYAL_STORAGE_DIR . 'templates/emails/payment-confirmation.php';

		$message = $this->render_template( $template_file, array(
			'booking_id'      => $booking_id,
			'payment_amount'  => $booking->get_total_price(),
			'payment_date'    => gmdate( 'Y-m-d H:i:s' ),
			'payment_method'  => 'Online Payment',
			'transaction_id'  => 'TXN-' . $booking_id . '-' . gmdate( 'YmdHis' ),
			'access_code'     => $booking->get_access_code(),
		) );

		return wp_mail( $customer->user_email, $subject, $message, $this->get_email_headers() );
	}

	/**
	 * Send expiry reminder email
	 *
	 * @param int $booking_id Booking ID.
	 * @return bool
	 */
	public function send_expiry_reminder( $booking_id ) {
		$booking = new Models\Booking( $booking_id );
		$customer = get_user_by( 'id', $booking->get_customer_id() );

		if ( ! $customer ) {
			return false;
		}

		$subject = __( 'Booking Expiry Reminder - Royal Storage', 'royal-storage' );
		$template_file = ROYAL_STORAGE_DIR . 'templates/emails/expiry-reminder.php';

		$message = $this->render_template( $template_file, array(
			'booking_id'     => $booking_id,
			'unit_name'      => $this->get_unit_name( $booking ),
			'end_date'       => $booking->get_end_date(),
			'renewal_link'   => home_url( '/customer-portal/?action=renew&booking=' . $booking_id ),
		) );

		return wp_mail( $customer->user_email, $subject, $message, $this->get_email_headers() );
	}

	/**
	 * Send overdue reminder email
	 *
	 * @param int $booking_id Booking ID.
	 * @return bool
	 */
	public function send_overdue_reminder( $booking_id ) {
		$booking = new Models\Booking( $booking_id );
		$customer = get_user_by( 'id', $booking->get_customer_id() );

		if ( ! $customer ) {
			return false;
		}

		$subject = __( 'Overdue Payment Reminder - Royal Storage', 'royal-storage' );
		$template_file = ROYAL_STORAGE_DIR . 'templates/emails/overdue-reminder.php';

		$end_date = new \DateTime( $booking->get_end_date() );
		$today = new \DateTime();
		$days_overdue = $today->diff( $end_date )->days;

		$pricing_engine = new PricingEngine();
		$late_fee = $pricing_engine->calculate_late_fee( 100, $days_overdue ); // Assuming 100 RSD daily rate

		$message = $this->render_template( $template_file, array(
			'booking_id'           => $booking_id,
			'unit_name'            => $this->get_unit_name( $booking ),
			'end_date'             => $booking->get_end_date(),
			'outstanding_amount'   => $booking->get_total_price(),
			'days_overdue'         => $days_overdue,
			'late_fee'             => $late_fee,
			'payment_link'         => home_url( '/customer-portal/?action=pay&booking=' . $booking_id ),
		) );

		return wp_mail( $customer->user_email, $subject, $message, $this->get_email_headers() );
	}

	/**
	 * Render email template
	 *
	 * @param string $template_file Template file path.
	 * @param array  $variables     Template variables.
	 * @return string
	 */
	private function render_template( $template_file, $variables ) {
		if ( ! file_exists( $template_file ) ) {
			return '';
		}

		ob_start();
		include $template_file;
		$content = ob_get_clean();

		// Replace variables
		foreach ( $variables as $key => $value ) {
			$content = str_replace( '{' . $key . '}', $value, $content );
		}

		return $content;
	}

	/**
	 * Get email headers
	 *
	 * @return array
	 */
	private function get_email_headers() {
		$headers = array( 'Content-Type: text/html; charset=UTF-8' );
		$from_email = get_option( 'royal_storage_business_email', get_option( 'admin_email' ) );
		$from_name = get_option( 'royal_storage_business_name', 'Royal Storage' );

		$headers[] = 'From: ' . $from_name . ' <' . $from_email . '>';

		return $headers;
	}

	/**
	 * Get unit name
	 *
	 * @param Models\Booking $booking Booking object.
	 * @return string
	 */
	private function get_unit_name( $booking ) {
		if ( $booking->get_unit_id() > 0 ) {
			$unit = new Models\StorageUnit( $booking->get_unit_id() );
			return 'Storage Unit - ' . $unit->get_size();
		}

		if ( $booking->get_space_id() > 0 ) {
			$space = new Models\ParkingSpace( $booking->get_space_id() );
			return 'Parking Space - ' . $space->get_spot_number();
		}

		return 'Unknown';
	}
}

