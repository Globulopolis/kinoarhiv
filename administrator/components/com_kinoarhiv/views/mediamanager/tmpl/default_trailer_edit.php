<?php
/**
 * @package     Kinoarhiv.Administrator
 * @subpackage  com_kinoarhiv
 *
 * @copyright   Copyright (C) 2018 Libra.ms. All rights reserved.
 * @license     GNU General Public License version 2 or later
 * @url         http://киноархив.com
 */

defined('_JEXEC') or die;

JHtml::_('behavior.formvalidator');
JHtml::_('behavior.keepalive');
JHtml::_('bootstrap.tooltip');
JHtml::_('stylesheet', 'media/com_kinoarhiv/jqueryui/' . $this->params->get('ui_theme') . '/jquery-ui.min.css');
JHtml::_('script', 'media/com_kinoarhiv/js/jquery-ui.min.js');
JHtml::_('stylesheet', 'media/com_kinoarhiv/css/colorbox.css');
JHtml::_('script', 'media/com_kinoarhiv/js/jquery.colorbox.min.js');
KAComponentHelper::getScriptLanguage('jquery.colorbox-', 'media/com_kinoarhiv/js/i18n/colorbox/', true, true);
JHtml::_('script', 'media/com_kinoarhiv/js/jquery.plugin.min.js');
JHtml::_('script', 'media/com_kinoarhiv/js/jquery.more.min.js');
JHtml::_('script', 'media/com_kinoarhiv/js/validation-rules.min.js');

