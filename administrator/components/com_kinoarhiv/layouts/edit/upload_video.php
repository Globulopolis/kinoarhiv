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

$data = $displayData;
?>
<div id="videoModalUpload" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="videoModalUploadLabel" aria-hidden="true">
	<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
		<h3 id="videoModalUploadLabel"><?php echo JText::_('COM_KA_TRAILERS_VIDEO_UPLOAD_TITLE'); ?></h3>
	</div>
	<div class="modal-body">
		<div>
			<input type="hidden" autofocus="autofocus" />
			<div class="small">
				<?php echo JText::sprintf(
					'COM_KA_TRAILERS_EDIT_UPLOAD_FILENAME_CONVERT_VIDEO',
					$data['params']->get('upload_mime_video')
				);
				?>
			</div>
			<div id="video_uploader"><p>You browser doesn't have Flash, Silverlight or HTML5 support.</p></div>
		</div>
	</div>
	<div class="modal-footer">
		<button class="btn" data-dismiss="modal" aria-hidden="true"><?php echo JText::_('JTOOLBAR_CLOSE'); ?></button>
	</div>
</div>

<div id="subtitlesModalUpload" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="subtitlesModalUploadLabel" aria-hidden="true">
	<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
		<h3 id="subtitlesModalUploadLabel"><?php echo JText::_('COM_KA_TRAILERS_HEADING_UPLOAD_FILES_SUBTL'); ?></h3>
	</div>
	<div class="modal-body">
		<div>
			<input type="hidden" autofocus="autofocus" />
			<div class="small">
				<?php echo JText::sprintf(
					'COM_KA_TRAILERS_EDIT_UPLOAD_FILENAME_CONVERT_SUBTITLES',
					$data['params']->get('upload_mime_subtitles')
				);
				?>
			</div>
			<span class="small red" style="margin: 0 5px;"><?php echo JText::_('COM_KA_TRAILERS_HEADING_SUBTITLES_WARN'); ?></span>
			<div id="subtitles_uploader"><p>You browser doesn't have Flash, Silverlight or HTML5 support.</p></div>
		</div>
	</div>
	<div class="modal-footer">
		<button class="btn" data-dismiss="modal" aria-hidden="true"><?php echo JText::_('JTOOLBAR_CLOSE'); ?></button>
	</div>
</div>

<div id="chaptersModalUpload" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="chaptersModalUploadLabel" aria-hidden="true">
	<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
		<h3 id="chaptersModalUploadLabel"><?php echo JText::_('COM_KA_TRAILERS_HEADING_UPLOAD_FILES_CHAPTERS'); ?></h3>
	</div>
	<div class="modal-body">
		<div>
			<input type="hidden" autofocus="autofocus" />
			<div class="small">
				<?php echo JText::sprintf(
					'COM_KA_TRAILERS_EDIT_UPLOAD_FILENAME_CONVERT_CHAPTERS',
					$data['params']->get('upload_mime_chapters')
				);
				?>
			</div>
			<div id="chapters_uploader"><p>You browser doesn't have Flash, Silverlight or HTML5 support.</p></div>
		</div>
	</div>
	<div class="modal-footer">
		<button class="btn" data-dismiss="modal" aria-hidden="true"><?php echo JText::_('JTOOLBAR_CLOSE'); ?></button>
	</div>
</div>
