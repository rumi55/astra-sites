jQuery(document).ready(function($) {
	resetPagedCount();
});

function resetPagedCount() {
	categoryId = jQuery( '.filter-links li .current' ).data('id');
	jQuery( 'body' ).attr( 'data-astra-demo-paged', '1' );
	jQuery( 'body' ).attr( 'data-astra-demo-category', categoryId );
	jQuery( 'body' ).attr( 'data-astra-demo-search', '' );
	jQuery( 'body' ).attr( 'data-scrolling', false );
}

function updatedPagedCount() {
	paged = parseInt( jQuery( 'body' ).attr( 'data-astra-demo-paged' ) );
	jQuery( 'body' ).attr( 'data-astra-demo-paged', paged + 1 );
	window.setTimeout(function() {
		jQuery( 'body' ).data( 'scrolling', false );
	}, 800);
}

jQuery( document ).scroll(function(event) {
	var scrollDistance = jQuery(window).scrollTop();

    var themesBottom 	 = Math.abs( jQuery(window).height() - jQuery('.themes').offset().top - jQuery('.themes').height() );
    themesBottomEary 	 = themesBottom	* 20 / 100;
    themesBottomLarge 	 = themesBottom	* 70 / 100;

    ajaxLoading = jQuery( 'body' ).data( 'scrolling' );

    if ( scrollDistance > themesBottomEary && ajaxLoading == false ) {    	
		updatedPagedCount();
		jQuery( 'body' ).data( 'scrolling', true );
		body = jQuery( 'body' );
		id = body.attr( 'data-astra-demo-category' );
		search = body.attr( 'data-astra-demo-search' );
		paged = body.attr( 'data-astra-demo-paged' );

		if ( search !== '' ) {
			id = '';
		} else {
			search = '';
		}
		
		jQuery.ajax({
			url: ajaxurl,
			type: 'POST',
			dataType: 'json',
			data: {
				action: 'astra-list-demos',
				id: id,
				paged: paged,
				search: search
			},
		})
		.done(function(demos) {
			jQuery( 'body' ).removeClass('loading-content');
			renderDemoGrid( demos );
		})
		.fail(function() {
			jQuery( 'body' ).removeClass('loading-content');
			jQuery( '.spinner' ).after('<p class="no-themes" style="display:block;">There was a problem receiving a response from server.</p>');
		});

    }
});

jQuery( document ).on('click', '.theme-screenshot, .more-details, .theme-name, .install-theme-preview', function(event) {
	event.preventDefault();
	
	$this 	= jQuery( this ).parents( '.theme' );
	anchor 	= $this.find('.astra-demo-import');
	$this.addClass('theme-preview-on');	

	renderDemoPreview( anchor );
});

jQuery( document ).on('click', '.close-full-overlay', function(event) {
	event.preventDefault();
	
	jQuery('.theme-install-overlay').css('display', 'none');
	jQuery('.theme-install-overlay').remove();
	jQuery( '.theme-preview-on' ).removeClass('theme-preview-on');
});

jQuery( document ).on('click', '.next-theme', function(event) {
	event.preventDefault();
	
	currentDemo = jQuery( '.theme-preview-on' )
	currentDemo.removeClass('theme-preview-on');
	nextDemo = currentDemo.nextAll('.theme');
	nextDemo.addClass( 'theme-preview-on' );

	anchor 		= nextDemo.find('.astra-demo-import');
	renderDemoPreview( anchor );
});

jQuery( document ).on('click', '.previous-theme', function(event) {
	event.preventDefault();
	
	currentDemo = jQuery( '.theme-preview-on' );
	currentDemo.removeClass('theme-preview-on');
	prevDemo = currentDemo.prevAll('.theme');
	prevDemo.addClass( 'theme-preview-on' );
	anchor = prevDemo.find('.astra-demo-import');
	renderDemoPreview( anchor );
});

function renderDemoPreview( anchor ) {
	demoId 		= anchor.data('id');
	apiURL 		= anchor.data('demo-api');
	demoURL 	= anchor.data('demo-url');
	screenshot 	= anchor.data('screenshot');
	demo_name 	= anchor.data('demo-name');
	content 	= anchor.data('content');

	var template = wp.template('astra-demo-preview');

	templateData = [{id: demoId, astra_demo_url: demoURL, demo_api: apiURL, screenshot: screenshot, demo_name: demo_name, content: content}]

	// delete any earlier fullscreen preview before we render new one.
	jQuery( '.theme-install-overlay' ).remove();
	jQuery( '.wrap' ).append( template( templateData[0] ) );
	jQuery('.theme-install-overlay').css('display', 'block');
	checkNextPrevButtons();

	return;
}

function checkNextPrevButtons() {
	currentDemo = jQuery( '.theme-preview-on' );
	nextDemo = currentDemo.nextAll('.theme').length;
	prevDemo = currentDemo.prevAll('.theme').length;
	
	if ( nextDemo == 0 ) {
		jQuery( '.next-theme' ).addClass('disabled');
	} else if ( nextDemo != 0 ) {
		jQuery( '.next-theme' ).removeClass('disabled');
	}

	if ( prevDemo == 0 ) {
		jQuery( '.previous-theme' ).addClass('disabled');
	} else if ( prevDemo != 0 ) {
		jQuery( '.previous-theme' ).removeClass('disabled');
	}

	return;
}

