<?php defined('_JEXEC') or die; ?>
<fieldset class="form-horizontal">
	<legend><?php echo JText::_('COM_KA_SETTINGS_AP_NAVGLOBAL_LABEL'); ?></legend>
	<?php foreach ($this->form->getFieldset('ap_nav') as $field): ?>
		<div class="control-group">
			<div class="control-label"><?php echo $field->label; ?></div>
			<div class="controls"><?php echo $field->input; ?></div>
		</div>
	<?php endforeach; ?>
</fieldset>
