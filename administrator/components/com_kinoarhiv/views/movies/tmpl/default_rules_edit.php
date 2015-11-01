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
<form action="<?php echo JUri::base(); ?>index.php?option=com_kinoarhiv&controller=movies&task=saveAccessRules&format=json"
	id="rulesForm" autocomplete="off">
	<fieldset class="form-horizontal">
		<div class="control-group">
			<div class="controls" style="margin-left: 0 !important;"><?php echo $this->form->getInput('rules', 'movie'); ?></div>
		</div>
	</fieldset>
	<input type="hidden" name="<?php echo JSession::getFormToken(); ?>" value="1" />
</form>
