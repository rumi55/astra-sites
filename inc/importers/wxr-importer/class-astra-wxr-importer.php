<?php
/**
 * Class Astra WXR Importer
 *
 * @since  1.0.0
 * @package Astra Addon
 */

defined( 'ABSPATH' ) or exit;

/**
 * Class Astra WXR Importer
 *
 * @since  1.0.0
 */
class Astra_WXR_Importer {

	/**
	 * Instance of Astra_WXR_Importer
	 *
	 * @since  1.0.0
	 * @var Astra_WXR_Importer
	 */
	private static $_instance = null;

	/**
	 * Instantiate Astra_WXR_Importer
	 *
	 * @since  1.0.0
	 * @return (Object) Astra_WXR_Importer.
	 */
	public static function instance() {
		if ( ! isset( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	/**
	 * Constructor.
	 *
	 * @since  1.0.0
	 */
	private function __construct() {
		$this->includes();

		add_filter( 'upload_mimes', array( $this, 'custom_upload_mimes' ) );
		add_filter( 'wxr_importer.pre_process.user', '__return_false' );
	}

	/**
	 * Add .xml files as supported format in the uploader.
	 *
	 * @param array $mimes Already supported mime types.
	 */
	public function custom_upload_mimes( $mimes ) {
		$mimes = array_merge(
			$mimes, array(
				'xml' => 'application/xml',
			)
		);

		return $mimes;
	}

	/**
	 * Include required files.
	 *
	 * @since  1.0.0
	 */
	private function includes() {
		if ( ! class_exists( 'WP_Importer' ) ) {
			defined( 'WP_LOAD_IMPORTERS' ) || define( 'WP_LOAD_IMPORTERS', true );
			require ABSPATH . '/wp-admin/includes/class-wp-importer.php';
		}
		// require_once ASTRA_SITES_DIR . 'inc/importers/wxr-importer/class-wxr-importer.php';
		// require_once ASTRA_SITES_DIR . 'inc/importers/wxr-importer/class-logger.php';

		require_once ASTRA_SITES_DIR . 'inc/importers/wxr-importer/class-logger.php';
		require_once ASTRA_SITES_DIR . 'inc/importers/wxr-importer/class-wp-importer-logger-serversentevents.php';
		require_once ASTRA_SITES_DIR . 'inc/importers/wxr-importer/class-wxr-importer.php';
		require_once ASTRA_SITES_DIR . 'inc/importers/wxr-importer/class-wxr-import-info.php';

		// require dirname( __FILE__ ) . '/class-logger.php';
		// require dirname( __FILE__ ) . '/class-logger-cli.php';
		// require dirname( __FILE__ ) . '/class-logger-html.php';
		// require dirname( __FILE__ ) . '/class-logger-serversentevents.php';
		// require dirname( __FILE__ ) . '/class-wxr-importer.php';
		// require dirname( __FILE__ ) . '/class-wxr-import-info.php';
		// require dirname( __FILE__ ) . '/class-wxr-import-ui.php';
	}

	/**
	 * Start the xml import.
	 *
	 * @since  1.0.0
	 *
	 * @param  (String) $path Absolute path to the XML file.
	 */
	public function import_xml( $path ) {

		$args = array(
			'action' => 'wxr-import',
			'id'     => '1',
		);
		$url = add_query_arg( urlencode_deep( $args ), admin_url( 'admin-ajax.php' ) );
		// vl( '$url' );
		// vl( $url );

		$data = $this->get_data( $path );
		// vl( '$path' );
		// vl( $path );
		// vl( '$data' );
		// vl( $data );

		$script_data = array(
			'count' => array(
				'posts' => $data->post_count,
				'media' => $data->media_count,
				'users' => count( $data->users ),
				'comments' => $data->comment_count,
				'terms' => $data->term_count,
			),
			'url' => $url,
			'strings' => array(
				'complete' => __( 'Import complete!', 'wordpress-importer' ),
			),
		);

		return $script_data;
	}

	function get_data( $url ) {
		$importer = $this->get_importer();
	    $data = $importer->get_preliminary_information( $url );
	    if ( is_wp_error( $data ) ) {
	        return $data;
	    }
	    return $data;
	}

	public function get_importer() {
		$options  = array(
			'fetch_attachments' => true,
			'default_author'    => get_current_user_id(),
		);
		$logger   = new WP_Importer_Logger();
		$importer = new WXR_Importer( $options );
		$importer->set_logger( $logger );
		// $result = $importer->import( $path );
		return $importer;
	}

}
