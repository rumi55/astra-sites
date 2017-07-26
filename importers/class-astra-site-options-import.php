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

		$upload_dir	= wp_upload_dir(); 					// Set upload folder
	    $image_data	= file_get_contents($image_url); 	// Get image data
	    $filename	= basename($image_url); 			// Create image file name

	    // Check folder permission and define file location
	    if( wp_mkdir_p($upload_dir['path']) ) {
	    	$file = $upload_dir['path'] . '/' . $filename;
	    } else {
	    	$file = $upload_dir['basedir'] . '/' . $filename;
	    }

	    // Create the image  file on the server
	    file_put_contents($file, $image_data);

	    // Check image file type
	    $wp_filetype = wp_check_filetype($filename, null );

	    // Set attachment data
	    $attachment = array(
	        'post_mime_type' => $wp_filetype['type'],
	        'post_title'	 => sanitize_file_name($filename),
	        'post_content'	 => '',
	        'post_status'	 => 'inherit'
	    );

	    // Create the attachment
	    $attach_id = wp_insert_attachment( $attachment, $file );

	    set_theme_mod( 'custom_logo', $attach_id );

	}

}
