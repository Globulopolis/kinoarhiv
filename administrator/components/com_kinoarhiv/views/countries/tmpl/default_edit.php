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
?>
<script type="text/javascript">
	Joomla.submitbutton = function(task) {
		if (task == 'relations') {
			document.location.href = 'index.php?option=com_kinoarhiv&view=relations&task=countries&element=movies<?php echo ($this->form->getValue('id') != 0) ? '&id='.$this->form->getValue('id') : ''; ?>';
			return;
		}
		if (task == 'apply' || task == 'save' || task == 'save2new') {
			if (document.getElementById('form_name').value == '' || document.getElementById('form_code').value == '') {
				showMsg('#system-message-container', '<?php echo JText::_('COM_KA_REQUIRED'); ?>');
				return;
			}
		}
		Joomla.submitform(task);
	}
</script>
<div id="j-main-container">
	<form action="<?php echo JRoute::_('index.php?option=com_kinoarhiv'); ?>" method="post" name="adminForm" id="adminForm" class="form-validate" autocomplete="off">
		<div class="row-fluid">
			<div class="span6">
				<fieldset class="form-horizontal">
					<?php foreach ($this->form->getFieldset('edit') as $field): ?>
					<div class="control-group">
						<div class="control-label"><?php echo $field->label; ?></div>
						<div class="controls"><?php echo $field->input; ?></div>
					</div>
					<?php endforeach; ?>
				</fieldset>
			</div>
		</div>

		<input type="hidden" name="controller" value="countries" />
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="id" value="<?php echo ($this->form->getValue('id') != 0) ? $this->form->getValue('id') : ''; ?>" />
		<?php echo JHtml::_('form.token'); ?>
	</form>
</div>
