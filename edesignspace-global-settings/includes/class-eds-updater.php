<?php
/**
 * EDS Updater — hooks into WordPress's native plugin update system.
 *
 * When the license is active this class:
 *  1. Periodically checks edesignspace-update.json for a new version.
 *  2. Injects update data into WordPress's update_plugins transient so the
 *     Plugins page shows the native "Update Available" row with an "Update Now" link.
 *  3. Provides plugin info to the WordPress Plugin Details popup.
 *
 * @package EDS_Global_Settings
 * @since   1.8.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class EDS_Updater {

	/** How long to cache the remote version check (12 hours). */
	const CHECK_INTERVAL = 12 * HOUR_IN_SECONDS;

	/** Transient key for cached remote info. */
	const TRANSIENT_KEY = 'eds_remote_version_info';

	public function __construct() {
		// Only hook update checks when license is active.
		if ( ! get_option( 'eds_license_active', false ) ) {
			return;
		}

		add_filter( 'pre_set_site_transient_update_plugins', [ $this, 'inject_update' ] );
		add_filter( 'plugins_api',                           [ $this, 'plugin_info' ], 20, 3 );
		add_action( 'upgrader_process_complete',             [ $this, 'purge_cache' ], 10, 2 );

		// Daily cron — guaranteed once-per-day check independent of WP's own schedule.
		add_action( 'eds_daily_update_check', [ $this, 'run_daily_check' ] );
		if ( ! wp_next_scheduled( 'eds_daily_update_check' ) ) {
			wp_schedule_event( time(), 'daily', 'eds_daily_update_check' );
		}

		// Admin notice on Plugins page when an update is available.
		add_action( 'admin_notices', [ $this, 'admin_update_notice' ] );
	}

	// ─── Inject into WP update transient ─────────────────────────────────────

	/**
	 * Called by WordPress when it checks for plugin updates.
	 * If a newer version is available we add our plugin to the update list.
	 *
	 * @param  object $transient The update_plugins transient.
	 * @return object
	 */
	public function inject_update( object $transient ): object {
		if ( empty( $transient->checked ) ) {
			return $transient;
		}

		$remote = $this->get_remote_info();
		if ( ! $remote || empty( $remote['version'] ) ) {
			return $transient;
		}

		if ( version_compare( $remote['version'], EDS_VERSION, '>' ) ) {
			$transient->response[ EDS_PLUGIN_BASE ] = (object) [
				'slug'        => 'edesignspace-global-settings',
				'plugin'      => EDS_PLUGIN_BASE,
				'new_version' => $remote['version'],
				'url'         => 'https://edesignspace.com/',
				'package'     => $remote['download_url'] ?? '',
				'requires'    => $remote['requires']     ?? '5.8',
				'tested'      => $remote['tested']       ?? '',
				'requires_php'=> $remote['requires_php'] ?? '7.4',
				'icons'       => [],
				'banners'     => [],
			];
		} else {
			// Tell WordPress this plugin is up to date (prevents false "update available" notices).
			$transient->no_update[ EDS_PLUGIN_BASE ] = (object) [
				'slug'        => 'edesignspace-global-settings',
				'plugin'      => EDS_PLUGIN_BASE,
				'new_version' => EDS_VERSION,
				'url'         => 'https://edesignspace.com/',
				'package'     => '',
			];
		}

		return $transient;
	}

	// ─── Plugin info popup ────────────────────────────────────────────────────

	/**
	 * Provides plugin information for the "View details" / update screen popup.
	 *
	 * @param  false|object|array $result
	 * @param  string             $action
	 * @param  object             $args
	 * @return false|object
	 */
	public function plugin_info( $result, string $action, object $args ) {
		if ( $action !== 'plugin_information' ) {
			return $result;
		}
		if ( ! isset( $args->slug ) || $args->slug !== 'edesignspace-global-settings' ) {
			return $result;
		}

		$remote = $this->get_remote_info();
		if ( ! $remote ) {
			return $result;
		}

		return (object) [
			'name'          => 'EDS Toolkit',
			'slug'          => 'edesignspace-global-settings',
			'version'       => $remote['version']      ?? EDS_VERSION,
			'author'        => '<a href="https://edesignspace.com/">eDesign Space</a>',
			'homepage'      => 'https://edesignspace.com/',
			'download_link' => $remote['download_url'] ?? '',
			'requires'      => $remote['requires']     ?? '5.8',
			'tested'        => $remote['tested']       ?? '',
			'requires_php'  => $remote['requires_php'] ?? '7.4',
			'last_updated'  => $remote['last_updated'] ?? '',
			'sections'      => [
				'description' => 'EDS Toolkit — Global Settings, Dynamic Tags, and Elementor widgets by eDesign Space.',
				'changelog'   => $remote['changelog'] ?? '<p>See <a href="https://edesignspace.com/">edesignspace.com</a> for release notes.</p>',
			],
		];
	}

	// ─── Purge cache after update ─────────────────────────────────────────────

	/**
	 * Delete cached remote info after a plugin is updated so the next check is fresh.
	 *
	 * @param \WP_Upgrader $upgrader
	 * @param array        $hook_extra
	 */
	public function purge_cache( $upgrader, array $hook_extra ): void {
		if (
			isset( $hook_extra['type'], $hook_extra['action'] ) &&
			$hook_extra['type']   === 'plugin' &&
			$hook_extra['action'] === 'update'
		) {
			delete_transient( self::TRANSIENT_KEY );
			delete_transient( 'eds_update_info' );
		}
	}

	// ─── Daily cron check ────────────────────────────────────────────────────

	/**
	 * Runs once per day via WP-Cron.
	 * Clears the cache and fetches fresh remote info, then clears the WP
	 * update transient so the next admin page load sees the new data.
	 */
	public function run_daily_check(): void {
		delete_transient( self::TRANSIENT_KEY );
		$info = $this->get_remote_info(); // populates fresh cache
		if ( $info ) {
			// Update the License page display cache too.
			set_transient( 'eds_update_info', [
				'version'      => sanitize_text_field( $info['version']      ?? EDS_VERSION ),
				'download_url' => esc_url_raw( $info['download_url']         ?? '' ),
				'checked_at'   => time(),
			], self::CHECK_INTERVAL );
		}
		// Tell WordPress to re-evaluate plugin updates on next admin load.
		delete_site_transient( 'update_plugins' );
	}

	// ─── Admin notice ─────────────────────────────────────────────────────────

	/**
	 * Shows a dismissible admin notice on the Plugins page (and all admin pages)
	 * when a newer version is available.
	 */
	public function admin_update_notice(): void {
		$remote = $this->get_remote_info();
		if ( ! $remote || empty( $remote['version'] ) ) {
			return;
		}
		if ( ! version_compare( $remote['version'], EDS_VERSION, '>' ) ) {
			return;
		}

		$update_url = wp_nonce_url(
			admin_url( 'update.php?action=upgrade-plugin&plugin=' . rawurlencode( EDS_PLUGIN_BASE ) ),
			'upgrade-plugin_' . EDS_PLUGIN_BASE
		);
		$license_url = admin_url( 'admin.php?page=eds-license' );

		printf(
			'<div class="notice notice-warning is-dismissible"><p>
				<strong>EDS Toolkit %s</strong> %s
				<a href="%s">%s</a> &nbsp;|&nbsp;
				<a href="%s">%s</a>
			</p></div>',
			esc_html( $remote['version'] ),
			esc_html__( 'is available. ', 'eds-global-settings' ),
			esc_url( $update_url ),
			esc_html__( 'Update now', 'eds-global-settings' ),
			esc_url( $license_url ),
			esc_html__( 'View details', 'eds-global-settings' )
		);
	}

	// ─── Remote fetch ─────────────────────────────────────────────────────────

	/**
	 * Fetch (and cache) the remote update JSON.
	 * Returns the decoded array or null on failure.
	 *
	 * @return array|null
	 */
	private function get_remote_info(): ?array {
		$cached = get_transient( self::TRANSIENT_KEY );
		if ( is_array( $cached ) ) {
			return $cached;
		}

		$response = wp_remote_get( EDS_UPDATE_URL, [
			'timeout'    => 10,
			'user-agent' => 'EDS-Toolkit/' . EDS_VERSION . '; ' . get_bloginfo( 'url' ),
		] );

		if ( is_wp_error( $response ) || wp_remote_retrieve_response_code( $response ) !== 200 ) {
			return null;
		}

		$body = json_decode( wp_remote_retrieve_body( $response ), true );
		if ( ! is_array( $body ) || empty( $body['version'] ) ) {
			return null;
		}

		set_transient( self::TRANSIENT_KEY, $body, self::CHECK_INTERVAL );
		return $body;
	}

	/**
	 * Force a fresh remote check and store the result in the eds_update_info transient
	 * used by the License page. Called from EDS_License::check_update().
	 */
	public static function force_check(): void {
		delete_transient( self::TRANSIENT_KEY );
		$updater = new self();
		$info    = $updater->get_remote_info();
		if ( $info ) {
			set_transient( 'eds_update_info', [
				'version'      => sanitize_text_field( $info['version']      ?? EDS_VERSION ),
				'download_url' => esc_url_raw( $info['download_url']         ?? '' ),
				'checked_at'   => time(),
			], HOUR_IN_SECONDS );
		}
	}
}
