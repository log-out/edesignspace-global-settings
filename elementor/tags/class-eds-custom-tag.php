<?php
/**
 * EDS Custom Variable Dynamic Tag.
 *
 * Renders any value from the unlimited Custom Data variables.
 *
 * @package EDS_Global_Settings
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class EDS_Custom_Tag
 *
 * The options for this tag's SELECT control are loaded fresh from the database
 * on each request, so newly added custom variables appear immediately in the
 * Elementor editor without needing a page reload.
 */
class EDS_Custom_Tag extends \Elementor\Core\DynamicTags\Tag {

	// ─── Identity ─────────────────────────────────────────────────────────────

	public function get_name(): string {
		return 'eds-custom-variable';
	}

	public function get_title(): string {
		return __( 'EDS Custom Variable', 'eds-global-settings' );
	}

	public function get_group(): string {
		return 'eds-global-settings';
	}

	/**
	 * Custom variables can hold any type of value (text, URL, number string, etc.)
	 * so we register all common Elementor text-compatible categories.
	 */
	public function get_categories(): array {
		return [
			\Elementor\Modules\DynamicTags\Module::TEXT_CATEGORY,
			\Elementor\Modules\DynamicTags\Module::URL_CATEGORY,
			\Elementor\Modules\DynamicTags\Module::NUMBER_CATEGORY,
		];
	}

	// ─── Controls ─────────────────────────────────────────────────────────────

	protected function register_controls(): void {
		// Populate options dynamically from the saved custom items.
		$this->add_control(
			'variable_id',
			[
				'label'   => __( 'Custom Variable', 'eds-global-settings' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'default' => '',
				'options' => EDS_Helpers::custom_items_select_options(),
			]
		);
	}

	public function get_panel_template_setting_key(): string {
		return 'variable_id';
	}

	// ─── Render ───────────────────────────────────────────────────────────────

	public function render(): void {
		$variable_id = $this->get_settings( 'variable_id' );

		if ( empty( $variable_id ) ) {
			return;
		}

		$value = EDS_Helpers::get_custom( $variable_id );

		// Output with broad escaping — custom values can be text or URLs.
		echo wp_kses_post( $value );
	}
}
