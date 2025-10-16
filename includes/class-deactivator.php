<?php
/**
 * Deactivator Class
 *
 * @package RoyalStorage
 * @since 1.0.0
 */

namespace RoyalStorage;

/**
 * Deactivator class for plugin deactivation
 */
class Deactivator {

	/**
	 * Deactivate the plugin
	 *
	 * @return void
	 */
	public static function deactivate() {
		// Flush rewrite rules.
		flush_rewrite_rules();

		// Clear scheduled events.
		self::clear_scheduled_events();

		// Log deactivation.
		error_log( 'Royal Storage Plugin deactivated at ' . current_time( 'mysql' ) );
	}

	/**
	 * Clear scheduled events
	 *
	 * @return void
	 */
	private static function clear_scheduled_events() {
		// Clear any scheduled cron jobs.
		wp_clear_scheduled_hook( 'royal_storage_send_expiry_reminders' );
		wp_clear_scheduled_hook( 'royal_storage_send_overdue_reminders' );
		wp_clear_scheduled_hook( 'royal_storage_process_email_queue' );
	}
}

