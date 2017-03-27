<?php
/**
 * Plugin Name:     Astra Demo Import
 * Plugin URI:      PLUGIN SITE HERE
 * Description:     PLUGIN DESCRIPTION HERE
 * Author:          YOUR NAME HERE
 * Author URI:      YOUR SITE HERE
 * Text Domain:     astra-demo-import
 * Domain Path:     /languages
 * Version:         0.1.0
 *
 * @package         Astra_Demo_Import
 */

defined( 'ABSPATH' ) or exit;

define( 'ADI_DIR', plugin_dir_path( __FILE__ ) );
define( 'ADI_URI', plugins_url( '/', __FILE__ ) );
define( 'ADI_VER', '0.1.0' );

require_once ADI_DIR . 'class-astra-demo-import.php';

add_action( 'plugins_loaded', 'adi_init' );

function adi_init() {

	$importer_api = 'http://multi.sharkz.in/wp-json/wp/v2/astra-demos/';
	Astra_Demo_Import::instance( $importer_api );
	
}