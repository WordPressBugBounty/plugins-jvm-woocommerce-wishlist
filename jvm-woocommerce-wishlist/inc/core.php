<?php

// default codes for our plugins
if ( ! class_exists( 'Codeixer_Plugin_Core' ) ) {
	class Codeixer_Plugin_Core {
		public function __construct() {
			add_action( 'admin_enqueue_scripts', array( $this, 'codeixer_admin_scripts' ) );
			add_action( 'admin_menu', array( $this, 'codeixer_admin_menu' ) );
			add_action( 'admin_menu', array( $this, 'later' ), 99 );
		}

		public function codeixer_admin_scripts() {
			wp_enqueue_style( 'ci-admin', CIXWW_ASSETS . '/css/ci-admin.css' );
		}
		public function later() {
			/* === Remove Codeixer Sub-Links === */
			remove_submenu_page( 'codeixer', 'codeixer' );
		}

		public function codeixer_admin_menu() {
			add_menu_page( 'Codeixer', 'Codeixer', 'manage_options', 'codeixer', null, 'dashicons-codeixer', 60 );
			// * == License Activation Page ==

			do_action( 'codeixer_sub_menu' );
		}

		public function codeixer_license() {
			?>

			<h2>Codeixer License Settings</h2>


		<!-- <p class="about-description">Enter your Purchase key here, to activate the product, and get full feature updates and premium support.</p> -->


			<?php
			do_action( 'codeixer_license_form' );
			do_action( 'codeixer_license_data' );
		}
	}
	if ( apply_filters( 'bayna_is_from_bp', false ) == false ) {
		new Codeixer_Plugin_Core();
	}
}
