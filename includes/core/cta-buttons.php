<?php
/**
 * CTA button data model and sanitization helpers for Telkari.
 *
 * @package Telkari
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Return supported CTA button types.
 *
 * @return array
 */
function telkari_get_supported_cta_types() {
	return array(
		'whatsapp' => array(
			'label'            => __( 'WhatsApp', 'telkari' ),
			'description'      => __( 'Direct message with pre-filled text.', 'telkari' ),
			/* translators: %s: example WhatsApp number. */
			'example'          => sprintf( __( 'Example: %s', 'telkari' ), '905551112233' ),
			'supports_message' => true,
			'default_color'    => '#25D366',
		),
		'phone'    => array(
			'label'            => __( 'Phone', 'telkari' ),
			'description'      => __( 'Single tap to call.', 'telkari' ),
			/* translators: %s: example phone number. */
			'example'          => sprintf( __( 'Example: %s', 'telkari' ), '+905551112233' ),
			'supports_message' => false,
			'default_color'    => '#0F766E',
		),
		'email'    => array(
			'label'            => __( 'Email', 'telkari' ),
			'description'      => __( 'Opens email client.', 'telkari' ),
			/* translators: %s: example email address. */
			'example'          => sprintf( __( 'Example: %s', 'telkari' ), 'info@example.com' ),
			'supports_message' => false,
			'default_color'    => '#2563EB',
		),
		'url'      => array(
			'label'            => __( 'Custom URL', 'telkari' ),
			'description'      => __( 'Redirect to any webpage.', 'telkari' ),
			/* translators: %s: example destination URL. */
			'example'          => sprintf( __( 'Example: %s', 'telkari' ), 'https://example.com/contact' ),
			'supports_message' => false,
			'default_color'    => '#1E293B',
		),
	);
}

/**
 * Return a default label for a CTA type.
 *
 * @param string $type CTA type.
 * @return string
 */
function telkari_get_default_cta_button_label( $type ) {
	$cta_types = telkari_get_supported_cta_types();

	if ( isset( $cta_types[ $type ]['label'] ) ) {
		return $cta_types[ $type ]['label'];
	}

	return __( 'CTA', 'telkari' );
}

/**
 * Return the display label for a CTA button type.
 *
 * @param string $type CTA type.
 * @return string
 */
function telkari_get_cta_button_type_label( $type ) {
	return telkari_get_default_cta_button_label( $type );
}

/**
 * Return the display label for a CTA button.
 *
 * @param array $button CTA button data.
 * @return string
 */
function telkari_get_cta_button_label( $button ) {
	if ( ! is_array( $button ) ) {
		return telkari_get_default_cta_button_label( '' );
	}

	$label = isset( $button['label'] ) ? trim( (string) $button['label'] ) : '';

	if ( '' !== $label ) {
		return $label;
	}

	return telkari_get_cta_button_type_label( isset( $button['type'] ) ? (string) $button['type'] : '' );
}

/**
 * Return the resolved display color for a CTA button.
 *
 * @param array $button CTA button data.
 * @return string
 */
function telkari_get_cta_button_color( $button ) {
	$type  = isset( $button['type'] ) ? (string) $button['type'] : '';
	$color = isset( $button['color'] ) ? sanitize_hex_color( $button['color'] ) : '';

	if ( $color ) {
		return $color;
	}

	$cta_types = telkari_get_supported_cta_types();

	if ( isset( $cta_types[ $type ]['default_color'] ) ) {
		return $cta_types[ $type ]['default_color'];
	}

	return '#1E293B';
}

/**
 * Sanitize CTA button collection.
 *
 * @param array $buttons Raw CTA button collection.
 * @return array
 */
function telkari_sanitize_cta_buttons( $buttons ) {
	return telkari_sanitize_collection_items( $buttons, 'telkari_sanitize_single_cta_button' );
}

/**
 * Sanitize a single CTA button.
 *
 * @param array $button Raw CTA button data.
 * @return array|null
 */
