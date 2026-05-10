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
	return 3;
}

/**
 * Return default settings array.
 *
 * @return array
 */
function telkari_get_default_settings() {
	return array(
		'schema_version'  => telkari_get_settings_schema_version(),
		'active_design'   => 'design-1',
		'active_position' => 'bottom-right',
		'cta_position'    => 'bottom-right',
		'icon_size'       => 40,
		'icon_spacing'    => 8,
		'icon_style'      => 'rounded',
		'link_target'     => '_blank',
		'show_tooltip'    => true,
		'platform_colors' => array(),
		'cta_buttons'     => array(),
		'social_accounts' => array(),
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

	$normalized['schema_version']  = telkari_get_settings_schema_version();
	$normalized['cta_buttons']     = isset( $normalized['cta_buttons'] ) && is_array( $normalized['cta_buttons'] )
		? telkari_sanitize_cta_buttons( $normalized['cta_buttons'] )
		: array();
	$normalized['social_accounts'] = isset( $normalized['social_accounts'] ) && is_array( $normalized['social_accounts'] )
		? telkari_sanitize_collection_items( $normalized['social_accounts'], 'telkari_sanitize_single_account' )
		: array();

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
