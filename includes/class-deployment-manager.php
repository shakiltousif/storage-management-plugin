<?php
/**
 * Deployment Manager Class
 *
 * @package RoyalStorage
 * @since 1.0.0
 */

namespace RoyalStorage;

/**
 * Deployment manager class for deployment and maintenance
 */
class DeploymentManager {

	/**
	 * Get deployment checklist
	 *
	 * @return array
	 */
	public function get_deployment_checklist() {
		return array(
			'database'      => $this->check_database(),
			'tables'        => $this->check_tables(),
			'permissions'   => $this->check_permissions(),
			'dependencies'  => $this->check_dependencies(),
			'configuration' => $this->check_configuration(),
			'security'      => $this->check_security(),
		);
	}

	/**
	 * Check database
	 *
	 * @return array
	 */
	private function check_database() {
		global $wpdb;

		$checks = array(
			'connection' => $wpdb->check_connection(),
			'version'    => $wpdb->db_version(),
		);

		return $checks;
	}

	/**
	 * Check tables
	 *
	 * @return array
	 */
	private function check_tables() {
		global $wpdb;

		$tables = array(
			'royal_bookings',
			'royal_storage_units',
			'royal_parking_spaces',
			'royal_invoices',
			'royal_notifications',
			'royal_subscriptions',
			'royal_events',
			'royal_security_logs',
		);

		$results = array();

		foreach ( $tables as $table ) {
			$table_name = $wpdb->prefix . $table;
			$exists = $wpdb->get_var(
				$wpdb->prepare(
					"SELECT 1 FROM information_schema.TABLES WHERE TABLE_SCHEMA = %s AND TABLE_NAME = %s",
					DB_NAME,
					$table_name
				)
			);

			$results[ $table ] = ! empty( $exists );
		}

		return $results;
	}

	/**
	 * Check permissions
	 *
	 * @return array
	 */
	private function check_permissions() {
		$plugin_dir = WP_PLUGIN_DIR . '/royal-storage';

		return array(
			'plugin_dir_readable'  => is_readable( $plugin_dir ),
			'plugin_dir_writable'  => is_writable( $plugin_dir ),
			'uploads_dir_writable' => is_writable( WP_CONTENT_DIR . '/uploads' ),
		);
	}

	/**
	 * Check dependencies
	 *
	 * @return array
	 */
	private function check_dependencies() {
		return array(
			'wordpress_version' => get_bloginfo( 'version' ),
			'php_version'       => phpversion(),
			'woocommerce'       => class_exists( 'WooCommerce' ),
			'curl'              => extension_loaded( 'curl' ),
			'json'              => extension_loaded( 'json' ),
		);
	}

	/**
	 * Check configuration
	 *
	 * @return array
	 */
	private function check_configuration() {
		return array(
			'debug_mode'        => defined( 'WP_DEBUG' ) && WP_DEBUG,
			'debug_log'         => defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG,
			'file_edit_disabled' => defined( 'DISALLOW_FILE_EDIT' ) && DISALLOW_FILE_EDIT,
			'file_mods_disabled' => defined( 'DISALLOW_FILE_MODS' ) && DISALLOW_FILE_MODS,
		);
	}

	/**
	 * Check security
	 *
	 * @return array
	 */
	private function check_security() {
		$security_manager = new SecurityManager();
		$report = $security_manager->get_security_report();

		return array(
			'failed_logins'  => $report->failed_logins,
			'role_changes'   => $report->role_changes,
			'plugin_changes' => $report->plugin_changes,
		);
	}

	/**
	 * Get system health
	 *
	 * @return object
	 */
	public function get_system_health() {
		$checklist = $this->get_deployment_checklist();

		$all_passed = true;

		foreach ( $checklist as $category => $checks ) {
			if ( is_array( $checks ) ) {
				foreach ( $checks as $check => $result ) {
					if ( is_bool( $result ) && ! $result ) {
						$all_passed = false;
					}
				}
			}
		}

		return (object) array(
			'status'    => $all_passed ? 'healthy' : 'warning',
			'checklist' => $checklist,
		);
	}

	/**
	 * Create backup
	 *
	 * @return string|false
	 */
	public function create_backup() {
		global $wpdb;

		$backup_dir = WP_CONTENT_DIR . '/backups';

		if ( ! is_dir( $backup_dir ) ) {
			wp_mkdir_p( $backup_dir );
		}

		$backup_file = $backup_dir . '/royal-storage-backup-' . date( 'Y-m-d-H-i-s' ) . '.sql';

		$tables = array(
			$wpdb->prefix . 'royal_bookings',
			$wpdb->prefix . 'royal_storage_units',
			$wpdb->prefix . 'royal_parking_spaces',
			$wpdb->prefix . 'royal_invoices',
			$wpdb->prefix . 'royal_notifications',
			$wpdb->prefix . 'royal_subscriptions',
			$wpdb->prefix . 'royal_events',
			$wpdb->prefix . 'royal_security_logs',
		);

		$backup_content = '';

		foreach ( $tables as $table ) {
			$backup_content .= "-- Table: $table\n";
			$backup_content .= $wpdb->get_var( "SHOW CREATE TABLE $table" ) . ";\n\n";

			$rows = $wpdb->get_results( "SELECT * FROM $table" );
			foreach ( $rows as $row ) {
				$backup_content .= "INSERT INTO $table VALUES (" . implode( ', ', array_map( array( $wpdb, 'prepare' ), array_fill( 0, count( (array) $row ), '%s' ), (array) $row ) ) . ");\n";
			}

			$backup_content .= "\n";
		}

		if ( file_put_contents( $backup_file, $backup_content ) ) {
			return $backup_file;
		}

		return false;
	}

	/**
	 * Get backup files
	 *
	 * @return array
	 */
	public function get_backup_files() {
		$backup_dir = WP_CONTENT_DIR . '/backups';

		if ( ! is_dir( $backup_dir ) ) {
			return array();
		}

		$files = scandir( $backup_dir );
		$backups = array();

		foreach ( $files as $file ) {
			if ( strpos( $file, 'royal-storage-backup' ) === 0 ) {
				$backups[] = array(
					'name' => $file,
					'path' => $backup_dir . '/' . $file,
					'size' => filesize( $backup_dir . '/' . $file ),
					'date' => filemtime( $backup_dir . '/' . $file ),
				);
			}
		}

		return $backups;
	}

	/**
	 * Get deployment status
	 *
	 * @return object
	 */
	public function get_deployment_status() {
		$health = $this->get_system_health();
		$backups = $this->get_backup_files();

		return (object) array(
			'health'  => $health,
			'backups' => $backups,
			'ready'   => $health->status === 'healthy' && count( $backups ) > 0,
		);
	}
}