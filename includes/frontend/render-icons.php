<?php
/**
 * Frontend icon rendering for Telkari plugin.
 *
 * @package Telkari
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Enqueue design-specific CSS on the frontend.
 */
function telkari_enqueue_frontend_css() {
	$settings = telkari_get_settings();
	$groups   = telkari_get_enabled_group_state( $settings );
	$accounts = $groups['social'] ? telkari_get_enabled_sorted_collection_items( $settings['social_accounts'], array( 'url' ) ) : array();
	$buttons  = $groups['cta'] ? telkari_get_enabled_sorted_collection_items( $settings['cta_buttons'], array( 'type', 'url' ) ) : array();

	if ( empty( $accounts ) && empty( $buttons ) ) {
		return;
	}

	$design = $settings['active_design'];

	wp_enqueue_style(
		'telkari-frontend-shared',
		TELKARI_URL . 'assets/css/frontend-shared.css',
		array(),
		TELKARI_VERSION
	);

	wp_enqueue_style(
		'telkari-frontend',
		TELKARI_URL . 'assets/css/' . $design . '.css',
		array( 'telkari-frontend-shared' ),
		TELKARI_VERSION
	);

	// Enqueue design-1 toggle script only when the social orbit trigger exists.
	if ( 'design-1' === $design && ! empty( $accounts ) ) {
		wp_enqueue_script(
			'telkari-design1-toggle',
			TELKARI_URL . 'assets/js/design-1-toggle.js',
			array(),
			TELKARI_VERSION,
			true
		);
	}

	// Convert px values to rem for CSS custom properties.
	$social_icon_size    = isset( $settings['social_icon_size'] ) ? $settings['social_icon_size'] : $settings['icon_size'];
	$social_icon_spacing = isset( $settings['social_icon_spacing'] ) ? $settings['social_icon_spacing'] : $settings['icon_spacing'];
	$cta_button_spacing  = isset( $settings['cta_button_spacing'] ) ? $settings['cta_button_spacing'] : $settings['icon_spacing'];
	$icon_size_rem       = round( $social_icon_size / 16, 4 );
	$icon_spacing_rem    = round( $social_icon_spacing / 16, 4 );
	$cta_spacing_rem     = round( $cta_button_spacing / 16, 4 );

	// Resolve wrapper background color.
	$brand_colors    = telkari_get_platform_brand_colors();
	$platform_colors = isset( $settings['platform_colors'] ) ? $settings['platform_colors'] : array();
	$wrapper_bg      = ! empty( $platform_colors['wrapper_bg'] ) ? $platform_colors['wrapper_bg'] : $brand_colors['wrapper_bg'];

	$custom_css = sprintf(
		'.telkari-container { --telkari-icon-size: %srem; --telkari-icon-spacing: %srem; --telkari-cta-spacing: %srem; --telkari-wrapper-bg: %s; }',
		esc_attr( $icon_size_rem ),
		esc_attr( $icon_spacing_rem ),
		esc_attr( $cta_spacing_rem ),
		esc_attr( $wrapper_bg )
	);

	wp_add_inline_style( 'telkari-frontend-shared', $custom_css );
}
add_action( 'wp_enqueue_scripts', 'telkari_enqueue_frontend_css' );

/**
 * Render social media icons in the footer.
 */
function telkari_render_frontend_icons() {
	$settings = telkari_get_settings();
	$groups   = telkari_get_enabled_group_state( $settings );
	$accounts = $groups['social'] ? telkari_get_enabled_sorted_collection_items( $settings['social_accounts'], array( 'url' ) ) : array();
	$buttons  = $groups['cta'] ? telkari_get_enabled_sorted_collection_items( $settings['cta_buttons'], array( 'type', 'url' ) ) : array();

	if ( empty( $accounts ) && empty( $buttons ) ) {
		return;
	}

	$render_groups      = array(
		'social' => ! empty( $accounts ),
		'cta'    => ! empty( $buttons ),
	);
	$normalized_pair    = telkari_normalize_position_pair(
		$settings['active_design'],
		isset( $settings['active_position'] ) ? $settings['active_position'] : 'bottom-right',
		isset( $settings['cta_position'] ) ? $settings['cta_position'] : 'bottom-right',
		$render_groups
	);
	$social_position    = $normalized_pair['social_position'];
	$cta_position       = $normalized_pair['cta_position'];

	if ( ! empty( $accounts ) && ! empty( $buttons ) && $social_position !== $cta_position ) {
		telkari_render_frontend_container( $settings, $accounts, array(), $social_position );
		telkari_render_frontend_container( $settings, array(), $buttons, $cta_position );
		return;
	}

	if ( empty( $accounts ) && ! empty( $buttons ) ) {
		telkari_render_frontend_container( $settings, array(), $buttons, $cta_position );
		return;
	}

	telkari_render_frontend_container( $settings, $accounts, $buttons, $social_position );
}
add_action( 'wp_footer', 'telkari_render_frontend_icons' );

