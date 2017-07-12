<?php
/**
 * Customizer Site options importer class.
 *
 * @since  1.0.0
 * @package Astra Addon
 */

defined( 'ABSPATH' ) or exit;

/**
 * Customizer Site options importer class.
 *
 * @since  1.0.0
 */
class Astra_Site_Options_Import {

	/**
	 * Instance of Astra_Site_Options_Importer
	 *
	 * @since  1.0.0
	 * @var (Object) Astra_Site_Options_Importer
	 */
	private static $_instance = null;

	/**
	 * Instanciate Astra_Site_Options_Importer
	 *
	 * @since  1.0.0
	 * @return (Object) Astra_Site_Options_Importer
	 */
	public static function instance() {
		if ( ! isset( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	/**
	 * Import site options.
	 *
	 * @since  1.0.0
	 *
	 * @param  (Array) $options Array of site options to be imported from the demo.
	 */
	public function import_options( $options ) {
		$show_on_front    = $options['show_on_front'];
		$page_on_front    = get_page_by_title( $options['page_on_front'] );
		$page_for_posts   = get_page_by_title( $options['page_for_posts'] );
		$registered_menus = $options['registered_menus'];

		// Update site options.
		update_option( 'show_on_front', $show_on_front );
		update_option( 'page_on_front', $page_on_front->ID );
		update_option( 'page_for_posts', $page_for_posts->ID );

		// Update menu locations.
		foreach ( $registered_menus as $menu => $value ) {

			$term = get_term_by( 'name', $value, 'nav_menu' );

			if ( is_object( $term ) ) {
				$registered_menus[ $menu ] = $term->id;
			}
		}

		set_theme_mod( 'nav_menu_locations', $registered_menus );
	}

}
