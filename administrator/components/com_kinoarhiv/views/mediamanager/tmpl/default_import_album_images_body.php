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
<div class="container-fluid">
	<div class="row-fluid">
		<div class="control-group span12">
			<div class="control-label">
				<label for="import_images_path" class="required"><?php echo JText::_('JLIB_HTML_TOOLBAR_IMPORT_IMAGES_LABEL'); ?> *</label>
			</div>
			<div class="controls">
				<input type="text" name="import_images_path" id="import_images_path" value="" class="span12 required" required />
				<span class="help-block">
					<?php echo JText::sprintf(
						'COM_KA_MUSIC_ALBUMS_IMPORT_IMAGES_HELP',
						JText::_('COM_KA_FIELD_COVER_PATH_LABEL'),
						JText::_('COM_KA_FIELD_COVER_PATH_WWW_LABEL')
					); ?>
				</span>
			</div>
		</div>
	</div>
	<div class="row-fluid">
		<div class="import-result"></div>
	</div>
</div>
