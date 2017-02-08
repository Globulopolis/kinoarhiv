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
	 * Store active tab and activate it. Require hidden input with name 'active_tab' and with cookie name as value.
	 *
	 * @return  void
	 */
	Kinoarhiv.setActiveTab = function(){
		jQuery(document).ready(function($){
			var tabs = $('a[data-toggle="tab"]');

			if (tabs.length > 0) {
				var input = $('input[name="active_tab"]'),
					active_tab_name = input.val(),
					active_tab = '#page0',
					expire = input.data('expire') || 30;

				if (typeof Cookies.get(active_tab_name) == 'undefined') {
					Cookies.set(active_tab_name, '#page0', {expires: expire});
				} else {
					active_tab = Cookies.get(active_tab_name);
					$('a[href="' + active_tab + '"]').trigger('click');
				}

				tabs.on('shown', function(e){
					Cookies.set(active_tab_name, $(e.target).attr('href'), {expires: expire});
				});
			}
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
			modal = $($this).closest('.modal'),
			input = $('#remote_urls'),
			token = Kinoarhiv.getFormToken(),
			data  = {},
			refresh = JSON.parse($('input[name="refresh"]').val()) || [];

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
	$('.cmd-gallery-copyfrom').click(function(e){
		e.preventDefault();

		var $this = $(this),
			token = Kinoarhiv.getFormToken(),
			data  = {
				'section':   $('input[name="section"]').val(),
				'type':      $('input[name="type"]').val(),
				'tab':       $('input[name="tab"]').val(),
				'id':        $('input[name="id"]').val(),
				'from_tab':  $('select[name="from_tab"]').val(),
				'from_id':   $('#from_id').select2('val')
			};

		if (data.from_id == 0 || data.from_id == '') {
			showMsg('fieldset.copy', KA_vars.language.COM_KA_REQUIRED);

			return false;
		}

		$this.attr('disabled', 'disabled');
		Kinoarhiv.blockUI('show');

		// Assign data
		data[token] = 1;

		$.ajax({
			type: 'POST',
			url: 'index.php?option=com_kinoarhiv&task=mediamanager.copyfrom&format=json',
			data: data,
			dataType: 'json'
		}).done(function(response){
			if (!response.success) {
				showMsg('fieldset.copy', response.message);
			}

			Kinoarhiv.blockUI();
			document.location.reload(true);
		}).fail(function(xhr, status, error){
			showMsg('fieldset.copy', error);
			$this.removeAttr('disabled');
			Kinoarhiv.blockUI();
		});
	});

	$('.hasUploader').each(function(index, element){
		var config = {},
			id = 'uploader_' + index,
			content_type = $(element).data('content-type');

		config.url = $(element).data('url');
		config.multipart_params = $(element).data('multipart_params');
		config.multi_selection = $(element).data('multi_selection');
		config.max_files = $(element).data('max_files');
		config.max_file_size = $(element).data('max_file_size');
		config.multiple_queues = $(element).data('multiple_queues');
		config.filters = $(element).data('filters');
		config.flash_swf_url = $(element).data('flash_swf_url') || KA_vars.uri_root + 'media/com_kinoarhiv/js/plupload/Moxie.swf';
		config.silverlight_xap_url = $(element).data('silverlight_xap_url') || KA_vars.uri_root + 'media/com_kinoarhiv/js/plupload/Moxie.xap';
		config.chunk_size = $(element).data('chunk_size');

		// Add an ID attribute
		$(element).attr('id', id);

		config.preinit = {
			init: function(up, info){
				$('#' + id)
					.find('.plupload_buttons a:last')
					.after('<a class="plupload_button plupload_clear_all" href="#">' + KA_vars.language.JCLEAR + '</a>');
				$('#' + id + ' .plupload_clear_all').click(function(e){
					e.preventDefault();

					up.splice();
					$.each(up.files, function(i, file){
						up.removeFile(file);
					});
				});
			},
			UploadComplete: function(up, files){
				if (content_type == 'images') {
					$('input[name="file_uploaded"]').val(1);
				} else if (content_type == 'video' || content_type == 'subtitles' || content_type == 'chapters') {
					$('table[data-list="' + content_type + '"] .cmd-refresh-filelist').trigger('click');
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
				/*var obj = $.parseJSON(info.response),
					div_video_scr_a = $('div.video_screenshot').find('#screenshot_file');

				if (div_video_scr_a.length == 0) {
					var a = '<a href="<?php echo $this->item->screenshot_folder_www; ?>' + obj.id + '?_=' + new Date().getTime() + '" class="tooltip-img" id="screenshot_file">' + obj.id + '</a>';
					$('div.video_screenshot div').eq(0).html('').append(a);
				} else {
					div_video_scr_a.text(obj.id);
					div_video_scr_a.attr('href', '<?php echo $this->item->screenshot_folder_www; ?>' + obj.id + '?_=' + new Date().getTime());
				}
				$('#screenshot_file').show();
				$('.cmd-file-remove.scrimage').attr('href', 'index.php?option=com_kinoarhiv&task=mediamanager.removeTrailerFiles&type=image&item_id=<?php echo $trailer_id; ?>&file=' + obj.id + '&id=<?php echo $movie_id; ?>&format=json');*/
			},
			StateChanged: function(up){
				if (up.state == plupload.STARTED) {
					// TODO Приводит к ошибке?
					//$('.cmd-file-remove.scrimage').trigger('click');
				}
			}
		};

		$(element).pluploadQueue(config);
	});
});
