<?php
/**
 * Admin settings page for Telkari plugin.
 *
 * @package Telkari
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register top-level admin menu item.
 */
function telkari_add_admin_menu() {
	$menu_position = 32.2; // Telmih uses 32 and Nigehban uses 32.1.

	add_menu_page(
		__( 'Telkari - Floating Social Icons & CTAs', 'telkari' ),
		__( 'Telkari', 'telkari' ),
		'manage_options',
		'telkari-settings',
		'telkari_render_settings_page',
		'dashicons-share',
		$menu_position
	);
}
add_action( 'admin_menu', 'telkari_add_admin_menu' );

/**
 * Register settings via WordPress Settings API.
 */
function telkari_register_settings() {
	register_setting(
		'telkari_settings_group',
		'telkari_settings',
		array(
			'type'              => 'array',
			'sanitize_callback' => 'telkari_sanitize_settings',
			'default'           => telkari_get_default_settings(),
		)
	);
}
add_action( 'admin_init', 'telkari_register_settings' );

/**
 * Enqueue admin assets only on the plugin settings page.
 *
 * @param string $hook Current admin page hook.
 */
function telkari_enqueue_admin_assets( $hook ) {
	if ( 'toplevel_page_telkari-settings' !== $hook ) {
		return;
	}

	wp_enqueue_style( 'wp-color-picker' );

	wp_enqueue_style(
		'telkari-admin',
		TELKARI_URL . 'assets/css/admin.css',
		array(),
		TELKARI_VERSION
	);

	wp_enqueue_script(
		'sortablejs',
		TELKARI_URL . 'assets/js/sortable.min.js',
		array(),
		'1.15.6',
		true
	);

	wp_enqueue_script(
		'telkari-admin',
		TELKARI_URL . 'assets/js/admin.js',
		array( 'sortablejs', 'wp-color-picker' ),
		TELKARI_VERSION,
		true
	);

	wp_localize_script(
		'telkari-admin',
		'telkariAdmin',
		array(
			'positions'      => array(
				'design-1' => telkari_get_allowed_positions( 'design-1' ),
				'design-2' => telkari_get_allowed_positions( 'design-2' ),
				'design-3' => telkari_get_allowed_positions( 'design-3' ),
			),
			'positionLabels' => telkari_get_position_labels(),
			'designLabels'   => array(
				'design-1' => __( 'Orbit', 'telkari' ),
				'design-2' => __( 'Ribbon', 'telkari' ),
				'design-3' => __( 'Pillar', 'telkari' ),
			),
			'platforms'      => telkari_get_supported_platforms(),
			'ctaTypes'       => telkari_get_supported_cta_types(),
			'i18n'           => array(
				'selectPlatform'         => __( 'Select Platform', 'telkari' ),
				'confirmDelete'          => __( 'Are you sure you want to delete this account?', 'telkari' ),
				'fillFields'             => __( 'Please select a platform and enter a valid URL.', 'telkari' ),
				'addNewAccount'          => __( 'Add New Account', 'telkari' ),
				'addAccount'             => __( 'Add Account', 'telkari' ),
				'addAccountDescription'  => __( 'Select a platform and enter the public profile URL.', 'telkari' ),
				'editAccountTitle'       => __( 'Edit Social Account', 'telkari' ),
				'editAccountDescription' => __( 'Update the selected social account and save the changes back into the list.', 'telkari' ),
				'editingAccount'         => __( 'Editing Social Account', 'telkari' ),
				'accountAddedFeedback'   => __( 'Social account added.', 'telkari' ),
				'accountUpdatedFeedback' => __( 'Social account updated.', 'telkari' ),
				'duplicateAccount'       => __( 'A similar social account already exists. You can still save this one if needed.', 'telkari' ),
				'saveAccount'            => __( 'Save Account', 'telkari' ),
				'selectCtaType'          => __( 'Select CTA Type', 'telkari' ),
				'confirmDeleteCta'       => __( 'Are you sure you want to delete this CTA button?', 'telkari' ),
				'fillCtaFields'          => __( 'Please select a CTA type and enter a valid value.', 'telkari' ),
				'ctaHintDefault'         => __( 'Choose a CTA type to see the expected format.', 'telkari' ),
				'ctaHintWhatsapp'        => __( 'Use international format without spaces, for example 905551112233.', 'telkari' ),
				'ctaHintPhone'           => __( 'Phone numbers may include a leading plus sign, for example +905551112233.', 'telkari' ),
				'ctaHintEmail'           => __( 'Enter a valid email address, for example info@example.com.', 'telkari' ),
				'ctaHintUrl'             => __( 'Enter a full web address including the protocol.', 'telkari' ),
				'ctaErrorWhatsapp'       => __( 'Use international format without spaces. Example: 905551112233.', 'telkari' ),
				'ctaErrorPhone'          => __( 'Enter a valid phone number. Example: +905551112233.', 'telkari' ),
				'ctaErrorEmail'          => __( 'Enter a valid email address. Example: info@example.com.', 'telkari' ),
				'ctaErrorUrl'            => __( 'Enter a full address including the protocol. Example: https://example.com/contact.', 'telkari' ),
				'ctaValueLabelDefault'   => __( 'Destination Value', 'telkari' ),
				'ctaValueLabelWhatsapp'  => __( 'WhatsApp Number', 'telkari' ),
				'ctaValueLabelPhone'     => __( 'Phone Number', 'telkari' ),
				'ctaValueLabelEmail'     => __( 'Email Address', 'telkari' ),
				'ctaValueLabelUrl'       => __( 'URL', 'telkari' ),
				'defaultColor'           => __( 'Default color', 'telkari' ),
				'customColor'            => __( 'Custom color', 'telkari' ),
				'edit'                   => __( 'Edit', 'telkari' ),
				'addCtaTitle'            => __( 'Add New CTA Button', 'telkari' ),
				'addCtaDescription'      => __( 'Build a CTA button with guided fields and instant preview.', 'telkari' ),
				'editCtaTitle'           => __( 'Edit CTA Button', 'telkari' ),
				'editCtaDescription'     => __( 'Update the selected CTA button and save the changes back into the list.', 'telkari' ),
				'editingCta'             => __( 'Editing CTA Button', 'telkari' ),
				'ctaAddedFeedback'       => __( 'CTA button added.', 'telkari' ),
				'ctaUpdatedFeedback'     => __( 'CTA button updated.', 'telkari' ),
				'addCtaButton'           => __( 'Add CTA Button', 'telkari' ),
				'saveCtaButton'          => __( 'Save CTA Button', 'telkari' ),
				'cancelEdit'             => __( 'Cancel Edit', 'telkari' ),
				'resetForm'              => __( 'Reset Form', 'telkari' ),
				'enabled'                => __( 'Enabled', 'telkari' ),
				'visible'                => __( 'Visible', 'telkari' ),
				'hidden'                 => __( 'Hidden', 'telkari' ),
				'delete'                 => __( 'Delete', 'telkari' ),
				'placementNoGroups'      => __( 'Enable Social Icons or CTA Buttons to configure placement.', 'telkari' ),
				/* translators: 1: Social Icons placement label, 2: CTA Buttons placement label. */
				'placementAdjusted'      => __( 'Placement adjusted to avoid overlap: Social Icons %1$s, CTA Buttons %2$s.', 'telkari' ),
				/* translators: 1: Design name, 2: CTA placement label. */
				'ctaPreviewContext'      => __( 'Design: %1$s. CTA placement: %2$s.', 'telkari' ),
			),
		)
	);
}
add_action( 'admin_enqueue_scripts', 'telkari_enqueue_admin_assets' );

