<?php
/**
 * Uninstall handler for Telkari plugin.
 *
 * Removes all plugin data from the database on uninstall.
 *
 * @package Telkari
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

delete_option( 'telkari_settings' );

// Multisite support.
if ( is_multisite() ) {
	$telkari_sites = get_sites( array( 'number' => 0 ) );

	foreach ( $telkari_sites as $telkari_site ) {
		switch_to_blog( $telkari_site->blog_id );
		delete_option( 'telkari_settings' );
		restore_current_blog();
	}
}
