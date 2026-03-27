<?php
/**
 * EDS Helpers — shared data definitions and utility methods.
 *
 * @package EDS_Global_Settings
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class EDS_Helpers
 *
 * Central registry for:
 *  - WordPress option key names
 *  - Field definitions for Contact Info and Booking Links
 *  - Default values
 *  - Sanitisation helpers
 */
class EDS_Helpers {

	// ─── Option Keys ──────────────────────────────────────────────────────────

	/** @return string[] All option keys used by the plugin. */
	public static function option_keys(): array {
		return [
			'eds_contact_info',
			'eds_booking_links',
			'eds_social_links',
			'eds_custom_social',
			'eds_custom_data',
			'eds_widgets',
		];
	}

	/** Returns the default value for a given option key. */
	public static function default_option( string $key ) {
		$defaults = [
			'eds_contact_info'  => [],
			'eds_booking_links' => [],
			'eds_social_links'  => [],
			'eds_custom_social' => [],
			'eds_custom_data'   => [],
			'eds_widgets'       => [],
		];
		return $defaults[ $key ] ?? [];
	}

	// ─── Widget Registry ──────────────────────────────────────────────────────

	/**
	 * Central registry of all EDS custom Elementor widgets.
	 * Add new widgets here as the plugin grows.
	 *
	 * @return array[]
	 */
	public static function widget_registry(): array {
		return [
			[
				'id'          => 'blog-posts',
				'label'       => __( 'Blog Posts', 'eds-global-settings' ),
				'description' => __( 'Display a filterable grid or list of posts with full query control, multiple skins, and Load More / Infinite Scroll pagination — exactly like Elementor Pro.', 'eds-global-settings' ),
				'icon'        => 'dashicons-admin-post',
				'category'    => __( 'EDS Elements', 'eds-global-settings' ),
				'file'        => EDS_PLUGIN_DIR . 'widgets/blog-posts/class-eds-blog-posts-widget.php',
				'class'       => 'EDS_Blog_Posts_Widget',
			],
			[
				'id'          => 'image-list',
				'label'       => __( 'Image List', 'eds-global-settings' ),
				'description' => __( 'Display a list of items each with an uploaded image, text, and optional link — like Elementor\'s Icon List but with custom images instead of icons.', 'eds-global-settings' ),
				'icon'        => 'dashicons-list-view',
				'category'    => __( 'EDS Elements', 'eds-global-settings' ),
				'file'        => EDS_PLUGIN_DIR . 'widgets/image-list/class-eds-image-list-widget.php',
				'class'       => 'EDS_Image_List_Widget',
			],
			[
				'id'          => 'gallery',
				'label'       => __( 'Gallery', 'eds-global-settings' ),
				'description' => __( 'Filterable image gallery with Grid and Masonry layouts, hover overlays, lightbox, and per-item category filtering — fully styled and responsive.', 'eds-global-settings' ),
				'icon'        => 'dashicons-format-gallery',
				'category'    => __( 'EDS Elements', 'eds-global-settings' ),
				'file'        => EDS_PLUGIN_DIR . 'widgets/gallery/class-eds-gallery-widget.php',
				'class'       => 'EDS_Gallery_Widget',
			],
			[
				'id'          => 'quick-gallery',
				'label'       => __( 'Quick Gallery', 'eds-global-settings' ),
				'description' => __( 'Select multiple images at once from the media library. Simple, fast gallery with style controls and Elementor lightbox. No per-image setup needed.', 'eds-global-settings' ),
				'icon'        => 'dashicons-images-alt2',
				'category'    => __( 'EDS Elements', 'eds-global-settings' ),
				'file'        => EDS_PLUGIN_DIR . 'widgets/quick-gallery/class-eds-quick-gallery-widget.php',
				'class'       => 'EDS_Quick_Gallery_Widget',
			],
			[
				'id'          => 'advanced-heading',
				'label'       => __( 'Advanced Heading', 'eds-global-settings' ),
				'description' => __( 'Animated and highlighted headings with rotating words, typing effects, highlighted text, wave animations, and more — like Elementor Pro\'s animated heading.', 'eds-global-settings' ),
				'icon'        => 'dashicons-editor-textcolor',
				'category'    => __( 'EDS Elements', 'eds-global-settings' ),
				'file'        => EDS_PLUGIN_DIR . 'widgets/advanced-heading/class-eds-advanced-heading-widget.php',
				'class'       => 'EDS_Advanced_Heading_Widget',
			],
		];
	}

