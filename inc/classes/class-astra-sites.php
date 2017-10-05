<?php
/**
 * Astra Sites
 *
 * @since  1.0.0
 * @package Astra Sites
 */

defined( 'ABSPATH' ) or exit;

if ( ! class_exists( 'Astra_Sites' ) ) :

	/**
	 * Astra_Sites
	 */
	class Astra_Sites {

		/**
		 * API URL which is used to get the response from.
		 *
		 * @since  1.0.0
		 * @var (String) URL
		 */
		public static $api_url;

		/**
		 * Instance of Astra_Sites
		 *
		 * @since  1.0.0
		 * @var (Object) Astra_Sites
		 */
		private static $_instance = null;

		/**
		 * Instance of Astra_Sites.
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

			self::set_api_url();

			$this->includes();

			add_action( 'admin_notices',                                    array( $this, 'add_notice' ), 1 );
			add_action( 'admin_notices',                                    array( $this, 'admin_notices' ) );

			add_action( 'admin_enqueue_scripts',                            array( $this, 'admin_enqueue' ) );
			add_action( 'wp_ajax_astra-import-demo',                        array( $this, 'demo_ajax_import' ) );
			add_action( 'wp_ajax_astra-required-plugins',                   array( $this, 'required_plugin' ) );
			add_action( 'wp_ajax_astra-required-plugin-activate',           array( $this, 'required_plugin_activate' ) );
			add_action( 'plugins_loaded',                                   array( $this, 'load_textdomain' ) );
			add_action( 'astra_sites_image_import_complete',                array( $this, 'clear_cache' ) );

		}

		/**
		 * Add Admin Notice.
		 */
		function add_notice() {

			Astra_Sites_Notices::add_notice(
				array(
					'type'              => 'error',
					'show_if'           => ( ! defined( 'ASTRA_THEME_SETTINGS' ) ) ? true : false,
					/* translators: 1: theme.php file*/
					'message'           => sprintf( __( 'Astra Theme needs to be active for you to use currently installed "%1$s" plugin. <a href="%2$s">Install & Activate Now</a>', 'astra-sites' ), ASTRA_SITES_NAME, esc_url( admin_url( 'themes.php?theme=astra' ) ) ),
					'dismissible'       => true,
					'dismissible-time'  => MINUTE_IN_SECONDS,
				)
			);

		}

		/**
		 * Loads textdomain for the plugin.
		 *
		 * @since 1.0.1
		 */
		function load_textdomain() {
			load_plugin_textdomain( 'astra-sites' );
		}

		/**
		 * Admin Notices
		 *
		 * @since 1.0.5
		 * @return void
		 */
		function admin_notices() {

			if ( ! defined( 'ASTRA_THEME_SETTINGS' ) ) {
				return;
			}

			add_action( 'plugin_action_links_' . ASTRA_SITES_BASE,    array( $this, 'action_links' ) );
		}

		/**
		 * Show action links on the plugin screen.
		 *
		 * @param   mixed $links Plugin Action links.
		 * @return  array
		 */
		function action_links( $links ) {
			$action_links = array(
				'settings' => '<a href="' . admin_url( 'themes.php?page=astra-sites' ) . '" aria-label="' . esc_attr__( 'See Library', 'astra-sites' ) . '">' . esc_html__( 'See Library', 'astra-sites' ) . '</a>',
			);

			return array_merge( $action_links, $links );
		}

		/**
		 * Setter for $api_url
		 *
		 * @since  1.0.0
		 */
		public static function set_api_url() {

			self::$api_url = apply_filters( 'astra_sites_api_url', 'https://sites.wpastra.com/wp-json/wp/v2/' );

		}

		/**
		 * Enqueue admin scripts.
		 *
		 * @since  1.0.5    Added 'getUpgradeText' and 'getUpgradeURL' localize variables.
		 *
		 * @since  1.0.0
		 *
		 * @param  string $hook Current hook name.
		 * @return void
		 */
		public function admin_enqueue( $hook = '' ) {

			if ( 'appearance_page_astra-sites' !== $hook ) {
				return;
			}

			// Lazyload & Image Loaded.
			wp_register_script( 'astra-sites-lazyload', ASTRA_SITES_URI . 'inc/assets/vendor/js/jquery.lazy.min.js', array( 'jquery' ), ASTRA_SITES_VER, true );

			// API.
			wp_register_script( 'astra-sites-api', ASTRA_SITES_URI . 'inc/assets/js/astra-sites-api.js', array( 'jquery' ), ASTRA_SITES_VER, true );

			// Admin Page.
			wp_enqueue_style( 'astra-sites-admin', ASTRA_SITES_URI . 'inc/assets/css/admin.css', ASTRA_SITES_VER, true );
			wp_enqueue_script( 'astra-sites-admin-page', ASTRA_SITES_URI . 'inc/assets/js/admin-page.js', array( 'jquery', 'wp-util', 'updates' ), ASTRA_SITES_VER, true );
			wp_enqueue_script( 'astra-sites-render-grid', ASTRA_SITES_URI . 'inc/assets/js/render-grid.js', array( 'wp-util', 'astra-sites-api', 'imagesloaded', 'jquery', 'astra-sites-lazyload' ), ASTRA_SITES_VER, true );

			$data = array(
				'ApiURL' => self::$api_url,
				'filters' => array(
					'page_builder' => array(
						'title'   => __( 'Page Builder', 'astra-sites' ),
						'slug'    => 'astra-site-page-builder',
						'trigger' => 'astra-api-category-loaded',
					),
					'categories' => array(
						'title'   => __( 'Categories', 'astra-sites' ),
						'slug'    => 'astra-site-category',
						'trigger' => 'astra-api-category-loaded',
					),
				),
			);
			wp_localize_script( 'astra-sites-api', 'astraSitesApi', $data );

			// Use this for premium demos.
			$request_params = apply_filters(
				'astra_sites_api_params', array(
					'purchase_key' => '',
					'site_url'     => '',
					'par-page'     => 6,
				)
			);

			$data = apply_filters(
				'astra_sites_localize_vars',
				array(
					'sites' => $request_params,
					'settings' => array(),
				)
			);

			wp_localize_script( 'astra-sites-render-grid', 'astraRenderGrid', $data );

			$data = apply_filters(
				'astra_sites_localize_vars',
				array(
					'ajaxurl'         => esc_url( admin_url( 'admin-ajax.php' ) ),
					'siteURL'         => site_url(),
					'getProText'      => __( 'Purchase', 'astra-sites' ),
					'getProURL'       => esc_url( 'https://wpastra.com/agency/?utm_source=demo-import-panel&utm_campaign=astra-sites&utm_medium=wp-dashboard' ),
					'getUpgradeText'  => __( 'Upgrade', 'astra-sites' ),
					'getUpgradeURL'   => esc_url( 'https://wpastra.com/agency/?utm_source=demo-import-panel&utm_campaign=astra-sites&utm_medium=wp-dashboard' ),
					'_ajax_nonce'     => wp_create_nonce( 'astra-sites' ),
					'requiredPlugins' => array(),
					'strings'         => array(
						'importFailedBtnSmall' => __( 'Error!', 'astra-sites' ),
						'importFailedBtnLarge' => __( 'Error! Read Possibilities.', 'astra-sites' ),
						'importFailedURL'      => esc_url( 'https://wpastra.com/docs/?p=1314' ),
						'viewSite'             => __( 'Done! View Site', 'astra-sites' ),
						'btnActivating'        => __( 'Activating', 'astra-sites' ) . '&hellip;',
						'btnActive'            => __( 'Active', 'astra-sites' ),
						'importDemo'           => __( 'Import This Site', 'astra-sites' ),
						'importingDemo'        => __( 'Importing Demo', 'astra-sites' ),
						'DescExpand'           => __( 'Read more', 'astra-sites' ) . '&hellip;',
						'DescCollapse'         => __( 'Hide', 'astra-sites' ),
						'responseError'        => __( 'There was a problem receiving a response from server.', 'astra-sites' ),
						'searchNoFound'        => __( 'No Demos found, Try a different search.', 'astra-sites' ),
						'importWarning'        => __( "Executing Demo Import will make your site similar as ours. Please bear in mind -\n\n1. It is recommended to run import on a fresh WordPress installation.\n\n2. Importing site does not delete any pages or posts. However, it can overwrite your existing content.\n\n3. Copyrighted media will not be imported. Instead it will be replaced with placeholders.", 'astra-sites' ),
					),
				)
			);

			wp_localize_script( 'astra-sites-admin-page', 'astraSitesAdmin', $data );

		}

		/**
		 * Load all the required files in the importer.
		 *
		 * @since  1.0.0
		 */
		private function includes() {

			require_once ASTRA_SITES_DIR . 'inc/classes/class-astra-sites-notices.php';
			require_once ASTRA_SITES_DIR . 'inc/classes/class-astra-sites-page.php';
			require_once ASTRA_SITES_DIR . 'inc/classes/compatibility/class-astra-sites-compatibility.php';

			// Load the Importers.
			require_once ASTRA_SITES_DIR . 'inc/importers/class-astra-sites-helper.php';
			require_once ASTRA_SITES_DIR . 'inc/importers/class-widgets-importer.php';
			require_once ASTRA_SITES_DIR . 'inc/importers/class-astra-customizer-import.php';
			require_once ASTRA_SITES_DIR . 'inc/importers/wxr-importer/class-astra-wxr-importer.php';
			require_once ASTRA_SITES_DIR . 'inc/importers/class-astra-site-options-import.php';

			// White Label.
			require_once ASTRA_SITES_DIR . 'inc/classes/class-astra-sites-white-label.php';
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
						'message' => __( 'No plugin specified', 'astra-sites' ),
					)
				);
			}

			$data = array();
			$plugin_init        = ( isset( $_POST['init'] ) ) ? esc_attr( $_POST['init'] ) : '';
			$astra_site_options = ( isset( $_POST['options'] ) ) ? json_decode( stripslashes( $_POST['options'] ) ) : '';
			$enabled_extensions = ( isset( $_POST['enabledExtensions'] ) ) ? json_decode( stripslashes( $_POST['enabledExtensions'] ) ) : '';

			$data['astra_site_options'] = $astra_site_options;
			$data['enabled_extensions'] = $enabled_extensions;

			$activate = activate_plugin( $plugin_init, '', false, true );

			if ( is_wp_error( $activate ) ) {
				wp_send_json_error(
					array(
						'success' => false,
						'message' => $activate->get_error_message(),
					)
				);
			}

			do_action( 'astra_sites_after_plugin_activation', $plugin_init, $data );

			wp_send_json_success(
				array(
					'success' => true,
					'message' => __( 'Plugin Successfully Activated', 'astra-sites' ),
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
			check_ajax_referer( 'astra-sites', '_ajax_nonce' );

			$response = array(
				'active'       => array(),
				'inactive'     => array(),
				'notinstalled' => array(),
			);

			if ( ! current_user_can( 'customize' ) ) {
				wp_send_json_error( $response );
			}

			$required_plugins = ( isset( $_POST['required_plugins'] ) ) ? $_POST['required_plugins'] : array();

			if ( count( $required_plugins ) > 0 ) {
				foreach ( $required_plugins as $key => $plugin ) {

					/**
					 * Has Pro Version Support?
					  * And
					  * Is Pro Version Installed?
					 */
					$plugin_pro = self::pro_plugin_exist( $plugin['init'] );
					if ( $plugin_pro ) {

						// Pro - Active.
						if ( is_plugin_active( $plugin_pro['init'] ) ) {
							$response['active'][] = $plugin_pro;

							// Pro - Inactive.
						} else {
							$response['inactive'][] = $plugin_pro;
						}
					} else {

						// Lite - Installed but Inactive.
						if ( file_exists( WP_PLUGIN_DIR . '/' . $plugin['init'] ) && is_plugin_inactive( $plugin['init'] ) ) {

							$response['inactive'][] = $plugin;

							// Lite - Not Installed.
						} elseif ( ! file_exists( WP_PLUGIN_DIR . '/' . $plugin['init'] ) ) {

							$response['notinstalled'][] = $plugin;

							// Lite - Active.
						} else {
							$response['active'][] = $plugin;
						}
					}
				}
			}

			// Send response.
			wp_send_json_success( $response );
		}

		/**
		 * Has Pro Version Support?
		 * And
		 * Is Pro Version Installed?
		 *
		 * Check Pro plugin version exist of requested plugin lite version.
		 *
		 * Eg. If plugin 'BB Lite Version' required to import demo. Then we check the 'BB Agency Version' is exist?
		 * If yes then we only 'Activate' Agency Version. [We couldn't install agency version.]
		 * Else we 'Activate' or 'Install' Lite Version.
		 *
		 * @since 1.0.1
		 *
		 * @param  string $lite_version Lite version init file.
		 * @return mixed               Return false if not installed or not supported by us
		 *                                    else return 'Pro' version details.
		 */
		public static function pro_plugin_exist( $lite_version = '' ) {

			// Lite init => Pro init.
			$plugins = apply_filters(
				'astra_sites_pro_plugin_exist', array(
					'beaver-builder-lite-version/fl-builder.php' => array(
						'slug' => 'bb-plugin',
						'init' => 'bb-plugin/fl-builder.php',
						'name' => 'Beaver Builder Plugin (Agency Version)',
					),
					'ultimate-addons-for-beaver-builder-lite/bb-ultimate-addon.php' => array(
						'slug' => 'bb-ultimate-addon',
						'init' => 'bb-ultimate-addon/bb-ultimate-addon.php',
						'name' => 'Ultimate Addon for Beaver Builder',
					),
				), $lite_version
			);

			if ( isset( $plugins[ $lite_version ] ) ) {

				// Pro plugin directory exist?
				if ( file_exists( WP_PLUGIN_DIR . '/' . $plugins[ $lite_version ]['init'] ) ) {
					return $plugins[ $lite_version ];
				}
			}

			return false;
		}

		/**
		 * Ajax callback for demo import action.
		 *
		 * @since  1.0.0
		 */
		public function demo_ajax_import() {

			$report = array(
				'success' => false,
				'message' => '',
			);

			if ( ! current_user_can( 'customize' ) ) {
				$report['message'] = __( 'You have not "customize" access to import the astra site.', 'astra-sites' );
				wp_send_json( $report );
			}

			$demo_api_uri = isset( $_POST['api_url'] ) ? esc_url( $_POST['api_url'] ) : '';
			$this->import_demo( $demo_api_uri );

			$report['success'] = true;
			$report['message'] = __( 'Demo Imported Successfully.', 'astra-sites' );
			wp_send_json( $report );

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

			// Import Customizer Settings.
			$this->import_customizer_settings( $demo_data['astra-site-customizer-data'] );

			// Import XML.
			$this->import_wxr( $demo_data['astra-site-wxr-path'] );

			// Import WordPress site options.
			$this->import_site_options( $demo_data['astra-site-options-data'] );

			// Import Custom 404 extension options.
			$this->import_custom_404_extension_options( $demo_data['astra-custom-404'] );

			// Import Widgets data.
			$this->import_widgets( $demo_data['astra-site-widgets-data'] );

			// Clear Cache.
			$this->clear_cache();

			do_action( 'astra_sites_import_complete', $demo_data );
		}

		/**
		 * Clear Cache.
		 *
		 * @since  1.0.9
		 */
		public function clear_cache() {

			// Clear 'Elementor' file cache.
			if ( class_exists( '\Elementor\Plugin' ) ) {
				Elementor\Plugin::$instance->posts_css_manager->clear_cache();
			}

			// Clear 'Builder Builder' cache.
			if ( is_callable( 'FLBuilderModel::delete_asset_cache_for_all_posts' ) ) {
				FLBuilderModel::delete_asset_cache_for_all_posts();
			}
		}

		/**
		 * Import widgets and assign to correct sidebars.
		 *
		 * @since  1.0.0
		 *
		 * @param  (Object) $data Widgets data.
		 */
		private function import_widgets( $data ) {

			// bail if widgets data is not available.
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
			if ( is_callable( 'Astra_Admin_Helper::update_admin_settings_option' ) ) {
				Astra_Admin_Helper::update_admin_settings_option( '_astra_ext_enabled_extensions', $saved_extensions );
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
			if ( is_callable( 'Astra_Admin_Helper::update_admin_settings_option' ) ) {
				Astra_Admin_Helper::update_admin_settings_option( '_astra_ext_custom_404', $options_404 );
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
				'id'                         => '',
				'astra-site-widgets-data'    => '',
				'astra-site-customizer-data' => '',
				'astra-site-options-data'    => '',
				'astra-site-wxr-path'        => '',
				'astra-enabled-extensions'   => '',
				'astra-custom-404'           => '',
				'required-plugins'           => '',
			);

			$api_args = apply_filters(
				'astra_sites_api_args', array(
					'timeout' => 15,
				)
			);

			// Use this for premium demos.
			$request_params = apply_filters(
				'astra_sites_api_params', array(
					'purchase_key' => '',
					'site_url'     => '',
				)
			);

			$demo_api_uri = add_query_arg( $request_params, $demo_api_uri );

			// API Call.
			$response = wp_remote_get( $demo_api_uri, $api_args );

			if ( ! is_wp_error( $response ) || wp_remote_retrieve_response_code( $response ) === 200 ) {

				$result                                     = json_decode( wp_remote_retrieve_body( $response ), true );

				if ( ! isset( $result['code'] ) ) {
					$remote_args['id']                           = $result['id'];
					$remote_args['astra-site-widgets-data']      = json_decode( $result['astra-site-widgets-data'] );
					$remote_args['astra-site-customizer-data']   = $result['astra-site-customizer-data'];
					$remote_args['astra-site-options-data'] = $result['astra-site-options-data'];
					$remote_args['astra-site-wxr-path']          = $result['astra-site-wxr-path'];
					$remote_args['astra-enabled-extensions']     = $result['astra-enabled-extensions'];
					$remote_args['astra-custom-404']             = $result['astra-custom-404'];
					$remote_args['required-plugins']             = $result['required-plugins'];
				}
			}

			// Merge remote demo and defaults.
			return wp_parse_args( $remote_args, $defaults );
		}

	}

	/**
	 * Kicking this off by calling 'set_instance()' method
	 */
	Astra_Sites::set_instance();

endif;
