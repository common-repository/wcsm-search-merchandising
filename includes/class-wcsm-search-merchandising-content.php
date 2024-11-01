<?php

if ( !defined( 'ABSPATH' ) ) {
	exit;
}

if ( !class_exists( 'WCSM_Search_Merchandising_Content' ) ) {

	class WCSM_Search_Merchandising_Content {

		public function __construct() {

			add_action( 'init', array( $this, 'post_type' ), 0 );
			add_action( 'woocommerce_before_shop_loop', array( $this, 'before' ) );
			add_action( 'woocommerce_after_shop_loop', array( $this, 'after' ), 20 ); // 20 so after pagination at 10
			add_action( 'woocommerce_no_products_found', array( $this, 'before' ), 9 );
			add_action( 'woocommerce_no_products_found', array( $this, 'after' ), 11 );

		}

		public function post_type() {

			$labels = array(
				'name'                  	=> _x( 'Search Merchandising Content', 'Post Type General Name', 'wpsm-search-merchandising' ),
				'singular_name'         	=> _x( 'Search Merchandising Content', 'Post Type Singular Name', 'wpsm-search-merchandising' ),
				'menu_name'             	=> __( 'Search Merchandising Content', 'wpsm-search-merchandising' ),
				'name_admin_bar'        	=> __( 'Search Merchandising Content', 'wpsm-search-merchandising' ),
				'archives'              	=> __( 'Search Merchandising Content Archives', 'wpsm-search-merchandising' ),
				'attributes'            	=> __( 'Search Merchandising Content Attributes', 'wpsm-search-merchandising' ),
				'parent_item_colon'     	=> __( 'Parent Search Merchandising Content:', 'wpsm-search-merchandising' ),
				'all_items'             	=> __( 'All Search Merchandising Content', 'wpsm-search-merchandising' ),
				'add_new_item'          	=> __( 'Add New Search Merchandising Content', 'wpsm-search-merchandising' ),
				'add_new'               	=> __( 'Add New', 'wpsm-search-merchandising' ),
				'new_item'              	=> __( 'New Search Merchandising Content', 'wpsm-search-merchandising' ),
				'edit_item'             	=> __( 'Edit Search Merchandising Content', 'wpsm-search-merchandising' ),
				'update_item'           	=> __( 'Update Search Merchandising Content', 'wpsm-search-merchandising' ),
				'view_item'             	=> __( 'View Search Merchandising Content', 'wpsm-search-merchandising' ),
				'view_items'            	=> __( 'View Search Merchandising Content', 'wpsm-search-merchandising' ),
				'search_items'          	=> __( 'Search Search Merchandising Content', 'wpsm-search-merchandising' ),
				'not_found'             	=> __( 'Not found', 'wpsm-search-merchandising' ),
				'not_found_in_trash'    	=> __( 'Not found in Trash', 'wpsm-search-merchandising' ),
				'featured_image'        	=> __( 'Featured Image', 'wpsm-search-merchandising' ),
				'set_featured_image'    	=> __( 'Set featured image', 'wpsm-search-merchandising' ),
				'remove_featured_image' 	=> __( 'Remove featured image', 'wpsm-search-merchandising' ),
				'use_featured_image'    	=> __( 'Use as featured image', 'wpsm-search-merchandising' ),
				'insert_into_item'      	=> __( 'Insert into Search Merchandising Content', 'wpsm-search-merchandising' ),
				'uploaded_to_this_item' 	=> __( 'Uploaded to this Search Merchandising Content', 'wpsm-search-merchandising' ),
				'items_list'            	=> __( 'Search Merchandising Content list', 'wpsm-search-merchandising' ),
				'items_list_navigation' 	=> __( 'Search Merchandising Content list navigation', 'wpsm-search-merchandising' ),
				'filter_items_list'     	=> __( 'Filter Search Merchandising Content list', 'wpsm-search-merchandising' ),
				'item_published'			=> __( 'Search Merchandising Content published', 'wpsm-search-merchandising' ),
				'item_published_privately'	=> __( 'Search Merchandising Content published privately', 'wpsm-search-merchandising' ),
				'item_reverted_to_draft'	=> __( 'Search Merchandising Content reverted to draft', 'wpsm-search-merchandising' ),
				'item_scheduled'			=> __( 'Search Merchandising Content scheduled', 'wpsm-search-merchandising' ),
				'item_updated'				=> __( 'Search Merchandising Content updated', 'wpsm-search-merchandising' ),
			);
			$args = array(
				'label'                 => __( 'Search Merchandising Content', 'wpsm-search-merchandising' ),
				'description'           => __( 'Post Type Description', 'wpsm-search-merchandising' ),
				'labels'                => $labels,
				'supports'              => array( 'title', 'editor' ),
				'hierarchical'          => false,
				'public'                => false,
				'show_ui'               => true,
				'show_in_menu'          => false,
				'menu_position'         => 40,
				'menu_icon'             => 'dashicons-search',
				'show_in_admin_bar'     => false,
				'show_in_nav_menus'     => false,
				'can_export'            => true,
				'has_archive'           => false,
				'exclude_from_search'   => true,
				'publicly_queryable'    => false,
				'rewrite'               => false,
				'show_in_rest'          => true,
			);
			register_post_type( 'wcsm_content', $args );

		}

		public function before() {

			if ( isset( $_GET['post_type'] ) ) {

				if ( is_search() && 'product' == $_GET['post_type'] ) { // $_GET['post_type'] is used rather than get_post_type as get_post_type not available if no results as it's a no search results page so post type not set

					if ( isset( $_GET['s'] ) ) {

						$this->display( sanitize_text_field( $_GET['s'] ), 'before' );

					}

				}

			}

		}

		public function after() {

			if ( isset( $_GET['post_type'] ) ) {

				if ( is_search() && 'product' == $_GET['post_type'] ) { // $_GET['post_type'] is used rather than get_post_type as get_post_type not available if no results as it's a no search results page so post type not set

					if ( isset( $_GET['s'] ) ) {

						$this->display( sanitize_text_field( $_GET['s'] ), 'after' );

					}

				}

			}

		}

		public function display( $search_term, $type ) {

			if ( !empty( $type ) ) {

				global $wpdb;

				$search_term_content = $wpdb->get_results(
					$wpdb->prepare(
						"SELECT search_term_content FROM `{$wpdb->prefix}wcsm_search_merchandising_search_terms` WHERE `search_term` = %s;",
						$search_term
					)
				);

				if ( '' !== $search_term_content[0]->search_term_content ) {

					$search_term_content = unserialize( $search_term_content[0]->search_term_content );

					if ( 'before' == $type ) {

						if ( 'publish' == get_post_status( $search_term_content['before'] ) ) {

							echo wp_kses_post( apply_filters( 'the_content', get_post_field( 'post_content', $search_term_content['before'] ) ) );

						}

					} elseif ( 'after' == $type ) {

						if ( 'publish' == get_post_status( $search_term_content['after'] ) ) {

							echo wp_kses_post( apply_filters( 'the_content', get_post_field( 'post_content', $search_term_content['after'] ) ) );

						}

					}

				}

			}

		}

	}

}
