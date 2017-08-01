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

		// Show on Front.
		update_option( 'show_on_front', $options['show_on_front'] );

		// Page on Front.
		$page_on_front = get_page_by_title( $options['page_on_front'] );
		if ( is_object( $page_on_front ) ) {
			update_option( 'page_on_front', $page_on_front->ID );
		}

		// Page for Posts.
		$page_for_posts = get_page_by_title( $options['page_for_posts'] );
		if ( is_object( $page_for_posts ) ) {
			update_option( 'page_for_posts', $page_for_posts->ID );
		}

		// Nav Menu Locations.
		$this->set_nav_menu_locations( $options['nav_menu_locations'] );

		// Insert Logo.
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
		if ( isset( $nav_menu_locations ) ) {

			foreach ( $nav_menu_locations as $menu => $value ) {

				$term = get_term_by( 'slug', $value, 'nav_menu' );

				if ( is_object( $term ) ) {
					$menu_locations[ $menu ] = $term->term_id;
				}
			}

			set_theme_mod( 'nav_menu_locations', $menu_locations );
		}
	}


	/**
	 * Insert Logo By URL
	 *
	 * @since 1.0.0
	 * @param  string $image_url Logo URL.
	 * @return void
	 */
	function insert_logo( $image_url = '' ) {

		// Download Site Logo Image.
		$response = Astra_Sites_Helper::download_file( $image_url );

		// Is Success?
		if ( $response['success'] ) {

			// Set attachment data.
			$attachment = array(
				'post_mime_type' => $response['data']['type'],
				'post_title'     => sanitize_file_name( basename( $image_url ) ),
				'post_content'   => '',
				'post_status'    => 'inherit',
			);

			// Create the attachment.
			$attach_id = wp_insert_attachment( $attachment, $response['data']['file'] );

			set_theme_mod( 'custom_logo', $attach_id );
		}

	}
}
