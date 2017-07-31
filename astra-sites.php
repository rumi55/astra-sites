<?php
/**
 * Plugin Name: Astra Sites
 * Plugin URI: http://www.wpastra.com/pro/
 * Description: Import sites build with Astra theme.
 * Version: 1.0.0
 * Author: Brainstorm Force
 * Author URI: http://www.brainstormforce.com
 * Text Domain: astra-sites
 *
 * @package Astra Sites
 */

/**
 * Set constants.
 */
define( 'ASTRA_SITES_VER',  '1.0.0' );
define( 'ASTRA_SITES_FILE', __FILE__ );
define( 'ASTRA_SITES_BASE', plugin_basename( ASTRA_SITES_FILE ) );
define( 'ASTRA_SITES_DIR',  plugin_dir_path( ASTRA_SITES_FILE ) );
define( 'ASTRA_SITES_URI',  plugins_url( '/', ASTRA_SITES_FILE ) );

require_once ASTRA_SITES_DIR . 'classes/class-astra-sites.php';

// add_action( 'init', function() {

// 	vl( SiteOrigin_Widgets_Bundle::$default_active_widgets );
// 	// global $wp_registered_sidebars;
// 	// // vl( $wp_registered_sidebars );
// 	// var_dump( get_theme_mod( 'custom_css_post_id', '' ) );

// 	// $styles = wp_get_custom_css();
// 	// echo strip_tags( $styles );
// 	wp_die();
// } );
