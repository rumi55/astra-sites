<?php
/**
 * Astra Sites Compatibility for 'Astra Pro'
 *
 * @see  https://wordpress.org/plugins/astra-pro/
 *
 * @package Astra Sites
 * @since 1.0.0
 */

if ( ! class_exists( 'Astra_Sites_Compatibility_Astra_Pro' ) ) :

	/**
	 * Astra_Sites_Compatibility_Astra_Pro
	 *
	 * @since 1.0.0
	 */
	class Astra_Sites_Compatibility_Astra_Pro {

		/**
		 * Instance
		 *
		 * @access private
		 * @var object Class object.
		 * @since 1.0.0
		 */
		private static $instance;

		/**
		 * Initiator
		 *
		 * @since 1.0.0
		 * @return object initialized object of class.
		 */
		public static function instance() {
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
			add_action( 'astra_sites_after_plugin_activation', array( $this, 'astra_pro' ), 10, 2 );
		}

		/**
		 * Update Site Origin Active Widgets
		 *
		 * @since 1.0.0
		 *
		 * @param  string $plugin_init        Plugin init file.
		 * @param  array  $data               Data.
		 * @return void
		 */
		function astra_pro( $plugin_init = '', $data = array() ) {

			if ( 'astra-addon/astra-addon.php' === $plugin_init ) {

				$data = json_decode( json_encode( $data ), true );

				if ( isset( $data['enabled_extensions'] ) ) {
					$extensions = $data['enabled_extensions'];

					if ( ! empty( $extensions ) ) {
						if ( is_callable( 'Astra_Admin_Helper::update_admin_settings_option' ) ) {
							Astra_Admin_Helper::update_admin_settings_option( '_astra_ext_enabled_extensions', $extensions );
						}
					}
				}
			}

		}

	}

	/**
	 * Kicking this off by calling 'instance()' method
	 */
	Astra_Sites_Compatibility_Astra_Pro::instance();

endif;
