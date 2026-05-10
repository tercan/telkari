<?php
/**
 * Options management for Telkari plugin.
 *
 * @package Telkari
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Return the current settings schema version.
 *
 * @return int
 */
function telkari_get_settings_schema_version() {
	return 4;
}

/**
 * Return default settings array.
 *
 * @return array
 */
function telkari_get_default_settings() {
	return array(
		'schema_version'       => telkari_get_settings_schema_version(),
		'active_design'        => 'design-1',
		'active_position'      => 'bottom-right',
		'cta_position'         => 'bottom-right',
		'show_social_accounts' => true,
		'show_cta_buttons'     => true,
		'icon_size'            => 40,
		'icon_spacing'         => 8,
		'social_icon_size'     => 40,
		'social_icon_spacing'  => 8,
		'cta_button_size'      => 'default',
		'cta_button_spacing'   => 8,
		'cta_button_width'     => 'content',
		'icon_style'           => 'rounded',
		'link_target'          => '_blank',
		'show_tooltip'         => true,
		'platform_colors'      => array(),
		'cta_buttons'          => array(),
		'social_accounts'      => array(),
	);
}

/**
 * Get current settings merged with defaults.
 *
 * @return array
 */
function telkari_get_settings() {
	$settings = get_option( 'telkari_settings', null );

	if ( ! is_array( $settings ) ) {
		return telkari_get_default_settings();
	}

	$migrated_settings = telkari_migrate_settings( $settings );

	if ( $migrated_settings !== $settings ) {
		update_option( 'telkari_settings', $migrated_settings, false );
	}

	return $migrated_settings;
}

/**
 * Update settings in database.
 *
 * @param array $settings Settings array.
 * @return bool
 */
function telkari_update_settings( $settings ) {
	return update_option( 'telkari_settings', telkari_migrate_settings( $settings ), false );
}

/**
 * Normalize a settings payload to the current schema.
 *
 * @param array $settings Settings payload.
 * @return array
 */
function telkari_normalize_settings( $settings ) {
	$defaults = telkari_get_default_settings();

	if ( ! is_array( $settings ) ) {
		return $defaults;
	}

	$normalized = wp_parse_args( $settings, $defaults );

	if ( ! array_key_exists( 'social_icon_size', $settings ) && array_key_exists( 'icon_size', $settings ) ) {
		$normalized['social_icon_size'] = $settings['icon_size'];
	}

	if ( ! array_key_exists( 'social_icon_spacing', $settings ) && array_key_exists( 'icon_spacing', $settings ) ) {
		$normalized['social_icon_spacing'] = $settings['icon_spacing'];
	}

	$normalized['schema_version']       = telkari_get_settings_schema_version();
	$normalized['active_design']        = telkari_sanitize_design( $normalized['active_design'] );
	$normalized['show_social_accounts'] = ! empty( $normalized['show_social_accounts'] );
	$normalized['show_cta_buttons']     = ! empty( $normalized['show_cta_buttons'] );
	$normalized['social_icon_size']     = min( 96, max( 24, absint( $normalized['social_icon_size'] ) ) );
	$normalized['social_icon_spacing']  = min( 48, max( 0, absint( $normalized['social_icon_spacing'] ) ) );
	$normalized['icon_size']            = $normalized['social_icon_size'];
	$normalized['icon_spacing']         = $normalized['social_icon_spacing'];
	$normalized['cta_button_size']      = in_array( $normalized['cta_button_size'], array( 'compact', 'default', 'large' ), true )
		? $normalized['cta_button_size']
		: $defaults['cta_button_size'];
	$normalized['cta_button_spacing']   = min( 48, max( 0, absint( $normalized['cta_button_spacing'] ) ) );
	$normalized['cta_button_width']     = in_array( $normalized['cta_button_width'], array( 'content', 'fixed', 'full' ), true )
		? $normalized['cta_button_width']
		: $defaults['cta_button_width'];
	$normalized['icon_style']           = in_array( $normalized['icon_style'], array( 'rounded', 'square' ), true )
		? $normalized['icon_style']
		: $defaults['icon_style'];
	$normalized['link_target']          = in_array( $normalized['link_target'], array( '_self', '_blank' ), true )
		? $normalized['link_target']
		: $defaults['link_target'];
	$normalized['show_tooltip']         = ! empty( $normalized['show_tooltip'] );
	$normalized['cta_buttons']          = isset( $normalized['cta_buttons'] ) && is_array( $normalized['cta_buttons'] )
		? telkari_sanitize_cta_buttons( $normalized['cta_buttons'] )
		: array();
	$normalized['social_accounts']      = isset( $normalized['social_accounts'] ) && is_array( $normalized['social_accounts'] )
		? telkari_sanitize_collection_items( $normalized['social_accounts'], 'telkari_sanitize_single_account' )
		: array();

	$position_pair = telkari_normalize_position_pair(
		$normalized['active_design'],
		$normalized['active_position'],
		$normalized['cta_position'],
		telkari_get_enabled_group_state( $normalized )
	);

	$normalized['active_position'] = $position_pair['social_position'];
	$normalized['cta_position']    = $position_pair['cta_position'];

	return $normalized;
}

