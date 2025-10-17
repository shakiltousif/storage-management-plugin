<?php
/**
 * Post Types Class
 *
 * @package RoyalStorage
 * @since 1.0.0
 */

namespace RoyalStorage;

/**
 * Post Types class for registering custom post types
 */
class PostTypes {

	/**
	 * Constructor
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'register_post_types' ) );
		add_action( 'init', array( $this, 'register_taxonomies' ) );
	}

	/**
	 * Register custom post types
	 *
	 * @return void
	 */
	public function register_post_types() {
		// Storage Unit CPT.
		register_post_type(
			'rs_storage_unit',
			array(
				'label'               => __( 'Storage Units', 'royal-storage' ),
				'description'         => __( 'Storage unit rental units', 'royal-storage' ),
				'public'              => true,
				'publicly_queryable'  => true,
				'show_ui'             => true,
				'show_in_menu'        => false,
				'show_in_nav_menus'   => false,
				'show_in_rest'        => true,
				'has_archive'         => true,
				'hierarchical'        => false,
				'exclude_from_search' => false,
				'supports'            => array( 'title', 'editor', 'thumbnail', 'custom-fields' ),
				'menu_icon'           => 'dashicons-archive',
				'rewrite'             => array( 'slug' => 'storage-unit' ),
			)
		);

		// Parking Space CPT.
		register_post_type(
			'rs_parking_space',
			array(
				'label'               => __( 'Parking Spaces', 'royal-storage' ),
				'description'         => __( 'Parking space rental units', 'royal-storage' ),
				'public'              => true,
				'publicly_queryable'  => true,
				'show_ui'             => true,
				'show_in_menu'        => false,
				'show_in_nav_menus'   => false,
				'show_in_rest'        => true,
				'has_archive'         => true,
				'hierarchical'        => false,
				'exclude_from_search' => false,
				'supports'            => array( 'title', 'editor', 'thumbnail', 'custom-fields' ),
				'menu_icon'           => 'dashicons-car',
				'rewrite'             => array( 'slug' => 'parking-space' ),
			)
		);

		// Booking CPT.
		register_post_type(
			'rs_booking',
			array(
				'label'               => __( 'Bookings', 'royal-storage' ),
				'description'         => __( 'Customer bookings', 'royal-storage' ),
				'public'              => false,
				'publicly_queryable'  => false,
				'show_ui'             => true,
				'show_in_menu'        => false,
				'show_in_nav_menus'   => false,
				'show_in_rest'        => true,
				'has_archive'         => false,
				'hierarchical'        => false,
				'supports'            => array( 'title', 'custom-fields' ),
				'menu_icon'           => 'dashicons-calendar-alt',
				'rewrite'             => false,
			)
		);

		// Invoice CPT.
		register_post_type(
			'rs_invoice',
			array(
				'label'               => __( 'Invoices', 'royal-storage' ),
				'description'         => __( 'Customer invoices', 'royal-storage' ),
				'public'              => false,
				'publicly_queryable'  => false,
				'show_ui'             => true,
				'show_in_menu'        => false,
				'show_in_nav_menus'   => false,
				'show_in_rest'        => true,
				'has_archive'         => false,
				'hierarchical'        => false,
				'supports'            => array( 'title', 'custom-fields' ),
				'menu_icon'           => 'dashicons-media-document',
				'rewrite'             => false,
			)
		);
	}

	/**
	 * Register custom taxonomies
	 *
	 * @return void
	 */
	public function register_taxonomies() {
		// Unit Size taxonomy.
		register_taxonomy(
			'rs_unit_size',
			array( 'rs_storage_unit' ),
			array(
				'label'              => __( 'Unit Size', 'royal-storage' ),
				'public'             => true,
				'publicly_queryable' => true,
				'show_ui'            => true,
				'show_in_menu'       => true,
				'show_in_rest'       => true,
				'hierarchical'       => false,
				'rewrite'            => array( 'slug' => 'unit-size' ),
			)
		);

		// Booking Status taxonomy.
		register_taxonomy(
			'rs_booking_status',
			array( 'rs_booking' ),
			array(
				'label'              => __( 'Booking Status', 'royal-storage' ),
				'public'             => false,
				'publicly_queryable' => false,
				'show_ui'            => true,
				'show_in_menu'       => true,
				'show_in_rest'       => true,
				'hierarchical'       => false,
				'rewrite'            => false,
			)
		);

		// Payment Status taxonomy.
		register_taxonomy(
			'rs_payment_status',
			array( 'rs_booking', 'rs_invoice' ),
			array(
				'label'              => __( 'Payment Status', 'royal-storage' ),
				'public'             => false,
				'publicly_queryable' => false,
				'show_ui'            => true,
				'show_in_menu'       => true,
				'show_in_rest'       => true,
				'hierarchical'       => false,
				'rewrite'            => false,
			)
		);

		// Insert default terms.
		$this->insert_default_terms();
	}

	/**
	 * Insert default taxonomy terms
	 *
	 * @return void
	 */
	private function insert_default_terms() {
		// Unit sizes.
		$sizes = array( 'M', 'L', 'XL' );
		foreach ( $sizes as $size ) {
			if ( ! term_exists( $size, 'rs_unit_size' ) ) {
				wp_insert_term( $size, 'rs_unit_size' );
			}
		}

		// Booking statuses.
		$booking_statuses = array( 'pending', 'confirmed', 'active', 'expired', 'cancelled' );
		foreach ( $booking_statuses as $status ) {
			if ( ! term_exists( $status, 'rs_booking_status' ) ) {
				wp_insert_term( ucfirst( $status ), 'rs_booking_status', array( 'slug' => $status ) );
			}
		}

		// Payment statuses.
		$payment_statuses = array( 'unpaid', 'paid', 'overdue', 'refunded' );
		foreach ( $payment_statuses as $status ) {
			if ( ! term_exists( $status, 'rs_payment_status' ) ) {
				wp_insert_term( ucfirst( $status ), 'rs_payment_status', array( 'slug' => $status ) );
			}
		}
	}
}

