<?php
/**
 * Plugin Name: Jet Sticky For Elementor
 * Description: JetSticky is the plugin which allows to make the sections and columns built with Elementor sticky!
 * Version:     1.0.0
 * Author:      Crocoblock
 * Author URI:  https://crocoblock.com/
 * Text Domain: jet-sticky
 * License:     GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Domain Path: /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die();
}

// If class `Jet_Sticky` doesn't exists yet.
if ( ! class_exists( 'Jet_Sticky' ) ) {

	/**
	 * Sets up and initializes the plugin.
	 */
	class Jet_Sticky {

		/**
		 * A reference to an instance of this class.
		 *
		 * @since  1.0.0
		 * @access private
		 * @var    object
		 */
		private static $instance = null;

		/**
		 * Holder for base plugin URL
		 *
		 * @since  1.0.0
		 * @access private
		 * @var    string
		 */
		private $plugin_url = null;

		/**
		 * Plugin version
		 *
		 * @var string
		 */
		private $version = '1.0.0';

		/**
		 * Holder for base plugin path
		 *
		 * @since  1.0.0
		 * @access private
		 * @var    string
		 */
		private $plugin_path = null;

		/**
		 * Components
		 */
		public $assets;

		/**
		 * Sets up needed actions/filters for the plugin to initialize.
		 *
		 * @since 1.0.0
		 * @access public
		 * @return void
		 */
		public function __construct() {

			// Internationalize the text strings used.
			add_action( 'init', array( $this, 'lang' ), -999 );
			// Load files.
			add_action( 'init', array( $this, 'init' ), -999 );

			// Register activation and deactivation hook.
			register_activation_hook( __FILE__, array( $this, 'activation' ) );
			register_deactivation_hook( __FILE__, array( $this, 'deactivation' ) );
		}

		/**
		 * Returns plugin version
		 *
		 * @return string
		 */
		public function get_version() {
			return $this->version;
		}

		/**
		 * Manually init required modules.
		 *
		 * @return void
		 */
		public function init() {

			$this->load_files();

			jet_sticky_element_extension()->init();
			jet_sticky_assets()->init();

			do_action( 'jet-sticky/init', $this );

		}

		/**
		 * Show recommended plugins notice.
		 *
		 * @return void
		 */
		public function required_plugins_notice() {
			require $this->plugin_path( 'includes/lib/class-tgm-plugin-activation.php' );
			add_action( 'tgmpa_register', array( $this, 'register_required_plugins' ) );
		}

		/**
		 * Register required plugins
		 *
		 * @return void
		 */
		public function register_required_plugins() {

			$plugins = array(
				array(
					'name'     => 'Elementor',
					'slug'     => 'elementor',
					'required' => true,
				),
			);

			$config = array(
				'id'           => 'jet-sticky',
				'default_path' => '',
				'menu'         => 'jet-sticky-install-plugins',
				'parent_slug'  => 'plugins.php',
				'capability'   => 'manage_options',
				'has_notices'  => true,
				'dismissable'  => true,
				'dismiss_msg'  => '',
				'is_automatic' => false,
				'strings'      => array(
					'notice_can_install_required'     => _n_noop(
						'Jet Blog for Elementor requires the following plugin: %1$s.',
						'Jet Blog for Elementor requires the following plugins: %1$s.',
						'jet-sticky'
					),
					'notice_can_install_recommended'  => _n_noop(
						'Jet Blog for Elementor recommends the following plugin: %1$s.',
						'Jet Blog for Elementor recommends the following plugins: %1$s.',
						'jet-sticky'
					),
				),
			);

			tgmpa( $plugins, $config );
		}

		/**
		 * Load required files
		 *
		 * @return void
		 */
		public function load_files() {
			require $this->plugin_path( 'includes/ext/element-extension.php' );
			require $this->plugin_path( 'includes/assets.php' );

		}

		/**
		 * Check if theme has elementor
		 *
		 * @return boolean
		 */
		public function has_elementor() {
			return defined( 'ELEMENTOR_VERSION' );
		}

		/**
		 * Returns elementor instance
		 *
		 * @return object
		 */
		public function elementor() {
			return \Elementor\Plugin::$instance;
		}

		/**
		 * Returns path to file or dir inside plugin folder
		 *
		 * @param  string $path Path inside plugin dir.
		 * @return string
		 */
		public function plugin_path( $path = null ) {

			if ( ! $this->plugin_path ) {
				$this->plugin_path = trailingslashit( plugin_dir_path( __FILE__ ) );
			}

			return $this->plugin_path . $path;
		}
		/**
		 * Returns url to file or dir inside plugin folder
		 *
		 * @param  string $path Path inside plugin dir.
		 * @return string
		 */
		public function plugin_url( $path = null ) {

			if ( ! $this->plugin_url ) {
				$this->plugin_url = trailingslashit( plugin_dir_url( __FILE__ ) );
			}

			return $this->plugin_url . $path;
		}

		/**
		 * Loads the translation files.
		 *
		 * @since 1.0.0
		 * @access public
		 * @return void
		 */
		public function lang() {
			load_plugin_textdomain( 'jet-sticky', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
		}

		/**
		 * Get the template path.
		 *
		 * @return string
		 */
		public function template_path() {
			return apply_filters( 'jet-sticky/template-path', 'jet-sticky/' );
		}

		/**
		 * Returns path to template file.
		 *
		 * @return string|bool
		 */
		public function get_template( $name = null ) {

			$template = locate_template( $this->template_path() . $name );

			if ( ! $template ) {
				$template = $this->plugin_path( 'templates/' . $name );
			}

			if ( file_exists( $template ) ) {
				return $template;
			} else {
				return false;
			}
		}

		/**
		 * Do some stuff on plugin activation
		 *
		 * @since  1.0.0
		 * @return void
		 */
		public function activation() {
		}

		/**
		 * Do some stuff on plugin activation
		 *
		 * @since  1.0.0
		 * @return void
		 */
		public function deactivation() {
		}

		/**
		 * Returns the instance.
		 *
		 * @since  1.0.0
		 * @access public
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

if ( ! function_exists( 'jet_sticky' ) ) {

	/**
	 * Returns instanse of the plugin class.
	 *
	 * @since  1.0.0
	 * @return object
	 */
	function jet_sticky() {
		return Jet_Sticky::get_instance();
	}
}

jet_sticky();
