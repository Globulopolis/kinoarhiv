<?php
/**
 * @package     Kinoarhiv.Administrator
 * @subpackage  com_kinoarhiv
 *
 * @copyright   Copyright (C) 2018 Libra.ms. All rights reserved.
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
