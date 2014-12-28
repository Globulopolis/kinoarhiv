<?php defined('_JEXEC') or die; ?>
<form action="index.php?option=com_kinoarhiv&controller=names&task=saveNameAccessRules&format=json" id="rulesForm" autocomplete="off">
	<fieldset class="form-horizontal">
		<div class="control-group">
			<div class="controls" style="margin-left: 0px !important;"><?php echo $this->form->getInput('rules', 'name'); ?></div>
		</div>
	</fieldset>
	<input type="hidden" name="<?php echo JSession::getFormToken(); ?>" value="1" />
</form>
