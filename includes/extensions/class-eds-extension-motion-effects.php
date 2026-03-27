<?php
/**
 * EDS Motion Effects Extension
 * @package EDS_Global_Settings
 * @since   2.4.0
 */
if ( ! defined( 'ABSPATH' ) ) exit;

use Elementor\Controls_Manager;

class EDS_Extension_Motion_Effects {

	public function __construct() {
		add_action( 'elementor/element/after_section_end', [ $this, 'add_controls' ], 10, 3 );
		add_action( 'elementor/frontend/before_render',   [ $this, 'before_render' ] );
		add_action( 'elementor/editor/before_enqueue_scripts', [ $this, 'enqueue_editor' ] );
		add_action( 'wp_enqueue_scripts',                 [ $this, 'enqueue_frontend' ] );
		add_action( 'elementor/preview/enqueue_scripts',  [ $this, 'enqueue_preview' ] );
	}

	public function enqueue_editor(): void {
		wp_enqueue_style( 'eds-motion-effects', EDS_PLUGIN_URL . 'includes/extensions/assets/css/eds-motion-effects.css', [], EDS_VERSION );
	}
	public function enqueue_frontend(): void {
		wp_enqueue_style( 'eds-motion-effects', EDS_PLUGIN_URL . 'includes/extensions/assets/css/eds-motion-effects.css', [], EDS_VERSION );
		wp_enqueue_script( 'eds-motion-effects', EDS_PLUGIN_URL . 'includes/extensions/assets/js/eds-motion-effects.js', [], EDS_VERSION, true );
	}
	public function enqueue_preview(): void {
		wp_enqueue_style( 'eds-motion-effects' );
		wp_enqueue_script( 'eds-motion-effects' );
	}

