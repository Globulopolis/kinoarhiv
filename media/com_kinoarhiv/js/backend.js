/**
 * This file provide basic support for Kinoarhiv component and used in backend only.
 *
 * @package     Kinoarhiv.Administrator
 * @subpackage  com_kinoarhiv
 *
 * @copyright   Copyright (C) Libra.ms. All rights reserved.
 * @license     GNU General Public License version 2 or later
 * @url         http://киноархив.com/
 */

Kinoarhiv = window.Kinoarhiv || {};

(function(Kinoarhiv, document){
	'use strict';

	/**
	 * Update poster thumbnail.
	 *
	 * @param  {string|object}  data  Response from server.
	 *
	 * @return  {void}
	 */
	Kinoarhiv.updatePoster = function(data){
		jQuery(document).ready(function($){
			if (!Date.now) {
				Date.now = function(){
					return new Date().getTime();
				}
			}

			var doc = (!document.querySelector('input[name="img_folder"]')) ? parent.document : document;

			var response = (typeof data !== 'object') ? JSON.parse(data) : data,
				img_folder = doc.querySelector('input[name="img_folder"]').value,
				image = new Image(),
				img_preview = doc.querySelector('a.img-preview img');

			image.src = img_folder + 'thumb_' + response.filename + '?_=' + Date.now();
			image.onload = function(){
				img_preview.width = image.naturalWidth;
				img_preview.height = image.naturalHeight;
				img_preview.setAttribute('style', 'width: ' + image.naturalWidth + 'px; height: ' + image.naturalHeight + 'px;');
			};

			doc.querySelector('input[name="image_id"]').value = response.insertid;
			doc.querySelector('a.img-preview').setAttribute('href', img_folder + response.filename + '?_=' + Date.now());
			img_preview.setAttribute('src', img_folder + 'thumb_' + response.filename + '?_=' + Date.now());
		});
	};

	/**
	 * Set poster or photo.
	 *
	 * @param   {object}  el        Current clicked element.
	 * @param   {string}  section   Section. (can be: movie, name, trailer, soundtrack)
	 * @param   {number}  type      Item type(movie or name). For movie: 2 - poster. For name: 3 - photo.
	 * @param   {number}  item_id   Item ID(movie or name).
	 * @param   {number}  file_id   File ID.
	 * @param   {string}  filename  Filename.
	 *
	 * @return  {void}
	 */
	Kinoarhiv.selectFrontpageImage = function(el, section, type, item_id, file_id, filename){
		jQuery(document).ready(function($){
			var token = Kinoarhiv.getFormToken(),
				data  = {},
				msg_div = $(el).closest('#j-main-container').prev(),
				alert = '<div class="alert alert-error"><button type="button" class="close" data-dismiss="alert">&times;</button>';

			// Assign token
			data[token] = 1;

			$.ajax({
				type: 'POST',
				url: 'index.php?option=com_kinoarhiv&task=mediamanager.setFrontpage&format=json&section=' + section + '&type=' + type + '&id=' + item_id + '&item_id[]=' + file_id,
				data: data,
				dataType: 'json'
			}).done(function(response){
				if (!response.success) {
					msg_div.html(alert + response.message + '</div>');

					return;
				}

				Kinoarhiv.updatePoster({
					insertid: file_id,
					filename: filename
				});

				msg_div.html('<div class="alert alert-success"><button type="button" class="close" data-dismiss="alert">&times;</button>' + response.message + '</div>');
			}).fail(function(xhr, status, error){
				msg_div.html(alert + error + '</div>');
			});
		});
	};
}(Kinoarhiv, document));

