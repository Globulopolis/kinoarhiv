<?php defined('_JEXEC') or die;
JHtml::_('script', 'system/html5fallback.js', false, true);
?>
<div class="modal hide fade" id="collapseModal">
	<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal">&#215;</button>
		<h3><?php echo JText::_('COM_KA_SETTINGS_BUTTON_RESTORECONFIG'); ?></h3>
	</div>
	<div class="modal-body modal-upload">
		<div class="row-fluid">
			<form method="post" enctype="multipart/form-data" id="adminRestoreConfig">
				<fieldset class="form-horizontal">
					<div class="control-group span6">
						<div class="control-label"><label id="form_upload_config-lbl" class="" for="form_upload_config"><?php echo JText::_('COM_KA_SETTINGS_BUTTON_RESTORECONFIG_FILE'); ?></label></div>
						<div class="controls">
							<input id="form_upload_config" type="file" accept=".json" value="" name="form_upload_config" required aria-required="true" />
						</div>
					</div>
					<input type="hidden" name="controller" value="settings" />
					<input type="hidden" name="task" value="restoreConfig" />
					<?php echo JHtml::_('form.token'); ?>
				</fieldset>
			</form>
		</div>
	</div>
	<div class="modal-footer">
		<button class="btn btn-primary" type="submit" onclick="Joomla.submitbutton('restoreConfig');">
			<?php echo JText::_('JTOOLBAR_UNTRASH'); ?>
		</button>
		<button class="btn" type="button" onclick="document.getElementById('form_upload_config').value='';" data-dismiss="modal">
			<?php echo JText::_('JCANCEL'); ?>
		</button>
	</div>
</div>
