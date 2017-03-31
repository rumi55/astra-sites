<?php

class Astra_Demo_Import_Admin {

	private static $_instance = null;

	public static function instance() {
		if ( ! isset( self::$_instance ) ) {
			self::$_instance = new self;
		}

		return self::$_instance;
	}

	private function __construct() {
		add_filter( 'ast_menu_options', array( $this, 'astra_demo_import_menu' ) );
		add_action( 'ast_menu_astra_demos_action', array( $this, 'view_astra_demos' ) );
	}

	public function astra_demo_import_menu( $actions ) {

		$actions['astra-demos'] = array(
			'label' => __( 'Astra Demos', 'astra-demo-import' ),
			'show'  => ! is_network_admin(),
		);

		return $actions;
	}

	public function view_astra_demos() {
		include ADI_DIR . 'admin/view-astra-demos.php';
	}

}

Astra_Demo_Import_Admin::instance();
