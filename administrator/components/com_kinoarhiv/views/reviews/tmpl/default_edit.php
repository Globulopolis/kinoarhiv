<?php defined('_JEXEC') or die;
JHtml::_('behavior.keepalive');
?>
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
		$('.hasAutocomplete').each(function(){
			var datatype = $(this).data('ac-type'),
				allow_clear = $(this).data('allow-clear');;

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

					if (!empty(id)) {
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
		<div class="row-fluid">
			<fieldset class="form-horizontal">
				<div class="span6">
					<div class="control-group">
						<?php echo $this->form->getLabel('review'); ?>
						<?php echo $this->form->getInput('review'); ?>
					</div>
				</div>
				<div class="span6">
					<div class="control-group">
						<div class="control-label"><?php echo $this->form->getLabel('movie_id'); ?></div>
						<div class="controls"><?php echo $this->form->getInput('movie_id'); ?></div>
					</div>
					<div class="control-group">
						<div class="control-label"><?php echo $this->form->getLabel('created'); ?></div>
						<div class="controls"><?php echo $this->form->getInput('created'); ?></div>
					</div>
					<div class="control-group">
						<div class="control-label"><?php echo $this->form->getLabel('uid'); ?></div>
						<div class="controls"><?php echo $this->form->getInput('uid'); ?></div>
					</div>
					<div class="control-group">
						<div class="control-label"><?php echo $this->form->getLabel('ip'); ?></div>
						<div class="controls"><?php echo $this->form->getInput('ip'); ?></div>
					</div>
					<div class="control-group">
						<div class="control-label"><?php echo $this->form->getLabel('type'); ?></div>
						<div class="controls"><?php echo $this->form->getInput('type'); ?></div>
					</div>
					<div class="control-group">
						<div class="control-label"><?php echo $this->form->getLabel('state'); ?></div>
						<div class="controls"><?php echo $this->form->getInput('state'); ?></div>
					</div>
				</div>
			</fieldset>
		</div>
	</div>

	<input type="hidden" name="controller" value="reviews" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="id" value="<?php echo ($this->form->getValue('id') != 0) ? $this->form->getValue('id') : ''; ?>" />
	<?php echo JHtml::_('form.token'); ?>
</form>
