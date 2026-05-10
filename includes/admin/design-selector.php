<?php
/**
 * Design template selector for Telkari admin.
 *
 * @package Telkari
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Render the design selector cards.
 *
 * @param array $settings Current settings.
 */
function telkari_design_selector_render( $settings ) {
	$designs = telkari_get_design_definitions();

	$enabled_groups  = telkari_get_enabled_group_state( $settings );
	$position_pair   = telkari_normalize_position_pair(
		$settings['active_design'],
		$settings['active_position'],
		isset( $settings['cta_position'] ) ? $settings['cta_position'] : $settings['active_position'],
		$enabled_groups
	);
	$social_position = $position_pair['social_position'];
	$cta_position    = $position_pair['cta_position'];
	$positions       = array(
		'social' => $social_position,
		'cta'    => $cta_position,
	);

	?>
	<h3 class="telkari-settings-section-title" id="telkari-display-groups"><?php esc_html_e( 'Display Groups', 'telkari' ); ?></h3>
	<div class="telkari-settings-panel telkari-settings-panel--appearance telkari-design-groups-panel">
		<div class="telkari-settings-row telkari-settings-row--two-columns">
			<?php
			telkari_render_display_group_card(
				'social',
				'show_social_accounts',
				__( 'Show Social Icons', 'telkari' ),
				__( 'Display saved social accounts on the frontend.', 'telkari' ),
				$enabled_groups['social'],
				'telkari_settings[active_position]',
				$social_position,
				$settings['active_design'],
				$enabled_groups,
				$positions
			);
			telkari_render_display_group_card(
				'cta',
				'show_cta_buttons',
				__( 'Show CTA Buttons', 'telkari' ),
				__( 'Display saved CTA buttons on the frontend.', 'telkari' ),
				$enabled_groups['cta'],
				'telkari_settings[cta_position]',
				$cta_position,
				$settings['active_design'],
				$enabled_groups,
				$positions
			);
			?>
		</div>
		<p
			class="telkari-placement-status"
			id="telkari-placement-status"
			role="status"
			aria-live="polite"
			aria-atomic="true"
			<?php echo ( $enabled_groups['social'] || $enabled_groups['cta'] ) ? 'hidden' : ''; ?>>
			<?php
			if ( ! $enabled_groups['social'] && ! $enabled_groups['cta'] ) {
				esc_html_e( 'Enable Social Icons or CTA Buttons to configure placement.', 'telkari' );
			}
			?>
		</p>
	</div>

	<h3 class="telkari-settings-section-title"><?php esc_html_e( 'Design Style', 'telkari' ); ?></h3>
	<div class="telkari-design-selector">
		<?php foreach ( $designs as $design_id => $design ) : ?>
			<label class="telkari-design-option <?php echo esc_attr( $settings['active_design'] === $design_id ? 'telkari-design-option--active' : '' ); ?>">
				<input type="radio"
						name="telkari_settings[active_design]"
						value="<?php echo esc_attr( $design_id ); ?>"
						<?php checked( $settings['active_design'], $design_id ); ?>
						class="telkari-design-radio">

				<div class="telkari-design-preview">
					<?php echo wp_kses( telkari_get_design_preview_svg( $design_id ), telkari_get_svg_kses_allowed() ); ?>
				</div>

				<div class="telkari-design-info">
					<h3 class="telkari-design-title"><?php echo esc_html( $design['label'] ); ?></h3>
					<p class="telkari-design-desc"><?php echo esc_html( $design['description'] ); ?></p>
				</div>
			</label>
		<?php endforeach; ?>
	</div>

	<?php
	// Preserve other settings as hidden fields.
	telkari_render_hidden_settings( $settings, array( 'active_design', 'active_position', 'cta_position', 'show_social_accounts', 'show_cta_buttons' ) );
}

/**
 * Render a display group toggle card.
 *
 * @param string $group          Group identifier.
 * @param string $setting_key    Setting key.
 * @param string $label          Visible label.
 * @param string $description    Description text.
 * @param bool   $is_enabled     Whether the group is enabled.
 * @param string $setting_name   Placement form field name.
 * @param string $selected       Selected placement.
 * @param string $design         Active design.
 * @param array  $enabled_groups Enabled group state.
 * @param array  $selected_pair  Selected positions by group.
 */
