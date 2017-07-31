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

	static $default_active_widgets = array();

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
		if( is_object( $page_on_front ) ) {
			update_option( 'page_on_front', $page_on_front->ID );
		}

		// Page for Posts.
		$page_for_posts = get_page_by_title( $options['page_for_posts'] );
		if( is_object( $page_for_posts ) ) {
			update_option( 'page_for_posts', $page_for_posts->ID );
		}

		if( class_exists( 'SiteOrigin_Widgets_Bundle' ) ) {
			vl( 'yes' );

			self::$default_active_widgets = $options['siteorigin_widgets_active'];

			// foreach ( $options['siteorigin_widgets_active'] as $key => $value) {
			// 	if( 1 == $value || '1' == $value ) {
			// 		$temp[$key] = true;
			// 	} else {
			// 		$temp[$key] = false;
			// 	}
			// }
			wp_cache_flush();
			// Siteorigin Widgets
			$active_widgets = wp_cache_get( 'active_widgets', 'siteorigin_widgets' );
			vl( $active_widgets );
			vl( $temp );
			vl( get_option( 'siteorigin_widgets_active', '' ) );
			SiteOrigin_Widgets_Bundle::$default_active_widgets = $temp;
			wp_cache_add( 'active_widgets', $temp, 'siteorigin_widgets' );
			update_option( 'siteorigin_widgets_active', $temp );
			add_filter( 'siteorigin_widgets_default_active', array( $this, 'set_default_widgets' ) );
			// create the initial single
			$ob = new SiteOrigin_Widgets_Bundle();
			// $ob::single();
			// $ob = new WP_Customize_Widgets();
			// $ob->customize_controls_init();

		} else {
			vl( 'no' );
		}

		// Nav Menu Locations.
		$this->set_nav_menu_locations( $options['nav_menu_locations'] );

		// Insert Logo.
		$this->insert_logo( $options['custom_logo'] );
	}

	function set_default_widgets( $defaults = array() ) {

		return wp_parse_args( self::$default_active_widgets, $defaults );
		// static $default_active_widgets = array(
		// 	'button' => true,
		// 	'google-map' => true,
		// 	'image' => true,
		// 	'slider' => true,
		// 	'post-carousel' => true,
		// 	'editor' => true,
		// );
		// ;
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
		if( isset( $nav_menu_locations ) ) {

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
