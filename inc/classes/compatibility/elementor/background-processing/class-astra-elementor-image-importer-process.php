<?php
/**
 * Image Background Process
 *
 * @package Astra Sites
 * @since 1.0.4
 */

if ( class_exists( 'WP_Background_Process' ) ) :

	/**
	 * Image Background Process
	 *
	 * @since 1.0.4
	 */
	class Astra_Elementor_Image_Importer_Process extends WP_Background_Process {

		/**
		 * Image Process
		 *
		 * @var string
		 */
		protected $action = 'image_process';

		/**
		 * Task
		 *
		 * Override this method to perform any actions required on each
		 * queue item. Return the modified item for further processing
		 * in the next pass through. Or, return false to remove the
		 * item from the queue.
		 *
		 * @param mixed $post_id Queue item to iterate over.
		 *
		 * @return mixed
		 */
		protected function task( $post_id ) {

			error_log( 'Importing Start!' );

			$import = new \Elementor\TemplateLibrary\Astra_Sites_Source_Remote();
			$import->hotlink_images( $post_id );

			error_log( 'Successfully Imported #' . $post_id );
			error_log( '----------------------------------------------------------------' );

			return false;

		}

		/**
		 * Complete
		 *
		 * Override if applicable, but ensure that the below actions are
		 * performed, or, call parent::complete().
		 */
		protected function complete() {
			parent::complete();

			error_log( 'Import process complete' );
		}

	}

endif;
