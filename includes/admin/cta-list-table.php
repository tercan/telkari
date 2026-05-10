<?php
/**
 * CTA button list management for Telkari admin.
 *
 * @package Telkari
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Return the modifier class for an admin CTA icon.
 *
 * @param string $type CTA type.
 * @return string
 */
function telkari_get_cta_admin_icon_class( $type ) {
	switch ( $type ) {
		case 'whatsapp':
			return 'telkari-cta-row-icon--whatsapp';
		case 'phone':
			return 'telkari-cta-row-icon--phone';
		case 'email':
			return 'telkari-cta-row-icon--email';
		case 'url':
		default:
			return 'telkari-cta-row-icon--url';
	}
}

/**
 * Return the SVG markup used for admin CTA icons.
 *
 * @param string $type CTA type.
 * @return string
 */
function telkari_get_cta_admin_icon_svg( $type ) {
	$icons = array(
		'whatsapp' => '<svg class="telkari-cta-admin-icon-svg" viewBox="0 0 448 512" fill="currentColor" aria-hidden="true" focusable="false"><path d="M380.9 97.1C339 55.1 283.2 32 223.9 32c-122.4 0-222 99.6-222 222 0 39.1 10.2 77.3 29.6 111L0 480l117.7-30.9c32.4 17.7 68.9 27 106.1 27h.1c122.3 0 224.1-99.6 224.1-222 0-59.3-25.2-115-67.1-157zm-157 341.6c-33.2 0-65.7-8.9-94-25.7l-6.7-4-69.8 18.3L72 359.2l-4.4-7c-18.5-29.4-28.2-63.3-28.2-98.2 0-101.7 82.8-184.5 184.6-184.5 49.3 0 95.6 19.2 130.4 54.1 34.8 34.9 56.2 81.2 56.1 130.5 0 101.8-84.9 184.6-186.6 184.6zm101.2-138.2c-5.5-2.8-32.8-16.2-37.9-18-5.1-1.9-8.8-2.8-12.5 2.8-3.7 5.6-14.3 18-17.6 21.8-3.2 3.7-6.5 4.2-12 1.4-32.6-16.3-54-29.1-75.5-66-5.7-9.8 5.7-9.1 16.3-30.3 1.8-3.7.9-6.9-.5-9.7-1.4-2.8-12.5-30.1-17.1-41.2-4.5-10.8-9.1-9.3-12.5-9.5-3.2-.2-6.9-.2-10.6-.2-3.7 0-9.7 1.4-14.8 6.9-5.1 5.6-19.4 19-19.4 46.3 0 27.3 19.9 53.7 22.6 57.4 2.8 3.7 39.1 59.7 94.8 83.8 35.2 15.2 49 16.5 66.6 13.9 10.7-1.6 32.8-13.4 37.4-26.4 4.6-13 4.6-24.1 3.2-26.4-1.3-2.5-5-3.9-10.5-6.6z"/></svg>',
		'phone'    => '<svg class="telkari-cta-admin-icon-svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3 19.4 19.4 0 0 1-6-6 19.8 19.8 0 0 1-3-8.7A2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7c.1.9.4 1.8.7 2.6a2 2 0 0 1-.4 2.1L8.2 9.8a16 16 0 0 0 6 6l1.4-1.2a2 2 0 0 1 2.1-.4c.8.3 1.7.6 2.6.7A2 2 0 0 1 22 16.9Z"/></svg>',
		'email'    => '<svg class="telkari-cta-admin-icon-svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><rect x="3" y="5" width="18" height="14" rx="2" ry="2"/><path d="m3 7 9 6 9-6"/></svg>',
		'url'      => '<svg class="telkari-cta-admin-icon-svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M14 3h7v7"/><path d="M10 14 21 3"/><path d="M19 14v5a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V7a2 2 0 0 1 2-2h5"/></svg>',
	);

	return isset( $icons[ $type ] ) ? $icons[ $type ] : $icons['url'];
}

/**
 * Build a compact aria label using an existing translated action label.
 *
 * @param string $action_label Translated action label.
 * @param string $button_label CTA button label.
 * @return string
 */
function telkari_get_cta_admin_action_aria_label( $action_label, $button_label ) {
	$button_label = trim( (string) $button_label );

	if ( '' === $button_label ) {
		return $action_label;
	}

	return trim( $action_label . ' ' . $button_label );
}

