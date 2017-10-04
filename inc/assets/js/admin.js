

(function($){

	AstraSites = {

		_ref: null,
		
		_iconUploader: null,
	
		init: function()
		{
			this._bind();
			this._resetPagedCount();
			this._initial_load_demos();
		},
		
		/**
		 * Binds events for the Astra Sites.
		 *
		 * @since 1.0.1
		 * @access private
		 * @method _bind
		 */
		_bind: function()
		{
			$( document ).on('scroll', AstraSites._scroll);
			
			
			
			$( document ).on('click', '.filter-links li a', AstraSites._filter);
			
			$( document ).on('keyup input', '#wp-filter-search-input', AstraSites._serach);
			
		},

		/**
		 * Previous Theme.
		 */
		_previousTheme: function (event) {
			event.preventDefault();

			currentDemo = jQuery('.theme-preview-on');
			currentDemo.removeClass('theme-preview-on');
			prevDemo = currentDemo.prev('.theme');
			prevDemo.addClass('theme-preview-on');

			AstraSites._renderDemoPreview(prevDemo);
		},

		/**
		 * Next Theme.
		 */
		_nextTheme: function (event) {
			event.preventDefault();
			currentDemo = jQuery('.theme-preview-on')
			currentDemo.removeClass('theme-preview-on');
			nextDemo = currentDemo.next('.theme');
			nextDemo.addClass('theme-preview-on');

			AstraSites._renderDemoPreview( nextDemo );
		},

		

		
		
		_renderDemoPreview: function(anchor) {

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

			// delete any earlier fullscreen preview before we render new one.
			jQuery('.theme-install-overlay').remove();

			jQuery('#astra-sites-menu-page').append(template(templateData[0]));
			jQuery('.theme-install-overlay').css('display', 'block');
			AstraSites._checkNextPrevButtons();

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


					console.log('response: ' + response);

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
					astraDemo.requiredPlugins = response;
					AstraSites._enable_demo_import_button();

				} );

			} else {

				// Enable Demo Import Button
				AstraSites._enable_demo_import_button( demoType );
				jQuery('.required-plugins-wrap').remove();
			}

			return;
		},

		/**
		 * Check Next Previous Buttons.
		 */
		_checkNextPrevButtons: function() {
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
		},

		/**
		 * Filter Demo by Category.
		 */
		_filter: function(event) {
			event.preventDefault();

			$this = jQuery(this);
			$this.parent('li').siblings().find('.current').removeClass('current');
			$this.addClass('current');
			
			var astra_page_builder = jQuery('.filter-links.astra-page-builder'),
				astra_category 	   = jQuery('.filter-links.astra-category'),
				page_builder_id   	= astra_page_builder.find('.current').data('id'),
				category_id   		= astra_category.find('.current').data('id');

			AstraSites._resetPagedCount();
			
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
					AstraSites._renderDemoGrid(demos.sites);
				} else {
					jQuery('.spinner').after('<p class="no-themes" style="display:block;">'+astraDemo.strings.searchNoFound+'</p>');
				}

			})
			.fail(function () {
				jQuery('body').removeClass('loading-content');
				jQuery('.spinner').after('<p class="no-themes" style="display:block;">'+astraDemo.strings.responseError+'</p>');
			});

		},


		/**
		 * Search Site.
		 */
		_serach: function() {
			$this = jQuery('#wp-filter-search-input').val();

			id = '';
			if ($this.length < 2) {
				id = 'all';
			}

			var astra_page_builder = jQuery('.filter-links.astra-page-builder'),
				astra_category 	   = jQuery('.filter-links.astra-category'),
				page_builder_id   	= astra_page_builder.find('.current').data('id'),
				category_id   		= astra_category.find('.current').data('id');
			

			window.clearTimeout(AstraSites._ref);
			AstraSites._ref = window.setTimeout(function () {
				AstraSites._ref = null;

				AstraSites._resetPagedCount();
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

					jQuery('.filter-count .count').text( demos.sites_count );

					if ( demos.sites_count > 0 ) {
						AstraSites._renderDemoGrid(demos.sites);
					} else {
						jQuery('.spinner').after('<p class="no-themes" style="display:block;">'+astraDemo.strings.searchNoFound+'</p>');
					}

				})
				.fail(function () {
					jQuery('body').removeClass('loading-content');
					jQuery('.spinner').after('<p class="no-themes" style="display:block;">'+astraDemo.strings.responseError+'.</p>');
				});


			}, 500);

		},

		

		/**
		 * Individual Site Preview
		 *
		 * On click on image, more link & preview button.
		 */
		_preview: function( event ) {

			event.preventDefault();

			$this = jQuery(this).parents('.theme');
			$this.addClass('theme-preview-on');

			jQuery('html').addClass('astra-site-preview-on');

			AstraSites._renderDemoPreview($this);
		},

		_scroll: function(event) {

			var scrollDistance = jQuery(window).scrollTop();

			var themesBottom = Math.abs(jQuery(window).height() - jQuery('.themes').offset().top - jQuery('.themes').height());
			themesBottom = themesBottom * 20 / 100;

			ajaxLoading = jQuery('body').data('scrolling');

			if (scrollDistance > themesBottom && ajaxLoading == false) {
				AstraSites._updatedPagedCount();

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
						AstraSites._renderDemoGrid(demos.sites);
					}
				})
				.fail(function () {
					jQuery('body').removeClass('loading-content');
					jQuery('.spinner').after('<p class="no-themes" style="display:block;">'+astraDemo.strings.responseError+'</p>');
				});

			}
		},

		
		
		/**
		 * Update Page Count.
		 */
		_updatedPagedCount: function() {
			paged = parseInt(jQuery('body').attr('data-astra-demo-paged'));
			jQuery('body').attr('data-astra-demo-paged', paged + 1);
			window.setTimeout(function () {
				jQuery('body').data('scrolling', false);
			}, 800);
		},

		_resetPagedCount: function() {

			categoryId = jQuery('.astra-category.filter-links li .current').data('id');
			jQuery('body').attr('data-astra-demo-paged', '1');
			jQuery('body').attr('data-astra-site-category', categoryId);
			jQuery('body').attr('data-astra-demo-search', '');
			jQuery('body').attr('data-scrolling', false);
			jQuery('body').attr( 'data-required-plugins', 0 )

		},

		_initial_load_demos: function() {

			var astra_page_builder = jQuery('.filter-links.astra-page-builder'),
				astra_category 	   = jQuery('.filter-links.astra-category'),
				page_builder_id   	= astra_page_builder.find('.current').data('id'),
				category_id   		= astra_category.find('.current').data('id');

			jQuery('body').addClass('loading-content');

			jQuery.ajax({
				url: astraDemo.ajaxurl,
				type: 'POST',
				dataType: 'json',
				data: {
					action          : 'astra-list-sites',
					paged           : '1',
					page_builder_id : page_builder_id,
					category_id     : category_id,
				},
			})
			.done(function (demos) {

				jQuery('body').removeClass('loading-content');
				jQuery('.filter-count .count').text( demos.sites_count );

				// Has sites?
				if ( demos.sites_count > 0 ) {
					AstraSites._renderDemoGrid( demos.sites );

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
		},

		/**
		 * Render Demo Grid.
		 */
		_renderDemoGrid: function(demos) {

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

		},

		

		

		

		


		

		/**
		 * Enable Demo Import Button.
		 */
		_enable_demo_import_button: function( type = 'free' ) {

			switch( type ) {

				case 'free':
							var all_buttons      = parseInt( jQuery( '.plugin-card .button' ).length ) || 0,
								disabled_buttons = parseInt( jQuery( '.plugin-card .button.disabled' ).length ) || 0;

							if( all_buttons === disabled_buttons ) {

								jQuery('.astra-demo-import')
									.removeAttr('data-import')
									.removeClass('updating-message')
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

		},

		

	};

	/**
	 * Initialize AstraSites
	 */
	$(function(){
		AstraSites.init();
	});

})(jQuery);