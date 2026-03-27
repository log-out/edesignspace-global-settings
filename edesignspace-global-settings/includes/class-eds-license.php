<?php
/**
 * EDS Dashboard, License & Update handler — loaded from class-eds-admin.php.
 * Methods are mixed into EDS_Admin via include at the bottom of that class.
 *
 * @package EDS_Global_Settings
 * @since   1.7.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * EDS_License — handles Dashboard, License and Update pages.
 * Instantiated by EDS_Admin constructor.
 */
class EDS_License {

	/** URL of the remote license keys JSON file. */

	/** WordPress menu callback → dashboard page. */
	public static function render_dashboard_static(): void {
		( new self() )->render_dashboard();
	}

	/** WordPress menu callback → license page. */
	public static function render_license_static(): void {
		( new self() )->render_license_page();
	}

	public function __construct() {
		add_action( 'admin_post_eds_save_license', [ $this, 'save_license' ] );
		add_action( 'admin_post_eds_check_update', [ $this, 'check_update' ] );
	}

	// ─── Dashboard ────────────────────────────────────────────────────────────

	public function render_dashboard(): void {
		if ( ! current_user_can( 'manage_options' ) ) return;

		$licensed      = get_option( 'eds_license_active', false );
		$settings_url  = admin_url( 'admin.php?page=eds-global-settings' );
		$widgets_url   = admin_url( 'admin.php?page=eds-widgets' );
		$license_url   = admin_url( 'admin.php?page=eds-license' );
		$extensions_url = admin_url( 'admin.php?page=eds-extensions' );

		$tabs = [
			'contact' => [ 'label' => 'Contact Info',  'icon' => 'dashicons-id-alt',       'url' => $settings_url . '&tab=contact' ],
			'booking' => [ 'label' => 'Booking Links', 'icon' => 'dashicons-calendar-alt',  'url' => $settings_url . '&tab=booking' ],
			'social'  => [ 'label' => 'Social Media',  'icon' => 'dashicons-share',         'url' => $settings_url . '&tab=social' ],
			'custom'  => [ 'label' => 'Custom Data',   'icon' => 'dashicons-database',      'url' => $settings_url . '&tab=custom' ],
			'widgets' => [ 'label' => 'Widgets',       'icon' => 'dashicons-layout',        'url' => $widgets_url ],
			'extensions' => [ 'label' => 'Extensions', 'icon' => 'dashicons-superhero-alt', 'url' => $extensions_url ],
		];

		$registry = EDS_Helpers::widget_registry();
		$ws       = EDS_Helpers::get_widgets_settings();
		$enabled  = count( array_filter( array_map( fn($w) => ! empty( $ws[ $w['id'] ] ), $registry ) ) );
		?>
		<div class="eds-wrap">
			<?php $this->render_header( 'Global Data, Dynamic Tags & Elementor Widgets' ); ?>
			<div class="eds-body">

				<div class="eds-dashboard-layout">

					<!-- Hero card -->
					<div class="eds-dashboard-hero">
						<h2>Welcome to EDS Toolkit</h2>
						<p>Manage global site data, Elementor Dynamic Tags, widgets and extensions from one place.</p>

						<div class="eds-stat-row">
							<div class="eds-stat-card">
								<span class="eds-stat-card__number"><?php echo count( $tabs ); ?></span>
								<span class="eds-stat-card__label">Sections</span>
							</div>
							<div class="eds-stat-card">
								<span class="eds-stat-card__number"><?php echo $enabled; ?>/<?php echo count( $registry ); ?></span>
								<span class="eds-stat-card__label">Widgets On</span>
							</div>
							<div class="eds-stat-card <?php echo $licensed ? 'eds-stat-card--active' : 'eds-stat-card--warning'; ?>">
								<span class="eds-stat-card__number"><?php echo $licensed ? '✓' : '!'; ?></span>
								<span class="eds-stat-card__label"><?php echo $licensed ? 'Licensed' : 'Unlicensed'; ?></span>
							</div>
						</div>

						<div class="eds-dashboard-hero__actions">
							<a href="<?php echo esc_url( $settings_url ); ?>" class="eds-hero-btn eds-hero-btn--primary">
								<span class="dashicons dashicons-database"></span> Global Data
							</a>
							<a href="<?php echo esc_url( $widgets_url ); ?>" class="eds-hero-btn">
								<span class="dashicons dashicons-layout"></span> Widgets
							</a>
							<a href="<?php echo esc_url( $license_url ); ?>" class="eds-hero-btn">
								<span class="dashicons dashicons-awards"></span> License
							</a>
						</div>
					</div>

					<!-- Column 2: Quick Access -->
					<div>
						<h3 class="eds-section-title" style="margin-bottom:10px;">Quick Access</h3>
						<div class="eds-dashboard-grid">
							<?php foreach ( $tabs as $key => $tab ) : ?>
							<a href="<?php echo esc_url( $tab['url'] ); ?>" class="eds-dashboard-card">
								<span class="dashicons <?php echo esc_attr( $tab['icon'] ); ?> eds-dashboard-card__icon"></span>
								<span class="eds-dashboard-card__label"><?php echo esc_html( $tab['label'] ); ?></span>
								<span class="dashicons dashicons-arrow-right-alt eds-dashboard-card__arrow"></span>
							</a>
							<?php endforeach; ?>
							<a href="<?php echo esc_url( $license_url ); ?>" class="eds-dashboard-card">
								<span class="dashicons dashicons-awards eds-dashboard-card__icon"></span>
								<span class="eds-dashboard-card__label">License &amp; Updates</span>
								<span class="dashicons dashicons-arrow-right-alt eds-dashboard-card__arrow"></span>
							</a>
						</div>
					</div>

					<!-- Column 3: Plugin Details -->
					<div>
						<h3 class="eds-section-title" style="margin-bottom:10px;">Plugin Details</h3>
						<div class="eds-plugin-info-card">
							<table class="eds-info-table" style="width:100%;">
								<tr><th style="width:90px;">Plugin</th><td>EDS Toolkit</td></tr>
								<tr><th>Version</th><td>v<?php echo esc_html( EDS_VERSION ); ?></td></tr>
								<tr><th>Author</th><td><a href="https://edesignspace.com/" target="_blank">eDesign Space</a></td></tr>
								<tr><th>License</th><td><?php echo $licensed
									? '<span style="color:#10b981;font-weight:600;">✓ Active</span>'
									: '<span style="color:#f59e0b;font-weight:600;">⚠ <a href="' . esc_url( $license_url ) . '">Activate</a></span>'; ?></td></tr>
							</table>
						</div>
					</div>
				</div>

			</div>
			<?php $this->render_footer(); ?>
		</div>
		<?php
	}

