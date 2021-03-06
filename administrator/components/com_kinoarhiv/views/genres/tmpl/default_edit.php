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
$item_type = (JFactory::getApplication()->input->get('type', 'movie', 'word') == 'music') ? 'music' : 'movie';
?>
<script type="text/javascript">
	Joomla.submitbutton = function(task) {
		if (task == 'relations') {
			document.location.href = 'index.php?option=com_kinoarhiv&view=relations&task=genres&type=<?php echo $item_type; ?>&element=movies<?php echo ($this->form->getValue('id') != 0) ? '&id='.$this->form->getValue('id') : ''; ?>';
			return;
		}
		if (task == 'apply' || task == 'save' || task == 'save2new') {
			if (document.getElementById('form_name').value == '' || document.getElementById('form_stats').value == '') {
				showMsg('#system-message-container', '<?php echo JText::_('COM_KA_REQUIRED'); ?>', 'before');
				return;
			}
		}
		Joomla.submitform(task);
	};

	jQuery(document).ready(function($){
		$('#form_stats').after('&nbsp;<a href="#" class="updateStat hasTooltip" title="<?php echo JText::_('COM_KA_GENRES_STATS_UPDATE'); ?>"><img src="components/com_kinoarhiv/assets/images/icons/arrow_refresh_small.png" border="0" /></a>');
		$('#adminForm').on('click', 'a.updateStat', function(e){
			e.preventDefault();
			var _this = $(this);

			$.getJSON('index.php?option=com_kinoarhiv&controller=genres&task=updateStat&type=<?php echo $item_type; ?>&id[]=<?php echo ($this->form->getValue('id') != 0) ? $this->form->getValue('id') : ''; ?>&format=json&<?php echo JSession::getFormToken(); ?>=1', function(response){
				if (response.success) {
					_this.prev('input').val(response.total);
					showMsg('#system-message-container', '<?php echo JText::_('COM_KA_GENRES_STATS_UPDATED'); ?>');
				} else {
					_this.prev('input').val('0');
					showMsg('#system-message-container', response.message);
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
	<input type="hidden" name="type" value="<?php echo $item_type; ?>" />
	<input type="hidden" name="id" value="<?php echo ($this->form->getValue('id') != 0) ? $this->form->getValue('id') : ''; ?>" />
	<?php echo JHtml::_('form.token'); ?>
</form>
