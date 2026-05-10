<?php
/**
 * Social accounts list management for Telkari admin.
 *
 * @package Telkari
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Return the modifier class for a social account admin icon.
 *
 * @param string $platform Platform key.
 * @return string
 */
function telkari_get_social_account_admin_icon_class( $platform ) {
	$platform = sanitize_key( $platform );

	if ( '' === $platform ) {
		return 'telkari-account-row-icon--default';
	}

	return 'telkari-account-row-icon--' . $platform;
}

/**
 * Return an example public URL for a supported platform.
 *
 * @param string $platform Platform key.
 * @return string
 */
function telkari_get_social_account_example_url( $platform ) {
	switch ( $platform ) {
		case 'instagram':
			return 'https://instagram.com/username';
		case 'youtube':
			return 'https://youtube.com/@channel';
		case 'facebook':
			return 'https://facebook.com/page';
		case 'x':
			return 'https://x.com/username';
		case 'linkedin':
			return 'https://linkedin.com/in/username';
		case 'tiktok':
			return 'https://tiktok.com/@username';
		case 'github':
			return 'https://github.com/username';
		case 'pinterest':
			return 'https://pinterest.com/username';
		case 'telegram':
			return 'https://t.me/username';
		case 'whatsapp':
			return 'https://wa.me/905551112233';
		case 'discord':
			return 'https://discord.gg/server';
		case 'twitch':
			return 'https://twitch.tv/username';
		case 'spotify':
			return 'https://open.spotify.com/artist/example';
		default:
			return 'https://';
	}
}

/**
 * Build a compact aria label using an existing translated action label.
 *
 * @param string $action_label   Translated action label.
 * @param string $platform_label Account platform label.
 * @return string
 */
function telkari_get_social_account_action_aria_label( $action_label, $platform_label ) {
	$platform_label = trim( (string) $platform_label );

	if ( '' === $platform_label ) {
		return $action_label;
	}

	return trim( $action_label . ' ' . $platform_label );
}

/**
 * Render the social accounts management interface.
 *
 * @param array $settings Current settings.
 */
function telkari_social_list_render( $settings ) {
	$platforms      = telkari_get_supported_platforms();
	$accounts       = isset( $settings['social_accounts'] ) ? $settings['social_accounts'] : array();
	$enabled_groups = telkari_get_enabled_group_state( $settings );
	?>
	<section class="telkari-admin-workspace" aria-labelledby="telkari-account-section-title">
		<header class="telkari-account-panel-header">
			<div>
				<h2 id="telkari-account-section-title"><?php esc_html_e( 'Social Media Accounts', 'telkari' ); ?></h2>
				<p class="description"><?php esc_html_e( 'Add, reorder, and manage your social media accounts. Drag to reorder.', 'telkari' ); ?></p>
				<p class="telkari-group-status" data-state="<?php echo esc_attr( $enabled_groups['social'] ? 'visible' : 'hidden' ); ?>">
					<?php
					if ( $enabled_groups['social'] ) {
						printf(
							'%1$s <a class="telkari-group-status-link" href="%2$s">%3$s</a>',
							esc_html__( 'Social icons are visible on the frontend.', 'telkari' ),
							esc_url( admin_url( 'admin.php?page=telkari-settings&tab=design#telkari-display-groups' ) ),
							esc_html__( 'You can disable them from the Design tab', 'telkari' )
						);
					} else {
						printf(
							'%1$s <a class="telkari-group-status-link" href="%2$s">%3$s</a>',
							esc_html__( 'Social accounts are saved but hidden on the frontend.', 'telkari' ),
							esc_url( admin_url( 'admin.php?page=telkari-settings&tab=design#telkari-display-groups' ) ),
							esc_html__( 'You can make it visible from the Design tab', 'telkari' )
						);
					}
					?>
				</p>
			</div>
		</header>
		<!-- /.telkari-account-panel-header -->

		<div class="telkari-account-content-shell">
			<div class="telkari-account-main-panel">
				<div class="telkari-account-list-section">
					<div id="telkari-accounts-list"
						class="telkari-sortable-list telkari-account-list"
						data-collection-key="social_accounts"
						data-row-selector=".telkari-account-row"
						data-empty-state-id="telkari-account-empty-state"
						data-empty-message="<?php echo esc_attr__( 'No social accounts added yet. Add your first account below.', 'telkari' ); ?>">
						<?php if ( empty( $accounts ) ) : ?>
							<p class="telkari-empty-state" id="telkari-account-empty-state">
								<?php esc_html_e( 'No social accounts added yet. Add your first account below.', 'telkari' ); ?>
							</p>
						<?php else : ?>
							<?php foreach ( $accounts as $index => $account ) : ?>
								<?php telkari_render_account_row( $account, $index, $platforms ); ?>
							<?php endforeach; ?>
						<?php endif; ?>
					</div>
				</div>
				<!-- /.telkari-account-list-section -->

				<div class="telkari-add-account">
					<?php telkari_render_add_account_form( $platforms ); ?>
				</div>
				<!-- /.telkari-add-account -->
			</div>
			<!-- /.telkari-account-main-panel -->
		</div>
		<!-- /.telkari-account-content-shell -->
	</section>
	<!-- /.telkari-admin-workspace -->
	<?php
}

