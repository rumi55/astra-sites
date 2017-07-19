jQuery(document).ready(function ($) {
	resetPagedCount();
});

function vl( data, is_json ) {
	
	if( is_json ) {
		console.log( JSON.stringify( data ) );
	} else {
		console.log( data );
	}
}

/**
 * Enable Demo Import Button.
 */
function enable_demo_import_button( type = 'free' ) {

	if( 'free' === type ) {
		// Get initial required plugins count.
		var remaining = parseInt( astraDemo.requiredPluginsCount ) || 0;

		// Decrease count as per the requested from AJAX.
		remaining--;

		// Set required plugin count.
		astraDemo.requiredPluginsCount = remaining;

		// Enable demo import button.
		if( 0 >= remaining ) {

			jQuery('.astra-demo-import')
				.removeAttr('disabled')
				.addClass('button-primary')
				.text( 'Import Demo' );
			// jQuery('.wp-full-overlay-header').append('<button class="astra-demo-import button button-primary">Import Demo</button>');
		// } else {
		// 	jQuery('.wp-full-overlay-header').append('<button class="astra-demo-import button button-primary">Install Plugins</button>');
		}
	} else {

		jQuery('.astra-demo-import')
				.addClass('go-pro')
				.removeClass('astra-demo-import')
				.attr('target', '_blank')
				.attr('href', 'https://wpastra.com/pro/')
				.text( 'Get Pro' )
				.prepend('<i class="dashicons dashicons-external"></i>');
	}
}

function resetPagedCount() {
	categoryId = jQuery('.filter-links li .current').data('id');
	jQuery('body').attr('data-astra-demo-paged', '1');
	jQuery('body').attr('data-astra-demo-category', categoryId);
	jQuery('body').attr('data-astra-demo-search', '');
	jQuery('body').attr('data-scrolling', false);
	jQuery('body').attr( 'data-required-plugins', 0 )
}

function updatedPagedCount() {
	paged = parseInt(jQuery('body').attr('data-astra-demo-paged'));
	jQuery('body').attr('data-astra-demo-paged', paged + 1);
	window.setTimeout(function () {
		jQuery('body').data('scrolling', false);
	}, 800);
}

jQuery(document).scroll(function (event) {
	var scrollDistance = jQuery(window).scrollTop();

	var themesBottom = Math.abs(jQuery(window).height() - jQuery('.themes').offset().top - jQuery('.themes').height());
	themesBottom = themesBottom * 20 / 100;

	ajaxLoading = jQuery('body').data('scrolling');

	if (scrollDistance > themesBottom && ajaxLoading == false) {
		updatedPagedCount();
		jQuery('body').data('scrolling', true);
		body = jQuery('body');
		id = body.attr('data-astra-demo-category');
		search = body.attr('data-astra-demo-search');
		paged = body.attr('data-astra-demo-paged');

		if (search !== '') {
			id = '';
		} else {
			search = '';
		}

		jQuery('.no-themes').remove();

		jQuery.ajax({
			url: astraDemo.ajaxurl,
			type: 'POST',
			dataType: 'json',
			data: {
				action: 'astra-list-demos',
				id: id,
				paged: paged,
				search: search
			},
		})
			.done(function (demos) {
				jQuery('body').removeClass('loading-content');
				renderDemoGrid(demos);
			})
			.fail(function () {
				jQuery('body').removeClass('loading-content');
				jQuery('.spinner').after('<p class="no-themes" style="display:block;">There was a problem receiving a response from server.</p>');
			});

	}
});

jQuery(document).on('click', '.theme-screenshot, .more-details, .install-theme-preview', function (event) {
	event.preventDefault();

	$this = jQuery(this).parents('.theme');
	// anchor = $this.find('.astra-demo-import');
	$this.addClass('theme-preview-on');

	renderDemoPreview($this);
});

jQuery(document).on('click', '.close-full-overlay', function (event) {
	event.preventDefault();

	jQuery('.theme-install-overlay').css('display', 'none');
	jQuery('.theme-install-overlay').remove();
	jQuery('.theme-preview-on').removeClass('theme-preview-on');
});

