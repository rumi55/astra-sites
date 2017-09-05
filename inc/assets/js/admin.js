jQuery(document).ready(function ($) {
	resetPagedCount();

	initial_load_demos();
});

function initial_load_demos() {

	jQuery('body').addClass('loading-content');

	jQuery.ajax({
		url: astraDemo.ajaxurl,
		type: 'POST',
		dataType: 'json',
		data: {
			action   : 'astra-list-sites',
			category : 'all',
			id       : 'all',
			paged    : '1',
		},
	})
	.done(function (demos) {
		jQuery('body').removeClass('loading-content');

		// Has sites?
		if( demos.length ) {
			renderDemoGrid( demos );

		// Something is wrong in API request.
		} else {
			var template = wp.template('astra-no-demos');

			jQuery('.themes').append( template );
		}
	})
	.fail(function () {
		jQuery('body').removeClass('loading-content');
		jQuery('.spinner').after('<p class="no-themes" style="display:block;">'+astraDemo.strings.responseError+'</p>');
	});
}


/**
 * Enable Demo Import Button.
 */
function enable_demo_import_button( type = 'free' ) {

	switch( type ) {

		case 'free':
					// Get initial required plugins count.
					var remaining = parseInt( astraDemo.requiredPluginsCount ) || 0;

					// Enable demo import button.
					if( 0 >= remaining ) {

						jQuery('.astra-demo-import')
							.removeAttr('data-import')
							.addClass('button-primary')
							.text( astraDemo.strings.importDemo );
					}
			break;

		case 'upgrade':
					var demo_slug = jQuery('.wp-full-overlay-header').attr('data-demo-slug');

					jQuery('.astra-demo-import')
							.addClass('go-pro button-primary')
							.removeClass('astra-demo-import')
							.attr('target', '_blank')
							.attr('href', astraDemo.getUpgradeURL + demo_slug )
							.text( astraDemo.getUpgradeText )
							.append('<i class="dashicons dashicons-external"></i>');
			break;

		default:
					var demo_slug = jQuery('.wp-full-overlay-header').attr('data-demo-slug');

					jQuery('.astra-demo-import')
							.addClass('go-pro button-primary')
							.removeClass('astra-demo-import')
							.attr('target', '_blank')
							.attr('href', astraDemo.getProURL )
							.text( astraDemo.getProText )
							.append('<i class="dashicons dashicons-external"></i>');
			break;
	}

}

function resetPagedCount() {
	categoryId = jQuery('.filter-links li .current').data('id');
	jQuery('body').attr('data-astra-demo-paged', '1');
	jQuery('body').attr('data-astra-site-category', categoryId);
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
		id = body.attr('data-astra-site-category');
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
				action: 'astra-list-sites',
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
				jQuery('.spinner').after('<p class="no-themes" style="display:block;">'+astraDemo.strings.responseError+'</p>');
			});

	}
});

/**
 * Individual Site Preview
 *
 * On click on image, more link & preview button.
 */
jQuery(document).on('click', '.theme-browser .theme-screenshot, .theme-browser .more-details, .theme-browser .install-theme-preview', function (event) {
	event.preventDefault();

	$this = jQuery(this).parents('.theme');
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
	nextDemo = currentDemo.next('.theme');
	nextDemo.addClass('theme-preview-on');

	renderDemoPreview( nextDemo );

});

jQuery(document).on('click', '.previous-theme', function (event) {
	event.preventDefault();

	currentDemo = jQuery('.theme-preview-on');
	currentDemo.removeClass('theme-preview-on');
	prevDemo = currentDemo.prev('.theme');
	prevDemo.addClass('theme-preview-on');

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
		slug: $button.data( 'slug' )
	} );

} );

jQuery(document).on( 'wp-plugin-install-error', function( event, response ) {

	var $message = jQuery( '.plugin-card-' + response.slug ).find( '.install-now' );

	$message.removeClass( 'button-disabled' )
		.addClass( 'button-primary' )
		.html( wp.updates.l10n.installNow );

});