/**
 * Render a single positioned frontend container.
 *
 * @param array  $settings Plugin settings.
 * @param array  $accounts Enabled social accounts for this container.
 * @param array  $buttons  Enabled CTA buttons for this container.
 * @param string $position Position identifier.
 */
function telkari_render_frontend_container( $settings, $accounts, $buttons, $position ) {
	$classes = array(
		'telkari-container',
		'telkari-' . $settings['active_design'],
		'telkari-position-' . $position,
		'telkari-style-' . $settings['icon_style'],
	);

	if ( $settings['show_tooltip'] ) {
		$classes[] = 'telkari-has-tooltips';
	}

	if ( ! empty( $accounts ) ) {
		$classes[] = 'telkari-has-socials';
	}

	if ( ! empty( $buttons ) ) {
		$classes[] = 'telkari-has-ctas';
	}

	if ( ! empty( $accounts ) && ! empty( $buttons ) ) {
		$classes[] = 'telkari-container--mixed';
	} elseif ( ! empty( $accounts ) ) {
		$classes[] = 'telkari-container--social-only';
	} elseif ( ! empty( $buttons ) ) {
		$classes[] = 'telkari-container--cta-only';
	}

	$cta_button_size  = isset( $settings['cta_button_size'] ) ? sanitize_html_class( $settings['cta_button_size'] ) : 'default';
	$cta_button_width = isset( $settings['cta_button_width'] ) ? sanitize_html_class( $settings['cta_button_width'] ) : 'content';
	$classes[]        = 'telkari-cta-size-' . $cta_button_size;
	$classes[]        = 'telkari-cta-width-' . $cta_button_width;

	$is_design_1 = 'design-1' === $settings['active_design'];
	?>
	<div class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>">
		<div class="telkari-design-shell">
			<?php if ( $is_design_1 && ! empty( $buttons ) ) : ?>
				<?php telkari_render_cta_group( $buttons, $settings ); ?>
			<?php endif; ?>

			<?php if ( ! empty( $accounts ) ) : ?>
				<?php telkari_render_social_group( $accounts, $settings, $is_design_1 ); ?>
			<?php endif; ?>

			<?php if ( ! $is_design_1 && ! empty( $buttons ) ) : ?>
				<?php telkari_render_cta_group( $buttons, $settings ); ?>
			<?php endif; ?>
		</div>
	</div>
	<?php
}

/**
 * Render the social link group.
 *
 * @param array $accounts    Enabled social accounts.
 * @param array $settings    Plugin settings.
 * @param bool  $is_design_1 Whether the active design is Orbit.
 */
