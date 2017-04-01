<?php
wp_enqueue_script( 'astra-demo-import-admin' );
?>

<style type="text/css">

	.theme-browser .theme.focus .theme-actions, .theme-browser .theme:focus .theme-actions, .theme-browser .theme:hover .theme-actions {
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
		<!-- <div class="filter-count">
			<span class="count theme-count">15</span>
		</div> -->

		<ul class="filter-links">
			<li><a href="#" data-sort="all" class="current" data-id="all">All</a></li>
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

		<div class="search-form"><label class="screen-reader-text" for="wp-filter-search-input">Search Demos</label><input placeholder="Search Demos..." type="search" aria-describedby="live-search-desc" id="wp-filter-search-input" class="wp-filter-search"></div>

	</div>

	<span class="spinner"></span>

	<div class="theme-browser rendered">
		<div class="themes wp-clearfix">
		<?php

		foreach ( Astra_Demo_Import::get_astra_all_demos() as $key => $demo ) {
			?>

			<div class="theme" tabindex="0" aria-describedby="astra-theme-action astra-theme-name">

				<div class="theme-screenshot">
					<img src="<?php echo esc_attr( $demo['featured_image_url'] ) ?>" alt="">
				</div>

				<a href="<?php echo esc_url( $demo['astra_demo_url'] ) ?>" target="_blank"> 
					<span class="more-details" id="astra-theme-action">Details &amp; Preview</span>
				</a>

				<h2 class="theme-name" id="astra-theme-name"><?php echo esc_attr( $demo['title'] ) ?></h2>

				<div class="theme-actions">
					<a class="button button-primary hide-if-no-customize astra-demo-import" href="#"
					   data-demo-id="<?php echo esc_attr( $demo['id'] ); ?>"
					   data-demo-url="<?php echo esc_url( $demo['astra_demo_url'] ); ?>"
					   data-demo-api="<?php echo esc_url( $demo['demo_api'] ); ?>"
					   data-screenshot="<?php echo esc_url( $demo['featured_image_url'] ); ?>"
					   data-demo-name="<?php echo esc_attr( $demo['title'] ); ?>"
					   data-content="<?php echo esc_attr( $demo['content'] ); ?>"
					>Import</a>
					<button class="button preview install-theme-preview">Preview</button>
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
				<button class="close-full-overlay"><span class="screen-reader-text">Close</span></button>
				<button class="previous-theme"><span class="screen-reader-text">Previous</span></button>
				<button class="next-theme"><span class="screen-reader-text">Next</span></button>

				<a class="button button-primary hide-if-no-customize astra-demo-import" href="#"
				   data-demo-id="{{{data.id}}}"
				   data-demo-url="{{{data.astra_demo_url}}}"
				   data-demo-api="{{{data.demo_api}}}"
				   data-demo-name="{{{data.demo_name}}}"
				>Import Demo</a>

			</div>
			<div class="wp-full-overlay-sidebar-content">
				<div class="install-theme-info">
					<h3 class="theme-name">{{{data.demo_name}}}</h3>

					<img class="theme-screenshot" src="{{{data.screenshot}}}" alt="">

					<div class="theme-details">
					{{{data.content}}}
					</div>
				</div>
			</div>
			<div class="wp-full-overlay-footer">
				<button type="button" class="collapse-sidebar button" aria-expanded="true"
				        aria-label="Collapse Sidebar">
					<span class="collapse-sidebar-arrow"></span>
					<span class="collapse-sidebar-label">Collapse</span>
				</button>
			</div>
		</div>
		<div class="wp-full-overlay-main">
			<iframe src="{{{data.astra_demo_url}}}" title="Preview"></iframe>
		</div>
	</div>
</script>

<script type="text/template" id="tmpl-astra-single-demo">
	<div class="theme" tabindex="0" aria-describedby="astra-theme-action astra-theme-name">

		<div class="theme-screenshot">
			<img src="{{{data.screenshot}}}" alt="">
		</div>

		<a href="{{{data.astra_demo_url}}}" target="_blank"> 
			<span class="more-details" id="astra-theme-action">Details &amp; Preview</span>
		</a>

		<h2 class="theme-name" id="astra-theme-name">{{{data.demo_name}}}</h2>

		<div class="theme-actions">

			<a class="button button-primary hide-if-no-customize astra-demo-import" href="#"
			   data-demo-id="{{{data.id}}}"
			   data-demo-url="{{{data.astra_demo_url}}}"
			   data-demo-api="{{{data.demo_api}}}"
			   data-demo-name="{{{data.demo_name}}}"
			   data-screenshot="{{{data.screenshot}}}"
			   data-content="{{{data.content}}}"
			>Import</a>
			<button class="button preview install-theme-preview">Preview</button>

		</div>

	</div>
</script>