jQuery(document).on( 'wp-plugin-install-success', function( event, response ) {
	event.preventDefault();

	var $message     = jQuery( '.plugin-card-' + response.slug ).find( '.install-now' );
	var $siteOptions = jQuery( '.wp-full-overlay-header').find('.astra-site-options').val();
	var $enabledExtensions = jQuery( '.wp-full-overlay-header').find('.astra-enabled-extensions').val();

	// Transform the 'Install' button into an 'Activate' button.
	var $init = $message.data('init');

	$message.removeClass( 'install-now installed button-disabled updated-message' )
		.addClass('updating-message')
		.html( astraDemo.strings.btnActivating );

	// WordPress adds "Activate" button after waiting for 1000ms. So we will run our activation after that.
	setTimeout( function() {

		jQuery.ajax({
			url: astraDemo.ajaxurl,
			type: 'POST',
			data: {
				'action'	: 'astra-required-plugin-activate',
				'init'		: $init,
				'options'	: $siteOptions,
				'enabledExtensions' : $enabledExtensions,
			},
		})
		.done(function (result) {

			if( result.success ) {
				$message.removeClass( 'button-primary activate-now updating-message' )
				.attr('disabled', 'disabled')
				.addClass('disabled')
				.text( astraDemo.strings.btnActive );

				// Enable Demo Import Button
				astraDemo.requiredPluginsCount--;
				enable_demo_import_button();
			} else {

				$message.removeClass( 'updating-message' );

			}

		});

	}, 1200 );

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

	$button.addClass('updating-message button-primary')
		.html( astraDemo.strings.btnActivating );

	var $siteOptions = jQuery( '.wp-full-overlay-header').find('.astra-site-options').val();
	var $enabledExtensions = jQuery( '.wp-full-overlay-header').find('.astra-enabled-extensions').val();

	jQuery.ajax({
		url: astraDemo.ajaxurl,
		type: 'POST',
		data: {
			'action'	: 'astra-required-plugin-activate',
			'init'		: $init,
			'options' 	: $siteOptions,
			'enabledExtensions' 	: $enabledExtensions,
		},
	})
	.done(function (result) {

		if( result.success ) {
			$button.removeClass( 'button-primary activate-now updating-message' )
				.attr('disabled', 'disabled')
				.addClass('disabled')
				.text( astraDemo.strings.btnActive );

			// Enable Demo Import Button
			astraDemo.requiredPluginsCount--;
			enable_demo_import_button();
		}

	})
	.fail(function () {
	});

} );

