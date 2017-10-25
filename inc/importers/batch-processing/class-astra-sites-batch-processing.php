<?php
/**
 * Batch Processing
 *
 * @package Astra Sites
 * @since 1.0.0
 */

if( ! class_exists( 'Astra_Sites_Batch_Processing' ) ) :

	/**
	 * Astra_Sites_Batch_Processing
	 *
	 * @since 1.0.0
	 */
	class Astra_Sites_Batch_Processing {

		/**
		 * Instance
		 *
		 * @access private
		 * @since 1.0.0
		 */
		private static $instance;

		public static $process_all;

		/**
		 * Initiator
		 *
		 * @since 1.0.0
		 * @return object initialized object of class.
		 */
		public static function set_instance(){
			if ( ! isset( self::$instance ) ) {
				self::$instance = new self;
			}
			return self::$instance;
		}

		/**
		 * Constructor
		 *
		 * @since 1.0.0
		 */
		public function __construct() {

			if ( ! ini_get( 'allow_url_fopen' ) ) {
				return;
			}

			// Core Helpers - Image Downloader.
			require_once ASTRA_SITES_DIR . 'inc/importers/batch-processing/helpers/class-astra-image-downloader.php';
			
			// Core Helpers - Batch Processing.
			require_once ASTRA_SITES_DIR . 'inc/importers/batch-processing/helpers/wp-async-request.php';
			require_once ASTRA_SITES_DIR . 'inc/importers/batch-processing/helpers/wp-background-process.php';
			require_once ASTRA_SITES_DIR . 'inc/importers/batch-processing/helpers/class-astra-image-downloader-process.php';

			// Prepare Widgets.
			require_once ASTRA_SITES_DIR . 'inc/importers/batch-processing/class-astra-sites-batch-processing-widgets.php';

			self::$process_all = new Astra_Sites_Image_Imorter_Process();

			// Start image importing after site import complete.
			add_action( 'astra_sites_import_complete', array( $this, 'start_process' ) );
			// add_action( 'admin_head', array( $this, 'start_process' ) );
		}

		/**
		 * Start Image Import
		 *
		 * @since 1.0.11
		 *
		 * @param  array $data Site API Data.
		 * @return void
		 */
		public function start_process( $data ) {

			// Add "widget" in import [queue].
			self::$process_all->push_to_queue( Astra_Sites_Batch_Processing_Widgets::set_instance() );

			// // Add "elementor" in import [queue].
			// self::$process_all->push_to_queue( Astra_Sites_Batch_Processing_Widgets::set_instance() );

			// // Add "bb-plugin" in import [queue].
			// self::$process_all->push_to_queue( Astra_Sites_Batch_Processing_Widgets::set_instance() );

			// Dispatch Queue.
			self::$process_all->save()->dispatch();
		}

	}

	/**
	 * Kicking this off by calling 'set_instance()' method
	 */
	Astra_Sites_Batch_Processing::set_instance();

endif;
// 


