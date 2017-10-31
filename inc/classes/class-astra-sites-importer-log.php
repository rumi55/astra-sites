<?php
/**
 * Astra Sites Importer Log
 *
 * @since  1.0.0
 * @package Astra Sites
 */

defined( 'ABSPATH' ) or exit;

if ( ! class_exists( 'Astra_Sites_Importer_Log' ) ) :

	/**
	 * Astra Sites Importer
	 */
	class Astra_Sites_Importer_Log {

		/**
		 * Instance
		 *
		 * @since  1.0.0
		 * @var (Object) Class object
		 */
		private static $_instance = null;

		/**
		 * Log File
		 *
		 * @since  1.0.0
		 * @var (Object) Class object
		 */
		private static $log_file = null;

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

			// Check file read/write permissions.
			add_action( 'admin_init' , array( $this, 'has_file_read_write') );
		}

		function has_file_read_write() {

			// Get user credentials for WP file-system API.
			$astra_sites_import = wp_nonce_url( admin_url( 'themes.php?page=astra-sites' ), 'astra-import' );
			if ( false === ( $creds = request_filesystem_credentials( $astra_sites_import, '', false, false, null ) ) ) {
				return;
			}

			// Initial AJAX Import Hooks
			add_action( 'astra_sites_import_start'               , array( $this, 'start'), 10, 2 );
			add_action( 'astra_sites_import_customizer_settings' , array( $this, 'start_customizer') );
			add_action( 'astra_sites_import_xml'                 , array( $this, 'start_xml') );
			add_action( 'astra_sites_import_options'             , array( $this, 'start_options') );
			add_action( 'astra_sites_import_widgets'             , array( $this, 'start_widgets') );
			add_action( 'astra_sites_import_complete'            , array( $this, 'start_end') );

			// Hooks in between the process of import.
			add_filter( 'wie_import_results'                     , array( $this, 'widgets_data') );
			add_action( 'astra_sites_import_xml_log'             , array( $this, 'xml_log'), 10, 3 );
			
			// Added log file info in JSON.
			add_filter( 'astra_sites_import_end_data'            , array( $this, 'add_log_file_url') );
		}

		function add_log_file_url( $data = array() ) {

			$upload_dir   = self::log_dir();
			$upload_path  = trailingslashit( $upload_dir['url'] );
			$file_abs_url = get_option( 'astra_sites_recent_import_log_file', self::$log_file );
			$file_name    = basename( $file_abs_url );
			$file_url     = $upload_path . basename( $file_abs_url );

			$data['log_file'] = array(
				'abs_url' => $file_abs_url,
				'name' => $file_name,
				'url' => $file_url,
			);

			return $data;
		}

		function xml_log( $level = '', $message = '', $context = '' ) {
			Astra_Sites_Importer_Log::add( $message );
		}

		function start( $data = array(), $demo_api_uri = '' ) {

			// Set log file.
			self::set_log_file();

			Astra_Sites_Importer_Log::add( '---------------------------------------------------' . PHP_EOL );
			Astra_Sites_Importer_Log::add( 'Site API URL: ' . $demo_api_uri . PHP_EOL );
			Astra_Sites_Importer_Log::add( '---------------------------------------------------' . PHP_EOL );
			Astra_Sites_Importer_Log::add( 'Importing Started! - ' . date("h:i:s") );

		}

		function start_customizer() {
			Astra_Sites_Importer_Log::add(  PHP_EOL . '1. Imported "Customizer Settings"  - ' . date("h:i:s") );
			Astra_Sites_Importer_Log::add(  PHP_EOL . '---' );
		}

		function start_xml() {
			Astra_Sites_Importer_Log::add(  PHP_EOL . '2. Importing "XML"  - ' . date("h:i:s") );
		}

		function start_options() {
			Astra_Sites_Importer_Log::add(  PHP_EOL . '---' );
			Astra_Sites_Importer_Log::add(  PHP_EOL . '3. Imported "Site Options"  - ' . date("h:i:s") );
			Astra_Sites_Importer_Log::add(  PHP_EOL . '---' );
		}

		function start_widgets() {
			Astra_Sites_Importer_Log::add(  PHP_EOL . '4. Importing "Widgets"  - ' . date("h:i:s") );
		}

		function start_end() {
			Astra_Sites_Importer_Log::add(  PHP_EOL . '---' );
			Astra_Sites_Importer_Log::add(  PHP_EOL . 'Import Complete!  - ' . date("h:i:s") );

			// Delete Log file.
			delete_option( 'astra_sites_recent_import_log_file' );
		}

		function widgets_data( $results ) {

			if( is_array( $results ) ) {
				foreach ($results as $sidebar_key => $widgets) {
					Astra_Sites_Importer_Log::add( 'Sidebar: ' . $sidebar_key );
					foreach ($widgets['widgets'] as $widget_key => $widget) {
						if( isset( $widget['name'] ) && isset( $widget['message'] ) ) {
							Astra_Sites_Importer_Log::add( "\t" . 'Widget: "' . $widget['name'] . '" - ' . $widget['message'] );
						}
					}
				}
			}
		}

		/**
		 * Get an instance of WP_Filesystem_Direct.
		 *
		 * @since 1.0.14
		 * @return object A WP_Filesystem_Direct instance.
		 */
		static public function get_filesystem() {
			global $wp_filesystem;

			require_once ABSPATH . '/wp-admin/includes/file.php';

			WP_Filesystem();

			return $wp_filesystem;
		}

		public static function get_log_file() {
			return self::$log_file;
		}

		public static function log_dir( $dir_name = 'astra-sites' ) {

			$upload_dir  = wp_upload_dir();

			// Build the paths.
			$dir_info = array(
				'path'	 => $upload_dir['basedir'] . '/' . $dir_name . '/',
				'url'	 => $upload_dir['baseurl'] . '/' . $dir_name . '/'
			);

			// Create the upload dir if it doesn't exist.
			if ( ! file_exists( $dir_info['path'] ) ) {

				// Create the directory.
				mkdir( $dir_info['path'] );

				// Add an index file for security.
				file_put_contents( $dir_info['path'] . 'index.html', '' );
			}

			return $dir_info;
		}

		public static function set_log_file() {

			$upload_dir  = self::log_dir();

			$upload_path = trailingslashit( $upload_dir['path'] );

			// File format e.g. 'import-31-Oct-2017-06-39-12.txt'.
			self::$log_file = $upload_path . 'import-' . date("d-M-Y-h-i-s") . '.txt';

			update_option( 'astra_sites_recent_import_log_file', self::$log_file );
		}

		/**
		 * Write content to a file.
		 *
		 * @param string $content content to be saved to the file.
		 * @param string $file_path file path where the content should be saved.
		 * @return string|WP_Error path to the saved file or WP_Error object with error message.
		 */
		public static function add( $content ) {

			if( get_option( 'astra_sites_recent_import_log_file', false ) ) {
				$log_file = get_option( 'astra_sites_recent_import_log_file', self::$log_file );
			} else {
				$log_file = self::$log_file;
			}


			$existing_data = '';
			if ( file_exists( $log_file ) ) {
				$existing_data = self::get_filesystem()->get_contents( $log_file );
			}

			// Style separator.
			$separator = PHP_EOL;

			self::get_filesystem()->put_contents( $log_file, $existing_data . $separator .  $content, FS_CHMOD_FILE );
		}

	}

	/**
	 * Kicking this off by calling 'set_instance()' method
	 */
	Astra_Sites_Importer_Log::set_instance();

endif;




