<?php


/**
 * MY CLASS NAME
 *
 * @package MY CLASS NAME
 * @since 1.0.0
 */

if( ! class_exists( 'TestMeElemenotr' ) ) :

	/**
	 * TestMeElemenotr
	 *
	 * @since 1.0.0
	 */
	class TestMeElemenotr {

		/**
		 * Instance
		 *
		 * @access private
		 * @since 1.0.0
		 */
		private static $instance;

		/**
		 * Initiator
		 *
		 * @since 1.0.0
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
		 * @since 1.0.0
		 */
		public function __construct() {

			// add_action( 'shutdown', array( $this, 'close' ) );
			// add_action( 'admin_init', array( $this, 'get_attachments' ) );
			add_action( 'admin_notices', array( $this, 'test' ) );
			// add_action( 'wp_head', array( $this, 'test' ) );
		}
		function close() {
		}
		public function test() {
			// vl( get_post_meta( get_the_id() ) );
			// wp_die();
			// vl( get_post_meta( get_the_id(), '_elementor_data' ) );
			// vl( get_post_meta( get_the_id(), '_elementor_page_settings', true ) );
			// $data = array();
			$data = get_post_meta( get_the_id(), '_elementor_data', true );
			vl( $data );
			$data = json_decode( $data, true );


			if ( is_wp_error( $data ) ) {
				return $data;
			}

			// TODO: since 1.5.0 to content container named `content` instead of `data`.
			// if ( ! empty( $data['data'] ) ) {
			// 	$data['content'] = $data['data'];
			// 	unset( $data['data'] );
			// }

			$source_base = new Elementor\TemplateLibrary\Source_Remote();

			$data = $source_base->replace_elements_ids( $data );
			$data = $source_base->process_export_import_content( $data, 'on_import' );

			// $data = wp_slash( wp_json_encode( $data ) );
			// vl( $data );
			// wp_die();


			// vl( $data['content'] );

			// if ( ! empty( $args['page_settings'] ) && ! empty( $data['page_settings'] ) ) {
			// 	$page = new Page( [
			// 		'settings' => $data['page_settings'],
			// 	] );

			// 	$page_settings_data = $source_base->process_element_export_import_content( $page, 'on_import' );
			// 	$data['page_settings'] = $page_settings_data['settings'];
			// }

// vl( json_decode( get_post_meta( get_the_id(), '_elementor_data', true ), 1) );
// vl( json_decode( get_post_meta( get_the_id(), '_elementor_data', true ), 1 ) );

// vl( $data );


			$json_value = wp_slash( wp_json_encode( $data ) );
			// vl( $json_value );

			// CHECK IS JSON_DECODE?
			// $data = json_decode( $data, true );
			// vl( $data );

			// update_post_meta( get_the_id(), '_elementor_data', $data );
			// vl( get_post_meta( get_the_id(), '_elementor_data', true ) );
			// $is_meta_updated = update_metadata( 'post', get_the_id(), '_elementor_data', $json_value );
		}

		public function get_attachments() {
			$all_attachments = array();
			$attachments = get_posts( array(
			    'posts_per_page' => -1,
			    'post_type' => 'attachment',
			) );
			// vl( $attachments );
			foreach ($attachments as $key => $attachment) {
				$settings = array(
					'id' => $attachment->ID,
					'url'  => $attachment->guid,
				);
				$settings = Plugin::$instance->templates_manager->get_import_images_instance()->import( $settings );
				vl( $settings );
			}
			wp_die( );
		}


	}

	/**
	 * Kicking this off by calling 'set_instance()' method
	 */
	TestMeElemenotr::set_instance();

endif;

