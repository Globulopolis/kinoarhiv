<?php defined('_JEXEC') or die; ?>
<fieldset class="form-horizontal">
	<?php foreach ($this->form->getFieldset('music_global') as $field): ?>
		<div class="control-group">
			<div class="control-label"><?php echo $field->label; ?></div>
			<div class="controls"><?php echo $field->input; ?></div>
		</div>
	<?php endforeach; ?>
</fieldset>
