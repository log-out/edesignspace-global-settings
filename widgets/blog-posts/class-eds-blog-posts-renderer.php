<?php
/**
 * EDS Blog Posts Renderer — pure PHP post rendering, no Elementor dependency.
 *
 * This file is safe to require_once from admin-ajax.php because it contains
 * no references to Elementor classes, no Widget_Base extension, and no
 * Controls_Manager usage. It is also used by the widget's render() method.
 *
 * @package EDS_Global_Settings
 * @since   1.2.4
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class EDS_Blog_Posts_Renderer
 */
class EDS_Blog_Posts_Renderer {

	// ─── Entry point ──────────────────────────────────────────────────────────

	/**
	 * Handle the AJAX load-more request.
	 * Called directly — no Elementor widget instantiation needed.
	 */
	public static function handle_ajax(): void {

		$widget_id = isset( $_POST['widget_id'] ) ? sanitize_key( wp_unslash( $_POST['widget_id'] ) ) : '';
		$nonce     = isset( $_POST['nonce'] )     ? sanitize_text_field( wp_unslash( $_POST['nonce'] ) ) : '';

		if ( ! $widget_id || ! $nonce || ! wp_verify_nonce( $nonce, 'eds_lm_' . $widget_id ) ) {
			wp_send_json_error( [ 'message' => 'Security check failed.' ] );
			wp_die();
		}

		// Retrieve settings saved as transient during page render.
		$saved = get_transient( $widget_id );
		if ( ! is_array( $saved ) ) {
			wp_send_json_error( [ 'message' => 'Widget data expired. Please reload the page.' ] );
			wp_die();
		}

		$query_data  = is_array( $saved['query']  ?? null ) ? $saved['query']  : [];
		$render_data = is_array( $saved['render'] ?? null ) ? $saved['render'] : [];

		$page     = max( 2, (int) ( $_POST['page']     ?? 2 ) );
		$per_page = max( 1, (int) ( $_POST['per_page'] ?? 6 ) );

		// Offset-based: never use paged in an AJAX context.
		$offset = ( $page - 1 ) * $per_page;

		$args             = self::build_query_args( $query_data, $per_page );
		$args['offset']   = $offset;
		unset( $args['paged'] ); // must not mix paged + offset

		$query = new WP_Query( $args );

		if ( ! $query->have_posts() ) {
			wp_send_json_success( [ 'html' => '', 'has_more' => false ] );
			wp_die();
		}

		$skin = sanitize_key( $render_data['skin'] ?? 'classic' );

		ob_start();
		while ( $query->have_posts() ) {
			$query->the_post();
			self::render_post( $render_data, $skin );
		}
		wp_reset_postdata();
		$html = ob_get_clean();

		// has_more: if we got a full page there are likely more posts.
		$has_more = ( $query->post_count >= $per_page );

		wp_send_json_success( [ 'html' => $html, 'has_more' => $has_more ] );
		wp_die();
	}

	// ─── Query ────────────────────────────────────────────────────────────────

	/**
	 * Build WP_Query args from the stored query data.
	 *
	 * @param array $qd        Query data array.
	 * @param int   $per_page  Posts per page.
	 * @param int   $page      Current page (1-based).
	 * @return array
	 */
	public static function build_query_args( array $qd, int $per_page ): array {
		$post_type = sanitize_key( $qd['post_type'] ?? 'post' ) ?: 'post';

		$args = [
			'post_status'         => 'publish',
			'posts_per_page'      => $per_page,
			'orderby'             => sanitize_key( $qd['orderby'] ?? 'date' ) ?: 'date',
			'order'               => in_array( $qd['order'] ?? '', [ 'ASC', 'DESC' ], true ) ? $qd['order'] : 'DESC',
			'ignore_sticky_posts' => true,
		];

		if ( 'by_id' === $post_type ) {
			$ids = array_filter( array_map( 'absint', explode( ',', sanitize_text_field( $qd['posts_ids'] ?? '' ) ) ) );
			$args['post_type']  = 'any';
			$args['post__in']   = $ids ?: [ 0 ];
		} else {
			$args['post_type'] = $post_type;
		}

		$tax_query = [];
		$cat_ids   = array_filter( array_map( 'absint', (array) ( $qd['category_ids'] ?? [] ) ) );
		$tag_ids   = array_filter( array_map( 'absint', (array) ( $qd['tag_ids']      ?? [] ) ) );
		$author_ids = array_filter( array_map( 'absint', (array) ( $qd['author_ids']  ?? [] ) ) );

		if ( ! empty( $cat_ids ) ) {
			$tax_query[] = [
				'taxonomy' => 'category',
				'field'    => 'term_id',
				'terms'    => array_values( $cat_ids ),
			];
		}
		if ( ! empty( $tag_ids ) ) {
			$tax_query[] = [
				'taxonomy' => 'post_tag',
				'field'    => 'term_id',
				'terms'    => array_values( $tag_ids ),
			];
		}
		if ( $tax_query ) {
			$args['tax_query'] = $tax_query;
		}

		if ( ! empty( $author_ids ) ) {
			$args['author__in'] = array_values( $author_ids );
		}

		return $args;
	}

	// ─── Render: Post ─────────────────────────────────────────────────────────