	// ─── License Page ─────────────────────────────────────────────────────────

	public function render_license_page(): void {
		if ( ! current_user_can( 'manage_options' ) ) return;

		$licensed    = get_option( 'eds_license_active', false );
		$saved_key   = get_option( 'eds_license_key', '' );
		$notice      = get_transient( 'eds_license_notice' );
		$update_info = get_transient( 'eds_update_info' );
		delete_transient( 'eds_license_notice' );
		?>
		<div class="eds-wrap">
			<?php $this->render_header( 'License & Updates' ); ?>
			<div class="eds-body eds-body--column">

				<?php if ( $notice ) : ?>
				<div class="eds-notice eds-notice--<?php echo esc_attr( $notice['type'] ); ?>">
					<span class="dashicons dashicons-<?php echo $notice['type'] === 'success' ? 'yes-alt' : ( $notice['type'] === 'error' ? 'dismiss' : 'warning' ); ?>"></span>
					<?php echo esc_html( $notice['message'] ); ?>
				</div>
				<?php endif; ?>

				<div class="eds-license-grid">

					<!-- Card 1: Activation -->
					<div class="eds-card">
						<div class="eds-tab-header">
							<h2><span class="dashicons dashicons-awards"></span> License Activation</h2>
							<p>Enter your license key to enable automatic updates and access priority support.</p>
						</div>
						<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
							<?php wp_nonce_field( 'eds_save_license', 'eds_nonce' ); ?>
							<input type="hidden" name="action" value="eds_save_license">
							<div style="display:flex;gap:10px;align-items:center;margin-bottom:12px;">
								<input type="text" name="eds_license_key"
								       value="<?php echo esc_attr( $licensed ? str_repeat( '•', 10 ) . substr( $saved_key, -4 ) : $saved_key ); ?>"
								       placeholder="Enter your license key…"
								       style="flex:1;padding:10px 14px;border:1.5px solid #e5e7eb;border-radius:6px;font-size:14px;font-family:monospace;letter-spacing:1px;"
								       <?php echo $licensed ? 'readonly' : ''; ?>>
								<?php if ( $licensed ) : ?>
								<button type="submit" name="eds_action" value="deactivate"
								        style="padding:10px 16px;background:#fee2e2;color:#b91c1c;border:1px solid #fca5a5;border-radius:6px;cursor:pointer;white-space:nowrap;">
									Deactivate
								</button>
								<?php else : ?>
								<button type="submit" name="eds_action" value="activate" class="eds-btn eds-btn--primary">
									Activate
								</button>
								<?php endif; ?>
							</div>
							<?php if ( $licensed ) : ?>
							<div style="display:flex;align-items:center;gap:8px;padding:10px 14px;background:#f0fdf4;border:1px solid #bbf7d0;border-radius:6px;color:#166534;">
								<span class="dashicons dashicons-yes-alt" style="color:#16a34a;"></span>
								<strong>License is active</strong>
							</div>
							<?php endif; ?>
						</form>
					</div>

					<?php if ( $licensed ) : ?>

					<!-- Card 2: License Details -->
					<div class="eds-card">
						<div class="eds-tab-header">
							<h2><span class="dashicons dashicons-info-outline"></span> License Details</h2>
						</div>
						<table class="eds-info-table" style="width:100%;">
							<tr><th>Product</th><td>EDS Toolkit</td></tr>
							<tr><th>Type</th><td>Lifetime License</td></tr>
							<tr><th>Sites</th><td>Unlimited</td></tr>
							<tr><th>Updates</th><td>Lifetime Included</td></tr>
							<tr><th>Support</th><td>Priority via eDesign Space</td></tr>
							<tr><th>Version</th><td>v<?php echo esc_html( EDS_VERSION ); ?></td></tr>
						</table>
					</div>

					<!-- Card 3: Updates -->
					<div class="eds-card">
						<div class="eds-tab-header">
							<h2><span class="dashicons dashicons-update"></span> Updates</h2>
						</div>
						<?php if ( $update_info ) :
							$has_update = version_compare( $update_info['version'] ?? '0', EDS_VERSION, '>' );
						?>
						<div style="margin-bottom:16px;padding:14px 16px;background:<?php echo $has_update ? '#fffbeb' : '#f0fdf4'; ?>;border:1px solid <?php echo $has_update ? '#fcd34d' : '#bbf7d0'; ?>;border-radius:8px;">
							<?php if ( $has_update ) : ?>
							<strong style="color:#92400e;">🎉 Update available: v<?php echo esc_html( $update_info['version'] ); ?></strong>
							<p style="margin:6px 0 10px;font-size:12px;color:#78716c;">You have v<?php echo esc_html( EDS_VERSION ); ?> installed.</p>
							<?php
							$update_url = wp_nonce_url(
								admin_url( 'update.php?action=upgrade-plugin&plugin=' . rawurlencode( EDS_PLUGIN_BASE ) ),
								'upgrade-plugin_' . EDS_PLUGIN_BASE
							);
							?>
							<a href="<?php echo esc_url( $update_url ); ?>" class="eds-btn eds-btn--primary" style="display:inline-flex;">
								<span class="dashicons dashicons-update"></span> Update Now
							</a>
							<?php else : ?>
							<strong style="color:#166534;">✓ Latest version installed.</strong>
							<?php endif; ?>
							<p style="margin:8px 0 0;font-size:12px;color:#6b7280;">
								Checked: <?php echo esc_html( date_i18n( 'd M Y H:i', $update_info['checked_at'] ?? time() ) ); ?>
							</p>
						</div>
						<?php endif; ?>
						<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
							<?php wp_nonce_field( 'eds_check_update', 'eds_nonce' ); ?>
							<input type="hidden" name="action" value="eds_check_update">
							<button type="submit" class="eds-btn eds-btn--primary">
								<span class="dashicons dashicons-update"></span> Check for Updates
							</button>
						</form>
					</div>

					<?php endif; ?>

				</div><!-- .eds-license-grid -->

			</div>
			<?php $this->render_footer(); ?>
		</div>
		<?php
	}

