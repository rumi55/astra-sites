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
		$show_on_front      = $options['show_on_front'];
		$page_on_front      = get_page_by_title( $options['page_on_front'] );
		$page_for_posts     = get_page_by_title( $options['page_for_posts'] );

		// Update site options.
		update_option( 'show_on_front', $show_on_front );
		update_option( 'page_on_front', $page_on_front->ID );
		update_option( 'page_for_posts', $page_for_posts->ID );

		$this->set_nav_menu_locations( $options['nav_menu_locations'] );
	}

	/**
	 * In WP nav menu is stored as ( 'menu_location' => 'menu_id' );
	 * In export we send 'menu_slug' like ( 'menu_location' => 'menu_slug' );
	 * In import we set 'menu_id' from menu slug like ( 'menu_location' => 'menu_id' );
	 */
	function set_nav_menu_locations( $nav_menu_locations = array() ) {
		
		$menu_locations = array();

		// Update menu locations.
		foreach ( $nav_menu_locations as $menu => $value ) {

			$term = get_term_by( 'slug', $value, 'nav_menu' );

			if ( is_object( $term ) ) {
				$menu_locations[ $menu ] = $term->term_id;
			}
		}

		set_theme_mod( 'nav_menu_locations', $menu_locations );
	}

}