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
	class Astra_Sites_Image_Imorter_Process extends WP_Background_Process {

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
		 * @since 1.0.11
		 *
		 * @param mixed $post_id Queue item to iterate over.
		 * @return mixed
		 */
		protected function task( $process ) {

			if( method_exists( $process, 'import') ) {
				$process->import();
			}
			// Process Widget.
			
			// if( 'widget' === $process ) {
			// 	Astra_Sites_Batch_Processing_Widgets::set_instance()->import();
			// }

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
		protected function complete() {
			error_log('completed');

			parent::complete();

			do_action( 'astra_sites_image_import_complete' );

		}

	}

endif;