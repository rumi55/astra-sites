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
			require_once ASTRA_SITES_DIR . 'classes/compatibility/so-widgets-bundle/class-astra-sites-compatibility-so-widgets.php';
			add_action( 'plugins_loaded', array( $this, 'plugins_loaded' ) );
		}

		function plugins_loaded() {
			require_once ASTRA_SITES_DIR . 'classes/compatibility/elementor/elementor.php';
			require_once ASTRA_SITES_DIR . 'classes/compatibility/elementor/helper.php';
			require_once ASTRA_SITES_DIR . 'classes/compatibility/elementor/importer.php';
		}

	}

	/**
	 * Kicking this off by calling 'instance()' method
	 */
	Astra_Sites_Compatibility::instance();

endif;
