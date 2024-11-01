<?php

if ( !defined( 'ABSPATH' ) ) {
	exit;
}

if ( !class_exists( 'WCSM_Search_Merchandising_Activation' ) ) {

	class WCSM_Search_Merchandising_Activation {

		public function __construct() {

			register_activation_hook( plugin_dir_path( __DIR__ ) . 'wcsm-search-merchandising.php', array( $this, 'install' ) );

		}

		public function install() {

			WCSM_Search_Merchandising_Upgrade::upgrade();

		}

	}

}
