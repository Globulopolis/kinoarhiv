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

$input = JFactory::getApplication()->input;
$movie_id = $input->get('movie_id', 0, 'int');
$name_id = $input->get('name_id', 0, 'int');
?>
<script type="text/javascript" src="<?php echo JUri::base(); ?>components/com_kinoarhiv/assets/js/utils.js"></script>
<script type="text/javascript">
	jQuery(document).ready(function($){
		$('#form_career_apply, #form_career_cancel, #form_name_apply, #form_name_cancel').button();
		$('a.quick-add').click(function(e){
			e.preventDefault();

			var class_id = $('.' + $(this).attr('id'));

			class_id.slideToggle();

			if ($(this).hasClass('name')) {
				class_id.addClass('name');
			} else if ($(this).hasClass('dub')) {
				class_id.addClass('dub');
			}

			$('.rel-form_name .group').slideToggle();
			$('#rel-add-apply').button('disable');
		});
		$('#form_career_cancel, #form_name_cancel').click(function(e){
			e.preventDefault();

			$(this).closest('fieldset').parent().slideToggle().removeClass('name dub');
			$('.rel-form_name .group').slideToggle();
			$('#rel-add-apply').button('enable');
		});
		$('#form_career_apply, #form_name_apply').click(function(e){
			e.preventDefault();

			var _this = $(this),
				cmd = $(this).attr('id');

			if (cmd == 'form_career_apply') {
				if ($('#form_c_title').val() != '') {
					$.ajax({
						type: 'POST',
						url: 'index.php?option=com_kinoarhiv&controller=careers&task=save&alias=1&format=json',
						data: $('.form_career fieldset').serialize() + '&<?php echo JSession::getFormToken(); ?>=1'
					}).done(function(response){
						if (response.success) {
							$('#form_type').select2('data', response.data);
							_this.closest('fieldset').parent().slideToggle();
							$('.rel-form_name .group').slideToggle();
							$('#rel-add-apply').button('enable');

							$('#form_c_title').val('');
							$('#form_c_ordering').val('0');
						} else {
							showMsg('.form_career .control-group:last', response.message);
						}
					}).fail(function(xhr, status, error){
						showMsg('.form_career .control-group:last', error);
					});
				}
			} else if (cmd == 'form_name_apply') {
				if ($('#form_n_name').val() != '' || $('#form_n_latin_name').val() != '') {
					$.ajax({
						type: 'POST',
						url: 'index.php?option=com_kinoarhiv&controller=names&task=save&quick_save=1&format=json',
						data: $('.form_name fieldset').serialize() + '&<?php echo JSession::getFormToken(); ?>=1'
					}).done(function(response){
						if (response.success) {
							if (_this.closest('fieldset').parent().hasClass('name')) {
								$('#form_name_id').select2('data', response.data);
							} else if (_this.closest('fieldset').parent().hasClass('dub')) {
								$('#form_dub_id').select2('data', response.data);
							}

							_this.closest('fieldset').parent().slideToggle().removeClass('name dub');
							$('.rel-form_name .group').slideToggle();
							$('#rel-add-apply').button('enable');

							$('#form_n_name, #form_n_latin_name, #form_n_date_of_birth').val('');
							$('#form_n_ordering').val('0');
						} else {
							showMsg('.form_name .control-group:last', response.message);
						}
					}).fail(function(xhr, status, error){
						showMsg('.form_name .control-group:last', error);
					});
				}
			}
		});

		$('#form_is_directors').change(function(){
			if (this.value == 1) {
				$('#form_is_actors').val(0);
			}
		});
	});
