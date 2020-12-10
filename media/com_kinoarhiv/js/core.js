/**
 * This file provide basic support for kinoarhiv components used in frontend and backend.
 *
 * @package     Kinoarhiv.Site
 * @subpackage  com_kinoarhiv
 *
 * @copyright   Copyright (C) 2018 Libra.ms. All rights reserved.
 * @license     GNU General Public License version 2 or later
 * @url         http://киноархив.com/
 */

Kinoarhiv = window.Kinoarhiv || {};

// This object will be filled from PHP script. See KAComponentHelper::setHeadTags()
KA_vars = window.KA_vars || {};

(function(Kinoarhiv, document){
	'use strict';

	/**
	 * Get form token.
	 *
	 * @return  {string}
	 */
	Kinoarhiv.getFormToken = function(){
		return jQuery('input[type="hidden"]').filter(function(){
			return /^[0-9A-F]{32}$/i.test(this.name);
		}).attr('name');
	};

	/**
	 * Toggles the check state a group of boxes.
	 *
	 * @param   {string}  object    Main checkbox
	 * @param   {string}  selector  Checkbox wraper where to find checkbox
	 *
	 * @return  {void}
	 */
	Kinoarhiv.checkall = function(object, selector){
		jQuery(document).ready(function($){
			if ($(object).is(':checked')) {
				$(':checkbox', selector).prop('checked', true);
			} else {
				$(':checkbox', selector).prop('checked', false);
			}
		});
	};

	/**
	 * Block UI and display loader.
	 *
	 * @param   {string}  action    Show or hide block.
	 * @param   {object}  selector  Object where to attach.
	 *
	 * @return  {void}
	 */
	Kinoarhiv.showLoading = function(action, selector) {
		jQuery(document).ready(function($){
			var total = $('.modal-loading:hidden').length++;

			if (action === 'show') {
				// For document or body we need to block all visible area in browser window and prepend a div into element.
				var position = (selector.selector === 'document' || selector.selector === 'body') ? 'fixed' : 'absolute';
				var offset = selector.offset(),
					top    = typeof offset === 'undefined' ? 0 : offset.top,
					left   = typeof offset === 'undefined' ? 0 : offset.left,
					width  = selector.outerWidth(),
					height = selector.innerHeight(),
					html   = '<div class="modal-backdrop modal-loading" id="mdl' + total + '" style="position: ' + position + '; top: ' + top + 'px; left: ' + left + 'px; width: ' + width + 'px; height: ' + height + 'px; z-index: 10001;"><div class="ajax-loading" style="cursor: pointer;" title="Press to close">&nbsp;</div></div>';

				if (selector.selector === 'document' || selector.selector === 'body') {
					$(html).prependTo(selector);
				} else {
					$(html).insertAfter(selector);
				}

				// Bind 'close' for emergency situations
				$('.modal-backdrop').on('click', '.ajax-loading', function(){
					$(this).parent().remove();
				});
			} else {
				if (selector.selector === 'document' || selector.selector === 'body') {
					selector.find('div#mdl' + total).remove();
				} else {
					selector.next('div#mdl' + total).remove();
				}
			}
		});
	};

	/**
	 * Open new browser window.
	 *
	 * @param  {string}  url  URL to open.
	 *
	 * @return  {void}
	 */
	Kinoarhiv.openWindow = function(url){
		var handler = window.open(url);

		if (!handler) {
			Aurora.message([{text: KA_vars.language.COM_KA_NEWWINDOW_BLOCKED_A + url + KA_vars.language.COM_KA_NEWWINDOW_BLOCKED_B, type: 'alert'}], '#system-message-container', {replace: true});
		}
	};

	/**
	 * Format item title. If item have two fields for title, sometimes we need to properly process title if item
	 * does not have one of these fields.
	 *
	 * @param   {string}  firstTitle   First item title.
	 * @param   {string}  secondTitle  Second item title.
	 * @param   {string}  date         Show date.
	 * @param   {string}  separator    Separator to split titles.
	 *
	 * @return  {string}
	 */
	Kinoarhiv.formatItemTitle = function(firstTitle, secondTitle, date, separator) {
		var title = '';

		if (!empty(firstTitle)) {
			if (typeof firstTitle !== 'undefined' && !empty(firstTitle)) {
				title += firstTitle;
			}

			if (!empty(firstTitle) && !empty(secondTitle)) {
				title += (typeof separator !== 'undefined' && !empty(separator) ? separator : ' / ');
			}

			if (typeof secondTitle !== 'undefined' && !empty(secondTitle)) {
				title += secondTitle;
			}

			// Do not validate date format because it's can be in different formats and hard to implement, and actually not necessary here.
			if (!empty(date) && date !== '0000' && date !== '0000-00-00') {
				title += ' (' + date + ')';
			}

			return title;
		} else {
			return '';
		}
	}
}(Kinoarhiv, document));

