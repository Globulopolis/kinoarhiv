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

JHtml::_('behavior.keepalive');
KAComponentHelperBackend::loadMediamanagerAssets();

$this->id = ($this->form->getValue('id', $this->form_edit_group) != 0) ? $this->form->getValue('id', $this->form_edit_group) : 0;
?>
<link type="text/css" rel="stylesheet" href="<?php echo JUri::root(); ?>media/com_kinoarhiv/css/plupload.css"/>
<script type="text/javascript" src="<?php echo JUri::base(); ?>components/com_kinoarhiv/assets/js/ui.multiselect.js"></script>
<script type="text/javascript" src="<?php echo JUri::base(); ?>components/com_kinoarhiv/assets/js/jquery.jqGrid.min.js"></script>
<?php KAComponentHelper::getScriptLanguage('grid.locale-', 'media/com_kinoarhiv/js/i18n/grid/', false); ?>
<script type="text/javascript" src="<?php echo JUri::base(); ?>components/com_kinoarhiv/assets/js/jquery.searchFilter.min.js"></script>
<script type="text/javascript" src="<?php echo JUri::base(); ?>components/com_kinoarhiv/assets/js/grid.setcolumns.js"></script>
<script type="text/javascript">
	Joomla.submitbutton = function(task) {
		if (task == 'apply' || task == 'save' || task == 'save2new') {
			if (document.getElementById('form_title').value == '') {
				showMsg('#system-message-container', '<?php echo JText::_('COM_KA_REQUIRED'); ?>');
				return;
			}
		}
		Joomla.submitform(task);
	};

	jQuery(document).ready(function($){
		// Strongly needed for override fucking bootstrap
		var bootstrapTooltip = $.fn.tooltip.noConflict();
		$.fn.bootstrapTlp = bootstrapTooltip;
		var bootstrapButton = $.fn.button.noConflict();
		$.fn.bootstrapBtn = bootstrapButton;

		<?php if ($this->id != 0): ?>
		$('.cmd-rules').click(function(e){
			e.preventDefault();
			var dialog = $('<div id="dialog-rules" title="<?php echo JText::_('COM_KA_PERMISSION_SETTINGS'); ?>"><p class="ajax-loading"><?php echo JText::_('COM_KA_LOADING'); ?></p></div>');

			$(dialog).dialog({
				dialogClass: 'rules-dlg',
				modal: true,
				width: 800,
				height: 520,
				close: function(event, ui){
					dialog.remove();
				},
				buttons: [
					{
						text: '<?php echo JText::_('JTOOLBAR_APPLY'); ?>',
						id: 'rules-apply',
						click: function(){
							var $this = $(this);
							blockUI('show');
							$.ajax({
								type: 'POST',
								url: $('#rulesForm', this).attr('action') + '&id=' + $('#id').val(),
								data: $('#rulesForm', this).serialize()
							}).done(function(response){
								blockUI();
								if (response.success) {
									$this.dialog('close');
								} else {
									showMsg('.rules-dlg #rulesForm', response.message);
								}
							}).fail(function(xhr, status, error){
								showMsg('.rules-dlg #rulesForm', error);
								blockUI();
							});
						}
					},
					{
						text: '<?php echo JText::_('JTOOLBAR_CLOSE'); ?>',
						click: function(){
							$(this).dialog('close');
						}
					}
				]
			});
			dialog.load('index.php?option=com_kinoarhiv&task=loadTemplate&template=rules_edit&model=album&view=music&format=raw');
		});
		<?php endif; ?>
	});
