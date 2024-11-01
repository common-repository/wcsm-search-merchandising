<?php

if ( !defined( 'ABSPATH' ) ) {
	exit;
}

if ( !class_exists( 'WCSM_Search_Merchandising_Search' ) ) {

	class WCSM_Search_Merchandising_Search {

		public function __construct() {

			add_action( 'wp', array( $this, 'track_search_term' ) );
			add_action( 'wp', array( $this, 'track_search_product' ) );
			add_filter( 'woocommerce_redirect_single_search_result', '__return_false' ); // Done to ensure single results are recorded rather than redirecting to product page and missing record

		}

		public static function track_search_term() {

			if ( !is_admin() && self::disable_tracking() == false ) {

				if ( isset( $_GET['post_type'] ) && isset( $_GET['s'] ) ) {

					if ( is_search() && 'product' == $_GET['post_type'] ) { // $_GET['post_type'] is used rather than get_post_type as get_post_type not available if no results as it's a no search results page so post type not set
							
						if ( !is_paged() ) {

							// Globals

							global $wp_query;
							global $wpdb;

							// Get search term and results searches

							$search_term = sanitize_text_field( $_GET['s'] );
							$searches = (float) $wp_query->found_posts;

							// Look for search term

							$search_terms = $wpdb->get_results(
								$wpdb->prepare(
									"SELECT * FROM `{$wpdb->prefix}wcsm_search_merchandising_search_terms` WHERE `search_term` = %s;",
									$search_term
								)
							);

							// If search term not recorded

							if ( empty( $search_terms ) ) {

								// Insert into search terms

								$wpdb->query(
									$wpdb->prepare(
										"INSERT INTO `{$wpdb->prefix}wcsm_search_merchandising_search_terms` (`search_term_id`, `search_term`, `search_term_searches`, `search_term_results_average` ) VALUES ( '', %s, %d, %d );",
										$search_term,
										1,
										$searches
									)
								);

								// Insert into search terms date

								$wpdb->query(
									$wpdb->prepare(
										"INSERT INTO `{$wpdb->prefix}wcsm_search_merchandising_search_terms_date` (`search_term_date_id`, `search_term_date`, `search_term_id`, `search_term_searches`, `search_term_results_average`) VALUES ( '', %s, %d, %d, %f );",
										gmdate( 'Y-m-d' ),
										$wpdb->insert_id,
										1,
										$searches
									)
								);

							} else {

								// Get search term id from search term

								$search_term_id = $search_terms[0]->search_term_id;

								// Get search data

								$search_searches = (int) $search_terms[0]->search_term_searches;
								$search_results_average = (float) $search_terms[0]->search_term_results_average;
								$search_searches_new = $search_searches + 1;

								// Update search terms searches and average

								$wpdb->query(
									$wpdb->prepare(
										"UPDATE `{$wpdb->prefix}wcsm_search_merchandising_search_terms` SET `search_term_searches` = %d, `search_term_results_average` = %f WHERE `search_term_id` = %s;",
										$search_searches_new,
										( ( $search_results_average * $search_searches ) + $searches ) / $search_searches_new,
										$search_term_id
									)
								);

								// Get search terms date data matching date

								$search_terms_date = $wpdb->get_results(
									$wpdb->prepare(
										"SELECT * FROM `{$wpdb->prefix}wcsm_search_merchandising_search_terms_date` WHERE `search_term_date` = %s AND `search_term_id` = %d;",
										gmdate('Y-m-d' ),
										$search_term_id
									)
								);

								// If date data exists for date

								if ( !empty( $search_terms_date ) ) {

									// Update search date searches and average

									$date_search_searches = (int) $search_terms_date[0]->search_term_searches;
									$date_search_results_average = (float) $search_terms_date[0]->search_term_results_average;
									$date_search_searches_new = $date_search_searches + 1;

									$wpdb->query(
										$wpdb->prepare(
											"UPDATE `{$wpdb->prefix}wcsm_search_merchandising_search_terms_date` SET `search_term_searches` = %d, `search_term_results_average` = %f WHERE `search_term_date` = %s AND `search_term_id` = %d;",
											$date_search_searches_new,
											( ( $date_search_results_average * $date_search_searches ) + $searches ) / $date_search_searches_new,
											gmdate('Y-m-d' ),
											$search_term_id
										)
									);

								} else {

									// Insert search date

									$wpdb->query(
										$wpdb->prepare(
											"INSERT INTO `{$wpdb->prefix}wcsm_search_merchandising_search_terms_date` (`search_term_date_id`, `search_term_date`, `search_term_id`, `search_term_searches`, `search_term_results_average`) VALUES ( '', %s, %d, %d, %f );",
											gmdate( 'Y-m-d' ),
											$search_term_id,
											1,
											$searches
										)
									);

								}

							}

						}

					}

				}

			}

		}

		public static function track_search_product() {

			if ( !is_admin() && self::disable_tracking() == false && is_product() ) { // is_product() is important or could track non-products if clicekd from product search page

				$referer = wp_get_referer();

				if ( !empty( $referer ) ) {

					global $wpdb;
					global $post;
					$product_id = $post->ID;
					$referer_query = parse_url( $referer, PHP_URL_QUERY );

					if ( !empty( $referer_query ) ) {

						$referer_vars = explode( '&', $referer_query );

						if ( !empty( $referer_vars ) ) {

							$referer_is_search = false;
							$referer_is_product_search = false;

							foreach ( $referer_vars as $referer_var ) {

								// If starts with s= (we check if starts as there could be shirts=1 which would trigger s=1 if not checking starts with)

								if ( 's=' === substr( $referer_var, 0, strlen( 's=' ) ) ) {

									$referer_is_search = true;
									$search_term = str_replace( 's=', '', $referer_var );

								} elseif ( 'post_type=product' === substr( $referer_var, 0, strlen( 'post_type=product' ) ) ) {

									$referer_is_product_search = true;

								}

							}

							if ( true == $referer_is_search && true == $referer_is_product_search ) {

								if ( !empty( $search_term ) ) {

									$search_terms = $wpdb->get_results(
										$wpdb->prepare(
											"SELECT * FROM `{$wpdb->prefix}wcsm_search_merchandising_search_terms` WHERE `search_term` = %s;",
											$search_term
										)
									);

									if ( !empty( $search_terms ) ) {

										$search_term_id = $search_terms[0]->search_term_id;

										$search_products = $wpdb->get_results(
											$wpdb->prepare(
												"SELECT * FROM `{$wpdb->prefix}wcsm_search_merchandising_search_products` WHERE `search_term_id` = %d AND `product_id` = %d;",
												$search_term_id,
												$product_id
											)
										);

										if ( empty( $search_products ) ) {

											$wpdb->query(
												$wpdb->prepare(
													"INSERT INTO `{$wpdb->prefix}wcsm_search_merchandising_search_products` (`search_product_id`, `search_product_clicks`, `search_term_id`, `product_id`) VALUES ( '', %d, %d, %d );",
													1,
													$search_term_id,
													$product_id
												)
											);

											$wpdb->query(
												$wpdb->prepare(
													"INSERT INTO `{$wpdb->prefix}wcsm_search_merchandising_search_products_date` (`search_product_date_id`, `search_product_date`, `search_product_clicks`, `search_term_id`, `product_id`) VALUES ( '', %s, %d, %d, %d );",
													gmdate( 'Y-m-d' ),
													1,
													$search_term_id,
													$product_id
												)
											);

										} else {

											$wpdb->query(
												$wpdb->prepare(
													"UPDATE `{$wpdb->prefix}wcsm_search_merchandising_search_products` SET `search_product_clicks` = `search_product_clicks` + 1 WHERE `search_term_id` = %d AND `product_id` = %d;",
													$search_term_id,
													$product_id
												)
											);

											// Get search products date data matching date

											$search_products_date = $wpdb->get_results(
												$wpdb->prepare(
													"SELECT * FROM `{$wpdb->prefix}wcsm_search_merchandising_search_products_date` WHERE `search_product_date` = %s AND `search_term_id` = %d AND `product_id` = %d;",
													gmdate('Y-m-d' ),
													$search_term_id,
													$product_id
												)
											);

											if ( !empty( $search_products_date ) ) {

												$wpdb->query(
													$wpdb->prepare(
														"UPDATE `{$wpdb->prefix}wcsm_search_merchandising_search_products_date` SET `search_product_clicks` = `search_product_clicks` + 1 WHERE `search_product_date` = %s AND `search_term_id` = %d AND `product_id` = %d;",
														gmdate('Y-m-d' ),
														$search_term_id,
														$product_id
													)
												);

											} else {

												$wpdb->query(
													$wpdb->prepare(
														"INSERT INTO `{$wpdb->prefix}wcsm_search_merchandising_search_products_date` (`search_product_date_id`, `search_product_date`, `search_product_clicks`, `search_term_id`, `product_id`) VALUES ( '', %s, %d, %d, %d );",
														gmdate( 'Y-m-d' ),
														1,
														$search_term_id,
														$product_id
													)
												);

											}

										}

									}

								}

							}

						}

					}

				}

			}

		}

		public static function disable_tracking() {

			$disable_tracking = false;

			if ( is_user_logged_in() ) {

				$user = wp_get_current_user();
				$user_roles = $user->roles;
				$disable_tracking_user_roles = get_option( 'wcsm_search_merchandising_disable_tracking_user_roles' );
				$disable_tracking_user_roles = ( '' !== $disable_tracking_user_roles ? $disable_tracking_user_roles : array() );

				foreach ( $user_roles as $user_role ) {

					if ( in_array( $user_role, $disable_tracking_user_roles ) ) {

						$disable_tracking = true;
						break;

					}

				}

			}

			return $disable_tracking;

		}

	}

}
