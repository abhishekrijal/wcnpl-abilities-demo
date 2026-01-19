<?php
/**
 * Fired during plugin activation.
 *
 * @package    WCNPL_Abilities_Demo
 * @subpackage WCNPL_Abilities_Demo/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 */
class WCNPL_Abilities_Demo_Activator {

	/**
	 * Activate the plugin.
	 *
	 * Create database tables for forms and submissions.
	 */
	public static function activate() {
		global $wpdb;

		$charset_collate = $wpdb->get_charset_collate();

		// Table for forms.
		$forms_table_name = $wpdb->prefix . 'wcnpl_forms';

		$forms_sql = "CREATE TABLE IF NOT EXISTS $forms_table_name (
			id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			title varchar(255) NOT NULL,
			description text,
			created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
			updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			PRIMARY KEY (id)
		) $charset_collate;";

		// Table for form submissions.
		$submissions_table_name = $wpdb->prefix . 'wcnpl_form_submissions';

		$submissions_sql = "CREATE TABLE IF NOT EXISTS $submissions_table_name (
			id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			form_id bigint(20) UNSIGNED NOT NULL,
			name varchar(255) NOT NULL,
			email varchar(255) NOT NULL,
			message text,
			submitted_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			KEY form_id (form_id)
		) $charset_collate;";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $forms_sql );
		dbDelta( $submissions_sql );

		// Insert a sample form for demonstration.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$existing_form = $wpdb->get_var( "SELECT COUNT(*) FROM `$forms_table_name`" );
		if ( 0 === (int) $existing_form ) {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$wpdb->insert(
				$forms_table_name,
				array(
					'title'       => 'Contact Form',
					'description' => 'A simple contact form for demonstration purposes.',
				),
				array( '%s', '%s' )
			);
		}

		// Store the database version.
		add_option( 'wcnpl_abilities_demo_db_version', WCNPL_ABILITIES_DEMO_VERSION );
	}
}
