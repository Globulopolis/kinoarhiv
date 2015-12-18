<?php
/**
 * @package     Kinoarhiv.Administrator
 * @subpackage  com_kinoarhiv
 *
 * @copyright   Copyright (C) 2010 Libra.ms. All rights reserved.
 * @license     GNU General Public License version 2 or later
 * @url            http://киноархив.com/
 */

defined('_JEXEC') or die;

use Joomla\String\String;

JHtml::_('behavior.keepalive');

$input = JFactory::getApplication()->input;
$section = $input->get('section', '', 'word');
$type = $input->get('type', '', 'word');

if ($this->form->getValue('id') != 0):
	KAComponentHelper::loadMediamanagerAssets();
endif;
?>
<script type="text/javascript" src="<?php echo JURI::root(); ?>components/com_kinoarhiv/assets/js/jquery.colorbox.min.js"></script>
<?php KAComponentHelper::getScriptLanguage('jquery.colorbox-', false, 'colorbox', true); ?>
<script type="text/javascript" src="<?php echo JURI::base(); ?>components/com_kinoarhiv/assets/js/utils.js"></script>
<script type="text/javascript">
	//<![CDATA[
	jQuery(document).ready(function ($) {
		var bootstrapTooltip = $.fn.tooltip.noConflict();
		$.fn.bootstrapTlp = bootstrapTooltip;
		var bootstrapButton = $.fn.button.noConflict();
		$.fn.bootstrapBtn = bootstrapButton;

		<?php if ($this->form->getValue('id') != 0): ?>
		$('#v_sortable').sortable({
			placeholder: 'ui-state-highlight',
			cursor: 'move',
			update: function (e, ui) {
				$.post('<?php echo JUri::base(); ?>index.php?option=com_kinoarhiv&controller=mediamanager&task=saveOrderTrailerVideofile&item_id=<?php echo $input->get('item_id', 0, 'int'); ?>&format=json', $('#v_sortable input').serialize() + '&<?php echo JSession::getFormToken(); ?>=1', function (response) {
					if (response.success) {
						$.each($('#v_sortable input'), function (i, el) {
							$(el).val(i);
							$(el).next().find('.ord_numbering').text(i);
						});
						showMsg('#v_sortable', '<?php echo JText::_('COM_KA_SAVED'); ?>');
					} else {
						showMsg('#v_sortable', response.message);
					}
				}).fail(function (xhr, status, error) {
					showMsg('#system-message-container', error);
				});
			}
		});
		$('#sub_sortable').sortable({
			placeholder: 'ui-state-highlight',
			cursor: 'move',
			update: function (e, ui) {
				$.post('<?php echo JUri::base(); ?>index.php?option=com_kinoarhiv&controller=mediamanager&task=saveOrderTrailerSubtitlefile&item_id=<?php echo $input->get('item_id', 0, 'int'); ?>&format=json', $('#sub_sortable input').serialize() + '&<?php echo JSession::getFormToken(); ?>=1', function (response) {
					if (response.success) {
						$('.t-subtitles').trigger('click');
					} else {
						showMsg('#sub_sortable', response.message);
					}
				}).fail(function (xhr, status, error) {
					showMsg('#system-message-container', error);
				});
			}
		});
		$('#v_sortable, #sub_sortable').disableSelection();

		$('#sub_sortable').on('click', 'input:radio', function () {
			var _this = $(this);

			$.post('<?php echo JUri::base(); ?>index.php?option=com_kinoarhiv&controller=mediamanager&task=saveDefaultTrailerSubtitlefile&item_id=<?php echo $input->get('item_id', 0, 'int'); ?>&format=json', '&default=' + _this.closest('li').find(':hidden').eq(0).val() + '&<?php echo JSession::getFormToken(); ?>=1', function (response) {
				if (response.success) {
					showMsg('#sub_sortable', '<?php echo JText::_('COM_KA_SAVED'); ?>');
				} else {
					showMsg('#sub_sortable', response.message);
				}
			}).fail(function (xhr, status, error) {
				showMsg('#system-message-container', error);
			});
		});

		$('#filelist').on('click', 'a.cmd-file-remove', function(e, all){
			e.preventDefault();
			var _this = $(this);

			if (all != 0) {
				if (!confirm((_this.hasClass('all')) ? '<?php echo JText::_('COM_KA_DELETE_ALL'); ?>' : '<?php echo JText::_('JTOOLBAR_DELETE'); ?>?')) {
					return false;
				}
			}

			blockUI('show');
			$.post('<?php echo JUri::base(); ?>' + $(this).attr('href'), {'<?php echo JSession::getFormToken(); ?>': 1}, function (response) {
				if (response.success) {
					if (_this.hasClass('video')) {
						_this.closest('li').remove();

						$.each(_this.closest('ul').find('input:hidden'), function (i, el) {
							$(el).val(i);
							$(el).next().find('.ord_numbering').text(i);
						});
					} else if (_this.hasClass('subtitle')) {
						if (_this.hasClass('all')) {
							$('#sub_sortable').children('li').remove();
						} else {
							_this.closest('li').remove();

							$.each(_this.closest('ul').find('input:hidden'), function (i, el) {
								$(el).val(i);
								$(el).next().find('.ord_numbering').text(i);
							});
						}
					} else if (_this.hasClass('chapter')) {
						_this.closest('li').remove();
					} else if (_this.hasClass('scrimage')) {
						_this.closest('div.video_screenshot').find('#screenshot_file').hide();
					}

					showMsg(_this.closest('ul'), '<?php echo JText::_('COM_KA_FILE_DELETED_SUCCESS'); ?>');
				} else {
					alert(response.message);
				}
				blockUI();
			}).fail(function (xhr, status, error) {
				showMsg('#system-message-container', error);
				blockUI();
			});
		});

		$('#v_sortable').on('click', '.video-edit', function (e) {
			e.preventDefault();
			var _this = $(this);
			var dlg = $('<div style="display: none;" class="dialog" title="<?php echo JText::_('COM_KA_TRAILERS_HEADING_VIDEOS_DATA_EDIT'); ?>"><p class="ajax-loading"></p></div>');

			dlg.dialog({
				buttons: {
					'<?php echo JText::_('JAPPLY'); ?>': function () {
						$.post('<?php echo JUri::base(); ?>index.php?option=com_kinoarhiv&controller=mediamanager&task=saveVideofileData&trailer_id=<?php echo $input->get('item_id', 0, 'int'); ?>&movie_id=<?php echo $input->get('id', 0, 'int'); ?>&format=raw', $('#video_edit_form').serialize(), function (response) {
							if (response) {
								$('.t-video').trigger('click');
								showMsg('#video_edit_form .message', response == '1' ? '' : response);
							} else {
								showMsg('#video_edit_form .message', '<?php echo JText::_('JERROR_AN_ERROR_HAS_OCCURRED'); ?>');
							}
						}).fail(function (xhr, status, error) {
							showMsg('#video_edit_form .message', error);
						});
					},
					'<?php echo JText::_('JCANCEL'); ?>': function () {
						dlg.remove();
					}
				},
				resizable: false,
				modal: true,
				height: 330,
				width: 550,
				close: function (e, ui) {
					dlg.remove();
				}
			});

			dlg.load(_this.attr('href'));
		});

		$('#sub_sortable').on('click', '.lang-edit', function (e) {
			e.preventDefault();
			var _this = $(this);
			var dlg = $('<div style="display: none;" class="dialog" title="<?php echo JText::_('COM_KA_TRAILERS_HEADING_SUBTITLES_LANG_EDIT'); ?>"><p class="ajax-loading"></p></div>');

			dlg.dialog({
				buttons: {
					'<?php echo JText::_('JAPPLY'); ?>': function () {
						$.post('<?php echo JUri::base(); ?>index.php?option=com_kinoarhiv&controller=mediamanager&task=saveSubtitles&trailer_id=<?php echo $input->get('item_id', 0, 'int'); ?>&format=raw', {
							'subtitle_id': _this.closest('li').find('input').eq(0).val(),
							'language': $('#subtl_edit_form #jform_language_subtl option:selected').val(),
							'desc': $('#subtl_edit_form #jform_desc').val(),
							'default': $('#subtl_edit_form #jform_default option:selected').val(),
							'movie_id': <?php echo $input->get('id', 0, 'int'); ?>,
							'<?php echo JSession::getFormToken(); ?>': 1
						}, function (response) {
							if (response) {
								$('.t-subtitles').trigger('click');
							} else {
								showMsg('#subtl_edit_form .message', '<?php echo JText::_('JERROR_AN_ERROR_HAS_OCCURRED'); ?>');
							}
						}).fail(function (xhr, status, error) {
							showMsg('#subtl_edit_form .message', error);
						});
					},
					'<?php echo JText::_('JCANCEL'); ?>': function () {
						dlg.remove();
					}
				},
				resizable: false,
				modal: true,
				height: 300,
				width: 450,
				close: function (e, ui) {
					dlg.remove();
				}
			});

			dlg.load(_this.attr('href'));
		});

		$('.cmd-refresh-filelist').click(function(e){
			e.preventDefault();
			var _this = $(this),
				html = '';

			blockUI('show');
			$('body').aurora.destroy({indexes: 'all'});
			$.get(_this.attr('href'), function (response) {
				if (_this.hasClass('t-video')) {
					if (typeof response != 'object') {
						showMsg('#v_sortable', response);
						blockUI('hide');
						return false;
					}

					_this.closest('h3').next('.files').find('li').remove();

					$.each(response, function (k, object) {
						html += '<li>'
							+ '<input type="hidden" name="ord[]" value="' + k + '" />'
							+ '<div style="float: left;"><span class="ord_numbering">' + k + '</span>. ' + object.src + ' <a href="index.php?option=com_kinoarhiv&task=loadTemplate&template=upload_videodata_edit&model=mediamanager&view=mediamanager&format=raw&trailer_id=<?php echo $input->get('item_id', 0, 'int'); ?>&video_id=' + k + '" class="video-edit"><img src="components/com_kinoarhiv/assets/images/icons/table_edit.png" border="0" /></a></div>'
							+ '<div style="float: right;"><a href="index.php?option=com_kinoarhiv&controller=mediamanager&task=removeTrailerFiles&type=video&item_id=<?php echo $input->get('item_id', 0, 'int'); ?>&file=' + object.src + '&id=<?php echo $input->get('id', 0, 'int'); ?>&format=json" class="cmd-file-remove video"><span class="icon-delete"></span></a></div>'
							+ '</li>';
					});

					$('#v_sortable').append(html);
					blockUI('hide');
				} else if (_this.hasClass('t-subtitles')) {
					if (typeof response != 'object') {
						showMsg('#sub_sortable', response);
						blockUI('hide');
						return false;
					}

					_this.closest('h3').next('.files').find('li').remove();

					$.each(response, function (i, obj) {
						var checked = obj.default ? ' checked="checked"' : '';

						html += '<li>'
							+ '<input type="hidden" name="cord[]" value="' + i + '" />'
							+ '<div style="float: left;"><span class="ord_numbering">' + i + '</span>. ' + obj.file + ' (' + obj.lang_code + ', ' + obj.lang + ' <a href="index.php?option=com_kinoarhiv&task=loadTemplate&template=upload_subtitles_lang_edit&model=mediamanager&view=mediamanager&format=raw&trailer_id=<?php echo $input->get('item_id', 0, 'int'); ?>&subtitle_id=' + i + '" class="lang-edit"><img src="components/com_kinoarhiv/assets/images/icons/table_edit.png" border="0" /></a>)</div>'
							+ '<div style="float: right;"><input type="radio" name="sub_default" title="<?php echo JText::_('JDEFAULT'); ?>" class="hasTooltip" style="margin: 0px 4px 4px 0px;" autocomplete="off"' + checked + ' /> <a href="index.php?option=com_kinoarhiv&controller=mediamanager&task=removeTrailerFiles&type=subtitle&item_id=<?php echo $input->get('item_id', 0, 'int'); ?>&file=' + obj.file + '&id=<?php echo $input->get('id', 0, 'int'); ?>&format=json" class="cmd-file-remove subtitle"><span class="icon-delete"></span></a></div>'
							+ '</li>';
					});

					$('#sub_sortable').append(html);
					blockUI('hide');
				} else if (_this.hasClass('t-chapters')) {
					if (!response.file) {
						if (typeof response != 'object') {
							showMsg('#chap_sortable', response);
						}
						blockUI('hide');
						return false;
					}

					_this.closest('h3').next('.files').find('li').remove();

					html = '<li>'
						+ '<div style="float: left;">' + response.file + '</div>'
						+ '<div style="float: right;"><a href="index.php?option=com_kinoarhiv&controller=mediamanager&task=removeTrailerFiles&type=chapter&item_id=<?php echo $input->get('item_id', 0, 'int'); ?>&file=' + response.file + '&id=<?php echo $input->get('id', 0, 'int'); ?>&format=json" class="cmd-file-remove chapter"><span class="icon-delete"></span></a></div>'
						+ '</li>';

					$('#chap_sortable').append(html);
					blockUI('hide');
				}
			}).fail(function (xhr, status, error) {
				showMsg(_this.closest('h3').find('ul'), error);
				blockUI('hide');
			});
		});

		$('.video_screenshot').on('click', 'a.tooltip-img', function (e) {
			e.preventDefault();
			var url = $(this).attr('href');
			$.colorbox({href: url, maxHeight: '95%', maxWidth: '95%', fixed: true});
		});

		$('a.file-create-scr').click(function (e) {
			e.preventDefault();

			var _this = $(this),
				dlg = $('<div style="display: none;" class="dialog" title="<?php echo JText::_('COM_KA_TRAILERS_VIDEO_SCREENSHOT_CREATE_TITLE'); ?>"><p><label for="time"><?php echo JText::_('COM_KA_TRAILERS_VIDEO_SCREENSHOT_CREATE_TIME_DESC'); ?></label><br /><input type="text" name="time" id="time" value="00:02:00.000" required="required" size="16" maxlength="12" placeholder="00:00:00.000" /><br /><span class="err_msg red"></span></p></div>');

			dlg.dialog({
				buttons: {
					'<?php echo JText::_('JTOOLBAR_NEW'); ?>': function () {
						blockUI('show');
						$.post(_this.attr('href'), {
							'time': $('#time').val(),
							'<?php echo JSession::getFormToken(); ?>': 1
						}, function (response) {
							$('.err_msg', dlg).text('').hide();

							var pattern = /error:/g;
							if (pattern.test(response)) {
								$('.err_msg', dlg).text(response.substr(6)).show();
								blockUI();
								return false;
							}

							try {
								var obj = $.parseJSON(response);
							} catch (e) {
								blockUI();
								$('.err_msg', dlg).text('File not found').show();

								return;
							}

							dlg.dialog('option', {
								height: parseInt($(window).height() - 100, 10),
								width: parseInt($(window).width() - 100, 10)
							});

							$('p', dlg).html(obj.output);
							var div_video_scr_a = _this.closest('div.video_screenshot').find('#screenshot_file');

							if (div_video_scr_a.length == 0) {
								var a = '<a href="<?php echo $this->item->get('screenshot_folder_www'); ?>' + obj.file + '?_=' + new Date().getTime() + '" class="tooltip-img" id="screenshot_file">' + obj.file + '</a>';
								_this.closest('div').prev('div').html('').append(a);
							} else {
								div_video_scr_a.text(obj.file);
								div_video_scr_a.attr('href', '<?php echo $this->item->get('screenshot_folder_www'); ?>' + obj.file + '?_=' + new Date().getTime());
							}
							$('.cmd-file-remove.scrimage').attr('href', 'index.php?option=com_kinoarhiv&controller=mediamanager&task=removeTrailerFiles&type=image&item_id=<?php echo $input->get('item_id', 0, 'int'); ?>&file=' + obj.file + '&id=<?php echo $input->get('id', 0, 'int'); ?>&format=json');
							div_video_scr_a.show();

							$('.ui-dialog .ui-dialog-buttonset button').eq(0).remove();
							blockUI();
						}).fail(function (xhr, status, error) {
							showMsg('#system-message-container', error);
							blockUI();
							dlg.remove();
						});
					},
					'<?php echo JText::_('JTOOLBAR_CLOSE'); ?>': function () {
						dlg.remove();
					}
				},
				modal: true,
				height: 300,
				width: 450,
				close: function (e, ui) {
					dlg.remove();
				}
			});
		});

		$('a.file-upload-scr').click(function (e) {
			e.preventDefault();

			$('#image_uploader').pluploadQueue({
				runtimes: 'html5,flash,silverlight,html4',
				url: '<?php echo JUri::base(); ?>index.php?option=com_kinoarhiv&controller=mediamanager&task=upload&format=raw&section=<?php echo $section; ?>&type=<?php echo $type; ?>&upload=images&id=<?php echo $input->get('id', 0, 'int'); ?>&item_id=<?php echo $input->get('item_id', 0, 'int'); ?>',
				multipart_params: {
					'<?php echo JSession::getFormToken(); ?>': 1
				},
				max_file_size: '<?php echo $this->params->get('upload_limit'); ?>',
				unique_names: false,
				multiple_queues: true,
				multi_selection: false,
				max_files: 1,
				filters: [{title: 'Image', extensions: '<?php echo $this->params->get('upload_mime_images'); ?>'}],
				flash_swf_url: '<?php echo JURI::base(); ?>components/com_kinoarhiv/assets/js/mediamanager/plupload.flash.swf',
				silverlight_xap_url: '<?php echo JURI::base(); ?>components/com_kinoarhiv/assets/js/mediamanager/plupload.silverlight.xap',
				preinit: {
					init: function (up, info) {
						$('#image_uploader').find('.plupload_buttons a:last').after('<a class="plupload_button plupload_clear_all" href="#"><?php echo JText::_('JCLEAR'); ?></a>');
						$('#image_uploader .plupload_clear_all').click(function (e) {
							e.preventDefault();
							up.splice();
							$.each(up.files, function (i, file) {
								up.removeFile(file);
							});
						});
					}
				},
				init: {
					PostInit: function () {
						$('#image_uploader_container').removeAttr('title', '');
					},
					StateChanged: function (up) {
						if (up.state == plupload.STARTED) {
							// TODO Приводит к ошибке?
							//$('.cmd-file-remove.scrimage').trigger('click');
						}
					},
					FileUploaded: function (up, file, info) {
						var obj = $.parseJSON(info.response),
							div_video_scr_a = $('div.video_screenshot').find('#screenshot_file');

						if (div_video_scr_a.length == 0) {
							var a = '<a href="<?php echo $this->item->get('screenshot_folder_www'); ?>' + obj.id + '?_=' + new Date().getTime() + '" class="tooltip-img" id="screenshot_file">' + obj.id + '</a>';
							$('div.video_screenshot div').eq(0).html('').append(a);
						} else {
							div_video_scr_a.text(obj.id);
							div_video_scr_a.attr('href', '<?php echo $this->item->get('screenshot_folder_www'); ?>' + obj.id + '?_=' + new Date().getTime());
						}
						$('#screenshot_file').show();
						$('.cmd-file-remove.scrimage').attr('href', 'index.php?option=com_kinoarhiv&controller=mediamanager&task=removeTrailerFiles&type=image&item_id=<?php echo $input->get('item_id', 0, 'int'); ?>&file=' + obj.id + '&id=<?php echo $input->get('id', 0, 'int'); ?>&format=json');
					},
					FilesAdded: function(up, files){
						var max_files = up.getOption('max_files');

						if (up.files.length > max_files) {
							up.splice(max_files);
							showMsg(
								'#imgModalUpload .modal-body',
								mOxie.sprintf(plupload.translate('Upload element accepts only %d file(s) at a time. Extra files were stripped.'), max_files)
							);
						}
					}
				}
			});
			$('#imgModalUpload').modal();
		});
		<?php endif; ?>

		$('.cmd-form-urls').click(function (e) {
			e.preventDefault();
			var _this = $(this),
				dlg = $('<div style="display: none;" class="dialog" title=""><p></p></div>');

			if ($(this).hasClass('video')) {
				$('p', dlg).html($('#urls_layout_video'));
				dlg.dialog({
					title: '<?php echo JText::_('JTOOLBAR_ADD') . ' ' . String::strtolower(JText::_('COM_KA_TRAILERS_HEADING_UPLOAD_FILES_VIDEO')); ?>',
					buttons: {
						'<?php echo JText::_('JTOOLBAR_ADD'); ?>': function () {
							var input = $('.dialog #urls_url_video');
							if (input.val() != '') {
								var form_urls = $('#form_urls');
								form_urls.val(form_urls.val() + (form_urls.val() != '' ? "\n" : '') + '[url="' + input.val() + '" type="' + $('#urls_url_video_type').val() + '" player="' + $('#urls_url_video_inplayer').val() + '"]'); // Set value
								$('#urls_layout_video_form')[0].reset();
							} else {
								showMsg('.dialog .err_msg', '<?php echo JText::_('COM_KA_TRAILERS_HEADING_UPLOAD_URLS_ERR'); ?>');
							}
						},
						'<?php echo JText::_('JTOOLBAR_CLOSE'); ?>': function () {
							// We should use close instead of remove so div isn't removed from DOM
							dlg.dialog('close');
						}
					},
					modal: true,
					height: 360,
					width: 600,
					open: function (e, ui) {
						$('#urls_layout_video').show();
					}
				});
			} else if ($(this).hasClass('subtitles')) {
				$('p', dlg).html($('#urls_layout_subtitles'));
				dlg.dialog({
					title: '<?php echo JText::_('JTOOLBAR_ADD') . ' ' . String::strtolower(JText::_('COM_KA_TRAILERS_HEADING_SUBTITLES')); ?>',
					buttons: {
						'<?php echo JText::_('JTOOLBAR_ADD'); ?>': function () {
							var input = $('.dialog #urls_url_subtitles');
							if (input.val() != '') {
								var form_urls = $('#form_urls');
								form_urls.val(form_urls.val() + (form_urls.val() != '' ? "\n" : '') + '[url="' + input.val() + '" kind="subtitles" srclang="' + $('#urls_url_subtitles_lang').val() + '" label="' + $('#urls_url_subtitles_lang :selected').text() + '" default="' + $('#urls_url_subtitles_default').val() + '"]'); // Set value
								$('#urls_layout_subtitles_form')[0].reset();
							} else {
								showMsg('.dialog .err_msg', '<?php echo JText::_('COM_KA_TRAILERS_HEADING_UPLOAD_URLS_ERR'); ?>');
							}
						},
						'<?php echo JText::_('JTOOLBAR_CLOSE'); ?>': function () {
							// We should use close instead of remove so div isn't removed from DOM
							dlg.dialog('close');
						}
					},
					modal: true,
					height: 360,
					width: 600,
					open: function (e, ui) {
						$('#urls_layout_subtitles').show();
					}
				});
			} else if ($(this).hasClass('chapters')) {
				$('p', dlg).html('<label for="urls_url_chp"><?php echo JText::_('COM_KA_TRAILERS_HEADING_UPLOAD_URLS_CHAPTERS'); ?></label><input id="urls_url_chp" class="span6" type="text" size="35" value="" name="urls_url_chp" /><div class="err_msg"></div>');
				dlg.dialog({
					title: '<?php echo JText::_('JTOOLBAR_ADD') . ' ' . String::strtolower(JText::_('COM_KA_TRAILERS_HEADING_CHAPTERS')); ?>',
					buttons: {
						'<?php echo JText::_('JTOOLBAR_ADD'); ?>': function () {
							var input = $('.dialog #urls_url_chp');
							if (input.val() != '') {
								var form_urls = $('#form_urls');
								form_urls.val(form_urls.val() + "\n" + '[url="' + input.val() + '" kind="chapters"]'); // Set value
								input.val(''); // Clear input in dialog
							} else {
								showMsg('.dialog .err_msg', '<?php echo JText::_('COM_KA_TRAILERS_HEADING_UPLOAD_URLS_ERR'); ?>');
							}
						},
						'<?php echo JText::_('JTOOLBAR_CLOSE'); ?>': function () {
							dlg.remove();
						}
					},
					modal: true,
					height: 240,
					width: 600,
					close: function (e, ui) {
						dlg.remove();
					}
				});
			} else if ($(this).hasClass('help')) {
				$('p', dlg).html('<?php echo JText::_('COM_KA_TRAILERS_HEADING_UPLOAD_URLS_HELP', true); ?>');
				dlg.dialog({
					title: '<?php echo JText::_('JHELP'); ?>',
					buttons: {
						'<?php echo JText::_('JTOOLBAR_CLOSE'); ?>': function () {
							dlg.remove();
						}
					},
					modal: true,
					height: 400,
					width: 600,
					close: function (e, ui) {
						dlg.remove();
					}
				});
			}
		});

		$('.file-upload-video').click(function(e){
			e.preventDefault();

			$('#video_uploader').pluploadQueue({
				runtimes: 'html5,flash,silverlight,html4',
				url: '<?php echo JUri::base(); ?>index.php?option=com_kinoarhiv&controller=mediamanager&task=upload&format=raw&section=<?php echo $section; ?>&type=<?php echo $type; ?>&upload=video&id=<?php echo $input->get('id', 0, 'int'); ?>&item_id=<?php echo $input->get('item_id', 0, 'int'); ?>',
				multipart_params: {
					'<?php echo JSession::getFormToken(); ?>': 1
				},
				max_file_size: '<?php echo $this->params->get('upload_limit'); ?>',
				<?php if ($this->params->get('upload_chunk') == 1): ?>chunk_size: '<?php echo $this->params->get('upload_chunk_size'); ?>', <?php endif; ?>
				unique_names: false,
				multiple_queues: true,
				filters: [
					{title: 'Video files', extensions: '<?php echo $this->params->get('upload_mime_video'); ?>'}
				],
				flash_swf_url: '<?php echo JURI::base(); ?>components/com_kinoarhiv/assets/js/mediamanager/plupload.flash.swf',
				silverlight_xap_url: '<?php echo JURI::base(); ?>components/com_kinoarhiv/assets/js/mediamanager/plupload.silverlight.xap',
				preinit: {
					init: function(up, info){
						$('#video_uploader').find('.plupload_buttons a:last').after('<a class="plupload_button plupload_clear_all" href="#"><?php echo JText::_('JCLEAR'); ?></a>');
						$('#video_uploader .plupload_clear_all').click(function(e){
							e.preventDefault();
							up.splice();
							$.each(up.files, function(i, file){
								up.removeFile(file);
							});
						});
					},
					UploadComplete: function(up, files){
						$('.t-video').trigger('click');
					}
				},
				init: {
					PostInit: function(){
						$('#video_uploader_container').removeAttr('title', '');
					}
				}
			});
			$('#videoModalUpload').modal();
		});

		$('.file-upload-subtitles').click(function(e) {
			e.preventDefault();

			$('#subtitles_uploader').pluploadQueue({
				runtimes: 'html5,flash,silverlight,html4',
				url: '<?php echo JUri::base(); ?>index.php?option=com_kinoarhiv&controller=mediamanager&task=upload&format=raw&section=<?php echo $section; ?>&type=<?php echo $type; ?>&upload=subtitles&id=<?php echo $input->get('id', 0, 'int'); ?>&item_id=<?php echo $input->get('item_id', 0, 'int'); ?>',
				multipart_params: {
					'<?php echo JSession::getFormToken(); ?>': 1
				},
				max_file_size: '<?php echo $this->params->get('upload_limit'); ?>',
				filters: [{title: 'Subtitle files', extensions: '<?php echo $this->params->get('upload_mime_subtitles'); ?>'}],
				flash_swf_url: '<?php echo JURI::base(); ?>components/com_kinoarhiv/assets/js/mediamanager/plupload.flash.swf',
				silverlight_xap_url: '<?php echo JURI::base(); ?>components/com_kinoarhiv/assets/js/mediamanager/plupload.silverlight.xap',
				unique_names: false,
				multiple_queues: true,
				preinit: {
					init: function(up, info){
						$('#subtitles_uploader').find('.plupload_buttons a:last').after('<a class="plupload_button plupload_clear_all" href="#"><?php echo JText::_('JCLEAR'); ?></a>');
						$('#subtitles_uploader .plupload_clear_all').click(function(e){
							e.preventDefault();
							up.splice();
							$.each(up.files, function(i, file){
								up.removeFile(file);
							});
						});
					},
					UploadComplete: function(up, files){
						$('.t-subtitles').trigger('click');
					}
				},
				init: {
					PostInit: function(){
						$('#subtitles_uploader_container').removeAttr('title', '');
					}
				}
			});
			$('#subtitlesModalUpload').modal();
		});

		$('.file-upload-chapters').click(function(e){
			e.preventDefault();

			$('#chapters_uploader').pluploadQueue({
				runtimes: 'html5,flash,silverlight,html4',
				url: '<?php echo JUri::base(); ?>index.php?option=com_kinoarhiv&controller=mediamanager&task=upload&format=raw&section=<?php echo $section; ?>&type=<?php echo $type; ?>&upload=chapters&id=<?php echo $input->get('id', 0, 'int'); ?>&item_id=<?php echo $input->get('item_id', 0, 'int'); ?>',
				multipart_params: {
					'<?php echo JSession::getFormToken(); ?>': 1
				},
				max_file_size: '<?php echo $this->params->get('upload_limit'); ?>',
				unique_names: false,
				multiple_queues: true,
				multi_selection: false,
				max_files: 1,
				filters: [{title: 'Chapter files', extensions: '<?php echo $this->params->get('upload_mime_chapters'); ?>'}],
				flash_swf_url: '<?php echo JURI::base(); ?>components/com_kinoarhiv/assets/js/mediamanager/plupload.flash.swf',
				silverlight_xap_url: '<?php echo JURI::base(); ?>components/com_kinoarhiv/assets/js/mediamanager/plupload.silverlight.xap',
				preinit: {
					init: function(up, info){
						$('#chapters_uploader').find('.plupload_buttons a:last').after('<a class="plupload_button plupload_clear_all" href="#"><?php echo JText::_('JCLEAR'); ?></a>');
						$('#chapters_uploader .plupload_clear_all').click(function(e){
							e.preventDefault();
							up.splice();
							$.each(up.files, function(i, file){
								up.removeFile(file);
							});
						});
					},
					UploadComplete: function(up, files){
						$('.t-chapters').trigger('click');
					}
				},
				init: {
					PostInit: function(){
						$('#chapters_uploader_container').removeAttr('title', '');
					},
					FilesAdded: function(up, files){
						var max_files = up.getOption('max_files');

						if (up.files.length > max_files) {
							up.splice(max_files);
							showMsg(
								'#chaptersModalUpload .modal-body',
								mOxie.sprintf(plupload.translate('Upload element accepts only %d file(s) at a time. Extra files were stripped.'), max_files)
							);
						}
					}
				}
			});
			$('#chaptersModalUpload').modal();
		});
	});
	//]]>
