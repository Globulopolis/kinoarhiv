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

JHtml::_('behavior.keepalive');
JHtml::_('behavior.tabstate');
JHtml::_('bootstrap.tooltip');
JHtml::_('bootstrap.modal', 'collapseModal');
JHtml::_('formbehavior.chosen', 'select:not(.hasAutocomplete)');
JHtml::_('stylesheet', 'media/com_kinoarhiv/jqueryui/' . $this->form->getValue('ui_theme') . '/jquery-ui.min.css');
JHtml::_('script', 'media/com_kinoarhiv/js/jquery-ui.min.js');
?>
<script type="text/javascript">
	Joomla.submitbutton = function(task) {
		jQuery(document).ready(function($){
			var form = $('#adminForm'),
				datetime = Kinoarhiv.datetime() + ' ';

			if (task !== 'settings.cancel' && task !== 'settings.save' && task !== 'settings.saveConfig'
				&& task !== 'restoreConfigLayout' && task !== 'settings.restoreConfig')
			{
				$.post(form.attr('action'), form.serialize() + '&task=' + task + '&format=json', function(response){
					Aurora.message([{text: datetime + response.message, type: 'success'}], '', {attachTo: 'window', replace: true});
				}).fail(function(xhr, status, error){
					Aurora.message([{text: datetime + error, type: 'error'}], '#system-message-container', {replace: true});
				});
			} else {
				if (task === 'settings.saveConfig') {
					window.location = '<?php echo JUri::base(); ?>index.php?option=com_kinoarhiv&task=settings.saveConfig&format=json';
				} else if (task === 'restoreConfigLayout') {
					$('#collapseModal').modal();
				} else if (task === 'settings.restoreConfig') {
					Joomla.submitform(task, document.getElementById('adminRestoreConfig'));
				} else {
					Joomla.submitform(task, document.getElementById('adminForm'));
				}
			}
		});
	};

	jQuery(document).ready(function($){
		$('#jform_premieres_list_limit, #jform_releases_list_limit').spinner({
			spin: function(event, ui){
				if (ui.value > 5) {
					$(this).spinner('value', 0);
					return false;
				} else if (ui.value < 0) {
					$(this).spinner('value', 5);
					return false;
				}
			}
		});
		$('#jform_person_list_limit').spinner({
			spin: function(event, ui){
				if (ui.value > 10) {
					$(this).spinner('value', 1);
					return false;
				} else if (ui.value < 1) {
					$(this).spinner('value', 10);
					return false;
				}
			}
		});
		$('#jform_introtext_actors_list_limit').spinner({
			spin: function(event, ui){
				if (ui.value > 10) {
					$(this).spinner('value', 0);
					return false;
				} else if (ui.value < 0) {
					$(this).spinner('value', 10);
					return false;
				}
			}
		});
		$('#jform_slider_min_item').spinner({
			spin: function(event, ui){
				if (ui.value > 10) {
					$(this).spinner('value', 1);
					return false;
				} else if (ui.value < 1) {
					$(this).spinner('value', 10);
					return false;
				}
			}
		});

		// For alphabet
		var cloned_rows = $('.alphabet .letters-lang').length;
		$('.cmd-ab-new-row').click(function(e){
			e.preventDefault();
			var row = $(this).closest('.row-fluid');
			var cloned_row = row.clone(true);

			row.after(cloned_row);
			$('.letters-lang', cloned_row).val('');
			$('.letters', cloned_row).val('');
			cloned_rows++;
		});
		$('.cmd-ab-remove-row').click(function(e){
			e.preventDefault();

			if (cloned_rows > 1) {
				$(this).closest('.row-fluid').remove();
				cloned_rows--;
			}
		});
		// End

		$.post('index.php?option=com_kinoarhiv&task=settings.validatePaths&format=json',
			$('form .validate-path').serialize(), function(response){
				$.each(response, function(key, message){
					$('#jform_' + key).css({
						'color': 'red',
						'border': '1px solid red'
					})
					.attr('title', message);
				});

				$('input[title]').tooltip();
		}).fail(function (xhr, status, error) {
			Aurora.message([{text: error, type: 'error'}], '#system-message-container', {replace: true});
		});
	});
