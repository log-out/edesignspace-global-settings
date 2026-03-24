<?php
/**
 * EDS Motion Effects Extension
 *
 * Adds Scrolling Effects, Mouse Effects, and Sticky positioning to every
 * Elementor element including sections, containers, columns, and widgets.
 *
 * @package EDS_Global_Settings
 * @since   2.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Elementor\Controls_Manager;

class EDS_Extension_Motion_Effects {

	/**
	 * Section IDs that appear in the Advanced tab across all element types.
	 * We inject AFTER _section_responsive which is present on every element.
	 */
	const TARGET_SECTIONS = [
		'_section_responsive',  // widgets + all elements
	];

	public function __construct() {
		add_action( 'elementor/element/after_section_end',  [ $this, 'add_controls' ], 10, 3 );
		add_action( 'wp_enqueue_scripts',                   [ $this, 'enqueue_assets' ] );
		add_action( 'elementor/preview/enqueue_scripts',    [ $this, 'enqueue_assets' ] );
	}

	// ─── Enqueue ──────────────────────────────────────────────────────────────

	public function enqueue_assets(): void {
		wp_enqueue_style( 'eds-motion-effects',
			EDS_PLUGIN_URL . 'includes/extensions/assets/css/eds-motion-effects.css', [], EDS_VERSION );
		wp_enqueue_script( 'eds-motion-effects',
			EDS_PLUGIN_URL . 'includes/extensions/assets/js/eds-motion-effects.js',
			[ 'jquery', 'elementor-frontend' ], EDS_VERSION, true );
	}

	// ─── Controls ─────────────────────────────────────────────────────────────

	public function add_controls( $element, $section_id, $args ): void {
		// Fire only after _section_responsive (present on ALL element types).
		if ( '_section_responsive' !== $section_id ) {
			return;
		}

		$element->start_controls_section( '_eds_motion_section', [
			'label' => __( 'EDS Motion Effects', 'eds-global-settings' ),
			'tab'   => Controls_Manager::TAB_ADVANCED,
		] );

		// ── Scrolling Effects ──────────────────────────────────────────────────
		$element->add_control( '_eds_scroll_fx', [
			'label'              => __( 'Scrolling Effects', 'eds-global-settings' ),
			'type'               => Controls_Manager::SWITCHER,
			'return_value'       => 'yes',
			'render_type'        => 'none',
			'frontend_available' => true,
		] );

		// — Vertical Scroll popover ————————————————————————————————————————————
		$element->add_control( '_eds_scroll_y_enable', [
			'label'              => __( 'Vertical Scroll', 'eds-global-settings' ),
			'type'               => Controls_Manager::POPOVER_TOGGLE,
			'return_value'       => 'yes',
			'condition'          => [ '_eds_scroll_fx' => 'yes' ],
			'render_type'        => 'none',
			'frontend_available' => true,
		] );
		$element->start_popover();
		$element->add_control( '_eds_scroll_y_dir', [
			'label'              => __( 'Direction', 'eds-global-settings' ),
			'type'               => Controls_Manager::SELECT,
			'default'            => 'negative',
			'options'            => [ 'negative' => __( 'Up', 'eds-global-settings' ), 'positive' => __( 'Down', 'eds-global-settings' ) ],
			'condition'          => [ '_eds_scroll_fx' => 'yes', '_eds_scroll_y_enable' => 'yes' ],
			'render_type'        => 'none',
			'frontend_available' => true,
		] );
		$element->add_control( '_eds_scroll_y_speed', [
			'label'              => __( 'Speed', 'eds-global-settings' ),
			'type'               => Controls_Manager::SLIDER,
			'default'            => [ 'size' => 4 ],
			'range'              => [ 'px' => [ 'min' => 0, 'max' => 10, 'step' => 0.1 ] ],
			'condition'          => [ '_eds_scroll_fx' => 'yes', '_eds_scroll_y_enable' => 'yes' ],
			'render_type'        => 'none',
			'frontend_available' => true,
		] );
		$element->add_control( '_eds_scroll_y_viewport_enter', [
			'label'              => __( 'Viewport — Start (%)', 'eds-global-settings' ),
			'type'               => Controls_Manager::SLIDER,
			'default'            => [ 'size' => 0 ],
			'range'              => [ 'px' => [ 'min' => 0, 'max' => 100 ] ],
			'condition'          => [ '_eds_scroll_fx' => 'yes', '_eds_scroll_y_enable' => 'yes' ],
			'render_type'        => 'none',
			'frontend_available' => true,
		] );
		$element->add_control( '_eds_scroll_y_viewport_leave', [
			'label'              => __( 'Viewport — End (%)', 'eds-global-settings' ),
			'type'               => Controls_Manager::SLIDER,
			'default'            => [ 'size' => 100 ],
			'range'              => [ 'px' => [ 'min' => 0, 'max' => 100 ] ],
			'condition'          => [ '_eds_scroll_fx' => 'yes', '_eds_scroll_y_enable' => 'yes' ],
			'render_type'        => 'none',
			'frontend_available' => true,
		] );
		$element->end_popover();

		// — Horizontal Scroll popover ──────────────────────────────────────────
		$element->add_control( '_eds_scroll_x_enable', [
			'label'              => __( 'Horizontal Scroll', 'eds-global-settings' ),
			'type'               => Controls_Manager::POPOVER_TOGGLE,
			'return_value'       => 'yes',
			'condition'          => [ '_eds_scroll_fx' => 'yes' ],
			'render_type'        => 'none',
			'frontend_available' => true,
		] );
		$element->start_popover();
		$element->add_control( '_eds_scroll_x_dir', [
			'label'              => __( 'Direction', 'eds-global-settings' ),
			'type'               => Controls_Manager::SELECT,
			'default'            => 'positive',
			'options'            => [ 'positive' => __( 'Right', 'eds-global-settings' ), 'negative' => __( 'Left', 'eds-global-settings' ) ],
			'condition'          => [ '_eds_scroll_fx' => 'yes', '_eds_scroll_x_enable' => 'yes' ],
			'render_type'        => 'none',
			'frontend_available' => true,
		] );
		$element->add_control( '_eds_scroll_x_speed', [
			'label'              => __( 'Speed', 'eds-global-settings' ),
			'type'               => Controls_Manager::SLIDER,
			'default'            => [ 'size' => 4 ],
			'range'              => [ 'px' => [ 'min' => 0, 'max' => 10, 'step' => 0.1 ] ],
			'condition'          => [ '_eds_scroll_fx' => 'yes', '_eds_scroll_x_enable' => 'yes' ],
			'render_type'        => 'none',
			'frontend_available' => true,
		] );
		$element->end_popover();

		// — Transparency popover ───────────────────────────────────────────────
		$element->add_control( '_eds_scroll_opacity_enable', [
			'label'              => __( 'Transparency', 'eds-global-settings' ),
			'type'               => Controls_Manager::POPOVER_TOGGLE,
			'return_value'       => 'yes',
			'condition'          => [ '_eds_scroll_fx' => 'yes' ],
			'render_type'        => 'none',
			'frontend_available' => true,
		] );
		$element->start_popover();
		$element->add_control( '_eds_scroll_opacity_dir', [
			'label'              => __( 'Direction', 'eds-global-settings' ),
			'type'               => Controls_Manager::SELECT,
			'default'            => 'in-out',
			'options'            => [ 'in-out' => __( 'In & Out', 'eds-global-settings' ), 'in' => __( 'In', 'eds-global-settings' ), 'out' => __( 'Out', 'eds-global-settings' ) ],
			'condition'          => [ '_eds_scroll_fx' => 'yes', '_eds_scroll_opacity_enable' => 'yes' ],
			'render_type'        => 'none',
			'frontend_available' => true,
		] );
		$element->add_control( '_eds_scroll_opacity_level', [
			'label'              => __( 'Level', 'eds-global-settings' ),
			'type'               => Controls_Manager::SLIDER,
			'default'            => [ 'size' => 10 ],
			'range'              => [ 'px' => [ 'min' => 0, 'max' => 10 ] ],
			'condition'          => [ '_eds_scroll_fx' => 'yes', '_eds_scroll_opacity_enable' => 'yes' ],
			'render_type'        => 'none',
			'frontend_available' => true,
		] );
		$element->end_popover();

		// — Blur popover ───────────────────────────────────────────────────────
		$element->add_control( '_eds_scroll_blur_enable', [
			'label'              => __( 'Blur', 'eds-global-settings' ),
			'type'               => Controls_Manager::POPOVER_TOGGLE,
			'return_value'       => 'yes',
			'condition'          => [ '_eds_scroll_fx' => 'yes' ],
			'render_type'        => 'none',
			'frontend_available' => true,
		] );
		$element->start_popover();
		$element->add_control( '_eds_scroll_blur_dir', [
			'label'              => __( 'Direction', 'eds-global-settings' ),
			'type'               => Controls_Manager::SELECT,
			'default'            => 'in-out',
			'options'            => [ 'in-out' => __( 'In & Out', 'eds-global-settings' ), 'in' => __( 'Blur In', 'eds-global-settings' ), 'out' => __( 'Blur Out', 'eds-global-settings' ) ],
			'condition'          => [ '_eds_scroll_fx' => 'yes', '_eds_scroll_blur_enable' => 'yes' ],
			'render_type'        => 'none',
			'frontend_available' => true,
		] );
		$element->add_control( '_eds_scroll_blur_level', [
			'label'              => __( 'Level', 'eds-global-settings' ),
			'type'               => Controls_Manager::SLIDER,
			'default'            => [ 'size' => 7 ],
			'range'              => [ 'px' => [ 'min' => 0, 'max' => 10 ] ],
			'condition'          => [ '_eds_scroll_fx' => 'yes', '_eds_scroll_blur_enable' => 'yes' ],
			'render_type'        => 'none',
			'frontend_available' => true,
		] );
		$element->end_popover();

		// — Rotate popover ─────────────────────────────────────────────────────
		$element->add_control( '_eds_scroll_rotate_enable', [
			'label'              => __( 'Rotate', 'eds-global-settings' ),
			'type'               => Controls_Manager::POPOVER_TOGGLE,
			'return_value'       => 'yes',
			'condition'          => [ '_eds_scroll_fx' => 'yes' ],
			'render_type'        => 'none',
			'frontend_available' => true,
		] );
		$element->start_popover();
		$element->add_control( '_eds_scroll_rotate_dir', [
			'label'              => __( 'Direction', 'eds-global-settings' ),
			'type'               => Controls_Manager::SELECT,
			'default'            => 'positive',
			'options'            => [ 'positive' => __( 'Clockwise', 'eds-global-settings' ), 'negative' => __( 'Counter-Clockwise', 'eds-global-settings' ) ],
			'condition'          => [ '_eds_scroll_fx' => 'yes', '_eds_scroll_rotate_enable' => 'yes' ],
			'render_type'        => 'none',
			'frontend_available' => true,
		] );
		$element->add_control( '_eds_scroll_rotate_speed', [
			'label'              => __( 'Speed', 'eds-global-settings' ),
			'type'               => Controls_Manager::SLIDER,
			'default'            => [ 'size' => 1 ],
			'range'              => [ 'px' => [ 'min' => 0, 'max' => 10, 'step' => 0.1 ] ],
			'condition'          => [ '_eds_scroll_fx' => 'yes', '_eds_scroll_rotate_enable' => 'yes' ],
			'render_type'        => 'none',
			'frontend_available' => true,
		] );
		$element->end_popover();

		// — Scale popover ──────────────────────────────────────────────────────
		$element->add_control( '_eds_scroll_scale_enable', [
			'label'              => __( 'Scale', 'eds-global-settings' ),
			'type'               => Controls_Manager::POPOVER_TOGGLE,
			'return_value'       => 'yes',
			'condition'          => [ '_eds_scroll_fx' => 'yes' ],
			'render_type'        => 'none',
			'frontend_available' => true,
		] );
		$element->start_popover();
		$element->add_control( '_eds_scroll_scale_dir', [
			'label'              => __( 'Direction', 'eds-global-settings' ),
			'type'               => Controls_Manager::SELECT,
			'default'            => 'in',
			'options'            => [ 'in' => __( 'Scale Up', 'eds-global-settings' ), 'out' => __( 'Scale Down', 'eds-global-settings' ) ],
			'condition'          => [ '_eds_scroll_fx' => 'yes', '_eds_scroll_scale_enable' => 'yes' ],
			'render_type'        => 'none',
			'frontend_available' => true,
		] );
		$element->add_control( '_eds_scroll_scale_speed', [
			'label'              => __( 'Speed', 'eds-global-settings' ),
			'type'               => Controls_Manager::SLIDER,
			'default'            => [ 'size' => 4 ],
			'range'              => [ 'px' => [ 'min' => 0, 'max' => 10, 'step' => 0.1 ] ],
			'condition'          => [ '_eds_scroll_fx' => 'yes', '_eds_scroll_scale_enable' => 'yes' ],
			'render_type'        => 'none',
			'frontend_available' => true,
		] );
		$element->end_popover();

		// ── Mouse Effects ──────────────────────────────────────────────────────
		$element->add_control( '_eds_mouse_fx', [
			'label'              => __( 'Mouse Effects', 'eds-global-settings' ),
			'type'               => Controls_Manager::SWITCHER,
			'return_value'       => 'yes',
			'separator'          => 'before',
			'render_type'        => 'none',
			'frontend_available' => true,
		] );

		// — Mouse Track popover ────────────────────────────────────────────────
		$element->add_control( '_eds_mouse_track_enable', [
			'label'              => __( 'Mouse Track', 'eds-global-settings' ),
			'type'               => Controls_Manager::POPOVER_TOGGLE,
			'return_value'       => 'yes',
			'condition'          => [ '_eds_mouse_fx' => 'yes' ],
			'render_type'        => 'none',
			'frontend_available' => true,
		] );
		$element->start_popover();
		$element->add_control( '_eds_mouse_track_dir', [
			'label'              => __( 'Direction', 'eds-global-settings' ),
			'type'               => Controls_Manager::SELECT,
			'default'            => 'direct',
			'options'            => [ 'direct' => __( 'Direct', 'eds-global-settings' ), 'opposite' => __( 'Opposite', 'eds-global-settings' ) ],
			'condition'          => [ '_eds_mouse_fx' => 'yes', '_eds_mouse_track_enable' => 'yes' ],
			'render_type'        => 'none',
			'frontend_available' => true,
		] );
		$element->add_control( '_eds_mouse_track_speed', [
			'label'              => __( 'Speed', 'eds-global-settings' ),
			'type'               => Controls_Manager::SLIDER,
			'default'            => [ 'size' => 1 ],
			'range'              => [ 'px' => [ 'min' => 0, 'max' => 10, 'step' => 0.1 ] ],
			'condition'          => [ '_eds_mouse_fx' => 'yes', '_eds_mouse_track_enable' => 'yes' ],
			'render_type'        => 'none',
			'frontend_available' => true,
		] );
		$element->end_popover();

		// — Tilt popover ───────────────────────────────────────────────────────
		$element->add_control( '_eds_mouse_tilt_enable', [
			'label'              => __( 'Tilt', 'eds-global-settings' ),
			'type'               => Controls_Manager::POPOVER_TOGGLE,
			'return_value'       => 'yes',
			'condition'          => [ '_eds_mouse_fx' => 'yes' ],
			'render_type'        => 'none',
			'frontend_available' => true,
		] );
		$element->start_popover();
		$element->add_control( '_eds_mouse_tilt_dir', [
			'label'              => __( 'Direction', 'eds-global-settings' ),
			'type'               => Controls_Manager::SELECT,
			'default'            => 'direct',
			'options'            => [ 'direct' => __( 'Direct', 'eds-global-settings' ), 'opposite' => __( 'Opposite', 'eds-global-settings' ) ],
			'condition'          => [ '_eds_mouse_fx' => 'yes', '_eds_mouse_tilt_enable' => 'yes' ],
			'render_type'        => 'none',
			'frontend_available' => true,
		] );
		$element->add_control( '_eds_mouse_tilt_speed', [
			'label'              => __( 'Speed', 'eds-global-settings' ),
			'type'               => Controls_Manager::SLIDER,
			'default'            => [ 'size' => 4 ],
			'range'              => [ 'px' => [ 'min' => 0, 'max' => 10, 'step' => 0.1 ] ],
			'condition'          => [ '_eds_mouse_fx' => 'yes', '_eds_mouse_tilt_enable' => 'yes' ],
			'render_type'        => 'none',
			'frontend_available' => true,
		] );
		$element->end_popover();

		// ── Sticky ─────────────────────────────────────────────────────────────
		$element->add_control( '_eds_sticky', [
			'label'     => __( 'Sticky', 'eds-global-settings' ),
			'type'      => Controls_Manager::SELECT,
			'default'   => '',
			'separator' => 'before',
			'options'   => [
				''       => __( 'None', 'eds-global-settings' ),
				'top'    => __( 'Top', 'eds-global-settings' ),
				'bottom' => __( 'Bottom', 'eds-global-settings' ),
			],
			'render_type'        => 'none',
			'frontend_available' => true,
		] );
		$element->add_control( '_eds_sticky_offset', [
			'label'     => __( 'Offset', 'eds-global-settings' ),
			'type'      => Controls_Manager::SLIDER,
			'default'   => [ 'size' => 0 ],
			'range'     => [ 'px' => [ 'min' => 0, 'max' => 500 ] ],
			'condition' => [ '_eds_sticky!' => '' ],
			'frontend_available' => true,
		] );
		$element->add_control( '_eds_sticky_column', [
			'label'        => __( 'Stay Within Column', 'eds-global-settings' ),
			'description'  => __( 'Element stays sticky only within its parent container.', 'eds-global-settings' ),
			'type'         => Controls_Manager::SWITCHER,
			'return_value' => 'yes',
			'condition'    => [ '_eds_sticky!' => '' ],
			'frontend_available' => true,
		] );
		$element->add_control( '_eds_sticky_on', [
			'label'    => __( 'Sticky On', 'eds-global-settings' ),
			'type'     => Controls_Manager::SELECT2,
			'multiple' => true,
			'default'  => [ 'desktop', 'tablet' ],
			'options'  => [
				'desktop' => __( 'Desktop', 'eds-global-settings' ),
				'tablet'  => __( 'Tablet', 'eds-global-settings' ),
				'mobile'  => __( 'Mobile', 'eds-global-settings' ),
			],
			'condition' => [ '_eds_sticky!' => '' ],
			'frontend_available' => true,
		] );

		$element->end_controls_section();
	}

	// ─── Before render ────────────────────────────────────────────────────────

}