/**
 * Render the main settings page.
 */
function telkari_render_settings_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$settings   = telkari_get_settings();
	$active_tab = isset( $_GET['tab'] ) ? sanitize_key( wp_unslash( $_GET['tab'] ) ) : 'design'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Tab navigation only, no data processing.
	$tabs       = array(
		'design'     => __( 'Design', 'telkari' ),
		'accounts'   => __( 'Social Accounts', 'telkari' ),
		'cta'        => __( 'CTA Buttons', 'telkari' ),
		'appearance' => __( 'Appearance', 'telkari' ),
	);

	?>
	<div class="wrap telkari-admin-wrap">
		<div class="telkari-admin-header">
			<div class="telkari-name">
				<i class="telkari-icon"></i>
				<?php echo esc_html( get_admin_page_title() ); ?>
				<a href="https://tercan.net/telkari/" target="_blank" rel="noopener noreferrer" class="telkari-admin-header-action">
					(v<?php echo esc_html( TELKARI_VERSION ); ?>)
				</a>
			</div>
			<div class="telkari-admin-header-actions">
				<a href="https://tercan.github.io/telkari/" target="_blank" rel="noopener noreferrer" class="telkari-admin-header-action">
					<?php echo esc_html__( 'Demo/Documentation', 'telkari' ); ?>
				</a>
			</div>
		</div>

		<nav class="nav-tab-wrapper telkari-admin-tabs">
			<?php foreach ( $tabs as $tab_key => $tab_label ) : ?>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=telkari-settings&tab=' . $tab_key ) ); ?>"
					class="nav-tab telkari-admin-tab <?php echo esc_attr( $active_tab === $tab_key ? 'nav-tab-active telkari-admin-tab--active' : '' ); ?>">
					<?php echo esc_html( $tab_label ); ?>
				</a>
			<?php endforeach; ?>
		</nav>

		<form method="post" action="options.php" id="telkari-settings-form" class="telkari-admin-form">
			<?php settings_fields( 'telkari_settings_group' ); ?>

			<?php
			switch ( $active_tab ) {
				case 'design':
					telkari_render_design_tab( $settings );
					break;
				case 'accounts':
					telkari_render_accounts_tab( $settings );
					break;
				case 'cta':
					telkari_render_cta_tab( $settings );
					break;
				case 'appearance':
					telkari_render_appearance_tab( $settings );
					break;
			}
			?>

			<?php submit_button(); ?>
		</form>
	</div>
	<?php
}

