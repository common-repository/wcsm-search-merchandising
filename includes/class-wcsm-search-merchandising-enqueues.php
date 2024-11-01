<?php

if ( !defined( 'ABSPATH' ) ) {
    exit;
}
if ( !class_exists( 'WCSM_Search_Merchandising_Enqueues' ) ) {
    class WCSM_Search_Merchandising_Enqueues
    {
        public function __construct()
        {
            add_action( 'admin_enqueue_scripts', array( $this, 'admin' ) );
        }
        
        public function admin()
        {
            global  $pagenow ;
            wp_enqueue_style(
                'wcsm-search-merchandising-admin-free',
                plugin_dir_url( __DIR__ ) . 'assets/css/admin-free.css',
                array(),
                WCSM_SEARCH_MERCHANDISING_VERSION,
                'all'
            );
            if ( 'admin.php' == $pagenow ) {
                if ( isset( $_GET['page'] ) ) {
                    
                    if ( 'wcsm-search-merchandising' == $_GET['page'] ) {
                        $this->litepicker();
                        $this->select2();
                    }
                
                }
            }
        }
        
        public function litepicker()
        {
            wp_enqueue_script(
                'wcsm-search-merchandising-litepicker',
                plugin_dir_url( __DIR__ ) . 'libraries/litepicker/dist/js/main.js',
                array(),
                WCSM_SEARCH_MERCHANDISING_VERSION
            );
        }
        
        public function select2()
        {
            wp_enqueue_script(
                'wcsm-search-merchandising-select2',
                plugin_dir_url( __DIR__ ) . 'libraries/select2/dist/js/select2.min.js',
                array(),
                WCSM_SEARCH_MERCHANDISING_VERSION
            );
            wp_enqueue_style(
                'wcsm-search-merchandising-select2',
                plugin_dir_url( __DIR__ ) . 'libraries/select2/dist/css/select2.min.css',
                array(),
                WCSM_SEARCH_MERCHANDISING_VERSION,
                'all'
            );
        }
    
    }
}