jQuery(document).on('click', '.next-theme', function (event) {
	event.preventDefault();
	currentDemo = jQuery('.theme-preview-on')
	currentDemo.removeClass('theme-preview-on');
	nextDemo = currentDemo.nextAll('.theme');
	nextDemo.addClass('theme-preview-on');

	// anchor = nextDemo.find('.astra-demo-import');
	renderDemoPreview( nextDemo );

});

jQuery(document).on('click', '.previous-theme', function (event) {
	event.preventDefault();

	currentDemo = jQuery('.theme-preview-on');
	currentDemo.removeClass('theme-preview-on');
	prevDemo = currentDemo.prevAll('.theme');
	prevDemo.addClass('theme-preview-on');
	
	// anchor = prevDemo.find('.astra-demo-import');
	renderDemoPreview(prevDemo);
});

/**
 * Click handler for plugin installs in plugin install view.
 *
 * @since 4.6.0
 *
 * @param {Event} event Event interface.
 */
jQuery(document).on('click', '.install-now', function (event) {
	event.preventDefault();

	var $button 	= jQuery( event.target ),
		$document   = jQuery(document);

	if ( $button.hasClass( 'updating-message' ) || $button.hasClass( 'button-disabled' ) ) {
		return;
	}

	if ( wp.updates.shouldRequestFilesystemCredentials && ! wp.updates.ajaxLocked ) {
		wp.updates.requestFilesystemCredentials( event );

		$document.on( 'credential-modal-cancel', function() {
			var $message = $( '.install-now.updating-message' );

			$message
				.removeClass( 'updating-message' )
				.text( wp.updates.l10n.installNow );

			wp.a11y.speak( wp.updates.l10n.updateCancel, 'polite' );
		} );
	}

	wp.updates.installPlugin( {
		slug: $button.data( 'slug' ),
	} );

} );

jQuery(document).on( 'wp-plugin-install-success', function( event, response ) {
	event.preventDefault();
	
	// Enable Demo Import Button
	enable_demo_import_button();
});


/**
 * Click handler for plugin installs in plugin install view.
 *
 * @since 4.6.0
 *
 * @param {Event} event Event interface.
 */
jQuery(document).on('click', '.activate-now', function (event) {
	event.preventDefault();

	var $button = jQuery( event.target ),
		$init 	= $button.data( 'init' );

	if ( $button.hasClass( 'updating-message' ) || $button.hasClass( 'button-disabled' ) ) {
		return;
	}

	$button.addClass( 'updating-message' );

	console.log('Slug: ' + $init );

	jQuery.ajax({
		url: astraDemo.ajaxurl,
		type: 'POST',
		dataType: 'json',
		data: {
			'action'	: 'astra-required-plugin-activate',
			'init'		: $init
		},
	})
	.done(function (result) {

		if( result.success ) {
			$button.removeClass( 'button-primary activate-now updating-message' )
				.addClass('disabled')
				.text( wp.updates.l10n.pluginInstalled );

			// Enable Demo Import Button
			enable_demo_import_button();
		}

	})
	.fail(function () {
	});

} );

// jQuery(document).on('click', '.theme-details-read-more', function (event) {

// 	var self = jQuery(this);

// 	var maxHeight = jQuery('.theme-details').outerHeight();


// 	vl( jQuery('.theme-details').outerHeight() );

// 	jQuery('.theme-details').animate({ height: 57 });

// 	vl( 'toggled' );

// 	if( self.hasClass('open') ) {
// 		vl( 'open' );
// 		jQuery('.theme-details').animate({ height: 57 },
// 			1000, function() {
// 			self.removeClass('open');
// 		});
// 		// jQuery('.theme-details').css('height',57);
		
// 	} else {
// 		vl( 'closed' );
// 		jQuery('.theme-details').animate({ height: maxHeight },
// 			1000, function() {
// 			self.addClass('open');
// 		});
// 		// jQuery('.theme-details').css('height','100%');
// 	}

	
// });

