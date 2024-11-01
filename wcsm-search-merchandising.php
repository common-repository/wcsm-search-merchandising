<?php

/**
 * Plugin Name: Search Merchandising
 * Plugin URI: https://99w.co.uk
 * Description: Increase product search conversions on your WooCommerce store. Track product searches, add in-search content, boost products and redirect based on search terms
 * Version: 1.0.4
 * Requires at least: 5.0
 * Requires PHP: 7.0
 * Author: 99w
 * Author URI: https://profiles.wordpress.org/ninetyninew/
 * Text Domain: wcsm-search-merchandising
 * Domain Path: /languages
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 *
 */
if ( !defined( 'ABSPATH' ) ) {
    exit;
}
// Freemius auto deactivate free if premium used

if ( function_exists( 'wcsm_search_merchandising_freemius' ) ) {
    wcsm_search_merchandising_freemius()->set_basename( false, __FILE__ );
} else {
    
    if ( !function_exists( 'wcsm_search_merchandising_freemius' ) ) {
        // Freemius integration
        function wcsm_search_merchandising_freemius()
        {
            global  $wcsm_search_merchandising_freemius ;
            
            if ( !isset( $wcsm_search_merchandising_freemius ) ) {
                require_once dirname( __FILE__ ) . '/freemius/start.php';
                $wcsm_search_merchandising_freemius = fs_dynamic_init( array(
                    'id'             => '7434',
                    'slug'           => 'wcsm-search-merchandising',
                    'type'           => 'plugin',
                    'public_key'     => 'pk_cb0c698f30ed2bb3fb98954f65344',
                    'is_premium'     => false,
                    'premium_suffix' => 'Premium',
                    'has_addons'     => false,
                    'has_paid_plans' => true,
                    'menu'           => array(
                    'slug' => 'wcsm-search-merchandising',
                ),
                    'is_live'        => true,
                ) );
            }
            
            return $wcsm_search_merchandising_freemius;
        }
        
        wcsm_search_merchandising_freemius();
        do_action( 'wcsm_search_merchandising_freemius_loaded' );
        // Instantiate
        
        if ( !class_exists( 'WCSM_Search_Merchandising' ) ) {
            define( 'WCSM_SEARCH_MERCHANDISING_VERSION', '1.0.4' );
            load_plugin_textdomain( 'wcsm-search-merchandising', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
            class WCSM_Search_Merchandising
            {
                public function __construct()
                {
                    require_once __DIR__ . '/includes/class-wcsm-search-merchandising-activation.php';
                    require_once __DIR__ . '/includes/class-wcsm-search-merchandising-upgrade.php';
                    new WCSM_Search_Merchandising_Activation();
                    new WCSM_Search_Merchandising_Upgrade();
                    include_once ABSPATH . 'wp-admin/includes/plugin.php';
                    // Ensures is_plugin_active() can be used here
                    
                    if ( is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
                        // If WooCommerce is active, works for standalone and multisite network
                        require_once __DIR__ . '/includes/class-wcsm-search-merchandising-boosts.php';
                        require_once __DIR__ . '/includes/class-wcsm-search-merchandising-content.php';
                        require_once __DIR__ . '/includes/class-wcsm-search-merchandising-dashboard.php';
                        require_once __DIR__ . '/includes/class-wcsm-search-merchandising-enqueues.php';
                        require_once __DIR__ . '/includes/class-wcsm-search-merchandising-redirect.php';
                        require_once __DIR__ . '/includes/class-wcsm-search-merchandising-search.php';
                        new WCSM_Search_Merchandising_Boosts();
                        new WCSM_Search_Merchandising_Content();
                        new WCSM_Search_Merchandising_Dashboard();
                        new WCSM_Search_Merchandising_Enqueues();
                        new WCSM_Search_Merchandising_Redirect();
                        new WCSM_Search_Merchandising_Search();
                    } else {
                        add_action( 'admin_notices', function () {
                            
                            if ( current_user_can( 'edit_plugins' ) ) {
                                ?>

								<div class="notice notice-error">
									<p><?php 
                                _e( 'Search Merchandising cannot be used as WooCommerce is not active, to use Search Merchandising activate WooCommerce.', 'wcsm-search-merchandising' );
                                ?></p>
								</div>

							<?php 
                            }
                        
                        } );
                    }
                
                }
            
            }
            new WCSM_Search_Merchandising();
        }
        
        // Uninstall
        wcsm_search_merchandising_freemius()->add_action( 'after_uninstall', function () {
            
            if ( 'yes' == get_option( 'wcsm_search_merchandising_delete_data_on_uninstall' ) ) {
                global  $wpdb ;
                // Options
                $wpdb->query( "DELETE FROM `{$wpdb->prefix}options` WHERE `option_name` LIKE '%wcsm_search_merchandising_%'" );
                // Database tables
                $wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}wcsm_search_merchandising_search_terms" );
                $wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}wcsm_search_merchandising_search_terms_date" );
                $wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}wcsm_search_merchandising_search_products" );
                $wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}wcsm_search_merchandising_search_products_date" );
                // Content (Uses wp_delete_post to ensure all post meta and related data to post is removed)
                $content_post_ids = get_posts( array(
                    'post_type'      => 'wcsm_content',
                    'post_status'    => get_post_stati(),
                    'fields'         => 'ids',
                    'posts_per_page' => -1,
                ) );
                foreach ( $content_post_ids as $content_post_id ) {
                    wp_delete_post( $content_post_id, true );
                }
            }
        
        } );
    }

}