function telkari_render_social_group( $accounts, $settings, $is_design_1 ) {
	$account_count = count( $accounts );
	?>
	<nav class="telkari-social-group" aria-label="<?php esc_attr_e( 'Social Media Links', 'telkari' ); ?>">
		<div class="telkari-icons-wrapper"<?php echo $is_design_1 ? ' style="--telkari-item-count:' . esc_attr( (int) $account_count ) . '"' : ''; ?>>
			<?php
			$index = 0;
			foreach ( $accounts as $account ) :
				telkari_render_single_icon( $account, $settings, $is_design_1 ? $index : -1 );
				++$index;
			endforeach;
			?>
		</div>
		<?php if ( $is_design_1 ) : ?>
			<?php
			$brand_colors    = telkari_get_platform_brand_colors();
			$platform_colors = isset( $settings['platform_colors'] ) ? $settings['platform_colors'] : array();
			$trigger_bg      = ! empty( $platform_colors['trigger_button'] ) ? $platform_colors['trigger_button'] : $brand_colors['trigger_button'];
			$trigger_fg      = telkari_get_contrast_color( $trigger_bg );
			?>
			<button type="button" class="telkari-trigger" style="--telkari-trigger-bg:<?php echo esc_attr( $trigger_bg ); ?>;--telkari-trigger-fg:<?php echo esc_attr( $trigger_fg ); ?>" aria-label="<?php esc_attr_e( 'Social Media Links', 'telkari' ); ?>">
				<svg class="telkari-trigger-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" aria-hidden="true" focusable="false">
					<line x1="12" y1="5" x2="12" y2="19"/>
					<line x1="5" y1="12" x2="19" y2="12"/>
				</svg>
			</button>
		<?php endif; ?>
	</nav>
	<?php
}

/**
 * Render the CTA button group.
 *
 * @param array $buttons  Enabled CTA buttons.
 * @param array $settings Plugin settings.
 */
function telkari_render_cta_group( $buttons, $settings ) {
	?>
	<div class="telkari-cta-wrapper" role="group" aria-label="<?php esc_attr_e( 'CTA Buttons', 'telkari' ); ?>">
		<?php foreach ( $buttons as $button ) : ?>
			<?php telkari_render_single_cta_button( $button, $settings ); ?>
		<?php endforeach; ?>
	</div>
	<?php
}

/**
 * Compute a contrasting foreground color for a given hex background.
 *
 * Returns white for dark backgrounds, dark slate for light backgrounds.
 *
 * @param string $hex Hex color (e.g. '#E4405F').
 * @return string Hex color for foreground.
 */
function telkari_get_contrast_color( $hex ) {
	$hex = ltrim( $hex, '#' );
	if ( strlen( $hex ) === 3 ) {
		$hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
	}
	$r = hexdec( substr( $hex, 0, 2 ) ) / 255;
	$g = hexdec( substr( $hex, 2, 2 ) ) / 255;
	$b = hexdec( substr( $hex, 4, 2 ) ) / 255;

	// sRGB to linear.
	$r = $r <= 0.03928 ? $r / 12.92 : pow( ( $r + 0.055 ) / 1.055, 2.4 );
	$g = $g <= 0.03928 ? $g / 12.92 : pow( ( $g + 0.055 ) / 1.055, 2.4 );
	$b = $b <= 0.03928 ? $b / 12.92 : pow( ( $b + 0.055 ) / 1.055, 2.4 );

	$luminance = 0.2126 * $r + 0.7152 * $g + 0.0722 * $b;

	return $luminance > 0.35 ? '#1e293b' : '#ffffff';
}

/**
 * Render a single icon link.
 *
 * @param array $account  Account data.
 * @param array $settings Plugin settings.
 * @param int   $index    Item index for design-1 arc positioning (-1 to skip).
 */
function telkari_render_single_icon( $account, $settings, $index = -1 ) {
	$platforms = telkari_get_supported_platforms();
	$platform  = isset( $platforms[ $account['platform'] ] ) ? $platforms[ $account['platform'] ] : null;

	if ( ! $platform ) {
		return;
	}

	// Allowed attribute keys for the icon link element.
	$allowed_attrs = array( 'href', 'class', 'target', 'rel', 'title', 'aria-label', 'style' );

	$attrs = array(
		'href'  => $account['url'],
		'class' => 'telkari-icon-link telkari-platform-' . $account['platform'],
	);

	$attrs = array_merge( $attrs, telkari_get_link_target_attributes( $settings['link_target'] ) );

	if ( $settings['show_tooltip'] ) {
		$attrs['title']      = $platform['label'];
		$attrs['aria-label'] = $platform['label'];
	} else {
		$attrs['aria-label'] = $platform['label'];
	}

	// Resolve per-platform background and foreground colors.
	$brand_colors    = telkari_get_platform_brand_colors();
	$platform_colors = isset( $settings['platform_colors'] ) ? $settings['platform_colors'] : array();
	$platform_key    = $account['platform'];

	if ( ! empty( $platform_colors[ $platform_key ] ) ) {
		$bg_color = $platform_colors[ $platform_key ];
	} elseif ( isset( $brand_colors[ $platform_key ] ) ) {
		$bg_color = $brand_colors[ $platform_key ];
	} else {
		$bg_color = '#1e293b';
	}
	$fg_color = telkari_get_contrast_color( $bg_color );

	$style_parts = array(
		'--telkari-bg:' . esc_attr( $bg_color ),
		'--telkari-fg:' . esc_attr( $fg_color ),
	);

	if ( $index >= 0 ) {
		$style_parts[] = '--telkari-item-index:' . (int) $index;
	}

	$attrs['style'] = implode( ';', $style_parts );

	$svg = telkari_get_svg_icon( $account['platform'] );

	echo '<a';
	telkari_render_html_attributes( $attrs, $allowed_attrs );
	echo '>' . wp_kses( $svg, telkari_get_svg_kses_allowed() ) . '</a>';
}

