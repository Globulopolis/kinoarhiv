/*
 * A JavaScript equivalent of PHP's empty. See http://phpjs.org/functions/empty/
 *
 * @return  boolean
 */
function empty(mixedVar) {
	var undef,
		key,
		i,
		len,
		emptyValues = [undef, null, false, 0, '', '0'];

	for (i = 0, len = emptyValues.length; i < len; i++) {
		if (mixedVar === emptyValues[i]) {
			return true
		}
	}

	if (typeof mixedVar === 'object') {
		for (key in mixedVar) {
			if (mixedVar.hasOwnProperty(key)) {
				return false
			}
		}
		return true
	}

	return false
}

/**
 * Format item title. If item have two fields for title, sometimes we need to properly process title if item
 * does not have one of these fields.
 *
 * @param   firstTitle   First item title.
 * @param   secondTitle  Second item title.
 * @param   date         Show date.
 * @param   separator    Separator to split titles.
 *
 * @return  string
 */
function formatItemTitle(firstTitle, secondTitle, date, separator) {
	var title = '';

	if (!empty(firstTitle)) {
		if (typeof firstTitle != 'undefined' && !empty(firstTitle)) {
			title += firstTitle;
		}

		if (!empty(firstTitle) && !empty(secondTitle)) {
			title += (typeof separator != 'undefined' && !empty(separator) ? separator : ' / ');
		}

		if (typeof secondTitle != 'undefined' && !empty(secondTitle)) {
			title += secondTitle;
		}

		// Do not validate date format because it's can be in different formats and hard to implement, and actually not necessary here.
		if (!empty(date) && date != '0000') {
			title += ' (' + date + ')';
		}

		return title;
	}
}

/**
 * Create a message and display
 *
 * @param   selector   Selector.
 * @param   text       Message text.
 * @param   placement  WHere to lace the message.
 * @param   btn_type   Show button to close or hide.
 * @param   btn_title  Text for close or hide button.
 *
 * @return  void
 */
