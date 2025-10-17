<?php
/**
 * Portal Class
 *
 * @package RoyalStorage\Frontend
 * @since 1.0.0
 */

namespace RoyalStorage\Frontend;

use RoyalStorage\EmailManager;

/**
 * Portal class for customer portal
 */
class Portal {

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
		$this->email_manager = new EmailManager();
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		add_action( 'init', array( $this, 'register_shortcodes' ) );
		add_action( 'wp_ajax_royal_storage_renew_booking', array( $this, 'handle_renew_booking' ) );
		add_action( 'wp_ajax_royal_storage_cancel_booking', array( $this, 'handle_cancel_booking' ) );
	}

	/**
	 * Enqueue frontend scripts and styles
	 *
	 * @return void
	 */
	public function enqueue_scripts() {
		if ( is_user_logged_in() ) {
			wp_enqueue_style(
				'royal-storage-portal',
				ROYAL_STORAGE_URL . 'assets/css/portal.css',
				array(),
				ROYAL_STORAGE_VERSION
			);

			wp_enqueue_script(
				'royal-storage-portal',
				ROYAL_STORAGE_URL . 'assets/js/portal.js',
				array( 'jquery' ),
				ROYAL_STORAGE_VERSION,
				true
			);

			wp_localize_script(
				'royal-storage-portal',
				'royalStoragePortal',
				array(
					'ajaxUrl' => admin_url( 'admin-ajax.php' ),
					'nonce'   => wp_create_nonce( 'royal_storage_portal' ),
				)
			);
		}
	}

	/**
	 * Register shortcodes
	 *
	 * @return void
	 */
	public function register_shortcodes() {
		add_shortcode( 'royal_storage_portal', array( $this, 'render_portal' ) );
		add_shortcode( 'royal_storage_login', array( $this, 'render_login' ) );
	}

	/**
	 * Initialize portal
	 *
	 * @return void
	 */
	public function init() {
		// Portal initialization code.
	}

	/**
	 * Render portal shortcode
	 *
	 * @return string
	 */
	public function render_portal() {
		if ( ! is_user_logged_in() ) {
			return $this->render_login();
		}

		$current_user = wp_get_current_user();
		$tab = isset( $_GET['tab'] ) ? sanitize_text_field( wp_unslash( $_GET['tab'] ) ) : 'dashboard';

		ob_start();
		?>
		<div class="royal-storage-portal">
			<div class="portal-header">
				<h1><?php esc_html_e( 'My Storage Account', 'royal-storage' ); ?></h1>
				<div class="portal-user-info">
					<span><?php echo esc_html( $current_user->display_name ); ?></span>
					<a href="<?php echo esc_url( wp_logout_url( home_url() ) ); ?>" class="logout-btn">
						<?php esc_html_e( 'Logout', 'royal-storage' ); ?>
					</a>
				</div>
			</div>

			<div class="portal-nav">
				<a href="<?php echo esc_url( add_query_arg( 'tab', 'dashboard' ) ); ?>" class="nav-item <?php echo 'dashboard' === $tab ? 'active' : ''; ?>">
					<?php esc_html_e( 'Dashboard', 'royal-storage' ); ?>
				</a>
				<a href="<?php echo esc_url( add_query_arg( 'tab', 'bookings' ) ); ?>" class="nav-item <?php echo 'bookings' === $tab ? 'active' : ''; ?>">
					<?php esc_html_e( 'My Bookings', 'royal-storage' ); ?>
				</a>
				<a href="<?php echo esc_url( add_query_arg( 'tab', 'invoices' ) ); ?>" class="nav-item <?php echo 'invoices' === $tab ? 'active' : ''; ?>">
					<?php esc_html_e( 'Invoices', 'royal-storage' ); ?>
				</a>
				<a href="<?php echo esc_url( add_query_arg( 'tab', 'account' ) ); ?>" class="nav-item <?php echo 'account' === $tab ? 'active' : ''; ?>">
					<?php esc_html_e( 'Account', 'royal-storage' ); ?>
				</a>
			</div>

			<div class="portal-content">
				<?php
				switch ( $tab ) {
					case 'bookings':
						$this->render_bookings_tab();
						break;
					case 'invoices':
						$this->render_invoices_tab();
						break;
					case 'account':
						$this->render_account_tab();
						break;
					default:
						$this->render_dashboard_tab();
				}
				?>
			</div>
		</div>
		<?php
		return ob_get_clean();
	}

	/**
	 * Render dashboard tab
	 *
	 * @return void
	 */
	private function render_dashboard_tab() {
		$current_user = wp_get_current_user();
		$bookings = new Bookings();
		$stats = $bookings->get_customer_stats( $current_user->ID );
		
		// Add missing stats for the dashboard
		$stats->unpaid_invoices = $this->get_customer_unpaid_invoices_count( $current_user->ID );
		$stats->unpaid_amount = $this->get_customer_unpaid_amount( $current_user->ID );

		include ROYAL_STORAGE_DIR . 'templates/frontend/portal-dashboard.php';
	}

	/**
	 * Render bookings tab
	 *
	 * @return void
	 */
	private function render_bookings_tab() {
		$current_user = wp_get_current_user();
		$bookings = new Bookings();
		$customer_bookings = $bookings->get_customer_bookings( $current_user->ID );

		include ROYAL_STORAGE_DIR . 'templates/frontend/portal-bookings.php';
	}

	/**
	 * Render invoices tab
	 *
	 * @return void
	 */
	private function render_invoices_tab() {
		$current_user = wp_get_current_user();
		$invoices = new Invoices();
		$customer_invoices = $invoices->get_customer_invoices( $current_user->ID );

		include ROYAL_STORAGE_DIR . 'templates/frontend/portal-invoices.php';
	}

	/**
	 * Render account tab
	 *
	 * @return void
	 */
	private function render_account_tab() {
		$current_user = wp_get_current_user();

		include ROYAL_STORAGE_DIR . 'templates/frontend/portal-account.php';
	}

	/**
	 * Render login shortcode
	 *
	 * @return string
	 */
	public function render_login() {
		if ( is_user_logged_in() ) {
			return '';
		}

		ob_start();
		?>
		<div class="royal-storage-login">
			<div class="login-container">
				<h2><?php esc_html_e( 'Login to Your Account', 'royal-storage' ); ?></h2>
				<?php wp_login_form( array( 'redirect' => home_url( '/portal/' ) ) ); ?>
				<p class="login-register">
					<?php esc_html_e( "Don't have an account?", 'royal-storage' ); ?>
					<a href="<?php echo esc_url( wp_registration_url() ); ?>">
						<?php esc_html_e( 'Register here', 'royal-storage' ); ?>
					</a>
				</p>
			</div>
		</div>
		<?php
		return ob_get_clean();
	}

	/**
	 * Get customer bookings
	 *
	 * @param int $customer_id Customer ID.
	 * @return array
	 */
	public function get_customer_bookings( $customer_id ) {
		global $wpdb;

		$bookings_table = $wpdb->prefix . 'royal_bookings';

		return $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM $bookings_table WHERE customer_id = %d ORDER BY created_at DESC",
				$customer_id
			)
		);
	}

	/**
	 * Get customer active bookings
	 *
	 * @param int $customer_id Customer ID.
	 * @return array
	 */
	public function get_customer_active_bookings( $customer_id ) {
		global $wpdb;

		$bookings_table = $wpdb->prefix . 'royal_bookings';

		return $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM $bookings_table WHERE customer_id = %d AND status IN ('confirmed', 'active') ORDER BY start_date ASC",
				$customer_id
			)
		);
	}

	/**
	 * Renew booking
	 *
	 * @param int $booking_id Booking ID.
	 * @param int $days Number of days to renew.
	 * @return bool
	 */
	public function renew_booking( $booking_id, $days ) {
		global $wpdb;

		$bookings_table = $wpdb->prefix . 'royal_bookings';

		$booking = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM $bookings_table WHERE id = %d",
				$booking_id
			)
		);

		if ( ! $booking ) {
			return false;
		}

		$new_end_date = date( 'Y-m-d', strtotime( $booking->end_date . ' +' . $days . ' days' ) );

		return $wpdb->update(
			$bookings_table,
			array( 'end_date' => $new_end_date ),
			array( 'id' => $booking_id ),
			array( '%s' ),
			array( '%d' )
		);
	}

	/**
	 * Handle renew booking AJAX
	 *
	 * @return void
	 */
	public function handle_renew_booking() {
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'royal_storage_portal' ) ) {
			wp_send_json_error( array( 'message' => __( 'Security check failed', 'royal-storage' ) ) );
		}

		if ( ! is_user_logged_in() ) {
			wp_send_json_error( array( 'message' => __( 'Not logged in', 'royal-storage' ) ) );
		}

		$booking_id = isset( $_POST['booking_id'] ) ? intval( $_POST['booking_id'] ) : 0;
		$days = isset( $_POST['days'] ) ? intval( $_POST['days'] ) : 30;

		if ( $this->renew_booking( $booking_id, $days ) ) {
			wp_send_json_success( array( 'message' => __( 'Booking renewed successfully', 'royal-storage' ) ) );
		} else {
			wp_send_json_error( array( 'message' => __( 'Failed to renew booking', 'royal-storage' ) ) );
		}
	}

	/**
	 * Handle cancel booking AJAX
	 *
	 * @return void
	 */
	public function handle_cancel_booking() {
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'royal_storage_portal' ) ) {
			wp_send_json_error( array( 'message' => __( 'Security check failed', 'royal-storage' ) ) );
		}

		if ( ! is_user_logged_in() ) {
			wp_send_json_error( array( 'message' => __( 'Not logged in', 'royal-storage' ) ) );
		}

		$booking_id = isset( $_POST['booking_id'] ) ? intval( $_POST['booking_id'] ) : 0;

		if ( $this->cancel_booking( $booking_id ) ) {
			wp_send_json_success( array( 'message' => __( 'Booking cancelled successfully', 'royal-storage' ) ) );
		} else {
			wp_send_json_error( array( 'message' => __( 'Failed to cancel booking', 'royal-storage' ) ) );
		}
	}

	/**
	 * Cancel booking
	 *
	 * @param int $booking_id Booking ID.
	 * @return bool
	 */
	public function cancel_booking( $booking_id ) {
		global $wpdb;

		$bookings_table = $wpdb->prefix . 'royal_bookings';

		return $wpdb->update(
			$bookings_table,
			array( 'status' => 'cancelled' ),
			array( 'id' => $booking_id ),
			array( '%s' ),
			array( '%d' )
		);
	}

	/**
	 * Check if user is customer
	 *
	 * @param int $user_id User ID.
	 * @return bool
	 */
	public static function is_customer( $user_id ) {
		$user = get_user_by( 'id', $user_id );
		return $user && in_array( 'customer', $user->roles, true );
	}

	/**
	 * Get customer unpaid invoices count
	 *
	 * @param int $customer_id Customer ID.
	 * @return int
	 */
	private function get_customer_unpaid_invoices_count( $customer_id ) {
		global $wpdb;

		$invoices_table = $wpdb->prefix . 'royal_invoices';
		$bookings_table = $wpdb->prefix . 'royal_bookings';

		return (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM $invoices_table i 
				JOIN $bookings_table b ON i.booking_id = b.id 
				WHERE b.customer_id = %d AND i.status = 'unpaid'",
				$customer_id
			)
		);
	}

	/**
	 * Get customer unpaid amount
	 *
	 * @param int $customer_id Customer ID.
	 * @return float
	 */
	private function get_customer_unpaid_amount( $customer_id ) {
		global $wpdb;

		$invoices_table = $wpdb->prefix . 'royal_invoices';
		$bookings_table = $wpdb->prefix . 'royal_bookings';

		$result = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT SUM(i.amount) FROM $invoices_table i 
				JOIN $bookings_table b ON i.booking_id = b.id 
				WHERE b.customer_id = %d AND i.status = 'unpaid'",
				$customer_id
			)
		);

		return $result ? floatval( $result ) : 0;
	}
}

