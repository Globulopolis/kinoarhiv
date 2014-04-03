<?php defined('_JEXEC') or die;
$input = JFactory::getApplication()->input;
$award_id = $input->get('award_id', 0, 'int');
?>
<script type="text/javascript" src="<?php echo JURI::base(); ?>components/com_kinoarhiv/assets/js/jquery.ui.tooltip.min.js"></script>
<script type="text/javascript" src="<?php echo JURI::base(); ?>components/com_kinoarhiv/assets/js/utils.js"></script>
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

		$('#form_award_id').select2({
			placeholder: '<?php echo JText::_('COM_KA_SEARCH_AJAX'); ?>',
			quietMillis: 100,
			minimumInputLength: 1,
			maximumSelectionSize: 1,
			multiple: true,
			<?php if ($award_id != 0): ?>
			initSelection: function(element, callback){
				var id = $(element).val();
				if (id !== "") {
					$.ajax('index.php?option=com_kinoarhiv&task=ajaxData&element=awards&format=json', {
						data: {
							id: id
						}
					}).done(function(data) { callback(data); });
				}
			},
			<?php endif; ?>
			ajax: {
				cache: true,
				url: 'index.php?option=com_kinoarhiv&task=ajaxData&element=awards&format=json',
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
	});
</script>
<div class="row-fluid">
	<!-- At this first hidden input we will remove autofocus -->
	<input type="hidden" autofocus="autofocus" />
	<div class="span12 rel-form_award">
		<fieldset class="form-horizontal">
			<legend><?php echo JText::_('COM_KA_MOVIES_AWARDS_LAYOUT_ADD_FIELD_TITLE'); ?></legend>
			<div class="group">
				<div class="control-group">
					<div class="control-label">
						<label id="form_award_id-lbl" class="hasTip" for="form_award_id"><?php echo JText::_('COM_KA_MOVIES_AWARDS_LAYOUT_ADD_FIELD_TITLE'); ?> <span class="star">*</span></label>
					</div>
					<div class="controls">
						<?php echo $this->form->getInput('award_id'); ?>&nbsp;
						<a class="btn btn-small quick-add hasTip" id="form_award" href="#" title="::<?php echo JText::_('COM_KA_AW_LAYOUT_QUICK_ADD_DESC'); ?>"><i class="icon-new"> </i> <?php echo JText::_('JTOOLBAR_NEW'); ?></a>
					</div>
				</div>
				<div class="control-group">
					<div class="control-label"><?php echo $this->form->getLabel('aw_year'); ?></div>
					<div class="controls"><?php echo $this->form->getInput('aw_year'); ?></div>
				</div>
				<div class="control-group">
					<div class="control-label"><?php echo $this->form->getLabel('aw_desc'); ?></div>
					<div class="controls"><?php echo $this->form->getInput('aw_desc'); ?></div>
				</div>
				<div class="control-group">
					<div class="control-label"><?php echo $this->form->getLabel('rel_aw_id'); ?></div>
					<div class="controls"><?php echo $this->form->getInput('rel_aw_id'); ?></div>
				</div>
			</div>
		</fieldset>
		<div class="placeholder"></div>
	</div>
	<div class="span12 form_award" style="display: none;">
		<fieldset class="form-horizontal">
			<legend><?php echo JText::_('COM_KA_MOVIES_AW_LAYOUT_ADD_TITLE'); ?></legend>
			<div class="control-group">
				<div class="control-label"><?php echo $this->form->getLabel('a_title'); ?></div>
				<div class="controls"><?php echo $this->form->getInput('a_title'); ?></div>
			</div>
			<div class="control-group">
				<div class="control-label"><?php echo $this->form->getLabel('a_desc'); ?></div>
				<div class="controls"><?php echo $this->form->getInput('a_desc'); ?></div>
			</div>
			<div class="control-group">
				<div class="control-label"><?php echo $this->form->getLabel('a_state'); ?></div>
				<div class="controls"><?php echo $this->form->getInput('a_state'); ?></div>
			</div>
			<div class="control-group">
				<div class="control-label"><?php echo $this->form->getLabel('a_language'); ?></div>
				<div class="controls"><?php echo $this->form->getInput('a_language'); ?></div>
			</div>
			<div class="control-group">
				<button id="form_award_apply"><?php echo JText::_('JTOOLBAR_APPLY'); ?></button>
				<button id="form_award_cancel"><?php echo JText::_('JTOOLBAR_CANCEL'); ?></button>
			</div>
		</fieldset>
	</div>
</div>
