<?php
/**
 * Customizer Site options importer class.
 *
 * @since  1.0.0
 * @package Astra Addon
 */

defined( 'ABSPATH' ) or exit;

/**
 * Customizer Site options importer class.
 *
 * @since  1.0.0
 */
class Astra_Site_Options_Import {

	/**
	 * Instance of Astra_Site_Options_Importer
	 *
	 * @since  1.0.0
	 * @var (Object) Astra_Site_Options_Importer
	 */
	private static $_instance = null;

	/**
	 * Instanciate Astra_Site_Options_Importer
	 *
	 * @since  1.0.0
	 * @return (Object) Astra_Site_Options_Importer
	 */
	public static function instance() {
		if ( ! isset( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	/**
	 * Import site options.
	 *
	 * @since  1.0.0
	 *
	 * @param  (Array) $options Array of site options to be imported from the demo.
	 */
	public function import_options( $options ) {
		$show_on_front      = $options['show_on_front'];
		$page_on_front      = get_page_by_title( $options['page_on_front'] );
		$page_for_posts     = get_page_by_title( $options['page_for_posts'] );

		// Update site options.
		update_option( 'show_on_front', $show_on_front );
		update_option( 'page_on_front', $page_on_front->ID );
		update_option( 'page_for_posts', $page_for_posts->ID );

		$this->set_nav_menu_locations( $options['nav_menu_locations'] );

		$this->insert_logo( $options['custom_logo'] );
	}

	/**
	 * In WP nav menu is stored as ( 'menu_location' => 'menu_id' );
	 * In export we send 'menu_slug' like ( 'menu_location' => 'menu_slug' );
	 * In import we set 'menu_id' from menu slug like ( 'menu_location' => 'menu_id' );
	 *
	 * @since 1.0.0
	 * @param array $nav_menu_locations Array of nav menu locations.
	 */
	function set_nav_menu_locations( $nav_menu_locations = array() ) {

		$menu_locations = array();

		// Update menu locations.
		foreach ( $nav_menu_locations as $menu => $value ) {

			$term = get_term_by( 'slug', $value, 'nav_menu' );

			if ( is_object( $term ) ) {
				$menu_locations[ $menu ] = $term->term_id;
			}
		}

		set_theme_mod( 'nav_menu_locations', $menu_locations );
	}


	/**
	 * Insert Logo By URL
	 *
	 * @since 1.0.0
	 * @param  string $image_url Logo URL.
	 * @return void
	 */
	function insert_logo( $image_url = '' ) {

		// Gives us access to the download_url() and wp_handle_sideload() functions.
		require_once( ABSPATH . 'wp-admin/includes/file.php' );

		$timeout_seconds = 5;

		// Download file to temp dir.
		$temp_file = download_url( $image_url, $timeout_seconds );

		if ( ! is_wp_error( $temp_file ) ) {

			// Array based on $_FILE as seen in PHP file uploads
			$file = array(
				'name'     => basename( $image_url ),
				'tmp_name' => $temp_file,
				'error'    => 0,
				'size'     => filesize($temp_file),
			);

			$overrides = array(

				// Tells WordPress to not look for the POST form
				// fields that would normally be present as
				// we downloaded the file from a remote server, so there
				// will be no form fields
				// Default is true
				'test_form' => false,

				// Setting this to false lets WordPress allow empty files, not recommended
				// Default is true
				'test_size' => true,

				// A properly uploaded file will pass this test. There should be no reason to override this one.
				'test_upload' => true,

			);

			// Move the temporary file into the uploads directory
			$results = wp_handle_sideload( $file, $overrides );

			if ( empty( $results['error'] ) ) {

				// Set attachment data.
				$attachment = array(
					'post_mime_type' => $results['type'],
					'post_title'     => sanitize_file_name( basename($image_url) ),
					'post_content'   => '',
					'post_status'    => 'inherit',
				);

				// Create the attachment.
				$attach_id = wp_insert_attachment( $attachment, $results['file'] );

				set_theme_mod( 'custom_logo', $attach_id );
			}

		}// End if().
	}
}
