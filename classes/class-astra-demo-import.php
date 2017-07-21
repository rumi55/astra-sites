<?php
/**
 * Class Astra Demo Importer
 *
 * @since  1.0.0
 * @package Astra Addon
 */

defined( 'ABSPATH' ) or exit;

if ( ! class_exists( 'Astra_Demo_Import' ) ) :

	/**
	 * Astra_Demo_Import
	 */
	class Astra_Demo_Import {

		/**
		 * API URL which is used to get the response from.
		 *
		 * @since  1.0.0
		 * @var (String) URL
		 */
		public static $api_url;

		/**
		 * Instance of Astra_Demo_Import
		 *
		 * @since  1.0.0
		 * @var (Object) Astra_Demo_Import
		 */
		private static $_instance = null;

		/**
		 * Instance of Astra_Demo_Import.
		 *
		 * @since  1.0.0
		 *
		 * @return object Class object.
		 */
		public static function set_instance() {
			if ( ! isset( self::$_instance ) ) {
				self::$_instance = new self;
			}

			return self::$_instance;
		}

		/**
		 * Constructor.
		 *
		 * @since  1.0.0
		 */
		private function __construct() {

			add_action( 'admin_notices',                                    array( $this, 'admin_notices' ) );

			self::set_api_url();

			$this->includes();

			add_action( 'wp_enqueue_scripts',                               array( $this, 'admin_enqueue' ) );
			add_action( 'admin_enqueue_scripts',                            array( $this, 'admin_enqueue' ) );
			add_action( 'wp_ajax_astra-import-demo',                        array( $this, 'demo_ajax_import' ) );
			add_action( 'wp_ajax_astra-list-demos',                         array( $this, 'list_demos' ) );
			add_action( 'wp_ajax_astra-required-plugins',                   array( $this, 'required_plugin' ) );
			add_action( 'wp_ajax_astra-required-plugin-activate',           array( $this, 'required_plugin_activate' ) );
		}

		/**
		 * Admin Notices
		 *
		 * @since 1.0.0
		 * @return void
		 */
		function admin_notices() {

			if ( ! defined( 'ASTRA_THEME_SETTINGS' ) ) {
				?>
				<div class="notice notice-error ast-active-notice is-dismissible">
					<p>
						<?php
						printf(
							/* translators: 1: plugin name, 2: theme.php file*/
							__( 'Astra Theme needs to be active for you to use currently installed "Astra Demo Import" plugin. <a href="%1$s">Install & Activate Now</a>', 'astra' ),
							esc_url( admin_url( 'themes.php' ) )
						);
						?>
					</p>
				</div>
				<?php
				return;
			}

			add_action( 'plugin_action_links_' . ASTRA_DEMO_IMPORT_BASE,    array( $this, 'action_links' ) );
		}

		/**
		 * Show action links on the plugin screen.
		 *
		 * @param   mixed $links Plugin Action links.
		 * @return  array
		 */
		function action_links( $links ) {
			$action_links = array(
				'settings' => '<a href="' . admin_url( 'themes.php?page=astra&action=astra-demos' ) . '" aria-label="' . esc_attr__( 'Import Demos', 'astra-demo-import' ) . '">' . esc_html__( 'Import Demos', 'astra-demo-import' ) . '</a>',
			);

			return array_merge( $action_links, $links );
		}

		/**
		 * Setter for $api_url
		 *
		 * @since  1.0.0
		 */
		public static function set_api_url() {

			if ( defined( 'ASTRA_DEMO_API_URL' ) ) {
				self::$api_url = ASTRA_DEMO_API_URL;
			} else {
				self::$api_url = 'http://multi.sharkz.in/wp-json/wp/v2/';
			}

		}

		/**
		 * Returns the API URL that depending based on the category, search term and pagination.
		 *
		 * @since  1.0.0
		 *
		 * @param  object $args Arguments for selecting correct list of demos.
		 *         args->id        = ID of the demo.
		 *         $args->search    = Search term used in the demo.
		 * @param  string $page Page number for pagination.
		 *
		 * @return string URL that can be queried to return the demos.
		 */
		public static function get_api_url( $args, $page = '1' ) {

			$request_params = array(
				'page'         => $page,
				'per_page'     => '15',

				// Use this for premium demos.
				'purchase_key' => '',
				'site_url'     => '',
			);

			$args_search = isset( $args->search ) ? $args->search : '';
			$args_id     = isset( $args->id ) ? $args->id : '';

			// Not Search?
			if ( '' !== $args_search ) {
				$request_params['search'] = $args_search;

			// Not All?
			} elseif ( 'all' != $args_id ) {
				$request_params['astra-demo-category'] = $args_id;
			}

			return add_query_arg( $request_params, self::$api_url . 'astra-demos' );
		}

		/**
		 * Returns the API URL for searching demos basedon taxanomies.
		 *
		 * @since  1.0.0
		 * @return (String) URL that can be queried to return the demos.
		 */
		public static function get_taxanomy_api_url() {
			return self::$api_url . 'astra-demo-category/';
		}

		/**
		 * Enqueue admin scripts.
		 *
		 * @since  1.0.0
		 */
		public function admin_enqueue() {

			wp_register_script(
				'astra-demo-import-admin', ASTRA_DEMO_IMPORT_URI . 'assets/js/admin.js', array(
					'jquery',
					'wp-util',
					'updates',
				), ASTRA_DEMO_IMPORT_VER, true
			);

			wp_register_style( 'astra-demo-import-admin', ASTRA_DEMO_IMPORT_URI . 'assets/css/admin.css', ASTRA_DEMO_IMPORT_VER, true );

			wp_localize_script(
				'astra-demo-import-admin', 'astraDemo', array(
					'ajaxurl'              => esc_url( admin_url( 'admin-ajax.php' ) ),
					'siteURL'              => site_url(),
					'getProText'           => __( 'Get Pro', 'astra-demo-import' ),
					'getProURL'            => esc_url( 'https://wpastra.com/pro/?utm_source=demo-import-panel&utm_campaign=astra-demo-import&utm_medium=' ),
					'_ajax_nonce'          => wp_create_nonce( 'astra-demo-import' ),
					'requiredPluginsCount' => 0,
					'strings'              => array(
						'btnActivating' => __( 'Activating', 'astra-demo-import' ) . '&hellip;',
						'btnActive'     => __( 'Active', 'astra-demo-import' ),
						'importDemo'    => __( 'Import Demo', 'astra-demo-import' ),
						'DescExpand'    => __( 'Read more', 'astra-demo-import' ) . '&hellip;',
						'DescCollapse'  => __( 'Hide', 'astra-demo-import' ),
						'responseError' => __( 'There was a problem receiving a response from server.', 'astra-demo-import' ),
						'searchNoFound' => __( 'No Demos found, Try a different search.', 'astra-demo-import' ),
						'importWarning' => __( "Executing Demo Import will make your site similar as ours. Please bear in mind -\n\n1. It is strongly recommended to run Demo Import on a fresh WordPress installation.\n\n2. If you have any existing pages, posts, menus & other data, it will be overwritten.\n\n3. Some copyrighted images won't be imported. Instead they will be replaced with placeholders.", 'astra-demo-import' ),
					),
				)
			);

		}

		/**
		 * Load all the required files in the importer.
		 *
		 * @since  1.0.0
		 */
		private function includes() {

			require_once ASTRA_DEMO_IMPORT_DIR . 'admin/class-astra-demo-import-admin.php';

			// Load the Importers.
			require_once ASTRA_DEMO_IMPORT_DIR . 'importers/class-widgets-importer.php';
			require_once ASTRA_DEMO_IMPORT_DIR . 'importers/class-astra-customizer-import.php';
			require_once ASTRA_DEMO_IMPORT_DIR . 'importers/wxr-importer/class-astra-wxr-importer.php';
			require_once ASTRA_DEMO_IMPORT_DIR . 'importers/class-astra-site-options-import.php';
		}

		/**
		 * Required Plugin Activate
		 *
		 * @since 1.0.0
		 */
		public function required_plugin_activate() {

			if ( ! current_user_can( 'install_plugins' ) || ! isset( $_POST['init'] ) || ! $_POST['init'] ) {
				wp_send_json_error(
					array(
						'success' => false,
						'message' => __( 'No plugin specified', 'astra-demo-import' ),
					)
				);
			}

			$plugin_init = esc_attr( $_POST['init'] );

			$activate = activate_plugin( $plugin_init, '', false, true );

			if ( is_wp_error( $activate ) ) {
				wp_send_json_error(
					array(
						'success' => false,
						'message' => $activate->get_error_message(),
					)
				);
			}

			wp_send_json_success(
				array(
					'success' => true,
					'message' => __( 'Plugin Successfully Activated', 'astra-demo-import' ),
				)
			);

		}

		/**
		 * Required Plugin
		 *
		 * @since 1.0.0
		 * @return void
		 */
		public function required_plugin() {

			// Verify Nonce.
			check_ajax_referer( 'astra-demo-import', '_ajax_nonce' );

			$report = array(
				'success' => false
			);

			if ( ! current_user_can( 'customize' ) ) {
				wp_send_json_error( $report );
			}

			$required_plugins = ( isset( $_POST['required_plugins'] ) ) ? $_POST['required_plugins'] : array();

			$inactive     = array();
			$notinstalled = array();
			$active       = array();

			if ( count( $required_plugins ) > 0 ) {
				foreach ( $required_plugins as $key => $plugin ) {

					if ( file_exists( WP_PLUGIN_DIR . '/' . $plugin['init'] ) && is_plugin_inactive( $plugin['init'] ) ) {

						$inactive[] = $plugin;

					} elseif ( ! file_exists( WP_PLUGIN_DIR . '/' . $plugin['init'] ) ) {
						$notinstalled[] = $plugin;
					} else {
						$active[] = $plugin;
					}
				}

				$success = true;
				if ( count( $notinstalled ) > 0 || count( $inactive ) > 0 ) {
					$success = false;
				}

				$report['plugins']['inactive']     = $inactive;
				$report['plugins']['notinstalled'] = $notinstalled;
				$report['plugins']['active']       = $active;
				$report['success']                 = $success;

				wp_send_json_success( $report );
			} else {

				wp_send_json_error( $report );
			}
		}

		/**
		 * Ajax callback for demo import action.
		 *
		 * @since  1.0.0
		 */
		public function demo_ajax_import() {

			if ( ! current_user_can( 'customize' ) ) {
				return;
			}

			$demo_api_uri = isset( $_POST['api_url'] ) ? esc_url( $_POST['api_url'] ) : '';
			$this->import_demo( $demo_api_uri );

			wp_die();
		}

		/**
		 * Ajax handler for retreiving the list of demos.
		 *
		 * @since  1.0.0
		 * @return (JSON) Json response retreived from the API.
		 */
		public function list_demos() {

			if ( ! current_user_can( 'customize' ) ) {
				return;
			}

			$args           = new stdClass();
			$args->category = isset( $_POST['category'] ) ? esc_attr( $_POST['category'] ) : '';
			$args->id       = isset( $_POST['id'] ) ? esc_attr( $_POST['id'] ) : '';
			$args->search   = isset( $_POST['search'] ) ? esc_attr( $_POST['search'] ) : '';
			$paged          = isset( $_POST['paged'] ) ? esc_attr( $_POST['paged'] ) : '1';

			return wp_send_json( self::get_astra_demos( $args, $paged ) );
		}

		/**
		 * Get the list of demos.
		 *
		 * @since  1.0.0
		 * @see  admin/view-astra-demos.php
		 * @return (Array) Demos.
		 */
		public static function get_astra_all_demos() {
			$args     = new stdClass();
			$args->id = 'all';

			return self::get_astra_demos( $args );
		}

		/**
		 * Import the demo.
		 *
		 * @since  1.0.0
		 *
		 * @param  (String) $demo_api_uri API URL for the single demo.
		 */
		public function import_demo( $demo_api_uri ) {

			$demo_data = self::get_astra_single_demo( $demo_api_uri );

			// Import Enabled Extensions.
			$this->import_astra_enabled_extension( $demo_data['astra-enabled-extensions'] );

			// Import Widgets data.
			$this->import_widgets( $demo_data['astra-demo-widgets-data'] );

			// Import Customizer Settings.
			$this->import_customizer_settings( $demo_data['astra-demo-customizer-data'] );

			// Import XML.
			$this->import_wxr( $demo_data['astra-demo-wxr-path'] );

			// Import WordPress site options.
			$this->import_site_options( $demo_data['astra-demo-site-options-data'] );

			// Import Custom 404 extension options.
			$this->import_custom_404_extension_options( $demo_data['astra-custom-404'] );
		}

		/**
		 * Import widgets and assign to correct sidebars.
		 *
		 * @since  1.0.0
		 *
		 * @param  (Object) $data Widgets data.
		 */
		private function import_widgets( $data ) {

			// bail if wiegets data is not available.
			if ( null == $data ) {
				return;
			}

			$widgets_importer = Astra_Widget_Importer::instance();
			$widgets_importer->import_widgets_data( $data );
		}

		/**
		 * Import Customizer data.
		 *
		 * @since  1.0.0
		 *
		 * @param  (Array) $customizer_data Customizer data for the demo to be imported.
		 */
		private function import_customizer_settings( $customizer_data ) {
			$customizer_import = Astra_Customizer_Import::instance();
			$customizer_data   = $customizer_import->import( $customizer_data );
		}

		/**
		 * Download and import the XML from the demo.
		 *
		 * @since  1.0.0
		 *
		 * @param  (String) $wxr_url URL of the xml export of the demo to be imported.
		 */
		private function import_wxr( $wxr_url ) {
			$wxr_importer = Astra_WXR_Importer::instance();
			$xml_path     = $wxr_importer->download_xml( $wxr_url );
			$wxr_importer->import_xml( $xml_path['file'] );
		}

		/**
		 * Import site options - Front Page, Menus, Blog page etc.
		 *
		 * @since  1.0.0
		 *
		 * @param  (Array) $options Array of required site options from the demo.
		 */
		private function import_site_options( $options ) {
			$options_importer = Astra_Site_Options_Import::instance();
			$options_importer->import_options( $options );
		}

		/**
		 * Import settings enabled astra extensions from the demo.
		 *
		 * @since  1.0.0
		 *
		 * @param  (Array) $saved_extensions Array of enabled extensions.
		 */
		private function import_astra_enabled_extension( $saved_extensions ) {
			if ( is_callable( 'AST_Admin_Helper::update_admin_settings_option' ) ) {
				AST_Admin_Helper::update_admin_settings_option( '_astra_ext_enabled_extensions', $saved_extensions );
			}
		}

		/**
		 * Import custom 404 section.
		 *
		 * @since 1.0.0
		 *
		 * @param  (Array) $options_404 404 Extensions settings from the demo.
		 */
		private function import_custom_404_extension_options( $options_404 ) {
			if ( is_callable( 'AST_Admin_Helper::update_admin_settings_option' ) ) {
				AST_Admin_Helper::update_admin_settings_option( '_astra_ext_custom_404', $options_404 );
			}
		}

		/**
		 * Get single demo.
		 *
		 * @since  1.0.0
		 *
		 * @param  (String) $demo_api_uri API URL of a demo.
		 *
		 * @return (Array) $astra_demo_data demo data for the demo.
		 */
		public static function get_astra_single_demo( $demo_api_uri ) {

			// default values.
			$remote_args = array();
			$defaults    = array(
				'id'                           => '',
				'astra-demo-widgets-data'      => '',
				'astra-demo-customizer-data'   => '',
				'astra-demo-site-options-data' => '',
				'astra-demo-wxr-path'          => '',
				'astra-enabled-extensions'     => '',
				'astra-custom-404'             => '',
				'required-plugins'             => '',
			);

			$api_args = array(
				'timeout' => 15,
			);

			$response = wp_remote_get( $demo_api_uri, $api_args );

			if ( ! is_wp_error( $response ) || wp_remote_retrieve_response_code( $response ) === 200 ) {
				$result                                     = json_decode( wp_remote_retrieve_body( $response ), true );
				$remote_args['id']                           = $result['id'];
				$remote_args['astra-demo-widgets-data']      = json_decode( $result['astra-demo-widgets-data'] );
				$remote_args['astra-demo-customizer-data']   = $result['astra-demo-customizer-data'];
				$remote_args['astra-demo-site-options-data'] = $result['astra-demo-site-options-data'];
				$remote_args['astra-demo-wxr-path']          = $result['astra-demo-wxr-path'];
				$remote_args['astra-enabled-extensions']     = $result['astra-enabled-extensions'];
				$remote_args['astra-custom-404']             = $result['astra-custom-404'];
				$remote_args['required-plugins']             = $result['required-plugins'];
			}

			// Merge remote demo and defaults.
			return wp_parse_args( $remote_args, $defaults );
		}

		/**
		 * Get astra demos.
		 *
		 * @since 1.0.0
		 *
		 * @param  (Array)  $args For selecting the demos (Search terms, pagination etc).
		 * @param  (String) $paged Page number.
		 */
		public static function get_astra_demos( $args, $paged = '1' ) {

			$url = self::get_api_url( $args, $paged );

			$astra_demos = array();

			$api_args = array(
				'timeout' => 15,
			);

			$response = wp_remote_get( $url, $api_args );

			if ( ! is_wp_error( $response ) || wp_remote_retrieve_response_code( $response ) === 200 ) {
				$result = json_decode( wp_remote_retrieve_body( $response ), true );

				// If is array then proceed
				// Else skip it.
				if ( is_array( $result ) ) {

					foreach ( $result as $key => $demo ) {

						if ( ! isset( $demo['id'] ) ) {
							continue;
						}

						$astra_demos[ $key ]['id']                 = isset( $demo['id'] ) ? esc_attr( $demo['id'] ) : '';
						$astra_demos[ $key ]['slug']               = isset( $demo['slug'] ) ? esc_attr( $demo['slug'] ) : '';
						$astra_demos[ $key ]['date']               = isset( $demo['date'] ) ? esc_attr( $demo['date'] ) : '';
						$astra_demos[ $key ]['astra_demo_type']    = isset( $demo['astra-demo-type'] ) ? sanitize_key( $demo['astra-demo-type'] ) : '';
						$astra_demos[ $key ]['astra_demo_url']     = isset( $demo['astra-demo-url'] ) ? esc_url( $demo['astra-demo-url'] ) : '';
						$astra_demos[ $key ]['title']              = isset( $demo['title']['rendered'] ) ? esc_attr( $demo['title']['rendered'] ) : '';
						$astra_demos[ $key ]['featured_image_url'] = isset( $demo['featured-image-url'] ) ? esc_url( $demo['featured-image-url'] ) : '';
						$astra_demos[ $key ]['demo_api']           = isset( $demo['_links']['self'][0]['href'] ) ? esc_url( $demo['_links']['self'][0]['href'] ) : self::get_api_url( new stdClass() ) . $demo['id'];
						$astra_demos[ $key ]['content']            = isset( $demo['content']['rendered'] ) ? strip_tags( $demo['content']['rendered'] ) : '';
						$astra_demos[ $key ]['required_plugins']   = isset( $demo['required-plugins'] ) ? json_encode( $demo['required-plugins'] ) : '';
					}

					// Free up memory by unsetting variables that are not required.
					unset( $result );
					unset( $response );
				}
			}

			return $astra_demos;

		}

		/**
		 * Get demo categories.
		 *
		 * @since  1.0.0
		 * @return (Array) Array of demo categories.
		 */
		public static function get_demo_categories() {
			$categories = array();

			$api_args = array(
				'timeout' => 15,
			);

			$response = wp_remote_get( self::get_taxanomy_api_url(), $api_args );

			if ( ! is_wp_error( $response ) || 200 === wp_remote_retrieve_response_code( $response ) ) {
				$result = json_decode( wp_remote_retrieve_body( $response ), true );

				// If is array then proceed
				// Else skip it.
				if ( is_array( $result ) ) {

					foreach ( $result as $key => $category ) {
						if ( 0 == $category['count'] ) {
							continue;
						}
						$categories[ $key ]['id']            = $category['id'];
						$categories[ $key ]['name']          = $category['name'];
						$categories[ $key ]['slug']          = $category['slug'];
						$categories[ $key ]['count']         = $category['count'];
						$categories[ $key ]['link-category'] = $category['_links']['self'][0]['href'];
					}

					// Free up memory by unsetting variables that are not required.
					unset( $result );
					unset( $response );

				}
			}

			return $categories;
		}

	}

	/**
	 * Kicking this off by calling 'set_instance()' method
	 */
	Astra_Demo_Import::set_instance();

endif;

