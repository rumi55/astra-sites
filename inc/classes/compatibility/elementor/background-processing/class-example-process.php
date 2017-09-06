<?php

if( class_exists( 'WP_Background_Process' ) ) :

	class Astra_Elementor_Image_Importer_Process extends WP_Background_Process {

		use WP_Example_Logger;

		/**
		 * @var string
		 */
		protected $action = 'example_process';

		/**
		 * Task
		 *
		 * Override this method to perform any actions required on each
		 * queue item. Return the modified item for further processing
		 * in the next pass through. Or, return false to remove the
		 * item from the queue.
		 *
		 * @param mixed $item Queue item to iterate over
		 *
		 * @return mixed
		 */
		protected function task( $item ) {
			// $message = $this->get_message( $item );

			$this->really_long_running_task();
			// $this->log( $message );
			$this->log( 'Started' );

			$this->hotlink_images( $item );

			// // Gather post data.
			// $my_post = array(
			// 	'post_title'   => 'Post : ' . $item,
			// 	'post_content' => $message,
			// 	'post_status'  => 'publish',
			// );

			// // Insert the post into the database.
			// wp_insert_post( $my_post );

			return false;
		}

		/**
		 * Update post meta.
		 */
		function hotlink_images( $post_id ) {

			$import = new \Elementor\TemplateLibrary\Astra_Sites_Source_Remote();
			$import->hotlink_images( $post_id );

			if( get_option( '_astra_sites_elementor_image_import' ) ) {
				error_log( '_astra_sites_elementor_image_import : TRUE' );
			} else {
				error_log( '_astra_sites_elementor_image_import : FALSE' );
			}

			$this->log( 'Imported hotlink images for Post ID: ' . $post_id );
			$this->log( '----------------------------------------------------------------' );

			// $hotlink_imported = get_post_meta( $post_id, '_astra_sites_hotlink_imported', true );
			// $this->log( $hotlink_imported );

			// if( empty( $hotlink_imported ) ) {

			// 	$data = get_post_meta( get_the_id(), '_elementor_data', true );
			// 	$this->log( '$data' );
			// 	$this->log( $data );

			// 	$data = json_decode( $data, true );

			// 	if ( ! is_wp_error( $data ) ) {

			// 		$source_base = new Elementor\TemplateLibrary\Astra_Sites_Source_Remote();

			// 		$data = $source_base->replace_elements_ids( $data );
			// 		$data = $source_base->process_export_import_content( $data, 'on_import' );

			// 		$json_value = wp_slash( wp_json_encode( $data ) );

			// 		$this->log( '$json_value' );
			// 		$this->log( $json_value );

			// 		update_metadata( 'post', get_the_id(), '_elementor_data', $json_value );
			// 		update_metadata( 'post', get_the_id(), '_astra_sites_hotlink_imported', true );

			// 		// !important, Clear the cache after images import.
			// 		Elementor\Plugin::$instance->posts_css_manager->clear_cache();

			// 		$this->log( 'Imported hotlink images for Post ID: ' . $post_id );

			// 	} else {
			// 		$this->log( 'Error while importing hotlink images for Post ID: ' . $post_id );
			// 	}
			// } else {
			// 	$this->log( 'Already imported hotlink images for Post ID: ' . $post_id );
			// }

		}

		/**
		 * Complete
		 *
		 * Override if applicable, but ensure that the below actions are
		 * performed, or, call parent::complete().
		 */
		protected function complete() {
			parent::complete();

			if( get_option( '_astra_sites_elementor_image_import' ) ) {
				error_log( '_astra_sites_elementor_image_import : TRUE' );
			} else {
				error_log( '_astra_sites_elementor_image_import : FALSE' );
			}

			do_action( 'astra_sites_elementor_image_import_complete' );

			if( get_option( '_astra_sites_elementor_image_import' ) ) {
				error_log( '_astra_sites_elementor_image_import : TRUE' );
			} else {
				error_log( '_astra_sites_elementor_image_import : FALSE' );
			}

			error_log('Import process complete');

			// Show notice to user or perform some other arbitrary task...
		}

	}

endif;
