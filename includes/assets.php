<?php

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( ! class_exists( 'Jet_Sticky_Assets' ) ) {

	/**
	 * Define Jet_Sticky_Assets class
	 */
	class Jet_Sticky_Assets {

		/**
		 * A reference to an instance of this class.
		 *
		 * @since 1.0.0
		 * @var   object
		 */
		private static $instance = null;

		/**
		 * Localize data
		 *
		 * @var array
		 */
		public $elements_data = array(
			'sections' => array(),
			'columns'  => array(),
		);

		/**
		 * Constructor for the class
		 */
		public function init() {
			add_action( 'elementor/frontend/after_enqueue_styles',   array( $this, 'enqueue_styles' ) );
			add_action( 'elementor/frontend/before_enqueue_scripts', array( $this, 'enqueue_scripts' ), 10 );
			add_action( 'admin_enqueue_scripts',  array( $this, 'admin_enqueue_styles' ) );
		}

		/**
		 * Enqueue public-facing stylesheets.
		 *
		 * @since 1.0.0
		 * @access public
		 * @return void
		 */
		public function enqueue_styles() {

			wp_enqueue_style(
				'jet-sticky-frontend',
				jet_sticky()->plugin_url( 'assets/css/jet-sticky-frontend.css' ),
				false,
				jet_sticky()->get_version()
			);
		}

		/**
		 * Enqueue plugin scripts only with elementor scripts
		 *
		 * @return void
		 */
		public function enqueue_scripts() {

			wp_enqueue_script(
				'jet-sticky-frontend',
				jet_sticky()->plugin_url( 'assets/js/jet-sticky-frontend.js' ),
				array( 'jquery', 'elementor-frontend' ),
				jet_sticky()->get_version(),
				true
			);

			wp_localize_script( 'jet-sticky-frontend', 'JetStickySettings', array(
				'elements_data' => $this->elements_data,
			) );
		}

		/**
		 * Enqueue admin styles
		 *
		 * @return void
		 */
		public function admin_enqueue_styles() {
			$screen = get_current_screen();

			if ( 'plugins' === $screen->base ) {
				wp_enqueue_style(
					'jet-sticky-admin',
					jet_sticky()->plugin_url( 'assets/css/jet-sticky-admin.css' ),
					false,
					jet_sticky()->get_version()
				);
			}
		}

		/**
		 * Returns the instance.
		 *
		 * @since  1.0.0
		 * @return object
		 */
		public static function get_instance() {

			// If the single instance hasn't been set, set it now.
			if ( null == self::$instance ) {
				self::$instance = new self;
			}
			return self::$instance;
		}
	}

}

function jet_sticky_assets() {
	return Jet_Sticky_Assets::get_instance();
}