function telkari_render_display_group_card(
	$group,
	$setting_key,
	$label,
	$description,
	$is_enabled,
	$setting_name,
	$selected,
	$design,
	$enabled_groups,
	$selected_pair
) {
	$input_id = 'telkari-' . str_replace( '_', '-', $setting_key );
	$label_id = $input_id . '-label';
	?>
	<div class="telkari-setting-card telkari-display-group-card">
		<label
			id="<?php echo esc_attr( $label_id ); ?>"
			class="telkari-setting-label"
			for="<?php echo esc_attr( $input_id ); ?>">
			<?php echo esc_html( $label ); ?>
		</label>
		<div class="telkari-setting-control">
			<input type="hidden"
				name="telkari_settings[<?php echo esc_attr( $setting_key ); ?>]"
				value="0">
			<label
				class="telkari-toggle telkari-cta-toggle telkari-display-group-toggle"
				for="<?php echo esc_attr( $input_id ); ?>">
				<input type="checkbox"
					id="<?php echo esc_attr( $input_id ); ?>"
					class="telkari-display-group-input"
					name="telkari_settings[<?php echo esc_attr( $setting_key ); ?>]"
					value="1"
					aria-labelledby="<?php echo esc_attr( $label_id ); ?>"
					<?php checked( $is_enabled ); ?>>
				<span class="telkari-cta-toggle-track" aria-hidden="true">
					<span class="telkari-cta-toggle-thumb"></span>
				</span>
			</label>
		</div>
		<span class="telkari-setting-description"><?php echo esc_html( $description ); ?></span>
		<div
			class="telkari-display-group-placement telkari-placement-card"
			data-placement-card="<?php echo esc_attr( $group ); ?>"
			<?php echo $is_enabled ? '' : 'hidden'; ?>>
			<span class="telkari-display-group-placement-label"><?php esc_html_e( 'Placement', 'telkari' ); ?></span>
			<div class="telkari-display-group-placement-control">
				<?php telkari_render_position_button_group( $group, $setting_name, $selected, $design, $enabled_groups, $selected_pair ); ?>
			</div>
		</div>
	</div>
	<?php
}

/**
 * Render a relationship-aware placement button group.
 *
 * @param string $group          Group identifier.
 * @param string $setting_name   Form field name.
 * @param string $selected       Selected position.
 * @param string $design         Active design.
 * @param array  $enabled_groups Enabled group state.
 * @param array  $selected_pair  Selected positions by group.
 */
function telkari_render_position_button_group( $group, $setting_name, $selected, $design, $enabled_groups, $selected_pair ) {
	$position_labels = telkari_get_position_labels();
	$positions       = telkari_get_allowed_group_positions( $design, $group, $enabled_groups );
	$other_group     = 'social' === $group ? 'cta' : 'social';
	$other_position  = isset( $selected_pair[ $other_group ] ) ? $selected_pair[ $other_group ] : '';
	?>
	<div class="telkari-btn-group telkari-position-group"
		id="<?php echo esc_attr( 'telkari-' . $group . '-position-group' ); ?>"
		data-group="<?php echo esc_attr( $group ); ?>"
		data-setting-name="<?php echo esc_attr( $setting_name ); ?>">
		<?php foreach ( $positions as $position ) : ?>
			<?php
			$is_disabled = telkari_is_position_option_disabled( $design, $group, $position, $other_group, $other_position, $enabled_groups );
			$is_active   = $selected === $position;
			?>
			<label class="telkari-btn-option <?php echo esc_attr( $is_active ? 'telkari-btn-option--active' : '' ); ?> <?php echo esc_attr( $is_disabled ? 'telkari-btn-option--disabled' : '' ); ?>"
					<?php echo $is_disabled ? 'aria-disabled="true"' : ''; ?>>
				<input type="radio"
						name="<?php echo esc_attr( $setting_name ); ?>"
						value="<?php echo esc_attr( $position ); ?>"
						<?php checked( $is_active ); ?>
						<?php disabled( $is_disabled ); ?>>
				<?php echo esc_html( $position_labels[ $position ] ?? $position ); ?>
			</label>
		<?php endforeach; ?>
	</div>
	<?php
}

/**
 * Determine whether a placement option should be disabled in the current pair.
 *
 * @param string $design         Design identifier.
 * @param string $group          Group identifier.
 * @param string $position       Position option.
 * @param string $other_group    Other group identifier.
 * @param string $other_position Other selected position.
 * @param array  $enabled_groups Enabled group state.
 * @return bool
 */
function telkari_is_position_option_disabled( $design, $group, $position, $other_group, $other_position, $enabled_groups ) {
	unset( $other_group );

	if ( empty( $enabled_groups['social'] ) || empty( $enabled_groups['cta'] ) || 'design-1' === $design ) {
		return false;
	}

	if ( $position !== $other_position ) {
		return false;
	}

	return in_array( $group, array( 'social', 'cta' ), true );
}

/**
 * Get design definitions with labels and descriptions.
 *
 * @return array
 */
