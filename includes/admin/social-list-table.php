<?php
/**
 * Social accounts list management for Telkari admin.
 *
 * @package Telkari
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Render the social accounts management interface.
 *
 * @param array $settings Current settings.
 */
function telkari_social_list_render( $settings ) {
	$platforms = telkari_get_supported_platforms();
	$accounts  = $settings['social_accounts'];
	?>
	<div class="telkari-social-accounts">
		<h2><?php esc_html_e( 'Social Media Accounts', 'telkari' ); ?></h2>
		<p class="description"><?php esc_html_e( 'Add, reorder, and manage your social media accounts. Drag to reorder.', 'telkari' ); ?></p>

		<div id="telkari-accounts-list" class="telkari-sortable-list">
			<?php if ( empty( $accounts ) ) : ?>
				<p class="telkari-empty-state" id="telkari-empty-state">
					<?php esc_html_e( 'No social accounts added yet. Add your first account below.', 'telkari' ); ?>
				</p>
			<?php else : ?>
				<?php foreach ( $accounts as $index => $account ) : ?>
					<?php telkari_render_account_row( $account, $index, $platforms ); ?>
				<?php endforeach; ?>
			<?php endif; ?>
		</div>

		<div class="telkari-add-account">
			<h3><?php esc_html_e( 'Add New Account', 'telkari' ); ?></h3>
			<?php telkari_render_add_account_form( $platforms ); ?>
		</div>
	</div>
	<?php
}

/**
 * Render a single account row.
 *
 * @param array $account   Account data.
 * @param int   $index     Row index.
 * @param array $platforms Supported platforms.
 */
function telkari_render_account_row( $account, $index, $platforms ) {
	$platform_label = isset( $platforms[ $account['platform'] ] )
		? $platforms[ $account['platform'] ]['label']
		: $account['platform'];
	?>
	<div class="telkari-account-row" data-id="<?php echo esc_attr( $account['id'] ); ?>">
		<span class="telkari-drag-handle dashicons dashicons-menu"></span>

		<div class="telkari-account-info">
			<strong class="telkari-account-platform"><?php echo esc_html( $platform_label ); ?></strong>
			<span class="telkari-account-url"><?php echo esc_html( $account['url'] ); ?></span>
		</div>

		<div class="telkari-account-actions">
			<label class="telkari-toggle">
				<input type="checkbox"
					   name="telkari_settings[social_accounts][<?php echo (int) $index; ?>][enabled]"
					   value="1"
					   <?php checked( ! empty( $account['enabled'] ) ); ?>>
				<span class="telkari-toggle-label"><?php esc_html_e( 'Enabled', 'telkari' ); ?></span>
			</label>

			<button type="button" class="button telkari-delete-account">
				<?php esc_html_e( 'Delete', 'telkari' ); ?>
			</button>
		</div>

		<input type="hidden"
			   name="telkari_settings[social_accounts][<?php echo (int) $index; ?>][id]"
			   value="<?php echo esc_attr( $account['id'] ); ?>">
		<input type="hidden"
			   name="telkari_settings[social_accounts][<?php echo (int) $index; ?>][platform]"
			   value="<?php echo esc_attr( $account['platform'] ); ?>">
		<input type="hidden"
			   name="telkari_settings[social_accounts][<?php echo (int) $index; ?>][url]"
			   value="<?php echo esc_attr( $account['url'] ); ?>">
		<input type="hidden"
			   name="telkari_settings[social_accounts][<?php echo (int) $index; ?>][order]"
			   value="<?php echo esc_attr( $account['order'] ); ?>"
			   class="telkari-order-field">
	</div>
	<?php
}

/**
 * Render the add-new-account form.
 *
 * @param array $platforms Supported platforms.
 */
function telkari_render_add_account_form( $platforms ) {
	?>
	<div class="telkari-add-form">
		<div class="telkari-add-form-row">
			<label for="telkari-new-platform"><?php esc_html_e( 'Platform', 'telkari' ); ?></label>
			<select id="telkari-new-platform" class="regular-text">
				<option value=""><?php esc_html_e( 'Select Platform', 'telkari' ); ?></option>
				<?php foreach ( $platforms as $key => $platform ) : ?>
					<option value="<?php echo esc_attr( $key ); ?>">
						<?php echo esc_html( $platform['label'] ); ?>
					</option>
				<?php endforeach; ?>
			</select>
		</div>

		<div class="telkari-add-form-row">
			<label for="telkari-new-url"><?php esc_html_e( 'Profile URL', 'telkari' ); ?></label>
			<input type="url" id="telkari-new-url" class="regular-text" placeholder="https://">
		</div>

		<div class="telkari-add-form-row">
			<button type="button" id="telkari-add-account-btn" class="button button-primary">
				<?php esc_html_e( 'Add Account', 'telkari' ); ?>
			</button>
		</div>
	</div>
	<?php
}
