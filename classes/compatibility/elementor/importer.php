<?php

// If plugin - 'Elementor' not exist then return.
if ( ! class_exists( '\Elementor\Plugin' ) ) {
	return;
}

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
			// add_action( 'admin_head-post.php', array( $this, 'hotlink_images' ) );
		}

		/**
		 * Update post meta.
		 */
		public function hotlink_images( $post_id = 0 ) {


			if( 0 == $post_id ) {
				$post_id = get_the_ID();
			}

			if( ! empty( $post_id ) ) {

				// delete_post_meta( $post_id, '_astra_sites_hotlink_imported' );
				// wp_die();

				error_log( $post_id );

				// $all = get_post_meta( $post_id );
				// error_log( $all );

				$hotlink_imported = get_post_meta( $post_id, '_astra_sites_hotlink_imported', true );

				// error_log( $hotlink_imported, true );

				if( empty( $hotlink_imported ) ) {

					$data = get_post_meta( $post_id, '_elementor_data', true );
					// vl( $all );

					if ( $data ) {

						$data = json_decode( $data, true );

						error_log( 'ok' );
						// wp_die();

						$source_base = new \Elementor\TemplateLibrary\Astra_Sites_Source_Remote();

						$data = $source_base->replace_elements_ids( $data );
						$data = $source_base->process_export_import_content( $data, 'on_import' );

						// $json_value = wp_slash( wp_json_encode( $data ) );
						$json_value = wp_json_encode( $data );

						// error_log( $json_value );


						update_metadata( 'post', $post_id, '_elementor_data', $json_value );
						update_metadata( 'post', $post_id, '_astra_sites_hotlink_imported', true );

						// !important, Clear the cache after images import.
						\Elementor\Plugin::$instance->posts_css_manager->clear_cache();
						// wp_die();

					}
				}
			}


		}

	}

	/**
	 * Kicking this off by calling 'set_instance()' method
	 */
	Astra_Sites_Compatibility_Elementor::set_instance();

endif;

