<?php
/**
 * Astra Site Helper
 *
 * @since  1.0.0
 * @package Astra Sites
 */

if ( ! class_exists( 'Astra_Sites_Helper' ) ) :

	/**
	 * Astra_Sites_Helper
	 *
	 * @since 1.0.0
	 */
	class Astra_Sites_Helper {

		/**
		 * Instance
		 *
		 * @access private
		 * @var object Instance
		 * @since 1.0.0
		 */
		private static $instance;

		/**
		 * Initiator
		 *
		 * @since 1.0.0
		 * @return object initialized object of class.
		 */
		public static function get_instance() {
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
			add_filter( 'wie_import_data', array( $this, 'custom_menu_widget' ) );
		}

		/**
		 * Custom Menu Widget
		 *
		 * In widget export we set the nav menu slug instead of ID.
		 * So, In import process we check get menu id by slug and set
		 * it in import widget process.
		 *
		 * @since 1.0.7
		 *
		 * @param  object $all_sidebars Widget data.
		 * @return object               Set custom menu id by slug.
		 */
		function custom_menu_widget( $all_sidebars ) {

			// Get current menu ID & Slugs.
			$menu_locations = array();
			$nav_menus = (object) wp_get_nav_menus();
			if ( isset( $nav_menus ) ) {
				foreach ( $nav_menus as $menu_key => $menu ) {
					if ( is_object( $menu ) ) {
						$menu_locations[ $menu->term_id ] = $menu->slug;
					}
				}
			}

			// Import widget data.
			$all_sidebars = (object) $all_sidebars;
			foreach ( $all_sidebars as $widgets_key => $widgets ) {
				foreach ( $widgets as $widget_key => $widget ) {

					// Found slug in current menu list.
					if ( isset( $widget->nav_menu ) ) {
						$menu_id = array_search( $widget->nav_menu, $menu_locations );
						if ( ! empty( $menu_id ) ) {
							$all_sidebars->$widgets_key->$widget_key->nav_menu = $menu_id;
						}
					}
				}
			}

			return $all_sidebars;
		}

		/**
		 * Download File Into Uploads Directory
		 *
		 * @param  string $file Download File URL.
		 * @return array        Downloaded file data.
		 */
		public static function download_file( $file = '' ) {

			// Gives us access to the download_url() and wp_handle_sideload() functions.
			require_once( ABSPATH . 'wp-admin/includes/file.php' );

			$timeout_seconds = 5;

			// Download file to temp dir.
			$temp_file = download_url( $file, $timeout_seconds );

			// WP Error.
			if ( is_wp_error( $temp_file ) ) {
				return array(
					'success' => false,
					'data'    => $temp_file->get_error_message(),
				);
			}

			// Array based on $_FILE as seen in PHP file uploads.
			$file_args = array(
				'name'     => basename( $file ),
				'tmp_name' => $temp_file,
				'error'    => 0,
				'size'     => filesize( $temp_file ),
			);

			$overrides = array(

				// Tells WordPress to not look for the POST form
				// fields that would normally be present as
				// we downloaded the file from a remote server, so there
				// will be no form fields
				// Default is true.
				'test_form' => false,

				// Setting this to false lets WordPress allow empty files, not recommended.
				// Default is true.
				'test_size' => true,

				// A properly uploaded file will pass this test. There should be no reason to override this one.
				'test_upload' => true,

			);

			// Move the temporary file into the uploads directory.
			$results = wp_handle_sideload( $file_args, $overrides );

			if ( isset( $results['error'] ) ) {
				return array(
					'success' => false,
					'data'    => $results,
				);
			}

			// Success!
			return array(
				'success' => true,
				'data'    => $results,
			);
		}

	}

	/**
	 * Kicking this off by calling 'get_instance()' method
	 */
	Astra_Sites_Helper::get_instance();

endif;
