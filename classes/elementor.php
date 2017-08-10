<?php
namespace Elementor;

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
		}
		function close() {
		}
		public function test() {
			// vl( get_post_meta( get_the_id() ) );
			// vl( get_post_meta( get_the_id(), '_elementor_data' ) );
			// vl( get_post_meta( get_the_id(), '_elementor_page_settings', true ) );
			$data = get_post_meta( get_the_id(), '_elementor_data', true );
			$data = json_decode( $data );
			vl( $data );
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