function renderDemoPreview(anchor) {

	var demoId          = anchor.data('id'),
		apiURL          = anchor.data('demo-api'),
		demoType        = anchor.data('demo-type'),
		demoURL         = anchor.data('demo-url'),
		screenshot      = anchor.data('screenshot'),
		demo_name       = anchor.data('demo-name'),
		content         = anchor.data('content'),
		requiredPlugins = anchor.data('required-plugins') || '';

	vl( demoType );

	var template = wp.template('astra-demo-preview');

	templateData = [{
		id                      : demoId,
		astra_demo_type         : demoType,
		astra_demo_url          : demoURL,
		demo_api                : apiURL,
		screenshot              : screenshot,
		demo_name               : demo_name,
		content                 : content,
		requiredPlugins         : requiredPlugins
	}];

	// Initial set count.
	astraDemo.requiredPluginsCount = requiredPlugins.length || 0;

	// delete any earlier fullscreen preview before we render new one.
	jQuery('.theme-install-overlay').remove();

	jQuery('#ast-menu-page').append(template(templateData[0]));
	jQuery('.theme-install-overlay').css('display', 'block');
	checkNextPrevButtons();

	var desc       = jQuery('.theme-details');
	var descHeight = parseInt( desc.outerHeight() );
	var descBtn    = jQuery('.theme-details-read-more');

	if( descHeight >= 55 ) {

		// Show button.
		descBtn.css( 'display', 'inline-block' );

		// Set height upto 3 line.
		desc.css( 'height', 57 );

		// Button Click.
		descBtn.click(function(event) {

			if( descBtn.hasClass('open') ) {
				desc.animate({ height: 57 },
					300, function() {
					descBtn.removeClass('open');
				});
				
			} else {
				desc.animate({ height: descHeight },
					300, function() {
					descBtn.addClass('open');
				});
			}

		});
	}

	if( 'free' === demoType ) {

		// or
		var $pluginsFilter    = jQuery( '#plugin-filter' ),
			data 			= {
								_ajax_nonce		 : astraDemo._ajax_nonce,
								required_plugins : requiredPlugins
							};

		jQuery('.required-plugins').html('<span class="spinner is-active"></span>');

		wp.ajax.post( 'astra-required-plugins', data ).done( function( result ) {

			jQuery('.required-plugins').html('');
		// jQuery.ajax({
		// 		url: astraDemo.ajaxurl,
		// 		type: 'POST',
		// 		dataType: 'json',
		// 		data: {
		// 			action 			 : 'astra-required-plugins',
		// 			_ajax_nonce		 : astraDemo._ajax_nonce,
		// 			required_plugins : requiredPlugins
		// 		},
		// 	})
		// 	.done(function ( result ) {

			if( result.success ) {
				
				// Set required plugin count.
				astraDemo.requiredPluginsCount = 0;	

				// Enable Demo Import Button
				enable_demo_import_button();
			}

			/**
			 * Not Installed
			 *
			 * List of not installed required plugins.
			 */
			if ( typeof result.plugins.notinstalled !== 'undefined' ) {

				jQuery( result.plugins.notinstalled ).each(function( index, plugin ) {

					var output  = '<div class="plugin-card ';
						output += ' 		plugin-card-'+plugin.slug+'"';
						output += ' 		data-slug="'+plugin.slug+'">';
						output += '	<span class="title">'+plugin.name+'</span>';
						output += '	<button class="button install-now"';
						output += '			data-init="' + plugin.init + '"';
						output += '			data-slug="' + plugin.slug + '"';
						output += '			data-name="' + plugin.name + '">';
						output += 	wp.updates.l10n.installNow;
						output += '	</button>';
						output += '</div>';

					jQuery('.required-plugins').append(output);
					// $pluginsFilter.html(output);

				});
			}

			/**
			 * Inactive
			 *
			 * List of not inactive required plugins.
			 */
			if ( typeof result.plugins.inactive !== 'undefined' ) {

				jQuery( result.plugins.inactive ).each(function( index, plugin ) {

					var output  = '<div class="plugin-card ';
						output += ' 		plugin-card-'+plugin.slug+'"';
						output += ' 		data-slug="'+plugin.slug+'">';
						output += '	<span class="title">'+plugin.name+'</span>';

						output += '	<button class="button activate-now button-primary"';
						output += '		data-init="' + plugin.init + '">';
						output += 	wp.updates.l10n.activatePlugin;
						output += '	</button>';
						
						// output += '	<a class="button activate-now button-primary"';
						// output += '			data-slug="' + plugin.slug + '"';
						// output += '			data-name="' + plugin.name + '">';
						// output += 	wp.updates.l10n.activatePlugin;
						// output += '	</a>';
						output += '</div>';

					jQuery('.required-plugins').append(output);
					// $pluginsFilter.html(output);

				});
			}

			/**
			 * Active
			 *
			 * List of not active required plugins.
			 */
			if ( typeof result.plugins.active !== 'undefined' ) {

				jQuery( result.plugins.active ).each(function( index, plugin ) {

					var output  = '<div class="plugin-card ';
						output += ' 		plugin-card-'+plugin.slug+'"';
						output += ' 		data-slug="'+plugin.slug+'">';
						output += '	<span class="title">'+plugin.name+'</span>';
						output += '	<button class="button disabled"';
						output += '			data-slug="' + plugin.slug + '"';
						output += '			data-name="' + plugin.name + '">';
						output += 'Activated';
						// output += 	wp.updates.l10n.pluginInstalled;
						output += '	</button>';
						output += '</div>';

					jQuery('.required-plugins').append(output);
					// $pluginsFilter.html(output);

				});
			}
		} );

	} else {

		// Enable Demo Import Button
		enable_demo_import_button( demoType );
		jQuery('.required-plugins-wrap').remove();
	}

	// Generate a new password.
	// wp.ajax.post( 'generate-password' )
	// 	.done( function( data ) {
	// 		vl( data );
	// 	} );

	// vl( 'requiredPlugins' );
	// vl( requiredPlugins );

	// if ( typeof requiredPlugins !== 'undefined' && typeof requiredPlugins !== '' ) {
	// 	// vl( 'process' );
	// 	// jQuery( '#plugin-filter' ).html('');
	// 	jQuery.ajax({
	// 			url: astraDemo.ajaxurl,
	// 			type: 'POST',
	// 			dataType: 'json',
	// 			data: {
	// 				action: 'astra-required-plugins',
	// 				'required-plugins': requiredPlugins
	// 			},
	// 		})
	// 		.done(function (plugins) {

	// 			/**
	// 			 * Not Installed
	// 			 *
	// 			 * List of not installed required plugins.
	// 			 */
	// 			if ( typeof plugins.notinstalled !== 'undefined' ) {

	// 				jQuery('.astra-demo-import').addClass('disabled').removeClass('astra-demo-import button-primary');

	// 				jQuery( plugins.notinstalled ).each(function( index, plugin ) {

	// 					var output  = '<div class="plugin-card ';
	// 						output += ' 		plugin-card-'+plugin.slug+'"';
	// 						output += ' 		data-slug="'+plugin.slug+'">';
	// 						output += '	<span class="title">'+plugin.name+'</span>';
	// 						output += '	<button class="button install-now"';
	// 						output += '			data-slug="' + plugin.slug + '"';
	// 						output += '			data-name="' + plugin.name + '">';
	// 						output += 	wp.updates.l10n.installNow;
	// 						output += '	</button>';
	// 						output += '</div>';

	// 					jQuery( '#plugin-filter' ).html(output);

	// 				});
	// 			}

	// 			/**
	// 			 * Inactive
	// 			 *
	// 			 * List of not inactive required plugins.
	// 			 */
	// 			if ( typeof plugins.inactive !== 'undefined' ) {

	// 				console.log('Okay: ');

	// 				jQuery('.astra-demo-import').addClass('disabled').removeClass('button-primary');

	// 				jQuery( plugins.inactive ).each(function( index, plugin ) {

	// 					var output  = '<div class="plugin-card ';
	// 						output += ' 		plugin-card-'+plugin.slug+'"';
	// 						output += ' 		data-slug="'+plugin.slug+'">';
	// 						output += '	<span class="title">'+plugin.name+'</span>';

	// 						output += '	<button class="button activate-now button-primary"';
	// 						output += '		data-init="' + plugin.init + '">';
	// 						output += 	wp.updates.l10n.activatePlugin;
	// 						output += '	</button>';
							
	// 						// output += '	<a class="button activate-now button-primary"';
	// 						// output += '			data-slug="' + plugin.slug + '"';
	// 						// output += '			data-name="' + plugin.name + '">';
	// 						// output += 	wp.updates.l10n.activatePlugin;
	// 						// output += '	</a>';
	// 						output += '</div>';

	// 					jQuery( '#plugin-filter' ).html(output);

	// 				});
	// 			}

	// 			/**
	// 			 * Active
	// 			 *
	// 			 * List of not active required plugins.
	// 			 */
	// 			if ( typeof plugins.active !== 'undefined' ) {

	// 				jQuery( plugins.active ).each(function( index, plugin ) {

	// 					var output  = '<div class="plugin-card ';
	// 						output += ' 		plugin-card-'+plugin.slug+'"';
	// 						output += ' 		data-slug="'+plugin.slug+'">';
	// 						output += '	<span class="title">'+plugin.name+'</span>';
	// 						output += '	<button class="button disabled"';
	// 						output += '			data-slug="' + plugin.slug + '"';
	// 						output += '			data-name="' + plugin.name + '">';
	// 						output += 	wp.updates.l10n.pluginInstalled;
	// 						output += '	</button>';
	// 						output += '</div>';

	// 					jQuery( '#plugin-filter' ).html(output);

	// 				});
	// 			}
	// 		})
	// 		.fail(function () {
	// 			jQuery('body').removeClass('loading-content');
	// 			jQuery('.spinner').after('<p class="no-themes" style="display:block;">There was a problem receiving a response from server.</p>');
	// 		});
	// } else {
	// 	vl( 'fail' );
	// }


	

	return;
}