/**
 * Render a single CTA button.
 *
 * @param array $button   CTA button data.
 * @param array $settings Plugin settings.
 */
function telkari_render_single_cta_button( $button, $settings ) {
	$type = isset( $button['type'] ) ? $button['type'] : '';
	$url  = isset( $button['url'] ) ? $button['url'] : '';

	if ( empty( $type ) || empty( $url ) ) {
		return;
	}

	$label = telkari_get_cta_button_label( $button );
	$bg    = telkari_get_cta_button_color( $button );
	$fg    = telkari_get_contrast_color( $bg );

	$attrs = array(
		'href'       => $url,
		'class'      => 'telkari-cta-link telkari-cta-type-' . $type,
		'aria-label' => $label,
		'style'      => implode(
			';',
			array(
				'--telkari-bg:' . esc_attr( $bg ),
				'--telkari-fg:' . esc_attr( $fg ),
			)
		),
	);

	$attrs = array_merge( $attrs, telkari_get_cta_link_target_attributes( $button, $settings ) );
	$icon  = telkari_get_cta_icon_svg( $type );

	echo '<a';
	telkari_render_html_attributes( $attrs, array( 'href', 'class', 'target', 'rel', 'aria-label', 'style' ) );
	echo '>';
	echo wp_kses( $icon, telkari_get_svg_kses_allowed() );
	echo '<span class="telkari-cta-link-label">' . esc_html( $label ) . '</span>';
	echo '</a>';
}

/**
 * Return link attributes for a CTA button.
 *
 * `tel:` and `mailto:` actions are left untouched. URL-based actions can
 * still follow the global link target preference for backward compatibility.
 *
 * @param array $button   CTA button data.
 * @param array $settings Plugin settings.
 * @return array
 */
function telkari_get_cta_link_target_attributes( $button, $settings ) {
	$type = isset( $button['type'] ) ? $button['type'] : '';

	if ( ! in_array( $type, array( 'url', 'whatsapp' ), true ) ) {
		return array();
	}

	return telkari_get_link_target_attributes( $settings['link_target'] );
}

/**
 * Render a sanitized set of HTML attributes.
 *
 * @param array $attrs         Attribute map.
 * @param array $allowed_attrs Allowed attribute keys.
 */
function telkari_render_html_attributes( $attrs, $allowed_attrs ) {
	foreach ( $attrs as $key => $value ) {
		if ( ! in_array( $key, $allowed_attrs, true ) ) {
			continue;
		}

		if ( 'href' === $key ) {
			echo ' href="' . esc_url( $value ) . '"';
			continue;
		}

		echo ' ' . esc_attr( $key ) . '="' . esc_attr( $value ) . '"';
	}
}

/**
 * Return an SVG icon for a CTA button type.
 *
 * @param string $type CTA button type.
 * @return string
 */
