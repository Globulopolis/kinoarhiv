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
 * A JavaScript equivalent of PHP's empty. See http://phpjs.org/functions/empty/
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

	$('.hasDatetime').each(function(index, element){
		var $this = $(element);

		if ($this.attr('readonly')) {
			return;
		}

		if ($this.val() === 'NOW') {
			$this.val(new Date().toISOString().slice(0, 19).replace('T', ' '));
		}

		if ($this.data('type') == 'time') {
			$this.timepicker({
				timeFormat: $this.data('time-format'),
				showOn: 'button'
			});
		} else if ($this.data('type') == 'date') {
			$this.datepicker({
				dateFormat: $this.data('date-format'),
				showButtonPanel: true,
				showOn: 'button'
			});
		} else if ($this.data('type') == 'datetime') {
			$this.datetimepicker({
				dateFormat: $this.data('date-format'),
				timeFormat: $this.data('time-format'),
				showButtonPanel: true,
				showOn: 'button'
			});
		}

		$this.next('.ui-datepicker-trigger').addClass('btn btn-default').html('<i class="icon-calendar"></i>');
	});

	$('.hasAutocomplete').each(function(index, element){
		var $this = $(element),
			datatype = $this.data('ac-type'),
			allow_clear = $this.data('allow-clear'),
			min_input = $this.data('min-input'),
			multiple = $this.data('multiple'),
			max_sel_size = $this.data('sel-size'),
			sortable = $this.data('sortable'),
			ignore_ids = $this.data('ignore-ids');

		if (!empty(ignore_ids)) {
			ignore_ids = '&ignore[]=' + [ignore_ids].join('&ignore[]=');
		} else {
			ignore_ids = '';
		}

		var data_url = 'index.php?option=com_kinoarhiv&task=ajaxData&element=' + datatype + '&format=json' + ignore_ids;

		var select = $this.select2({
			placeholder: Joomla.JText._('COM_KA_SEARCH_AJAX'),
			allowClear: allow_clear ? true : false,
			quietMillis: 200,
			minimumInputLength: min_input ? min_input : 1,
			maximumSelectionSize: parseInt(max_sel_size),
			multiple: multiple ? true : false,
			ajax: {
				cache: true,
				url: data_url,
				data: function(term, page){
					return {
						term: term,
						showAll: 0
					}
				},
				results: function(data, page){
					return {results: data};
				}
			},
			initSelection: function(element, callback){
				var id = $(element).val();

				if (!empty(id)) {
					$.ajax(data_url, {
						data: {
							id: id,
							multiple: multiple ? 1 : 0
						}
					}).done(function(data){
						callback(data);
					});
				}
			},
			formatResult: function(data){
				var title = '';

				if (datatype == 'countries') {
					if (data.length < 1) {
						return '';
					} else {
						return "<img class='flag-dd' src='" + uri_root + "components/com_kinoarhiv/assets/themes/component/" + ka_theme + "/images/icons/countries/" + data.code + ".png'/>" + data.title;
					}
				} else if (datatype == 'names') {
					if (data.name != '') title += data.name;
					if (data.name != '' && data.latin_name != '') title += ' / ';
					if (data.latin_name != '') title += data.latin_name;
					if (data.date_of_birth != '0000-00-00') title += ' (' + data.date_of_birth + ')';

					return title;
				} else if (datatype == 'movies') {
					if (data.year == '0000') return data.title;

					return data.title + ' (' + data.year + ')';
				} else if (datatype == 'vendors') {
					if (data.company_name != '') title += data.company_name;
					if (data.company_name != '' && data.company_name_intl != '') title += ' / ';
					if (data.company_name_intl != '') title += data.company_name_intl;

					return title;
				} else {
					return data.title;
				}
			},
			formatSelection: function(data){
				var title = '';

				if (datatype == 'countries') {
					if (data.length < 1) {
						return '';
					} else {
						return "<img class='flag-dd' src='" + uri_root + "components/com_kinoarhiv/assets/themes/component/" + ka_theme + "/images/icons/countries/" + data.code + ".png'/>" + data.title;
					}
				} else if (datatype == 'names') {
					if (data.name != '') title += data.name;
					if (data.name != '' && data.latin_name != '') title += ' / ';
					if (data.latin_name != '') title += data.latin_name;
					if (data.date_of_birth != '0000-00-00') title += ' (' + data.date_of_birth + ')';

					return title;
				} else if (datatype == 'movies') {
					if (data.year == '0000') return data.title;

					return data.title + ' (' + data.year + ')';
				} else if (datatype == 'vendors') {
					if (data.company_name != '') title += data.company_name;
					if (data.company_name != '' && data.company_name_intl != '') title += ' / ';
					if (data.company_name_intl != '') title += data.company_name_intl;

					return title;
				} else {
					return data.title;
				}
			},
			escapeMarkup: function(m) { return m; }
		});

		if (sortable == true) {
			select.select2('container').find('ul.select2-choices').sortable({
				containment: 'parent',
				start: function () {
					$this.select2('onSortStart');
				},
				update: function () {
					$this.select2('onSortEnd');
				}
			});
		}
	});
});
