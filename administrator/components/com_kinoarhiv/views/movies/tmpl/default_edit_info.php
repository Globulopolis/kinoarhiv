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

use Joomla\String\StringHelper;

$movie_id = $this->form->getValue('id', $this->form_edit_group);

// Set values to ignore in autocomplete for parent_id field.
$this->form->setFieldAttribute('parent_id', 'data-ignore-ids', $movie_id, $this->form_edit_group);

if (StringHelper::substr($this->params->get('media_posters_root_www'), 0, 1) == '/')
{
	$poster_url = JUri::root() . StringHelper::substr($this->params->get('media_posters_root_www'), 1) . '/'
		. urlencode($this->form->getValue('fs_alias', $this->form_edit_group)) . '/' . $movie_id . '/posters/';
}
else
{
	$poster_url = $this->params->get('media_posters_root_www') . '/' . urlencode($this->form->getValue('fs_alias', $this->form_edit_group))
		. '/' . $movie_id . '/posters/';
}
?>
<script type="text/javascript">
	jQuery(document).ready(function($) {
		$('#form_movie_rate_sum_loc, #form_movie_rate_loc').blur(function () {
			var vote = parseFloat($('#form_movie_rate_sum_loc').val() / $('#form_movie_rate_loc').val()).toFixed(<?php echo (int)$this->params->get('vote_summ_precision'); ?>);
			if (isNaN(vote) || $('#form_movie_rate_loc').val() == '' || $('#form_movie_rate_loc').val() == '0') {
				$('#vote').text('0');
			} else {
				$('#vote').text(vote);
			}
		}).trigger('blur');

		$('a.update-vote').click(function (e) {
			e.preventDefault();

			var cmd = $(this).attr('id');

			if (cmd == 'imdb_vote') {
				// Below we use ID from Kinopoisk because they return a xml with IMDB and KP votes
				if ($('#form_movie_kp_id').val() == '') {
					return;
				}

				blockUI('show');
				$.ajax({
					url: 'index.php?option=com_kinoarhiv&controller=movies&task=getRates&format=json&param=' + cmd + '&id=' + $('#form_movie_kp_id').val() + '&movie_id=<?php echo ($movie_id != 0) ? $movie_id : ''; ?>'
				}).done(function (response) {
					if (response.success) {
						$('#form_movie_imdb_votesum').val(response.votesum);
						$('#form_movie_imdb_votes').val(response.votes);
						requestUpdateStatImg(cmd, response);
					} else {
						showMsg('#system-message-container', response.message);
						$(document).scrollTop(0);
					}
					blockUI('hide');
				}).fail(function (xhr, status, error) {
					showMsg('#system-message-container', error);
					$(document).scrollTop(0);
					blockUI('hide');
				});
			} else if (cmd == 'kp_vote') {
				if ($('#form_movie_kp_id').val() == '') {
					return;
				}

				blockUI('show');
				$.ajax({
					url: 'index.php?option=com_kinoarhiv&controller=movies&task=getRates&format=json&param=' + cmd + '&id=' + $('#form_movie_kp_id').val() + '&movie_id=<?php echo ($movie_id != 0) ? $movie_id : ''; ?>'
				}).done(function (response) {
					if (response.success) {
						$('#form_movie_kp_votesum').val(response.votesum);
						$('#form_movie_kp_votes').val(response.votes);
						requestUpdateStatImg(cmd, response);
					} else {
						showMsg('#system-message-container', response.message);
						$(document).scrollTop(0);
					}
					blockUI('hide');
				}).fail(function (xhr, status, error) {
					showMsg('#system-message-container', error);
					$(document).scrollTop(0);
					blockUI('hide');
				});
			} else if (cmd == 'rt_vote') {
				if ($('#form_movie_rottentm_id').val() == '') {
					return;
				}

				blockUI('show');
				$.ajax({
					url: 'index.php?option=com_kinoarhiv&controller=movies&task=getRates&format=json&param=' + cmd + '&id=' + $('#form_movie_rottentm_id').val() + '&movie_id=<?php echo ($movie_id != 0) ? $movie_id : ''; ?>'
				}).done(function (response) {
					if (response.success) {
						$('#form_movie_rate_fc').val(response.votesum);
						requestUpdateStatImg(cmd, response);
					} else {
						showMsg('#system-message-container', response.message);
						$(document).scrollTop(0);
					}
					blockUI('hide');
				}).fail(function (xhr, status, error) {
					showMsg('#system-message-container', error);
					$(document).scrollTop(0);
					blockUI('hide');
				});
			} else if (cmd == 'mc_vote') {
				if ($('#form_movie_metacritics_id').val() == '') {
					return;
				}

				blockUI('show');
				$.ajax({
					url: 'index.php?option=com_kinoarhiv&controller=movies&task=getRates&format=json&param=' + cmd + '&id=' + $('#form_movie_metacritics_id').val() + '&movie_id=<?php echo ($movie_id != 0) ? $movie_id : ''; ?>'
				}).done(function (response) {
					if (response.success) {
						$('#form_movie_metacritics').val(response.votesum);
						requestUpdateStatImg(cmd, response);
					} else {
						showMsg('#system-message-container', response.message);
						$(document).scrollTop(0);
					}
					blockUI('hide');
				}).fail(function (xhr, status, error) {
					showMsg('#system-message-container', error);
					$(document).scrollTop(0);
					blockUI('hide');
				});
			}
		});

		function requestUpdateStatImg(elem, data) {
			$('<div id="dialog-message" title="<?php echo JText::_('MESSAGE'); ?>"><p><?php echo JText::_('COM_KA_MOVIE_RATES_UPDATE_IMG') . JText::_('COM_KA_MOVIE_RATES_UPDATE_IMG_TEXT'); ?><br /><?php echo JText::_('COM_KA_FIELD_MOVIE_VOTESUMM'); ?> - ' + data.votesum + ';<br /><?php echo JText::_('COM_KA_FIELD_MOVIE_VOTES'); ?> - ' + data.votes + '</p></div>').dialog({
				modal: true,
				buttons: [
					{
						text: '<?php echo JText::_('JTOOLBAR_REFRESH'); ?>',
						id: 'updateStatImg_do',
						click: function(){
							$('#updateStatImg_do, #updateStatImg_close').button('disable');

							var $this = $(this);
							$.ajax({
								type: 'POST',
								url: 'index.php?option=com_kinoarhiv&controller=movies&task=updateRateImg&format=json&id=<?php echo ($movie_id != 0) ? $movie_id : ''; ?>&elem=' + elem,
								data: data
							}).done(function (response) {
								if (response.success) {
									$this.find('p').html('<p><img src="' + response.image + '" border="0" /></p>');
									$('#updateStatImg_do, #updateStatImg_close').button('enable');
								} else {
									$this.find('p').html(response.message);
									$('#updateStatImg_close').button('enable');
								}
							}).fail(function (xhr, status, error) {
								$this.find('p').html(error);
								$('#updateStatImg_close').button('enable');
							});
						}
					},
					{
						text: '<?php echo JText::_('JTOOLBAR_CLOSE'); ?>',
						id: 'updateStatImg_close',
						click: function(){
							$(this).dialog('close');
						}
					}
				]
			});
		}

		<?php if ($movie_id != 0): ?>
		$('.movie-poster-preview').parent().click(function (e) {
			e.preventDefault();

			$('<div id="dialog-message"><img src="' + $(this).attr('href') + '" border="0" /></div>').dialog({
				modal: true,
				minHeight: $(window).height() - 100,
				minWidth: $(window).height() - 100,
				maxHeight: $(window).height() - 100,
				maxWidth: $(window).width() - 100
			});
		});

		$('a.file-upload-scr').click(function(e){
			e.preventDefault();

			$('#image_uploader').pluploadQueue({
				runtimes: 'html5,flash,silverlight,html4',
				url: 'index.php?option=com_kinoarhiv&controller=mediamanager&task=upload&format=raw&section=movie&type=gallery&tab=2&id=<?php echo ($movie_id != 0) ? $movie_id : 0; ?>&frontpage=1',
				multipart_params: {
					'<?php echo JSession::getFormToken(); ?>': 1
				},
				max_file_size: '<?php echo $this->params->get('upload_limit'); ?>',
				unique_names: false,
				multiple_queues: true,
				multi_selection: false,
				max_files: 1,
				filters: [{title: 'Image files', extensions: '<?php echo $this->params->get('upload_mime_images'); ?>'}],
				flash_swf_url: '<?php echo JUri::root(); ?>media/com_kinoarhiv/js/mediamanager/Moxie.swf',
				silverlight_xap_url: '<?php echo JUri::root(); ?>media/com_kinoarhiv/js/mediamanager/Moxie.xap',
				preinit: {
					init: function (up, info) {
						$('#image_uploader').find('.plupload_buttons a:last').after('<a class="plupload_button plupload_clear_all" href="#"><?php echo JText::_('JCLEAR'); ?></a>');
						$('#image_uploader .plupload_clear_all').click(function(e){
							e.preventDefault();
							up.splice();
							$.each(up.files, function(i, file){
								up.removeFile(file);
							});
						});
					}
				},
				init: {
					PostInit: function(){
						$('#image_uploader_container').removeAttr('title', '');
					},
					FileUploaded: function(up, file, info){
						var response = $.parseJSON(info.response),
							response_obj = $.parseJSON(response.id),
							url = '<?php echo $poster_url; ?>';

						blockUI('show');
						$.post('index.php?option=com_kinoarhiv&controller=mediamanager&view=mediamanager&task=fpOff&section=movie&type=gallery&tab=2&id=<?php echo ($movie_id != 0) ? $movie_id : 0; ?>&format=raw',
							{'_id[]': response_obj.id, '<?php echo JSession::getFormToken(); ?>': 1, 'reload': 0}
						).done(function (response) {
							var cover_preview = $('img.movie-poster-preview');

							cover_preview.attr('src', url + 'thumb_' + response_obj.filename + '?_=' + new Date().getTime());
							cover_preview.parent('a').attr('href', url + response_obj.filename + '?_=' + new Date().getTime());
							$('.cmd-scr-delete').attr('href', 'index.php?option=com_kinoarhiv&controller=mediamanager&view=mediamanager&task=remove&section=movie&type=gallery&tab=2&id=<?php echo ($movie_id != 0) ? $movie_id : 0; ?>&_id[]=' + response_obj.id + '&format=raw');
							blockUI();
							$('#imgModalUpload').modal('hide');
						}).fail(function (xhr, status, error) {
							showMsg('#system-message-container', error);
							blockUI();
						});
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

		$('a.cmd-scr-delete').click(function(e){
			e.preventDefault();

			if (!confirm('<?php echo JText::_('JTOOLBAR_DELETE'); ?>?')) {
				return false;
			}

			blockUI('show');
			$.post($(this).attr('href'), { '<?php echo JSession::getFormToken(); ?>': 1, 'reload': 0 }, function(response){
				
				if (typeof response !== 'object' && response != "") {
					showMsg('#system-message-container', response);
				} else {
					$('img.movie-poster-preview').attr('src', '<?php echo JUri::root(); ?>components/com_kinoarhiv/assets/themes/component/<?php echo $this->params->get('ka_theme'); ?>/images/no_movie_cover.png');
					$('img.movie-poster-preview').parent('a').attr('href', '<?php echo JUri::root(); ?>components/com_kinoarhiv/assets/themes/component/<?php echo $this->params->get('ka_theme'); ?>/images/no_movie_cover.png');
				}
				blockUI();
			}).fail(function(xhr, status, error){
				showMsg('#system-message-container', error);
				blockUI();
			});
		});
		<?php endif; ?>

		$('.cmd-alias').click(function(e){
			e.preventDefault();

			var dialog = $('<div id="dialog_alias" title="<?php echo JText::_('NOTICE'); ?>"><p><?php echo $this->params->get('media_posters_root') . '/' . $this->form->getValue('fs_alias', $this->form_edit_group) . '/' . $movie_id . '/'; ?><hr /><?php echo JText::_('COM_KA_FIELD_MOVIE_FS_ALIAS_DESC', true); ?><hr /><?php echo JText::_('COM_KA_FIELD_MOVIE_ALIAS_CHANGE_NOTICE', true); ?></p></div>');

			if ($(this).hasClass('info')) {
				$(dialog).dialog({
					modal: true,
					width: 800,
					height: $(window).height() - 100,
					draggable: false,
					close: function(event, ui){
						dialog.remove();
					}
				});
			} else if ($(this).hasClass('get-alias')) {
				$.getJSON('<?php echo JUri::base(); ?>index.php?option=com_kinoarhiv&controller=movies&task=getFilesystemAlias&form_movie_alias=' + $('#form_movie_alias').val() + '&form_movie_title=' + $('#form_movie_title').val() + '&format=json', function(response){
					if (response.success) {
						$('#form_movie_fs_alias').val(response.data);
					} else {
						showMsg('#system-message-container', response.message);
					}
				});
			}
		});
	});
</script>
<div class="row-fluid">
	<div class="span10">
		<fieldset class="form-horizontal">
			<div class="control-group">
				<div class="control-label"><?php echo $this->form->getLabel('parent_id', $this->form_edit_group); ?></div>
				<div class="controls"><?php echo $this->form->getInput('parent_id', $this->form_edit_group); ?></div>
			</div>
		</fieldset>
	</div>
</div>
<div class="row-fluid">
	<div class="span6">
		<fieldset class="form-horizontal">
			<div class="control-group">
				<div class="control-label"><?php echo $this->form->getLabel('title', $this->form_edit_group); ?></div>
				<div class="controls"><?php echo $this->form->getInput('title', $this->form_edit_group); ?></div>
			</div>
			<div class="control-group">
				<div class="control-label"><?php echo $this->form->getLabel('alias', $this->form_edit_group); ?></div>
				<div class="controls">
					<?php echo $this->form->getInput('alias', $this->form_edit_group); ?>
				</div>
			</div>
			<div class="control-group">
				<div class="control-label"><?php echo $this->form->getLabel('fs_alias', $this->form_edit_group); ?></div>
				<div class="controls">
					<div class="input-append">
						<?php echo $this->form->getInput('fs_alias', $this->form_edit_group); ?>
						<?php echo $this->form->getInput('fs_alias_orig', $this->form_edit_group); ?>
						<button class="btn btn-default cmd-alias get-alias hasTooltip" title="<?php echo JText::_('COM_KA_FIELD_MOVIE_FS_ALIAS_GET'); ?>"><i class="icon-refresh"></i></button>
						<button class="btn btn-default cmd-alias info"><i class="icon-help"></i></button>
					</div>
				</div>
			</div>
			<div class="control-group">
				<div class="control-label"><?php echo $this->form->getLabel('slogan', $this->form_edit_group); ?></div>
				<div class="controls"><?php echo $this->form->getInput('slogan', $this->form_edit_group); ?></div>
			</div>
			<div class="control-group">
				<div class="control-label"><?php echo $this->form->getLabel('genres', $this->form_edit_group); ?></div>
				<div class="controls">
					<?php echo $this->form->getInput('genres', $this->form_edit_group); ?>
					<span class="rel-link"><a href="index.php?option=com_kinoarhiv&controller=genres&task=add" target="_blank"><span class="icon-new"></span></a></span>
					<span class="rel-link"><a href="index.php?option=com_kinoarhiv&view=relations&task=genres&element=movies&mid=<?php echo ($movie_id != 0) ? $movie_id : 0; ?>" class="hasTip" title="<?php echo JText::_('COM_KA_TABLES_RELATIONS'); ?>" target="_blank"><span class="icon-out-2"></span></a></span>
				</div>
			</div>
		</fieldset>
	</div>
	<div class="span6">
		<div class="span9">
			<fieldset class="form-horizontal">
				<div class="control-group">
					<div class="control-label"><?php echo $this->form->getLabel('year', $this->form_edit_group); ?></div>
					<div class="controls"><?php echo $this->form->getInput('year', $this->form_edit_group); ?></div>
				</div>
				<div class="control-group">
					<div class="control-label"><?php echo $this->form->getLabel('length', $this->form_edit_group); ?></div>
					<div class="controls"><?php echo $this->form->getInput('length', $this->form_edit_group); ?></div>
				</div>
				<div class="control-group">
					<div class="control-label"><?php echo $this->form->getLabel('budget', $this->form_edit_group); ?></div>
					<div class="controls"><?php echo $this->form->getInput('budget', $this->form_edit_group); ?></div>
				</div>
			</fieldset>
		</div>
		<div class="span3">
			<?php if ($movie_id != 0): ?>
			<a href="<?php echo $this->items->get('poster'); ?>"><img src="<?php echo $this->items->get('th_poster'); ?>" class="movie-poster-preview" height="110" /></a>
			<a href="#" class="file-upload-scr hasTip" title="<?php echo JText::_('JTOOLBAR_UPLOAD'); ?>"><span class="icon-upload"></span></a>
			<a href="index.php?option=com_kinoarhiv&controller=mediamanager&view=mediamanager&task=remove&section=movie&type=gallery&tab=2&id=<?php echo $movie_id; ?>&_id[]=<?php echo $this->form->getValue('gid', $this->form_edit_group); ?>&format=raw" class="cmd-scr-delete hasTip" title="<?php echo JText::_('JTOOLBAR_DELETE'); ?>"><span class="icon-delete"></span></a>
			<?php endif; ?>
		</div>
		<fieldset class="form-horizontal">
			<div class="control-group">
				<div class="control-label"><?php echo $this->form->getLabel('countries', $this->form_edit_group); ?></div>
				<div class="controls">
					<?php echo $this->form->getInput('countries', $this->form_edit_group); ?>
					<span class="rel-link"><a href="index.php?option=com_kinoarhiv&controller=countries&task=add" target="_blank"><span class="icon-new"></span></a></span>
					<span class="rel-link"><a href="index.php?option=com_kinoarhiv&view=relations&task=countries&element=movies&mid=<?php echo ($movie_id != 0) ? $movie_id : 0; ?>" class="hasTip" title="<?php echo JText::_('COM_KA_TABLES_RELATIONS'); ?>" target="_blank"><span class="icon-out-2"></span></a></span>
				</div>
			</div>
		</fieldset>
	</div>
</div>
<div class="row-fluid">
	<div class="span12">
		<fieldset class="form-horizontal">
			<div class="control-group">
				<div class="control-label"><?php echo $this->form->getLabel('plot', $this->form_edit_group); ?></div>
				<div class="controls"><?php echo $this->form->getInput('plot', $this->form_edit_group); ?></div>
			</div>
			<div class="control-group">
				<div class="control-label"><?php echo $this->form->getLabel('known', $this->form_edit_group); ?></div>
				<div class="controls"><?php echo $this->form->getInput('known', $this->form_edit_group); ?></div>
			</div>
			<div class="control-group">
				<div class="control-label"><?php echo $this->form->getLabel('desc', $this->form_edit_group); ?></div>
				<div class="controls"><?php echo $this->form->getInput('desc', $this->form_edit_group); ?></div>
			</div>
			<div class="control-group">
				<div class="control-label"><?php echo $this->form->getLabel('buy_urls', $this->form_edit_group); ?></div>
				<div class="controls"><?php echo $this->form->getInput('buy_urls', $this->form_edit_group); ?></div>
			</div>
			<div class="control-group">
				<div class="control-label"><?php echo $this->form->getLabel('urls', $this->form_edit_group); ?></div>
				<div class="controls"><?php echo $this->form->getInput('urls', $this->form_edit_group); ?></div>
			</div>
		</fieldset>
	</div>
</div>

<?php // TODO Обработка не готова
echo JLayoutHelper::render(
	'layouts.edit.upload_image',
	array(
		'remote_upload' => false,
		'remote_url' => 'index.php?option=com_kinoarhiv&controller=mediamanager&task=upload_remote&format=raw&section=movie&type=gallery&tab=2&id=' . $movie_id . '&frontpage=1'
	),
	JPATH_COMPONENT
); ?>
