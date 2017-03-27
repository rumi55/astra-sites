<?php

defined( 'ABSPATH' ) or exit;

class Astra_Demo_Import {

	private static $api_url;

	private static $_instance = null;

	public static function instance( $importer_api ) {
		if ( ! isset( self::$_instance ) ) {
			self::$_instance = new self( $importer_api );
		}

		return self::$_instance;
	}

	private function __construct( $importer_api ) {
		self::set_api_url( $importer_api );
		$this->includes();
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue' ) );
		add_action( 'wp_ajax_astra-import-demo', array( $this, 'demo_ajax_import' ) );
	}

	private static function set_api_url( $importer_api ) {
		self::$api_url = $importer_api;
	}

	public static function get_api_url() {
		return self::$api_url;
	}

	public function admin_enqueue() {
		wp_register_script( 'astra-demo-import-admin', ADI_URI . 'assets/js/admin.js', array( 'jquery' ), ADI_VER, true );
	}

	private function includes() {
		require_once ADI_DIR . 'admin/class-astra-demo-import-admin.php';

		// Load the Importers.
		require_once ADI_DIR . 'importers/class-widgets-importer.php';
		require_once ADI_DIR . 'importers/class-customizer-import.php';
	}

	public function demo_ajax_import() {
		$demo_api_uri = isset( $_POST['api_url'] ) ? esc_url( $_POST['api_url'] ) : '';
		$this->import_demo( $demo_api_uri );
	}

	public function import_demo( $demo_api_uri ) {
		$demo_data = self::get_astra_single_demo( $demo_api_uri );

		// Import Widgets data.
		$this->import_widgets( $demo_data['astra-demo-widgets-data'] );

		// Import Customizer Settings.
		$this->import_customizer_Settings( $demo_data['astra-demo-customizer-data'] );
	}

	private function import_widgets( $data ) {
		$widgets_importer = Astra_Widget_Importer::instance();
		$widgets_importer->import_widgets_data( $data );
	}

	private function import_customizer_Settings( $customizer_data ) {
		$customizer_import = Astra_Customizer_Import::instance();
		$customizer_data   = $customizer_import->import( $customizer_data );
	}

	public static function get_astra_single_demo( $demo_api_uri ) {

		// default values.
		$astra_demo = array(
			'id'                           => '',
			'astra-demo-widgets-data'      => '',
			'astra-demo-customizer-data'   => '',
			'astra-demo-site-options-data' => '',
			'astra-demo-wxr-path'          => '',
		);

		$api_args = array(
			'timeout' => 15
		);

		$response = wp_remote_get( $demo_api_uri, $api_args );

		if ( ! is_wp_error( $response ) || wp_remote_retrieve_response_code( $response ) === 200 ) {
			$result                                     = json_decode( wp_remote_retrieve_body( $response ), true );
			$astra_demo['id']                           = $result['id'];
			$astra_demo['astra-demo-widgets-data']      = json_decode( $result['astra-demo-widgets-data'] );
			$astra_demo['astra-demo-customizer-data']   = $result['astra-demo-customizer-data'];
			$astra_demo['astra-demo-site-options-data'] = $result['astra-demo-site-options-data'];
			$astra_demo['astra-demo-wxr-path']          = $result['astra-demo-wxr-path'];
		}

		return $astra_demo;
	}

	public static function get_all_astra_demos() {

		$astra_demos = array();

		$api_args = array(
			'timeout' => 15
		);

		$response = wp_remote_get( self::get_api_url(), $api_args );

		if ( ! is_wp_error( $response ) || wp_remote_retrieve_response_code( $response ) === 200 ) {
			$result = json_decode( wp_remote_retrieve_body( $response ), true );

			foreach ( $result as $key => $demo ) {
				$astra_demos[ $key ]['id']                 = $demo['id'];
				$astra_demos[ $key ]['slug']               = $demo['slug'];
				$astra_demos[ $key ]['date']               = $demo['date'];
				$astra_demos[ $key ]['astra-demo-url']     = $demo['astra-demo-url'];
				$astra_demos[ $key ]['title']              = $demo['title']['rendered'];
				$astra_demos[ $key ]['featured-image-url'] = $demo['featured-image-url'];
				$astra_demos[ $key ]['demo-api']           = isset( $demo['_links']['self'][0]['href'] ) ? $demo['_links']['self'][0]['href'] : self::get_api_url() . $demo['id'];
			}

			// Free up memory by unsetting variables that are not required.
			unset( $result );
			unset( $response );

			return $astra_demos;
		}

	}

}
