<?php defined('_JEXEC') or die; ?>
<div class="row-fluid">
	<div class="span6">
		<fieldset class="form-horizontal">
			<legend><?php echo JText::_('COM_KA_SEARCH_SETTINGS_PREMIERES'); ?></legend>
			<?php foreach ($this->form->getFieldset('filter_premieres') as $field): ?>
				<div class="control-group">
					<div class="control-label"><?php echo $field->label; ?></div>
					<div class="controls"><?php echo $field->input; ?></div>
				</div>
			<?php endforeach; ?>
		</fieldset>
	</div>
	<div class="span6">
		<fieldset class="form-horizontal">
			<legend><?php echo JText::_('COM_KA_SEARCH_SETTINGS_RELEASES'); ?></legend>
			<?php foreach ($this->form->getFieldset('filter_releases') as $field): ?>
				<div class="control-group">
					<div class="control-label"><?php echo $field->label; ?></div>
					<div class="controls"><?php echo $field->input; ?></div>
				</div>
			<?php endforeach; ?>
		</fieldset>
	</div>
</div>
