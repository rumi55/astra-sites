jQuery( '.astra-demo-import' ).on('click', function(event) {
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
		console.log("success");
		$this.removeClass('updating-message installing').text( 'Demo Imported' ).attr('disabled', 'disabled');
	})
	.fail(function() {
		console.log("error");
		$this.removeClass('updating-message installing').text( 'Error.' );
	});

});