<?php defined('_JEXEC') or die;
JHtml::_('behavior.keepalive');
?>
<script type="text/javascript">
	Joomla.submitbutton = function(task) {
		if (task == 'relations') {
			document.location.href = 'index.php?option=com_kinoarhiv&view=relations&task=careers<?php echo !empty($this->form->getValue('id')) ? '&id='.$this->form->getValue('id') : ''; ?>';
			return;
		}
		if (task == 'apply' || task == 'save' || task == 'save2new') {
			if (jQuery('#form_title').val() == '') {
				showMsg('#j-main-container', '<?php echo JText::_('COM_KA_REQUIRED'); ?>');
				return;
			}
		}
		Joomla.submitform(task);
	}
</script>
<form action="<?php echo JRoute::_('index.php?option=com_kinoarhiv'); ?>" method="post" name="adminForm" id="adminForm" class="form-validate" autocomplete="off">
	<div id="j-main-container">
		<fieldset class="form-horizontal">
			<?php foreach ($this->form->getFieldset('edit') as $field): ?>
			<div class="control-group">
				<div class="control-label"><?php echo $field->label; ?></div>
				<div class="controls"><?php echo $field->input; ?></div>
			</div>
			<?php endforeach; ?>
		</fieldset>
	</div>

	<input type="hidden" name="controller" value="careers" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="id" value="<?php echo !empty($this->form->getValue('id')) ? $this->form->getValue('id') : ''; ?>" />
	<?php echo JHtml::_('form.token'); ?>
</form>
