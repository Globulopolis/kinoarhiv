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
