<?php
/**
 * EDS Blog Posts Widget — Elementor Pro Posts widget equivalent.
 *
 * @package EDS_Global_Settings
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Elementor\Controls_Manager;
use Elementor\Group_Control_Image_Size;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Background;
use Elementor\Group_Control_Css_Filter;
use Elementor\Core\Schemes;

/**
 * Class EDS_Blog_Posts_Widget
 */
class EDS_Blog_Posts_Widget extends \Elementor\Widget_Base {

	// ─── Identity ─────────────────────────────────────────────────────────────

	public function get_name(): string        { return 'eds-blog-posts'; }
	public function get_title(): string       { return __( 'EDS Blog Posts', 'eds-global-settings' ); }
	public function get_icon(): string        { return 'eicon-posts-grid'; }
	public function get_categories(): array   { return [ 'eds-elements', 'general' ]; }
	public function get_keywords(): array     { return [ 'posts', 'blog', 'grid', 'list', 'cards', 'eds' ]; }
	public function get_style_depends(): array  { return [ 'eds-widget-blog-posts' ]; }
	public function get_script_depends(): array { return [ 'eds-widget-blog-posts' ]; }

	// ─── Controls ─────────────────────────────────────────────────────────────

	protected function register_controls(): void {
		$this->section_layout();
		$this->section_query();
		$this->section_pagination();
		$this->section_style_layout();
		$this->section_style_post_block();
		$this->section_style_card();
		$this->section_style_image();
		$this->section_style_content();
		$this->section_style_title();
		$this->section_style_meta();
		$this->section_style_excerpt();
		$this->section_style_button();
		$this->section_style_pagination();
	}

	// ── Layout Section ─────────────────────────────────────────────────────────

	private function section_layout(): void {
		$this->start_controls_section( 'section_layout', [
			'label' => __( 'Layout', 'eds-global-settings' ),
			'tab'   => Controls_Manager::TAB_CONTENT,
		] );

		$this->add_control( 'skin', [
			'label'   => __( 'Skin', 'eds-global-settings' ),
			'type'    => Controls_Manager::SELECT,
			'default' => 'classic',
			'options' => [
				'classic'      => __( 'Classic', 'eds-global-settings' ),
				'cards'        => __( 'Cards', 'eds-global-settings' ),
				'full_content' => __( 'Full Content', 'eds-global-settings' ),
			],
		] );

		$this->add_responsive_control( 'columns', [
			'label'          => __( 'Columns', 'eds-global-settings' ),
			'type'           => Controls_Manager::SELECT,
			'default'        => '3',
			'tablet_default' => '2',
			'mobile_default' => '1',
			'options' => [
				'1' => '1', '2' => '2', '3' => '3',
				'4' => '4', '5' => '5', '6' => '6',
			],
			'selectors' => [
				'{{WRAPPER}} .eds-posts__grid' => '--eds-cols: {{VALUE}};',
			],
		] );

		$this->add_control( 'posts_per_page', [
			'label'   => __( 'Posts Per Page', 'eds-global-settings' ),
			'type'    => Controls_Manager::NUMBER,
			'default' => 6,
			'min'     => 1,
			'max'     => 100,
		] );

		$this->add_control( 'image_position', [
			'label'        => __( 'Image Position', 'eds-global-settings' ),
			'type'         => Controls_Manager::SELECT,
			'default'      => 'top',
			'options'      => [
				'top'    => __( 'Top',         'eds-global-settings' ),
				'left'   => __( 'Left',        'eds-global-settings' ),
				'right'  => __( 'Right',       'eds-global-settings' ),
				'bottom' => __( 'Bottom',      'eds-global-settings' ),
				'none'   => __( 'None (Hide)', 'eds-global-settings' ),
			],
			'prefix_class' => 'eds-img-pos-',
			'render_type'  => 'template',
			'condition'    => [ 'skin!' => 'cards' ],
		] );

		$this->add_control( 'content_align_vertical', [
			'label'   => __( 'Vertical Alignment', 'eds-global-settings' ),
			'type'    => Controls_Manager::CHOOSE,
			'default' => 'flex-start',
			'options' => [
				'flex-start' => [ 'title' => __( 'Top',    'eds-global-settings' ), 'icon' => 'eicon-v-align-top' ],
				'center'     => [ 'title' => __( 'Middle', 'eds-global-settings' ), 'icon' => 'eicon-v-align-middle' ],
				'flex-end'   => [ 'title' => __( 'Bottom', 'eds-global-settings' ), 'icon' => 'eicon-v-align-bottom' ],
			],
			'selectors'   => [
				'{{WRAPPER}} .eds-post' => 'align-items: {{VALUE}};',
			],
			'condition'   => [ 'skin!' => 'cards', 'image_position' => [ 'left', 'right' ] ],
			'description' => __( 'Vertical alignment between image and text when position is Left or Right.', 'eds-global-settings' ),
		] );

		$this->add_control( 'open_new_tab', [
			'label'        => __( 'Open in New Tab', 'eds-global-settings' ),
			'type'         => Controls_Manager::SWITCHER,
			'label_on'     => __( 'Yes', 'eds-global-settings' ),
			'label_off'    => __( 'No', 'eds-global-settings' ),
			'return_value' => 'yes',
			'default'      => '',
		] );

		$this->end_controls_section();

		// ── Elements ───────────────────────────────────────────────────────────
		$this->start_controls_section( 'section_elements', [
			'label' => __( 'Elements', 'eds-global-settings' ),
			'tab'   => Controls_Manager::TAB_CONTENT,
		] );

		$this->add_control( 'show_image', [
			'label'        => __( 'Image', 'eds-global-settings' ),
			'type'         => Controls_Manager::SWITCHER,
			'label_on'     => __( 'Show', 'eds-global-settings' ),
			'label_off'    => __( 'Hide', 'eds-global-settings' ),
			'return_value' => 'yes',
			'default'      => 'yes',
		] );

		$this->add_group_control( Group_Control_Image_Size::get_type(), [
			'name'      => 'thumbnail',
			'default'   => 'medium_large',
			'condition' => [ 'show_image' => 'yes' ],
		] );

		$this->add_responsive_control( 'thumbnail_ratio', [
			'label'       => __( 'Image Ratio', 'eds-global-settings' ),
			'type'        => Controls_Manager::SLIDER,
			'default'     => [ 'size' => 0.66, 'unit' => 'px' ],
			'range'       => [ 'px' => [ 'min' => 0.1, 'max' => 2.5, 'step' => 0.01 ] ],
			'selectors'   => [
				'{{WRAPPER}} .eds-post__thumbnail-wrap' => 'padding-bottom: calc( {{SIZE}} * 100% ); height: auto;',
			],
			'condition'   => [ 'show_image' => 'yes' ],
			'render_type' => 'ui',
			'description' => __( 'Height as a ratio of width. 0.66 = landscape, 1.0 = square, 1.5 = portrait.', 'eds-global-settings' ),
		] );

		$this->add_responsive_control( 'image_custom_width', [
			'label'      => __( 'Image Width', 'eds-global-settings' ),
			'type'       => Controls_Manager::SLIDER,
			'size_units' => [ 'px', '%' ],
			'default'    => [ 'size' => '', 'unit' => '%' ],
			'range'      => [
				'%'  => [ 'min' => 5, 'max' => 100 ],
				'px' => [ 'min' => 50, 'max' => 800 ],
			],
			'selectors'  => [
				'{{WRAPPER}} .eds-post__thumbnail' => 'flex: 0 0 {{SIZE}}{{UNIT}}; max-width: {{SIZE}}{{UNIT}};',
			],
			'condition'  => [ 'show_image' => 'yes' ],
			'description' => __( 'Sets a fixed width. Most useful when Image Position is Left or Right.', 'eds-global-settings' ),
		] );

		$this->add_control( 'show_title', [
			'label'        => __( 'Title', 'eds-global-settings' ),
			'type'         => Controls_Manager::SWITCHER,
			'label_on'     => __( 'Show', 'eds-global-settings' ),
			'label_off'    => __( 'Hide', 'eds-global-settings' ),
			'return_value' => 'yes',
			'default'      => 'yes',
			'separator'    => 'before',
		] );

		$this->add_control( 'title_tag', [
			'label'     => __( 'Title HTML Tag', 'eds-global-settings' ),
			'type'      => Controls_Manager::SELECT,
			'default'   => 'h3',
			'options'   => [
				'h1' => 'H1', 'h2' => 'H2', 'h3' => 'H3',
				'h4' => 'H4', 'h5' => 'H5', 'h6' => 'H6',
			],
			'condition' => [ 'show_title' => 'yes' ],
		] );

		$this->add_control( 'show_excerpt', [
			'label'        => __( 'Excerpt', 'eds-global-settings' ),
			'type'         => Controls_Manager::SWITCHER,
			'label_on'     => __( 'Show', 'eds-global-settings' ),
			'label_off'    => __( 'Hide', 'eds-global-settings' ),
			'return_value' => 'yes',
			'default'      => 'yes',
			'separator'    => 'before',
		] );

		$this->add_control( 'excerpt_length', [
			'label'     => __( 'Excerpt Length', 'eds-global-settings' ),
			'type'      => Controls_Manager::NUMBER,
			'default'   => 25,
			'min'       => 1,
			'condition' => [ 'show_excerpt' => 'yes', 'skin!' => 'full_content' ],
		] );

		$this->add_control( 'show_meta_data', [
			'label'        => __( 'Meta Data', 'eds-global-settings' ),
			'type'         => Controls_Manager::SWITCHER,
			'label_on'     => __( 'Show', 'eds-global-settings' ),
			'label_off'    => __( 'Hide', 'eds-global-settings' ),
			'return_value' => 'yes',
			'default'      => 'yes',
			'separator'    => 'before',
		] );

		$this->add_control( 'meta_data', [
			'label'       => __( 'Meta Data Items', 'eds-global-settings' ),
			'type'        => Controls_Manager::SELECT2,
			'multiple'    => true,
			'default'     => [ 'author', 'date' ],
			'options'     => [
				'author'   => __( 'Author', 'eds-global-settings' ),
				'date'     => __( 'Date', 'eds-global-settings' ),
				'comments' => __( 'Comments', 'eds-global-settings' ),
				'category' => __( 'Category', 'eds-global-settings' ),
			],
			'condition'  => [ 'show_meta_data' => 'yes' ],
		] );

		$this->add_control( 'meta_separator', [
			'label'     => __( 'Meta Separator', 'eds-global-settings' ),
			'type'      => Controls_Manager::TEXT,
			'default'   => '·',
			'condition' => [ 'show_meta_data' => 'yes' ],
		] );

		$this->add_control( 'show_read_more', [
			'label'        => __( 'Read More Button', 'eds-global-settings' ),
			'type'         => Controls_Manager::SWITCHER,
			'label_on'     => __( 'Show', 'eds-global-settings' ),
			'label_off'    => __( 'Hide', 'eds-global-settings' ),
			'return_value' => 'yes',
			'default'      => 'yes',
			'separator'    => 'before',
		] );

		$this->add_control( 'read_more_text', [
			'label'     => __( 'Read More Text', 'eds-global-settings' ),
			'type'      => Controls_Manager::TEXT,
			'default'   => __( 'Read More »', 'eds-global-settings' ),
			'condition' => [ 'show_read_more' => 'yes' ],
		] );

		$this->end_controls_section();
	}

