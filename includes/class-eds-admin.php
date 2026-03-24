<?php
/**
 * EDS Admin — WordPress admin panel for Site Global Settings.
 *
 * @package EDS_Global_Settings
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class EDS_Admin
 *
 * Handles:
 *  - Admin menu registration
 *  - Asset enqueueing (admin-only)
 *  - Settings save via admin_post
 *  - Admin page rendering
 */
class EDS_Admin {

	/** The admin page slug. */
	const PAGE_SLUG = 'eds-global-settings';

	public function __construct() {
		add_action( 'admin_menu',            [ $this, 'register_menu' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_assets' ] );
		add_action( 'admin_post_eds_save_settings', [ $this, 'save_settings' ] );
	}

	// ─── Menu ─────────────────────────────────────────────────────────────────

	public function register_menu(): void {
		add_menu_page(
			__( 'EDS Toolkit', 'eds-global-settings' ),
			__( 'EDS Toolkit', 'eds-global-settings' ),
			'manage_options',
			'eds-dashboard',
			[ 'EDS_License', 'render_dashboard_static' ],
			'dashicons-superhero-alt',
			58
		);

		add_submenu_page( 'eds-dashboard',
			__( 'Dashboard', 'eds-global-settings' ),
			__( 'Dashboard', 'eds-global-settings' ),
			'manage_options', 'eds-dashboard',
			[ 'EDS_License', 'render_dashboard_static' ]
		);

		add_submenu_page( 'eds-dashboard',
			__( 'Global Data', 'eds-global-settings' ),
			__( 'Global Data', 'eds-global-settings' ),
			'manage_options', self::PAGE_SLUG,
			[ $this, 'render_page' ]
		);

		add_submenu_page( 'eds-dashboard',
			__( 'Widgets', 'eds-global-settings' ),
			__( 'Widgets', 'eds-global-settings' ),
			'manage_options', 'eds-widgets',
			[ $this, 'render_widgets_page' ]
		);

		add_submenu_page( 'eds-dashboard',
			__( 'Extensions', 'eds-global-settings' ),
			__( 'Extensions', 'eds-global-settings' ),
			'manage_options', 'eds-extensions',
			[ 'EDS_Extensions_Admin', 'render_page_static' ]
		);

		add_submenu_page( 'eds-dashboard',
			__( 'License', 'eds-global-settings' ),
			__( 'License', 'eds-global-settings' ),
			'manage_options', 'eds-license',
			[ 'EDS_License', 'render_license_static' ]
		);
	}

	// ─── Assets ───────────────────────────────────────────────────────────────

	public function enqueue_assets( string $hook_suffix ): void {
		// Use $_GET['page'] — the most reliable way to detect our pages regardless of hook suffix format.
		$page = isset( $_GET['page'] ) ? sanitize_key( $_GET['page'] ) : '';
		$our_pages = [ 'eds-dashboard', 'eds-global-settings', 'eds-widgets', 'eds-extensions', 'eds-license' ];
		if ( ! in_array( $page, $our_pages, true ) ) {
			return;
		}

		wp_enqueue_style(
			'eds-admin',
			EDS_PLUGIN_URL . 'assets/css/eds-admin.css',
			[],
			EDS_VERSION
		);

		wp_enqueue_script(
			'eds-admin',
			EDS_PLUGIN_URL . 'assets/js/eds-admin.js',
			[ 'jquery' ],
			EDS_VERSION,
			true
		);

		wp_localize_script( 'eds-admin', 'EDS_Admin', [
			'nonce'       => wp_create_nonce( 'eds_admin_nonce' ),
			'confirm_del' => __( 'Delete this variable?', 'eds-global-settings' ),
			'add_title'   => __( 'New variable title…', 'eds-global-settings' ),
			'add_value'   => __( 'Value…', 'eds-global-settings' ),
			'status_on'   => __( 'Active in Elementor', 'eds-global-settings' ),
			'status_off'  => __( 'Hidden from Elementor', 'eds-global-settings' ),
		] );
	}

	// ─── Save ─────────────────────────────────────────────────────────────────

	/**
	 * Handle the form POST — sanitise and persist all settings.
	 */
	public function save_settings(): void {
		// Security.
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Unauthorised.', 'eds-global-settings' ) );
		}
		check_admin_referer( 'eds_save_settings', 'eds_nonce' );

		$active_tab = isset( $_POST['eds_active_tab'] )
			? sanitize_key( $_POST['eds_active_tab'] )
			: 'contact';

		// ── Only save the active tab's data ───────────────────────────────────
		// Each tab's form only renders its own fields, so we must never write
		// other tabs' options — otherwise saving one tab wipes the others.

		switch ( $active_tab ) {

			case 'contact':
				$contact_data = [];
				foreach ( EDS_Helpers::contact_fields() as $field ) {
					$raw = $_POST['eds_contact'][ $field['key'] ] ?? '';
					$contact_data[ $field['key'] ] = EDS_Helpers::sanitize_by_type(
						wp_unslash( $raw ),
						$field['type']
					);
				}
				update_option( 'eds_contact_info', $contact_data );
				break;

			case 'booking':
				$booking_data = [];
				foreach ( EDS_Helpers::booking_fields() as $field ) {
					$raw = $_POST['eds_booking'][ $field['key'] ] ?? '';
					$booking_data[ $field['key'] ] = EDS_Helpers::sanitize_by_type(
						wp_unslash( $raw ),
						$field['type']
					);
				}
				update_option( 'eds_booking_links', $booking_data );
				break;

			case 'social':
				$social_data = [];
				foreach ( EDS_Helpers::social_fields() as $field ) {
					$raw = $_POST['eds_social'][ $field['key'] ] ?? '';
					$social_data[ $field['key'] ] = EDS_Helpers::sanitize_by_type(
						wp_unslash( $raw ),
						$field['type']
					);
				}
				update_option( 'eds_social_links', $social_data );
				break;

			case 'custom':
				$custom_items = [];
				$raw_ids      = $_POST['eds_custom_id']   ?? [];
				$raw_titles   = $_POST['eds_custom_title'] ?? [];
				$raw_values   = $_POST['eds_custom_value'] ?? [];

				foreach ( $raw_ids as $index => $id ) {
					$id    = sanitize_key( wp_unslash( $id ) );
					$title = sanitize_text_field( wp_unslash( $raw_titles[ $index ] ?? '' ) );
					$value = sanitize_textarea_field( wp_unslash( $raw_values[ $index ] ?? '' ) );

					if ( '' === $id ) {
						$id = 'eds_var_' . uniqid();
					}

					if ( '' !== $title ) {
						$custom_items[] = compact( 'id', 'title', 'value' );
					}
				}
				update_option( 'eds_custom_data', $custom_items );
				break;

			case 'widgets':
				$widget_settings = [];
				$raw_widgets     = $_POST['eds_widget_enabled'] ?? [];
				foreach ( EDS_Helpers::widget_registry() as $widget ) {
					$wid = sanitize_key( $widget['id'] );
					$widget_settings[ $wid ] = isset( $raw_widgets[ $wid ] ) && '1' === $raw_widgets[ $wid ];
				}
				update_option( 'eds_widgets', $widget_settings );
				break;
		}

		// ── Redirect back ─────────────────────────────────────────────────────
		if ( 'widgets' === $active_tab ) {
			wp_safe_redirect(
				add_query_arg(
					[ 'page' => 'eds-widgets', 'settings-updated' => 'true' ],
					admin_url( 'admin.php' )
				)
			);
		} else {
			wp_safe_redirect(
				add_query_arg(
					[
						'page'    => self::PAGE_SLUG,
						'tab'     => $active_tab,
						'updated' => '1',
					],
					admin_url( 'admin.php' )
				)
			);
		}
		exit;
	}

	// ─── Render ───────────────────────────────────────────────────────────────

	/**
	 * Render the full admin page.
	 */
	public function render_page(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$active_tab = isset( $_GET['tab'] ) ? sanitize_key( $_GET['tab'] ) : 'contact';
		$saved      = isset( $_GET['updated'] ) && '1' === $_GET['updated'];

		$tabs = [
			'contact' => [
				'label' => __( 'Contact Info', 'eds-global-settings' ),
				'icon'  => 'dashicons-id-alt',
			],
			'booking' => [
				'label' => __( 'Booking Links', 'eds-global-settings' ),
				'icon'  => 'dashicons-calendar-alt',
			],
			'social'  => [
				'label' => __( 'Social Media', 'eds-global-settings' ),
				'icon'  => 'dashicons-share',
			],
			'custom'  => [
				'label' => __( 'Custom Data', 'eds-global-settings' ),
				'icon'  => 'dashicons-editor-code',
			],
			'help'    => [
				'label' => __( 'How to Use', 'eds-global-settings' ),
				'icon'  => 'dashicons-editor-help',
			],
		];

		// ── Page Wrapper ──────────────────────────────────────────────────────
		?>
		<div class="eds-wrap">

			<!-- ── Header ──────────────────────────────────────────── -->
			<div class="eds-header">
				<div class="eds-header__inner">
					<div class="eds-header__brand">
						<span class="eds-header__logo">
							<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><ellipse cx="12" cy="5" rx="9" ry="3"/><path d="M21 12c0 1.66-4 3-9 3s-9-1.34-9-3"/><path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5"/></svg>
						</span>
						<div>
							<h1 class="eds-header__title">
								<?php esc_html_e( 'EDS Toolkit', 'eds-global-settings' ); ?>
							</h1>
							<p class="eds-header__sub">
								<?php esc_html_e( 'Global Data, Dynamic Tags & Site Information', 'eds-global-settings' ); ?>
							</p>
						</div>
					</div>
					<div class="eds-header__meta">
						<span class="eds-badge">v<?php echo esc_html( EDS_VERSION ); ?></span>
						<a href="https://edesignspace.com/" target="_blank" rel="noopener" class="eds-header__author">
							eDesign Space ↗
						</a>
					</div>
				</div>
			</div>

			<?php if ( $saved ) : ?>
			<!-- ── Notice ───────────────────────────────────────────── -->
			<div class="eds-notice eds-notice--success" id="eds-save-notice">
				<span class="dashicons dashicons-yes-alt"></span>
				<?php esc_html_e( 'Settings saved successfully. Your Elementor Dynamic Tags are now updated.', 'eds-global-settings' ); ?>
			</div>
			<?php endif; ?>

			<!-- ── Body ─────────────────────────────────────────────── -->
			<div class="eds-body">

				<!-- Sidebar Nav -->
				<nav class="eds-nav" aria-label="<?php esc_attr_e( 'Settings sections', 'eds-global-settings' ); ?>">
					<?php foreach ( $tabs as $slug => $tab ) : ?>
					<a href="<?php echo esc_url( add_query_arg( [ 'page' => self::PAGE_SLUG, 'tab' => $slug ], admin_url( 'admin.php' ) ) ); ?>"
					   class="eds-nav__item <?php echo $active_tab === $slug ? 'is-active' : ''; ?>">
						<span class="dashicons <?php echo esc_attr( $tab['icon'] ); ?>"></span>
						<?php echo esc_html( $tab['label'] ); ?>
					</a>
					<?php endforeach; ?>
				</nav>

				<!-- Main Content -->
				<main class="eds-main">
					<?php
					if ( 'help' === $active_tab ) {
						$this->render_tab_help();
					} else {
						$this->render_form( $active_tab );
					}
					?>
				</main>

			</div><!-- .eds-body -->

			<!-- Footer -->
			<div class="eds-footer">
				<span>EDS Toolkit <?php echo esc_html( EDS_VERSION ); ?> &nbsp;·&nbsp; Built with ♥ by <a href="https://edesignspace.com/" target="_blank" rel="noopener">eDesign Space</a></span>
			</div>

		</div><!-- .eds-wrap -->
		<?php
	}

	// ─── Form Wrapper ─────────────────────────────────────────────────────────

	/**
	 * Wrap a tab's fields inside the shared settings form.
	 *
	 * @param string $active_tab Current active tab slug.
	 */
	private function render_form( string $active_tab ): void {
		$contact_data = get_option( 'eds_contact_info',  [] );
		$booking_data = get_option( 'eds_booking_links', [] );
		$social_data  = get_option( 'eds_social_links',  [] );
		$custom_items = EDS_Helpers::get_custom_items();
		?>
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" id="eds-settings-form" novalidate>
			<?php wp_nonce_field( 'eds_save_settings', 'eds_nonce' ); ?>
			<input type="hidden" name="action"          value="eds_save_settings">
			<input type="hidden" name="eds_active_tab"  value="<?php echo esc_attr( $active_tab ); ?>" id="eds-active-tab">

			<?php
			switch ( $active_tab ) {
				case 'contact':
					$this->render_tab_contact( $contact_data );
					break;
				case 'booking':
					$this->render_tab_booking( $booking_data );
					break;
				case 'social':
					$this->render_tab_social( $social_data );
					break;
				case 'custom':
					$this->render_tab_custom( $custom_items );
					break;
			}
			?>

			<div class="eds-form-footer">
				<button type="submit" class="eds-btn eds-btn--primary">
					<span class="dashicons dashicons-saved"></span>
					<?php esc_html_e( 'Save Changes', 'eds-global-settings' ); ?>
				</button>
				<span class="eds-form-footer__hint">
					<?php esc_html_e( 'Changes take effect immediately across all Elementor Dynamic Tags.', 'eds-global-settings' ); ?>
				</span>
			</div>
		</form>
		<?php
	}

	// ─── Tabs ─────────────────────────────────────────────────────────────────

	/**
	 * Render the Contact Information tab.
	 *
	 * @param array $saved Saved values.
	 */
	private function render_tab_contact( array $saved ): void {
		?>
		<div class="eds-tab-header">
			<h2><span class="dashicons dashicons-id-alt"></span> <?php esc_html_e( 'Contact Information', 'eds-global-settings' ); ?></h2>
			<p><?php esc_html_e( 'Store your business contact details. Use the "Contact Info" Dynamic Tag group in Elementor to insert these values anywhere.', 'eds-global-settings' ); ?></p>
		</div>

		<div class="eds-card">
			<div class="eds-fields-grid">
				<?php foreach ( EDS_Helpers::contact_fields() as $field ) : ?>
				<div class="eds-field <?php echo 'textarea' === $field['type'] ? 'eds-field--full' : ''; ?>">
					<label for="eds_contact_<?php echo esc_attr( $field['key'] ); ?>" class="eds-label">
						<span class="dashicons <?php echo esc_attr( $field['icon'] ); ?>"></span>
						<?php echo esc_html( $field['label'] ); ?>
					</label>
					<?php if ( 'textarea' === $field['type'] ) : ?>
					<textarea
						id="eds_contact_<?php echo esc_attr( $field['key'] ); ?>"
						name="eds_contact[<?php echo esc_attr( $field['key'] ); ?>]"
						class="eds-input eds-input--textarea"
						placeholder="<?php echo esc_attr( $field['placeholder'] ); ?>"
						rows="3"
					><?php echo esc_textarea( $saved[ $field['key'] ] ?? '' ); ?></textarea>
					<?php else : ?>
					<input
						type="<?php echo esc_attr( $field['type'] ); ?>"
						id="eds_contact_<?php echo esc_attr( $field['key'] ); ?>"
						name="eds_contact[<?php echo esc_attr( $field['key'] ); ?>]"
						class="eds-input"
						value="<?php echo esc_attr( $saved[ $field['key'] ] ?? '' ); ?>"
						placeholder="<?php echo esc_attr( $field['placeholder'] ); ?>"
					>
					<?php endif; ?>
					<span class="eds-field__tag"><?php echo esc_html( 'eds_contact.' . $field['key'] ); ?></span>
				</div>
				<?php endforeach; ?>
			</div>
		</div>
		<?php
	}

	/**
	 * Render the Booking Links tab.
	 *
	 * @param array $saved Saved values.
	 */
	private function render_tab_booking( array $saved ): void {
		?>
		<div class="eds-tab-header">
			<h2><span class="dashicons dashicons-calendar-alt"></span> <?php esc_html_e( 'Booking & Links', 'eds-global-settings' ); ?></h2>
			<p><?php esc_html_e( 'Store booking URLs, CTA links, and service links. Use the "Booking Links" Dynamic Tag in Elementor to insert them in buttons and text fields.', 'eds-global-settings' ); ?></p>
		</div>

		<div class="eds-card">
			<div class="eds-fields-grid">
				<?php foreach ( EDS_Helpers::booking_fields() as $field ) : ?>
				<div class="eds-field">
					<label for="eds_booking_<?php echo esc_attr( $field['key'] ); ?>" class="eds-label">
						<span class="dashicons <?php echo esc_attr( $field['icon'] ); ?>"></span>
						<?php echo esc_html( $field['label'] ); ?>
					</label>
					<input
						type="<?php echo esc_attr( $field['type'] ); ?>"
						id="eds_booking_<?php echo esc_attr( $field['key'] ); ?>"
						name="eds_booking[<?php echo esc_attr( $field['key'] ); ?>]"
						class="eds-input"
						value="<?php echo esc_attr( $saved[ $field['key'] ] ?? '' ); ?>"
						placeholder="<?php echo esc_attr( $field['placeholder'] ); ?>"
					>
					<span class="eds-field__tag"><?php echo esc_html( 'eds_booking.' . $field['key'] ); ?></span>
				</div>
				<?php endforeach; ?>
			</div>
		</div>
		<?php
	}

	/**
	 * Render the Social Media tab.
	 *
	 * @param array $saved Saved built-in social link values.
	 */
	private function render_tab_social( array $saved ): void {
		?>
		<div class="eds-tab-header">
			<h2><span class="dashicons dashicons-share"></span> <?php esc_html_e( 'Social Media Links', 'eds-global-settings' ); ?></h2>
			<p><?php esc_html_e( 'Store all your social profile URLs in one place. Access them via the "Social Links" Dynamic Tag in Elementor.', 'eds-global-settings' ); ?></p>
		</div>

		<div class="eds-card">
			<div class="eds-fields-grid">
				<?php foreach ( EDS_Helpers::social_fields() as $field ) : ?>
				<div class="eds-field">
					<label for="eds_social_<?php echo esc_attr( $field['key'] ); ?>" class="eds-label">
						<span class="dashicons <?php echo esc_attr( $field['icon'] ); ?>"></span>
						<?php echo esc_html( $field['label'] ); ?>
					</label>
					<input
						type="url"
						id="eds_social_<?php echo esc_attr( $field['key'] ); ?>"
						name="eds_social[<?php echo esc_attr( $field['key'] ); ?>]"
						class="eds-input"
						value="<?php echo esc_attr( $saved[ $field['key'] ] ?? '' ); ?>"
						placeholder="<?php echo esc_attr( $field['placeholder'] ); ?>"
					>
					<span class="eds-field__tag"><?php echo esc_html( 'eds_social.' . $field['key'] ); ?></span>
				</div>
				<?php endforeach; ?>
			</div>
		</div>
		<?php
	}

	/**
	 * Render the Custom Data tab.
	 *
	 * @param array $items Saved custom items.
	 */
	private function render_tab_custom( array $items ): void {
		?>
		<div class="eds-tab-header">
			<h2><span class="dashicons dashicons-database"></span> <?php esc_html_e( 'Custom Variables', 'eds-global-settings' ); ?></h2>
			<p><?php esc_html_e( 'Create unlimited custom key/value variables. Each one becomes a selectable option inside the "Custom Variables" Dynamic Tag in Elementor.', 'eds-global-settings' ); ?></p>
		</div>

		<div class="eds-card eds-card--custom">

			<div class="eds-custom-toolbar">
				<div class="eds-custom-toolbar__info">
					<span class="dashicons dashicons-info-outline"></span>
					<?php esc_html_e( 'Add a title and a value. The title is what you see in the Elementor selector; the value is what gets rendered on the page.', 'eds-global-settings' ); ?>
				</div>
				<button type="button" class="eds-btn eds-btn--outline" id="eds-add-custom">
					<span class="dashicons dashicons-plus-alt2"></span>
					<?php esc_html_e( 'Add Variable', 'eds-global-settings' ); ?>
				</button>
			</div>

			<div class="eds-custom-table-wrap">
				<table class="eds-custom-table" id="eds-custom-table">
					<thead>
						<tr>
							<th class="eds-th--drag"></th>
							<th><?php esc_html_e( 'Variable Title (label in Elementor)', 'eds-global-settings' ); ?></th>
							<th><?php esc_html_e( 'Value (output on page)', 'eds-global-settings' ); ?></th>
							<th class="eds-th--id"><?php esc_html_e( 'ID', 'eds-global-settings' ); ?></th>
							<th class="eds-th--action"></th>
						</tr>
					</thead>
					<tbody id="eds-custom-tbody">

						<?php if ( empty( $items ) ) : ?>
						<tr class="eds-custom-empty" id="eds-custom-empty">
							<td colspan="5">
								<span class="dashicons dashicons-plus-alt2"></span>
								<?php esc_html_e( 'No custom variables yet. Click "Add Variable" to create your first one.', 'eds-global-settings' ); ?>
							</td>
						</tr>
						<?php else : ?>
						<?php foreach ( $items as $item ) : ?>
						<tr class="eds-custom-row" data-id="<?php echo esc_attr( $item['id'] ); ?>">
							<td class="eds-td--drag"><span class="dashicons dashicons-move eds-drag-handle"></span></td>
							<td>
								<input type="hidden"
									   name="eds_custom_id[]"
									   value="<?php echo esc_attr( $item['id'] ); ?>"
									   class="eds-custom-id">
								<input type="text"
									   name="eds_custom_title[]"
									   value="<?php echo esc_attr( $item['title'] ); ?>"
									   class="eds-input eds-input--sm"
									   placeholder="<?php esc_attr_e( 'Variable title…', 'eds-global-settings' ); ?>">
							</td>
							<td>
								<input type="text"
									   name="eds_custom_value[]"
									   value="<?php echo esc_attr( $item['value'] ); ?>"
									   class="eds-input eds-input--sm"
									   placeholder="<?php esc_attr_e( 'Value…', 'eds-global-settings' ); ?>">
							</td>
							<td class="eds-td--id">
								<code><?php echo esc_html( $item['id'] ); ?></code>
							</td>
							<td class="eds-td--action">
								<button type="button" class="eds-btn-icon eds-btn-icon--danger eds-delete-row" title="<?php esc_attr_e( 'Delete', 'eds-global-settings' ); ?>">
									<span class="dashicons dashicons-trash"></span>
								</button>
							</td>
						</tr>
						<?php endforeach; ?>
						<?php endif; ?>

					</tbody>
				</table>
			</div><!-- .eds-custom-table-wrap -->

		</div><!-- .eds-card -->

		<!-- Hidden template row for JS cloning -->
		<template id="eds-custom-row-tpl">
			<tr class="eds-custom-row" data-id="">
				<td class="eds-td--drag"><span class="dashicons dashicons-move eds-drag-handle"></span></td>
				<td>
					<input type="hidden" name="eds_custom_id[]" value="" class="eds-custom-id">
					<input type="text" name="eds_custom_title[]" value="" class="eds-input eds-input--sm" placeholder="<?php esc_attr_e( 'Variable title…', 'eds-global-settings' ); ?>">
				</td>
				<td>
					<input type="text" name="eds_custom_value[]" value="" class="eds-input eds-input--sm" placeholder="<?php esc_attr_e( 'Value…', 'eds-global-settings' ); ?>">
				</td>
				<td class="eds-td--id"><code class="eds-new-id"><?php esc_html_e( 'auto-generated', 'eds-global-settings' ); ?></code></td>
				<td class="eds-td--action">
					<button type="button" class="eds-btn-icon eds-btn-icon--danger eds-delete-row" title="<?php esc_attr_e( 'Delete', 'eds-global-settings' ); ?>">
						<span class="dashicons dashicons-trash"></span>
					</button>
				</td>
			</tr>
		</template>
		<?php
	}

	// ─── Widgets Page (standalone submenu page) ───────────────────────────────

	public function render_widgets_page(): void {
		$saved = isset( $_GET['settings-updated'] ) && 'true' === $_GET['settings-updated'];
		?>
		<div class="eds-wrap">
			<div class="eds-header">
				<div class="eds-header__inner">
					<div class="eds-header__brand">
						<span class="eds-header__logo">
							<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
						</span>
						<div>
							<h1 class="eds-header__title"><?php esc_html_e( 'EDS Widgets', 'eds-global-settings' ); ?></h1>
							<p class="eds-header__sub"><?php esc_html_e( 'Enable or disable custom Elementor widgets', 'eds-global-settings' ); ?></p>
						</div>
					</div>
					<div class="eds-header__meta">
						<span class="eds-badge">v<?php echo esc_html( EDS_VERSION ); ?></span>
						<a href="https://edesignspace.com/" target="_blank" rel="noopener" class="eds-header__author">eDesign Space ↗</a>
					</div>
				</div>
			</div>

			<?php if ( $saved ) : ?>
			<div class="eds-notice eds-notice--success" id="eds-save-notice">
				<span class="dashicons dashicons-yes-alt"></span>
				<?php esc_html_e( 'Widget settings saved.', 'eds-global-settings' ); ?>
			</div>
			<?php endif; ?>

			<div class="eds-body">
				<main class="eds-main" style="width:100%;">
					<?php $this->render_tab_widgets(); ?>
				</main>
			</div>
		</div>
		<?php
	}

	/**
	 * Render the Widgets tab — enable/disable EDS custom Elementor widgets.
	 */
	private function render_tab_widgets(): void {
		$widgets_settings = EDS_Helpers::get_widgets_settings();
		$registry         = EDS_Helpers::widget_registry();
		$elementor_active = defined( 'ELEMENTOR_VERSION' );
		$enabled_count    = count( array_filter( array_column( array_map( function( $w ) use ( $widgets_settings ) {
			return [ 'on' => ! empty( $widgets_settings[ $w['id'] ] ) ];
		}, $registry ), 'on' ) ) );
		?>
		<div class="eds-tab-header">
			<h2><span class="dashicons dashicons-layout"></span> <?php esc_html_e( 'EDS Widgets', 'eds-global-settings' ); ?></h2>
			<p><?php esc_html_e( 'Enable or disable custom Elementor widgets. Disabled widgets load zero assets and are completely hidden from the Elementor panel.', 'eds-global-settings' ); ?></p>
		</div>

		<?php if ( ! $elementor_active ) : ?>
		<div style="display:flex;gap:10px;align-items:flex-start;padding:14px 18px;background:#fffbeb;border:1px solid #fcd34d;border-radius:var(--eds-radius-sm);color:#92400e;margin-bottom:20px;">
			<span class="dashicons dashicons-warning" style="flex-shrink:0;color:#f59e0b;margin-top:1px;"></span>
			<div><strong><?php esc_html_e( 'Elementor not detected.', 'eds-global-settings' ); ?></strong>
			<?php esc_html_e( 'Enable widgets here — they will register automatically once Elementor is installed and activated.', 'eds-global-settings' ); ?></div>
		</div>
		<?php endif; ?>

		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" id="eds-settings-form" novalidate>
			<?php wp_nonce_field( 'eds_save_settings', 'eds_nonce' ); ?>
			<input type="hidden" name="action"         value="eds_save_settings">
			<input type="hidden" name="eds_active_tab" value="widgets">

			<!-- Info bar -->
			<div class="eds-widgets-header">
				<div style="display:flex;align-items:center;gap:8px;">
					<span class="dashicons dashicons-info-outline"></span>
					<span><?php esc_html_e( 'Toggle each widget on or off. Changes take effect in Elementor after saving — you may need to refresh the Elementor editor.', 'eds-global-settings' ); ?></span>
				</div>
				<span class="eds-widgets-count">
					<span class="dashicons dashicons-layout" style="font-size:14px;width:14px;height:14px;"></span>
					<?php echo esc_html( sprintf(
						/* translators: %1$d = enabled count, %2$d = total count */
						_n( '%1$d of %2$d active', '%1$d of %2$d active', $enabled_count, 'eds-global-settings' ),
						$enabled_count,
						count( $registry )
					) ); ?>
				</span>
			</div>

			<div class="eds-widgets-grid">
				<?php foreach ( $registry as $widget ) :
					$wid     = $widget['id'];
					$enabled = ! empty( $widgets_settings[ $wid ] );
				?>
				<div class="eds-widget-card <?php echo $enabled ? 'is-enabled' : ''; ?>">

					<div class="eds-widget-card__body">
						<div class="eds-widget-card__header">
							<div class="eds-widget-card__icon-wrap">
								<span class="eds-widget-card__icon dashicons <?php echo esc_attr( $widget['icon'] ); ?>"></span>
							</div>
							<div class="eds-widget-card__meta">
								<h3 class="eds-widget-card__title"><?php echo esc_html( $widget['label'] ); ?></h3>
								<span class="eds-widget-card__cat"><?php echo esc_html( $widget['category'] ); ?></span>
							</div>
						</div>
						<p class="eds-widget-card__desc"><?php echo esc_html( $widget['description'] ); ?></p>
					</div>

					<div class="eds-widget-card__footer">
						<span class="eds-widget-card__status <?php echo $enabled ? 'is-on' : 'is-off'; ?>">
							<span class="eds-widget-card__status-dot"></span>
							<span><?php echo $enabled ? esc_html__( 'Active in Elementor', 'eds-global-settings' ) : esc_html__( 'Hidden from Elementor', 'eds-global-settings' ); ?></span>
						</span>
						<label class="eds-toggle" title="<?php echo $enabled ? esc_attr__( 'Enabled — click to disable', 'eds-global-settings' ) : esc_attr__( 'Disabled — click to enable', 'eds-global-settings' ); ?>">
							<input type="hidden"   name="eds_widget_enabled[<?php echo esc_attr( $wid ); ?>]" value="0">
							<input type="checkbox" name="eds_widget_enabled[<?php echo esc_attr( $wid ); ?>]" value="1" class="eds-toggle__input" <?php checked( $enabled ); ?>>
							<span class="eds-toggle__track">
								<span class="eds-toggle__thumb"></span>
							</span>
						</label>
					</div>

				</div>
				<?php endforeach; ?>
			</div>

			<div class="eds-form-footer">
				<button type="submit" class="eds-btn eds-btn--primary">
					<span class="dashicons dashicons-saved"></span>
					<?php esc_html_e( 'Save Widget Settings', 'eds-global-settings' ); ?>
				</button>
				<span class="eds-form-footer__hint">
					<?php esc_html_e( 'Refresh the Elementor editor after saving to see changes.', 'eds-global-settings' ); ?>
				</span>
			</div>
		</form>
		<?php
	}

	/**
	 * Render the How-to-Use help tab.
	 */
	private function render_tab_help(): void {
		?>
		<div class="eds-tab-header">
			<h2><span class="dashicons dashicons-editor-help"></span> <?php esc_html_e( 'How to Use', 'eds-global-settings' ); ?></h2>
			<p><?php esc_html_e( 'Quick reference guide for using EDS Toolkit with Elementor Dynamic Tags.', 'eds-global-settings' ); ?></p>
		</div>

		<div class="eds-card eds-help-card">
			<div class="eds-help-step">
				<div class="eds-help-step__num">1</div>
				<div class="eds-help-step__body">
					<h3><?php esc_html_e( 'Save your values here', 'eds-global-settings' ); ?></h3>
					<p><?php esc_html_e( 'Fill in Contact Info, Booking Links, Social Media, or Custom Variables in the tabs on the left. Click "Save Changes" when done.', 'eds-global-settings' ); ?></p>
				</div>
			</div>
			<div class="eds-help-step">
				<div class="eds-help-step__num">2</div>
				<div class="eds-help-step__body">
					<h3><?php esc_html_e( 'Open Elementor on any page', 'eds-global-settings' ); ?></h3>
					<p><?php esc_html_e( 'Edit any page or template with Elementor. Select a widget that supports Dynamic Tags (Heading, Text Editor, Button, Icon Box, etc.).', 'eds-global-settings' ); ?></p>
				</div>
			</div>
			<div class="eds-help-step">
				<div class="eds-help-step__num">3</div>
				<div class="eds-help-step__body">
					<h3><?php esc_html_e( 'Click the Dynamic Tag icon', 'eds-global-settings' ); ?></h3>
					<p><?php esc_html_e( 'In the widget controls panel, click the database/link icon next to a text or URL field to open the Dynamic Tags panel.', 'eds-global-settings' ); ?></p>
				</div>
			</div>
			<div class="eds-help-step">
				<div class="eds-help-step__num">4</div>
				<div class="eds-help-step__body">
					<h3><?php esc_html_e( 'Select an EDS tag', 'eds-global-settings' ); ?></h3>
					<p><?php esc_html_e( 'Under the "EDS Toolkit" group, you will find four tag types: Contact Info, Booking Links, Social Links, and Custom Variables. Select the appropriate one, then pick the specific field from the dropdown.', 'eds-global-settings' ); ?></p>
				</div>
			</div>
			<div class="eds-help-step">
				<div class="eds-help-step__num">5</div>
				<div class="eds-help-step__body">
					<h3><?php esc_html_e( 'Value updates everywhere at once', 'eds-global-settings' ); ?></h3>
					<p><?php esc_html_e( 'Whenever you change a value here and save, it automatically updates in every widget on every page that uses that Dynamic Tag — no re-editing of individual pages needed.', 'eds-global-settings' ); ?></p>
				</div>
			</div>
		</div>

		<div class="eds-card eds-help-card eds-help-tags-ref">
			<h3><span class="dashicons dashicons-tag"></span> <?php esc_html_e( 'Dynamic Tag Reference', 'eds-global-settings' ); ?></h3>
			<p><?php esc_html_e( 'These are the four Dynamic Tag types registered by this plugin:', 'eds-global-settings' ); ?></p>
			<table class="eds-ref-table">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Tag Name', 'eds-global-settings' ); ?></th>
						<th><?php esc_html_e( 'Group', 'eds-global-settings' ); ?></th>
						<th><?php esc_html_e( 'What it inserts', 'eds-global-settings' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td><code>EDS Contact Info</code></td>
						<td>EDS Toolkit</td>
						<td><?php esc_html_e( 'Business name, phone, email, address, maps link…', 'eds-global-settings' ); ?></td>
					</tr>
					<tr>
						<td><code>EDS Booking Links</code></td>
						<td>EDS Toolkit</td>
						<td><?php esc_html_e( 'Booking URLs, CTA labels, portal links…', 'eds-global-settings' ); ?></td>
					</tr>
					<tr>
						<td><code>EDS Social Links</code></td>
						<td>EDS Toolkit</td>
						<td><?php esc_html_e( 'Facebook, Instagram, LinkedIn, etc.', 'eds-global-settings' ); ?></td>
					</tr>
					<tr>
						<td><code>EDS Custom Variable</code></td>
						<td>EDS Toolkit</td>
						<td><?php esc_html_e( 'Any custom key/value pair you defined in Custom Data.', 'eds-global-settings' ); ?></td>
					</tr>
				</tbody>
			</table>
		</div>

		<div class="eds-card eds-help-card">
			<h3><span class="dashicons dashicons-sos"></span> <?php esc_html_e( 'Support', 'eds-global-settings' ); ?></h3>
			<p>
				<?php esc_html_e( 'Built and maintained by ', 'eds-global-settings' ); ?>
				<a href="https://edesignspace.com/" target="_blank" rel="noopener">eDesign Space</a>.
				<?php esc_html_e( 'For help, feature requests, or custom development, visit the website above.', 'eds-global-settings' ); ?>
			</p>
		</div>
		<?php
	}
}