function telkari_get_design_definitions() {
	$position_labels = telkari_get_position_labels();

	return array(
		'design-1' => array(
			'label'       => __( 'Orbit', 'telkari' ),
			'description' => __( 'Corner trigger with social orbit behavior and CTA buttons near the selected edge.', 'telkari' ),
			'positions'   => array(
				$position_labels['bottom-left'],
				$position_labels['bottom-right'],
			),
		),
		'design-2' => array(
			'label'       => __( 'Ribbon', 'telkari' ),
			'description' => __( 'Bottom ribbon where social icons stay in one row and CTA buttons can use a separate placement.', 'telkari' ),
			'positions'   => array(
				$position_labels['bottom-left'],
				$position_labels['bottom-right'],
				$position_labels['bottom-center'],
			),
		),
		'design-3' => array(
			'label'       => __( 'Pillar', 'telkari' ),
			'description' => __( 'Vertical side strip where social icons and CTA buttons can sit on opposite edges.', 'telkari' ),
			'positions'   => array(
				$position_labels['bottom-left'],
				$position_labels['bottom-right'],
			),
		),
	);
}

/**
 * Return an inline SVG preview illustration for a design.
 *
 * @param string $design_id Design identifier.
 * @return string SVG markup.
 */
function telkari_get_design_preview_svg( $design_id ) {
	switch ( $design_id ) {
		case 'design-2':
			// Ribbon preview: single-row social strip with a separate CTA group.
			return '<svg viewBox="0 0 240 140" xmlns="http://www.w3.org/2000/svg" class="telkari-preview-svg">
				<rect width="240" height="140" rx="2" fill="#f0f0f1"/>
				<rect x="10" y="8" width="220" height="16" rx="1" fill="#dcdcde"/>
				<rect x="10" y="32" width="220" height="80" rx="1" fill="#fff"/>
				<rect x="20" y="118" width="88" height="16" rx="1" fill="#1e293b"/>
				<circle cx="36" cy="126" r="5" fill="#fff"/>
				<circle cx="52" cy="126" r="5" fill="#fff"/>
				<circle cx="68" cy="126" r="5" fill="#fff"/>
				<circle cx="84" cy="126" r="5" fill="#fff"/>
				<rect x="144" y="116" width="76" height="20" rx="1" fill="#003999"/>
				<rect x="154" y="123" width="46" height="3" rx="1" fill="#fff"/>
				<rect x="154" y="129" width="30" height="3" rx="1" fill="#c8d6f0"/>
			</svg>';

		case 'design-3':
			// Pillar preview: social strip and CTA group on opposite edges.
			return '<svg viewBox="0 0 240 140" xmlns="http://www.w3.org/2000/svg" class="telkari-preview-svg">
				<rect width="240" height="140" rx="2" fill="#f0f0f1"/>
				<rect x="10" y="8" width="220" height="16" rx="1" fill="#dcdcde"/>
				<rect x="30" y="32" width="200" height="100" rx="1" fill="#fff"/>
				<rect x="10" y="44" width="16" height="88" rx="1" fill="#1e293b"/>
				<circle cx="18" cy="60" r="5" fill="#fff"/>
				<circle cx="18" cy="76" r="5" fill="#fff"/>
				<circle cx="18" cy="92" r="5" fill="#fff"/>
				<circle cx="18" cy="108" r="5" fill="#fff"/>
				<circle cx="18" cy="124" r="5" fill="#fff"/>
				<rect x="182" y="70" width="48" height="48" rx="1" fill="#003999"/>
				<rect x="192" y="82" width="28" height="3" rx="1" fill="#fff"/>
				<rect x="192" y="92" width="22" height="3" rx="1" fill="#c8d6f0"/>
				<rect x="192" y="102" width="30" height="3" rx="1" fill="#fff"/>
			</svg>';

		case 'design-1':
			// Orbit preview: corner trigger, social orbit and nearby CTA group.
			return '<svg viewBox="0 0 240 140" xmlns="http://www.w3.org/2000/svg" class="telkari-preview-svg">
				<rect width="240" height="140" rx="2" fill="#f0f0f1"/>
				<rect x="10" y="8" width="220" height="16" rx="1" fill="#dcdcde"/>
				<rect x="10" y="32" width="220" height="100" rx="1" fill="#fff"/>
				<path d="M230 132 L230 112 A20 20 0 0 0 210 132 Z" fill="#003999"/>
				<circle cx="196" cy="126" r="6" fill="#003999" opacity="0.7"/>
				<circle cx="200" cy="112" r="6" fill="#003999" opacity="0.7"/>
				<circle cx="210" cy="102" r="6" fill="#003999" opacity="0.7"/>
				<circle cx="224" cy="98" r="6" fill="#003999" opacity="0.7"/>
				<rect x="14" y="110" width="68" height="18" rx="1" fill="#1e293b"/>
				<rect x="28" y="117" width="42" height="3" rx="1" fill="#fff"/>
			</svg>';

		default:
			return '';
	}
}
