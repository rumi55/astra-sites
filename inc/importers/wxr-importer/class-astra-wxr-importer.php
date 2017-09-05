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

		add_filter( 'upload_mimes',                     array( $this, 'custom_upload_mimes' ) );
		add_filter( 'wxr_importer.pre_process.user',    array( $this, 'avoid_user' ), 10, 2 );
	}

	/**
	 * Get the importer instance.
	 *
	 * @return WXR_Importer
	 */
	public function get_importer() {
		$options  = array(
			'fetch_attachments' => true,
			'default_author'    => 0,
		);
		$importer = new WXR_Importer( $options );
		// $logger = new WP_Importer_Logger_ServerSentEvents();
		// $importer->set_logger( $logger );

		return $importer;
	}

	public function info( $xml_url ) {
		$importer = $this->get_importer();
		$data = $importer->get_preliminary_information( $xml_url );

		// if ( is_wp_error( $data ) ) {
		// 	return $data;
		// }
		return $data;
	}

	/**
	 * Pre-process user data.
	 *
	 * @since 1.0.3
	 * @param array $data User data. (Return empty to skip.).
	 * @param array $meta Meta data.
	 */
	function avoid_user( $data, $meta ) {
		return '';
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
		require_once ASTRA_SITES_DIR . 'inc/importers/wxr-importer/class-wxr-import-info.php';
		require_once ASTRA_SITES_DIR . 'inc/importers/wxr-importer/class-wxr-importer.php';
		require_once ASTRA_SITES_DIR . 'inc/importers/wxr-importer/class-logger.php';
	}

	/**
	 * Start the xml import.
	 *
	 * @since  1.0.0
	 *
	 * @param  (String) $path Absolute path to the XML file.
	 */
	public function import_xml( $path ) {
		$options  = array(
			'fetch_attachments' => true,
			'default_author'    => 0,
		);
		$logger   = new WP_Importer_Logger();
		$importer = new WXR_Importer( $options );
		$importer->set_logger( $logger );
		$result = $importer->import( $path );
	}

	/**
	 * Download and save XML file to uploads directory.
	 *
	 * @since  1.0.0
	 *
	 * @param  (String) $url URL of the xml file.
	 *
	 * @return (Array)      Attachment array of the downloaded xml file.
	 */
	public function download_xml( $url ) {

		// Download XML file.
		$response = Astra_Sites_Helper::download_file( $url );

		// Is Success?
		if ( $response['success'] ) {
			return $response['data'];
		}

	}

}




// add_action( 'admin_head', function() {

// 	// C:\xampp\htdocs\m.sharkz.in/wp-content/uploads/2017/09/wxr-29.xml
// 	// http://sites-wpastra.sharkz.in/wp-json/wp/v2/astra-sites/17383
// 	// http://sites-wpastra.sharkz.in/wp-content/uploads/astra-sites/pet-services/wxr.xml

// 	$wxr_url = 'http://sites-wpastra.sharkz.in/wp-content/uploads/astra-sites/car-repair/wxr.xml';

// 	$importer = Astra_WXR_Importer::instance();
// 	$xml_path     = $importer->download_xml( $wxr_url );
// 	// $importer->import_xml( $xml_path['file'] );

// 	$data = $importer->info( $xml_path['file'] );
// 	vl( $data );
	

// 	wp_die();
// } );


