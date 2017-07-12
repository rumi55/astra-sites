<?php
/**
 * Class Astra WXR Importer
 *
 * @since  1.0.0
 * @package Astra Addon
 */

defined( 'ABSPATH' ) or exit;

/**
 * Class Astra WXR Importer
 *
 * @since  1.0.0
 */
class Astra_WXR_Importer {

	/**
	 * Instance of Astra_WXR_Importer
	 *
	 * @since  1.0.0
	 * @var Astra_WXR_Importer
	 */
	private static $_instance = null;

	/**
	 * Instantiate Astra_WXR_Importer
	 *
	 * @since  1.0.0
	 * @return (Object) Astra_WXR_Importer.
	 */
	public static function instance() {
		if ( ! isset( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	/**
	 * Constructor.
	 *
	 * @since  1.0.0
	 */
	private function __construct() {
		$this->includes();

		add_filter( 'upload_mimes', array( $this, 'custom_upload_mimes' ) );
	}

	/**
	 * Add .xml files as supported format in the uploader.
	 *
	 * @param array $mimes Already supported mime types.
	 */
	public function custom_upload_mimes( $mimes ) {
		$mimes = array_merge( $mimes, array(
			'xml' => 'application/xml',
		) );

		return $mimes;
	}

	/**
	 * Include required files.
	 *
	 * @since  1.0.0
	 */
	private function includes() {
		if ( ! class_exists( 'WP_Importer' ) ) {
			defined( 'WP_LOAD_IMPORTERS' ) || define( 'WP_LOAD_IMPORTERS', true );
			require ABSPATH . '/wp-admin/includes/class-wp-importer.php';
		}
		require_once ASTRA_DEMO_IMPORT_DIR . 'importers/wxr-importer/class-wxr-importer.php';
		require_once ASTRA_DEMO_IMPORT_DIR . 'importers/wxr-importer/class-logger.php';
	}

	/**
	 * Start the xml import.
	 *
	 * @since  1.0.0
	 *
	 * @param  (String) $path Absolute path to the XML file.
	 */
	public function import_xml( $path ) {
		$options  = array(
			'fetch_attachments' => true,
			'default_author'    => 0,
		);
		$logger   = new WP_Importer_Logger();
		$importer = new WXR_Importer( $options );
		$importer->set_logger( $logger );
		$result = $importer->import( $path );
	}

	/**
	 * Download and save XML file to uploads directory.
	 *
	 * @since  1.0.0
	 *
	 * @param  (String) $url URL of the xml file.
	 *
	 * @return (Array)      Attachment array of the downloaded xml file.
	 */
	public function download_xml( $url ) {
		// Gives us access to the download_url() and wp_handle_sideload() functions.
		require_once( ABSPATH . 'wp-admin/includes/file.php' );

		$timeout_seconds = 5;

		// Download file to temp dir.
		$temp_file = download_url( $url, $timeout_seconds );

		if ( ! is_wp_error( $temp_file ) ) {

			// Array based on $_FILE as seen in PHP file uploads.
			$file = array(
				'name'     => basename( $url ), // ex: wp-header-logo.png.
				'type'     => 'image/png',
				'tmp_name' => $temp_file,
				'error'    => 0,
				'size'     => filesize( $temp_file ),
			);

			$overrides = array(

				/*
				 * Tells WordPress to not look for the POST form fields that would
				 * normally be present, default is true, we downloaded the file from
				 * a remote server, so there will be no form fields.
				 */
				'test_form'   => false,

				// Setting this to false lets WordPress allow empty files, not recommended.
				'test_size'   => true,

				// A properly uploaded file will pass this test. There should be no reason to override this one.
				'test_upload' => true,
			);

			// Move the temporary file into the uploads directory.
			$results = wp_handle_sideload( $file, $overrides );

			if ( ! empty( $results['error'] ) ) {
				// Insert any error handling here.
			} else {
				return $results;
			}
		}// End if().
	}

}
