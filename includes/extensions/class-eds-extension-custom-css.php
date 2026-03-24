<?php
/**
 * EDS Custom CSS Extension
 *
 * @package EDS_Global_Settings
 * @since   2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

use Elementor\Controls_Manager;

class EDS_Extension_Custom_CSS {

	const CSS_KEY = '_eds_custom_css';

	public function __construct() {
		add_action( 'elementor/element/after_section_end', [ $this, 'add_controls' ], 10, 3 );
		add_action( 'elementor/element/parse_css',         [ $this, 'parse_css' ],    10, 2 );
		// Load live preview handler into editor preview iframe
		add_action( 'elementor/preview/enqueue_scripts',   [ $this, 'enqueue_preview' ] );
	}

	public function enqueue_preview(): void {
		wp_enqueue_script(
			'eds-custom-css-preview',
			EDS_PLUGIN_URL . 'includes/extensions/assets/js/eds-custom-css-preview.js',
			[ 'elementor-frontend' ], EDS_VERSION, true
		);
	}

	public function add_controls( $element, $section_id, $args ): void {
		if ( '_section_responsive' !== $section_id ) return;

		$element->start_controls_section( '_eds_custom_css_section', [
			'label' => __( 'EDS Custom CSS', 'eds-global-settings' ),
			'tab'   => Controls_Manager::TAB_ADVANCED,
		] );

		$element->add_control( '_eds_custom_css_info', [
			'type' => Controls_Manager::RAW_HTML,
			'raw'  => '<div style="font-size:12px;line-height:1.6;color:#6b7280;">'
				. __( 'Use <code>selector</code> to target this element.', 'eds-global-settings' )
				. '<br><em>' . __( 'Example: <code>selector { color: red; }</code>', 'eds-global-settings' ) . '</em>'
				. '</div>',
		] );

		$element->add_control( self::CSS_KEY, [
			'label'              => __( 'Custom CSS', 'eds-global-settings' ),
			'type'               => Controls_Manager::CODE,
			'language'           => 'css',
			'render_type'        => 'ui',
			'separator'          => 'none',
			'default'            => '',
			'description'        => __( 'selector refers to the element\'s unique CSS class.', 'eds-global-settings' ),
			'frontend_available' => true,
		] );

		$element->end_controls_section();
	}

	public function parse_css( $post_css, $element ): void {
		$settings = $element->get_settings();
		$raw_css  = $settings[ self::CSS_KEY ] ?? '';
		if ( empty( trim( $raw_css ) ) ) return;

		$selector = '.elementor-element.elementor-element-' . $element->get_id();
		$css      = str_replace( 'selector', $selector, $raw_css );
		$post_css->get_stylesheet()->add_raw_css( wp_strip_all_tags( $css ) );
	}
}