</script>

<form action="<?php echo JUri::base(); ?>index.php" method="post" style="margin: 0;" name="adminForm" id="adminForm" class="admform-upload-trailers">
	<!-- At this first hidden input we will remove autofocus -->
	<input type="hidden" autofocus="autofocus"/>

	<div class="row-fluid">
		<div class="span12">
			<div class="row-fluid">
				<div class="span6">
					<fieldset class="form-horizontal">
						<?php foreach ($this->form->getFieldset('tr_edit') as $field): ?>
							<div class="control-group">
								<div class="control-label"><?php echo $field->label; ?></div>
								<div class="controls"><?php echo $field->input; ?></div>
							</div>
						<?php endforeach; ?>
						<div class="control-group">
							<?php echo $this->form->getLabel('urls'); ?>
							<div class="urls_form_toolbar">
								<a href="#" title="<?php echo JText::_('JTOOLBAR_ADD') . ' ' . String::strtolower(JText::_('COM_KA_TRAILERS_HEADING_UPLOAD_FILES_VIDEO')); ?>" class="hasTooltip cmd-form-urls video"><img src="<?php echo JUri::base(); ?>components/com_kinoarhiv/assets/images/icons/film.png" border="0"/></a>
								<a href="#" title="<?php echo JText::_('JTOOLBAR_ADD') . ' ' . String::strtolower(JText::_('COM_KA_TRAILERS_HEADING_SUBTITLES')); ?>" class="hasTooltip cmd-form-urls subtitles"><img src="<?php echo JUri::base(); ?>components/com_kinoarhiv/assets/images/icons/subtitles.png" border="0"/></a>
								<a href="#" title="<?php echo JText::_('JTOOLBAR_ADD') . ' ' . String::strtolower(JText::_('COM_KA_TRAILERS_HEADING_CHAPTERS')); ?>" class="hasTooltip cmd-form-urls chapters"><img src="<?php echo JUri::base(); ?>components/com_kinoarhiv/assets/images/icons/timeline_marker.png" border="0"/></a>
								<a href="#" title="<?php echo JText::_('JHELP'); ?>" class="hasTooltip cmd-form-urls help"><img src="<?php echo JUri::base(); ?>components/com_kinoarhiv/assets/images/icons/help.png" border="0"/></a>
							</div>
							<?php echo $this->form->getInput('urls'); ?>
						</div>
					</fieldset>
				</div>

				<?php if ($this->form->getValue('id') != 0): ?>
					<div class="span6" id="filelist">
						<h3 class="ui-widget ui-widget-content"><?php echo JText::_('COM_KA_TRAILERS_HEADING_UPLOAD_FILES_VIDEO'); ?>
							<span class="btn-small hasTooltip icon-help" title="<?php echo JText::_('COM_KA_TRAILERS_HEADING_SORT_VIDEOFILES_DESC'); ?>"></span>
							<a href="index.php?option=com_kinoarhiv&task=ajaxData&element=trailer_files&id=<?php echo $input->get('item_id', 0, 'int'); ?>&type=video&format=json" class="cmd-refresh-filelist t-video hasTooltip" title="<?php echo JText::_('JTOOLBAR_REFRESH'); ?>"><img src="<?php echo JUri::base(); ?>components/com_kinoarhiv/assets/images/icons/arrow_refresh_small.png" border="0"/></a>
						</h3>

						<div class="files">
							<ul id="v_sortable">
								<?php $files = json_decode($this->form->getValue('filename'));
								if (count($files) > 0):
									foreach ($files as $key => $item): ?>
										<li>
											<input type="hidden" name="ord[]" value="<?php echo (int) $key; ?>"/>

											<div style="float: left;">
												<span class="ord_numbering"><?php echo (int) $key; ?></span>. <?php echo $item->src; ?>
												<a href="index.php?option=com_kinoarhiv&task=loadTemplate&template=upload_videodata_edit&model=mediamanager&view=mediamanager&format=raw&trailer_id=<?php echo $input->get('item_id', 0, 'int'); ?>&video_id=<?php echo (int) $key; ?>" class="video-edit"><img src="components/com_kinoarhiv/assets/images/icons/table_edit.png" border="0"/></a>
											</div>
											<div style="float: right;">
												<a href="index.php?option=com_kinoarhiv&controller=mediamanager&task=removeTrailerFiles&type=video&item_id=<?php echo $input->get('item_id', 0, 'int'); ?>&file=<?php echo $item->src; ?>&id=<?php echo $input->get('id', 0, 'int'); ?>&format=json" class="cmd-file-remove video"><span class="icon-delete"></span></a>
											</div>
										</li>
									<?php endforeach;
								endif; ?>
							</ul>
							<div>
								<div class="video_screenshot">
									<div style="float: left;">
										<?php if (is_file($this->item->get('screenshot_path'))): ?>
											<a href="<?php echo $this->item->get('screenshot_path_www'); ?>?_=<?php echo time(); ?>" class="tooltip-img" id="screenshot_file"><?php echo $this->form->getValue('screenshot'); ?></a>
										<?php else: ?>
											&nbsp;
										<?php endif; ?>
									</div>
									<div style="float: right;">
										<a href="#" class="file-upload-scr hasTip" title="<?php echo JText::_('COM_KA_TRAILERS_VIDEO_SCREENSHOT_UPLOAD_TITLE'); ?>"><span class="icon-upload"></span></a>
										<a href="index.php?option=com_kinoarhiv&controller=mediamanager&task=create_screenshot&item_id=<?php echo $input->get('item_id', 0, 'int'); ?>&id=<?php echo $input->get('id', 0, 'int'); ?>&format=raw" class="file-create-scr hasTooltip" title="<?php echo JText::_('COM_KA_TRAILERS_VIDEO_SCREENSHOT_CREATE_TITLE'); ?>"><span class="icon-refresh"></span></a>
										<a href="index.php?option=com_kinoarhiv&controller=mediamanager&task=removeTrailerFiles&type=image&item_id=<?php echo $input->get('item_id', 0, 'int'); ?>&file=<?php echo $this->form->getValue('screenshot'); ?>&id=<?php echo $input->get('id', 0, 'int'); ?>&format=json" class="cmd-file-remove scrimage"><span class="icon-delete"></span></a>
									</div>
								</div>
								<div class="video_upload_files">
									<span class="divider">&nbsp;</span>
									<a href="#" class="file-upload-video hasTip" title="<?php echo JText::_('COM_KA_TRAILERS_VIDEO_UPLOAD_TITLE'); ?>"><span class="icon-upload"></span></a>
								</div>
							</div>
						</div>
						<br />

						<h3 class="ui-widget ui-widget-content"><?php echo JText::_('COM_KA_TRAILERS_HEADING_SUBTITLES'); ?>
							<span class="btn-small hasTooltip icon-help" title="<?php echo JText::_('COM_KA_TRAILERS_HEADING_SORT_VIDEOFILES_DESC'); ?>"></span>
							<a href="index.php?option=com_kinoarhiv&controller=mediamanager&task=removeTrailerFiles&type=subtitles&item_id=<?php echo $input->get('item_id', 0, 'int'); ?>&all=1&id=<?php echo $input->get('id', 0, 'int'); ?>&format=json" class="cmd-file-remove all subtitle hasTooltip" title="<?php echo JText::_('COM_KA_DELETE_ALL'); ?>"><img src="<?php echo JUri::base(); ?>components/com_kinoarhiv/assets/images/icons/mediamanager/delete.png" border="0"/></a>
							<a href="<?php echo JUri::base(); ?>index.php?option=com_kinoarhiv&task=ajaxData&element=trailer_files&id=<?php echo $input->get('item_id', 0, 'int'); ?>&type=subtitles&format=json" class="cmd-refresh-filelist t-subtitles hasTooltip" title="<?php echo JText::_('JTOOLBAR_REFRESH'); ?>"><img src="<?php echo JUri::base(); ?>components/com_kinoarhiv/assets/images/icons/arrow_refresh_small.png" border="0"/></a>
						</h3>

						<div class="files">
							<ul id="sub_sortable">
								<?php $subtitles = json_decode($this->form->getValue('_subtitles'));
								if (count($subtitles) > 0):
									foreach ($subtitles as $k => $sub_data): ?>
										<li>
											<input type="hidden" name="cord[]" value="<?php echo (int) $k; ?>"/>

											<div style="float: left;">
												<span class="ord_numbering"><?php echo $k; ?></span>. <?php echo $sub_data->file; ?>
												(<?php echo $sub_data->lang_code; ?>, <?php echo $sub_data->lang; ?>
												<a href="index.php?option=com_kinoarhiv&task=loadTemplate&template=upload_subtitles_lang_edit&model=mediamanager&view=mediamanager&format=raw&trailer_id=<?php echo $input->get('item_id', 0, 'int'); ?>&subtitle_id=<?php echo (int) $k; ?>" class="lang-edit"><img src="<?php echo JUri::base(); ?>components/com_kinoarhiv/assets/images/icons/table_edit.png" border="0"/></a>)
											</div>
											<div style="float: right;">
												<input type="radio" name="sub_default" title="<?php echo JText::_('JDEFAULT'); ?>" class="hasTooltip" style="margin: 0 4px 4px 0;" autocomplete="off"<?php echo $sub_data->default ? ' checked="checked"' : ''; ?> />
												<a href="index.php?option=com_kinoarhiv&controller=mediamanager&task=removeTrailerFiles&type=subtitle&item_id=<?php echo $input->get('item_id', 0, 'int'); ?>&file=<?php echo $sub_data->file; ?>&id=<?php echo $input->get('id', 0, 'int'); ?>&format=json" class="cmd-file-remove subtitle"><span class="icon-delete"></span></a>
											</div>
										</li>
									<?php endforeach;
								endif; ?>
							</ul>
							<div>
								<div class="video_upload_files">
									<span class="divider">&nbsp;</span>
									<a href="#" class="file-upload-subtitles hasTip" title="<?php echo JText::_('COM_KA_TRAILERS_HEADING_UPLOAD_FILES_SUBTL'); ?>"><span class="icon-upload"></span></a>
								</div>
							</div>
						</div>
						<br/>

						<h3 class="ui-widget ui-widget-content"><?php echo JText::_('COM_KA_TRAILERS_HEADING_CHAPTERS'); ?>
							<a href="index.php?option=com_kinoarhiv&task=ajaxData&element=trailer_files&id=<?php echo $input->get('item_id', 0, 'int'); ?>&type=chapters&format=json" class="cmd-refresh-filelist t-chapters hasTooltip" title="<?php echo JText::_('JTOOLBAR_REFRESH'); ?>"><img src="<?php echo JUri::base(); ?>components/com_kinoarhiv/assets/images/icons/arrow_refresh_small.png" border="0"/></a>
						</h3>

						<div class="files">
							<ul id="chap_sortable">
								<?php $chapters = json_decode($this->form->getValue('_chapters'));
								if (count($chapters) > 0):
									foreach ($chapters as $chapter): ?>
										<li>
											<div style="float: left;"><?php echo $chapter; ?></div>
											<div style="float: right;">
												<a href="index.php?option=com_kinoarhiv&controller=mediamanager&task=removeTrailerFiles&type=chapter&item_id=<?php echo $input->get('item_id', 0, 'int'); ?>&file=<?php echo $chapter; ?>&id=<?php echo $input->get('id', 0, 'int'); ?>&format=json" class="cmd-file-remove chapter"><span class="icon-delete"></span></a>
											</div>
										</li>
									<?php endforeach;
								endif; ?>
							</ul>
							<div>
								<div class="video_upload_files">
									<span class="divider">&nbsp;</span>
									<a href="#" class="file-upload-chapters hasTip" title="<?php echo JText::_('COM_KA_TRAILERS_HEADING_UPLOAD_FILES_CHAPTERS'); ?>"><span class="icon-upload"></span></a>
								</div>
							</div>
						</div>
						<br/>
					</div>
				<?php else: ?>
					<div class="span6" id="filelist"><?php echo JText::_('COM_KA_TRAILERS_HEADING_UPLOAD_FILES_NOTSAVED'); ?></div>
				<?php endif; ?>
			</div>
		</div>
	</div>

	<input type="hidden" name="option" value="com_kinoarhiv"/>
	<input type="hidden" name="controller" value="mediamanager"/>
	<input type="hidden" name="task" value="upload"/>
	<input type="hidden" name="section" value="movie"/>
	<input type="hidden" name="type" value="trailers"/>
	<input type="hidden" name="id" value="<?php echo $input->get('id', 0, 'int'); ?>"/>
	<input type="hidden" name="item_id" value="<?php echo ($this->form->getValue('id') != 0) ? $this->form->getValue('id') : 0; ?>"/>
	<?php echo JHtml::_('form.token'); ?>
