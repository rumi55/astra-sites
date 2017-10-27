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

			// Core Helpers - Image.
			// @todo 	This file is required for Elementor.
			// 			Once we implement our logic for updating elementor data then we'll delete this file.
			require_once ABSPATH . 'wp-admin/includes/image.php';

			// Core Helpers - Image Downloader.
			require_once ASTRA_SITES_DIR . 'inc/importers/batch-processing/helpers/class-astra-image-importer.php';
			
			// Core Helpers - Batch Processing.
			require_once ASTRA_SITES_DIR . 'inc/importers/batch-processing/helpers/wp-async-request.php';
			require_once ASTRA_SITES_DIR . 'inc/importers/batch-processing/helpers/wp-background-process.php';
			require_once ASTRA_SITES_DIR . 'inc/importers/batch-processing/helpers/wp-background-process-astra.php';

			// Prepare Widgets.
			require_once ASTRA_SITES_DIR . 'inc/importers/batch-processing/class-astra-sites-batch-processing-widgets.php';
			require_once ASTRA_SITES_DIR . 'inc/importers/batch-processing/class-astra-sites-batch-processing-beaver-builder.php';
			require_once ASTRA_SITES_DIR . 'inc/importers/batch-processing/class-astra-sites-batch-processing-elementor.php';

			self::$process_all = new WP_Background_Process_Astra();

			// Start image importing after site import complete.
			add_action( 'astra_sites_import_complete', 	array( $this, 'start_process' ) );
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
			if( class_exists( 'Astra_Sites_Batch_Processing_Widgets' ) )
			{
				self::$process_all->push_to_queue( Astra_Sites_Batch_Processing_Widgets::set_instance() );
			}

			// Add "bb-plugin" in import [queue].
			// Add "beaver-builder-lite-version" in import [queue].
			if ( is_plugin_active( 'beaver-builder-lite-version/fl-builder.php' ) || is_plugin_active( 'bb-plugin/fl-builder.php' ) )
			{
				if( class_exists( 'Astra_Sites_Batch_Processing_Beaver_Builder' ) )
				{
					self::$process_all->push_to_queue( Astra_Sites_Batch_Processing_Beaver_Builder::set_instance() );
				}
			}

			// Add "elementor" in import [queue].
			if ( is_plugin_active( 'elementor/elementor.php' ) )
			{
				if( class_exists( '\Elementor\TemplateLibrary\Astra_Sites_Source_Remote' ) )
				{
					$import = new \Elementor\TemplateLibrary\Astra_Sites_Source_Remote();
					self::$process_all->push_to_queue( $import );
				}
			}


			// Dispatch Queue.
			self::$process_all->save()->dispatch();
		}

		/**
		 * Get Page IDs
		 *
		 * @since 1.0.11
		 *
		 * @return array
		 */
		public static function get_pages() {

			$args = array(
				'post_type'    => 'page',

				// Query performance optimization.
				'fields'        => 'ids',
				'no_found_rows' => true,
				'post_status'   => 'publish',
			);

			$query = new WP_Query( $args );

			// Have posts?
			if ( $query->have_posts() ) :

				return $query->posts;

			endif;

			return null;
		}

	}

	/**
	 * Kicking this off by calling 'set_instance()' method
	 */
	Astra_Sites_Batch_Processing::set_instance();

endif;
// 