function showMsg(selector, text, placement, btn_type, btn_title) {
	placement = (placement == 'undefinded') ? 'after' : placement;
	btn_type = (btn_type == 'undefinded' || empty(btn_type)) ? 'close' : btn_type;
	btn_title = (btn_title == 'undefinded' || empty(btn_title)) ? KA_vars.language.close : btn_title;

	if (jQuery.fn.aurora) {
		jQuery(selector).aurora({
			text: text,
			placement: placement,
			button: btn_type,
			button_title: '[' + btn_title + ']'
		});
	}
}

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

	$('.hasAutocomplete').each(function(index, element){
		if ($(element).data('select2-disabled')) {
			return;
		}

		var $this = $(element), config = {},
			data_url = $this.data('url'),
			datatype = $this.data('content'),
			multiple = $this.attr('multiple'),
			remote = $this.data('remote'),
			sortable = $this.data('sortable'),
			min_length = $this.data('minimum-input-length'),
			max_selection = $this.data('maximum-selection-size'),
			show_all = $this.data('remote-show-all'),
			ignore_ids = $this.data('ignore-ids');

		config.placeholder = !empty($this.data('placeholder')) ? $this.data('placeholder') : KA_vars.language.placeholder;
		config.allowClear = $this.data('allow-clear');

		if (min_length) {
			//config['minimumInputLength'] = parseInt(min_length, 10);
			config.minimumInputLength = parseInt(min_length, 10);
		}

		if (max_selection) {
			config.maximumSelectionSize = parseInt(max_selection, 10);
		}

		if (remote == true || (remote == false && $.inArray(datatype, ['countries', 'vendors', 'genres-movie', 'genres-name', 'tags', 'amplua']))) {
			// Do not add 'multiple' element to the configuration object if select2 attached to <select>!
			if (multiple) {
				config.multiple = true;
			}

			if (!data_url) {
				if (!empty(ignore_ids)) {
					ignore_ids = '&ignore[]=' + [ignore_ids].join('&ignore[]=');
				} else {
					ignore_ids = '';
				}

				data_url = 'index.php?option=com_kinoarhiv&task=api.read&element=' + datatype + '&format=json&tmpl=component&lang=' + KA_vars.language.tag.substr(0, 2) + '&' + ignore_ids;
			}

			// Require if ID from query results is not an `id` field.
			config.id = function(item){
				return !empty($this.data('key')) ? item[$this.data('key')] : item.value;
			};

			config.ajax = {
				cache: true,
				url: data_url,
				quietMillis: 250,
				data: function (term, page) {
					return {
						term: term,
						showAll: show_all ? 1 : 0,
						page: page
					}
				},
				results: function (data, page) {
					var more = (page * 30) < data.total_count;

					return {
						results: data,
						more: more
					};
				}
			};

			config.initSelection = function(element, callback){
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
			};
		} else {
			// Code bellow required only in configuration: data-remote="false" multiple="true" data-sortable="true"
			if (sortable == 'true' || sortable) {
				var data_content = $this.data('content-value'),
					data_count = data_content.length;

				if (multiple) {
					config.multiple = true;
				}

				config.initSelection = function(element, callback){
					var data = [];

					if (datatype == 'countries') {
						$(element.val().split(',')).each(function(){
							var i = 0;

							for (i; i < data_count; i++) {
								if (data_content[i]['value'] == this) {
									data.push({
										value: data_content[i]['value'],
										text: data_content[i]['text'],
										code: data_content[i]['code']
									});
								}
							}
						});
					} else {

					}

					callback(data);
				};

				// Require if ID from query results is not an `id` field.
				config.id = function(item){
					return !empty($this.data('key')) ? item[$this.data('key')] : item.value;
				};

				config.query = function(query){
					var result = {results: []},
						i = 0;

					if (datatype == 'countries') {
						for (i; i < data_count; i++) {
							result.results.push({
								value: data_content[i]['value'],
								text: data_content[i]['text'],
								code: data_content[i]['code']
							});
						}
					} else {
						result = data_content;
					}

					query.callback(result);
				}
			}
		}

		config.formatResult = function(data){
			var title = '';

			if (datatype == 'names') {
				return remote == true ? formatItemTitle(data.name, data.latin_name, data.date_of_birth) : data.text;
			} else if (datatype == 'movies') {
				var year = (remote == true || (sortable == 'true' || sortable)) ? data.year : $(data.element).data('year');
				title = !empty(data.text) ? data.text : data.title;

				return formatItemTitle(title, '', year);
			} else if (datatype == 'vendors') {
				return remote == true ? formatItemTitle(data.company_name, data.company_name_intl) : data.text;
			} else {
				return data.text;
			}
		};

		config.formatSelection = function(data){
			var title = '';

			if (datatype == 'countries') {
				if (data.length < 1) {
					return '';
				} else {
					var country_code = (remote == true || (sortable == 'true' || sortable)) ? data.code : $(data.element).data('country-code');

					return '<img class="flag-dd" src="components/com_kinoarhiv/assets/themes/component/' + KA_vars.ka_theme + '/images/icons/countries/' + country_code + '.png"/>' + data.text;
				}
			} else if (datatype == 'names') {
				return remote == true ? formatItemTitle(data.name, data.latin_name, data.date_of_birth) : data.text;
			} else if (datatype == 'movies') {
				var year = (remote == true || (sortable == 'true' || sortable)) ? data.year : $(data.element).data('year');
				title = !empty(data.text) ? data.text : data.title;

				return formatItemTitle(title, '', year);
			} else if (datatype == 'vendors') {
				return remote == true ? formatItemTitle(data.company_name, data.company_name_intl) : data.text;
			} else {
				return data.text;
			}
		};

		config.escapeMarkup = function(markup){ return markup; };

		var select = $this.select2(config);

		if (sortable == 'true' || sortable) {
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

	$('.hasSlider').each(function(index, element){
		var $this = $(element);

		if ($this.data('slider-disabled')) {
			return;
		}

		var slider = $this.slider();

		slider.on('slide', function(e){
			if (typeof $this.data('slider-input-min') != 'undefined' && $this.data('slider-input-min') != '') {
				$($this.data('slider-input-min')).val(e.value[0]);
			}

			if (typeof $this.data('slider-input-max') != 'undefined' && $this.data('slider-input-max') != '') {
				$($this.data('slider-input-max')).val(e.value[1]);
			}
		});
	});
});
