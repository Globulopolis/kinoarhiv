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

use Joomla\String\StringHelper;

if (StringHelper::substr($this->params->get('media_music_images_root_www'), 0, 1) == '/')
{
	$poster_url = JUri::root() . StringHelper::substr($this->params->get('media_music_images_root_www'), 1) . '/'
		. urlencode($this->form->getValue('fs_alias')) . '/' . $this->form->getValue('id');
}
else
{
	$poster_url = $this->params->get('media_music_images_root_www') . '/' . urlencode($this->form->getValue('fs_alias'))
		. '/' . $this->form->getValue('id');
}
?>
<script type="text/javascript">
	jQuery(document).ready(function($){
		<?php if ($this->form->getValue('id') != 0): ?>
		$('.album-cover-preview').parent().click(function(e){
			e.preventDefault();

			$('<div id="dialog-message"><img src="'+ $(this).attr('href') +'" border="0" /></div>').dialog({
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
				url: 'index.php?option=com_kinoarhiv&controller=mediamanager&task=upload&format=raw&section=movie&type=gallery&tab=2&id=<?php echo ($this->form->getValue('id') != 0) ? $this->form->getValue('id') : 0; ?>&frontpage=1',
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
					init: function(up, info){
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
						$.post('index.php?option=com_kinoarhiv&controller=mediamanager&view=mediamanager&task=fpOff&section=movie&type=gallery&tab=2&id=<?php echo ($this->form->getValue('id') != 0) ? $this->form->getValue('id') : 0; ?>&format=raw',
							{ '_id[]': response_obj.id, '<?php echo JSession::getFormToken(); ?>': 1, 'reload': 0 }
						).done(function(response){
							var cover_preview = $('img.album-cover-preview');

							cover_preview.attr('src', url + 'thumb_' + response_obj.filename + '?_=' + new Date().getTime());
							cover_preview.parent('a').attr('href', url + response_obj.filename + '?_=' + new Date().getTime());
							$('.cmd-scr-delete').attr('href', 'index.php?option=com_kinoarhiv&controller=mediamanager&view=mediamanager&task=remove&section=music&type=gallery&tab=2&id=<?php echo ($this->form->getValue('id') != 0) ? $this->form->getValue('id') : 0; ?>&_id[]=' + response_obj.id + '&format=raw');
							blockUI();
							$('#imgModalUpload').modal('hide');
						}).fail(function(xhr, status, error){
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
					var cover_preview = $('img.album-cover-preview');
					cover_preview.attr('src', '<?php echo JUri::root(); ?>components/com_kinoarhiv/assets/themes/component/<?php echo $this->params->get('ka_theme'); ?>/images/no_movie_cover.png');
					cover_preview.parent('a').attr('href', '<?php echo JUri::root(); ?>components/com_kinoarhiv/assets/themes/component/<?php echo $this->params->get('ka_theme'); ?>/images/no_movie_cover.png');
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

			var dialog = $('<div id="dialog_alias" title="<?php echo JText::_('NOTICE'); ?>"><p><?php echo $this->params->get('media_posters_root') . '/' . $this->form->getValue('fs_alias') . '/' . $this->form->getValue('id') . '/'; ?><hr /><?php echo JText::_('COM_KA_FIELD_ALBUMS_ALIAS_CHANGE_NOTICE', true); ?><hr /><?php echo JText::_('COM_KA_FIELD_MOVIE_ALIAS_CHANGE_NOTICE', true); ?></p></div>');

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
				$.getJSON('<?php echo JUri::base(); ?>index.php?option=com_kinoarhiv&controller=music&task=getFilesystemAlias&form_album_alias=' + $('#form_album_alias').val() + '&form_album_title=' + $('#form_album_title').val() + '&format=json', function(response){
					if (response.success) {
						$('#form_album_fs_alias').val(response.data);
					} else {
						showMsg('#system-message-container', response.message);
					}
				});
			}
		});
	});
</script>
<div class="row-fluid">
	<div class="span6">
		<fieldset class="form-horizontal">
			<div class="control-group">
				<div class="control-label"><?php echo $this->form->getLabel('title'); ?></div>
				<div class="controls"><?php echo $this->form->getInput('title'); ?></div>
			</div>
			<div class="control-group">
				<div class="control-label"><?php echo $this->form->getLabel('alias'); ?></div>
				<div class="controls">
					<?php echo $this->form->getInput('alias'); ?>
				</div>
			</div>
			<div class="control-group">
				<div class="control-label"><?php echo $this->form->getLabel('fs_alias'); ?></div>
				<div class="controls">
					<div class="input-append">
						<?php echo $this->form->getInput('fs_alias'); ?>
						<?php echo $this->form->getInput('fs_alias_orig'); ?>
						<button class="btn btn-default cmd-alias get-alias hasTooltip" title="<?php echo JText::_('COM_KA_FIELD_MOVIE_FS_ALIAS_GET'); ?>"><i class="icon-refresh"></i></button>
						<button class="btn btn-default cmd-alias info"><i class="icon-help"></i></button>
					</div>
				</div>
			</div>
			<div class="control-group">
				<div class="control-label"><?php echo $this->form->getLabel('composer'); ?></div>
				<div class="controls"><?php echo $this->form->getInput('composer'); ?></div>
			</div>
			<div class="control-group">
				<div class="control-label"><?php echo $this->form->getLabel('genres'); ?></div>
				<div class="controls">
					<?php echo $this->form->getInput('genres'); ?>
					<span class="rel-link"><a href="index.php?option=com_kinoarhiv&task=genres.add" target="_blank"><span class="icon-new"></span></a></span>
				</div>
			</div>
		</fieldset>
	</div>
	<div class="span6">
		<div class="span9">
			<fieldset class="form-horizontal">
				<div class="control-group">
					<div class="control-label"><?php echo $this->form->getLabel('year'); ?></div>
					<div class="controls"><?php echo $this->form->getInput('year'); ?></div>
				</div>
				<div class="control-group">
					<div class="control-label"><?php echo $this->form->getLabel('length'); ?></div>
					<div class="controls"><?php echo $this->form->getInput('length'); ?></div>
				</div>
				<div class="control-group">
					<div class="control-label"><?php echo $this->form->getLabel('isrc'); ?></div>
					<div class="controls"><?php echo $this->form->getInput('isrc'); ?></div>
				</div>
			</fieldset>
		</div>
		<div class="span3">
			<?php if ($this->form->getValue('id') != 0): ?>
			<a href="<?php echo $this->items->get('poster'); ?>"><img src="<?php echo $this->items->get('th_poster'); ?>" class="album-cover-preview" height="110" /></a>
			<a href="#" class="file-upload-scr hasTip" title="<?php echo JText::_('JTOOLBAR_UPLOAD'); ?>"><span class="icon-upload"></span></a>
			<a href="index.php?option=com_kinoarhiv&controller=mediamanager&view=mediamanager&task=remove&section=music&type=gallery&id=<?php echo $this->form->getValue('id'); ?>&format=raw" class="cmd-scr-delete hasTip" title="<?php echo JText::_('JTOOLBAR_DELETE'); ?>"><span class="icon-delete"></span></a>
			<?php endif; ?>
		</div>
	</div>
</div>
<div class="row-fluid">
	<div class="span12">
		<fieldset class="form-horizontal">
			<div class="control-group">
				<div class="control-label"><?php echo $this->form->getLabel('desc'); ?></div>
				<div class="controls"><?php echo $this->form->getInput('desc'); ?></div>
			</div>
			<div class="control-group">
				<div class="control-label"><?php echo $this->form->getLabel('buy_url'); ?></div>
				<div class="controls"><?php echo $this->form->getInput('buy_url'); ?></div>
			</div>
		</fieldset>
	</div>
</div>

<?php
// TODO Remove?
$path = JPath::clean(
	$this->params->get('media_posters_root') . '/' . $this->form->getValue('fs_alias') . '/' . $this->id . '/'
);

echo JHtml::_(
	'bootstrap.renderModal',
	'helpAliasModal',
	array(
		'title'  => JText::_('NOTICE'),
		'footer' => '<a class="btn" data-dismiss="modal">' . JText::_('COM_KA_CLOSE') . '</a>'
	),
	'<div class="container-fluid">' . JText::sprintf('COM_KA_FIELD_MOVIE_FS_ALIAS_DESC', $path) . '</div>'
);
