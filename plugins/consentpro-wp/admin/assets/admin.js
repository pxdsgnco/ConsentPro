/**
 * ConsentPro Admin Scripts
 *
 * @package ConsentPro
 */

/* global jQuery */
(function ($) {
  'use strict';

  /**
   * Debounce function to limit execution rate.
   *
   * @param {Function} func Function to debounce.
   * @param {number} wait Wait time in milliseconds.
   * @returns {Function} Debounced function.
   */
  function debounce(func, wait) {
    var timeout;
    return function executedFunction() {
      var context = this;
      var args = arguments;
      clearTimeout(timeout);
      timeout = setTimeout(function () {
        func.apply(context, args);
      }, wait);
    };
  }

  /**
   * Current preview state.
   */
  var previewState = {
    layer: 1,
    isMobile: false,
  };

  /**
   * Initialize WordPress color pickers on the Appearance tab.
   */
  function initColorPickers() {
    $('.consentpro-color-field').each(function () {
      var $field = $(this);

      if (typeof $.fn.wpColorPicker !== 'undefined') {
        $field.wpColorPicker({
          defaultColor: $field.data('default') || false,
          change: function (event, ui) {
            // Update the input value immediately for buildPreviewHTML to read.
            $field.val(ui.color.toString());
            debouncedUpdatePreview();
          },
          clear: function () {
            $field.val($field.data('default') || '');
            debouncedUpdatePreview();
          },
        });
      }
    });
  }

  /**
   * Escape HTML special characters to prevent XSS.
   *
   * @param {string} str String to escape.
   * @returns {string} Escaped string.
   */
  function escapeHtml(str) {
    if (!str) return '';
    var div = document.createElement('div');
    div.textContent = str;
    return div.innerHTML;
  }

  /**
   * Update preview configuration from form fields and refresh preview.
   */
  function updatePreviewFromFields() {
    var config = window.consentproPreviewConfig || {};

    // Get color values from color pickers.
    config.colors = {
      primary: $('#consentpro-color-primary').val() || '#2563eb',
      secondary: $('#consentpro-color-secondary').val() || '#64748b',
      background: $('#consentpro-color-background').val() || '#ffffff',
      text: $('#consentpro-color-text').val() || '#1e293b',
    };

    // Get text values from text inputs.
    config.text = {
      heading:
        $('#consentpro-text-heading').val() ||
        $('#consentpro-text-heading').attr('placeholder') ||
        'We value your privacy',
      acceptAll:
        $('#consentpro-text-accept').val() ||
        $('#consentpro-text-accept').attr('placeholder') ||
        'Accept All',
      rejectNonEssential:
        $('#consentpro-text-reject').val() ||
        $('#consentpro-text-reject').attr('placeholder') ||
        'Reject Non-Essential',
      settings:
        $('#consentpro-text-settings').val() ||
        $('#consentpro-text-settings').attr('placeholder') ||
        'Cookie Settings',
      save:
        $('#consentpro-text-save').val() ||
        $('#consentpro-text-save').attr('placeholder') ||
        'Save Preferences',
      settingsTitle: config.text ? config.text.settingsTitle : 'Privacy Preferences',
    };

    // Get category values if on Categories tab.
    if ($('#consentpro-essential-name').length) {
      config.categories = getCategoriesFromFields();
    }

    window.consentproPreviewConfig = config;
    updatePreviewContent(previewState.layer);
  }

  /**
   * Get category configuration from form fields.
   *
   * @returns {Array} Categories array.
   */
  function getCategoriesFromFields() {
    var categoryIds = ['essential', 'analytics', 'marketing', 'personalization'];
    var defaults = {
      essential: {
        name: 'Essential',
        description: 'Required for the website to function properly.',
      },
      analytics: { name: 'Analytics', description: 'Help us understand usage.' },
      marketing: { name: 'Marketing', description: 'Personalized advertisements.' },
      personalization: { name: 'Personalization', description: 'Remember your preferences.' },
    };

    return categoryIds.map(function (id) {
      var $name = $('#consentpro-' + id + '-name');
      var $desc = $('#consentpro-' + id + '-description');

      return {
        id: id,
        name: $name.val() || $name.attr('placeholder') || defaults[id].name,
        description: $desc.val() || $desc.attr('placeholder') || defaults[id].description,
        required: id === 'essential',
      };
    });
  }

  // Create debounced version (300ms).
  var debouncedUpdatePreview = debounce(updatePreviewFromFields, 300);

  /**
   * Build Layer 1 HTML structure (main banner).
   *
   * @param {Object} text Text configuration.
   * @returns {string} HTML string.
   */
  function buildLayer1HTML(text) {
    return (
      '<div class="consentpro__container">' +
      '<div class="consentpro__content">' +
      '<h2 id="consentpro-heading" class="consentpro__heading">' +
      escapeHtml(text.heading) +
      '</h2>' +
      '<p id="consentpro-description" class="consentpro__description">' +
      escapeHtml(text.description) +
      ' <a href="#" class="consentpro__link">Learn more</a>' +
      '</p>' +
      '</div>' +
      '<div class="consentpro__actions" role="group">' +
      '<button type="button" class="consentpro__btn consentpro__btn--secondary">' +
      escapeHtml(text.rejectNonEssential) +
      '</button>' +
      '<button type="button" class="consentpro__btn consentpro__btn--secondary">' +
      escapeHtml(text.settings) +
      '</button>' +
      '<button type="button" class="consentpro__btn consentpro__btn--primary">' +
      escapeHtml(text.acceptAll) +
      '</button>' +
      '</div>' +
      '</div>'
    );
  }

  /**
   * Build Layer 2 HTML structure (settings panel).
   *
   * @param {Object} text Text configuration.
   * @param {Array} categories Category definitions.
   * @returns {string} HTML string.
   */
  function buildLayer2HTML(text, categories) {
    var categoriesHTML = categories
      .map(function (cat) {
        var requiredBadge = cat.required
          ? '<span class="consentpro__category-required">(Always active)</span>'
          : '';
        var checkedAttr = cat.required ? 'true' : 'false';
        var disabledAttr = cat.required ? ' disabled' : '';

        return (
          '<li class="consentpro__category">' +
          '<div class="consentpro__category-info">' +
          '<div class="consentpro__category-header">' +
          '<span class="consentpro__category-name">' +
          escapeHtml(cat.name) +
          '</span>' +
          requiredBadge +
          '</div>' +
          '<p class="consentpro__category-desc">' +
          escapeHtml(cat.description) +
          '</p>' +
          '</div>' +
          '<button type="button" role="switch" class="consentpro__toggle" ' +
          'aria-checked="' +
          checkedAttr +
          '"' +
          disabledAttr +
          '></button>' +
          '</li>'
        );
      })
      .join('');

    return (
      '<div class="consentpro__container">' +
      '<header class="consentpro__settings-header">' +
      '<button type="button" class="consentpro__back" aria-label="Back">' +
      '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">' +
      '<path d="M19 12H5M12 19l-7-7 7-7"/>' +
      '</svg>' +
      '</button>' +
      '<h2 class="consentpro__settings-title">' +
      escapeHtml(text.settingsTitle) +
      '</h2>' +
      '</header>' +
      '<ul class="consentpro__categories" role="list">' +
      categoriesHTML +
      '</ul>' +
      '<footer class="consentpro__settings-footer">' +
      '<a href="#" class="consentpro__policy-link">Privacy Policy</a>' +
      '<button type="button" class="consentpro__btn consentpro__btn--primary">' +
      escapeHtml(text.save) +
      '</button>' +
      '</footer>' +
      '</div>'
    );
  }

  /**
   * Build complete preview HTML document for iframe.
   *
   * @param {number} layer Layer number (1 or 2).
   * @returns {string} Complete HTML document.
   */
  function buildPreviewHTML(layer) {
    var config = window.consentproPreviewConfig || {};

    var colors = {
      primary: config.colors && config.colors.primary ? config.colors.primary : '#2563eb',
      secondary: config.colors && config.colors.secondary ? config.colors.secondary : '#64748b',
      background: config.colors && config.colors.background ? config.colors.background : '#ffffff',
      text: config.colors && config.colors.text ? config.colors.text : '#1e293b',
    };

    var text = {
      heading: config.text && config.text.heading ? config.text.heading : 'We value your privacy',
      description: 'We use cookies to enhance your browsing experience and analyze our traffic.',
      acceptAll: config.text && config.text.acceptAll ? config.text.acceptAll : 'Accept All',
      rejectNonEssential:
        config.text && config.text.rejectNonEssential
          ? config.text.rejectNonEssential
          : 'Reject Non-Essential',
      settings: config.text && config.text.settings ? config.text.settings : 'Cookie Settings',
      save: config.text && config.text.save ? config.text.save : 'Save Preferences',
      settingsTitle:
        config.text && config.text.settingsTitle
          ? config.text.settingsTitle
          : 'Privacy Preferences',
    };

    var categories =
      config.categories && config.categories.length
        ? config.categories
        : [
            {
              id: 'essential',
              name: 'Essential',
              description: 'Required for the website to function properly.',
              required: true,
            },
            {
              id: 'analytics',
              name: 'Analytics',
              description: 'Help us understand usage.',
              required: false,
            },
            {
              id: 'marketing',
              name: 'Marketing',
              description: 'Personalized advertisements.',
              required: false,
            },
            {
              id: 'personalization',
              name: 'Personalization',
              description: 'Remember your preferences.',
              required: false,
            },
          ];

    // CSS custom properties for theming.
    var cssVars =
      ':root{' +
      '--consentpro-color-primary:' +
      colors.primary +
      ';' +
      '--consentpro-color-secondary:' +
      colors.secondary +
      ';' +
      '--consentpro-color-background:' +
      colors.background +
      ';' +
      '--consentpro-color-text:' +
      colors.text +
      ';' +
      '}';

    // Build appropriate layer HTML.
    var bannerHTML = layer === 1 ? buildLayer1HTML(text) : buildLayer2HTML(text, categories);

    // Get CSS URL from config.
    var cssUrl = config.cssUrl || '';

    return (
      '<!DOCTYPE html>' +
      '<html lang="en">' +
      '<head>' +
      '<meta charset="UTF-8">' +
      '<meta name="viewport" content="width=device-width, initial-scale=1.0">' +
      '<style>' +
      cssVars +
      '</style>' +
      (cssUrl ? '<link rel="stylesheet" href="' + cssUrl + '">' : '') +
      '<style>' +
      'body{' +
      'margin:0;' +
      'padding:20px;' +
      'font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,sans-serif;' +
      'background:#f5f5f5;' +
      'min-height:100%;' +
      'display:flex;' +
      'align-items:flex-end;' +
      '}' +
      '.consentpro{' +
      'position:relative!important;' +
      'width:100%;' +
      '}' +
      '</style>' +
      '</head>' +
      '<body>' +
      '<div id="consentpro-banner" class="consentpro consentpro--visible">' +
      bannerHTML +
      '</div>' +
      '</body>' +
      '</html>'
    );
  }

  /**
   * Update preview iframe content.
   *
   * @param {number} layer Layer number (1 or 2).
   */
  function updatePreviewContent(layer) {
    var $iframe = $('#consentpro-preview-iframe');
    if (!$iframe.length) return;

    var iframe = $iframe[0];
    var doc = iframe.contentDocument || iframe.contentWindow.document;

    doc.open();
    doc.write(buildPreviewHTML(layer));
    doc.close();
  }

  /**
   * Toggle mobile/desktop preview view.
   *
   * @param {boolean} isMobile Whether to show mobile view.
   */
  function setMobilePreview(isMobile) {
    previewState.isMobile = isMobile;
    var $wrapper = $('.consentpro-preview-frame-wrapper');
    var $iframe = $('#consentpro-preview-iframe');

    if (isMobile) {
      $wrapper.addClass('consentpro-preview-frame-wrapper--mobile');
      $iframe.css('width', '375px');
    } else {
      $wrapper.removeClass('consentpro-preview-frame-wrapper--mobile');
      $iframe.css('width', '100%');
    }

    // Update toggle button states.
    $('.consentpro-viewport-toggle').attr('aria-pressed', 'false');
    $(
      '.consentpro-viewport-toggle[data-viewport="' + (isMobile ? 'mobile' : 'desktop') + '"]'
    ).attr('aria-pressed', 'true');
  }

  /**
   * Initialize viewport toggle buttons.
   */
  function initViewportToggle() {
    $('.consentpro-viewport-toggle').on('click', function () {
      var viewport = $(this).data('viewport');
      setMobilePreview(viewport === 'mobile');
    });
  }

  /**
   * Initialize the preview panel.
   */
  function initPreview() {
    var $iframe = $('#consentpro-preview-iframe');
    if (!$iframe.length) return;

    // Initial render with Layer 1.
    updatePreviewContent(1);

    // Layer toggle button handlers.
    $('.consentpro-preview-toggle').on('click', function () {
      var $btn = $(this);
      var layer = parseInt($btn.data('layer'), 10);
      previewState.layer = layer;

      // Update active states.
      $('.consentpro-preview-toggle')
        .removeClass('consentpro-preview-toggle--active')
        .attr('aria-pressed', 'false');
      $btn.addClass('consentpro-preview-toggle--active').attr('aria-pressed', 'true');

      // Update preview content.
      updatePreviewContent(layer);
    });

    // Initialize viewport toggle.
    initViewportToggle();

    // Bind live update listeners to text fields.
    $(
      '.consentpro-settings-content input[type="text"]:not(.consentpro-color-field), ' +
        '.consentpro-settings-content textarea'
    ).on('input', debouncedUpdatePreview);
  }

  // =====================
  // Consent Log Functions
  // =====================

  /**
   * Initialize consent log tab functionality.
   */
  function initConsentLog() {
    var $container = $('#consentpro-metrics-container');
    if (!$container.length) return;

    // Load initial data.
    loadMetrics();
    loadLogEntries(1);

    // Clear log button handler.
    $('#consentpro-clear-log').on('click', function () {
      var $btn = $(this);
      var admin = window.consentproAdmin || {};
      var confirmMsg = admin.i18n
        ? admin.i18n.confirmClear
        : 'Are you sure you want to clear all consent log entries?';

      if (!confirm(confirmMsg)) {
        return;
      }

      $btn.prop('disabled', true).text(admin.i18n ? admin.i18n.clearing : 'Clearing...');

      $.post(admin.ajaxUrl, {
        action: 'consentpro_clear_log',
        nonce: admin.nonce,
      })
        .done(function (response) {
          if (response.success) {
            loadMetrics();
            loadLogEntries(1);
          } else {
            alert(response.data.message || 'Error clearing log');
          }
        })
        .fail(function () {
          alert(admin.i18n ? admin.i18n.errorLoading : 'Error clearing log');
        })
        .always(function () {
          $btn.prop('disabled', false).text(admin.i18n ? admin.i18n.clearLog : 'Clear Log');
        });
    });
  }

  /**
   * Load consent metrics via AJAX.
   */
  function loadMetrics() {
    var $container = $('#consentpro-metrics-container');
    var admin = window.consentproAdmin || {};

    $.post(admin.ajaxUrl, {
      action: 'consentpro_get_metrics',
      nonce: admin.nonce,
      days: 30,
    })
      .done(function (response) {
        if (response.success) {
          renderMetrics(response.data);
        } else {
          $container.html(
            '<p class="consentpro-error">' + escapeHtml(response.data.message) + '</p>'
          );
        }
      })
      .fail(function () {
        $container.html(
          '<p class="consentpro-error">' +
            escapeHtml(admin.i18n ? admin.i18n.errorLoading : 'Error loading data') +
            '</p>'
        );
      });
  }

  /**
   * Render metrics HTML.
   *
   * @param {Object} metrics Metrics data.
   */
  function renderMetrics(metrics) {
    var $container = $('#consentpro-metrics-container');
    var admin = window.consentproAdmin || {};

    if (metrics.total === 0) {
      $container.html(
        '<p class="consentpro-empty-state">' +
          escapeHtml(admin.i18n ? admin.i18n.noEntries : 'No consent records found.') +
          '</p>'
      );
      return;
    }

    var html =
      '<div class="consentpro-metrics-grid">' +
      '<div class="consentpro-metric-item">' +
      '<span class="consentpro-metric-value">' +
      metrics.total +
      '</span>' +
      '<span class="consentpro-metric-label">Total Consents</span>' +
      '</div>' +
      '<div class="consentpro-metric-item consentpro-metric-item--accept">' +
      '<span class="consentpro-metric-value">' +
      metrics.accept_percent +
      '%</span>' +
      '<span class="consentpro-metric-label">Accept All</span>' +
      '</div>' +
      '<div class="consentpro-metric-item consentpro-metric-item--custom">' +
      '<span class="consentpro-metric-value">' +
      metrics.custom_percent +
      '%</span>' +
      '<span class="consentpro-metric-label">Custom</span>' +
      '</div>' +
      '<div class="consentpro-metric-item consentpro-metric-item--reject">' +
      '<span class="consentpro-metric-value">' +
      metrics.reject_percent +
      '%</span>' +
      '<span class="consentpro-metric-label">Reject</span>' +
      '</div>' +
      '</div>';

    // Add percentage bar.
    html +=
      '<div class="consentpro-metrics-bar-container">' +
      '<div class="consentpro-metric-bar">' +
      '<div class="consentpro-metric-bar-segment consentpro-metric-bar-segment--accept" ' +
      'style="width:' +
      metrics.accept_percent +
      '%" ' +
      'title="Accept All: ' +
      metrics.accept_percent +
      '%"></div>' +
      '<div class="consentpro-metric-bar-segment consentpro-metric-bar-segment--custom" ' +
      'style="width:' +
      metrics.custom_percent +
      '%" ' +
      'title="Custom: ' +
      metrics.custom_percent +
      '%"></div>' +
      '<div class="consentpro-metric-bar-segment consentpro-metric-bar-segment--reject" ' +
      'style="width:' +
      metrics.reject_percent +
      '%" ' +
      'title="Reject: ' +
      metrics.reject_percent +
      '%"></div>' +
      '</div>' +
      '</div>';

    $container.html(html);
  }

  /**
   * Load log entries via AJAX.
   *
   * @param {number} page Page number.
   */
  function loadLogEntries(page) {
    var $container = $('#consentpro-log-table-container');
    var $pagination = $('#consentpro-log-pagination');
    var admin = window.consentproAdmin || {};

    $container.html(
      '<p class="consentpro-loading">' +
        escapeHtml(admin.i18n ? admin.i18n.loading : 'Loading...') +
        '</p>'
    );

    $.post(admin.ajaxUrl, {
      action: 'consentpro_get_log_entries',
      nonce: admin.nonce,
      page: page,
      per_page: 50,
    })
      .done(function (response) {
        if (response.success) {
          renderLogTable(response.data.entries);
          renderPagination(response.data, $pagination);
        } else {
          $container.html(
            '<p class="consentpro-error">' + escapeHtml(response.data.message) + '</p>'
          );
        }
      })
      .fail(function () {
        $container.html(
          '<p class="consentpro-error">' +
            escapeHtml(admin.i18n ? admin.i18n.errorLoading : 'Error loading data') +
            '</p>'
        );
      });
  }

  /**
   * Render log table HTML.
   *
   * @param {Array} entries Log entries.
   */
  function renderLogTable(entries) {
    var $container = $('#consentpro-log-table-container');
    var admin = window.consentproAdmin || {};

    if (!entries || entries.length === 0) {
      $container.html(
        '<p class="consentpro-empty-state">' +
          escapeHtml(admin.i18n ? admin.i18n.noEntries : 'No consent records found.') +
          '</p>'
      );
      return;
    }

    var html =
      '<table class="consentpro-log-table">' +
      '<thead><tr>' +
      '<th>Timestamp</th>' +
      '<th>Type</th>' +
      '<th>Categories</th>' +
      '<th>Region</th>' +
      '</tr></thead><tbody>';

    entries.forEach(function (entry) {
      var categories = {};
      try {
        categories = JSON.parse(entry.categories);
      } catch {
        categories = {};
      }
      var catList = Object.keys(categories)
        .filter(function (k) {
          return categories[k];
        })
        .join(', ');

      var typeClass = 'consentpro-consent-type--' + entry.consent_type;
      var typeLabel = entry.consent_type.replace(/_/g, ' ');
      typeLabel = typeLabel.charAt(0).toUpperCase() + typeLabel.slice(1);

      html +=
        '<tr>' +
        '<td>' +
        escapeHtml(entry.timestamp) +
        '</td>' +
        '<td><span class="consentpro-consent-type ' +
        typeClass +
        '">' +
        escapeHtml(typeLabel) +
        '</span></td>' +
        '<td>' +
        escapeHtml(catList || '-') +
        '</td>' +
        '<td>' +
        escapeHtml(entry.region || '-') +
        '</td>' +
        '</tr>';
    });

    html += '</tbody></table>';
    $container.html(html);
  }

  /**
   * Render pagination controls.
   *
   * @param {Object} data Pagination data.
   * @param {jQuery} $container Pagination container.
   */
  function renderPagination(data, $container) {
    if (data.total_pages <= 1) {
      $container.empty();
      return;
    }

    var html =
      '<button type="button" class="button" data-page="' +
      (data.page - 1) +
      '"' +
      (data.page <= 1 ? ' disabled' : '') +
      '>&laquo; Prev</button>' +
      '<span class="page-info">Page ' +
      data.page +
      ' of ' +
      data.total_pages +
      '</span>' +
      '<button type="button" class="button" data-page="' +
      (data.page + 1) +
      '"' +
      (data.page >= data.total_pages ? ' disabled' : '') +
      '>Next &raquo;</button>';

    $container.html(html);

    $container.find('button').on('click', function () {
      var page = $(this).data('page');
      if (page > 0 && page <= data.total_pages) {
        loadLogEntries(page);
      }
    });
  }

  // =====================
  // License Validation
  // =====================

  /**
   * Initialize license validation functionality.
   */
  function initLicenseValidation() {
    var $validateBtn = $('#consentpro-validate-license');
    if (!$validateBtn.length) return;

    $validateBtn.on('click', function () {
      var $btn = $(this);
      var $input = $('#consentpro-license-key');
      var $message = $('#consentpro-license-message');
      var $spinner = $btn.find('.consentpro-btn-spinner');
      var $btnText = $btn.find('.consentpro-btn-text');
      var admin = window.consentproAdmin || {};
      var licenseKey = $input.val().trim();

      if (!licenseKey) {
        showLicenseMessage($message, 'Please enter a license key.', 'error');
        return;
      }

      // Show loading state.
      $btn.prop('disabled', true);
      $spinner.css('display', 'inline-block').addClass('is-active');
      $btnText.text('Validating...');
      $message.hide();

      $.post(admin.ajaxUrl, {
        action: 'consentpro_validate_license',
        nonce: admin.nonce,
        license_key: licenseKey,
      })
        .done(function (response) {
          if (response.success) {
            showLicenseMessage($message, response.data.message, 'success');
            // Reload after short delay to show updated status.
            setTimeout(function () {
              location.reload();
            }, 1500);
          } else {
            showLicenseMessage($message, response.data.message || 'Validation failed.', 'error');
          }
        })
        .fail(function () {
          showLicenseMessage($message, 'Connection error. Please try again.', 'error');
        })
        .always(function () {
          $btn.prop('disabled', false);
          $spinner.css('display', 'none').removeClass('is-active');
          $btnText.text('Activate License');
        });
    });
  }

  /**
   * Show license validation message.
   *
   * @param {jQuery} $container Message container.
   * @param {string} message Message text.
   * @param {string} type Message type (success|error).
   */
  function showLicenseMessage($container, message, type) {
    $container
      .removeClass('consentpro-license-message--success consentpro-license-message--error')
      .addClass('consentpro-license-message--' + type)
      .html(
        '<span class="dashicons dashicons-' +
          (type === 'success' ? 'yes' : 'no') +
          '"></span> ' +
          escapeHtml(message)
      )
      .show();
  }

  // Initialize on DOM ready.
  $(document).ready(function () {
    initColorPickers();
    initPreview();
    initConsentLog();
    initLicenseValidation();
  });
})(jQuery);