	// ── Query Section ──────────────────────────────────────────────────────────

	private function section_query(): void {
		$this->start_controls_section( 'section_query', [
			'label' => __( 'Query', 'eds-global-settings' ),
			'tab'   => Controls_Manager::TAB_CONTENT,
		] );

		// Build CPT options.
		$post_type_options = [ 'post' => __( 'Posts', 'eds-global-settings' ) ];
		foreach ( get_post_types( [ 'public' => true, '_builtin' => false ], 'objects' ) as $cpt ) {
			$post_type_options[ $cpt->name ] = $cpt->label;
		}
		$post_type_options['page']        = __( 'Pages', 'eds-global-settings' );
		$post_type_options['by_id']       = __( 'Manual Selection', 'eds-global-settings' );
		$post_type_options['current_query'] = __( 'Current Query', 'eds-global-settings' );

		$this->add_control( 'post_type', [
			'label'   => __( 'Source', 'eds-global-settings' ),
			'type'    => Controls_Manager::SELECT,
			'default' => 'post',
			'options' => $post_type_options,
		] );

		$this->add_control( 'posts_ids', [
			'label'       => __( 'Search & Select', 'eds-global-settings' ),
			'type'        => Controls_Manager::TEXT,
			'placeholder' => __( 'Post IDs, comma-separated', 'eds-global-settings' ),
			'condition'   => [ 'post_type' => 'by_id' ],
			'description' => __( 'Enter comma-separated post IDs.', 'eds-global-settings' ),
		] );

		// Category.
		$categories = get_terms( [ 'taxonomy' => 'category', 'hide_empty' => false ] );
		$cat_options = [ '' => __( '— All Categories —', 'eds-global-settings' ) ];
		if ( ! is_wp_error( $categories ) ) {
			foreach ( $categories as $cat ) {
				$cat_options[ $cat->term_id ] = $cat->name;
			}
		}

		$this->add_control( 'category_ids', [
			'label'       => __( 'Categories', 'eds-global-settings' ),
			'type'        => Controls_Manager::SELECT2,
			'multiple'    => true,
			'options'     => $cat_options,
			'condition'   => [ 'post_type' => 'post' ],
		] );

		// Tags.
		$tags = get_terms( [ 'taxonomy' => 'post_tag', 'hide_empty' => false ] );
		$tag_options = [ '' => __( '— All Tags —', 'eds-global-settings' ) ];
		if ( ! is_wp_error( $tags ) ) {
			foreach ( $tags as $tag ) {
				$tag_options[ $tag->term_id ] = $tag->name;
			}
		}

		$this->add_control( 'tag_ids', [
			'label'     => __( 'Tags', 'eds-global-settings' ),
			'type'      => Controls_Manager::SELECT2,
			'multiple'  => true,
			'options'   => $tag_options,
			'condition' => [ 'post_type' => 'post' ],
		] );

		// Authors.
		$authors        = get_users( [ 'who' => 'authors', 'has_published_posts' => true ] );
		$author_options = [];
		foreach ( $authors as $author ) {
			$author_options[ $author->ID ] = $author->display_name;
		}

		$this->add_control( 'author_ids', [
			'label'    => __( 'Author', 'eds-global-settings' ),
			'type'     => Controls_Manager::SELECT2,
			'multiple' => true,
			'options'  => $author_options,
		] );

		$this->add_control( 'offset', [
			'label'       => __( 'Offset', 'eds-global-settings' ),
			'type'        => Controls_Manager::NUMBER,
			'default'     => 0,
			'min'         => 0,
			'description' => __( 'Skip this number of posts at the start.', 'eds-global-settings' ),
		] );

		$this->add_control( 'orderby', [
			'label'   => __( 'Order By', 'eds-global-settings' ),
			'type'    => Controls_Manager::SELECT,
			'default' => 'date',
			'options' => [
				'date'          => __( 'Date', 'eds-global-settings' ),
				'modified'      => __( 'Last Modified', 'eds-global-settings' ),
				'title'         => __( 'Title', 'eds-global-settings' ),
				'comment_count' => __( 'Comment Count', 'eds-global-settings' ),
				'menu_order'    => __( 'Menu Order', 'eds-global-settings' ),
				'rand'          => __( 'Random', 'eds-global-settings' ),
			],
		] );

		$this->add_control( 'order', [
			'label'   => __( 'Order', 'eds-global-settings' ),
			'type'    => Controls_Manager::SELECT,
			'default' => 'DESC',
			'options' => [
				'ASC'  => __( 'ASC', 'eds-global-settings' ),
				'DESC' => __( 'DESC', 'eds-global-settings' ),
			],
		] );

		$this->add_control( 'ignore_sticky_posts', [
			'label'        => __( 'Ignore Sticky Posts', 'eds-global-settings' ),
			'type'         => Controls_Manager::SWITCHER,
			'return_value' => 'yes',
			'default'      => 'yes',
		] );

		$this->add_control( 'exclude_current', [
			'label'        => __( 'Exclude Current Post', 'eds-global-settings' ),
			'type'         => Controls_Manager::SWITCHER,
			'return_value' => 'yes',
			'default'      => '',
		] );

		$this->end_controls_section();
	}