</form>

<div id="urls_layout_video" style="display: none;">
	<form id="urls_layout_video_form">
		<label for="urls_url_video"><?php echo JText::_('COM_KA_TRAILERS_HEADING_UPLOAD_URLS_VIDEO'); ?></label>
		<input id="urls_url_video" class="span6" type="text" size="35" value="" name="urls_url_video"/>
		<label for="urls_url_video_type"><?php echo JText::_('COM_KA_TRAILERS_HEADING_UPLOAD_URLS_VIDEO_TYPE'); ?></label>
		<?php echo JHTML::_('select.genericlist',
			array('' => JText::_('JNONE'), 'video/mp4' => 'video/mp4', 'video/webm' => 'video/webm', 'video/ogv' => 'video/ogv'),
			'urls_url_video_type',
			array('class' => 'span3'),
			'value',
			'text',
			'',
			'urls_url_video_type'
		); ?>
		<label for="urls_url_video_inplayer"><?php echo JText::_('COM_KA_TRAILERS_HEADING_UPLOAD_URLS_VIDEO_INPLAYER'); ?></label>
		<?php echo JHTML::_('select.genericlist',
			array('false' => JText::_('JNO'), 'true' => JText::_('JYES')),
			'urls_url_video_inplayer',
			array('class' => 'span3'),
			'value',
			'text',
			'false',
			'urls_url_video_inplayer'
		); ?>
		<div class="err_msg"></div>
	</form>