function checkNextPrevButtons() {
	currentDemo = jQuery('.theme-preview-on');
	nextDemo = currentDemo.nextAll('.theme').length;
	prevDemo = currentDemo.prevAll('.theme').length;

	if (nextDemo == 0) {
		jQuery('.next-theme').addClass('disabled');
	} else if (nextDemo != 0) {
		jQuery('.next-theme').removeClass('disabled');
	}

	if (prevDemo == 0) {
		jQuery('.previous-theme').addClass('disabled');
	} else if (prevDemo != 0) {
		jQuery('.previous-theme').removeClass('disabled');
	}

	return;
}

jQuery(document).on('click', '.filter-links li a', function (event) {
	event.preventDefault();

	$this = jQuery(this);
	$this.parent('li').siblings().find('.current').removeClass('current');
	$this.addClass('current');
	slug = $this.data('sort');
	id = $this.data('id');

	resetPagedCount();
	paged = parseInt(jQuery('body').attr('data-astra-demo-paged'));

	if (slug == 'all') {
		category = 'all';
	} else {
		category = slug;
	}

	jQuery('body').addClass('loading-content');
	jQuery('.theme-browser .theme').remove();
	jQuery('.no-themes').remove();
	jQuery('#wp-filter-search-input').val('');

	jQuery.ajax({
		url: astraDemo.ajaxurl,
		type: 'POST',
		dataType: 'json',
		data: {
			action: 'astra-list-demos',
			category: category,
			id: id,
			paged: paged,
		},
	})
		.done(function (demos) {
			jQuery('body').removeClass('loading-content');
			renderDemoGrid(demos);
		})
		.fail(function () {
			jQuery('body').removeClass('loading-content');
			jQuery('.spinner').after('<p class="no-themes" style="display:block;">There was a problem receiving a response from server.</p>');
		});

});

