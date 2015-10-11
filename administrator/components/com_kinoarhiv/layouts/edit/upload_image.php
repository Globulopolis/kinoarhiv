<?php
/**
 * @package     Kinoarhiv.Site
 * @subpackage  com_kinoarhiv
 *
 * @copyright   Copyright (C) 2010 Libra.ms. All rights reserved.
 * @license     GNU General Public License version 2 or later
 * @url            http://киноархив.com/
 */

defined('_JEXEC') or die;
?>
<div id="imgModalUpload" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="imgModalUploadLabel" aria-hidden="true">
	<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
		<h3 id="imgModalUploadLabel"><?php echo JText::_('COM_KA_TRAILERS_HEADING_UPLOAD_IMAGE'); ?></h3>
	</div>
	<div class="modal-body">
		<div>
			<input type="hidden" autofocus="autofocus" />
			<div id="image_uploader"><p>You browser doesn't have Flash, Silverlight or HTML5 support.</p></div>
		</div>
	</div>
	<div class="modal-footer">
		<button class="btn" data-dismiss="modal" aria-hidden="true"><?php echo JText::_('JTOOLBAR_CLOSE'); ?></button>
	</div>
</div>
