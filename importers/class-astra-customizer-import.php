<?php
/**
 * Customizer Data importer class.
 *
 * @since  1.0.0
 * @package Astra Addon
 */

defined( 'ABSPATH' ) or exit;

/**
 * Customizer Data importer class.
 *
 * @since  1.0.0
 */
class Astra_Customizer_Import {

	/**
	 * Instance of Astra_Customizer_Import
	 *
	 * @since  1.0.0
	 * @var Astra_Customizer_Import
	 */
	private static $_instance = null;

	/**
	 * Instantiate Astra_Customizer_Import
	 *
	 * @since  1.0.0
	 * @return (Object) Astra_Customizer_Import
	 */
	public static function instance() {

		if ( ! isset( self::$_instance ) ) {
			self::$_instance = new self;
		}

		return self::$_instance;
	}

	/**
	 * Import customizer options.
	 *
	 * @since  1.0.0
	 *
	 * @param  (Array) $options customizer options from the demo.
	 */
	public function import( $options ) {

		// Update Astra Theme customizer settings.
		update_option( 'astra-settings', $options['astra-settings'] );

		// Add Custom CSS.
		wp_update_custom_css_post( $options['custom-css'] );

	}
}
