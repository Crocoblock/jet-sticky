<?php

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( ! class_exists( 'Jet_Sticky_Element_Extension' ) ) {

	/**
	 * Define Jet_Sticky_Element_Extension class
	 */
	class Jet_Sticky_Element_Extension {

		/**
		 * Sections Data
		 *
		 * @var array
		 */
		public $sections_data = array();

		/**
		 * Columns Data
		 *
		 * @var array
		 */
		public $columns_data = array();

		/**
		 * A reference to an instance of this class.
		 *
		 * @since  1.0.0
		 * @access private
		 * @var    object
		 */
		private static $instance = null;

		/**
		 * Init Handler
		 */
		public function init() {

			add_action( 'elementor/element/column/section_advanced/after_section_end', array( $this, 'after_column_section_layout' ), 10, 2 );

			add_action( 'elementor/frontend/column/before_render',  array( $this, 'column_before_render' ) );
			add_action( 'elementor/frontend/element/before_render', array( $this, 'column_before_render' ) );

			add_action( 'elementor/frontend/before_enqueue_scripts', array( $this, 'enqueue_scripts' ), 9 );
		}

		/**
		 * After column_layout callback
		 *
		 * @param  object $obj
		 * @param  array $args
		 * @return void
		 */
		public function after_column_section_layout( $obj, $args ) {

			$obj->start_controls_section(
				'jet_sticky_column_sticky_section',
				array(
					'label' => esc_html__( 'Jet Sticky', 'jet-sticky' ),
					'tab'   => Elementor\Controls_Manager::TAB_ADVANCED,
				)
			);

			$obj->add_control(
				'jet_sticky_column_sticky_enable',
				array(
					'label'        => esc_html__( 'Sticky Column', 'jet-sticky' ),
					'type'         => Elementor\Controls_Manager::SWITCHER,
					'label_on'     => esc_html__( 'Yes', 'jet-sticky' ),
					'label_off'    => esc_html__( 'No', 'jet-sticky' ),
					'return_value' => 'true',
					'default'      => 'false',
				)
			);

			$obj->add_control(
				'jet_sticky_column_sticky_top_spacing',
				array(
					'label'   => esc_html__( 'Top Spacing', 'jet-sticky' ),
					'type'    => Elementor\Controls_Manager::NUMBER,
					'default' => 50,
					'min'     => 0,
					'max'     => 500,
					'step'    => 1,
					'condition' => array(
						'jet_sticky_column_sticky_enable' => 'true',
					),
				)
			);

			$obj->add_control(
				'jet_sticky_column_sticky_bottom_spacing',
				array(
					'label'   => esc_html__( 'Bottom Spacing', 'jet-sticky' ),
					'type'    => Elementor\Controls_Manager::NUMBER,
					'default' => 50,
					'min'     => 0,
					'max'     => 500,
					'step'    => 1,
					'condition' => array(
						'jet_sticky_column_sticky_enable' => 'true',
					),
				)
			);

			$obj->add_control(
				'jet_sticky_column_sticky_enable_on',
				array(
					'label'    => __( 'Sticky On', 'jet-sticky' ),
					'type'     => Elementor\Controls_Manager::SELECT2,
					'multiple' => true,
					'label_block' => 'true',
					'default' => array(
						'desktop',
						'tablet',
					),
					'options' => array(
						'desktop' => __( 'Desktop', 'jet-sticky' ),
						'tablet'  => __( 'Tablet', 'jet-sticky' ),
						'mobile'  => __( 'Mobile', 'jet-sticky' ),
					),
					'condition' => array(
						'jet_sticky_column_sticky_enable' => 'true',
					),
					'render_type' => 'none',
				)
			);

			$obj->end_controls_section();
		}

		/**
		 * Before column render callback.
		 *
		 * @param object $element
		 *
		 * @return bool|void
		 */
		public function column_before_render( $element ) {
			$data     = $element->get_data();
			$type     = isset( $data['elType'] ) ? $data['elType'] : 'column';
			$settings = $data['settings'];

			if ( 'column' !== $type ) {
				return false;
			}

			if ( isset( $settings['jet_sticky_column_sticky_enable'] ) ) {
				$column_settings = array(
					'id'            => $data['id'],
					'sticky'        => filter_var( $settings['jet_sticky_column_sticky_enable'], FILTER_VALIDATE_BOOLEAN ),
					'topSpacing'    => isset( $settings['jet_sticky_column_sticky_top_spacing'] ) ? $settings['jet_sticky_column_sticky_top_spacing'] : 50,
					'bottomSpacing' => isset( $settings['jet_sticky_column_sticky_bottom_spacing'] ) ? $settings['jet_sticky_column_sticky_bottom_spacing'] : 50,
					'stickyOn'      => isset( $settings['jet_sticky_column_sticky_enable_on'] ) ? $settings['jet_sticky_column_sticky_enable_on'] : array( 'desktop', 'tablet' ),
				);

				if ( filter_var( $settings['jet_sticky_column_sticky_enable'], FILTER_VALIDATE_BOOLEAN ) ) {

					$element->add_render_attribute( '_wrapper', array(
						'class'         => 'jet-sticky-column-sticky',
						'data-settings' => json_encode( $column_settings ),
					) );
				}

				$this->columns_data[ $data['id'] ] = $column_settings;
			}
		}

		/**
		 * Enqueue scripts
		 *
		 * @return void
		 */
		public function enqueue_scripts() {

			wp_enqueue_script(
				'jet-resize-sensor',
				jet_sticky()->plugin_url( 'assets/js/lib/ResizeSensor.min.js' ),
				array( 'jquery' ),
				'1.7.0',
				true
			);

			wp_enqueue_script(
				'jet-sticky-sidebar',
				jet_sticky()->plugin_url( 'assets/js/lib/sticky-sidebar/sticky-sidebar.min.js' ),
				array( 'jquery', 'jet-resize-sensor' ),
				'3.3.1',
				true
			);

			jet_sticky_assets()->elements_data['columns'] = $this->columns_data;
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

/**
 * Returns instance of Jet_Sticky_Element_Extension
 *
 * @return object
 */
function jet_sticky_element_extension() {
	return Jet_Sticky_Element_Extension::get_instance();
}