/**
 * Migrate settings payloads from older schemas to the current schema.
 *
 * @param array $settings Settings payload.
 * @return array
 */
function telkari_migrate_settings( $settings ) {
	if ( ! is_array( $settings ) ) {
		return telkari_get_default_settings();
	}

	$schema_version = isset( $settings['schema_version'] ) ? absint( $settings['schema_version'] ) : 0;

	if ( $schema_version < 1 ) {
		$settings       = telkari_migrate_settings_to_schema_1( $settings );
		$schema_version = 1;
	}

	if ( $schema_version < 2 ) {
		$settings       = telkari_migrate_settings_to_schema_2( $settings );
		$schema_version = 2;
	}

	if ( $schema_version < 3 ) {
		$settings       = telkari_migrate_settings_to_schema_3( $settings );
		$schema_version = 3;
	}

	if ( $schema_version < 4 ) {
		$settings       = telkari_migrate_settings_to_schema_4( $settings );
		$schema_version = 4;
	}

	return telkari_normalize_settings( $settings );
}

/**
 * Migrate legacy settings payloads to schema version 1.
 *
 * Schema 1 introduces an explicit internal schema version and normalizes the
 * social account collection through shared helper functions.
 *
 * @param array $settings Legacy settings payload.
 * @return array
 */
function telkari_migrate_settings_to_schema_1( $settings ) {
	if ( ! is_array( $settings ) ) {
		return telkari_get_default_settings();
	}

	$settings['schema_version'] = 1;

	return $settings;
}

/**
 * Migrate schema version 1 settings to schema version 2.
 *
 * Schema 2 introduces the CTA button collection used by the v0.2.0 CTA
 * feature set. Existing installs receive an empty collection so current
 * social link behaviour remains unchanged until CTA buttons are configured.
 *
 * @param array $settings Schema version 1 settings payload.
 * @return array
 */
function telkari_migrate_settings_to_schema_2( $settings ) {
	if ( ! is_array( $settings ) ) {
		return telkari_get_default_settings();
	}

	if ( ! isset( $settings['cta_buttons'] ) || ! is_array( $settings['cta_buttons'] ) ) {
		$settings['cta_buttons'] = array();
	}

	$settings['schema_version'] = 2;

	return $settings;
}

/**
 * Migrate schema version 2 settings to schema version 3.
 *
 * Schema 3 adds an independent CTA position setting so CTA buttons can be
 * placed separately from the social account group when needed.
 *
 * @param array $settings Schema version 2 settings payload.
 * @return array
 */
function telkari_migrate_settings_to_schema_3( $settings ) {
	if ( ! is_array( $settings ) ) {
		return telkari_get_default_settings();
	}

	if ( empty( $settings['cta_position'] ) ) {
		$settings['cta_position'] = isset( $settings['active_position'] ) ? $settings['active_position'] : 'bottom-right';
	}

	$settings['schema_version'] = 3;

	return $settings;
}

/**
 * Migrate schema version 3 settings to schema version 4.
 *
 * Schema 4 introduces explicit social/CTA group visibility flags and separate
 * social icon / CTA button appearance defaults without removing the legacy
 * icon_size and icon_spacing fields.
 *
 * @param array $settings Schema version 3 settings payload.
 * @return array
 */
