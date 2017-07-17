<?php
/**
 * @package     Kinoarhiv.Administrator
 * @subpackage  com_kinoarhiv
 *  
 * @copyright   Copyright (C) 2017 Libra.ms. All rights reserved.
 * @license     GNU General Public License version 2 or later
 * @url         http://киноархив.com
 */

defined('_JEXEC') or die;

JHtml::_('behavior.tabstate');
JHtml::_('bootstrap.tooltip');
JHtml::_('bootstrap.modal', 'collapseModal');
?>
<script type="text/javascript">
	Joomla.submitbutton = function(task) {
		jQuery(document).ready(function($){
			var form = $('#adminForm');
			if (task !== 'settings.cancel' && task !== 'settings.save' && task !== 'settings.saveConfig'
				&& task !== 'restoreConfigLayout' && task !== 'settings.restoreConfig')
			{
				$.post(form.attr('action'), form.serialize() + '&task=' + task + '&format=json', function(response){
					showMsg('#system-message-container', response.message);
					$(document).scrollTop(0);
				}).fail(function(xhr, status, error){
					showMsg('#system-message-container', error);
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
		$('#jform_slider_max_item').spinner({
			spin: function(event, ui){
				if (ui.value > 100) {
					$(this).spinner('value', 10);
					return false;
				} else if (ui.value < 10) {
					$(this).spinner('value', 100);
					return false;
				}
			}
		});

		// For movie alphabet
		var cloned_m_rows = $('.movie-ab .letters-lang').length;
		$('.cmd-abm-new-row').click(function(e){
			e.preventDefault();
			var row = $(this).closest('.row-fluid');
			var cloned_row = row.clone(true);

			row.after(cloned_row);
			$('.letters-lang', cloned_row).val('');
			$('.letters', cloned_row).val('');
			cloned_m_rows++;
		});
		$('.cmd-abm-remove-row').click(function(e){
			e.preventDefault();

			if (cloned_m_rows > 1) {
				$(this).closest('.row-fluid').remove();
				cloned_m_rows--;
			}
		});
		// End

		// For persons(names) alphabet
		var cloned_n_rows = $('.name-ab .letters-lang').length;
		$('.cmd-abn-new-row').click(function(e){
			e.preventDefault();
			var row = $(this).closest('.row-fluid');
			var cloned_row = row.clone(true);

			row.after(cloned_row);
			$('.letters-lang', cloned_row).val('');
			$('.letters', cloned_row).val('');
			cloned_n_rows++;
		});
		$('.cmd-abn-remove-row').click(function(e){
			e.preventDefault();

			if (cloned_n_rows > 1) {
				$(this).closest('.row-fluid').remove();
				cloned_n_rows--;
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
			showMsg('#system-message-container', error);
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
						<?php foreach ($this->form->getFieldset('global') as $field): ?>
							<div class="control-group">
								<div class="control-label"><?php echo $field->label; ?></div>
								<div class="controls"><?php echo $field->input; ?></div>
							</div>
						<?php endforeach; ?>
					</fieldset>
				</div>
				<div class="span6">
					<fieldset class="form-horizontal">
						<legend><?php echo JText::_('COM_KA_SETTINGS_META_LABEL'); ?></legend>
						<?php foreach ($this->form->getFieldset('metadata') as $field): ?>
							<div class="control-group">
								<div class="control-label"><?php echo $field->label; ?></div>
								<div class="controls"><?php echo $field->input; ?></div>
							</div>
						<?php endforeach; ?>
					</fieldset>
				</div>
			</div>
			<div class="row-fluid">
				<div class="span12">
					<fieldset class="form-horizontal paths">
						<legend><?php echo JText::_('COM_KA_PATHS_LABEL'); ?></legend>
						<?php foreach ($this->form->getFieldset('paths') as $field): ?>
							<div class="control-group">
								<div class="control-label"><?php echo $field->label; ?></div>
								<div class="controls settings-paths"><?php echo $field->input; ?></div>
							</div>
						<?php endforeach; ?>
					</fieldset>
				</div>
			</div>

			<?php echo JHtml::_('bootstrap.endTab'); ?>
			<?php echo JHtml::_('bootstrap.addTab', 'settings', 'page1', JText::_('COM_KA_UPLOAD_DOWNLOAD_TAB')); ?>

			<div class="row-fluid">
				<div class="span6">
					<fieldset class="form-horizontal">
						<legend><?php echo JText::_('COM_KA_SETTINGS_GALLERY_LABEL'); ?></legend>
						<?php foreach ($this->form->getFieldset('gallery') as $field): ?>
							<div class="control-group">
								<div class="control-label"><?php echo $field->label; ?></div>
								<div class="controls"><?php echo $field->input; ?></div>
							</div>
						<?php endforeach; ?>
					</fieldset>
				</div>
				<div class="span6">
					<fieldset class="form-horizontal">
						<legend><?php echo JText::_('COM_KA_SETTINGS_UPLOAD_DOWNLOAD_LABEL'); ?></legend>
						<?php foreach ($this->form->getFieldset('content_dl') as $field): ?>
							<div class="control-group">
								<div class="control-label"><?php echo $field->label; ?></div>
								<div class="controls"><?php echo $field->input; ?></div>
							</div>
						<?php endforeach; ?>
					</fieldset>
				</div>
			</div>

			<?php echo JHtml::_('bootstrap.endTab'); ?>
			<?php echo JHtml::_('bootstrap.addTab', 'settings', 'page2', JText::_('COM_KA_MUSIC_TAB')); ?>

			<div class="row-fluid">
				<div class="span6">
					<?php echo $this->loadTemplate('music_global'); ?>
				</div>
				<div class="span6">
					<?php echo $this->loadTemplate('music_covers'); ?>
				</div>
			</div>

			<?php echo JHtml::_('bootstrap.endTab'); ?>
			<?php echo JHtml::_('bootstrap.addTab', 'settings', 'page3', JText::_('COM_KA_APPEARANCE_TAB')); ?>

			<div class="row-fluid">
				<div class="span6">
					<fieldset class="form-horizontal">
						<legend><?php echo JText::_('COM_KA_SETTINGS_AP_GLOBAL_LABEL'); ?></legend>
						<?php foreach ($this->form->getFieldset('ap_global') as $field): ?>
							<div class="control-group">
								<div class="control-label"><?php echo $field->label; ?></div>
								<div class="controls"><?php echo $field->input; ?></div>
							</div>
						<?php endforeach; ?>
					</fieldset>
					<fieldset class="form-horizontal">
						<legend><?php echo JText::_('COM_KA_SETTINGS_AP_NAVGLOBAL_LABEL'); ?></legend>
						<?php foreach ($this->form->getFieldset('ap_nav') as $field): ?>
							<div class="control-group">
								<div class="control-label"><?php echo $field->label; ?></div>
								<div class="controls"><?php echo $field->input; ?></div>
							</div>
						<?php endforeach; ?>
					</fieldset>
				</div>
				<div class="span6">
					<?php echo $this->loadTemplate('ap_item'); ?>
				</div>
			</div>
			<div class="row-fluid">
				<div class="span12">
					<div class="span6">
						<fieldset class="form-horizontal">
							<legend><?php echo JText::_('COM_KA_SETTINGS_AP_RATE_LABEL'); ?></legend>
							<?php foreach ($this->form->getFieldset('ap_rate') as $field): ?>
								<div class="control-group">
									<div class="control-label"><?php echo $field->label; ?></div>
									<div class="controls"><?php echo $field->input; ?></div>
								</div>
							<?php endforeach; ?>
						</fieldset>
					</div>
					<div class="span6">
						<fieldset class="form-horizontal">
							<legend>&nbsp;</legend>
							<?php foreach ($this->form->getFieldset('ap_rate_img') as $field): ?>
								<div class="control-group">
									<div class="control-label"><?php echo $field->label; ?></div>
									<div class="controls"><?php echo $field->input; ?></div>
								</div>
							<?php endforeach; ?>
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
						<?php foreach ($this->form->getFieldset('reviews') as $field): ?>
							<div class="control-group">
								<div class="control-label"><?php echo $field->label; ?></div>
								<div class="controls"><?php echo $field->input; ?></div>
							</div>
						<?php endforeach; ?>
					</fieldset>
				</div>
				<div class="span6">
					<fieldset class="form-horizontal">
						<legend><?php echo JText::_('COM_KA_REVIEWS_SETTINGS_SAVE_LABEL'); ?></legend>
						<?php foreach ($this->form->getFieldset('reviews_save') as $field): ?>
							<div class="control-group">
								<div class="control-label"><?php echo $field->label; ?></div>
								<div class="controls"><?php echo $field->input; ?></div>
							</div>
						<?php endforeach; ?>
					</fieldset>
				</div>
			</div>

			<?php echo JHtml::_('bootstrap.endTab'); ?>
			<?php echo JHtml::_('bootstrap.addTab', 'settings', 'page5', JText::_('COM_KA_SEARCH_TAB')); ?>

			<div class="row-fluid">
				<div class="span6">
					<fieldset class="form-horizontal">
						<legend><?php echo JText::_('COM_KA_SEARCH_SETTINGS_MOVIES'); ?></legend>
						<?php foreach ($this->form->getFieldset('search_movies') as $field): ?>
							<div class="control-group">
								<div class="control-label"><?php echo $field->label; ?></div>
								<div class="controls"><?php echo $field->input; ?></div>
							</div>
						<?php endforeach; ?>
					</fieldset>
				</div>
				<div class="span6">
					<fieldset class="form-horizontal">
						<legend><?php echo JText::_('COM_KA_SEARCH_SETTINGS_NAMES'); ?></legend>
						<?php foreach ($this->form->getFieldset('search_names') as $field): ?>
							<div class="control-group">
								<div class="control-label"><?php echo $field->label; ?></div>
								<div class="controls"><?php echo $field->input; ?></div>
							</div>
						<?php endforeach; ?>
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
							<div class="control-label"><label id="form_upload_config-lbl" class="" for="form_upload_config"><?php echo JText::_('COM_KA_SETTINGS_BUTTON_RESTORECONFIG_FILE'); ?></label></div>
							<div class="controls">
								<input id="form_upload_config" type="file" accept=".json" value="" name="form_upload_config" required aria-required="true" />
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
