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
$remoteupload = false;

if (isset($data['remoteupload']) && $data['remoteupload'] === true)
{
	$remoteupload = true;
}
?>
<div id="imgModalUpload" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="imgModalUploadLabel" aria-hidden="true">
	<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
		<h3 id="imgModalUploadLabel"><?php echo JText::_('COM_KA_TRAILERS_HEADING_UPLOAD_IMAGES'); ?></h3>
	</div>
	<div class="modal-body">
		<div>
			<?php echo JHtml::_('bootstrap.startTabSet', 'upload_tab', array('active' => 'local')); ?>
				<?php echo JHtml::_('bootstrap.addTab', 'upload_tab', 'local', JText::_('COM_KA_TRAILERS_HEADING_UPLOAD_IMAGE_LOCAL')); ?>

				<input type="hidden" autofocus="autofocus" />
				<div id="image_uploader"><p>You browser doesn't have Flash, Silverlight or HTML5 support.</p></div>

				<?php echo JHtml::_('bootstrap.endTab'); ?>
				<?php if ($remoteupload):
				echo JHtml::_('bootstrap.addTab', 'upload_tab', 'remote', JText::_('COM_KA_TRAILERS_HEADING_UPLOAD_IMAGE_REMOTE')); ?>

				<fieldset class="form-horizontal">
					<div class="control-group">
						<div class="control-label">
							<label for="remote_urls" class="hasPopover" title="<?php echo JText::_('COM_KA_TRAILERS_HEADING_UPLOAD_IMAGE_URL'); ?>" data-content="<?php echo JText::_('COM_KA_TRAILERS_HEADING_UPLOAD_IMAGE_URL_HELP'); ?>"><?php echo JText::_('COM_KA_TRAILERS_HEADING_UPLOAD_IMAGE_URL'); ?></label>
						</div>
						<div class="controls">
							<textarea name="remote_urls" id="remote_urls" rows="7" cols="32" class="span12"></textarea>
						</div>
					</div>
					<input type="button" class="btn btn-success cmd-remote-urls" value="<?php echo JText::_('JTOOLBAR_UPLOAD'); ?>" />
				</fieldset>

				<?php
				echo JHtml::_('bootstrap.endTab');
				endif; ?>
			<?php echo JHtml::_('bootstrap.endTabSet'); ?>
		</div>
	</div>
	<div class="modal-footer">
		<button class="btn" data-dismiss="modal" aria-hidden="true"><?php echo JText::_('JTOOLBAR_CLOSE'); ?></button>
	</div>
</div>
