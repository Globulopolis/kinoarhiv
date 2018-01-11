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
		$('.thumb .item a, .album-art').colorbox({
			maxHeight: '90%',
			maxWidth: '90%',
			photo: true
		});
	}

	$('.rateit').bind('over', function (e, v) {
		$(this).attr('title', v);
	});

	$('.rate .rateit').bind('rated reset', function (e) {
		var $this = $(this),
			value = $this.rateit('value'),
			url   = $this.data('url'),
			place = $('.my_vote').next();

		$.ajax({
			type: 'POST',
			url: url,
			data: {'value': value}
		}).done(function (response) {
			var my_votes = $('.rate .my_votes'),
				my_vote  = $('.rate .my_vote');

			if (my_votes.is(':hidden')) {
				my_votes.show();
			}

			if (value !== 0) {
				if (my_vote.is(':hidden')) {
					my_vote.show();
				}
				$('.rate .my_vote span.small').text(KA_vars.language.COM_KA_RATE_MY_CURRENT + value);
			} else {
				$('.rate .my_vote span').text('').parent().hide();
			}

			Aurora.message([{text: response.message, type: 'success'}], place, {place: 'insertAfter', replace: true});
		}).fail(function (xhr, status, error) {
			Aurora.message([{text: error, type: 'error'}], place, {place: 'insertAfter', replace: true});
		});
	});

	$('#checkall-toggle').click(function(){
		if ($(this).is(':checked')) {
			$('.items-list tbody :checkbox').prop('checked', true);
		} else {
			$('.items-list tbody :checkbox').prop('checked', false);
		}
	});

	$('#profileForm').submit(function(e){
		if ($('input', this).filter(':checked').length === 0) {
			Aurora.message([{text: KA_vars.language.JLIB_HTML_PLEASE_MAKE_A_SELECTION_FROM_THE_LIST, type: 'error'}], '#profileForm', {place: 'insertAfter', replace: true});

			return false;
		}
	});

	// Add support for UIkit tooltips
	$('.hasTip, .hasTooltip').attr('data-uk-tooltip', '');

	$('.cmd-favorite, .cmd-watched').click(function(e){
		e.preventDefault();

		var $this = $(this),
			place = typeof $this.data('ka-msg-place') === 'undefined' ? '#system-message-container' : $this.closest($this.data('ka-msg-place'));

		$.ajax({
			url: $this.attr('href')
		}).done(function (response) {
			if (response.success) {
				Aurora.message([{text: response.message, type: 'success'}], place, {place: 'insertAfter', replace: true});
				$this.text(response.text);
				$this.attr('href', response.url);

				if ($this.hasClass('delete')) {
					$this.removeClass('delete').addClass('add');
				} else {
					$this.removeClass('add').addClass('delete');
				}
			} else {
				Aurora.message([{text: KA_vars.language.JERROR_AN_ERROR_HAS_OCCURRED, type: 'error'}], place, {place: 'insertAfter', replace: true});
			}
		}).fail(function (xhr, status, error) {
			Aurora.message([{text: error, type: 'error'}], place, {place: 'insertAfter', replace: true});
		});
	});
});
