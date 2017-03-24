jQuery( '.astra-demo-import' ).on('click', function(event) {
	event.preventDefault();

	$this = jQuery( this );
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
		console.log("success");
	})
	.fail(function() {
		console.log("error");
	});

});