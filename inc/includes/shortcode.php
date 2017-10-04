<?php
/**
 * Shortcode Markup
 *
 * @package Astra Sites Showcase
 * @since 1.0.0
 */

$row_class    = 'astra-showcase-row';
$column_class = 'astra-showcase-col-md-4';


?>
<!-- <div id="astra-sites-showcase">

	<div id="astra-sites-filters" class="wp-filter hide-if-no-js">

		<!All Filters
		<div class="filters-wrap">
			<div id="astra-site-page-builder"></div>
			<div id="astra-site-category"></div>
		</div>

	</div> 

	All Astra Sites
	<div id="astra-sites" class="astra-sites-grid astra-showcase <?php echo esc_attr( $row_class ); ?>"></div> #astra-sites 

	<span class="spinner is-active"></span>
	<span class="no-more-demos hide-me"> <p> <?php _e( 'No more sites!', 'astra-sites-showcase' ); ?> </p></span>

</div> #astra-sites -->

<script type="text/template" id="tmpl-astra-showcase-count">
	<!-- <div class="filter-count">
		<span class="count"></span>
	</div> -->
</script>

<script type="text/template" id="tmpl-astra-showcase-search">
	<!-- <div class="search-form">
		<label class="screen-reader-text" for="wp-filter-search-input"><?php _e( 'Search Sites', 'astra-sites-showcase' ); ?> </label>
		<input placeholder="<?php _e( 'Search Sites...', 'astra-sites-showcase' ); ?>" type="search" aria-describedby="live-search-desc" id="wp-filter-search-input" class="wp-filter-search">
	</div> -->
</script>

<script type="text/template" id="tmpl-astra-showcase-responsive-view">
	<span class="responsive-view">
		<span class="actions">
			<a class="desktop" href="#"><span data-view="desktop " class="active dashicons dashicons-desktop"></span></a>
			<a class="tablet" href="#"><span data-view="tablet" class="dashicons dashicons-tablet"></span></a>
			<a class="mobile" href="#"><span data-view="mobile" class="dashicons dashicons-smartphone"></span></a>
		</span>
	</span>
</script>
<script type="text/template" id="tmpl-astra-showcase-filters">

	<# if ( data ) { #>

		<ul class="{{ data.args.wrapper_class }} {{ data.args.class }}">
			
			<# if ( data.args.show_all ) { #>
				<li>
					<a href="#" data-group="all"> All </a>
				</li>
			<# } #>

			<# for ( key in data.items ) { #>
				<li>
					<a href="#" data-group='{{ data.items[ key ].id }}' class="{{ data.items[ key ].name }}">
						 {{ data.items[ key ].name }}
					</a>
				</li>
			<# } #>

		</ul>
	<# } #>

</script>

<script type="text/template" id="tmpl-astra-showcase-list">

	<# if ( data.items.length ) { #>
		<# for ( key in data.items ) { #>

			<div class="theme astra-theme site-single {{ data.items[ key ].status }}" tabindex="0" aria-describedby="astra-theme-action astra-theme-name"
				data-demo-id="{{{ data.items[ key ].id }}}"
				data-demo-type="{{{ data.items[ key ]['astra-site-type'] }}}"
				data-demo-url="{{{ data.items[ key ]['astra-site-url'] }}}"
				data-demo-api="{{{ data.items[ key ]['_links']['self'][0]['href'] }}}"
				data-demo-name="{{{  data.items[ key ].title.rendered }}}"
				data-demo-slug="{{{  data.items[ key ].slug }}}"
				data-screenshot="{{{ data.items[ key ]['featured-image-url'] }}}"
				data-content="{{{ data.items[ key ].content.rendered }}}"
				data-required-plugins="{{ JSON.stringify( data.items[ key ]['required-plugins'] ) }}"
				data-groups=["{{ data.items[ key ].tags }}"]>
				<input type="hidden" class="astra-site-options" value="{{ JSON.stringify(data.items[ key ]['astra-site-options-data'] ) }}" />
				<input type="hidden" class="astra-enabled-extensions" value="{{ JSON.stringify(data.items[ key ]['astra-enabled-extensions'] ) }}" />

			<!-- <div class="theme astra-theme site-single" data-groups=["{{ data.items[ key ].tags }}"]> -->
				<div class="inner">
				<span class="site-preview" data-href="{{ data.items[ key ]['astra-site-url'] }}?TB_iframe=true&width=600&height=550" data-title="{{ data.items[ key ].title.rendered }}">		
				
				<div class="theme-screenshot">
				<# if( '' !== data.items[ key ]['featured-image-url'] ) { #>
					<img class="lazy" data-src="{{ data.items[ key ]['featured-image-url'] }}" />
						<noscript>
							<img src="{{ data.items[ key ]['featured-image-url'] }}" />
						</noscript>
				<# } #>
				</div>

				<a href="{{{data.astra_demo_url}}}" target="_blank" class="view-demo-wrap">
					<span class="more-details view-demo" id="astra-theme-action"><?php esc_html_e( 'Details &amp; Preview', 'astra-sites' ); ?></span>
				</a>

				</span>
				<h3 class="theme-name" id="astra-theme-name">
					{{{ data.items[ key ].title.rendered }}}
					<# if ( data.items[ key ]['astra-site-type'] ) { #>
						<span class="site-type {{data.items[ key ]['astra-site-type']}}">{{data.items[ key ]['astra-site-type']}}</span>
					<# } #>
				</h3>
				<div class="theme-actions">
					<button class="button preview install-theme-preview"><?php esc_html_e( 'Preview', 'astra-sites' ); ?></button>
				</div>
				</div>
			</div>
		<# } #>
	<# } else { #>
		<div class="astra-showcase-not-found"> <p class="no-themes" style="display:block;"> <?php _e( 'No Demos found, Try a different search.', 'astra-sites-showcase' ); ?> </p> </div>
	<# } #>
</script>
