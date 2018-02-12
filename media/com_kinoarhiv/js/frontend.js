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
	var msgOptions = {replace: true};

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

	$('.rateit')
	.bind('over', function(e, value){
		$(this).attr('title', value);
	})
	.bind('rated', function(){
		var $this = $(this),
			value = $this.rateit('value'),
			place = $this.next().next();

		$.ajax({
			url: $this.data('rateit-url') + '&task=' + $(this).data('rateit-content').toLowerCase() + '.vote',
			data: {'value': value, 'id': $this.data('rateit-id')}
		}).done(function(response){
			if (response.success) {
				$('.my_vote .vote_rate_current').text(value);
				$('.my_vote, .my_votes').show();
				Aurora.message([{text: response.message, type: 'success'}], place, msgOptions);
			} else {
				Aurora.message([{text: response.message, type: 'alert'}], place, msgOptions);
			}
		}).fail(function(xhr, status, error){
			Aurora.message([{text: error, type: 'error'}], place, msgOptions);
		});
	})
	.bind('reset', function(){
		var place = $(this).next().next();

		$.ajax({
			url: $(this).data('rateit-url') + '&task=' + $(this).data('rateit-content').toLowerCase() + '.votesRemove',
			data: {'id[]': $(this).data('rateit-id')}
		}).done(function(response){
			if (response.success) {
				$('.my_vote').hide();
				Aurora.message([{text: response.message, type: 'success'}], place, msgOptions);
			} else {
				Aurora.message([{text: response.message, type: 'alert'}], place, msgOptions);
			}
		}).fail(function(xhr, status, error){
			Aurora.message([{text: error, type: 'error'}], place, msgOptions);
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
			Aurora.message([{text: KA_vars.language.JLIB_HTML_PLEASE_MAKE_A_SELECTION_FROM_THE_LIST, type: 'error'}], '#profileForm', msgOptions);

			return false;
		}

		if (!confirm(KA_vars.language.COM_KA_REMOVE_SELECTED + '?')) {
			return false;
		}
	});

	// Add support for UIkit tooltips
	$('.hasTooltip').attr('data-uk-tooltip', '');

	$('.cmd-favorite, .cmd-watched').click(function(e){
		e.preventDefault();

		var $this = $(this),
			place = typeof $this.data('ka-msg-place') === 'undefined' ? '#system-message-container' : $this.closest($this.data('ka-msg-place'));

		$.ajax({
			url: $this.attr('href') + '&format=json'
		}).done(function (response) {
			if (response.success) {
				Aurora.message([{text: response.message, type: 'success'}], place, msgOptions);
				$this.text(response.text);
				$this.attr('href', response.url);

				if ($this.hasClass('delete')) {
					$this.removeClass('delete').addClass('add');
				} else {
					$this.removeClass('add').addClass('delete');
				}
			} else {
				Aurora.message([{text: KA_vars.language.JERROR_AN_ERROR_HAS_OCCURRED, type: 'error'}], place, msgOptions);
			}
		}).fail(function (xhr, status, error) {
			Aurora.message([{text: error, type: 'error'}], place, msgOptions);
		});
	});
});
