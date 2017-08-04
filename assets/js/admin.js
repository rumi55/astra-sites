jQuery(document).ready(function ($) {
	resetPagedCount();
});

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

	var not_installed = astraDemo.requiredPlugins.notinstalled || '';

	// Bulk Install with wp.updates.queue.
	if( 'undefined' !== not_installed && not_installed.length ) {

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

	// Bulk activate.
	activateAllPlugins();
}

/**
 * Plugin Installing.
 */
jQuery(document).on('wp-plugin-installing', function (event, args) {
	event.preventDefault();

	var $card = jQuery( '.plugin-card-' + args.slug );

	// Remove icon.
	$card.find('.dashicons').remove();

	// Add spinner.
	$card.append('<span class="spinner is-active"></span>');

});

/**
 * Plugin Install Success.
 */
jQuery(document).on( 'wp-plugin-install-success', function( event, args ) {
	event.preventDefault();

	// Transform the 'Install' button into an 'Activate' button.
	var $card = jQuery( '.plugin-card-' + args.slug ),
		$init = $card.data('init'),
		$siteOptions = jQuery( '.wp-full-overlay-header').find('.astra-site-options').val();

	var pluginsList = astraDemo.requiredPlugins.notinstalled;

	// Reset not installed plugins list.
	astraDemo.requiredPlugins.notinstalled = removePluginFromQueue( args.slug, pluginsList );

	// WordPress adds "Activate" button after waiting for 1000ms.
	// So we will run our activation after that.
	setTimeout( function() {

		jQuery.ajax({
			url: astraDemo.ajaxurl,
			type: 'POST',
			dataType: 'json',
			data: {
				'action'	: 'astra-required-plugin-activate',
				'init'		: $init,
				'options'	: $siteOptions,
			},
		})
		.done(function (result) {

			if( result.success ) {

				$card.find('.spinner').remove();
				$card.append('<span class="dashicons-yes dashicons"></span>');

				var pluginsList = astraDemo.requiredPlugins.inactive;
				astraDemo.requiredPlugins.inactive = removePluginFromQueue( args.slug, pluginsList );

				// Enable Demo Import Button
				astraDemo.requiredPluginsCount--;
				enable_demo_import_button();

				activateAllPlugins();
			}
		});

	}, 1000 );

});

/**
 * Activate All Plugins.
 */
function activateAllPlugins() {

	// Get all plugins.
	var remainingPlugins = astraDemo.requiredPlugins.inactive;

	if( remainingPlugins.length ) {

		/**
		 * Process of cloud templates - (download, remove & fetch)
		 */
		AstraSitesAjaxQueue.run();

		jQuery.each( remainingPlugins, function(index, single_plugin) {

			var $card    	 = jQuery( '.plugin-card-' + single_plugin.slug ),
				$siteOptions = jQuery( '.wp-full-overlay-header').find('.astra-site-options').val();

			$card.append('<span class="spinner is-active"></span>');
			$card.find('.dashicons').remove();

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

						$card.append('<span class="dashicons-yes dashicons"></span>');
						$card.find('.spinner').remove();

						var pluginsList = astraDemo.requiredPlugins.inactive;

						// Reset Plugin Queue.
						astraDemo.requiredPlugins.inactive = removePluginFromQueue( single_plugin.slug, pluginsList );

						// Enable Demo Import Button
						astraDemo.requiredPluginsCount--;
						enable_demo_import_button();
					}
				}
			});
		});
	}
}

/**
 * Enable Demo Import Button.
 */
function enable_demo_import_button( type = 'free' ) {

	if( 'free' === type ) {

		// Get initial required plugins count.
		var remaining = parseInt( astraDemo.requiredPluginsCount ) || 0;

		// Enable demo import button.
		if( 0 >= remaining ) {

			jQuery('.astra-demo-import')
				.removeAttr('data-import')
				.addClass('button-primary')
				.text( astraDemo.strings.importDemo );
		}
	} else {

		var demo_slug = jQuery('.wp-full-overlay-header').attr('data-demo-slug');

		jQuery('.astra-demo-import')
				.addClass('go-pro button-primary')
				.removeClass('astra-demo-import')
				.attr('target', '_blank')
				.attr('href', astraDemo.getProURL + demo_slug )
				.text( astraDemo.getProText )
				.append('<i class="dashicons dashicons-external"></i>');
	}
}

/**
 * Reset Page Count.
 */
function resetPagedCount() {
	categoryId = jQuery('.filter-links li .current').data('id');
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

/**
 * Close Full Overlay.
 */
jQuery(document).on('click', '.close-full-overlay', function (event) {
	event.preventDefault();

	jQuery('.theme-install-overlay').css('display', 'none');
	jQuery('.theme-install-overlay').remove();
	jQuery('.theme-preview-on').removeClass('theme-preview-on');
});

/**
 * Next Theme.
 */
jQuery(document).on('click', '.next-theme', function (event) {
	event.preventDefault();
	currentDemo = jQuery('.theme-preview-on')
	currentDemo.removeClass('theme-preview-on');
	nextDemo = currentDemo.nextAll('.theme');
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
	prevDemo = currentDemo.prevAll('.theme');
	prevDemo.addClass('theme-preview-on');

	renderDemoPreview(prevDemo);
});

/**
 * Plugin Installation Error.
 */
jQuery(document).on( 'wp-plugin-install-error', function( event, response ) {

	var $card = jQuery( '.plugin-card-' + response.slug );

	$card.find('.spinner').remove();
	$card.find('.title').after('<span class="dashicons-no dashicons"></span>');

});

/**
 * Render Demo Preview
 */
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

	var template = wp.template('astra-demo-preview');

	templateData = [{
		id                      : demoId,
		astra_demo_type         : demoType,
		astra_demo_url          : demoURL,
		demo_api                : apiURL,
		screenshot              : screenshot,
		demo_name               : demo_name,
		slug               		: demo_slug,
		content                 : content,
		required_plugins        : JSON.stringify(requiredPlugins),
		astra_site_options      : astraSiteOptions,
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

	if( 'free' === demoType && descHeight >= 55 ) {

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

	if( 'free' === demoType ) {

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
						output += '	<span class="dashicons-no dashicons"></span>';
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
						output += '	<span class="dashicons-no dashicons"></span>';
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
						output += '	<span class="dashicons-yes dashicons"></span>';
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
	slug  = $this.data('sort');
	id    = $this.data('id');

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
		jQuery('.spinner').after('<p class="no-themes" style="display:block;">There was a problem receiving a response from server.</p>');
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
		astraSiteOptions = demo.astra_site_options || '';

		templateData = [{
			id: id,
			astra_demo_type: astra_demo_type,
			astra_demo_url: astra_demo_url,
			demo_api: demo_api,
			screenshot: screenshot,
			demo_name: demo_name,
			slug: demo_slug,
			content: content,
			required_plugins: requiredPlugins,
			astra_site_options: astraSiteOptions
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

		jQuery('.astra-demo-import').removeClass('updating-message installing')
			.removeAttr('data-import')
			.addClass('view-site')
			.removeClass('astra-demo-import')
			.text( astraDemo.strings.viewSite )
			.attr('target', '_blank')
			.append('<i class="dashicons dashicons-external"></i>')
			.attr('href', astraDemo.siteURL );
	})
	.fail(function ( demos ) {
		jQuery('.astra-demo-import').removeClass('updating-message installing').text('Error.');
	});

});