	// ─── Shared header/footer ─────────────────────────────────────────────────

	private function render_header( string $subtitle = '' ): void {
		?>
		<div class="eds-header">
			<div class="eds-header__inner">
				<div class="eds-header__brand">
					<span class="eds-header__logo">
						<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><ellipse cx="12" cy="5" rx="9" ry="3"/><path d="M21 12c0 1.66-4 3-9 3s-9-1.34-9-3"/><path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5"/></svg>
					</span>
					<div>
						<h1 class="eds-header__title">EDS Toolkit</h1>
						<?php if ( $subtitle ) : ?><p class="eds-header__sub"><?php echo esc_html( $subtitle ); ?></p><?php endif; ?>
					</div>
				</div>
				<div class="eds-header__meta">
					<span class="eds-badge">v<?php echo esc_html( EDS_VERSION ); ?></span>
					<a href="https://edesignspace.com/" target="_blank" rel="noopener" class="eds-header__author">eDesign Space ↗</a>
				</div>
			</div>
		</div>
		<?php
	}

	private function render_footer(): void {
		?>
		<div class="eds-footer">
			<span>EDS Toolkit <?php echo esc_html( EDS_VERSION ); ?> &nbsp;·&nbsp; Built with ♥ by <a href="https://edesignspace.com/" target="_blank" rel="noopener">eDesign Space</a></span>
		</div>
		<?php
	}

