<?php
/**
 * EDS Contact Info Dynamic Tag.
 *
 * Renders any field from the Contact Information settings.
 *
 * @package EDS_Global_Settings
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class EDS_Contact_Tag
 *
 * Available in: Text, URL, and Number fields in Elementor.
 */
class EDS_Contact_Tag extends \Elementor\Core\DynamicTags\Tag {

	// ─── Identity ─────────────────────────────────────────────────────────────

	public function get_name(): string {
		return 'eds-contact-info';
	}

	public function get_title(): string {
		return __( 'EDS Contact Info', 'eds-global-settings' );
	}

	public function get_group(): string {
		return 'eds-global-settings';
	}

	/**
	 * Return the Elementor field categories this tag can be used in.
	 * Returning TEXT + URL allows the tag to appear in both plain-text fields
	 * and link/URL fields (e.g. for maps_link, email, phone as href).
	 */
	public function get_categories(): array {
		return [
			\Elementor\Modules\DynamicTags\Module::TEXT_CATEGORY,
			\Elementor\Modules\DynamicTags\Module::URL_CATEGORY,
		];
	}

	// ─── Controls ─────────────────────────────────────────────────────────────

	/**
	 * Register the SELECT control that lets the user choose which contact field to use.
	 */
	protected function register_controls(): void {
		$this->add_control(
			'field',
			[
				'label'   => __( 'Contact Field', 'eds-global-settings' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'default' => '',
				'options' => EDS_Helpers::contact_select_options(),
			]
		);
	}

	/**
	 * Tell Elementor which control key to use for the panel preview label.
	 */
	public function get_panel_template_setting_key(): string {
		return 'field';
	}

	// ─── Render ───────────────────────────────────────────────────────────────

	/**
	 * Echo the saved value for the selected contact field.
	 * Elementor handles the escaping context; we escape on output here too.
	 */
	public function render(): void {
		$field = $this->get_settings( 'field' );

		if ( empty( $field ) ) {
			return;
		}

		$value = EDS_Helpers::get_contact( $field );

		// For URL fields, use esc_url; for others, esc_html.
		$url_fields = [ 'maps_link', 'maps_embed' ];
		if ( in_array( $field, $url_fields, true ) ) {
			echo esc_url( $value );
		} else {
			echo wp_kses_post( $value );
		}
	}
}
