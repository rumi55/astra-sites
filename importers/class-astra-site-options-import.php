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
	 * Site Options
	 *
	 * @since 1.0.2
	 *
	 * @return array    List of defined array.
	 */
	private static function site_options() {
		return array(
			'custom_logo',
			'nav_menu_locations',
			'show_on_front',
			'page_on_front',
			'page_for_posts',

			// Plugin Name: SiteOrigin Widgets Bundle.
			'siteorigin_widgets_active',

			// Plugin Name: Elementor.
			'elementor_container_width',
			'elementor_cpt_support',
			'elementor_css_print_method',
			'elementor_default_generic_fonts',
			'elementor_disable_color_schemes',
			'elementor_disable_typography_schemes',
			'elementor_editor_break_lines',
			'elementor_exclude_user_roles',
			'elementor_global_image_lightbox',
			'elementor_page_title_selector',
			'elementor_scheme_color',
			'elementor_scheme_color-picker',
			'elementor_scheme_typography',
			'elementor_space_between_widgets',
			'elementor_stretched_section_container',
		);
	}

	/**
	 * Import site options.
	 *
	 * @since  1.0.2    Updated option if exist in defined option array 'site_options()'.
	 *
	 * @since  1.0.0
	 *
	 * @param  (Array) $options Array of site options to be imported from the demo.
	 */
	public function import_options( $options ) {

		foreach ( $options as $option_name => $option_value ) {

			// Is option exist in defined array site_options()?
			if ( in_array( $option_name, self::site_options() ) ) {

				switch ( $option_name ) {

					// page on front.
					// page on front.
					case 'page_for_posts':
					case 'page_on_front':
							$this->update_page_id_by_option_value( $option_name, $option_value );
						break;

					// nav menu locations.
					case 'nav_menu_locations':
							$this->set_nav_menu_locations( $option_value );
						break;

					// insert logo.
					case 'custom_logo':
							$this->insert_logo( $option_value );
						break;

					default:
							update_option( $option_name, $option_value );
						break;
				}
			}
		}

	}

	/**
	 * Update Page ID
	 *
	 * @since 1.0.2
	 *
	 * @param  string $option_name  Option name.
	 * @param  mixed  $option_value Option value.
	 * @return void
	 */
	function update_page_id_by_option_value( $option_name, $option_value ) {
		$page = get_page_by_title( $option_value );
		if ( is_object( $page ) ) {
			update_option( $option_name, $page->ID );
		}
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
