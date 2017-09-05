jQuery(document).ready(function ($) {

	jQuery( '.astra-notice.is-dismissible' ).on( 'click', function() {
		var $id = jQuery( this ).attr( 'id' ) || '';
		var $time = jQuery( this ).attr( 'dismissible-time' ) || '';
		var $meta = jQuery( this ).attr( 'dismissible-meta' ) || '';

		jQuery.ajax({
			url: ajaxurl,
			type: 'POST',
			data: {
				action : 'astra-notices', // Registered with wp_ajax_ & wp_ajax_nopriv_
				id : $id,
				meta : $meta,
				time : $time,
			},
		})
		.done( function ( result ) {
			console.log('result: ' + result);
		})
		.fail(function () {
			// Fail.
		});

	});

});