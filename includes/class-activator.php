<?php
/**
 * Activator Class
 *
 * @package RoyalStorage
 * @since 1.0.0
 */

namespace RoyalStorage;

/**
 * Activator class for plugin activation
 */
class Activator {

	/**
	 * Activate the plugin
	 *
	 * @return void
	 */
	public static function activate() {
		// Create database tables.
		self::create_tables();

		// Set default options.
		self::set_default_options();

		// Create required pages with shortcodes.
		self::create_pages();

		// Flush rewrite rules.
		flush_rewrite_rules();

		// Log activation.
		error_log( 'Royal Storage Plugin activated at ' . current_time( 'mysql' ) );
	}

	/**
	 * Create database tables
	 *
	 * @return void
	 */
	private static function create_tables() {
		global $wpdb;

		$charset_collate = $wpdb->get_charset_collate();

		// Storage Units table.
		$storage_units_table = $wpdb->prefix . 'royal_storage_units';
		$sql                  = "CREATE TABLE IF NOT EXISTS $storage_units_table (
			id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			post_id BIGINT(20) UNSIGNED NOT NULL,
			size VARCHAR(20) NOT NULL,
			dimensions VARCHAR(100),
			amenities LONGTEXT,
			base_price DECIMAL(10, 2) NOT NULL,
			status VARCHAR(20) DEFAULT 'available',
			created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
			updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			UNIQUE KEY post_id (post_id),
			KEY status (status)
		) $charset_collate;";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );

