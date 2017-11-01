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
		private static $_instance = null;

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
		private function __construct() {

			require_once ASTRA_SITES_DIR . 'inc/importers/class-astra-sites-helper.php';
			require_once ASTRA_SITES_DIR . 'inc/importers/class-widgets-importer.php';
			require_once ASTRA_SITES_DIR . 'inc/importers/class-astra-customizer-import.php';
			require_once ASTRA_SITES_DIR . 'inc/importers/wxr-importer/class-astra-wxr-importer.php';
			require_once ASTRA_SITES_DIR . 'inc/importers/class-astra-site-options-import.php';

			add_action( 'wp_ajax_astra-import-demo',                        array( $this, 'demo_ajax_import' ) );
			add_action( 'astra_sites_image_import_complete',                array( $this, 'clear_cache' ) );

		}

		/**
		 * Ajax callback for demo import action.
		 *
		 * @since  1.0.0
		 */
		public function demo_ajax_import() {

			$report = array(
				'success' => false,
				'message' => '',
			);

			if ( ! current_user_can( 'customize' ) ) {
				$report['message'] = __( 'You have not "customize" access to import the astra site.', 'astra-sites' );
				wp_send_json( $report );
			}

			$demo_api_uri = isset( $_POST['api_url'] ) ? esc_url( $_POST['api_url'] ) : '';
			$this->import_demo( $demo_api_uri );

			$report['success'] = true;
			$report['message'] = __( 'Demo Imported Successfully.', 'astra-sites' );
			wp_send_json( $report );

		}


		/**
		 * Import the demo.
		 *
		 * @since  1.0.0
		 *
		 * @param  (String) $demo_api_uri API URL for the single demo.
		 */
		public function import_demo( $demo_api_uri ) {

			$demo_data = self::get_astra_single_demo( $demo_api_uri );

			// Import Enabled Extensions.
			$this->import_astra_enabled_extension( $demo_data['astra-enabled-extensions'] );

			// Import Customizer Settings.
			$this->import_customizer_settings( $demo_data['astra-site-customizer-data'] );

			// Import XML.
			$this->import_wxr( $demo_data['astra-site-wxr-path'] );

			// Import WordPress site options.
			$this->import_site_options( $demo_data['astra-site-options-data'] );

			// Import Custom 404 extension options.
			$this->import_custom_404_extension_options( $demo_data['astra-custom-404'] );

			// Import Widgets data.
			$this->import_widgets( $demo_data['astra-site-widgets-data'] );

			// Clear Cache.
			$this->clear_cache();

			do_action( 'astra_sites_import_complete', $demo_data );
		}

		/**
		 * Import widgets and assign to correct sidebars.
		 *
		 * @since  1.0.0
		 *
		 * @param  (Object) $data Widgets data.
		 */
		public function import_widgets( $data ) {

			// bail if widgets data is not available.
			if ( null == $data ) {
				return;
			}

			$widgets_importer = Astra_Widget_Importer::instance();
			$widgets_importer->import_widgets_data( $data );
		}

		/**
		 * Import custom 404 section.
		 *
		 * @since 1.0.0
		 *
		 * @param  (Array) $options_404 404 Extensions settings from the demo.
		 */
		public function import_custom_404_extension_options( $options_404 ) {
			if ( is_callable( 'Astra_Admin_Helper::update_admin_settings_option' ) ) {
				Astra_Admin_Helper::update_admin_settings_option( '_astra_ext_custom_404', $options_404 );
			}
		}

		/**
		 * Import site options - Front Page, Menus, Blog page etc.
		 *
		 * @since  1.0.0
		 *
		 * @param  (Array) $options Array of required site options from the demo.
		 */
		public function import_site_options( $options ) {
			$options_importer = Astra_Site_Options_Import::instance();
			$options_importer->import_options( $options );
		}

		/**
		 * Download and import the XML from the demo.
		 *
		 * @since  1.0.0
		 *
		 * @param  (String) $wxr_url URL of the xml export of the demo to be imported.
		 */
		public function import_wxr( $wxr_url ) {
			$wxr_importer = Astra_WXR_Importer::instance();
			$xml_path     = $wxr_importer->download_xml( $wxr_url );
			$wxr_importer->import_xml( $xml_path['file'] );
		}

		/**
		 * Import Customizer data.
		 *
		 * @since  1.0.0
		 *
		 * @param  (Array) $customizer_data Customizer data for the demo to be imported.
		 */
		public function import_customizer_settings( $customizer_data ) {
			$customizer_import = Astra_Customizer_Import::instance();
			$customizer_data   = $customizer_import->import( $customizer_data );
		}

		/**
		 * Import settings enabled astra extensions from the demo.
		 *
		 * @since  1.0.0
		 *
		 * @param  (Array) $saved_extensions Array of enabled extensions.
		 */
		public function import_astra_enabled_extension( $saved_extensions ) {
			if ( is_callable( 'Astra_Admin_Helper::update_admin_settings_option' ) ) {
				Astra_Admin_Helper::update_admin_settings_option( '_astra_ext_enabled_extensions', $saved_extensions );
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

	}

	/**
	 * Kicking this off by calling 'set_instance()' method
	 */
	Astra_Sites_Importer::set_instance();

endif;
