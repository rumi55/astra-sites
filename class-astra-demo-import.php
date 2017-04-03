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
		add_action( 'wp_ajax_astra-list-demos', array( $this, 'astra_list_demos' ) );
	}

	private static function set_api_url( $importer_api ) {
		self::$api_url = $importer_api;
	}

	public static function get_api_url( $args, $page = '1' ) {

		$args->search = isset( $args->search ) ? $args->search : '';
		$args->id 	  = isset( $args->id ) ? $args->id : '';

		if ( $args->search !== '' ) {
			return self::$api_url . 'astra-demos/?search=' . $args->search . '&per_page=15&page=' . $page;
		} elseif ( $args->id != 'all' ) {
			return self::$api_url . 'astra-demos/?astra-demo-category=' . $args->id . '&per_page=15&page=' . $page;
		}

		return self::$api_url . 'astra-demos/?per_page=15&page=' . $page;
	}

	public static function get_taxanomy_api_url() {
		return self::$api_url . 'astra-demo-category/';
	}

	public function admin_enqueue() {
		wp_register_script( 'astra-demo-import-admin', ADI_URI . 'assets/js/admin.js', array(
			'jquery',
			'wp-util',
		), ADI_VER, true );
	}

	private function includes() {
		require_once ADI_DIR . 'admin/class-astra-demo-import-admin.php';

		// Load the Importers.
		require_once ADI_DIR . 'importers/class-widgets-importer.php';
		require_once ADI_DIR . 'importers/class-customizer-import.php';
		require_once ADI_DIR . 'importers/wxr-importer/class-astra-wxr-importer.php';
		require_once ADI_DIR . 'importers/class-astra-site-options-import.php';
	}

	public function demo_ajax_import() {

		if ( ! current_user_can( 'customize' ) ) {
			return;
		}

		$demo_api_uri = isset( $_POST['api_url'] ) ? esc_url( $_POST['api_url'] ) : '';
		$this->import_demo( $demo_api_uri );
	}

	public function astra_list_demos() {

		if ( ! current_user_can( 'customize' ) ) {
			return;
		}

		$args = new stdClass();
		$args->category = isset( $_POST['category'] ) ? esc_attr( $_POST['category'] ) : '';
		$args->id 		= isset( $_POST['id'] ) ? esc_attr( $_POST['id'] ) : '';
		$args->search 	= isset( $_POST['search'] ) ? esc_attr( $_POST['search'] ) : '';
		$paged 	= isset( $_POST['paged'] ) ? esc_attr( $_POST['paged'] ) : '1';

		return wp_send_json( self::get_astra_demos( $args, $paged ) );
	}

	public static function get_astra_all_demos() {
		$args 		= new stdClass();
		$args->id 	= 'all';

		return self::get_astra_demos( $args );
	}

	public function import_demo( $demo_api_uri ) {
		$demo_data = self::get_astra_single_demo( $demo_api_uri );

		// Import Enabled Extensions.
		$this->import_astra_enabled_extension( $demo_data['astra-enabled-extensions'] );

		// Import Widgets data.
		$this->import_widgets( $demo_data['astra-demo-widgets-data'] );

		// Import Customizer Settings.
		$this->import_customizer_Settings( $demo_data['astra-demo-customizer-data'] );

		// Import XML.
		$this->import_wxr( $demo_data['astra-demo-wxr-path'] );

		// Import WordPress site options.
		$this->import_site_options( $demo_data['astra-demo-site-options-data'] );

		// Import Custom 404 extension options.
		$this->import_custom_404_extension_options( $demo_data['astra-custom-404'] );

		// Clear Astra Cache.
		$this->clear_astra_cache();
	}

	private function clear_astra_cache() {
		if ( class_exists( 'AST_Minify' ) ) {
			AST_Minify::clear_assets_cache();
		}
	}

	private function import_widgets( $data ) {
		$widgets_importer = Astra_Widget_Importer::instance();
		$widgets_importer->import_widgets_data( $data );
	}

	private function import_customizer_Settings( $customizer_data ) {
		$customizer_import = Astra_Customizer_Import::instance();
		$customizer_data   = $customizer_import->import( $customizer_data );
	}

	private function import_wxr( $wxr_url ) {
		$wxr_importer = Astra_WXR_Importer::instance();
		$xml_path     = $wxr_importer->download_xml( $wxr_url );
		$wxr_importer->import_xml( $xml_path['file'] );
	}

	private function import_site_options( $options ) {
		$options_importer = Astra_Site_Options_Importer::instance();
		$options_importer->import_options( $options );
	}

	private function import_astra_enabled_extension( $saved_extensions ) {
		if ( is_callable( 'AST_Admin_Helper::update_admin_settings_option' ) ) {
			AST_Admin_Helper::update_admin_settings_option( '_ast_ext_enabled_extensions', $saved_extensions );
		}
	}

	private function import_custom_404_extension_options( $options_404 ) {
		if ( is_callable( 'AST_Admin_Helper::update_admin_settings_option' ) ) {
			AST_Admin_Helper::update_admin_settings_option( '_ast_ext_custom_404', $options_404 );
		}
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
			'timeout' => 15,
		);

		$response = wp_remote_get( $demo_api_uri, $api_args );

		if ( ! is_wp_error( $response ) || wp_remote_retrieve_response_code( $response ) === 200 ) {
			$result                                     = json_decode( wp_remote_retrieve_body( $response ), true );
			$astra_demo['id']                           = $result['id'];
			$astra_demo['astra-demo-widgets-data']      = json_decode( $result['astra-demo-widgets-data'] );
			$astra_demo['astra-demo-customizer-data']   = $result['astra-demo-customizer-data'];
			$astra_demo['astra-demo-site-options-data'] = $result['astra-demo-site-options-data'];
			$astra_demo['astra-demo-wxr-path']          = $result['astra-demo-wxr-path'];
			$astra_demo['astra-enabled-extensions']     = $result['astra-enabled-extensions'];
			$astra_demo['astra-custom-404']             = $result['astra-custom-404'];
		}

		return $astra_demo;
	}

	public static function get_astra_demos( $args, $paged = '1' ) {

		$url = self::get_api_url( $args, $paged );

		$astra_demos = array();

		$api_args = array(
			'timeout' => 15,
		);

		$response = wp_remote_get( $url, $api_args );

		if ( ! is_wp_error( $response ) || wp_remote_retrieve_response_code( $response ) === 200 ) {
			$result = json_decode( wp_remote_retrieve_body( $response ), true );

			foreach ( $result as $key => $demo ) {
				$astra_demos[ $key ]['id']                 = $demo['id'];
				$astra_demos[ $key ]['slug']               = $demo['slug'];
				$astra_demos[ $key ]['date']               = $demo['date'];
				$astra_demos[ $key ]['astra_demo_url']     = $demo['astra-demo-url'];
				$astra_demos[ $key ]['title']              = $demo['title']['rendered'];
				$astra_demos[ $key ]['featured_image_url'] = $demo['featured-image-url'];
				$astra_demos[ $key ]['demo_api']           = isset( $demo['_links']['self'][0]['href'] ) ? $demo['_links']['self'][0]['href'] : self::get_api_url( new stdClass() ) . $demo['id'];
				$astra_demos[ $key ]['content']           = isset( $demo['content']['rendered'] ) ? strip_tags( $demo['content']['rendered'] ) : '';
			}

			// Free up memory by unsetting variables that are not required.
			unset( $result );
			unset( $response );
		}

		return $astra_demos;

	}

	public static function get_demo_categories() {
		$categories = array();

		$api_args = array(
			'timeout' => 15,
		);

		$response = wp_remote_get( self::get_taxanomy_api_url(), $api_args );

		if ( ! is_wp_error( $response ) || wp_remote_retrieve_response_code( $response ) === 200 ) {
			$result = json_decode( wp_remote_retrieve_body( $response ), true );

			foreach ( $result as $key => $category ) {
				if ( $category['count'] == 0 ) {
					continue;
				}
				$categories[ $key ]['id'] = $category['id'];
				$categories[ $key ]['name'] = $category['name'];
				$categories[ $key ]['slug'] = $category['slug'];
				$categories[ $key ]['count'] = $category['count'];
				$categories[ $key ]['link-category'] = $category['_links']['self'][0]['href'];
			}

			// Free up memory by unsetting variables that are not required.
			unset( $result );
			unset( $response );

		}

		return $categories;
	}

}
