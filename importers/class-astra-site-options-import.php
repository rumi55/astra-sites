<?php

class Astra_Site_Options_Importer {

	private static $_instance = null;

	public static function instance() {
		if ( ! isset( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

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
