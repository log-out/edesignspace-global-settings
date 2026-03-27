<?php
/**
 * EDS Quick Gallery Widget
 *
 * Bulk-select images from the media library, choose a layout preset,
 * apply styles. Uses Elementor's native lightbox. Zero per-image setup.
 *
 * @package EDS_Global_Settings
 * @since   1.6.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Elementor\Controls_Manager;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Css_Filter;
use Elementor\Group_Control_Typography;

class EDS_Quick_Gallery_Widget extends \Elementor\Widget_Base {

	public function get_name(): string         { return 'eds-quick-gallery'; }
	public function get_title(): string        { return __( 'EDS Quick Gallery', 'eds-global-settings' ); }
	public function get_icon(): string         { return 'eicon-gallery-grid'; }
	public function get_categories(): array    { return [ 'eds-elements', 'general' ]; }
	public function get_keywords(): array      { return [ 'gallery', 'images', 'grid', 'quick', 'bulk', 'eds' ]; }
	public function get_style_depends(): array  { return [ 'eds-widget-quick-gallery' ]; }
	public function get_script_depends(): array { return []; }

	// ─── Controls ─────────────────────────────────────────────────────────────

	protected function register_controls(): void {

		// ── Content ─────────────────────────────────────────────────────────────

		$this->start_controls_section( 'section_images', [
			'label' => __( 'Images', 'eds-global-settings' ),
			'tab'   => Controls_Manager::TAB_CONTENT,
		] );

		$this->add_control( 'images', [
			'label'      => __( 'Add Images', 'eds-global-settings' ),
			'type'       => Controls_Manager::GALLERY,
			'show_label' => false,
			'dynamic'    => [ 'active' => true ],
		] );

		$this->add_control( 'image_size', [
			'label'   => __( 'Image Resolution', 'eds-global-settings' ),
			'type'    => Controls_Manager::SELECT,
			'default' => 'large',
			'options' => [
				'thumbnail' => 'Thumbnail (150px)',
				'medium'    => 'Medium (300px)',
				'large'     => 'Large (1024px)',
				'full'      => 'Full',
			],
		] );

		$this->add_control( 'lightbox', [
			'label'        => __( 'Open in Lightbox', 'eds-global-settings' ),
			'type'         => Controls_Manager::SWITCHER,
			'return_value' => 'yes',
			'default'      => 'yes',
			'separator'    => 'before',
		] );

		$this->end_controls_section();

		// ── Layout ──────────────────────────────────────────────────────────────

		$this->start_controls_section( 'section_layout', [
			'label' => __( 'Layout', 'eds-global-settings' ),
			'tab'   => Controls_Manager::TAB_CONTENT,
		] );

		$this->add_control( 'layout', [
			'label'   => __( 'Layout', 'eds-global-settings' ),
			'type'    => Controls_Manager::SELECT,
			'default' => 'grid',
			'options' => [
				'grid'    => __( 'Grid', 'eds-global-settings' ),
				'masonry' => __( 'Masonry', 'eds-global-settings' ),
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
				'{{WRAPPER}} .eqg-grid'    => 'grid-template-columns: repeat({{VALUE}}, 1fr);',
				'{{WRAPPER}} .eqg-masonry' => 'column-count: {{VALUE}};',
			],
		] );

		$this->add_control( 'aspect_ratio', [
			'label'     => __( 'Image Height', 'eds-global-settings' ),
			'type'      => Controls_Manager::SELECT,
			'default'   => '1-1',
			'options'   => [
				'auto'   => __( 'Auto (natural height)', 'eds-global-settings' ),
				'1-1'    => '1:1 — Square',
				'4-3'    => '4:3 — Standard',
				'16-9'   => '16:9 — Widescreen',
				'3-2'    => '3:2 — Photo',
				'2-3'    => '2:3 — Portrait',
				'custom' => __( 'Custom Height', 'eds-global-settings' ),
			],
			'condition' => [ 'layout' => 'grid' ],
		] );

		$this->add_responsive_control( 'custom_height', [
			'label'      => __( 'Custom Height', 'eds-global-settings' ),
			'type'       => Controls_Manager::SLIDER,
			'size_units' => [ 'px', 'vh' ],
			'default'    => [ 'size' => 280, 'unit' => 'px' ],
			'range'      => [ 'px' => [ 'min' => 50, 'max' => 1000 ], 'vh' => [ 'min' => 5, 'max' => 100 ] ],
			'condition'  => [ 'layout' => 'grid', 'aspect_ratio' => 'custom' ],
			'selectors'  => [
				'{{WRAPPER}} .eqg-item__media' => 'padding-bottom: 0 !important; height: {{SIZE}}{{UNIT}};',
			],
		] );

		$this->end_controls_section();

		// ── Style ───────────────────────────────────────────────────────────────

		$this->start_controls_section( 'section_style', [
			'label' => __( 'Images', 'eds-global-settings' ),
			'tab'   => Controls_Manager::TAB_STYLE,
		] );

		$this->add_responsive_control( 'gap', [
			'label'      => __( 'Gap', 'eds-global-settings' ),
			'type'       => Controls_Manager::SLIDER,
			'size_units' => [ 'px' ],
			'default'    => [ 'size' => 8 ],
			'range'      => [ 'px' => [ 'min' => 0, 'max' => 80 ] ],
			'selectors'  => [
				'{{WRAPPER}} .eqg-grid'    => 'gap: {{SIZE}}px;',
				'{{WRAPPER}} .eqg-masonry' => 'column-gap: {{SIZE}}px;',
				'{{WRAPPER}} .eqg-masonry .eqg-item' => 'margin-bottom: {{SIZE}}px;',
			],
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
			'selectors' => [
				'{{WRAPPER}} .eqg-item__media img' => 'object-fit: {{VALUE}};',
			],
		] );

		$this->add_responsive_control( 'item_padding', [
			'label'      => __( 'Item Padding', 'eds-global-settings' ),
			'type'       => Controls_Manager::DIMENSIONS,
			'size_units' => [ 'px', 'em' ],
			'selectors'  => [
				'{{WRAPPER}} .eqg-item' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
			],
		] );

		$this->add_group_control( Group_Control_Border::get_type(), [
			'name'      => 'image_border',
			'selector'  => '{{WRAPPER}} .eqg-item',
			'separator' => 'before',
		] );

		$this->add_responsive_control( 'image_border_radius', [
			'label'      => __( 'Border Radius', 'eds-global-settings' ),
			'type'       => Controls_Manager::DIMENSIONS,
			'size_units' => [ 'px', '%' ],
			'selectors'  => [
				'{{WRAPPER}} .eqg-item'             => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}; overflow: hidden;',
				'{{WRAPPER}} .eqg-item__media img'  => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
			],
		] );

		$this->add_group_control( Group_Control_Box_Shadow::get_type(), [
			'name'     => 'image_shadow',
			'selector' => '{{WRAPPER}} .eqg-item',
		] );

		// Hover effects.
		$this->add_control( 'hover_effect', [
			'label'     => __( 'Hover Effect', 'eds-global-settings' ),
			'type'      => Controls_Manager::SELECT,
			'default'   => 'zoom-in',
			'options'   => [
				'none'     => __( 'None', 'eds-global-settings' ),
				'zoom-in'  => __( 'Zoom In', 'eds-global-settings' ),
				'zoom-out' => __( 'Zoom Out', 'eds-global-settings' ),
				'dim'      => __( 'Dim', 'eds-global-settings' ),
				'brighten' => __( 'Brighten', 'eds-global-settings' ),
			],
			'separator' => 'before',
		] );

		$this->add_control( 'hover_duration', [
			'label'     => __( 'Hover Transition (ms)', 'eds-global-settings' ),
			'type'      => Controls_Manager::SLIDER,
			'default'   => [ 'size' => 350 ],
			'range'     => [ 'px' => [ 'min' => 0, 'max' => 1500 ] ],
			'selectors' => [
				'{{WRAPPER}} .eqg-item__media img' => 'transition-duration: {{SIZE}}ms;',
			],
		] );

		// CSS Filters.
		$this->start_controls_tabs( 'tabs_filter', [ 'separator' => 'before' ] );

		$this->start_controls_tab( 'tab_filter_normal', [ 'label' => __( 'Normal', 'eds-global-settings' ) ] );
		$this->add_group_control( Group_Control_Css_Filter::get_type(), [
			'name'     => 'css_filter',
			'selector' => '{{WRAPPER}} .eqg-item__media img',
		] );
		$this->end_controls_tab();

		$this->start_controls_tab( 'tab_filter_hover', [ 'label' => __( 'Hover', 'eds-global-settings' ) ] );
		$this->add_group_control( Group_Control_Css_Filter::get_type(), [
			'name'     => 'css_filter_hover',
			'selector' => '{{WRAPPER}} .eqg-item:hover .eqg-item__media img',
		] );
		$this->end_controls_tab();

		$this->end_controls_tabs();

		// Hover overlay (optional caption / overlay colour).
		$this->add_control( 'overlay_color', [
			'label'     => __( 'Overlay Colour (Hover)', 'eds-global-settings' ),
			'type'      => Controls_Manager::COLOR,
			'default'   => '',
			'selectors' => [
				'{{WRAPPER}} .eqg-item:hover .eqg-item__overlay' => 'opacity: 1;',
				'{{WRAPPER}} .eqg-item__overlay'                  => 'background: {{VALUE}};',
			],
			'separator' => 'before',
		] );

		$this->end_controls_section();
	}

	// ─── Render ───────────────────────────────────────────────────────────────

	protected function render(): void {
		$settings    = $this->get_settings_for_display();
		$images      = $settings['images'] ?? [];
		$layout      = $settings['layout'] ?? 'grid';
		$aspect      = $settings['aspect_ratio'] ?? '1-1';
		$img_size    = $settings['image_size'] ?? 'large';
		$hover       = $settings['hover_effect'] ?? 'zoom-in';
		$lightbox    = 'yes' === ( $settings['lightbox'] ?? 'yes' );

		if ( empty( $images ) ) {
			if ( \Elementor\Plugin::$instance->editor->is_edit_mode() ) {
				echo '<p style="padding:20px;text-align:center;color:#999;">'
					. __( 'Click the widget and select images to display your gallery.', 'eds-global-settings' )
					. '</p>';
			}
			return;
		}

		$aspect_pb = [
			'1-1'  => '100%',
			'4-3'  => '75%',
			'16-9' => '56.25%',
			'3-2'  => '66.67%',
			'2-3'  => '150%',
		];

		$widget_id = 'eqg-' . $this->get_id();

		$wrap_class = 'eqg-wrap eqg-hover-' . esc_attr( $hover );
		$grid_class = $layout === 'masonry' ? 'eqg-masonry' : 'eqg-grid';

		echo '<div class="' . $wrap_class . '">';
		echo '<div class="' . $grid_class . '">';

		foreach ( $images as $index => $image ) {
			$img_id      = (int) ( $image['id'] ?? 0 );
			$img_url     = $image['url'] ?? '';
			$full_url    = $img_id ? ( wp_get_attachment_image_url( $img_id, 'full' ) ?: $img_url ) : $img_url;
			$alt         = $img_id ? get_post_meta( $img_id, '_wp_attachment_image_alt', true ) : '';

			$img_html    = $img_id
				? wp_get_attachment_image( $img_id, $img_size, false, [ 'alt' => $alt ] )
				: ( $img_url ? '<img src="' . esc_url( $img_url ) . '" alt="">' : '' );

			if ( ! $img_html ) {
				continue;
			}

			// Media style (aspect ratio).
			$media_style = '';
			if ( $layout === 'grid' && $aspect !== 'auto' && $aspect !== 'custom' && isset( $aspect_pb[ $aspect ] ) ) {
				$media_style = ' style="padding-bottom:' . $aspect_pb[ $aspect ] . ';"';
			}

			$use_abs = ( $layout === 'grid' && $aspect !== 'auto' );

			echo '<div class="eqg-item">';
			echo '<div class="eqg-item__media' . ( $use_abs ? ' eqg-item__media--ratio' : '' ) . '"' . $media_style . '>';

			if ( $lightbox ) {
				$slideshow_id = esc_attr( $widget_id );
				echo '<a href="' . esc_url( $full_url ) . '"'
					. ' data-elementor-open-lightbox="yes"'
					. ' data-elementor-lightbox-slideshow="' . $slideshow_id . '"'
					. ' data-elementor-lightbox-index="' . $index . '">';
			}

			echo $img_html;

			// Overlay div (for hover overlay colour).
			echo '<div class="eqg-item__overlay"></div>';

			if ( $lightbox ) {
				echo '</a>';
			}

			echo '</div>'; // .eqg-item__media
			echo '</div>'; // .eqg-item
		}

		echo '</div>'; // grid/masonry
		echo '</div>'; // wrap
	}
}
