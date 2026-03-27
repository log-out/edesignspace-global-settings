<?php
/**
 * EDS Widgets Manager — registers enabled custom widgets with Elementor.
 *
 * @package EDS_Global_Settings
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class EDS_Widgets_Manager
 *
 * - Iterates the widget registry from EDS_Helpers
 * - Skips widgets that are disabled in settings
 * - Requires the widget class file and registers it with Elementor
 * - Registers widget assets (style/script) for Elementor to enqueue only
 *   on pages where the widget is actually used
 */
class EDS_Widgets_Manager {

	public function __construct() {
		// Register assets (Elementor enqueues them lazily via get_style_depends).
		add_action( 'wp_enqueue_scripts',               [ $this, 'register_assets' ], 5 );
		add_action( 'elementor/editor/enqueue_scripts', [ $this, 'register_assets' ], 5 );

		// Enqueue assets inside the Elementor preview iframe so scripts/styles run live in the editor.
		add_action( 'elementor/preview/enqueue_scripts', [ $this, 'enqueue_preview_assets' ] );
		add_action( 'elementor/preview/enqueue_styles',  [ $this, 'enqueue_preview_assets' ] );

		// Register widgets with Elementor (modern API ≥3.5.0 and legacy).
		if ( defined( 'ELEMENTOR_VERSION' ) && version_compare( ELEMENTOR_VERSION, '3.5.0', '>=' ) ) {
			add_action( 'elementor/widgets/register', [ $this, 'register_widgets_new' ] );
		} else {
			add_action( 'elementor/widgets/widgets_registered', [ $this, 'register_widgets_legacy' ] );
		}
	}

	// ─── Assets ───────────────────────────────────────────────────────────────

	/**
	 * Register (not enqueue) all widget assets.
	 * Elementor will enqueue them only when the widget appears on the page
	 * via get_style_depends() / get_script_depends().
	 */
	public function register_assets(): void {
		foreach ( EDS_Helpers::widget_registry() as $widget ) {
			if ( ! EDS_Helpers::is_widget_enabled( $widget['id'] ) ) {
				continue;
			}

			$widget_url = EDS_PLUGIN_URL . 'widgets/' . $widget['id'] . '/assets/';
			$widget_dir = EDS_PLUGIN_DIR . 'widgets/' . $widget['id'] . '/assets/';

			$css = $widget_dir . 'css/eds-' . $widget['id'] . '.css';
			$js  = $widget_dir . 'js/eds-'  . $widget['id'] . '.js';

			if ( file_exists( $css ) ) {
				wp_register_style(
					'eds-widget-' . $widget['id'],
					$widget_url . 'css/eds-' . $widget['id'] . '.css',
					[],
					EDS_VERSION
				);
			}

			if ( file_exists( $js ) ) {
				wp_register_script(
					'eds-widget-' . $widget['id'],
					$widget_url . 'js/eds-' . $widget['id'] . '.js',
					[],
					EDS_VERSION,
					true
				);

				wp_localize_script(
					'eds-widget-' . $widget['id'],
					'EDS_Widget_' . str_replace( '-', '_', $widget['id'] ),
					[
						'ajaxurl' => admin_url( 'admin-ajax.php' ),
						'nonce'   => wp_create_nonce( 'eds_widget_nonce' ),
					]
				);
			}
		}
	}

	/**
	 * Enqueue widget assets inside the Elementor editor preview iframe.
	 * This makes animations and scripts run live as you edit, not just on the frontend.
	 */
	public function enqueue_preview_assets(): void {
		foreach ( EDS_Helpers::widget_registry() as $widget ) {
			if ( ! EDS_Helpers::is_widget_enabled( $widget['id'] ) ) {
				continue;
			}
			$widget_dir = EDS_PLUGIN_DIR . 'widgets/' . $widget['id'] . '/assets/';
			if ( file_exists( $widget_dir . 'css/eds-' . $widget['id'] . '.css' ) ) {
				wp_enqueue_style( 'eds-widget-' . $widget['id'] );
			}
			if ( file_exists( $widget_dir . 'js/eds-' . $widget['id'] . '.js' ) ) {
				wp_enqueue_script( 'eds-widget-' . $widget['id'] );
			}
		}
	}

	// ─── Widget Registration ──────────────────────────────────────────────────

	/**
	 * Register widgets — Elementor modern API (≥3.5.0).
	 *
	 * @param \Elementor\Widgets_Manager $widgets_manager
	 */
	public function register_widgets_new( $widgets_manager ): void {
		foreach ( $this->get_enabled_widget_instances() as $instance ) {
			$widgets_manager->register( $instance );
		}
	}

	/**
	 * Register widgets — Elementor legacy API (<3.5.0).
	 *
	 * @param \Elementor\Widgets_Manager $widgets_manager
	 */
	public function register_widgets_legacy( $widgets_manager ): void {
		foreach ( $this->get_enabled_widget_instances() as $instance ) {
			$widgets_manager->register_widget_type( $instance );
		}
	}

	/**
	 * Build instances of all enabled widget classes.
	 *
	 * @return \Elementor\Widget_Base[]
	 */
	private function get_enabled_widget_instances(): array {
		$instances = [];

		foreach ( EDS_Helpers::widget_registry() as $widget ) {
			if ( ! EDS_Helpers::is_widget_enabled( $widget['id'] ) ) {
				continue;
			}

			if ( ! file_exists( $widget['file'] ) ) {
				continue;
			}

			require_once $widget['file'];

			if ( ! class_exists( $widget['class'] ) ) {
				continue;
			}

			$instances[] = new $widget['class']();
		}

		return $instances;
	}

}
