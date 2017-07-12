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

	wp_enqueue_script( 'astra-demo-import-admin' );
	?>

	<style type="text/css">
		.theme-browser .theme.focus .theme-actions,
		.theme-browser .theme:focus .theme-actions,
		.theme-browser .theme:hover .theme-actions {
			-ms-filter: "progid:DXImageTransform.Microsoft.Alpha(Opacity=100)";
			opacity: 1
		}
		.theme-browser .theme .theme-screenshot:after {
			content: "";
			display: block;
			padding-top: 66.66666%
		}

	</style>

	<div class="wrap">

		<div class="wp-filter hide-if-no-js">

			<ul class="filter-links">
				<li><a href="#" data-sort="all" class="current" data-id="all"><?php esc_html_e( 'All', 'astra' ); ?></a></li>
				<?php
				foreach ( Astra_Demo_Import::get_demo_categories() as $key => $category ) {
					?>

					<li><a href="#"
						   data-sort="<?php echo esc_attr( $category['slug'] ); ?>"
						   data-id="<?php echo esc_attr( $category['id'] ); ?>"
						><?php echo esc_attr( $category['name'] ); ?></a></li>

					<?php
				}
				?>
			</ul>

			<div class="search-form">
				<label class="screen-reader-text" for="wp-filter-search-input"><?php esc_html_e( 'Search Demos', 'astra' ); ?></label>
				<input placeholder="<?php esc_attr_e( 'Search Demos...', 'astra' ); ?>" type="search" aria-describedby="live-search-desc" id="wp-filter-search-input" class="wp-filter-search">
			</div>

		</div>

		<span class="spinner"></span>

		<div class="theme-browser rendered">
			<div class="themes wp-clearfix">
				<?php

				foreach ( $all_demos as $key => $demo ) {
					?>

					<div class="theme" tabindex="0" aria-describedby="astra-theme-action astra-theme-name">

						<div class="theme-screenshot">
							<img src="<?php echo esc_attr( $demo['featured_image_url'] ) ?>" alt="">
						</div>

						<a href="<?php echo esc_url( $demo['astra_demo_url'] ) ?>" target="_blank">
							<span class="more-details" id="astra-theme-action"><?php esc_html_e( 'Details &amp; Preview', 'astra' ); ?></span>
						</a>

						<h3 class="theme-name" id="astra-theme-name"><?php echo esc_attr( $demo['title'] ) ?></h3>
						<div class="theme-actions">
							<a class="button button-primary hide-if-no-customize astra-demo-import" href="#"
							   data-demo-id="<?php echo esc_attr( $demo['id'] ); ?>"
							   data-demo-url="<?php echo esc_url( $demo['astra_demo_url'] ); ?>"
							   data-demo-api="<?php echo esc_url( $demo['demo_api'] ); ?>"
							   data-screenshot="<?php echo esc_url( $demo['featured_image_url'] ); ?>"
							   data-demo-name="<?php echo esc_attr( $demo['title'] ); ?>"
							   data-content="<?php echo esc_attr( $demo['content'] ); ?>"
							   data-required-plugins="<?php echo esc_attr( $demo['required_plugins'] ); ?>"
							><?php esc_html_e( 'Import', 'astra' ); ?></a>
							<button class="button preview install-theme-preview"><?php esc_html_e( 'Preview', 'astra' ); ?></button>
						</div>
					</div>

					<?php
				}

				?>
			</div>
		</div>

	</div>

	<script type="text/template" id="tmpl-astra-demo-preview">
		<div class="theme-install-overlay wp-full-overlay expanded">
			<div class="wp-full-overlay-sidebar">
				<div class="wp-full-overlay-header">
					<button class="close-full-overlay"><span class="screen-reader-text"><?php esc_html_e( 'Close', 'astra' ); ?></span></button>
					<button class="previous-theme"><span class="screen-reader-text"><?php esc_html_e( 'Previous', 'astra' ); ?></span></button>
					<button class="next-theme"><span class="screen-reader-text"><?php esc_html_e( 'Next', 'astra' ); ?></span></button>

					<a class="button button-primary hide-if-no-customize astra-demo-import" href="#"
					   data-demo-id="{{{data.id}}}"
					   data-demo-url="{{{data.astra_demo_url}}}"
					   data-demo-api="{{{data.demo_api}}}"
					   data-demo-name="{{{data.demo_name}}}"
					><?php esc_html_e( 'Import Demo', 'astra' ); ?></a>

				</div>
				<div class="wp-full-overlay-sidebar-content">
					<div class="install-theme-info">
						<h3 class="theme-name">{{{data.demo_name}}}</h3>

						<img class="theme-screenshot" src="{{{data.screenshot}}}" alt="">

						<div class="theme-details">
							{{{data.content}}}
						</div>

						<div class="required-plugins">
						</div>
					</div>
				</div>
				<div class="wp-full-overlay-footer">
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

	<script type="text/template" id="tmpl-astra-single-demo">
		<div class="theme" tabindex="0" aria-describedby="astra-theme-action astra-theme-name">

			<div class="theme-screenshot">
				<img src="{{{data.screenshot}}}" alt="">
			</div>

			<a href="{{{data.astra_demo_url}}}" target="_blank">
				<span class="more-details" id="astra-theme-action"><?php esc_html_e( 'Details &amp; Preview', 'astra' ); ?></span>
			</a>

			<h3 class="theme-name" id="astra-theme-name">{{{data.demo_name}}}</h3>

			<div class="theme-actions">

				<a class="button button-primary hide-if-no-customize astra-demo-import" href="#"
				   data-demo-id="{{{data.id}}}"
				   data-demo-url="{{{data.astra_demo_url}}}"
				   data-demo-api="{{{data.demo_api}}}"
				   data-demo-name="{{{data.demo_name}}}"
				   data-screenshot="{{{data.screenshot}}}"
				   data-content="{{{data.content}}}"
				   data-required-plugins="{{data.required_plugins}}"
				><?php esc_html_e( 'Import', 'astra' ); ?></a>
				<button class="button preview install-theme-preview"><?php esc_html_e( 'Preview', 'astra' ); ?></button>

			</div>

		</div>
	</script>

	<?php

	// Load demo importer welcome.
} else {
	?>
	<p class="no-themes" style="display:block;"><?php esc_html_e( 'No Demos found, Open support ticket.', 'astra' ); ?></p>
	<?php
}// End if().


