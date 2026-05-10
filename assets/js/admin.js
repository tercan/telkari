/**
 * Telkari Admin JavaScript
 *
 * Handles SortableJS integration, design-position filtering,
 * social account management, and CTA builder interactions.
 *
 * @package Telkari
 */

(function () {
	'use strict';

	var currentEditingAccountRow = null;
	var currentEditingCtaRow = null;
	var ctaBuilderFeedbackTimeout = null;

	document.addEventListener('DOMContentLoaded', function () {
		initSortable();
		initDesignSelector();
		initAccountManagement();
		initCtaManagement();
		initColorPickers();
		initRangeSliders();
		initButtonGroups();
	});

	/**
	 * Initialize SortableJS on the accounts list.
	 */
	function initSortable() {
		if (typeof Sortable === 'undefined') {
			return;
		}

		document.querySelectorAll('.telkari-sortable-list').forEach(function (listEl) {
			Sortable.create(listEl, {
				handle: '.telkari-drag-handle',
				animation: 150,
				ghostClass: 'sortable-ghost',
				onEnd: function () {
					reindexCollectionRows(listEl);
					syncCollectionEmptyState(listEl);
				}
			});
		});
	}

	/**
	 * Re-index collection field names and order values after sort/add/delete.
	 *
	 * @param {HTMLElement} listEl Collection list element.
	 */
	function reindexCollectionRows(listEl) {
		if (!listEl) {
			return;
		}

		var collectionKey = listEl.getAttribute('data-collection-key');
		var rowSelector = listEl.getAttribute('data-row-selector') || '.telkari-account-row';

		if (!collectionKey) {
			return;
		}

		var rows = listEl.querySelectorAll(rowSelector);
		var fieldPattern = new RegExp(collectionKey + '\\]\\[\\d+\\]');

		rows.forEach(function (row, index) {
			var inputs = row.querySelectorAll('input[name*="' + collectionKey + '"]');
			inputs.forEach(function (input) {
				input.name = input.name.replace(fieldPattern, collectionKey + '][' + index + ']');
			});

			var orderField = row.querySelector('.telkari-order-field');
			if (orderField) {
				orderField.value = index;
			}
		});
	}

	/**
	 * Sync empty state visibility for collection lists.
	 *
	 * @param {HTMLElement} listEl Collection list element.
	 */
	function syncCollectionEmptyState(listEl) {
		if (!listEl) {
			return;
		}

		var rowSelector = listEl.getAttribute('data-row-selector') || '.telkari-account-row';
		var emptyStateId = listEl.getAttribute('data-empty-state-id');
		var emptyMessage = listEl.getAttribute('data-empty-message') || '';
		var rows = listEl.querySelectorAll(rowSelector);
		var emptyState = emptyStateId ? document.getElementById(emptyStateId) : null;

		if (!rows.length && !emptyState && emptyStateId && emptyMessage) {
			var emptyEl = document.createElement('p');
			emptyEl.className = 'telkari-empty-state';
			emptyEl.id = emptyStateId;
			emptyEl.textContent = emptyMessage;
			listEl.appendChild(emptyEl);
			return;
		}

		if (rows.length && emptyState) {
			emptyState.remove();
		}
	}

	/**
	 * Listen for design radio changes, update active class, and rebuild position selectors.
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

				// Rebuild position button groups for the selected design.
				updatePositionGroups(this.value);
			});
		});
	}

	/**
	 * Rebuild all position button groups based on the selected design.
	 *
	 * @param {string} designId The selected design identifier.
	 */
	function updatePositionGroups(designId) {
		var groups = document.querySelectorAll('.telkari-position-group');

		if (!groups.length || !telkariAdmin.positions || !telkariAdmin.positionLabels) {
			return;
		}

		groups.forEach(function (group) {
			rebuildPositionGroup(group, designId);
		});
	}

	/**
	 * Rebuild a single position button group based on the selected design.
	 *
	 * @param {HTMLElement} group Position group element.
	 * @param {string} designId Selected design identifier.
	 */
	function rebuildPositionGroup(group, designId) {
		var positions;
		var labels;
		var settingName;

		if (!group) {
			return;
		}

		positions = telkariAdmin.positions[designId] || [];
		labels = telkariAdmin.positionLabels;
		settingName = group.getAttribute('data-setting-name') || 'telkari_settings[active_position]';

		var currentRadio = group.querySelector('input:checked');
		var currentValue = currentRadio ? currentRadio.value : '';

		var selectedValue = positions.indexOf(currentValue) !== -1 ? currentValue : positions[0] || '';

		var html = '';
		positions.forEach(function (pos) {
			var isActive = pos === selectedValue;
			html += '<label class="telkari-btn-option' + (isActive ? ' telkari-btn-option--active' : '') + '">' +
				'<input type="radio" name="' + escapeHtml(settingName) + '" value="' + escapeHtml(pos) + '"' + (isActive ? ' checked' : '') + '>' +
				escapeHtml(labels[pos] || pos) +
				'</label>';
		});

		group.innerHTML = html;
		bindButtonGroupInputs(group);
	}

	/**
	 * Bind active-state syncing for button group radios.
	 *
	 * @param {HTMLElement} group Button group element.
	 */
	function bindButtonGroupInputs(group) {
		var inputs;

		if (!group) {
			return;
		}

		inputs = group.querySelectorAll('input');
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

		jQuery('.telkari-color-picker').each(function () {
			var $input = jQuery(this);

			$input.wpColorPicker({
				change: function () {
					window.setTimeout(function () {
						$input.trigger('input');
						$input.trigger('change');
					}, 0);
				},
				clear: function () {
					window.setTimeout(function () {
						$input.trigger('input');
						$input.trigger('change');
					}, 0);
				}
			});
		});

		if (document.querySelector('.telkari-cta-builder')) {
			syncCtaFormState();
		}

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
			bindButtonGroupInputs(group);
		});
	}

	/**
	 * Handle add and delete account buttons.
	 */
	function initAccountManagement() {
		var addBtn = document.getElementById('telkari-add-account-btn');
		var resetBtn = document.getElementById('telkari-reset-account-form');
		var accountFormElements = getAccountFormElements();
		var accountList = document.getElementById('telkari-accounts-list');

		if (!accountList && !accountFormElements.builder) {
			return;
		}

		accountList.querySelectorAll('.telkari-account-row').forEach(function (row) {
			syncAccountRowState(row);
		});

		if (addBtn) {
			addBtn.addEventListener('click', function (e) {
				e.preventDefault();
				addAccount();
			});
		}

		if (resetBtn) {
			resetBtn.addEventListener('click', function (e) {
				e.preventDefault();
				resetAccountForm();
			});
		}

		accountFormElements.platformInputs.forEach(function (input) {
			input.addEventListener('change', function () {
				if (accountFormElements.platformSelector) {
					accountFormElements.platformSelector.setAttribute('aria-invalid', 'false');
					accountFormElements.platformSelector.classList.remove('telkari-account-platform-selector--invalid');
				}

				syncAccountFormState();
			});
		});

		if (accountFormElements.urlInput) {
			accountFormElements.urlInput.addEventListener('input', function () {
				accountFormElements.urlInput.setAttribute('aria-invalid', 'false');
				syncAccountFormState();
			});

			accountFormElements.urlInput.addEventListener('change', function () {
				accountFormElements.urlInput.setAttribute('aria-invalid', 'false');
				syncAccountFormState();
			});
		}

		document.addEventListener('click', function (e) {
			var target = e.target;
			var editButton;
			var deleteButton;

			if (!target || typeof target.closest !== 'function') {
				return;
			}

			editButton = target.closest('.telkari-edit-account');
			if (editButton) {
				e.preventDefault();
				startEditingAccountRow(editButton.closest('.telkari-account-row'));
				return;
			}

			deleteButton = target.closest('.telkari-delete-account');
			if (deleteButton) {
				e.preventDefault();
				deleteCollectionRow(deleteButton, telkariAdmin.i18n.confirmDelete);
			}
		});

		document.addEventListener('change', function (e) {
			var target = e.target;

			if (!target || typeof target.closest !== 'function') {
				return;
			}

			if (target.matches('.telkari-account-row .telkari-toggle input[type="checkbox"]')) {
				syncAccountRowState(target.closest('.telkari-account-row'));
			}
		});

		setAccountFormMode(false);
		syncAccountFormState();
	}

	/**
	 * Handle add and delete CTA buttons.
	 */
	function initCtaManagement() {
		var builderElements = getCtaBuilderElements();

		document.querySelectorAll('.telkari-cta-row').forEach(function (row) {
			syncCtaRowState(row);
		});

		builderElements.typeInputs.forEach(function (input) {
			input.addEventListener('change', syncCtaFormState);
		});

		[
			builderElements.labelInput,
			builderElements.valueInput,
			builderElements.messageInput,
			builderElements.colorInput
		].forEach(function (field) {
			if (!field) {
				return;
			}

			field.addEventListener('input', syncCtaFormState);
			field.addEventListener('change', syncCtaFormState);
		});

		if (builderElements.addBtn) {
			builderElements.addBtn.addEventListener('click', function (e) {
				e.preventDefault();
				addCtaButton();
			});
		}

		if (builderElements.resetBtn) {
			builderElements.resetBtn.addEventListener('click', function (e) {
				e.preventDefault();
				resetCtaForm();
			});
		}

		document.addEventListener('click', function (e) {
			var target = e.target;
			var editButton;
			var deleteButton;

			if (!target || typeof target.closest !== 'function') {
				return;
			}

			editButton = target.closest('.telkari-edit-cta');
			if (editButton) {
				e.preventDefault();
				startEditingCtaRow(editButton.closest('.telkari-cta-row'));
				return;
			}

			deleteButton = target.closest('.telkari-delete-cta');
			if (deleteButton) {
				e.preventDefault();
				deleteCollectionRow(deleteButton, telkariAdmin.i18n.confirmDeleteCta);
			}
		});

		document.addEventListener('change', function (e) {
			var target = e.target;

			if (!target || typeof target.closest !== 'function') {
				return;
			}

			if (target.matches('.telkari-cta-row .telkari-toggle input[type="checkbox"]')) {
				syncCtaRowState(target.closest('.telkari-cta-row'));
			}
		});

		setCtaBuilderMode(false);
		syncCtaFormState();
	}

	/**
	 * Add a new social account row from the form inputs.
	 */
	function addAccount() {
		var accountFormElements = getAccountFormElements();
		var platform = getSelectedAccountPlatform();
		var urlInput = accountFormElements.urlInput;
		var listEl = document.getElementById('telkari-accounts-list');
		var existingAccount = currentEditingAccountRow ? extractAccountRowData(currentEditingAccountRow) : null;
		var targetRow = null;

		if (!urlInput || !listEl) {
			return;
		}

		var url = urlInput.value.trim();

		if (!platform || !url) {
			if (accountFormElements.platformSelector) {
				accountFormElements.platformSelector.setAttribute('aria-invalid', platform ? 'false' : 'true');
				accountFormElements.platformSelector.classList.toggle('telkari-account-platform-selector--invalid', !platform);
			}
			urlInput.setAttribute('aria-invalid', url ? 'false' : 'true');
			alert(telkariAdmin.i18n.fillFields);
			return;
		}

		// Basic URL validation.
		try {
			new URL(url);
		} catch (err) {
			urlInput.setAttribute('aria-invalid', 'true');
			alert(telkariAdmin.i18n.fillFields);
			return;
		}

		if (accountFormElements.platformSelector) {
			accountFormElements.platformSelector.setAttribute('aria-invalid', 'false');
			accountFormElements.platformSelector.classList.remove('telkari-account-platform-selector--invalid');
		}
		urlInput.setAttribute('aria-invalid', 'false');
		var existingRows = listEl.querySelectorAll('.telkari-account-row');
		var index = currentEditingAccountRow ? getCollectionRowIndex(currentEditingAccountRow, listEl) : existingRows.length;
		var accountData = {
			id: existingAccount && existingAccount.id ? existingAccount.id : 'telkari_' + Date.now(),
			platform: platform,
			url: url,
			enabled: existingAccount ? existingAccount.enabled : true,
			order: existingAccount ? existingAccount.order : index
		};
		var platformLabel = getPlatformLabel(platform);

		if (currentEditingAccountRow) {
			targetRow = currentEditingAccountRow;
			updateAccountRow(currentEditingAccountRow, accountData, platformLabel);
		} else {
			listEl.insertAdjacentHTML('beforeend', buildAccountRowHtml(accountData, index, platformLabel));
			targetRow = listEl.querySelectorAll('.telkari-account-row')[index] || null;
		}

		if (accountFormElements.status) {
			accountFormElements.status.textContent = '';
		}

		reindexCollectionRows(listEl);
		syncCollectionEmptyState(listEl);

		if (targetRow) {
			syncAccountRowState(targetRow);
		}

		resetAccountForm();

		if (targetRow) {
			flashCollectionRow(targetRow);
		}
	}

	/**
	 * Return the currently selected platform value.
	 *
	 * @return {string}
	 */
	function getSelectedAccountPlatform() {
		var selectedInput = document.querySelector('input[name="telkari-new-platform"]:checked');

		return selectedInput ? selectedInput.value : '';
	}

	/**
	 * Select a platform card by value.
	 *
	 * @param {string} platform Platform key.
	 */
	function setSelectedAccountPlatform(platform) {
		document.querySelectorAll('input[name="telkari-new-platform"]').forEach(function (input) {
			input.checked = input.value === platform;
		});
	}

	/**
	 * Return the placeholder/example URL for a platform selection.
	 *
	 * @param {string} platform Platform key.
	 * @return {string}
	 */
	function getAccountPlatformExampleUrl(platform) {
		var selectedInput = document.querySelector('input[name="telkari-new-platform"][value="' + escapeAttribute(platform) + '"]');

		if (!selectedInput) {
			return '';
		}

		return selectedInput.getAttribute('data-example-url') || '';
	}

	/**
	 * Sync the social account form placeholders, summary, and submit state.
	 */
	function syncAccountFormState() {
		var accountFormElements = getAccountFormElements();
		var platform = getSelectedAccountPlatform();
		var exampleUrl = getAccountPlatformExampleUrl(platform);
		var urlValue = accountFormElements.urlInput ? accountFormElements.urlInput.value.trim() : '';
		var defaultPlaceholder = '';
		var isValidUrl = false;

		if (!accountFormElements.builder || !accountFormElements.urlInput || !accountFormElements.addBtn) {
			return;
		}

		defaultPlaceholder = accountFormElements.urlInput.getAttribute('data-default-placeholder') || 'https://';
		accountFormElements.urlInput.placeholder = exampleUrl || defaultPlaceholder;

		if (urlValue) {
			try {
				new URL(urlValue);
				isValidUrl = true;
			} catch (err) {
				isValidUrl = false;
			}
		}

		accountFormElements.addBtn.disabled = !(platform && isValidUrl);
		syncAccountPlatformSummary(platform, getPlatformLabel(platform), exampleUrl);
	}

	/**
	 * Return key social account form elements used by create/edit flows.
	 *
	 * @return {Object}
	 */
	function getAccountFormElements() {
		return {
			builder: document.querySelector('.telkari-account-builder'),
			title: document.getElementById('telkari-account-builder-title'),
			description: document.getElementById('telkari-account-builder-description'),
			status: document.getElementById('telkari-account-builder-status'),
			platformSelector: document.getElementById('telkari-account-platform-selector'),
			platformInputs: document.querySelectorAll('input[name="telkari-new-platform"]'),
			urlInput: document.getElementById('telkari-new-url'),
			addBtn: document.getElementById('telkari-add-account-btn'),
			resetBtn: document.getElementById('telkari-reset-account-form'),
			platformSummary: document.getElementById('telkari-account-platform-summary'),
			platformSummaryIcon: document.getElementById('telkari-account-platform-summary-icon'),
			platformSummaryLabel: document.getElementById('telkari-account-platform-summary-label'),
			platformSummaryExample: document.getElementById('telkari-account-platform-summary-example')
		};
	}

	/**
	 * Toggle the social account form between create and edit mode.
	 *
	 * @param {boolean} isEditing Whether an existing row is being edited.
	 */
	function setAccountFormMode(isEditing) {
		var accountFormElements = getAccountFormElements();

		if (!accountFormElements.builder || !accountFormElements.title || !accountFormElements.status || !accountFormElements.addBtn || !accountFormElements.resetBtn) {
			return;
		}

		accountFormElements.builder.setAttribute('data-mode', isEditing ? 'edit' : 'create');
		accountFormElements.title.textContent = isEditing ? telkariAdmin.i18n.editAccountTitle : telkariAdmin.i18n.addNewAccount;
		if (accountFormElements.description) {
			accountFormElements.description.textContent = isEditing ? telkariAdmin.i18n.editAccountDescription : telkariAdmin.i18n.addAccountDescription;
		}
		accountFormElements.addBtn.textContent = isEditing ? telkariAdmin.i18n.saveAccount : telkariAdmin.i18n.addAccount;
		accountFormElements.resetBtn.textContent = isEditing ? telkariAdmin.i18n.cancelEdit : telkariAdmin.i18n.resetForm;
		accountFormElements.status.textContent = isEditing ? telkariAdmin.i18n.editingAccount : '';
		accountFormElements.status.hidden = !isEditing;
	}

	/**
	 * Start editing an existing social account row.
	 *
	 * @param {?HTMLElement} row Social account row.
	 */
	function startEditingAccountRow(row) {
		var accountFormElements = getAccountFormElements();
		var accountData;
		var platformLabel;

		if (!row || !accountFormElements.builder || !accountFormElements.urlInput) {
			return;
		}

		accountData = extractAccountRowData(row);
		platformLabel = getPlatformLabel(accountData.platform);
		setCurrentEditingAccountRow(row);
		setSelectedAccountPlatform(accountData.platform);
		accountFormElements.urlInput.value = accountData.url;
		if (accountFormElements.platformSelector) {
			accountFormElements.platformSelector.setAttribute('aria-invalid', 'false');
			accountFormElements.platformSelector.classList.remove('telkari-account-platform-selector--invalid');
		}
		accountFormElements.urlInput.setAttribute('aria-invalid', 'false');
		syncAccountFormState();

		if (accountFormElements.status && platformLabel) {
			accountFormElements.status.textContent = telkariAdmin.i18n.editingAccount + ': ' + platformLabel;
		}

		accountFormElements.builder.scrollIntoView({ block: 'nearest' });
		accountFormElements.urlInput.focus();
	}

	/**
	 * Mark the current social account row being edited and update form mode.
	 *
	 * @param {?HTMLElement} row Social account row.
	 */
	function setCurrentEditingAccountRow(row) {
		var activeEditButton;

		document.querySelectorAll('.telkari-edit-account').forEach(function (button) {
			button.classList.remove('telkari-edit-account--active');
			button.setAttribute('aria-pressed', 'false');
		});

		if (currentEditingAccountRow) {
			currentEditingAccountRow.classList.remove('telkari-account-row--editing');
		}

		currentEditingAccountRow = row || null;

		if (currentEditingAccountRow) {
			currentEditingAccountRow.classList.add('telkari-account-row--editing');
			activeEditButton = currentEditingAccountRow.querySelector('.telkari-edit-account');
			if (activeEditButton) {
				activeEditButton.classList.add('telkari-edit-account--active');
				activeEditButton.setAttribute('aria-pressed', 'true');
			}
		}

		setAccountFormMode(!!currentEditingAccountRow);
	}

	/**
	 * Reset the social account form back to its initial state.
	 */
	function resetAccountForm() {
		var accountFormElements = getAccountFormElements();

		setCurrentEditingAccountRow(null);

		setSelectedAccountPlatform('');

		if (accountFormElements.platformSelector) {
			accountFormElements.platformSelector.setAttribute('aria-invalid', 'false');
			accountFormElements.platformSelector.classList.remove('telkari-account-platform-selector--invalid');
		}

		if (accountFormElements.urlInput) {
			accountFormElements.urlInput.value = '';
			accountFormElements.urlInput.setAttribute('aria-invalid', 'false');
		}

		syncAccountFormState();
	}

	/**
	 * Extract social account data from an existing row.
	 *
	 * @param {HTMLElement} row Social account row.
	 * @return {Object}
	 */
	function extractAccountRowData(row) {
		var enabledInput = row ? row.querySelector('input[type="checkbox"]') : null;

		return {
			id: getHiddenRowFieldValue(row, 'id'),
			platform: getHiddenRowFieldValue(row, 'platform'),
			url: getHiddenRowFieldValue(row, 'url'),
			order: parseInt(getHiddenRowFieldValue(row, 'order'), 10) || 0,
			enabled: !!(enabledInput && enabledInput.checked)
		};
	}

	/**
	 * Build the HTML string for a social account row.
	 *
	 * @param {Object} account Social account data.
	 * @param {number} index Collection index.
	 * @param {string} platformLabel Visible platform label.
	 * @return {string}
	 */
	function buildAccountRowHtml(account, index, platformLabel) {
		var rowClasses = 'telkari-account-row' + (account.enabled ? '' : ' telkari-account-row--disabled');
		var editAriaLabel = getAccountActionAriaLabel(telkariAdmin.i18n.edit, platformLabel);
		var deleteAriaLabel = getAccountActionAriaLabel(telkariAdmin.i18n.delete, platformLabel);
		var enabledAriaLabel = getAccountActionAriaLabel(telkariAdmin.i18n.enabled, platformLabel);

		return '<div class="' + escapeAttribute(rowClasses) + '" data-id="' + escapeAttribute(account.id) + '">' +
			'<span class="telkari-drag-handle dashicons dashicons-menu" aria-hidden="true"></span>' +
			buildAccountIconHtml(account.platform) +
			'<div class="telkari-account-info">' +
				'<strong class="telkari-account-platform">' + escapeHtml(platformLabel) + '</strong>' +
				'<span class="telkari-account-url" title="' + escapeAttribute(account.url) + '">' + escapeHtml(account.url) + '</span>' +
			'</div>' +
			'<div class="telkari-account-actions">' +
				'<label class="telkari-toggle telkari-cta-toggle">' +
					'<input type="checkbox" name="telkari_settings[social_accounts][' + index + '][enabled]" value="1" aria-label="' + escapeAttribute(enabledAriaLabel) + '"' + (account.enabled ? ' checked' : '') + '>' +
					'<span class="telkari-cta-toggle-track" aria-hidden="true"><span class="telkari-cta-toggle-thumb"></span></span>' +
					'<span class="screen-reader-text">' + escapeHtml(enabledAriaLabel) + '</span>' +
				'</label>' +
				'<button type="button" class="telkari-cta-action-button telkari-cta-action-button--edit telkari-edit-account" aria-label="' + escapeAttribute(editAriaLabel) + '" aria-pressed="false"><span class="dashicons dashicons-edit" aria-hidden="true"></span></button>' +
				'<button type="button" class="telkari-cta-action-button telkari-cta-action-button--delete telkari-delete-account" aria-label="' + escapeAttribute(deleteAriaLabel) + '"><span class="dashicons dashicons-trash" aria-hidden="true"></span></button>' +
			'</div>' +
			'<input type="hidden" name="telkari_settings[social_accounts][' + index + '][id]" value="' + escapeAttribute(account.id) + '">' +
			'<input type="hidden" name="telkari_settings[social_accounts][' + index + '][platform]" value="' + escapeAttribute(account.platform) + '">' +
			'<input type="hidden" name="telkari_settings[social_accounts][' + index + '][url]" value="' + escapeAttribute(account.url) + '">' +
			'<input type="hidden" name="telkari_settings[social_accounts][' + index + '][order]" value="' + index + '" class="telkari-order-field">' +
		'</div>';
	}

	/**
	 * Update an existing social account row after editing.
	 *
	 * @param {HTMLElement} row Social account row.
	 * @param {Object} account Social account data.
	 * @param {string} platformLabel Visible platform label.
	 */
	function updateAccountRow(row, account, platformLabel) {
		var enabledInput = row.querySelector('input[type="checkbox"]');
		var iconEl = row.querySelector('.telkari-account-row-icon');
		var platformEl = row.querySelector('.telkari-account-platform');
		var urlEl = row.querySelector('.telkari-account-url');
		var editButton = row.querySelector('.telkari-edit-account');
		var deleteButton = row.querySelector('.telkari-delete-account');
		var enabledAriaLabel = getAccountActionAriaLabel(telkariAdmin.i18n.enabled, platformLabel);
		var editAriaLabel = getAccountActionAriaLabel(telkariAdmin.i18n.edit, platformLabel);
		var deleteAriaLabel = getAccountActionAriaLabel(telkariAdmin.i18n.delete, platformLabel);

		row.setAttribute('data-id', account.id);

		if (iconEl) {
			iconEl.className = 'telkari-account-row-icon ' + getAccountIconClass(account.platform);
			iconEl.innerHTML = '<span class="telkari-account-row-icon-glyph"></span>';
		}

		if (platformEl) {
			platformEl.textContent = platformLabel;
		}

		if (urlEl) {
			urlEl.textContent = account.url;
			urlEl.setAttribute('title', account.url);
		}

		if (enabledInput) {
			enabledInput.checked = !!account.enabled;
			enabledInput.setAttribute('aria-label', enabledAriaLabel);
		}

		if (editButton) {
			editButton.setAttribute('aria-label', editAriaLabel);
		}

		if (deleteButton) {
			deleteButton.setAttribute('aria-label', deleteAriaLabel);
		}

		setHiddenRowFieldValue(row, 'id', account.id);
		setHiddenRowFieldValue(row, 'platform', account.platform);
		setHiddenRowFieldValue(row, 'url', account.url);
		syncAccountRowState(row);
	}

	/**
	 * Return the visible label for a platform key.
	 *
	 * @param {string} platform Platform key.
	 * @return {string}
	 */
	function getPlatformLabel(platform) {
		if (telkariAdmin.platforms && telkariAdmin.platforms[platform]) {
			return telkariAdmin.platforms[platform].label;
		}

		return platform;
	}

	/**
	 * Return the modifier class for a social account icon.
	 *
	 * @param {string} platform Platform key.
	 * @return {string}
	 */
	function getAccountIconClass(platform) {
		return 'telkari-account-row-icon--' + (platform || 'default');
	}

	/**
	 * Return the row icon markup for a platform.
	 *
	 * @param {string} platform Platform key.
	 * @return {string}
	 */
	function buildAccountIconHtml(platform) {
		return '<span class="telkari-account-row-icon ' + escapeAttribute(getAccountIconClass(platform)) + '" aria-hidden="true"><span class="telkari-account-row-icon-glyph"></span></span>';
	}

	/**
	 * Build a compact aria label for icon-only account actions.
	 *
	 * @param {string} actionLabel Translated action label.
	 * @param {string} platformLabel Platform label.
	 * @return {string}
	 */
	function getAccountActionAriaLabel(actionLabel, platformLabel) {
		if (!platformLabel) {
			return actionLabel;
		}

		return actionLabel + ' ' + platformLabel;
	}

	/**
	 * Sync the selected platform summary card.
	 *
	 * @param {string} platform Platform key.
	 * @param {string} platformLabel Platform label.
	 * @param {string} exampleUrl Example public URL.
	 */
	function syncAccountPlatformSummary(platform, platformLabel, exampleUrl) {
		var accountFormElements = getAccountFormElements();

		if (!accountFormElements.platformSummary || !accountFormElements.platformSummaryIcon || !accountFormElements.platformSummaryLabel || !accountFormElements.platformSummaryExample) {
			return;
		}

		if (!platform) {
			accountFormElements.platformSummary.hidden = true;
			accountFormElements.platformSummaryIcon.className = 'telkari-account-platform-summary-icon';
			accountFormElements.platformSummaryIcon.innerHTML = '';
			accountFormElements.platformSummaryLabel.textContent = '';
			accountFormElements.platformSummaryExample.textContent = '';
			return;
		}

		accountFormElements.platformSummary.hidden = false;
		accountFormElements.platformSummaryIcon.className = 'telkari-account-platform-summary-icon ' + getAccountIconClass(platform);
		accountFormElements.platformSummaryIcon.innerHTML = '<span class="telkari-account-platform-summary-icon-glyph"></span>';
		accountFormElements.platformSummaryLabel.textContent = platformLabel;
		accountFormElements.platformSummaryExample.textContent = exampleUrl;
	}

	/**
	 * Add a new CTA button row from the form inputs.
	 */
	function addCtaButton() {
		var type = getSelectedCtaType();
		var builderElements = getCtaBuilderElements();
		var labelInput = builderElements.labelInput;
		var valueInput = builderElements.valueInput;
		var messageInput = builderElements.messageInput;
		var colorInput = builderElements.colorInput;
		var listEl = document.getElementById('telkari-cta-list');
		var existingButton = currentEditingCtaRow ? extractCtaRowData(currentEditingCtaRow) : null;
		var isEditingExistingButton = !!currentEditingCtaRow;
		var targetRow = null;

		if (!labelInput || !valueInput || !messageInput || !colorInput || !listEl) {
			return;
		}

		var label = labelInput.value.trim();
		var value = valueInput.value.trim();
		var message = messageInput.value.trim();
		var color = colorInput.value.trim();
		var normalizedCta = normalizeCtaInput(type, value, message);

		if (!normalizedCta) {
			alert(telkariAdmin.i18n.fillCtaFields);
			return;
		}

		var ctaType = telkariAdmin.ctaTypes && telkariAdmin.ctaTypes[type] ? telkariAdmin.ctaTypes[type] : null;
		var normalizedColor = normalizeCtaColorValue(color, ctaType);
		var existingRows = listEl.querySelectorAll('.telkari-cta-row');
		var index = currentEditingCtaRow ? getCollectionRowIndex(currentEditingCtaRow, listEl) : existingRows.length;
		var buttonData = {
			id: existingButton && existingButton.id ? existingButton.id : 'telkari_cta_' + Date.now(),
			type: type,
			label: label,
			value: normalizedCta.value,
			message: normalizedCta.message,
			url: normalizedCta.url,
			color: normalizedColor,
			enabled: existingButton ? existingButton.enabled : true,
			order: existingButton ? existingButton.order : index
		};

		if (currentEditingCtaRow) {
			targetRow = currentEditingCtaRow;
			updateCtaRow(targetRow, buttonData);
		} else {
			listEl.insertAdjacentHTML('beforeend', buildCtaRowHtml(buttonData, index, ctaType));
			targetRow = listEl.querySelectorAll('.telkari-cta-row')[index] || null;
		}

		reindexCollectionRows(listEl);
		syncCollectionEmptyState(listEl);

		if (targetRow) {
			syncCtaRowState(targetRow);
		}

		resetCtaForm();

		if (targetRow) {
			flashCollectionRow(targetRow);
		}

		showCtaBuilderFeedback(isEditingExistingButton ? telkariAdmin.i18n.ctaUpdatedFeedback : telkariAdmin.i18n.ctaAddedFeedback);
	}

	/**
	 * Sync CTA form placeholders, validation, and optional rows.
	 */
	function syncCtaFormState() {
		var type = getSelectedCtaType();
		var builderElements = getCtaBuilderElements();
		var addBtn = builderElements.addBtn;
		var labelInput = builderElements.labelInput;
		var valueInput = builderElements.valueInput;
		var messageRow = builderElements.messageRow;
		var messageInput = builderElements.messageInput;
		var errorOutput = builderElements.errorOutput;
		var feedbackOutput = builderElements.feedbackOutput;
		var colorInput = builderElements.colorInput;
		var hasValue;
		var errorMessage = '';
		var defaultLabelPlaceholder = '';
		var defaultValuePlaceholder = '';

		if (!addBtn || !labelInput || !valueInput || !messageRow || !messageInput || !errorOutput || !feedbackOutput || !colorInput) {
			return;
		}

		var ctaType = type && telkariAdmin.ctaTypes ? telkariAdmin.ctaTypes[type] : null;
		var supportsMessage = !!(ctaType && ctaType.supports_message);
		var normalizedCta;

		defaultLabelPlaceholder = labelInput.getAttribute('data-default-placeholder') || '';
		defaultValuePlaceholder = valueInput.getAttribute('data-default-placeholder') || '';

		messageRow.classList.toggle('telkari-add-form-row--hidden', !supportsMessage);
		if (!supportsMessage) {
			messageInput.value = '';
		}

		switch (type) {
			case 'whatsapp':
				valueInput.placeholder = '905551112233';
				valueInput.setAttribute('inputmode', 'tel');
				break;
			case 'phone':
				valueInput.placeholder = '+905551112233';
				valueInput.setAttribute('inputmode', 'tel');
				break;
			case 'email':
				valueInput.placeholder = 'info@example.com';
				valueInput.setAttribute('inputmode', 'email');
				break;
			case 'url':
				valueInput.placeholder = 'https://example.com/contact';
				valueInput.setAttribute('inputmode', 'url');
				break;
			default:
				valueInput.placeholder = defaultValuePlaceholder;
				valueInput.removeAttribute('inputmode');
		}

		labelInput.placeholder = ctaType ? ctaType.label : defaultLabelPlaceholder;

		setColorPickerDefaultColor(colorInput, ctaType && ctaType.default_color ? ctaType.default_color : '#1E293B');

		normalizedCta = type ? normalizeCtaInput(type, valueInput.value, messageInput.value) : null;
		hasValue = valueInput.value.trim() !== '';

		if (type && hasValue && !normalizedCta) {
			errorMessage = getCtaValidationErrorMessage(type);
			valueInput.setAttribute('aria-invalid', 'true');
			errorOutput.textContent = errorMessage;
			errorOutput.hidden = false;
		} else {
			valueInput.setAttribute('aria-invalid', 'false');
			errorOutput.textContent = '';
			errorOutput.hidden = true;
		}

		addBtn.disabled = !normalizedCta;
		syncCtaTypeSummary(type, ctaType, colorInput.value.trim());
		syncCtaBuilderStatus();
		updateCtaPreview(type, ctaType, labelInput.value.trim(), valueInput.value.trim(), messageInput.value.trim(), colorInput.value.trim(), normalizedCta);
	}

	/**
	 * Return the currently selected CTA type value.
	 *
	 * @return {string}
	 */
	function getSelectedCtaType() {
		var selectedInput = document.querySelector('input[name="telkari-new-cta-type"]:checked');

		return selectedInput ? selectedInput.value : '';
	}

	/**
	 * Select a CTA type card by value.
	 *
	 * @param {string} type CTA type.
	 */
	function setSelectedCtaType(type) {
		document.querySelectorAll('input[name="telkari-new-cta-type"]').forEach(function (input) {
			input.checked = input.value === type;
		});
	}

	/**
	 * Return key CTA builder elements used by create/edit flows.
	 *
	 * @return {Object}
	 */
	function getCtaBuilderElements() {
		return {
			builder: document.querySelector('.telkari-cta-builder'),
			title: document.getElementById('telkari-cta-builder-title'),
			description: document.getElementById('telkari-cta-builder-description'),
			status: document.getElementById('telkari-cta-builder-status'),
			feedbackOutput: document.getElementById('telkari-cta-builder-feedback'),
			addBtn: document.getElementById('telkari-add-cta-btn'),
			resetBtn: document.getElementById('telkari-reset-cta-form'),
			typeInputs: document.querySelectorAll('input[name="telkari-new-cta-type"]'),
			labelInput: document.getElementById('telkari-new-cta-label'),
			valueInput: document.getElementById('telkari-new-cta-value'),
			messageRow: document.getElementById('telkari-cta-message-row'),
			messageInput: document.getElementById('telkari-new-cta-message'),
			errorOutput: document.getElementById('telkari-cta-value-error'),
			colorInput: document.getElementById('telkari-new-cta-color'),
			typeSummary: document.getElementById('telkari-cta-type-summary'),
			typeSummaryIcon: document.getElementById('telkari-cta-type-summary-icon'),
			typeSummaryLabel: document.getElementById('telkari-cta-type-summary-label'),
			typeSummaryDescription: document.getElementById('telkari-cta-type-summary-description'),
			typeSummaryExample: document.getElementById('telkari-cta-type-summary-example'),
			typeSummarySwatch: document.getElementById('telkari-cta-type-summary-swatch'),
			colorState: document.getElementById('telkari-cta-color-state'),
			colorStateSwatch: document.getElementById('telkari-cta-color-state-swatch'),
			colorStateValue: document.getElementById('telkari-cta-color-state-value')
		};
	}

	/**
	 * Return the visible validation message for the current CTA type.
	 *
	 * @param {string} type CTA type.
	 * @return {string}
	 */
	function getCtaValidationErrorMessage(type) {
		switch (type) {
			case 'whatsapp':
				return telkariAdmin.i18n.ctaErrorWhatsapp;
			case 'phone':
				return telkariAdmin.i18n.ctaErrorPhone;
			case 'email':
				return telkariAdmin.i18n.ctaErrorEmail;
			case 'url':
				return telkariAdmin.i18n.ctaErrorUrl;
		}

		return '';
	}

	/**
	 * Toggle CTA builder mode between create and edit.
	 *
	 * @param {boolean} isEditing Whether the builder is editing an existing row.
	 */
	function setCtaBuilderMode(isEditing) {
		var builderElements = getCtaBuilderElements();

		if (!builderElements.builder || !builderElements.title || !builderElements.status || !builderElements.addBtn || !builderElements.resetBtn) {
			return;
		}

		builderElements.builder.setAttribute('data-mode', isEditing ? 'edit' : 'create');
		builderElements.title.textContent = isEditing ? telkariAdmin.i18n.editCtaTitle : telkariAdmin.i18n.addCtaTitle;
		if (builderElements.description) {
			builderElements.description.textContent = isEditing ? telkariAdmin.i18n.editCtaDescription : telkariAdmin.i18n.addCtaDescription;
		}
		builderElements.addBtn.textContent = isEditing
			? telkariAdmin.i18n.saveCtaButton
			: (telkariAdmin.i18n.addCtaButton || 'Add CTA Button');
		builderElements.resetBtn.textContent = isEditing ? telkariAdmin.i18n.cancelEdit : telkariAdmin.i18n.resetForm;
		syncCtaBuilderStatus();
	}

	/**
	 * Start editing an existing CTA row.
	 *
	 * @param {?HTMLElement} row CTA row element.
	 */
	function startEditingCtaRow(row) {
		var builderElements = getCtaBuilderElements();
		var buttonData;

		if (!row || !builderElements.builder || !builderElements.labelInput || !builderElements.valueInput || !builderElements.messageInput || !builderElements.colorInput) {
			return;
		}

		buttonData = extractCtaRowData(row);
		hideCtaBuilderFeedback();
		setCurrentEditingCtaRow(row);
		setSelectedCtaType(buttonData.type);
		builderElements.labelInput.value = buttonData.label;
		builderElements.valueInput.value = buttonData.value;
		builderElements.messageInput.value = buttonData.message;
		setColorPickerValue(builderElements.colorInput, buttonData.color);

		syncCtaFormState();
		builderElements.builder.scrollIntoView({ block: 'nearest' });
		builderElements.valueInput.focus();
	}

	/**
	 * Mark the current row being edited and update builder mode.
	 *
	 * @param {?HTMLElement} row CTA row element.
	 */
	function setCurrentEditingCtaRow(row) {
		var activeEditButton;

		document.querySelectorAll('.telkari-edit-cta').forEach(function (button) {
			button.classList.remove('telkari-edit-cta--active');
			button.setAttribute('aria-pressed', 'false');
		});

		if (currentEditingCtaRow) {
			currentEditingCtaRow.classList.remove('telkari-cta-row--editing');
		}

		currentEditingCtaRow = row || null;

		if (currentEditingCtaRow) {
			currentEditingCtaRow.classList.add('telkari-cta-row--editing');
			activeEditButton = currentEditingCtaRow.querySelector('.telkari-edit-cta');
			if (activeEditButton) {
				activeEditButton.classList.add('telkari-edit-cta--active');
				activeEditButton.setAttribute('aria-pressed', 'true');
			}
		}

		setCtaBuilderMode(!!currentEditingCtaRow);
	}

	/**
	 * Sync the CTA builder edit status text.
	 */
	function syncCtaBuilderStatus() {
		var builderElements = getCtaBuilderElements();
		var selectedType;
		var ctaType;
		var statusLabel;

		if (!builderElements.status) {
			return;
		}

		if (!currentEditingCtaRow) {
			builderElements.status.textContent = '';
			builderElements.status.hidden = true;
			return;
		}

		selectedType = getSelectedCtaType();
		ctaType = selectedType && telkariAdmin.ctaTypes ? telkariAdmin.ctaTypes[selectedType] : null;
		statusLabel = builderElements.labelInput && builderElements.labelInput.value.trim()
			? builderElements.labelInput.value.trim()
			: (ctaType ? ctaType.label : selectedType);

		builderElements.status.textContent = statusLabel
			? telkariAdmin.i18n.editingCta + ': ' + statusLabel
			: telkariAdmin.i18n.editingCta;
		builderElements.status.hidden = false;
	}

	/**
	 * Show a temporary CTA builder success message.
	 *
	 * @param {string} message Feedback message.
	 */
	function showCtaBuilderFeedback(message) {
		var builderElements = getCtaBuilderElements();

		if (!builderElements.feedbackOutput || !message) {
			return;
		}

		if (ctaBuilderFeedbackTimeout) {
			window.clearTimeout(ctaBuilderFeedbackTimeout);
		}

		builderElements.feedbackOutput.textContent = message;
		builderElements.feedbackOutput.hidden = false;
		builderElements.feedbackOutput.setAttribute('data-state', 'success');

		ctaBuilderFeedbackTimeout = window.setTimeout(function () {
			hideCtaBuilderFeedback();
		}, 2000);
	}

	/**
	 * Hide the temporary CTA builder feedback region.
	 */
	function hideCtaBuilderFeedback() {
		var builderElements = getCtaBuilderElements();

		if (ctaBuilderFeedbackTimeout) {
			window.clearTimeout(ctaBuilderFeedbackTimeout);
			ctaBuilderFeedbackTimeout = null;
		}

		if (!builderElements.feedbackOutput) {
			return;
		}

		builderElements.feedbackOutput.textContent = '';
		builderElements.feedbackOutput.hidden = true;
		builderElements.feedbackOutput.removeAttribute('data-state');
	}

	/**
	 * Sync disabled styling for a CTA row from its enabled checkbox state.
	 *
	 * @param {?HTMLElement} row CTA row element.
	 */
	function syncCtaRowState(row) {
		var enabledInput;
		var isEnabled;

		if (!row) {
			return;
		}

		enabledInput = row.querySelector('.telkari-toggle input[type="checkbox"]');
		isEnabled = !enabledInput || enabledInput.checked;
		row.classList.toggle('telkari-cta-row--disabled', !isEnabled);
	}

	/**
	 * Sync disabled styling for a social account row from its enabled checkbox state.
	 *
	 * @param {?HTMLElement} row Social account row element.
	 */
	function syncAccountRowState(row) {
		var enabledInput;
		var isEnabled;

		if (!row) {
			return;
		}

		enabledInput = row.querySelector('.telkari-toggle input[type="checkbox"]');
		isEnabled = !enabledInput || enabledInput.checked;
		row.classList.toggle('telkari-account-row--disabled', !isEnabled);
	}

	/**
	 * Flash a collection row after add or update.
	 *
	 * @param {?HTMLElement} row Collection row element.
	 */
	function flashCollectionRow(row) {
		if (!row) {
			return;
		}

		row.classList.remove('telkari-row-flash');

		if (row._telkariFlashTimeout) {
			window.clearTimeout(row._telkariFlashTimeout);
		}

		// Force reflow so repeated flashes restart the animation cleanly.
		void row.offsetWidth;
		row.classList.add('telkari-row-flash');
		row._telkariFlashTimeout = window.setTimeout(function () {
			row.classList.remove('telkari-row-flash');
		}, 1500);
	}

	/**
	 * Extract CTA data from an existing row.
	 *
	 * @param {HTMLElement} row CTA row element.
	 * @return {Object}
	 */
	function extractCtaRowData(row) {
		var enabledInput = row ? row.querySelector('input[type="checkbox"]') : null;

		return {
			id: getCtaRowFieldValue(row, 'id'),
			type: getCtaRowFieldValue(row, 'type'),
			label: getCtaRowFieldValue(row, 'label'),
			value: getCtaRowFieldValue(row, 'value'),
			message: getCtaRowFieldValue(row, 'message'),
			url: getCtaRowFieldValue(row, 'url'),
			color: getCtaRowFieldValue(row, 'color'),
			order: parseInt(getCtaRowFieldValue(row, 'order'), 10) || 0,
			enabled: !!(enabledInput && enabledInput.checked)
		};
	}

	/**
	 * Read a hidden field value from a collection row.
	 *
	 * @param {?HTMLElement} row Collection row element.
	 * @param {string} field Field name.
	 * @return {string}
	 */
	function getHiddenRowFieldValue(row, field) {
		var fieldSuffix = '[' + field + ']';
		var value = '';

		if (!row) {
			return '';
		}

		row.querySelectorAll('input[type="hidden"]').forEach(function (input) {
			if (input.name && input.name.slice(-fieldSuffix.length) === fieldSuffix) {
				value = input.value;
			}
		});

		return value;
	}

	/**
	 * Write a hidden field value back into a collection row.
	 *
	 * @param {HTMLElement} row Collection row element.
	 * @param {string} field Field name.
	 * @param {string} value Field value.
	 */
	function setHiddenRowFieldValue(row, field, value) {
		var fieldSuffix = '[' + field + ']';

		if (!row) {
			return;
		}

		row.querySelectorAll('input[type="hidden"]').forEach(function (input) {
			if (input.name && input.name.slice(-fieldSuffix.length) === fieldSuffix) {
				input.value = value;
			}
		});
	}

	/**
	 * Read a hidden CTA field value from a row.
	 *
	 * @param {?HTMLElement} row CTA row element.
	 * @param {string} field Field name.
	 * @return {string}
	 */
	function getCtaRowFieldValue(row, field) {
		return getHiddenRowFieldValue(row, field);
	}

	/**
	 * Write a hidden CTA field value back into a row.
	 *
	 * @param {HTMLElement} row CTA row element.
	 * @param {string} field Field name.
	 * @param {string} value Field value.
	 */
	function setCtaRowFieldValue(row, field, value) {
		setHiddenRowFieldValue(row, field, value);
	}

	/**
	 * Return the collection index for a row inside its list.
	 *
	 * @param {HTMLElement} row CTA row element.
	 * @param {HTMLElement} listEl CTA list element.
	 * @return {number}
	 */
	function getCollectionRowIndex(row, listEl) {
		var rows;
		var rowSelector;

		if (!row || !listEl) {
			return 0;
		}

		rowSelector = listEl.getAttribute('data-row-selector') || '.telkari-account-row';
		rows = Array.prototype.slice.call(listEl.querySelectorAll(rowSelector));

		return Math.max(rows.indexOf(row), 0);
	}

	/**
	 * Return the CTA icon modifier class for a type.
	 *
	 * @param {string} type CTA type key.
	 * @return {string}
	 */
	function getCtaIconModifierClass(type) {
		switch (type) {
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
	 * Return SVG markup for a CTA type icon.
	 *
	 * @param {string} type CTA type key.
	 * @return {string}
	 */
	function getCtaIconSvg(type) {
		switch (type) {
			case 'whatsapp':
				return '<svg class="telkari-cta-admin-icon-svg" viewBox="0 0 448 512" fill="currentColor" aria-hidden="true" focusable="false"><path d="M380.9 97.1C339 55.1 283.2 32 223.9 32c-122.4 0-222 99.6-222 222 0 39.1 10.2 77.3 29.6 111L0 480l117.7-30.9c32.4 17.7 68.9 27 106.1 27h.1c122.3 0 224.1-99.6 224.1-222 0-59.3-25.2-115-67.1-157zm-157 341.6c-33.2 0-65.7-8.9-94-25.7l-6.7-4-69.8 18.3L72 359.2l-4.4-7c-18.5-29.4-28.2-63.3-28.2-98.2 0-101.7 82.8-184.5 184.6-184.5 49.3 0 95.6 19.2 130.4 54.1 34.8 34.9 56.2 81.2 56.1 130.5 0 101.8-84.9 184.6-186.6 184.6zm101.2-138.2c-5.5-2.8-32.8-16.2-37.9-18-5.1-1.9-8.8-2.8-12.5 2.8-3.7 5.6-14.3 18-17.6 21.8-3.2 3.7-6.5 4.2-12 1.4-32.6-16.3-54-29.1-75.5-66-5.7-9.8 5.7-9.1 16.3-30.3 1.8-3.7.9-6.9-.5-9.7-1.4-2.8-12.5-30.1-17.1-41.2-4.5-10.8-9.1-9.3-12.5-9.5-3.2-.2-6.9-.2-10.6-.2-3.7 0-9.7 1.4-14.8 6.9-5.1 5.6-19.4 19-19.4 46.3 0 27.3 19.9 53.7 22.6 57.4 2.8 3.7 39.1 59.7 94.8 83.8 35.2 15.2 49 16.5 66.6 13.9 10.7-1.6 32.8-13.4 37.4-26.4 4.6-13 4.6-24.1 3.2-26.4-1.3-2.5-5-3.9-10.5-6.6z"></path></svg>';
			case 'phone':
				return '<svg class="telkari-cta-admin-icon-svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3 19.4 19.4 0 0 1-6-6 19.8 19.8 0 0 1-3-8.7A2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7c.1.9.4 1.8.7 2.6a2 2 0 0 1-.4 2.1L8.2 9.8a16 16 0 0 0 6 6l1.4-1.2a2 2 0 0 1 2.1-.4c.8.3 1.7.6 2.6.7A2 2 0 0 1 22 16.9Z"></path></svg>';
			case 'email':
				return '<svg class="telkari-cta-admin-icon-svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><rect x="3" y="5" width="18" height="14" rx="2" ry="2"></rect><path d="m3 7 9 6 9-6"></path></svg>';
			case 'url':
			default:
				return '<svg class="telkari-cta-admin-icon-svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M14 3h7v7"></path><path d="M10 14 21 3"></path><path d="M19 14v5a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V7a2 2 0 0 1 2-2h5"></path></svg>';
		}
	}

	/**
	 * Build a compact aria label from an existing translated action label.
	 *
	 * @param {string} actionLabel Translated action label.
	 * @param {string} buttonLabel CTA button label.
	 * @return {string}
	 */
	function getCtaActionAriaLabel(actionLabel, buttonLabel) {
		var compactLabel = (buttonLabel || '').trim();

		if (!compactLabel) {
			return actionLabel;
		}

		return (actionLabel + ' ' + compactLabel).trim();
	}

	/**
	 * Build the HTML string for a CTA row.
	 *
	 * @param {Object} button CTA button data.
	 * @param {number} index Collection index.
	 * @param {?Object} ctaType CTA type config.
	 * @return {string}
	 */
	function buildCtaRowHtml(button, index, ctaType) {
		var typeLabel = ctaType ? ctaType.label : button.type;
		var buttonLabel = button.label || typeLabel;
		var rowClasses = 'telkari-account-row telkari-cta-row' + (button.enabled ? '' : ' telkari-cta-row--disabled');
		var valueText = button.value || '';
		var messageText = button.message || '';
		var iconClass = getCtaIconModifierClass(button.type);
		var iconHtml = getCtaIconSvg(button.type);
		var editAriaLabel = getCtaActionAriaLabel(telkariAdmin.i18n.edit, buttonLabel);
		var deleteAriaLabel = getCtaActionAriaLabel(telkariAdmin.i18n.delete, buttonLabel);
		var enabledAriaLabel = getCtaActionAriaLabel(telkariAdmin.i18n.enabled, buttonLabel);
		var messageHtml = messageText ? '<span class="telkari-cta-message" title="' + escapeAttribute(messageText) + '">' + escapeHtml(messageText) + '</span>' : '';

		return '<div class="' + rowClasses + '" data-id="' + escapeAttribute(button.id) + '">' +
			'<span class="telkari-drag-handle dashicons dashicons-menu" aria-hidden="true"></span>' +
			'<span class="telkari-cta-row-icon ' + iconClass + '" aria-hidden="true">' + iconHtml + '</span>' +
			'<div class="telkari-account-info">' +
				'<div class="telkari-cta-heading">' +
					'<strong class="telkari-account-platform">' + escapeHtml(buttonLabel) + '</strong>' +
				'</div>' +
				'<span class="telkari-account-url" title="' + escapeAttribute(valueText) + '">' + escapeHtml(valueText) + '</span>' +
				messageHtml +
			'</div>' +
			'<div class="telkari-account-actions">' +
				'<label class="telkari-toggle telkari-cta-toggle">' +
					'<input type="checkbox" name="telkari_settings[cta_buttons][' + index + '][enabled]" value="1" aria-label="' + escapeAttribute(enabledAriaLabel) + '"' + (button.enabled ? ' checked' : '') + '>' +
					'<span class="telkari-cta-toggle-track" aria-hidden="true"><span class="telkari-cta-toggle-thumb"></span></span>' +
					'<span class="screen-reader-text">' + escapeHtml(enabledAriaLabel) + '</span>' +
				'</label>' +
				'<button type="button" class="telkari-cta-action-button telkari-cta-action-button--edit telkari-edit-cta" aria-label="' + escapeAttribute(editAriaLabel) + '" aria-pressed="false">' +
					'<span class="dashicons dashicons-edit" aria-hidden="true"></span>' +
				'</button>' +
				'<button type="button" class="telkari-cta-action-button telkari-cta-action-button--delete telkari-delete-cta" aria-label="' + escapeAttribute(deleteAriaLabel) + '">' +
					'<span class="dashicons dashicons-trash" aria-hidden="true"></span>' +
				'</button>' +
			'</div>' +
			'<input type="hidden" name="telkari_settings[cta_buttons][' + index + '][id]" value="' + escapeAttribute(button.id) + '">' +
			'<input type="hidden" name="telkari_settings[cta_buttons][' + index + '][type]" value="' + escapeAttribute(button.type) + '">' +
			'<input type="hidden" name="telkari_settings[cta_buttons][' + index + '][label]" value="' + escapeAttribute(button.label) + '">' +
			'<input type="hidden" name="telkari_settings[cta_buttons][' + index + '][value]" value="' + escapeAttribute(button.value) + '">' +
			'<input type="hidden" name="telkari_settings[cta_buttons][' + index + '][message]" value="' + escapeAttribute(button.message) + '">' +
			'<input type="hidden" name="telkari_settings[cta_buttons][' + index + '][url]" value="' + escapeAttribute(button.url) + '">' +
			'<input type="hidden" name="telkari_settings[cta_buttons][' + index + '][color]" value="' + escapeAttribute(button.color) + '">' +
			'<input type="hidden" name="telkari_settings[cta_buttons][' + index + '][order]" value="' + index + '" class="telkari-order-field">' +
		'</div>';
	}

	/**
	 * Update an existing CTA row after editing.
	 *
	 * @param {HTMLElement} row CTA row element.
	 * @param {Object} button CTA button data.
	 */
	function updateCtaRow(row, button) {
		var ctaType = telkariAdmin.ctaTypes && telkariAdmin.ctaTypes[button.type] ? telkariAdmin.ctaTypes[button.type] : null;
		var typeLabel = ctaType ? ctaType.label : button.type;
		var buttonLabel = button.label || typeLabel;
		var iconClass = getCtaIconModifierClass(button.type);
		var iconHtml = getCtaIconSvg(button.type);
		var editAriaLabel = getCtaActionAriaLabel(telkariAdmin.i18n.edit, buttonLabel);
		var deleteAriaLabel = getCtaActionAriaLabel(telkariAdmin.i18n.delete, buttonLabel);
		var enabledAriaLabel = getCtaActionAriaLabel(telkariAdmin.i18n.enabled, buttonLabel);
		var messageEl = row.querySelector('.telkari-cta-message');
		var enabledInput = row.querySelector('input[type="checkbox"]');
		var headingLabel = row.querySelector('.telkari-account-platform');
		var valueEl = row.querySelector('.telkari-account-url');
		var iconEl = row.querySelector('.telkari-cta-row-icon');
		var editButton = row.querySelector('.telkari-edit-cta');
		var deleteButton = row.querySelector('.telkari-delete-cta');
		var toggleText = row.querySelector('.telkari-cta-toggle .screen-reader-text');

		row.setAttribute('data-id', button.id);

		if (iconEl) {
			iconEl.className = 'telkari-cta-row-icon ' + iconClass;
			iconEl.innerHTML = iconHtml;
		}

		if (headingLabel) {
			headingLabel.textContent = buttonLabel;
		}

		if (valueEl) {
			valueEl.textContent = button.value;
			valueEl.setAttribute('title', button.value);
		}

		if (button.message) {
			if (!messageEl) {
				messageEl = document.createElement('span');
				messageEl.className = 'telkari-cta-message';
				row.querySelector('.telkari-account-info').appendChild(messageEl);
			}

			messageEl.textContent = button.message;
			messageEl.setAttribute('title', button.message);
		} else if (messageEl) {
			messageEl.remove();
		}

		if (enabledInput) {
			enabledInput.checked = !!button.enabled;
			enabledInput.setAttribute('aria-label', enabledAriaLabel);
		}

		if (toggleText) {
			toggleText.textContent = enabledAriaLabel;
		}

		if (editButton) {
			editButton.setAttribute('aria-label', editAriaLabel);
		}

		if (deleteButton) {
			deleteButton.setAttribute('aria-label', deleteAriaLabel);
		}

		setCtaRowFieldValue(row, 'id', button.id);
		setCtaRowFieldValue(row, 'type', button.type);
		setCtaRowFieldValue(row, 'label', button.label);
		setCtaRowFieldValue(row, 'value', button.value);
		setCtaRowFieldValue(row, 'message', button.message);
		setCtaRowFieldValue(row, 'url', button.url);
		setCtaRowFieldValue(row, 'color', button.color);
		syncCtaRowState(row);
	}

	/**
	 * Sync the visible color picker state with a raw color value.
	 *
	 * @param {?HTMLElement} colorInput Color input.
	 * @param {string} color Raw color value.
	 */
	function setColorPickerValue(colorInput, color) {
		var $colorInput;

		if (!colorInput) {
			return;
		}

		colorInput.value = color || '';

		if (typeof jQuery !== 'undefined' && typeof jQuery.fn.wpColorPicker === 'function') {
			$colorInput = jQuery(colorInput);
			if ($colorInput.data('wpWpColorPicker')) {
				$colorInput.wpColorPicker('color', color || '');
			}
		}
	}

	/**
	 * Sync the dynamic default color used by the WordPress color picker.
	 *
	 * @param {?HTMLElement} colorInput Color input.
	 * @param {string} defaultColor Effective CTA default color.
	 */
	function setColorPickerDefaultColor(colorInput, defaultColor) {
		var normalizedDefaultColor = isHexColor(defaultColor) ? defaultColor : '#1E293B';
		var $colorInput;

		if (!colorInput) {
			return;
		}

		colorInput.setAttribute('data-default-color', normalizedDefaultColor);

		if (typeof jQuery !== 'undefined' && typeof jQuery.fn.wpColorPicker === 'function') {
			$colorInput = jQuery(colorInput);
			if ($colorInput.data('wpWpColorPicker')) {
				$colorInput.wpColorPicker('defaultColor', normalizedDefaultColor);
			}
		}
	}

	/**
	 * Sync the selected CTA type summary and effective color readout.
	 *
	 * @param {string} type Current CTA type key.
	 * @param {?Object} ctaType Current CTA type definition.
	 * @param {string} rawColor Raw custom color value.
	 */
	function syncCtaTypeSummary(type, ctaType, rawColor) {
		var builderElements = getCtaBuilderElements();
		var summary = builderElements.typeSummary;
		var summaryIcon = builderElements.typeSummaryIcon;
		var summaryLabel = builderElements.typeSummaryLabel;
		var summaryDescription = builderElements.typeSummaryDescription;
		var summaryExample = builderElements.typeSummaryExample;
		var summarySwatch = builderElements.typeSummarySwatch;
		var colorState = builderElements.colorState;
		var colorStateSwatch = builderElements.colorStateSwatch;
		var colorStateValue = builderElements.colorStateValue;
		var resolvedColor;
		var hasCustomColor;

		if (!summary || !summaryIcon || !summaryLabel || !summaryDescription || !summaryExample || !summarySwatch || !colorState || !colorStateSwatch || !colorStateValue) {
			return;
		}

		if (!type || !ctaType) {
			summary.hidden = true;
			summaryIcon.className = 'telkari-cta-type-summary-icon';
			summaryIcon.innerHTML = '';
			summaryLabel.textContent = '';
			summaryDescription.textContent = '';
			summaryExample.textContent = '';
			summarySwatch.style.backgroundColor = '';
			colorState.hidden = true;
			colorStateValue.textContent = '';
			colorStateSwatch.style.backgroundColor = '';
			colorState.removeAttribute('data-color-source');
			syncCtaColorPickerUi(builderElements.colorInput, '', false);
			return;
		}

		resolvedColor = getResolvedCtaColor(rawColor, ctaType);
		hasCustomColor = isCustomCtaColor(rawColor, ctaType);

		summary.hidden = false;
		summaryIcon.className = 'telkari-cta-type-summary-icon ' + getCtaIconModifierClass(type);
		summaryIcon.innerHTML = getCtaIconSvg(type);
		summaryLabel.textContent = ctaType.label || '';
		summaryDescription.textContent = ctaType.description || '';
		summaryExample.textContent = ctaType.example || '';
		summarySwatch.style.backgroundColor = ctaType.default_color || resolvedColor;

		colorState.hidden = false;
		colorState.setAttribute('data-color-source', hasCustomColor ? 'custom' : 'default');
		colorStateValue.textContent = resolvedColor.toUpperCase();
		colorStateSwatch.style.backgroundColor = resolvedColor;
		syncCtaColorPickerUi(builderElements.colorInput, resolvedColor, hasCustomColor);
	}

	/**
	 * Sync the visible WordPress color picker toggle with the effective CTA color.
	 *
	 * @param {?HTMLElement} colorInput Color input.
	 * @param {string} resolvedColor Effective color shown in the UI.
	 * @param {boolean} hasCustomColor Whether the picker currently uses a custom value.
	 */
	function syncCtaColorPickerUi(colorInput, resolvedColor, hasCustomColor) {
		var pickerContainer;
		var toggleButton;
		var toggleText;
		var defaultLabel = '';

		if (!colorInput) {
			return;
		}

		pickerContainer = colorInput.closest('.wp-picker-container');
		if (!pickerContainer) {
			return;
		}

		toggleButton = pickerContainer.querySelector('.wp-color-result');
		if (!toggleButton) {
			return;
		}

		toggleText = toggleButton.querySelector('.wp-color-result-text');
		if (toggleText) {
			defaultLabel = toggleText.getAttribute('data-default-label') || toggleText.textContent || '';
			toggleText.setAttribute('data-default-label', defaultLabel);
			toggleText.textContent = resolvedColor ? resolvedColor.toUpperCase() : defaultLabel;
		}

		toggleButton.setAttribute('data-color-source', resolvedColor ? (hasCustomColor ? 'custom' : 'default') : 'empty');
		toggleButton.style.backgroundColor = resolvedColor || '';
	}

	/**
	 * Return whether a raw CTA color should be treated as a custom override.
	 *
	 * @param {string} rawColor Raw custom color value.
	 * @param {?Object} ctaType Current CTA type definition.
	 * @return {boolean}
	 */
	function isCustomCtaColor(rawColor, ctaType) {
		var normalizedColor = (rawColor || '').trim().toLowerCase();
		var defaultColor = ctaType && isHexColor(ctaType.default_color) ? ctaType.default_color.toLowerCase() : '';

		if (!isHexColor(normalizedColor)) {
			return false;
		}

		return normalizedColor !== defaultColor;
	}

	/**
	 * Normalize a CTA color so default colors are not stored as custom overrides.
	 *
	 * @param {string} rawColor Raw custom color value.
	 * @param {?Object} ctaType Current CTA type definition.
	 * @return {string}
	 */
	function normalizeCtaColorValue(rawColor, ctaType) {
		if (!isCustomCtaColor(rawColor, ctaType)) {
			return '';
		}

		return rawColor.trim();
	}

	/**
	 * Reset the CTA builder form back to its initial state.
	 */
	function resetCtaForm() {
		var builderElements = getCtaBuilderElements();

		hideCtaBuilderFeedback();
		setSelectedCtaType('');
		setCurrentEditingCtaRow(null);

		if (builderElements.labelInput) {
			builderElements.labelInput.value = '';
		}

		if (builderElements.valueInput) {
			builderElements.valueInput.value = '';
			builderElements.valueInput.setAttribute('aria-invalid', 'false');
		}

		if (builderElements.messageInput) {
			builderElements.messageInput.value = '';
		}

		setColorPickerValue(builderElements.colorInput, '');
		syncCtaFormState();
	}

	/**
	 * Update the CTA preview card using the current draft values.
	 *
	 * @param {string} type Current CTA type key.
	 * @param {?Object} ctaType Current CTA type definition.
	 * @param {string} label Custom label value.
	 * @param {string} rawValue Raw destination value.
	 * @param {string} rawMessage Raw WhatsApp message.
	 * @param {string} rawColor Raw custom color.
	 * @param {?Object} normalizedCta Normalized CTA draft.
	 */
	function updateCtaPreview(type, ctaType, label, rawValue, rawMessage, rawColor, normalizedCta) {
		var previewButton = document.getElementById('telkari-cta-preview-button');
		var previewIcon = document.getElementById('telkari-cta-preview-icon');
		var previewLabel = document.getElementById('telkari-cta-preview-label');
		var previewValue = document.getElementById('telkari-cta-preview-value');
		var previewNote = document.getElementById('telkari-cta-preview-note');
		var resolvedColor = getResolvedCtaColor(rawColor, ctaType);
		var contrastColor = getContrastColor(resolvedColor);
		var previewLabelText = label || (ctaType ? ctaType.label : '');
		var previewValueText = normalizedCta ? normalizedCta.value : rawValue;
		var previewNoteText = ctaType ? ctaType.description : '';
		var hasSelectedType = !!(type && ctaType);

		if (!previewButton || !previewIcon || !previewLabel || !previewValue || !previewNote) {
			return;
		}

		if (!previewLabelText) {
			previewLabelText = previewLabel.getAttribute('data-empty-text') || '';
		}

		if (!previewValueText) {
			previewValueText = previewValue.getAttribute('data-empty-text') || '';
		}

		if ('whatsapp' === type && rawMessage) {
			previewNoteText = rawMessage;
		}

		if (!previewNoteText) {
			previewNoteText = previewNote.getAttribute('data-empty-text') || '';
		}

		previewButton.classList.toggle('telkari-cta-preview-button--empty', !hasSelectedType);
		if (hasSelectedType) {
			previewButton.style.backgroundColor = resolvedColor;
			previewButton.style.borderColor = resolvedColor;
			previewButton.style.color = contrastColor;
		} else {
			previewButton.style.backgroundColor = '';
			previewButton.style.borderColor = '';
			previewButton.style.color = '';
		}

		previewIcon.innerHTML = hasSelectedType ? getCtaIconSvg(type) : '';
		previewLabel.textContent = previewLabelText;
		previewValue.textContent = previewValueText;
		previewNote.textContent = previewNoteText;
	}

	/**
	 * Resolve the active preview color for the CTA draft.
	 *
	 * @param {string} rawColor Raw custom color.
	 * @param {?Object} ctaType Current CTA type definition.
	 * @return {string}
	 */
	function getResolvedCtaColor(rawColor, ctaType) {
		if (isHexColor(rawColor)) {
			return rawColor;
		}

		if (ctaType && isHexColor(ctaType.default_color)) {
			return ctaType.default_color;
		}

		return '#1E293B';
	}

	/**
	 * Check whether a value is a valid hex color.
	 *
	 * @param {string} value Candidate color.
	 * @return {boolean}
	 */
	function isHexColor(value) {
		return /^#(?:[0-9a-fA-F]{3}){1,2}$/.test((value || '').trim());
	}

	/**
	 * Compute a readable foreground color for a hex background.
	 *
	 * @param {string} hexColor Background color.
	 * @return {string}
	 */
	function getContrastColor(hexColor) {
		var normalizedHex = (hexColor || '').replace('#', '');
		var red;
		var green;
		var blue;
		var luminance;

		if (normalizedHex.length === 3) {
			normalizedHex = normalizedHex.charAt(0) + normalizedHex.charAt(0) +
				normalizedHex.charAt(1) + normalizedHex.charAt(1) +
				normalizedHex.charAt(2) + normalizedHex.charAt(2);
		}

		if (normalizedHex.length !== 6) {
			return '#ffffff';
		}

		red = parseInt(normalizedHex.slice(0, 2), 16) / 255;
		green = parseInt(normalizedHex.slice(2, 4), 16) / 255;
		blue = parseInt(normalizedHex.slice(4, 6), 16) / 255;

		red = red <= 0.03928 ? red / 12.92 : Math.pow((red + 0.055) / 1.055, 2.4);
		green = green <= 0.03928 ? green / 12.92 : Math.pow((green + 0.055) / 1.055, 2.4);
		blue = blue <= 0.03928 ? blue / 12.92 : Math.pow((blue + 0.055) / 1.055, 2.4);
		luminance = (0.2126 * red) + (0.7152 * green) + (0.0722 * blue);

		return luminance > 0.35 ? '#1e293b' : '#ffffff';
	}

	/**
	 * Normalize CTA input values on the client before appending hidden fields.
	 *
	 * @param {string} type CTA type.
	 * @param {string} value Raw value.
	 * @param {string} message Raw message.
	 * @return {?Object}
	 */
	function normalizeCtaInput(type, value, message) {
		var normalizedValue = value.trim();
		var normalizedMessage = message.trim();
		var digits;
		var hasLeadingPlus;

		switch (type) {
			case 'whatsapp':
				digits = normalizedValue.replace(/\D+/g, '');
				if (digits.length < 6 || digits.length > 18) {
					return null;
				}

				return {
					value: digits,
					message: normalizedMessage,
					url: buildWhatsappUrl(digits, normalizedMessage)
				};

			case 'phone':
				hasLeadingPlus = normalizedValue.indexOf('+') === 0;
				digits = normalizedValue.replace(/\D+/g, '');
				if (digits.length < 6 || digits.length > 18) {
					return null;
				}

				normalizedValue = hasLeadingPlus ? '+' + digits : digits;

				return {
					value: normalizedValue,
					message: '',
					url: 'tel:' + normalizedValue
				};

			case 'email':
				if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(normalizedValue)) {
					return null;
				}

				return {
					value: normalizedValue,
					message: '',
					url: 'mailto:' + normalizedValue
				};

			case 'url':
				try {
					normalizedValue = new URL(normalizedValue).href;
				} catch (err) {
					return null;
				}

				return {
					value: normalizedValue,
					message: '',
					url: normalizedValue
				};
		}

		return null;
	}

	/**
	 * Build a WhatsApp URL on the client.
	 *
	 * @param {string} digits Phone digits.
	 * @param {string} message Optional message.
	 * @return {string}
	 */
	function buildWhatsappUrl(digits, message) {
		var url = 'https://wa.me/' + digits;

		if (!message) {
			return url;
		}

		return url + '?text=' + encodeURIComponent(message);
	}

	/**
	 * Delete a collection row.
	 *
	 * @param {HTMLElement} btn The clicked delete button.
	 * @param {string} confirmationMessage Delete confirmation message.
	 */
	function deleteCollectionRow(btn, confirmationMessage) {
		if (!confirm(confirmationMessage)) {
			return;
		}

		var row = btn.closest('.telkari-account-row, .telkari-cta-row');
		if (row) {
			var listEl = row.parentElement;
			var isEditingAccountRow = currentEditingAccountRow === row;
			var isEditingCurrentRow = currentEditingCtaRow === row;
			row.remove();
			reindexCollectionRows(listEl);
			syncCollectionEmptyState(listEl);

			if (isEditingAccountRow) {
				resetAccountForm();
			}

			if (isEditingCurrentRow) {
				resetCtaForm();
			}
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

	/**
	 * Escape attribute values for safe insertion into HTML strings.
	 *
	 * @param {string} str Input string.
	 * @return {string} Escaped string.
	 */
	function escapeAttribute(str) {
		return String(str === null || typeof str === 'undefined' ? '' : str)
			.replace(/&/g, '&amp;')
			.replace(/"/g, '&quot;')
			.replace(/'/g, '&#039;')
			.replace(/</g, '&lt;')
			.replace(/>/g, '&gt;');
	}

})();
