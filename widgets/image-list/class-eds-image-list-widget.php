<?php
/**
 * EDS Image List Widget — Image-based list items, equivalent to Elementor Icon List.
 *
 * @package EDS_Global_Settings
 * @since   1.4.0
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

/**
 * Class EDS_Image_List_Widget
 */
class EDS_Image_List_Widget extends \Elementor\Widget_Base {

	// ─── Identity ─────────────────────────────────────────────────────────────

	public function get_name(): string        { return 'eds-image-list'; }
	public function get_title(): string       { return __( 'EDS Image List', 'eds-global-settings' ); }
	public function get_icon(): string        { return 'eicon-bullet-list'; }
	public function get_categories(): array   { return [ 'eds-elements', 'general' ]; }
	public function get_keywords(): array     { return [ 'image', 'list', 'icon', 'items', 'eds' ]; }
	public function get_style_depends(): array  { return [ 'eds-widget-image-list' ]; }
	public function get_script_depends(): array { return []; }

	// ─── Controls ─────────────────────────────────────────────────────────────

	protected function register_controls(): void {
		$this->section_content();
		$this->section_style_list();
		$this->section_style_item();
		$this->section_style_image();
		$this->section_style_text();
	}

	// ── Content ────────────────────────────────────────────────────────────────

	private function section_content(): void {
		$this->start_controls_section( 'section_content', [
			'label' => __( 'Image List', 'eds-global-settings' ),
			'tab'   => Controls_Manager::TAB_CONTENT,
		] );

		$repeater = new Repeater();

		$repeater->add_control( 'image', [
			'label'   => __( 'Image', 'eds-global-settings' ),
			'type'    => Controls_Manager::MEDIA,
			'default' => [ 'url' => Utils::get_placeholder_image_src() ],
		] );

		$repeater->add_group_control( Group_Control_Image_Size::get_type(), [
			'name'    => 'image',
			'default' => 'thumbnail',
		] );

		$repeater->add_control( 'text', [
			'label'       => __( 'Text', 'eds-global-settings' ),
			'type'        => Controls_Manager::TEXT,
			'default'     => __( 'List Item', 'eds-global-settings' ),
			'label_block' => true,
			'dynamic'     => [ 'active' => true ],
		] );

		$repeater->add_control( 'link', [
			'label'   => __( 'Link', 'eds-global-settings' ),
			'type'    => Controls_Manager::URL,
			'dynamic' => [ 'active' => true ],
		] );

		$repeater->add_control( 'text_color_override', [
			'label'     => __( 'Custom Text Colour', 'eds-global-settings' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => [
				'{{WRAPPER}} {{CURRENT_ITEM}} .eds-image-list__text' => 'color: {{VALUE}};',
			],
			'separator' => 'before',
		] );

		$this->add_control( 'image_list', [
			'label'       => __( 'Items', 'eds-global-settings' ),
			'type'        => Controls_Manager::REPEATER,
			'fields'      => $repeater->get_controls(),
			'default'     => [
				[ 'text' => __( 'List Item 1', 'eds-global-settings' ), 'image' => [ 'url' => Utils::get_placeholder_image_src() ] ],
				[ 'text' => __( 'List Item 2', 'eds-global-settings' ), 'image' => [ 'url' => Utils::get_placeholder_image_src() ] ],
				[ 'text' => __( 'List Item 3', 'eds-global-settings' ), 'image' => [ 'url' => Utils::get_placeholder_image_src() ] ],
			],
			'title_field' => '{{{ text }}}',
		] );

		$this->add_control( 'layout', [
			'label'        => __( 'Layout', 'eds-global-settings' ),
			'type'         => Controls_Manager::CHOOSE,
			'default'      => 'vertical',
			'options'      => [
				'vertical'   => [ 'title' => __( 'Default', 'eds-global-settings' ), 'icon' => 'eicon-editor-list-ul' ],
				'horizontal' => [ 'title' => __( 'Inline',  'eds-global-settings' ), 'icon' => 'eicon-ellipsis-h' ],
				'grid'       => [ 'title' => __( 'Grid',    'eds-global-settings' ), 'icon' => 'eicon-gallery-grid' ],
			],
			'prefix_class' => 'eds-image-list--',
			'render_type'  => 'template',
			'separator'    => 'before',
		] );

		$this->add_responsive_control( 'grid_columns', [
			'label'          => __( 'Columns', 'eds-global-settings' ),
			'type'           => Controls_Manager::SELECT,
			'default'        => '3',
			'tablet_default' => '2',
			'mobile_default' => '1',
			'options'        => [
				'1' => '1', '2' => '2', '3' => '3',
				'4' => '4', '5' => '5', '6' => '6',
			],
			'condition'  => [ 'layout' => 'grid' ],
			'selectors'  => [
				'{{WRAPPER}}.eds-image-list--grid .eds-image-list' => 'grid-template-columns: repeat({{VALUE}}, 1fr);',
			],
		] );

		$this->add_control( 'item_tag', [
			'label'   => __( 'Item HTML Tag', 'eds-global-settings' ),
			'type'    => Controls_Manager::SELECT,
			'default' => 'li',
			'options' => [
				'li'   => 'LI',
				'div'  => 'DIV',
				'span' => 'SPAN',
			],
		] );

		$this->end_controls_section();
	}

	// ── Style: List ────────────────────────────────────────────────────────────

	private function section_style_list(): void {
		$this->start_controls_section( 'section_style_list', [
			'label' => __( 'List', 'eds-global-settings' ),
			'tab'   => Controls_Manager::TAB_STYLE,
		] );

		$this->add_responsive_control( 'row_gap', [
			'label'      => __( 'Row Gap', 'eds-global-settings' ),
			'type'       => Controls_Manager::SLIDER,
			'size_units' => [ 'px', 'em' ],
			'default'    => [ 'size' => 8, 'unit' => 'px' ],
			'range'      => [ 'px' => [ 'min' => 0, 'max' => 100 ] ],
			'selectors'  => [
				'{{WRAPPER}} .eds-image-list'                => 'row-gap: {{SIZE}}{{UNIT}};',
				'{{WRAPPER}}.eds-image-list--horizontal .eds-image-list' => 'column-gap: {{SIZE}}{{UNIT}};',
			],
		] );

		$this->add_responsive_control( 'column_gap', [
			'label'      => __( 'Column Gap (Horizontal only)', 'eds-global-settings' ),
			'type'       => Controls_Manager::SLIDER,
			'size_units' => [ 'px', 'em' ],
			'default'    => [ 'size' => 24, 'unit' => 'px' ],
			'range'      => [ 'px' => [ 'min' => 0, 'max' => 100 ] ],
			'selectors'  => [
				'{{WRAPPER}}.eds-image-list--horizontal .eds-image-list' => 'column-gap: {{SIZE}}{{UNIT}};',
			],
			'condition'  => [ 'layout' => 'horizontal' ],
		] );

		// Divider.
		$this->add_control( 'divider', [
			'label'        => __( 'Divider', 'eds-global-settings' ),
			'type'         => Controls_Manager::SWITCHER,
			'label_on'     => __( 'On', 'eds-global-settings' ),
			'label_off'    => __( 'Off', 'eds-global-settings' ),
			'return_value' => 'yes',
			'separator'    => 'before',
		] );

		$this->add_control( 'divider_style', [
			'label'     => __( 'Style', 'eds-global-settings' ),
			'type'      => Controls_Manager::SELECT,
			'default'   => 'solid',
			'options'   => [
				'solid'  => __( 'Solid', 'eds-global-settings' ),
				'dashed' => __( 'Dashed', 'eds-global-settings' ),
				'dotted' => __( 'Dotted', 'eds-global-settings' ),
			],
			'condition' => [ 'divider' => 'yes' ],
			'selectors' => [
				'{{WRAPPER}} .eds-image-list__item:not(:last-child)' => 'border-bottom-style: {{VALUE}};',
			],
		] );

		$this->add_control( 'divider_weight', [
			'label'      => __( 'Weight', 'eds-global-settings' ),
			'type'       => Controls_Manager::SLIDER,
			'size_units' => [ 'px' ],
			'default'    => [ 'size' => 1 ],
			'range'      => [ 'px' => [ 'min' => 1, 'max' => 10 ] ],
			'condition'  => [ 'divider' => 'yes' ],
			'selectors'  => [
				'{{WRAPPER}} .eds-image-list__item:not(:last-child)' => 'border-bottom-width: {{SIZE}}px;',
			],
		] );

		$this->add_control( 'divider_color', [
			'label'     => __( 'Colour', 'eds-global-settings' ),
			'type'      => Controls_Manager::COLOR,
			'default'   => '#e5e7eb',
			'condition' => [ 'divider' => 'yes' ],
			'selectors' => [
				'{{WRAPPER}} .eds-image-list__item:not(:last-child)' => 'border-bottom-color: {{VALUE}};',
			],
		] );

		$this->add_responsive_control( 'divider_padding', [
			'label'      => __( 'Divider Spacing', 'eds-global-settings' ),
			'type'       => Controls_Manager::SLIDER,
			'size_units' => [ 'px' ],
			'default'    => [ 'size' => 8 ],
			'range'      => [ 'px' => [ 'min' => 0, 'max' => 50 ] ],
			'condition'  => [ 'divider' => 'yes' ],
			'selectors'  => [
				'{{WRAPPER}} .eds-image-list__item:not(:last-child)' => 'padding-bottom: {{SIZE}}px;',
			],
		] );

		$this->end_controls_section();
	}

	// ── Style: Item ────────────────────────────────────────────────────────────

	private function section_style_item(): void {
		$this->start_controls_section( 'section_style_item', [
			'label' => __( 'Item', 'eds-global-settings' ),
			'tab'   => Controls_Manager::TAB_STYLE,
		] );

		$this->add_responsive_control( 'item_align', [
			'label'   => __( 'Vertical Alignment', 'eds-global-settings' ),
			'type'    => Controls_Manager::CHOOSE,
			'default' => 'center',
			'options' => [
				'flex-start' => [ 'title' => __( 'Top',    'eds-global-settings' ), 'icon' => 'eicon-v-align-top' ],
				'center'     => [ 'title' => __( 'Middle', 'eds-global-settings' ), 'icon' => 'eicon-v-align-middle' ],
				'flex-end'   => [ 'title' => __( 'Bottom', 'eds-global-settings' ), 'icon' => 'eicon-v-align-bottom' ],
			],
			'selectors' => [
				'{{WRAPPER}} .eds-image-list__item' => 'align-items: {{VALUE}};',
			],
		] );

		$this->add_responsive_control( 'item_padding', [
			'label'      => __( 'Item Padding', 'eds-global-settings' ),
			'type'       => Controls_Manager::DIMENSIONS,
			'size_units' => [ 'px', 'em' ],
			'selectors'  => [
				'{{WRAPPER}} .eds-image-list__item' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
			],
		] );

		$this->add_group_control( Group_Control_Background::get_type(), [
			'name'     => 'item_background',
			'label'    => __( 'Background', 'eds-global-settings' ),
			'types'    => [ 'classic', 'gradient' ],
			'selector' => '{{WRAPPER}} .eds-image-list__item',
		] );

		$this->add_control( 'item_background_hover_heading', [
			'label'     => __( 'Background (Hover)', 'eds-global-settings' ),
			'type'      => Controls_Manager::HEADING,
			'separator' => 'before',
		] );

		$this->add_group_control( Group_Control_Background::get_type(), [
			'name'     => 'item_background_hover',
			'types'    => [ 'classic', 'gradient' ],
			'selector' => '{{WRAPPER}} .eds-image-list__item:hover',
		] );

		$this->add_group_control( Group_Control_Border::get_type(), [
			'name'      => 'item_border',
			'selector'  => '{{WRAPPER}} .eds-image-list__item',
			'separator' => 'before',
		] );

		$this->add_responsive_control( 'item_border_radius', [
			'label'      => __( 'Border Radius', 'eds-global-settings' ),
			'type'       => Controls_Manager::DIMENSIONS,
			'size_units' => [ 'px', '%' ],
			'selectors'  => [
				'{{WRAPPER}} .eds-image-list__item' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
			],
		] );

		$this->add_group_control( Group_Control_Box_Shadow::get_type(), [
			'name'     => 'item_box_shadow',
			'selector' => '{{WRAPPER}} .eds-image-list__item',
		] );

		$this->end_controls_section();
	}

	// ── Style: Image ───────────────────────────────────────────────────────────

	private function section_style_image(): void {
		$this->start_controls_section( 'section_style_image', [
			'label' => __( 'Image', 'eds-global-settings' ),
			'tab'   => Controls_Manager::TAB_STYLE,
		] );

		$this->add_responsive_control( 'image_width', [
			'label'      => __( 'Width', 'eds-global-settings' ),
			'type'       => Controls_Manager::SLIDER,
			'size_units' => [ 'px', '%', 'em', 'vw' ],
			'default'    => [ 'size' => 40, 'unit' => 'px' ],
			'range'      => [
				'px' => [ 'min' => 10, 'max' => 500 ],
				'%'  => [ 'min' => 5,  'max' => 100 ],
			],
			'selectors'  => [
				'{{WRAPPER}} .eds-image-list__image-wrap' => 'width: {{SIZE}}{{UNIT}}; flex: 0 0 {{SIZE}}{{UNIT}};',
			],
		] );

		$this->add_responsive_control( 'image_height', [
			'label'      => __( 'Height', 'eds-global-settings' ),
			'type'       => Controls_Manager::SLIDER,
			'size_units' => [ 'px', 'em', 'vh' ],
			'default'    => [ 'size' => 40, 'unit' => 'px' ],
			'range'      => [
				'px' => [ 'min' => 10, 'max' => 500 ],
			],
			'selectors'  => [
				'{{WRAPPER}} .eds-image-list__image-wrap' => 'height: {{SIZE}}{{UNIT}};',
				'{{WRAPPER}} .eds-image-list__image-wrap img' => 'height: {{SIZE}}{{UNIT}};',
			],
		] );

		$this->add_control( 'image_fit', [
			'label'     => __( 'Object Fit', 'eds-global-settings' ),
			'type'      => Controls_Manager::SELECT,
			'default'   => 'cover',
			'options'   => [
				'cover'   => __( 'Cover',    'eds-global-settings' ),
				'contain' => __( 'Contain',  'eds-global-settings' ),
				'fill'    => __( 'Fill',     'eds-global-settings' ),
				'none'    => __( 'None',     'eds-global-settings' ),
				'auto'    => __( 'Auto',     'eds-global-settings' ),
			],
			'selectors' => [
				'{{WRAPPER}} .eds-image-list__image-wrap img' => 'object-fit: {{VALUE}};',
			],
		] );

		$this->add_control( 'image_position_css', [
			'label'     => __( 'Object Position', 'eds-global-settings' ),
			'type'      => Controls_Manager::SELECT,
			'default'   => 'center center',
			'options'   => [
				'center center' => __( 'Centre',        'eds-global-settings' ),
				'top center'    => __( 'Top',           'eds-global-settings' ),
				'bottom center' => __( 'Bottom',        'eds-global-settings' ),
				'left center'   => __( 'Left',          'eds-global-settings' ),
				'right center'  => __( 'Right',         'eds-global-settings' ),
				'top left'      => __( 'Top Left',      'eds-global-settings' ),
				'top right'     => __( 'Top Right',     'eds-global-settings' ),
				'bottom left'   => __( 'Bottom Left',   'eds-global-settings' ),
				'bottom right'  => __( 'Bottom Right',  'eds-global-settings' ),
			],
			'selectors' => [
				'{{WRAPPER}} .eds-image-list__image-wrap img' => 'object-position: {{VALUE}};',
			],
		] );

		$this->add_responsive_control( 'image_spacing', [
			'label'      => __( 'Spacing (Image → Text)', 'eds-global-settings' ),
			'type'       => Controls_Manager::SLIDER,
			'size_units' => [ 'px', 'em' ],
			'default'    => [ 'size' => 12, 'unit' => 'px' ],
			'range'      => [ 'px' => [ 'min' => 0, 'max' => 80 ] ],
			'selectors'  => [
				'{{WRAPPER}} .eds-image-list__image-wrap' => 'margin-inline-end: {{SIZE}}{{UNIT}};',
			],
			'separator'  => 'before',
		] );

		// Image Border & Radius.
		$this->add_group_control( Group_Control_Border::get_type(), [
			'name'      => 'image_border',
			'selector'  => '{{WRAPPER}} .eds-image-list__image-wrap',
			'separator' => 'before',
		] );

		$this->add_responsive_control( 'image_border_radius', [
			'label'      => __( 'Border Radius', 'eds-global-settings' ),
			'type'       => Controls_Manager::DIMENSIONS,
			'size_units' => [ 'px', '%' ],
			'selectors'  => [
				'{{WRAPPER}} .eds-image-list__image-wrap'     => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}; overflow: hidden;',
				'{{WRAPPER}} .eds-image-list__image-wrap img' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
			],
		] );

		$this->add_group_control( Group_Control_Box_Shadow::get_type(), [
			'name'     => 'image_box_shadow',
			'selector' => '{{WRAPPER}} .eds-image-list__image-wrap',
		] );

		// CSS Filters.
		$this->start_controls_tabs( 'image_filter_tabs' );

		$this->start_controls_tab( 'image_filter_normal', [
			'label' => __( 'Normal', 'eds-global-settings' ),
		] );
		$this->add_group_control( Group_Control_Css_Filter::get_type(), [
			'name'     => 'image_css_filter',
			'selector' => '{{WRAPPER}} .eds-image-list__image-wrap img',
		] );
		$this->end_controls_tab();

		$this->start_controls_tab( 'image_filter_hover', [
			'label' => __( 'Hover', 'eds-global-settings' ),
		] );
		$this->add_group_control( Group_Control_Css_Filter::get_type(), [
			'name'     => 'image_css_filter_hover',
			'selector' => '{{WRAPPER}} .eds-image-list__item:hover .eds-image-list__image-wrap img',
		] );
		$this->add_control( 'image_hover_transition', [
			'label'      => __( 'Transition Duration (ms)', 'eds-global-settings' ),
			'type'       => Controls_Manager::SLIDER,
			'default'    => [ 'size' => 300 ],
			'range'      => [ 'px' => [ 'min' => 0, 'max' => 1000 ] ],
			'selectors'  => [
				'{{WRAPPER}} .eds-image-list__image-wrap img' => 'transition-duration: {{SIZE}}ms;',
			],
		] );
		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->end_controls_section();
	}