function telkari_migrate_settings_to_schema_4( $settings ) {
	if ( ! is_array( $settings ) ) {
		return telkari_get_default_settings();
	}

	if ( ! array_key_exists( 'show_social_accounts', $settings ) ) {
		$settings['show_social_accounts'] = true;
	}

	if ( ! array_key_exists( 'show_cta_buttons', $settings ) ) {
		$settings['show_cta_buttons'] = true;
	}

	if ( empty( $settings['social_icon_size'] ) ) {
		$settings['social_icon_size'] = isset( $settings['icon_size'] ) ? $settings['icon_size'] : 40;
	}

	if ( ! array_key_exists( 'social_icon_spacing', $settings ) ) {
		$settings['social_icon_spacing'] = isset( $settings['icon_spacing'] ) ? $settings['icon_spacing'] : 8;
	}

	if ( empty( $settings['cta_button_size'] ) ) {
		$settings['cta_button_size'] = 'default';
	}

	if ( ! array_key_exists( 'cta_button_spacing', $settings ) ) {
		$settings['cta_button_spacing'] = 8;
	}

	if ( empty( $settings['cta_button_width'] ) ) {
		$settings['cta_button_width'] = 'content';
	}

	$settings['schema_version'] = 4;

	return $settings;
}

/**
 * Get allowed positions for a given design.
 *
 * @param string $design Design identifier.
 * @return array
 */
function telkari_get_allowed_positions( $design ) {
	$positions = array(
		'design-2' => array( 'bottom-left', 'bottom-right', 'bottom-center' ),
		'design-3' => array( 'bottom-left', 'bottom-right' ),
		'design-1' => array( 'bottom-left', 'bottom-right' ),
	);

	return isset( $positions[ $design ] ) ? $positions[ $design ] : array( 'bottom-right' );
}

/**
 * Return enabled state for the social and CTA groups.
 *
 * @param array $settings Settings payload.
 * @return array
 */
function telkari_get_enabled_group_state( $settings ) {
	return array(
		'social' => ! empty( $settings['show_social_accounts'] ),
		'cta'    => ! empty( $settings['show_cta_buttons'] ),
	);
}

/**
 * Sanitize group visibility flags from a settings payload.
 *
 * Missing keys default to the existing enabled state to preserve behaviour for
 * legacy forms and tabs that do not manage group visibility yet.
 *
 * @param array $input Raw settings input.
 * @return array
 */
function telkari_sanitize_group_visibility_flags( $input ) {
	$defaults = telkari_get_default_settings();

	if ( ! is_array( $input ) ) {
		$input = array();
	}

	return array(
		'show_social_accounts' => array_key_exists( 'show_social_accounts', $input )
			? ! empty( $input['show_social_accounts'] )
			: $defaults['show_social_accounts'],
		'show_cta_buttons'     => array_key_exists( 'show_cta_buttons', $input )
			? ! empty( $input['show_cta_buttons'] )
			: $defaults['show_cta_buttons'],
	);
}

/**
 * Get allowed positions for a group in the given design.
 *
 * @param string $design         Design identifier.
 * @param string $group          Group identifier: social or cta.
 * @param array  $enabled_groups Enabled group state.
 * @return array
 */
function telkari_get_allowed_group_positions( $design, $group, $enabled_groups = array() ) {
	unset( $group, $enabled_groups );

	return telkari_get_allowed_positions( $design );
}

/**
 * Get valid social/CTA position pairs for a design.
 *
 * @param string $design Design identifier.
 * @return array
 */
function telkari_get_valid_position_pairs( $design ) {
	switch ( $design ) {
		case 'design-2':
			return array(
				array( 'social' => 'bottom-left', 'cta' => 'bottom-right' ),
				array( 'social' => 'bottom-right', 'cta' => 'bottom-left' ),
				array( 'social' => 'bottom-center', 'cta' => 'bottom-left' ),
				array( 'social' => 'bottom-center', 'cta' => 'bottom-right' ),
				array( 'social' => 'bottom-left', 'cta' => 'bottom-center' ),
				array( 'social' => 'bottom-right', 'cta' => 'bottom-center' ),
			);

		case 'design-3':
			return array(
				array( 'social' => 'bottom-left', 'cta' => 'bottom-right' ),
				array( 'social' => 'bottom-right', 'cta' => 'bottom-left' ),
			);

		case 'design-1':
		default:
			return array();
	}
}

/**
 * Normalize social and CTA positions as a relationship-aware pair.
 *
 * @param string $design          Design identifier.
 * @param string $social_position Social group position.
 * @param string $cta_position    CTA group position.
 * @param array  $enabled_groups  Enabled group state.
 * @return array
 */
