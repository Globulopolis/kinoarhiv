<?php defined('_JEXEC') or die; ?>
<div class="row-fluid">
	<div class="span6">
		<fieldset class="form-horizontal">
			<div class="control-group">
				<div class="control-label"><?php echo $this->form->getLabel('metakey', $this->form_edit_group); ?></div>
				<div class="controls"><?php echo $this->form->getInput('metakey', $this->form_edit_group); ?></div>
			</div>
			<div class="control-group">
				<div class="control-label"><?php echo $this->form->getLabel('metadesc', $this->form_edit_group); ?></div>
				<div class="controls"><?php echo $this->form->getInput('metadesc', $this->form_edit_group); ?></div>
			</div>
		</fieldset>
	</div>
	<div class="span6">
		<fieldset class="form-horizontal">
			<div class="control-group">
				<div class="control-label"><?php echo $this->form->getLabel('robots', $this->form_edit_group); ?></div>
				<div class="controls"><?php echo $this->form->getInput('robots', $this->form_edit_group); ?></div>
			</div>
		</fieldset>
	</div>
</div>
