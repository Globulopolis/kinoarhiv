<?php defined('_JEXEC') or die;
$input = JFactory::getApplication()->input;
JFormHelper::addFieldPath(JPATH_COMPONENT . '/models/fields');

if ($input->get('section', '', 'word') == 'movie') {
	$movies_field = JFormHelper::loadFieldType('movies');
	$element = 'movies';
} elseif ($input->get('section', '', 'word') == 'name') {
	$names_field = JFormHelper::loadFieldType('names');
	$element = 'names';
}

$params = JComponentHelper::getParams('com_kinoarhiv');
?>
<style type="text/css">
	@import url("<?php echo JURI::root(); ?>components/com_kinoarhiv/assets/themes/component/<?php echo $params->get('ka_theme'); ?>/css/select.css");
</style>
<script src="<?php echo JURI::root(); ?>components/com_kinoarhiv/assets/js/select2.min.js" type="text/javascript"></script>
<?php KAComponentHelper::getScriptLanguage('select2_locale_', false, 'select', true); ?>
<script src="<?php echo JURI::base(); ?>components/com_kinoarhiv/assets/js/utils.js" type="text/javascript"></script>
<script type="text/javascript">
//<![CDATA[
	jQuery(document).ready(function($){
		function format(data) {
		<?php if ($input->get('section', '', 'word') == 'movie'): ?>
			if (data.year == '0000') return data.title;
			return data.title+' ('+data.year+')';
		<?php elseif ($input->get('section', '', 'word') == 'name'): ?>
			var title = '';
			
			if (data.name != '') title += data.name;
			if (data.name != '' && data.latin_name != '') title += ' / ';
			if (data.latin_name != '') title += data.latin_name;

			return title;
		<?php endif; ?>
		}

		$('#item_id').select2({
			placeholder: '<?php echo JText::_('COM_KA_SEARCH_AJAX'); ?>',
			quietMillis: 200,
			allowClear: true,
			minimumInputLength: 1,
			maximumSelectionSize: 1,
			ajax: {
				cache: true,
				url: 'index.php?option=com_kinoarhiv&task=ajaxData&element=<?php echo $element; ?>&format=json&ignore[]=<?php echo $input->get('id', 0, 'int'); ?>',
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
			formatResult: format,
			formatSelection: format,
			escapeMarkup: function(m) { return m; }
		});
	});
//]]>
</script>
<div class="row-fluid">
	<div class="span12">
		<form action="index.php?option=com_kinoarhiv&controller=mediamanager&task=copyfrom&format=json" id="form_copyfrom">
			<fieldset class="form-horizontal copy">
				<div class="control-group">
					<div class="control-label">
						<?php if ($input->get('section', '', 'word') == 'movie'):
							echo $movies_field->getLabel('item_id', 'COM_KA_MOVIES_GALLERY_COPYFROM_LABEL', 'COM_KA_MOVIES_GALLERY_COPYFROM_DESC', 'required');
						elseif ($input->get('section', '', 'word') == 'name'):
							echo $names_field->getLabel('item_id', 'COM_KA_NAMES_GALLERY_COPYFROM_LABEL', 'COM_KA_NAMES_GALLERY_COPYFROM_DESC', 'required');
						endif;
						?>
					</div>
					<div class="controls copy-from">
						<?php if ($input->get('section', '', 'word') == 'movie'):
							echo $movies_field->getInput('item_id', 100, '', 0, 'span12 required');
						elseif ($input->get('section', '', 'word') == 'name'):
							echo $names_field->getInput('item_id', 100, '', 0, 'span12 required');
						endif;
						?>
					</div>
				</div>
				<div class="control-group">
					<div class="control-label">
						<label class="required" for="item_subtype"><?php echo JText::_('COM_KA_MOVIES_GALLERY_COPYFROM_ITEMTYPE_LABEL'); ?></label>
					</div>
					<div class="controls copy-from">
						<select name="item_subtype" id="item_subtype" class="span7 required">
						<?php if ($input->get('section', '', 'word') == 'movie'): ?>
							<option value="1"><?php echo JText::_('COM_KA_MOVIES_WALLPP'); ?></option>
							<option value="2"><?php echo JText::_('COM_KA_MOVIES_POSTERS'); ?></option>
							<option value="3"><?php echo JText::_('COM_KA_MOVIES_SCRSHOTS'); ?></option>
						<?php elseif ($input->get('section', '', 'word') == 'name'): ?>
							<option value="1"><?php echo JText::_('COM_KA_NAMES_GALLERY_WALLPP'); ?></option>
							<option value="2"><?php echo JText::_('COM_KA_NAMES_GALLERY_POSTERS'); ?></option>
							<option value="3"><?php echo JText::_('COM_KA_NAMES_GALLERY_PHOTO'); ?></option>
						<?php endif; ?>
						</select>
					</div>
				</div>
			</fieldset>
			<input type="hidden" name="item_type" id="item_type" value="<?php echo $input->get('item_type', '', 'word'); ?>" />
			<input type="hidden" name="section" id="section" value="<?php echo $input->get('section', '', 'word'); ?>" />
			<input type="hidden" name="id" id="id" value="<?php echo $input->get('id', 0, 'int'); ?>" /><!-- Parent movie/name ID -->
		</form>
	</div>
</div>
