<?php defined('_JEXEC') or die;
JHtml::_('behavior.tooltip');
JHtml::_('behavior.keepalive');
?>
<script type="text/javascript" src="<?php echo JURI::base(); ?>components/com_kinoarhiv/assets/js/select2.min.js"></script>
<script type="text/javascript" src="<?php echo JURI::base(); ?>components/com_kinoarhiv/assets/js/i18n/select/select2_locale_<?php echo substr($this->lang->getTag(), 0, 2); ?>.js"></script>
<script type="text/javascript">
	Joomla.submitbutton = function(task) {
		if (task == 'apply' || task == 'save') {
			if (jQuery('#form_review').val() == '' || jQuery('#form_movie_id').select2('val') == '') {
				showMsg('#j-main-container', '<?php echo JText::_('COM_KA_REQUIRED'); ?>');
				return;
			}
		}
		Joomla.submitform(task);
	}

	jQuery(document).ready(function($){
		$('input.autocomplete').each(function(){
			var datatype = $(this).data('ac-type');

			$(this).select2({
				placeholder: '<?php echo JText::_('COM_KA_SEARCH_AJAX'); ?>',
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
					if (data.year == '0000') return data.title;
					return data.title+' ('+data.year+')';
				},
				formatSelection: function(data){
					if (data.year == '0000') return data.title;
					return data.title+' ('+data.year+')';
				},
				escapeMarkup: function(m) { return m; }
			});
		});
	});
</script>
<form action="<?php echo JRoute::_('index.php?option=com_kinoarhiv'); ?>" method="post" name="adminForm" id="adminForm" autocomplete="off">
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

	<input type="hidden" name="controller" value="reviews" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="id" value="<?php echo !empty($this->items->id) ? $this->items->id : ''; ?>" />
	<?php echo JHtml::_('form.token'); ?>
</form>
