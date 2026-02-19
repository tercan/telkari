<?php
/**
 * Plugin Name: Telkari
 * Plugin URI: https://tercan.net/telkari
 * Description: Theme-independent WordPress social media links management plugin.
 * Version: 0.1.1
 * Requires at least: 5.9
 * Requires PHP: 7.4
 * Author: Tercan Keskin
 * Author URI: https://tercan.net/
 * Text Domain: telkari
 * Domain Path: /languages
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'TELKARI_VERSION', '0.1.1' );
define( 'TELKARI_PATH', plugin_dir_path( __FILE__ ) );
define( 'TELKARI_URL', plugin_dir_url( __FILE__ ) );
define( 'TELKARI_BASENAME', plugin_basename( __FILE__ ) );

// Core includes (always loaded).
require_once TELKARI_PATH . 'includes/core/options.php';
require_once TELKARI_PATH . 'includes/core/sanitization.php';

// Admin includes.
if ( is_admin() ) {
	require_once TELKARI_PATH . 'includes/admin/settings-page.php';
}

/**
 * Add settings link on the plugins list page.
 *
 * @param array $links Existing action links.
 * @return array
 */
function telkari_plugin_action_links( $links ) {
	$settings_link = sprintf(
		'<a href="%s">%s</a>',
		esc_url( admin_url( 'admin.php?page=telkari-settings' ) ),
		esc_html__( 'Settings', 'telkari' )
	);
	array_unshift( $links, $settings_link );
	return $links;
}
add_filter( 'plugin_action_links_' . TELKARI_BASENAME, 'telkari_plugin_action_links' );

// Frontend includes.
if ( ! is_admin() ) {
	require_once TELKARI_PATH . 'includes/frontend/render-icons.php';
}
