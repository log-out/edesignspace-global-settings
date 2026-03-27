<?php
/**
 * EDS Elementor Manager — registers Dynamic Tags with Elementor (Free & Pro).
 *
 * @package EDS_Global_Settings
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class EDS_Elementor_Manager
 *
 * Handles:
 *  - Loading Dynamic Tag class files
 *  - Registering the "EDS Global Settings" group
 *  - Registering all four tag objects
 *  - Supporting both legacy (<3.5) and modern (≥3.5) Elementor APIs
 */
class EDS_Elementor_Manager {

	/** Group identifier — must match get_group() in each tag class. */
	const GROUP = 'eds-global-settings';

	public function __construct() {
		$this->load_tag_classes();
		$this->register_hooks();
	}

	// ─── Load Tag Files ───────────────────────────────────────────────────────

	private function load_tag_classes(): void {
		require_once EDS_PLUGIN_DIR . 'elementor/tags/class-eds-contact-tag.php';
		require_once EDS_PLUGIN_DIR . 'elementor/tags/class-eds-booking-tag.php';
		require_once EDS_PLUGIN_DIR . 'elementor/tags/class-eds-social-tag.php';
		require_once EDS_PLUGIN_DIR . 'elementor/tags/class-eds-custom-tag.php';
	}

	// ─── Hooks ────────────────────────────────────────────────────────────────

	private function register_hooks(): void {
		// Register the group at the earliest possible point.
		add_action( 'elementor/dynamic_tags/before_render', [ $this, 'register_group' ] );
		add_action( 'elementor/editor/before_enqueue_scripts', [ $this, 'register_group' ] );

		// Register EDS widget category (panel section).
		add_action( 'elementor/elements/categories_registered', [ $this, 'register_category' ] );

		// Support both new API (≥3.5.0) and legacy API (<3.5.0).
		if ( defined( 'ELEMENTOR_VERSION' ) && version_compare( ELEMENTOR_VERSION, '3.5.0', '>=' ) ) {
			add_action( 'elementor/dynamic_tags/register', [ $this, 'register_tags_new' ] );
		} else {
			add_action( 'elementor/dynamic_tags/register_tags', [ $this, 'register_tags_legacy' ] );
		}
	}

	/**
	 * Register the "EDS Elements" widget panel category.
	 *
	 * @param \Elementor\Elements_Manager $elements_manager
	 */
	public function register_category( $elements_manager ): void {
		$elements_manager->add_category(
			'eds-elements',
			[
				'title' => __( 'EDS Elements', 'eds-global-settings' ),
				'icon'  => 'eicon-plug',
			]
		);
	}

	// ─── Group Registration ───────────────────────────────────────────────────

	/**
	 * Register our custom group in the Elementor Dynamic Tags panel.
	 * Safe to call multiple times (Elementor ignores duplicate registrations).
	 */
	public function register_group(): void {
		if (
			! isset( \Elementor\Plugin::$instance )
			|| ! isset( \Elementor\Plugin::$instance->dynamic_tags )
		) {
			return;
		}

		\Elementor\Plugin::$instance->dynamic_tags->register_group(
			self::GROUP,
			[
				'title' => __( 'EDS Global Settings', 'eds-global-settings' ),
			]
		);
	}

	// ─── Tag Registration — New API (≥3.5.0) ─────────────────────────────────

	/**
	 * Register tags using the modern Elementor API.
	 *
	 * @param \Elementor\Core\DynamicTags\Manager $dynamic_tags_manager
	 */
	public function register_tags_new( $dynamic_tags_manager ): void {
		$this->register_group(); // Ensure group exists first.

		$dynamic_tags_manager->register( new EDS_Contact_Tag() );
		$dynamic_tags_manager->register( new EDS_Booking_Tag() );
		$dynamic_tags_manager->register( new EDS_Social_Tag() );
		$dynamic_tags_manager->register( new EDS_Custom_Tag() );
	}

	// ─── Tag Registration — Legacy API (<3.5.0) ───────────────────────────────

	/**
	 * Register tags using the legacy Elementor API.
	 *
	 * @param \Elementor\Core\DynamicTags\Manager $dynamic_tags_manager
	 */
	public function register_tags_legacy( $dynamic_tags_manager ): void {
		$this->register_group(); // Ensure group exists first.

		$dynamic_tags_manager->register_tag( 'EDS_Contact_Tag' );
		$dynamic_tags_manager->register_tag( 'EDS_Booking_Tag' );
		$dynamic_tags_manager->register_tag( 'EDS_Social_Tag' );
		$dynamic_tags_manager->register_tag( 'EDS_Custom_Tag' );
	}
}
