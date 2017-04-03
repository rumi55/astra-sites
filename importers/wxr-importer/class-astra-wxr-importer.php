<?php

class Astra_WXR_Importer {

	private static $_instance = null;

	public static function instance() {
		if ( ! isset( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

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

	private function includes() {
		require ABSPATH . '/wp-admin/includes/class-wp-importer.php';
		require_once ADI_DIR . 'importers/wxr-importer/class-wxr-importer.php';
		require_once ADI_DIR . 'importers/wxr-importer/class-logger.php';
	}

	public function import_xml( $path ) {
		$options = array(
			'fetch_attachments' => true,
			'default_author' 	=> 0,
		);
		$logger = new WP_Importer_Logger();
		$importer = new WXR_Importer( $options );
		$importer->set_logger( $logger );
		$result = $importer->import( $path );
	}

	public function download_xml( $url ) {
		// Gives us access to the download_url() and wp_handle_sideload() functions.
		require_once( ABSPATH . 'wp-admin/includes/file.php' );

		$timeout_seconds = 5;

		// Download file to temp dir.
		$temp_file = download_url( $url, $timeout_seconds );

		if ( ! is_wp_error( $temp_file ) ) {

			// Array based on $_FILE as seen in PHP file uploads.
			$file = array(
				'name'     => basename( $url ), // ex: wp-header-logo.png
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
				// $filename  = $results['file']; // Full path to the file.
				// $local_url = $results['url'];  // URL to the file in the uploads dir.
				// $type      = $results['type']; // MIME type of the file.
				return $results;
			}
		}// End if().
	}

}