/**
 * Render a single account row.
 *
 * @param array $account   Account data.
 * @param int   $index     Row index.
 * @param array $platforms Supported platforms.
 */
function telkari_render_account_row( $account, $index, $platforms ) {
	$platform_label = isset( $platforms[ $account['platform'] ] )
		? $platforms[ $account['platform'] ]['label']
		: $account['platform'];
	$platform_class    = telkari_get_social_account_admin_icon_class( $account['platform'] );
	$account_id        = isset( $account['id'] ) ? $account['id'] : wp_generate_uuid4();
	$order             = isset( $account['order'] ) ? absint( $account['order'] ) : (int) $index;
	$is_enabled        = ! empty( $account['enabled'] );
	$row_classes       = 'telkari-account-row';
	$edit_aria_label   = telkari_get_social_account_action_aria_label( __( 'Edit', 'telkari' ), $platform_label );
	$delete_aria_label = telkari_get_social_account_action_aria_label( __( 'Delete', 'telkari' ), $platform_label );
	$toggle_aria_label = telkari_get_social_account_action_aria_label( __( 'Enabled', 'telkari' ), $platform_label );

	if ( ! $is_enabled ) {
		$row_classes .= ' telkari-account-row--disabled';
	}
	?>
	<div class="<?php echo esc_attr( $row_classes ); ?>" data-id="<?php echo esc_attr( $account_id ); ?>">
		<span class="telkari-drag-handle dashicons dashicons-menu" aria-hidden="true"></span>

		<span class="telkari-account-row-icon <?php echo esc_attr( $platform_class ); ?>" aria-hidden="true">
			<span class="telkari-account-row-icon-glyph"></span>
		</span>

		<div class="telkari-account-info">
			<strong class="telkari-account-platform"><?php echo esc_html( $platform_label ); ?></strong>
			<span class="telkari-account-url" title="<?php echo esc_attr( $account['url'] ); ?>"><?php echo esc_html( $account['url'] ); ?></span>
		</div>

		<div class="telkari-account-actions">
			<label class="telkari-toggle telkari-cta-toggle">
				<input type="checkbox"
						name="telkari_settings[social_accounts][<?php echo (int) $index; ?>][enabled]"
						value="1"
						aria-label="<?php echo esc_attr( $toggle_aria_label ); ?>"
						<?php checked( $is_enabled ); ?>>
				<span class="telkari-cta-toggle-track" aria-hidden="true">
					<span class="telkari-cta-toggle-thumb"></span>
				</span>
				<span class="screen-reader-text"><?php echo esc_html( $toggle_aria_label ); ?></span>
			</label>

			<button type="button"
					class="telkari-cta-action-button telkari-cta-action-button--edit telkari-edit-account"
					aria-label="<?php echo esc_attr( $edit_aria_label ); ?>"
					aria-pressed="false">
				<span class="dashicons dashicons-edit" aria-hidden="true"></span>
			</button>

			<button type="button"
					class="telkari-cta-action-button telkari-cta-action-button--delete telkari-delete-account"
					aria-label="<?php echo esc_attr( $delete_aria_label ); ?>">
				<span class="dashicons dashicons-trash" aria-hidden="true"></span>
			</button>
		</div>

		<input type="hidden"
				name="telkari_settings[social_accounts][<?php echo (int) $index; ?>][id]"
				value="<?php echo esc_attr( $account_id ); ?>">
		<input type="hidden"
				name="telkari_settings[social_accounts][<?php echo (int) $index; ?>][platform]"
				value="<?php echo esc_attr( $account['platform'] ); ?>">
		<input type="hidden"
				name="telkari_settings[social_accounts][<?php echo (int) $index; ?>][url]"
				value="<?php echo esc_attr( $account['url'] ); ?>">
		<input type="hidden"
				name="telkari_settings[social_accounts][<?php echo (int) $index; ?>][order]"
				value="<?php echo esc_attr( $order ); ?>"
				class="telkari-order-field">
	</div>
	<?php
}