function renderDemoPreview(anchor) {

	var demoId           = anchor.data('id') || '',
		apiURL           = anchor.data('demo-api') || '',
		demoType         = anchor.data('demo-type') || '',
		demoURL          = anchor.data('demo-url') || '',
		screenshot       = anchor.data('screenshot') || '',
		demo_name        = anchor.data('demo-name') || '',
		demo_slug        = anchor.data('demo-slug') || '',
		content          = anchor.data('content') || '',
		requiredPlugins  = anchor.data('required-plugins') || '',
		astraSiteOptions = anchor.find('.astra-site-options').val() || '';
		astraEnabledExtensions = anchor.find('.astra-enabled-extensions').val() || '';

	var template = wp.template('astra-demo-preview');

	templateData = [{
		id                       : demoId,
		astra_demo_type          : demoType,
		astra_demo_url           : demoURL,
		demo_api                 : apiURL,
		screenshot               : screenshot,
		demo_name                : demo_name,
		slug               		 : demo_slug,
		content                  : content,
		requiredPlugins          : requiredPlugins,
		astra_site_options       : astraSiteOptions,
		astra_enabled_extensions : astraEnabledExtensions,
	}];

	// Initial set count.
	astraDemo.requiredPluginsCount = requiredPlugins.length || 0;

	// delete any earlier fullscreen preview before we render new one.
	jQuery('.theme-install-overlay').remove();

	jQuery('#astra-sites-menu-page').append(template(templateData[0]));
	jQuery('.theme-install-overlay').css('display', 'block');
	checkNextPrevButtons();

	var desc       = jQuery('.theme-details');
	var descHeight = parseInt( desc.outerHeight() );
	var descBtn    = jQuery('.theme-details-read-more');

	if( jQuery.isArray( requiredPlugins ) ) {

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
						descBtn.html( astraDemo.strings.DescExpand );
					});
				} else {
					desc.animate({ height: descHeight },
						300, function() {
						descBtn.addClass('open');
						descBtn.html( astraDemo.strings.DescCollapse );
					});
				}

			});
		}

		// or
		var $pluginsFilter    = jQuery( '#plugin-filter' ),
			data 			= {
								_ajax_nonce		 : astraDemo._ajax_nonce,
								required_plugins : requiredPlugins
							};

		jQuery('.required-plugins').addClass('loading').html('<span class="spinner is-active"></span>');

		wp.ajax.post( 'astra-required-plugins', data ).done( function( response ) {

			// Remove loader.
			jQuery('.required-plugins').removeClass('loading').html('');

			/**
			 * Count remaining plugins.
			 * @type number
			 */
			var remaining_plugins = 0;

			/**
			 * Not Installed
			 *
			 * List of not installed required plugins.
			 */
			if ( typeof response.notinstalled !== 'undefined' ) {

				// Add not have installed plugins count.
				remaining_plugins += parseInt( response.notinstalled.length );

				jQuery( response.notinstalled ).each(function( index, plugin ) {

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

				});
			}

			/**
			 * Inactive
			 *
			 * List of not inactive required plugins.
			 */
			if ( typeof response.inactive !== 'undefined' ) {

				// Add inactive plugins count.
				remaining_plugins += parseInt( response.inactive.length );

				jQuery( response.inactive ).each(function( index, plugin ) {

					var output  = '<div class="plugin-card ';
						output += ' 		plugin-card-'+plugin.slug+'"';
						output += ' 		data-slug="'+plugin.slug+'">';
						output += '	<span class="title">'+plugin.name+'</span>';

						output += '	<button class="button activate-now button-primary"';
						output += '		data-init="' + plugin.init + '">';
						output += 	wp.updates.l10n.activatePlugin;
						output += '	</button>';
						output += '</div>';

					jQuery('.required-plugins').append(output);

				});
			}

			/**
			 * Active
			 *
			 * List of not active required plugins.
			 */
			if ( typeof response.active !== 'undefined' ) {

				jQuery( response.active ).each(function( index, plugin ) {

					var output  = '<div class="plugin-card ';
						output += ' 		plugin-card-'+plugin.slug+'"';
						output += ' 		data-slug="'+plugin.slug+'">';
						output += '	<span class="title">'+plugin.name+'</span>';
						output += '	<button class="button disabled"';
						output += '			data-slug="' + plugin.slug + '"';
						output += '			data-name="' + plugin.name + '">';
						output += astraDemo.strings.btnActive;
						output += '	</button>';
						output += '</div>';

					jQuery('.required-plugins').append(output);

				});
			}

			/**
			 * Enable Demo Import Button
			 * @type number
			 */
			astraDemo.requiredPluginsCount = remaining_plugins;
			enable_demo_import_button();

		} );

	} else {

		// Enable Demo Import Button
		astraDemo.requiredPluginsCount = requiredPlugins.length;
		enable_demo_import_button( demoType );
		jQuery('.required-plugins-wrap').remove();
	}

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
			action: 'astra-list-sites',
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
				jQuery('.spinner').after('<p class="no-themes" style="display:block;">'+astraDemo.strings.responseError+'</p>');
		});

});

var ref;
jQuery(document).on('keyup input', '#wp-filter-search-input', function () {
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
				action: 'astra-list-sites',
				search: $this,
				id: id,
			},
		})
			.done(function (demos) {
				jQuery('.filter-links li a[data-id="all"]').addClass('current');
				jQuery('.filter-links li a[data-id="all"]').parent('li').siblings().find('.current').removeClass('current');
				jQuery('body').removeClass('loading-content');

				if (demos.length > 0) {
					renderDemoGrid(demos);
				} else {
					jQuery('.spinner').after('<p class="no-themes" style="display:block;">'+astraDemo.strings.searchNoFound+'</p>');
				}

			})
			.fail(function () {
				jQuery('body').removeClass('loading-content');
				jQuery('.spinner').after('<p class="no-themes" style="display:block;">'+astraDemo.strings.responseError+'.</p>');
			});

	}, 500);

});

