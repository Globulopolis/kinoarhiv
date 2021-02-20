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

$input = JFactory::getApplication()->input;
$task  = $input->get('task', '');

/** @var array $displayData */
/** @var JForm $form */
$form  = $displayData['form'];

JHtml::_('behavior.formvalidator');
JHtml::_('behavior.keepalive');
JHtml::_('formbehavior.chosen', 'select');
JHtml::_('script', 'media/com_kinoarhiv/js/validation-rules.min.js');
?>
<script type="text/javascript">
	Joomla.submitbutton = function(task){
		if (task === 'cancel') {
			window.close();
		} else {
			if (document.formvalidator.isValid(document.getElementById('adminForm'))) {
				Joomla.submitform(task, document.getElementById('adminForm'));
			}
		}
	};
</script>
<form action="<?php echo JRoute::_('index.php?option=com_kinoarhiv'); ?>" method="post" name="adminForm"
	  id="adminForm" autocomplete="off" class="form-validate">
	<div class="row-fluid form-horizontal-desktop">
	<?php if ($task == 'editTrack'): ?>

		<div class="span6">
			<fieldset class="form-horizontal">
				<?php foreach ($form->getFieldset('basic') as $field):
					if ($field->getAttribute('name') == 'filename'): ?>

					<div class="control-group">
						<div class="control-label"><?php echo $field->label; ?></div>
						<div class="controls"><?php echo $field->input; ?></div>
					</div>
					<?php else:
						echo $field->renderField();
					endif;
				endforeach; ?>
			</fieldset>
		</div>
		<div class="span6">
			<fieldset class="form-horizontal">
				<?php echo $form->renderFieldset('additional'); ?>
			</fieldset>
		</div>

	<?php else: ?>

		<div class="span6">
			<fieldset class="form-horizontal">
				<?php foreach ($form->getFieldset() as $field):
					echo $field->renderField();
				endforeach; ?>
			</fieldset>
		</div>

			<?php if ($task == 'editMovieCast'): ?>
			<div class="span6">
				<?php echo KAComponentHelper::showMsg(JText::_('COM_KA_MOVIES_NAMES_LAYOUT_ADD_HELP')); ?>
			</div>
			<?php endif; ?>

	<?php endif; ?>
	</div>

	<input type="hidden" name="task" value=""/>
	<input type="hidden" name="item_id" value="<?php echo $input->getInt('item_id', 0); ?>"/>
	<input type="hidden" name="row_id" value="<?php echo $input->getInt('row_id', 0); ?>"/>
	<input type="hidden" name="input_name" value="<?php echo $input->getCmd('input_name', ''); ?>"/>
	<?php echo JHtml::_('form.token'); ?>
</form>
