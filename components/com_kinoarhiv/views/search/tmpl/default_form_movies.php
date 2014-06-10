<?php defined('_JEXEC') or die; ?>
<div class="advsearch-movies<?php echo (JFactory::getApplication()->input->get('task', '', 'cmd') != 'movies') ? ' well uk-panel uk-panel-box' : ''; ?>">
	<form action="<?php echo JRoute::_('index.php?option=com_kinoarhiv&view=movies'); ?>" id="filters_movies" method="post" autocomplete="off">
		<fieldset class="form-horizontal uk-form">
			<legend class="uk-panel-title"><?php echo JText::_('COM_KA_SEARCH_ADV_MOVIES_TITLE'); ?></legend>

			<div class="row-fluid uk-form-row">
				<div class="span12 uk-width-1-1">
					<div class="control-group uk-width-1-1">
						<div class="control-label uk-width-1-6"><?php echo GlobalHelper::setLabel('filters_movies_title', 'COM_KA_SEARCH_ADV_MOVIES_TITLE_LABEL'); ?></div>
						<div class="controls uk-width-1-2"><input name="filters[movies][title]" type="text" id="filters_movies_title" class="span10 uk-width-1-1" value="<?php echo $this->activeFilters->def('filters.movies.title', ''); ?>" required /></div>
					</div>
				</div>
			</div>

			<div class="row-fluid uk-form-row">
				<div class="span12 uk-width-1-1">
					<div class="control-group uk-width-1-1">
						<div class="control-label uk-width-1-6"><?php echo GlobalHelper::setLabel('filters_movies_year', 'COM_KA_SEARCH_ADV_MOVIES_YEAR_LABEL'); ?></div>
						<div class="controls uk-width-1-2"><input name="filters[movies][year]" type="text" id="filters_movies_year" class="span3 uk-width-1-4" value="<?php echo $this->activeFilters->def('filters.movies.year', ''); ?>" maxlength="9" /></div>
					</div>
				</div>
			</div>

			<div class="row-fluid uk-form-row">
				<div class="span12 uk-width-1-3">
					<div class="control-group uk-width-1-1">
						<div class="control-label uk-width-1-2"><?php echo GlobalHelper::setLabel('filters_movies_from_year', 'COM_KA_SEARCH_ADV_MOVIES_YEAR_FROM_TO_LABEL'); ?></div>
						<div class="controls uk-width-1-3">
							<?php echo JText::_('COM_KA_SEARCH_ADV_MOVIES_YEAR_FROM_LABEL'); ?> <?php echo JHTML::_('select.genericlist', $this->items->movies->from_year, 'filters[movies][from_year]', array('class'=>'span3'), 'value', 'text', $this->activeFilters->def('filters.movies.from_year', ''), 'filters_movies_from_year'); ?>&nbsp;&nbsp;&nbsp;
							<?php echo JText::_('COM_KA_SEARCH_ADV_MOVIES_YEAR_TO_LABEL'); ?> <?php echo JHTML::_('select.genericlist', $this->items->movies->to_year, 'filters[movies][to_year]', array('class'=>'span3'), 'value', 'text', $this->activeFilters->def('filters.movies.to_year', ''), 'filters_movies_to_year'); ?>
						</div>
					</div>
				</div>
			</div>

			<div class="row-fluid uk-form-row">
				<div class="span12 uk-width-1-1">
					<div class="control-group uk-width-1-1">
						<div class="control-label uk-width-1-6"><?php echo GlobalHelper::setLabel('filters_movies_country', 'COM_KA_SEARCH_ADV_MOVIES_COUNTRY_LABEL'); ?></div>
						<div class="controls uk-width-1-2">
							<select name="filters[movies][country]" id="filters_movies_country" class="span10 uk-width-1-2">
								<?php foreach ($this->items->movies->countries as $country):
									$selected = ($country->id == $this->activeFilters->def('filters.movies.country', '')) ? ' selected' : ''; ?>
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
						<div class="control-label uk-width-1-6"><?php echo GlobalHelper::setLabel('filters_movies_vendor', 'COM_KA_SEARCH_ADV_MOVIES_VENDOR_LABEL'); ?></div>
						<div class="controls uk-width-1-2"><?php echo JHTML::_('select.genericlist', $this->items->movies->vendors, 'filters[movies][vendor]', array('class'=>'span10 uk-width-1-2'), 'value', 'text', $this->activeFilters->def('filters.movies.vendor', ''), 'filters_movies_vendor'); ?></div>
					</div>
				</div>
			</div>

			<div class="row-fluid uk-form-row">
				<div class="span12 uk-width-1-1">
					<div class="control-group uk-width-1-1">
						<div class="control-label uk-width-1-6"><?php echo GlobalHelper::setLabel('filters_movies_genre', 'COM_KA_GENRE'); ?></div>
						<div class="controls uk-width-1-2"><?php echo JHTML::_('select.genericlist', $this->items->movies->genres, 'filters[movies][genre][]', array('class'=>'span10 uk-width-1-2', 'multiple'=>true), 'value', 'text', $this->activeFilters->def('filters.movies.genre', ''), 'filters_movies_genre'); ?></div>
					</div>
				</div>
			</div>

			<div class="row-fluid uk-form-row">
				<div class="span6 uk-width-1-1">
					<div class="control-group uk-width-1-1">
						<div class="control-label uk-width-1-6"><?php echo GlobalHelper::setLabel('filters_movies_mpaa', 'COM_KA_MPAA'); ?></div>
						<div class="controls uk-width-1-2"><?php echo JHTML::_('select.genericlist', $this->items->movies->mpaa, 'filters[movies][mpaa]', array('class'=>'span7 uk-width-1-6'), 'value', 'text', $this->activeFilters->def('filters.movies.mpaa', ''), 'filters_movies_mpaa'); ?></div>
					</div>
				</div>
				<div class="span6 uk-width-1-1">
					<div class="control-group uk-width-1-1">
						<div class="control-label uk-width-1-6"><?php echo GlobalHelper::setLabel('filters_movies_age_restrict', 'COM_KA_SEARCH_ADV_MOVIES_RU_AGE_RESTICT_LABEL'); ?></div>
						<div class="controls uk-width-1-2"><?php echo JHTML::_('select.genericlist', $this->items->movies->age_restrict, 'filters[movies][age_restrict]', array('class'=>'span7 uk-width-1-6'), 'value', 'text', $this->activeFilters->def('filters.movies.age_restrict', '-1'), 'filters_movies_age_restrict'); ?></div>
					</div>
				</div>
			</div>

			<div class="row-fluid uk-form-row">
				<div class="span12 uk-width-1-1">
					<div class="control-group uk-width-1-1">
						<div class="control-label uk-width-1-6"><?php echo GlobalHelper::setLabel('filters_movies_ua_rate', 'COM_KA_UA_RATE'); ?></div>
						<div class="controls uk-width-1-2"><?php echo JHTML::_('select.genericlist', $this->items->movies->ua_rate, 'filters[movies][ua_rate]', array('class'=>'span4 uk-width-1-3'), 'value', 'text', $this->activeFilters->def('filters.movies.ua_rate', ''), 'filters_movies_ua_rate'); ?></div>
					</div>
				</div>
			</div>

			<div class="row-fluid uk-form-row">
				<div class="span12 uk-width-1-1">
					<div class="control-group uk-width-1-1">
						<div class="control-label uk-width-1-6"><?php echo GlobalHelper::setLabel('filters_movies_rate', 'COM_KA_RATE'); ?></div>
						<div class="controls uk-width-1-2" style="padding-top: 4px;">
							<div class="span3">
								<input type="text" name="filters[movies][rate][min]" value="<?php echo (int)$this->activeFilters->def('filters.movies.rate.min', 0); ?>" id="filters_movies_rate_min" maxlength="2" size="3" /> - <input type="text" name="filters[movies][rate][max]" value="<?php echo (int)$this->activeFilters->def('filters.movies.rate.max', $this->params->get('vote_summ_num')); ?>" id="filters_movies_rate_max" maxlength="2" size="3" />
							</div>
							<div class="span6">
								<div id="filters_movies_rate" style="margin-top: 4px;"></div>
							</div>
						</div>
					</div>
				</div>
			</div>

			<div class="row-fluid uk-form-row">
				<div class="span12 uk-width-1-1">
					<div class="control-group uk-width-1-1">
						<div class="control-label uk-width-1-6"><?php echo GlobalHelper::setLabel('filters_movies_imdbrate', 'COM_KA_SEARCH_ADV_MOVIES_IMDB_RATE'); ?></div>
						<div class="controls uk-width-1-2" style="padding-top: 4px;">
							<div class="span3">
								<input type="text" name="filters[movies][imdbrate][min]" value="<?php echo (int)$this->activeFilters->def('filters.movies.imdbrate.min', 6); ?>" id="filters_movies_imdbrate_min" maxlength="2" size="3" /> - <input type="text" name="filters[movies][imdbrate][max]" value="<?php echo (int)$this->activeFilters->def('filters.movies.imdbrate.max', 10); ?>" id="filters_movies_imdbrate_max" maxlength="2" size="3" />
							</div>
							<div class="span6">
								<div id="filters_movies_imdbrate" style="margin-top: 4px;"></div>
							</div>
						</div>
					</div>
				</div>
			</div>

			<div class="row-fluid uk-form-row">
				<div class="span12 uk-width-1-1">
					<div class="control-group uk-width-1-1">
						<div class="control-label uk-width-1-6"><?php echo GlobalHelper::setLabel('filters_movies_kprate', 'COM_KA_SEARCH_ADV_MOVIES_KP_RATE'); ?></div>
						<div class="controls uk-width-1-2" style="padding-top: 4px;">
							<div class="span3">
								<input type="text" name="filters[movies][kprate][min]" value="<?php echo (int)$this->activeFilters->def('filters.movies.kprate.min', 6); ?>" id="filters_movies_kprate_min" maxlength="2" size="3" /> - <input type="text" name="filters[movies][kprate][max]" value="<?php echo (int)$this->activeFilters->def('filters.movies.kprate.max', 10); ?>" id="filters_movies_kprate_max" maxlength="2" size="3" />
							</div>
							<div class="span6">
								<div id="filters_movies_kprate" style="margin-top: 4px;"></div>
							</div>
						</div>
					</div>
				</div>
			</div>

			<div class="row-fluid uk-form-row">
				<div class="span12 uk-width-1-1">
					<div class="control-group uk-width-1-1">
						<div class="control-label uk-width-1-6"><?php echo GlobalHelper::setLabel('filters_movies_rtrate', 'COM_KA_SEARCH_ADV_MOVIES_RT_RATE'); ?></div>
						<div class="controls uk-width-1-2" style="padding-top: 4px;">
							<div class="span3">
								<input type="text" name="filters[movies][rtrate][min]" value="<?php echo (int)$this->activeFilters->def('filters.movies.rtrate.min', 0); ?>" id="filters_movies_rtrate_min" maxlength="3" size="3" /> - <input type="text" name="filters[movies][rtrate][max]" value="<?php echo (int)$this->activeFilters->def('filters.movies.rtrate.max', 100); ?>" id="filters_movies_rtrate_max" maxlength="3" size="3" />
							</div>
							<div class="span6">
								<div id="filters_movies_rtrate" style="margin-top: 4px;"></div>
							</div>
						</div>
					</div>
				</div>
			</div>

			<div class="row-fluid uk-form-row">
				<div class="span12 uk-width-1-1">
					<div class="control-group uk-width-1-1">
						<div class="control-label uk-width-1-6"><?php echo GlobalHelper::setLabel('filters_movies_from_budget', 'COM_KA_BUDGET'); ?></div>
						<div class="controls uk-width-1-2">
							<?php echo JText::_('COM_KA_SEARCH_ADV_RANGE_FROM_LABEL'); ?> <?php echo JHTML::_('select.genericlist', $this->items->movies->from_budget, 'filters[movies][from_budget]', array('class'=>'span4 uk-width-1-4'), 'value', 'text', $this->activeFilters->def('filters.movies.from_budget', ''), 'filters_movies_from_budget'); ?>&nbsp;&nbsp;&nbsp;
							<?php echo JText::_('COM_KA_SEARCH_ADV_RANGE_TO_LABEL'); ?> <?php echo JHTML::_('select.genericlist', $this->items->movies->to_budget, 'filters[movies][to_budget]', array('class'=>'span4 uk-width-1-4'), 'value', 'text', $this->activeFilters->def('filters.movies.to_budget', ''), 'filters_movies_to_budget'); ?>
						</div>
					</div>
				</div>
			</div>
		</fieldset>

		<input type="hidden" name="option" value="com_kinoarhiv" />
		<input type="hidden" name="view" value="movies" />
		<input type="hidden" name="task" value="search" />
		<input type="hidden" name="Itemid" value="<?php echo $this->home_itemid['movies']; ?>" />
		<?php echo JHtml::_('form.token'); ?>
		<input type="submit" class="btn btn-primary uk-button uk-button-primary" value="<?php echo JText::_('JSEARCH_FILTER_SUBMIT'); ?>" />
		<input type="reset" class="btn btn-default uk-button cmd-reset" value="<?php echo JText::_('JSEARCH_FILTER_CLEAR'); ?>" />
	</form>
</div>
