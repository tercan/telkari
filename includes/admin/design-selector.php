<?php
/**
 * Design template selector for Telkari admin.
 *
 * @package Telkari
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Render the design selector cards.
 *
 * @param array $settings Current settings.
 */
function telkari_design_selector_render( $settings ) {
	$designs = telkari_get_design_definitions();

	$position_labels   = telkari_get_position_labels();
	$allowed_positions = telkari_get_allowed_positions( $settings['active_design'] );

	?>
	<div class="telkari-design-selector">
		<?php foreach ( $designs as $design_id => $design ) : ?>
			<label class="telkari-design-option <?php echo $settings['active_design'] === $design_id ? 'telkari-design-option--active' : ''; ?>">
				<input type="radio"
					   name="telkari_settings[active_design]"
					   value="<?php echo esc_attr( $design_id ); ?>"
					   <?php checked( $settings['active_design'], $design_id ); ?>
					   class="telkari-design-radio">

				<div class="telkari-design-preview">
					<?php echo telkari_get_design_preview_svg( $design_id ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- SVG is hardcoded. ?>
				</div>

				<div class="telkari-design-info">
					<h3 class="telkari-design-title"><?php echo esc_html( $design['label'] ); ?></h3>
					<p class="telkari-design-desc"><?php echo esc_html( $design['description'] ); ?></p>
				</div>
			</label>
		<?php endforeach; ?>
	</div>

	<div class="telkari-settings-panel" style="margin-top:1.5rem;">
		<div class="telkari-setting-card">
			<span class="telkari-setting-label"><?php esc_html_e( 'Select Position', 'telkari' ); ?></span>
			<div class="telkari-setting-control">
				<div class="telkari-btn-group" id="telkari-position-group">
					<?php foreach ( $allowed_positions as $position ) : ?>
						<label class="telkari-btn-option <?php echo $settings['active_position'] === $position ? 'telkari-btn-option--active' : ''; ?>">
							<input type="radio"
								   name="telkari_settings[active_position]"
								   value="<?php echo esc_attr( $position ); ?>"
								   <?php checked( $settings['active_position'], $position ); ?>>
							<?php echo esc_html( $position_labels[ $position ] ?? $position ); ?>
						</label>
					<?php endforeach; ?>
				</div>
			</div>
		</div>
	</div>

	<?php
	// Preserve other settings as hidden fields.
	telkari_render_hidden_settings( $settings, array( 'active_design', 'active_position' ) );
}

/**
 * Get design definitions with labels and descriptions.
 *
 * @return array
 */
function telkari_get_design_definitions() {
	$position_labels = telkari_get_position_labels();

	return array(
		'design-1' => array(
			'label'       => __( 'Orbit', 'telkari' ),
			'description' => __( 'Quarter circle trigger in a corner. Icons fan out in an arc on hover or click.', 'telkari' ),
			'positions'   => array(
				$position_labels['bottom-left'],
				$position_labels['bottom-right'],
			),
		),
		'design-2' => array(
			'label'       => __( 'Ribbon', 'telkari' ),
			'description' => __( 'Horizontal bar at the bottom of the page with icons in a row.', 'telkari' ),
			'positions'   => array(
				$position_labels['bottom-left'],
				$position_labels['bottom-right'],
				$position_labels['bottom-center'],
			),
		),
		'design-3' => array(
			'label'       => __( 'Pillar', 'telkari' ),
			'description' => __( 'Vertical sidebar strip with icons stacked in a column.', 'telkari' ),
			'positions'   => array(
				$position_labels['bottom-left'],
				$position_labels['bottom-right'],
			),
		),
	);
}

/**
 * Return an inline SVG preview illustration for a design.
 *
 * @param string $design_id Design identifier.
 * @return string SVG markup.
 */
function telkari_get_design_preview_svg( $design_id ) {
	switch ( $design_id ) {
		case 'design-2':
			// Horizontal bar preview: row of circles at bottom-center.
			return '<svg viewBox="0 0 240 140" xmlns="http://www.w3.org/2000/svg" class="telkari-preview-svg">
				<rect width="240" height="140" rx="2" fill="#f0f0f1"/>
				<rect x="10" y="8" width="220" height="16" rx="1" fill="#dcdcde"/>
				<rect x="10" y="32" width="220" height="80" rx="1" fill="#fff"/>
				<rect x="40" y="118" width="160" height="16" rx="1" fill="#1e293b"/>
				<circle cx="88" cy="126" r="5" fill="#fff"/>
				<circle cx="104" cy="126" r="5" fill="#fff"/>
				<circle cx="120" cy="126" r="5" fill="#fff"/>
				<circle cx="136" cy="126" r="5" fill="#fff"/>
				<circle cx="152" cy="126" r="5" fill="#fff"/>
			</svg>';

		case 'design-3':
			// Vertical bar preview: column of circles on left side.
			return '<svg viewBox="0 0 240 140" xmlns="http://www.w3.org/2000/svg" class="telkari-preview-svg">
				<rect width="240" height="140" rx="2" fill="#f0f0f1"/>
				<rect x="10" y="8" width="220" height="16" rx="1" fill="#dcdcde"/>
				<rect x="30" y="32" width="200" height="100" rx="1" fill="#fff"/>
				<rect x="10" y="44" width="16" height="88" rx="1" fill="#1e293b"/>
				<circle cx="18" cy="60" r="5" fill="#fff"/>
				<circle cx="18" cy="76" r="5" fill="#fff"/>
				<circle cx="18" cy="92" r="5" fill="#fff"/>
				<circle cx="18" cy="108" r="5" fill="#fff"/>
				<circle cx="18" cy="124" r="5" fill="#fff"/>
			</svg>';

		case 'design-1':
			// Quarter arc preview: quarter circle trigger with icons fanning out.
			return '<svg viewBox="0 0 240 140" xmlns="http://www.w3.org/2000/svg" class="telkari-preview-svg">
				<rect width="240" height="140" rx="2" fill="#f0f0f1"/>
				<rect x="10" y="8" width="220" height="16" rx="1" fill="#dcdcde"/>
				<rect x="10" y="32" width="220" height="100" rx="1" fill="#fff"/>
				<path d="M230 132 L230 112 A20 20 0 0 0 210 132 Z" fill="#2271b1"/>
				<circle cx="196" cy="126" r="6" fill="#2271b1" opacity="0.7"/>
				<circle cx="188" cy="114" r="6" fill="#2271b1" opacity="0.7"/>
				<circle cx="196" cy="102" r="6" fill="#2271b1" opacity="0.7"/>
				<circle cx="210" cy="96" r="6" fill="#2271b1" opacity="0.7"/>
				<circle cx="222" cy="102" r="6" fill="#2271b1" opacity="0.7"/>
			</svg>';

		default:
			return '';
	}
}