</script>
<form action="<?php echo JRoute::_('index.php?option=com_kinoarhiv');?>" id="adminForm" method="post" name="adminForm" autocomplete="off">
	<div class="row-fluid">
		<div class="span12">
		<?php echo JHtml::_('bootstrap.startTabSet', 'settings', array('active' => 'page0')); ?>
			<?php echo JHtml::_('bootstrap.addTab', 'settings', 'page0', JText::_('COM_KA_SETTINGS_TAB')); ?>

			<div class="row-fluid">
				<div class="span6">
					<fieldset class="form-horizontal">
						<legend><?php echo JText::_('COM_KA_SETTINGS_GLOBAL_LABEL'); ?></legend>
						<?php echo $this->form->renderFieldset('global'); ?>
					</fieldset>
				</div>
				<div class="span6">
					<fieldset class="form-horizontal">
						<legend><?php echo JText::_('COM_KA_SETTINGS_META_LABEL'); ?></legend>
						<?php echo $this->form->renderFieldset('metadata'); ?>
					</fieldset>
				</div>
			</div>
			<div class="row-fluid">
				<div class="span12">
					<fieldset class="form-horizontal paths">
						<legend><?php echo JText::_('COM_KA_PATHS_LABEL'); ?></legend>
						<?php echo $this->form->renderFieldset('paths'); ?>
					</fieldset>
				</div>
			</div>

			<?php echo JHtml::_('bootstrap.endTab'); ?>
			<?php echo JHtml::_('bootstrap.addTab', 'settings', 'page1', JText::_('COM_KA_UPLOAD_DOWNLOAD_TAB')); ?>

			<div class="row-fluid">
				<div class="span6">
					<fieldset class="form-horizontal">
						<legend><?php echo JText::_('COM_KA_SETTINGS_GALLERY_LABEL'); ?></legend>
						<?php echo $this->form->renderFieldset('gallery'); ?>
					</fieldset>
				</div>
				<div class="span6">
					<fieldset class="form-horizontal">
						<legend><?php echo JText::_('COM_KA_SETTINGS_UPLOAD_DOWNLOAD_LABEL'); ?></legend>
						<?php echo $this->form->renderFieldset('content_dl'); ?>
					</fieldset>
				</div>
			</div>

			<?php echo JHtml::_('bootstrap.endTab'); ?>
			<?php echo JHtml::_('bootstrap.addTab', 'settings', 'page2', JText::_('COM_KA_MUSIC_TAB')); ?>

			<div class="row-fluid">
				<div class="span6">
					<fieldset class="form-horizontal">
						<legend><?php echo JText::_('COM_KA_FIELD_MUSIC_GLOBAL_SPACER'); ?></legend>
						<?php echo $this->form->renderFieldset('music_global'); ?>
					</fieldset>
				</div>
				<div class="span6">
					<fieldset class="form-horizontal">
						<legend><?php echo JText::_('COM_KA_FIELD_MUSIC_COVERS_SPACER'); ?></legend>
						<?php echo $this->form->renderFieldset('music_arts'); ?>
					</fieldset>
				</div>
			</div>

			<?php echo JHtml::_('bootstrap.endTab'); ?>
			<?php echo JHtml::_('bootstrap.addTab', 'settings', 'page3', JText::_('COM_KA_APPEARANCE_TAB')); ?>

			<div class="row-fluid">
				<div class="span6">
					<fieldset class="form-horizontal">
						<legend><?php echo JText::_('COM_KA_SETTINGS_AP_GLOBAL_LABEL'); ?></legend>
						<?php echo $this->form->renderFieldset('ap_global'); ?>
					</fieldset>
					<fieldset class="form-horizontal">
						<legend><?php echo JText::_('COM_KA_SETTINGS_AP_NAVGLOBAL_LABEL'); ?></legend>
						<?php echo $this->form->renderFieldset('ap_nav'); ?>
					</fieldset>
				</div>
				<div class="span6">
					<fieldset class="form-horizontal">
						<legend><?php echo JText::_('COM_KA_SETTINGS_AP_ITEM_LABEL'); ?></legend>
						<?php echo $this->form->renderFieldset('ap_item'); ?>
					</fieldset>
					<fieldset class="form-horizontal">
						<legend><?php echo JText::_('COM_KA_SETTINGS_AP_ITEM_PLAYER'); ?></legend>
						<?php echo $this->form->renderFieldset('ap_item_player'); ?>
					</fieldset>
				</div>
			</div>
			<div class="row-fluid">
				<div class="span12">
					<div class="span6">
						<fieldset class="form-horizontal">
							<legend><?php echo JText::_('COM_KA_SETTINGS_AP_RATE_LABEL'); ?></legend>
							<?php echo $this->form->renderFieldset('ap_rate'); ?>
						</fieldset>
					</div>
					<div class="span6">
						<fieldset class="form-horizontal">
							<legend>&nbsp;</legend>
							<?php echo $this->form->renderFieldset('ap_rate_img'); ?>
						</fieldset>
					</div>
				</div>
			</div>
			<div class="row-fluid">
				<div class="span12">
					<?php echo $this->loadTemplate('ap_alphabet'); ?>
				</div>
			</div>

			<?php echo JHtml::_('bootstrap.endTab'); ?>
			<?php echo JHtml::_('bootstrap.addTab', 'settings', 'page4', JText::_('COM_KA_REVIEWS_TAB')); ?>

			<div class="row-fluid">
				<div class="span6">
					<fieldset class="form-horizontal">
						<legend><?php echo JText::_('COM_KA_SETTINGS_TAB'); ?></legend>
						<?php echo $this->form->renderFieldset('reviews'); ?>
					</fieldset>
				</div>
				<div class="span6">
					<fieldset class="form-horizontal">
						<legend><?php echo JText::_('COM_KA_REVIEWS_SETTINGS_SAVE_LABEL'); ?></legend>
						<?php echo $this->form->renderFieldset('reviews_save'); ?>
					</fieldset>
				</div>
			</div>

			<?php echo JHtml::_('bootstrap.endTab'); ?>
			<?php echo JHtml::_('bootstrap.addTab', 'settings', 'page5', JText::_('COM_KA_SEARCH_TAB')); ?>

			<div class="row-fluid">
				<div class="span6">
					<fieldset class="form-horizontal">
						<legend><?php echo JText::_('COM_KA_SEARCH_SETTINGS_MOVIES'); ?></legend>
						<?php echo $this->form->renderFieldset('search_movies'); ?>
					</fieldset>
				</div>
				<div class="span6">
					<fieldset class="form-horizontal">
						<legend><?php echo JText::_('COM_KA_SEARCH_SETTINGS_NAMES'); ?></legend>
						<?php echo $this->form->renderFieldset('search_names'); ?>
					</fieldset>
					<fieldset class="form-horizontal">
						<legend><?php echo JText::_('COM_KA_SEARCH_SETTINGS_MUSIC'); ?></legend>
						<?php echo $this->form->renderFieldset('search_albums'); ?>
					</fieldset>
				</div>
			</div>

			<?php echo JHtml::_('bootstrap.endTab'); ?>
			<?php if ($this->userIsSuperAdmin): ?>
				<?php echo JHtml::_('bootstrap.addTab', 'settings', 'page6', JText::_('COM_KA_PERMISSIONS_LABEL')); ?>

				<div class="row-fluid">
					<div class="span12">
						<fieldset class="form-horizontal">
							<div class="control-group">
								<div class="controls" style="margin-left: 0 !important;"><?php echo $this->form->getInput('rules'); ?></div>
							</div>
						</fieldset>
					</div>
				</div>

				<?php echo JHtml::_('bootstrap.endTab'); ?>
			<?php endif; ?>
		<?php echo JHtml::_('bootstrap.endTabSet'); ?>

			<input type="hidden" name="task" value="" />
			<input type="hidden" name="return" value="<?php echo $this->return; ?>" />
			<?php echo JHtml::_('form.token'); ?>
		</div>
	</div>
