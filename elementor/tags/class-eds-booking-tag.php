<?php
/**
 * EDS Booking Links Dynamic Tag.
 *
 * Renders any field from the Booking / Links settings.
 *
 * @package EDS_Global_Settings
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class EDS_Booking_Tag
 */
class EDS_Booking_Tag extends \Elementor\Core\DynamicTags\Tag {

	// ─── Identity ─────────────────────────────────────────────────────────────

	public function get_name(): string {
		return 'eds-booking-links';
	}

	public function get_title(): string {
		return __( 'EDS Booking Links', 'eds-global-settings' );
	}

	public function get_group(): string {
		return 'eds-global-settings';
	}

	/**
	 * Booking fields are primarily URLs but also include CTA label text,
	 * so we register both TEXT and URL categories.
	 */
	public function get_categories(): array {
		return [
			\Elementor\Modules\DynamicTags\Module::TEXT_CATEGORY,
			\Elementor\Modules\DynamicTags\Module::URL_CATEGORY,
		];
	}

	// ─── Controls ─────────────────────────────────────────────────────────────

	protected function register_controls(): void {
		$this->add_control(
			'field',
			[
				'label'   => __( 'Booking Field', 'eds-global-settings' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'default' => '',
				'options' => EDS_Helpers::booking_select_options(),
			]
		);
	}

	public function get_panel_template_setting_key(): string {
		return 'field';
	}

	// ─── Render ───────────────────────────────────────────────────────────────

	public function render(): void {
		$field = $this->get_settings( 'field' );

		if ( empty( $field ) ) {
			return;
		}

		$value = EDS_Helpers::get_booking( $field );

		// Text fields (CTA labels) vs URL fields.
		$text_fields = [ 'cta_primary_text', 'cta_secondary_text' ];
		if ( in_array( $field, $text_fields, true ) ) {
			echo esc_html( $value );
		} else {
			echo esc_url( $value );
		}
	}
}
