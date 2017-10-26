<?php
/**
 * Beaver Builder Compatibility File.
 *
 * @package Astra
 */

/**
 * Astra Beaver Builder Compatibility
 */
if ( ! class_exists( 'Astra_Sites_Compatibility_Beaver_Builder_Downloader' ) ) :

	/**
	 * Astra Beaver Builder Compatibility
	 *
	 * @since 1.0.0
	 */
	class Astra_Sites_Compatibility_Beaver_Builder_Downloader {

		/**
		 * Member Variable
		 *
		 * @var object instance
		 */
		private static $instance;

		/**
		 * Initiator
		 */
		public static function set_instance() {
			if ( ! isset( self::$instance ) ) {
				self::$instance = new self;
			}
			return self::$instance;
		}

		/**
		 * Constructor
		 */
		public function __construct() {
		}

		
	}

	/**
	 * Kicking this off by calling 'set_instance()' method
	 */
	Astra_Sites_Compatibility_Beaver_Builder_Downloader::set_instance();

endif;