<?php
/**
 * Astra Demo View.
 *
 * @package Astra Addon
 */

defined( 'ABSPATH' ) or exit;

// Load demo importer markup.
$all_demos = Astra_Demo_Import::get_astra_all_demos();

if ( count( $all_demos ) > 0 ) {

	// Enqueue scripts.
	wp_enqueue_script( 'astra-demo-import-admin' );
	wp_enqueue_style( 'astra-demo-import-admin' );

	/**
	 * Initial Demo List
	 *
	 * Generated though PHP
	 */
	?>
	<div class="wrap">

		<div class="wp-filter hide-if-no-js">

			<ul class="filter-links">
				
				<li><a href="#" data-sort="all" class="current" data-id="all"><?php esc_html_e( 'All', 'astra' ); ?></a></li>
				
				<?php foreach ( Astra_Demo_Import::get_demo_categories() as $key => $category ) { ?>
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
				<label class="screen-reader-text" for="wp-filter-search-input"><?php esc_html_e( 'Search Demos', 'astra' ); ?></label>
				<input placeholder="<?php esc_attr_e( 'Search Demos...', 'astra' ); ?>" type="search" aria-describedby="live-search-desc" id="wp-filter-search-input" class="wp-filter-search">
			</div>

		</div>

		<span class="spinner"></span>

		<div class="theme-browser rendered">
			<div class="themes wp-clearfix">
				
				<?php foreach ( $all_demos as $key => $demo ) { ?>

					<div class="theme astra-theme" tabindex="0" aria-describedby="astra-theme-action astra-theme-name"
						data-demo-id="<?php echo esc_attr( $demo['id'] ); ?>"
						data-demo-type="<?php echo esc_attr( $demo['astra_demo_type'] ); ?>"
						data-demo-url="<?php echo esc_url( $demo['astra_demo_url'] ); ?>"
						data-demo-api="<?php echo esc_url( $demo['demo_api'] ); ?>"
						data-screenshot="<?php echo esc_url( $demo['featured_image_url'] ); ?>"
						data-demo-name="<?php echo esc_attr( $demo['title'] ); ?>"
						data-demo-slug="<?php echo esc_attr( $demo['slug'] ); ?>"
						data-content="<?php echo esc_attr( $demo['content'] ); ?>"
						data-required-plugins="<?php echo esc_attr( $demo['required_plugins'] ); ?>">

						<?php if( 'premium' === $demo['astra_demo_type'] ) { ?>
							<span class="demo-type <?php echo esc_attr( $demo['astra_demo_type'] ) ?>"><?php echo esc_attr( $demo['astra_demo_type'] ) ?></span>
						<?php } ?>
					
						<div class="theme-screenshot">
							<?php if( ! empty( $demo['featured_image_url'] ) ) { ?>
								<img src="<?php echo esc_attr( $demo['featured_image_url'] ) ?>" alt="">
							<?php } ?>
						</div>

						<a href="<?php echo esc_url( $demo['astra_demo_url'] ) ?>" target="_blank">
							<span class="more-details" id="astra-theme-action"><?php esc_html_e( 'Details &amp; Preview', 'astra' ); ?></span>
						</a>

						<h3 class="theme-name" id="astra-theme-name"><?php echo esc_attr( $demo['title'] ) ?></h3>
						<div class="theme-actions">
							<button class="button preview install-theme-preview"><?php esc_html_e( 'Preview', 'astra' ); ?></button>
						</div>
					</div>

				<?php } ?>

			</div>
		</div>

	</div>
	
	<?php
	/**
	 * Regenerated Demo List
	 *
	 * Generated though JS after search demo, filter demo etc.
	 */
	?>
	<script type="text/template" id="tmpl-astra-single-demo">
		<div class="theme astra-theme" tabindex="0" aria-describedby="astra-theme-action astra-theme-name"
			data-demo-id="{{{data.id}}}"
			data-demo-type="{{{data.astra_demo_type}}}"
			data-demo-url="{{{data.astra_demo_url}}}"
			data-demo-api="{{{data.demo_api}}}"
			data-demo-name="{{{data.demo_name}}}"
			data-demo-slug="{{{data.slug}}}"
			data-screenshot="{{{data.screenshot}}}"
			data-content="{{{data.content}}}"
			data-required-plugins="{{data.required_plugins}}">
	
			<span class="demo-type {{{data.astra_demo_type}}}">{{{data.astra_demo_type}}}</span>

			<div class="theme-screenshot">
				<# if ( data.screenshot.length ) { #>
					<img src="{{{data.screenshot}}}" alt="">
				<# } #>
			</div>

			<a href="{{{data.astra_demo_url}}}" target="_blank">
				<span class="more-details" id="astra-theme-action"><?php esc_html_e( 'Details &amp; Preview', 'astra' ); ?></span>
			</a>

			<h3 class="theme-name" id="astra-theme-name">{{{data.demo_name}}}</h3>

			<div class="theme-actions">
				<button class="button preview install-theme-preview"><?php esc_html_e( 'Preview', 'astra' ); ?></button>

			</div>

		</div>
	</script>

	<?php
	/**
	 * Single Demo Preview
	 */
	?>
	<script type="text/template" id="tmpl-astra-demo-preview">
		<div class="astra-demo-import-preview theme-install-overlay wp-full-overlay expanded">
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
					<button class="close-full-overlay"><span class="screen-reader-text"><?php esc_html_e( 'Close', 'astra' ); ?></span></button>
					<button class="previous-theme"><span class="screen-reader-text"><?php esc_html_e( 'Previous', 'astra' ); ?></span></button>
					<button class="next-theme"><span class="screen-reader-text"><?php esc_html_e( 'Next', 'astra' ); ?></span></button>
					<a class="button hide-if-no-customize astra-demo-import" href="#" data-import="disabled"><?php esc_html_e( 'Install Plugins', 'astra' ); ?></a>

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
						<a href="#" class="theme-details-read-more"><?php _e( 'Read more', 'astra-demo-import' ); ?> &hellip;</a>

						<div class="required-plugins-wrap">
							<h3><?php _e( 'Required Plugin', 'astra-demo-import' ); ?> </h3>
							<div class="required-plugins"></div>
						</div>
					</div>
				</div>

				<div class="wp-full-overlay-footer">
					<a class="button button-hero hide-if-no-customize astra-demo-import" href="#" data-import="disabled">
						<?php esc_html_e( 'Install Plugins', 'astra' ); ?>							
					</a>
					<button type="button" class="collapse-sidebar button" aria-expanded="true"
							aria-label="Collapse Sidebar">
						<span class="collapse-sidebar-arrow"></span>
						<span class="collapse-sidebar-label"><?php esc_html_e( 'Collapse', 'astra' ); ?></span>
					</button>
				</div>
			</div>
			<div class="wp-full-overlay-main">
				<iframe src="{{{data.astra_demo_url}}}" title="<?php esc_attr_e( 'Preview', 'astra' ); ?>"></iframe>
			</div>
		</div>
	</script>

	<?php

	// Load demo importer welcome.
} else {
	?>
	<p class="no-themes" style="display:block;">
		<?php printf( __( 'No Demos found, Open <a href="%s" target="_blank">support ticket</a>.', 'astra-demo-import' ) , esc_url( 'https://wpastra.com/support/' ) ); ?>
	</p>
	<?php
}// End if().


