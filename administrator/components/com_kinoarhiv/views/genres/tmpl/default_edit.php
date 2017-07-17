<?php
/**
 * @package     Kinoarhiv.Administrator
 * @subpackage  com_kinoarhiv
 *  
 * @copyright   Copyright (C) 2017 Libra.ms. All rights reserved.
 * @license     GNU General Public License version 2 or later
 * @url         http://киноархив.com
 */

defined('_JEXEC') or die;

JHtml::_('behavior.formvalidator');
JHtml::_('behavior.keepalive');

$item_type = (JFactory::getApplication()->input->get('type', 'movie', 'word') == 'music') ? 'music' : 'movie';
$id = (int) $this->form->getValue('id');
?>
<script type="text/javascript">
	Joomla.submitbutton = function(task) {
		if (task === 'genres.cancel' || document.formvalidator.isValid(document.getElementById('item-form'))) {
			Joomla.submitform(task, document.getElementById('item-form'));
		}

		if (task === 'relations') {
			document.location.href = 'index.php?option=com_kinoarhiv&view=relations&task=genres&type=<?php echo $item_type; ?>&element=movies<?php echo $id != 0 ? '&id=' . $id : ''; ?>';
		}
	};

	jQuery(document).ready(function($){
		<?php if ($id != 0): ?>
		$('#form_stats').wrap('<div class="input-append"/>')
			 .after('<button class="btn cmd-update-stats hasTooltip" title="<?php echo JText::_('COM_KA_GENRES_STATS_UPDATE'); ?>"><span class="icon-refresh"></span></button>');

		$('.cmd-update-stats').tooltip();
		<?php endif; ?>

		$('form').on('click', 'a.cmd-update-stats', function(e){
			e.preventDefault();
			var _this = $(this);

			if (empty($('#form_id').val())) {
				return;
			}

			$.getJSON('index.php?option=com_kinoarhiv&task=genres.updateStat&type=<?php echo $item_type; ?>&id[]=<?php echo $id != 0 ? $id : ''; ?>&format=json&boxchecked=1&<?php echo JSession::getFormToken(); ?>=1', function(response){
				if (response.success) {
					_this.prev('input').val(response.total);
					showMsg('#system-message-container', response.message);
				} else {
					_this.prev('input').val('0');
					showMsg('#system-message-container', response.message);
				}
			}).fail(function (xhr, status, error) {
				showMsg('#system-message-container', error);
			});
		});
	});
</script>
<form action="<?php echo JRoute::_('index.php?option=com_kinoarhiv&id=' . $id); ?>" method="post" name="adminForm" autocomplete="off" id="item-form" class="form-validate">
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

	<input type="hidden" name="task" value="" />
	<input type="hidden" name="type" value="<?php echo $item_type; ?>" />
	<?php echo JHtml::_('form.token'); ?>
</form>
