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

/** @var array $displayData */
$form = $displayData['form'];

JHtml::_('behavior.formvalidator');
JHtml::_('behavior.keepalive');
JHtml::_('script', 'media/com_kinoarhiv/js/validation-rules.min.js');
?>
<script type="text/javascript">
	Joomla.submitbutton = function(task){
		if (task === 'cancel') {
			window.close();
		} else {
			if (document.formvalidator.isValid(document.getElementById('rel-release-form'))) {
				Joomla.submitform(task, document.getElementById('rel-release-form'));
			}
		}
	};
</script>
<form action="<?php echo JRoute::_('index.php?option=com_kinoarhiv&item_id=' . JFactory::getApplication()->input->getInt('item_id', 0)); ?>"
	method="post" name="adminForm" id="rel-release-form" autocomplete="off" class="form-validate">
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
