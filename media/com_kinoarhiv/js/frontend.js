/**
 * This file provide basic support for kinoarhiv components used in frontend only.
 *
 * @package     Kinoarhiv.Site
 * @subpackage  com_kinoarhiv
 *
 * @copyright   Copyright (C) Libra.ms. All rights reserved.
 * @license     GNU General Public License version 2 or later
 * @url         http://киноархив.com/
 */

jQuery(document).ready(function($){
	if ($.fn.lazyload) {
		$('img.lazy').lazyload({
			threshold: 200
		});
	}

	if ($.fn.colorbox) {
		$('.thumb .item a').colorbox({
			maxHeight: '90%',
			maxWidth: '90%',
			photo: true
		});
	}

	// Add support for UIkit tooltips
	$('.hasTip, .hasTooltip').attr('data-uk-tooltip', '');

	$('.cmd-favorite').click(function(e){
		e.preventDefault();

		var _this = $(this),
			msg_placement = (typeof _this.data('msg_placement') === 'undefined') ? 'header' : _this.data('msg_placement');

		$.ajax({
			url: _this.attr('href') + '&format=json'
		}).done(function (response) {
			if (response.success) {
				showMsg(_this.closest(msg_placement), response.message);
				_this.text(response.text);
				_this.attr('href', response.url);

				if (_this.hasClass('remove')) {
					_this.closest(_this.data('remove')).remove();
				} else {
					if (_this.hasClass('delete')) {
						_this.removeClass('delete').addClass('add');
					} else {
						_this.removeClass('add').addClass('delete');
					}
				}
			} else {
				showMsg(_this.closest(msg_placement), KA_vars.language.JERROR_AN_ERROR_HAS_OCCURRED);
			}
		}).fail(function (xhr, status, error) {
			showMsg(_this.closest(msg_placement), error);
		});
	});
});
