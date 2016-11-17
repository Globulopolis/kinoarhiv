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

$user      = JFactory::getUser();
$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));

KAComponentHelper::loadMediamanagerAssets();
JHtml::_('stylesheet', JUri::root() . 'components/com_kinoarhiv/assets/themes/component/' . $this->params->get('ka_theme') . '/css/select.css');
JHtml::_('script', JUri::root() . 'components/com_kinoarhiv/assets/js/select2.min.js');
KAComponentHelper::getScriptLanguage('select2_locale_', true, 'select', true);
?>
<script type="text/javascript">
	jQuery(document).ready(function($){
		var bootstrapTooltip = $.fn.tooltip.noConflict();
		$.fn.bootstrapTlp = bootstrapTooltip;
		var bootstrapButton = $.fn.button.noConflict();
		$.fn.bootstrapBtn = bootstrapButton;

		var tooltip_img = $('a.tooltip-img');

		tooltip_img.hover(function(e){
			$(this).next('img').stop().hide().fadeIn();
		}, function(e){
			$(this).next('img').stop().fadeOut();
		});
		tooltip_img.colorbox({ maxHeight: '95%', maxWidth: '95%', fixed: true });

		// Reload page if files uploaded
		$('#imgModalUpload').on('hidden', function() {
			if (parseInt($('input[name="file_uploaded"]').val(), 10) == 1) {
				document.location.reload();
			}
		});

		<?php if ($this->tab == 2): ?>
		$('.cmd-fp_off, .cmd-fp_on').click(function(){
			var boxchecked = $('input[name="boxchecked"]');

			$(this).closest('tr').find(':checkbox').prop('checked', true);
			boxchecked.val(parseInt(boxchecked.val(), 10) + 1);

			if ($(this).hasClass('cmd-fp_off')) {
				$('input[name="task"]').val('fpOff');
				$('form').submit();
			} else if ($(this).hasClass('cmd-fp_on')) {
				$('input[name="task"]').val('fpOn');
				$('form').submit();
			}
		});
		<?php endif; ?>

		$('.cmd-remote-urls').click(function(e){
			e.preventDefault();
			var input = $('#remote_urls');

			if (input.val() == '') {
				showMsg('#remote_urls', '<?php echo JText::_('COM_KA_FILE_UPLOAD_ERROR'); ?>');
				return false;
			}

			$('.cmd-remote-urls').attr('disabled', 'disabled');
			blockUI('show');

			$.ajax({
				type: 'POST',
				url: 'index.php?option=com_kinoarhiv&controller=mediamanager&task=upload_remote&format=raw&section=<?php echo $this->section; ?>&type=<?php echo $this->type; ?>&tab=<?php echo $this->tab; ?>&id=<?php echo $this->id; ?>',
				data: {'urls': input.val(), '<?php echo JSession::getFormToken(); ?>': 1},
				dataType: 'json'
			}).done(function(response){
				if (!response.success) {
					showMsg('#remote_urls', response.message);
				}

				$('.cmd-remote-urls').removeAttr('disabled');
				blockUI();
			}).fail(function(xhr, status, error){
				showMsg('#remote_urls', error);
				$('.cmd-remote-urls').removeAttr('disabled');
				blockUI();
			});
		});

		Joomla.submitbutton = function(task) {
			if (task == 'upload') {
				$('#image_uploader').pluploadQueue({
					runtimes: 'html5,flash,silverlight,html4',
					url: 'index.php?option=com_kinoarhiv&controller=mediamanager&task=upload&format=raw&section=<?php echo $this->section; ?>&type=<?php echo $this->type; ?>&tab=<?php echo $this->tab; ?>&id=<?php echo $this->id; ?>',
					multipart_params: {
						'<?php echo JSession::getFormToken(); ?>': 1
					},
					max_file_size: '<?php echo $this->params->get('upload_limit'); ?>',
					unique_names: false,
					multiple_queues: true,
					filters: [{title: 'Image', extensions: '<?php echo $this->params->get('upload_mime_images'); ?>'}],
					flash_swf_url: '<?php echo JUri::base(); ?>components/com_kinoarhiv/assets/js/mediamanager/Moxie.swf',
					silverlight_xap_url: '<?php echo JUri::base(); ?>components/com_kinoarhiv/assets/js/mediamanager/Moxie.xap',
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
						},
						UploadComplete: function(up, files){
							$('input[name="file_uploaded"]').val(1);
						}
					},
					init: {
						PostInit: function () {
							$('#image_uploader_container').removeAttr('title', '');
						}
					}
				});

				$('#imgModalUpload').modal();

				return false;
			} else if (task == 'copyfrom') {
				var dialog = $('<div id="dialog-copy" title="<?php echo JText::_('JTOOLBAR_COPYFROM'); ?>"><p class="ajax-loading"><?php echo JText::_('COM_KA_LOADING'); ?></p></div>');

				dialog.dialog({
					dialogClass: 'copy-dlg',
					modal: true,
					width: 600,
					height: 300,
					close: function(event, ui){
						$('#item_id').select2('destroy');
						dialog.remove();
					},
					buttons: [
						{
							text: '<?php echo JText::_('JTOOLBAR_COPY'); ?>',
							id: 'copy-apply',
							click: function(){
								if ($('#item_id', this).select2('val') == 0 || $('#item_id', this).select2('val') == '') {
									return false;
								}

								blockUI('show');
								$('#copy-apply').button('disable');
								var $this = $(this);

								$.ajax({
									type: 'POST',
									url: $('#form_copyfrom', this).attr('action'),
									data: '&id=' + $('#id', this).val() + '&item_id=' + $('#item_id', this).select2('val') + '&item_subtype=' + $('#item_subtype', this).val() + '&item_type=' + $('#item_type', this).val() + '&section=' + $('#section', this).val() + '&<?php echo JSession::getFormToken(); ?>=1'
								}).done(function(response){
									blockUI();
									if (response.success) {
										$this.dialog('close');
										document.location.reload(true);
									} else {
										showMsg('.copy-dlg #id', response.message);
									}
									$('#copy-apply').button('enable');
								}).fail(function(xhr, status, error){
									showMsg('.copy-dlg #id', error);
									$('#copy-apply').button('enable');
									blockUI();
								});
							}
						},
						{
							text: '<?php echo JText::_('JTOOLBAR_CLOSE'); ?>',
							click: function(){
								$('#item_id').select2('destroy');
								$(this).dialog('close');
							}
						}
					]
				});

				$.ajax({
					url: 'index.php?option=com_kinoarhiv&task=loadTemplate&template=copyfrom&model=mediamanager&view=mediamanager&format=raw&id=<?php echo $this->id; ?>&item_type=<?php echo $this->type; ?>&section=<?php echo $this->section; ?>',
					dataType: 'html'
				}).done(function(response){
					$('div#dialog-copy p').replaceWith(response);
				}).fail(function(xhr, status, error){
					showMsg('#dialog-copy p', status + ': ' + error);
				});

				return false;
			}

			Joomla.submitform(task);
		}
	});
