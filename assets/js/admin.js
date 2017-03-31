jQuery( document ).on('click', '.theme', function(event) {
	event.preventDefault();
	
	$this 	= jQuery( this );
	anchor 	= $this.find('.astra-demo-import');
	$this.addClass('theme-preview-on');

	renderDemoPreview( anchor );
});

jQuery( document ).on('click', '.close-full-overlay', function(event) {
	event.preventDefault();
	
	jQuery('.theme-install-overlay').css('display', 'none');
	jQuery('.theme-install-overlay').remove();
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

	var template = wp.template('astra-demo-preview');

	templateData = [{id: demoId, astra_demo_url: demoURL, demo_api: apiURL, screenshot: screenshot}]

	jQuery( '.wrap' ).append( template( templateData[0] ) );
	jQuery('.theme-install-overlay').css('display', 'block');
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

	$this.addClass('updating-message installing').text( 'Importing The Demo' );
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