/**
 * Render the CTA button management interface.
 *
 * @param array $settings Current settings.
 */
function telkari_cta_list_render( $settings ) {
	$cta_types = telkari_get_supported_cta_types();
	$buttons   = isset( $settings['cta_buttons'] ) ? $settings['cta_buttons'] : array();
	?>
	<section class="telkari-admin-workspace" aria-labelledby="telkari-cta-section-title">
		<header class="telkari-cta-panel-header">
			<div>
				<h2 id="telkari-cta-section-title"><?php esc_html_e( 'CTA Buttons', 'telkari' ); ?></h2>
				<p class="description"><?php esc_html_e( 'Add, reorder, and manage your CTA buttons. Drag to reorder.', 'telkari' ); ?></p>
			</div>
		</header>
		<!-- /.telkari-cta-panel-header -->

		<div class="telkari-cta-content-shell">
			<div class="telkari-cta-main-panel">
				<div class="telkari-cta-list-section">
					<div id="telkari-cta-list"
						class="telkari-sortable-list telkari-cta-list"
						data-collection-key="cta_buttons"
						data-row-selector=".telkari-cta-row"
						data-empty-state-id="telkari-cta-empty-state"
						data-empty-message="<?php echo esc_attr__( 'CTA list is empty. Add your first CTA button below.', 'telkari' ); ?>">
						<?php if ( empty( $buttons ) ) : ?>
							<p class="telkari-empty-state" id="telkari-cta-empty-state">
								<?php esc_html_e( 'CTA list is empty. Add your first CTA button below.', 'telkari' ); ?>
							</p>
						<?php else : ?>
							<?php foreach ( $buttons as $index => $button ) : ?>
								<?php telkari_render_cta_row( $button, $index ); ?>
							<?php endforeach; ?>
						<?php endif; ?>
					</div>
				</div>
				<!-- /.telkari-cta-list-section -->

				<div class="telkari-add-cta">
					<?php telkari_render_add_cta_form( $cta_types ); ?>
				</div>
				<!-- /.telkari-add-cta -->
			</div>
			<!-- /.telkari-cta-main-panel -->

			<?php telkari_render_cta_preview_panel(); ?>
		</div>
		<!-- /.telkari-cta-content-shell -->
	</section>
	<!-- /.telkari-admin-workspace -->
	<?php
}

/**
 * Render a single CTA row.
 *
 * @param array $button CTA button data.
 * @param int   $index  Row index.
 */
