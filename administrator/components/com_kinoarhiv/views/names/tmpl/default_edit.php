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

JHtml::_('behavior.keepalive');
JHtml::_('stylesheet', 'media/com_kinoarhiv/css/colorbox.css');
JHtml::_('script', 'media/com_kinoarhiv/js/jquery.colorbox.min.js');
KAComponentHelper::getScriptLanguage('jquery.colorbox-', 'media/com_kinoarhiv/js/i18n/colorbox/', true, true);
KAComponentHelperBackend::loadMediamanagerAssets();

$this->input   = JFactory::getApplication()->input;
$this->section = $this->input->get('section', '', 'word');
$this->type    = $this->input->get('type', '', 'word');
$this->tab     = $this->input->get('tab', 0, 'int');
$id            = $this->input->get('id', null, 'array');
$this->id      = $id[0];
?>
<script type="text/javascript">
	Kinoarhiv.setActiveTab();

	Joomla.submitbutton = function(task) {
		if (task == 'names.apply' || task == 'names.save' || task == 'names.save2new') {
			if (document.getElementById('form_name_name').value == ''
				|| document.getElementById('form_name_latin_name').value == ''
				|| document.getElementById('form_name_alias').value == ''
			) {
				showMsg('#system-message-container', '<?php echo JText::_('COM_KA_REQUIRED'); ?>');
				return;
			}
		} else if (task == 'gallery') {
			var tab = (task == 'gallery') ? '&tab=3' : '';
			var url = 'index.php?option=com_kinoarhiv&view=mediamanager&section=name&type='+ task + tab +'<?php echo ($this->id != 0) ? '&id=' . $this->id : ''; ?>';
			var handler = window.open(url);
			if (!handler) {
				showMsg('#system-message-container', '<?php echo JText::_('COM_KA_NEWWINDOW_BLOCKED_A'); ?>' + url + '<?php echo JText::_('COM_KA_NEWWINDOW_BLOCKED_B'); ?>');
			}

			return false;
		}

		Joomla.submitform(task);
	};
</script>
<form action="<?php echo JRoute::_('index.php?option=com_kinoarhiv'); ?>" method="post" name="adminForm" id="adminForm" autocomplete="off">
	<div id="j-main-container">
		<div class="row-fluid">
			<div class="span12">
			<?php echo JHtml::_('bootstrap.startTabSet', 'names', array('active' => 'page0')); ?>
				<?php echo JHtml::_('bootstrap.addTab', 'names', 'page0', JText::_('COM_KA_NAMES_TAB_MAIN')); ?>

				<div id="page0">
					<?php echo $this->loadTemplate('edit_info'); ?>
				</div>

				<?php echo JHtml::_('bootstrap.endTab'); ?>
				<?php echo JHtml::_('bootstrap.addTab', 'names', 'page1', JText::_('COM_KA_NAMES_TAB_AWARDS')); ?>

				<div id="page1">
					<?php //echo $this->loadTemplate('edit_awards'); ?>
				</div>

				<?php echo JHtml::_('bootstrap.endTab'); ?>
				<?php echo JHtml::_('bootstrap.addTab', 'names', 'page2', JText::_('COM_KA_NAMES_TAB_META')); ?>

				<div id="page2">
					<?php echo $this->loadTemplate('edit_meta'); ?>
				</div>

				<?php echo JHtml::_('bootstrap.endTab'); ?>
				<?php echo JHtml::_('bootstrap.addTab', 'names', 'page3', JText::_('COM_KA_NAMES_TAB_PUB')); ?>

				<div id="page3">
					<div class="row-fluid">
						<div class="span6">
							<fieldset class="form-horizontal">
								<div class="control-group">
									<div class="control-label"><?php echo $this->form->getLabel('ordering', $this->form_edit_group); ?></div>
									<div class="controls"><?php echo $this->form->getInput('ordering', $this->form_edit_group); ?></div>
								</div>
								<div class="control-group">
									<div class="control-label"><?php echo $this->form->getLabel('link_titles', $this->form_attribs_group); ?></div>
									<div class="controls"><?php echo $this->form->getInput('link_titles', $this->form_attribs_group); ?></div>
								</div>
								<div class="control-group">
									<div class="control-label"><?php echo $this->form->getLabel('tab_name_wallpp', $this->form_attribs_group); ?></div>
									<div class="controls"><?php echo $this->form->getInput('tab_name_wallpp', $this->form_attribs_group); ?></div>
								</div>
								<div class="control-group">
									<div class="control-label"><?php echo $this->form->getLabel('tab_name_posters', $this->form_attribs_group); ?></div>
									<div class="controls"><?php echo $this->form->getInput('tab_name_posters', $this->form_attribs_group); ?></div>
								</div>
								<div class="control-group">
									<div class="control-label"><?php echo $this->form->getLabel('tab_name_photos', $this->form_attribs_group); ?></div>
									<div class="controls"><?php echo $this->form->getInput('tab_name_photos', $this->form_attribs_group); ?></div>
								</div>
								<div class="control-group">
									<div class="control-label"><?php echo $this->form->getLabel('tab_name_awards', $this->form_attribs_group); ?></div>
									<div class="controls"><?php echo $this->form->getInput('tab_name_awards', $this->form_attribs_group); ?></div>
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
							</fieldset>
						</div>
					</div>
				</div>

				<?php echo JHtml::_('bootstrap.endTab'); ?>
				<?php echo JHtml::_('bootstrap.addTab', 'names', 'page4', JText::_('COM_KA_PERMISSIONS_LABEL')); ?>

				<div id="page4">
					<div class="row-fluid">
						<div class="span12">
							<fieldset class="form-horizontal">
								<div class="control-group">
									<div class="controls" style="margin-left: 0 !important;"><?php echo $this->form->getInput('rules', $this->form_edit_group); ?></div>
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

	<?php echo $this->form->getInput('genres_orig', $this->form_edit_group)."\n"; ?>
	<?php echo $this->form->getInput('careers_orig', $this->form_edit_group)."\n"; ?>
	<?php echo $this->form->getInput('id', $this->form_edit_group)."\n"; ?>
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="id" id="id" value="<?php echo $this->id; ?>" />
	<input type="hidden" name="active_tab" value="<?php echo md5('com_kinoarhiv.names.tabs.' . $this->user->get('id') . '.' . $this->id); ?>" />
	<?php echo JHtml::_('form.token'); ?>
</form>
