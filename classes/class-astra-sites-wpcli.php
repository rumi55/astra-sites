<?php
/**
 * Astra Sites WP CLI
 *
 * 1. Run `wp astra-sites list`						List of all astra sites.
 * 2. Run `wp astra-sites import <site-api-url>`	Import site.
 *
 * @package Astra Sites
 * @since 1.0.6
 */

if ( class_exists( 'WP_CLI_Command' ) && ! class_exists( 'Astra_Sites_WP_CLI' ) ) :

	/**
	 * Astra_Sites_WP_CLI
	 *
	 * @since 1.0.6
	 */
	class Astra_Sites_WP_CLI extends WP_CLI_Command {

		/**
		 * Import Site
		 *
		 * @since 1.0.6
		 *
		 * E.g. `wp astra-sites import <site-api-url>`
		 * 
		 * @param  array $args       Arguments.
		 * @param  array $assoc_args Associated Arguments.
		 * @return void
		 */
		public function import( $args, $assoc_args ) {

			$url = isset( $args[0] ) ? $args[0] : '';

			if ( filter_var( $url, FILTER_VALIDATE_URL) === FALSE) {
				WP_CLI::error( sprintf( __( 'Invalid Site URL %1$s.', 'astra-sites' ) , $url ) );
			}

			$demo_data = Astra_Sites::get_astra_single_demo( $url );

			// Import Enabled Extensions.
			Astra_Sites::set_instance()->import_astra_enabled_extension( $demo_data['astra-enabled-extensions'] );
			WP_CLI::line( __( 'Imported enabled extensions.', 'astra-sites' ) );

			// Import Customizer Settings.
			Astra_Sites::set_instance()->import_customizer_settings( $demo_data['astra-site-customizer-data'] );
			WP_CLI::line( __( 'Imported customizer settings.', 'astra-sites' ) );

			// Import XML.
			Astra_Sites::set_instance()->import_wxr( $demo_data['astra-site-wxr-path'] );
			WP_CLI::line( __( 'Imported XML.', 'astra-sites' ) );

			// Import WordPress site options.
			Astra_Sites::set_instance()->import_site_options( $demo_data['astra-site-options-data'] );
			WP_CLI::line( __( 'Imported WordPress site options.', 'astra-sites' ) );

			// Import Custom 404 extension options.
			Astra_Sites::set_instance()->import_custom_404_extension_options( $demo_data['astra-custom-404'] );
			WP_CLI::line( __( 'Imported custom 404 extension options.', 'astra-sites' ) );

			// Import Widgets data.
			Astra_Sites::set_instance()->import_widgets( $demo_data['astra-site-widgets-data'] );
			WP_CLI::line( __( 'Imported widgets data.', 'astra-sites' ) );

			WP_CLI::success( sprintf( __( 'Astra site imported form API: %1$s', 'text-domain' ) , $url ) );
		}

		/**
		 * List
		 *
		 * @since 1.0.6
		 *
		 * E.g. `wp astra-sites list`
		 *
		 * @param  array $args       Arguments.
		 * @param  array $assoc_args Associated Arguments.
		 * @return void
		 */
		public function list( $args, $assoc_args ) {

			$list = get_transient( 'astra-sites' );

			if ( empty( $list ) ) {

				$args           = new stdClass();
				$args->category = 'all';
				$args->id       = 'all';
				$args->search   = '';
				$paged          = '1';

				$list = (array) Astra_Sites::set_instance()->get_astra_demos( $args, $paged );

				set_transient( 'astra-sites', $list, 24 * HOUR_IN_SECONDS );
			}

			foreach ($list as $key => $item) {

				$id 				= ( isset( $item['id'] ) ) ? $item['id'] : '';
				$demo_api 			= ( isset( $item['demo_api'] ) ) ? $item['demo_api'] : '';
				$slug 				= ( isset( $item['slug'] ) ) ? $item['slug'] : '';
				$status 			= ( isset( $item['status'] ) ) ? $item['status'] : '';
				$astra_demo_type 	= ( isset( $item['astra_demo_type'] ) ) ? $item['astra_demo_type'] : '';
				
				WP_CLI::line( $id.' | '.$demo_api.' | '.$status.' | '.$slug.' |' .$astra_demo_type );
			}
		}
	}

	/**
	 * Add Command
	 */
	WP_CLI::add_command( 'astra-sites', 'Astra_Sites_WP_CLI' );

endif;