	// ── Style: Text ────────────────────────────────────────────────────────────

	private function section_style_text(): void {
		$this->start_controls_section( 'section_style_text', [
			'label' => __( 'Text', 'eds-global-settings' ),
			'tab'   => Controls_Manager::TAB_STYLE,
		] );

		$this->add_group_control( Group_Control_Typography::get_type(), [
			'name'     => 'text_typography',
			'selector' => '{{WRAPPER}} .eds-image-list__text',
		] );

		$this->start_controls_tabs( 'text_color_tabs' );

		$this->start_controls_tab( 'text_color_normal', [
			'label' => __( 'Normal', 'eds-global-settings' ),
		] );
		$this->add_control( 'text_color', [
			'label'     => __( 'Colour', 'eds-global-settings' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => [
				'{{WRAPPER}} .eds-image-list__text' => 'color: {{VALUE}};',
			],
		] );
		$this->end_controls_tab();

		$this->start_controls_tab( 'text_color_hover', [
			'label' => __( 'Hover', 'eds-global-settings' ),
		] );
		$this->add_control( 'text_hover_color', [
			'label'     => __( 'Text Colour', 'eds-global-settings' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => [
				'{{WRAPPER}} .eds-image-list__item:hover .eds-image-list__text'                       => 'color: {{VALUE}};',
				'{{WRAPPER}} .eds-image-list__item:hover .eds-image-list__link .eds-image-list__text' => 'color: {{VALUE}};',
			],
		] );
		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->add_control( 'text_indent', [
			'label'      => __( 'Text Indent', 'eds-global-settings' ),
			'type'       => Controls_Manager::SLIDER,
			'size_units' => [ 'px', 'em' ],
			'selectors'  => [
				'{{WRAPPER}} .eds-image-list__text' => 'padding-inline-start: {{SIZE}}{{UNIT}};',
			],
			'separator'  => 'before',
		] );

		$this->end_controls_section();
	}

	// ─── Render ───────────────────────────────────────────────────────────────

	protected function render(): void {
		$settings = $this->get_settings_for_display();
		$items    = $settings['image_list'] ?? [];
		$tag      = in_array( $settings['item_tag'] ?? 'li', [ 'li', 'div', 'span' ], true )
			? $settings['item_tag']
			: 'li';
		$wrapper  = ( 'li' === $tag ) ? 'ul' : 'div';

		if ( empty( $items ) ) {
			return;
		}

		echo '<' . esc_attr( $wrapper ) . ' class="eds-image-list">';

		foreach ( $items as $index => $item ) {
			$item_key = 'item_' . $index;
			$this->add_render_attribute( $item_key, [
				'class' => 'eds-image-list__item elementor-repeater-item-' . ( $item['_id'] ?? '' ),
			] );

			// Build link.
			$has_link = ! empty( $item['link']['url'] );
			if ( $has_link ) {
				$this->add_link_attributes( 'link_' . $index, $item['link'] );
			}

			echo '<' . esc_attr( $tag ) . ' ' . $this->get_render_attribute_string( $item_key ) . '>';

			if ( $has_link ) {
				echo '<a ' . $this->get_render_attribute_string( 'link_' . $index ) . ' class="eds-image-list__link">';
			}

			// Image.
			if ( ! empty( $item['image']['url'] ) ) {
				$image_html = Group_Control_Image_Size::get_attachment_image_html( $item, 'image' );
				if ( ! $image_html && ! empty( $item['image']['id'] ) ) {
					$image_html = wp_get_attachment_image( (int) $item['image']['id'], 'thumbnail' );
				}
				if ( ! $image_html && ! empty( $item['image']['url'] ) ) {
					$image_html = '<img src="' . esc_url( $item['image']['url'] ) . '" alt="' . esc_attr( $item['text'] ?? '' ) . '">';
				}
				echo '<span class="eds-image-list__image-wrap">' . $image_html . '</span>';
			}

			// Text.
			if ( ! empty( $item['text'] ) ) {
				echo '<span class="eds-image-list__text">' . esc_html( $item['text'] ) . '</span>';
			}

			if ( $has_link ) {
				echo '</a>';
			}

			echo '</' . esc_attr( $tag ) . '>';
		}

		echo '</' . esc_attr( $wrapper ) . '>';
	}
}
