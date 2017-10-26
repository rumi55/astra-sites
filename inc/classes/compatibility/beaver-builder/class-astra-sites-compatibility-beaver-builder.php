<?php
/**
 * Astra Sites Beaver Builder
 *
 * @package Astra Sites
 * @since 1.0.11
 */

// If plugin - 'Builder Builder' not exist then return.
if ( ! class_exists( 'FLBuilderModel' ) ) {
	return;
}

if ( ! class_exists( 'Astra_Sites_Compatibility_Beaver_Builder' ) ) :

	/**
	 * Astra_Sites_Compatibility_Beaver_Builder
	 *
	 * @since 1.0.11
	 */
	class Astra_Sites_Compatibility_Beaver_Builder {

		/**
		 * All Processes.
		 *
		 * @since 1.0.11
		 *
		 * @access protected
		 * @var $process_all
		 */
		protected $process_all;

		/**
		 * Instance
		 *
		 * @since 1.0.11
		 *
		 * @access private
		 * @var object Class object.
		 */
		private static $instance;

		/**
		 * Initiator
		 *
		 * @since 1.0.11
		 * @return object initialized object of class.
		 */
		public static function set_instance() {
			if ( ! isset( self::$instance ) ) {
				self::$instance = new self;
			}
			return self::$instance;
		}

		/**
		 * Constructor
		 *
		 * @since 1.0.11
		 */
		public function __construct() {

			if ( ini_get( 'allow_url_fopen' ) ) {

				// BB Import Image Compatibility.
				require_once ASTRA_SITES_DIR . 'inc/classes/compatibility/beaver-builder/class-astra-sites-compatibility-beaver-builder-importer.php';

				// Background Processing.
				require_once ASTRA_SITES_DIR . 'inc/classes/compatibility/beaver-builder/background-processing/class-astra-beaver-builder-image-importer-process.php';

				$this->process_all = new Astra_Beaver_Builder_Image_Importer_Process();

				// Start image importing after site import complete.
				add_action( 'astra_sites_import_complete', array( $this, 'start_image_import' ) );
			}

		}

		

	}

	/**
	 * Kicking this off by calling 'set_instance()' method
	 */
	Astra_Sites_Compatibility_Beaver_Builder::set_instance();

endif;
