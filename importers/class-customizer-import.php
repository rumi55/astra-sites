<?php

/**
 * Widget Data exporter class.
 *
 * @see - https://wordpress.org/plugins/widget-importer-exporter/
 */
class Astra_Customizer_Import {

	/**
	 * Instance of Astra_Widget_Importer
	 *
	 * @var Astra_Widget_Importer
	 */
	private static $_instance = null;

	public static function instance() {

		if ( ! isset( self::$_instance ) ) {
			self::$_instance = new self;
		}

		return self::$_instance;
	}

	public function import( $data ) {
		update_option( AST_THEME_SETTINGS, $data );
	}
}