</div>

<div id="urls_layout_subtitles" style="display: none;">
	<form id="urls_layout_subtitles_form">
		<label for="urls_url_subtitles"><?php echo JText::_('COM_KA_TRAILERS_HEADING_UPLOAD_URLS_SUBTITLES'); ?></label>
		<input id="urls_url_subtitles" class="span6" type="text" size="35" value="" name="urls_url_subtitles"/>
		<label for="urls_url_subtitles_lang"><?php echo JText::_('COM_KA_TRAILERS_HEADING_SUBTITLES_LANG_EDIT_SELECT'); ?></label>
		<?php echo JHTML::_('select.genericlist',
			$this->item->get('subtitles_lang_list'),
			'urls_url_subtitles_lang',
			array('class' => 'span3'),
			'value',
			'text',
			'en',
			'urls_url_subtitles_lang'
		); ?>
		<label for="urls_url_subtitles_default"><?php echo JText::_('JDEFAULT'); ?></label>
		<?php echo JHTML::_('select.genericlist',
			array('false' => JText::_('JNO'), 'true' => JText::_('JYES')),
			'urls_url_subtitles_default',
			array('class' => 'span3'),
			'value',
			'text',
			'false',
			'urls_url_subtitles_default'
		); ?>
		<div class="err_msg"></div>
	</form>
</div>

<?php
echo JLayoutHelper::render('layouts.edit.upload_video', array('params' => $this->params), JPATH_COMPONENT);
echo JLayoutHelper::render('layouts.edit.upload_image', array(), JPATH_COMPONENT);
