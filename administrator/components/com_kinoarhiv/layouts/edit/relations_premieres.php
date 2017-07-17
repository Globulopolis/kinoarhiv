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

$form = $displayData['form'];

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
		document.formvalidator.setHandler('date', function(value){
			var regex = /^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/;

			if (!value.match(regex)) {
				return false;
			}

			return !!Date.parse(value);
		});
	});
</script>
<form action="<?php echo JRoute::_('index.php?option=com_kinoarhiv&item_id=' . JFactory::getApplication()->input->getInt('item_id', 0)); ?>"
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
	</div>
	<input type="hidden" name="task" value=""/>
	<?php echo JHtml::_('form.token'); ?>
</form>
