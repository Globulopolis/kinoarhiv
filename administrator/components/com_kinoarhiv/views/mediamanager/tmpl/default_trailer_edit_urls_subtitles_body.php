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
<div class="urlsSubtitlesModal">
	<form id="urls_layout_subtitles_form">
		<div class="form-horizontal">
			<div class="control-group">
				<div class="control-label">
					<label for="urls_url_subtitles"><?php echo JText::_('COM_KA_TRAILERS_HEADING_UPLOAD_URLS_SUBTITLES'); ?></label>
				</div>
				<div class="controls">
					<input id="urls_url_subtitles" class="span12" type="text" size="35" value="" name="urls_url_subtitles" required aria-required="true"/>
				</div>
			</div>

			<div class="control-group">
				<div class="control-label">
					<label for="urls_url_subtitles_lang"><?php echo JText::_('COM_KA_TRAILERS_HEADING_SUBTITLES_LANG_EDIT_SELECT'); ?></label>
				</div>
				<div class="controls">
					<?php echo JHtml::_('select.genericlist',
						$this->lang_list,
						'urls_url_subtitles_lang',
						array('class' => 'span6'),
						'value',
						'text',
						'en',
						'urls_url_subtitles_lang'
					); ?>
				</div>
			</div>

			<div class="control-group">
				<div class="control-label">
					<label for="urls_url_subtitles_default"><?php echo JText::_('JDEFAULT'); ?></label>
				</div>
				<div class="controls">
					<?php echo JHtml::_('select.genericlist',
						array('false' => JText::_('JNO'), 'true' => JText::_('JYES')),
						'urls_url_subtitles_default',
						array('class' => 'span6'),
						'value',
						'text',
						'false',
						'urls_url_subtitles_default'
					); ?>
				</div>
			</div>
		</div>
	</form>
</div>