function telkari_get_cta_icon_svg( $type ) {
	$icons = array(
		'whatsapp' => '<svg class="telkari-cta-link-icon" viewBox="0 0 448 512" fill="currentColor" aria-hidden="true" focusable="false"><path d="M380.9 97.1C339 55.1 283.2 32 223.9 32c-122.4 0-222 99.6-222 222 0 39.1 10.2 77.3 29.6 111L0 480l117.7-30.9c32.4 17.7 68.9 27 106.1 27h.1c122.3 0 224.1-99.6 224.1-222 0-59.3-25.2-115-67.1-157zm-157 341.6c-33.2 0-65.7-8.9-94-25.7l-6.7-4-69.8 18.3L72 359.2l-4.4-7c-18.5-29.4-28.2-63.3-28.2-98.2 0-101.7 82.8-184.5 184.6-184.5 49.3 0 95.6 19.2 130.4 54.1 34.8 34.9 56.2 81.2 56.1 130.5 0 101.8-84.9 184.6-186.6 184.6zm101.2-138.2c-5.5-2.8-32.8-16.2-37.9-18-5.1-1.9-8.8-2.8-12.5 2.8-3.7 5.6-14.3 18-17.6 21.8-3.2 3.7-6.5 4.2-12 1.4-32.6-16.3-54-29.1-75.5-66-5.7-9.8 5.7-9.1 16.3-30.3 1.8-3.7.9-6.9-.5-9.7-1.4-2.8-12.5-30.1-17.1-41.2-4.5-10.8-9.1-9.3-12.5-9.5-3.2-.2-6.9-.2-10.6-.2-3.7 0-9.7 1.4-14.8 6.9-5.1 5.6-19.4 19-19.4 46.3 0 27.3 19.9 53.7 22.6 57.4 2.8 3.7 39.1 59.7 94.8 83.8 35.2 15.2 49 16.5 66.6 13.9 10.7-1.6 32.8-13.4 37.4-26.4 4.6-13 4.6-24.1 3.2-26.4-1.3-2.5-5-3.9-10.5-6.6z"/></svg>',
		'phone'    => '<svg class="telkari-cta-link-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3 19.4 19.4 0 0 1-6-6 19.8 19.8 0 0 1-3-8.7A2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7c.1.9.4 1.8.7 2.6a2 2 0 0 1-.4 2.1L8.2 9.8a16 16 0 0 0 6 6l1.4-1.2a2 2 0 0 1 2.1-.4c.8.3 1.7.6 2.6.7A2 2 0 0 1 22 16.9Z"/></svg>',
		'email'    => '<svg class="telkari-cta-link-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><rect x="3" y="5" width="18" height="14" rx="2" ry="2"/><path d="m3 7 9 6 9-6"/></svg>',
		'url'      => '<svg class="telkari-cta-link-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M14 3h7v7"/><path d="M10 14 21 3"/><path d="M19 14v5a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V7a2 2 0 0 1 2-2h5"/></svg>',
	);

	if ( ! isset( $icons[ $type ] ) ) {
		return '';
	}

	return $icons[ $type ];
}


/**
 * Read and return an SVG icon for a platform.
 *
 * Uses a static cache to avoid reading the same file multiple times.
 *
 * @param string $platform Platform key.
 * @return string SVG markup or empty string.
 */
function telkari_get_svg_icon( $platform ) {
	static $cache = array();

	if ( isset( $cache[ $platform ] ) ) {
		return $cache[ $platform ];
	}

	$platforms = telkari_get_supported_platforms();

	if ( ! isset( $platforms[ $platform ]['icon'] ) ) {
		$cache[ $platform ] = '';
		return '';
	}

	$icon_file = $platforms[ $platform ]['icon'];

	// Whitelist: only allow known filenames.
	$allowed_icons = array_column( $platforms, 'icon' );
	if ( ! in_array( $icon_file, $allowed_icons, true ) ) {
		$cache[ $platform ] = '';
		return '';
	}

	$icon_path = TELKARI_PATH . 'assets/icons/' . $icon_file;

	if ( ! file_exists( $icon_path ) ) {
		$cache[ $platform ] = '';
		return '';
	}

	$svg = file_get_contents( $icon_path ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- Local trusted file.

	if ( empty( $svg ) ) {
		$cache[ $platform ] = '';
		return '';
	}

	// Add CSS class to the SVG element.
	$svg = str_replace( '<svg', '<svg class="telkari-icon"', $svg );

	// Sanitize SVG markup with a strict allowlist.
	$svg = wp_kses( $svg, telkari_get_svg_kses_allowed() );

	$cache[ $platform ] = $svg;
	return $svg;
}
