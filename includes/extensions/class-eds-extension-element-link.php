<?php
/**
 * EDS Element Link Extension
 *
 * Adds a clickable link to any Elementor element (widget, section, column,
 * container) without wrapping in an <a> tag. Uses JS onclick so layout is
 * never broken. Supports Elementor Dynamic Tags on the URL field.
 *
 * @package EDS_Global_Settings
 * @since   2.4.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Elementor\Controls_Manager;
use Elementor\Modules\DynamicTags\Module as TagsModule;

class EDS_Extension_Element_Link {

	public function __construct() {
		add_action( 'elementor/element/after_section_end', [ $this, 'add_controls' ], 10, 3 );
		add_action( 'elementor/frontend/before_render',   [ $this, 'before_render' ] );
		add_action( 'elementor/preview/enqueue_scripts',  [ $this, 'enqueue_preview' ] );
		add_action( 'wp_enqueue_scripts',                 [ $this, 'enqueue_frontend' ] );
	}

	// ─── Enqueue ──────────────────────────────────────────────────────────────

	public function enqueue_preview(): void {
		wp_enqueue_script(
			'eds-element-link',
			EDS_PLUGIN_URL . 'includes/extensions/assets/js/eds-element-link.js',
			[ 'jquery' ], EDS_VERSION, true
		);
	}

	public function enqueue_frontend(): void {
		wp_enqueue_script(
			'eds-element-link',
			EDS_PLUGIN_URL . 'includes/extensions/assets/js/eds-element-link.js',
			[ 'jquery' ], EDS_VERSION, true
		);
	}

	// ─── Controls ─────────────────────────────────────────────────────────────

	public function add_controls( $element, $section_id, $args ): void {
		if ( '_section_responsive' !== $section_id ) {
			return;
		}

		$element->start_controls_section( '_eds_link_section', [
			'label' => __( 'EDS Element Link', 'eds-global-settings' ),
			'tab'   => Controls_Manager::TAB_ADVANCED,
		] );

		$element->add_control( '_eds_link_info', [
			'type' => Controls_Manager::RAW_HTML,
			'raw'  => '<div style="font-size:12px;line-height:1.6;color:#6b7280;">'
				. __( 'Makes the entire element clickable without wrapping in an &lt;a&gt; tag — safe for sections, columns, containers, and widgets.', 'eds-global-settings' )
				. '</div>',
		] );

		$element->add_control( '_eds_link_url', [
			'label'       => __( 'Link URL', 'eds-global-settings' ),
			'type'        => Controls_Manager::URL,
			'placeholder' => 'https://example.com',
			'options'     => [ 'url', 'is_external', 'nofollow' ],
			'default'     => [
				'url'         => '',
				'is_external' => false,
				'nofollow'    => false,
			],
			'dynamic'     => [
				'active'     => true,
				'categories' => [
					TagsModule::URL_CATEGORY,
					TagsModule::POST_META_CATEGORY,
				],
			],
			'label_block'        => true,
			'render_type'  => 'template',
		] );

		$element->add_control( '_eds_link_cursor', [
			'label'   => __( 'Cursor', 'eds-global-settings' ),
			'type'    => Controls_Manager::SELECT,
			'default' => 'pointer',
			'options' => [
				'pointer'  => __( 'Pointer', 'eds-global-settings' ),
				'zoom-in'  => __( 'Zoom In', 'eds-global-settings' ),
				'zoom-out' => __( 'Zoom Out', 'eds-global-settings' ),
				'default'  => __( 'Default', 'eds-global-settings' ),
			],
			'condition'          => [ '_eds_link_url[url]!' => '' ],
			'selectors'          => [
				'{{WRAPPER}}' => 'cursor: {{VALUE}};',
			],
			'render_type'  => 'template',
		] );

		$element->add_control( '_eds_link_hover_opacity', [
			'label'     => __( 'Hover Opacity', 'eds-global-settings' ),
			'type'      => Controls_Manager::SLIDER,
			'default'   => [ 'size' => 1 ],
			'range'     => [ 'px' => [ 'min' => 0, 'max' => 1, 'step' => 0.05 ] ],
			'condition' => [ '_eds_link_url[url]!' => '' ],
			'selectors' => [
				'{{WRAPPER}}:hover' => 'opacity: {{SIZE}};',
			],
		] );

		$element->add_control( '_eds_link_transition', [
			'label'     => __( 'Hover Transition (s)', 'eds-global-settings' ),
			'type'      => Controls_Manager::SLIDER,
			'default'   => [ 'size' => 0.3 ],
			'range'     => [ 'px' => [ 'min' => 0, 'max' => 2, 'step' => 0.05 ] ],
			'condition' => [ '_eds_link_url[url]!' => '' ],
			'selectors' => [
				'{{WRAPPER}}' => 'transition: opacity {{SIZE}}s ease;',
			],
		] );

		$element->end_controls_section();
	}

	// ─── Before render — write data-eds-link ──────────────────────────────────

	public function before_render( $element ): void {
		$settings = $element->get_settings_for_display();
		$link     = $settings['_eds_link_url'] ?? [];
		$url      = $link['url'] ?? '';

		if ( empty( trim( $url ) ) ) {
			return;
		}

		$element->add_render_attribute( '_wrapper', [
			'data-eds-link'        => esc_url( $url ),
			'data-eds-link-ext'    => ! empty( $link['is_external'] ) ? '1' : '0',
			'data-eds-link-nofollow' => ! empty( $link['nofollow'] ) ? '1' : '0',
		] );
	}
}
