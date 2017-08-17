<?php

namespace Elementor;

/**
 * Astra Sites Compatibility Elementor
 *
 * @package Astra Sites
 * @since 1.0.4
 */
if( ! class_exists( 'Astra_Sites_Compatibility_Elementor' ) ) :

	/**
	 * Astra Sites Compatibility Elementor
	 *
	 * @since 1.0.4
	 */
	class Astra_Sites_Compatibility_Elementor {

		/**
		 * Instance
		 *
		 * @access private
		 * @since 1.0.4
		 */
		private static $instance;

		/**
		 * Initiator
		 *
		 * @since 1.0.4
		 * @return object initialized object of class.
		 */
		public static function set_instance(){
			if ( ! isset( self::$instance ) ) {
				self::$instance = new self;
			}
			return self::$instance;
		}

		/**
		 * Constructor
		 *
		 * @since 1.0.4
		 */
		public function __construct() {
			add_action( 'admin_head-post.php', array( $this, 'hotlink_images' ) );
		}

		/**
		 * Update post meta.
		 */
		function hotlink_images() {

			$hotlink_imported = get_post_meta( get_the_ID(), '_astra_sites_hotlink_imported', true );
			if( empty( $hotlink_imported ) ) {

				$data = get_post_meta( get_the_id(), '_elementor_data', true );
				$data = json_decode( $data, true );

				if ( ! is_wp_error( $data ) ) {

					$source_base = new TemplateLibrary\Astra_Sites_Source_Remote();

					$data = $source_base->replace_elements_ids( $data );
					$data = $source_base->process_export_import_content( $data, 'on_import' );

					$json_value = wp_slash( wp_json_encode( $data ) );

					update_metadata( 'post', get_the_id(), '_elementor_data', $json_value );
					update_metadata( 'post', get_the_id(), '_astra_sites_hotlink_imported', true );

					// !important, Clear the cache after images import.
					Plugin::$instance->posts_css_manager->clear_cache();

				}
			}

		}

	}

	/**
	 * Kicking this off by calling 'set_instance()' method
	 */
	Astra_Sites_Compatibility_Elementor::set_instance();

endif;

