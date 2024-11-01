<?php

if ( !defined( 'ABSPATH' ) ) {
	exit;
}

if ( !class_exists( 'WCSM_Search_Merchandising_Redirect' ) ) {

	class WCSM_Search_Merchandising_Redirect {

		public function __construct() {

			add_action( 'wp', array( $this, 'redirect' ) );

		}

		public function redirect() {

			if ( !is_admin() ) {

				if ( isset( $_GET['post_type'] ) && isset( $_GET['s'] ) ) {

					if ( is_search() && 'product' == $_GET['post_type'] ) { // $_GET['post_type'] is used rather than get_post_type as get_post_type not available if no results as it's a no search results page so post type not set

						global $wpdb;

						$redirect = $wpdb->get_results(
							$wpdb->prepare(
								"SELECT search_term_redirect FROM `{$wpdb->prefix}wcsm_search_merchandising_search_terms` WHERE `search_term` = %s;",
								sanitize_text_field( $_GET['s'] )
							)
						);

						if ( !empty( $redirect ) ) {

							if ( isset( $redirect[0] ) ) {

								if ( !empty( $redirect[0]->search_term_redirect ) ) {

									WCSM_Search_Merchandising_Search::track_search_term();
									wp_redirect( $redirect[0]->search_term_redirect, 301 );
									exit;

								}

							}

						}

					}

				}

			}

		}

	}

}
