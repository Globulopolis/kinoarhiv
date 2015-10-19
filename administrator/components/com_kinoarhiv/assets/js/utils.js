function showMsg(selector, text) {
	jQuery(selector).aurora({
		text: text,
		placement: 'before',
		button: 'close',
		button_title: '['+ Joomla.JText._('COM_KA_CLOSE') +']'
	});
}

function blockUI(action) {
	if (action == 'show') {
		jQuery('<div class="ui-widget-overlay" id="blockui" style="z-index: 10001;"></div>').appendTo('body').show();
	} else {
		jQuery('#blockui').remove();
	}
}

/*
 * A JavaScript equivalent of PHP’s empty. See http://phpjs.org/functions/empty/
 *
 * @return  boolean
 */
function empty(mixed_var) {
	var undef, key, i, len;
	var emptyValues = [undef, null, false, 0, '', '0'];

	for (i = 0, len = emptyValues.length; i < len; i++) {
		if (mixed_var === emptyValues[i]) {
		  return true;
		}
	}

	if (typeof mixed_var === 'object') {
		for (key in mixed_var) {
		  // TODO: should we check for own properties only?
		  //if (mixed_var.hasOwnProperty(key)) {
		  return false;
		  //}
		}
		return true;
	}

	return false;
}

/**
 * Toggles the check state of a group of boxes
*/
function checkall(object, selector) {
	jQuery(document).ready(function($){
		if ($(object).is(':checked')) {
			$(':checkbox', selector).prop('checked', true);
		} else {
			$(':checkbox', selector).prop('checked', false);
		}
	});
}

jQuery(document).ready(function($){
	$('.hasTip, .hasTooltip, td[title]').tooltip({
		show: null,
		position: {
			my: 'left top',
			at: 'left bottom'
		},
		open: function(event, ui){
			ui.tooltip.animate({ top: ui.tooltip.position().top + 10 }, 'fast');
		},
		content: function(){
			var parts = $(this).attr('title').split('::', 2),
				title = '';

			if (parts.length == 2) {
				if (parts[0] != '') {
					title += '<div style="text-align: center; border-bottom: 1px solid #eee;">' + parts[0] + '</div>' + parts[1];
				} else {
					title += parts[1];
				}
			} else {
				title += $(this).attr('title');
			}

			return title;
		}
	});

	$('.hasDatetime').each(function(i, el){
		if ($(el).attr('readonly')) {
			return;
		}

		if ($(el).val() === 'NOW') {
			$(el).val(new Date().toISOString().slice(0, 19).replace('T', ' '));
		}

		if ($(el).data('type') == 'time') {
			$(el).timepicker({
				timeFormat: $(el).data('time-format'),
				showOn: 'button'
			});
		} else if ($(el).data('type') == 'date') {
			$(el).datepicker({
				dateFormat: $(el).data('date-format'),
				showButtonPanel: true,
				showOn: 'button'
			});
		} else if ($(el).data('type') == 'datetime') {
			$(el).datetimepicker({
				dateFormat: $(el).data('date-format'),
				timeFormat: $(el).data('time-format'),
				showButtonPanel: true,
				showOn: 'button'
			});
		}

		$(el).next('.ui-datepicker-trigger').addClass('btn btn-default').html('<i class="icon-calendar"></i>');
	});
});