	/**
	 * Check whether a given widget ID is currently enabled.
	 *
	 * @param  string $widget_id Widget ID (e.g. 'blog-posts').
	 * @return bool
	 */
	public static function is_widget_enabled( string $widget_id ): bool {
		$settings = get_option( 'eds_widgets', [] );
		// Default to disabled — admin must explicitly enable.
		return ! empty( $settings[ $widget_id ] );
	}

	/**
	 * Return the full saved widgets settings array.
	 *
	 * @return array  [ 'widget-id' => true|false ]
	 */
	public static function get_widgets_settings(): array {
		return (array) get_option( 'eds_widgets', [] );
	}

	// ─── Field Definitions ────────────────────────────────────────────────────

	/**
	 * Contact Information field definitions.
	 *
	 * Each entry: [ key, label, type, placeholder, icon ]
	 *
	 * @return array[]
	 */
	public static function contact_fields(): array {
		return [
			[
				'key'         => 'phone',
				'label'       => __( 'Phone Number', 'eds-global-settings' ),
				'type'        => 'text',
				'placeholder' => __( 'e.g. +1 555 000 100', 'eds-global-settings' ),
				'icon'        => 'dashicons-phone',
			],
			[
				'key'         => 'whatsapp',
				'label'       => __( 'WhatsApp Number', 'eds-global-settings' ),
				'type'        => 'text',
				'placeholder' => __( 'e.g. +1 555 000 200', 'eds-global-settings' ),
				'icon'        => 'dashicons-whatsapp',
			],
			[
				'key'         => 'email',
				'label'       => __( 'Email Address', 'eds-global-settings' ),
				'type'        => 'email',
				'placeholder' => __( 'e.g. hello@example.com', 'eds-global-settings' ),
				'icon'        => 'dashicons-email-alt',
			],
			[
				'key'         => 'email_support',
				'label'       => __( 'Support Email', 'eds-global-settings' ),
				'type'        => 'email',
				'placeholder' => __( 'e.g. support@example.com', 'eds-global-settings' ),
				'icon'        => 'dashicons-email',
			],
			[
				'key'         => 'address',
				'label'       => __( 'Address', 'eds-global-settings' ),
				'type'        => 'textarea',
				'placeholder' => __( '123 Main St, City, Country', 'eds-global-settings' ),
				'icon'        => 'dashicons-location-alt',
			],
			[
				'key'         => 'city',
				'label'       => __( 'City', 'eds-global-settings' ),
				'type'        => 'text',
				'placeholder' => __( 'e.g. New York', 'eds-global-settings' ),
				'icon'        => 'dashicons-location',
			],
			[
				'key'         => 'country',
				'label'       => __( 'Country', 'eds-global-settings' ),
				'type'        => 'text',
				'placeholder' => __( 'e.g. United States', 'eds-global-settings' ),
				'icon'        => 'dashicons-flag',
			],
			[
				'key'         => 'maps_link',
				'label'       => __( 'Google Maps Link', 'eds-global-settings' ),
				'type'        => 'url',
				'placeholder' => __( 'https://maps.google.com/...', 'eds-global-settings' ),
				'icon'        => 'dashicons-admin-site-alt3',
			],
			[
				'key'         => 'maps_embed',
				'label'       => __( 'Google Maps Embed URL', 'eds-global-settings' ),
				'type'        => 'url',
				'placeholder' => __( 'https://www.google.com/maps/embed?pb=...', 'eds-global-settings' ),
				'icon'        => 'dashicons-admin-site',
			],
			[
				'key'         => 'business_hours',
				'label'       => __( 'Business Hours', 'eds-global-settings' ),
				'type'        => 'textarea',
				'placeholder' => __( 'Mon–Fri: 9am – 6pm', 'eds-global-settings' ),
				'icon'        => 'dashicons-clock',
			],
			[
				'key'         => 'vat_number',
				'label'       => __( 'VAT / Tax Number', 'eds-global-settings' ),
				'type'        => 'text',
				'placeholder' => __( 'e.g. GB123456789', 'eds-global-settings' ),
				'icon'        => 'dashicons-id',
			],
		];
	}