function telkari_render_cta_row( $button, $index ) {
	$type               = isset( $button['type'] ) ? $button['type'] : '';
	$button_label       = telkari_get_cta_button_label( $button );
	$button_id          = isset( $button['id'] ) ? $button['id'] : wp_generate_uuid4();
	$value_preview      = isset( $button['value'] ) ? $button['value'] : '';
	$message            = isset( $button['message'] ) ? $button['message'] : '';
	$order              = isset( $button['order'] ) ? absint( $button['order'] ) : (int) $index;
	$is_enabled         = ! empty( $button['enabled'] );
	$row_classes        = 'telkari-account-row telkari-cta-row';
	$icon_class         = telkari_get_cta_admin_icon_class( $type );
	$icon_svg           = telkari_get_cta_admin_icon_svg( $type );
	$edit_aria_label    = telkari_get_cta_admin_action_aria_label( __( 'Edit', 'telkari' ), $button_label );
	$delete_aria_label  = telkari_get_cta_admin_action_aria_label( __( 'Delete', 'telkari' ), $button_label );
	$enabled_aria_label = telkari_get_cta_admin_action_aria_label( __( 'Enabled', 'telkari' ), $button_label );

	if ( ! $is_enabled ) {
		$row_classes .= ' telkari-cta-row--disabled';
	}
	?>
	<div class="<?php echo esc_attr( $row_classes ); ?>" data-id="<?php echo esc_attr( $button_id ); ?>">
		<span class="telkari-drag-handle dashicons dashicons-menu" aria-hidden="true"></span>

		<span class="telkari-cta-row-icon <?php echo esc_attr( $icon_class ); ?>" aria-hidden="true">
			<?php echo wp_kses( $icon_svg, telkari_get_svg_kses_allowed() ); ?>
		</span>

		<div class="telkari-account-info">
			<div class="telkari-cta-heading">
				<strong class="telkari-account-platform"><?php echo esc_html( $button_label ); ?></strong>
			</div>
			<span class="telkari-account-url" title="<?php echo esc_attr( $value_preview ); ?>"><?php echo esc_html( $value_preview ); ?></span>
			<?php if ( ! empty( $message ) ) : ?>
				<span class="telkari-cta-message" title="<?php echo esc_attr( $message ); ?>"><?php echo esc_html( $message ); ?></span>
			<?php endif; ?>
		</div>

		<div class="telkari-account-actions">
			<label class="telkari-toggle telkari-cta-toggle">
				<input type="checkbox"
						name="telkari_settings[cta_buttons][<?php echo (int) $index; ?>][enabled]"
						value="1"
						aria-label="<?php echo esc_attr( $enabled_aria_label ); ?>"
						<?php checked( $is_enabled ); ?>>
				<span class="telkari-cta-toggle-track" aria-hidden="true">
					<span class="telkari-cta-toggle-thumb"></span>
				</span>
				<span class="screen-reader-text"><?php echo esc_html( $enabled_aria_label ); ?></span>
			</label>

			<button type="button"
					class="telkari-cta-action-button telkari-cta-action-button--edit telkari-edit-cta"
					aria-label="<?php echo esc_attr( $edit_aria_label ); ?>"
					aria-pressed="false">
				<span class="dashicons dashicons-edit" aria-hidden="true"></span>
			</button>

			<button type="button"
					class="telkari-cta-action-button telkari-cta-action-button--delete telkari-delete-cta"
					aria-label="<?php echo esc_attr( $delete_aria_label ); ?>">
				<span class="dashicons dashicons-trash" aria-hidden="true"></span>
			</button>
		</div>

		<input type="hidden"
				name="telkari_settings[cta_buttons][<?php echo (int) $index; ?>][id]"
				value="<?php echo esc_attr( $button_id ); ?>">
		<input type="hidden"
				name="telkari_settings[cta_buttons][<?php echo (int) $index; ?>][type]"
				value="<?php echo esc_attr( $type ); ?>">
		<input type="hidden"
				name="telkari_settings[cta_buttons][<?php echo (int) $index; ?>][label]"
				value="<?php echo esc_attr( isset( $button['label'] ) ? $button['label'] : '' ); ?>">
		<input type="hidden"
				name="telkari_settings[cta_buttons][<?php echo (int) $index; ?>][value]"
				value="<?php echo esc_attr( $value_preview ); ?>">
		<input type="hidden"
				name="telkari_settings[cta_buttons][<?php echo (int) $index; ?>][message]"
				value="<?php echo esc_attr( $message ); ?>">
		<input type="hidden"
				name="telkari_settings[cta_buttons][<?php echo (int) $index; ?>][url]"
				value="<?php echo esc_attr( isset( $button['url'] ) ? $button['url'] : '' ); ?>">
		<input type="hidden"
				name="telkari_settings[cta_buttons][<?php echo (int) $index; ?>][color]"
				value="<?php echo esc_attr( isset( $button['color'] ) ? $button['color'] : '' ); ?>">
		<input type="hidden"
				name="telkari_settings[cta_buttons][<?php echo (int) $index; ?>][order]"
				value="<?php echo esc_attr( $order ); ?>"
				class="telkari-order-field">
	</div>
	<?php
}

/**
 * Render the add-new-CTA form.
 *
 * @param array $cta_types Supported CTA types.
 */
