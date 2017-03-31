<?php

class Astra_WXR_Importer {

	private static $_instance = null;

	public static function instance() {
		if ( ! isset( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	public function import_xml( $file ) {

		// Make sure importers constant is defined
		if ( ! defined( 'WP_LOAD_IMPORTERS' ) ) {
			define( 'WP_LOAD_IMPORTERS', true );
		}

		if ( ! defined( 'IMPORT_DEBUG' ) ) {
			define( 'IMPORT_DEBUG', false );
		}

		// Import file location
		$import_file = ABSPATH . 'wp-admin/includes/import.php';

		// Include import file
		if ( ! file_exists( $import_file ) ) {
			return;
		}

		// Include import file
		require_once( $import_file );

		// Define error var
		$importer_error = false;

		if ( ! class_exists( 'WP_Importer' ) ) {
			$class_wp_importer = ABSPATH . 'wp-admin/includes/class-wp-importer.php';

			if ( file_exists( $class_wp_importer ) ) {
				require_once $class_wp_importer;
			} else {
				$importer_error = __( 'Can not retrieve class-wp-importer.php', 'ocean-demo-import' );
			}
		}

		if ( ! class_exists( 'WP_Import' ) ) {
			$class_wp_import = ADI_DIR . 'importers/wxr-importer/class-wordpress-importer.php';

			if ( file_exists( $class_wp_import ) ) {
				require_once $class_wp_import;
			} else {
				$importer_error = __( 'Can not retrieve wordpress-importer.php', 'ocean-demo-import' );
			}
		}

		// Display error
		if ( $importer_error ) {
			return new WP_Error( 'xml_import_error', $importer_error );
		} else {

			// No error, lets import things...
			if ( ! is_file( $file ) ) {
				$importer_error = __( 'Sample data file appears corrupt or can not be accessed.', 'ocean-demo-import' );

				return new WP_Error( 'xml_import_error', $importer_error );
			} else {
				$importer                    = new WP_Import();
				$importer->fetch_attachments = true;
				$importer->import( $file );

				// Clear sample data content from temp xml file
				// $temp_xml = ODI_PATH .'includes/temp.xml';
				// file_put_contents( $temp_xml, '' );
			}
		}
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

	/**
	 * Perform a HTTP HEAD or GET request.
	 *
	 * If $file_path is a writable filename, this will do a GET request and write
	 * the file to that path.
	 *
	 * This is a re-implementation of the deprecated wp_get_http() function from WP Core,
	 * but this time using the recommended WP_Http() class and the WordPress filesystem.
	 *
	 * @param string      $url URL to fetch.
	 * @param string|bool $file_path Optional. File path to write request to. Default false.
	 * @param array       $args Optional. Arguments to be passed-on to the request.
	 *
	 * @return bool|string False on failure and string of headers if HEAD request.
	 */
	public static function wp_get_http( $url, $file_path = false, $red = 1 ) {

		// No need to proceed if we don't have a $url or a $file_path.
		if ( ! $url || ! $file_path ) {
			return false;
		}

		$try_file_get_contents = false;

		// Make sure we normalize $file_path.
		$file_path = wp_normalize_path( $file_path );

		// Include the WP_Http class if it doesn't already exist.
		if ( ! class_exists( 'WP_Http' ) ) {
			include_once( wp_normalize_path( ABSPATH . WPINC . '/class-http.php' ) );
		}
		// Inlude the wp_remote_get function if it doesn't already exist.
		if ( ! function_exists( 'wp_remote_get' ) ) {
			include_once( wp_normalize_path( ABSPATH . WPINC . '/http.php' ) );
		}

		$args     = wp_parse_args( $args, array(
			'timeout'    => 30,
			'user-agent' => 'avada-user-agent',
		) );
		$response = wp_remote_get( esc_url_raw( $url ), $args );

		if ( ! is_wp_error( $response ) && wp_remote_retrieve_response_code( $response ) != 200 ) {
			return;
		}

		$body = wp_remote_retrieve_body( $response );

		// Try file_get_contents if body is empty.
		if ( empty( $body ) ) {
			if ( function_exists( 'ini_get' ) && ini_get( 'allow_url_fopen' ) ) {
				$body = @file_get_contents( $url );
			}
		}

		// Initialize the Wordpress filesystem.
		global $wp_filesystem;
		if ( empty( $wp_filesystem ) ) {
			require_once( ABSPATH . '/wp-admin/includes/file.php' );
			WP_Filesystem();
		}

		// Attempt to write the file.
		if ( ! $wp_filesystem->put_contents( $file_path, $body, FS_CHMOD_FILE ) ) {
			// If the attempt to write to the file failed, then fallback to fwrite.
			@unlink( $file_path );
			$fp      = fopen( $file_path, 'w' );
			$written = fwrite( $fp, $body );
			fclose( $fp );
			if ( false === $written ) {
				return false;
			}
		}

		// If all went well, then return the headers of the request.
		if ( isset( $response['headers'] ) ) {
			$response['headers']['response'] = $response['response']['code'];

			return $response['headers'];
		}

		// If all else fails, then return false.
		return false;

	}

}