	// ─── License Save ─────────────────────────────────────────────────────────

	public function save_license(): void {
		if ( ! current_user_can( 'manage_options' ) || ! check_admin_referer( 'eds_save_license', 'eds_nonce' ) ) {
			wp_die( 'Unauthorised.' );
		}
		$action = sanitize_key( $_POST['eds_action'] ?? 'activate' );
		if ( 'deactivate' === $action ) {
			update_option( 'eds_license_active', false );
			update_option( 'eds_license_key', '' );
			set_transient( 'eds_license_notice', [ 'type' => 'warning', 'message' => 'License deactivated.' ], 60 );
		} else {
			$key = strtoupper( trim( sanitize_text_field( $_POST['eds_license_key'] ?? '' ) ) );
			if ( empty( $key ) ) {
				set_transient( 'eds_license_notice', [ 'type' => 'error', 'message' => 'Please enter a license key.' ], 60 );
			} else {
				$valid = $this->validate_key_remotely( $key );
				if ( $valid ) {
					update_option( 'eds_license_active', true );
					update_option( 'eds_license_key', $key );
					set_transient( 'eds_license_notice', [ 'type' => 'success', 'message' => 'License activated successfully. Automatic updates are now enabled.' ], 60 );
				} else {
					set_transient( 'eds_license_notice', [ 'type' => 'error', 'message' => 'Invalid license key. Please check and try again.' ], 60 );
				}
			}
		}
		wp_redirect( admin_url( 'admin.php?page=eds-license' ) );
		exit;
	}

	/**
	 * Validate a key against the remote update JSON file.
	 * The same file used for update checks also contains the license keys array.
	 * Format: { "version": "...", "download_url": "...", "keys": ["KEY1", "KEY2"] }
	 */
	private function validate_key_remotely( string $key ): bool {
		$response = wp_remote_get( EDS_UPDATE_URL, [ 'timeout' => 10 ] );
		if ( is_wp_error( $response ) ) {
			return false;
		}
		$body = json_decode( wp_remote_retrieve_body( $response ), true );
		if ( ! isset( $body['keys'] ) || ! is_array( $body['keys'] ) ) {
			return false;
		}
		// Compare case-insensitively.
		$valid_keys = array_map( 'strtoupper', $body['keys'] );
		return in_array( $key, $valid_keys, true );
	}

	// ─── Update Check ─────────────────────────────────────────────────────────

	public function check_update(): void {
		if ( ! current_user_can( 'manage_options' ) || ! check_admin_referer( 'eds_check_update', 'eds_nonce' ) ) {
			wp_die( 'Unauthorised.' );
		}

		// 1. Purge our remote info cache so a fresh fetch happens.
		delete_transient( EDS_Updater::TRANSIENT_KEY );

		// 2. Purge the eds_update_info shown on the License page.
		delete_transient( 'eds_update_info' );

		// 3. Purge WordPress's own plugin update transient — forces WP to
		//    re-check all plugins on next load, picking up our fresh data.
		delete_site_transient( 'update_plugins' );

		// 4. Force a fresh fetch now so the License page shows the result immediately.
		EDS_Updater::force_check();

		// 5. Trigger WordPress to re-run its update check right now.
		wp_update_plugins();

		wp_redirect( admin_url( 'admin.php?page=eds-license' ) );
		exit;
	}
}
