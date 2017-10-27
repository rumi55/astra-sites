<?php
/**
 * Image Importer
 * 
 * => How to use?
 * 
 *	$image = array(
 *		'url' => '<image-url>',
 *		'id'  => '<image-id>',
 *	);
 *
 *  $downloaded_image = Astra_Sites_Image_Imorter::set_instance()->import( $image );
 *
 * @package Astra Sites
 * @since 1.0.0
 */

if( ! class_exists( 'Astra_Sites_Image_Imorter' ) ) :

	/**
	 * Astra_Sites_Image_Imorter
	 *
	 * @since 1.0.0
	 */
	class Astra_Sites_Image_Imorter {

		private $already_imported_ids = [];

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

			if ( ! function_exists( 'WP_Filesystem' ) ) {
				require_once ABSPATH . 'wp-admin/includes/file.php';
			}

			WP_Filesystem();
		}

		/**
		 * Process Image Download
		 * 
		 * @param  array $attachments Attachment array.
		 * @return array              Attachment array.
		 */
		public function process( $attachments ) {

			$downloaded_images = array();

			foreach ($attachments as $key => $attachment) {
				$downloaded_images[] = $this->import( $attachment );
			}

			return $downloaded_images;
		}

		/**
		 * Get Hash Image.
		 * 
		 * @param  string $attachment_url Attachment URL.
		 * @return string                 Hash string.
		 */
		private function get_hash_image( $attachment_url ) {
			return sha1( $attachment_url );
		}

		/**
		 * Get Saved Image.
		 * 
		 * @param  string $attachment_url Attachment URL.
		 * @return string                 Hash string.
		 */
		private function get_saved_image( $attachment ) {

			global $wpdb;

			// Already imported? Then return!
			if ( isset( $this->already_imported_ids[ $attachment['id'] ] ) ) {

				// @Debug Log
				Astra_Sites_Image_Imorter::log( 'Already Processed Image ' . basename( $attachment['url'] ) );

				return $this->already_imported_ids[ $attachment['id'] ];
			}

			// 1. Is already imported in Batch Import Process?
			$post_id = $wpdb->get_var(
				$wpdb->prepare(
					'SELECT `post_id` FROM `' . $wpdb->postmeta . '`
						WHERE `meta_key` = \'_astra_sites_image_hash\'
							AND `meta_value` = %s
					;',
					$this->get_hash_image( $attachment['url'] )
				)
			);

			// 2. Is image already imported though XML?
			if( empty( $post_id ) ) {

				// Get file name without extension.
				// To check it exist in attachment.
				$filename = preg_replace('/\\.[^.\\s]{3,4}$/', '', basename( $attachment['url'] ) );

				$post_id = $wpdb->get_var(
					$wpdb->prepare(
						'SELECT `post_id` FROM `' . $wpdb->postmeta . '`
							WHERE `meta_key` = \'_wp_attached_file\'
							AND `meta_value` LIKE %s
						;',
						'%' . $filename . '%'
					)
				);

				// @Debug Log
				Astra_Sites_Image_Imorter::log( 'Imported from XML. ' . basename( $attachment['url'] ) );

			} else {

				// @Debug Log
				Astra_Sites_Image_Imorter::log( 'Imported from Batch Import Process. ' . basename( $attachment['url'] ) );
			}

			if ( $post_id ) {
				$new_attachment = array(
					'id'  => $post_id,
					'url' => wp_get_attachment_url( $post_id ),
				);
				$this->already_imported_ids[ $attachment['id'] ] = $new_attachment;

				return $new_attachment;
			}

			return false;
		}

		/**
		 * Import Image
		 * 
		 * @param  array $attachments Attachment array.
		 * @return array              Attachment array.
		 */
		public function import( $attachment ) {
			
			$saved_image = $this->get_saved_image( $attachment );
			if ( $saved_image ) {
				return $saved_image;
			}

			// Extract the file name and extension from the url
			$filename = basename( $attachment['url'] );

			if ( function_exists( 'file_get_contents' ) ) {
				$options = array(
					'http' => array(
						'user_agent' => 'Mozilla/5.0 (X11; Ubuntu; Linux i686 on x86_64; rv:49.0) Gecko/20100101 Firefox/49.0',
					),
				);

				$context = stream_context_create( $options );

				$file_content = file_get_contents( $attachment['url'], false, $context );
			} else {
				$file_content = wp_remote_retrieve_body( wp_safe_remote_get( $attachment['url'] ) );
			}

			// Empty file content?
			if ( empty( $file_content ) ) {
				return false;
			}

			$upload = wp_upload_bits(
				$filename,
				null,
				$file_content
			);

			$post = array(
				'post_title' => $filename,
				'guid'       => $upload['url'],
			);

			$info = wp_check_filetype( $upload['file'] );
			if ( $info ) {
				$post['post_mime_type'] = $info['type'];
			} else {
				// For now just return the origin attachment
				return $attachment;
				// return new \WP_Error( 'attachment_processing_error', __( 'Invalid file type', 'elementor' ) );
			}

			$post_id = wp_insert_attachment( $post, $upload['file'] );
			wp_update_attachment_metadata(
				$post_id,
				wp_generate_attachment_metadata( $post_id, $upload['file'] )
			);
			update_post_meta( $post_id, '_astra_sites_image_hash', $this->get_hash_image( $attachment['url'] ) );

			$new_attachment = array(
				'id'  => $post_id,
				'url' => $upload['url'],
			);

			$this->already_imported_ids[ $attachment['id'] ] = $new_attachment;

			return $new_attachment;
		}

		/**
		 * Debugging Log.
		 * 
		 * @param  [type] $log [description]
		 * @return [type]      [description]
		 */
		public static function log( $log )  {
			
			if( ! WP_DEBUG_LOG ) {
				return;
			}

	  		if ( is_array( $log ) || is_object( $log ) ) {
				error_log( print_r( $log, true ) );
	  		} else {
				error_log( $log );
	  		}
	   	}

	}

	/**
	 * Kicking this off by calling 'set_instance()' method
	 */
	Astra_Sites_Image_Imorter::set_instance();

endif;