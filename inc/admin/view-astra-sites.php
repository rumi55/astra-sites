<?php
/**
 * Astra Demo View.
 *
 * @package Astra Addon
 */

defined( 'ABSPATH' ) or exit;

// Enqueue scripts.
wp_enqueue_script( 'astra-sites-admin' );
wp_enqueue_style( 'astra-sites-admin' );

	/**
	 * Initial Demo List
	 *
	 * Generated though PHP
	 */
	?>
	<div class="wrap">


		<div class="wp-filter hide-if-no-js">

			<div class="left-section">
				
				<div class="filter-count">
					<span class="count"></span>
				</div>

				<ul class="filter-links astra-page-builder">
					<?php foreach ( Astra_Sites::get_page_builders() as $key => $category ) { ?>
						<li>
							<a href="#"
							   data-sort="<?php echo esc_attr( $category['slug'] ); ?>"
							   data-id="<?php echo esc_attr( $category['id'] ); ?>">
								<?php echo esc_attr( $category['name'] ); ?>
							</a>
						</li>
					<?php } ?>
				</ul>
			</div>

			<div class="right-section">
				<ul class="filter-links astra-category">

					<li><a href="#" data-sort="all" class="current" data-id="all"><?php esc_html_e( 'All', 'astra-sites' ); ?></a></li>

					<?php foreach ( Astra_Sites::get_demo_categories() as $key => $category ) { ?>
						<li>
							<a href="#"
							   data-sort="<?php echo esc_attr( $category['slug'] ); ?>"
							   data-id="<?php echo esc_attr( $category['id'] ); ?>">
								<?php echo esc_attr( $category['name'] ); ?>
							</a>
						</li>
					<?php } ?>
				</ul>

				<div class="search-form">
					<label class="screen-reader-text" for="wp-filter-search-input"><?php esc_html_e( 'Search Sites', 'astra-sites' ); ?></label>
					<input placeholder="<?php esc_attr_e( 'Search Sites...', 'astra-sites' ); ?>" type="search" aria-describedby="live-search-desc" id="wp-filter-search-input" class="wp-filter-search">
				</div>
			 </div>

		</div>

		<?php do_action( 'astra_sites_before_site_grid' ); ?>

		<span class="spinner"></span>

		<div class="theme-browser rendered">
			<div class="themes wp-clearfix"></div>
		</div>

		<?php do_action( 'astra_sites_after_site_grid' ); ?>

	</div>

	<?php
	/**
	 * Regenerated Demo List
	 *
	 * Generated though JS after search demo, filter demo etc.
	 */
	?>
	<script type="text/template" id="tmpl-astra-single-demo">
		<div class="theme astra-theme {{data.status}}" tabindex="0" aria-describedby="astra-theme-action astra-theme-name"
			data-demo-id="{{{data.id}}}"
			data-demo-type="{{{data.astra_demo_type}}}"
			data-demo-url="{{{data.astra_demo_url}}}"
			data-demo-api="{{{data.demo_api}}}"
			data-demo-name="{{{data.demo_name}}}"
			data-demo-slug="{{{data.slug}}}"
			data-screenshot="{{{data.screenshot}}}"
			data-content="{{{data.content}}}"
			data-required-plugins="{{data.required_plugins}}">
			<input type="hidden" class="astra-site-options" value="{{data.astra_site_options}}" >
			<input type="hidden" class="astra-enabled-extensions" value="{{data.astra_enabled_extensions}}" >

			<span class="status {{data.status}}">{{data.status}}</span>

			<span class="demo-type {{{data.astra_demo_type}}}">{{{data.astra_demo_type}}}</span>

			<div class="theme-screenshot">
				<# if ( data.screenshot.length ) { #>
					<img src="{{{data.screenshot}}}" alt="">
				<# } #>
			</div>

			<a href="{{{data.astra_demo_url}}}" target="_blank">
				<span class="more-details" id="astra-theme-action"><?php esc_html_e( 'Details &amp; Preview', 'astra-sites' ); ?></span>
			</a>

			<h3 class="theme-name" id="astra-theme-name">{{{data.demo_name}}}</h3>

			<div class="theme-actions">
				<button class="button preview install-theme-preview"><?php esc_html_e( 'Preview', 'astra-sites' ); ?></button>

			</div>

		</div>
	</script>

	<?php
	/**
	 * Single Demo Preview
	 */
	?>
	<script type="text/template" id="tmpl-astra-demo-preview">
		<div class="astra-sites-preview theme-install-overlay wp-full-overlay expanded">
			<div class="wp-full-overlay-sidebar">
				<div class="wp-full-overlay-header"
						data-demo-id="{{{data.id}}}"
						data-demo-type="{{{data.astra_demo_type}}}"
						data-demo-url="{{{data.astra_demo_url}}}"
						data-demo-api="{{{data.demo_api}}}"
						data-demo-name="{{{data.demo_name}}}"
						data-demo-slug="{{{data.slug}}}"
						data-screenshot="{{{data.screenshot}}}"
						data-content="{{{data.content}}}"
						data-required-plugins="{{data.required_plugins}}">
					<input type="hidden" class="astra-site-options" value="{{data.astra_site_options}}" >
					<input type="hidden" class="astra-enabled-extensions" value="{{data.astra_enabled_extensions}}" >
					<button class="close-full-overlay"><span class="screen-reader-text"><?php esc_html_e( 'Close', 'astra-sites' ); ?></span></button>
					<button class="previous-theme"><span class="screen-reader-text"><?php esc_html_e( 'Previous', 'astra-sites' ); ?></span></button>
					<button class="next-theme"><span class="screen-reader-text"><?php esc_html_e( 'Next', 'astra-sites' ); ?></span></button>
					<a class="button hide-if-no-customize astra-demo-import" href="#" data-import="disabled"><?php esc_html_e( 'Install Plugins', 'astra-sites' ); ?></a>

				</div>
				<div class="wp-full-overlay-sidebar-content">
					<div class="install-theme-info">

						<span class="demo-type {{{data.astra_demo_type}}}">{{{data.astra_demo_type}}}</span>
						<h3 class="theme-name">{{{data.demo_name}}}</h3>

						<# if ( data.screenshot.length ) { #>
							<img class="theme-screenshot" src="{{{data.screenshot}}}" alt="">
						<# } #>

						<div class="theme-details">
							{{{data.content}}}
						</div>
						<a href="#" class="theme-details-read-more"><?php _e( 'Read more', 'astra-sites' ); ?> &hellip;</a>

						<div class="required-plugins-wrap">
							<h4><?php _e( 'Required Plugins', 'astra-sites' ); ?> </h4>
							<div class="required-plugins"></div>
						</div>
					</div>
				</div>

				<div class="wp-full-overlay-footer">
					<div class="footer-import-button-wrap">
						<a class="button button-hero hide-if-no-customize astra-demo-import" href="#" data-import="disabled">
							<?php esc_html_e( 'Install Plugins', 'astra-sites' ); ?>
						</a>
					</div>
					<button type="button" class="collapse-sidebar button" aria-expanded="true"
							aria-label="Collapse Sidebar">
						<span class="collapse-sidebar-arrow"></span>
						<span class="collapse-sidebar-label"><?php esc_html_e( 'Collapse', 'astra-sites' ); ?></span>
					</button>
				</div>
			</div>
			<div class="wp-full-overlay-main">
				<iframe src="{{{data.astra_demo_url}}}" title="<?php esc_attr_e( 'Preview', 'astra-sites' ); ?>"></iframe>
			</div>
		</div>
	</script>

	<script type="text/template" id="tmpl-astra-no-demos">
		<div class="no-themes">
			<?php

			/* translators: %1$s & %2$s are a Demo API URL */
			printf( __( '<p> It seems the demo data server, <i><a href="%1$s">%2$s</a></i> is unreachable from your site.</p>', 'astra-sites' ) , esc_url( Astra_Sites::$api_url ), esc_url( Astra_Sites::$api_url ) );

			_e( '<p class="left-margin"> 1. Sometimes, simple page reload fixes any temporary issues. No kidding!</p>', 'astra-sites' );

			_e( '<p class="left-margin"> 2. If that does not work, you will need to talk to your server administrator and check if demo server is being blocked by the firewall!</p>', 'astra-sites' );

			/* translators: %1$s is a support link */
			printf( __( '<p>If that does not help, please open up a <a href="%1$s" target="_blank">Support Ticket</a> and we will be glad take a closer look for you.</p>', 'astra-sites' ), esc_url( 'https://wpastra.com/support/' ) );
			?>
		</div>
	</script>
