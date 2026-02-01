/**
 * Telkari Admin JavaScript
 *
 * Handles SortableJS integration, design-position filtering,
 * and social account add/delete operations.
 *
 * @package Telkari
 */

(function () {
	'use strict';

	document.addEventListener('DOMContentLoaded', function () {
		initSortable();
		initDesignSelector();
		initAccountManagement();
		initColorPickers();
		initRangeSliders();
		initButtonGroups();
	});

	/**
	 * Initialize SortableJS on the accounts list.
	 */
	function initSortable() {
		var listEl = document.getElementById('telkari-accounts-list');

		if (!listEl || typeof Sortable === 'undefined') {
			return;
		}

		Sortable.create(listEl, {
			handle: '.telkari-drag-handle',
			animation: 150,
			ghostClass: 'sortable-ghost',
			onEnd: function () {
				reindexAccountRows();
			}
		});
	}

	/**
	 * Re-index all account row field names and order values after sort/add/delete.
	 */
	function reindexAccountRows() {
		var rows = document.querySelectorAll('.telkari-account-row');

		rows.forEach(function (row, index) {
			// Update hidden field names.
			var inputs = row.querySelectorAll('input[name*="social_accounts"]');
			inputs.forEach(function (input) {
				input.name = input.name.replace(
					/social_accounts\]\[\d+\]/,
					'social_accounts][' + index + ']'
				);
			});

			// Update order value.
			var orderField = row.querySelector('.telkari-order-field');
			if (orderField) {
				orderField.value = index;
			}
		});
	}

	/**
	 * Listen for design radio changes, update active class, and rebuild position selector.
	 */
	function initDesignSelector() {
		var designInputs = document.querySelectorAll('.telkari-design-radio');

		if (!designInputs.length) {
			return;
		}

		designInputs.forEach(function (input) {
			input.addEventListener('change', function () {
				// Update active class.
				document.querySelectorAll('.telkari-design-option').forEach(function (opt) {
					opt.classList.remove('telkari-design-option--active');
				});
				this.closest('.telkari-design-option').classList.add('telkari-design-option--active');

				// Rebuild position button group for the selected design.
				updatePositionGroup(this.value);
			});
		});
	}

	/**
	 * Rebuild the position button group based on the selected design.
	 *
	 * @param {string} designId The selected design identifier.
	 */
	function updatePositionGroup(designId) {
		var group = document.getElementById('telkari-position-group');
		if (!group || !telkariAdmin.positions || !telkariAdmin.positionLabels) {
			return;
		}

		var positions = telkariAdmin.positions[designId] || [];
		var labels = telkariAdmin.positionLabels;

		// Get currently selected position.
		var currentRadio = group.querySelector('input:checked');
		var currentValue = currentRadio ? currentRadio.value : '';

		// If current selection is not in new positions, default to first.
		var selectedValue = positions.indexOf(currentValue) !== -1 ? currentValue : positions[0] || '';

		var html = '';
		positions.forEach(function (pos) {
			var isActive = pos === selectedValue;
			html += '<label class="telkari-btn-option' + (isActive ? ' telkari-btn-option--active' : '') + '">' +
				'<input type="radio" name="telkari_settings[active_position]" value="' + escapeHtml(pos) + '"' + (isActive ? ' checked' : '') + '>' +
				escapeHtml(labels[pos] || pos) +
				'</label>';
		});

		group.innerHTML = html;

		// Re-bind button group events for the new elements.
		var inputs = group.querySelectorAll('input');
		inputs.forEach(function (input) {
			input.addEventListener('change', function () {
				group.querySelectorAll('.telkari-btn-option').forEach(function (label) {
					label.classList.remove('telkari-btn-option--active');
				});
				this.closest('.telkari-btn-option').classList.add('telkari-btn-option--active');
			});
		});
	}

	/**
	 * Initialize WordPress color pickers and reset button.
	 */
	function initColorPickers() {
		if (typeof jQuery === 'undefined' || typeof jQuery.fn.wpColorPicker === 'undefined') {
			return;
		}

		jQuery('.telkari-color-picker').wpColorPicker();

		// Transparent toggle for wrapper background.
		var transparentCheckbox = document.getElementById('telkari-wrapper-bg-transparent');
		var wrapperPicker = document.getElementById('telkari-wrapper-bg-picker');
		var wrapperHidden = document.getElementById('telkari-wrapper-bg-hidden');

		if (transparentCheckbox && wrapperPicker && wrapperHidden) {
			var pickerWrap = jQuery(wrapperPicker).closest('.wp-picker-container');

			function syncTransparent() {
				if (transparentCheckbox.checked) {
					pickerWrap.hide();
					wrapperHidden.disabled = false;
					wrapperHidden.name = wrapperPicker.name;
					wrapperPicker.name = '';
				} else {
					pickerWrap.show();
					wrapperHidden.disabled = true;
					wrapperHidden.name = '';
					wrapperPicker.name = 'telkari_settings[platform_colors][wrapper_bg]';
				}
			}

			syncTransparent();
			transparentCheckbox.addEventListener('change', syncTransparent);
		}

		var resetBtn = document.getElementById('telkari-reset-colors');
		if (resetBtn) {
			resetBtn.addEventListener('click', function () {
				// Uncheck transparent if active.
				if (transparentCheckbox && transparentCheckbox.checked) {
					transparentCheckbox.checked = false;
					syncTransparent();
				}
				jQuery('.telkari-color-picker').each(function () {
					var $input = jQuery(this);
					var defaultColor = $input.data('default-color');
					if (defaultColor) {
						$input.wpColorPicker('color', defaultColor);
					}
				});
			});
		}
	}

	/**
	 * Sync range slider values with their output elements.
	 */
	function initRangeSliders() {
		var sliders = document.querySelectorAll('.telkari-range-input');

		sliders.forEach(function (slider) {
			var output = slider.parentElement.querySelector('.telkari-range-value');
			if (!output) {
				return;
			}
			slider.addEventListener('input', function () {
				output.textContent = this.value;
			});
		});
	}

	/**
	 * Toggle active state on button group labels.
	 */
	function initButtonGroups() {
		var groups = document.querySelectorAll('.telkari-btn-group');

		groups.forEach(function (group) {
			var inputs = group.querySelectorAll('input');
			inputs.forEach(function (input) {
				input.addEventListener('change', function () {
					group.querySelectorAll('.telkari-btn-option').forEach(function (label) {
						label.classList.remove('telkari-btn-option--active');
					});
					this.closest('.telkari-btn-option').classList.add('telkari-btn-option--active');
				});
			});
		});
	}

	/**
	 * Handle add and delete account buttons.
	 */
	function initAccountManagement() {
		var addBtn = document.getElementById('telkari-add-account-btn');

		if (addBtn) {
			addBtn.addEventListener('click', function (e) {
				e.preventDefault();
				addAccount();
			});
		}

		document.addEventListener('click', function (e) {
			if (e.target.classList.contains('telkari-delete-account')) {
				e.preventDefault();
				deleteAccount(e.target);
			}
		});
	}

	/**
	 * Add a new social account row from the form inputs.
	 */
	function addAccount() {
		var platformSelect = document.getElementById('telkari-new-platform');
		var urlInput = document.getElementById('telkari-new-url');

		if (!platformSelect || !urlInput) {
			return;
		}

		var platform = platformSelect.value;
		var url = urlInput.value.trim();

		if (!platform || !url) {
			alert(telkariAdmin.i18n.fillFields);
			return;
		}

		// Basic URL validation.
		try {
			new URL(url);
		} catch (err) {
			alert(telkariAdmin.i18n.fillFields);
			return;
		}

		var listEl = document.getElementById('telkari-accounts-list');
		var emptyState = document.getElementById('telkari-empty-state');

		if (emptyState) {
			emptyState.remove();
		}

		var existingRows = listEl.querySelectorAll('.telkari-account-row');
		var index = existingRows.length;
		var id = 'telkari_' + Date.now();
		var platformLabel = '';

		if (telkariAdmin.platforms && telkariAdmin.platforms[platform]) {
			platformLabel = telkariAdmin.platforms[platform].label;
		} else {
			platformLabel = platform;
		}

		var rowHTML = '<div class="telkari-account-row" data-id="' + id + '">' +
			'<span class="telkari-drag-handle dashicons dashicons-menu"></span>' +
			'<div class="telkari-account-info">' +
				'<strong class="telkari-account-platform">' + escapeHtml(platformLabel) + '</strong>' +
				'<span class="telkari-account-url">' + escapeHtml(url) + '</span>' +
			'</div>' +
			'<div class="telkari-account-actions">' +
				'<label class="telkari-toggle">' +
					'<input type="checkbox" name="telkari_settings[social_accounts][' + index + '][enabled]" value="1" checked>' +
					'<span class="telkari-toggle-label">' + escapeHtml(telkariAdmin.i18n.enabled) + '</span>' +
				'</label>' +
				'<button type="button" class="button telkari-delete-account">' + escapeHtml(telkariAdmin.i18n.delete) + '</button>' +
			'</div>' +
			'<input type="hidden" name="telkari_settings[social_accounts][' + index + '][id]" value="' + id + '">' +
			'<input type="hidden" name="telkari_settings[social_accounts][' + index + '][platform]" value="' + escapeHtml(platform) + '">' +
			'<input type="hidden" name="telkari_settings[social_accounts][' + index + '][url]" value="' + escapeHtml(url) + '">' +
			'<input type="hidden" name="telkari_settings[social_accounts][' + index + '][order]" value="' + index + '" class="telkari-order-field">' +
		'</div>';

		listEl.insertAdjacentHTML('beforeend', rowHTML);

		// Clear form.
		platformSelect.value = '';
		urlInput.value = '';
	}

	/**
	 * Delete an account row.
	 *
	 * @param {HTMLElement} btn The clicked delete button.
	 */
	function deleteAccount(btn) {
		if (!confirm(telkariAdmin.i18n.confirmDelete)) {
			return;
		}

		var row = btn.closest('.telkari-account-row');
		if (row) {
			row.remove();
			reindexAccountRows();
		}
	}

	/**
	 * Escape HTML entities for safe insertion.
	 *
	 * @param {string} str Input string.
	 * @return {string} Escaped string.
	 */
	function escapeHtml(str) {
		var div = document.createElement('div');
		div.appendChild(document.createTextNode(str));
		return div.innerHTML;
	}

})();
