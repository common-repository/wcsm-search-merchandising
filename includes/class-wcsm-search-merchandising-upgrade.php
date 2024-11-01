<?php

if ( !defined( 'ABSPATH' ) ) {
	exit;
}

if ( !class_exists( 'WCSM_Search_Merchandising_Upgrade' ) ) {

	class WCSM_Search_Merchandising_Upgrade {

		public function __construct() {

			add_action( 'plugins_loaded', array( $this, 'upgrade' ) );

		}

		public static function upgrade() {

			$version = get_option( 'wcsm_search_merchandising_version' );

			// If version defined in options is not the latest version or is empty as a new install or version older than option

			if ( WCSM_SEARCH_MERCHANDISING_VERSION !== $version ) {

				global $wpdb;

				// Specific conditions for changes in versions e.g. deleting an option that is no longer required, in order oldest to newest as an option could be removed then re-added in later version

				if ( $version < '1.0.0' ) { // If below version x (if version empty this still triggers)

					// 1.0.0 - Populate options (=== to check if option exists)

					if ( get_option( 'wcsm_search_merchandising_rows_per_page' ) === false ) {

						update_option( 'wcsm_search_merchandising_rows_per_page', '20' );

					}

					if ( get_option( 'wcsm_search_merchandising_disable_tracking_user_roles' ) === false ) {

						update_option( 'wcsm_search_merchandising_disable_tracking_user_roles', '' );

					}

					if ( get_option( 'wcsm_search_merchandising_delete_data_on_uninstall' ) === false ) {

						update_option( 'wcsm_search_merchandising_delete_data_on_uninstall', 'no' );

					}

					// 1.0.0 - Create search terms table

					$wpdb->query("
						CREATE TABLE IF NOT EXISTS {$wpdb->prefix}wcsm_search_merchandising_search_terms (
						search_term_id bigint(20) NOT NULL AUTO_INCREMENT,
						search_term longtext NOT NULL,
						search_term_searches bigint(20) NOT NULL,
						search_term_results_average float NOT NULL,
						search_term_content longtext NOT NULL,
						search_term_boosts longtext NOT NULL,
						search_term_redirect longtext NOT NULL,
						PRIMARY KEY (search_term_id)
					)");

					// 1.0.0 - Create search terms date table

					$wpdb->query("
						CREATE TABLE IF NOT EXISTS {$wpdb->prefix}wcsm_search_merchandising_search_terms_date (
						search_term_date_id bigint(20) NOT NULL AUTO_INCREMENT,
						search_term_date date NOT NULL default '0000-00-00',
						search_term_id bigint(20) NOT NULL,
						search_term_searches bigint(20) NOT NULL,
						search_term_results_average float NOT NULL,
						PRIMARY KEY (search_term_date_id)
					)");

					// 1.0.0 - Create search products table

					$wpdb->query("
						CREATE TABLE IF NOT EXISTS {$wpdb->prefix}wcsm_search_merchandising_search_products (
						search_product_id bigint(20) NOT NULL AUTO_INCREMENT,
						search_product_clicks bigint(20) NOT NULL,
						search_term_id bigint(20) NOT NULL,
						product_id bigint(20) NOT NULL,
						PRIMARY KEY (search_product_id)
					)");

					// 1.0.0 - Create search products date table

					$wpdb->query("
						CREATE TABLE IF NOT EXISTS {$wpdb->prefix}wcsm_search_merchandising_search_products_date (
						search_product_date_id bigint(20) NOT NULL AUTO_INCREMENT,
						search_product_date date NOT NULL default '0000-00-00',
						search_product_clicks bigint(20) NOT NULL,
						search_term_id bigint(20) NOT NULL,
						product_id bigint(20) NOT NULL,
						PRIMARY KEY (search_product_date_id)
					)");

				}

				// Update version number

				update_option( 'wcsm_search_merchandising_version', WCSM_SEARCH_MERCHANDISING_VERSION );

			}

		}

	}

}
