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

$url = 'index.php?option=com_kinoarhiv&task=mediamanager.upload&format=json&section=' . $this->section
	. '&type=' . $this->type . '&id=' . $this->id . '&item_id=' . $this->trailer_id;
?>
<?php echo JHtml::_('bootstrap.startTabSet', 'upload_video_tab', array('active' => 'video')); ?>
	<?php echo JHtml::_('bootstrap.addTab', 'upload_video_tab', 'video', JText::_('COM_KA_TRAILERS_VIDEO_UPLOAD_TITLE')); ?>

	<div>
		<input type="hidden" autofocus="autofocus" />
		<div class="hasUploader" data-url="<?php echo $url; ?>&upload=video"
			 data-multipart_params="{'<?php echo JSession::getFormToken(); ?>': 1}" data-content-type="video"
			 data-max_file_size="<?php echo $this->params->get('upload_limit'); ?>" data-multiple_queues="true"
			 data-filters="[{title: 'Video files', extensions: '<?php echo $this->params->get('upload_mime_video'); ?>'}]"
			 data-chunk_size="<?php echo $this->params->get('upload_chunk_size'); ?>"
		>
			<p>You browser doesn't have Flash, Silverlight or HTML5 support.</p>
		</div>
		<?php echo KAComponentHelper::showMsg(
			JText::sprintf('COM_KA_TRAILERS_EDIT_UPLOAD_FILENAME_CONVERT_VIDEO', $this->params->get('upload_mime_video')),
			array('type' => 'disable')
		); ?>
	</div>

	<?php echo JHtml::_('bootstrap.endTab'); ?>
	<?php echo JHtml::_('bootstrap.addTab', 'upload_video_tab', 'subtitles', JText::_('COM_KA_TRAILERS_HEADING_UPLOAD_FILES_SUBTL')); ?>

	<div>
		<input type="hidden" autofocus="autofocus" />
		<div class="hasUploader" data-url="<?php echo $url; ?>&upload=subtitles"
		     data-multipart_params="{'<?php echo JSession::getFormToken(); ?>': 1}" data-content-type="subtitles"
		     data-max_file_size="<?php echo $this->params->get('upload_limit'); ?>" data-multiple_queues="true"
		     data-filters="[{title: 'Subtitle files', extensions: '<?php echo $this->params->get('upload_mime_subtitles'); ?>'}]"
		     data-chunk_size="<?php echo $this->params->get('upload_chunk_size'); ?>"
		>
			<p>You browser doesn't have Flash, Silverlight or HTML5 support.</p>
		</div>
		<?php echo KAComponentHelper::showMsg(
			JText::sprintf('COM_KA_TRAILERS_EDIT_UPLOAD_FILENAME_CONVERT_SUBTITLES', $this->params->get('upload_mime_subtitles')) . JText::_('COM_KA_TRAILERS_HEADING_SUBTITLES_WARN'),
			array('type' => 'disable')
		); ?>
	</div>

	<?php echo JHtml::_('bootstrap.endTab'); ?>
	<?php echo JHtml::_('bootstrap.addTab', 'upload_video_tab', 'chapters', JText::_('COM_KA_TRAILERS_HEADING_UPLOAD_FILES_CHAPTERS')); ?>

	<div>
		<input type="hidden" autofocus="autofocus" />
		<div class="hasUploader" data-url="<?php echo $url; ?>&upload=chapters"
		     data-multipart_params="{'<?php echo JSession::getFormToken(); ?>': 1}" data-content-type="chapters"
		     data-multi_selection="false" data-max_files="1"
		     data-max_file_size="<?php echo $this->params->get('upload_limit'); ?>" data-multiple_queues="true"
		     data-filters="[{title: 'Chapter files', extensions: '<?php echo $this->params->get('upload_mime_chapters'); ?>'}]"
		     data-chunk_size="<?php echo $this->params->get('upload_chunk_size'); ?>"
		>
			<p>You browser doesn't have Flash, Silverlight or HTML5 support.</p>
		</div>
		<?php echo KAComponentHelper::showMsg(
			JText::sprintf('COM_KA_TRAILERS_EDIT_UPLOAD_FILENAME_CONVERT_CHAPTERS', $this->params->get('upload_mime_chapters')),
			array('type' => 'disable')
		); ?>
	</div>

	<?php echo JHtml::_('bootstrap.endTab'); ?>
<?php echo JHtml::_('bootstrap.endTabSet'); ?>
