/**
 * This file provide basic support for kinoarhiv components used in backend only.
 *
 * @package     Kinoarhiv.Administrator
 * @subpackage  com_kinoarhiv
 *
 * @copyright   Copyright (C) 2010 Libra.ms. All rights reserved.
 * @license     GNU General Public License version 2 or later
 * @url            http://киноархив.com/
 */

Kinoarhiv = window.Kinoarhiv || {};

(function(Kinoarhiv, document){
	'use strict';
	/**
	 * Update poster thumbnail.
	 *
	 * @param  data  Response from server
	 *
	 * @return  void
	 */
	Kinoarhiv.updatePoster = function(data){
		jQuery(document).ready(function($){
			if (!Date.now) {
				Date.now = function(){
					return new Date().getTime();
				}
			}

			var response = (typeof data != 'object') ? JSON.parse(data) : data,
				img_folder = $('input[name="img_folder"]').val(),
				image = new Image();

			image.src = img_folder + 'thumb_' + response.filename + '?_=' + Date.now();
			image.onload = function(){
				$('a.img-preview img').attr({
					width: image.naturalWidth,
					height: image.naturalHeight,
					style: 'width: ' + image.naturalWidth + 'px; height: ' + image.naturalHeight + 'px;'
				});
			};

			$('input[name="image_id"]').val(response.insertid);
			$('a.img-preview').attr('href', img_folder + response.filename + '?_=' + Date.now());
			$('a.img-preview img').attr('src', img_folder + 'thumb_' + response.filename + '?_=' + Date.now());
		});
	};
}(Kinoarhiv, document));

jQuery(document).ready(function($){
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

		if (input.val() == '') {
			showMsg('#remote_urls', KA_vars.language.COM_KA_FILE_UPLOAD_ERROR);
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
				showMsg('#remote_urls', response.message);
			} else {
				showMsg('#remote_urls', response.message != "" ? showMsg('#remote_urls', response.message) : KA_vars.language.COM_KA_FILES_UPLOAD_SUCCESS);

				// Set upload state to 1
				$('input[name="file_uploaded"]').val(1);

				// Refresh element
				if (Object.keys(refresh).length > 0) {
					$(refresh.el_parent).find(refresh.el_trigger).trigger('click');
				}

				if (content_type == 'poster') {
					Kinoarhiv.updatePoster(response);
				}
			}

			$this.removeAttr('disabled');
			Kinoarhiv.showLoading('hide', modal);
		}).fail(function(xhr, status, error){
			showMsg('#remote_urls', error);
			$this.removeAttr('disabled');
			Kinoarhiv.showLoading('hide', modal);
		});
	});

	// Run copy process for gallery items
	$('#copyForm').submit(function(){
		if (empty($('#from_id').select2('val'))) {
			showMsg('fieldset.copy', KA_vars.language.COM_KA_REQUIRED);

			return false;
		}

		$('.cmd-gallery-copyfrom').attr('disabled', 'disabled');

		return true;
	});

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
				if (content_type == 'images') {
					$('input[name="file_uploaded"]').val(1);
				} else if (content_type == 'video' || content_type == 'subtitles' || content_type == 'chapters') {
					$('table[data-list="' + content_type + '"] .cmd-refresh-filelist').trigger('click');
				} else if (content_type == 'screenshot') {
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
					showMsg(
						$(element),
						plupload.sprintf(plupload.translate('Upload element accepts only %d file(s) at a time. Extra files were stripped.'), config.max_files)
					);
				}
			},
			FileUploaded: function(up, file, info){
				if (content_type == 'poster') {
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

	if (jQuery.fn.colorbox) {
		$('.img-preview').colorbox({maxHeight: '95%', maxWidth: '95%', fixed: true, photo: true});
	}
});