var ref;
jQuery(document).on('keyup', '#wp-filter-search-input', function () {
	$this = jQuery('#wp-filter-search-input').val();

	id = '';
	if ($this.length < 2) {
		id = 'all';
	}

	window.clearTimeout(ref);
	ref = window.setTimeout(function () {
		ref = null;

		resetPagedCount();
		jQuery('body').addClass('loading-content');
		jQuery('.theme-browser .theme').remove();
		jQuery('.no-themes').remove();
		jQuery('body').attr('data-astra-demo-search', $this);

		jQuery.ajax({
			url: astraDemo.ajaxurl,
			type: 'POST',
			dataType: 'json',
			data: {
				action: 'astra-list-demos',
				search: $this,
				id: id,
			},
		})
			.done(function (demos) {
				console.log('demos: ');
				vl( demos );
				jQuery('.filter-links li a[data-id="all"]').addClass('current');
				jQuery('.filter-links li a[data-id="all"]').parent('li').siblings().find('.current').removeClass('current');
				jQuery('body').removeClass('loading-content');

				if (demos.length > 0) {
					renderDemoGrid(demos);
				} else {
					jQuery('.spinner').after('<p class="no-themes" style="display:block;">No Demos found, Try a different search.</p>');
				}

			})
			.fail(function () {
				jQuery('body').removeClass('loading-content');
				jQuery('.spinner').after('<p class="no-themes" style="display:block;">There was a problem receiving a response from server.</p>');
			});

	}, 500);

});

