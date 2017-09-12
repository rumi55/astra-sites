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
			paged    : '1',
		},
	})
	.done(function (demos) {

		jQuery('body').removeClass('loading-content');
		jQuery('.filter-count .count').text( demos.sites_count );

		// Has sites?
		if ( demos.sites_count > 0 ) {
			renderDemoGrid( demos.sites );

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
 * Remove plugin from the queue.
 */
function removePluginFromQueue( removeItem, pluginsList ) {
	return jQuery.grep(pluginsList, function( value ) {
		return value.slug != removeItem;
	});
}

/**
 * AJAX Request Queue
 *
 * - add()
 * - remove()
 * - run()
 * - stop()
 *
 * @since 1.0.0
 */
var AstraSitesAjaxQueue = (function() {

	var requests = [];

	return {

		/**
		 * Add AJAX request
		 *
		 * @since 1.0.0
		 */
		add:  function(opt) {
		    requests.push(opt);
		},

		/**
		 * Remove AJAX request
		 *
		 * @since 1.0.0
		 */
		remove:  function(opt) {
		    if( jQuery.inArray(opt, requests) > -1 )
		        requests.splice($.inArray(opt, requests), 1);
		},

		/**
		 * Run / Process AJAX request
		 *
		 * @since 1.0.0
		 */
		run: function() {
		    var self = this,
		        oriSuc;

		    if( requests.length ) {
		        oriSuc = requests[0].complete;

		        requests[0].complete = function() {
		             if( typeof(oriSuc) === 'function' ) oriSuc();
		             requests.shift();
		             self.run.apply(self, []);
		        };

		        jQuery.ajax(requests[0]);

		    } else {

		      self.tid = setTimeout(function() {
		         self.run.apply(self, []);
		      }, 1000);
		    }
		},

		/**
		 * Stop AJAX request
		 *
		 * @since 1.0.0
		 */
		stop:  function() {

		    requests = [];
		    clearTimeout(this.tid);
		}
	};

}());

/**
 * Bulk Plugin Install and Activate
 */
function bulkPluginInstallActivate() {

	if( 0 === astraDemo.requiredPlugins.length ) {
		return;
	}

	var not_installed 	 = astraDemo.requiredPlugins.notinstalled || '';
	var activate_plugins = astraDemo.requiredPlugins.inactive || '';

	// First Install Bulk.
	if( not_installed.length > 0 ) {
		installAllPlugins( not_installed );
	}

	// Second Activate Bulk.
	if( activate_plugins.length > 0 ) {
		activateAllPlugins( activate_plugins );
	}

}

/**
 * Install All Plugins.
 */
function installAllPlugins( not_installed ) {
	
	jQuery.each( not_installed, function(index, single_plugin) {

		// Add each plugin activate request in Ajax queue.
		// @see wp-admin/js/updates.js
		wp.updates.queue.push( {
			action: 'install-plugin', // Required action.
			data:   {
				slug: single_plugin.slug
			}
		} );
	});

	// Required to set queue.
	wp.updates.queueChecker();
}

/**
 * Activate All Plugins.
 */
function activateAllPlugins( activate_plugins ) {

	console.log('started activate all: ');
	console.log('started activate all: ' + activate_plugins );

	// Process of cloud templates - (download, remove & fetch).
	AstraSitesAjaxQueue.run();

	jQuery.each( activate_plugins, function(index, single_plugin) {

		var $card    	 = jQuery( '.plugin-card-' + single_plugin.slug ),
			$button  	 = $card.find('.button'),
			$siteOptions = jQuery( '.wp-full-overlay-header').find('.astra-site-options').val();

		console.log('single_plugin.slug: ' + single_plugin.slug);

		$button.addClass('updating-message');
		// $button.append('<span class="spinner is-active"></span>');
		// $button.find('.dashicons').remove();

		AstraSitesAjaxQueue.add({
			url: astraDemo.ajaxurl,
			type: 'POST',
			data: {
				'action'	: 'astra-required-plugin-activate',
				'init'		: single_plugin.init,
				'options'	: $siteOptions,
			},
			success: function( result ){

				if( result.success ) {

					var pluginsList = astraDemo.requiredPlugins.inactive;

					// Reset not installed plugins list.
					astraDemo.requiredPlugins.inactive = removePluginFromQueue( single_plugin.slug, pluginsList );

					// console.log('astraDemo.requiredPlugins: ' + astraDemo.requiredPlugins);
					// console.log('astraDemo.requiredPlugins: ' + JSON.stringify(astraDemo.requiredPlugins));
					console.log('$button: ' + $button.attr('class') );
					console.log('$parent: ' + $button.parents('.plugin-card-' + single_plugin.slug).attr('class') );
					$button.removeClass( 'button-primary install-now activate-now updating-message' )
						.attr('disabled', 'disabled')
						.addClass('disabled')
						.text( astraDemo.strings.btnActive );

					// bulkPluginInstallActivate();

					// Enable Demo Import Button
					astraDemo.requiredPluginsCount--;
					enable_demo_import_button();




					// $button.removeClass('updating-message');
					// // $button.append('<span class="dashicons-yes dashicons"></span>');
					// // $button.find('.spinner').remove();

					// var pluginsList = astraDemo.requiredPlugins.inactive;

					// // Reset Plugin Queue.
					// astraDemo.requiredPlugins.inactive = removePluginFromQueue( single_plugin.slug, pluginsList );

					// // Enable Demo Import Button
					// astraDemo.requiredPluginsCount--;
					// enable_demo_import_button();
				}
			}
		});
	});
}

/**
 * Plugin Installing.
 */
jQuery(document).on('wp-plugin-installing', function (event, args) {
	event.preventDefault();

	var $card = jQuery( '.plugin-card-' + args.slug );

	$card.addClass('updating-message');
	
	// Remove icon.
	// $card.find('.dashicons').remove();

	// // Add spinner.
	// $card.append('<span class="spinner is-active"></span>');

});

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

/**
 * Plugin Install Success.
 */
// jQuery(document).on( 'wp-plugin-install-success', function( event, args ) {
// 	event.preventDefault();

// 	// Transform the 'Install' button into an 'Activate' button.
// 	var $card = jQuery( '.plugin-card-' + args.slug ),
// 		$init = $card.data('init'),
// 		$siteOptions = jQuery( '.wp-full-overlay-header').find('.astra-site-options').val();

// 	var pluginsList = astraDemo.requiredPlugins.notinstalled;

// 	// Reset not installed plugins list.
// 	astraDemo.requiredPlugins.notinstalled = removePluginFromQueue( args.slug, pluginsList );

// 	// WordPress adds "Activate" button after waiting for 1000ms.
// 	// So we will run our activation after that.
// 	setTimeout( function() {

// 		jQuery.ajax({
// 			url: astraDemo.ajaxurl,
// 			type: 'POST',
// 			dataType: 'json',
// 			data: {
// 				'action'	: 'astra-required-plugin-activate',
// 				'init'		: $init,
// 				'options'	: $siteOptions,
// 			},
// 		})
// 		.done(function (result) {

// 			if( result.success ) {

// 				var pluginsList = astraDemo.requiredPlugins.inactive;

// 				// Reset not installed plugins list.
// 				astraDemo.requiredPlugins.inactive = removePluginFromQueue( args.slug, pluginsList );

// 				// console.log('astraDemo.requiredPlugins: ' + astraDemo.requiredPlugins);
// 				// console.log('astraDemo.requiredPlugins: ' + JSON.stringify(astraDemo.requiredPlugins));

// 				$card.removeClass('updating-message');

// 				bulkPluginInstallActivate();

// 				// Enable Demo Import Button
// 				astraDemo.requiredPluginsCount--;
// 				enable_demo_import_button();

// 				// // If install plugin queue is empty?
// 				// // Then Start Bulk activate.
// 				// var not_installed = astraDemo.requiredPlugins.notinstalled;
// 				// console.log('not_installed.length: ' + not_installed.length);
// 				// console.log('not_installed: ' + JSON.stringify( not_installed ));
// 				// if( 'undefined' !== not_installed && 0 == not_installed.length ) {
// 				// 	activateAllPlugins();
// 				// }
// 				// // $card.find('.spinner').remove();
// 				// // $card.append('<span class="dashicons-yes dashicons"></span>');

// 				// var pluginsList = astraDemo.requiredPlugins.inactive;
// 				// astraDemo.requiredPlugins.inactive = removePluginFromQueue( args.slug, pluginsList );

// 				// // Enable Demo Import Button
// 				// astraDemo.requiredPluginsCount--;
// 				// enable_demo_import_button();

// 				// activateAllPlugins();
// 			}
// 		});

// 	}, 1000 );

// });



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

/**
 * Reset Page Count.
 */
function resetPagedCount() {
	categoryId = jQuery('.astra-category.filter-links li .current').data('id');
	jQuery('body').attr('data-astra-demo-paged', '1');
	jQuery('body').attr('data-astra-site-category', categoryId);
	jQuery('body').attr('data-astra-demo-search', '');
	jQuery('body').attr('data-scrolling', false);
	jQuery('body').attr( 'data-required-plugins', 0 )
}

/**
 * Update Page Count.
 */
function updatedPagedCount() {
	paged = parseInt(jQuery('body').attr('data-astra-demo-paged'));
	jQuery('body').attr('data-astra-demo-paged', paged + 1);
	window.setTimeout(function () {
		jQuery('body').data('scrolling', false);
	}, 800);
}

/**
 * On Scroll.
 */
jQuery(document).scroll(function (event) {
	var scrollDistance = jQuery(window).scrollTop();

	var themesBottom = Math.abs(jQuery(window).height() - jQuery('.themes').offset().top - jQuery('.themes').height());
	themesBottom = themesBottom * 20 / 100;

	ajaxLoading = jQuery('body').data('scrolling');

	if (scrollDistance > themesBottom && ajaxLoading == false) {
		updatedPagedCount();

		jQuery('body').data('scrolling', true);

		var body   = jQuery('body'),
			id     = body.attr('data-astra-site-category'),
			search = body.attr('data-astra-demo-search'),
			paged  = body.attr('data-astra-demo-paged');

		if (search !== '') {
			id = '';
		} else {
			search = '';
		}

		jQuery('.no-themes').remove();

		var astra_page_builder = jQuery('.filter-links.astra-page-builder'),
		astra_category 	   = jQuery('.filter-links.astra-category'),
		page_builder_id   	= astra_page_builder.find('.current').data('id'),
		category_id   		= astra_category.find('.current').data('id');

		jQuery.ajax({
			url: astraDemo.ajaxurl,
			type: 'POST',
			dataType: 'json',
			data: {
				action: 'astra-list-sites',
				paged: paged,
				search: search,
				page_builder_id : page_builder_id,
				category_id : category_id,
			},
		})
		.done(function (demos) {
			jQuery('body').removeClass('loading-content');
			if ( demos.sites_count > 0 ) {
				renderDemoGrid(demos.sites);
			}
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

	jQuery('html').addClass('astra-site-preview-on');

	renderDemoPreview($this);
});

/**
 * Close Full Overlay.
 */
jQuery(document).on('click', '.close-full-overlay', function (event) {
	event.preventDefault();

	jQuery('.theme-install-overlay').css('display', 'none');
	jQuery('.theme-install-overlay').remove();
	jQuery('.theme-preview-on').removeClass('theme-preview-on');
	jQuery('html').removeClass('astra-site-preview-on');
});

/**
 * Next Theme.
 */
jQuery(document).on('click', '.next-theme', function (event) {
	event.preventDefault();
	currentDemo = jQuery('.theme-preview-on')
	currentDemo.removeClass('theme-preview-on');
	nextDemo = currentDemo.next('.theme');
	nextDemo.addClass('theme-preview-on');

	renderDemoPreview( nextDemo );
});

/**
 * Previous Theme.
 */
jQuery(document).on('click', '.previous-theme', function (event) {
	event.preventDefault();

	currentDemo = jQuery('.theme-preview-on');
	currentDemo.removeClass('theme-preview-on');
	prevDemo = currentDemo.prev('.theme');
	prevDemo.addClass('theme-preview-on');

	renderDemoPreview(prevDemo);
});

/**
 * Plugin Installation Error.
 */
jQuery(document).on( 'wp-plugin-install-error', function( event, response ) {

	var $card = jQuery( '.plugin-card-' + response.slug );

	$card
		.addClass( 'button-primary' )
		.html( wp.updates.l10n.installNow );

	// $card.find('.spinner').remove();
	// $card.find('.title').after('<span class="dashicons-no dashicons"></span>');

});

jQuery(document).on( 'wp-plugin-install-success', function( event, response ) {
	event.preventDefault();

	var $message     = jQuery( '.plugin-card-' + response.slug ).find( '.button' );
	var $siteOptions = jQuery( '.wp-full-overlay-header').find('.astra-site-options').val();
	var $enabledExtensions = jQuery( '.wp-full-overlay-header').find('.astra-enabled-extensions').val();

	// Transform the 'Install' button into an 'Activate' button.
	var $init = $message.data('init');

	$message.removeClass( 'install-now installed button-disabled updated-message' )
		.addClass('updating-message')
		.html( astraDemo.strings.btnActivating );

	// Reset not installed plugins list.
	var pluginsList = astraDemo.requiredPlugins.notinstalled;
	astraDemo.requiredPlugins.notinstalled = removePluginFromQueue( response.slug, pluginsList );

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

				var pluginsList = astraDemo.requiredPlugins.inactive;

				// Reset not installed plugins list.
				astraDemo.requiredPlugins.inactive = removePluginFromQueue( response.slug, pluginsList );

				// console.log('astraDemo.requiredPlugins: ' + astraDemo.requiredPlugins);
				// console.log('astraDemo.requiredPlugins: ' + JSON.stringify(astraDemo.requiredPlugins));
				console.log('$button: ' + $message.attr('class') );
				console.log('$parent: ' + $message.parents('.plugin-card-' + response.slug).attr('class') );
				$message.removeClass( 'button-primary install-now activate-now updating-message' )
					.attr('disabled', 'disabled')
					.addClass('disabled')
					.text( astraDemo.strings.btnActive );

				// bulkPluginInstallActivate();

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
 * Render Demo Preview
 */
jQuery(document).on('click', '.activate-now', function (event) {
	event.preventDefault();

	var $button = jQuery( event.target ),
		$init 	= $button.data( 'init' ),
		$slug 	= $button.data( 'slug' );

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

			var pluginsList = astraDemo.requiredPlugins.inactive;

			// Reset not installed plugins list.
			astraDemo.requiredPlugins.inactive = removePluginFromQueue( $slug, pluginsList );

			// console.log('astraDemo.requiredPlugins: ' + astraDemo.requiredPlugins);
			// console.log('astraDemo.requiredPlugins: ' + JSON.stringify(astraDemo.requiredPlugins));
			console.log('$button: ' + $button.attr('class') );
			console.log('$parent: ' + $button.parents('.plugin-card-' + $slug).attr('class') );
			$button.removeClass( 'button-primary install-now activate-now updating-message' )
				.attr('disabled', 'disabled')
				.addClass('disabled')
				.text( astraDemo.strings.btnActive );

			// bulkPluginInstallActivate();

			// Enable Demo Import Button
			astraDemo.requiredPluginsCount--;
			enable_demo_import_button();


			// $button.removeClass( 'button-primary activate-now updating-message' )
			// 	.attr('disabled', 'disabled')
			// 	.addClass('disabled')
			// 	.text( astraDemo.strings.btnActive );

			// // Enable Demo Import Button
			// astraDemo.requiredPluginsCount--;
			// enable_demo_import_button();
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
		required_plugins        : JSON.stringify(requiredPlugins),
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
						output += ' 		data-slug="'+plugin.slug+'"';
						output += ' 		data-init="'+plugin.init+'">';
						output += '	<span class="title">'+plugin.name+'</span>';
						output += '	<button class="button install-now"';
						output += '			data-init="' + plugin.init + '"';
						output += '			data-slug="' + plugin.slug + '"';
						output += '			data-name="' + plugin.name + '">';
						output += 	wp.updates.l10n.installNow;
						output += '	</button>';
						// output += '	<span class="dashicons-no dashicons"></span>';
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
						output += ' 		data-slug="'+plugin.slug+'"';
						output += ' 		data-init="'+plugin.init+'">';
						output += '	<span class="title">'+plugin.name+'</span>';
						output += '	<button class="button activate-now button-primary"';
						output += '		data-init="' + plugin.init + '"';
						output += '		data-slug="' + plugin.slug + '"';
						output += '		data-name="' + plugin.name + '">';
						output += 	wp.updates.l10n.activatePlugin;
						output += '	</button>';
						// output += '	<span class="dashicons-no dashicons"></span>';
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
						output += ' 		data-slug="'+plugin.slug+'"';
						output += ' 		data-init="'+plugin.init+'">';
						output += '	<span class="title">'+plugin.name+'</span>';
						output += '	<button class="button disabled"';
						output += '			data-slug="' + plugin.slug + '"';
						output += '			data-name="' + plugin.name + '">';
						output += astraDemo.strings.btnActive;
						output += '	</button>';
						// output += '	<span class="dashicons-yes dashicons"></span>';
						output += '</div>';

					jQuery('.required-plugins').append(output);

				});
			}

			/**
			 * Enable Demo Import Button
			 * @type number
			 */
			astraDemo.requiredPluginsCount = remaining_plugins;
			astraDemo.requiredPlugins = response;
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

/**
 * Check Next Previous Buttons.
 */
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

/**
 * Filter Demo by Category.
 */
jQuery(document).on('click', '.filter-links li a', function (event) {
	event.preventDefault();

	$this = jQuery(this);
	$this.parent('li').siblings().find('.current').removeClass('current');
	$this.addClass('current');
	
	var astra_page_builder = jQuery('.filter-links.astra-page-builder'),
		astra_category 	   = jQuery('.filter-links.astra-category'),
		page_builder_id   	= astra_page_builder.find('.current').data('id'),
		category_id   		= astra_category.find('.current').data('id');

	resetPagedCount();
	
	paged = parseInt(jQuery('body').attr('data-astra-demo-paged'));

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
			paged: paged,
			page_builder_id : page_builder_id,
			category_id : category_id,
		},
	})
	.done(function (demos) {

		jQuery('.filter-count .count').text( demos.sites_count );
		jQuery('body').removeClass('loading-content');

		if ( demos.sites_count > 0 ) {
			renderDemoGrid(demos.sites);
		} else {
			jQuery('.spinner').after('<p class="no-themes" style="display:block;">'+astraDemo.strings.searchNoFound+'</p>');
		}

	})
	.fail(function () {
		jQuery('body').removeClass('loading-content');
		jQuery('.spinner').after('<p class="no-themes" style="display:block;">'+astraDemo.strings.responseError+'</p>');
	});

});

/**
 * Search Site.
 */
var ref;
jQuery(document).on('keyup input', '#wp-filter-search-input', function () {
	$this = jQuery('#wp-filter-search-input').val();

	id = '';
	if ($this.length < 2) {
		id = 'all';
	}

	var astra_page_builder = jQuery('.filter-links.astra-page-builder'),
		astra_category 	   = jQuery('.filter-links.astra-category'),
		page_builder_id   	= astra_page_builder.find('.current').data('id'),
		category_id   		= astra_category.find('.current').data('id');
	

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
				page_builder_id : page_builder_id,
				category_id : category_id,
			},
		})
		.done(function (demos) {
			jQuery('body').removeClass('loading-content');

			console.log('demos.sites_count: ' + demos.sites_count);

			jQuery('.filter-count .count').text( demos.sites_count );

			if ( demos.sites_count > 0 ) {
				renderDemoGrid(demos.sites);
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

/**
 * Render Demo Grid.
 */
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

/**
 * Collapse Sidebar.
 */
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

/**
 * Import Demo.
 */
jQuery(document).on('click', '.astra-demo-import', function (event) {
	event.preventDefault();

	var $this 	= jQuery(this),
		$theme  = $this.closest('.astra-sites-preview').find('.wp-full-overlay-header'),
		apiURL  = $theme.data('demo-api') || '',
		plugins = $theme.data('required-plugins');

	var disabled = $this.attr('data-import');

	if ( typeof disabled !== 'undefined' && disabled === 'disabled' ) {

		/**
		 * Process Bulk Plugin Install & Activate
		 */
		bulkPluginInstallActivate();

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

		// Success?
		if( demos.success ) {
			jQuery('.astra-demo-import').removeClass('updating-message installing')
				.removeAttr('data-import')
				.addClass('view-site')
				.removeClass('astra-demo-import')
				.text( astraDemo.strings.viewSite )
				.attr('target', '_blank')
				.append('<i class="dashicons dashicons-external"></i>')
				.attr('href', astraDemo.siteURL );

		} else {

			var output  = '<div class="astra-api-error notice notice-error notice-alt is-dismissible">';
				output += '	<p>'+demos.message+'</p>';
				output += '	<button type="button" class="notice-dismiss">';
				output += '		<span class="screen-reader-text">'+commonL10n.dismiss+'</span>';
				output += '	</button>';
				output += '</div>';

			jQuery('.install-theme-info').prepend( output );

			// !important to add trigger.
			// Which reinitialize the dismiss error message events.
			jQuery(document).trigger('wp-updates-notice-added');
		}

	})
	.fail(function ( demos ) {
		jQuery('.astra-demo-import').removeClass('updating-message installing')
			.removeAttr('data-import')
			.addClass('view-site')
			.removeClass('astra-demo-import')
			.attr('target', '_blank')
			.attr('href', astraDemo.strings.importFailedURL );

		jQuery('.wp-full-overlay-header .view-site').text( astraDemo.strings.importFailedBtnSmall ).append('<i class="dashicons dashicons-external"></i>');
		jQuery('.footer-import-button-wrap .view-site').text( astraDemo.strings.importFailedBtnLarge ).append('<i class="dashicons dashicons-external"></i>');
	});

});