		// Parking Spaces table.
		$parking_table = $wpdb->prefix . 'royal_parking_spaces';
		$sql           = "CREATE TABLE IF NOT EXISTS $parking_table (
			id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			post_id BIGINT(20) UNSIGNED NOT NULL,
			spot_number INT(11) NOT NULL,
			height_limit VARCHAR(50),
			base_price DECIMAL(10, 2) NOT NULL,
			status VARCHAR(20) DEFAULT 'available',
			created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
			updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			UNIQUE KEY post_id (post_id),
			UNIQUE KEY spot_number (spot_number),
			KEY status (status)
		) $charset_collate;";

		dbDelta( $sql );

		// Bookings table.
		$bookings_table = $wpdb->prefix . 'royal_bookings';
		$sql            = "CREATE TABLE IF NOT EXISTS $bookings_table (
			id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			post_id BIGINT(20) UNSIGNED NOT NULL,
			customer_id BIGINT(20) UNSIGNED NOT NULL,
			unit_id BIGINT(20) UNSIGNED NOT NULL,
			unit_type VARCHAR(20) NOT NULL,
			start_date DATE NOT NULL,
			end_date DATE NOT NULL,
			total_price DECIMAL(10, 2) NOT NULL,
			vat_amount DECIMAL(10, 2) NOT NULL,
			status VARCHAR(20) DEFAULT 'pending',
			payment_status VARCHAR(20) DEFAULT 'unpaid',
			access_code VARCHAR(50),
			created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
			updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			UNIQUE KEY post_id (post_id),
			KEY customer_id (customer_id),
			KEY unit_id (unit_id),
			KEY status (status),
			KEY payment_status (payment_status),
			KEY start_date (start_date),
			KEY end_date (end_date)
		) $charset_collate;";

		dbDelta( $sql );

		// Invoices table.
		$invoices_table = $wpdb->prefix . 'royal_invoices';
		$sql            = "CREATE TABLE IF NOT EXISTS $invoices_table (
			id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			post_id BIGINT(20) UNSIGNED NOT NULL,
			booking_id BIGINT(20) UNSIGNED NOT NULL,
			invoice_number VARCHAR(50) NOT NULL,
			amount DECIMAL(10, 2) NOT NULL,
			vat_amount DECIMAL(10, 2) NOT NULL,
			status VARCHAR(20) DEFAULT 'draft',
			invoice_type VARCHAR(20) DEFAULT 'invoice',
			created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
			updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			UNIQUE KEY post_id (post_id),
			UNIQUE KEY invoice_number (invoice_number),
			KEY booking_id (booking_id),
			KEY status (status)
		) $charset_collate;";

		dbDelta( $sql );
	}

	/**
	 * Set default options
	 *
	 * @return void
	 */
	private static function set_default_options() {
		// Set default business settings.
		if ( ! get_option( 'royal_storage_business_name' ) ) {
			update_option( 'royal_storage_business_name', 'Royal Storage' );
		}

		if ( ! get_option( 'royal_storage_currency' ) ) {
			update_option( 'royal_storage_currency', 'RSD' );
		}

		if ( ! get_option( 'royal_storage_vat_rate' ) ) {
			update_option( 'royal_storage_vat_rate', 20 );
		}

		if ( ! get_option( 'royal_storage_daily_rate' ) ) {
			update_option( 'royal_storage_daily_rate', 100 );
		}

		if ( ! get_option( 'royal_storage_weekly_rate' ) ) {
			update_option( 'royal_storage_weekly_rate', 600 );
		}

		if ( ! get_option( 'royal_storage_monthly_rate' ) ) {
			update_option( 'royal_storage_monthly_rate', 2000 );
		}

		// Guest checkout settings
		if ( ! get_option( 'royal_storage_guest_checkout' ) ) {
			update_option( 'royal_storage_guest_checkout', 'yes' );
		}

		if ( ! get_option( 'royal_storage_auto_create_account' ) ) {
			update_option( 'royal_storage_auto_create_account', 'yes' );
		}

		if ( ! get_option( 'royal_storage_send_account_credentials' ) ) {
			update_option( 'royal_storage_send_account_credentials', 'yes' );
		}
	}

	/**
	 * Create required pages with shortcodes
	 *
	 * @return void
	 */
	public static function create_pages() {
		$pages = array(
			'booking' => array(
				'title'   => __( 'Book Storage', 'royal-storage' ),
				'slug'    => 'book-storage',
				'content' => '<!-- wp:shortcode -->[royal_storage_booking]<!-- /wp:shortcode -->',
			),
			'portal' => array(
				'title'   => __( 'Customer Portal', 'royal-storage' ),
				'slug'    => 'customer-portal',
				'content' => '<!-- wp:shortcode -->[royal_storage_portal]<!-- /wp:shortcode -->',
			),
			'login' => array(
				'title'   => __( 'Login', 'royal-storage' ),
				'slug'    => 'storage-login',
				'content' => '<!-- wp:shortcode -->[royal_storage_login]<!-- /wp:shortcode -->',
			),
			'checkout' => array(
				'title'   => __( 'Checkout', 'royal-storage' ),
				'slug'    => 'checkout',
				'content' => '<!-- wp:shortcode -->[royal_storage_checkout]<!-- /wp:shortcode -->',
			),
		);

		foreach ( $pages as $key => $page ) {
			$option_name = 'royal_storage_' . $key . '_page_id';
			$page_id = get_option( $option_name );

			// Check if page already exists
			if ( $page_id ) {
				$existing_page = get_post( $page_id );
				if ( $existing_page && 'page' === $existing_page->post_type && 'trash' !== $existing_page->post_status ) {
					continue; // Page already exists and is valid
				}
			}

			// Check if page with same slug exists
			$existing_page = get_page_by_path( $page['slug'] );
			if ( $existing_page && 'trash' !== $existing_page->post_status ) {
				update_option( $option_name, $existing_page->ID );
				continue;
			}

			// Create new page
			$page_data = array(
				'post_title'    => $page['title'],
				'post_name'     => $page['slug'],
				'post_content'  => $page['content'],
				'post_status'   => 'publish',
				'post_type'     => 'page',
				'post_author'   => 1,
				'comment_status' => 'closed',
			);

			$page_id = wp_insert_post( $page_data );

			if ( $page_id && ! is_wp_error( $page_id ) ) {
				update_option( $option_name, $page_id );
				error_log( sprintf( 'Royal Storage: Created %s page with ID %d', $key, $page_id ) );
			} else {
				error_log( sprintf( 'Royal Storage: Failed to create %s page', $key ) );
			}
		}
	}
}

