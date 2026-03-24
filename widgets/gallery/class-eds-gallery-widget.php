<?php
/**
 * EDS Gallery Widget
 *
 * @package EDS_Global_Settings
 * @since   1.5.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Elementor\Controls_Manager;
use Elementor\Repeater;
use Elementor\Utils;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Css_Filter;
use Elementor\Group_Control_Background;
use Elementor\Group_Control_Image_Size;

class EDS_Gallery_Widget extends \Elementor\Widget_Base {

	public function get_name(): string         { return 'eds-gallery'; }
	public function get_title(): string        { return __( 'EDS Gallery', 'eds-global-settings' ); }
	public function get_icon(): string         { return 'eicon-gallery-grid'; }
	public function get_categories(): array    { return [ 'eds-elements', 'general' ]; }
	public function get_keywords(): array      { return [ 'gallery', 'images', 'grid', 'masonry', 'filter', 'lightbox', 'eds' ]; }
	public function get_style_depends(): array  { return [ 'eds-widget-gallery' ]; }
	public function get_script_depends(): array { return [ 'eds-widget-gallery' ]; }

	protected function register_controls(): void {
		$this->section_content_gallery();
		$this->section_content_layout();
		$this->section_content_filter();
		$this->section_content_overlay();
		$this->section_content_lightbox();
		$this->section_style_items();
		$this->section_style_image();
		$this->section_style_overlay();
		$this->section_style_caption();
		$this->section_style_filter();
	}

	// ─── Content: Gallery ──────────────────────────────────────────────────────

	private function section_content_gallery(): void {
		$this->start_controls_section( 'section_gallery', [
			'label' => __( 'Gallery', 'eds-global-settings' ),
			'tab'   => Controls_Manager::TAB_CONTENT,
		] );

		$repeater = new Repeater();

		$repeater->add_control( 'image', [
			'label'   => __( 'Image', 'eds-global-settings' ),
			'type'    => Controls_Manager::MEDIA,
			'default' => [ 'url' => Utils::get_placeholder_image_src() ],
		] );

		$repeater->add_control( 'title', [
			'label'       => __( 'Title', 'eds-global-settings' ),
			'type'        => Controls_Manager::TEXT,
			'default'     => '',
			'label_block' => true,
		] );

		$repeater->add_control( 'description', [
			'label'   => __( 'Description', 'eds-global-settings' ),
			'type'    => Controls_Manager::TEXTAREA,
			'default' => '',
		] );

		$repeater->add_control( 'tags', [
			'label'       => __( 'Filter Tags (comma-separated)', 'eds-global-settings' ),
			'type'        => Controls_Manager::TEXT,
			'default'     => '',
			'label_block' => true,
		] );

		$repeater->add_control( 'link', [
			'label' => __( 'Custom Link', 'eds-global-settings' ),
			'type'  => Controls_Manager::URL,
		] );

		$this->add_control( 'gallery_items', [
			'label'       => __( 'Gallery Items', 'eds-global-settings' ),
			'type'        => Controls_Manager::REPEATER,
			'fields'      => $repeater->get_controls(),
			'default'     => [
				[ 'image' => [ 'url' => Utils::get_placeholder_image_src() ], 'title' => 'Image 1', 'tags' => 'Nature' ],
				[ 'image' => [ 'url' => Utils::get_placeholder_image_src() ], 'title' => 'Image 2', 'tags' => 'Architecture' ],
				[ 'image' => [ 'url' => Utils::get_placeholder_image_src() ], 'title' => 'Image 3', 'tags' => 'Nature' ],
				[ 'image' => [ 'url' => Utils::get_placeholder_image_src() ], 'title' => 'Image 4', 'tags' => 'People' ],
				[ 'image' => [ 'url' => Utils::get_placeholder_image_src() ], 'title' => 'Image 5', 'tags' => 'Architecture' ],
				[ 'image' => [ 'url' => Utils::get_placeholder_image_src() ], 'title' => 'Image 6', 'tags' => 'People' ],
			],
			'title_field' => '{{{ title }}}',
		] );

		$this->add_control( 'thumbnail_size', [
			'label'   => __( 'Image Size', 'eds-global-settings' ),
			'type'    => Controls_Manager::SELECT,
			'default' => 'large',
			'options' => [
				'thumbnail' => 'Thumbnail (150px)',
				'medium'    => 'Medium (300px)',
				'large'     => 'Large (1024px)',
				'full'      => 'Full',
			],
		] );

		$this->end_controls_section();
	}

	// ─── Content: Layout ───────────────────────────────────────────────────────

	private function section_content_layout(): void {
		$this->start_controls_section( 'section_layout', [
			'label' => __( 'Layout', 'eds-global-settings' ),
			'tab'   => Controls_Manager::TAB_CONTENT,
		] );

		$this->add_control( 'gallery_layout', [
			'label'   => __( 'Layout', 'eds-global-settings' ),
			'type'    => Controls_Manager::SELECT,
			'default' => 'grid',
			'options' => [
				'grid'     => __( 'Grid', 'eds-global-settings' ),
				'masonry'  => __( 'Masonry', 'eds-global-settings' ),
			],
		] );

		$this->add_responsive_control( 'columns', [
			'label'          => __( 'Columns', 'eds-global-settings' ),
			'type'           => Controls_Manager::SELECT,
			'default'        => '3',
			'tablet_default' => '2',
			'mobile_default' => '1',
			'options'        => [ '1' => '1', '2' => '2', '3' => '3', '4' => '4', '5' => '5', '6' => '6' ],
			'selectors'      => [
				'{{WRAPPER}} .eds-gallery'          => 'grid-template-columns: repeat({{VALUE}}, 1fr);',
				'{{WRAPPER}} .eds-gallery--masonry' => 'column-count: {{VALUE}};',
			],
		] );

		$this->add_responsive_control( 'gap', [
			'label'      => __( 'Gap', 'eds-global-settings' ),
			'type'       => Controls_Manager::SLIDER,
			'size_units' => [ 'px' ],
			'default'    => [ 'size' => 10 ],
			'range'      => [ 'px' => [ 'min' => 0, 'max' => 60 ] ],
			'selectors'  => [
				'{{WRAPPER}} .eds-gallery'          => 'gap: {{SIZE}}px;',
				'{{WRAPPER}} .eds-gallery--masonry' => 'column-gap: {{SIZE}}px;',
				'{{WRAPPER}} .eds-gallery--masonry .eds-gallery__item' => 'margin-bottom: {{SIZE}}px;',
			],
		] );

		$this->add_control( 'aspect_ratio', [
			'label'     => __( 'Aspect Ratio', 'eds-global-settings' ),
			'type'      => Controls_Manager::SELECT,
			'default'   => '1-1',
			'options'   => [
				'1-1'    => '1:1 Square',
				'4-3'    => '4:3',
				'16-9'   => '16:9 Widescreen',
				'3-2'    => '3:2',
				'2-3'    => '2:3 Portrait',
				'custom' => __( 'Custom Height', 'eds-global-settings' ),
			],
			'condition' => [ 'gallery_layout' => 'grid' ],
		] );

		$this->add_responsive_control( 'custom_height', [
			'label'      => __( 'Height', 'eds-global-settings' ),
			'type'       => Controls_Manager::SLIDER,
			'size_units' => [ 'px', 'vh' ],
			'default'    => [ 'size' => 300, 'unit' => 'px' ],
			'range'      => [ 'px' => [ 'min' => 50, 'max' => 1000 ], 'vh' => [ 'min' => 10, 'max' => 100 ] ],
			'condition'  => [ 'gallery_layout' => 'grid', 'aspect_ratio' => 'custom' ],
			'selectors'  => [
				'{{WRAPPER}} .eds-gallery--grid .eds-gallery__media' => 'padding-bottom: 0 !important; height: {{SIZE}}{{UNIT}};',
			],
		] );

		$this->end_controls_section();
	}

	// ─── Content: Filter ───────────────────────────────────────────────────────

	private function section_content_filter(): void {
		$this->start_controls_section( 'section_filter', [
			'label' => __( 'Filter Bar', 'eds-global-settings' ),
			'tab'   => Controls_Manager::TAB_CONTENT,
		] );

		$this->add_control( 'show_filter', [
			'label'        => __( 'Show Filter Bar', 'eds-global-settings' ),
			'type'         => Controls_Manager::SWITCHER,
			'return_value' => 'yes',
			'default'      => 'yes',
		] );

		$this->add_control( 'all_label', [
			'label'     => __( '"All" Button Label', 'eds-global-settings' ),
			'type'      => Controls_Manager::TEXT,
			'default'   => __( 'All', 'eds-global-settings' ),
			'condition' => [ 'show_filter' => 'yes' ],
		] );

		$this->add_responsive_control( 'filter_align', [
			'label'     => __( 'Alignment', 'eds-global-settings' ),
			'type'      => Controls_Manager::CHOOSE,
			'options'   => [
				'flex-start' => [ 'title' => __( 'Left',   'eds-global-settings' ), 'icon' => 'eicon-h-align-left' ],
				'center'     => [ 'title' => __( 'Center', 'eds-global-settings' ), 'icon' => 'eicon-h-align-center' ],
				'flex-end'   => [ 'title' => __( 'Right',  'eds-global-settings' ), 'icon' => 'eicon-h-align-right' ],
			],
			'default'   => 'center',
			'condition' => [ 'show_filter' => 'yes' ],
			'selectors' => [ '{{WRAPPER}} .eds-gallery__filter' => 'justify-content: {{VALUE}};' ],
		] );

		$this->end_controls_section();
	}

	// ─── Content: Overlay ──────────────────────────────────────────────────────

	private function section_content_overlay(): void {
		$this->start_controls_section( 'section_overlay', [
			'label' => __( 'Overlay', 'eds-global-settings' ),
			'tab'   => Controls_Manager::TAB_CONTENT,
		] );

		$this->add_control( 'overlay_type', [
			'label'   => __( 'Overlay', 'eds-global-settings' ),
			'type'    => Controls_Manager::SELECT,
			'default' => 'hover',
			'options' => [
				'none'   => __( 'None', 'eds-global-settings' ),
				'hover'  => __( 'On Hover', 'eds-global-settings' ),
				'always' => __( 'Always Visible', 'eds-global-settings' ),
			],
		] );

		$this->add_control( 'overlay_animation', [
			'label'     => __( 'Animation', 'eds-global-settings' ),
			'type'      => Controls_Manager::SELECT,
			'default'   => 'fade',
			'options'   => [
				'fade'         => __( 'Fade', 'eds-global-settings' ),
				'slide-top'    => __( 'Slide from Top', 'eds-global-settings' ),
				'slide-bottom' => __( 'Slide from Bottom', 'eds-global-settings' ),
				'slide-left'   => __( 'Slide from Left', 'eds-global-settings' ),
				'slide-right'  => __( 'Slide from Right', 'eds-global-settings' ),
				'zoom-in'      => __( 'Zoom In', 'eds-global-settings' ),
				'zoom-out'     => __( 'Zoom Out', 'eds-global-settings' ),
			],
			'condition' => [ 'overlay_type' => 'hover' ],
		] );

		$this->add_control( 'show_title', [
			'label'        => __( 'Show Title', 'eds-global-settings' ),
			'type'         => Controls_Manager::SWITCHER,
			'return_value' => 'yes',
			'default'      => 'yes',
			'separator'    => 'before',
		] );

		$this->add_control( 'show_description', [
			'label'        => __( 'Show Description', 'eds-global-settings' ),
			'type'         => Controls_Manager::SWITCHER,
			'return_value' => 'yes',
			'default'      => 'yes',
		] );

		$this->end_controls_section();
	}

	// ─── Content: Lightbox ─────────────────────────────────────────────────────

	private function section_content_lightbox(): void {
		$this->start_controls_section( 'section_lightbox', [
			'label' => __( 'Lightbox', 'eds-global-settings' ),
			'tab'   => Controls_Manager::TAB_CONTENT,
		] );

		$this->add_control( 'lightbox', [
			'label'        => __( 'Enable Lightbox', 'eds-global-settings' ),
			'type'         => Controls_Manager::SWITCHER,
			'return_value' => 'yes',
			'default'      => 'yes',
		] );

		$this->add_control( 'lightbox_type', [
			'label'     => __( 'Lightbox Type', 'eds-global-settings' ),
			'type'      => Controls_Manager::SELECT,
			'default'   => 'built-in',
			'options'   => [
				'built-in'  => __( 'Built-in', 'eds-global-settings' ),
				'elementor' => __( 'Elementor Lightbox', 'eds-global-settings' ),
			],
			'condition' => [ 'lightbox' => 'yes' ],
		] );

		$this->add_control( 'lightbox_caption', [
			'label'        => __( 'Show Caption', 'eds-global-settings' ),
			'type'         => Controls_Manager::SWITCHER,
			'return_value' => 'yes',
			'default'      => 'yes',
			'condition'    => [ 'lightbox' => 'yes', 'lightbox_type' => 'built-in' ],
		] );

		$this->add_control( 'lightbox_counter', [
			'label'        => __( 'Show Counter', 'eds-global-settings' ),
			'type'         => Controls_Manager::SWITCHER,
			'return_value' => 'yes',
			'default'      => 'yes',
			'condition'    => [ 'lightbox' => 'yes', 'lightbox_type' => 'built-in' ],
		] );

		$this->add_control( 'lightbox_loop', [
			'label'        => __( 'Loop', 'eds-global-settings' ),
			'type'         => Controls_Manager::SWITCHER,
			'return_value' => 'yes',
			'default'      => 'yes',
			'condition'    => [ 'lightbox' => 'yes', 'lightbox_type' => 'built-in' ],
		] );

		$this->add_control( 'lightbox_keyboard', [
			'label'        => __( 'Keyboard Navigation', 'eds-global-settings' ),
			'type'         => Controls_Manager::SWITCHER,
			'return_value' => 'yes',
			'default'      => 'yes',
			'condition'    => [ 'lightbox' => 'yes', 'lightbox_type' => 'built-in' ],
		] );

		$this->end_controls_section();
	}

	// ─── Style: Items ──────────────────────────────────────────────────────────

	private function section_style_items(): void {
		$this->start_controls_section( 'section_style_items', [
			'label' => __( 'Items', 'eds-global-settings' ),
			'tab'   => Controls_Manager::TAB_STYLE,
		] );

		$this->add_group_control( Group_Control_Border::get_type(), [
			'name'     => 'item_border',
			'selector' => '{{WRAPPER}} .eds-gallery__item',
		] );

		$this->add_responsive_control( 'item_border_radius', [
			'label'      => __( 'Border Radius', 'eds-global-settings' ),
			'type'       => Controls_Manager::DIMENSIONS,
			'size_units' => [ 'px', '%' ],
			'selectors'  => [
				'{{WRAPPER}} .eds-gallery__item' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}; overflow: hidden;',
			],
		] );

		$this->add_group_control( Group_Control_Box_Shadow::get_type(), [
			'name'     => 'item_box_shadow',
			'selector' => '{{WRAPPER}} .eds-gallery__item',
		] );

		$this->end_controls_section();
	}

	// ─── Style: Image ──────────────────────────────────────────────────────────

	private function section_style_image(): void {
		$this->start_controls_section( 'section_style_image', [
			'label' => __( 'Image', 'eds-global-settings' ),
			'tab'   => Controls_Manager::TAB_STYLE,
		] );

		$this->add_control( 'image_fit', [
			'label'     => __( 'Object Fit', 'eds-global-settings' ),
			'type'      => Controls_Manager::SELECT,
			'default'   => 'cover',
			'options'   => [
				'cover'   => 'Cover',
				'contain' => 'Contain',
				'fill'    => 'Fill',
			],
			'selectors' => [ '{{WRAPPER}} .eds-gallery__media img' => 'object-fit: {{VALUE}};' ],
		] );

		$this->add_control( 'image_hover_effect', [
			'label'   => __( 'Hover Effect', 'eds-global-settings' ),
			'type'    => Controls_Manager::SELECT,
			'default' => 'zoom-in',
			'options' => [
				'none'     => __( 'None', 'eds-global-settings' ),
				'zoom-in'  => __( 'Zoom In', 'eds-global-settings' ),
				'zoom-out' => __( 'Zoom Out', 'eds-global-settings' ),
			],
		] );

		$this->add_control( 'image_transition', [
			'label'     => __( 'Transition (ms)', 'eds-global-settings' ),
			'type'      => Controls_Manager::SLIDER,
			'default'   => [ 'size' => 400 ],
			'range'     => [ 'px' => [ 'min' => 0, 'max' => 2000 ] ],
			'selectors' => [ '{{WRAPPER}} .eds-gallery__media img' => 'transition-duration: {{SIZE}}ms;' ],
		] );

		$this->start_controls_tabs( 'tabs_image_filter' );

		$this->start_controls_tab( 'tab_image_filter_normal', [ 'label' => __( 'Normal', 'eds-global-settings' ) ] );
		$this->add_group_control( Group_Control_Css_Filter::get_type(), [
			'name'     => 'image_css_filter',
			'selector' => '{{WRAPPER}} .eds-gallery__media img',
		] );
		$this->end_controls_tab();

		$this->start_controls_tab( 'tab_image_filter_hover', [ 'label' => __( 'Hover', 'eds-global-settings' ) ] );
		$this->add_group_control( Group_Control_Css_Filter::get_type(), [
			'name'     => 'image_css_filter_hover',
			'selector' => '{{WRAPPER}} .eds-gallery__item:hover .eds-gallery__media img',
		] );
		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->end_controls_section();
	}

	// ─── Style: Overlay ────────────────────────────────────────────────────────

	private function section_style_overlay(): void {
		$this->start_controls_section( 'section_style_overlay', [
			'label' => __( 'Overlay', 'eds-global-settings' ),
			'tab'   => Controls_Manager::TAB_STYLE,
		] );

		$this->add_control( 'overlay_color', [
			'label'     => __( 'Background', 'eds-global-settings' ),
			'type'      => Controls_Manager::COLOR,
			'default'   => 'rgba(0,0,0,0.55)',
			'selectors' => [ '{{WRAPPER}} .eds-gallery__overlay' => 'background: {{VALUE}};' ],
		] );

		$this->add_responsive_control( 'overlay_padding', [
			'label'      => __( 'Padding', 'eds-global-settings' ),
			'type'       => Controls_Manager::DIMENSIONS,
			'size_units' => [ 'px', 'em' ],
			'default'    => [ 'top' => 16, 'right' => 16, 'bottom' => 16, 'left' => 16, 'unit' => 'px', 'isLinked' => true ],
			'selectors'  => [
				'{{WRAPPER}} .eds-gallery__overlay' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
			],
		] );

		$this->add_responsive_control( 'overlay_v_align', [
			'label'     => __( 'Vertical Alignment', 'eds-global-settings' ),
			'type'      => Controls_Manager::CHOOSE,
			'default'   => 'center',
			'options'   => [
				'flex-start' => [ 'title' => __( 'Top',    'eds-global-settings' ), 'icon' => 'eicon-v-align-top' ],
				'center'     => [ 'title' => __( 'Middle', 'eds-global-settings' ), 'icon' => 'eicon-v-align-middle' ],
				'flex-end'   => [ 'title' => __( 'Bottom', 'eds-global-settings' ), 'icon' => 'eicon-v-align-bottom' ],
			],
			'selectors' => [ '{{WRAPPER}} .eds-gallery__overlay' => 'justify-content: {{VALUE}};' ],
		] );

		$this->add_responsive_control( 'overlay_text_align', [
			'label'     => __( 'Text Alignment', 'eds-global-settings' ),
			'type'      => Controls_Manager::CHOOSE,
			'default'   => 'center',
			'options'   => [
				'left'   => [ 'title' => __( 'Left',   'eds-global-settings' ), 'icon' => 'eicon-text-align-left' ],
				'center' => [ 'title' => __( 'Center', 'eds-global-settings' ), 'icon' => 'eicon-text-align-center' ],
				'right'  => [ 'title' => __( 'Right',  'eds-global-settings' ), 'icon' => 'eicon-text-align-right' ],
			],
			'selectors' => [ '{{WRAPPER}} .eds-gallery__caption' => 'text-align: {{VALUE}};' ],
		] );

		$this->end_controls_section();
	}

	// ─── Style: Caption ────────────────────────────────────────────────────────

	private function section_style_caption(): void {
		$this->start_controls_section( 'section_style_caption', [
			'label' => __( 'Caption', 'eds-global-settings' ),
			'tab'   => Controls_Manager::TAB_STYLE,
		] );

		$this->add_group_control( Group_Control_Typography::get_type(), [
			'name'     => 'caption_title_typo',
			'label'    => __( 'Title Typography', 'eds-global-settings' ),
			'selector' => '{{WRAPPER}} .eds-gallery__caption-title',
		] );

		$this->add_control( 'caption_title_color', [
			'label'     => __( 'Title Colour', 'eds-global-settings' ),
			'type'      => Controls_Manager::COLOR,
			'default'   => '#ffffff',
			'selectors' => [ '{{WRAPPER}} .eds-gallery__caption-title' => 'color: {{VALUE}};' ],
		] );

		$this->add_control( 'caption_title_spacing', [
			'label'     => __( 'Title Bottom Spacing', 'eds-global-settings' ),
			'type'      => Controls_Manager::SLIDER,
			'default'   => [ 'size' => 6 ],
			'range'     => [ 'px' => [ 'min' => 0, 'max' => 60 ] ],
			'selectors' => [ '{{WRAPPER}} .eds-gallery__caption-title' => 'margin-bottom: {{SIZE}}px;' ],
		] );

		$this->add_group_control( Group_Control_Typography::get_type(), [
			'name'      => 'caption_desc_typo',
			'label'     => __( 'Description Typography', 'eds-global-settings' ),
			'selector'  => '{{WRAPPER}} .eds-gallery__caption-desc',
			'separator' => 'before',
		] );

		$this->add_control( 'caption_desc_color', [
			'label'     => __( 'Description Colour', 'eds-global-settings' ),
			'type'      => Controls_Manager::COLOR,
			'default'   => 'rgba(255,255,255,0.85)',
			'selectors' => [ '{{WRAPPER}} .eds-gallery__caption-desc' => 'color: {{VALUE}};' ],
		] );

		$this->end_controls_section();
	}

	// ─── Style: Filter Bar ─────────────────────────────────────────────────────

	private function section_style_filter(): void {
		$this->start_controls_section( 'section_style_filter', [
			'label' => __( 'Filter Bar', 'eds-global-settings' ),
			'tab'   => Controls_Manager::TAB_STYLE,
		] );

		$this->add_responsive_control( 'filter_bottom_spacing', [
			'label'     => __( 'Spacing Below', 'eds-global-settings' ),
			'type'      => Controls_Manager::SLIDER,
			'default'   => [ 'size' => 24 ],
			'selectors' => [ '{{WRAPPER}} .eds-gallery__filter' => 'margin-bottom: {{SIZE}}px;' ],
		] );

		$this->add_responsive_control( 'filter_gap', [
			'label'     => __( 'Button Gap', 'eds-global-settings' ),
			'type'      => Controls_Manager::SLIDER,
			'default'   => [ 'size' => 8 ],
			'selectors' => [ '{{WRAPPER}} .eds-gallery__filter' => 'gap: {{SIZE}}px;' ],
		] );

		$this->add_group_control( Group_Control_Typography::get_type(), [
			'name'     => 'filter_typo',
			'selector' => '{{WRAPPER}} .eds-gallery__filter-btn',
		] );

		$this->start_controls_tabs( 'tabs_filter_btn' );

		$this->start_controls_tab( 'tab_filter_normal', [ 'label' => __( 'Normal', 'eds-global-settings' ) ] );
		$this->add_control( 'filter_btn_color', [
			'label'     => __( 'Text Colour', 'eds-global-settings' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => [ '{{WRAPPER}} .eds-gallery__filter-btn' => 'color: {{VALUE}};' ],
		] );
		$this->add_control( 'filter_btn_bg', [
			'label'     => __( 'Background', 'eds-global-settings' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => [ '{{WRAPPER}} .eds-gallery__filter-btn' => 'background-color: {{VALUE}};' ],
		] );
		$this->end_controls_tab();

		$this->start_controls_tab( 'tab_filter_active', [ 'label' => __( 'Active', 'eds-global-settings' ) ] );
		$this->add_control( 'filter_active_color', [
			'label'     => __( 'Text Colour', 'eds-global-settings' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => [ '{{WRAPPER}} .eds-gallery__filter-btn.is-active' => 'color: {{VALUE}};' ],
		] );
		$this->add_control( 'filter_active_bg', [
			'label'     => __( 'Background', 'eds-global-settings' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => [ '{{WRAPPER}} .eds-gallery__filter-btn.is-active' => 'background-color: {{VALUE}};' ],
		] );
		$this->end_controls_tab();

		$this->start_controls_tab( 'tab_filter_hover', [ 'label' => __( 'Hover', 'eds-global-settings' ) ] );
		$this->add_control( 'filter_hover_color', [
			'label'     => __( 'Text Colour', 'eds-global-settings' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => [ '{{WRAPPER}} .eds-gallery__filter-btn:hover' => 'color: {{VALUE}};' ],
		] );
		$this->add_control( 'filter_hover_bg', [
			'label'     => __( 'Background', 'eds-global-settings' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => [ '{{WRAPPER}} .eds-gallery__filter-btn:hover' => 'background-color: {{VALUE}};' ],
		] );
		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->add_responsive_control( 'filter_btn_padding', [
			'label'      => __( 'Button Padding', 'eds-global-settings' ),
			'type'       => Controls_Manager::DIMENSIONS,
			'size_units' => [ 'px', 'em' ],
			'default'    => [ 'top' => 8, 'right' => 20, 'bottom' => 8, 'left' => 20, 'unit' => 'px', 'isLinked' => false ],
			'selectors'  => [
				'{{WRAPPER}} .eds-gallery__filter-btn' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
			],
			'separator'  => 'before',
		] );

		$this->add_group_control( Group_Control_Border::get_type(), [
			'name'     => 'filter_btn_border',
			'selector' => '{{WRAPPER}} .eds-gallery__filter-btn',
		] );

		$this->add_responsive_control( 'filter_btn_radius', [
			'label'      => __( 'Border Radius', 'eds-global-settings' ),
			'type'       => Controls_Manager::DIMENSIONS,
			'size_units' => [ 'px', '%' ],
			'default'    => [ 'top' => 4, 'right' => 4, 'bottom' => 4, 'left' => 4, 'unit' => 'px', 'isLinked' => true ],
			'selectors'  => [
				'{{WRAPPER}} .eds-gallery__filter-btn' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
			],
		] );

		$this->end_controls_section();
	}

	// ─── Render ────────────────────────────────────────────────────────────────

	protected function render(): void {
		$settings    = $this->get_settings_for_display();
		$items       = $settings['gallery_items'] ?? [];
		$layout      = $settings['gallery_layout'] ?? 'grid';
		$columns     = (int) ( $settings['columns'] ?? 3 );
		$gap         = (int) ( $settings['gap']['size'] ?? 10 );
		$aspect      = $settings['aspect_ratio'] ?? '1-1';
		$img_size    = $settings['thumbnail_size'] ?? 'large';
		$img_hover   = $settings['image_hover_effect'] ?? 'zoom-in';
		$show_filter = 'yes' === ( $settings['show_filter'] ?? 'yes' );
		$overlay     = $settings['overlay_type'] ?? 'hover';
		$overlay_anim = $settings['overlay_animation'] ?? 'fade';
		$show_title  = 'yes' === ( $settings['show_title'] ?? 'yes' );
		$show_desc   = 'yes' === ( $settings['show_description'] ?? 'yes' );
		$lightbox    = 'yes' === ( $settings['lightbox'] ?? 'yes' );
		$lb_type     = $settings['lightbox_type'] ?? 'built-in';
		$lb_caption  = 'yes' === ( $settings['lightbox_caption'] ?? 'yes' );
		$lb_counter  = 'yes' === ( $settings['lightbox_counter'] ?? 'yes' );
		$lb_loop     = 'yes' === ( $settings['lightbox_loop'] ?? 'yes' );
		$lb_keyboard = 'yes' === ( $settings['lightbox_keyboard'] ?? 'yes' );
		$builtin_lb  = $lightbox && ( $lb_type === 'built-in' );
		$elementor_lb = $lightbox && ( $lb_type === 'elementor' );

		if ( empty( $items ) ) {
			return;
		}

		$aspect_map = [
			'1-1'  => '100%',
			'4-3'  => '75%',
			'16-9' => '56.25%',
			'3-2'  => '66.67%',
			'2-3'  => '150%',
		];

		// Collect tags.
		$all_tags = [];
		foreach ( $items as $item ) {
			if ( ! empty( $item['tags'] ) ) {
				foreach ( array_map( 'trim', explode( ',', $item['tags'] ) ) as $tag ) {
					if ( $tag !== '' ) {
						$all_tags[ sanitize_title( $tag ) ] = $tag;
					}
				}
			}
		}

		$widget_id = 'eds-gallery-' . $this->get_id();
		$config    = wp_json_encode( [
			'lightbox'    => $builtin_lb,
			'lb_caption'  => $lb_caption,
			'lb_counter'  => $lb_counter,
			'lb_loop'     => $lb_loop,
			'lb_keyboard' => $lb_keyboard,
		] );

		$gallery_class = 'eds-gallery'
			. ' eds-gallery--' . esc_attr( $layout )
			. ' eds-gallery--img-' . esc_attr( $img_hover )
			. ( $overlay === 'hover'  ? ' eds-gallery--overlay-hover'  : '' )
			. ( $overlay === 'always' ? ' eds-gallery--overlay-always' : '' )
			. ' eds-gallery--anim-' . esc_attr( $overlay_anim );

		echo '<div class="eds-gallery-wrap" id="' . esc_attr( $widget_id ) . '" data-config="' . esc_attr( $config ) . '">';

		// Filter bar.
		if ( $show_filter && ! empty( $all_tags ) ) {
			$all_label = ! empty( $settings['all_label'] ) ? $settings['all_label'] : __( 'All', 'eds-global-settings' );
			echo '<div class="eds-gallery__filter">';
			echo '<button class="eds-gallery__filter-btn is-active" data-filter="*">' . esc_html( $all_label ) . '</button>';
			foreach ( $all_tags as $slug => $label ) {
				echo '<button class="eds-gallery__filter-btn" data-filter="' . esc_attr( $slug ) . '">' . esc_html( $label ) . '</button>';
			}
			echo '</div>';
		}

		// Grid.
		echo '<div class="' . $gallery_class . '">';

		foreach ( $items as $index => $item ) {
			// Tags.
			$item_tags = [];
			if ( ! empty( $item['tags'] ) ) {
				foreach ( array_map( 'trim', explode( ',', $item['tags'] ) ) as $t ) {
					if ( $t !== '' ) {
						$item_tags[] = sanitize_title( $t );
					}
				}
			}

			$img_id       = ! empty( $item['image']['id'] ) ? (int) $item['image']['id'] : 0;
			$img_url      = $item['image']['url'] ?? '';
			$img_full_url = $img_id ? ( wp_get_attachment_image_url( $img_id, 'full' ) ?: $img_url ) : $img_url;
			$title        = $item['title'] ?? '';
			$desc         = $item['description'] ?? '';
			$has_link     = ! empty( $item['link']['url'] );

			// Media style.
			$media_style = '';
			if ( $layout === 'grid' && $aspect !== 'custom' && isset( $aspect_map[ $aspect ] ) ) {
				$media_style = ' style="padding-bottom:' . $aspect_map[ $aspect ] . ';"';
			}

			// Image HTML.
			$img_html = $img_id
				? wp_get_attachment_image( $img_id, $img_size, false, [ 'class' => 'eds-gallery__img' ] )
				: ( $img_url ? '<img class="eds-gallery__img" src="' . esc_url( $img_url ) . '" alt="' . esc_attr( $title ) . '">' : '' );

			// Item.
			echo '<div class="eds-gallery__item elementor-repeater-item-' . esc_attr( $item['_id'] ?? '' ) . '"'
				. ' data-tags="' . esc_attr( implode( ' ', $item_tags ) ) . '"'
				. ' data-src="' . esc_attr( $img_full_url ) . '"'
				. ' data-title="' . esc_attr( $title ) . '"'
				. ' data-desc="' . esc_attr( $desc ) . '"'
				. ' data-index="' . $index . '">';

			echo '<div class="eds-gallery__media"' . $media_style . '>';

			// Open link.
			if ( $builtin_lb ) {
				echo '<a class="eds-gallery__lb-trigger" href="' . esc_url( $img_full_url ) . '" aria-label="' . esc_attr( $title ) . '">';
			} elseif ( $elementor_lb ) {
				echo '<a class="eds-gallery__lb-trigger"'
					. ' href="' . esc_url( $img_full_url ) . '"'
					. ' data-elementor-open-lightbox="yes"'
					. ' data-elementor-lightbox-slideshow="' . esc_attr( $widget_id ) . '"'
					. ' data-elementor-lightbox-title="' . esc_attr( $title ) . '">';
			} elseif ( $has_link ) {
				$target   = ! empty( $item['link']['is_external'] ) ? ' target="_blank" rel="noopener noreferrer"' : '';
				$nofollow = ! empty( $item['link']['nofollow'] ) ? ' rel="nofollow"' : '';
				echo '<a href="' . esc_url( $item['link']['url'] ) . '"' . $target . $nofollow . '>';
			}

			echo $img_html;

			// Overlay.
			if ( $overlay !== 'none' && ( $show_title || $show_desc ) ) {
				echo '<div class="eds-gallery__overlay"><div class="eds-gallery__caption">';
				if ( $show_title && $title ) {
					echo '<span class="eds-gallery__caption-title">' . esc_html( $title ) . '</span>';
				}
				if ( $show_desc && $desc ) {
					echo '<span class="eds-gallery__caption-desc">' . esc_html( $desc ) . '</span>';
				}
				echo '</div></div>';
			}

			// Close link.
			if ( $lightbox || $has_link ) {
				echo '</a>';
			}

			echo '</div>'; // .eds-gallery__media
			echo '</div>'; // .eds-gallery__item
		}

		echo '</div>'; // .eds-gallery
		echo '</div>'; // .eds-gallery-wrap

		// Lightbox markup.
		if ( $builtin_lb ) {
			echo '<div class="eds-lightbox" id="' . esc_attr( $widget_id . '-lb' ) . '" role="dialog" aria-modal="true" hidden>';
			echo '<div class="eds-lightbox__backdrop"></div>';
			echo '<div class="eds-lightbox__stage">';
			echo '<button class="eds-lightbox__close" aria-label="' . esc_attr__( 'Close', 'eds-global-settings' ) . '">&#x2715;</button>';
			if ( $lb_counter ) {
				echo '<div class="eds-lightbox__counter"></div>';
			}
			echo '<button class="eds-lightbox__prev" aria-label="' . esc_attr__( 'Previous', 'eds-global-settings' ) . '">&#8249;</button>';
			echo '<button class="eds-lightbox__next" aria-label="' . esc_attr__( 'Next', 'eds-global-settings' ) . '">&#8250;</button>';
			echo '<div class="eds-lightbox__image-wrap"><img class="eds-lightbox__img" src="" alt=""></div>';
			if ( $lb_caption ) {
				echo '<div class="eds-lightbox__caption"><p class="eds-lightbox__caption-title"></p><p class="eds-lightbox__caption-desc"></p></div>';
			}
			echo '</div>'; // .eds-lightbox__stage
			echo '</div>'; // .eds-lightbox
		}
	}
}
