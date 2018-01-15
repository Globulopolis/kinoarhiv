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
?>
<script type="text/javascript">
	Joomla.submitbutton = function(task) {
		if (task === 'reviews.cancel' || document.formvalidator.isValid(document.getElementById('item-form'))) {
			Joomla.submitform(task, document.getElementById('item-form'));
		}
	};
</script>
<form action="<?php echo JRoute::_('index.php?option=com_kinoarhiv&id=' . $this->form->getValue('id')); ?>" method="post" name="adminForm" autocomplete="off" id="item-form" class="form-validate">
	<div id="j-main-container">
		<div class="row-fluid">
			<fieldset class="form-horizontal">
				<div class="span6">
					<div class="control-group">
						<?php echo $this->form->getLabel('review'); ?>
						<?php echo $this->form->getInput('review'); ?>
					</div>
				</div>
				<div class="span6">
					<div class="control-group">
						<div class="control-label"><?php echo $this->form->getLabel('movie_id'); ?></div>
						<div class="controls"><?php echo $this->form->getInput('movie_id'); ?></div>
					</div>
					<div class="control-group">
						<div class="control-label"><?php echo $this->form->getLabel('created'); ?></div>
						<div class="controls"><?php echo $this->form->getInput('created'); ?></div>
					</div>
					<div class="control-group">
						<div class="control-label"><?php echo $this->form->getLabel('uid'); ?></div>
						<div class="controls"><?php echo $this->form->getInput('uid'); ?></div>
					</div>
					<div class="control-group">
						<div class="control-label"><?php echo $this->form->getLabel('ip'); ?></div>
						<div class="controls"><?php echo $this->form->getInput('ip'); ?></div>
					</div>
					<div class="control-group">
						<div class="control-label"><?php echo $this->form->getLabel('type'); ?></div>
						<div class="controls"><?php echo $this->form->getInput('type'); ?></div>
					</div>
					<div class="control-group">
						<div class="control-label"><?php echo $this->form->getLabel('state'); ?></div>
						<div class="controls"><?php echo $this->form->getInput('state'); ?></div>
					</div>
					<div class="control-group">
						<div class="control-label"><?php echo $this->form->getLabel('id'); ?></div>
						<div class="controls"><?php echo $this->form->getInput('id'); ?></div>
					</div>
				</div>
			</fieldset>
		</div>
	</div>

	<input type="hidden" name="task" value="" />
	<?php echo JHtml::_('form.token'); ?>
</form>