	// ── Pagination Section ─────────────────────────────────────────────────────

	private function section_pagination(): void {
		$this->start_controls_section( 'section_pagination', [
			'label' => __( 'Pagination', 'eds-global-settings' ),
			'tab'   => Controls_Manager::TAB_CONTENT,
		] );

		$this->add_control( 'pagination_type', [
			'label'   => __( 'Pagination', 'eds-global-settings' ),
			'type'    => Controls_Manager::SELECT,
			'default' => 'none',
			'options' => [
				'none'            => __( 'None', 'eds-global-settings' ),
				'numbers'         => __( 'Numbers', 'eds-global-settings' ),
				'prev_next'       => __( 'Previous / Next', 'eds-global-settings' ),
				'numbers_and_prev_next' => __( 'Numbers + Prev/Next', 'eds-global-settings' ),
				'load_more'       => __( 'Load More', 'eds-global-settings' ),
				'infinite_scroll' => __( 'Infinite Scroll', 'eds-global-settings' ),
			],
		] );

		$this->add_control( 'pagination_page_limit', [
			'label'     => __( 'Page Limit', 'eds-global-settings' ),
			'type'      => Controls_Manager::NUMBER,
			'default'   => 5,
			'condition' => [
				'pagination_type!' => [ 'none', 'load_more', 'infinite_scroll' ],
			],
		] );

		$this->add_control( 'pagination_prev_label', [
			'label'     => __( '« Previous Label', 'eds-global-settings' ),
			'type'      => Controls_Manager::TEXT,
			'default'   => __( '« Prev', 'eds-global-settings' ),
			'condition' => [
				'pagination_type' => [ 'prev_next', 'numbers_and_prev_next' ],
			],
		] );

		$this->add_control( 'pagination_next_label', [
			'label'     => __( 'Next Label »', 'eds-global-settings' ),
			'type'      => Controls_Manager::TEXT,
			'default'   => __( 'Next »', 'eds-global-settings' ),
			'condition' => [
				'pagination_type' => [ 'prev_next', 'numbers_and_prev_next' ],
			],
		] );

		$this->add_control( 'load_more_label', [
			'label'     => __( 'Load More Text', 'eds-global-settings' ),
			'type'      => Controls_Manager::TEXT,
			'default'   => __( 'Load More', 'eds-global-settings' ),
			'condition' => [ 'pagination_type' => 'load_more' ],
		] );

		$this->add_control( 'no_more_posts_label', [
			'label'     => __( 'No More Posts Message', 'eds-global-settings' ),
			'type'      => Controls_Manager::TEXT,
			'default'   => __( 'No more posts to show.', 'eds-global-settings' ),
			'condition' => [
				'pagination_type' => [ 'load_more', 'infinite_scroll' ],
			],
		] );

		$this->end_controls_section();
	}

	// ── Style Sections ─────────────────────────────────────────────────────────

	private function section_style_layout(): void {
		$this->start_controls_section( 'section_style_layout', [
			'label' => __( 'Layout', 'eds-global-settings' ),
			'tab'   => Controls_Manager::TAB_STYLE,
		] );

		$this->add_responsive_control( 'column_gap', [
			'label'      => __( 'Columns Gap', 'eds-global-settings' ),
			'type'       => Controls_Manager::SLIDER,
			'size_units' => [ 'px' ],
			'default'    => [ 'size' => 24 ],
			'range'      => [ 'px' => [ 'min' => 0, 'max' => 100 ] ],
			'selectors'  => [
				'{{WRAPPER}} .eds-posts__grid' => '--eds-col-gap: {{SIZE}}px;',
			],
		] );

		$this->add_responsive_control( 'row_gap', [
			'label'      => __( 'Rows Gap', 'eds-global-settings' ),
			'type'       => Controls_Manager::SLIDER,
			'size_units' => [ 'px' ],
			'default'    => [ 'size' => 32 ],
			'range'      => [ 'px' => [ 'min' => 0, 'max' => 100 ] ],
			'selectors'  => [
				'{{WRAPPER}} .eds-posts__grid' => '--eds-row-gap: {{SIZE}}px;',
			],
		] );

		$this->end_controls_section();
	}