/**
 * Render the design selection tab.
 *
 * @param array $settings Current settings.
 */
function telkari_render_design_tab( $settings ) {
	require_once TELKARI_PATH . 'includes/admin/design-selector.php';
	?>
	<section class="telkari-admin-workspace" aria-label="<?php echo esc_attr__( 'Design', 'telkari' ); ?>">
		<?php telkari_design_selector_render( $settings ); ?>
	</section>
	<!-- /.telkari-admin-workspace -->
	<?php
}

/**
 * Render the social accounts tab.
 *
 * @param array $settings Current settings.
 */
function telkari_render_accounts_tab( $settings ) {
	require_once TELKARI_PATH . 'includes/admin/social-list-table.php';
	telkari_social_list_render( $settings );

	// Preserve other settings as hidden fields.
	telkari_render_hidden_settings( $settings, array( 'social_accounts' ) );
}

/**
 * Render the CTA buttons tab.
 *
 * @param array $settings Current settings.
 */
function telkari_render_cta_tab( $settings ) {
	require_once TELKARI_PATH . 'includes/admin/cta-list-table.php';
	telkari_cta_list_render( $settings );

	// Preserve other settings as hidden fields.
	telkari_render_hidden_settings( $settings, array( 'cta_buttons' ) );
}

/**
 * Render the appearance settings tab.
 *
 * @param array $settings Current settings.
 */
