/**
 * ConsentPro Admin Scripts
 *
 * @package ConsentPro
 */

/* global jQuery */
(function ($) {
  'use strict';

  /**
   * Initialize WordPress color pickers on the Appearance tab.
   */
  function initColorPickers() {
    $('.consentpro-color-field').each(function () {
      var $field = $(this);

      if (typeof $.fn.wpColorPicker !== 'undefined') {
        $field.wpColorPicker({
          defaultColor: $field.data('default') || false,
          change: function () {
            // Future: trigger live preview update (US-021b).
          },
          clear: function () {
            // Future: trigger live preview update (US-021b).
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

      // Update active states.
      $('.consentpro-preview-toggle')
        .removeClass('consentpro-preview-toggle--active')
        .attr('aria-pressed', 'false');
      $btn.addClass('consentpro-preview-toggle--active').attr('aria-pressed', 'true');

      // Update preview content.
      updatePreviewContent(layer);
    });
  }

  // Initialize on DOM ready.
  $(document).ready(function () {
    initColorPickers();
    initPreview();
  });
})(jQuery);
