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
<button class="btn btn-success cmd-form-urls" data-type="video" type="submit">
	<?php echo JText::_('JTOOLBAR_ADD'); ?>
</button>
<a class="btn" onclick="document.getElementById('urls_url_video').value='';document.getElementById('form_trailer_finfo_video_type').value='';document.getElementById('urls_url_video_inplayer').value='true';" data-dismiss="modal">
	<?php echo JText::_('JCANCEL'); ?>
</a>
