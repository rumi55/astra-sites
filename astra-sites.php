<?php
/**
 * Plugin Name: Astra Sites
 * Plugin URI: http://www.wpastra.com/pro/
 * Description: Import sites build with Astra theme.
 * Version: 1.0.1
 * Author: Brainstorm Force
 * Author URI: http://www.brainstormforce.com
 * Text Domain: astra-sites
 *
 * @package Astra Sites
 */

/**
 * Set constants.
 */
define( 'ASTRA_SITES_VER',  '1.0.1' );
define( 'ASTRA_SITES_FILE', __FILE__ );
define( 'ASTRA_SITES_BASE', plugin_basename( ASTRA_SITES_FILE ) );
define( 'ASTRA_SITES_DIR',  plugin_dir_path( ASTRA_SITES_FILE ) );
define( 'ASTRA_SITES_URI',  plugins_url( '/', ASTRA_SITES_FILE ) );



add_action( 'plugins_loaded', function() {
	require_once ASTRA_SITES_DIR . 'classes/class-astra-sites.php';
	require_once ASTRA_SITES_DIR . 'classes/elementor.php';
} );

// add_action( 'admin_init', function() {

// 	$attachment_url = 'https://library.elementor.com/wp-content/uploads/2016/08/0004.png';
// 	$filename       = basename( $attachment_url );
// 	$file_content   = wp_remote_retrieve_body( wp_safe_remote_get( $attachment_url ) );
// 	$upload = wp_upload_bits(
// 		$filename,
// 		null,
// 		$file_content
// 	);

// 	vl( $upload );

// 	// vl( $file_content );
// 	// vl( sha1( 'f5d2f0285a7deab69481da9d4361daaa7bae1df5' ) );
// 	// vl( get_option( 'elementor_remote_info_templates_data' ) );
// 	wp_die();
// } );