function renderDemoGrid(demos) {
	jQuery.each(demos, function (index, demo) {
		
		id                      = demo.id;
		content                 = demo.content;
		demo_api                = demo.demo_api;
		demo_name               = demo.title;
		screenshot              = demo.featured_image_url;
		astra_demo_url          = demo.astra_demo_url;
		astra_demo_type         = demo.astra_demo_type;
		requiredPlugins         = demo.required_plugins;

		templateData = [{
			id: id,
			astra_demo_type: astra_demo_type,
			astra_demo_url: astra_demo_url,
			demo_api: demo_api,
			screenshot: screenshot,
			demo_name: demo_name,
			content: content,
			required_plugins: requiredPlugins
		}]

		var template = wp.template('astra-single-demo');
		jQuery('.themes').append(template(templateData[0]));
	});
}

jQuery(document).on('click', '.collapse-sidebar', function (event) {
	event.preventDefault();

	overlay = jQuery('.wp-full-overlay');

	if (overlay.hasClass('expanded')) {
		overlay.removeClass('expanded');
		overlay.addClass('collapsed');
		return;
	}

	if (overlay.hasClass('collapsed')) {
		overlay.removeClass('collapsed');
		overlay.addClass('expanded');
		return;
	}
});

jQuery(document).on('click', '.astra-demo-import', function (event) {
	event.preventDefault();

	$this = jQuery(this);
	var disabled = $this.attr('disabled');

	if (typeof disabled !== 'undefined' && disabled !== false) {
		return;
	}

	$this.attr('disabled', 'disabled')
		.addClass('updating-message installing')
		.text('Importing Demo');

	$this.closest('.theme').focus();
	
	var $theme = $this.closest('.wp-full-overlay-header');

	var apiURL = $theme.data('demo-api') || '';

	vl( 'apiURL: ' + apiURL );
	vl( 'astraDemo.ajaxurl: ' + astraDemo.ajaxurl );

	jQuery.ajax({
		url: astraDemo.ajaxurl,
		type: 'POST',
		dataType: 'json',
		data: {
			action: 'astra-import-demo',
			api_url: apiURL
		},
	})
	.done(function ( demos ) {

		vl( demos );

		$this.removeClass('updating-message installing')
			.removeAttr('disabled')
			.addClass('view-site')
			.removeClass('astra-demo-import')
			.text('View Site')
			.attr('target', '_blank')
			.prepend('<i class="dashicons dashicons-external"></i>')
			.attr('href', astraDemo.site_url );
	})
	.fail(function ( demos ) {
		$this.removeClass('updating-message installing').text('Error.');
	});

	// vl( 'here 1' );

	// $this     = jQuery(this);
	// $disabled = jQuery(this).data('href') || '#';
	
	// if( $this.hasClass('go-pro') || $this.hasClass('view-site') ) {
	// 	return;
	// }

	// requiredPlugins = jQuery('.required-plugin');

	// vl( 'requiredPluginsCount: ' + requiredPluginsCount );

	// var requiredPluginsCount = requiredPlugins.length;
	// if (requiredPluginsCount == 0) {
	// 	jQuery($this).trigger('pluginsInstallComplete');
	// 	return;
	// }

	// jQuery( 'body' ).attr( 'data-required-plugins', requiredPluginsCount );

	// requiredPlugins.each(function (index, el) {

	// 	setTimeout(function () {

	// 		var productId = jQuery(el).data('product-id');
	// 		var action = jQuery(el).data('action');
	// 		var init = jQuery(el).data('plugin-init');

	// 		var data = {
	// 			'action': 'bsf_' + action,
	// 			'product_id': productId,
	// 			'init': init,
	// 			'bundled': true
	// 		};

	// 		// Install/Activate the required plugins.
	// 		jQuery.ajax({
	// 			url: astraDemo.ajaxurl,
	// 			type: 'POST',
	// 			data: data,
	// 		}).done(function (response) {

	// 			var plugin_status = response.split('|');
	// 			var status = plugin_status[ plugin_status.length -1 ];
				
	// 			// Add error message if FTP credentials are required for installing the extensions.
	// 			if (/Connection Type/i.test( response )) {
	// 				is_ftp = true;
	// 			}

	// 			jQuery($this).trigger('pluginInstalled');

	// 		});

	// 	}, index * 1500);

	// });

});

