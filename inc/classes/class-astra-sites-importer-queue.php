<?php
// function wpimportv2_init() {
// 	/**
// 	 * WordPress Importer object for registering the import callback
// 	 * @global WP_Import $wp_import
// 	 */
// 	$GLOBALS['wxr_importer'] = new WXR_Import_UI();
// 	register_importer(
// 		'wordpress',
// 		'WordPress (v2)',
// 		__( 'Import <strong>posts, pages, comments, custom fields, categories, and tags</strong> from a WordPress export (WXR) file.', 'wordpress-importer' ),
// 		array( $GLOBALS['wxr_importer'], 'dispatch' )
// 	);
//	add_action( 'wp_ajax_wxr-import', array( $GLOBALS['wxr_importer'], 'stream_import' ) );
// 	add_action( 'load-importer-wordpress', array( $GLOBALS['wxr_importer'], 'on_load' ) );
// }
// add_action( 'admin_init', 'wpimportv2_init' );


/**
 * MY CLASS NAME
 *
 * @package MY PACKAGE
 * @since 1.0.0
 */

if( ! class_exists( 'AstraImporterQueue' ) ) :

	/**
	 * AstraImporterQueue
	 *
	 * @since 1.0.0
	 */
	class AstraImporterQueue {

		/**
		 * Should we fetch attachments?
		 *
		 * Set in {@see display_import_step}.
		 *
		 * @var bool
		 */
		protected $fetch_attachments = false;

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
			add_action( 'wp_ajax_astra-wxr-import', array( $this, 'stream_import' ) );
		}


		/**
		 * Run an import, and send an event-stream response.
		 *
		 * Streams logs and success messages to the browser to allow live status
		 * and updates.
		 */
		public function stream_import() {
			// Turn off PHP output compression
			$previous = error_reporting( error_reporting() ^ E_WARNING );
			ini_set( 'output_buffering', 'off' );
			ini_set( 'zlib.output_compression', false );
			error_reporting( $previous );

			if ( $GLOBALS['is_nginx'] ) {
				// Setting this header instructs Nginx to disable fastcgi_buffering
				// and disable gzip for this request.
				header( 'X-Accel-Buffering: no' );
				header( 'Content-Encoding: none' );
			}

			// Start the event stream.
			header( 'Content-Type: text/event-stream' );

			// $this->id = wp_unslash( (int) $_REQUEST['id'] );
			// $settings = get_post_meta( $this->id, '_wxr_import_settings', true );
			// $settings = $_REQUEST['xmlData'];
			
			$settings = array(
                'mapping' => array(
                    'mapping' => array(),
                    'slug_overrides' => array()
                ),
                'fetch_attachments' => false
            );

			if ( empty( $settings ) ) {
				// Tell the browser to stop reconnecting.
				status_header( 204 );
				exit;
			}

			// 2KB padding for IE
			echo ':' . str_repeat( ' ', 2048 ) . "\n\n";

			// Time to run the import!
			set_time_limit( 0 );

			// Ensure we're not buffered.
			wp_ob_end_flush_all();
			flush();

			$mapping = $settings['mapping'];
			// $this->fetch_attachments = (bool) $settings['fetch_attachments'];

			// // $importer = $this->get_importer();
			$importer =  Astra_WXR_Importer::instance()->get_importer();
			if ( ! empty( $mapping['mapping'] ) ) {
				$importer->set_user_mapping( $mapping['mapping'] );
			}
			if ( ! empty( $mapping['slug_overrides'] ) ) {
				$importer->set_user_slug_overrides( $mapping['slug_overrides'] );
			}

			// // Are we allowed to create users?
			if ( ! $this->allow_create_users() ) {
				add_filter( 'wxr_importer.pre_process.user', '__return_null' );
			}


			// Keep track of our progress
			add_action( 'wxr_importer.processed.post', array( $this, 'imported_post' ), 10, 2 );
			add_action( 'wxr_importer.process_failed.post', array( $this, 'imported_post' ), 10, 2 );
			add_action( 'wxr_importer.process_already_imported.post', array( $this, 'already_imported_post' ), 10, 2 );
			add_action( 'wxr_importer.process_skipped.post', array( $this, 'already_imported_post' ), 10, 2 );
			add_action( 'wxr_importer.processed.comment', array( $this, 'imported_comment' ) );
			add_action( 'wxr_importer.process_already_imported.comment', array( $this, 'imported_comment' ) );
			add_action( 'wxr_importer.processed.term', array( $this, 'imported_term' ) );
			add_action( 'wxr_importer.process_failed.term', array( $this, 'imported_term' ) );
			add_action( 'wxr_importer.process_already_imported.term', array( $this, 'imported_term' ) );
			add_action( 'wxr_importer.processed.user', array( $this, 'imported_user' ) );
			add_action( 'wxr_importer.process_failed.user', array( $this, 'imported_user' ) );

			// Clean up some memory
			unset( $settings );

			// Flush once more.
			flush();

			// $file = get_attached_file( $this->id );
			// $file = $settings['tempXmlUrl'];
			// var_dump( $file );

			// $file = $settings['tempXmlUrl'];

			// $xml_path     = $importer->download_xml( $file );
  			// $file = $importer->import_xml( $xml_path['file'] );
			// $this->testme( $settings['tempXmlUrl'] );
			// $this->testme( $xml_path );

			$file = 'C:\xampp\htdocs\m.sharkz.in/wp-content/uploads/2017/09/wptest.xml.txt';
			$err = $importer->import( $file );

			// Remove the settings to stop future reconnects.
			delete_post_meta( $this->id, '_wxr_import_settings' );

			// Let the browser know we're done.
			$complete = array(
				'action' => 'complete',
				'error' => false,
			);
			if ( is_wp_error( $err ) ) {
				$complete['error'] = $err->get_error_message();
			}

			$this->emit_sse_message( $complete );
			exit;
		}

		/**
		 * Send message when a post has been imported.
		 *
		 * @param int $id Post ID.
		 * @param array $data Post data saved to the DB.
		 */
		public function testme( $tempXmlUrl ) {
			$this->emit_sse_message( array(
				'action' => 'updateDelta',
				'type'   => $tempXmlUrl,
				'delta'  => 1,
			));
		}

		/**
		 * Send message when a user has been imported.
		 */
		public function imported_user() {
			$this->emit_sse_message( array(
				'action' => 'updateDelta',
				'type'   => 'users',
				'delta'  => 1,
			));
		}

		/**
		 * Send message when a term has been imported.
		 */
		public function imported_term() {
			$this->emit_sse_message( array(
				'action' => 'updateDelta',
				'type'   => 'terms',
				'delta'  => 1,
			));
		}

		/**
		 * Send message when a comment has been imported.
		 */
		public function imported_comment() {
			$this->emit_sse_message( array(
				'action' => 'updateDelta',
				'type'   => 'comments',
				'delta'  => 1,
			));
		}

		/**
		 * Send message when a post is marked as already imported.
		 *
		 * @param array $data Post data saved to the DB.
		 */
		public function already_imported_post( $data ) {
			$this->emit_sse_message( array(
				'action' => 'updateDelta',
				'type'   => ( $data['post_type'] === 'attachment' ) ? 'media' : 'posts',
				'delta'  => 1,
			));
		}

		/**
		 * Send message when a post has been imported.
		 *
		 * @param int $id Post ID.
		 * @param array $data Post data saved to the DB.
		 */
		public function imported_post( $id, $data ) {
			$this->emit_sse_message( array(
				'action' => 'updateDelta',
				'type'   => ( $data['post_type'] === 'attachment' ) ? 'media' : 'posts',
				'delta'  => 1,
			));
		}

		/**
		 * Emit a Server-Sent Events message.
		 *
		 * @param mixed $data Data to be JSON-encoded and sent in the message.
		 */
		protected function emit_sse_message( $data ) {
			echo "event: message\n";
			echo 'data: ' . wp_json_encode( $data ) . "\n\n";

			// Extra padding.
			echo ':' . str_repeat( ' ', 2048 ) . "\n\n";

			flush();
		}

		/**
		 * Decide whether or not the importer is allowed to create users.
		 * Default is true, can be filtered via import_allow_create_users
		 *
		 * @return bool True if creating users is allowed
		 */
		protected function allow_create_users() {
			return apply_filters( 'import_allow_create_users', true );
		}

	}

	/**
	 * Kicking this off by calling 'set_instance()' method
	 */
	AstraImporterQueue::set_instance();

endif;