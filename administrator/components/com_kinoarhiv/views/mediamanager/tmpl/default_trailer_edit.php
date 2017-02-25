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

JHtml::_('behavior.formvalidator');
JHtml::_('behavior.keepalive');
JHtml::_('bootstrap.tooltip');
JHtml::_('stylesheet', 'media/com_kinoarhiv/css/colorbox.css');
JHtml::_('script', 'media/com_kinoarhiv/js/jquery.colorbox.min.js');
KAComponentHelper::getScriptLanguage('jquery.colorbox-', 'media/com_kinoarhiv/js/i18n/colorbox/', true, true);
JHtml::_('script', 'media/com_kinoarhiv/js/jquery.plugin.min.js');
JHtml::_('script', 'media/com_kinoarhiv/js/jquery.more.min.js');

$input            = JFactory::getApplication()->input;
$this->section    = $input->get('section', '', 'word');
$this->type       = $input->get('type', '', 'word');
$this->tab        = $input->get('tab', '', 'int');
$this->id         = $input->get('id', 0, 'int');
$trailer_id       = $input->get('item_id', null, 'array');
$this->trailer_id = $trailer_id[0];
?>
<script type="text/javascript">
	Joomla.submitbutton = function(task) {
		if (task == 'mediamanager.cancel' || document.formvalidator.isValid(document.getElementById('item-form'))) {
			Joomla.submitform(task, document.getElementById('item-form'));
		}
	};

	jQuery(document).ready(function($){
		// Bind actions to the URLs modal button
		$('.cmd-form-urls').click(function(e){
			e.preventDefault();

			var target_form = $('#form_trailer_urls');

			if ($(this).data('type') == 'video') {
				var url_video = $('#urls_url_video');

				if (url_video.val() != '') {
					target_form.val(
						target_form.val()
						+ (target_form.val() != '' ? "\n" : '')
						+ '[url="' + url_video.val() + '" type="' + $('#form_trailer_finfo_video_type').val() + '" player="' + $('#urls_url_video_inplayer').val() + '"]'
					);
					$('#urls_layout_video_form')[0].reset();

					return true;
				}

				showMsg('#urls_layout_video_form', '<?php echo JText::_('COM_KA_TRAILERS_UPLOAD_URLS_ERR'); ?>');
			} else if ($(this).data('type') == 'subtitles') {
				var url_subtitle = $('#urls_url_subtitles');

				if (url_subtitle.val() != '') {
					var lang = $('#urls_url_subtitles_lang');

					target_form.val(
						target_form.val()
						+ (target_form.val() != '' ? "\n" : '')
						+ '[url="' + url_subtitle.val() + '" kind="subtitles" srclang="' + lang.val() + '" label="' + $(':selected', lang).text() + '" default="' + $('#urls_url_subtitles_default').val() + '"]');
					$('#urls_layout_subtitles_form')[0].reset();

					return true;
				}

				showMsg('#urls_layout_subtitles_form', '<?php echo JText::_('COM_KA_TRAILERS_UPLOAD_URLS_ERR'); ?>');
			} else if ($(this).data('type') == 'chapters') {
				var url_chapter = $('#urls_url_chapters');

				if (url_chapter.val() != '') {
					target_form.val(target_form.val() + "\n" + '[url="' + url_chapter.val() + '" kind="chapters"]');
					$('#urls_layout_chapters_form')[0].reset();

					return true;
				}

				showMsg('#urls_layout_chapters_form', '<?php echo JText::_('COM_KA_TRAILERS_UPLOAD_URLS_ERR'); ?>');
			}
		});

		var filelist = $('.filelist'),
			sortable_list = $('.filelist tbody');

		sortable_list.sortable({
			axis:'y',
			cancel: 'input,textarea,button,select,option,.inactive',
			placeholder: 'ui-state-highlight',
			handle: '.sortable-handler',
			cursor: 'move',
			helper: function(e, tr){
				var $originals = tr.children(),
					$helper = tr.clone();

				$helper.children().each(function(index){
					$(this).width($originals.eq(index).width());
				});

				return $helper;
			},
			update: function(){
				var $this = $(this);

				$.post($this.parent().data('sort-url'), ($('input[name="ord[]"]', $this).serialize() + '&' + $.param({'<?php echo JSession::getFormToken(); ?>': 1})), function(response){
					if (!response.success) {
						showMsg('#system-message-container', response.message);
					}
				}).fail(function(xhr, status, error){
					showMsg('#system-message-container', error);
				});
			}
		});

		// Get and update filelist
		$('.cmd-refresh-filelist').click(function(e){
			e.preventDefault();

			var $this = $(this),
				table = $this.closest('table'),
				tbody = $this.closest('thead').next('tbody'),
				list  = table.data('list'),
				html  = '';

			Kinoarhiv.showLoading('show', table);

			$.ajax({
				url: $this.attr('href'),
				data: {'<?php echo JSession::getFormToken(); ?>': 1}
			}).done(function(response){
				if (response.length < 1) {
					showMsg('#system-message-container', response.message);
					Kinoarhiv.showLoading('hide', table);

					return false;
				}

				var total = Object.keys(response[list]).length,
					sort_handler = total < 2 ? ' inactive tip-top' : '';

				if (list == 'video') {
					// Update row with screenshot
					if (!empty(response.screenshot.file)) {
						if (!Date.now) {
							Date.now = function(){
								return new Date().getTime();
							}
						}

						var screenshot_row = $this.closest('table').find('tfoot .screenshot'),
							screenshot_filename_class = response.screenshot.is_file == 0 ? ' error_image' : '';

						screenshot_row.find('div').remove();
						screenshot_row.prepend('<div class="item-row"><a href="<?php echo $this->folder_path_www; ?>' + response.screenshot.file + '?_=' + Date.now() + '" id="screenshot_file" class="more' + screenshot_filename_class + '">' + response.screenshot.file + '</a></div>');
						screenshot_row.find('.more').more('destroy').more();
					}

					// Run updates only if total > 0
					if (total == 0) {
						tbody.find('tr').remove();
						tbody.prepend('<tr><td colspan="4"><?php echo JText::_('COM_KA_NO_FILES'); ?></td></tr>');
						Kinoarhiv.showLoading('hide', table);

						return;
					}

					// Update rows
					$.each(response[list], function(key, object){
						var file_info = formatItemTitle(object.type, object.resolution, '', ', '),
							file_info_text = file_info != "" ? ' <span class="gray">(' + file_info + ')</span>': '',
							filename_class = object.is_file == 0 ? ' red' : '';

						html += '<tr>' +
							'<td width="1%" class="ord_numbering">' +
								'<span class="sortable-handler' + sort_handler + '"><i class="icon-menu"></i></span>' +
								'<input type="hidden" name="ord[]" value="' + key + '" />' +
							'</td>' +
							'<td width="4%">' + key + '</td>' +
							'<td class="item-row"><span class="more' + filename_class + '">' + object.src + '</span>' + file_info_text + '</td>' +
							'<td width="12%">' +
								'<div class="pull-right">' +
									'<a href="index.php?option=com_kinoarhiv&task=mediamanager.editTrailerFile&type=video&id=<?php echo $this->id; ?>&item_id=<?php echo $this->trailer_id; ?>&item=' + key + '&format=raw" class="cmd-file-edit"><span class="icon-pencil"></span></a>&nbsp;<a href="index.php?option=com_kinoarhiv&task=mediamanager.removeTrailerFiles&type=video&id=<?php echo $this->id; ?>&item_id=<?php echo $this->trailer_id; ?>&item=' + key + '&format=json" class="cmd-remove-file"><span class="icon-delete"></span></a>' +
								'</div>' +
							'</td>' +
						'</tr>';
					});

					tbody.find('tr').remove();
					tbody.prepend(html);
				} else if (list == 'subtitles') {
					if (total == 0) {
						tbody.find('tr').remove();
						tbody.prepend('<tr><td colspan="5"><?php echo JText::_('COM_KA_NO_FILES'); ?></td></tr>');
						Kinoarhiv.showLoading('hide', table);

						return;
					}

					$.each(response[list], function(key, object){
						var lang = object.lang != "" ? ' <span class="gray">(' + object.lang + ')</span>' : '',
							filename_class = object.is_file == 0 ? ' red' : '';

						html += '<tr>' +
							'<td width="1%" class="ord_numbering">' +
								'<span class="sortable-handler' + sort_handler + '"><i class="icon-menu"></i></span>' +
								'<input type="hidden" name="ord[]" value="' + key + '" />' +
							'</td>' +
							'<td width="4%">' + key + '</td>' +
							'<td class="item-row"><span class="more' + filename_class + '">' + object.file + '</span>' + lang + '</td>' +
							'<td width="4%">';

							if (object.default) {
								html += '<a class="btn btn-micro cmd-subtitle-default" href="index.php?option=com_kinoarhiv&task=mediamanager.subtitleUnsetDefault&item_id=<?php echo $this->trailer_id; ?>&id=<?php echo $this->id; ?>&item=' + key + '&format=json"><span class="icon-featured"></span></a>';
							} else {
								html += '<a class="btn btn-micro cmd-subtitle-default" href="index.php?option=com_kinoarhiv&task=mediamanager.subtitleSetDefault&item_id=<?php echo $this->trailer_id; ?>&id=<?php echo $this->id; ?>&item=' + key + '&format=json"><span class="icon-unfeatured"></span></a>';
							}

							html += '</td>' +
							'<td>' +
								'<div class="pull-right">' +
									'<a href="index.php?option=com_kinoarhiv&task=mediamanager.editTrailerFile&type=subtitles&id=<?php echo $this->id; ?>&item_id=<?php echo $this->trailer_id; ?>&item=' + key + '&format=raw" class="cmd-file-edit"><span class="icon-pencil"></span></a>&nbsp;<a href="index.php?option=com_kinoarhiv&task=mediamanager.removeTrailerFiles&type=subtitles&id=<?php echo $this->id; ?>&item_id=<?php echo $this->trailer_id; ?>&item=' + key + '&format=json" class="cmd-remove-file"><span class="icon-delete"></span></a>' +
								'</div>' +
							'</td>' +
						'</tr>';
					});

					tbody.find('tr').remove();
					tbody.prepend(html);
				} else if (list == 'chapters') {
					if (total == 0) {
						tbody.find('tr').remove();
						tbody.prepend('<tr><td colspan="4"><?php echo JText::_('COM_KA_NO_FILES'); ?></td></tr>');
						Kinoarhiv.showLoading('hide', table);

						return;
					}

					var filename_class = response[list].is_file == 0 ? ' red' : '';

					html += '<tr>' +
						'<td class="item-row"><span class="more' + filename_class + '">' + response[list].file + '</span></td>' +
						'<td class="center" width="9%">' +
							'<div class="pull-right">' +
								'<a href="index.php?option=com_kinoarhiv&task=mediamanager.editTrailerFile&type=chapters&id=<?php echo $this->id; ?>&item_id=<?php echo $this->trailer_id; ?>&item=0&format=raw" class="cmd-file-edit"><span class="icon-pencil"></span></a>&nbsp;<a href="index.php?option=com_kinoarhiv&task=mediamanager.removeTrailerFiles&type=chapters&id=<?php echo $this->id; ?>&item_id=<?php echo $this->trailer_id; ?>&item=0&format=json" class="cmd-remove-file"><span class="icon-delete"></span></a>' +
							'</div>' +
						'</td>' +
					'</tr>';

					tbody.find('tr').remove();
					tbody.prepend(html);
				}

				// Re-init 'more' plugin
				tbody.find('.more').more('destroy').more();

				Kinoarhiv.showLoading('hide', table);
			}).fail(function(xhr, status, error){
				showMsg('#system-message-container', error);
				Kinoarhiv.showLoading('hide', table);
			});
		});

		$('.screenshot').on('click', '#screenshot_file', function(e){
			e.preventDefault();

			var url = $(this).attr('href');
			$.colorbox({href: url, maxHeight: '95%', maxWidth: '95%', fixed: true});
		});

		// Bind action to 'Set default' button for subtitles
		filelist.on('click', '.cmd-subtitle-default', function(e){
			e.preventDefault();

			var $this = $(this),
				item_state = $this.hasClass('is_default');

			$.ajax({
				type: 'POST',
				url: $this.attr('href'),
				data: {'<?php echo JSession::getFormToken(); ?>': 1}
			}).done(function(response){
				if (response.success) {
					$('table[data-list="subtitles"] .cmd-refresh-filelist').trigger('click');

					return;
				}

				showMsg('#system-message-container', response.message);
			}).fail(function (xhr, status, error) {
				showMsg('#system-message-container', error);
			});
		});

		// Bind 'show modal' functional for upload
		$('.cmd-upload').click(function(e){
			e.preventDefault();

			var tab = $(this).data('upload-tab'),
				modal = {};

			if (tab == 'screenshot') {
				modal = $('#imgModalUpload');
			} else {
				modal = $('#uploadVideoModal');

				$('.modal-header h3', modal).text($(this).text());
				$('a[href="#' + tab + '"]', modal).tab('show');
			}

			modal.modal('toggle');
		});

		// Bind 'show modal' functional for trailer files data edit
		filelist.on('click', '.cmd-file-edit', function(e){
			e.preventDefault();

			var $this = $(this),
				list  = $this.closest('table').data('list'),
				title = '',
				modal = $('#editFileModal');

			$('.modal-header h3', modal).text('<?php echo JText::_('COM_KA_TRAILERS_HEADING_VIDEOS_DATA_EDIT'); ?>');

			$.ajax({
				url: $this.attr('href'),
				cache: false
			}).done(function(response){
				$('.modal-body', modal).html(response);

				modal.popover({ selector: '.hasPopover', trigger: 'hover' });
				modal.modal('toggle');
			}).fail(function (xhr, status, error) {
				showMsg('#system-message-container', error);
			});
		});

		// Bind 'save' functional for trailer files data edit
		$('#editFileModal').on('click', '.cmd-fileinfo-save', function(e){
			e.preventDefault();

			var $this = $(this),
				modal = $this.closest('#editFileModal'),
				list  = modal.find('input[name="list"]').val();

			if (!document.formvalidator.isValid(document.getElementById('fileinfo-item-form'))) {
				// Due to the formvalidator specifics in Joomla we need to copy error message in new place in modal
				var msg_container = $('#system-message-container'),
					msg = $('button', msg_container).siblings('div').text();

				msg_container.html("");
				showMsg('#fileinfo-item-form', msg);

				return;
			}

			$.ajax({
				type: 'POST',
				url: 'index.php?option=com_kinoarhiv&task=mediamanager.saveFileInfo&format=json',
				data: $('form[name="adminFormFile"]').serialize()
			}).done(function(response){
				if (!response.success) {
					showMsg('#fileinfo-item-form', response.message);

					return;
				} else if (response.message != "") {
					showMsg('#fileinfo-item-form', response.message);
				}

				if (list == 'screenshot') {
					list = 'video';
				}

				$('table[data-list="' + list + '"] .cmd-refresh-filelist').trigger('click');
				modal.modal('hide');
			}).fail(function (xhr, status, error) {
				showMsg('#fileinfo-item-form', error);
			});
		});

		// Bind 'remove' functional for trailer files
		filelist.on('click', '.cmd-remove-file', function(e){
			e.preventDefault();

			var $this = $(this),
				table = $this.closest('table'),
				remove_all = $this.hasClass('all'),
				list = table.data('list');

			// Check if at least one file in list
			if (remove_all && table.find('.item-row').length < 1) {
				return;
			} else {
				// Check if it's a row with screenshot
				if (list == 'video' && table.find('.screenshot').length < 1) {
					return;
				}
			}

			if (!confirm(remove_all ? '<?php echo JText::_('COM_KA_DELETE_ALL'); ?>' : '<?php echo JText::_('JTOOLBAR_DELETE'); ?>?')) {
				return;
			}

			Kinoarhiv.showLoading('show', table);
			$.ajax({
				type: 'POST',
				url: $this.attr('href'),
				data: {'<?php echo JSession::getFormToken(); ?>': 1}
			}).done(function(response){
				showMsg('#system-message-container', response.message ? response.message : $(response).text());

				if ((remove_all && list == 'video') || (!remove_all && $this.data('type') == 'image')) {
					$('.screenshot div', table).remove();
				}

				table.find('.cmd-refresh-filelist').trigger('click');
				Kinoarhiv.showLoading('hide', table);
			}).fail(function (xhr, status, error) {
				showMsg('#system-message-container', error);
				Kinoarhiv.showLoading('hide', table);
			});
		});

		// Bind 'save' functional for createScreenshot
		$('#createScreenshotModal').on('click', '.cmd-create-scr', function(e){
			e.preventDefault();

			var $this = $(this),
				$screenshot_time = $('#screenshot_time');

			if (!document.formvalidator.isValid(document.getElementById('screenshot_layout_create_form'))
			|| ($screenshot_time.val() == '00:00:00' || $screenshot_time.val() == '00:00:00.000')) {
				// Due to the formvalidator specifics in Joomla we need to copy error message in new place in modal
				var msg_container = $('#system-message-container'),
					msg = $('button', msg_container).siblings('div').text();

				msg_container.html("");
				msg = (msg != '') ? msg + '<br />' : '';
				showMsg('#screenshot_layout_create_form', msg + '<?php echo JText::_('COM_KA_TRAILERS_VIDEO_SCREENSHOT_CREATE_TIME_ERR'); ?>');

				return;
			}

			$this.prop('disabled', 'disabled');
			$.ajax({
				type: 'POST',
				url: 'index.php?option=com_kinoarhiv&task=mediamanager.createScreenshot&item_id=<?php echo $this->trailer_id; ?>&id=<?php echo $this->id; ?>&format=json',
				data: {'screenshot_time': $screenshot_time.val(),'<?php echo JSession::getFormToken(); ?>': 1}
			}).done(function(response){
				$this.removeProp('disabled', 'disabled');

				if (!response.success) {
					showMsg('#screenshot_layout_create_form', response.message ? response.message : $(response).text());

					return;
				}

				$('table[data-list="video"]').find('.cmd-refresh-filelist').trigger('click');
				showMsg('#screenshot_layout_create_form', response.message);
			}).fail(function (xhr, status, error) {
				showMsg('#screenshot_layout_create_form', error);
				$this.removeProp('disabled', 'disabled');
			});
		});

		$('#uploadVideoModal a[data-toggle="tab"]').on('show', function(e){
			var $this = $(this);

			$this.closest('.modal').find('.modal-header h3').text($this.text());
		});

		// Validate filename in 'fileinfo edit' dialog
		document.formvalidator.setHandler('filename', function(value){
			return !/[^a-z0-9_.,[]%@'()\s-]/i.test(value);
		});

		// Validate screenshot time value in 'createScreenshot' dialog
		document.formvalidator.setHandler('time', function(value){
			return /^\d{2,}:(?:[0-5]\d):(?:[0-5]\d)(?:.\d{3,})?$/.test(value);
		});
	});
</script>
<div class="row-fluid">
	<div class="span6"><?php echo $this->loadTemplate('trailer_edit_form'); ?></div>
	<div class="span6">
	<?php if (empty($this->trailer_id)):
		echo JText::_('COM_KA_TRAILERS_HEADING_UPLOAD_FILES_NOTSAVED');
	else:
		echo $this->loadTemplate('trailer_edit_filelist');
	endif; ?>
	</div>
</div>