function telkari_sanitize_single_cta_button( $button ) {
	if ( ! is_array( $button ) ) {
		return null;
	}

	$cta_types = telkari_get_supported_cta_types();
	$type      = isset( $button['type'] ) ? sanitize_key( $button['type'] ) : '';

	if ( empty( $type ) || ! isset( $cta_types[ $type ] ) ) {
		return null;
	}

	$value = telkari_sanitize_cta_button_value(
		$type,
		isset( $button['value'] ) ? $button['value'] : ''
	);

	if ( '' === $value ) {
		return null;
	}

	$message = '';
	if ( ! empty( $cta_types[ $type ]['supports_message'] ) ) {
		$message = isset( $button['message'] ) ? sanitize_textarea_field( $button['message'] ) : '';
	}

	$url = telkari_build_cta_button_url( $type, $value, $message );

	if ( '' === $url ) {
		return null;
	}

	$label = isset( $button['label'] ) ? sanitize_text_field( $button['label'] ) : '';
	$color = isset( $button['color'] ) ? sanitize_hex_color( $button['color'] ) : '';

	return array(
		'id'      => isset( $button['id'] ) ? sanitize_text_field( $button['id'] ) : wp_generate_uuid4(),
		'type'    => $type,
		'label'   => $label,
		'value'   => $value,
		'message' => $message,
		'url'     => $url,
		'color'   => $color ? $color : '',
		'enabled' => ! empty( $button['enabled'] ),
		'order'   => isset( $button['order'] ) ? absint( $button['order'] ) : 0,
	);
}

/**
 * Sanitize CTA button value by type.
 *
 * @param string $type  CTA type.
 * @param mixed  $value Raw CTA value.
 * @return string
 */
function telkari_sanitize_cta_button_value( $type, $value ) {
	if ( ! is_scalar( $value ) ) {
		return '';
	}

	$value = (string) $value;

	switch ( $type ) {
		case 'whatsapp':
			return telkari_sanitize_phone_number( $value, false );

		case 'phone':
			return telkari_sanitize_phone_number( $value, true );

		case 'email':
			return sanitize_email( $value );

		case 'url':
			return esc_url_raw( $value );
	}

	return '';
}

/**
 * Build the canonical CTA URL for a CTA button.
 *
 * @param string $type    CTA type.
 * @param string $value   Sanitized CTA value.
 * @param string $message Optional CTA message.
 * @return string
 */
function telkari_build_cta_button_url( $type, $value, $message = '' ) {
	switch ( $type ) {
		case 'whatsapp':
			if ( '' === $value ) {
				return '';
			}

			$url = 'https://wa.me/' . $value;

			if ( '' !== $message ) {
				$url = add_query_arg( 'text', $message, $url );
			}

			return esc_url_raw( $url, array( 'https' ) );

		case 'phone':
			if ( '' === $value ) {
				return '';
			}

			return esc_url_raw( 'tel:' . $value, array( 'tel' ) );

		case 'email':
			if ( '' === $value ) {
				return '';
			}

			return esc_url_raw( 'mailto:' . $value, array( 'mailto' ) );

		case 'url':
			return esc_url_raw( $value );
	}

	return '';
}

/**
 * Sanitize phone numbers for CTA storage.
 *
 * The returned format is canonical and stripped of spaces, separators and
 * duplicate plus characters to keep stored values stable across migrations.
 *
 * @param string $value      Raw phone value.
 * @param bool   $allow_plus Whether the leading plus sign is allowed.
 * @return string
 */
function telkari_sanitize_phone_number( $value, $allow_plus ) {
	$value = trim( sanitize_text_field( $value ) );

	if ( '' === $value ) {
		return '';
	}

	$has_leading_plus = $allow_plus && 0 === strpos( $value, '+' );
	$digits           = preg_replace( '/\D+/', '', $value );

	if ( ! is_string( $digits ) || '' === $digits ) {
		return '';
	}

	$digits_length = strlen( $digits );

	if ( $digits_length < 6 || $digits_length > 18 ) {
		return '';
	}

	if ( $has_leading_plus ) {
		return '+' . $digits;
	}

	return $digits;
}
