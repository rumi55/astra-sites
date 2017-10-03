<?php
/**
 * Image Background Process
 *
 * @package Astra Sites
 * @since 1.0.11
 */

if ( class_exists( 'WP_Background_Process' ) ) :

	/**
	 * Image Background Process
	 *
	 * @since 1.0.11
	 */
	class Astra_Elementor_Image_Importer_Process extends WP_Background_Process {

		/**
		 * Image Process
		 *
		 * @var string
		 */
		public $action = 'image_process';

		/**
		 * Task
		 *
		 * Override this method to perform any actions required on each
		 * queue item. Return the modified item for further processing
		 * in the next pass through. Or, return false to remove the
		 * item from the queue.
		 *
		 * @since 1.0.11
		 *
		 * @param mixed $post_id Queue item to iterate over.
		 * @return mixed
		 */
		public function task( $post_id ) {

			$import = new \Elementor\TemplateLibrary\Astra_Sites_Source_Remote();
			$import->hotlink_images( $post_id );

			return false;
		}

		/**
		 * Complete
		 *
		 * Override if applicable, but ensure that the below actions are
		 * performed, or, call parent::complete().
		 *
		 * @since 1.0.11
		 */
		public function complete() {

			parent::complete();

			do_action( 'astra_sites_image_import_complete' );

		}

	}

endif;
