<?php
/**
 * EDS Loop Animations Extension
 * @package EDS_Global_Settings
 * @since   2.2.0
 */
if ( ! defined( 'ABSPATH' ) ) exit;

use Elementor\Controls_Manager;

class EDS_Extension_Loop_Animations {

	public function __construct() {
		add_action( 'elementor/element/after_section_end', [ $this, 'add_controls' ], 10, 3 );
		add_action( 'elementor/frontend/before_render',   [ $this, 'before_render' ] );
		add_action( 'elementor/editor/before_enqueue_scripts', [ $this, 'enqueue_editor' ] );
		add_action( 'wp_enqueue_scripts',                 [ $this, 'enqueue_frontend' ] );
		add_action( 'elementor/preview/enqueue_scripts',  [ $this, 'enqueue_preview' ] );
	}

	public function enqueue_editor(): void {
		wp_enqueue_style( 'eds-loop-animations', EDS_PLUGIN_URL . 'includes/extensions/assets/css/eds-loop-animations.css', [], EDS_VERSION );
		wp_enqueue_script( 'eds-loop-editor', EDS_PLUGIN_URL . 'includes/extensions/assets/js/eds-loop-editor.js', [ 'jquery', 'elementor-editor' ], EDS_VERSION, true );
	}
	public function enqueue_frontend(): void {
		wp_enqueue_style( 'eds-loop-animations', EDS_PLUGIN_URL . 'includes/extensions/assets/css/eds-loop-animations.css', [], EDS_VERSION );
		wp_enqueue_script( 'eds-loop-animations', EDS_PLUGIN_URL . 'includes/extensions/assets/js/eds-loop-animations.js', [], EDS_VERSION, true );
	}
	public function enqueue_preview(): void {
		wp_enqueue_style( 'eds-loop-animations' );
		wp_enqueue_script( 'eds-loop-animations' );
	}

	public function add_controls( $element, $section_id, $args ): void {
		if ( '_section_responsive' !== $section_id ) return;

		$element->start_controls_section( '_eds_loop_section', [
			'label' => __( 'EDS Loop Animations', 'eds-global-settings' ),
			'tab'   => Controls_Manager::TAB_ADVANCED,
		] );

		$element->add_control( '_eds_loop_animation', [
			'label'       => __( 'Animation', 'eds-global-settings' ),
			'type'        => Controls_Manager::SELECT,
			'default'     => '',
			'render_type' => 'template',
			'options'     => [
				''           => __( 'None', 'eds-global-settings' ),
				'float'      => __( 'Float (Up & Down)', 'eds-global-settings' ),
				'float-side' => __( 'Float (Side to Side)', 'eds-global-settings' ),
				'shake'      => __( 'Shake', 'eds-global-settings' ),
				'spin'       => __( 'Spin', 'eds-global-settings' ),
				'spin-ccw'   => __( 'Spin Counter-Clockwise', 'eds-global-settings' ),
				'pulse'      => __( 'Pulse (Scale)', 'eds-global-settings' ),
				'pulse-fade' => __( 'Pulse (Fade)', 'eds-global-settings' ),
				'bounce'     => __( 'Bounce', 'eds-global-settings' ),
				'swing'      => __( 'Swing', 'eds-global-settings' ),
				'wobble'     => __( 'Wobble', 'eds-global-settings' ),
				'wiggle'     => __( 'Wiggle', 'eds-global-settings' ),
				'heartbeat'  => __( 'Heartbeat', 'eds-global-settings' ),
				'jello'      => __( 'Jello', 'eds-global-settings' ),
				'rubber'     => __( 'Rubber Band', 'eds-global-settings' ),
				'tada'       => __( 'Tada', 'eds-global-settings' ),
				'orbit'      => __( 'Orbit (Circle)', 'eds-global-settings' ),
				'random'     => __( 'Random Move', 'eds-global-settings' ),
			],
		] );

		$element->add_control( '_eds_loop_duration', [
			'label'      => __( 'Duration (seconds)', 'eds-global-settings' ),
			'type'       => Controls_Manager::SLIDER,
			'default'    => [ 'size' => 2 ],
			'range'      => [ 'px' => [ 'min' => 0.2, 'max' => 10, 'step' => 0.1 ] ],
			'render_type'=> 'template',
			'condition'  => [ '_eds_loop_animation!' => '' ],
			'selectors'  => [ '{{WRAPPER}}.eds-loop-el' => 'animation-duration: {{SIZE}}s;' ],
		] );

		$element->add_control( '_eds_loop_delay', [
			'label'     => __( 'Delay (seconds)', 'eds-global-settings' ),
			'type'      => Controls_Manager::SLIDER,
			'default'   => [ 'size' => 0 ],
			'range'     => [ 'px' => [ 'min' => 0, 'max' => 5, 'step' => 0.1 ] ],
			'condition' => [ '_eds_loop_animation!' => '' ],
			'selectors' => [ '{{WRAPPER}}.eds-loop-el' => 'animation-delay: {{SIZE}}s;' ],
		] );

		$element->add_control( '_eds_loop_intensity', [
			'label'       => __( 'Intensity', 'eds-global-settings' ),
			'type'        => Controls_Manager::SELECT,
			'default'     => 'medium',
			'render_type' => 'template',
			'options'     => [ 'low' => __( 'Low', 'eds-global-settings' ), 'medium' => __( 'Medium', 'eds-global-settings' ), 'high' => __( 'High', 'eds-global-settings' ) ],
			'condition'   => [ '_eds_loop_animation!' => '' ],
		] );

		$element->add_control( '_eds_loop_easing', [
			'label'     => __( 'Easing', 'eds-global-settings' ),
			'type'      => Controls_Manager::SELECT,
			'default'   => 'ease-in-out',
			'options'   => [ 'linear' => __( 'Linear', 'eds-global-settings' ), 'ease' => __( 'Ease', 'eds-global-settings' ), 'ease-in-out' => __( 'Ease In Out', 'eds-global-settings' ), 'ease-in' => __( 'Ease In', 'eds-global-settings' ), 'ease-out' => __( 'Ease Out', 'eds-global-settings' ) ],
			'condition' => [ '_eds_loop_animation!' => [ '', 'random' ] ],
			'selectors' => [ '{{WRAPPER}}.eds-loop-el' => 'animation-timing-function: {{VALUE}};' ],
		] );

		$element->add_control( '_eds_loop_hover_pause', [
			'label'        => __( 'Pause on Hover', 'eds-global-settings' ),
			'type'         => Controls_Manager::SWITCHER,
			'return_value' => 'yes',
			'render_type'  => 'template',
			'condition'    => [ '_eds_loop_animation!' => '' ],
		] );

		$element->end_controls_section();
	}

	// Runs on frontend AND in editor preview (render_type=template triggers PHP re-render).
	// Writes class + compact data-elp ONLY when an animation is selected.
	public function before_render( $element ): void {
		$s    = $element->get_settings_for_display();
		$anim = $s['_eds_loop_animation'] ?? '';
		if ( empty( $anim ) ) return;

		$intensity = $s['_eds_loop_intensity']   ?? 'medium';
		$hover     = ( $s['_eds_loop_hover_pause'] ?? '' ) === 'yes' ? 1 : 0;

		$element->add_render_attribute( '_wrapper', [
			'class'    => 'eds-loop-el eds-loop--' . esc_attr( $anim ) . ' eds-loop-i--' . esc_attr( $intensity ),
			'data-elp' => wp_json_encode( [ 'a' => $anim, 'i' => $intensity, 'h' => $hover ] ),
		] );
	}
}
