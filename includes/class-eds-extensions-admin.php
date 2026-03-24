<?php
/**
 * EDS Extensions Admin — manages available extensions.
 *
 * @package EDS_Global_Settings
 * @since   2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class EDS_Extensions_Admin {

	const OPTION_KEY = 'eds_extensions_settings';

	/** Return the list of available extensions. */
	public static function get_registry(): array {
		return [
			[
				'id'          => 'custom-css',
				'label'       => __( 'Custom CSS', 'eds-global-settings' ),
				'description' => __( 'Add custom CSS to any widget, section, or column directly from the Elementor Advanced tab — just like Elementor Pro. Supports the selector variable to scope styles to the element.', 'eds-global-settings' ),
				'icon'        => 'dashicons-editor-code',
				'default'     => true,
			],
			[
				'id'          => 'motion-effects',
				'label'       => __( 'Motion Effects', 'eds-global-settings' ),
				'description' => __( 'Scroll parallax, mouse tracking, and sticky positioning for any element — like Elementor Pro Motion Effects. Includes Stay Within Column support.', 'eds-global-settings' ),
				'icon'        => 'dashicons-move',
				'default'     => false,
			],
			[
				'id'          => 'loop-animations',
				'label'       => __( 'Loop Animations', 'eds-global-settings' ),
				'description' => __( 'Add continuous loop animations to any element — float, shake, spin, pulse, orbit, bounce, wiggle, swing, heartbeat and more. Fully customisable speed and intensity.', 'eds-global-settings' ),
				'icon'        => 'dashicons-controls-repeat',
				'default'     => false,
			],
		];
	}

	/** Check if a specific extension is enabled. */
	public static function is_enabled( string $id ): bool {
		$settings = get_option( self::OPTION_KEY, [] );

		// If not set yet, use the default from registry.
		if ( ! isset( $settings[ $id ] ) ) {
			foreach ( self::get_registry() as $ext ) {
				if ( $ext['id'] === $id ) {
					return ! empty( $ext['default'] );
				}
			}
			return false;
		}

		return ! empty( $settings[ $id ] );
	}

	public static function render_page_static(): void {
		( new self() )->render_page();
	}

	public function __construct() {
		add_action( 'admin_post_eds_save_extensions', [ $this, 'save_extensions' ] );
	}

	public function save_extensions(): void {
		if ( ! current_user_can( 'manage_options' ) || ! check_admin_referer( 'eds_save_extensions', 'eds_nonce' ) ) {
			wp_die( 'Unauthorised.' );
		}

		$new = [];
		foreach ( self::get_registry() as $ext ) {
			$new[ $ext['id'] ] = ! empty( $_POST[ 'ext_' . str_replace( '-', '_', $ext['id'] ) ] );
		}
		update_option( self::OPTION_KEY, $new );

		wp_redirect( add_query_arg( [ 'page' => 'eds-extensions', 'updated' => '1' ], admin_url( 'admin.php' ) ) );
		exit;
	}

	public function render_page(): void {
		if ( ! current_user_can( 'manage_options' ) ) return;

		$saved   = isset( $_GET['updated'] ) && '1' === $_GET['updated'];
		$registry = self::get_registry();
		?>
		<div class="eds-wrap">
			<div class="eds-header">
				<div class="eds-header__inner">
					<div class="eds-header__brand">
						<span class="eds-header__logo">
							<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><ellipse cx="12" cy="5" rx="9" ry="3"/><path d="M21 12c0 1.66-4 3-9 3s-9-1.34-9-3"/><path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5"/></svg>
						</span>
						<div>
							<h1 class="eds-header__title"><?php esc_html_e( 'EDS Toolkit', 'eds-global-settings' ); ?></h1>
							<p class="eds-header__sub"><?php esc_html_e( 'Extensions', 'eds-global-settings' ); ?></p>
						</div>
					</div>
					<div class="eds-header__meta">
						<span class="eds-badge">v<?php echo esc_html( EDS_VERSION ); ?></span>
						<a href="https://edesignspace.com/" target="_blank" rel="noopener" class="eds-header__author">eDesign Space ↗</a>
					</div>
				</div>
			</div>

			<div class="eds-body" style="flex-direction:column;">

				<?php if ( $saved ) : ?>
				<div class="eds-notice eds-notice--success" style="margin-bottom:20px;">
					<span class="dashicons dashicons-yes-alt"></span>
					<?php esc_html_e( 'Extension settings saved.', 'eds-global-settings' ); ?>
				</div>
				<?php endif; ?>

				<div class="eds-tab-header">
					<h2><?php esc_html_e( 'Elementor Extensions', 'eds-global-settings' ); ?></h2>
					<p><?php esc_html_e( 'Enable or disable EDS extensions for Elementor. Changes take effect immediately after saving.', 'eds-global-settings' ); ?></p>
				</div>

				<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
					<?php wp_nonce_field( 'eds_save_extensions', 'eds_nonce' ); ?>
					<input type="hidden" name="action" value="eds_save_extensions">

					<div class="eds-widgets-grid">
						<?php foreach ( $registry as $ext ) :
							$field   = 'ext_' . str_replace( '-', '_', $ext['id'] );
							$enabled = self::is_enabled( $ext['id'] );
						?>
						<div class="eds-widget-card <?php echo $enabled ? 'is-enabled' : ''; ?>">
							<div class="eds-widget-card__body">
								<div class="eds-widget-card__header">
									<div class="eds-widget-card__icon-wrap">
										<span class="eds-widget-card__icon dashicons <?php echo esc_attr( $ext['icon'] ); ?>"></span>
									</div>
									<div class="eds-widget-card__meta">
										<h3 class="eds-widget-card__title"><?php echo esc_html( $ext['label'] ); ?></h3>
										<span class="eds-widget-card__cat"><?php esc_html_e( 'EDS Extension', 'eds-global-settings' ); ?></span>
									</div>
								</div>
								<p class="eds-widget-card__desc"><?php echo esc_html( $ext['description'] ); ?></p>
							</div>
							<div class="eds-widget-card__footer">
								<span class="eds-widget-card__status <?php echo $enabled ? 'is-on' : 'is-off'; ?>">
									<span class="eds-widget-card__status-dot"></span>
									<span><?php echo $enabled ? esc_html__( 'Active', 'eds-global-settings' ) : esc_html__( 'Inactive', 'eds-global-settings' ); ?></span>
								</span>
								<label class="eds-toggle">
									<input type="hidden"   name="<?php echo esc_attr( $field ); ?>" value="0">
									<input type="checkbox" name="<?php echo esc_attr( $field ); ?>" value="1" class="eds-toggle__input"
									       <?php checked( $enabled ); ?>
									       onchange="
									       		var card = this.closest('.eds-widget-card');
									       		card.classList.toggle('is-enabled', this.checked);
									       		var s = card.querySelector('.eds-widget-card__status');
									       		s.className = 'eds-widget-card__status ' + (this.checked ? 'is-on' : 'is-off');
									       		s.querySelector('span:last-child').textContent = this.checked ? '<?php echo esc_js( __( 'Active', 'eds-global-settings' ) ); ?>' : '<?php echo esc_js( __( 'Inactive', 'eds-global-settings' ) ); ?>';
									       ">
									<span class="eds-toggle__track">
										<span class="eds-toggle__thumb"></span>
									</span>
								</label>
							</div>
						</div>
						<?php endforeach; ?>
					</div>

					<div class="eds-form-footer" style="margin-top:24px;">
						<button type="submit" class="eds-btn eds-btn--primary">
							<span class="dashicons dashicons-saved"></span>
							<?php esc_html_e( 'Save Extensions', 'eds-global-settings' ); ?>
						</button>
					</div>
				</form>

			</div>
			<div class="eds-footer">
				<span>EDS Toolkit <?php echo esc_html( EDS_VERSION ); ?> &nbsp;·&nbsp; Built with ♥ by <a href="https://edesignspace.com/" target="_blank" rel="noopener">eDesign Space</a></span>
			</div>
		</div>
		<?php
	}
}
