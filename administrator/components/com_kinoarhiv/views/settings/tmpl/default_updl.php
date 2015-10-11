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
?>
<div class="span6">
	<fieldset class="form-horizontal">
		<legend><?php echo JText::_('COM_KA_SETTINGS_GALLERY_LABEL'); ?></legend>
		<?php foreach ($this->form->getFieldset('gallery') as $field): ?>
			<div class="control-group">
				<div class="control-label"><?php echo $field->label; ?></div>
				<div class="controls"><?php echo $field->input; ?></div>
			</div>
		<?php endforeach; ?>
	</fieldset>
</div>
<div class="span6">
	<fieldset class="form-horizontal">
		<legend><?php echo JText::_('COM_KA_SETTINGS_UPLOAD_DOWNLOAD_LABEL'); ?></legend>
		<?php foreach ($this->form->getFieldset('content_dl') as $field): ?>
			<div class="control-group">
				<div class="control-label"><?php echo $field->label; ?></div>
				<div class="controls"><?php echo $field->input; ?></div>
			</div>
		<?php endforeach; ?>
	</fieldset>
</div>
