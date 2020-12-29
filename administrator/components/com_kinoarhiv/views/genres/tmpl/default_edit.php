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

$id = (int) $this->form->getValue('id');
?>
<script type="text/javascript">
	Joomla.submitbutton = function(task) {
		if (task === 'genres.cancel' || document.formvalidator.isValid(document.getElementById('item-form'))) {
			Joomla.submitform(task, document.getElementById('item-form'));
		}

		if (task === 'relations') {
			document.location.href = 'index.php?option=com_kinoarhiv&view=relations&task=genres&element=movies<?php echo $id != 0 ? '&id=' . $id : ''; ?>';
		}
	};
</script>
<form action="<?php echo JRoute::_('index.php?option=com_kinoarhiv&id=' . $id); ?>" method="post" name="adminForm"
	  autocomplete="off" id="item-form" class="form-validate">
	<div class="form-horizontal">
		<div class="row-fluid">
			<div class="span6"><?php echo $this->form->renderField('name'); ?></div>
			<div class="span6"><?php echo $this->form->renderField('language'); ?></div>
		</div>
		<div class="row-fluid">
			<div class="span6"><?php echo $this->form->renderField('alias'); ?></div>
			<div class="span6"><?php echo $this->form->renderField('access'); ?></div>
		</div>
		<div class="row-fluid">
			<div class="span6"><?php echo $this->form->renderField('type'); ?></div>
			<div class="span6"><?php echo $this->form->renderField('state'); ?></div>
		</div>
		<div class="row-fluid">
			<div class="span6"><?php echo $this->form->renderField('stats'); ?></div>
			<div class="span6"><?php echo $this->form->renderField('id'); ?></div>
		</div>
		<div class="row-fluid">
			<div class="span12"><?php echo $this->form->renderField('desc'); ?></div>
		</div>
	</div>

	<input type="hidden" name="task" value="" />
	<?php echo JHtml::_('form.token'); ?>
</form>
