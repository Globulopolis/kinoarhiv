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
<button class="btn btn-primary" type="submit" onclick="Joomla.submitbutton('reviews.batch');">
	<?php echo JText::_('JGLOBAL_BATCH_PROCESS'); ?>
</button>
<button class="btn" type="button" onclick="document.getElementById('batch-type').value='';document.getElementById('batch-user-id').value='';" data-dismiss="modal">
	<?php echo JText::_('JCANCEL'); ?>
</button>
