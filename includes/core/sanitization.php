<?php
/**
 * Input sanitization and validation for Telkari plugin.
 *
 * @package Telkari
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Sanitize the full settings array.
 *
 * @param array $input Raw input from form.
 * @return array Sanitized settings.
 */
function telkari_sanitize_settings( $input ) {
	$defaults  = telkari_get_default_settings();
	$sanitized = array();

	// Design.
	$sanitized['active_design'] = telkari_sanitize_design(
		isset( $input['active_design'] ) ? $input['active_design'] : $defaults['active_design']
	);

	// Position (must be valid for the selected design).
	$sanitized['active_position'] = telkari_sanitize_position(
		isset( $input['active_position'] ) ? $input['active_position'] : $defaults['active_position'],
		$sanitized['active_design']
	);

	// Icon size (24-96).
	$sanitized['icon_size'] = isset( $input['icon_size'] )
		? min( 96, max( 24, absint( $input['icon_size'] ) ) )
		: $defaults['icon_size'];

	// Icon spacing (0-48).
	$sanitized['icon_spacing'] = isset( $input['icon_spacing'] )
		? min( 48, max( 0, absint( $input['icon_spacing'] ) ) )
		: $defaults['icon_spacing'];

	// Icon style.
	$sanitized['icon_style'] = isset( $input['icon_style'] ) && in_array( $input['icon_style'], array( 'rounded', 'square' ), true )
		? $input['icon_style']
		: $defaults['icon_style'];

	// Link target.
	$sanitized['link_target'] = isset( $input['link_target'] ) && in_array( $input['link_target'], array( '_self', '_blank' ), true )
		? $input['link_target']
		: $defaults['link_target'];

	// Tooltip.
	$sanitized['show_tooltip'] = ! empty( $input['show_tooltip'] );

	// Platform colors.
	$sanitized['platform_colors'] = array();
	if ( isset( $input['platform_colors'] ) && is_array( $input['platform_colors'] ) ) {
		$valid_platforms = array_merge( array_keys( telkari_get_supported_platforms() ), array( 'trigger_button', 'wrapper_bg' ) );
		foreach ( $input['platform_colors'] as $platform => $color ) {
			if ( ! in_array( $platform, $valid_platforms, true ) ) {
				continue;
			}
			if ( 'wrapper_bg' === $platform && 'transparent' === $color ) {
				$sanitized['platform_colors'][ $platform ] = 'transparent';
				continue;
			}
			$sanitized_color = sanitize_hex_color( $color );
			if ( $sanitized_color ) {
				$sanitized['platform_colors'][ $platform ] = $sanitized_color;
			}
		}
	}

	// Social accounts.
	$sanitized['social_accounts'] = isset( $input['social_accounts'] ) && is_array( $input['social_accounts'] )
		? telkari_sanitize_social_accounts( $input['social_accounts'] )
		: array();

	return $sanitized;
}

/**
 * Validate design identifier.
 *
 * @param string $design Design ID.
 * @return string
 */
function telkari_sanitize_design( $design ) {
	$allowed = array( 'design-2', 'design-3', 'design-1' );
	return in_array( $design, $allowed, true ) ? $design : 'design-1';
}

/**
 * Validate position for a given design.
 *
 * @param string $position Position identifier.
 * @param string $design   Design identifier.
 * @return string
 */
function telkari_sanitize_position( $position, $design ) {
	$allowed = telkari_get_allowed_positions( $design );

	if ( in_array( $position, $allowed, true ) ) {
		return $position;
	}

	return ! empty( $allowed ) ? $allowed[0] : 'bottom-right';
}

/**
 * Sanitize social accounts array.
 *
 * @param array $accounts Raw accounts data.
 * @return array
 */
function telkari_sanitize_social_accounts( $accounts ) {
	$sanitized = array();

	foreach ( $accounts as $account ) {
		$clean = telkari_sanitize_single_account( $account );
		if ( $clean ) {
			$sanitized[] = $clean;
		}
	}

	return $sanitized;
}

/**
 * Sanitize a single social account.
 *
 * @param array $account Raw account data.
 * @return array|null Sanitized account or null if invalid.
 */
function telkari_sanitize_single_account( $account ) {
	if ( ! is_array( $account ) ) {
		return null;
	}

	$platforms = array_keys( telkari_get_supported_platforms() );

	$platform = isset( $account['platform'] ) ? sanitize_text_field( $account['platform'] ) : '';
	$url      = isset( $account['url'] ) ? esc_url_raw( $account['url'] ) : '';

	if ( empty( $platform ) || ! in_array( $platform, $platforms, true ) || empty( $url ) ) {
		return null;
	}

	return array(
		'id'       => isset( $account['id'] ) ? sanitize_text_field( $account['id'] ) : wp_generate_uuid4(),
		'platform' => $platform,
		'url'      => $url,
		'enabled'  => ! empty( $account['enabled'] ),
		'order'    => isset( $account['order'] ) ? absint( $account['order'] ) : 0,
	);
}
