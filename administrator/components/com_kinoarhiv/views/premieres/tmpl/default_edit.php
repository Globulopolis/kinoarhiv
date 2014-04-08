<?php defined('_JEXEC') or die;
JHtml::_('behavior.keepalive');
?>
<script type="text/javascript">
	Joomla.submitbutton = function(task) {
		if (task == 'apply' || task == 'save' || task == 'save2new') {
			if (jQuery('#form_movie_id').select2('val') == '' || jQuery('#form_vendor_id').select2('val') == '' || jQuery('#form_premiere_date').val() == '') {
				showMsg('#j-main-container', '<?php echo JText::_('COM_KA_REQUIRED'); ?>');
				return;
			}
		}
		Joomla.submitform(task);
	}

	jQuery(document).ready(function($){
		$('.hasTip, .hasTooltip, td[title]').tooltip({
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

		$('.hasAutocomplete').each(function(){
			var datatype = $(this).data('ac-type'),
				allow_clear = $(this).data('allow-clear');

			$(this).select2({
				placeholder: '<?php echo JText::_('COM_KA_SEARCH_AJAX'); ?>',
				allowClear: allow_clear ? true : false,
				quietMillis: 200,
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
						if (data.length < 1) {
							return '';
						} else {
							return "<img class='flag-dd' src='<?php echo JURI::root(); ?>components/com_kinoarhiv/assets/themes/component/<?php echo $this->params->get('ka_theme'); ?>/images/icons/countries/" + data.code + ".png'/>" + data.title;
						}
					} else if (datatype == 'movies') {
						if (data.year == '0000') return data.title;
						return data.title+' ('+data.year+')';
					} else if (datatype == 'vendors') {
						title = '';
						if (data.company_name != '') title += data.company_name;
						if (data.company_name != '' && data.company_name_intl != '') title += ' / ';
						if (data.company_name_intl != '') title += data.company_name_intl;

						return title;
					}
				},
				formatSelection: function(data){
					if (datatype == 'countries') {
						if (data.length < 1) {
							return '';
						} else {
							return "<img class='flag-dd' src='<?php echo JURI::root(); ?>components/com_kinoarhiv/assets/themes/component/<?php echo $this->params->get('ka_theme'); ?>/images/icons/countries/" + data.code + ".png'/>" + data.title;
						}
					} else if (datatype == 'movies') {
						if (data.year == '0000') return data.title;
						return data.title+' ('+data.year+')';
					} else if (datatype == 'vendors') {
						title = '';
						if (data.company_name != '') title += data.company_name;
						if (data.company_name != '' && data.company_name_intl != '') title += ' / ';
						if (data.company_name_intl != '') title += data.company_name_intl;

						return title;
					}
				},
				escapeMarkup: function(m) { return m; }
			});
		});
	});
</script>
<form action="<?php echo JRoute::_('index.php?option=com_kinoarhiv'); ?>" method="post" name="adminForm" id="adminForm" class="form-validate" autocomplete="off">
	<div id="j-main-container">
		<fieldset class="form-horizontal">
			<?php foreach ($this->form->getFieldset('edit') as $field): ?>
			<div class="control-group">
				<div class="control-label"><?php echo $field->label; ?></div>
				<div class="controls"><?php echo $field->input; ?></div>
			</div>
			<?php endforeach; ?>
		</fieldset>
	</div>

	<input type="hidden" name="controller" value="premieres" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="id[]" value="<?php echo !empty($this->items->id) ? $this->items->id : ''; ?>" />
	<?php echo JHtml::_('form.token'); ?>
</form>
