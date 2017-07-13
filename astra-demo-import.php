<?php
/**
 * Plugin Name: Astra Demo Import
 * Plugin URI: http://www.wpastra.com/
 * Description: Demo Importer
 * Version: 1.0.0-beta.1
 * Author: Brainstorm Force
 * Author URI: http://www.brainstormforce.com
 * Text Domain: astra-demo-import
 *
 * @package Astra Demo Import
 */

/**
 * Set constants.
 */
define( 'ASTRA_DEMO_IMPORT_FILE', __FILE__ );
define( 'ASTRA_DEMO_IMPORT_BASE', plugin_basename( ASTRA_DEMO_IMPORT_FILE ) );
define( 'ASTRA_DEMO_IMPORT_DIR',  plugin_dir_path( ASTRA_DEMO_IMPORT_FILE ) );
define( 'ASTRA_DEMO_IMPORT_URI',  plugins_url( '/', ASTRA_DEMO_IMPORT_FILE ) );
define( 'ASTRA_DEMO_IMPORT_VER',  '1.0.0-beta.1' );

require_once ASTRA_DEMO_IMPORT_DIR . 'classes/class-astra-demo-import.php';