// jQuery(document).on('pluginInstalled', '.astra-demo-import', function (event) {
// 	$this = jQuery(this);
// 	requiredPlugins = parseInt( jQuery( 'body' ).attr( 'data-required-plugins' ) );

// 	var newRequiredPlugins = requiredPlugins - 1;
// 	jQuery( 'body' ).attr('data-required-plugins', newRequiredPlugins);
// 	if ( newRequiredPlugins == 0 ) {
// 		jQuery($this).trigger('pluginsInstallComplete');	
// 	}
	
// });

// jQuery(document).on('pluginsInstallComplete', '.astra-demo-import', function (event) {
// 	event.preventDefault();

// 	vl( 'here 2' );

// 	$this = jQuery(this);
// 	var disabled = $this.attr('disabled');

// 	if (typeof disabled !== 'undefined' && disabled !== false) {
// 		return;
// 	}

// 	$this.addClass('updating-message installing').text('Importing Demo');
// 	$this.closest('.theme').focus();

// 	demoId = $this.data('id');
// 	apiURL = $this.data('demo-api');

// 	vl( 'apiURL' );
// 	vl( apiURL );
// 	return;

// 	jQuery.ajax({
// 		url: astraDemo.ajaxurl,
// 		type: 'POST',
// 		dataType: 'json',
// 		data: {
// 			action: 'astra-import-demo',
// 			api_url: apiURL
// 		},
// 	})
// 	.done(function ( demos ) {

// 		$this.removeClass('updating-message installing')
// 			.addClass('view-site')
// 			.removeClass('astra-demo-import')
// 			.text('View Site')
// 			.attr('target', '_blank')
// 			.prepend('<i class="dashicons dashicons-external"></i>')
// 			.attr('href', 'https://wpastra.com/pro/');
// 	})
// 	.fail(function ( demos ) {
// 		$this.removeClass('updating-message installing').text('Error.');
// 	});

// });