	/**
	 * Booking / Links field definitions.
	 *
	 * @return array[]
	 */
	public static function booking_fields(): array {
		return [
			[
				'key'         => 'booking_main',
				'label'       => __( 'Main Booking Link', 'eds-global-settings' ),
				'type'        => 'url',
				'placeholder' => __( 'https://calendly.com/...', 'eds-global-settings' ),
				'icon'        => 'dashicons-calendar-alt',
			],
			[
				'key'         => 'booking_consultation',
				'label'       => __( 'Free Consultation Link', 'eds-global-settings' ),
				'type'        => 'url',
				'placeholder' => __( 'https://...', 'eds-global-settings' ),
				'icon'        => 'dashicons-video-alt3',
			],
			[
				'key'         => 'booking_appointment',
				'label'       => __( 'Appointment Link', 'eds-global-settings' ),
				'type'        => 'url',
				'placeholder' => __( 'https://...', 'eds-global-settings' ),
				'icon'        => 'dashicons-clock',
			],
			[
				'key'         => 'booking_service_1',
				'label'       => __( 'Service Link 1', 'eds-global-settings' ),
				'type'        => 'url',
				'placeholder' => __( 'https://...', 'eds-global-settings' ),
				'icon'        => 'dashicons-admin-links',
			],
			[
				'key'         => 'booking_service_2',
				'label'       => __( 'Service Link 2', 'eds-global-settings' ),
				'type'        => 'url',
				'placeholder' => __( 'https://...', 'eds-global-settings' ),
				'icon'        => 'dashicons-admin-links',
			],
			[
				'key'         => 'booking_service_3',
				'label'       => __( 'Service Link 3', 'eds-global-settings' ),
				'type'        => 'url',
				'placeholder' => __( 'https://...', 'eds-global-settings' ),
				'icon'        => 'dashicons-admin-links',
			],
			[
				'key'         => 'portal_client',
				'label'       => __( 'Client Portal Link', 'eds-global-settings' ),
				'type'        => 'url',
				'placeholder' => __( 'https://...', 'eds-global-settings' ),
				'icon'        => 'dashicons-id-alt',
			],
			[
				'key'         => 'cta_primary_url',
				'label'       => __( 'Primary CTA URL', 'eds-global-settings' ),
				'type'        => 'url',
				'placeholder' => __( 'https://...', 'eds-global-settings' ),
				'icon'        => 'dashicons-button',
			],
			[
				'key'         => 'cta_primary_text',
				'label'       => __( 'Primary CTA Label', 'eds-global-settings' ),
				'type'        => 'text',
				'placeholder' => __( 'e.g. Get Started', 'eds-global-settings' ),
				'icon'        => 'dashicons-edit-large',
			],
			[
				'key'         => 'cta_secondary_url',
				'label'       => __( 'Secondary CTA URL', 'eds-global-settings' ),
				'type'        => 'url',
				'placeholder' => __( 'https://...', 'eds-global-settings' ),
				'icon'        => 'dashicons-button',
			],
			[
				'key'         => 'cta_secondary_text',
				'label'       => __( 'Secondary CTA Label', 'eds-global-settings' ),
				'type'        => 'text',
				'placeholder' => __( 'e.g. Learn More', 'eds-global-settings' ),
				'icon'        => 'dashicons-edit-large',
			],
		];
	}

