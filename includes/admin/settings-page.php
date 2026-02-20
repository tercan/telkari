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
	add_menu_page(
		__( 'Telkari', 'telkari' ),
		__( 'Telkari', 'telkari' ),
		'manage_options',
		'telkari-settings',
		'telkari_render_settings_page',
		'dashicons-share',
		66
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

	wp_localize_script( 'telkari-admin', 'telkariAdmin', array(
		'positions'      => array(
			'design-1' => telkari_get_allowed_positions( 'design-1' ),
			'design-2' => telkari_get_allowed_positions( 'design-2' ),
			'design-3' => telkari_get_allowed_positions( 'design-3' ),
		),
		'positionLabels' => telkari_get_position_labels(),
		'platforms'      => telkari_get_supported_platforms(),
		'i18n'           => array(
			'selectPlatform' => __( 'Select Platform', 'telkari' ),
			'confirmDelete'  => __( 'Are you sure you want to delete this account?', 'telkari' ),
			'fillFields'     => __( 'Please select a platform and enter a valid URL.', 'telkari' ),
			'enabled'        => __( 'Enabled', 'telkari' ),
			'delete'         => __( 'Delete', 'telkari' ),
		),
	) );
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
		'appearance' => __( 'Appearance', 'telkari' ),
	);

	?>
	<div class="wrap">
		<div class="telkari-admin-header" style="display: flex; justify-content: space-between;">
			<div class="telkari-name">
				<i class="telkari-icon"></i>
				<?php echo esc_html( get_admin_page_title() ); ?>
				<a href="https://tercan.net/telkari" target="_blank" class="telkari-admin-header-action" title="View Changelog">
					(v<?php echo esc_html( TELKARI_VERSION ); ?>)
				</a>
			</div>
			<div class="telkari-admin-header-actions">
				<a href="https://tercan.net/telkari" target="_blank" class="telkari-admin-header-action" title="Visit Documentation">
					<?php echo esc_html__( 'Documentation', 'telkari' ); ?>
				</a>
			</div>
		</div>

		<nav class="nav-tab-wrapper">
			<?php foreach ( $tabs as $tab_key => $tab_label ) : ?>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=telkari-settings&tab=' . $tab_key ) ); ?>"
				   class="nav-tab <?php echo $active_tab === $tab_key ? 'nav-tab-active' : ''; ?>">
					<?php echo esc_html( $tab_label ); ?>
				</a>
			<?php endforeach; ?>
		</nav>

		<form method="post" action="options.php" id="telkari-settings-form">
			<?php settings_fields( 'telkari_settings_group' ); ?>

			<?php
			switch ( $active_tab ) {
				case 'design':
					telkari_render_design_tab( $settings );
					break;
				case 'accounts':
					telkari_render_accounts_tab( $settings );
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
	telkari_design_selector_render( $settings );
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
 * Render the appearance settings tab.
 *
 * @param array $settings Current settings.
 */
function telkari_render_appearance_tab( $settings ) {
	?>
	<div class="telkari-settings-panel">
		<div class="telkari-setting-card">
			<label class="telkari-setting-label" for="telkari-icon-size"><?php esc_html_e( 'Icon Size (px)', 'telkari' ); ?></label>
			<div class="telkari-setting-control">
				<div class="telkari-range-field">
					<input type="range"
						   id="telkari-icon-size"
						   name="telkari_settings[icon_size]"
						   value="<?php echo esc_attr( $settings['icon_size'] ); ?>"
						   min="24" max="96" step="4"
						   class="telkari-range-input">
					<output class="telkari-range-value" for="telkari-icon-size"><?php echo esc_html( $settings['icon_size'] ); ?></output>
				</div>
			</div>
			<span class="telkari-setting-description"><?php esc_html_e( 'Icon size in pixels (24-96).', 'telkari' ); ?></span>
		</div>
		<div class="telkari-setting-card">
			<label class="telkari-setting-label" for="telkari-icon-spacing"><?php esc_html_e( 'Icon Spacing (px)', 'telkari' ); ?></label>
			<div class="telkari-setting-control">
				<div class="telkari-range-field">
					<input type="range"
						   id="telkari-icon-spacing"
						   name="telkari_settings[icon_spacing]"
						   value="<?php echo esc_attr( $settings['icon_spacing'] ); ?>"
						   min="0" max="48" step="4"
						   class="telkari-range-input">
					<output class="telkari-range-value" for="telkari-icon-spacing"><?php echo esc_html( $settings['icon_spacing'] ); ?></output>
				</div>
			</div>
			<span class="telkari-setting-description"><?php esc_html_e( 'Space between icons in pixels (0-48).', 'telkari' ); ?></span>
		</div>
		<div class="telkari-setting-card">
			<span class="telkari-setting-label"><?php esc_html_e( 'Icon Style', 'telkari' ); ?></span>
			<div class="telkari-setting-control">
				<div class="telkari-btn-group">
					<label class="telkari-btn-option <?php echo 'rounded' === $settings['icon_style'] ? 'telkari-btn-option--active' : ''; ?>">
						<input type="radio" name="telkari_settings[icon_style]" value="rounded" <?php checked( $settings['icon_style'], 'rounded' ); ?>>
						<?php esc_html_e( 'Rounded', 'telkari' ); ?>
					</label>
					<label class="telkari-btn-option <?php echo 'square' === $settings['icon_style'] ? 'telkari-btn-option--active' : ''; ?>">
						<input type="radio" name="telkari_settings[icon_style]" value="square" <?php checked( $settings['icon_style'], 'square' ); ?>>
						<?php esc_html_e( 'Square', 'telkari' ); ?>
					</label>
				</div>
			</div>
		</div>
		<div class="telkari-setting-card">
			<span class="telkari-setting-label"><?php esc_html_e( 'Link Target', 'telkari' ); ?></span>
			<div class="telkari-setting-control">
				<div class="telkari-btn-group">
					<label class="telkari-btn-option <?php echo '_self' === $settings['link_target'] ? 'telkari-btn-option--active' : ''; ?>">
						<input type="radio" name="telkari_settings[link_target]" value="_self" <?php checked( $settings['link_target'], '_self' ); ?>>
						<?php esc_html_e( 'Same Tab', 'telkari' ); ?>
					</label>
					<label class="telkari-btn-option <?php echo '_blank' === $settings['link_target'] ? 'telkari-btn-option--active' : ''; ?>">
						<input type="radio" name="telkari_settings[link_target]" value="_blank" <?php checked( $settings['link_target'], '_blank' ); ?>>
						<?php esc_html_e( 'New Tab', 'telkari' ); ?>
					</label>
				</div>
			</div>
		</div>
		<div class="telkari-setting-card">
			<span class="telkari-setting-label"><?php esc_html_e( 'Tooltips', 'telkari' ); ?></span>
			<div class="telkari-setting-control">
				<div class="telkari-btn-group">
					<label class="telkari-btn-option <?php echo $settings['show_tooltip'] ? 'telkari-btn-option--active' : ''; ?>">
						<input type="radio" name="telkari_settings[show_tooltip]" value="1" <?php checked( $settings['show_tooltip'], true ); ?>>
						<?php esc_html_e( 'On', 'telkari' ); ?>
					</label>
					<label class="telkari-btn-option <?php echo ! $settings['show_tooltip'] ? 'telkari-btn-option--active' : ''; ?>">
						<input type="radio" name="telkari_settings[show_tooltip]" value="0" <?php checked( $settings['show_tooltip'], false ); ?>>
						<?php esc_html_e( 'Off', 'telkari' ); ?>
					</label>
				</div>
			</div>
		</div>
	</div>

	<h3 class="telkari-settings-section-title"><?php esc_html_e( 'Icon Colors', 'telkari' ); ?></h3>
	<p class="telkari-setting-description" style="margin-bottom:0.75rem;">
		<?php esc_html_e( 'Override the default brand color for each platform. Leave empty to use the official brand color.', 'telkari' ); ?>
	</p>
	<?php
	$brand_colors    = telkari_get_platform_brand_colors();
	$platforms       = telkari_get_supported_platforms();
	$platform_colors = isset( $settings['platform_colors'] ) ? $settings['platform_colors'] : array();
	$trigger_default = $brand_colors['trigger_button'];
	$trigger_current = isset( $platform_colors['trigger_button'] ) ? $platform_colors['trigger_button'] : $trigger_default;
	$wrapper_default = $brand_colors['wrapper_bg'];
	$wrapper_current = isset( $platform_colors['wrapper_bg'] ) ? $platform_colors['wrapper_bg'] : $wrapper_default;
	?>
	<div class="telkari-color-highlight">
		<div class="telkari-color-item">
			<label><?php esc_html_e( 'Bar Background (Ribbon / Pillar)', 'telkari' ); ?></label>
			<?php $is_transparent = ( 'transparent' === $wrapper_current || empty( $wrapper_current ) ); ?>
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
		<?php foreach ( $platforms as $key => $platform ) :
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
	<p style="margin-top:0.75rem;">
		<button type="button" class="button" id="telkari-reset-colors">
			<?php esc_html_e( 'Reset All Colors', 'telkari' ); ?>
		</button>
	</p>

	<?php
	// Preserve other settings as hidden fields.
	telkari_render_hidden_settings( $settings, array( 'icon_size', 'icon_spacing', 'icon_style', 'link_target', 'show_tooltip', 'platform_colors' ) );
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
	$simple_keys = array( 'active_design', 'active_position', 'icon_size', 'icon_spacing', 'icon_style', 'link_target', 'show_tooltip' );

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

	// Social accounts need special handling.
	if ( ! in_array( 'social_accounts', $exclude_keys, true ) && ! empty( $settings['social_accounts'] ) ) {
		foreach ( $settings['social_accounts'] as $i => $account ) {
			foreach ( $account as $field => $value ) {
				if ( is_bool( $value ) ) {
					$value = $value ? '1' : '';
				}
				printf(
					'<input type="hidden" name="telkari_settings[social_accounts][%d][%s]" value="%s">',
					(int) $i,
					esc_attr( $field ),
					esc_attr( $value )
				);
			}
		}
	}
}
