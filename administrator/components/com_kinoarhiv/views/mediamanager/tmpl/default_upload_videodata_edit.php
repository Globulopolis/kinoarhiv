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
<form action="index.php" method="post" style="margin: 0;" id="video_edit_form">
	<fieldset class="form-horizontal">
		<div class="control-group">
			<div class="control-label">
				<label for="jform_src" class="required"><?php echo JText::_('COM_KA_TRAILERS_HEADING_VIDEOS_DATA_EDIT_FILENAME'); ?></label>
			</div>
			<div class="controls">
				<input id="jform_src" type="text" size="50" value="<?php echo $this->data['src']; ?>" name="src" class="span3" />
			</div>
		</div>
		<div class="control-group">
			<div class="control-label">&nbsp;</div>
			<div class="controls">
				<input id="jform_src_rename" type="checkbox" value="1" name="src_rename" checked /> <label for="jform_src_rename" style="display: inline;" class="hasTooltip" title="<?php echo JText::_('COM_KA_TRAILERS_HEADING_VIDEOS_DATA_EDIT_FILENAME_RENAME_DESC'); ?>"><?php echo JText::_('COM_KA_TRAILERS_HEADING_VIDEOS_DATA_EDIT_FILENAME_RENAME'); ?></label>
			</div>
		</div>
		<div class="control-group">
			<div class="control-label">
				<label for="jform_type"><?php echo JText::_('COM_KA_TRAILERS_HEADING_UPLOAD_URLS_VIDEO_TYPE'); ?></label>
			</div>
			<div class="controls">
				<?php echo JHTML::_('select.genericlist',
					array(''=>JText::_('JNONE'), 'video/mp4'=>'video/mp4', 'video/webm'=>'video/webm', 'video/ogv'=>'video/ogv'),
					'type',
					array('class'=>'span3'),
					'value',
					'text',
					$this->data['type'],
					'type'
				); ?>
			</div>
		</div>
		<div class="control-group">
			<div class="control-label">
				<label for="jform_resolution" class="hasTip" title="<?php echo JText::_('COM_KA_TRAILERS_HEADING_VIDEOS_DATA_EDIT_RESOLUTION_DESC'); ?>"><?php echo JText::_('COM_KA_TRAILERS_HEADING_VIDEOS_DATA_EDIT_RESOLUTION'); ?></label>
			</div>
			<div class="controls">
				<input id="jform_resolution" type="text" size="50" value="<?php echo $this->data['resolution']; ?>" name="resolution" class="span3" />
			</div>
		</div>
		<input id="jform_video_id" type="hidden" value="<?php echo $this->data['video_id']; ?>" name="video_id" />
		<?php echo JHtml::_('form.token'); ?>
		<div class="message"></div>
	</fieldset>
</form>
