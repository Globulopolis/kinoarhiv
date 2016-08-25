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

		$('.cmd-reset-names').click(function(){
			$('#name_country').select2('val', '');
		});
	});
</script>
<div class="advsearch-names<?php echo (JFactory::getApplication()->input->get('task', '', 'cmd') != 'names') ? ' well uk-panel uk-panel-box' : ''; ?>">
	<form action="<?php echo JRoute::_('index.php?option=com_kinoarhiv&view=names', false); ?>" id="filters_names" method="post" autocomplete="off" class="form-validate">
		<fieldset class="form-horizontal uk-form">
			<legend class="uk-panel-title"><?php echo JText::_('COM_KA_SEARCH_ADV_NAMES_TITLE'); ?></legend>

			<?php if ($this->params->get('search_names_name') == 1): ?>
			<div class="row-fluid uk-form-row">
				<div class="span12 uk-width-1-1">
					<div class="control-group uk-width-1-1">
						<div class="control-label uk-width-1-6"><?php echo $this->form->getLabel('name', 'name'); ?></div>
						<div class="controls uk-width-1-2"><?php echo $this->form->getInput('name', 'name'); ?></div>
					</div>
				</div>
			</div>
			<?php endif; ?>

			<?php if ($this->params->get('search_names_birthday') == 1 || $this->params->get('search_names_gender') == 1): ?>
			<div class="row-fluid uk-form-row">
				<div class="span12 uk-width-1-1">
					<div class="control-group uk-width-1-1">
						<div class="control-label uk-width-1-2"><?php echo $this->form->getLabel('birthday', 'name'); ?></div>
						<div class="controls uk-width-1-1">
							<?php if ($this->params->get('search_names_birthday') == 1): ?>
								<?php echo $this->form->getInput('birthday', 'name'); ?>
							<?php endif; ?>
							<?php if ($this->params->get('search_names_gender') == 1): ?>
							&nbsp;&nbsp;&nbsp;<?php echo JText::_('COM_KA_SEARCH_ADV_NAMES_GENDER_LABEL'); ?>&nbsp;&nbsp;<?php echo $this->form->getInput('gender', 'name'); ?>
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
						<div class="control-label uk-width-1-6"><?php echo $this->form->getLabel('title', 'name'); ?></div>
						<div class="controls uk-width-1-2"><?php echo $this->form->getInput('title', 'name'); ?></div>
					</div>
				</div>
			</div>
			<?php endif; ?>

			<?php if ($this->params->get('search_names_birthplace') == 1): ?>
			<div class="row-fluid uk-form-row">
				<div class="span12 uk-width-1-1">
					<div class="control-group uk-width-1-1">
						<div class="control-label uk-width-1-4"><?php echo $this->form->getLabel('birthplace', 'name'); ?></div>
						<div class="controls uk-width-1-2"><?php echo $this->form->getInput('birthplace', 'name'); ?></div>
					</div>
				</div>
			</div>
			<?php endif; ?>

			<?php if ($this->params->get('search_names_birthcountry') == 1): ?>
			<div class="row-fluid uk-form-row">
				<div class="span12 uk-width-1-1">
					<div class="control-group uk-width-1-1">
						<div class="control-label uk-width-1-4"><?php echo $this->form->getLabel('country', 'name'); ?></div>
						<div class="controls uk-width-1-2"><?php echo $this->form->getInput('country', 'name'); ?></div>
					</div>
				</div>
			</div>
			<?php endif; ?>

			<?php if ($this->params->get('search_names_amplua') == 1): ?>
			<div class="row-fluid uk-form-row">
				<div class="span12 uk-width-1-1">
					<div class="control-group uk-width-1-1">
						<div class="control-label uk-width-1-6"><?php echo $this->form->getLabel('amplua', 'name'); ?></div>
						<div class="controls uk-width-1-2"><?php echo $this->form->getInput('amplua', 'name'); ?></div>
					</div>
				</div>
			</div>
			<?php endif; ?>
		</fieldset>

		<input type="hidden" name="option" value="com_kinoarhiv" />
		<input type="hidden" name="task" value="search.results" />
		<input type="hidden" name="content" value="name" />
		<input type="hidden" name="Itemid" value="<?php echo $this->home_itemid['names']; ?>" />
		<?php echo JHtml::_('form.token'); ?>
		<input type="submit" class="btn btn-primary uk-button uk-button-primary validate" value="<?php echo JText::_('JSEARCH_FILTER_SUBMIT'); ?>" />
		<input type="reset" class="btn uk-button cmd-reset-names" value="<?php echo JText::_('JSEARCH_FILTER_CLEAR'); ?>" />
	</form>
</div>
