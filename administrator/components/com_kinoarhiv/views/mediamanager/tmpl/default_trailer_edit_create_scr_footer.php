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
?>
<button class="btn btn-success cmd-create-scr" type="submit">
	<?php echo JText::_('JGLOBAL_BATCH_PROCESS'); ?>
</button>
<a class="btn" onclick="document.getElementById('screenshot_time').value='00:01:00';jQuery('.cmd-create-scr').removeProp('disabled');" data-dismiss="modal">
	<?php echo JText::_('COM_KA_CLOSE'); ?>
</a>
