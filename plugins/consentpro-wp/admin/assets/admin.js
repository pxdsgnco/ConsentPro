/**
 * ConsentPro Admin Scripts
 *
 * @package ConsentPro
 */

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
					change: function (event, ui) {
						// Color changed - could add live preview here.
					},
					clear: function () {
						// Color cleared - reset to default.
					}
				});
			}
		});
	}

	// Initialize on DOM ready.
	$(document).ready(function () {
		initColorPickers();
	});
})(jQuery);
