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
 * Return default settings array.
 *
 * @return array
 */
function telkari_get_default_settings() {
	return array(
		'active_design'   => 'design-1',
		'active_position' => 'bottom-right',
		'icon_size'       => 40,
		'icon_spacing'    => 8,
		'icon_style'      => 'rounded',
		'link_target'     => '_blank',
		'show_tooltip'    => true,
		'platform_colors' => array(),
		'social_accounts' => array(),
	);
}

/**
 * Get current settings merged with defaults.
 *
 * @return array
 */
function telkari_get_settings() {
	$settings = get_option( 'telkari_settings', array() );
	return wp_parse_args( $settings, telkari_get_default_settings() );
}

/**
 * Update settings in database.
 *
 * @param array $settings Settings array.
 * @return bool
 */
function telkari_update_settings( $settings ) {
	return update_option( 'telkari_settings', $settings, false );
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
		'instagram' => '#E4405F',
		'youtube'   => '#FF0000',
		'facebook'  => '#1877F2',
		'x'         => '#000000',
		'linkedin'  => '#0A66C2',
		'tiktok'    => '#000000',
		'github'    => '#181717',
		'pinterest' => '#BD081C',
		'telegram'  => '#26A5E4',
		'whatsapp'  => '#25D366',
		'discord'   => '#5865F2',
		'twitch'    => '#9146FF',
		'spotify'        => '#1DB954',
		'trigger_button' => '#1e293b',
		'wrapper_bg'     => '#1e293b',
	);
}
