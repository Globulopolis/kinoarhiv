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

function empty(mixed_var) {
	//  discuss at: http://phpjs.org/functions/empty/
	// original by: Philippe Baumann
	//    input by: Onno Marsman
	//    input by: LH
	//    input by: Stoyan Kyosev (http://www.svest.org/)
	// bugfixed by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
	// improved by: Onno Marsman
	// improved by: Francesco
	// improved by: Marc Jansen
	// improved by: Rafal Kukawski
	//   example 1: empty(null);
	//   returns 1: true
	//   example 2: empty(undefined);
	//   returns 2: true
	//   example 3: empty([]);
	//   returns 3: true
	//   example 4: empty({});
	//   returns 4: true
	//   example 5: empty({'aFunc' : function () { alert('humpty'); } });
	//   returns 5: false

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
					title += '<div style="text-align: center; border-bottom: 1px solid #EEEEEE;">' + parts[0] + '</div>' + parts[1];
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
		if ($(el).val() === 'NOW') {
			$(el).val(new Date().toISOString().slice(0, 19).replace('T', ' '));
		}

		if ($(el).data('type') == 'time') {
			$(el).timepicker({
				timeFormat: $(el).data('time-format')
			});
		} else if ($(el).data('type') == 'date') {
			$(el).datepicker({
				dateFormat: $(el).data('date-format')
			});
		} else if ($(el).data('type') == 'datetime') {
			$(el).datetimepicker({
				dateFormat: $(el).data('date-format'),
				timeFormat: $(el).data('time-format')
			});
		}
	}).next('.cmd-datetime').click(function(e){
		e.preventDefault();
		$(this).prev('input').trigger('focus');
	});
});
