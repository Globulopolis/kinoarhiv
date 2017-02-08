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
<div class="urlsChaptersModal">
	<form id="urls_layout_chapters_form">
		<div class="form-horizontal">
			<div class="control-group">
				<div class="control-label">
					<label for="urls_url_chapters"><?php echo JText::_('COM_KA_TRAILERS_HEADING_UPLOAD_URLS_CHAPTERS'); ?></label>
				</div>
				<div class="controls">
					<input id="urls_url_chapters" class="span12" type="text" size="35" value="" name="urls_url_chapters" required aria-required="true"/>
				</div>
			</div>
		</div>
	</form>
</div>
