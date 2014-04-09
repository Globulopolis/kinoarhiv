<?php defined('_JEXEC') or die;
$input = JFactory::getApplication()->input;
$movie_id = $input->get('movie_id', 0, 'int');
$name_id = $input->get('name_id', 0, 'int');
?>
<script type="text/javascript" src="<?php echo JURI::base(); ?>components/com_kinoarhiv/assets/js/jquery.ui.tooltip.min.js"></script>
<script type="text/javascript" src="<?php echo JURI::base(); ?>components/com_kinoarhiv/assets/js/utils.js"></script>
<script type="text/javascript">
	jQuery(document).ready(function($){
		$('#form_career_apply, #form_career_cancel, #form_name_apply, #form_name_cancel').button();
		$('a.quick-add').click(function(e){
			e.preventDefault();

			$('.' + $(this).attr('id')).slideToggle();

			if ($(this).hasClass('name')) {
				$('.' + $(this).attr('id')).addClass('name');
			} else if ($(this).hasClass('dub')) {
				$('.' + $(this).attr('id')).addClass('dub');
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
			var _this = $(this), cmd = $(this).attr('id');

			if (cmd == 'form_career_apply') {
				if ($('#form_c_title').val() != '') {
					$.ajax({
						type: 'POST',
						url: 'index.php?option=com_kinoarhiv&controller=careers&task=save&quick=1&format=json',
						data: $('.form_career fieldset').serialize() + '&<?php echo JSession::getFormToken(); ?>=1'
					}).done(function(response){
						if (response.success) {
							$('#form_type').select2('data', response.data);
							_this.closest('fieldset').parent().slideToggle();
							$('.rel-form_name .group').slideToggle();
							$('#rel-add-apply').button('enable');
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
						url: 'index.php?option=com_kinoarhiv&controller=names&task=quickSave&format=json',
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
						} else {
							showMsg('.form_name .control-group:last', response.message);
						}
					}).fail(function(xhr, status, error){
						showMsg('.form_name .control-group:last', error);
					});
				}
			}
		});

		$('#form_type').select2({
			placeholder: '<?php echo JText::_('COM_KA_SEARCH_AJAX'); ?>',
			quietMillis: 100,
			minimumInputLength: 1,
			maximumSelectionSize: 1,
			multiple: true,
			<?php if ($movie_id != 0 && $name_id != 0): ?>
			initSelection: function(element, callback){
				var id = $(element).val();
				if (id !== "") {
					$.ajax('index.php?option=com_kinoarhiv&task=ajaxData&element=career&format=json', {
						data: {
							id: id
						}
					}).done(function(data) { callback(data); });
				}
			},
			<?php endif; ?>
			ajax: {
				cache: true,
				url: 'index.php?option=com_kinoarhiv&task=ajaxData&element=career&format=json',
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

		function formatNames(data) {
			var title = '';

			if (data.name != '') title += data.name;
			if (data.name != '' && data.latin_name != '') title += ' / ';
			if (data.latin_name != '') title += data.latin_name;

			return title;
		}

		$('#form_name_id').select2({
			placeholder: '<?php echo JText::_('COM_KA_SEARCH_AJAX'); ?>',
			quietMillis: 100,
			minimumInputLength: 1,
			maximumSelectionSize: 1,
			multiple: true,
			<?php if ($movie_id != 0 && $name_id != 0): ?>
			initSelection: function(element, callback){
				var id = $(element).val();
				if (id !== "") {
					$.ajax('index.php?option=com_kinoarhiv&task=ajaxData&element=names&format=json', {
						data: {
							id: id
						}
					}).done(function(data) { callback(data); });
				}
			},
			<?php endif; ?>
			ajax: {
				cache: true,
				url: 'index.php?option=com_kinoarhiv&task=ajaxData&element=names&format=json',
				data: function(term, page){
					return { term: term, showAll: 0 }
				},
				results: function(data, page){
					return { results: data };
				}
			},
			formatResult: formatNames,
			formatSelection: formatNames,
			escapeMarkup: function(m) { return m; }
		});

		$('#form_dub_id').select2({
			placeholder: '<?php echo JText::_('COM_KA_SEARCH_AJAX'); ?>',
			quietMillis: 100,
			minimumInputLength: 1,
			maximumSelectionSize: 1,
			multiple: true,
			<?php if ($movie_id != 0 && $name_id != 0): ?>
			initSelection: function(element, callback){
				var id = $(element).val();
				if (id !== "" && id != 0) {
					$.ajax('index.php?option=com_kinoarhiv&task=ajaxData&element=names&format=json', {
						data: {
							id: id
						}
					}).done(function(data) { callback(data); });
				}
			},
			<?php endif; ?>
			ajax: {
				cache: true,
				url: 'index.php?option=com_kinoarhiv&task=ajaxData&element=names&format=json',
				data: function(term, page){
					return { term: term, showAll: 0 }
				},
				results: function(data, page){
					return { results: data };
				}
			},
			formatResult: formatNames,
			formatSelection: formatNames,
			escapeMarkup: function(m) { return m; }
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
			<legend><?php echo JText::_('COM_KA_MOVIES_NAMES_LAYOUT_ADD_FIELD_NAME'); ?></legend>
			<div class="group">
				<div class="control-group">
					<div class="control-label">
						<label id="form_type-lbl" class="hasTip" for="form_type" title="<?php echo JText::_('COM_KA_MOVIES_NAMES_LAYOUT_ADD_FIELD_TYPE_DESC'); ?>"><?php echo JText::_('COM_KA_MOVIES_NAMES_LAYOUT_ADD_FIELD_TYPE'); ?> <span class="star">*</span></label>
					</div>
					<div class="controls">
						<?php echo $this->form->getInput('type'); ?>&nbsp;
						<a class="btn btn-small quick-add hasTip" id="form_career" href="#" title="::<?php echo JText::_('COM_KA_CAREER_LAYOUT_QUICK_ADD_DESC'); ?>"><i class="icon-new"> </i> <?php echo JText::_('JTOOLBAR_NEW'); ?></a>
					</div>
				</div>
				<div class="control-group">
					<div class="control-label">
						<label id="form_name_id-lbl" for="form_name_id"><?php echo JText::_('COM_KA_MOVIES_NAMES_LAYOUT_ADD_FIELD_NAME'); ?> <span class="star">*</span></label>
					</div>
					<div class="controls">
						<?php echo $this->form->getInput('name_id'); ?>&nbsp;
						<a class="btn btn-small quick-add name hasTip" id="form_name" href="#" title="::<?php echo JText::_('COM_KA_NAMES_LAYOUT_QUICK_ADD_DESC'); ?>"><i class="icon-new"> </i> <?php echo JText::_('JTOOLBAR_NEW'); ?></a>
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
						<a class="btn btn-small quick-add dub hasTip" id="form_name" href="#" title="::<?php echo JText::_('COM_KA_NAMES_LAYOUT_QUICK_ADD_DESC'); ?>"><i class="icon-new"> </i> <?php echo JText::_('JTOOLBAR_NEW'); ?></a>
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
			<div class="control-group">
				<div class="control-label"><?php echo $this->form->getLabel('c_title'); ?></div>
				<div class="controls"><?php echo $this->form->getInput('c_title'); ?></div>
			</div>
			<div class="control-group">
				<div class="control-label"><?php echo $this->form->getLabel('c_language'); ?></div>
				<div class="controls"><?php echo $this->form->getInput('c_language'); ?></div>
			</div>
			<div class="control-group">
				<div class="control-label"><?php echo $this->form->getLabel('c_ordering'); ?></div>
				<div class="controls"><?php echo $this->form->getInput('c_ordering'); ?></div>
			</div>
			<div class="control-group">
				<button id="form_career_apply"><?php echo JText::_('JTOOLBAR_APPLY'); ?></button>
				<button id="form_career_cancel"><?php echo JText::_('JTOOLBAR_CANCEL'); ?></button>
			</div>
		</fieldset>
	</div>
	<div class="span12 form_name" style="display: none;">
		<fieldset class="form-horizontal">
			<div class="control-group">
				<div class="control-label"><?php echo $this->form->getLabel('n_name'); ?></div>
				<div class="controls"><?php echo $this->form->getInput('n_name'); ?></div>
			</div>
			<div class="control-group">
				<div class="control-label"><?php echo $this->form->getLabel('n_latin_name'); ?></div>
				<div class="controls"><?php echo $this->form->getInput('n_latin_name'); ?></div>
			</div>
			<div class="control-group">
				<div class="control-label"><?php echo $this->form->getLabel('n_date_of_birth'); ?></div>
				<div class="controls"><?php echo $this->form->getInput('n_date_of_birth'); ?></div>
			</div>
			<div class="control-group">
				<div class="control-label"><?php echo $this->form->getLabel('n_language'); ?></div>
				<div class="controls"><?php echo $this->form->getInput('n_language'); ?></div>
			</div>
			<div class="control-group">
				<div class="control-label"><?php echo $this->form->getLabel('n_ordering'); ?></div>
				<div class="controls"><?php echo $this->form->getInput('n_ordering'); ?></div>
			</div>
			<div class="control-group">
				<button id="form_name_apply"><?php echo JText::_('JTOOLBAR_APPLY'); ?></button>
				<button id="form_name_cancel"><?php echo JText::_('JTOOLBAR_CANCEL'); ?></button>
			</div>
		</fieldset>
	</div>
</div>