function telkari_render_appearance_tab( $settings ) {
	$groups              = telkari_get_enabled_group_state( $settings );
	$social_icon_size    = isset( $settings['social_icon_size'] ) ? $settings['social_icon_size'] : $settings['icon_size'];
	$social_icon_spacing = isset( $settings['social_icon_spacing'] ) ? $settings['social_icon_spacing'] : $settings['icon_spacing'];
	$cta_button_size     = isset( $settings['cta_button_size'] ) ? $settings['cta_button_size'] : 'default';
	$cta_button_spacing  = isset( $settings['cta_button_spacing'] ) ? $settings['cta_button_spacing'] : 8;
	$cta_button_width    = isset( $settings['cta_button_width'] ) ? $settings['cta_button_width'] : 'content';
	$brand_colors        = telkari_get_platform_brand_colors();
	$platforms           = telkari_get_supported_platforms();
	$platform_colors     = isset( $settings['platform_colors'] ) ? $settings['platform_colors'] : array();
	$trigger_default     = $brand_colors['trigger_button'];
	$trigger_current     = isset( $platform_colors['trigger_button'] ) ? $platform_colors['trigger_button'] : $trigger_default;
	$wrapper_default     = $brand_colors['wrapper_bg'];
	$wrapper_current     = isset( $platform_colors['wrapper_bg'] ) ? $platform_colors['wrapper_bg'] : $wrapper_default;
	$is_transparent      = ( 'transparent' === $wrapper_current || empty( $wrapper_current ) );
	$cta_size_options    = array(
		'compact' => __( 'Compact', 'telkari' ),
		'default' => __( 'Default', 'telkari' ),
		'large'   => __( 'Large', 'telkari' ),
	);
	$cta_width_options   = array(
		'content' => __( 'Natural', 'telkari' ),
		'fixed'   => __( 'Fixed', 'telkari' ),
		'full'    => __( 'Full', 'telkari' ),
	);
	?>
	<section class="telkari-admin-workspace telkari-appearance-workspace" aria-label="<?php echo esc_attr__( 'Appearance', 'telkari' ); ?>">
		<h3 class="telkari-settings-section-title"><?php esc_html_e( 'Social Icons', 'telkari' ); ?></h3>
		<p class="telkari-setting-description telkari-settings-section-description">
			<?php
			echo esc_html(
				$groups['social']
					? __( 'Controls the size, spacing, shape, and hover labels for social icons.', 'telkari' )
					: __( 'Social icons are currently hidden on the frontend, but these settings are kept.', 'telkari' )
			);
			?>
		</p>

		<div class="telkari-settings-panel telkari-settings-panel--appearance<?php echo esc_attr( $groups['social'] ? '' : ' telkari-settings-section--inactive' ); ?>">
			<div class="telkari-settings-row telkari-settings-row--two-columns">
				<div class="telkari-setting-card">
					<label class="telkari-setting-label" for="telkari-social-icon-size"><?php esc_html_e( 'Social Icon Size (px)', 'telkari' ); ?></label>
					<div class="telkari-setting-control">
						<div class="telkari-range-field">
							<input type="range"
									id="telkari-social-icon-size"
									name="telkari_settings[social_icon_size]"
									value="<?php echo esc_attr( $social_icon_size ); ?>"
									min="24" max="96" step="4"
									class="telkari-range-input">
							<output class="telkari-range-value" for="telkari-social-icon-size"><?php echo esc_html( $social_icon_size ); ?></output>
						</div>
					</div>
					<span class="telkari-setting-description"><?php esc_html_e( 'Social icon size in pixels (24-96).', 'telkari' ); ?></span>
				</div>

				<div class="telkari-setting-card">
					<label class="telkari-setting-label" for="telkari-social-icon-spacing"><?php esc_html_e( 'Social Icon Spacing (px)', 'telkari' ); ?></label>
					<div class="telkari-setting-control">
						<div class="telkari-range-field">
							<input type="range"
									id="telkari-social-icon-spacing"
									name="telkari_settings[social_icon_spacing]"
									value="<?php echo esc_attr( $social_icon_spacing ); ?>"
									min="0" max="48" step="4"
									class="telkari-range-input">
							<output class="telkari-range-value" for="telkari-social-icon-spacing"><?php echo esc_html( $social_icon_spacing ); ?></output>
						</div>
					</div>
					<span class="telkari-setting-description"><?php esc_html_e( 'Space between social icons in pixels (0-48).', 'telkari' ); ?></span>
				</div>
			</div>

			<div class="telkari-settings-row telkari-settings-row--two-columns">
				<div class="telkari-setting-card">
					<span class="telkari-setting-label"><?php esc_html_e( 'Social Icon Style', 'telkari' ); ?></span>
					<div class="telkari-setting-control">
						<div class="telkari-btn-group">
							<label class="telkari-btn-option <?php echo esc_attr( 'rounded' === $settings['icon_style'] ? 'telkari-btn-option--active' : '' ); ?>">
								<input type="radio" name="telkari_settings[icon_style]" value="rounded" <?php checked( $settings['icon_style'], 'rounded' ); ?>>
								<?php esc_html_e( 'Rounded', 'telkari' ); ?>
							</label>
							<label class="telkari-btn-option <?php echo esc_attr( 'square' === $settings['icon_style'] ? 'telkari-btn-option--active' : '' ); ?>">
								<input type="radio" name="telkari_settings[icon_style]" value="square" <?php checked( $settings['icon_style'], 'square' ); ?>>
								<?php esc_html_e( 'Square', 'telkari' ); ?>
							</label>
						</div>
					</div>
				</div>

				<div class="telkari-setting-card">
					<span class="telkari-setting-label"><?php esc_html_e( 'Social Tooltips', 'telkari' ); ?></span>
					<div class="telkari-setting-control">
						<div class="telkari-btn-group">
							<label class="telkari-btn-option <?php echo esc_attr( $settings['show_tooltip'] ? 'telkari-btn-option--active' : '' ); ?>">
								<input type="radio" name="telkari_settings[show_tooltip]" value="1" <?php checked( $settings['show_tooltip'], true ); ?>>
								<?php esc_html_e( 'On', 'telkari' ); ?>
							</label>
							<label class="telkari-btn-option <?php echo esc_attr( ! $settings['show_tooltip'] ? 'telkari-btn-option--active' : '' ); ?>">
								<input type="radio" name="telkari_settings[show_tooltip]" value="0" <?php checked( $settings['show_tooltip'], false ); ?>>
								<?php esc_html_e( 'Off', 'telkari' ); ?>
							</label>
						</div>
					</div>
				</div>
			</div>
		</div>

		<h3 class="telkari-settings-section-title"><?php esc_html_e( 'CTA Buttons', 'telkari' ); ?></h3>
		<p class="telkari-setting-description telkari-settings-section-description">
			<?php
			echo esc_html(
				$groups['cta']
					? __( 'Controls CTA button density, spacing, and width behavior.', 'telkari' )
					: __( 'CTA buttons are currently hidden on the frontend, but these settings are kept.', 'telkari' )
			);
			?>
		</p>

		<div class="telkari-settings-panel telkari-settings-panel--appearance<?php echo esc_attr( $groups['cta'] ? '' : ' telkari-settings-section--inactive' ); ?>">
			<div class="telkari-settings-row telkari-settings-row--three-columns">
				<div class="telkari-setting-card">
					<span class="telkari-setting-label"><?php esc_html_e( 'CTA Button Size', 'telkari' ); ?></span>
					<div class="telkari-setting-control">
						<div class="telkari-btn-group">
							<?php foreach ( $cta_size_options as $value => $label ) : ?>
								<label class="telkari-btn-option <?php echo esc_attr( $cta_button_size === $value ? 'telkari-btn-option--active' : '' ); ?>">
									<input type="radio" name="telkari_settings[cta_button_size]" value="<?php echo esc_attr( $value ); ?>" <?php checked( $cta_button_size, $value ); ?>>
									<?php echo esc_html( $label ); ?>
								</label>
							<?php endforeach; ?>
						</div>
					</div>
				</div>

				<div class="telkari-setting-card">
					<label class="telkari-setting-label" for="telkari-cta-button-spacing"><?php esc_html_e( 'CTA Button Spacing (px)', 'telkari' ); ?></label>
					<div class="telkari-setting-control">
						<div class="telkari-range-field">
							<input type="range"
									id="telkari-cta-button-spacing"
									name="telkari_settings[cta_button_spacing]"
									value="<?php echo esc_attr( $cta_button_spacing ); ?>"
									min="0" max="48" step="4"
									class="telkari-range-input">
							<output class="telkari-range-value" for="telkari-cta-button-spacing"><?php echo esc_html( $cta_button_spacing ); ?></output>
						</div>
					</div>
					<span class="telkari-setting-description"><?php esc_html_e( 'Space between CTA buttons in pixels (0-48).', 'telkari' ); ?></span>
				</div>

				<div class="telkari-setting-card">
					<span class="telkari-setting-label"><?php esc_html_e( 'CTA Button Width', 'telkari' ); ?></span>
					<div class="telkari-setting-control">
						<div class="telkari-btn-group">
							<?php foreach ( $cta_width_options as $value => $label ) : ?>
								<label class="telkari-btn-option <?php echo esc_attr( $cta_button_width === $value ? 'telkari-btn-option--active' : '' ); ?>">
									<input type="radio" name="telkari_settings[cta_button_width]" value="<?php echo esc_attr( $value ); ?>" <?php checked( $cta_button_width, $value ); ?>>
									<?php echo esc_html( $label ); ?>
								</label>
							<?php endforeach; ?>
						</div>
					</div>
					<span class="telkari-setting-description"><?php esc_html_e( 'Mobile CTA labels stay on one line and shorten when needed.', 'telkari' ); ?></span>
				</div>
			</div>
		</div>

		<h3 class="telkari-settings-section-title"><?php esc_html_e( 'Link Behavior', 'telkari' ); ?></h3>
		<div class="telkari-settings-panel telkari-settings-panel--appearance">
			<div class="telkari-settings-row telkari-settings-row--two-columns">
				<div class="telkari-setting-card">
					<span class="telkari-setting-label"><?php esc_html_e( 'Link Target', 'telkari' ); ?></span>
					<div class="telkari-setting-control">
						<div class="telkari-btn-group">
							<label class="telkari-btn-option <?php echo esc_attr( '_self' === $settings['link_target'] ? 'telkari-btn-option--active' : '' ); ?>">
								<input type="radio" name="telkari_settings[link_target]" value="_self" <?php checked( $settings['link_target'], '_self' ); ?>>
								<?php esc_html_e( 'Same Tab', 'telkari' ); ?>
							</label>
							<label class="telkari-btn-option <?php echo esc_attr( '_blank' === $settings['link_target'] ? 'telkari-btn-option--active' : '' ); ?>">
								<input type="radio" name="telkari_settings[link_target]" value="_blank" <?php checked( $settings['link_target'], '_blank' ); ?>>
								<?php esc_html_e( 'New Tab', 'telkari' ); ?>
							</label>
						</div>
					</div>
					<span class="telkari-setting-description"><?php esc_html_e( 'Applies to social links and CTA buttons.', 'telkari' ); ?></span>
				</div>
			</div>
		</div>

		<h3 class="telkari-settings-section-title"><?php esc_html_e( 'Colors', 'telkari' ); ?></h3>
		<p class="telkari-setting-description telkari-settings-section-description">
			<?php esc_html_e( 'Override social platform colors and shared design colors. CTA button colors are managed per button in the CTA Buttons tab.', 'telkari' ); ?>
		</p>
		<div class="telkari-color-highlight">
			<div class="telkari-color-item">
				<label><?php esc_html_e( 'Bar Background (Ribbon / Pillar)', 'telkari' ); ?></label>
				<input type="text"
						class="telkari-color-picker"
						id="telkari-wrapper-bg-picker"
						name="telkari_settings[platform_colors][wrapper_bg]"
						value="<?php echo esc_attr( $is_transparent ? '' : $wrapper_current ); ?>"
						data-default-color="<?php echo esc_attr( $wrapper_default ); ?>">
				<label class="telkari-transparent-toggle">
					<input type="checkbox"
							id="telkari-wrapper-bg-transparent"
							<?php checked( $is_transparent ); ?>>
					<?php esc_html_e( 'Transparent', 'telkari' ); ?>
				</label>
				<input type="hidden" id="telkari-wrapper-bg-hidden" name="" value="transparent" disabled>
			</div>
			<div class="telkari-color-item">
				<label><?php esc_html_e( 'Main Button (Orbit)', 'telkari' ); ?></label>
				<input type="text"
						class="telkari-color-picker"
						name="telkari_settings[platform_colors][trigger_button]"
						value="<?php echo esc_attr( $trigger_current ); ?>"
						data-default-color="<?php echo esc_attr( $trigger_default ); ?>">
			</div>
		</div>
		<div class="telkari-color-grid">
			<?php
			foreach ( $platforms as $key => $platform ) :
				$default_color = isset( $brand_colors[ $key ] ) ? $brand_colors[ $key ] : '#1e293b';
				$current_color = isset( $platform_colors[ $key ] ) ? $platform_colors[ $key ] : $default_color;
				?>
				<div class="telkari-color-item">
					<label><?php echo esc_html( $platform['label'] ); ?></label>
					<input type="text"
							class="telkari-color-picker"
							name="telkari_settings[platform_colors][<?php echo esc_attr( $key ); ?>]"
							value="<?php echo esc_attr( $current_color ); ?>"
							data-default-color="<?php echo esc_attr( $default_color ); ?>">
				</div>
			<?php endforeach; ?>
		</div>
		<p class="telkari-color-actions">
			<button type="button" class="button" id="telkari-reset-colors">
				<?php esc_html_e( 'Reset All Colors', 'telkari' ); ?>
			</button>
		</p>

		<?php
		telkari_render_hidden_settings(
			$settings,
			array(
				'icon_size',
				'icon_spacing',
				'social_icon_size',
				'social_icon_spacing',
				'cta_button_size',
				'cta_button_spacing',
				'cta_button_width',
				'icon_style',
				'link_target',
				'show_tooltip',
				'platform_colors',
			)
		);
		?>
	</section>
	<!-- /.telkari-admin-workspace -->
	<?php
}

