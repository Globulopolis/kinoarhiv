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
<div class="createScreenshotModal container-fluid">
	<form id="screenshot_layout_create_form" class="form-validate">
		<div class="form-horizontal">
			<div class="control-group">
				<div class="control-label">
					<label for="screenshot_time" class="hasPopover" data-title="<?php echo JText::_('COM_KA_TRAILERS_VIDEO_SCREENSHOT_CREATE_TIME'); ?>" data-content="<?php echo JText::_('COM_KA_TRAILERS_VIDEO_SCREENSHOT_CREATE_TIME_DESC'); ?>"><?php echo JText::_('COM_KA_TRAILERS_VIDEO_SCREENSHOT_CREATE_TIME'); ?> <span>*</span></label>
				</div>
				<div class="controls">
					<div class="input-append">
						<input type="text" name="screenshot_time" id="screenshot_time" value="00:01:00" required="required" size="16" maxlength="12" placeholder="00:00:00" class="hasDatetime required validate-time" data-type="time" data-time-format="HH:mm:ss" />
					</div>
				</div>
			</div>
		</div>
	</form>

	<?php echo JHtml::_('bootstrap.startAccordion', 'stdoutSlide', array('active' => ''));
		echo JHtml::_('bootstrap.addSlide', 'stdoutSlide', JText::_('STDOUT'), 'slide0'); ?>

		<p></p>

		<?php echo JHtml::_('bootstrap.endSlide');
	echo JHtml::_('bootstrap.endAccordion'); ?>
</div>
