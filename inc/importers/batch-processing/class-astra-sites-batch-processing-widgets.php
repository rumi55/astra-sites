<?php
/**
 * Batch Processing
 *
 * @package Astra Sites
 * @since 1.0.0
 */

if( ! class_exists( 'Astra_Sites_Batch_Processing_Widgets' ) ) :

	/**
	 * Astra_Sites_Batch_Processing_Widgets
	 *
	 * @since 1.0.0
	 */
	class Astra_Sites_Batch_Processing_Widgets {

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
		}

		public function import() {

			// Media Widget.
			$this->widget_media_image();
		}

		public function widget_media_image() {

			$data = get_option( 'widget_media_image', null );

			// @Debug Log
			Astra_Sites_Image_Imorter::log('------------------------- WIDGETS - START -----------------------------');

			foreach ( $data as $key => $value) {

				if(
					isset( $value['url'] ) &&
					isset( $value['attachment_id'] )
				) {

					// @Debug Log
					Astra_Sites_Image_Imorter::log( 'BG_IMAGE' . $value['attachment_id'] . ' : ' . $value['url'] );

					$image = array(
					    'url' => $value['url'],
					    'id'  => $value['attachment_id'],
					);

					$downloaded_image = Astra_Sites_Image_Imorter::set_instance()->import( $image );

					$data[ $key ]['url']           = $downloaded_image['url'];
					$data[ $key ]['attachment_id'] = $downloaded_image['id'];
				}
			}

			// @Debug Log
			Astra_Sites_Image_Imorter::log('------------------------- WIDGETS - END -----------------------------');

			update_option( 'widget_media_image', $data );
		}
	}

	/**
	 * Kicking this off by calling 'set_instance()' method
	 */
	Astra_Sites_Batch_Processing_Widgets::set_instance();

endif;