/**
 * Render hidden fields to preserve settings from other tabs.
 *
 * When submitting from one tab, settings from other tabs would be lost
 * without hidden fields to carry them through.
 *
 * @param array $settings     Current settings.
 * @param array $exclude_keys Keys managed by the current tab.
 */
function telkari_render_hidden_settings( $settings, $exclude_keys ) {
	$simple_keys = array(
		'active_design',
		'active_position',
		'cta_position',
		'show_social_accounts',
		'show_cta_buttons',
		'icon_size',
		'icon_spacing',
		'social_icon_size',
		'social_icon_spacing',
		'cta_button_size',
		'cta_button_spacing',
		'cta_button_width',
		'icon_style',
		'link_target',
		'show_tooltip',
	);

	foreach ( $simple_keys as $key ) {
		if ( in_array( $key, $exclude_keys, true ) ) {
			continue;
		}
		$value = isset( $settings[ $key ] ) ? $settings[ $key ] : '';
		if ( is_bool( $value ) ) {
			$value = $value ? '1' : '';
		}
		printf(
			'<input type="hidden" name="telkari_settings[%s]" value="%s">',
			esc_attr( $key ),
			esc_attr( $value )
		);
	}

	// Platform colors need special handling.
	if ( ! in_array( 'platform_colors', $exclude_keys, true ) && ! empty( $settings['platform_colors'] ) ) {
		foreach ( $settings['platform_colors'] as $platform => $color ) {
			printf(
				'<input type="hidden" name="telkari_settings[platform_colors][%s]" value="%s">',
				esc_attr( $platform ),
				esc_attr( $color )
			);
		}
	}

	if ( ! in_array( 'cta_buttons', $exclude_keys, true ) && ! empty( $settings['cta_buttons'] ) ) {
		telkari_render_hidden_collection_fields( 'cta_buttons', $settings['cta_buttons'] );
	}

	if ( ! in_array( 'social_accounts', $exclude_keys, true ) && ! empty( $settings['social_accounts'] ) ) {
		telkari_render_hidden_collection_fields( 'social_accounts', $settings['social_accounts'] );
	}
}

/**
 * Render hidden fields for collection-style settings.
 *
 * @param string $setting_key Collection setting key.
 * @param array  $items       Collection items.
 */
function telkari_render_hidden_collection_fields( $setting_key, $items ) {
	if ( ! is_array( $items ) ) {
		return;
	}

	foreach ( $items as $index => $item ) {
		if ( ! is_array( $item ) ) {
			continue;
		}

		foreach ( $item as $field => $value ) {
			if ( is_bool( $value ) ) {
				$value = $value ? '1' : '';
			}

			printf(
				'<input type="hidden" name="telkari_settings[%s][%d][%s]" value="%s">',
				esc_attr( $setting_key ),
				(int) $index,
				esc_attr( $field ),
				esc_attr( $value )
			);
		}
	}
}
