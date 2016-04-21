<?php
/**
 * @package     Kinoarhiv.Site
 * @subpackage  com_kinoarhiv
 *
 * @copyright   Copyright (C) 2010 Libra.ms. All rights reserved.
 * @license     GNU General Public License version 2 or later
 * @url            http://киноархив.com/
 */

defined('_JEXEC') or die;

if ($this->params->get('search_names_enable') == 0)
{
	return;
}
?>
<script type="text/javascript">
	jQuery(document).ready(function($){
		$('#filters_names_birthcountry').select2({
			placeholder: '<?php echo JText::_('JGLOBAL_SELECT_AN_OPTION'); ?>',
			allowClear: true,
			formatSelection: function(data){
				return "<img class='flag-dd' src='<?php echo JUri::base(); ?>components/com_kinoarhiv/assets/themes/component/<?php echo $this->params->get('ka_theme'); ?>/images/icons/countries/" + $(data.element).data('code') + ".png'/> " + data.text;
			},
			escapeMarkup: function(m) { return m; }
		});

		$('#filters_names_mtitle').select2({
			placeholder: '<?php echo JText::_('JGLOBAL_KEEP_TYPING'); ?>',
			allowClear: true,
			minimumInputLength: 1,
			maximumSelectionSize: 1,
			ajax: {
				cache: true,
				url: '<?php echo JRoute::_('index.php?option=com_kinoarhiv&task=ajaxData&element=movies&format=json&Itemid=' . $this->home_itemid['movies'], false); ?>',
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
				var id = parseInt($(element).val(), 10);

				if (id !== 0) {
					$.ajax('<?php echo JRoute::_('index.php?option=com_kinoarhiv&task=ajaxData&element=movies&format=json&Itemid=' . $this->home_itemid['movies'], false); ?>', {
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
</script>
<div class="advsearch-names<?php echo (JFactory::getApplication()->input->get('task', '', 'cmd') != 'names') ? ' well uk-panel uk-panel-box' : ''; ?>">
	<form action="<?php echo JRoute::_('index.php?option=com_kinoarhiv&view=names', false); ?>" id="filters_names" method="post" autocomplete="off">
		<fieldset class="form-horizontal uk-form">
			<legend class="uk-panel-title"><?php echo JText::_('COM_KA_SEARCH_ADV_NAMES_TITLE'); ?></legend>

			<?php if ($this->params->get('search_names_name') == 1): ?>
			<div class="row-fluid uk-form-row">
				<div class="span12 uk-width-1-1">
					<div class="control-group uk-width-1-1">
						<div class="control-label uk-width-1-6"><?php echo KAComponentHelper::setLabel('filters_names_name', 'COM_KA_SEARCH_ADV_NAMES_NAME_LABEL'); ?></div>
						<div class="controls uk-width-1-2"><input name="filters[names][name]" type="text" id="filters_names_name" class="span10 uk-width-1-1" value="<?php echo $this->activeFilters->def('filters.names.name', ''); ?>" /></div>
					</div>
				</div>
			</div>
			<?php endif; ?>

			<?php if ($this->params->get('search_names_birthday') == 1 || $this->params->get('search_names_gender') == 1): ?>
			<div class="row-fluid uk-form-row">
				<div class="span12 uk-width-1-1">
					<div class="control-group uk-width-1-1">
						<div class="control-label uk-width-1-4"><?php echo $this->params->get('search_names_birthday') == 1 ? KAComponentHelper::setLabel('filters_names_birthday', 'COM_KA_NAMES_DATE_OF_BIRTH') : ''; ?></div>
						<div class="controls uk-width-1-1">
							<?php if ($this->params->get('search_names_birthday') == 1): ?>
							<div class="input-append date"
								data-provide="datepicker" data-date-format="yyyy-mm-dd" data-date-today-btn="linked"
								data-date-clear-btn="true" data-date-calendar-weeks="true" data-date-today-highlight="true"
								data-date-autoclose="true" data-date-language="ru">
								<input name="filters[names][birthday]" type="text" id="filters_names_birthday" class="span8 uk-width-1-2" value="<?php echo $this->activeFilters->def('filters.names.birthday', ''); ?>" />
								<div class="btn">
									<span class="icon-calendar"></span>
								</div>
							</div>
							<?php endif; ?>
							<?php if ($this->params->get('search_names_gender') == 1): ?>&nbsp;&nbsp;&nbsp;<?php echo JText::_('COM_KA_SEARCH_ADV_NAMES_GENDER_LABEL'); ?> <?php echo JHTML::_('select.genericlist', $this->items->names->gender, 'filters[names][gender]', array('class'=>'span4 uk-width-1-4'), 'value', 'text', $this->activeFilters->def('filters.names.gender', ''), 'filters_names_gender'); ?>
							<?php endif; ?>
						</div>
					</div>
				</div>
			</div>
			<?php endif; ?>

			<?php if ($this->params->get('search_names_mtitle') == 1): ?>
			<div class="row-fluid uk-form-row">
				<div class="span12 uk-width-1-1">
					<div class="control-group uk-width-1-1">
						<div class="control-label uk-width-1-6"><?php echo KAComponentHelper::setLabel('filters_names_mtitle', 'COM_KA_SEARCH_ADV_MOVIES_TITLE_LABEL'); ?></div>
						<div class="controls uk-width-1-2"><input name="filters[names][mtitle]" type="hidden" id="filters_names_mtitle" class="span10 uk-width-1-1" value="<?php echo $this->activeFilters->def('filters.names.mtitle', 0); ?>" /></div>
					</div>
				</div>
			</div>
			<?php endif; ?>

			<?php if ($this->params->get('search_names_birthplace') == 1): ?>
			<div class="row-fluid uk-form-row">
				<div class="span12 uk-width-1-1">
					<div class="control-group uk-width-1-1">
						<div class="control-label uk-width-1-4"><?php echo KAComponentHelper::setLabel('filters_names_birthplace', 'COM_KA_NAMES_BIRTHPLACE_1'); ?></div>
						<div class="controls uk-width-1-2">
							<input name="filters[names][birthplace]" type="text" id="filters_names_birthplace" class="span10 uk-width-1-1" value="<?php echo $this->activeFilters->def('filters.names.birthplace', ''); ?>" />
						</div>
					</div>
				</div>
			</div>
			<?php endif; ?>

			<?php if ($this->params->get('search_names_birthcountry') == 1): ?>
			<div class="row-fluid uk-form-row">
				<div class="span12 uk-width-1-1">
					<div class="control-group uk-width-1-1">
						<div class="control-label uk-width-1-4"><?php echo KAComponentHelper::setLabel('filters_names_birthcountry', 'COM_KA_COUNTRY'); ?></div>
						<div class="controls uk-width-1-2">
							<select name="filters[names][birthcountry]" id="filters_names_birthcountry" class="span10 uk-width-1-1">
								<?php foreach ($this->items->names->birthcountry as $country):
									$selected = ($country->id == $this->activeFilters->def('filters.names.birthcountry', '')) ? ' selected' : ''; ?>
								<option value="<?php echo $country->id; ?>" data-code="<?php echo $country->code; ?>"<?php echo $selected; ?>><?php echo $country->name; ?></option>
								<?php endforeach; ?>
							</select>
						</div>
					</div>
				</div>
			</div>
			<?php endif; ?>

			<?php if ($this->params->get('search_names_amplua') == 1): ?>
			<div class="row-fluid uk-form-row">
				<div class="span12 uk-width-1-1">
					<div class="control-group uk-width-1-1">
						<div class="control-label uk-width-1-6"><?php echo KAComponentHelper::setLabel('filters_names_amplua', 'COM_KA_SEARCH_ADV_NAMES_AMPLUA_LABEL'); ?></div>
						<div class="controls uk-width-1-2"><?php echo JHTML::_('select.genericlist', $this->items->names->amplua, 'filters[names][amplua]', array('class'=>'span10 uk-width-1-2'), 'value', 'text', $this->activeFilters->def('filters.names.amplua', ''), 'filters_names_amplua'); ?></div>
					</div>
				</div>
			</div>
			<?php endif; ?>
		</fieldset>

		<input type="hidden" name="option" value="com_kinoarhiv" />
		<input type="hidden" name="view" value="names" />
		<input type="hidden" name="task" value="search" />
		<input type="hidden" name="Itemid" value="<?php echo $this->home_itemid['names']; ?>" />
		<?php echo JHtml::_('form.token'); ?>
		<input type="submit" class="btn btn-primary uk-button uk-button-primary" value="<?php echo JText::_('JSEARCH_FILTER_SUBMIT'); ?>" />
		<input type="reset" class="btn uk-button" value="<?php echo JText::_('JSEARCH_FILTER_CLEAR'); ?>" onclick="jQuery('#filters_names_birthcountry, #filters_names_mtitle').select2('val', ''); return true;" />
	</form>
</div>
