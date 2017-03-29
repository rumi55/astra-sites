<?php
wp_enqueue_script( 'astra-demo-import-admin' );
?>

<style type="text/css">

	.theme-browser .theme.focus .theme-actions,.theme-browser .theme:focus .theme-actions,.theme-browser .theme:hover .theme-actions {
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
	
	<h3 style="margin-bottom: 15px;">Astra Demo Import</h3>

	<div class="theme-browser rendered">
		<?php

		foreach ( Astra_Demo_Import::get_all_astra_demos() as $key => $demo ) {
		 	?>

		 	<div class="theme" tabindex="0" aria-describedby="astra-theme-action astra-theme-name">

		 		<div class="theme-screenshot">
		 			<img src="<?php echo esc_attr( $demo['featured-image-url'] ) ?>" alt="">
		 		</div>

		 		<a href="<?php echo esc_url( $demo[ 'astra-demo-url' ] ) ?>" target="_blank"> <span class="more-details" id="astra-theme-action">See the demo</span> </a>

		 		<h2 class="theme-name" id="astra-theme-name"><?php echo esc_attr( $demo['title'] ) ?></h2>

		 		<div class="theme-actions">
		 			<a class="button button-primary hide-if-no-customize astra-demo-import" href="#" 
		 				data-demo-id="<?php echo esc_attr( $demo[ 'id' ] ); ?>"
		 				data-demo-url="<?php echo esc_url( $demo[ 'astra-demo-url' ] ); ?>"
		 				data-demo-api="<?php echo esc_url( $demo[ 'demo-api' ] ); ?>"
		 			>Import Demo</a>
		 		</div>
		 	</div>

		 	<?php
		}

		?>
	</div>

</div>