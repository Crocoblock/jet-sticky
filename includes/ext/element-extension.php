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
			add_action( 'elementor/element/section/section_advanced/after_section_end', array( $this, 'add_section_sticky_controls' ), 10, 2 );
			add_action( 'elementor/frontend/before_enqueue_scripts', array( $this, 'enqueue_scripts' ), 9 );

			//compability elementor flex container
			add_action( 'elementor/frontend/container/before_render', array( $this, 'column_before_render' ) );
			add_action( 'elementor/element/container/section_layout/after_section_end', array( $this, 'add_section_sticky_controls' ), 10, 2 );
			add_action( 'elementor/element/container/jet_sticky_section_sticky_settings/before_section_end', array( $this, 'after_column_section_setting' ), 10, 2 );

		}


		/**
		 * After column_layout callback
		 *
		 * @param  object $obj
		 * @param  array $args
		 * @return void
		 */
		public function after_column_section_layout($obj, $args) {

			$obj->start_controls_section(
				'jet_sticky_column_sticky_section',
				array(
					'label' => esc_html__( 'Jet Sticky', 'jetsticky-for-elementor' ),
					'tab'   => Elementor\Controls_Manager::TAB_ADVANCED,
				)
			);

			$this->after_column_section_setting($obj, $args);

			$obj->end_controls_section();
		}

		/**
		 * After column_layout callback
		 *
		 * @param  object $obj
		 * @param  array $args
		 * @return void
		 */
		public function after_column_section_setting( $obj, $args ) {

			if ( \Elementor\Plugin::$instance->breakpoints && method_exists( \Elementor\Plugin::$instance->breakpoints, 'get_active_breakpoints')) {
				$active_breakpoints = \Elementor\Plugin::$instance->breakpoints->get_active_breakpoints();
				$breakpoints_list   = array();

				foreach ($active_breakpoints as $key => $value) {
					$breakpoints_list[$key] = $value->get_label();
				}

				$breakpoints_list['desktop'] = 'Desktop';
				$breakpoints_list            = array_reverse($breakpoints_list);
			} else {
				$breakpoints_list = array(
					'desktop' => 'Desktop',
					'tablet'  => 'Tablet',
					'mobile'  => 'Mobile'
				);
			}

			$obj->add_control(
				'jet_sticky_column_sticky_enable',
				array(
					'label'        => esc_html__( 'Sticky Column', 'jetsticky-for-elementor' ),
					'type'         => Elementor\Controls_Manager::SWITCHER,
					'label_on'     => esc_html__( 'Yes', 'jetsticky-for-elementor' ),
					'label_off'    => esc_html__( 'No', 'jetsticky-for-elementor' ),
					'return_value' => 'true',
					'default'      => 'false',
				)
			);

			$obj->add_control(
				'jet_sticky_column_sticky_top_spacing',
				array(
					'label'   => esc_html__( 'Top Spacing', 'jetsticky-for-elementor' ),
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
					'label'   => esc_html__( 'Bottom Spacing', 'jetsticky-for-elementor' ),
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
					'label'    => __( 'Sticky On', 'jetsticky-for-elementor' ),
					'type'     => Elementor\Controls_Manager::SELECT2,
					'multiple' => true,
					'label_block' => 'true',
					'default' => array(
						'desktop',
						'tablet',
					),
					'options' => $breakpoints_list,
					'condition' => array(
						'jet_sticky_column_sticky_enable' => 'true',
					),
					'render_type' => 'none',
				)
			);
		}

		/**
		 * Before column render callback.
		 *
		 * @param object $element
		 *
		 * @return void
		 */
		public function column_before_render( $element ) {
			$data     = $element->get_data();
			$type     = isset( $data['elType'] ) ? $data['elType'] : 'column';
			$settings = $data['settings'];

			if ( 'column' !== $type && 'container' !== $type ) {
				return;
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
						'class' => 'jet-sticky-column-sticky',
						'data-jet-sticky-column-settings' => json_encode( $column_settings ),
					) );
				}

				$this->columns_data[ $data['id'] ] = $column_settings;
			}
		}

		/**
		 * Add sticky controls to section settings.
		 *
		 * @param object $element Element instance.
		 * @param array  $args    Element arguments.
		 */
		public function add_section_sticky_controls( $element, $args ) {
			$element->start_controls_section(
				'jet_sticky_section_sticky_settings',
				array(
					'label' => esc_html__( 'Jet Sticky', 'jetsticky-for-elementor' ),
					'tab'   => Elementor\Controls_Manager::TAB_ADVANCED,
				)
			);

			$element->add_control(
				'jet_sticky_section_sticky',
				array(
					'label'   => esc_html__( 'Sticky Section', 'jetsticky-for-elementor' ),
					'type'    => Elementor\Controls_Manager::SWITCHER,
					'default' => '',
					'frontend_available' => true,
				)
			);

			$element->add_control(
				'jet_sticky_section_sticky_visibility',
				array(
					'label'       => esc_html__( 'Sticky Section Visibility', 'jetsticky-for-elementor' ),
					'type'        => Elementor\Controls_Manager::SELECT2,
					'multiple'    => true,
					'label_block' => true,
					'default' => array( 'desktop', 'tablet', 'mobile' ),
					'options' => array(
						'desktop' => esc_html__( 'Desktop', 'jetsticky-for-elementor' ),
						'tablet'  => esc_html__( 'Tablet', 'jetsticky-for-elementor' ),
						'mobile'  => esc_html__( 'Mobile', 'jetsticky-for-elementor' ),
					),
					'condition' => array(
						'jet_sticky_section_sticky' => 'yes',
					),
					'frontend_available' => true,
				)
			);

			$element->add_control(
				'jet_sticky_section_sticky_z_index',
				array(
					'label'       => esc_html__( 'Z-index', 'jetsticky-for-elementor' ),
					'type'        => Elementor\Controls_Manager::NUMBER,
					'placeholder' => 1100,
					'min'         => 1,
					'max'         => 10000,
					'step'        => 1,
					'selectors'   => array(
						'{{WRAPPER}}.jet-sticky-section-sticky--stuck' => 'z-index: {{VALUE}};',
					),
					'condition' => array(
						'jet_sticky_section_sticky' => 'yes',
					),
				)
			);

			$element->add_control(
				'jet_sticky_section_sticky_max_width',
				array(
					'label' => esc_html__( 'Max Width (px)', 'jetsticky-for-elementor' ),
					'type'  => Elementor\Controls_Manager::SLIDER,
					'range' => array(
						'px' => array(
							'min' => 500,
							'max' => 2000,
						),
					),
					'selectors'   => array(
						'{{WRAPPER}}.jet-sticky-section-sticky--stuck' => 'max-width: {{SIZE}}{{UNIT}};',
					),
					'condition' => array(
						'jet_sticky_section_sticky' => 'yes',
					),
				)
			);

			$element->add_responsive_control(
				'jet_sticky_section_sticky_style_heading',
				array(
					'label'     => esc_html__( 'Sticky Section Style', 'jetsticky-for-elementor' ),
					'type'      => Elementor\Controls_Manager::HEADING,
					'separator' => 'before',
					'condition' => array(
						'jet_sticky_section_sticky' => 'yes',
					),
				)
			);

			$element->add_responsive_control(
				'jet_sticky_section_sticky_margin',
				array(
					'label'      => esc_html__( 'Margin', 'jetsticky-for-elementor' ),
					'type'       => Elementor\Controls_Manager::DIMENSIONS,
					'size_units' => array( 'px', '%' ),
					'allowed_dimensions' => 'vertical',
					'placeholder' => array(
						'top'    => '',
						'right'  => 'auto',
						'bottom' => '',
						'left'   => 'auto',
					),
					'selectors' => array(
						'{{WRAPPER}}.jet-sticky-section-sticky--stuck' => 'margin-top: {{TOP}}{{UNIT}}; margin-bottom: {{BOTTOM}}{{UNIT}};',
					),
					'condition' => array(
						'jet_sticky_section_sticky' => 'yes',
					),
				)
			);

			$element->add_responsive_control(
				'jet_sticky_section_sticky_padding',
				array(
					'label'      => esc_html__( 'Padding', 'jetsticky-for-elementor' ),
					'type'       => Elementor\Controls_Manager::DIMENSIONS,
					'size_units' => array( 'px', 'em', '%' ),
					'selectors'  => array(
						'{{WRAPPER}}.jet-sticky-section-sticky--stuck' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					),
					'condition' => array(
						'jet_sticky_section_sticky' => 'yes',
					),
				)
			);

			$element->add_group_control(
				Elementor\Group_Control_Background::get_type(),
				array(
					'name'      => 'jet_sticky_section_sticky_background',
					'selector'  => '{{WRAPPER}}.jet-sticky-section-sticky--stuck',
					'condition' => array(
						'jet_sticky_section_sticky' => 'yes',
					),
				)
			);

			$element->add_group_control(
				Elementor\Group_Control_Box_Shadow::get_type(),
				array(
					'name'      => 'jet_sticky_section_sticky_box_shadow',
					'selector'  => '{{WRAPPER}}.jet-sticky-section-sticky--stuck',
					'condition' => array(
						'jet_sticky_section_sticky' => 'yes',
					),
				)
			);

			$element->add_control(
				'jet_sticky_section_sticky_transition',
				array(
					'label'   => esc_html__( 'Transition Duration', 'jetsticky-for-elementor' ),
					'type'    => Elementor\Controls_Manager::SLIDER,
					'default' => array(
						'size' => 0.1,
					),
					'range' => array(
						'px' => array(
							'max'  => 3,
							'step' => 0.1,
						),
					),
					'selectors' => array(
						'{{WRAPPER}}.jet-sticky-section-sticky--stuck.jet-sticky-transition-in, {{WRAPPER}}.jet-sticky-section-sticky--stuck.jet-sticky-transition-out' => 'transition: margin {{SIZE}}s, padding {{SIZE}}s, background {{SIZE}}s, box-shadow {{SIZE}}s',
					),
					'condition' => array(
						'jet_sticky_section_sticky' => 'yes',
					),
				)
			);

			$element->end_controls_section();
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

			wp_enqueue_script(
				'jsticky',
				jet_sticky()->plugin_url( 'assets/js/lib/jsticky/jquery.jsticky.js' ),
				array( 'jquery' ),
				'1.1.0',
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