function renderDemoGrid(demos) {
	jQuery.each(demos, function (index, demo) {

		id               = demo.id;
		content          = demo.content;
		demo_api         = demo.demo_api;
		demo_name        = demo.title;
		demo_slug        = demo.slug;
		screenshot       = demo.featured_image_url;
		astra_demo_url   = demo.astra_demo_url;
		astra_demo_type  = demo.astra_demo_type;
		requiredPlugins  = demo.required_plugins;
		status  = demo.status;
		astraSiteOptions = demo.astra_site_options || '';
		astraEnabledExtensions = demo.astra_enabled_extensions || '';

		templateData = [{
			id: id,
			astra_demo_type: astra_demo_type,
			status: status,
			astra_demo_url: astra_demo_url,
			demo_api: demo_api,
			screenshot: screenshot,
			demo_name: demo_name,
			slug: demo_slug,
			content: content,
			required_plugins: requiredPlugins,
			astra_site_options: astraSiteOptions,
			astra_enabled_extensions: astraEnabledExtensions
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

	var $this 	 = jQuery(this),
		disabled = $this.attr('data-import');

	if ( typeof disabled !== 'undefined' && disabled === 'disabled' ) {

		// Highlight required plugins list.
		var pluginTitle = jQuery('.required-plugins-wrap h4');
		pluginTitle.css({'background-color':'rgba(255, 235, 59, 0.20)'});
		setTimeout(function() {
			pluginTitle.css({'background-color':''});
		}, 1000);

		return;
	}

	// Proceed?
	if( ! confirm( astraDemo.strings.importWarning ) ) {
		return;
	}

	jQuery('.astra-demo-import').attr('data-import', 'disabled')
		.addClass('updating-message installing')
		.text('Importing Demo');

	$this.closest('.theme').focus();

	var $theme = $this.closest('.astra-sites-preview').find('.wp-full-overlay-header');

	var apiURL = $theme.data('demo-api') || '';
	console.log('apiURL: ' + apiURL);

	// Get all info.
	jQuery.ajax({
		url: astraDemo.ajaxurl,
		type: 'POST',
		dataType: 'json',
		data: {
			action: 'astra-import-info',
			api_url: apiURL
		},
	})
	.done(function ( demos ) {
		console.log('demos: ' + demos);
		console.log('demos: ' + JSON.stringify( demos ) );

		importer( demos );
	})
	.fail(function ( demos ) {
		console.log( 'failed' );
	});
	

	// jQuery.ajax({
	// 	url: astraDemo.ajaxurl,
	// 	type: 'POST',
	// 	dataType: 'json',
	// 	data: {
	// 		action: 'astra-import-demo',
	// 		api_url: apiURL
	// 	},
	// })
	// .done(function ( demos ) {

	// 	if( demos.success ) {
	// 		jQuery('.astra-demo-import').removeClass('updating-message installing')
	// 			.removeAttr('data-import')
	// 			.addClass('view-site')
	// 			.removeClass('astra-demo-import')
	// 			.text( astraDemo.strings.viewSite )
	// 			.attr('target', '_blank')
	// 			.append('<i class="dashicons dashicons-external"></i>')
	// 			.attr('href', astraDemo.siteURL );
	// 	}
	// })
	// .fail(function ( demos ) {
	// 	jQuery('.astra-demo-import').removeClass('updating-message installing')
	// 		.removeAttr('data-import')
	// 		.addClass('view-site')
	// 		.removeClass('astra-demo-import')
	// 		.attr('target', '_blank')
	// 		.attr('href', astraDemo.strings.importFailedURL );

	// 	jQuery('.wp-full-overlay-header .view-site').text( astraDemo.strings.importFailedBtnSmall ).append('<i class="dashicons dashicons-external"></i>');
	// 	jQuery('.footer-import-button-wrap .view-site').text( astraDemo.strings.importFailedBtnLarge ).append('<i class="dashicons dashicons-external"></i>');
	// });
	

});

(function ($) {
// var url = 'http://sites-wpastra.sharkz.in/wp-json/wp/v2/astra-sites/17383';

	// // Get all info.
	// jQuery.ajax({
	// 	url: astraDemo.ajaxurl,
	// 	type: 'POST',
	// 	dataType: 'json',
	// 	data: {
	// 		action: 'astra-import-info',
	// 		api_url: 'http://sites-wpastra.sharkz.in/wp-json/wp/v2/astra-sites/17383'
	// 	},
	// })
	// .done(function ( demos ) {
	// 	console.log('demos: ' + demos);
	// 	console.log('demos: ' + JSON.stringify( demos ) );

	// 	importer( demos );
	// })
	// .fail(function ( demos ) {
	// 	console.log( 'failed' );
	// });




	importer();

	
	console.log('Processing' );
	
})(jQuery);

function importer( ) {

	var wxrImport = {
		complete: {
			posts: 0, //posts: 0,
			media: 0, //media: 0,
			users: 0, //users: 0,
			comments: 0, //comments: 0,
			terms: 0 //terms: 0,
		},

		updateDelta: function (type, delta) {
			console.log('type: ' + type);
			this.complete[ type ] += delta;

			var self = this;
			requestAnimationFrame(function () {
				self.render();
			});
		},
		updateProgress: function ( type, complete, total ) {
			var text = complete + '/' + total;
			console.log('text: ' + type);
			console.log('text: ' + text);
			document.getElementById( 'completed-' + type ).innerHTML = text;
			total = parseInt( total, 10 );
			if ( 0 === total || isNaN( total ) ) {
				total = 1;
			}
			var percent = parseInt( complete, 10 ) / total;
			console.log('percent: ' + percent);

			document.getElementById( 'progress-' + type ).innerHTML = Math.round( percent * 100 ) + '%';
			document.getElementById( 'progressbar-' + type ).value = percent * 100;
		},
		render: function () {
			var types = Object.keys( this.complete );
			var complete = 0;
			var total = 0;

			for (var i = types.length - 1; i >= 0; i--) {
				var type = types[i];
				this.updateProgress( type, this.complete[ type ], this.data.count[ type ] );

				complete += this.complete[ type ];
				total += this.data.count[ type ];
			}

			this.updateProgress( 'total', complete, total );
		}
	};

	console.log('astraDemo.importer: ' + JSON.stringify( astraDemo.importer ) );

	wxrImport.data = astraDemo.importer;
	wxrImport.render();

	console.log('wxrImport.data.url: ' + wxrImport.data.url);

	var evtSource = new EventSource( wxrImport.data.url );
	evtSource.onmessage = function ( message ) {
		var data = JSON.parse( message.data );

		console.log('data: ' + JSON.stringify( data ) );
		
		switch ( data.action ) {
			case 'updateDelta':
				wxrImport.updateDelta( data.type, data.delta );
				break;

			case 'complete':
				evtSource.close();
				var import_status_msg = jQuery('#import-status-message');
				import_status_msg.text( wxrImport.data.strings.complete );
				import_status_msg.removeClass('notice-info');
				import_status_msg.addClass('notice-success');
				break;
		}
	};
	evtSource.addEventListener( 'log', function ( message ) {
		var data = JSON.parse( message.data );
		console.log('data: ' + JSON.stringify( data ) );
		console.log('data: ' + data);
		var row = document.createElement('tr');
		var level = document.createElement( 'td' );
		level.appendChild( document.createTextNode( data.level ) );
		row.appendChild( level );

		var message = document.createElement( 'td' );
		message.appendChild( document.createTextNode( data.message ) );
		row.appendChild( message );

		jQuery('#import-log').append( row );
	});
}