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
<fieldset class="form-horizontal paths">
	<legend><?php echo JText::_('COM_KA_PATHS_LABEL'); ?></legend>
	<?php foreach ($this->form->getFieldset('paths') as $field): ?>
		<div class="control-group">
			<div class="control-label"><?php echo $field->label; ?></div>
			<div class="controls settings-paths"><?php echo $field->input; ?></div>
		</div>
	<?php endforeach; ?>
</fieldset>