/*
 * A JavaScript equivalent of PHP's empty. See http://phpjs.org/functions/empty/
 *
 * @param   {mixed}  mixedVar  Value to test.
 * @return  {boolean}
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

jQuery(document).ready(function($){
	if (jQuery.fn.select2) {
		$('.hasAutocomplete').each(function (index, element) {
			if ($(element).data('select2-disabled')) {
				return;
			}

			var $this = $(element),
				config = {},
				data_url = $this.data('url'),
				data_lang = $this.data('lang') || '',
				content = $this.data('content'),
				multiple = $this.attr('multiple'),
				remote = $this.data('remote'),
				sortable = $this.data('sortable'),
				min_length = $this.data('minimum-input-length'),
				max_selection = $this.data('maximum-selection-size'),
				show_all = $this.data('remote-show-all'),
				ignore_ids = $this.data('ignore-ids') || '',
				api_root = KA_vars.api_root || '',
				img_root = KA_vars.img_root || '',
				select = {},
				allowedTypes = [
					'countries', 'vendors', 'movieGenres', 'nameGenres', 'albumGenres', 'trackGenres', 'artistGenres',
					'tags', 'amplua', 'mediatypes'
				];

			config.placeholder = !empty($this.data('placeholder')) ? $this.data('placeholder') : KA_vars.language.JGLOBAL_SELECT_AN_OPTION;
			config.allowClear = $this.data('allow-clear');

			if (min_length) {
				//config['minimumInputLength'] = parseInt(min_length, 10);
				config.minimumInputLength = parseInt(min_length, 10);
			}

			if (max_selection) {
				config.maximumSelectionSize = parseInt(max_selection, 10);
			}

			if (remote === true || (remote === false && $.inArray(content, allowedTypes))) {
				// Do not add 'multiple' element to the configuration object if select2 attached to <select>!
				if (multiple) {
					config.multiple = true;
				}

				if (!data_url) {
					if (!empty(ignore_ids)) {
						ignore_ids = '&ignore_ids[]=' + [ignore_ids].join('&ignore_ids[]=');
					}

					if (!empty(data_lang)) {
						data_lang = '&data_lang=' + data_lang;
					}

					data_url = api_root + 'index.php?option=com_kinoarhiv&task=api.data&content=' + content + '&format=json&tmpl=component&lang=' + KA_vars.language.tag.substr(0, 2) + data_lang + ignore_ids + '&' + Kinoarhiv.getFormToken() + '=1';
				}

				// Require if ID from query results is not an `id` field.
				config.id = function (item) {
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

				config.initSelection = function (element, callback) {
					var id = $(element).val();

					if (!empty(id)) {
						$.ajax(data_url, {
							data: {
								id: id,
								multiple: multiple ? 1 : 0
							},
							cache: true
						}).done(function (data) {
							callback(data);
						});
					}
				};
			} else {
				// Code bellow required only in configuration: data-remote="false" multiple="true" data-sortable="true"
				if (sortable === 'true' || sortable) {
					var data_content = $this.data('content-value'),
						data_count = data_content.length;

					if (multiple) {
						config.multiple = true;
					}

					config.initSelection = function (element, callback) {
						var data = [];

						if (content === 'countries') {
							$(element.val().split(',')).each(function () {
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
							$(element.val().split(',')).each(function () {
								var i = 0;

								for (i; i < data_count; i++) {
									if (data_content[i]['value'] == this) {
										data.push({
											value: data_content[i]['value'],
											text: data_content[i]['text']
										});
									}
								}
							});
						}

						callback(data);
					};

					// Require if ID from query results is not an `id` field.
					config.id = function (item) {
						return !empty($this.data('key')) ? item[$this.data('key')] : item.value;
					};

					config.query = function (query) {
						var result = {results: []},
							i = 0;

						if (content === 'countries') {
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

			config.formatResult = function (data) {
				var title = '';

				if (content === 'countries') {
					if (data.length < 1) {
						return '';
					} else {
						var country_code = (remote === true || (sortable === 'true' || sortable)) ? data.code : $(data.element).data('country-code');

						return '<img class="flag-dd" src="' + img_root + 'media/com_kinoarhiv/images/icons/countries/' + country_code + '.png"/>' + data.text;
					}
				} else if (content === 'names') {
					return remote === true ? Kinoarhiv.formatItemTitle(data.name, data.latin_name, data.date_of_birth) : data.text;
				} else if (content === 'movies' || content === 'albums') {
					var year = (remote === true || (sortable === 'true' || sortable)) ? data.year : $(data.element).data('year');
					title = !empty(data.text) ? data.text : data.title;

					return Kinoarhiv.formatItemTitle(title, '', year);
				} else {
					return data.text;
				}
			};

			config.formatSelection = function (data) {
				var title = '';

				if (content === 'countries') {
					if (data.length < 1) {
						return '';
					} else {
						var country_code = (remote === true || (sortable === 'true' || sortable)) ? data.code : $(data.element).data('country-code');

						return '<img class="flag-dd" src="' + img_root + 'media/com_kinoarhiv/images/icons/countries/' + country_code + '.png"/>' + data.text;
					}
				} else if (content === 'names') {
					return remote === true ? Kinoarhiv.formatItemTitle(data.name, data.latin_name, data.date_of_birth) : data.text;
				} else if (content === 'movies' || content === 'albums') {
					var year = (remote === true || (sortable === 'true' || sortable)) ? data.year : $(data.element).data('year');
					title = !empty(data.text) ? data.text : data.title;

					return Kinoarhiv.formatItemTitle(title, '', year);
				} else {
					return data.text;
				}
			};

			config.escapeMarkup = function (markup) {
				return markup;
			};

			try {
				select = $this.select2(config);
			} catch (e) {
				console.error('Wrong data-content attribute value!');

				return false;
			}

			if (sortable === 'true' || sortable) {
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
	}

	if (jQuery.fn.slider) {
		$('.hasSlider').each(function (index, element) {
			var $this = $(element);

			if ($this.data('slider-disabled')) {
				return;
			}

			var slider = $this.slider();

			slider.on('slide', function (e) {
				if (typeof $this.data('slider-input-min') !== 'undefined' && !empty($this.data('slider-input-min'))) {
					$($this.data('slider-input-min')).val(e.value[0]);
				}

				if (typeof $this.data('slider-input-max') !== 'undefined' && !empty($this.data('slider-input-max'))) {
					$($this.data('slider-input-max')).val(e.value[1]);
				}
			});
		});
	}
});
