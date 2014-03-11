<?php defined('_JEXEC') or die;
JHtml::_('behavior.keepalive');
?>
<script src="<?php echo JURI::root(); ?>components/com_kinoarhiv/assets/js/ui.aurora.min.js" type="text/javascript"></script>
<script type="text/javascript">
	function showMsg(selector, text) {
		jQuery(selector).aurora({
			text: text,
			placement: 'before',
			button: 'close',
			button_title: '[<?php echo JText::_('COM_KA_CLOSE'); ?>]'
		});
	}

	Joomla.submitbutton = function(task) {
		if (task == 'apply' || task == 'save' || task == 'save2new') {
			if (jQuery('#form_title').val() == '') {
				showMsg('#j-main-container', '<?php echo JText::_('COM_KA_REQUIRED'); ?>');
				return;
			}
		}
		Joomla.submitform(task);
	}

	jQuery(document).ready(function($){
		$('input.autocomplete').each(function(){
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
						if (data.length < 1) {
							return '';
						} else {
							return "<img class='flag-dd' src='<?php echo JURI::root(); ?>components/com_kinoarhiv/assets/themes/component/<?php echo $this->params->get('ka_theme'); ?>/images/icons/countries/" + data.code + ".png'/>" + data.title;
						}
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
	<input type="hidden" name="id" value="<?php echo !empty($this->items->id) ? $this->items->id : ''; ?>" />
	<?php echo JHtml::_('form.token'); ?>
</form>
