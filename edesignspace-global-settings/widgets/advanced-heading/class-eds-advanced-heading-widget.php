<?php
/**
 * EDS Advanced Heading Widget
 *
 * Animated/advanced heading with before text, animated text, and after text.
 * Supports: Typing, Rotating Words, Highlighted, Wave, Slide, Zoom, Flip, Fade.
 *
 * @package EDS_Global_Settings
 * @since   1.9.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Elementor\Controls_Manager;
use Elementor\Repeater;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Text_Shadow;
use Elementor\Group_Control_Text_Stroke;
use Elementor\Core\Kits\Documents\Tabs\Global_Typography;
use Elementor\Core\Kits\Documents\Tabs\Global_Colors;

class EDS_Advanced_Heading_Widget extends \Elementor\Widget_Base {

	public function get_name(): string         { return 'eds-advanced-heading'; }
	public function get_title(): string        { return __( 'EDS Advanced Heading', 'eds-global-settings' ); }
	public function get_icon(): string         { return 'eicon-animated-headline'; }
	public function get_categories(): array    { return [ 'eds-elements', 'general' ]; }
	public function get_keywords(): array      { return [ 'heading', 'animated', 'advanced', 'typing', 'rotate', 'highlight', 'wave', 'text', 'eds' ]; }
	public function get_style_depends(): array  { return [ 'eds-widget-advanced-heading' ]; }
	public function get_script_depends(): array { return [ 'eds-widget-advanced-heading' ]; }

	// ─── Controls ─────────────────────────────────────────────────────────────

	protected function register_controls(): void {
		$this->section_content();
		$this->section_style_before();
		$this->section_style_animated();
		$this->section_style_after();
	}

	// ── Content ────────────────────────────────────────────────────────────────

	private function section_content(): void {
		$this->start_controls_section( 'section_content', [
			'label' => __( 'Heading', 'eds-global-settings' ),
			'tab'   => Controls_Manager::TAB_CONTENT,
		] );

		$this->add_control( 'heading_tag', [
			'label'   => __( 'HTML Tag', 'eds-global-settings' ),
			'type'    => Controls_Manager::SELECT,
			'default' => 'h2',
			'options' => [
				'h1'   => 'H1',
				'h2'   => 'H2',
				'h3'   => 'H3',
				'h4'   => 'H4',
				'h5'   => 'H5',
				'h6'   => 'H6',
				'div'  => 'DIV',
				'p'    => 'P',
			],
		] );

		$this->add_responsive_control( 'align', [
			'label'     => __( 'Alignment', 'eds-global-settings' ),
			'type'      => Controls_Manager::CHOOSE,
			'options'   => [
				'left'   => [ 'title' => __( 'Left',   'eds-global-settings' ), 'icon' => 'eicon-text-align-left' ],
				'center' => [ 'title' => __( 'Center', 'eds-global-settings' ), 'icon' => 'eicon-text-align-center' ],
				'right'  => [ 'title' => __( 'Right',  'eds-global-settings' ), 'icon' => 'eicon-text-align-right' ],
			],
			'default'   => 'center',
			'selectors' => [ '{{WRAPPER}} .eah-wrap' => 'text-align: {{VALUE}};' ],
		] );

		$this->add_control( 'before_text', [
			'label'       => __( 'Before Text', 'eds-global-settings' ),
			'type'        => Controls_Manager::TEXT,
			'default'     => __( 'We build', 'eds-global-settings' ),
			'label_block' => true,
			'dynamic'     => [ 'active' => true ],
			'separator'   => 'before',
		] );

		// ── Animation style ─────────────────────────────────────────────────

		$this->add_control( 'animation_type', [
			'label'   => __( 'Animation Type', 'eds-global-settings' ),
			'type'    => Controls_Manager::SELECT,
			'default' => 'typing',
			'options' => [
				'typing'      => __( 'Typing',            'eds-global-settings' ),
				'rotating'    => __( 'Rotating Words',    'eds-global-settings' ),
				'highlighted' => __( 'Highlighted',       'eds-global-settings' ),
				'wave'        => __( 'Wave',              'eds-global-settings' ),
				'slide'       => __( 'Slide Up',          'eds-global-settings' ),
				'zoom'        => __( 'Zoom In',           'eds-global-settings' ),
				'flip'        => __( 'Flip',              'eds-global-settings' ),
				'fade'        => __( 'Fade In/Out',       'eds-global-settings' ),
				'static'      => __( 'Static (No Animation)', 'eds-global-settings' ),
			],
		] );

		// Animated words (for rotating/typing/slide/etc).
		$repeater = new Repeater();
		$repeater->add_control( 'word', [
			'label'       => __( 'Word', 'eds-global-settings' ),
			'type'        => Controls_Manager::TEXT,
			'default'     => __( 'Websites', 'eds-global-settings' ),
			'label_block' => true,
		] );

		$this->add_control( 'animated_words', [
			'label'       => __( 'Animated Words', 'eds-global-settings' ),
			'type'        => Controls_Manager::REPEATER,
			'fields'      => $repeater->get_controls(),
			'default'     => [
				[ 'word' => __( 'Websites',         'eds-global-settings' ) ],
				[ 'word' => __( 'Applications',     'eds-global-settings' ) ],
				[ 'word' => __( 'Experiences',      'eds-global-settings' ) ],
			],
			'title_field' => '{{{ word }}}',
			'condition'   => [ 'animation_type!' => [ 'highlighted', 'wave', 'static' ] ],
		] );

		// Single animated text for highlighted/wave/static.
		$this->add_control( 'animated_text', [
			'label'       => __( 'Animated Text', 'eds-global-settings' ),
			'type'        => Controls_Manager::TEXT,
			'default'     => __( 'Beautiful Websites', 'eds-global-settings' ),
			'label_block' => true,
			'condition'   => [ 'animation_type' => [ 'highlighted', 'wave', 'static' ] ],
			'dynamic'     => [ 'active' => true ],
		] );

		// Highlighted shape type.
		$this->add_control( 'highlight_type', [
			'label'     => __( 'Highlight Shape', 'eds-global-settings' ),
			'type'      => Controls_Manager::SELECT,
			'default'   => 'underline',
			'options'   => [
				'underline'       => __( 'Underline',         'eds-global-settings' ),
				'double-underline'=> __( 'Double Underline',  'eds-global-settings' ),
				'strikethrough'   => __( 'Strikethrough',     'eds-global-settings' ),
				'background'      => __( 'Background',        'eds-global-settings' ),
				'frame'           => __( 'Frame / Box',       'eds-global-settings' ),
				'circle'          => __( 'Circle',            'eds-global-settings' ),
			],
			'condition' => [ 'animation_type' => 'highlighted' ],
		] );

		$this->add_control( 'after_text', [
			'label'       => __( 'After Text', 'eds-global-settings' ),
			'type'        => Controls_Manager::TEXT,
			'default'     => '',
			'label_block' => true,
			'dynamic'     => [ 'active' => true ],
			'separator'   => 'before',
		] );

		// Animation settings.
		$this->add_control( 'speed', [
			'label'      => __( 'Animation Speed (ms)', 'eds-global-settings' ),
			'type'       => Controls_Manager::SLIDER,
			'default'    => [ 'size' => 3000 ],
			'range'      => [ 'px' => [ 'min' => 500, 'max' => 10000, 'step' => 100 ] ],
			'separator'  => 'before',
			'condition'  => [ 'animation_type!' => [ 'highlighted', 'wave', 'static' ] ],
		] );

		$this->add_control( 'typing_speed', [
			'label'     => __( 'Typing Speed (ms per char)', 'eds-global-settings' ),
			'type'      => Controls_Manager::SLIDER,
			'default'   => [ 'size' => 80 ],
			'range'     => [ 'px' => [ 'min' => 20, 'max' => 300 ] ],
			'condition' => [ 'animation_type' => 'typing' ],
		] );

		$this->add_control( 'cursor', [
			'label'        => __( 'Show Cursor', 'eds-global-settings' ),
			'type'         => Controls_Manager::SWITCHER,
			'default'      => 'yes',
			'return_value' => 'yes',
			'condition'    => [ 'animation_type' => 'typing' ],
		] );

		$this->add_control( 'loop', [
			'label'        => __( 'Loop', 'eds-global-settings' ),
			'type'         => Controls_Manager::SWITCHER,
			'default'      => 'yes',
			'return_value' => 'yes',
			'condition'    => [ 'animation_type!' => [ 'highlighted', 'wave', 'static' ] ],
		] );

		$this->end_controls_section();
	}

	// ── Style: Before Text ────────────────────────────────────────────────────

	private function section_style_before(): void {
		$this->start_controls_section( 'section_style_before', [
			'label' => __( 'Before Text', 'eds-global-settings' ),
			'tab'   => Controls_Manager::TAB_STYLE,
		] );

		$this->add_group_control( Group_Control_Typography::get_type(), [
			'name'     => 'before_typography',
			'selector' => '{{WRAPPER}} .eah-before',
			'global'   => [ 'default' => Global_Typography::TYPOGRAPHY_PRIMARY ],
		] );

		$this->add_control( 'before_color', [
			'label'     => __( 'Colour', 'eds-global-settings' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => [ '{{WRAPPER}} .eah-before' => 'color: {{VALUE}};' ],
		] );

		$this->add_group_control( Group_Control_Text_Shadow::get_type(), [
			'name'     => 'before_text_shadow',
			'selector' => '{{WRAPPER}} .eah-before',
		] );

		$this->add_responsive_control( 'before_gap', [
			'label'      => __( 'Gap After', 'eds-global-settings' ),
			'type'       => Controls_Manager::SLIDER,
			'size_units' => [ 'px', 'em' ],
			'default'    => [ 'size' => 8, 'unit' => 'px' ],
			'selectors'  => [ '{{WRAPPER}} .eah-before' => 'margin-inline-end: {{SIZE}}{{UNIT}};' ],
		] );

		$this->end_controls_section();
	}

	// ── Style: Animated Text ──────────────────────────────────────────────────

	private function section_style_animated(): void {
		$this->start_controls_section( 'section_style_animated', [
			'label' => __( 'Animated Text', 'eds-global-settings' ),
			'tab'   => Controls_Manager::TAB_STYLE,
		] );

		$this->add_group_control( Group_Control_Typography::get_type(), [
			'name'     => 'animated_typography',
			'selector' => '{{WRAPPER}} .eah-animated, {{WRAPPER}} .eah-char',
			'global'   => [ 'default' => Global_Typography::TYPOGRAPHY_PRIMARY ],
		] );

		$this->add_control( 'animated_color', [
			'label'     => __( 'Text Colour', 'eds-global-settings' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => [
				'{{WRAPPER}} .eah-animated'  => 'color: {{VALUE}};',
				'{{WRAPPER}} .eah-char'      => 'color: {{VALUE}};',
				'{{WRAPPER}} .eah-cursor'    => 'background-color: {{VALUE}};',
			],
		] );

		$this->add_group_control( Group_Control_Text_Shadow::get_type(), [
			'name'     => 'animated_text_shadow',
			'selector' => '{{WRAPPER}} .eah-animated, {{WRAPPER}} .eah-char',
		] );

		// Highlight colour (used for underline/bg/frame/circle).
		$this->add_control( 'highlight_bg_color', [
			'label'     => __( 'Background Colour', 'eds-global-settings' ),
			'type'      => Controls_Manager::COLOR,
			'default'   => 'rgba(99,102,241,0.2)',
			'selectors' => [
				'{{WRAPPER}} .eah-highlight-bg' => 'background: {{VALUE}}; opacity: 1;',
			],
			'condition' => [ 'animation_type' => 'highlighted', 'highlight_type' => 'background' ],
		] );

		$this->add_control( 'highlight_color', [
			'label'     => __( 'Highlight / Decoration Colour', 'eds-global-settings' ),
			'type'      => Controls_Manager::COLOR,
			'default'   => '#6366f1',
			'selectors' => [
				'{{WRAPPER}} .eah-highlight-line'   => 'background: {{VALUE}};',
				'{{WRAPPER}} .eah-highlight-frame'  => 'border-color: {{VALUE}};',
				'{{WRAPPER}} .eah-highlight-circle' => 'border-color: {{VALUE}};',
			],
			'separator' => 'before',
		] );

		$this->add_control( 'highlight_width', [
			'label'     => __( 'Highlight Thickness', 'eds-global-settings' ),
			'type'      => Controls_Manager::SLIDER,
			'default'   => [ 'size' => 4 ],
			'range'     => [ 'px' => [ 'min' => 1, 'max' => 20 ] ],
			'selectors' => [
				'{{WRAPPER}} .eah-highlight-line'  => 'height: {{SIZE}}px;',
				'{{WRAPPER}} .eah-highlight-frame' => 'border-width: {{SIZE}}px;',
			],
			'condition' => [ 'animation_type' => 'highlighted' ],
		] );

		$this->add_control( 'cursor_color', [
			'label'     => __( 'Cursor Colour', 'eds-global-settings' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => [ '{{WRAPPER}} .eah-cursor' => 'background-color: {{VALUE}};' ],
			'separator' => 'before',
			'condition' => [ 'animation_type' => 'typing', 'cursor' => 'yes' ],
		] );

		$this->add_control( 'animated_spacing_heading', [
			'label'     => __( 'Spacing', 'eds-global-settings' ),
			'type'      => Controls_Manager::HEADING,
			'separator' => 'before',
		] );

		$this->add_responsive_control( 'animated_padding', [
			'label'      => __( 'Padding', 'eds-global-settings' ),
			'type'       => Controls_Manager::DIMENSIONS,
			'size_units' => [ 'px', 'em', '%' ],
			'selectors'  => [ '{{WRAPPER}} .eah-animated-wrap' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ],
		] );

		$this->add_responsive_control( 'animated_margin', [
			'label'      => __( 'Margin', 'eds-global-settings' ),
			'type'       => Controls_Manager::DIMENSIONS,
			'size_units' => [ 'px', 'em', '%' ],
			'selectors'  => [ '{{WRAPPER}} .eah-animated-wrap' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ],
		] );

		$this->end_controls_section();
	}

	// ── Style: After Text ─────────────────────────────────────────────────────

	private function section_style_after(): void {
		$this->start_controls_section( 'section_style_after', [
			'label'     => __( 'After Text', 'eds-global-settings' ),
			'tab'       => Controls_Manager::TAB_STYLE,
			'condition' => [ 'after_text!' => '' ],
		] );

		$this->add_group_control( Group_Control_Typography::get_type(), [
			'name'     => 'after_typography',
			'selector' => '{{WRAPPER}} .eah-after',
			'global'   => [ 'default' => Global_Typography::TYPOGRAPHY_PRIMARY ],
		] );

		$this->add_control( 'after_color', [
			'label'     => __( 'Colour', 'eds-global-settings' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => [ '{{WRAPPER}} .eah-after' => 'color: {{VALUE}};' ],
		] );

		$this->add_group_control( Group_Control_Text_Shadow::get_type(), [
			'name'     => 'after_text_shadow',
			'selector' => '{{WRAPPER}} .eah-after',
		] );

		$this->add_responsive_control( 'after_gap', [
			'label'      => __( 'Gap Before', 'eds-global-settings' ),
			'type'       => Controls_Manager::SLIDER,
			'size_units' => [ 'px', 'em' ],
			'default'    => [ 'size' => 8, 'unit' => 'px' ],
			'selectors'  => [ '{{WRAPPER}} .eah-after' => 'margin-inline-start: {{SIZE}}{{UNIT}};' ],
		] );

		$this->end_controls_section();
	}

	// ─── Render ───────────────────────────────────────────────────────────────

	protected function render(): void {
		$s    = $this->get_settings_for_display();
		$type = $s['animation_type'] ?? 'typing';
		$tag  = in_array( $s['heading_tag'] ?? 'h2', [ 'h1','h2','h3','h4','h5','h6','div','p' ], true )
			? $s['heading_tag']
			: 'h2';

		$words = [];
		if ( ! in_array( $type, [ 'highlighted', 'wave', 'static' ], true ) ) {
			foreach ( (array) ( $s['animated_words'] ?? [] ) as $item ) {
				if ( ! empty( $item['word'] ) ) {
					$words[] = esc_html( $item['word'] );
				}
			}
		}
		$animated_text = esc_html( $s['animated_text'] ?? '' );

		$config = wp_json_encode( [
			'type'         => $type,
			'words'        => $words,
			'speed'        => (int) ( $s['speed']['size']        ?? 3000 ),
			'typing_speed' => (int) ( $s['typing_speed']['size'] ?? 80 ),
			'cursor'       => 'yes' === ( $s['cursor'] ?? 'yes' ),
			'loop'         => 'yes' === ( $s['loop']   ?? 'yes' ),
			'highlight'    => $s['highlight_type'] ?? 'underline',
		] );

		echo '<div class="eah-wrap" data-config="' . esc_attr( $config ) . '">';
		echo '<' . esc_attr( $tag ) . ' class="eah-heading">';

		// Before.
		if ( ! empty( $s['before_text'] ) ) {
			echo '<span class="eah-before">' . esc_html( $s['before_text'] ) . '</span>';
		}

		// Animated part.
		echo '<span class="eah-animated-wrap">';

		if ( $type === 'highlighted' ) {
			echo '<span class="eah-animated eah-highlighted" data-highlight="' . esc_attr( $s['highlight_type'] ?? 'underline' ) . '">';
			echo '<span class="eah-text">' . $animated_text . '</span>';
			echo $this->get_highlight_svg( $s['highlight_type'] ?? 'underline' );
			echo '</span>';
		} elseif ( $type === 'wave' ) {
			echo '<span class="eah-animated eah-wave">';
			foreach ( str_split( strip_tags( $animated_text ) ) as $i => $char ) {
				$delay = $i * 60;
				echo '<span class="eah-char" style="animation-delay:' . $delay . 'ms">'
					. ( $char === ' ' ? '&nbsp;' : esc_html( $char ) )
					. '</span>';
			}
			echo '</span>';
		} elseif ( $type === 'static' ) {
			echo '<span class="eah-animated">' . $animated_text . '</span>';
		} else {
			// Multi-word animations: typing, rotating, slide, zoom, flip, fade.
			echo '<span class="eah-animated eah-words-wrap">';
			foreach ( $words as $i => $word ) {
				$active = $i === 0 ? ' is-active' : '';
				echo '<span class="eah-word' . esc_attr( $active ) . '" data-index="' . $i . '">' . $word . '</span>';
			}
			if ( $type === 'typing' && 'yes' === ( $s['cursor'] ?? 'yes' ) ) {
				echo '<span class="eah-cursor" aria-hidden="true"></span>';
			}
			echo '</span>';
		}

		echo '</span>';

		// After.
		if ( ! empty( $s['after_text'] ) ) {
			echo '<span class="eah-after">' . esc_html( $s['after_text'] ) . '</span>';
		}

		echo '</' . esc_attr( $tag ) . '>';
		echo '</div>';
	}

	private function get_highlight_svg( string $type ): string {
		switch ( $type ) {
			case 'underline':
				return '<svg class="eah-highlight-svg" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 200 12" preserveAspectRatio="none">
					<path d="M0,10 Q50,2 100,10 Q150,18 200,10" fill="none" stroke="currentColor" stroke-width="4" class="eah-svg-path"/>
				</svg>';
			case 'double-underline':
				return '<svg class="eah-highlight-svg" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 200 16" preserveAspectRatio="none">
					<path d="M0,6 Q100,2 200,6" fill="none" stroke="currentColor" stroke-width="3" class="eah-svg-path"/>
					<path d="M0,13 Q100,9 200,13" fill="none" stroke="currentColor" stroke-width="3" class="eah-svg-path"/>
				</svg>';
			case 'strikethrough':
				return '<svg class="eah-highlight-svg eah-highlight-svg--mid" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 200 10" preserveAspectRatio="none">
					<path d="M0,5 Q100,2 200,5" fill="none" stroke="currentColor" stroke-width="4" class="eah-svg-path"/>
				</svg>';
			case 'background':
				return '<span class="eah-highlight-bg" aria-hidden="true"></span>';
			case 'frame':
				return '<span class="eah-highlight-frame" aria-hidden="true"></span>';
			case 'circle':
				return '<svg class="eah-highlight-svg eah-highlight-svg--circle" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 220 80" preserveAspectRatio="none">
					<ellipse cx="110" cy="40" rx="105" ry="35" fill="none" stroke="currentColor" stroke-width="4" class="eah-svg-path"/>
				</svg>';
			default:
				return '';
		}
	}
}
