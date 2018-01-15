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
<button class="btn btn-success cmd-form-urls" data-type="subtitles" type="submit">
	<?php echo JText::_('JTOOLBAR_ADD'); ?>
</button>
<a class="btn" onclick="document.getElementById('urls_url_subtitles').value='';document.getElementById('urls_url_subtitles_lang').value='';document.getElementById('urls_url_subtitles_default').value='false';" data-dismiss="modal">
	<?php echo JText::_('JCANCEL'); ?>
</a>
