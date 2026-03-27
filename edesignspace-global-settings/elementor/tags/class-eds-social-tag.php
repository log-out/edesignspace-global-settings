<?php
/**
 * EDS Social Links Dynamic Tag.
 *
 * Renders any social media profile URL — both built-in platforms and
 * any custom social links added by the admin.
 *
 * @package EDS_Global_Settings
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class EDS_Social_Tag
 *
 * The SELECT control is populated with all built-in platforms first,
 * then any custom social items stored in eds_custom_social.
 *
 * Values in the control use a prefixed format so the render method can
 * distinguish sources without ambiguity:
 *   builtin:<key>   → look up EDS_Helpers::get_social( $key )
 *   custom:<id>     → look up EDS_Helpers::get_custom_social( $id )
 */
class EDS_Social_Tag extends \Elementor\Core\DynamicTags\Tag {

	// ─── Identity ─────────────────────────────────────────────────────────────

	public function get_name(): string {
		return 'eds-social-links';
	}

	public function get_title(): string {
		return __( 'EDS Social Links', 'eds-global-settings' );
	}

	public function get_group(): string {
		return 'eds-global-settings';
	}

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
				'label'   => __( 'Social Platform', 'eds-global-settings' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'default' => '',
				'options' => EDS_Helpers::social_select_options(),
			]
		);
	}

	public function get_panel_template_setting_key(): string {
		return 'field';
	}

	public function render(): void {
		$field = $this->get_settings( 'field' );
		if ( empty( $field ) ) {
			return;
		}
		echo esc_url( EDS_Helpers::get_social( $field ) );
	}
}
