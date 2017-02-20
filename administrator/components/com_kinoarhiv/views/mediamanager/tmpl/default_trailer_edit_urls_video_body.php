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
<div class="urlsVideoModal">
	<form id="urls_layout_video_form">
		<div class="form-horizontal">
			<div class="control-group">
				<div class="control-label">
					<label for="urls_url_video"><?php echo JText::_('COM_KA_TRAILERS_UPLOAD_URLS_VIDEO'); ?><span class="star">&nbsp;*</span></label>
				</div>
				<div class="controls">
					<input id="urls_url_video" class="span12 required" type="text" size="35" value="" name="urls_url_video" required aria-required="true"/>
				</div>
			</div>

			<?php echo $this->form->renderField('type', 'trailer_finfo_video'); ?>

			<div class="control-group">
				<div class="control-label">
					<label for="urls_url_video_inplayer"><?php echo JText::_('COM_KA_TRAILERS_UPLOAD_URLS_VIDEO_INPLAYER'); ?></label>
				</div>
				<div class="controls">
					<?php echo JHtml::_('select.genericlist',
						array('false' => JText::_('JNO'), 'true' => JText::_('JYES')),
						'urls_url_video_inplayer',
						array('class' => 'span6'),
						'value',
						'text',
						'true',
						'urls_url_video_inplayer'
					); ?>
				</div>
			</div>
		</div>
	</form>
</div>
