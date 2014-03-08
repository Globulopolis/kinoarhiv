<?php defined('_JEXEC') or die;
$input = JFactory::getApplication()->input;
$award_id = $input->get('award_id', 0, 'int');
?>
<script type="text/javascript" src="<?php echo JURI::base(); ?>components/com_kinoarhiv/assets/js/jquery.ui.tooltip.min.js"></script>
<script type="text/javascript">
	jQuery(document).ready(function($){
		$('.hasTip, .hasTooltip').tooltip({
			show: null,
			position: {
				my: 'left top',
				at: 'left bottom'
			},
			open: function(event, ui){
				ui.tooltip.animate({ top: ui.tooltip.position().top + 10 }, 'fast');
			},
			content: function(){
				var parts = $(this).attr('title').split('::', 2),
					title = '';

				if (parts.length == 2) {
					if (parts[0] != '') {
						title += '<div style="text-align: center; border-bottom: 1px solid #EEEEEE;">' + parts[0] + '</div>' + parts[1];
					} else {
						title += parts[1];
					}
				} else {
					title += $(this).attr('title');
				}

				return title;
			}
		});

		$('.form_award button').button();
		$('a.quick-add').click(function(e){
			e.preventDefault();

			$('.form_award').slideToggle();

			$('.rel-form_award .group').slideToggle();
			$('#rel-add-apply').button('disable');
		});
		$('#form_award_cancel').click(function(e){
			e.preventDefault();

			$('.form_award').slideToggle();
			$('.rel-form_award .group').slideToggle();
			$('#rel-add-apply').button('enable');
		});
		$('#form_award_apply').click(function(e){
			e.preventDefault();
			var _this = $(this);

			if ($('#form_a_title').val() != '') {
				$.ajax({
					type: 'POST',
					url: 'index.php?option=com_kinoarhiv&controller=awards&task=quickSave&format=json',
					data: $('.form_award fieldset').serialize() + '&<?php echo JSession::getFormToken(); ?>=1'
				}).done(function(response){
					if (response.success) {
						$('#form_award_id').select2('data', response.data);
						_this.closest('fieldset').parent().slideToggle();
						$('.rel-form_award .group').slideToggle();
						$('#rel-add-apply').button('enable');
					} else {
						showMsg('.form_award .control-group:last', response.message);
					}
				}).fail(function(xhr, status, error){
					showMsg('.form_award .control-group:last', error);
				});
			} else {
				showMsg('.form_award .control-group:last', '<?php echo JText::_('COM_KA_REQUIRED'); ?>');
			}
		});

		function formatVendor(data) {
			var title = '';

			if (data.company_name != '') title += data.company_name;
			if (data.company_name != '' && data.company_name_intl != '') title += ' / ';
			if (data.company_name_intl != '') title += data.company_name_intl;

			return title;
		}

		$('#form_p_vendor_id').select2({
			placeholder: '<?php echo JText::_('COM_KA_SEARCH_AJAX'); ?>',
			quietMillis: 100,
			minimumInputLength: 1,
			maximumSelectionSize: 1,
			multiple: true,
			<?php if ($award_id != 0): ?>
			initSelection: function(element, callback){
				var id = $(element).val();
				if (id !== "") {
					$.ajax('index.php?option=com_kinoarhiv&task=ajaxData&element=vendors&format=json', {
						data: {
							id: id
						}
					}).done(function(data) { callback(data); });
				}
			},
			<?php endif; ?>
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

		$('#form_p_country_id').select2({
			placeholder: '<?php echo JText::_('COM_KA_SEARCH_AJAX'); ?>',
			quietMillis: 100,
			minimumInputLength: 1,
			maximumSelectionSize: 1,
			multiple: true,
			<?php if ($award_id != 0): ?>
			initSelection: function(element, callback){
				var id = $(element).val();
				if (id !== "") {
					$.ajax('index.php?option=com_kinoarhiv&task=ajaxData&element=countries&format=json', {
						data: {
							id: id
						}
					}).done(function(data) { callback(data); });
				}
			},
			<?php endif; ?>
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
				return data.title;
			},
			formatSelection: function(data, container){
				return data.title;
			},
			escapeMarkup: function(m) { return m; }
		});

		$('.hasDatetime').each(function(i, el){
			if ($(el).hasClass('time')) {
				$(el).timepicker({
					timeFormat: $(el).data('time-format')
				});
			} else if ($(el).hasClass('date')) {
				
			} else if ($(el).hasClass('datetime')) {
				$(el).datetimepicker({
					dateFormat: $(el).data('date-format'),
					timeFormat: $(el).data('time-format')
				});
			}
		});
	});
</script>
<div class="row-fluid">
	<!-- At this first hidden input we will remove autofocus -->
	<input type="hidden" autofocus="autofocus" />
	<div class="span12 rel-form_premiere">
		<fieldset class="form-horizontal">
			<div class="group">
				<div class="control-group">
					<div class="control-label">
						<label id="form_p_vendor_id-lbl" class="hasTooltip" for="form_p_vendor_id" title="<?php echo JText::_('COM_KA_FIELD_PREMIERE_VENDOR_DESC'); ?>"><?php echo JText::_('COM_KA_FIELD_PREMIERE_VENDOR'); ?> <span class="star">*</span></label>
					</div>
					<div class="controls"><?php echo $this->form->getInput('p_vendor_id'); ?></div>
				</div>
				<div class="control-group">
					<div class="control-label">
						<label id="form_p_country_id-lbl" for="form_p_country_id"><?php echo JText::_('COM_KA_FIELD_PREMIERE_COUNTRY_LABEL'); ?></label>
					</div>
					<div class="controls"><?php echo $this->form->getInput('p_country_id'); ?></div>
				</div>
				<div class="control-group">
					<div class="control-label"><?php echo $this->form->getLabel('p_premiere_date'); ?></div>
					<div class="controls"><?php echo $this->form->getInput('p_premiere_date'); ?></div>
				</div>
				<div class="control-group">
					<div class="control-label"><?php echo $this->form->getLabel('p_info'); ?></div>
					<div class="controls"><?php echo $this->form->getInput('p_info'); ?></div>
				</div>
				<div class="control-group">
					<div class="control-label"><?php echo $this->form->getLabel('p_ordering'); ?></div>
					<div class="controls"><?php echo $this->form->getInput('p_ordering'); ?></div>
				</div>
			</div>
		</fieldset>
		<div class="placeholder"></div>
	</div>
</div>
