<?php defined('_JEXEC') or die; ?>
<script type="text/javascript" src="<?php echo JURI::base(); ?>components/com_kinoarhiv/assets/js/jquery.ui.tooltip.min.js"></script>
<script type="text/javascript" src="<?php echo JURI::base(); ?>components/com_kinoarhiv/assets/js/utils.js"></script>
<script type="text/javascript">
	jQuery(document).ready(function($){
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
	<div class="span12 rel-form_premiere">
		<fieldset class="form-horizontal">
			<div class="group">
				<div class="control-group">
					<div class="control-label"><?php echo $this->form->getLabel('r_vendor_id'); ?></div>
					<div class="controls"><?php echo $this->form->getInput('r_vendor_id'); ?></div>
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
					<div class="control-label"><?php echo $this->form->getLabel('r_ordering'); ?></div>
					<div class="controls"><?php echo $this->form->getInput('r_ordering'); ?></div>
				</div>
			</div>
		</fieldset>
		<div class="placeholder"></div>
	</div>
</div>
