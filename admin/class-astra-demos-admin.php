<?php
/**
 * Astra Demo Importer Admin
 *
 * @package Astra Addon
 */

defined( 'ABSPATH' ) or exit;

/**
 * Astra Demo Importer Admin
 *
 * @since 1.0.0
 */
class Astra_Demos_Admin {

	/**
	 * Instance of Astra_Demos_Admin
	 *
	 * @since  1.0.0
	 * @var Astra_Demos_Admin
	 */
	private static $_instance = null;

	/**
	 * Instanciate Astra_Demos_Admin
	 *
	 * @since  1.0.0
	 * @return (Object) Astra_Demos_Admin
	 */
	public static function instance() {
		if ( ! isset( self::$_instance ) ) {
			self::$_instance = new self;
		}

		return self::$_instance;
	}

	/**
	 * Constructor
	 *
	 * @since  1.0.0
	 */
	private function __construct() {

		add_filter( 'astra_menu_options',            array( $this, 'menu' ) );
		add_action( 'astra_menu_astra_demos_action', array( $this, 'view_demos' ) );

	}

	/**
	 * Register admin menu for demo importer
	 *
	 * @since  1.0.0
	 *
	 * @param  (Array) $actions Previously registered tabs menus.
	 *
	 * @return (Array) registered tabs menus in Astra menu.
	 */
	public function menu( $actions ) {

		$actions['astra-demos'] = array(
			'label' => __( 'Astra Demos', 'astra-demos' ),
			'show'  => ! is_network_admin(),
		);

		return $actions;
	}

	/**
	 * View Astra Demos
	 *
	 * @since  1.0.0
	 */
	public function view_demos() {

		include ASTRA_DEMOS_DIR . 'admin/view-astra-demos.php';
	}

}

Astra_Demos_Admin::instance();