	public function add_controls( $element, $section_id, $args ): void {
		if ( '_section_responsive' !== $section_id ) return;

		$element->start_controls_section( '_eds_motion_section', [
			'label' => __( 'EDS Motion Effects', 'eds-global-settings' ),
			'tab'   => Controls_Manager::TAB_ADVANCED,
		] );

		// ── Scrolling Effects ─────────────────────────────────────────────────
		// render_type=template on the main toggle: changing it re-runs before_render
		// in the editor preview, rewriting data-eml. Sub-controls also need it
		// so their changes trigger the same re-render cycle.
		$element->add_control( '_eds_scroll_fx', [
			'label'        => __( 'Scrolling Effects', 'eds-global-settings' ),
			'type'         => Controls_Manager::SWITCHER,
			'return_value' => 'yes',
			'render_type'  => 'template',
		] );

		$element->add_control( '_eds_scroll_y_enable', [
			'label'        => __( 'Vertical Scroll', 'eds-global-settings' ),
			'type'         => Controls_Manager::POPOVER_TOGGLE,
			'return_value' => 'yes',
			'render_type'  => 'template',
			'condition'    => [ '_eds_scroll_fx' => 'yes' ],
		] );
		$element->start_popover();
		$element->add_control( '_eds_scroll_y_dir', [
			'label'       => __( 'Direction', 'eds-global-settings' ),
			'type'        => Controls_Manager::SELECT,
			'default'     => 'negative',
			'render_type' => 'template',
			'options'     => [ 'negative' => __( 'Up', 'eds-global-settings' ), 'positive' => __( 'Down', 'eds-global-settings' ) ],
			'condition'   => [ '_eds_scroll_fx' => 'yes', '_eds_scroll_y_enable' => 'yes' ],
		] );
		$element->add_control( '_eds_scroll_y_speed', [
			'label'       => __( 'Speed', 'eds-global-settings' ),
			'type'        => Controls_Manager::SLIDER,
			'default'     => [ 'size' => 4 ],
			'render_type' => 'template',
			'range'       => [ 'px' => [ 'min' => 0, 'max' => 10, 'step' => 0.1 ] ],
			'condition'   => [ '_eds_scroll_fx' => 'yes', '_eds_scroll_y_enable' => 'yes' ],
		] );
		$element->add_control( '_eds_scroll_y_viewport_enter', [
			'label'       => __( 'Viewport — Start (%)', 'eds-global-settings' ),
			'type'        => Controls_Manager::SLIDER,
			'default'     => [ 'size' => 0 ],
			'render_type' => 'template',
			'range'       => [ 'px' => [ 'min' => 0, 'max' => 100 ] ],
			'condition'   => [ '_eds_scroll_fx' => 'yes', '_eds_scroll_y_enable' => 'yes' ],
		] );
		$element->add_control( '_eds_scroll_y_viewport_leave', [
			'label'       => __( 'Viewport — End (%)', 'eds-global-settings' ),
			'type'        => Controls_Manager::SLIDER,
			'default'     => [ 'size' => 100 ],
			'render_type' => 'template',
			'range'       => [ 'px' => [ 'min' => 0, 'max' => 100 ] ],
			'condition'   => [ '_eds_scroll_fx' => 'yes', '_eds_scroll_y_enable' => 'yes' ],
		] );
		$element->end_popover();

		$element->add_control( '_eds_scroll_x_enable', [
			'label'        => __( 'Horizontal Scroll', 'eds-global-settings' ),
			'type'         => Controls_Manager::POPOVER_TOGGLE,
			'return_value' => 'yes',
			'render_type'  => 'template',
			'condition'    => [ '_eds_scroll_fx' => 'yes' ],
		] );
		$element->start_popover();
		$element->add_control( '_eds_scroll_x_dir', [
			'label'       => __( 'Direction', 'eds-global-settings' ),
			'type'        => Controls_Manager::SELECT,
			'default'     => 'positive',
			'render_type' => 'template',
			'options'     => [ 'positive' => __( 'Right', 'eds-global-settings' ), 'negative' => __( 'Left', 'eds-global-settings' ) ],
			'condition'   => [ '_eds_scroll_fx' => 'yes', '_eds_scroll_x_enable' => 'yes' ],
		] );
		$element->add_control( '_eds_scroll_x_speed', [
			'label'       => __( 'Speed', 'eds-global-settings' ),
			'type'        => Controls_Manager::SLIDER,
			'default'     => [ 'size' => 4 ],
			'render_type' => 'template',
			'range'       => [ 'px' => [ 'min' => 0, 'max' => 10, 'step' => 0.1 ] ],
			'condition'   => [ '_eds_scroll_fx' => 'yes', '_eds_scroll_x_enable' => 'yes' ],
		] );
		$element->end_popover();

		$element->add_control( '_eds_scroll_opacity_enable', [
			'label'        => __( 'Transparency', 'eds-global-settings' ),
			'type'         => Controls_Manager::POPOVER_TOGGLE,
			'return_value' => 'yes',
			'render_type'  => 'template',
			'condition'    => [ '_eds_scroll_fx' => 'yes' ],
		] );
		$element->start_popover();
		$element->add_control( '_eds_scroll_opacity_dir', [
			'label'       => __( 'Direction', 'eds-global-settings' ),
			'type'        => Controls_Manager::SELECT,
			'default'     => 'in-out',
			'render_type' => 'template',
			'options'     => [ 'in-out' => __( 'In & Out', 'eds-global-settings' ), 'in' => __( 'In', 'eds-global-settings' ), 'out' => __( 'Out', 'eds-global-settings' ) ],
			'condition'   => [ '_eds_scroll_fx' => 'yes', '_eds_scroll_opacity_enable' => 'yes' ],
		] );
		$element->add_control( '_eds_scroll_opacity_level', [
			'label'       => __( 'Level', 'eds-global-settings' ),
			'type'        => Controls_Manager::SLIDER,
			'default'     => [ 'size' => 10 ],
			'render_type' => 'template',
			'range'       => [ 'px' => [ 'min' => 0, 'max' => 10 ] ],
			'condition'   => [ '_eds_scroll_fx' => 'yes', '_eds_scroll_opacity_enable' => 'yes' ],
		] );
		$element->end_popover();

		$element->add_control( '_eds_scroll_blur_enable', [
			'label'        => __( 'Blur', 'eds-global-settings' ),
			'type'         => Controls_Manager::POPOVER_TOGGLE,
			'return_value' => 'yes',
			'render_type'  => 'template',
			'condition'    => [ '_eds_scroll_fx' => 'yes' ],
		] );
		$element->start_popover();
		$element->add_control( '_eds_scroll_blur_dir', [
			'label'       => __( 'Direction', 'eds-global-settings' ),
			'type'        => Controls_Manager::SELECT,
			'default'     => 'in-out',
			'render_type' => 'template',
			'options'     => [ 'in-out' => __( 'In & Out', 'eds-global-settings' ), 'in' => __( 'Blur In', 'eds-global-settings' ), 'out' => __( 'Blur Out', 'eds-global-settings' ) ],
			'condition'   => [ '_eds_scroll_fx' => 'yes', '_eds_scroll_blur_enable' => 'yes' ],
		] );
		$element->add_control( '_eds_scroll_blur_level', [
			'label'       => __( 'Level', 'eds-global-settings' ),
			'type'        => Controls_Manager::SLIDER,
			'default'     => [ 'size' => 7 ],
			'render_type' => 'template',
			'range'       => [ 'px' => [ 'min' => 0, 'max' => 10 ] ],
			'condition'   => [ '_eds_scroll_fx' => 'yes', '_eds_scroll_blur_enable' => 'yes' ],
		] );
		$element->end_popover();

		$element->add_control( '_eds_scroll_rotate_enable', [
			'label'        => __( 'Rotate', 'eds-global-settings' ),
			'type'         => Controls_Manager::POPOVER_TOGGLE,
			'return_value' => 'yes',
			'render_type'  => 'template',
			'condition'    => [ '_eds_scroll_fx' => 'yes' ],
		] );
		$element->start_popover();
		$element->add_control( '_eds_scroll_rotate_dir', [
			'label'       => __( 'Direction', 'eds-global-settings' ),
			'type'        => Controls_Manager::SELECT,
			'default'     => 'positive',
			'render_type' => 'template',
			'options'     => [ 'positive' => __( 'Clockwise', 'eds-global-settings' ), 'negative' => __( 'Counter-Clockwise', 'eds-global-settings' ) ],
			'condition'   => [ '_eds_scroll_fx' => 'yes', '_eds_scroll_rotate_enable' => 'yes' ],
		] );
		$element->add_control( '_eds_scroll_rotate_speed', [
			'label'       => __( 'Speed', 'eds-global-settings' ),
			'type'        => Controls_Manager::SLIDER,
			'default'     => [ 'size' => 1 ],
			'render_type' => 'template',
			'range'       => [ 'px' => [ 'min' => 0, 'max' => 10, 'step' => 0.1 ] ],
			'condition'   => [ '_eds_scroll_fx' => 'yes', '_eds_scroll_rotate_enable' => 'yes' ],
		] );
		$element->end_popover();

		$element->add_control( '_eds_scroll_scale_enable', [
			'label'        => __( 'Scale', 'eds-global-settings' ),
			'type'         => Controls_Manager::POPOVER_TOGGLE,
			'return_value' => 'yes',
			'render_type'  => 'template',
			'condition'    => [ '_eds_scroll_fx' => 'yes' ],
		] );
		$element->start_popover();
		$element->add_control( '_eds_scroll_scale_dir', [
			'label'       => __( 'Direction', 'eds-global-settings' ),
			'type'        => Controls_Manager::SELECT,
			'default'     => 'in',
			'render_type' => 'template',
			'options'     => [ 'in' => __( 'Scale Up', 'eds-global-settings' ), 'out' => __( 'Scale Down', 'eds-global-settings' ) ],
			'condition'   => [ '_eds_scroll_fx' => 'yes', '_eds_scroll_scale_enable' => 'yes' ],
		] );
		$element->add_control( '_eds_scroll_scale_speed', [
			'label'       => __( 'Speed', 'eds-global-settings' ),
			'type'        => Controls_Manager::SLIDER,
			'default'     => [ 'size' => 4 ],
			'render_type' => 'template',
			'range'       => [ 'px' => [ 'min' => 0, 'max' => 10, 'step' => 0.1 ] ],
			'condition'   => [ '_eds_scroll_fx' => 'yes', '_eds_scroll_scale_enable' => 'yes' ],
		] );
		$element->end_popover();

		// ── Mouse Effects ─────────────────────────────────────────────────────
		$element->add_control( '_eds_mouse_fx', [
			'label'        => __( 'Mouse Effects', 'eds-global-settings' ),
			'type'         => Controls_Manager::SWITCHER,
			'return_value' => 'yes',
			'separator'    => 'before',
			'render_type'  => 'template',
		] );

		$element->add_control( '_eds_mouse_track_enable', [
			'label'        => __( 'Mouse Track', 'eds-global-settings' ),
			'type'         => Controls_Manager::POPOVER_TOGGLE,
			'return_value' => 'yes',
			'render_type'  => 'template',
			'condition'    => [ '_eds_mouse_fx' => 'yes' ],
		] );
		$element->start_popover();
		$element->add_control( '_eds_mouse_track_dir', [
			'label'       => __( 'Direction', 'eds-global-settings' ),
			'type'        => Controls_Manager::SELECT,
			'default'     => 'direct',
			'render_type' => 'template',
			'options'     => [ 'direct' => __( 'Direct', 'eds-global-settings' ), 'opposite' => __( 'Opposite', 'eds-global-settings' ) ],
			'condition'   => [ '_eds_mouse_fx' => 'yes', '_eds_mouse_track_enable' => 'yes' ],
		] );
		$element->add_control( '_eds_mouse_track_speed', [
			'label'       => __( 'Speed', 'eds-global-settings' ),
			'type'        => Controls_Manager::SLIDER,
			'default'     => [ 'size' => 1 ],
			'render_type' => 'template',
			'range'       => [ 'px' => [ 'min' => 0, 'max' => 10, 'step' => 0.1 ] ],
			'condition'   => [ '_eds_mouse_fx' => 'yes', '_eds_mouse_track_enable' => 'yes' ],
		] );
		$element->end_popover();

		$element->add_control( '_eds_mouse_tilt_enable', [
			'label'        => __( 'Tilt', 'eds-global-settings' ),
			'type'         => Controls_Manager::POPOVER_TOGGLE,
			'return_value' => 'yes',
			'render_type'  => 'template',
			'condition'    => [ '_eds_mouse_fx' => 'yes' ],
		] );
		$element->start_popover();
		$element->add_control( '_eds_mouse_tilt_dir', [
			'label'       => __( 'Direction', 'eds-global-settings' ),
			'type'        => Controls_Manager::SELECT,
			'default'     => 'direct',
			'render_type' => 'template',
			'options'     => [ 'direct' => __( 'Direct', 'eds-global-settings' ), 'opposite' => __( 'Opposite', 'eds-global-settings' ) ],
			'condition'   => [ '_eds_mouse_fx' => 'yes', '_eds_mouse_tilt_enable' => 'yes' ],
		] );
		$element->add_control( '_eds_mouse_tilt_speed', [
			'label'       => __( 'Speed', 'eds-global-settings' ),
			'type'        => Controls_Manager::SLIDER,
			'default'     => [ 'size' => 4 ],
			'render_type' => 'template',
			'range'       => [ 'px' => [ 'min' => 0, 'max' => 10, 'step' => 0.1 ] ],
			'condition'   => [ '_eds_mouse_fx' => 'yes', '_eds_mouse_tilt_enable' => 'yes' ],
		] );
		$element->end_popover();

		// ── Sticky ────────────────────────────────────────────────────────────
		$element->add_control( '_eds_sticky', [
			'label'       => __( 'Sticky', 'eds-global-settings' ),
			'type'        => Controls_Manager::SELECT,
			'default'     => '',
			'separator'   => 'before',
			'render_type' => 'template',
			'options'     => [ '' => __( 'None', 'eds-global-settings' ), 'top' => __( 'Top', 'eds-global-settings' ), 'bottom' => __( 'Bottom', 'eds-global-settings' ) ],
		] );
		$element->add_control( '_eds_sticky_offset', [
			'label'       => __( 'Offset', 'eds-global-settings' ),
			'type'        => Controls_Manager::SLIDER,
			'default'     => [ 'size' => 0 ],
			'render_type' => 'template',
			'range'       => [ 'px' => [ 'min' => 0, 'max' => 500 ] ],
			'condition'   => [ '_eds_sticky!' => '' ],
		] );
		$element->add_control( '_eds_sticky_column', [
			'label'        => __( 'Stay Within Column', 'eds-global-settings' ),
			'description'  => __( 'Element stays sticky only within its parent container.', 'eds-global-settings' ),
			'type'         => Controls_Manager::SWITCHER,
			'return_value' => 'yes',
			'render_type'  => 'template',
			'condition'    => [ '_eds_sticky!' => '' ],
		] );
		$element->add_control( '_eds_sticky_on', [
			'label'       => __( 'Sticky On', 'eds-global-settings' ),
			'type'        => Controls_Manager::SELECT2,
			'multiple'    => true,
			'default'     => [ 'desktop', 'tablet' ],
			'render_type' => 'template',
			'options'     => [ 'desktop' => __( 'Desktop', 'eds-global-settings' ), 'tablet' => __( 'Tablet', 'eds-global-settings' ), 'mobile' => __( 'Mobile', 'eds-global-settings' ) ],
			'condition'   => [ '_eds_sticky!' => '' ],
		] );

		$element->end_controls_section();
	}