jQuery( document ).on('click', '.filter-links li a', function(event) {
	event.preventDefault();
		
	resetPagedCount();
	$this = jQuery( this );
	slug = $this.data( 'sort' );
	id = $this.data( 'id' );
	paged = parseInt( jQuery( 'body' ).attr( 'data-astra-demo-paged' ) );

	$this.parent( 'li' ).siblings().find('.current').removeClass('current');
	$this.addClass('current');

	if ( slug == 'all' ) {
		category = 'all';
	} else {
		category = slug;
	}

	jQuery( 'body' ).addClass('loading-content');
	jQuery( '.theme-browser .theme' ).remove();
	jQuery( '.no-themes' ).remove();
	jQuery( '#wp-filter-search-input' ).val( '' );

	jQuery.ajax({
		url: ajaxurl,
		type: 'POST',
		dataType: 'json',
		data: {
			action: 'astra-list-demos',
			category: category,
			id: id,
			paged: paged,
		},
	})
	.done(function(demos) {
		jQuery( 'body' ).removeClass('loading-content');
		renderDemoGrid( demos );
	})
	.fail(function() {
		jQuery( 'body' ).removeClass('loading-content');
		jQuery( '.spinner' ).after('<p class="no-themes" style="display:block;">There was a problem receiving a response from server.</p>');
	});

});

var ref;
jQuery( document ).on('keyup', '#wp-filter-search-input', function (){
	$this = jQuery( '#wp-filter-search-input' ).val();

	id = '';
	if ( $this.length < 2 ) {
		id = 'all';
	}

	window.clearTimeout(ref);
	ref = window.setTimeout( function(){
		ref = null;

		jQuery( 'body' ).addClass('loading-content');
		jQuery( '.theme-browser .theme' ).remove();
		jQuery( '.no-themes' ).remove();
		jQuery( 'body' ).attr( 'data-astra-demo-search', $this );

		jQuery.ajax({
			url: ajaxurl,
			type: 'POST',
			dataType: 'json',
			data: {
				action: 'astra-list-demos',
				search: $this,
				id: id,
			},
		})
		.done(function(demos) {
			jQuery( '.filter-links li a[data-id="all"]' ).addClass('current');
			jQuery( '.filter-links li a[data-id="all"]' ).parent( 'li' ).siblings().find('.current').removeClass('current');
			jQuery( 'body' ).removeClass('loading-content');

			if ( demos.length > 0 ) {
				renderDemoGrid( demos );
			} else {
				jQuery( '.spinner' ).after('<p class="no-themes" style="display:block;">No Demos found, Try a different search.</p>');
			}

		})
		.fail(function() {
			jQuery( 'body' ).removeClass('loading-content');
			jQuery( '.spinner' ).after('<p class="no-themes" style="display:block;">There was a problem receiving a response from server.</p>');
		});

	} , 500);

});

function renderDemoGrid( demos ) {
	jQuery.each(demos, function(index, demo) {
		screenshot = demo.featured_image_url;
		id = demo.id;
		astra_demo_url = demo.astra_demo_url;
		demo_api = demo.demo_api;
		demo_name = demo.title;
		content = demo.content;

		templateData = [{id: id, astra_demo_url: astra_demo_url, demo_api: demo_api, screenshot: screenshot, demo_name: demo_name, content: content}]

		var template = wp.template('astra-single-demo');
		jQuery( '.themes' ).append( template( templateData[0] ) );
	});
}

jQuery( document ).on('click', '.collapse-sidebar', function(event) {
	event.preventDefault();

	overlay = jQuery( '.wp-full-overlay' );

	if( overlay.hasClass('expanded') ) {
		overlay.removeClass('expanded');
		overlay.addClass('collapsed');
		return;
	}

	if( overlay.hasClass('collapsed') ) {
		overlay.removeClass('collapsed');
		overlay.addClass('expanded');
		return;
	}
});

jQuery( document ).on('click', '.astra-demo-import', function(event) {
	event.preventDefault();

	$this = jQuery( this );

	disabled = $this.attr('disabled');

	if (typeof disabled !== typeof undefined && disabled !== false) {
		return;
	}

	$this.addClass('updating-message installing').text( 'Importing Demo' );
	$this.closest( '.theme' ).focus();

	demoId = $this.data('id');
	apiURL = $this.data('demo-api');

	jQuery.ajax({
		url: ajaxurl,
		type: 'POST',
		dataType: 'json',
		data: {
			action: 'astra-import-demo',
			api_url: apiURL
		},
	})
	.done(function() {
		$this.removeClass('updating-message installing').text( 'Demo Imported' ).attr('disabled', 'disabled');
	})
	.fail(function() {
		$this.removeClass('updating-message installing').text( 'Error.' );
	});

});