	/**
	 * Social Media link field definitions.
	 *
	 * @return array[]
	 */
	public static function social_fields(): array {
		return [
			[
				'key'         => 'facebook',
				'label'       => __( 'Facebook', 'eds-global-settings' ),
				'type'        => 'url',
				'placeholder' => __( 'https://facebook.com/yourpage', 'eds-global-settings' ),
				'icon'        => 'dashicons-facebook-alt',
			],
			[
				'key'         => 'instagram',
				'label'       => __( 'Instagram', 'eds-global-settings' ),
				'type'        => 'url',
				'placeholder' => __( 'https://instagram.com/yourhandle', 'eds-global-settings' ),
				'icon'        => 'dashicons-instagram',
			],
			[
				'key'         => 'twitter',
				'label'       => __( 'X / Twitter', 'eds-global-settings' ),
				'type'        => 'url',
				'placeholder' => __( 'https://x.com/yourhandle', 'eds-global-settings' ),
				'icon'        => 'dashicons-twitter',
			],
			[
				'key'         => 'linkedin',
				'label'       => __( 'LinkedIn', 'eds-global-settings' ),
				'type'        => 'url',
				'placeholder' => __( 'https://linkedin.com/in/...', 'eds-global-settings' ),
				'icon'        => 'dashicons-linkedin',
			],
			[
				'key'         => 'youtube',
				'label'       => __( 'YouTube', 'eds-global-settings' ),
				'type'        => 'url',
				'placeholder' => __( 'https://youtube.com/@yourchannel', 'eds-global-settings' ),
				'icon'        => 'dashicons-video-alt3',
			],
			[
				'key'         => 'tiktok',
				'label'       => __( 'TikTok', 'eds-global-settings' ),
				'type'        => 'url',
				'placeholder' => __( 'https://tiktok.com/@yourhandle', 'eds-global-settings' ),
				'icon'        => 'dashicons-controls-play',
			],
			[
				'key'         => 'pinterest',
				'label'       => __( 'Pinterest', 'eds-global-settings' ),
				'type'        => 'url',
				'placeholder' => __( 'https://pinterest.com/yourprofile', 'eds-global-settings' ),
				'icon'        => 'dashicons-admin-appearance',
			],
			[
				'key'         => 'snapchat',
				'label'       => __( 'Snapchat', 'eds-global-settings' ),
				'type'        => 'url',
				'placeholder' => __( 'https://snapchat.com/add/yourhandle', 'eds-global-settings' ),
				'icon'        => 'dashicons-smiley',
			],
			[
				'key'         => 'github',
				'label'       => __( 'GitHub', 'eds-global-settings' ),
				'type'        => 'url',
				'placeholder' => __( 'https://github.com/yourprofile', 'eds-global-settings' ),
				'icon'        => 'dashicons-editor-code',
			],
			[
				'key'         => 'dribbble',
				'label'       => __( 'Dribbble', 'eds-global-settings' ),
				'type'        => 'url',
				'placeholder' => __( 'https://dribbble.com/yourprofile', 'eds-global-settings' ),
				'icon'        => 'dashicons-art',
			],
			[
				'key'         => 'behance',
				'label'       => __( 'Behance', 'eds-global-settings' ),
				'type'        => 'url',
				'placeholder' => __( 'https://behance.net/yourprofile', 'eds-global-settings' ),
				'icon'        => 'dashicons-art',
			],
		];
	}

	// ─── Sanitisation ─────────────────────────────────────────────────────────

	/**
	 * Sanitise a value based on its field type.
	 *
	 * @param  string $value Raw input value.
	 * @param  string $type  Field type (text|email|url|textarea).
	 * @return string
	 */
	public static function sanitize_by_type( string $value, string $type ): string {
		switch ( $type ) {
			case 'email':
				return sanitize_email( $value );
			case 'url':
				return esc_url_raw( $value );
			case 'textarea':
				return sanitize_textarea_field( $value );
			default:
				return sanitize_text_field( $value );
		}
	}

	// ─── Data Retrieval ───────────────────────────────────────────────────────

	/**
	 * Get a single contact info value.
	 *
	 * @param  string $key     Field key.
	 * @param  string $default Fallback value.
	 * @return string
	 */
	public static function get_contact( string $key, string $default = '' ): string {
		$data = get_option( 'eds_contact_info', [] );
		return isset( $data[ $key ] ) ? $data[ $key ] : $default;
	}

	/**
	 * Get a single booking link value.
	 *
	 * @param  string $key     Field key.
	 * @param  string $default Fallback value.
	 * @return string
	 */
	public static function get_booking( string $key, string $default = '' ): string {
		$data = get_option( 'eds_booking_links', [] );
		return isset( $data[ $key ] ) ? $data[ $key ] : $default;
	}

