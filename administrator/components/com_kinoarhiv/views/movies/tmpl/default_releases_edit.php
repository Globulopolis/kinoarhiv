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

		function formatVendor(data) {
			var title = '';

			if (data.company_name != '') title += data.company_name;
			if (data.company_name != '' && data.company_name_intl != '') title += ' / ';
			if (data.company_name_intl != '') title += data.company_name_intl;

			return title;
		}

		$('#form_r_vendor_id').select2({
			placeholder: '<?php echo JText::_('COM_KA_SEARCH_AJAX'); ?>',
			quietMillis: 100,
			minimumInputLength: 1,
			maximumSelectionSize: 1,
			multiple: false,
			initSelection: function(element, callback){
				var id = $(element).val();

				if (!empty(id)) {
					$.ajax('index.php?option=com_kinoarhiv&task=ajaxData&element=vendors&format=json', {
						data: {
							id: id
						}
					}).done(function(data) { callback(data); });
				}
			},
			ajax: {
				cache: true,
				url: 'index.php?option=com_kinoarhiv&task=ajaxData&element=vendors&format=json',
				data: function(term, page){
					return { term: term, showAll: 0 }
				},
				results: function(data, page){
					return { results: data };
				}
			},
			formatResult: formatVendor,
			formatSelection: formatVendor,
			escapeMarkup: function(m) { return m; }
		});

		$('#form_r_country_id').select2({
			placeholder: '<?php echo JText::_('COM_KA_SEARCH_AJAX'); ?>',
			quietMillis: 100,
			minimumInputLength: 1,
			maximumSelectionSize: 1,
			multiple: false,
			initSelection: function(element, callback){
				var id = $(element).val();

				if (!empty(id)) {
					$.ajax('index.php?option=com_kinoarhiv&task=ajaxData&element=countries&format=json', {
						data: {
							id: id
						}
					}).done(function(data) { callback(data); });
				}
			},
			ajax: {
				cache: true,
				url: 'index.php?option=com_kinoarhiv&task=ajaxData&element=countries&format=json',
				data: function(term, page){
					return { term: term, showAll: 0 }
				},
				results: function(data, page){
					return { results: data };
				}
			},
			formatResult: function(data){
				return "<img class='flag-dd' src='<?php echo JURI::root(); ?>components/com_kinoarhiv/assets/themes/component/<?php echo $this->params->get('ka_theme'); ?>/images/icons/countries/" + data.code + ".png'/>" + data.title;
			},
			formatSelection: function(data, container){
				return "<img class='flag-dd' src='<?php echo JURI::root(); ?>components/com_kinoarhiv/assets/themes/component/<?php echo $this->params->get('ka_theme'); ?>/images/icons/countries/" + data.code + ".png'/>" + data.title;
			},
			escapeMarkup: function(m) { return m; }
		});

		$('#form_r_media_type').select2();
	});
</script>
<div class="row-fluid">
	<!-- At this first hidden input we will remove autofocus -->
	<input type="hidden" autofocus="autofocus" />
	<div class="span12 rel-form_release">
		<fieldset class="form-horizontal">
			<legend><?php echo JText::_('COM_KA_MOVIES_RELEASE_LAYOUT_ADD_TITLE'); ?></legend>
			<div class="group">
				<div class="control-group">
					<div class="control-label">
						<label id="form_r_vendor_id_id-lbl" class="hasTip" for="form_r_vendor_id"><?php echo JText::_('COM_KA_FIELD_PREMIERE_VENDOR'); ?> <span class="star">*</span></label>
					</div>
					<div class="controls">
						<?php echo $this->form->getInput('r_vendor_id'); ?>&nbsp;
						<a class="btn btn-small quick-add hasTip" id="form_vendor" href="#" title="::<?php echo JText::_('COM_KA_PREMIERE_LAYOUT_QUICK_ADD_VENDOR_DESC'); ?>"><i class="icon-new"> </i> <?php echo JText::_('JTOOLBAR_NEW'); ?></a>
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
					<div class="control-label"><?php echo $this->form->getLabel('r_media_type'); ?></div>
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
