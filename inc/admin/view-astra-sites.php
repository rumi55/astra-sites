<?php

function debugme() {

	// http://sites-wpastra.sharkz.in/wp-content/uploads/astra-sites/car-repair/wxr.xml
	
	$data = array(
	    'home' => 'http://wptest.io/demo',
	    'siteurl' => 'http://wptest.io/demo',
	    'title' => 'WP Test Demo',
	    'users' => array(
	        array(
	            'data' => array(
	                'ID' => '1',
	                'user_login' => 'manovotny',
	                'user_email' => 'manovotny@gmail.com',
	                'display_name' => 'Michael Novotny',
	                'first_name' => 'Michael',
	                'last_name' => 'Novotny',
	            ),
	            'meta' => array()
	        ),
	        array(
	            'data' => array(
	                'ID' => '2',
	                'user_login' => 'dewde',
	                'user_email' => 'yo@chrisam.es',
	                'display_name' => 'Chris Ames',
	                'first_name' => 'Chris',
	                'last_name' => 'Ames',
	            ),
	            'meta' => array()
	        ),
	        array(
	            'data' => array(
	                'ID' => '3',
	                'user_login' => 'tommcfarlin',
	                'user_email' => 'tom@tommcfarlin.com',
	                'display_name' => 'Tom McFarlin',
	                'first_name' => 'Tom',
	                'last_name' => 'McFarlin',
	            ),
	            'meta' => array()
	        ),
	        array(
	            'data' => array(
	                'ID' => '4',
	                'user_login' => 'saddington',
	                'user_email' => 'me@john.do',
	                'display_name' => 'John Saddington',
	                'first_name' => 'John',
	                'last_name' => 'Saddington',
	            ),
	            'meta' => array()
	        ),
	        array(
	            'data' => array(
	                'ID' => '5',
	                'user_login' => 'alliswell',
	                'user_email' => 'jared@lessmade.com',
	                'display_name' => 'Jared Erickson',
	                'first_name' => 'Jared',
	                'last_name' => 'Erickson',
	            ),
	            'meta' => array()
	        ),
	        array(
	            'data' => array(
	                'ID' => '6',
	                'user_login' => 'jbrad',
	                'user_email' => 'jason.bradley@me.com',
	                'display_name' => 'Jason Bradley',
	                'first_name' => 'Jason',
	                'last_name' => 'Bradley',
	            ),
	            'meta' => array()
	        ),
	    ),
	    'posts' => 154,
	    'media' => 44,
	    'comments' => 30,
	    'terms' => 61,
	    'generator' => 'http://wordpress.org/?v=3.5.1',
	    'version' => 1.2,
	);
	// $data = array(
	// 	'tempXmlUrl' => 'http://sites-wpastra.sharkz.in/wp-content/uploads/astra-sites/car-repair/wxr.xml',
	//     'home' => 'http://sites-wpastra.sharkz.in/car-repair',
	//     'siteurl' => 'http://sites-wpastra.sharkz.in/',
	//     'title' => 'Car Repair',
	//     'users' => array(
	//         array(
	//             'data' => array(
	//                 'ID' => '4',
	//                 'user_login' => 'komalg',
	//                 'user_email' => 'komalg@bsf.io',
	//                 'display_name' => 'komalg',
	//                 'first_name' => '',
	//                 'last_name' => '',
	//             ),
	//             'meta' => array()
	//         ),
	//         array(
	//             'data' => array(
	//                 'ID' => '2',
	//                 'user_login' => 'satishk',
	//                 'user_email' => 'satishk@bsf.io',
	//                 'display_name' => 'satishk',
	//                 'first_name' => '',
	//                 'last_name' => '',
	//             ),
	//             'meta' => array()
	//         )
	//     ),
	//     'post' => '14',
	//     'media' => '35',
	//     'comment' => '1',
	//     'term' => '4',
	//     'generator' => 'https://wordpress.org/?v=4.8.1',
	//     'version' => '1.2',
	// );

	?>

	<table id="import-log" class="widefat">
		<thead>
			<tr>
				<th><?php esc_html_e( 'Type', 'wordpress-importer' ) ?></th>
				<th><?php esc_html_e( 'Message', 'wordpress-importer' ) ?></th>
			</tr>
		</thead>
		<tbody>
		</tbody>
	</table>


	<div class="welcome-panel">
		<div class="welcome-panel-content">
			<h2><?php esc_html_e( 'Step 3: Importing', 'wordpress-importer' ) ?></h2>
			<div id="import-status-message" class="notice notice-info"><?php esc_html_e( 'Now importing.', 'wordpress-importer' ) ?></div>

			<table class="import-status">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Import Summary', 'wordpress-importer' ) ?></th>
						<th><?php esc_html_e( 'Completed', 'wordpress-importer' ) ?></th>
						<th><?php esc_html_e( 'Progress', 'wordpress-importer' ) ?></th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td>
							<span class="dashicons dashicons-admin-post"></span>
							<?php
							echo esc_html( sprintf(
								_n( '%d post (including CPTs)', '%d posts (including CPTs)', $data['posts'], 'wordpress-importer' ),
								$data['posts']
							));
							?>
						</td>
						<td>
							<span id="completed-posts" class="completed">0/0</span>
						</td>
						<td>
							<progress id="progressbar-posts" max="100" value="0"></progress>
							<span id="progress-posts" class="progress">0%</span>
						</td>
					</tr>
					<tr>
						<td>
							<span class="dashicons dashicons-admin-media"></span>
							<?php
							echo esc_html( sprintf(
								_n( '%d media item', '%d media items', $data['media'], 'wordpress-importer' ),
								$data['media']
							));
							?>
						</td>
						<td>
							<span id="completed-media" class="completed">0/0</span>
						</td>
						<td>
							<progress id="progressbar-media" max="100" value="0"></progress>
							<span id="progress-media" class="progress">0%</span>
						</td>
					</tr>

					<tr>
						<td>
							<span class="dashicons dashicons-admin-users"></span>
							<?php
							echo esc_html( sprintf(
								_n( '%d user', '%d users', count( $data['users'] ), 'wordpress-importer' ),
								count( $data['users'] )
							));
							?>
						</td>
						<td>
							<span id="completed-users" class="completed">0/0</span>
						</td>
						<td>
							<progress id="progressbar-users" max="100" value="0"></progress>
							<span id="progress-users" class="progress">0%</span>
						</td>
					</tr>

					<tr>
						<td>
							<span class="dashicons dashicons-admin-comments"></span>
							<?php
							echo esc_html( sprintf(
								_n( '%d comment', '%d comments', $data['comments'], 'wordpress-importer' ),
								$data['comments']
							));
							?>
						</td>
						<td>
							<span id="completed-comments" class="completed">0/0</span>
						</td>
						<td>
							<progress id="progressbar-comments" max="100" value="0"></progress>
							<span id="progress-comments" class="progress">0%</span>
						</td>
					</tr>

					<tr>
						<td>
							<span class="dashicons dashicons-category"></span>
							<?php
							echo esc_html( sprintf(
								_n( '%d term', '%d terms', $data['terms'], 'wordpress-importer' ),
								$data['terms']
							));
							?>
						</td>
						<td>
							<span id="completed-terms" class="completed">0/0</span>
						</td>
						<td>
							<progress id="progressbar-terms" max="100" value="0"></progress>
							<span id="progress-terms" class="progress">0%</span>
						</td>
					</tr>
				</tbody>
			</table>

			<div class="import-status-indicator">
				<div class="progress">
					<progress id="progressbar-total" max="100" value="0"></progress>
				</div>
				<div class="status">
					<span id="completed-total" class="completed">0/0</span>
					<span id="progress-total" class="progress">0%</span>
				</div>
			</div>
		</div>
	</div>
	<?php
}

/**
 * Astra Demo View.
 *
 * @package Astra Addon
 */

defined( 'ABSPATH' ) or exit;

// Enqueue scripts.
wp_enqueue_script( 'astra-sites-admin' );
wp_enqueue_style( 'astra-sites-admin' );
wp_enqueue_style( 'astra-sites-importer' );

	/**
	 * Initial Demo List
	 *
	 * Generated though PHP
	 */
	?>
	<div class="wrap">

		<?php debugme(); ?>


		<div class="wp-filter hide-if-no-js">

			<ul class="filter-links">

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