</script>
<form action="<?php echo JRoute::_('index.php?option=com_kinoarhiv'); ?>" method="post" name="adminForm" id="adminForm" class="form-validate" autocomplete="off">
	<div id="j-main-container">
		<div class="row-fluid">
			<div class="span12">
			<?php echo JHtml::_('bootstrap.startTabSet', 'albums', array('active' => 'page0')); ?>
				<?php echo JHtml::_('bootstrap.addTab', 'albums', 'page0', JText::_('COM_KA_MOVIES_TAB_MAIN')); ?>

				<div id="page0">
					<?php echo $this->loadTemplate('edit_album_info'); ?>
				</div>

				<?php echo JHtml::_('bootstrap.endTab'); ?>
				<?php echo JHtml::_('bootstrap.addTab', 'albums', 'page1', JText::_('COM_KA_MUSIC_GROUP_HEADING')); ?>

				<div id="page1">
					<div class="row-fluid">
						<?php echo $this->loadTemplate('edit_composer'); ?>
					</div>
				</div>

				<?php echo JHtml::_('bootstrap.endTab'); ?>
				<?php echo JHtml::_('bootstrap.addTab', 'albums', 'page2', JText::_('COM_KA_MUSIC_TRACKS_TITLE')); ?>

				<div id="page2">
					<div class="row-fluid">
						<?php //echo $this->loadTemplate('edit_tracks'); ?>
					</div>
				</div>

				<?php echo JHtml::_('bootstrap.endTab'); ?>
				<?php echo JHtml::_('bootstrap.addTab', 'albums', 'page3', JText::_('COM_KA_MOVIES_TAB_RATE')); ?>

				<div id="page3">
					<?php //echo $this->loadTemplate('edit_rates'); ?>
				</div>

				<?php echo JHtml::_('bootstrap.endTab'); ?>
				<?php echo JHtml::_('bootstrap.addTab', 'albums', 'page4', JText::_('COM_KA_MOVIES_TAB_META')); ?>

				<div id="page4">
					<?php echo $this->loadTemplate('edit_meta'); ?>
				</div>

				<?php echo JHtml::_('bootstrap.endTab'); ?>
				<?php echo JHtml::_('bootstrap.addTab', 'albums', 'page5', JText::_('COM_KA_MOVIES_TAB_PUB')); ?>

				<div id="page5">
					<div class="row-fluid">
						<div class="span6">
							<fieldset class="form-horizontal">
								<div class="control-group">
									<div class="control-label"><?php echo $this->form->getLabel('created', $this->form_edit_group); ?></div>
									<div class="controls"><?php echo $this->form->getInput('created', $this->form_edit_group); ?></div>
								</div>
								<div class="control-group">
									<div class="control-label"><?php echo $this->form->getLabel('modified', $this->form_edit_group); ?></div>
									<div class="controls"><?php echo $this->form->getInput('modified', $this->form_edit_group); ?></div>
								</div>
								<div class="control-group">
									<div class="control-label"><?php echo $this->form->getLabel('ordering', $this->form_edit_group); ?></div>
									<div class="controls"><?php echo $this->form->getInput('ordering', $this->form_edit_group); ?></div>
								</div>
								<div class="control-group">
									<div class="control-label"><?php echo $this->form->getLabel('created_by', $this->form_edit_group); ?></div>
									<div class="controls"><?php echo $this->form->getInput('created_by', $this->form_edit_group); ?></div>
								</div>
								<div class="control-group">
									<div class="control-label"><?php echo $this->form->getLabel('link_titles', $this->form_attribs_group); ?></div>
									<div class="controls"><?php echo $this->form->getInput('link_titles', $this->form_attribs_group); ?></div>
								</div>
								<div class="control-group">
									<div class="control-label"><?php echo $this->form->getLabel('show_author', $this->form_attribs_group); ?></div>
									<div class="controls"><?php echo $this->form->getInput('show_author', $this->form_attribs_group); ?></div>
								</div>
								<div class="control-group">
									<div class="control-label"><?php echo $this->form->getLabel('show_create_date', $this->form_attribs_group); ?></div>
									<div class="controls"><?php echo $this->form->getInput('show_create_date', $this->form_attribs_group); ?></div>
								</div>
								<div class="control-group">
									<div class="control-label"><?php echo $this->form->getLabel('show_modify_date', $this->form_attribs_group); ?></div>
									<div class="controls"><?php echo $this->form->getInput('show_modify_date', $this->form_attribs_group); ?></div>
								</div>
							</fieldset>
						</div>
						<div class="span6">
							<fieldset class="form-horizontal">
								<div class="control-group">
									<div class="control-label"><?php echo $this->form->getLabel('language', $this->form_edit_group); ?></div>
									<div class="controls"><?php echo $this->form->getInput('language', $this->form_edit_group); ?></div>
								</div>
								<div class="control-group">
									<div class="control-label"><?php echo $this->form->getLabel('access', $this->form_edit_group); ?></div>
									<div class="controls"><?php echo $this->form->getInput('access', $this->form_edit_group); ?></div>
								</div>
								<div class="control-group">
									<div class="control-label"><?php echo $this->form->getLabel('state', $this->form_edit_group); ?></div>
									<div class="controls"><?php echo $this->form->getInput('state', $this->form_edit_group); ?></div>
								</div>
								<div class="control-group">
									<div class="control-label"><label><?php echo JText::_('JGLOBAL_ACTION_PERMISSIONS_LABEL'); ?></label></div>
									<div class="controls">
										<?php if ($this->id != 0): ?>
											<button class="btn btn-small btn-default cmd-rules"><span class="icon-users"></span> <?php echo JText::_('COM_KA_PERMISSION_ACTION_DO'); ?></button>
										<?php else: ?>
											<button class="btn btn-small btn-default" title="<?php echo JText::_('COM_KA_NO_ID'); ?>" disabled><span class="icon-users"></span> <?php echo JText::_('COM_KA_PERMISSION_ACTION_DO'); ?></button>
										<?php endif; ?>
									</div>
								</div>
								<div class="control-group">
									<div class="control-label"><?php echo $this->form->getLabel('show_tags', $this->form_attribs_group); ?></div>
									<div class="controls"><?php echo $this->form->getInput('show_tags', $this->form_attribs_group); ?></div>
								</div>
								<div class="control-group">
									<div class="control-label"><?php echo $this->form->getLabel('allow_votes', $this->form_attribs_group); ?></div>
									<div class="controls"><?php echo $this->form->getInput('allow_votes', $this->form_attribs_group); ?></div>
								</div>
								<div class="control-group">
									<div class="control-label"><?php echo $this->form->getLabel('ratings_show_local', $this->form_attribs_group); ?></div>
									<div class="controls"><?php echo $this->form->getInput('ratings_show_local', $this->form_attribs_group); ?></div>
								</div>
								<div class="control-group">
									<div class="control-label"><?php echo $this->form->getLabel('allow_reviews', $this->form_attribs_group); ?></div>
									<div class="controls"><?php echo $this->form->getInput('allow_reviews', $this->form_attribs_group); ?></div>
								</div>
							</fieldset>
						</div>
					</div>
				</div>

				<?php echo JHtml::_('bootstrap.endTab'); ?>
			<?php echo JHtml::_('bootstrap.endTabSet'); ?>
			</div>
		</div>
	</div>

	<?php echo $this->form->getInput('fs_alias', $this->form_edit_group) . "\n"; ?>
	<input type="hidden" name="controller" value="music" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="id" id="id" value="<?php echo $this->id; ?>" />
	<?php echo JHtml::_('form.token'); ?>
</form>
