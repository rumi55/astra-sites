<?php
/**
 * Batch Processing
 *
 * @package Astra Sites
 * @since 1.0.14
 */

if ( ! class_exists( 'Astra_Sites_Batch_Processing' ) ) :

	/**
	 * Astra_Sites_Batch_Processing
	 *
	 * @since 1.0.14
	 */
	class Astra_Sites_Batch_Processing {

		/**
		 * Instance
		 *
		 * @since 1.0.14
		 * @var object Class object.
		 * @access private
		 */
		private static $instance;

		/**
		 * Process All
		 *
		 * @since 1.0.14
		 * @var object Class object.
		 * @access public
		 */
		public static $process_all;

		/**
		 * Initiator
		 *
		 * @since 1.0.14
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
		 * @since 1.0.14
		 */
		public function __construct() {

			// Core Helpers - Image.
			// @todo 	This file is required for Elementor.
			// Once we implement our logic for updating elementor data then we'll delete this file.
			require_once ABSPATH . 'wp-admin/includes/image.php';

			// Core Helpers - Image Downloader.
			require_once ASTRA_SITES_DIR . 'inc/importers/batch-processing/helpers/class-astra-sites-image-importer.php';

			// Core Helpers - Batch Processing.
			require_once ASTRA_SITES_DIR . 'inc/importers/batch-processing/helpers/class-wp-async-request.php';
			require_once ASTRA_SITES_DIR . 'inc/importers/batch-processing/helpers/class-wp-background-process.php';
			require_once ASTRA_SITES_DIR . 'inc/importers/batch-processing/helpers/class-wp-background-process-astra.php';

			// Prepare Widgets.
			require_once ASTRA_SITES_DIR . 'inc/importers/batch-processing/class-astra-sites-batch-processing-widgets.php';
			require_once ASTRA_SITES_DIR . 'inc/importers/batch-processing/class-astra-sites-batch-processing-beaver-builder.php';
			require_once ASTRA_SITES_DIR . 'inc/importers/batch-processing/class-astra-sites-batch-processing-elementor.php';

			self::$process_all = new WP_Background_Process_Astra();

			// Start image importing after site import complete.
			add_filter( 'astra_sites_image_importer_skip_image' , array( $this, 'skip_image' ), 10, 2 );
			add_action( 'admin_head'                            , array( $this, 'batch_import' ) );
		}

		/**
		 * Skip Image from Batch Processing.
		 *
		 * @since 1.0.14
		 *
		 * @param  boolean $can_process Batch process image status.
		 * @param  array   $attachment  Batch process image input.
		 * @return boolean
		 */
		function skip_image( $can_process, $attachment ) {

			if ( isset( $attachment['url'] ) && ! empty( $attachment['url'] ) ) {
				if (
					strpos( $attachment['url'], 'sites.wpastra.com' ) !== false ||
					strpos( $attachment['url'], 'sites-wpastra.sharkz.in' ) !== false
				) {
					return false;
				}
			}

			return true;
		}

		/**
		 * Process Batch Import on User Request.
		 *
		 * @since 1.0.14
		 *
		 * @return void
		 */
		public function batch_import() {

			if ( ! isset( $_GET['batch-import'] ) || 'true' !== $_GET['batch-import'] ) {
				return;
			}

			// Site import not complete?
			if ( ! get_option( 'astra-site-import-complete', 0 ) ) {
				return;
			}

			// Already processed batch import?
			if ( get_option( 'batch-import', 0 ) ) {
				return;
			}

			$this->start_process();
		}

		/**
		 * Start Image Import
		 *
		 * @since 1.0.14
		 *
		 * @return void
		 */
		public function start_process() {

			// Add "widget" in import [queue].
			if ( class_exists( 'Astra_Sites_Batch_Processing_Widgets' ) ) {
				self::$process_all->push_to_queue( Astra_Sites_Batch_Processing_Widgets::set_instance() );
			}

			// Add "bb-plugin" in import [queue].
			// Add "beaver-builder-lite-version" in import [queue].
			if ( is_plugin_active( 'beaver-builder-lite-version/fl-builder.php' ) || is_plugin_active( 'bb-plugin/fl-builder.php' ) ) {
				if ( class_exists( 'Astra_Sites_Batch_Processing_Beaver_Builder' ) ) {
					self::$process_all->push_to_queue( Astra_Sites_Batch_Processing_Beaver_Builder::set_instance() );
				}
			}

			// Add "elementor" in import [queue].
			if ( is_plugin_active( 'elementor/elementor.php' ) ) {
				if ( class_exists( '\Elementor\TemplateLibrary\Astra_Sites_Batch_Processing_Elementor' ) ) {
					$import = new \Elementor\TemplateLibrary\Astra_Sites_Batch_Processing_Elementor();
					self::$process_all->push_to_queue( $import );
				}
			}

			// Dispatch Queue.
			self::$process_all->save()->dispatch();
		}

		/**
		 * Get Page IDs
		 *
		 * @since 1.0.14
		 *
		 * @return array
		 */
		public static function get_pages() {

			$args = array(
				'post_type'     => 'page',

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


