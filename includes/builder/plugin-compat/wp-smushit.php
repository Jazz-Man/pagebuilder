<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Plugin compatibility for Smush
 *
 * @since 3.17.1
 *
 * @link https://wordpress.org/plugins/wp-smushit/
 */
class ET_Builder_Plugin_Compat_Smush extends ET_Builder_Plugin_Compat_Base {
	/**
	 * Constructor
	 */
	public function __construct() {
		$this->plugin_id = 'wp-smushit/wp-smush.php';
		$this->init_hooks();
	}

	/**
	 * Hook methods to WordPress
	 *
	 * Latest plugin version: 1601
	 *
	 * @return void
	 */
	public function init_hooks() {
		// Bail if there's no version found
		if ( ! $this->get_plugin_version() ) {
			return;
		}
		add_filter( 'wp_smush_enqueue', array( $this, 'wp_smush_enqueue' ) );
	}

	public function wp_smush_enqueue() {
		return function_exists( 'current_screen' );
	}
}

new ET_Builder_Plugin_Compat_Smush();