	// before_render: writes ONE compact attribute ONLY when effects are configured.
	// Runs on frontend AND in editor preview when render_type=template triggers re-render.
	public function before_render( $element ): void {
		$s = $element->get_settings_for_display();
		$has_scroll = ( $s['_eds_scroll_fx'] ?? '' ) === 'yes';
		$has_mouse  = ( $s['_eds_mouse_fx']  ?? '' ) === 'yes';
		$sticky     = $s['_eds_sticky'] ?? '';
		if ( ! $has_scroll && ! $has_mouse && empty( $sticky ) ) return;

		$cfg = [];
		if ( $has_scroll ) {
			$cfg['sx'] = 1;
			if ( ( $s['_eds_scroll_y_enable'] ?? '' ) === 'yes' ) $cfg['sy'] = [ 'd' => $s['_eds_scroll_y_dir'] ?? 'negative', 's' => (float)( $s['_eds_scroll_y_speed']['size'] ?? 4 ), 'e' => (int)( $s['_eds_scroll_y_viewport_enter']['size'] ?? 0 ), 'l' => (int)( $s['_eds_scroll_y_viewport_leave']['size'] ?? 100 ) ];
			if ( ( $s['_eds_scroll_x_enable'] ?? '' ) === 'yes' ) $cfg['sX'] = [ 'd' => $s['_eds_scroll_x_dir'] ?? 'positive', 's' => (float)( $s['_eds_scroll_x_speed']['size'] ?? 4 ) ];
			if ( ( $s['_eds_scroll_opacity_enable'] ?? '' ) === 'yes' ) $cfg['so'] = [ 'd' => $s['_eds_scroll_opacity_dir'] ?? 'in-out', 'l' => (float)( $s['_eds_scroll_opacity_level']['size'] ?? 10 ) ];
			if ( ( $s['_eds_scroll_blur_enable'] ?? '' ) === 'yes' ) $cfg['sb'] = [ 'd' => $s['_eds_scroll_blur_dir'] ?? 'in-out', 'l' => (float)( $s['_eds_scroll_blur_level']['size'] ?? 7 ) ];
			if ( ( $s['_eds_scroll_rotate_enable'] ?? '' ) === 'yes' ) $cfg['sr'] = [ 'd' => $s['_eds_scroll_rotate_dir'] ?? 'positive', 's' => (float)( $s['_eds_scroll_rotate_speed']['size'] ?? 1 ) ];
			if ( ( $s['_eds_scroll_scale_enable'] ?? '' ) === 'yes' ) $cfg['ss'] = [ 'd' => $s['_eds_scroll_scale_dir'] ?? 'in', 's' => (float)( $s['_eds_scroll_scale_speed']['size'] ?? 4 ) ];
		}
		if ( $has_mouse ) {
			$cfg['mx'] = 1;
			if ( ( $s['_eds_mouse_track_enable'] ?? '' ) === 'yes' ) $cfg['mt'] = [ 'd' => $s['_eds_mouse_track_dir'] ?? 'direct', 's' => (float)( $s['_eds_mouse_track_speed']['size'] ?? 1 ) ];
			if ( ( $s['_eds_mouse_tilt_enable']  ?? '' ) === 'yes' ) $cfg['mi'] = [ 'd' => $s['_eds_mouse_tilt_dir'] ?? 'direct',  's' => (float)( $s['_eds_mouse_tilt_speed']['size']  ?? 4 ) ];
		}
		if ( ! empty( $sticky ) ) {
			$cfg['st']  = $sticky;
			$cfg['sof'] = (int)( $s['_eds_sticky_offset']['size'] ?? 0 );
			$cfg['sc']  = ( $s['_eds_sticky_column'] ?? '' ) === 'yes' ? 1 : 0;
			$cfg['son'] = $s['_eds_sticky_on'] ?? [ 'desktop', 'tablet' ];
		}
		$element->add_render_attribute( '_wrapper', 'data-eml', wp_json_encode( $cfg ) );
	}
}
