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
<button class="btn btn-success cmd-gallery-copyfrom" type="submit">
	<?php echo JText::_('JGLOBAL_BATCH_PROCESS'); ?>
</button>
<a class="btn" onclick="jQuery('#from_id').select2('val', '');document.getElementById('from_tab').value='1';" data-dismiss="modal">
	<?php echo JText::_('JTOOLBAR_CLOSE'); ?>
</a>