	private function section_style_post_block(): void {
		$this->start_controls_section( 'section_style_post_block', [
			'label' => __( 'Post Block', 'eds-global-settings' ),
			'tab'   => Controls_Manager::TAB_STYLE,
		] );

		// The selector targets the card wrapper for Cards skin, and the post article for Classic/Full Content.
		// We use a combined selector so it works for all skins.
		$block_selector = '{{WRAPPER}} .eds-posts--cards .eds-post__card, {{WRAPPER}} .eds-posts--classic .eds-post, {{WRAPPER}} .eds-posts--full_content .eds-post';

		$this->add_control( 'post_block_heading_bg', [
			'label'     => __( 'Background', 'eds-global-settings' ),
			'type'      => Controls_Manager::HEADING,
			'separator' => 'before',
		] );

		$this->add_group_control( Group_Control_Background::get_type(), [
			'name'     => 'post_block_bg',
			'label'    => __( 'Background', 'eds-global-settings' ),
			'types'    => [ 'classic', 'gradient' ],
			'selector' => $block_selector,
		] );

		$this->add_control( 'post_block_heading_border', [
			'label'     => __( 'Border', 'eds-global-settings' ),
			'type'      => Controls_Manager::HEADING,
			'separator' => 'before',
		] );

		$this->add_group_control( Group_Control_Border::get_type(), [
			'name'     => 'post_block_border',
			'selector' => $block_selector,
		] );

		$this->add_responsive_control( 'post_block_border_radius', [
			'label'      => __( 'Border Radius', 'eds-global-settings' ),
			'type'       => Controls_Manager::DIMENSIONS,
			'size_units' => [ 'px', '%' ],
			'selectors'  => [
				$block_selector => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}; overflow: hidden;',
			],
		] );

		$this->add_group_control( Group_Control_Box_Shadow::get_type(), [
			'name'      => 'post_block_box_shadow',
			'label'     => __( 'Box Shadow', 'eds-global-settings' ),
			'selector'  => $block_selector,
			'separator' => 'before',
		] );

		$this->add_responsive_control( 'post_block_padding', [
			'label'      => __( 'Padding', 'eds-global-settings' ),
			'type'       => Controls_Manager::DIMENSIONS,
			'size_units' => [ 'px', 'em', '%' ],
			'separator'  => 'before',
			'selectors'  => [
				// Cards: pad the card wrapper
				'{{WRAPPER}} .eds-posts--cards .eds-post__card'           => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				// Classic / Full Content: pad the article
				'{{WRAPPER}} .eds-posts--classic .eds-post'               => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				'{{WRAPPER}} .eds-posts--full_content .eds-post'          => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
			],
		] );

		$this->add_responsive_control( 'post_block_margin', [
			'label'      => __( 'Margin', 'eds-global-settings' ),
			'type'       => Controls_Manager::DIMENSIONS,
			'size_units' => [ 'px', 'em' ],
			'selectors'  => [
				'{{WRAPPER}} .eds-posts--cards .eds-post__card'           => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				'{{WRAPPER}} .eds-posts--classic .eds-post'               => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				'{{WRAPPER}} .eds-posts--full_content .eds-post'          => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
			],
		] );

		// ── Hover states ────────────────────────────────────────────────────────
		$this->add_control( 'post_block_heading_hover', [
			'label'     => __( 'Hover State', 'eds-global-settings' ),
			'type'      => Controls_Manager::HEADING,
			'separator' => 'before',
		] );

		$this->add_group_control( Group_Control_Background::get_type(), [
			'name'     => 'post_block_bg_hover',
			'label'    => __( 'Background on Hover', 'eds-global-settings' ),
			'types'    => [ 'classic', 'gradient' ],
			'selector' => '{{WRAPPER}} .eds-posts--cards .eds-post__card:hover, {{WRAPPER}} .eds-posts--classic .eds-post:hover, {{WRAPPER}} .eds-posts--full_content .eds-post:hover',
		] );

		$this->add_group_control( Group_Control_Border::get_type(), [
			'name'     => 'post_block_border_hover',
			'selector' => '{{WRAPPER}} .eds-posts--cards .eds-post__card:hover, {{WRAPPER}} .eds-posts--classic .eds-post:hover, {{WRAPPER}} .eds-posts--full_content .eds-post:hover',
		] );

		$this->add_group_control( Group_Control_Box_Shadow::get_type(), [
			'name'     => 'post_block_box_shadow_hover',
			'label'    => __( 'Box Shadow on Hover', 'eds-global-settings' ),
			'selector' => '{{WRAPPER}} .eds-posts--cards .eds-post__card:hover, {{WRAPPER}} .eds-posts--classic .eds-post:hover, {{WRAPPER}} .eds-posts--full_content .eds-post:hover',
		] );

		$this->add_control( 'post_block_hover_transition', [
			'label'      => __( 'Hover Transition Duration (ms)', 'eds-global-settings' ),
			'type'       => Controls_Manager::SLIDER,
			'default'    => [ 'size' => 300 ],
			'range'      => [ 'px' => [ 'min' => 0, 'max' => 1000 ] ],
			'selectors'  => [
				'{{WRAPPER}} .eds-post__card, {{WRAPPER}} .eds-posts--classic .eds-post, {{WRAPPER}} .eds-posts--full_content .eds-post' => 'transition-duration: {{SIZE}}ms;',
			],
		] );