$input            = JFactory::getApplication()->input;
$this->section    = $input->get('section', '', 'word');
$this->type       = $input->get('type', '', 'word');
$this->tab        = $input->get('tab', '', 'int');
$this->id         = $input->get('id', 0, 'int');
$trailerID        = $input->get('item_id', 0, 'int');
$this->trailer_id = $trailerID;
?>
<script type="text/javascript">
	Joomla.submitbutton = function(task) {
		if (task === 'mediamanager.cancel' || document.formvalidator.isValid(document.getElementById('item-form'))) {
			Joomla.submitform(task, document.getElementById('item-form'));
		}
	};

	jQuery(document).ready(function($){
		var msgOptions = {replace: true};

		// Bind actions to the URLs modal button
		$('.cmd-form-urls').click(function(e){
			e.preventDefault();

			var target_form = $('#form_trailer_urls');

			if ($(this).data('type') === 'video') {
				var url_video = $('#urls_url_video');

				if (!empty(url_video.val())) {
					target_form.val(
						target_form.val()
						+ (target_form.val() !== '' ? "\n" : '')
						+ '[url="' + url_video.val() + '" type="' + $('#form_trailer_finfo_video_type').val() + '" player="' + $('#urls_url_video_inplayer').val() + '"]'
					);
					$('#urls_layout_video_form')[0].reset();

					return true;
				}

				Aurora.message([{text: '<?php echo JText::_('COM_KA_TRAILERS_UPLOAD_URLS_ERR'); ?>', type: 'alert'}], '#urls_layout_video_form', msgOptions);
			} else if ($(this).data('type') === 'subtitles') {
				var url_subtitle = $('#urls_url_subtitles');

				if (!empty(url_subtitle.val())) {
					var lang = $('#urls_url_subtitles_lang');

					target_form.val(
						target_form.val()
						+ (target_form.val() !== '' ? "\n" : '')
						+ '[url="' + url_subtitle.val() + '" kind="subtitles" srclang="' + lang.val() + '" label="' + $(':selected', lang).text() + '" default="' + $('#urls_url_subtitles_default').val() + '"]');
					$('#urls_layout_subtitles_form')[0].reset();

					return true;
				}

				Aurora.message([{text: '<?php echo JText::_('COM_KA_TRAILERS_UPLOAD_URLS_ERR'); ?>', type: 'alert'}], '#urls_layout_subtitles_form', msgOptions);
			} else if ($(this).data('type') === 'chapters') {
				var url_chapter = $('#urls_url_chapters');

				if (!empty(url_chapter.val())) {
					target_form.val(target_form.val() + "\n" + '[url="' + url_chapter.val() + '" kind="chapters"]');
					$('#urls_layout_chapters_form')[0].reset();

					return true;
				}

				Aurora.message([{text: '<?php echo JText::_('COM_KA_TRAILERS_UPLOAD_URLS_ERR'); ?>', type: 'alert'}], '#urls_layout_chapters_form', msgOptions);
			}
		});

		var filelist = $('.filelist');

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
					Aurora.message([{text: response.message, type: 'alert'}], '#system-message-container', msgOptions);
					Kinoarhiv.showLoading('hide', table);

					return false;
				}

				var total = Object.keys(response[list]).length,
					sort_handler = total < 2 ? ' inactive tip-top' : '';

				if (list === 'video') {
					// Update row with screenshot
					if (typeof response.screenshot !== 'undefined' && !empty(response.screenshot.file)) {
						var screenshot_row = $this.closest('table').find('tfoot .screenshot'),
							screenshot_filename_class = response.screenshot.is_file === 0 ? ' error_image' : '';

						screenshot_row.find('div').remove();
						screenshot_row.prepend('<div class="item-row"><a href="<?php echo $this->folder_path_www; ?>' + response.screenshot.file + '?_=' + Kinoarhiv.datetime('now') + '" id="screenshot_file" class="more' + screenshot_filename_class + '">' + response.screenshot.file + '</a></div>');
						screenshot_row.find('.more').more('destroy').more();
					}

					// Run updates only if total > 0
					if (total === 0) {
						tbody.find('tr').remove();
						tbody.prepend('<tr><td colspan="4"><?php echo JText::_('COM_KA_NO_FILES'); ?></td></tr>');
						Kinoarhiv.showLoading('hide', table);

						return;
					}

					// Update rows
					$.each(response[list], function(key, object){
						var file_info = Kinoarhiv.formatItemTitle(object.type, object.resolution, '', ', '),
							file_info_text = !empty(file_info) ? ' <span class="gray">(' + file_info + ')</span>': '',
							filename_class = object.is_file === 0 ? ' red' : '';

						html += '<tr>' +
							'<td width="1%" class="order">' +
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
				} else if (list === 'subtitles') {
					if (total === 0) {
						tbody.find('tr').remove();
						tbody.prepend('<tr><td colspan="5"><?php echo JText::_('COM_KA_NO_FILES'); ?></td></tr>');
						Kinoarhiv.showLoading('hide', table);

						return;
					}

					$.each(response[list], function(key, object){
						var lang = !empty(object.lang) ? ' <span class="gray">(' + object.lang + ')</span>' : '',
							filename_class = object.is_file === 0 ? ' red' : '';

						html += '<tr>' +
							'<td width="1%" class="order">' +
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
				} else if (list === 'chapters') {
					if (total === 0) {
						tbody.find('tr').remove();
						tbody.prepend('<tr><td colspan="4"><?php echo JText::_('COM_KA_NO_FILES'); ?></td></tr>');
						Kinoarhiv.showLoading('hide', table);

						return;
					}

					var filename_class = response[list].is_file === 0 ? ' red' : '';

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
				Aurora.message([{text: error, type: 'error'}], '#system-message-container', msgOptions);
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

			var $this = $(this);

			$.ajax({
				type: 'POST',
				url: $this.attr('href'),
				data: {'<?php echo JSession::getFormToken(); ?>': 1}
			}).done(function(response){
				if (response.success) {
					$('table[data-list="subtitles"] .cmd-refresh-filelist').trigger('click');

					return;
				}

				Aurora.message([{text: response.message, type: 'alert'}], '#system-message-container', msgOptions);
			}).fail(function (xhr, status, error) {
				Aurora.message([{text: error, type: 'error'}], '#system-message-container', msgOptions);
			});
		});

		// Bind 'show modal' functional for upload
		$('.cmd-upload').click(function(e){
			e.preventDefault();

			var tab = $(this).data('upload-tab'),
				modal;

			if (tab === 'screenshot') {
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
				Aurora.message([{text: error, type: 'error'}], '#system-message-container', msgOptions);
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
				Aurora.message([{text: msg, type: 'alert'}], '#fileinfo-item-form', {place: 'appendTo', replace: true});

				return;
			}

			$.ajax({
				type: 'POST',
				url: 'index.php?option=com_kinoarhiv&task=mediamanager.saveFileInfo&format=json',
				data: $('form[name="adminFormFile"]').serialize()
			}).done(function(response){
				if (!response.success) {
					Aurora.message([{text: response.message, type: 'alert'}], '#fileinfo-item-form', {place: 'appendTo', replace: true});

					return;
				} else if (response.message !== "") {
					Aurora.message([{text: response.message, type: 'success'}], '#fileinfo-item-form', {place: 'appendTo', replace: true});
				}

				if (list === 'screenshot') {
					list = 'video';
				}

				$('table[data-list="' + list + '"] .cmd-refresh-filelist').trigger('click');
				modal.modal('hide');
			}).fail(function (xhr, status, error) {
				Aurora.message([{text: error, type: 'error'}], '#fileinfo-item-form', {place: 'appendTo', replace: true});
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
				if (list === 'video' && table.find('.screenshot').length < 1) {
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
				var text = response.message ? response.message : $(response).text();
				Aurora.message([{text: text, type: 'info'}], '#system-message-container', msgOptions);

				if ((remove_all && list === 'video') || (!remove_all && $this.data('type') === 'image')) {
					$('.screenshot div', table).remove();
				}

				table.find('.cmd-refresh-filelist').trigger('click');
				Kinoarhiv.showLoading('hide', table);
			}).fail(function (xhr, status, error) {
				Aurora.message([{text: error, type: 'error'}], '#system-message-container', msgOptions);
				Kinoarhiv.showLoading('hide', table);
			});
		});

		// Bind 'save' functional for createScreenshot
		$('#createScreenshotModal').on('click', '.cmd-create-scr', function(e){
			e.preventDefault();

			var $this = $(this),
				$screenshot_time = $('#screenshot_time');

			if (!document.formvalidator.isValid(document.getElementById('screenshot_layout_create_form'))
			|| ($screenshot_time.val() === '00:00:00' || $screenshot_time.val() === '00:00:00.000')) {
				// Due to the formvalidator specifics in Joomla we need to copy error message in new place in modal
				var msg_container = $('#system-message-container'),
					msg = $('button', msg_container).siblings('div').text();

				msg_container.html("");
				msg = !empty(msg) ? msg + '<br />' : '';
				msg += '<?php echo JText::_('COM_KA_TRAILERS_VIDEO_SCREENSHOT_CREATE_TIME_ERR'); ?>';
				Aurora.message([{text: msg, type: 'alert'}], '#screenshot_layout_create_form', {place: 'appendTo', replace: true});

				return;
			}

			$this.prop('disabled', 'disabled');
			$.ajax({
				type: 'POST',
				url: 'index.php?option=com_kinoarhiv&task=mediamanager.createScreenshot&item_id=<?php echo $this->trailer_id; ?>&id=<?php echo $this->id; ?>&format=json',
				data: {'screenshot_time': $screenshot_time.val(),'<?php echo JSession::getFormToken(); ?>': 1}
			}).done(function(response){
				$this.removeProp('disabled', 'disabled');
				var msg = response.message ? response.message : $(response).text();

				if (!response.success) {
					Aurora.message([{text: msg, type: 'alert'}], '#screenshot_layout_create_form', {place: 'appendTo', replace: true});

					return;
				}

				$('table[data-list="video"]').find('.cmd-refresh-filelist').trigger('click');
				Aurora.message([{text: response.message, type: 'success'}], '#screenshot_layout_create_form', {place: 'appendTo', replace: true});
				$('#stdoutSlide .accordion-inner p').html(response.stdout);
			}).fail(function(xhr, status, error){
				Aurora.message([{text: error, type: 'error'}], '#screenshot_layout_create_form', {place: 'appendTo', replace: true});
				$this.removeProp('disabled', 'disabled');
			});
		});

		$('#stdoutSlide').on('show', function(){
			$('#createScreenshotModal').animate({
				'width': '100vw',
				'margin-left': '-50vw'
			});
		});

		$('#uploadVideoModal a[data-toggle="tab"]').on('show', function(e){
			var $this = $(this);

			$this.closest('.modal').find('.modal-header h3').text($this.text());
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
