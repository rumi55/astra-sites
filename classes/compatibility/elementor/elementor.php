<?php
/**
 * Astra Sites Elementor
 *
 * @package Astra Sites
 * @since 1.0.4
 */

if( ! class_exists( 'Astra_Sites_Comp_Elementor' ) ) :

	/**
	 * Astra_Sites_Comp_Elementor
	 *
	 * @since 1.0.4
	 */
	class Astra_Sites_Comp_Elementor {

		/**
		 * @var WP_Example_Request
		 */
		protected $process_single;

		/**
		 * @var WP_Example_Process
		 */
		protected $process_all;

		/**
		 * Instance
		 *
		 * @access private
		 * @since 1.0.4
		 */
		private static $instance;

		/**
		 * Initiator
		 *
		 * @since 1.0.4
		 * @return object initialized object of class.
		 */
		public static function set_instance(){
			if ( ! isset( self::$instance ) ) {
				self::$instance = new self;
			}
			return self::$instance;
		}

		/**
		 * Constructor
		 *
		 * @since 1.0.4
		 */
		public function __construct() {

			$this->includes();

			add_action( 'admin_head', array( $this, 'astra_sites_process_handler' ) );
			// add_action( 'astra_sites_import_complete', array( $this, 'astra_sites_process_handler' ) );
		}

		function includes() {

			require_once ASTRA_SITES_DIR . 'classes/compatibility/elementor/bg/classes/wp-async-request.php';
			require_once ASTRA_SITES_DIR . 'classes/compatibility/elementor/bg/classes/wp-background-process.php';


			require_once ASTRA_SITES_DIR . 'classes/compatibility/elementor/bg/class-logger.php';
			require_once ASTRA_SITES_DIR . 'classes/compatibility/elementor/bg/class-example-request.php';
			require_once ASTRA_SITES_DIR . 'classes/compatibility/elementor/bg/class-example-process.php';
			// $this->process_single = new WP_Example_Request();
			$this->process_all    = new WP_Example_Process();
		}

		/**
		 * Process handler
		 */
		public function astra_sites_process_handler() {
			// vl( get_the_ID() );

			// if( get_the_ID() === 21 ) {
			// 	vl( get_the_ID() );
			// 	vl( 'importing...' );
			// 	$this->process_all->push_to_queue( get_the_ID() );
			// 	$this->process_all->save()->dispatch();
			// }
			// wp_die();

			// delete_site_option( '_astra_sites_import_images' );
			// wp_die();

			$imported = get_site_option( '_astra_sites_import_images', 0, false );
			if( $imported == 2 ) {
				$this->handle_all();
				vl( 'importing...' );
				vl( $imported );
				wp_die();
			}
			$imported++;
			update_site_option( '_astra_sites_import_images', $imported );
		}

		/**
		 * Handle all
		 */
		protected function handle_all() {
			$names = $this->get_names();

			if( is_array( $names ) ) {
				foreach ( $names as $name ) {
					$this->process_all->push_to_queue( $name );
				}
				$this->process_all->save()->dispatch();
			}
		}

		/**
		 * Get names
		 *
		 * @return array
		 */
		protected function get_names() {

			$args = array(
				'post_type'    => 'page',

				// Query performance optimization.
				'fields'        => 'ids',
				'no_found_rows' => true,
				'post_status'   => 'publish',
			);

			$query = new WP_Query( $args );

			// Have posts?
			if ( $query->have_posts() ) :

				return $query->posts;
				// while ( $query->have_posts() ) {

				// 	$query->the_post();

				// 	vl( get_the_id() );
				// }

				// // Restore original post data.
				// wp_reset_postdata();

			endif;

			return;
		}

	}

	/**
	 * Kicking this off by calling 'set_instance()' method
	 */
	Astra_Sites_Comp_Elementor::set_instance();

endif;