		$this->end_controls_section();
	}

	private function section_style_card(): void {
		$this->start_controls_section( 'section_style_card', [
			'label'     => __( 'Card', 'eds-global-settings' ),
			'tab'       => Controls_Manager::TAB_STYLE,
			'condition' => [ 'skin' => 'cards' ],
		] );

		$this->add_control( 'card_bg_color', [
			'label'     => __( 'Background Color', 'eds-global-settings' ),
			'type'      => Controls_Manager::COLOR,
			'default'   => '#ffffff',
			'selectors' => [
				'{{WRAPPER}} .eds-post__card' => 'background-color: {{VALUE}};',
			],
		] );

		$this->add_group_control( Group_Control_Box_Shadow::get_type(), [
			'name'     => 'card_box_shadow',
			'selector' => '{{WRAPPER}} .eds-post__card',
		] );

		$this->add_group_control( Group_Control_Border::get_type(), [
			'name'     => 'card_border',
			'selector' => '{{WRAPPER}} .eds-post__card',
		] );

		$this->add_responsive_control( 'card_border_radius', [
			'label'      => __( 'Border Radius', 'eds-global-settings' ),
			'type'       => Controls_Manager::DIMENSIONS,
			'size_units' => [ 'px', '%' ],
			'selectors'  => [
				'{{WRAPPER}} .eds-post__card' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}; overflow: hidden;',
			],
		] );

		$this->end_controls_section();
	}

	private function section_style_image(): void {
		$this->start_controls_section( 'section_style_image', [
			'label'     => __( 'Image', 'eds-global-settings' ),
			'tab'       => Controls_Manager::TAB_STYLE,
			'condition' => [ 'show_image' => 'yes' ],
		] );

		$this->add_responsive_control( 'image_border_radius', [
			'label'      => __( 'Border Radius', 'eds-global-settings' ),
			'type'       => Controls_Manager::DIMENSIONS,
			'size_units' => [ 'px', '%' ],
			'selectors'  => [
				'{{WRAPPER}} .eds-post__thumbnail-wrap' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}; overflow: hidden;',
			],
		] );

		$this->start_controls_tabs( 'image_tabs' );

		$this->start_controls_tab( 'image_tab_normal', [ 'label' => __( 'Normal', 'eds-global-settings' ) ] );
		$this->add_group_control( Group_Control_Css_Filter::get_type(), [
			'name'     => 'image_css_filter',
			'selector' => '{{WRAPPER}} .eds-post__thumbnail-wrap img',
		] );
		$this->end_controls_tab();

		$this->start_controls_tab( 'image_tab_hover', [ 'label' => __( 'Hover', 'eds-global-settings' ) ] );
		$this->add_group_control( Group_Control_Css_Filter::get_type(), [
			'name'     => 'image_css_filter_hover',
			'selector' => '{{WRAPPER}} .eds-post:hover .eds-post__thumbnail-wrap img',
		] );
		$this->add_control( 'image_hover_animation', [
			'label'   => __( 'Hover Animation', 'eds-global-settings' ),
			'type'    => Controls_Manager::SELECT,
			'default' => 'zoom-in',
			'options' => [
				''        => __( 'None', 'eds-global-settings' ),
				'zoom-in' => __( 'Zoom In', 'eds-global-settings' ),
				'zoom-out'=> __( 'Zoom Out', 'eds-global-settings' ),
			],
		] );
		$this->end_controls_tab();

		$this->end_controls_tabs();
		$this->end_controls_section();
	}

	private function section_style_content(): void {
		$this->start_controls_section( 'section_style_content', [
			'label' => __( 'Content', 'eds-global-settings' ),
			'tab'   => Controls_Manager::TAB_STYLE,
		] );

		$this->add_responsive_control( 'content_padding', [
			'label'      => __( 'Content Padding', 'eds-global-settings' ),
			'type'       => Controls_Manager::DIMENSIONS,
			'size_units' => [ 'px', 'em', '%' ],
			'default'    => [ 'top' => '20', 'right' => '20', 'bottom' => '20', 'left' => '20', 'unit' => 'px', 'isLinked' => true ],
			'selectors'  => [
				'{{WRAPPER}} .eds-post__text' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
			],
		] );

		$this->end_controls_section();
	}

	private function section_style_title(): void {
		$this->start_controls_section( 'section_style_title', [
			'label'     => __( 'Title', 'eds-global-settings' ),
			'tab'       => Controls_Manager::TAB_STYLE,
			'condition' => [ 'show_title' => 'yes' ],
		] );

		$this->add_group_control( Group_Control_Typography::get_type(), [
			'name'     => 'title_typography',
			'selector' => '{{WRAPPER}} .eds-post__title a',
		] );

		$this->start_controls_tabs( 'title_tabs' );
		$this->start_controls_tab( 'title_tab_normal', [ 'label' => __( 'Normal', 'eds-global-settings' ) ] );
		$this->add_control( 'title_color', [
			'label'     => __( 'Color', 'eds-global-settings' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => [ '{{WRAPPER}} .eds-post__title a' => 'color: {{VALUE}};' ],
		] );
		$this->end_controls_tab();
		$this->start_controls_tab( 'title_tab_hover', [ 'label' => __( 'Hover', 'eds-global-settings' ) ] );
		$this->add_control( 'title_color_hover', [
			'label'     => __( 'Color', 'eds-global-settings' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => [ '{{WRAPPER}} .eds-post__title a:hover' => 'color: {{VALUE}};' ],
		] );
		$this->end_controls_tab();
		$this->end_controls_tabs();

		$this->add_responsive_control( 'title_spacing', [
			'label'      => __( 'Spacing', 'eds-global-settings' ),
			'type'       => Controls_Manager::SLIDER,
			'size_units' => [ 'px' ],
			'default'    => [ 'size' => 10 ],
			'selectors'  => [ '{{WRAPPER}} .eds-post__title' => 'margin-bottom: {{SIZE}}px;' ],
			'separator'  => 'before',
		] );

		$this->end_controls_section();
	}

	private function section_style_meta(): void {
		$this->start_controls_section( 'section_style_meta', [
			'label'     => __( 'Meta', 'eds-global-settings' ),
			'tab'       => Controls_Manager::TAB_STYLE,
			'condition' => [ 'show_meta_data' => 'yes' ],
		] );

		$this->add_group_control( Group_Control_Typography::get_type(), [
			'name'     => 'meta_typography',
			'selector' => '{{WRAPPER}} .eds-post__meta-data',
		] );

		$this->add_control( 'meta_color', [
			'label'     => __( 'Color', 'eds-global-settings' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => [ '{{WRAPPER}} .eds-post__meta-data, {{WRAPPER}} .eds-post__meta-data a' => 'color: {{VALUE}};' ],
		] );

		$this->add_control( 'meta_link_color', [
			'label'     => __( 'Link Color', 'eds-global-settings' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => [ '{{WRAPPER}} .eds-post__meta-data a:hover' => 'color: {{VALUE}};' ],
		] );

		$this->add_responsive_control( 'meta_spacing', [
			'label'      => __( 'Spacing', 'eds-global-settings' ),
			'type'       => Controls_Manager::SLIDER,
			'size_units' => [ 'px' ],
			'default'    => [ 'size' => 10 ],
			'selectors'  => [ '{{WRAPPER}} .eds-post__meta-data' => 'margin-bottom: {{SIZE}}px;' ],
		] );

		$this->end_controls_section();
	}

	private function section_style_excerpt(): void {
		$this->start_controls_section( 'section_style_excerpt', [
			'label'     => __( 'Excerpt', 'eds-global-settings' ),
			'tab'       => Controls_Manager::TAB_STYLE,
			'condition' => [ 'show_excerpt' => 'yes' ],
		] );

		$this->add_group_control( Group_Control_Typography::get_type(), [
			'name'     => 'excerpt_typography',
			'selector' => '{{WRAPPER}} .eds-post__excerpt p',
		] );

		$this->add_control( 'excerpt_color', [
			'label'     => __( 'Color', 'eds-global-settings' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => [ '{{WRAPPER}} .eds-post__excerpt p' => 'color: {{VALUE}};' ],
		] );

		$this->add_responsive_control( 'excerpt_spacing', [
			'label'      => __( 'Spacing', 'eds-global-settings' ),
			'type'       => Controls_Manager::SLIDER,
			'size_units' => [ 'px' ],
			'default'    => [ 'size' => 15 ],
			'selectors'  => [ '{{WRAPPER}} .eds-post__excerpt' => 'margin-bottom: {{SIZE}}px;' ],
		] );

		$this->end_controls_section();
	}

	private function section_style_button(): void {
		$this->start_controls_section( 'section_style_button', [
			'label'     => __( 'Read More Button', 'eds-global-settings' ),
			'tab'       => Controls_Manager::TAB_STYLE,
			'condition' => [ 'show_read_more' => 'yes' ],
		] );

		$this->add_group_control( Group_Control_Typography::get_type(), [
			'name'     => 'read_more_typography',
			'selector' => '{{WRAPPER}} .eds-post__read-more',
		] );

		$this->start_controls_tabs( 'button_tabs' );
		$this->start_controls_tab( 'button_tab_normal', [ 'label' => __( 'Normal', 'eds-global-settings' ) ] );
		$this->add_control( 'read_more_color', [
			'label'     => __( 'Text Color', 'eds-global-settings' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => [ '{{WRAPPER}} .eds-post__read-more' => 'color: {{VALUE}};' ],
		] );
		$this->add_control( 'read_more_bg', [
			'label'     => __( 'Background', 'eds-global-settings' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => [ '{{WRAPPER}} .eds-post__read-more' => 'background-color: {{VALUE}};' ],
		] );
		$this->end_controls_tab();
		$this->start_controls_tab( 'button_tab_hover', [ 'label' => __( 'Hover', 'eds-global-settings' ) ] );
		$this->add_control( 'read_more_color_hover', [
			'label'     => __( 'Text Color', 'eds-global-settings' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => [ '{{WRAPPER}} .eds-post__read-more:hover' => 'color: {{VALUE}};' ],
		] );
		$this->add_control( 'read_more_bg_hover', [
			'label'     => __( 'Background', 'eds-global-settings' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => [ '{{WRAPPER}} .eds-post__read-more:hover' => 'background-color: {{VALUE}};' ],
		] );
		$this->end_controls_tab();
		$this->end_controls_tabs();

		$this->add_responsive_control( 'read_more_padding', [
			'label'      => __( 'Padding', 'eds-global-settings' ),
			'type'       => Controls_Manager::DIMENSIONS,
			'size_units' => [ 'px', 'em' ],
			'selectors'  => [ '{{WRAPPER}} .eds-post__read-more' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ],
			'separator'  => 'before',
		] );

		$this->add_group_control( Group_Control_Border::get_type(), [
			'name'     => 'read_more_border',
			'selector' => '{{WRAPPER}} .eds-post__read-more',
		] );

		$this->add_responsive_control( 'read_more_border_radius', [
			'label'      => __( 'Border Radius', 'eds-global-settings' ),
			'type'       => Controls_Manager::DIMENSIONS,
			'size_units' => [ 'px', '%' ],
			'selectors'  => [ '{{WRAPPER}} .eds-post__read-more' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ],
		] );

		$this->add_responsive_control( 'read_more_spacing', [
			'label'      => __( 'Spacing', 'eds-global-settings' ),
			'type'       => Controls_Manager::SLIDER,
			'size_units' => [ 'px' ],
			'default'    => [ 'size' => 0 ],
			'selectors'  => [ '{{WRAPPER}} .eds-post__read-more' => 'margin-top: {{SIZE}}px;' ],
		] );

		$this->end_controls_section();
	}

	private function section_style_pagination(): void {
		$this->start_controls_section( 'section_style_pagination', [
			'label'     => __( 'Pagination', 'eds-global-settings' ),
			'tab'       => Controls_Manager::TAB_STYLE,
			'condition' => [ 'pagination_type!' => 'none' ],
		] );

		$this->add_responsive_control( 'pagination_spacing', [
			'label'      => __( 'Spacing', 'eds-global-settings' ),
			'type'       => Controls_Manager::SLIDER,
			'size_units' => [ 'px' ],
			'default'    => [ 'size' => 30 ],
			'selectors'  => [ '{{WRAPPER}} .eds-posts__pagination' => 'margin-top: {{SIZE}}px;' ],
		] );

		$this->add_group_control( Group_Control_Typography::get_type(), [
			'name'     => 'pagination_typography',
			'selector' => '{{WRAPPER}} .eds-posts__page-btn, {{WRAPPER}} .eds-posts__load-more',
		] );

		$this->start_controls_tabs( 'pagination_tabs' );

		$this->start_controls_tab( 'pagination_tab_normal', [ 'label' => __( 'Normal', 'eds-global-settings' ) ] );
		$this->add_control( 'pagination_color', [
			'label'     => __( 'Color', 'eds-global-settings' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => [ '{{WRAPPER}} .eds-posts__page-btn' => 'color: {{VALUE}};' ],
		] );
		$this->add_control( 'pagination_bg', [
			'label'     => __( 'Background', 'eds-global-settings' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => [ '{{WRAPPER}} .eds-posts__page-btn' => 'background-color: {{VALUE}};' ],
		] );
		$this->end_controls_tab();

		$this->start_controls_tab( 'pagination_tab_hover', [ 'label' => __( 'Hover', 'eds-global-settings' ) ] );
		$this->add_control( 'pagination_color_hover', [
			'label'     => __( 'Color', 'eds-global-settings' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => [ '{{WRAPPER}} .eds-posts__page-btn:hover' => 'color: {{VALUE}};' ],
		] );
		$this->add_control( 'pagination_bg_hover', [
			'label'     => __( 'Background', 'eds-global-settings' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => [ '{{WRAPPER}} .eds-posts__page-btn:hover' => 'background-color: {{VALUE}};' ],
		] );
		$this->end_controls_tab();

		$this->start_controls_tab( 'pagination_tab_active', [ 'label' => __( 'Active', 'eds-global-settings' ) ] );
		$this->add_control( 'pagination_color_active', [
			'label'     => __( 'Color', 'eds-global-settings' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => [ '{{WRAPPER}} .eds-posts__page-btn.is-active' => 'color: {{VALUE}};' ],
		] );
		$this->add_control( 'pagination_bg_active', [
			'label'     => __( 'Background', 'eds-global-settings' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => [ '{{WRAPPER}} .eds-posts__page-btn.is-active' => 'background-color: {{VALUE}};' ],
		] );
		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->add_responsive_control( 'pagination_padding', [
			'label'      => __( 'Padding', 'eds-global-settings' ),
			'type'       => Controls_Manager::DIMENSIONS,
			'size_units' => [ 'px' ],
			'selectors'  => [ '{{WRAPPER}} .eds-posts__page-btn' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ],
			'separator'  => 'before',
		] );

		$this->add_group_control( Group_Control_Border::get_type(), [
			'name'     => 'pagination_border',
			'selector' => '{{WRAPPER}} .eds-posts__page-btn',
		] );

		$this->add_responsive_control( 'pagination_border_radius', [
			'label'      => __( 'Border Radius', 'eds-global-settings' ),
			'type'       => Controls_Manager::DIMENSIONS,
			'size_units' => [ 'px', '%' ],
			'selectors'  => [ '{{WRAPPER}} .eds-posts__page-btn' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ],
		] );

		// Load More button specific.
		$this->add_control( 'load_more_heading', [
			'label'     => __( 'Load More Button', 'eds-global-settings' ),
			'type'      => Controls_Manager::HEADING,
			'separator' => 'before',
			'condition' => [ 'pagination_type' => [ 'load_more', 'infinite_scroll' ] ],
		] );

		$this->add_control( 'load_more_color', [
			'label'     => __( 'Text Color', 'eds-global-settings' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => [ '{{WRAPPER}} .eds-posts__load-more' => 'color: {{VALUE}};' ],
			'condition' => [ 'pagination_type' => [ 'load_more', 'infinite_scroll' ] ],
		] );

		$this->add_control( 'load_more_bg', [
			'label'     => __( 'Background Color', 'eds-global-settings' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => [ '{{WRAPPER}} .eds-posts__load-more' => 'background-color: {{VALUE}};' ],
			'condition' => [ 'pagination_type' => [ 'load_more', 'infinite_scroll' ] ],
		] );

		$this->add_responsive_control( 'load_more_padding', [
			'label'      => __( 'Padding', 'eds-global-settings' ),
			'type'       => Controls_Manager::DIMENSIONS,
			'size_units' => [ 'px', 'em' ],
			'selectors'  => [ '{{WRAPPER}} .eds-posts__load-more' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ],
			'condition'  => [ 'pagination_type' => [ 'load_more', 'infinite_scroll' ] ],
		] );

		$this->add_group_control( Group_Control_Border::get_type(), [
			'name'      => 'load_more_border',
			'selector'  => '{{WRAPPER}} .eds-posts__load-more',
			'condition' => [ 'pagination_type' => [ 'load_more', 'infinite_scroll' ] ],
		] );

		$this->add_responsive_control( 'load_more_border_radius', [
			'label'      => __( 'Border Radius', 'eds-global-settings' ),
			'type'       => Controls_Manager::DIMENSIONS,
			'size_units' => [ 'px', '%' ],
			'selectors'  => [ '{{WRAPPER}} .eds-posts__load-more' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ],
			'condition'  => [ 'pagination_type' => [ 'load_more', 'infinite_scroll' ] ],
		] );

		$this->end_controls_section();
	}

	// ─── Render ───────────────────────────────────────────────────────────────

	protected function render(): void {
		$settings = $this->get_settings_for_display();
		$skin     = $settings['skin'] ?? 'classic';

		require_once EDS_PLUGIN_DIR . 'widgets/blog-posts/class-eds-blog-posts-renderer.php';

		$query = $this->build_query( $settings );

		if ( ! $query->have_posts() ) {
			echo '<p class="eds-posts__no-results">' . esc_html__( 'No posts found.', 'eds-global-settings' ) . '</p>';
			return;
		}

		$max_pages  = (int) $query->max_num_pages;
		$pagination = $settings['pagination_type'] ?? 'none';
		$per_page   = (int) ( $settings['posts_per_page'] ?? 6 );
		$widget_id  = 'edswid_' . substr( md5( $this->get_id() ), 0, 12 );

		// Save query + render data in a transient keyed to this widget instance.
		// AJAX handler looks this up — no complex data passing through requests.
		$transient_data = [
			'query'  => $this->build_query_data( $settings ),
			'render' => $this->build_render_data( $settings ),
		];
		set_transient( $widget_id, $transient_data, HOUR_IN_SECONDS );

		$wrapper_classes  = 'eds-posts';
		$wrapper_classes .= ' eds-posts--' . $skin;
		if ( in_array( $settings['image_hover_animation'] ?? '', [ 'zoom-in', 'zoom-out' ], true ) ) {
			$wrapper_classes .= ' eds-posts--img-' . $settings['image_hover_animation'];
		}

		$this->add_render_attribute( 'wrapper', [
			'class'           => $wrapper_classes,
			'data-pagination' => $pagination,
			'data-max-pages'  => $max_pages,
			'data-per-page'   => $per_page,
			'data-widget-id'  => $widget_id,
			'data-nonce'      => wp_create_nonce( 'eds_lm_' . $widget_id ),
			'data-ajaxurl'    => admin_url( 'admin-ajax.php' ),
		] );
		?>
		<div <?php $this->print_render_attribute_string( 'wrapper' ); ?>>
			<div class="eds-posts__grid">
				<?php while ( $query->have_posts() ) : $query->the_post(); ?>
					<?php EDS_Blog_Posts_Renderer::render_post( $settings, $skin ); ?>
				<?php endwhile; wp_reset_postdata(); ?>
			</div>
			<?php $this->render_pagination( $settings, $max_pages ); ?>
		</div>
		<?php
	}

	// ─── Pagination ───────────────────────────────────────────────────────────

	private function render_pagination( array $settings, int $max_pages ): void {
		$type = $settings['pagination_type'] ?? 'none';

		if ( 'none' === $type ) {
			return;
		}

		// Number-based pagination: render regardless, links will be disabled/enabled.
		if ( in_array( $type, [ 'numbers', 'prev_next', 'numbers_and_prev_next' ], true ) ) {
			echo '<nav class="eds-posts__pagination">';
			$this->render_number_pagination( $settings, $max_pages, $type );
			echo '</nav>';
			return;
		}

		// Load More / Infinite Scroll: only render when there is actually more than one page.
		// If max_pages <= 1 all posts already fit on page 1, so there is nothing to load.
		if ( $max_pages <= 1 ) {
			return;
		}

		$no_more = ! empty( $settings['no_more_posts_label'] )
			? $settings['no_more_posts_label']
			: __( 'No more posts to show.', 'eds-global-settings' );

		echo '<nav class="eds-posts__pagination">';

		if ( 'load_more' === $type ) {
			$label = ! empty( $settings['load_more_label'] ) ? $settings['load_more_label'] : __( 'Load More', 'eds-global-settings' );
			echo '<button class="eds-posts__load-more" data-label="' . esc_attr( $label ) . '">' . esc_html( $label ) . '</button>';
			echo '<div class="eds-posts__no-more" style="display:none;">' . esc_html( $no_more ) . '</div>';
			echo '<div class="eds-posts__spinner" style="display:none;" aria-hidden="true"><span></span><span></span><span></span></div>';
		} elseif ( 'infinite_scroll' === $type ) {
			echo '<div class="eds-posts__infinite-sentinel"></div>';
			echo '<div class="eds-posts__no-more" style="display:none;">' . esc_html( $no_more ) . '</div>';
			echo '<div class="eds-posts__spinner" aria-hidden="true" style="display:none;"><span></span><span></span><span></span></div>';
		}

		echo '</nav>';
	}

	private function render_number_pagination( array $settings, int $max_pages, string $type ): void {
		$paged     = max( 1, get_query_var( 'paged', 1 ) );
		$page_limit = min( (int) ( $settings['pagination_page_limit'] ?? 5 ), $max_pages );
		$prev_label = $settings['pagination_prev_label'] ?? '« Prev';
		$next_label = $settings['pagination_next_label'] ?? 'Next »';

		// Prev.
		if ( in_array( $type, [ 'prev_next', 'numbers_and_prev_next' ], true ) ) {
			$prev_url = $paged > 1 ? get_pagenum_link( $paged - 1 ) : null;
			echo '<a class="eds-posts__page-btn eds-posts__prev' . ( $prev_url ? '' : ' is-disabled' ) . '" href="' . ( $prev_url ? esc_url( $prev_url ) : '#' ) . '">' . esc_html( $prev_label ) . '</a>';
		}

		// Numbers.
		if ( in_array( $type, [ 'numbers', 'numbers_and_prev_next' ], true ) ) {
			$half  = (int) floor( $page_limit / 2 );
			$start = max( 1, $paged - $half );
			$end   = min( $max_pages, $start + $page_limit - 1 );
			$start = max( 1, $end - $page_limit + 1 );

			for ( $i = $start; $i <= $end; $i++ ) {
				$active = ( $i === $paged ) ? ' is-active' : '';
				echo '<a class="eds-posts__page-btn' . esc_attr( $active ) . '" href="' . esc_url( get_pagenum_link( $i ) ) . '">' . esc_html( (string) $i ) . '</a>';
			}
		}

		// Next.
		if ( in_array( $type, [ 'prev_next', 'numbers_and_prev_next' ], true ) ) {
			$next_url = $paged < $max_pages ? get_pagenum_link( $paged + 1 ) : null;
			echo '<a class="eds-posts__page-btn eds-posts__next' . ( $next_url ? '' : ' is-disabled' ) . '" href="' . ( $next_url ? esc_url( $next_url ) : '#' ) . '">' . esc_html( $next_label ) . '</a>';
		}
	}

	// ─── Query Builder ────────────────────────────────────────────────────────

	private function build_query( array $settings ): WP_Query {
		$pagination = $settings['pagination_type'] ?? 'none';

		// For number/prev-next pagination, respect the current page from the URL.
		// For load_more/infinite_scroll, always start at page 1 (JS handles paging).
		if ( in_array( $pagination, [ 'numbers', 'prev_next', 'numbers_and_prev_next' ], true ) ) {
			$paged = max( 1, (int) get_query_var( 'paged', 1 ) );
		} else {
			$paged = 1;
		}

		$args = $this->build_query_args( $settings, $paged );
		return new WP_Query( $args );
	}

	private function build_query_args( array $settings, int $page = 1 ): array {
		$post_type  = $settings['post_type'] ?? 'post';
		$per_page   = (int) ( $settings['posts_per_page'] ?? 6 );
		$user_offset = (int) ( $settings['offset'] ?? 0 );

		$args = [
			'post_status'         => 'publish',
			'posts_per_page'      => $per_page,
			'paged'               => $page,
			'orderby'             => $settings['orderby'] ?? 'date',
			'order'               => $settings['order'] ?? 'DESC',
			'ignore_sticky_posts' => 'yes' === ( $settings['ignore_sticky_posts'] ?? 'yes' ),
		];

		// Apply user-defined static offset only on page 1.
		// Do NOT combine paged + offset — WordPress does not support that combination.
		if ( $user_offset > 0 && $page === 1 ) {
			$args['offset'] = $user_offset;
		}

		if ( 'by_id' === $post_type ) {
			$ids = array_filter( array_map( 'absint', explode( ',', $settings['posts_ids'] ?? '' ) ) );
			$args['post_type'] = 'any';
			$args['post__in']  = $ids;
		} else {
			$args['post_type'] = $post_type;
		}

		// Categories — filter out zero/empty values from SELECT2 cleared state.
		$cat_ids = array_values( array_filter( array_map( 'absint', (array) ( $settings['category_ids'] ?? [] ) ) ) );
		if ( ! empty( $cat_ids ) ) {
			$args['tax_query'] = [ [
				'taxonomy' => 'category',
				'field'    => 'term_id',
				'terms'    => $cat_ids,
			] ];
		}

		// Tags.
		$tag_ids = array_values( array_filter( array_map( 'absint', (array) ( $settings['tag_ids'] ?? [] ) ) ) );
		if ( ! empty( $tag_ids ) ) {
			$existing    = $args['tax_query'] ?? [];
			$existing[]  = [
				'taxonomy' => 'post_tag',
				'field'    => 'term_id',
				'terms'    => $tag_ids,
			];
			$args['tax_query'] = $existing;
		}

		// Authors.
		$author_ids = array_values( array_filter( array_map( 'absint', (array) ( $settings['author_ids'] ?? [] ) ) ) );
		if ( ! empty( $author_ids ) ) {
			$args['author__in'] = $author_ids;
		}

		// Exclude current post.
		if ( 'yes' === ( $settings['exclude_current'] ?? '' ) && is_singular() ) {
			$args['post__not_in'] = [ get_the_ID() ];
		}

		return $args;
	}

	private function build_query_data( array $settings ): array {
		return [
			'post_type'      => $settings['post_type'] ?? 'post',
			'posts_per_page' => (int) ( $settings['posts_per_page'] ?? 6 ),
			'offset'         => (int) ( $settings['offset'] ?? 0 ),
			'orderby'        => $settings['orderby'] ?? 'date',
			'order'          => $settings['order'] ?? 'DESC',
			'ignore_sticky'  => 'yes' === ( $settings['ignore_sticky_posts'] ?? 'yes' ),
			// array_filter removes 0 values so empty SELECT2 fields don't create bad queries
			'category_ids'   => array_values( array_filter( array_map( 'absint', (array) ( $settings['category_ids'] ?? [] ) ) ) ),
			'tag_ids'        => array_values( array_filter( array_map( 'absint', (array) ( $settings['tag_ids']      ?? [] ) ) ) ),
			'author_ids'     => array_values( array_filter( array_map( 'absint', (array) ( $settings['author_ids']   ?? [] ) ) ) ),
			'posts_ids'      => $settings['posts_ids'] ?? '',
			'exclude_current'=> $settings['exclude_current'] ?? '',
		];
	}

	private function build_render_data( array $settings ): array {
		return [
			'skin'             => $settings['skin'] ?? 'classic',
			'show_image'       => $settings['show_image'] ?? 'yes',
			'thumbnail_size'   => $settings['thumbnail_size'] ?? 'medium_large',
			'thumbnail_ratio'  => $settings['thumbnail_ratio']['size'] ?? 0.66,
			'image_position'   => $settings['image_position'] ?? 'top',
			'open_new_tab'     => $settings['open_new_tab'] ?? '',
			'show_title'       => $settings['show_title'] ?? 'yes',
			'title_tag'        => $settings['title_tag'] ?? 'h3',
			'show_excerpt'     => $settings['show_excerpt'] ?? 'yes',
			'excerpt_length'   => (int) ( $settings['excerpt_length'] ?? 25 ),
			'show_meta_data'   => $settings['show_meta_data'] ?? 'yes',
			'meta_data'        => (array) ( $settings['meta_data'] ?? [ 'author', 'date' ] ),
			'meta_separator'   => $settings['meta_separator'] ?? '·',
			'show_read_more'   => $settings['show_read_more'] ?? 'yes',
			'read_more_text'   => $settings['read_more_text'] ?? 'Read More »',
			'no_more_label'    => $settings['no_more_posts_label'] ?? 'No more posts to show.',
		];
	}

	// ─── AJAX ─────────────────────────────────────────────────────────────────
	// AJAX is handled by EDS_Blog_Posts_Renderer::handle_ajax() which has
	// zero Elementor class dependencies and is safe to load on admin-ajax.php.
}
