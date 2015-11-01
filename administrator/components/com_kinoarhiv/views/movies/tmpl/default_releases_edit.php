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
<script type="text/javascript" src="<?php echo JURI::base(); ?>components/com_kinoarhiv/assets/js/utils.js"></script>
<script type="text/javascript">
	jQuery(document).ready(function($){
		$('.form_vendor button').button();
		$('a.quick-add').click(function(e){
			e.preventDefault();

			$('.form_vendor').slideToggle();

			$('.rel-form_release .group').slideToggle();
			$('#rel-add-apply').button('disable');
		});
		$('#form_vendor_cancel').click(function(e){
			e.preventDefault();

			$('.form_vendor').slideToggle();
			$('.rel-form_release .group').slideToggle();
			$('#rel-add-apply').button('enable');
		});
		$('#form_vendor_apply').click(function(e){
			e.preventDefault();
			var _this = $(this);

			if ($('#form_v_title').val() != '') {
				$.ajax({
					type: 'POST',
					url: 'index.php?option=com_kinoarhiv&controller=vendors&task=save&alias=1&format=json',
					data: $('.form_vendor fieldset').serialize() + '&<?php echo JSession::getFormToken(); ?>=1'
				}).done(function(response){
					if (response.success) {
						$('#form_r_vendor_id').select2('data', response.data);
						_this.closest('fieldset').parent().slideToggle();
						$('.rel-form_release .group').slideToggle();
						$('#rel-add-apply').button('enable');
					} else {
						showMsg('.form_vendor .control-group:last', response.message);
					}
				}).fail(function(xhr, status, error){
					showMsg('.form_vendor .control-group:last', error);
				});
			} else {
				showMsg('.form_vendor .control-group:last', '<?php echo JText::_('COM_KA_REQUIRED'); ?>');
			}
		});
	});
</script>
<div class="row-fluid">
	<!-- At this first hidden input we will remove autofocus -->
	<input type="hidden" autofocus="autofocus" />
	<div class="span12 rel-form_release">
		<fieldset class="form-horizontal">
			<legend><?php if (JFactory::getApplication()->input->get('release_id', 0, 'int') == 0):
				echo JText::_('COM_KA_MOVIES_RELEASE_LAYOUT_ADD_TITLE');
			else:
				echo JText::_('COM_KA_MOVIES_RELEASE_LAYOUT_EDIT_TITLE');
			endif; ?>
			</legend>
			<div class="group">
				<div class="control-group">
					<div class="control-label">
						<label id="form_r_vendor_id-lbl" class="hasTooltip" for="form_r_vendor_id"><?php echo JText::_('COM_KA_FIELD_PREMIERE_VENDOR'); ?> <span class="star">*</span></label>
					</div>
					<div class="controls">
						<?php echo $this->form->getInput('r_vendor_id'); ?>&nbsp;
						<a class="btn btn-small quick-add hasTooltip" id="form_vendor" href="#" title="::<?php echo JText::_('COM_KA_PREMIERE_LAYOUT_QUICK_ADD_VENDOR_DESC'); ?>"><i class="icon-new"> </i> <?php echo JText::_('JTOOLBAR_NEW'); ?></a>
					</div>
				</div>
				<div class="control-group">
					<div class="control-label"><?php echo $this->form->getLabel('r_country_id'); ?></div>
					<div class="controls"><?php echo $this->form->getInput('r_country_id'); ?></div>
				</div>
				<div class="control-group">
					<div class="control-label"><?php echo $this->form->getLabel('r_release_date'); ?></div>
					<div class="controls"><?php echo $this->form->getInput('r_release_date'); ?></div>
				</div>
				<div class="control-group">
					<div class="control-label">
						<label id="form_r_media_type-lbl" class="hasTooltip" for="form_r_media_type" title="<?php echo JText::_('COM_KA_FIELD_RELEASES_MEDIATYPE_DESC'); ?>"><?php echo JText::_('COM_KA_RELEASES_MEDIATYPE_TITLE'); ?></label>
					</div>
					<div class="controls"><?php echo $this->form->getInput('r_media_type'); ?></div>
				</div>
				<div class="control-group">
					<div class="control-label"><?php echo $this->form->getLabel('r_desc'); ?></div>
					<div class="controls"><?php echo $this->form->getInput('r_desc'); ?></div>
				</div>
				<div class="control-group">
					<div class="control-label"><?php echo $this->form->getLabel('r_language'); ?></div>
					<div class="controls"><?php echo $this->form->getInput('r_language'); ?></div>
				</div>
				<div class="control-group">
					<div class="control-label"><?php echo $this->form->getLabel('r_ordering'); ?></div>
					<div class="controls"><?php echo $this->form->getInput('r_ordering'); ?></div>
				</div>
			</div>
		</fieldset>
		<div class="placeholder"></div>
	</div>
	<div class="span12 form_vendor" style="display: none;">
		<fieldset class="form-horizontal">
			<legend><?php echo JText::_('COM_KA_PREMIERE_LAYOUT_QUICK_ADD_VENDOR_TITLE'); ?></legend>
			<div class="group">
				<?php foreach($this->form->getFieldset('vendor_quick_add') as $field): ?>
				<div class="control-group">
					<div class="control-label"><?php echo $field->label; ?></div>
					<div class="controls"><?php echo $field->input; ?></div>
				</div>
				<?php endforeach; ?>
			</div>
			<div class="control-group">
				<button id="form_vendor_apply"><?php echo JText::_('JTOOLBAR_APPLY'); ?></button>
				<button id="form_vendor_cancel"><?php echo JText::_('JTOOLBAR_CANCEL'); ?></button>
			</div>
		</fieldset>
	</div>
</div>
