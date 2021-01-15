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
<button class="btn btn-primary" type="submit" onclick="Joomla.submitbutton('albums.batch');">
	<?php echo JText::_('JGLOBAL_BATCH_PROCESS'); ?>
</button>
<button class="btn" type="button" data-dismiss="modal"
		onclick="document.getElementById('batch-access').value='';document.getElementById('batch-language-id').value='';document.getElementById('batch-tag-id').value='';">
	<?php echo JText::_('JCANCEL'); ?>
</button>