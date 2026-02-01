<?php
/**
 * Security utilities for Telkari plugin.
 *
 * @package Telkari
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Verify an admin request (nonce + capability).
 *
 * @param string $action Nonce action name.
 * @return bool
 */
function telkari_verify_admin_request( $action = 'telkari_settings' ) {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'You do not have permission to access this page.', 'telkari' ) );
	}

	if ( ! isset( $_POST['telkari_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['telkari_nonce'] ) ), $action ) ) {
		wp_die( esc_html__( 'Security check failed.', 'telkari' ) );
	}

	return true;
}

/**
 * Check if the current user has plugin management capability.
 *
 * @return bool
 */
function telkari_check_capability() {
	return current_user_can( 'manage_options' );
}
