<?php

if ( !defined( 'ABSPATH' ) ) {
	exit;
}

if ( !class_exists( 'WCSM_Search_Merchandising_Boosts' ) ) {

	class WCSM_Search_Merchandising_Boosts {

		public function __construct() {

			add_action( 'pre_get_posts', array( $this, 'query' ), 99999 );

		}

		public function query( $query ) {

			if ( !is_admin() && $query->is_main_query() && $query->is_search() ) {

				global $wpdb;
				$search_term = $query->get( 's' );
				$post_type = $query->get( 'post_type' );
				$boosts = $wpdb->get_results(
					$wpdb->prepare(
						"SELECT search_term_boosts FROM {$wpdb->prefix}wcsm_search_merchandising_search_terms WHERE search_term = %s;",
						$search_term
					)
				);
				$boosts = ( '' !== $boosts[0]->search_term_boosts ? unserialize( $boosts[0]->search_term_boosts ) : array() );

				$boosted = new WP_Query(
					array(
						's'					=> $search_term,
						'post_type'			=> $post_type,
						'post__in'			=> $boosts,
						'posts_per_page'	=> -1,
						'fields'			=> 'ids',
					)
				);
				
				$boosted_ids = $boosted->posts;

				$non_boosted = new WP_Query(
					array(
						's'					=> $search_term,
						'post_type'			=> $post_type,
						'post__not_in'		=> $boosted_ids,
						'posts_per_page'	=> -1,
						'fields'			=> 'ids',
					)
				);
				
				$non_boosted_ids = $non_boosted->posts;

				$ordered_ids = array_merge( $boosted_ids, $non_boosted_ids );
				$query->set( 'post__in', $ordered_ids );
				$query->set( 'orderby', 'post__in' ); // Orders by the post__in, if sorting used on search page this will not be used

			}

		}

	}

}
