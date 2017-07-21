<?php
/**
 * Plugin Name: Astra Demos
 * Plugin URI: http://www.wpastra.com/
 * Description: Astra Demo Importer.
 * Version: 1.0.0
 * Author: Brainstorm Force
 * Author URI: http://www.brainstormforce.com
 * Text Domain: astra-demos
 *
 * @package Astra Demos
 */

/**
 * Set constants.
 */
define( 'ASTRA_DEMOS_FILE', __FILE__ );
define( 'ASTRA_DEMOS_BASE', plugin_basename( ASTRA_DEMOS_FILE ) );
define( 'ASTRA_DEMOS_DIR',  plugin_dir_path( ASTRA_DEMOS_FILE ) );
define( 'ASTRA_DEMOS_URI',  plugins_url( '/', ASTRA_DEMOS_FILE ) );
define( 'ASTRA_DEMOS_VER',  '1.0.0' );

require_once ASTRA_DEMOS_DIR . 'classes/class-astra-demos.php';