/**
 * Render the add-new-account form.
 *
 * @param array $platforms Supported platforms.
 */
function telkari_render_add_account_form( $platforms ) {
	?>
	<section class="telkari-account-builder" aria-labelledby="telkari-account-builder-title" data-mode="create">
		<header class="telkari-account-builder-header">
			<div>
				<p class="telkari-account-builder-status" id="telkari-account-builder-status" hidden aria-live="polite"></p>
				<p class="telkari-cta-builder-feedback" id="telkari-account-builder-feedback" hidden role="status" aria-live="polite" aria-atomic="true"></p>
				<h3 id="telkari-account-builder-title"><?php esc_html_e( 'Add New Account', 'telkari' ); ?></h3>
			</div>
		</header>
		<!-- /.telkari-account-builder-header -->

		<fieldset class="telkari-account-platform-selector" id="telkari-account-platform-selector" aria-label="<?php echo esc_attr__( 'Platform', 'telkari' ); ?>">
			<div class="telkari-account-platform-grid">
				<?php foreach ( $platforms as $key => $platform ) : ?>
					<label class="telkari-account-platform-card" title="<?php echo esc_attr( $platform['label'] ); ?>">
						<input type="radio"
								name="telkari-new-platform"
								value="<?php echo esc_attr( $key ); ?>"
								class="telkari-account-platform-input"
								aria-label="<?php echo esc_attr( $platform['label'] ); ?>"
								data-example-url="<?php echo esc_attr( telkari_get_social_account_example_url( $key ) ); ?>">
						<span class="telkari-account-platform-card-body">
							<span class="telkari-account-platform-card-icon <?php echo esc_attr( telkari_get_social_account_admin_icon_class( $key ) ); ?>" aria-hidden="true">
								<span class="telkari-account-platform-card-icon-glyph"></span>
							</span>
							<strong class="telkari-account-platform-card-title" aria-hidden="true"><?php echo esc_html( $platform['label'] ); ?></strong>
						</span>
					</label>
				<?php endforeach; ?>
			</div>

			<div class="telkari-account-platform-summary" id="telkari-account-platform-summary" hidden aria-live="polite">
				<span class="telkari-account-platform-summary-icon" id="telkari-account-platform-summary-icon" aria-hidden="true"></span>
				<div class="telkari-account-platform-summary-copy">
					<strong class="telkari-account-platform-summary-label" id="telkari-account-platform-summary-label"></strong>
					<span class="telkari-account-platform-summary-example" id="telkari-account-platform-summary-example"></span>
				</div>
			</div>
		</fieldset>
		<!-- /.telkari-account-platform-selector -->

		<div class="telkari-account-builder-grid">
			<div class="telkari-add-form-row telkari-add-form-row--wide">
				<label for="telkari-new-url"><?php esc_html_e( 'Profile URL', 'telkari' ); ?></label>
				<input type="url"
						id="telkari-new-url"
						class="regular-text"
						placeholder="https://"
						data-default-placeholder="https://"
						aria-describedby="telkari-account-platform-summary-example telkari-account-url-error">
				<span class="telkari-field-error" id="telkari-account-url-error" hidden aria-live="polite"></span>
			</div>
		</div>
		<!-- /.telkari-account-builder-grid -->

		<div class="telkari-cta-builder-footer">
			<div class="telkari-cta-builder-actions telkari-account-builder-actions">
				<button type="button" class="button telkari-cta-builder-secondary-action" id="telkari-reset-account-form">
					<?php esc_html_e( 'Reset Form', 'telkari' ); ?>
				</button>
				<button type="button" id="telkari-add-account-btn" class="button button-primary" disabled>
					<?php esc_html_e( 'Add Account', 'telkari' ); ?>
				</button>
			</div>
		</div>
		<!-- /.telkari-cta-builder-footer -->
	</section>
	<?php
}