	/**
	 * Render a single post article.
	 *
	 * @param array  $settings Render/display settings.
	 * @param string $skin     'classic' | 'cards' | 'full_content'
	 */
	public static function render_post( array $settings, string $skin ): void {
		$target = 'yes' === ( $settings['open_new_tab'] ?? '' ) ? '_blank' : '_self';
		$rel    = '_blank' === $target ? ' rel="noopener noreferrer"' : '';

		echo '<article class="eds-post">';

		if ( 'cards' === $skin ) {
			echo '<div class="eds-post__card">';
		}

		// Image.
		$img_pos = $settings['image_position'] ?? 'top';
		if ( 'yes' === ( $settings['show_image'] ?? 'yes' ) && 'none' !== $img_pos ) {
			self::render_post_image( $settings, $target, $rel );
		}

		echo '<div class="eds-post__text">';

		// Meta.
		if ( 'yes' === ( $settings['show_meta_data'] ?? 'yes' ) ) {
			echo '<div class="eds-post__meta-top">';
			self::render_post_meta( $settings );
			echo '</div>';
		}

		// Title.
		if ( 'yes' === ( $settings['show_title'] ?? 'yes' ) ) {
			$tag = esc_html( $settings['title_tag'] ?? 'h3' );
			printf(
				'<%1$s class="eds-post__title"><a href="%2$s" target="%3$s"%4$s>%5$s</a></%1$s>',
				$tag,
				esc_url( get_permalink() ),
				esc_attr( $target ),
				$rel,
				get_the_title()
			);
		}

		// Excerpt / Full content.
		if ( 'yes' === ( $settings['show_excerpt'] ?? 'yes' ) ) {
			echo '<div class="eds-post__excerpt">';
			if ( 'full_content' === $skin ) {
				the_content();
			} else {
				$length = (int) ( $settings['excerpt_length'] ?? 25 );
				echo '<p>' . wp_kses_post( wp_trim_words( get_the_excerpt(), $length ) ) . '</p>';
			}
			echo '</div>';
		}

		// Read More.
		if ( 'yes' === ( $settings['show_read_more'] ?? 'yes' ) ) {
			$label = ! empty( $settings['read_more_text'] ) ? $settings['read_more_text'] : 'Read More »';
			printf(
				'<a class="eds-post__read-more" href="%1$s" target="%2$s"%3$s>%4$s</a>',
				esc_url( get_permalink() ),
				esc_attr( $target ),
				$rel,
				esc_html( $label )
			);
		}

		echo '</div>'; // .eds-post__text

		if ( 'cards' === $skin ) {
			echo '</div>'; // .eds-post__card
		}

		echo '</article>';
	}

	// ─── Render: Image ────────────────────────────────────────────────────────

	public static function render_post_image( array $settings, string $target, string $rel ): void {
		$post_id      = get_the_ID();
		$thumbnail_id = get_post_thumbnail_id( $post_id );

		if ( ! $thumbnail_id ) {
			echo '<div class="eds-post__thumbnail eds-post__thumbnail--none"></div>';
			return;
		}

		$img_size = ! empty( $settings['thumbnail_size'] ) ? $settings['thumbnail_size'] : 'large';

		if ( 'custom' === $img_size && ! empty( $settings['thumbnail_custom_dimension']['width'] ) ) {
			$w        = (int) $settings['thumbnail_custom_dimension']['width'];
			$h        = (int) ( $settings['thumbnail_custom_dimension']['height'] ?? 0 );
			$img_html = wp_get_attachment_image( $thumbnail_id, [ $w, $h ], false );
		} else {
			$img_html = wp_get_attachment_image( $thumbnail_id, $img_size, false, [ 'loading' => 'lazy' ] );
		}

		if ( ! $img_html ) {
			echo '<div class="eds-post__thumbnail eds-post__thumbnail--none"></div>';
			return;
		}

		printf(
			'<div class="eds-post__thumbnail"><a href="%1$s" target="%2$s"%3$s><div class="eds-post__thumbnail-wrap">%4$s</div></a></div>',
			esc_url( get_permalink( $post_id ) ),
			esc_attr( $target ),
			$rel,
			$img_html
		);
	}

	// ─── Render: Meta ─────────────────────────────────────────────────────────

	public static function render_post_meta( array $settings ): void {
		$items     = (array) ( $settings['meta_data'] ?? [ 'author', 'date' ] );
		$separator = esc_html( $settings['meta_separator'] ?? '·' );
		$parts     = [];

		if ( in_array( 'author', $items, true ) ) {
			$parts[] = '<span class="eds-post__meta-item">'
				. '<a href="' . esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ) . '">'
				. esc_html( get_the_author() )
				. '</a></span>';
		}

		if ( in_array( 'date', $items, true ) ) {
			$parts[] = '<span class="eds-post__meta-item">' . esc_html( get_the_date() ) . '</span>';
		}

		if ( in_array( 'comments', $items, true ) ) {
			$count   = (int) get_comments_number();
			$label   = sprintf( _n( '%d Comment', '%d Comments', $count, 'eds-global-settings' ), $count );
			$parts[] = '<span class="eds-post__meta-item">' . esc_html( $label ) . '</span>';
		}

		if ( in_array( 'category', $items, true ) ) {
			$cats = get_the_category();
			if ( $cats ) {
				$links = [];
				foreach ( $cats as $cat ) {
					$links[] = '<a href="' . esc_url( get_category_link( $cat->term_id ) ) . '">' . esc_html( $cat->name ) . '</a>';
				}
				$parts[] = '<span class="eds-post__meta-item">' . implode( ', ', $links ) . '</span>';
			}
		}

		if ( $parts ) {
			echo '<div class="eds-post__meta-data">'
				. implode( ' <span class="eds-meta-sep">' . $separator . '</span> ', $parts )
				. '</div>';
		}
	}
}
