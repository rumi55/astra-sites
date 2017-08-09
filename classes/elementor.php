<?php


function get_attachments() {
	$all_attachments = array();
	$attachments = get_posts( array(
	    'posts_per_page' => -1,
	    'post_type' => 'attachment',
	) );
	// vl( $attachments );
	foreach ($attachments as $key => $attachment) {
		vl( $attachment->ID );
		vl( $attachment->guid );
	}
	$all_attachments = array();
	wp_die( );

	// $settings = Plugin::$instance->templates_manager->get_import_images_instance()->import( $settings );
	// vl( $settings );

}
add_action( 'admin_init', 'get_attachments' );