</script>
<form action="<?php echo htmlspecialchars(JUri::getInstance()->toString()); ?>" method="post" name="adminForm" id="adminForm" autocomplete="off">
	<div class="btn-group pull-left" style="margin: 0 10px 0 0;">
		<a href="index.php?option=com_kinoarhiv&view=mediamanager&section=movie&type=gallery&tab=3&id=<?php echo $this->id; ?>" class="btn <?php echo ($this->tab == 3) ? 'btn-success' : ''; ?>">
			<span class="icon-picture icon-white"></span> <?php echo JText::_('COM_KA_MOVIES_SCRSHOTS'); ?>
		</a>
		<a href="index.php?option=com_kinoarhiv&view=mediamanager&section=movie&type=gallery&tab=2&id=<?php echo $this->id; ?>" class="btn <?php echo ($this->tab == 2) ? 'btn-success' : ''; ?>">
			<span class="icon-picture icon-white"></span> <?php echo JText::_('COM_KA_MOVIES_POSTERS'); ?>
		</a>
		<a href="index.php?option=com_kinoarhiv&view=mediamanager&section=movie&type=gallery&tab=1&id=<?php echo $this->id; ?>" class="btn <?php echo ($this->tab == 1) ? 'btn-success' : ''; ?>">
			<span class="icon-picture icon-white"></span> <?php echo JText::_('COM_KA_MOVIES_WALLPP'); ?>
		</a>
	</div>
	<?php echo JLayoutHelper::render('joomla.searchtools.default', array('view' => $this)); ?>
	<div class="clearfix"> </div>

	<table class="table table-striped gallery-list" id="articleList">
		<thead>
			<tr>
				<th width="1%" class="center">
					<?php echo JHtml::_('grid.checkall'); ?>
				</th>
				<th width="1%" style="min-width:35px;" class="nowrap center hidden-phone">
					<?php echo JHtml::_('searchtools.sort', 'JSTATUS', 'g.state', $listDirn, $listOrder); ?>
				</th>
				<th>
					<?php echo JHtml::_('searchtools.sort', 'COM_KA_MOVIES_GALLERY_HEADING_FILENAME', 'g.filename', $listDirn, $listOrder); ?>
				</th>
				<th width="15%" class="nowrap center hidden-phone">
					<?php echo JHtml::_('searchtools.sort', 'COM_KA_MOVIES_GALLERY_HEADING_DIMENSION', 'g.dimension', $listDirn, $listOrder); ?>
				</th>
				<?php if ($this->tab == 2): ?>
					<th width="10%" style="min-width: 55px" class="nowrap center">
						<?php echo JHtml::_('searchtools.sort', 'COM_KA_MOVIES_GALLERY_HEADING_FRONTPAGE', 'g.frontpage', $listDirn, $listOrder); ?>
					</th>
				<?php endif; ?>
				<th width="5%" class="nowrap center">
					<?php echo JHtml::_('searchtools.sort', 'JGRID_HEADING_ID', 'g.id', $listDirn, $listOrder); ?>
				</th>
			</tr>
		</thead>
		<tbody>
		<?php if (count($this->items) == 0): ?>
			<tr>
				<td colspan="6" class="center"><?php echo JText::_('COM_KA_NO_ITEMS'); ?></td>
			</tr>
		<?php else:
			foreach ($this->items as $i => $item):
				$canChange = $user->authorise('core.edit.state', 'com_kinoarhiv.movie.' . $item->id);
			?>
			<tr class="row<?php echo $i % 2; ?>" sortable-group-id="<?php echo $item->id; ?>">
				<td class="center">
					<?php echo JHtml::_('grid.id', $i, $item->id, false, '_id'); ?>
				</td>
				<td class="center hidden-phone">
					<?php echo JHtml::_('jgrid.published', $item->state, $i, '', $canChange, 'cb'); ?>
				</td>
				<td class="nowrap has-context">
					<?php if (!empty($item->error)): ?><a href="#" class="hasTooltip error_image" title="<?php echo $item->error; ?>"></a><?php endif; ?>
					<a href="<?php echo $item->filepath; ?>" class="tooltip-img" rel="group_<?php echo $this->tab; ?>"><?php echo $item->filename; ?></a>
					<?php if ($item->th_filepath != ''): ?><img src="<?php echo $item->th_filepath; ?>" class="tooltip-img-content" /><?php endif; ?>
					<?php if ($item->folderpath != ''): ?> <span class="small gray">(<?php echo $item->folderpath; ?>)</span><?php endif; ?>
				</td>
				<td class="center hidden-phone">
					<?php echo $item->dimension; ?>
				</td>
				<?php if ($this->tab == 2 && $canChange): ?>
					<td class="center">
						<div class="btn-group">
							<?php if ($item->frontpage == 0): ?>
								<a class="btn btn-micro active cmd-fp_off" href="javascript:void(0);"><i class="icon-unpublish"></i></a>
							<?php else: ?>
								<a class="btn btn-micro active cmd-fp_on" href="javascript:void(0);"><i class="icon-publish"></i></a>
							<?php endif; ?>
						</div>
					</td>
				<?php endif; ?>
				<td class="center">
					<?php echo (int) $item->id; ?>
				</td>
			</tr>
			<?php endforeach;
		endif; ?>
		</tbody>
	</table>
	<?php echo $this->pagination->getListFooter(); ?>
	<?php echo $this->pagination->getResultsCounter(); ?>

	<input type="hidden" name="controller" value="mediamanager" />
	<input type="hidden" name="section" value="<?php echo $this->section; ?>" />
	<input type="hidden" name="type" value="<?php echo $this->type; ?>" />
	<input type="hidden" name="tab" value="<?php echo $this->tab; ?>" />
	<input type="hidden" name="id" value="<?php echo $this->id; ?>" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="boxchecked" value="0" />
	<input type="hidden" name="file_uploaded" value="0" />

	<?php echo JLayoutHelper::render('layouts.edit.upload_image', array('remoteupload' => true), JPATH_COMPONENT); ?>
	<?php echo JHtml::_('form.token'); ?>
</form>