	/**
	 * Get a single social link value.
	 *
	 * @param  string $key     Field key.
	 * @param  string $default Fallback value.
	 * @return string
	 */
	public static function get_social( string $key, string $default = '' ): string {
		$data = get_option( 'eds_social_links', [] );
		return isset( $data[ $key ] ) ? $data[ $key ] : $default;
	}

	/**
	 * Get all custom social items.
	 *
	 * @return array[]  Array of [ 'id' => '', 'label' => '', 'url' => '' ].
	 */
	public static function get_custom_social_items(): array {
		$items = get_option( 'eds_custom_social', [] );
		return is_array( $items ) ? $items : [];
	}

	/**
	 * Get a single custom social URL by ID.
	 *
	 * @param  string $id      Item ID.
	 * @param  string $default Fallback.
	 * @return string
	 */
	public static function get_custom_social( string $id, string $default = '' ): string {
		foreach ( self::get_custom_social_items() as $item ) {
			if ( isset( $item['id'] ) && $item['id'] === $id ) {
				return $item['url'] ?? $default;
			}
		}
		return $default;
	}

	/**
	 * Build select options for all social fields including custom ones.
	 * Used by the EDS_Social_Tag Elementor control.
	 *
	 * @return string[]
	 */
	public static function social_all_select_options(): array {
		// Start with the built-in static platforms.
		$options = [ '' => __( '— Select a Platform —', 'eds-global-settings' ) ];
		foreach ( self::social_fields() as $field ) {
			$options[ 'builtin:' . $field['key'] ] = $field['label'];
		}
		// Append any custom social items.
		$custom = self::get_custom_social_items();
		if ( ! empty( $custom ) ) {
			foreach ( $custom as $item ) {
				if ( ! empty( $item['id'] ) && ! empty( $item['label'] ) ) {
					$options[ 'custom:' . $item['id'] ] = $item['label'];
				}
			}
		}
		return $options;
	}

	/**
	 * Get all custom data items.
	 *
	 * @return array[]  Array of [ 'id' => '', 'title' => '', 'value' => '' ].
	 */
	public static function get_custom_items(): array {
		$items = get_option( 'eds_custom_data', [] );
		return is_array( $items ) ? $items : [];
	}

	/**
	 * Get a single custom data value by its ID.
	 *
	 * @param  string $id      Custom item ID.
	 * @param  string $default Fallback.
	 * @return string
	 */
	public static function get_custom( string $id, string $default = '' ): string {
		foreach ( self::get_custom_items() as $item ) {
			if ( isset( $item['id'] ) && $item['id'] === $id ) {
				return $item['value'] ?? $default;
			}
		}
		return $default;
	}

	/**
	 * Build the label→value options array for custom items (used in Elementor select control).
	 *
	 * @return string[]  [ id => label ]
	 */
	public static function custom_items_select_options(): array {
		$options = [ '' => __( '— Select a Variable —', 'eds-global-settings' ) ];
		foreach ( self::get_custom_items() as $item ) {
			if ( ! empty( $item['id'] ) && ! empty( $item['title'] ) ) {
				$options[ $item['id'] ] = $item['title'];
			}
		}
		return $options;
	}

	/**
	 * Build select options for contact fields (used in Elementor select control).
	 *
	 * @return string[]
	 */
	public static function contact_select_options(): array {
		$options = [ '' => __( '— Select a Field —', 'eds-global-settings' ) ];
		foreach ( self::contact_fields() as $field ) {
			$options[ $field['key'] ] = $field['label'];
		}
		return $options;
	}

	/**
	 * Build select options for booking fields.
	 *
	 * @return string[]
	 */
	public static function booking_select_options(): array {
		$options = [ '' => __( '— Select a Field —', 'eds-global-settings' ) ];
		foreach ( self::booking_fields() as $field ) {
			$options[ $field['key'] ] = $field['label'];
		}
		return $options;
	}

	/**
	 * Build select options for social fields.
	 *
	 * @return string[]
	 */
	public static function social_select_options(): array {
		$options = [ '' => __( '— Select a Field —', 'eds-global-settings' ) ];
		foreach ( self::social_fields() as $field ) {
			$options[ $field['key'] ] = $field['label'];
		}
		return $options;
	}
}