jQuery(document).ready(function($){
	var msgOptions = {place: 'insertAfter', replace: true};

	// Attach 'more' plugin to hide overflowed content
	if (jQuery.fn.more) {
		$.more.setDefaults({
			length: 50,
			moreText: KA_vars.language.COM_KA_READ_MORE,
			lessText: KA_vars.language.COM_KA_READ_LESS
		});
		$('.more').more();
	}

	// Create datetime field
	$('.hasDatetime').each(function(index, element){
		var $this = $(element),
			input = $('input:text', $this),
			framework = $this.data('framework');

		// Sometimes input not wrapped into <div>
		if (input.length === 0) {
			input = $this;
		}

		if (input.val().toLowerCase() === 'now') {
			if (empty($this.data('time-format'))) {
				input.val(new Date().toISOString().slice(0, 10));
			} else if (empty($this.data('date-format'))) {
				input.val(new Date().toISOString().slice(11, 19));
			} else {
				input.val(new Date().toISOString().slice(0, 19).replace('T', ' '));
			}
		}

		if (input.attr('readonly')) {
			return;
		}

		if (framework === 'bootstrap') {
			$this.datetimepicker({
				language: $('html').attr('lang').substr(0, 2),
				weekStart: 1,
				todayBtn: true,
				autoclose: true,
				todayHighlight: true,
				startView: 2,
				keyboardNavigation: false
			});
		} else {
			if (input.data('type') === 'time') {
				input.timepicker({
					timeFormat: $this.data('time-format'),
					showOn: 'button'
				});
			} else if (input.data('type') === 'date') {
				input.datepicker({
					dateFormat: $this.data('date-format'),
					showButtonPanel: true,
					showOn: 'button'
				});
			} else if (input.data('type') === 'datetime') {
				input.datetimepicker({
					dateFormat: $this.data('date-format'),
					timeFormat: $this.data('time-format'),
					showButtonPanel: true,
					showOn: 'button'
				});
			}

			input.next('.ui-datepicker-trigger').addClass('btn btn-default').html('<i class="icon-calendar"></i>');
		}
	});

	// Process remote images upload
	$('.cmd-remote-urls').click(function(e){
		e.preventDefault();

		var $this = $(this),
			modal = $this.closest('.modal'),
			input = $('#remote_urls'),
			token = Kinoarhiv.getFormToken(),
			data  = {},
			refresh = JSON.parse($('input[name="refresh"]').val()) || [],
			content_type = input.data('content-type');

		if (empty(input.val())) {
			Aurora.message([{text: KA_vars.language.COM_KA_FILE_UPLOAD_ERROR, type: 'error'}], '#remote_urls', msgOptions);

			return false;
		}

		$this.attr('disabled', 'disabled');
		Kinoarhiv.showLoading('show', modal);

		// Assign data
		data[token] = 1;
		data['urls'] = input.val();

		$.ajax({
			type: 'POST',
			url: input.data('url'),
			data: data,
			dataType: 'json'
		}).done(function(response){
			if (!response.success) {
				Aurora.message([{text: response.message, type: 'alert'}], '#remote_urls', msgOptions);
			} else {
				var msg = !empty(response.message) ? response.message : KA_vars.language.COM_KA_FILES_UPLOAD_SUCCESS;
				Aurora.message([{text: msg, type: 'success'}], '#remote_urls', msgOptions);

				// Set upload state to 1
				$('input[name="file_uploaded"]').val(1);

				// Refresh element
				if (Object.keys(refresh).length > 0) {
					$(refresh.el_parent).find(refresh.el_trigger).trigger('click');
				}

				if (content_type === 'poster') {
					Kinoarhiv.updatePoster(response);
				}
			}

			$this.removeAttr('disabled');
			Kinoarhiv.showLoading('hide', modal);
		}).fail(function(xhr, status, error){
			Aurora.message([{text: error, type: 'error'}], '#remote_urls', msgOptions);
			$this.removeAttr('disabled');
			Kinoarhiv.showLoading('hide', modal);
		});
	});

	// Run copy process for gallery items
	$('#copyForm').submit(function(){
		if (jQuery.fn.select2) {
			if (empty($('#from_id').select2('val'))) {
				Aurora.message([{text: KA_vars.language.COM_KA_REQUIRED, type: 'alert'}], 'fieldset.copy', msgOptions);

				return false;
			}
		}

		$('.cmd-gallery-copyfrom').attr('disabled', 'disabled');

		return true;
	});

	if (jQuery.fn.pluploadQueue) {
		$('.hasUploader').each(function(index, element){
			var config = {},
				id = 'uploader_' + index,
				content_type = $(element).data('content-type'),
				error_div = $(element).next('.uploader-info'),
				filters = $(element).data('filters') || [],
				max_file_size = $(element).data('max_file_size') || '256kb',
				prevent_duplicates = $(element).data('prevent_duplicates') || true;

			config.url = $(element).data('url');
			config.chunk_size = $(element).data('chunk_size') || 0;
			config.file_data_name = $(element).data('file_data_name') || 'file';

			// Set filters for 'Select file' dialog
			if (!empty(filters)) {
				config.filters = {
					mime_types: [
						filters
					],
					max_file_size: max_file_size,
					prevent_duplicates: prevent_duplicates
				};
			}

			config.flash_swf_url = $(element).data('flash_swf_url') || KA_vars.uri_root + 'media/com_kinoarhiv/js/plupload/Moxie.swf';
			config.silverlight_xap_url = $(element).data('silverlight_xap_url') || KA_vars.uri_root + 'media/com_kinoarhiv/js/plupload/Moxie.xap';
			config.max_retries = $(element).data('max_retries') || 0;
			config.multipart = $(element).data('multipart') || true;
			config.multi_selection = $(element).data('multi_selection') || true;
			config.quality = $(element).data('quality') || 90;
			config.crop = $(element).data('crop') || false;
			config.runtimes = $(element).data('runtimes') || 'html5,flash,silverlight,html4';
			config.unique_names = $(element).data('unique_names') || false;
			config.dragdrop = $(element).data('dragdrop') || true;
			config.rename = $(element).data('rename') || false;
			config.multiple_queues = $(element).data('multiple_queues') || true;

			// Custom headers to send with the upload. Hash of name/value pairs.
			if (!empty($(element).data('headers'))) {
				config.headers = $(element).data('headers');
			}

			// Whether to send file and additional parameters as Multipart formated message.
			if (!empty($(element).data('multipart_params'))) {
				config.multipart_params = $(element).data('multipart_params');
			}

			// Either comma-separated list or hash of required features that chosen runtime should absolutely possess.
			if (!empty($(element).data('required_features'))) {
				config.required_features = $(element).data('required_features');
			}

			// Enable resizng of images on client-side.
			if (!empty($(element).data('resize'))) {
				config.resize = $(element).data('resize');
			}

			// If image is bigger, it will be resized.
			if (!empty($(element).data('width'))) {
				config.width = $(element).data('width');
			}

			// If image is bigger, it will be resized.
			if (!empty($(element).data('height'))) {
				config.height = $(element).data('height');
			}

			// Max number of files allowed to add in queue.
			if (!empty($(element).data('max_files'))) {
				config.max_files = $(element).data('max_files');
			}

			// Add an ID attribute
			$(element).attr('id', id);

			config.preinit = {
				Init: function(up, info){
					$('#' + id)
						.find('.plupload_buttons a:last')
						.after('<a class="plupload_button plupload_clear_all" href="#">' + KA_vars.language.JCLEAR + '</a>');

					$('#' + id + ' .plupload_clear_all').click(function(e){
						e.preventDefault();

						up.splice();
						$.each(up.files, function(i, file){
							up.removeFile(file);
						});

						error_div.html('').hide().removeClass('alert alert-error');
					});
				},
				UploadComplete: function(up, files){
					if (content_type === 'images') {
						$('input[name="file_uploaded"]').val(1);
					} else if (content_type === 'video' || content_type === 'subtitles' || content_type === 'chapters') {
						$('table[data-list="' + content_type + '"] .cmd-refresh-filelist').trigger('click');
					} else if (content_type === 'screenshot') {
						$('table[data-list="video"] .cmd-refresh-filelist').trigger('click');
					}
				}
			};

			config.init = {
				PostInit: function(){
					$('#' + id + '_container').removeAttr('title');
				},
				FilesAdded: function(up, files){
					if (config.max_files && (up.files.length > config.max_files)) {
						up.splice(config.max_files);
						Aurora.message([{text: plupload.sprintf(plupload.translate('Upload element accepts only %d file(s) at a time. Extra files were stripped.'), config.max_files), type: 'alert'}], $(element), msgOptions);
					}
				},
				FileUploaded: function(up, file, info){
					if (content_type === 'poster') {
						Kinoarhiv.updatePoster(info.response);
					}
				},
				Error: function(up, response){
					var error = JSON.parse(response.response);

					if (error_div.is(':hidden')) {
						error_div.show();
						error_div.addClass('alert alert-error');
					}

					error_div.html(error_div.html() + response.file.name + ': ' + error.message + '<br />');
				}
			};

			$(element).pluploadQueue(config);
		});
	}

	if (jQuery.fn.colorbox) {
		$('.img-preview').colorbox({maxHeight: '95%', maxWidth: '95%', fixed: true, photo: true});
	}

	if (jQuery.fn.jqGrid) {
		$('.jqgrid').each(function(index, element){
			var $this = $(element),
				width = isNaN(parseInt($this.data('width'), 10)) ? $($this.data('width')).width() : $this.data('width'),
				height = 0,
				navgrid = $this.data('navgrid_setup');

			var view_config = {
				width: width,
				closeOnEscape: true,
				beforeShowForm: function(form){
					document.querySelector('#viewmod' + element.id).style.top = '-108px';
				}
			};

			if (isNaN(parseInt($this.data('height'), 10))) {
				height = Math.round(($(window).height() - $($this.data('height')).offset().top) - 140);
			} else {
				height = $this.data('height');
			}

			$this.jqGrid({
				url: $this.data('url'),
				datatype: 'json',
				height: height < 100 ? 200 : height,
				width: width,
				shrinkToFit: true,
				multiselect: true,
				rownumbers: true,
				idPrefix: $this.data('idprefix'),
				colNames: $this.data('colnames'),
				colModel: $this.data('colmodel'),
				caption: '',
				toppager: $this.data('toppager'),
				pager: $this.data('pager'),
				sortname: $this.data('order'),
				sortorder: $this.data('orderby'),
				viewrecords: true,
				rowNum: parseInt($this.data('rownum'), 10),
				rowList: $(element).data('rowlist') || [],
				pgbuttons: !!$this.data('pgbuttons'),
				pginput: !!$this.data('pginput'),
				grouping: !!$(element).data('grouping') || false,
				groupingView: $(element).data('grouping-view') || {},
				ondblClickRow: function(rowid){
					$this.jqGrid('viewGridRow', rowid, view_config);
				},
				gridComplete: function(){
					$(this).find('.jqgroup').addClass('ui-widget-header');
				}
			}).jqGrid('navGrid', $this.next('div').attr('id'),
				{
					// Bottom nav config
					addfunc: function(){
						Kinoarhiv.openWindow($this.data('add_url'));
					},
					editfunc: function(){
						var chk = $this.children('tbody').find('input[type="checkbox"]').filter(':checked'),
							// Get the name of input, and get the last integer value(because it's a real item ID)
							id = chk.attr('name').split('_').slice(-1)[0],
							grid_id = $this.attr('id'),
							input_name = chk.attr('name').replace('jqg_' + grid_id + '_', '');

						Kinoarhiv.openWindow($this.data('edit_url') + '&row_id=' + parseInt(id, 10) + '&input_name=' + input_name);
					},
					delfunc: function(){
						if (!confirm(KA_vars.language.COM_KA_DELETE_SELECTED)) {
							return;
						}

						var items = $('.cbox', $this).filter(':checked'),
							data  = {},
							token = Kinoarhiv.getFormToken();

						data[token]   = 1;
						data['items'] = items.serializeArray();

						$.post($this.data('del_url'), data, function(response){
							if (!response.success) {
								Aurora.message([{text: response.message, type: 'alert'}], $this.closest('.ui-jqgrid'), {place: 'insertBefore', replace: true});
							}

							$this.trigger('reloadGrid');
						}).fail(function(xhr, status, error){
							Aurora.message([{text: error, type: 'error'}], '#system-message-container', msgOptions);
						});
					},
					addtext: navgrid.btn.lang.addtext, edittext: navgrid.btn.lang.edittext, deltext: navgrid.btn.lang.deltext,
					searchtext: navgrid.btn.lang.searchtext, refreshtext: navgrid.btn.lang.refreshtext,
					viewtext: navgrid.btn.lang.viewtext,
					view: true
				},
				{},
				{},
				{},
				{
					// Search form config
					width: width,
					closeAfterSearch: true, searchOnEnter: true, closeOnEscape: true
				},
				view_config
			).jqGrid('gridResize', {});
		});
	}

	if (jQuery.fn.sortable) {
		$('.sortable tbody').sortable({
			axis: 'y',
			cancel: 'input,textarea,button,select,option,.inactive',
			placeholder: 'ui-state-highlight',
			handle: '.sortable-handler',
			cursor: 'move',
			helper: function (e, tr) {
				var $originals = tr.children();
				var $helper = tr.clone();

				$helper.children().each(function(index){
					$(this).width($originals.eq(index).width());
				});

				return $helper;
			},
			update: function(e, ui){
				var table = $(this).parent();

				$.post(table.data('sort-url'), $('.order input', table).serialize() + '&' + Kinoarhiv.getFormToken() + '=1', function(response){
					if (!response.success) {
						Aurora.message([{
							text: response.message,
							type: 'alert'
						}], '#system-message-container', msgOptions);
					}
				}).fail(function(xhr, status, error){
					Aurora.message([{text: error, type: 'error'}], '#system-message-container', msgOptions);
				});
			}
		});
	}

	$('body').on('click', '.cmd-update-genre-stat', function(e){
		e.preventDefault();
		var $this = $(this);

		$.post('index.php?option=com_kinoarhiv&task=genres.updateStat&type=' + $this.data('gs-type') + '&id[]=' + $this.data('gs-id') + '&boxchecked=1&format=json', '&' + Kinoarhiv.getFormToken() + '=1', function(response){
			if (response.success) {
				var tagName = document.querySelector($this.data('gs-update')).tagName.toLowerCase();

				if (tagName === 'input' || tagName === 'select') {
					$($this.data('gs-update')).val(parseInt(response.total, 10));
				} else {
					$($this.data('gs-update')).text(response.total);
				}

				Aurora.message([{text: response.message, type: 'success'}], '#system-message-container', msgOptions);
			} else {
				Aurora.message([{text: response.message, type: 'alert'}], '#system-message-container', msgOptions);
			}
		});
	});
});