</form>

<!-- Upload config template -->
<div class="modal hide fade" id="collapseModal">
	<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal">&#215;</button>
		<h3><?php echo JText::_('COM_KA_SETTINGS_BUTTON_RESTORECONFIG'); ?></h3>
	</div>
	<div class="modal-body modal-upload">
		<div class="container-fluid">
			<div class="row-fluid">
				<form method="post" enctype="multipart/form-data" id="adminRestoreConfig">
					<fieldset class="form-horizontal">
						<div class="control-group span6">
							<div class="control-label"><label id="form_upload_config-lbl" class=""
								 for="form_upload_config"><?php echo JText::_('COM_KA_SETTINGS_BUTTON_RESTORECONFIG_FILE'); ?></label></div>
							<div class="controls">
								<input id="form_upload_config" type="file" accept=".json" value=""
									   name="form_upload_config" required aria-required="true" />
							</div>
						</div>
						<input type="hidden" name="controller" value="settings" />
						<input type="hidden" name="task" value="settings.restoreConfig" />
						<?php echo JHtml::_('form.token'); ?>
					</fieldset>
				</form>
			</div>
		</div>
	</div>
	<div class="modal-footer">
		<button class="btn btn-primary" type="submit" onclick="Joomla.submitbutton('settings.restoreConfig');">
			<?php echo JText::_('JTOOLBAR_UNTRASH'); ?>
		</button>
		<button class="btn" type="button" onclick="document.getElementById('form_upload_config').value='';" data-dismiss="modal">
			<?php echo JText::_('JCANCEL'); ?>
		</button>
	</div>
</div>
