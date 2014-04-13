<?php defined('_JEXEC') or die; ?>
<fieldset class="form-horizontal">
	<legend><?php echo JText::_('COM_KA_SETTINGS_AP_AB_LABEL'); ?></legend>
	<div class="control-group">
		<div class="control-label"><?php echo $this->form->getLabel('use_alphabet'); ?></div>
		<div class="controls"><?php echo $this->form->getInput('use_alphabet'); ?></div>
	</div>

	<?php foreach ($this->data->params->get('alphabet') as $letters): ?>
	<div class="row-fluid">
		<div class="span4">
			<div class="control-group">
				<div class="control-label"><label><?php echo JText::_('COM_KA_SETTINGS_AP_AB_LANG_LABEL'); ?></label></div>
				<div class="controls"><input type="text" name="letters[][lang]" value="<?php echo $letters->lang; ?>" class="letters-lang span12" /></div>
			</div>
		</div>
		<div class="span8">
			<div class="control-group">
				<div class="control-label"><label class="hasTip" title="<?php echo JText::_('COM_KA_SETTINGS_AP_AB_LETTERS_DESC'); ?>"><?php echo JText::_('COM_KA_SETTINGS_AP_AB_LETTERS_LABEL'); ?></label></div>
				<div class="controls">
					<input type="text" name="letters[][letters]" value="<?php echo implode(',', $letters->letters); ?>" class="letters span10" />
					<div class="btn-group">
						<button type="button" class="btn btn-default cmd-ab-new-row"><span class="icon-plus"></span></button>
						<button type="button" class="btn btn-default cmd-ab-remove-row"><span class="icon-minus"></span></button>
					</div>
				</div>
			</div>
		</div>
	</div>
	<?php endforeach; ?>
</fieldset>