function telkari_render_add_cta_form( $cta_types ) {
	?>
	<section class="telkari-cta-builder" aria-labelledby="telkari-cta-builder-title" data-mode="create">
		<header class="telkari-cta-builder-header">
			<div>
				<p class="telkari-cta-builder-status" id="telkari-cta-builder-status" hidden aria-live="polite"></p>
				<p class="telkari-cta-builder-feedback" id="telkari-cta-builder-feedback" hidden role="status" aria-live="polite" aria-atomic="true"></p>
				<h3 id="telkari-cta-builder-title"><?php esc_html_e( 'Add New CTA Button', 'telkari' ); ?></h3>
			</div>
		</header>
		<!-- /.telkari-cta-builder-header -->

		<fieldset class="telkari-cta-type-selector" aria-label="<?php echo esc_attr__( 'CTA Type', 'telkari' ); ?>">
			<div class="telkari-cta-type-grid">
				<?php foreach ( $cta_types as $key => $cta_type ) : ?>
					<label class="telkari-cta-type-card" title="<?php echo esc_attr( $cta_type['label'] ); ?>">
						<input type="radio"
								name="telkari-new-cta-type"
								value="<?php echo esc_attr( $key ); ?>"
								class="telkari-cta-type-input"
								aria-label="<?php echo esc_attr( $cta_type['label'] ); ?>">
						<span class="telkari-cta-type-card-body">
							<span class="telkari-cta-type-card-icon <?php echo esc_attr( telkari_get_cta_admin_icon_class( $key ) ); ?>" aria-hidden="true">
								<?php echo wp_kses( telkari_get_cta_admin_icon_svg( $key ), telkari_get_svg_kses_allowed() ); ?>
							</span>
							<span class="telkari-cta-type-card-content">
								<strong class="telkari-cta-type-card-title" aria-hidden="true"><?php echo esc_html( $cta_type['label'] ); ?></strong>
								<span class="telkari-cta-type-card-description" aria-hidden="true"><?php echo esc_html( $cta_type['description'] ); ?></span>
							</span>
						</span>
					</label>
				<?php endforeach; ?>
			</div>

			<div class="telkari-cta-type-summary" id="telkari-cta-type-summary" hidden aria-live="polite">
				<span class="telkari-cta-type-summary-icon" id="telkari-cta-type-summary-icon" aria-hidden="true"></span>
				<div class="telkari-cta-type-summary-copy">
					<strong class="telkari-cta-type-summary-label" id="telkari-cta-type-summary-label"></strong>
					<span class="telkari-cta-type-summary-description" id="telkari-cta-type-summary-description"></span>
					<span class="telkari-cta-type-summary-example" id="telkari-cta-type-summary-example"></span>
				</div>
				<span class="telkari-cta-type-summary-swatch" id="telkari-cta-type-summary-swatch" aria-hidden="true"></span>
			</div>
		</fieldset>
		<!-- /.telkari-cta-type-selector -->

		<div class="telkari-cta-builder-grid">
			<div class="telkari-add-form-row telkari-add-form-row--wide">
				<label for="telkari-new-cta-label"><?php esc_html_e( 'Button Label', 'telkari' ); ?></label>
				<input type="text"
						id="telkari-new-cta-label"
						class="regular-text"
						placeholder="<?php echo esc_attr( telkari_get_default_cta_button_label( '' ) ); ?>"
						data-default-placeholder="<?php echo esc_attr( telkari_get_default_cta_button_label( '' ) ); ?>">
			</div>

			<div class="telkari-add-form-row telkari-add-form-row--wide">
				<label for="telkari-new-cta-value"><?php esc_html_e( 'Destination Value', 'telkari' ); ?></label>
				<input type="text"
						id="telkari-new-cta-value"
						class="regular-text"
						placeholder="<?php echo esc_attr__( 'Choose a CTA type to see the expected format.', 'telkari' ); ?>"
						data-default-placeholder="<?php echo esc_attr__( 'Choose a CTA type to see the expected format.', 'telkari' ); ?>"
						aria-describedby="telkari-cta-type-summary-description telkari-cta-type-summary-example telkari-cta-value-error">
				<span class="telkari-field-error" id="telkari-cta-value-error" hidden aria-live="polite"></span>
			</div>

			<div class="telkari-add-form-row telkari-add-form-row--wide telkari-add-form-row--hidden" id="telkari-cta-message-row">
				<label for="telkari-new-cta-message"><?php esc_html_e( 'WhatsApp Message', 'telkari' ); ?></label>
				<textarea id="telkari-new-cta-message" rows="3" class="large-text"></textarea>
				<span class="telkari-setting-description"><?php esc_html_e( 'Optional. Only used for WhatsApp buttons.', 'telkari' ); ?></span>
			</div>

			<div class="telkari-add-form-row telkari-add-form-row--wide telkari-cta-color-field">
				<label for="telkari-new-cta-color"><?php esc_html_e( 'Button Color', 'telkari' ); ?></label>
				<div class="telkari-cta-color-picker-wrap">
					<input type="text" id="telkari-new-cta-color" class="telkari-color-picker" value="" data-default-color="#1E293B">
				</div>
				<div class="telkari-cta-color-state" id="telkari-cta-color-state" hidden aria-live="polite">
					<span class="telkari-cta-color-state-swatch" id="telkari-cta-color-state-swatch" aria-hidden="true"></span>
					<span class="telkari-cta-color-state-value" id="telkari-cta-color-state-value"></span>
				</div>
				<span class="telkari-setting-description"><?php esc_html_e( 'Optional. Leave empty to use the default CTA color.', 'telkari' ); ?></span>
			</div>
		</div>
		<!-- /.telkari-cta-builder-grid -->

		<div class="telkari-cta-builder-footer">
			<div class="telkari-cta-builder-actions">
				<button type="button" class="button telkari-cta-builder-secondary-action" id="telkari-reset-cta-form">
					<?php esc_html_e( 'Reset Form', 'telkari' ); ?>
				</button>
				<button type="button" id="telkari-add-cta-btn" class="button button-primary" disabled>
					<?php esc_html_e( 'Add CTA Button', 'telkari' ); ?>
				</button>
			</div>
		</div>
		<!-- /.telkari-cta-builder-footer -->
	</section>
	<?php
}

