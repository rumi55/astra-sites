<?php
/**
 * Astra Sites Importer
 *
 * @since  1.0.0
 * @package Astra Sites
 */

defined( 'ABSPATH' ) or exit;

if ( ! class_exists( 'Astra_Sites_Importer' ) ) :

	/**
	 * Astra Sites Importer
	 */
	class Astra_Sites_Importer {

		/**
		 * Instance
		 *
		 * @since  1.0.0
		 * @var (Object) Class object
		 */
		public static $_instance = null;

		/**
		 * Set Instance
		 *
		 * @since  1.0.0
		 *
		 * @return object Class object.
		 */
		public static function set_instance() {
			if ( ! isset( self::$_instance ) ) {
				self::$_instance = new self;
			}

			return self::$_instance;
		}

		/**
		 * Constructor.
		 *
		 * @since  1.0.0
		 */
		public function __construct() {

			require_once ASTRA_SITES_DIR . 'inc/importers/class-astra-sites-helper.php';
			require_once ASTRA_SITES_DIR . 'inc/importers/class-widgets-importer.php';
			require_once ASTRA_SITES_DIR . 'inc/importers/class-astra-customizer-import.php';
			require_once ASTRA_SITES_DIR . 'inc/importers/wxr-importer/class-astra-wxr-importer.php';
			require_once ASTRA_SITES_DIR . 'inc/importers/class-astra-site-options-import.php';

			add_action( 'wp_ajax_astra-sites-import-start'               , array( $this, 'import_start' ) );
			add_action( 'wp_ajax_astra-sites-import-customizer-settings' , array( $this, 'import_customizer_settings' ) );
			add_action( 'wp_ajax_astra-sites-import-xml'                 , array( $this, 'import_xml' ) );
			add_action( 'wp_ajax_astra-sites-import-options'             , array( $this, 'import_options' ) );
			add_action( 'wp_ajax_astra-sites-import-widgets'             , array( $this, 'import_widgets' ) );
			add_action( 'wp_ajax_astra-sites-import-end'                 , array( $this, 'import_end' ) );
			
			add_action( 'astra_sites_import_start'                       , array( $this, 'import_astra_enabled_extension' ) );
			add_action( 'astra_sites_import_customizer_settings'         , array( $this, 'import_custom_404_extension_options' ) );
			add_action( 'astra_sites_import_end'                         , array( $this, 'clear_cache' ) );
			
			// add_action( 'wp_ajax_astra-import-demo'                   , array( $this, 'demo_ajax_import' ) );
			add_action( 'astra_sites_image_import_complete'              , array( $this, 'clear_cache' ) );
		}

		function import_start() {

			// $upload_dir    = wp_upload_dir();
			// $upload_path   = trailingslashit( $upload_dir['path'] );
			// $log_file_path = $upload_path .  'log_file-' .microtime(). '.txt';
			// // vl( $log_file_path );
			// // wp_die();

			// // Add this message to log file.
			// $log_added = self::append_to_file(
			// 	__( 'The import files were successfully uploaded!', 'pt-ocdi' ),
			// 	self::$log_file_path,
			// 	esc_html__( 'Upload files' , 'pt-ocdi' )
			// );

			$report = array(
				'success' => false,
				'message' => '',
			);

			if ( ! current_user_can( 'customize' ) ) {
				$report['message'] = __( 'You have not "customize" access to import the astra site.', 'astra-sites' );
				wp_send_json( $report );
			}

			$demo_api_uri = isset( $_POST['api_url'] ) ? esc_url( $_POST['api_url'] ) : '';

			$demo_data = self::get_astra_single_demo( $demo_api_uri );

			do_action( 'astra_sites_import_start', $demo_data );
			
			wp_send_json_success( $demo_data );
		}

		// Import Customizer Settings.
		function import_customizer_settings() {

			$demo_data       = ( isset( $_POST['demo_data'] ) ) ? (array) json_decode( stripcslashes( $_POST['demo_data'] ), 1 ) : '';
			$customizer_data = $demo_data['data']['astra-site-customizer-data'];

			if( isset( $customizer_data ) ) {
				$customizer_import = Astra_Customizer_Import::instance();
				$customizer_data   = $customizer_import->import( $customizer_data );
			}

			do_action( 'astra_sites_import_customizer_settings', $demo_data );

			wp_send_json_success( $demo_data );
		}

		// Import XML.
		function import_xml() {

			$demo_data = ( isset( $_POST['demo_data'] ) ) ? (array) json_decode( stripcslashes( $_POST['demo_data'] ), 1 ) : '';
			$wxr_url   = $demo_data['data']['astra-site-wxr-path'];

			if( isset( $wxr_url ) ) {
				$wxr_importer = Astra_WXR_Importer::instance();
				$xml_path     = $wxr_importer->download_xml( $wxr_url );
				$wxr_importer->import_xml( $xml_path['file'] );
			}

			do_action( 'astra_sites_import_xml' );

			wp_send_json_success( $demo_data );
		}
		
		// Import WordPress site options.
		function import_options() {

			$demo_data = ( isset( $_POST['demo_data'] ) ) ? (array) json_decode( stripcslashes( $_POST['demo_data'] ), 1 ) : '';
			$options   = $demo_data['data']['astra-site-options-data'];
		
			if( isset( $options ) ) {
				$options_importer = Astra_Site_Options_Import::instance();
				$options_importer->import_options( $options );
			}
			
			do_action( 'astra_sites_import_options' );

			wp_send_json_success( $demo_data );
		}

		// bail if widgets data is not available.
		function import_widgets() {

			$demo_data    = ( isset( $_POST['demo_data'] ) ) ? (array) json_decode( stripcslashes( $_POST['demo_data'] ), 1 ) : '';
			$widgets_data = $demo_data['data']['astra-site-widgets-data'];

			if ( isset( $widgets_data ) ) {
				$widgets_importer = Astra_Widget_Importer::instance();
				$widgets_importer->import_widgets_data( $widgets_data );
			}

			do_action( 'astra_sites_import_widgets' );

			wp_send_json_success( $demo_data );
		}

		function import_end() {

			$demo_data = ( isset( $_POST['demo_data'] ) ) ? (array) json_decode( stripcslashes( $_POST['demo_data'] ), 1 ) : '';

			do_action( 'astra_sites_import_end', $demo_data );

			do_action( 'astra_sites_import_complete', $demo_data );

			// $report['success'] = true;
			// $report['message'] = __( 'Demo Imported Successfully.', 'astra-sites' );
			// wp_send_json( $report );

			wp_send_json_success( $demo_data );
		}

		/**
		 * Ajax callback for demo import action.
		 *
		 * @since  1.0.0
		 */
		// public function demo_ajax_import() {

		// 	$report = array(
		// 		'success' => false,
		// 		'message' => '',
		// 	);

		// 	if ( ! current_user_can( 'customize' ) ) {
		// 		$report['message'] = __( 'You have not "customize" access to import the astra site.', 'astra-sites' );
		// 		wp_send_json( $report );
		// 	}

		// 	$demo_api_uri = isset( $_POST['api_url'] ) ? esc_url( $_POST['api_url'] ) : '';
		// 	$this->import_demo( $demo_api_uri );

		// 	$report['success'] = true;
		// 	$report['message'] = __( 'Demo Imported Successfully.', 'astra-sites' );
		// 	wp_send_json( $report );

		// }


		/**
		 * Import the demo.
		 *
		 * @since  1.0.0
		 *
		 * @param  (String) $demo_api_uri API URL for the single demo.
		 */
		// public function import_demo( $demo_api_uri ) {

		// 	$demo_data = self::get_astra_single_demo( $demo_api_uri );

		// 	// Import Enabled Extensions.
		// 	$this->import_astra_enabled_extension( $demo_data['astra-enabled-extensions'] );

		// 	// Import Customizer Settings.
		// 	$this->import_customizer_settings( $demo_data['astra-site-customizer-data'] );

		// 	// Import XML.
		// 	$this->import_wxr( $demo_data['astra-site-wxr-path'] );

		// 	// Import WordPress site options.
		// 	$this->import_site_options( $demo_data['astra-site-options-data'] );

		// 	// Import Custom 404 extension options.
		// 	$this->import_custom_404_extension_options( $demo_data['astra-custom-404'] );

		// 	// Import Widgets data.
		// 	$this->import_widgets( $demo_data['astra-site-widgets-data'] );

		// 	// Clear Cache.
		// 	$this->clear_cache();

		// 	do_action( 'astra_sites_import_complete', $demo_data );
		// }

		/**
		 * Import custom 404 section.
		 *
		 * @since 1.0.0
		 *
		 * @param  (Array) $options_404 404 Extensions settings from the demo.
		 */
		public function import_custom_404_extension_options( ) {
			
			if( isset( $demo_data['astra-custom-404'] ) ) {
				if ( is_callable( 'Astra_Admin_Helper::update_admin_settings_option' ) ) {
					$options_404 = $demo_data['astra-custom-404'];
					Astra_Admin_Helper::update_admin_settings_option( '_astra_ext_custom_404', $options_404 );
				}
			}
		}

		/**
		 * Import settings enabled astra extensions from the demo.
		 *
		 * @since  1.0.0
		 */
		public function import_astra_enabled_extension() {

			if( isset( $demo_data['astra-enabled-extensions'] ) ) {
				if ( is_callable( 'Astra_Admin_Helper::update_admin_settings_option' ) ) {
					$saved_extensions = $demo_data['astra-enabled-extensions'];
					Astra_Admin_Helper::update_admin_settings_option( '_astra_ext_enabled_extensions', $saved_extensions );
				}
			}
		}

		/**
		 * Get single demo.
		 *
		 * @since  1.0.0
		 *
		 * @param  (String) $demo_api_uri API URL of a demo.
		 *
		 * @return (Array) $astra_demo_data demo data for the demo.
		 */
		public static function get_astra_single_demo( $demo_api_uri ) {

			// default values.
			$remote_args = array();
			$defaults    = array(
				'id'                         => '',
				'astra-site-widgets-data'    => '',
				'astra-site-customizer-data' => '',
				'astra-site-options-data'    => '',
				'astra-site-wxr-path'        => '',
				'astra-enabled-extensions'   => '',
				'astra-custom-404'           => '',
				'required-plugins'           => '',
			);

			$api_args = apply_filters(
				'astra_sites_api_args', array(
					'timeout' => 15,
				)
			);

			// Use this for premium demos.
			$request_params = apply_filters(
				'astra_sites_api_params', array(
					'purchase_key' => '',
					'site_url'     => '',
				)
			);

			$demo_api_uri = add_query_arg( $request_params, $demo_api_uri );

			// API Call.
			$response = wp_remote_get( $demo_api_uri, $api_args );

			if ( ! is_wp_error( $response ) || wp_remote_retrieve_response_code( $response ) === 200 ) {

				$result                                     = json_decode( wp_remote_retrieve_body( $response ), true );

				if ( ! isset( $result['code'] ) ) {
					$remote_args['id']                         = $result['id'];
					$remote_args['astra-site-widgets-data']    = json_decode( $result['astra-site-widgets-data'] );
					$remote_args['astra-site-customizer-data'] = $result['astra-site-customizer-data'];
					$remote_args['astra-site-options-data']    = $result['astra-site-options-data'];
					$remote_args['astra-site-wxr-path']        = $result['astra-site-wxr-path'];
					$remote_args['astra-enabled-extensions']   = $result['astra-enabled-extensions'];
					$remote_args['astra-custom-404']           = $result['astra-custom-404'];
					$remote_args['required-plugins']           = $result['required-plugins'];
				}
			}

			// Merge remote demo and defaults.
			return wp_parse_args( $remote_args, $defaults );
		}

		/**
		 * Clear Cache.
		 *
		 * @since  1.0.9
		 */
		public function clear_cache() {

			// Clear 'Elementor' file cache.
			if ( class_exists( '\Elementor\Plugin' ) ) {
				Elementor\Plugin::$instance->posts_css_manager->clear_cache();
			}

			// Clear 'Builder Builder' cache.
			if ( is_callable( 'FLBuilderModel::delete_asset_cache_for_all_posts' ) ) {
				FLBuilderModel::delete_asset_cache_for_all_posts();
			}
		}

		/**
		 * Append content to the file.
		 *
		 * @param string $content content to be saved to the file.
		 * @param string $file_path file path where the content should be saved.
		 * @param string $separator_text separates the existing content of the file with the new content.
		 * @return boolean|WP_Error, path to the saved file or WP_Error object with error message.
		 */
		public static function append_to_file( $content, $file_path, $separator_text = '' ) {
			// // Verify WP file-system credentials.
			// $verified_credentials = self::check_wp_filesystem_credentials();

			// if ( is_wp_error( $verified_credentials ) ) {
			// 	return $verified_credentials;
			// }

			// By this point, the $wp_filesystem global should be working, so let's use it to create a file.
			if ( ! function_exists( 'WP_Filesystem' ) ) {
	   			require_once ABSPATH . 'wp-admin/includes/file.php';
	   		}
	   
	   		WP_Filesystem();

			global $wp_filesystem;

			$existing_data = '';
			if ( file_exists( $file_path ) ) {
				$existing_data = $wp_filesystem->get_contents( $file_path );
			}

			// Style separator.
			$separator = PHP_EOL . '---' . $separator_text . '---' . PHP_EOL;

			if ( ! $wp_filesystem->put_contents( $file_path, $existing_data . $separator . $content . PHP_EOL ) ) {
				return new WP_Error(
					'failed_writing_file_to_server',
					sprintf(
						__( 'An error occurred while writing file to your server! Tried to write a file to: %s%s.', 'pt-ocdi' ),
						'<br>',
						$file_path
					)
				);
			}

			return true;
		}

	}

	/**
	 * Kicking this off by calling 'set_instance()' method
	 */
	Astra_Sites_Importer::set_instance();

endif;