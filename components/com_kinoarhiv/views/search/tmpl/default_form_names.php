<?php defined('_JEXEC') or die; ?>
<div class="advsearch-names<?php echo (JFactory::getApplication()->input->get('task', '', 'cmd') != 'names') ? ' well uk-panel uk-panel-box' : ''; ?>">
	<form action="<?php echo JRoute::_('index.php?option=com_kinoarhiv&view=names'); ?>" id="filters_names" method="post">
		<fieldset class="form-horizontal uk-form">
			<legend class="uk-panel-title"><?php echo JText::_('COM_KA_SEARCH_ADV_NAMES_TITLE'); ?></legend>

			<div class="row-fluid uk-form-row">
				<div class="span12 uk-width-1-1">
					<div class="control-group uk-width-1-1">
						<div class="control-label uk-width-1-6"><?php echo GlobalHelper::setLabel('filters_names_title', 'COM_KA_SEARCH_ADV_NAMES_TITLE_LABEL'); ?></div>
						<div class="controls uk-width-1-2"><input name="filters[names][title]" type="text" id="filters_names_title" class="span10 uk-width-1-1" value="<?php echo $this->activeFilters->def('filters.names.title', ''); ?>" required /></div>
					</div>
				</div>
			</div>

			<div class="row-fluid uk-form-row">
				<div class="span12 uk-width-1-1">
					<div class="control-group uk-width-1-1">
						<div class="control-label uk-width-1-4"><?php echo GlobalHelper::setLabel('filters_names_birthday', 'COM_KA_NAMES_DATE_OF_BIRTH'); ?></div>
						<div class="controls uk-width-1-1">
							<input name="filters[names][birthday]" type="text" id="filters_names_birthday" class="span4 uk-width-1-1" value="<?php echo $this->activeFilters->def('filters.names.birthday', ''); ?>" />&nbsp;&nbsp;&nbsp;<?php echo JText::_('COM_KA_SEARCH_ADV_NAMES_GENDER_LABEL'); ?> <?php echo JHTML::_('select.genericlist', $this->items->names->gender, 'filters[names][gender]', array('class'=>'span4 uk-width-1-4'), 'value', 'text', $this->activeFilters->def('filters.names.gender', ''), 'filters_names_gender'); ?>
						</div>
					</div>
				</div>
			</div>

			<div class="row-fluid uk-form-row">
				<div class="span12 uk-width-1-1">
					<div class="control-group uk-width-1-1">
						<div class="control-label uk-width-1-6"><?php echo GlobalHelper::setLabel('filters_names_mtitle', 'COM_KA_SEARCH_ADV_NAMES_MOVIETITLE_LABEL'); ?></div>
						<div class="controls uk-width-1-4"><input name="filters[names][mtitle]" type="hidden" id="filters_names_mtitle" class="span10 uk-width-1-1" value="<?php echo $this->activeFilters->def('filters.names.mtitle', 0); ?>" /></div>
					</div>
				</div>
			</div>

			<div class="row-fluid uk-form-row">
				<div class="span12 uk-width-1-1">
					<div class="control-group uk-width-1-1">
						<div class="control-label uk-width-1-4"><?php echo GlobalHelper::setLabel('filters_names_birthplace', 'COM_KA_NAMES_BIRTHPLACE_1'); ?></div>
						<div class="controls uk-width-1-1">
							<input name="filters[names][birthplace]" type="text" id="filters_names_birthplace" class="span10 uk-width-1-1" value="<?php echo $this->activeFilters->def('filters.names.birthplace', ''); ?>" />
						</div>
					</div>
				</div>
			</div>

			<div class="row-fluid uk-form-row">
				<div class="span12 uk-width-1-1">
					<div class="control-group uk-width-1-1">
						<div class="control-label uk-width-1-4"><?php echo GlobalHelper::setLabel('filters_names_birthcountry', 'COM_KA_COUNTRY'); ?></div>
						<div class="controls uk-width-1-1">
							<select name="filters[names][birthcountry]" id="filters_names_birthcountry" class="span10 uk-width-1-4">
								<?php foreach ($this->items->names->birthcountry as $country):
									$selected = ($country->id == $this->activeFilters->def('filters.names.birthcountry', '')) ? ' selected' : ''; ?>
								<option value="<?php echo $country->id; ?>" data-code="<?php echo $country->code; ?>"<?php echo $selected; ?>><?php echo $country->name; ?></option>
								<?php endforeach; ?>
							</select>
						</div>
					</div>
				</div>
			</div>

			<div class="row-fluid uk-form-row">
				<div class="span12 uk-width-1-1">
					<div class="control-group uk-width-1-1">
						<div class="control-label uk-width-1-6"><?php echo GlobalHelper::setLabel('filters_names_amplua', 'COM_KA_SEARCH_ADV_NAMES_AMPLUA_LABEL'); ?></div>
						<div class="controls uk-width-1-2"><?php echo JHTML::_('select.genericlist', $this->items->names->amplua, 'filters[names][amplua]', array('class'=>'span10 uk-width-1-2'), 'value', 'text', $this->activeFilters->def('filters.names.amplua', ''), 'filters_names_amplua'); ?></div>
					</div>
				</div>
			</div>
		</fieldset>

		<input type="hidden" name="option" value="com_kinoarhiv" />
		<input type="hidden" name="view" value="names" />
		<input type="hidden" name="task" value="search" />
		<input type="hidden" name="Itemid" value="<?php echo $this->home_itemid['names']; ?>" />
		<?php echo JHtml::_('form.token'); ?>
		<input type="submit" class="btn btn-primary uk-button uk-button-primary" value="<?php echo JText::_('JSEARCH_FILTER_SUBMIT'); ?>" />
		<input type="reset" class="btn btn-default uk-button cmd-reset" value="<?php echo JText::_('JSEARCH_FILTER_CLEAR'); ?>" />
	</form>
</div>