/**
 * Render the live preview panel for the CTA builder.
 */
function telkari_render_cta_preview_panel() {
	?>
	<aside class="telkari-cta-preview-panel" aria-labelledby="telkari-cta-preview-title">
		<section class="telkari-cta-preview-card">
			<div class="telkari-cta-preview-browser">
				<header class="telkari-cta-preview-browser-header" aria-hidden="true">
					<span class="telkari-cta-preview-browser-dot telkari-cta-preview-browser-dot--red"></span>
					<span class="telkari-cta-preview-browser-dot telkari-cta-preview-browser-dot--yellow"></span>
					<span class="telkari-cta-preview-browser-dot telkari-cta-preview-browser-dot--green"></span>
				</header>
				<!-- /.telkari-cta-preview-browser-header -->

				<div class="telkari-cta-preview-browser-body">
					<p class="telkari-cta-preview-line telkari-cta-preview-line--wide" aria-hidden="true"></p>
					<p class="telkari-cta-preview-line telkari-cta-preview-line--full" aria-hidden="true"></p>
					<p class="telkari-cta-preview-line telkari-cta-preview-line--medium" aria-hidden="true"></p>
					<button class="telkari-cta-preview-button" id="telkari-cta-preview-button" type="button" disabled>
						<span class="telkari-cta-preview-button-icon" id="telkari-cta-preview-icon" aria-hidden="true"></span>
						<span id="telkari-cta-preview-label"
								data-empty-text="<?php echo esc_attr__( 'Choose a CTA type', 'telkari' ); ?>">
							<?php esc_html_e( 'Choose a CTA type', 'telkari' ); ?>
						</span>
					</button>
				</div>
				<!-- /.telkari-cta-preview-browser-body -->
			</div>
			<!-- /.telkari-cta-preview-browser -->

			<div class="telkari-cta-preview-content">
				<h3 id="telkari-cta-preview-title" class="screen-reader-text"><?php esc_html_e( 'Live Preview', 'telkari' ); ?></h3>
				<p class="telkari-cta-preview-eyebrow"><?php esc_html_e( 'Live Preview', 'telkari' ); ?></p>
				<p class="telkari-cta-preview-value"
					id="telkari-cta-preview-value"
					data-empty-text="<?php echo esc_attr__( 'The CTA label, value, and color preview appear here.', 'telkari' ); ?>">
					<?php esc_html_e( 'The CTA label, value, and color preview appear here.', 'telkari' ); ?>
				</p>
				<p class="telkari-setting-description"
					id="telkari-cta-preview-note"
					data-empty-text="<?php echo esc_attr__( 'Your CTA preview updates as you edit the fields.', 'telkari' ); ?>">
					<?php esc_html_e( 'Your CTA preview updates as you edit the fields.', 'telkari' ); ?>
				</p>
			</div>
			<!-- /.telkari-cta-preview-content -->
		</section>
		<!-- /.telkari-cta-preview-card -->
	</aside>
	<?php
}
