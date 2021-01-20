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

$formUrl = 'index.php?option=com_kinoarhiv&view=releases';
?>
<script type="text/javascript">
	Joomla.submitbutton = function(task) {
		if (task === 'releases.cancel' || document.formvalidator.isValid(document.getElementById('item-form'))) {
			if (task === 'releases.save2new') {
				jQuery('#selectType').modal('show');
				return;
			}

			Joomla.submitform(task, document.getElementById('item-form'));
		}
	};

	function setType(type) {
		var input = document.getElementById('new_item_type');
		input.setAttribute('value', type);
		Joomla.submitform('releases.save2new', document.getElementById('item-form'));
	}
</script>
<form action="<?php echo JRoute::_('index.php?option=com_kinoarhiv&id=' . $this->form->getValue('id')); ?>"
	  method="post" name="adminForm" autocomplete="off" id="item-form" class="form-validate">
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
	<input type="hidden" name="new_item_type" id="new_item_type" value="" />
	<?php echo JHtml::_('form.token'); ?>
</form>
<?php
echo JHtml::_(
	'bootstrap.renderModal',
	'selectType',
	array(
		'title'      => JText::_('COM_KA_FIELD_TYPE_LABEL'),
		'footer'     => '<a class="btn" data-dismiss="modal">' . JText::_('COM_KA_CLOSE') . '</a>',
		'modalWidth' => 20
	),
	'<div class="container-fluid">
		<a href="' . $formUrl . '&task=releases.add&item_type=0" class="btn btn-success" onclick="setType(0);return false;">' . JText::_('COM_KA_TABLES_RELATIONS_AWARDS_TYPE_0') . '</a>
		<a href="' . $formUrl . '&task=releases.add&item_type=1" class="btn btn-info" onclick="setType(1);return false;">' . JText::_('COM_KA_MUSIC_ALBUM_TITLE') . '</a>
	</div>'
);
