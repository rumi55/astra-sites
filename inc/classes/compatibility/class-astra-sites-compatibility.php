<?php
/**
 * Astra Sites Compatibility for 3rd party plugins.
 *
 * @package Astra Sites
 * @since 1.0.4
 */

if ( ! class_exists( 'Astra_Sites_Compatibility' ) ) :

	/**
	 * Astra Sites Compatibility
	 *
	 * @since 1.0.4
	 */
	class Astra_Sites_Compatibility {

		/**
		 * Instance
		 *
		 * @access private
		 * @var object Class object.
		 * @since 1.0.4
		 */
		private static $instance;

		/**
		 * Initiator
		 *
		 * @since 1.0.4
		 * @return object initialized object of class.
		 */
		public static function instance() {
			if ( ! isset( self::$instance ) ) {
				self::$instance = new self;
			}
			return self::$instance;
		}

		/**
		 * Constructor
		 *
		 * @since 1.0.4
		 */
		public function __construct() {

			// Background Processing.
			require_once ASTRA_SITES_DIR . 'inc/classes/vendor/wp-async-request.php';
			require_once ASTRA_SITES_DIR . 'inc/classes/vendor/wp-background-process.php';

			// Plugin Compatibility files.
			// 
			// Plugin - Astra Pro
			require_once ASTRA_SITES_DIR . 'inc/classes/compatibility/astra-pro/class-astra-sites-compatibility-astra-pro.php';

			// Plugin - Site Origin Widgets.
			require_once ASTRA_SITES_DIR . 'inc/classes/compatibility/so-widgets-bundle/class-astra-sites-compatibility-so-widgets.php';

			// Plugin - Elementor.
			require_once ASTRA_SITES_DIR . 'inc/classes/compatibility/elementor/elementor.php';
		}


	}

	/**
	 * Kicking this off by calling 'instance()' method
	 */
	Astra_Sites_Compatibility::instance();

endif;
