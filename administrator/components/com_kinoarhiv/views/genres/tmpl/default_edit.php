<?php defined('_JEXEC') or die;
JHtml::_('behavior.keepalive');
?>
<script type="text/javascript">
	Joomla.submitbutton = function(task) {
		if (task == 'relations') {
			document.location.href = 'index.php?option=com_kinoarhiv&view=relations&task=genres<?php echo ($this->form->getValue('id') != 0) ? '&id='.$this->form->getValue('id') : ''; ?>';
			return;
		}
		if (task == 'apply' || task == 'save' || task == 'save2new') {
			if (jQuery('#form_name').val() == '' || jQuery('#form_stats').val() == '') {
				showMsg('#j-main-container', '<?php echo JText::_('COM_KA_REQUIRED'); ?>', 'before');
				return;
			}
		}
		Joomla.submitform(task);
	}

	jQuery(document).ready(function($){
		$('#form_stats').after('&nbsp;<a href="#" class="updateStat" title="<?php echo JText::_('COM_KA_GENRES_STATS_UPDATE'); ?>"><img src="components/com_kinoarhiv/assets/images/icons/arrow_refresh_small.png" border="0" /></a>');
		$('#adminForm').on('click', 'a.updateStat', function(e){
			e.preventDefault();
			var _this = $(this);

			$.getJSON('index.php?option=com_kinoarhiv&controller=genres&task=updateStat&id[]=<?php echo ($this->form->getValue('id') != 0) ? $this->form->getValue('id') : ''; ?>&format=json&<?php echo JSession::getFormToken(); ?>=1', function(response){
				if (response.success) {
					_this.prev('input').val(response.total);
					showMsg(_this, '<?php echo JText::_('COM_KA_GENRES_STATS_UPDATED'); ?>', 'after');
				} else {
					_this.prev('input').val('0');
					showMsg(_this, response.message, 'after');
				}
			});
		});
	});
</script>
<form action="<?php echo JRoute::_('index.php?option=com_kinoarhiv'); ?>" method="post" name="adminForm" id="adminForm" autocomplete="off">
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

	<input type="hidden" name="controller" value="genres" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="id" value="<?php echo ($this->form->getValue('id') != 0) ? $this->form->getValue('id') : ''; ?>" />
	<?php echo JHtml::_('form.token'); ?>
</form>
