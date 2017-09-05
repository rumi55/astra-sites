<?php
/**
 * Astra Sites Notices
 *
 * @package Astra Sites
 * @since 1.0.8
 */

if( ! class_exists( 'Astra_Sites_Notices' ) ) :

	/**
	 * Astra_Sites_Notices
	 *
	 * @since 1.0.8
	 */
	class Astra_Sites_Notices {

		private static $notices = array();

		/**
		 * Instance
		 *
		 * @access private
		 * @since 1.0.8
		 */
		private static $instance;

		/**
		 * Initiator
		 *
		 * @since 1.0.8
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
		 * @since 1.0.8
		 */
		public function __construct() {

			add_action( 'admin_notices', 			array( $this, 'show_notices' ) );
			add_action( 'admin_enqueue_scripts', 	array( $this, 'enqueue_scripts' ) );			
			add_action( 'wp_ajax_astra-notices', 	array( $this, 'dismiss' ) );

		}

		/**
		 * Add Notice.
		 *
		 * @since 1.0.8
		 * @param void
		 */
		public static function add_notice( $args ) {
			if( is_array( $args ) ) {
				self::$notices[] = $args;
			}
		}

		/**
		 * Dismiss Notice.
		 *
		 * @since 1.0.8
		 * @param void
		 */
		function dismiss() {

			$id   = ( isset( $_POST['id'] ) ) ? $_POST['id'] : '';
			$time = ( isset( $_POST['time'] ) ) ? $_POST['time'] : '';

			// Reset transient.
			if( ! empty( $time ) && ! empty( $id ) ) {
				set_transient( $id, true, $time );
				wp_send_json_success();
			}

			wp_send_json_error();
		}

		/**
		 * Enqueue Scripts.
		 *
		 * @since 1.0.8
		 * @param void
		 */
		function enqueue_scripts() {
			wp_register_script( 'astra-sites-notices', ASTRA_SITES_URI . 'inc/assets/js/astra-sites-notices.js', array( 'jquery' ), ASTRA_SITES_VER, true );
		}

		/**
		 * Notice Types
		 *
		 * @since 1.0.8
		 * @return void
		 */
		function show_notices() {

			// vl( ( ! defined( 'ASTRA_THEME_SETTINGS' ) ) ? true : false );

			// vl( self::$notices );

			$defaults = array(
				'type' => 'info',
				'show_if' => true,
				'message' => '',
				'class' => 'ast-active-notice',
				
				'dismissible' => false,
				// 'dismissible-meta' => 'user', // 'transient',
				'dismissible-time' => MINUTE_IN_SECONDS,

				'data' => '',
			);

			foreach ( self::$notices as $key => $notice) {

				$notice = wp_parse_args( $notice, $defaults );

				$classes = array( 'astra-notice', 'notice' );

				$classes[] = $notice['class'];
				if ( isset( $notice['type'] ) ) {
					$classes[] = 'notice-' . $notice['type'];
				}

				// Is notice dismissible?
				if( true === $notice['dismissible'] ) {
					$classes[] = 'is-dismissible';
					
					// Dismissable time.
					$notice['data'] = ' dismissible-time='.$notice['dismissible-time'].' ';
				}

				// Notice ID.
				$notice_id = 'astra-sites-notices-id-' . $key;
				$notice['id'] = $notice_id;
				$notice['classes'] = implode(' ', $classes);

				// Notices visible after transient expire.
				if( isset( $notice['show_if'] ) ) {

					if( true === $notice['show_if'] ) {

						// Is transient expired?
						if( false === get_transient( $notice_id ) ) {
							self::markup( $notice );
						}
					}

				} else {

					// No transient notices.
					self::markup( $notice );
				}

			}

		}

		/**
		 * Markup Notice.
		 *
		 * @since 1.0.8
		 * @param  array  $notice Notice markup.
		 * @return void
		 */
		public static function markup( $notice = array() ) {

			wp_enqueue_script( 'astra-sites-notices' );

			?>
			<div id="<?php echo esc_attr( $notice['id'] ); ?>" class="<?php echo esc_attr( $notice['classes'] ); ?>" <?php echo esc_attr( $notice['data'] ); ?>>
				<p>
					<?php echo $notice['message']; ?>
				</p>
			</div>
			<?php
		}

	}

	/**
	 * Kicking this off by calling 'set_instance()' method
	 */
	Astra_Sites_Notices::set_instance();

endif;