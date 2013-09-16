<?php defined('_JEXEC') or die; ?>
<fieldset class="form-horizontal">
	<legend><?php echo JText::_('COM_KA_REVIEWS_SETTINGS_SAVE_LABEL'); ?></legend>
	<?php foreach ($this->form->getFieldset('reviews_save') as $field): ?>
		<div class="control-group">
			<div class="control-label"><?php echo $field->label; ?></div>
			<div class="controls"><?php echo $field->input; ?></div>
		</div>
	<?php endforeach; ?>
</fieldset>
