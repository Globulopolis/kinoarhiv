<?php defined('_JEXEC') or die;
JHtml::_('behavior.keepalive');
?>
<script type="text/javascript">
	Joomla.submitbutton = function(task) {
		if (task == 'cancel') {
			document.location.href = 'index.php?option=com_kinoarhiv&view=relations&task=<?php echo $this->param; ?>';
			return;
		} else if (task == 'save' || task == 'apply' || task == 'save2new') {
			var state_required = true;

			jQuery('input.required').each(function(){
				var _this = jQuery(this);
				jQuery('#j-main-container').aurora.destroy({indexes:'all'});

				if (_this.val() == '') {
					state_required = false;
					_this.parent().prev('div').find('label').addClass('red-label');
					showMsg('#j-main-container', '<?php echo JText::_('COM_KA_REQUIRED'); ?>');
				} else {
					_this.parent().prev('div').find('label').removeClass('red-label');
				}
			});
			if (state_required) {
				jQuery.post('index.php?option=com_kinoarhiv&controller=relations&task='+task+'&format=json', jQuery('form').serialize(), function(response){
					if (response.success) {
						if (task == 'apply') {
							showMsg('#j-main-container', response.message);
							jQuery('input[name="control_id[0]"]').val(response.ids[0]);
							jQuery('input[name="control_id[1]"]').val(response.ids[1]);
						} else if (task == 'save') {
							document.location.href = 'index.php?option=com_kinoarhiv&view=relations&task=<?php echo $this->param; ?>';
						} else if (task == 'save2new') {
							document.location.href = 'index.php?option=com_kinoarhiv&controller=relations&task=add&param=<?php echo $this->param; ?>';
						}
					} else {
						showMsg('#j-main-container', response.message);
					}
				});
			}
			return;
		}
	}

	jQuery(document).ready(function($){
		$('.hasAutocomplete').each(function(){
			var datatype = $(this).data('ac-type'),
				allow_clear = $(this).data('allow-clear');

			$(this).select2({
				placeholder: '<?php echo JText::_('COM_KA_SEARCH_AJAX'); ?>',
				quietMillis: 200,
				allowClear: allow_clear ? true : false,
				minimumInputLength: 1,
				maximumSelectionSize: 1,
				ajax: {
					cache: true,
					url: 'index.php?option=com_kinoarhiv&task=ajaxData&element='+datatype+'&format=json',
					data: function(term, page){
						return {
							term: term,
							showAll: 0
						}
					},
					results: function(data, page){
						return {results: data};
					}
				},
				initSelection: function(element, callback){
					var id = $(element).val();

					if (id !== "") {
						$.ajax('index.php?option=com_kinoarhiv&task=ajaxData&element='+datatype+'&format=json', {
							data: {
								id: id
							}
						}).done(function(data){
							callback(data);
						});
					}
				},
				formatResult: function(data){
					if (datatype == 'countries') {
						return "<img class='flag-dd' src='<?php echo JURI::root(); ?>components/com_kinoarhiv/assets/themes/component/<?php echo $this->params->get('ka_theme'); ?>/images/icons/countries/" + data.code + ".png'/>" + data.title;
					} else if (datatype == 'genres' || datatype == 'careers') {
						return data.title;
					} else if (datatype == 'movies') {
						if (data.year == '0000') return data.title;
						return data.title+' ('+data.year+')';
					} else if (datatype == 'names') {
						title = '';
						if (data.name != '') title += data.name;
						if (data.name != '' && data.latin_name != '') title += ' / ';
						if (data.latin_name != '') title += data.latin_name;
						if (data.date_of_birth != '0000-00-00') title += ' ('+data.date_of_birth+')';
						return title;
					}
				},
				formatSelection: function(data){
					if (datatype == 'countries') {
						return "<img class='flag-dd' src='<?php echo JURI::root(); ?>components/com_kinoarhiv/assets/themes/component/<?php echo $this->params->get('ka_theme'); ?>/images/icons/countries/" + data.code + ".png'/>" + data.title;
					} else if (datatype == 'genres' || datatype == 'careers') {
						return data.title;
					} else if (datatype == 'movies') {
						if (data.year == '0000') return data.title;
						return data.title+' ('+data.year+')';
					} else if (datatype == 'names') {
						title = '';
						if (data.name != '') title += data.name;
						if (data.name != '' && data.latin_name != '') title += ' / ';
						if (data.latin_name != '') title += data.latin_name;
						if (data.date_of_birth != '0000-00-00') title += ' ('+data.date_of_birth+')';
						return title;
					}
				},
				escapeMarkup: function(m) { return m; }
			});
		});
		$('#formordering').select2({ minimumResultsForSearch: -1 });
	});
</script>
<form action="<?php echo JRoute::_('index.php?option=com_kinoarhiv'); ?>" method="post" name="adminForm" id="adminForm" autocomplete="off">
	<div id="j-main-container">
		<fieldset class="form-horizontal">
			<?php foreach ($this->form->getFieldset('relations_'.$this->param) as $field): ?>
			<div class="control-group">
				<div class="control-label"><?php echo $field->label; ?></div>
				<div class="controls"><?php echo $field->input; ?></div>
			</div>
			<?php endforeach; ?>
		</fieldset>
	</div>
	<input type="hidden" name="param" value="<?php echo $this->param; ?>" />
	<input type="hidden" name="new" value="<?php echo ($this->task == 'add') ? 1 : 0; ?>" />
	<!-- Control ids. 'Cause we need to know old id for update query. The decision on which id is responsible for what we receive in the model. These ids don't make sense when we simply creating a new item. -->
	<?php if ($this->param == 'countries'):
		$value1 = $this->form->getValue('country_id');
		$value2 = $this->form->getValue('movie_id');
	elseif ($this->param == 'genres'):
		$value1 = $this->form->getValue('country_id');
		$value2 = $this->form->getValue('movie_id');
	elseif ($this->param == 'careers'):
		$value1 = $this->form->getValue('career_id');
		$value2 = $this->form->getValue('name_id');
	endif; ?>
	<input type="hidden" name="control_id[0]" value="<?php echo $value1; ?>" />
	<input type="hidden" name="control_id[1]" value="<?php echo $value2; ?>" />
	<!-- end -->
	<?php echo JHtml::_('form.token'); ?>
</form>