function telkari_normalize_position_pair( $design, $social_position, $cta_position, $enabled_groups = array() ) {
	$design          = telkari_sanitize_design( $design );
	$enabled_groups  = wp_parse_args(
		is_array( $enabled_groups ) ? $enabled_groups : array(),
		array(
			'social' => true,
			'cta'    => true,
		)
	);
	$social_allowed  = telkari_get_allowed_group_positions( $design, 'social', $enabled_groups );
	$cta_allowed     = telkari_get_allowed_group_positions( $design, 'cta', $enabled_groups );
	$social_position = in_array( $social_position, $social_allowed, true ) ? $social_position : reset( $social_allowed );
	$cta_position    = in_array( $cta_position, $cta_allowed, true ) ? $cta_position : reset( $cta_allowed );

	if ( ! $enabled_groups['social'] || ! $enabled_groups['cta'] ) {
		return array(
			'social_position' => $social_position,
			'cta_position'    => $cta_position,
		);
	}

	if ( 'design-3' === $design ) {
		$cta_position = telkari_get_opposite_edge_position( $social_position );
	}

	if ( 'design-2' === $design && $social_position === $cta_position ) {
		$cta_position = telkari_get_fallback_cta_position_for_ribbon( $social_position );
	}

	return array(
		'social_position' => $social_position,
		'cta_position'    => $cta_position,
	);
}

/**
 * Return the opposite edge position.
 *
 * @param string $position Current edge position.
 * @return string
 */
function telkari_get_opposite_edge_position( $position ) {
	return 'bottom-left' === $position ? 'bottom-right' : 'bottom-left';
}

/**
 * Return a valid CTA fallback when Ribbon positions collide.
 *
 * @param string $social_position Social group position.
 * @return string
 */
function telkari_get_fallback_cta_position_for_ribbon( $social_position ) {
	if ( 'bottom-right' === $social_position ) {
		return 'bottom-left';
	}

	return 'bottom-right';
}

/**
 * Get all position labels.
 *
 * @return array
 */
function telkari_get_position_labels() {
	return array(
		'bottom-left'   => __( 'Bottom Left', 'telkari' ),
		'bottom-right'  => __( 'Bottom Right', 'telkari' ),
		'bottom-center' => __( 'Bottom Center', 'telkari' ),
	);
}

/**
 * Get supported social media platforms.
 *
 * @return array
 */
function telkari_get_supported_platforms() {
	return array(
		'instagram' => array(
			'label' => 'Instagram',
			'icon'  => 'instagram.svg',
		),
		'youtube'   => array(
			'label' => 'YouTube',
			'icon'  => 'youtube.svg',
		),
		'facebook'  => array(
			'label' => 'Facebook',
			'icon'  => 'facebook.svg',
		),
		'x'         => array(
			'label' => 'X (Twitter)',
			'icon'  => 'x-twitter.svg',
		),
		'linkedin'  => array(
			'label' => 'LinkedIn',
			'icon'  => 'linkedin.svg',
		),
		'tiktok'    => array(
			'label' => 'TikTok',
			'icon'  => 'tiktok.svg',
		),
		'github'    => array(
			'label' => 'GitHub',
			'icon'  => 'github.svg',
		),
		'pinterest' => array(
			'label' => 'Pinterest',
			'icon'  => 'pinterest.svg',
		),
		'telegram'  => array(
			'label' => 'Telegram',
			'icon'  => 'telegram.svg',
		),
		'whatsapp'  => array(
			'label' => 'WhatsApp',
			'icon'  => 'whatsapp.svg',
		),
		'discord'   => array(
			'label' => 'Discord',
			'icon'  => 'discord.svg',
		),
		'twitch'    => array(
			'label' => 'Twitch',
			'icon'  => 'twitch.svg',
		),
		'spotify'   => array(
			'label' => 'Spotify',
			'icon'  => 'spotify.svg',
		),
	);
}

/**
 * Return default brand colors for each platform.
 *
 * @return array
 */
function telkari_get_platform_brand_colors() {
	return array(
		'instagram'      => '#E4405F',
		'youtube'        => '#FF0000',
		'facebook'       => '#1877F2',
		'x'              => '#000000',
		'linkedin'       => '#0A66C2',
		'tiktok'         => '#000000',
		'github'         => '#181717',
		'pinterest'      => '#BD081C',
		'telegram'       => '#26A5E4',
		'whatsapp'       => '#25D366',
		'discord'        => '#5865F2',
		'twitch'         => '#9146FF',
		'spotify'        => '#1DB954',
		'trigger_button' => '#1e293b',
		'wrapper_bg'     => '#1e293b',
	);
}
