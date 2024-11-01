<?php

if ( !defined( 'ABSPATH' ) ) {
    exit;
}
if ( !class_exists( 'WCSM_Search_Merchandising_Dashboard' ) ) {
    class WCSM_Search_Merchandising_Dashboard
    {
        public function __construct()
        {
            add_action( 'admin_menu', array( $this, 'menu_pages' ) );
            add_filter( 'parent_file', array( $this, 'menu_page_highlighting' ) );
            add_action( 'wp_ajax_wcsm_search_merchandising_search_term_save_content', array( $this, 'save_content' ) );
            add_action( 'wp_ajax_wcsm_search_merchandising_search_term_save_boosts', array( $this, 'save_boosts' ) );
            add_action( 'wp_ajax_wcsm_search_merchandising_search_term_save_redirect', array( $this, 'save_redirect' ) );
            add_action( 'admin_head', array( $this, 'save_settings' ) );
        }
        
        public function menu_pages()
        {
            add_menu_page(
                __( 'Search Merchandising', 'wcsm-search-merchandising' ),
                __( 'Search Merch...', 'wcsm-search-merchandising' ),
                'manage_woocommerce',
                'wcsm-search-merchandising',
                array( $this, 'page' ),
                'dashicons-wcsm-search-merchandising',
                '55.6'
            );
            add_submenu_page(
                // This effectively removes the default sub menu which would normally get added by default
                'wcsm-search-merchandising',
                // points to top level menu_page page
                __( 'Search Merchandising', 'wcsm-search-merchandising' ),
                // This will end up as the browser title for all pages
                __( 'Terms', 'wcsm-search-merchandising' ),
                'manage_woocommerce',
                'wcsm-search-merchandising'
            );
            add_submenu_page(
                'wcsm-search-merchandising',
                __( 'Products', 'wcsm-search-merchandising' ),
                __( 'Products', 'wcsm-search-merchandising' ),
                'manage_woocommerce',
                'admin.php?page=wcsm-search-merchandising&tab=products'
            );
            add_submenu_page(
                'wcsm-search-merchandising',
                __( 'Content', 'wcsm-search-merchandising' ),
                __( 'Content', 'wcsm-search-merchandising' ),
                'manage_woocommerce',
                'edit.php?post_type=wcsm_content'
            );
        }
        
        public function menu_page_highlighting( $parent_file )
        {
            global  $submenu_file ;
            global  $current_screen ;
            if ( !empty($current_screen) ) {
                if ( isset( $current_screen->base ) ) {
                    if ( 'toplevel_page_wcsm-search-merchandising' == $current_screen->base ) {
                        
                        if ( isset( $_GET['tab'] ) ) {
                            $tab = sanitize_text_field( $_GET['tab'] );
                            // Other sub menu pages with the exception of these are highlighted automatically and therefore do no need any specific conditions here
                            
                            if ( 'products' == $tab ) {
                                $submenu_file = 'admin.php?page=wcsm-search-merchandising&tab=products';
                            } else {
                            }
                        
                        }
                    
                    }
                }
            }
            return $parent_file;
        }
        
        public function page()
        {
            global  $wpdb ;
            // Tab
            
            if ( isset( $_GET['tab'] ) ) {
                $tab = sanitize_text_field( $_GET['tab'] );
                if ( empty($tab) ) {
                    $tab = 'terms';
                }
            } else {
                $tab = 'terms';
            }
            
            // Dates
            
            if ( isset( $_GET['dates'] ) ) {
                $dates = sanitize_text_field( $_GET['dates'] );
                if ( empty($dates) ) {
                    $dates = 'all';
                }
                
                if ( 'all' !== $dates ) {
                    $dates_explode = explode( ' - ', $dates );
                    $dates_from = $dates_explode[0];
                    $dates_to = $dates_explode[1];
                }
            
            } else {
                $dates = 'all';
            }
            
            // Search
            
            if ( isset( $_GET['search'] ) ) {
                $search = sanitize_text_field( $_GET['search'] );
            } else {
                $search = '';
            }
            
            // Order By
            
            if ( isset( $_GET['order_by'] ) ) {
                $order_by = sanitize_text_field( $_GET['order_by'] );
            } else {
                $order_by = '';
            }
            
            // Order
            
            if ( isset( $_GET['order'] ) ) {
                $order = sanitize_text_field( $_GET['order'] );
            } else {
                $order = '';
            }
            
            // Pagination
            
            if ( isset( $_GET['paged'] ) ) {
                $pagination_paged = (int) sanitize_text_field( $_GET['paged'] );
            } else {
                $pagination_paged = 0;
            }
            
            $pagination_paged = ( 0 == $pagination_paged ? 1 : $pagination_paged );
            // 0 is page 1
            $pagination_rows_per_page = (int) get_option( 'wcsm_search_merchandising_rows_per_page' );
            $pagination_offset = ( $pagination_paged > 0 ? $pagination_paged * $pagination_rows_per_page - $pagination_rows_per_page : $pagination_rows_per_page );
            // Products
            $products = $wpdb->get_results( "SELECT ID, post_title FROM `{$wpdb->prefix}posts` WHERE `post_type` = 'product' AND `post_status` = 'publish' ORDER BY post_title ASC" );
            // Content
            $content = $wpdb->get_results( "SELECT ID, post_title FROM `{$wpdb->prefix}posts` WHERE `post_type` = 'wcsm_content' AND `post_status` = 'publish' ORDER BY post_title ASC" );
            // No Data
            $no_data = esc_html__( 'No data yet.', 'wcsm-search-merchandising' );
            // translators: standard WooCommerce product search URL
            $no_data_terms = $no_data . ' ' . esc_html__( 'Data will be available once customers have searched for products on the website, alternatively try some test searches.', 'wcsm-search-merchandising' ) . '<br>' . sprintf( esc_html__( 'Note that tracking relies on the search results URL being the standard WooCommerce product search URL (%s), if this is not used then data will not be tracked.', 'wcsm-search-merchandising' ), '?s=' . esc_html__( 'term', 'wcsm-search-merchandising' ) . '&post_type=product' );
            // translators: standard WooCommerce product search URL
            $no_data_products = $no_data . ' ' . esc_html__( 'Data will be available once customers have searched for products on the website and clicked through to products, alternatively try some test searches and click through to products.', 'wcsm-search-merchandising' ) . '<br>' . sprintf( esc_html__( 'Note that tracking relies on the search results URL being the standard WooCommerce product search URL (%s), if this is not used then data will not be tracked.', 'wcsm-search-merchandising' ), '?s=' . esc_html__( 'term', 'wcsm-search-merchandising' ) . '&post_type=product' );
            ?>

			<div class="wrap">
				<div class="wcsm-search-merchandising wcsm-search-merchandising-tab-<?php 
            esc_html_e( $tab );
            ?>">
					<div id="wcsm-search-merchandising-settings">
						<div class="wcsm-search-merchandising-settings-title"><?php 
            esc_html_e( 'Settings', 'wcsm-search-merchandising' );
            ?></div>
						<form method="post">
							<div class="wcsm-search-merchandising-settings-fields">
								<div class="wcsm-search-merchandising-settings-field">
									<label><?php 
            esc_html_e( 'Rows per page', 'wcsm-search-merchandising' );
            ?><input type="number" name="wcsm_search_merchandising_settings_rows_per_page" min="1" max="100" value="<?php 
            esc_html_e( $pagination_rows_per_page );
            ?>" required></label>
								</div>
								<?php 
            $user_roles = get_editable_roles();
            
            if ( !empty($user_roles) ) {
                $disable_tracking_user_roles = get_option( 'wcsm_search_merchandising_disable_tracking_user_roles' );
                $disable_tracking_user_roles = ( '' !== $disable_tracking_user_roles ? $disable_tracking_user_roles : array() );
                ?>
									<div class="wcsm-search-merchandising-settings-field">
										<fieldset>      
											<legend><?php 
                esc_html_e( 'Disable tracking for specific user roles', 'wcsm-search-merchandising' );
                ?></legend>
											<?php 
                foreach ( $user_roles as $user_role => $user_role_data ) {
                    ?>
												<label><input type="checkbox" name="wcsm_search_merchandising_settings_disable_tracking_user_roles[]" value="<?php 
                    esc_html_e( $user_role );
                    ?>"<?php 
                    echo  ( in_array( $user_role, $disable_tracking_user_roles ) ? ' checked' : '' ) ;
                    ?>><?php 
                    esc_html_e( $user_role_data['name'] );
                    ?></label>
											<?php 
                }
                ?>
										</fieldset>
									</div>
								<?php 
            }
            
            ?>
								<div class="wcsm-search-merchandising-settings-field">
									<label>
										<input type="checkbox" name="wcsm_search_merchandising_settings_delete_data_on_uninstall"<?php 
            echo  ( 'yes' == get_option( 'wcsm_search_merchandising_delete_data_on_uninstall' ) ? ' checked' : '' ) ;
            ?>>
										<?php 
            esc_html_e( 'Delete data on uninstall', 'wcsm-search-merchandising' );
            ?> <?php 
            esc_html_e( '-', 'wcsm-search-merchandising' );
            ?> <small class="wcsm-search-merchandising-red"><?php 
            esc_html_e( 'if this plugin is uninstalled all data will be removed including search merchandising content, if you reinstall later your old data will not be available', 'wcsm-search-merchandising' );
            ?></small>
									</label>
								</div>
								<div class="wcsm-search-merchandising-settings-field">
									<label>
										<input type="checkbox" name="wcsm_search_merchandising_settings_reset_data">
										<?php 
            esc_html_e( 'Reset data', 'wcsm-search-merchandising' );
            ?> <?php 
            esc_html_e( '-', 'wcsm-search-merchandising' );
            ?> <small class="wcsm-search-merchandising-red"><?php 
            esc_html_e( 'only use if you want to reset all the data within this dashboard, note this does not effect any search merchandising content created but does remove term assignment', 'wcsm-search-merchandising' );
            ?></small>
									</label>
								</div>
							</div>
							<div class="wcsm-search-merchandising-settings-buttons">
								<button class="button button-primary" type="submit" name="wcsm_search_merchandising_settings_save"><?php 
            esc_html_e( 'Save', 'wcsm-search-merchandising' );
            ?></button>
								<button id="wcsm-search-merchandising-settings-cancel" class="button"><?php 
            esc_html_e( 'Cancel', 'wcsm-search-merchandising' );
            ?></button>
								<?php 
            wp_nonce_field( 'wcsm_search_merchandising_settings_save', 'wcsm_search_merchandising_settings_save_nonce' );
            ?>
							</div>
						</form>
					</div>
					<h1 class="dashicons-wcsm-search-merchandising">
						<?php 
            // h1 is here and not settings first as that contains a h1 which would mean notices from saving settings are appended to that and therefore not visible
            $heading = esc_html__( 'Search Merchandising', 'wcsm-search-merchandising' ) . ' ' . esc_html__( '-', 'wcsm-search-merchandising' ) . ' ' . ucfirst( $tab ) . ' ' . esc_html__( '-', 'wcsm-search-merchandising' ) . ' ';
            $heading .= (( 'all' == $dates ? esc_html__( 'All time', 'wcsm-search-merchandising' ) : esc_html__( 'Dates:', 'wcsm-search-merchandising' ) . ' ' . $dates )) . ', ';
            $heading .= ( '' !== $search ? esc_html__( 'Search:', 'wcsm-search-merchandising' ) . ' ' . $search : '' );
            $heading = rtrim( $heading, ' , ' );
            esc_html_e( $heading );
            ?>
						<div id="wcsm-search-merchandising-plan">
							<?php 
            echo  esc_html( sprintf( __( 'v%s', 'wcsm-search-merchandising' ), WCSM_SEARCH_MERCHANDISING_VERSION ) ) ;
            ?>
						</div>
					</h1>
					<div id="wcsm-search-merchandising-screen-width-notice" class="notice notice-info inline">
						<p><?php 
            esc_html_e( 'For best results use Search Merchandising on a device with a screen width of 1300px or higher. You may need to refresh if resizing your browser window.', 'wcsm-search-merchandising' );
            ?></p>
					</div>
					<div id="wcsm-search-merchandising-saving"><?php 
            esc_html_e( 'Saving', 'wcsm-search-merchandising' );
            ?><img src="<?php 
            echo  esc_url( get_admin_url() . 'images/spinner.gif' ) ;
            ?>"></div>
					<nav class="nav-tab-wrapper">
						<div>
							<a href="<?php 
            echo  esc_url( remove_query_arg( array( 'order_by', 'order', 'paged' ), add_query_arg( 'tab', 'terms' ) ) ) ;
            ?>" class="nav-tab<?php 
            echo  ( 'terms' == $tab ? ' nav-tab-active' : '' ) ;
            ?>"><?php 
            esc_html_e( 'Terms', 'wcsm-search-merchandising' );
            ?></a>
							<a href="<?php 
            echo  esc_url( remove_query_arg( array( 'order_by', 'order', 'paged' ), add_query_arg( 'tab', 'products' ) ) ) ;
            ?>" class="nav-tab<?php 
            echo  ( 'products' == $tab ? ' nav-tab-active' : '' ) ;
            ?>"><?php 
            esc_html_e( 'Products', 'wcsm-search-merchandising' );
            ?></a>
							<?php 
            ?>
							<?php 
            
            if ( in_array( $tab, array( 'terms', 'products', 'insights' ) ) ) {
                ?>
								<form method="get" action="admin.php">
									<input type="hidden" name="post_type" value="product">
									<input type="hidden" name="page" value="wcsm-search-merchandising">
									<input type="hidden" name="tab" value="<?php 
                esc_html_e( $tab );
                ?>">
									<label>
										<?php 
                esc_html_e( 'Dates', 'wcsm-search-merchandising' );
                ?>
										<input type="text" name="dates" id="wcsm-search-merchandising-dates" value="<?php 
                esc_html_e( ( 'all' !== $dates ? $dates : '' ) );
                ?>" placeholder="<?php 
                echo  esc_html__( 'Filter', 'wcsm-search-merchandising' ) . ' ' . esc_html( $tab ) . ' ' . esc_html__( 'by date', 'wcsm-search-merchandising' ) ;
                ?>">
									</label>
									<?php 
                ?>
										<label>
											<?php 
                esc_html_e( 'Search', 'wcsm-search-merchandising' );
                ?>
											<input type="text" name="search" value="<?php 
                echo  esc_html( $search ) ;
                ?>" placeholder="<?php 
                echo  esc_html__( 'Search by', 'wcsm-search-merchandising' ) . ' ' . esc_html( substr( $tab, 0, -1 ) ) ;
                ?>">
										</label>
									<?php 
                ?>
									<button type="submit" class="button button-primary"><?php 
                esc_html_e( 'Apply', 'wcsm-search-merchandising' );
                ?></button>
									<?php 
                if ( 'all' !== $dates || '' !== $search ) {
                    echo  '<a href="' . esc_url( remove_query_arg( array( 'dates', 'search' ) ) ) . '" class="button">' . esc_html__( 'Reset', 'wcsm-search-merchandising' ) . '</a>' ;
                }
                ?>
								</form>
							<?php 
            }
            
            ?>
						</div>
						<div>
							<a href="<?php 
            echo  esc_url( get_admin_url() . 'post-new.php?post_type=wcsm_content' ) ;
            ?>" class="button button-primary"><?php 
            esc_html_e( 'Add Content', 'wcsm-search-merchandising' );
            ?></a>
							<a href="<?php 
            echo  esc_url( get_admin_url() . 'edit.php?post_type=wcsm_content' ) ;
            ?>" class="button button-primary"><?php 
            esc_html_e( 'Manage Content', 'wcsm-search-merchandising' );
            ?></a>
							<a id="wcsm-search-merchandising-settings-button" class="button"><?php 
            esc_html_e( 'Settings', 'wcsm-search-merchandising' );
            ?></a>
						</div>
						<br clear="all">
					</nav>
					<?php 
            // Search Terms/Products Main Query (All queries are standalone within conditions rather than built and concatinated due to $wpdb->prepare sniff not knowing a variable is preprepared, also order by and order within wpdb->prepare cannot be used as adds 'asc' quotes around the values which SQL doesn't like)
            // SQL is not split over multiple lines throughout these conditions or Freemius PHP processor will add \n\t\t\t etc to it
            
            if ( 'terms' == $tab ) {
                
                if ( 'all' == $dates ) {
                    
                    if ( '' !== $search ) {
                        
                        if ( 'terms' == $order_by && 'asc' == $order ) {
                            $search_terms = $wpdb->get_results( $wpdb->prepare(
                                "SELECT * FROM `{$wpdb->prefix}wcsm_search_merchandising_search_terms` WHERE search_term LIKE %s ORDER BY search_term ASC LIMIT %d, %d",
                                '%' . $search . '%',
                                $pagination_offset,
                                $pagination_rows_per_page
                            ) );
                        } elseif ( 'terms' == $order_by && 'desc' == $order ) {
                            $search_terms = $wpdb->get_results( $wpdb->prepare(
                                "SELECT * FROM `{$wpdb->prefix}wcsm_search_merchandising_search_terms` WHERE search_term LIKE %s ORDER BY search_term DESC LIMIT %d, %d",
                                '%' . $search . '%',
                                $pagination_offset,
                                $pagination_rows_per_page
                            ) );
                        } elseif ( 'searches' == $order_by && 'asc' == $order ) {
                            $search_terms = $wpdb->get_results( $wpdb->prepare(
                                "SELECT * FROM `{$wpdb->prefix}wcsm_search_merchandising_search_terms` WHERE search_term LIKE %s ORDER BY search_term_searches ASC LIMIT %d, %d",
                                '%' . $search . '%',
                                $pagination_offset,
                                $pagination_rows_per_page
                            ) );
                        } elseif ( 'searches' == $order_by && 'desc' == $order ) {
                            $search_terms = $wpdb->get_results( $wpdb->prepare(
                                "SELECT * FROM `{$wpdb->prefix}wcsm_search_merchandising_search_terms` WHERE search_term LIKE %s ORDER BY search_term_searches DESC LIMIT %d, %d",
                                '%' . $search . '%',
                                $pagination_offset,
                                $pagination_rows_per_page
                            ) );
                        } elseif ( 'results_average' == $order_by && 'asc' == $order ) {
                            $search_terms = $wpdb->get_results( $wpdb->prepare(
                                "SELECT * FROM `{$wpdb->prefix}wcsm_search_merchandising_search_terms` WHERE search_term LIKE %s ORDER BY search_term_results_average ASC LIMIT %d, %d",
                                '%' . $search . '%',
                                $pagination_offset,
                                $pagination_rows_per_page
                            ) );
                        } elseif ( 'results_average' == $order_by && 'desc' == $order ) {
                            $search_terms = $wpdb->get_results( $wpdb->prepare(
                                "SELECT * FROM `{$wpdb->prefix}wcsm_search_merchandising_search_terms` WHERE search_term LIKE %s ORDER BY search_term_results_average DESC LIMIT %d, %d",
                                '%' . $search . '%',
                                $pagination_offset,
                                $pagination_rows_per_page
                            ) );
                        } else {
                            $search_terms = $wpdb->get_results( $wpdb->prepare(
                                "SELECT * FROM `{$wpdb->prefix}wcsm_search_merchandising_search_terms` WHERE search_term LIKE %s ORDER BY search_term_searches DESC LIMIT %d, %d",
                                '%' . $search . '%',
                                $pagination_offset,
                                $pagination_rows_per_page
                            ) );
                        }
                        
                        $pagination_total = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM `{$wpdb->prefix}wcsm_search_merchandising_search_terms` WHERE search_term LIKE %s", '%' . $search . '%' ) );
                    } else {
                        
                        if ( 'terms' == $order_by && 'asc' == $order ) {
                            $search_terms = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM `{$wpdb->prefix}wcsm_search_merchandising_search_terms` ORDER BY search_term ASC LIMIT %d, %d", $pagination_offset, $pagination_rows_per_page ) );
                        } elseif ( 'terms' == $order_by && 'desc' == $order ) {
                            $search_terms = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM `{$wpdb->prefix}wcsm_search_merchandising_search_terms` ORDER BY search_term DESC LIMIT %d, %d", $pagination_offset, $pagination_rows_per_page ) );
                        } elseif ( 'searches' == $order_by && 'asc' == $order ) {
                            $search_terms = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM `{$wpdb->prefix}wcsm_search_merchandising_search_terms` ORDER BY search_term_searches ASC LIMIT %d, %d", $pagination_offset, $pagination_rows_per_page ) );
                        } elseif ( 'searches' == $order_by && 'desc' == $order ) {
                            $search_terms = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM `{$wpdb->prefix}wcsm_search_merchandising_search_terms` ORDER BY search_term_searches DESC LIMIT %d, %d", $pagination_offset, $pagination_rows_per_page ) );
                        } elseif ( 'results_average' == $order_by && 'asc' == $order ) {
                            $search_terms = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM `{$wpdb->prefix}wcsm_search_merchandising_search_terms` ORDER BY search_term_results_average ASC LIMIT %d, %d", $pagination_offset, $pagination_rows_per_page ) );
                        } elseif ( 'results_average' == $order_by && 'desc' == $order ) {
                            $search_terms = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM `{$wpdb->prefix}wcsm_search_merchandising_search_terms` ORDER BY search_term_results_average DESC LIMIT %d, %d", $pagination_offset, $pagination_rows_per_page ) );
                        } else {
                            $search_terms = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM `{$wpdb->prefix}wcsm_search_merchandising_search_terms` ORDER BY search_term_searches DESC LIMIT %d, %d", $pagination_offset, $pagination_rows_per_page ) );
                        }
                        
                        $pagination_total = $wpdb->get_var( "SELECT COUNT(*) FROM `{$wpdb->prefix}wcsm_search_merchandising_search_terms`" );
                    }
                
                } else {
                    
                    if ( '' !== $search ) {
                        
                        if ( 'terms' == $order_by && 'asc' == $order ) {
                            $search_terms = $wpdb->get_results( $wpdb->prepare(
                                "SELECT terms_date.search_term_id, terms.search_term, SUM( terms_date.search_term_searches ) AS search_term_searches, terms.search_term_content FROM `{$wpdb->prefix}wcsm_search_merchandising_search_terms_date` AS terms_date INNER JOIN `{$wpdb->prefix}wcsm_search_merchandising_search_terms` AS terms ON terms.search_term_id = terms_date.search_term_id WHERE terms_date.search_term_date BETWEEN %s AND %s AND terms.search_term LIKE %s GROUP BY terms_date.search_term_id ORDER BY search_term ASC LIMIT %d, %d",
                                $dates_from,
                                $dates_to,
                                '%' . $search . '%',
                                $pagination_offset,
                                $pagination_rows_per_page
                            ) );
                        } elseif ( 'terms' == $order_by && 'desc' == $order ) {
                            $search_terms = $wpdb->get_results( $wpdb->prepare(
                                "SELECT terms_date.search_term_id, terms.search_term, SUM( terms_date.search_term_searches ) AS search_term_searches, terms.search_term_content FROM `{$wpdb->prefix}wcsm_search_merchandising_search_terms_date` AS terms_date INNER JOIN `{$wpdb->prefix}wcsm_search_merchandising_search_terms` AS terms ON terms.search_term_id = terms_date.search_term_id WHERE terms_date.search_term_date BETWEEN %s AND %s AND terms.search_term LIKE %s GROUP BY terms_date.search_term_id ORDER BY search_term DESC LIMIT %d, %d",
                                $dates_from,
                                $dates_to,
                                '%' . $search . '%',
                                $pagination_offset,
                                $pagination_rows_per_page
                            ) );
                        } elseif ( 'searches' == $order_by && 'asc' == $order ) {
                            $search_terms = $wpdb->get_results( $wpdb->prepare(
                                "SELECT terms_date.search_term_id, terms.search_term, SUM( terms_date.search_term_searches ) AS search_term_searches, terms.search_term_content FROM `{$wpdb->prefix}wcsm_search_merchandising_search_terms_date` AS terms_date INNER JOIN `{$wpdb->prefix}wcsm_search_merchandising_search_terms` AS terms ON terms.search_term_id = terms_date.search_term_id WHERE terms_date.search_term_date BETWEEN %s AND %s AND terms.search_term LIKE %s GROUP BY terms_date.search_term_id ORDER BY search_term_searches ASC LIMIT %d, %d",
                                $dates_from,
                                $dates_to,
                                '%' . $search . '%',
                                $pagination_offset,
                                $pagination_rows_per_page
                            ) );
                        } elseif ( 'searches' == $order_by && 'desc' == $order ) {
                            $search_terms = $wpdb->get_results( $wpdb->prepare(
                                "SELECT terms_date.search_term_id, terms.search_term, SUM( terms_date.search_term_searches ) AS search_term_searches, terms.search_term_content FROM `{$wpdb->prefix}wcsm_search_merchandising_search_terms_date` AS terms_date INNER JOIN `{$wpdb->prefix}wcsm_search_merchandising_search_terms` AS terms ON terms.search_term_id = terms_date.search_term_id WHERE terms_date.search_term_date BETWEEN %s AND %s AND terms.search_term LIKE %s GROUP BY terms_date.search_term_id ORDER BY search_term_searches DESC LIMIT %d, %d",
                                $dates_from,
                                $dates_to,
                                '%' . $search . '%',
                                $pagination_offset,
                                $pagination_rows_per_page
                            ) );
                        } else {
                            $search_terms = $wpdb->get_results( $wpdb->prepare(
                                "SELECT terms_date.search_term_id, terms.search_term, SUM( terms_date.search_term_searches ) AS search_term_searches, terms.search_term_content FROM `{$wpdb->prefix}wcsm_search_merchandising_search_terms_date` AS terms_date INNER JOIN `{$wpdb->prefix}wcsm_search_merchandising_search_terms` AS terms ON terms.search_term_id = terms_date.search_term_id WHERE terms_date.search_term_date BETWEEN %s AND %s AND terms.search_term LIKE %s GROUP BY terms_date.search_term_id ORDER BY search_term_searches DESC LIMIT %d, %d",
                                $dates_from,
                                $dates_to,
                                '%' . $search . '%',
                                $pagination_offset,
                                $pagination_rows_per_page
                            ) );
                        }
                        
                        $pagination_total = count( $wpdb->get_results( $wpdb->prepare(
                            "SELECT terms_date.search_term_id, terms.search_term, SUM( terms_date.search_term_searches ) AS search_term_searches, terms.search_term_content FROM `{$wpdb->prefix}wcsm_search_merchandising_search_terms_date` AS terms_date INNER JOIN `{$wpdb->prefix}wcsm_search_merchandising_search_terms` AS terms ON terms.search_term_id = terms_date.search_term_id WHERE terms_date.search_term_date BETWEEN %s AND %s AND terms.search_term LIKE %s GROUP BY terms_date.search_term_id",
                            $dates_from,
                            $dates_to,
                            '%' . $search . '%'
                        ) ) );
                    } else {
                        
                        if ( 'terms' == $order_by && 'asc' == $order ) {
                            $search_terms = $wpdb->get_results( $wpdb->prepare(
                                "SELECT terms_date.search_term_id, terms.search_term, SUM( terms_date.search_term_searches ) AS search_term_searches, terms.search_term_content FROM `{$wpdb->prefix}wcsm_search_merchandising_search_terms_date` AS terms_date INNER JOIN `{$wpdb->prefix}wcsm_search_merchandising_search_terms` AS terms ON terms.search_term_id = terms_date.search_term_id WHERE terms_date.search_term_date BETWEEN %s AND %s GROUP BY terms_date.search_term_id ORDER BY search_term ASC LIMIT %d, %d",
                                $dates_from,
                                $dates_to,
                                $pagination_offset,
                                $pagination_rows_per_page
                            ) );
                        } elseif ( 'terms' == $order_by && 'desc' == $order ) {
                            $search_terms = $wpdb->get_results( $wpdb->prepare(
                                "SELECT terms_date.search_term_id, terms.search_term, SUM( terms_date.search_term_searches ) AS search_term_searches, terms.search_term_content FROM `{$wpdb->prefix}wcsm_search_merchandising_search_terms_date` AS terms_date INNER JOIN `{$wpdb->prefix}wcsm_search_merchandising_search_terms` AS terms ON terms.search_term_id = terms_date.search_term_id WHERE terms_date.search_term_date BETWEEN %s AND %s GROUP BY terms_date.search_term_id ORDER BY search_term DESC LIMIT %d, %d",
                                $dates_from,
                                $dates_to,
                                $pagination_offset,
                                $pagination_rows_per_page
                            ) );
                        } elseif ( 'searches' == $order_by && 'asc' == $order ) {
                            $search_terms = $wpdb->get_results( $wpdb->prepare(
                                "SELECT terms_date.search_term_id, terms.search_term, SUM( terms_date.search_term_searches ) AS search_term_searches, terms.search_term_content FROM `{$wpdb->prefix}wcsm_search_merchandising_search_terms_date` AS terms_date INNER JOIN `{$wpdb->prefix}wcsm_search_merchandising_search_terms` AS terms ON terms.search_term_id = terms_date.search_term_id WHERE terms_date.search_term_date BETWEEN %s AND %s GROUP BY terms_date.search_term_id ORDER BY search_term_searches ASC LIMIT %d, %d",
                                $dates_from,
                                $dates_to,
                                $pagination_offset,
                                $pagination_rows_per_page
                            ) );
                        } elseif ( 'searches' == $order_by && 'desc' == $order ) {
                            $search_terms = $wpdb->get_results( $wpdb->prepare(
                                "SELECT terms_date.search_term_id, terms.search_term, SUM( terms_date.search_term_searches ) AS search_term_searches, terms.search_term_content FROM `{$wpdb->prefix}wcsm_search_merchandising_search_terms_date` AS terms_date INNER JOIN `{$wpdb->prefix}wcsm_search_merchandising_search_terms` AS terms ON terms.search_term_id = terms_date.search_term_id WHERE terms_date.search_term_date BETWEEN %s AND %s GROUP BY terms_date.search_term_id ORDER BY search_term_searches DESC LIMIT %d, %d",
                                $dates_from,
                                $dates_to,
                                $pagination_offset,
                                $pagination_rows_per_page
                            ) );
                        } else {
                            $search_terms = $wpdb->get_results( $wpdb->prepare(
                                "SELECT terms_date.search_term_id, terms.search_term, SUM( terms_date.search_term_searches ) AS search_term_searches, terms.search_term_content FROM `{$wpdb->prefix}wcsm_search_merchandising_search_terms_date` AS terms_date INNER JOIN `{$wpdb->prefix}wcsm_search_merchandising_search_terms` AS terms ON terms.search_term_id = terms_date.search_term_id WHERE terms_date.search_term_date BETWEEN %s AND %s GROUP BY terms_date.search_term_id ORDER BY search_term_searches DESC LIMIT %d, %d",
                                $dates_from,
                                $dates_to,
                                $pagination_offset,
                                $pagination_rows_per_page
                            ) );
                        }
                        
                        $pagination_total = count( $wpdb->get_results( $wpdb->prepare( "SELECT terms_date.search_term_id, terms.search_term, SUM( terms_date.search_term_searches ) AS search_term_searches, terms.search_term_content FROM `{$wpdb->prefix}wcsm_search_merchandising_search_terms_date` AS terms_date INNER JOIN `{$wpdb->prefix}wcsm_search_merchandising_search_terms` AS terms ON terms.search_term_id = terms_date.search_term_id WHERE terms_date.search_term_date BETWEEN %s AND %s GROUP BY terms_date.search_term_id", $dates_from, $dates_to ) ) );
                    }
                
                }
            
            } elseif ( 'products' == $tab ) {
                
                if ( 'all' == $dates ) {
                    
                    if ( '' !== $search ) {
                        
                        if ( 'products' == $order_by && 'asc' == $order ) {
                            $search_products = $wpdb->get_results( $wpdb->prepare(
                                "SELECT search_products.product_id, SUM(search_products.search_product_clicks) AS search_product_clicks, posts.post_title FROM `{$wpdb->prefix}wcsm_search_merchandising_search_products` AS search_products INNER JOIN `{$wpdb->prefix}posts` AS posts ON search_products.product_id = posts.ID WHERE posts.post_title LIKE %s GROUP BY product_id ORDER BY post_title ASC LIMIT %d, %d",
                                '%' . $search . '%',
                                $pagination_offset,
                                $pagination_rows_per_page
                            ) );
                        } elseif ( 'products' == $order_by && 'desc' == $order ) {
                            $search_products = $wpdb->get_results( $wpdb->prepare(
                                "SELECT search_products.product_id, SUM(search_products.search_product_clicks) AS search_product_clicks, posts.post_title FROM `{$wpdb->prefix}wcsm_search_merchandising_search_products` AS search_products INNER JOIN `{$wpdb->prefix}posts` AS posts ON search_products.product_id = posts.ID WHERE posts.post_title LIKE %s GROUP BY product_id ORDER BY post_title DESC LIMIT %d, %d",
                                '%' . $search . '%',
                                $pagination_offset,
                                $pagination_rows_per_page
                            ) );
                        } elseif ( 'clicks' == $order_by && 'asc' == $order ) {
                            $search_products = $wpdb->get_results( $wpdb->prepare(
                                "SELECT search_products.product_id, SUM(search_products.search_product_clicks) AS search_product_clicks, posts.post_title FROM `{$wpdb->prefix}wcsm_search_merchandising_search_products` AS search_products INNER JOIN `{$wpdb->prefix}posts` AS posts ON search_products.product_id = posts.ID WHERE posts.post_title LIKE %s GROUP BY product_id ORDER BY search_product_clicks ASC LIMIT %d, %d",
                                '%' . $search . '%',
                                $pagination_offset,
                                $pagination_rows_per_page
                            ) );
                        } elseif ( 'clicks' == $order_by && 'desc' == $order ) {
                            $search_products = $wpdb->get_results( $wpdb->prepare(
                                "SELECT search_products.product_id, SUM(search_products.search_product_clicks) AS search_product_clicks, posts.post_title FROM `{$wpdb->prefix}wcsm_search_merchandising_search_products` AS search_products INNER JOIN `{$wpdb->prefix}posts` AS posts ON search_products.product_id = posts.ID WHERE posts.post_title LIKE %s GROUP BY product_id ORDER BY search_product_clicks DESC LIMIT %d, %d",
                                '%' . $search . '%',
                                $pagination_offset,
                                $pagination_rows_per_page
                            ) );
                        } else {
                            $search_products = $wpdb->get_results( $wpdb->prepare(
                                "SELECT search_products.product_id, SUM(search_products.search_product_clicks) AS search_product_clicks, posts.post_title FROM `{$wpdb->prefix}wcsm_search_merchandising_search_products` AS search_products INNER JOIN `{$wpdb->prefix}posts` AS posts ON search_products.product_id = posts.ID WHERE posts.post_title LIKE %s GROUP BY product_id ORDER BY search_product_clicks DESC LIMIT %d, %d",
                                '%' . $search . '%',
                                $pagination_offset,
                                $pagination_rows_per_page
                            ) );
                        }
                        
                        $pagination_total = count( $wpdb->get_results( $wpdb->prepare( "SELECT search_products.product_id, SUM(search_products.search_product_clicks) AS search_product_clicks, posts.post_title FROM `{$wpdb->prefix}wcsm_search_merchandising_search_products` AS search_products INNER JOIN `{$wpdb->prefix}posts` AS posts ON search_products.product_id = posts.ID WHERE posts.post_title LIKE %s GROUP BY product_id ORDER BY search_product_clicks ASC", '%' . $search . '%' ) ) );
                    } else {
                        
                        if ( 'products' == $order_by && 'asc' == $order ) {
                            $search_products = $wpdb->get_results( $wpdb->prepare( "SELECT search_products.product_id, SUM(search_products.search_product_clicks) AS search_product_clicks, posts.post_title FROM `{$wpdb->prefix}wcsm_search_merchandising_search_products` AS search_products INNER JOIN `{$wpdb->prefix}posts` AS posts ON search_products.product_id = posts.ID GROUP BY product_id ORDER BY post_title ASC LIMIT %d, %d", $pagination_offset, $pagination_rows_per_page ) );
                        } elseif ( 'products' == $order_by && 'desc' == $order ) {
                            $search_products = $wpdb->get_results( $wpdb->prepare( "SELECT search_products.product_id, SUM(search_products.search_product_clicks) AS search_product_clicks, posts.post_title FROM `{$wpdb->prefix}wcsm_search_merchandising_search_products` AS search_products INNER JOIN `{$wpdb->prefix}posts` AS posts ON search_products.product_id = posts.ID GROUP BY product_id ORDER BY post_title DESC LIMIT %d, %d", $pagination_offset, $pagination_rows_per_page ) );
                        } elseif ( 'clicks' == $order_by && 'asc' == $order ) {
                            $search_products = $wpdb->get_results( $wpdb->prepare( "SELECT search_products.product_id, SUM(search_products.search_product_clicks) AS search_product_clicks, posts.post_title FROM `{$wpdb->prefix}wcsm_search_merchandising_search_products` AS search_products INNER JOIN `{$wpdb->prefix}posts` AS posts ON search_products.product_id = posts.ID GROUP BY product_id ORDER BY search_product_clicks ASC LIMIT %d, %d", $pagination_offset, $pagination_rows_per_page ) );
                        } elseif ( 'clicks' == $order_by && 'desc' == $order ) {
                            $search_products = $wpdb->get_results( $wpdb->prepare( "SELECT search_products.product_id, SUM(search_products.search_product_clicks) AS search_product_clicks, posts.post_title FROM `{$wpdb->prefix}wcsm_search_merchandising_search_products` AS search_products INNER JOIN `{$wpdb->prefix}posts` AS posts ON search_products.product_id = posts.ID GROUP BY product_id ORDER BY search_product_clicks DESC LIMIT %d, %d", $pagination_offset, $pagination_rows_per_page ) );
                        } else {
                            $search_products = $wpdb->get_results( $wpdb->prepare( "SELECT search_products.product_id, SUM(search_products.search_product_clicks) AS search_product_clicks, posts.post_title FROM `{$wpdb->prefix}wcsm_search_merchandising_search_products` AS search_products INNER JOIN `{$wpdb->prefix}posts` AS posts ON search_products.product_id = posts.ID GROUP BY product_id ORDER BY search_product_clicks DESC LIMIT %d, %d", $pagination_offset, $pagination_rows_per_page ) );
                        }
                        
                        $pagination_total = count( $wpdb->get_results( "SELECT search_products.product_id, SUM(search_products.search_product_clicks) AS search_product_clicks, posts.post_title FROM `{$wpdb->prefix}wcsm_search_merchandising_search_products` AS search_products INNER JOIN `{$wpdb->prefix}posts` AS posts ON search_products.product_id = posts.ID GROUP BY product_id ORDER BY search_product_clicks ASC" ) );
                    }
                
                } else {
                    
                    if ( '' !== $search ) {
                        
                        if ( 'products' == $order_by && 'asc' == $order ) {
                            $search_products = $wpdb->get_results( $wpdb->prepare(
                                "SELECT search_products_date.product_id, SUM(search_products_date.search_product_clicks) AS search_product_clicks, posts.post_title AS post_title FROM `{$wpdb->prefix}wcsm_search_merchandising_search_products_date` AS search_products_date INNER JOIN `{$wpdb->prefix}posts` AS posts ON search_products_date.product_id = posts.ID WHERE search_products_date.search_product_date BETWEEN %s AND %s AND posts.post_title LIKE %s GROUP BY product_id ORDER BY post_title ASC LIMIT %d, %d",
                                $dates_from,
                                $dates_to,
                                '%' . $search . '%',
                                $pagination_offset,
                                $pagination_rows_per_page
                            ) );
                        } elseif ( 'products' == $order_by && 'desc' == $order ) {
                            $search_products = $wpdb->get_results( $wpdb->prepare(
                                "SELECT search_products_date.product_id, SUM(search_products_date.search_product_clicks) AS search_product_clicks, posts.post_title AS post_title FROM `{$wpdb->prefix}wcsm_search_merchandising_search_products_date` AS search_products_date INNER JOIN `{$wpdb->prefix}posts` AS posts ON search_products_date.product_id = posts.ID WHERE search_products_date.search_product_date BETWEEN %s AND %s AND posts.post_title LIKE %s GROUP BY product_id ORDER BY post_title DESC LIMIT %d, %d",
                                $dates_from,
                                $dates_to,
                                '%' . $search . '%',
                                $pagination_offset,
                                $pagination_rows_per_page
                            ) );
                        } elseif ( 'clicks' == $order_by && 'asc' == $order ) {
                            $search_products = $wpdb->get_results( $wpdb->prepare(
                                "SELECT search_products_date.product_id, SUM(search_products_date.search_product_clicks) AS search_product_clicks, posts.post_title AS post_title FROM `{$wpdb->prefix}wcsm_search_merchandising_search_products_date` AS search_products_date INNER JOIN `{$wpdb->prefix}posts` AS posts ON search_products_date.product_id = posts.ID WHERE search_products_date.search_product_date BETWEEN %s AND %s AND posts.post_title LIKE %s GROUP BY product_id ORDER BY search_product_clicks ASC LIMIT %d, %d",
                                $dates_from,
                                $dates_to,
                                '%' . $search . '%',
                                $pagination_offset,
                                $pagination_rows_per_page
                            ) );
                        } elseif ( 'clicks' == $order_by && 'desc' == $order ) {
                            $search_products = $wpdb->get_results( $wpdb->prepare(
                                "SELECT search_products_date.product_id, SUM(search_products_date.search_product_clicks) AS search_product_clicks, posts.post_title AS post_title FROM `{$wpdb->prefix}wcsm_search_merchandising_search_products_date` AS search_products_date INNER JOIN `{$wpdb->prefix}posts` AS posts ON search_products_date.product_id = posts.ID WHERE search_products_date.search_product_date BETWEEN %s AND %s AND posts.post_title LIKE %s GROUP BY product_id ORDER BY search_product_clicks DESC LIMIT %d, %d",
                                $dates_from,
                                $dates_to,
                                '%' . $search . '%',
                                $pagination_offset,
                                $pagination_rows_per_page
                            ) );
                        } else {
                            $search_products = $wpdb->get_results( $wpdb->prepare(
                                "SELECT search_products_date.product_id, SUM(search_products_date.search_product_clicks) AS search_product_clicks, posts.post_title AS post_title FROM `{$wpdb->prefix}wcsm_search_merchandising_search_products_date` AS search_products_date INNER JOIN `{$wpdb->prefix}posts` AS posts ON search_products_date.product_id = posts.ID WHERE search_products_date.search_product_date BETWEEN %s AND %s AND posts.post_title LIKE %s GROUP BY product_id ORDER BY search_product_clicks DESC LIMIT %d, %d",
                                $dates_from,
                                $dates_to,
                                '%' . $search . '%',
                                $pagination_offset,
                                $pagination_rows_per_page
                            ) );
                        }
                        
                        $pagination_total = count( $wpdb->get_results( $wpdb->prepare(
                            "SELECT search_products_date.product_id, SUM(search_products_date.search_product_clicks) AS search_product_clicks, posts.post_title AS post_title FROM `{$wpdb->prefix}wcsm_search_merchandising_search_products_date` AS search_products_date INNER JOIN `{$wpdb->prefix}posts` AS posts ON search_products_date.product_id = posts.ID WHERE search_products_date.search_product_date BETWEEN %s AND %s AND posts.post_title LIKE %s GROUP BY product_id ORDER BY search_product_clicks ASC",
                            $dates_from,
                            $dates_to,
                            '%' . $search . '%'
                        ) ) );
                    } else {
                        
                        if ( 'products' == $order_by && 'asc' == $order ) {
                            $search_products = $wpdb->get_results( $wpdb->prepare(
                                "SELECT search_products_date.product_id, SUM(search_products_date.search_product_clicks) AS search_product_clicks, posts.post_title AS post_title FROM `{$wpdb->prefix}wcsm_search_merchandising_search_products_date` AS search_products_date INNER JOIN `{$wpdb->prefix}posts` AS posts ON search_products_date.product_id = posts.ID WHERE search_products_date.search_product_date BETWEEN %s AND %s GROUP BY product_id ORDER BY post_title ASC LIMIT %d, %d",
                                $dates_from,
                                $dates_to,
                                $pagination_offset,
                                $pagination_rows_per_page
                            ) );
                        } elseif ( 'products' == $order_by && 'desc' == $order ) {
                            $search_products = $wpdb->get_results( $wpdb->prepare(
                                "SELECT search_products_date.product_id, SUM(search_products_date.search_product_clicks) AS search_product_clicks, posts.post_title AS post_title FROM `{$wpdb->prefix}wcsm_search_merchandising_search_products_date` AS search_products_date INNER JOIN `{$wpdb->prefix}posts` AS posts ON search_products_date.product_id = posts.ID WHERE search_products_date.search_product_date BETWEEN %s AND %s GROUP BY product_id ORDER BY post_title DESC LIMIT %d, %d",
                                $dates_from,
                                $dates_to,
                                $pagination_offset,
                                $pagination_rows_per_page
                            ) );
                        } elseif ( 'clicks' == $order_by && 'asc' == $order ) {
                            $search_products = $wpdb->get_results( $wpdb->prepare(
                                "SELECT search_products_date.product_id, SUM(search_products_date.search_product_clicks) AS search_product_clicks, posts.post_title AS post_title FROM `{$wpdb->prefix}wcsm_search_merchandising_search_products_date` AS search_products_date INNER JOIN `{$wpdb->prefix}posts` AS posts ON search_products_date.product_id = posts.ID WHERE search_products_date.search_product_date BETWEEN %s AND %s GROUP BY product_id ORDER BY search_product_clicks ASC LIMIT %d, %d",
                                $dates_from,
                                $dates_to,
                                $pagination_offset,
                                $pagination_rows_per_page
                            ) );
                        } elseif ( 'clicks' == $order_by && 'desc' == $order ) {
                            $search_products = $wpdb->get_results( $wpdb->prepare(
                                "SELECT search_products_date.product_id, SUM(search_products_date.search_product_clicks) AS search_product_clicks, posts.post_title AS post_title FROM `{$wpdb->prefix}wcsm_search_merchandising_search_products_date` AS search_products_date INNER JOIN `{$wpdb->prefix}posts` AS posts ON search_products_date.product_id = posts.ID WHERE search_products_date.search_product_date BETWEEN %s AND %s GROUP BY product_id ORDER BY search_product_clicks DESC LIMIT %d, %d",
                                $dates_from,
                                $dates_to,
                                $pagination_offset,
                                $pagination_rows_per_page
                            ) );
                        } else {
                            $search_products = $wpdb->get_results( $wpdb->prepare(
                                "SELECT search_products_date.product_id, SUM(search_products_date.search_product_clicks) AS search_product_clicks, posts.post_title AS post_title FROM `{$wpdb->prefix}wcsm_search_merchandising_search_products_date` AS search_products_date INNER JOIN `{$wpdb->prefix}posts` AS posts ON search_products_date.product_id = posts.ID WHERE search_products_date.search_product_date BETWEEN %s AND %s GROUP BY product_id ORDER BY search_product_clicks DESC LIMIT %d, %d",
                                $dates_from,
                                $dates_to,
                                $pagination_offset,
                                $pagination_rows_per_page
                            ) );
                        }
                        
                        $pagination_total = count( $wpdb->get_results( $wpdb->prepare( "SELECT search_products_date.product_id, SUM(search_products_date.search_product_clicks) AS search_product_clicks, posts.post_title AS post_title FROM `{$wpdb->prefix}wcsm_search_merchandising_search_products_date` AS search_products_date INNER JOIN `{$wpdb->prefix}posts` AS posts ON search_products_date.product_id = posts.ID WHERE search_products_date.search_product_date BETWEEN %s AND %s GROUP BY product_id ORDER BY search_product_clicks ASC", $dates_from, $dates_to ) ) );
                    }
                
                }
            
            }
            
            // Tables
            
            if ( 'terms' == $tab ) {
                $this->pagination(
                    $pagination_paged,
                    $pagination_total,
                    $pagination_rows_per_page,
                    $tab
                );
                ?>
						<table class="widefat fixed striped">
							<thead>
								<tr>
									<th class="sortable<?php 
                esc_html_e( ( 'terms' == $order_by ? ' ' . $order : ' desc' ) );
                ?>" style="width: 200px;">
										<?php 
                
                if ( 'terms' !== $order_by ) {
                    $column_order_link = add_query_arg( array(
                        'order_by' => 'terms',
                        'order'    => 'asc',
                    ) );
                } else {
                    
                    if ( 'asc' == $order ) {
                        $column_order_link = add_query_arg( array(
                            'order_by' => 'terms',
                            'order'    => 'desc',
                        ) );
                    } else {
                        $column_order_link = add_query_arg( array(
                            'order_by' => 'terms',
                            'order'    => 'asc',
                        ) );
                    }
                
                }
                
                ?>
										<a href="<?php 
                echo  esc_url( $column_order_link ) ;
                ?>">
											<span><?php 
                esc_html_e( 'Term', 'wcsm-search-merchandising' );
                ?></span>
											<span class="sorting-indicator"></span>
										</a>
									</th>
									<th class="sortable<?php 
                esc_html_e( ( 'searches' == $order_by ? ' ' . $order : ' desc' ) );
                ?>" style="width: 100px;">
										<?php 
                
                if ( 'searches' !== $order_by ) {
                    $column_order_link = add_query_arg( array(
                        'order_by' => 'searches',
                        'order'    => 'asc',
                    ) );
                } else {
                    
                    if ( 'asc' == $order ) {
                        $column_order_link = add_query_arg( array(
                            'order_by' => 'searches',
                            'order'    => 'desc',
                        ) );
                    } else {
                        $column_order_link = add_query_arg( array(
                            'order_by' => 'searches',
                            'order'    => 'asc',
                        ) );
                    }
                
                }
                
                ?>
										<a href="<?php 
                echo  esc_url( $column_order_link ) ;
                ?>">
											<span><?php 
                esc_html_e( 'Searches', 'wcsm-search-merchandising' );
                ?></span>
											<span class="sorting-indicator"></span>
										</a>
									</th>
									<?php 
                
                if ( 'all' == $dates ) {
                    // Cannot do results average order if dates set as sub query
                    ?>
										<th class="sortable<?php 
                    esc_html_e( ( 'results_average' == $order_by ? ' ' . $order : ' desc' ) );
                    ?>" style="width: 150px;">
											<?php 
                    
                    if ( 'results_average' !== $order_by ) {
                        $column_order_link = add_query_arg( array(
                            'order_by' => 'results_average',
                            'order'    => 'asc',
                        ) );
                    } else {
                        
                        if ( 'asc' == $order ) {
                            $column_order_link = add_query_arg( array(
                                'order_by' => 'results_average',
                                'order'    => 'desc',
                            ) );
                        } else {
                            $column_order_link = add_query_arg( array(
                                'order_by' => 'results_average',
                                'order'    => 'asc',
                            ) );
                        }
                    
                    }
                    
                    ?>
											<a href="<?php 
                    echo  esc_url( $column_order_link ) ;
                    ?>">
												<span><?php 
                    echo  esc_html__( 'Results', 'wcsm-search-merchandising' ) . ' <small>' . esc_html__( '-', 'wcsm-search-merchandising' ) . ' ' . esc_html__( 'Average', 'wcsm-search-merchandising' ) . '</small>' ;
                    ?></span>
												<span class="sorting-indicator"></span>
											</a>
										</th>
									<?php 
                } else {
                    ?>
										<th>
											<?php 
                    echo  esc_html__( 'Results', 'wcsm-search-merchandising' ) . ' <small>' . esc_html__( '-', 'wcsm-search-merchandising' ) . ' ' . esc_html__( 'Average', 'wcsm-search-merchandising' ) . '</small>' ;
                    ?>
										</th>
									<?php 
                }
                
                ?>
									<th><?php 
                esc_html_e( 'Clicks', 'wcsm-search-merchandising' );
                ?></th>
									<th><?php 
                esc_html_e( 'Content', 'wcsm-search-merchandising' );
                ?></th>
									<th><?php 
                esc_html_e( 'Boosts', 'wcsm-search-merchandising' );
                ?> <small><?php 
                echo  esc_html__( '-', 'wcsm-search-merchandising' ) . ' ' . esc_html__( 'If product in results', 'wcsm-search-merchandising' ) ;
                ?></small></th>
									<th><?php 
                esc_html_e( 'Redirect', 'wcsm-search-merchandising' );
                ?> <small><?php 
                echo  esc_html__( '-', 'wcsm-search-merchandising' ) . ' ' . esc_html__( 'Empty for no redirect', 'wcsm-search-merchandising' ) ;
                ?></small></th>
								</tr>
							</thead>
							<tbody>
								<?php 
                
                if ( !empty($search_terms) ) {
                    foreach ( $search_terms as $search_term ) {
                        $search_term_data = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}wcsm_search_merchandising_search_terms WHERE search_term_id = %d;", $search_term->search_term_id ) );
                        ?>
										<tr data-search-term-id="<?php 
                        esc_html_e( $search_term->search_term_id );
                        ?>">
											<td><a href="<?php 
                        echo  esc_url( get_site_url() . '/?s=' . esc_html( $search_term->search_term ) . '&post_type=product' ) ;
                        ?>" target="_blank"><?php 
                        esc_html_e( $search_term->search_term );
                        ?><span class="dashicons dashicons-external"></span></a></td>
											<td><?php 
                        esc_html_e( $search_term->search_term_searches );
                        ?></td>
											<td>
												<?php 
                        
                        if ( 'all' == $dates ) {
                            echo  ( 0 == $search_term->search_term_results_average ? '<span class="wcsm-search-merchandising-red">' : '' ) ;
                            esc_html_e( number_format( $search_term->search_term_results_average, 2 ) );
                            echo  ( 0 == $search_term->search_term_results_average ? '</span>' : '' ) ;
                        } else {
                            $search_terms_date_average = $wpdb->get_results( $wpdb->prepare(
                                "SELECT search_term_searches, search_term_results_average FROM `{$wpdb->prefix}wcsm_search_merchandising_search_terms_date` WHERE search_term_date BETWEEN %s AND %s AND search_term_id = %d",
                                $dates_from,
                                $dates_to,
                                $search_term->search_term_id
                            ) );
                            
                            if ( !empty($search_terms_date_average) ) {
                                $average = 0;
                                $total_term_count = 0;
                                foreach ( $search_terms_date_average as $stda ) {
                                    $average = $average + $stda->search_term_searches * $stda->search_term_results_average;
                                    $total_term_count = $total_term_count + $stda->search_term_searches;
                                }
                                $average = $average / $total_term_count;
                                echo  ( 0 == $average ? '<span class="wcsm-search-merchandising-red">' : '' ) ;
                                echo  number_format( $average, 2 ) ;
                                echo  ( 0 == $average ? '</span>' : '' ) ;
                            }
                        
                        }
                        
                        ?>
											</td>
											<td>
												<?php 
                        
                        if ( 'all' == $dates ) {
                            $search_products = $wpdb->get_results( $wpdb->prepare( "SELECT product_id, search_product_clicks FROM `{$wpdb->prefix}wcsm_search_merchandising_search_products` WHERE search_term_id = %d ORDER BY search_product_clicks DESC", $search_term->search_term_id ) );
                            
                            if ( !empty($search_products) ) {
                                echo  '<div style="overflow-y: scroll; max-height: 100px;">' ;
                                foreach ( $search_products as $search_product ) {
                                    echo  '<a href="' . esc_url( get_permalink( $search_product->product_id ) ) . '" target="_blank">' . esc_html( get_the_title( $search_product->product_id ) ) . '</a> (' . esc_html( $search_product->search_product_clicks ) . ')<br>' ;
                                }
                                echo  '</div>' ;
                            } else {
                                echo  wp_kses_post( $no_data ) ;
                            }
                        
                        } else {
                            $search_products_date = $wpdb->get_results( $wpdb->prepare(
                                "SELECT product_id, SUM( search_product_clicks ) AS search_product_clicks FROM `{$wpdb->prefix}wcsm_search_merchandising_search_products_date` WHERE search_product_date BETWEEN %s AND %s AND search_term_id = %d GROUP BY product_id ORDER BY search_product_clicks DESC",
                                $dates_from,
                                $dates_to,
                                $search_term->search_term_id
                            ) );
                            
                            if ( !empty($search_products_date) ) {
                                echo  '<div style="overflow-y: scroll; max-height: 100px;">' ;
                                foreach ( $search_products_date as $spd ) {
                                    echo  '<a href="' . esc_url( get_permalink( $spd->product_id ) ) . '" target="_blank">' . esc_html( get_the_title( $spd->product_id ) ) . '</a> (' . esc_html( $spd->search_product_clicks ) . ')<br>' ;
                                }
                                echo  '</div>' ;
                            } else {
                                echo  wp_kses_post( $no_data ) ;
                            }
                        
                        }
                        
                        ?>
											</td>
											<td>
												<?php 
                        
                        if ( !empty($content) ) {
                            $search_term_content = ( '' !== $search_term_data[0]->search_term_content ? unserialize( $search_term_data[0]->search_term_content ) : array() );
                            ?>
													<div>
														<label><?php 
                            esc_html_e( 'Before search results', 'wcsm-search-merchandising' );
                            ?><br>
															<select class="wcsm-search-merchandising-search-term-content" data-content-type="before">
																<option value=""><?php 
                            esc_html_e( 'None', 'wcsm-search-merchandising' );
                            ?></option>
																<?php 
                            foreach ( $content as $content_post ) {
                                ?>
																	<option value="<?php 
                                esc_html_e( $content_post->ID );
                                ?>"<?php 
                                echo  ( $content_post->ID == $search_term_content['before'] ? ' selected' : '' ) ;
                                ?>><?php 
                                esc_html_e( $content_post->post_title );
                                ?></option>
																<?php 
                            }
                            ?>
															</select>
														</label>
													</div>
													<div>
														<label><?php 
                            esc_html_e( 'After search results', 'wcsm-search-merchandising' );
                            ?><br>
															<select class="wcsm-search-merchandising-search-term-content" data-content-type="after">
																<option value=""><?php 
                            esc_html_e( 'None', 'wcsm-search-merchandising' );
                            ?></option>
																<?php 
                            foreach ( $content as $content_post ) {
                                ?>
																	<option value="<?php 
                                esc_html_e( $content_post->ID );
                                ?>"<?php 
                                echo  ( $content_post->ID == $search_term_content['after'] ? ' selected' : '' ) ;
                                ?>><?php 
                                esc_html_e( $content_post->post_title );
                                ?></option>
																<?php 
                            }
                            ?>
															</select>
														</label>
													</div>
													<?php 
                        } else {
                            esc_html_e( 'No content added yet.', 'wcsm-search-merchandising' );
                        }
                        
                        ?>
											</td>
											<td>
												<?php 
                        
                        if ( !empty($products) ) {
                            $search_term_boosts = ( !empty($search_term_data[0]->search_term_boosts) ? unserialize( $search_term_data[0]->search_term_boosts ) : array() );
                            ?>
													<label>
														<?php 
                            esc_html_e( 'Select boost products (if required)', 'wcsm-search-merchandising' );
                            ?><br>
														<select class="wcsm-search-merchandising-search-term-boosts" multiple="multiple">
															<?php 
                            foreach ( $products as $product ) {
                                ?>
																<option value="<?php 
                                esc_html_e( $product->ID );
                                ?>"<?php 
                                echo  ( in_array( $product->ID, $search_term_boosts ) ? ' selected' : '' ) ;
                                ?>><?php 
                                esc_html_e( $product->post_title );
                                ?></option>
															<?php 
                            }
                            ?>
														</select>
													</label>
													<?php 
                        } else {
                            esc_html_e( 'No products are setup in your store yet.', 'wcsm-search-merchandising' );
                        }
                        
                        ?>
											</td>
											<td>
												<?php 
                        $search_term_redirect = ( '' !== $search_term_data[0]->search_term_redirect ? $search_term_data[0]->search_term_redirect : '' );
                        ?>
												<label>
													<?php 
                        esc_html_e( 'Enter redirect URL (if required)', 'wcsm-search-merchandising' );
                        ?><br>
													<input type="text" value="<?php 
                        esc_html_e( $search_term_redirect );
                        ?>" class="wcsm-search-merchandising-search-term-redirect">
												</label>
												<button class="wcsm-search-merchandising-search-term-redirect-save button button-primary button-small"><?php 
                        esc_html_e( 'Save', 'wcsm-search-merchandising' );
                        ?></button>
											</td>
										</tr>
										<?php 
                    }
                } else {
                    ?>
									<tr>
										<td colspan="7"><?php 
                    echo  wp_kses_post( $no_data_terms ) ;
                    ?></td>
									</tr>
									<?php 
                }
                
                ?>
							</tbody>
						</table>
						<?php 
                $this->pagination(
                    $pagination_paged,
                    $pagination_total,
                    $pagination_rows_per_page,
                    $tab
                );
            } elseif ( 'products' == $tab ) {
                $this->pagination(
                    $pagination_paged,
                    $pagination_total,
                    $pagination_rows_per_page,
                    $tab
                );
                ?>
						<table class="widefat fixed striped">
							<thead>
								<tr>
									<th class="sortable<?php 
                esc_html_e( ( 'products' == $order_by ? ' ' . $order : ' desc' ) );
                ?>">
										<?php 
                
                if ( 'products' !== $order_by ) {
                    $column_order_link = add_query_arg( array(
                        'order_by' => 'products',
                        'order'    => 'asc',
                    ) );
                } else {
                    
                    if ( 'asc' == $order ) {
                        $column_order_link = add_query_arg( array(
                            'order_by' => 'products',
                            'order'    => 'desc',
                        ) );
                    } else {
                        $column_order_link = add_query_arg( array(
                            'order_by' => 'products',
                            'order'    => 'asc',
                        ) );
                    }
                
                }
                
                ?>
										<a href="<?php 
                echo  esc_url( $column_order_link ) ;
                ?>">
											<span><?php 
                esc_html_e( 'Product', 'wcsm-search-merchandising' );
                ?></span>
											<span class="sorting-indicator"></span>
										</a>
									</th>
									<th class="sortable<?php 
                esc_html_e( ( 'search_product_clicks' == $order_by ? ' ' . $order : ' desc' ) );
                ?>">
										<?php 
                
                if ( 'clicks' !== $order_by ) {
                    $column_order_link = add_query_arg( array(
                        'order_by' => 'clicks',
                        'order'    => 'asc',
                    ) );
                } else {
                    
                    if ( 'asc' == $order ) {
                        $column_order_link = add_query_arg( array(
                            'order_by' => 'clicks',
                            'order'    => 'desc',
                        ) );
                    } else {
                        $column_order_link = add_query_arg( array(
                            'order_by' => 'clicks',
                            'order'    => 'asc',
                        ) );
                    }
                
                }
                
                ?>
										<a href="<?php 
                echo  esc_url( $column_order_link ) ;
                ?>">
											<span><?php 
                esc_html_e( 'Clicks', 'wcsm-search-merchandising' );
                ?></span>
											<span class="sorting-indicator"></span>
										</a>	
									</th>
									<th><?php 
                esc_html_e( 'Terms', 'wcsm-search-merchandising' );
                ?></th>
								</tr>
							</thead>
							<tbody>
								<?php 
                
                if ( !empty($search_products) ) {
                    foreach ( $search_products as $search_product ) {
                        ?>
										<tr>
											<td><a href="<?php 
                        echo  esc_url( get_permalink( $search_product->product_id ) ) ;
                        ?>" target="_blank"><?php 
                        esc_html_e( $search_product->post_title );
                        ?><span class="dashicons dashicons-external"></span></a></td>
											<td><?php 
                        esc_html_e( $search_product->search_product_clicks );
                        ?></td>
											<td style="font-size: 11px;">
												<?php 
                        
                        if ( 'all' == $dates ) {
                            $search_terms = $wpdb->get_results( $wpdb->prepare( "SELECT terms.search_term, products.search_product_clicks FROM `{$wpdb->prefix}wcsm_search_merchandising_search_products` as products INNER JOIN `{$wpdb->prefix}wcsm_search_merchandising_search_terms` as terms ON products.search_term_id = terms.search_term_id WHERE products.product_id = %d ORDER BY search_product_clicks DESC", $search_product->product_id ) );
                        } else {
                            $search_terms = $wpdb->get_results( $wpdb->prepare(
                                "SELECT terms.search_term, SUM( products.search_product_clicks ) AS search_product_clicks FROM `{$wpdb->prefix}wcsm_search_merchandising_search_products_date` as products INNER JOIN `{$wpdb->prefix}wcsm_search_merchandising_search_terms` as terms ON products.search_term_id = terms.search_term_id WHERE products.search_product_date BETWEEN %s AND %s AND products.product_id = %d GROUP BY search_term ORDER BY search_product_clicks DESC",
                                $dates_from,
                                $dates_to,
                                $search_product->product_id
                            ) );
                        }
                        
                        
                        if ( !empty($search_terms) ) {
                            echo  '<div style="overflow-y: scroll; max-height: 50px;">' ;
                            foreach ( $search_terms as $st ) {
                                echo  '<a href="' . esc_url( get_site_url() . '/?s=' . esc_html( $st->search_term ) . '&post_type=product' ) . '" target="_blank">' . esc_html( $st->search_term ) . '</a> (' . esc_html( $st->search_product_clicks ) . ')<br>' ;
                            }
                            echo  '</div>' ;
                        } else {
                            echo  wp_kses_post( $no_data ) ;
                        }
                        
                        ?>
											</td>
										</tr>
										<?php 
                    }
                } else {
                    ?>
									<tr>
										<td colspan="3"><?php 
                    echo  wp_kses_post( $no_data_products ) ;
                    ?></td>
									</tr>
									<?php 
                }
                
                ?>
							</tbody>
						</table>
						<?php 
                $this->pagination(
                    $pagination_paged,
                    $pagination_total,
                    $pagination_rows_per_page,
                    $tab
                );
            } else {
            }
            
            ?>
					<div class="wcsm-search-merchandising-footer">
						<a href="#" class="button"><?php 
            esc_html_e( 'Back to top', 'wcsm-search-merchandising' );
            ?></a>
					</div>
				</div>
			</div>

			<script>

				var picker = new Litepicker({
					element: document.getElementById('wcsm-search-merchandising-dates'),
					singleMode: false,
					delimiter: ' - ',
				});

				jQuery(document).ready(function($) {

					// Save Error Message

					let saveErrorMessage = "<?php 
            esc_html_e( 'Sorry, there was an error while attempting to save, please refresh the page and try again.', 'wcsm-search-merchandising' );
            ?>";

					// Settings
					
					$('#wcsm-search-merchandising-settings-button').on( 'click', function(e) {

						e.preventDefault();
						$( 'html, body' ).animate({ scrollTop: 0 });
						$('#wcsm-search-merchandising-settings').slideDown();
						$(this).fadeOut();

					});

					$('#wcsm-search-merchandising-settings-cancel').on( 'click', function(e) {

						e.preventDefault();
						$('#wcsm-search-merchandising-settings').slideUp();
						$('#wcsm-search-merchandising-settings-button').fadeIn();

					});

					// Content

					$('.wcsm-search-merchandising-search-term-content').select2();

					$('.wcsm-search-merchandising-search-term-content').on( 'change', function(e) {

						var searchTermId = $(this).closest('tr').attr('data-search-term-id');

						$( '#wcsm-search-merchandising-saving' ).fadeIn();

						var data = {
							'action': 'wcsm_search_merchandising_search_term_save_content',
							'search_term_id' : searchTermId,
							'search_term_content': $(this).val(),
							'search_term_content_type': $(this).attr( 'data-content-type' ),
							'nonce': '<?php 
            echo  esc_html( wp_create_nonce( 'wcsm_search_merchandising_search_term_save_content' ) ) ;
            ?>',
						};

						$.post( '<?php 
            echo  esc_html( admin_url( 'admin-ajax.php' ) ) ;
            ?>', data, function( response ) {

							if ( response == '1' ) {

								$( '#wcsm-search-merchandising-saving' ).fadeOut();

							} else {

								alert( saveErrorMessage );
								$( '#wcsm-search-merchandising-saving' ).fadeOut();

							}

						});

					});

					// Boosts

					$('.wcsm-search-merchandising-search-term-boosts').select2();

					$('.wcsm-search-merchandising-search-term-boosts').on( 'change', function (e) {

						var searchTermId = $(this).closest('tr').attr('data-search-term-id');

						$( '#wcsm-search-merchandising-saving' ).fadeIn();

						var data = {
							'action': 'wcsm_search_merchandising_search_term_save_boosts',
							'search_term_id' : searchTermId,
							'search_term_boosts': $(this).val(),
							'nonce': '<?php 
            echo  esc_html( wp_create_nonce( 'wcsm_search_merchandising_search_term_save_boosts' ) ) ;
            ?>',
						};

						$.post( '<?php 
            echo  esc_html( admin_url( 'admin-ajax.php' ) ) ;
            ?>', data, function( response ) {

							if ( response == '1' ) {

								$( '#wcsm-search-merchandising-saving' ).fadeOut();

							} else {

								alert( saveErrorMessage );
								$( '#wcsm-search-merchandising-saving' ).fadeOut();

							}		

						});

					});

					// Redirect

					$('.wcsm-search-merchandising-search-term-redirect').on( 'input', function(e) {

						$(this).closest('td').find('.wcsm-search-merchandising-search-term-redirect-save').fadeIn();

					});

					$('.wcsm-search-merchandising-search-term-redirect-save').on( 'click', function(e) {

						let searchTermRedirectSave = $(this);
						var searchTermId = $(this).closest('tr').attr('data-search-term-id');
						var searchTermRedirect = $(this).closest('tr').find('.wcsm-search-merchandising-search-term-redirect').val();

						$( '#wcsm-search-merchandising-saving' ).fadeIn();

						var data = {
							'action': 'wcsm_search_merchandising_search_term_save_redirect',
							'search_term_id' : searchTermId,
							'search_term_redirect': searchTermRedirect,
							'nonce': '<?php 
            echo  esc_html( wp_create_nonce( 'wcsm_search_merchandising_search_term_save_redirect' ) ) ;
            ?>',
						};

						$.post( '<?php 
            echo  esc_html( admin_url( 'admin-ajax.php' ) ) ;
            ?>', data, function( response ) {

							disableFieldsIfRedirect();

							if ( response == '1' ) {

								searchTermRedirectSave.fadeOut();
								$( '#wcsm-search-merchandising-saving' ).fadeOut();

							} else {

								alert( saveErrorMessage );
								$( '#wcsm-search-merchandising-saving' ).fadeOut();

							}

						});

					});

					// Disable Fields if Redirect Used

					function disableFieldsIfRedirect() {

						$( '.wcsm-search-merchandising-search-term-redirect' ).each( function( index ) {

							if ( $(this).val() !== '' ) {

								$(this).closest('tr').find('.wcsm-search-merchandising-search-term-content').attr( 'disabled', true );
								$(this).closest('tr').find('.wcsm-search-merchandising-search-term-boosts').attr( 'disabled', true );

							} else {

								$(this).closest('tr').find('.wcsm-search-merchandising-search-term-content').attr( 'disabled', false );
								$(this).closest('tr').find('.wcsm-search-merchandising-search-term-boosts').attr( 'disabled', false );

							}

						});

					}

					disableFieldsIfRedirect();

				});

			</script>

			<?php 
        }
        
        public function pagination(
            $pagination_paged,
            $pagination_total,
            $pagination_rows_per_page,
            $tab
        )
        {
            $pagination_pages_total = ceil( $pagination_total / $pagination_rows_per_page );
            
            if ( $pagination_pages_total > 0 ) {
                echo  '<div class="wcsm-search-merchandising-pagination">' ;
                echo  '<div class="wcsm-search-merchandising-pagination-summary">' . esc_html__( 'Page', 'wcsm-search-merchandising' ) . ' <strong>' . esc_html( $pagination_paged ) . '</strong> ' . esc_html__( 'of', 'wcsm-search-merchandising' ) . ' <strong>' . esc_html( $pagination_pages_total ) . '</strong> ' . esc_html__( '-', 'wcsm-search-merchandising' ) . ' ' . esc_html__( 'Total', 'wcsm-search-merchandising' ) . ' ' . esc_html( $tab ) . esc_html__( ':', 'wcsm-search-merchandising' ) . ' <strong>' . esc_html( $pagination_total ) . '</strong></div>' ;
                $args = array(
                    'base'      => add_query_arg( 'paged', '%#%' ),
                    'format'    => '',
                    'prev_text' => __( '&laquo;' ),
                    'next_text' => __( '&raquo;' ),
                    'total'     => esc_html( $pagination_pages_total ),
                    'current'   => esc_html( $pagination_paged ),
                );
                $paginate_links = paginate_links( $args );
                $pagination = str_replace( 'class="', 'class="button ', $paginate_links );
                $pagination = str_replace( 'current', 'current button-primary', $pagination );
                echo  '<div class="wcsm-search-merchandising-pagination-pages">' . wp_kses_post( $pagination ) . '</div>' ;
                echo  '</div>' ;
            }
        
        }
        
        public function save_content()
        {
            $return = '0';
            if ( isset( $_POST['nonce'] ) ) {
                if ( wp_verify_nonce( sanitize_text_field( $_POST['nonce'] ), 'wcsm_search_merchandising_search_term_save_content' ) ) {
                    
                    if ( isset( $_POST['search_term_id'] ) && isset( $_POST['search_term_content'] ) && isset( $_POST['search_term_content_type'] ) ) {
                        global  $wpdb ;
                        $search_term_id = sanitize_text_field( $_POST['search_term_id'] );
                        $search_term_content_type = sanitize_text_field( $_POST['search_term_content_type'] );
                        // Get existing search term content
                        $existing_search_term_content = $wpdb->get_results( $wpdb->prepare( "SELECT search_term_content FROM `{$wpdb->prefix}wcsm_search_merchandising_search_terms` WHERE `search_term_id` = %d;", $search_term_id ) );
                        $search_term_content = ( !empty($existing_search_term_content) ? unserialize( $existing_search_term_content[0]->search_term_content ) : array() );
                        $search_term_content[$search_term_content_type] = sanitize_text_field( $_POST['search_term_content'] );
                        foreach ( $search_term_content as $stck => $stcv ) {
                            if ( empty($stcv) ) {
                                unset( $search_term_content[$stck] );
                            }
                        }
                        $search_term_content = ( !empty($search_term_content) ? serialize( $search_term_content ) : '' );
                        $query = $wpdb->query( $wpdb->prepare( "UPDATE `{$wpdb->prefix}wcsm_search_merchandising_search_terms` SET `search_term_content` = %s WHERE `{$wpdb->prefix}wcsm_search_merchandising_search_terms`.`search_term_id` = %d;", $search_term_content, $search_term_id ) );
                        if ( false !== $query && 0 !== $query ) {
                            // Not error (false) and not 0 (no rows updated)
                            $return = '1';
                        }
                    }
                
                }
            }
            esc_html_e( $return );
            exit;
        }
        
        public function save_boosts()
        {
            $return = '0';
            if ( isset( $_POST['nonce'] ) ) {
                if ( wp_verify_nonce( sanitize_text_field( $_POST['nonce'] ), 'wcsm_search_merchandising_search_term_save_boosts' ) ) {
                    
                    if ( isset( $_POST['search_term_id'] ) && isset( $_POST['search_term_boosts'] ) ) {
                        global  $wpdb ;
                        $search_term_id = sanitize_text_field( $_POST['search_term_id'] );
                        $search_term_boosts = ( !empty($_POST['search_term_boosts']) ? serialize( map_deep( $_POST['search_term_boosts'], 'wp_kses_post' ) ) : '' );
                        $query = $wpdb->query( $wpdb->prepare( "UPDATE `{$wpdb->prefix}wcsm_search_merchandising_search_terms` SET `search_term_boosts` = %s WHERE `{$wpdb->prefix}wcsm_search_merchandising_search_terms`.`search_term_id` = %d;", $search_term_boosts, $search_term_id ) );
                        if ( false !== $query && 0 !== $query ) {
                            // Not error (false) and not 0 (no rows updated)
                            $return = '1';
                        }
                    }
                
                }
            }
            esc_html_e( $return );
            exit;
        }
        
        public function save_redirect()
        {
            $return = '0';
            if ( isset( $_POST['nonce'] ) ) {
                if ( wp_verify_nonce( sanitize_text_field( $_POST['nonce'] ), 'wcsm_search_merchandising_search_term_save_redirect' ) ) {
                    
                    if ( isset( $_POST['search_term_id'] ) && isset( $_POST['search_term_redirect'] ) ) {
                        global  $wpdb ;
                        $search_term_id = sanitize_text_field( $_POST['search_term_id'] );
                        $search_term_redirect = sanitize_text_field( $_POST['search_term_redirect'] );
                        $query = $wpdb->query( $wpdb->prepare( "UPDATE `{$wpdb->prefix}wcsm_search_merchandising_search_terms` SET `search_term_redirect` = %s WHERE `{$wpdb->prefix}wcsm_search_merchandising_search_terms`.`search_term_id` = %d;", $search_term_redirect, $search_term_id ) );
                        if ( false !== $query && 0 !== $query ) {
                            // Not error (false) and not 0 (no rows updated)
                            $return = '1';
                        }
                    }
                
                }
            }
            esc_html_e( $return );
            exit;
        }
        
        public function save_settings()
        {
            if ( is_admin() ) {
                if ( isset( $_POST['wcsm_search_merchandising_settings_save_nonce'] ) && wp_verify_nonce( sanitize_text_field( $_POST['wcsm_search_merchandising_settings_save_nonce'] ), 'wcsm_search_merchandising_settings_save' ) ) {
                    
                    if ( isset( $_POST['wcsm_search_merchandising_settings_save'] ) ) {
                        // Ensures the form is being saved, otherwise the non isset conditions below would trigger without settings form being submitted
                        global  $wpdb ;
                        // Rows Per Page
                        if ( isset( $_POST['wcsm_search_merchandising_settings_rows_per_page'] ) ) {
                            update_option( 'wcsm_search_merchandising_rows_per_page', sanitize_text_field( $_POST['wcsm_search_merchandising_settings_rows_per_page'] ) );
                        }
                        // Disable Tracking User Roles
                        
                        if ( isset( $_POST['wcsm_search_merchandising_settings_disable_tracking_user_roles'] ) ) {
                            update_option( 'wcsm_search_merchandising_disable_tracking_user_roles', map_deep( $_POST['wcsm_search_merchandising_settings_disable_tracking_user_roles'], 'wp_kses_post' ) );
                        } else {
                            update_option( 'wcsm_search_merchandising_disable_tracking_user_roles', '' );
                        }
                        
                        // Delete Data on Uninstall
                        
                        if ( isset( $_POST['wcsm_search_merchandising_settings_delete_data_on_uninstall'] ) ) {
                            update_option( 'wcsm_search_merchandising_delete_data_on_uninstall', 'yes' );
                        } else {
                            update_option( 'wcsm_search_merchandising_delete_data_on_uninstall', 'no' );
                        }
                        
                        // Reset Data
                        
                        if ( isset( $_POST['wcsm_search_merchandising_settings_reset_data'] ) ) {
                            $wpdb->query( "TRUNCATE `{$wpdb->prefix}wcsm_search_merchandising_search_products`;" );
                            $wpdb->query( "TRUNCATE `{$wpdb->prefix}wcsm_search_merchandising_search_products_date`;" );
                            $wpdb->query( "TRUNCATE `{$wpdb->prefix}wcsm_search_merchandising_search_terms`;" );
                            $wpdb->query( "TRUNCATE `{$wpdb->prefix}wcsm_search_merchandising_search_terms_date`;" );
                        }
                        
                        // Add Notice
                        add_action( 'admin_notices', function () {
                            ?>
							<div class="notice notice-success is-dismissible">
								<p><?php 
                            esc_html_e( 'Settings saved.', 'wcsm-search-merchandising' );
                            ?></p>
							</div>
							<?php 
                        } );
                    }
                
                }
            }
        }
    
    }
}