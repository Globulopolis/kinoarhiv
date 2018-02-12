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
$form  = $displayData['form'];

JHtml::_('behavior.formvalidator');
JHtml::_('behavior.keepalive');
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

	jQuery(document).ready(function($){
		$('.cmd-voice-artists label').click(function(){
			var is_dir_group = $('.is_directors').closest('.control-group');

			if ($(this).hasClass('active btn-success')) {
				is_dir_group.slideUp();

				// Set Director radio button to none.
				var is_dir_radio1 = is_dir_group.find('input[value="0"]');
				is_dir_radio1.prop('checked', true);
				is_dir_radio1.next().addClass('active btn-danger');
				is_dir_radio1.prev().removeClass('active btn-success');
				is_dir_radio1.prev().prev().prop('checked', false);
			} else {
				is_dir_group.slideDown();
			}
		});
	});
</script>
<form action="<?php echo JRoute::_('index.php?option=com_kinoarhiv&item_id=' . $input->getInt('item_id', 0) . '&input_name=' . $input->getString('input_name', '')); ?>"
	method="post" name="adminForm" id="adminForm" autocomplete="off" class="form-validate">
	<div class="row-fluid form-horizontal-desktop">
		<div class="span6">
			<fieldset class="form-horizontal">
				<?php foreach ($form->getFieldset() as $field): ?>
					<div class="control-group">
						<div class="control-label"><?php echo $field->label; ?></div>
						<div class="controls"><?php echo $field->input; ?></div>
					</div>
				<?php endforeach; ?>
			</fieldset>
		</div>

		<div class="span6">
			<?php echo KAComponentHelper::showMsg(JText::_('COM_KA_MOVIES_NAMES_LAYOUT_ADD_HELP')); ?>
		</div>
	</div>
	<input type="hidden" name="task" value=""/>
	<?php echo JHtml::_('form.token'); ?>
</form>
