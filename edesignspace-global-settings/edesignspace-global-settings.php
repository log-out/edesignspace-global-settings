<?php
/**
 * Plugin Name:       EDS Toolkit
 * Plugin URI:        https://edesignspace.com/
 * Description:       EDS Toolkit — Global Settings, Dynamic Tags, and Elementor widgets by eDesign Space.
 * Version:           2.4.3
 * Author:            eDesign Space
 * Author URI:        https://edesignspace.com/
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       eds-global-settings
 * Domain Path:       /languages
 * Requires at least: 5.8
 * Requires PHP:      7.4
 *
 * @package EDS_Global_Settings
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// ─── Plugin Constants ────────────────────────────────────────────────────────

define( 'EDS_VERSION',     '2.4.3' );
define( 'EDS_PLUGIN_FILE', __FILE__ );
define( 'EDS_PLUGIN_DIR',  plugin_dir_path( __FILE__ ) );
define( 'EDS_PLUGIN_URL',  plugin_dir_url( __FILE__ ) );
define( 'EDS_PLUGIN_BASE', plugin_basename( __FILE__ ) );
define( 'EDS_UPDATE_URL',  'https://edesignspace.com/edesignspace-update.json' );

// ─── Bootstrap ───────────────────────────────────────────────────────────────

/**
 * Main plugin class — singleton entry point.
 *
 * @since 1.0.0
 */
final class EDS_Global_Settings {

	/** @var EDS_Global_Settings|null */
	private static $instance = null;

	/**
	 * Returns the single instance of the plugin.
	 *
	 * @return EDS_Global_Settings
	 */
	public static function instance(): self {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/** Private constructor — use ::instance(). */
	private function __construct() {
		$this->load_dependencies();
		$this->register_hooks();
	}

	// ─── Dependencies ─────────────────────────────────────────────────────────

	/**
	 * Require all module files.
	 */
	private function load_dependencies(): void {
		require_once EDS_PLUGIN_DIR . 'includes/class-eds-helpers.php';
		require_once EDS_PLUGIN_DIR . 'includes/class-eds-admin.php';
		require_once EDS_PLUGIN_DIR . 'includes/class-eds-license.php';
		require_once EDS_PLUGIN_DIR . 'includes/class-eds-updater.php';
		require_once EDS_PLUGIN_DIR . 'includes/class-eds-extensions-admin.php';
		require_once EDS_PLUGIN_DIR . 'includes/extensions/class-eds-extension-custom-css.php';
		require_once EDS_PLUGIN_DIR . 'includes/extensions/class-eds-extension-motion-effects.php';
		require_once EDS_PLUGIN_DIR . 'includes/extensions/class-eds-extension-loop-animations.php';
		require_once EDS_PLUGIN_DIR . 'includes/extensions/class-eds-extension-element-link.php';
		require_once EDS_PLUGIN_DIR . 'includes/class-eds-elementor-manager.php';
		require_once EDS_PLUGIN_DIR . 'includes/class-eds-widgets-manager.php';
	}

	// ─── Hooks ────────────────────────────────────────────────────────────────

	/**
	 * Register WordPress & Elementor hooks.
	 */
	private function register_hooks(): void {
		register_activation_hook( EDS_PLUGIN_FILE,  [ $this, 'on_activate' ] );
		register_deactivation_hook( EDS_PLUGIN_FILE, [ $this, 'on_deactivate' ] );

		// Boot updater immediately — the pre_set_site_transient_update_plugins
		// filter must be registered before WordPress fires its update check.
		new EDS_Updater();

		add_action( 'plugins_loaded', [ $this, 'init' ] );

		// Plugin row action link → Settings.
		add_filter( 'plugin_action_links_' . EDS_PLUGIN_BASE, [ $this, 'add_action_links' ] );
	}

	// ─── Init ─────────────────────────────────────────────────────────────────

	/**
	 * Initialise modules after all plugins have loaded.
	 */
	public function init(): void {
		// Load text domain for translations.
		load_plugin_textdomain(
			'eds-global-settings',
			false,
			dirname( EDS_PLUGIN_BASE ) . '/languages'
		);

		// Boot admin modules (always).
		new EDS_Admin();
		new EDS_License();
		new EDS_Extensions_Admin();

		// Boot active extensions (Elementor must be loaded first).
		add_action( 'elementor/init', function () {
			if ( EDS_Extensions_Admin::is_enabled( 'custom-css' ) ) {
				new EDS_Extension_Custom_CSS();
			}
			if ( EDS_Extensions_Admin::is_enabled( 'motion-effects' ) ) {
				new EDS_Extension_Motion_Effects();
			}
			if ( EDS_Extensions_Admin::is_enabled( 'loop-animations' ) ) {
				new EDS_Extension_Loop_Animations();
			}
			if ( EDS_Extensions_Admin::is_enabled( 'element-link' ) ) {
				new EDS_Extension_Element_Link();
			}
		} );

		// Register AJAX hooks unconditionally — must fire on every request including admin-ajax.php.
		add_action( 'wp_ajax_eds_blog_posts_load_more',        [ $this, 'handle_blog_posts_ajax' ] );
		add_action( 'wp_ajax_nopriv_eds_blog_posts_load_more', [ $this, 'handle_blog_posts_ajax' ] );

		// Boot Widgets manager.
		if ( did_action( 'elementor/loaded' ) ) {
			new EDS_Widgets_Manager();
		} else {
			add_action( 'elementor/loaded', static function () {
				new EDS_Widgets_Manager();
			} );
		}

		// Boot Elementor Dynamic Tags manager.
		if ( did_action( 'elementor/loaded' ) ) {
			new EDS_Elementor_Manager();
		} else {
			add_action( 'elementor/loaded', static function () {
				new EDS_Elementor_Manager();
			} );
		}
	}

	/**
	 * AJAX handler for Blog Posts widget pagination.
	 * Registered unconditionally — Elementor is not available on admin-ajax.php
	 * for non-Elementor AJAX requests, so only the renderer (no Elementor deps) is loaded.
	 */
	public function handle_blog_posts_ajax(): void {
		require_once EDS_PLUGIN_DIR . 'widgets/blog-posts/class-eds-blog-posts-renderer.php';
		EDS_Blog_Posts_Renderer::handle_ajax();
	}

	// ─── Lifecycle ────────────────────────────────────────────────────────────

	/**
	 * Runs on plugin activation — seeds default options.
	 */
	public function on_activate(): void {
		foreach ( EDS_Helpers::option_keys() as $key ) {
			if ( false === get_option( $key ) ) {
				add_option( $key, EDS_Helpers::default_option( $key ) );
			}
		}
	}

	/**
	 * Runs on plugin deactivation.
	 */
	public function on_deactivate(): void {
		$timestamp = wp_next_scheduled( 'eds_daily_update_check' );
		if ( $timestamp ) {
			wp_unschedule_event( $timestamp, 'eds_daily_update_check' );
		}
	}

	// ─── Misc ─────────────────────────────────────────────────────────────────

	/**
	 * Adds a "Settings" link on the Plugins list page.
	 *
	 * @param  array $links Existing action links.
	 * @return array
	 */
	public function add_action_links( array $links ): array {
		$settings_link = sprintf(
			'<a href="%s">%s</a>',
			esc_url( admin_url( 'admin.php?page=eds-global-settings' ) ),
			esc_html__( 'Settings', 'eds-global-settings' )
		);
		array_unshift( $links, $settings_link );
		return $links;
	}
}

// Kick off the plugin.
EDS_Global_Settings::instance();
