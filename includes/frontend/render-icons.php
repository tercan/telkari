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

	// Only load CSS if there are enabled accounts.
	$has_enabled = false;
	foreach ( $settings['social_accounts'] as $account ) {
		if ( ! empty( $account['enabled'] ) && ! empty( $account['url'] ) ) {
			$has_enabled = true;
			break;
		}
	}

	if ( ! $has_enabled ) {
		return;
	}

	$design = $settings['active_design'];

	wp_enqueue_style(
		'telkari-frontend',
		TELKARI_URL . 'assets/css/' . $design . '.css',
		array(),
		TELKARI_VERSION
	);

	// Convert px values to rem for CSS custom properties.
	$icon_size_rem    = round( $settings['icon_size'] / 16, 4 );
	$icon_spacing_rem = round( $settings['icon_spacing'] / 16, 4 );

	// Resolve wrapper background color.
	$brand_colors    = telkari_get_platform_brand_colors();
	$platform_colors = isset( $settings['platform_colors'] ) ? $settings['platform_colors'] : array();
	$wrapper_bg      = ! empty( $platform_colors['wrapper_bg'] ) ? $platform_colors['wrapper_bg'] : $brand_colors['wrapper_bg'];

	$custom_css = sprintf(
		'.telkari-container { --telkari-icon-size: %srem; --telkari-icon-spacing: %srem; --telkari-wrapper-bg: %s; }',
		esc_attr( $icon_size_rem ),
		esc_attr( $icon_spacing_rem ),
		esc_attr( $wrapper_bg )
	);

	wp_add_inline_style( 'telkari-frontend', $custom_css );
}
add_action( 'wp_enqueue_scripts', 'telkari_enqueue_frontend_css' );

/**
 * Render social media icons in the footer.
 */
function telkari_render_frontend_icons() {
	$settings = telkari_get_settings();

	// Filter to enabled accounts with valid URLs.
	$accounts = array_filter( $settings['social_accounts'], function ( $account ) {
		return ! empty( $account['enabled'] ) && ! empty( $account['url'] );
	} );

	if ( empty( $accounts ) ) {
		return;
	}

	// Sort by order.
	usort( $accounts, function ( $a, $b ) {
		return ( $a['order'] ?? 0 ) - ( $b['order'] ?? 0 );
	} );

	$classes = array(
		'telkari-container',
		'telkari-' . $settings['active_design'],
		'telkari-position-' . $settings['active_position'],
		'telkari-style-' . $settings['icon_style'],
	);

	if ( $settings['show_tooltip'] ) {
		$classes[] = 'telkari-has-tooltips';
	}

	$is_design_1  = 'design-1' === $settings['active_design'];
	$account_count = count( $accounts );

	?>
	<div class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>" role="navigation" aria-label="<?php esc_attr_e( 'Social Media Links', 'telkari' ); ?>">
		<div class="telkari-icons-wrapper"<?php echo $is_design_1 ? ' style="--telkari-item-count:' . (int) $account_count . '"' : ''; ?>>
			<?php
			$index = 0;
			foreach ( $accounts as $account ) :
				telkari_render_single_icon( $account, $settings, $is_design_1 ? $index : -1 );
				$index++;
			endforeach;
			?>
		</div>
		<?php if ( $is_design_1 ) :
			$brand_colors     = telkari_get_platform_brand_colors();
			$platform_colors  = isset( $settings['platform_colors'] ) ? $settings['platform_colors'] : array();
			$trigger_bg       = ! empty( $platform_colors['trigger_button'] ) ? $platform_colors['trigger_button'] : $brand_colors['trigger_button'];
			$trigger_fg       = telkari_get_contrast_color( $trigger_bg );
		?>
			<button type="button" class="telkari-trigger" style="--telkari-trigger-bg:<?php echo esc_attr( $trigger_bg ); ?>;--telkari-trigger-fg:<?php echo esc_attr( $trigger_fg ); ?>" aria-label="<?php esc_attr_e( 'Social Media Links', 'telkari' ); ?>">
				<svg class="telkari-trigger-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
					<line x1="12" y1="5" x2="12" y2="19"/>
					<line x1="5" y1="12" x2="19" y2="12"/>
				</svg>
			</button>
		<?php endif; ?>
	</div>
	<?php

	if ( $is_design_1 ) {
		telkari_render_design1_inline_js();
	}
}
add_action( 'wp_footer', 'telkari_render_frontend_icons' );

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

	$attrs = array(
		'href'  => esc_url( $account['url'] ),
		'class' => 'telkari-icon-link telkari-platform-' . esc_attr( $account['platform'] ),
	);

	if ( '_blank' === $settings['link_target'] ) {
		$attrs['target'] = '_blank';
		$attrs['rel']    = 'noopener noreferrer';
	}

	if ( $settings['show_tooltip'] ) {
		$attrs['title']      = esc_attr( $platform['label'] );
		$attrs['aria-label'] = esc_attr( $platform['label'] );
	} else {
		$attrs['aria-label'] = esc_attr( $platform['label'] );
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

	$attrs_str = '';
	foreach ( $attrs as $key => $value ) {
		$attrs_str .= ' ' . $key . '="' . $value . '"';
	}

	$svg = telkari_get_svg_icon( $account['platform'] );

	echo '<a' . $attrs_str . '>' . $svg . '</a>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- SVG is from trusted local file, attributes are escaped above.
}

/**
 * Output inline JavaScript for design-1 click toggle.
 */
function telkari_render_design1_inline_js() {
	?>
	<script>
	(function(){
		var c = document.querySelector('.telkari-design-1');
		if (!c) return;
		var btn = c.querySelector('.telkari-trigger');
		if (!btn) return;
		btn.addEventListener('click', function() {
			c.classList.toggle('telkari-open');
		});
		document.addEventListener('click', function(e) {
			if (!c.contains(e.target)) {
				c.classList.remove('telkari-open');
			}
		});
	})();
	</script>
	<?php
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

	$cache[ $platform ] = $svg;
	return $svg;
}