</script>
<div class="row-fluid">
	<!-- At this first hidden input we will remove autofocus -->
	<input type="hidden" autofocus="autofocus" />
	<div class="span12 rel-form_name">
		<fieldset class="form-horizontal">
			<legend><?php if ($name_id == 0):
				echo JText::_('COM_KA_MOVIES_NAMES_LAYOUT_ADD_TITLE');
			else:
				echo JText::_('COM_KA_MOVIES_NAMES_LAYOUT_EDIT_TITLE');
			endif; ?>
			</legend>
			<div class="group">
				<div class="control-group">
					<div class="control-label">
						<label id="form_type-lbl" class="hasTooltip" for="form_type" title="<?php echo JText::_('COM_KA_MOVIES_NAMES_LAYOUT_ADD_FIELD_TYPE_DESC'); ?>"><?php echo JText::_('COM_KA_MOVIES_NAMES_LAYOUT_ADD_FIELD_TYPE'); ?> <span class="star">*</span></label>
					</div>
					<div class="controls">
						<?php echo $this->form->getInput('type'); ?>&nbsp;
						<a class="btn btn-small quick-add hasTooltip" id="form_career" href="#" title="::<?php echo JText::_('COM_KA_CAREER_LAYOUT_QUICK_ADD_DESC'); ?>"><i class="icon-new"> </i> <?php echo JText::_('JTOOLBAR_NEW'); ?></a>
					</div>
				</div>
				<div class="control-group">
					<div class="control-label">
						<label id="form_name_id-lbl" for="form_name_id"><?php echo JText::_('COM_KA_MOVIES_NAMES_LAYOUT_ADD_FIELD_NAME'); ?> <span class="star">*</span></label>
					</div>
					<div class="controls">
						<?php echo $this->form->getInput('name_id'); ?>&nbsp;
						<a class="btn btn-small quick-add name hasTooltip" id="form_name" href="#" title="::<?php echo JText::_('COM_KA_NAMES_LAYOUT_QUICK_ADD_DESC'); ?>"><i class="icon-new"> </i> <?php echo JText::_('JTOOLBAR_NEW'); ?></a>
					</div>
				</div>
				<div class="control-group">
					<div class="control-label"><?php echo $this->form->getLabel('is_directors'); ?></div>
					<div class="controls"><?php echo $this->form->getInput('is_directors'); ?></div>
				</div>
				<div class="control-group">
					<div class="control-label"><?php echo $this->form->getLabel('is_actors'); ?></div>
					<div class="controls"><?php echo $this->form->getInput('is_actors'); ?></div>
				</div>
				<div class="control-group">
					<div class="control-label"><?php echo $this->form->getLabel('voice_artists'); ?></div>
					<div class="controls"><?php echo $this->form->getInput('voice_artists'); ?></div>
				</div>
				<div class="control-group">
					<div class="control-label">
						<label id="form_dub_id-lbl" for="form_dub_id"><?php echo JText::_('COM_KA_MOVIES_NAMES_LAYOUT_ADD_FIELD_DUB'); ?></label>
					</div>
					<div class="controls">
						<?php echo $this->form->getInput('dub_id'); ?>&nbsp;
						<a class="btn btn-small quick-add dub hasTooltip" id="form_name" href="#" title="::<?php echo JText::_('COM_KA_NAMES_LAYOUT_QUICK_ADD_DESC'); ?>"><i class="icon-new"> </i> <?php echo JText::_('JTOOLBAR_NEW'); ?></a>
					</div>
				</div>
				<div class="control-group">
					<div class="control-label"><?php echo $this->form->getLabel('role'); ?></div>
					<div class="controls"><?php echo $this->form->getInput('role'); ?></div>
				</div>
				<div class="control-group">
					<div class="control-label"><?php echo $this->form->getLabel('r_ordering'); ?></div>
					<div class="controls"><?php echo $this->form->getInput('r_ordering'); ?></div>
				</div>
				<div class="control-group">
					<div class="control-label"><?php echo $this->form->getLabel('r_desc'); ?></div>
					<div class="controls"><?php echo $this->form->getInput('r_desc'); ?></div>
				</div>
			</div>
		</fieldset>
		<div class="placeholder"></div>
	</div>
	<div class="span12 form_career" style="display: none;">
		<fieldset class="form-horizontal">
			<legend><?php echo JText::_('COM_KA_MOVIES_NAMES_LAYOUT_ADD_CAREER_LEGEND'); ?></legend>
			<div class="group">
				<?php foreach($this->form->getFieldset('career_quick_add') as $field): ?>
				<div class="control-group">
					<div class="control-label"><?php echo $field->label; ?></div>
					<div class="controls"><?php echo $field->input; ?></div>
				</div>
				<?php endforeach; ?>
			</div>
			<div class="control-group">
				<button id="form_career_apply"><?php echo JText::_('JTOOLBAR_APPLY'); ?></button>
				<button id="form_career_cancel"><?php echo JText::_('JTOOLBAR_CANCEL'); ?></button>
			</div>
		</fieldset>
	</div>
	<div class="span12 form_name" style="display: none;">
		<fieldset class="form-horizontal">
			<div class="group">
				<?php foreach($this->form->getFieldset('name_quick_add') as $field): ?>
				<div class="control-group">
					<div class="control-label"><?php echo $field->label; ?></div>
					<div class="controls"><?php echo $field->input; ?></div>
				</div>
				<?php endforeach; ?>
			</div>
			<div class="control-group">
				<button id="form_name_apply"><?php echo JText::_('JTOOLBAR_APPLY'); ?></button>
				<button id="form_name_cancel"><?php echo JText::_('JTOOLBAR_CANCEL'); ?></button>
			</div>
		</fieldset>
	</div>
</div>
