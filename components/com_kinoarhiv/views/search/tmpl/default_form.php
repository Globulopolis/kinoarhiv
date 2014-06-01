<?php defined('_JEXEC') or die;
JHtml::_('formbehavior.chosen', 'select');
?>
<script type="text/javascript">
	jQuery(document).ready(function($){
		$('.cmd-reset').click(function(){
			$(this).closest('form').find('select').val('').trigger('liszt:updated');
		});

		$('#form_movies_rate').slider({
			range: true,
			min: 0,
			max: 10,
			values: [0, 10],
			slide: function(event, ui){
				$('#rate_val').text(ui.values[0] + ' - ' + ui.values[1]);
			}
		});
		$('#rate_val').text($('#form_movies_rate').slider('values', 0) + ' - ' + $('#form_movies_rate').slider('values', 1));

		$('#form_movies_imdbrate').slider({
			range: true,
			min: 0,
			max: 10,
			values: [6, 10],
			slide: function(event, ui){
				$('#imdbrate_val').text(ui.values[0] + ' - ' + ui.values[1]);
			}
		});
		$('#imdbrate_val').text($('#form_movies_imdbrate').slider('values', 0) + ' - ' + $('#form_movies_imdbrate').slider('values', 1));

		$('#form_movies_kprate').slider({
			range: true,
			min: 0,
			max: 10,
			values: [6, 10],
			slide: function(event, ui){
				$('#kprate_val').text(ui.values[0] + ' - ' + ui.values[1]);
			}
		});
		$('#kprate_val').text($('#form_movies_kprate').slider('values', 0) + ' - ' + $('#form_movies_kprate').slider('values', 1));

		$('#form_movies_rtrate').slider({
			range: true,
			min: 0,
			max: 100,
			values: [0, 100],
			slide: function(event, ui){
				$('#rtrate_val').text(ui.values[0] + ' - ' + ui.values[1]);
			}
		});
		$('#rtrate_val').text($('#form_movies_rtrate').slider('values', 0) + ' - ' + $('#form_movies_rtrate').slider('values', 1));
	});
</script>
<div class="uk-article ka-content">
	<div class="advsearch-movies well uk-panel uk-panel-box">
		<form action="<?php echo JRoute::_('index.php');?>" id="form_movies" method="post" autocomplete="off">
			<fieldset class="form-horizontal uk-form">
				<legend class="uk-panel-title"><?php echo JText::_('COM_KA_SEARCH_ADV_MOVIES_TITLE'); ?></legend>

				<div class="row-fluid uk-form-row">
					<div class="span12 uk-width-1-1">
						<div class="control-group uk-width-1-1">
							<div class="control-label uk-width-1-6"><?php echo $this->setLabel('form_movies_title', 'COM_KA_SEARCH_ADV_MOVIES_TITLE_LABEL'); ?></div>
							<div class="controls uk-width-1-2"><input name="form[movies][title]" type="text" id="form_movies_title" class="span10 uk-width-1-1" value="" required /></div>
						</div>
					</div>
				</div>

				<div class="row-fluid uk-form-row">
					<div class="span12 uk-width-1-1">
						<div class="control-group uk-width-1-1">
							<div class="control-label uk-width-1-6"><?php echo $this->setLabel('form_movies_year', 'COM_KA_SEARCH_ADV_MOVIES_YEAR_LABEL'); ?></div>
							<div class="controls uk-width-1-2"><input name="form[movies][year]" type="text" id="form_movies_year" class="span3 uk-width-1-4" value="" maxlength="9" /></div>
						</div>
					</div>
				</div>

				<div class="row-fluid uk-form-row">
					<div class="span12 uk-width-1-3">
						<div class="control-group uk-width-1-1">
							<div class="control-label uk-width-1-2"><?php echo $this->setLabel('form_movies_from_year', 'COM_KA_SEARCH_ADV_MOVIES_YEAR_FROM_TO_LABEL'); ?></div>
							<div class="controls uk-width-1-3">
								<?php echo JText::_('COM_KA_SEARCH_ADV_MOVIES_YEAR_FROM_LABEL'); ?> <?php echo JHTML::_('select.genericlist', $this->items->movies->from_year, 'form[movies][from_year]', array('class'=>'span3'), 'value', 'text', '', 'form_movies_from_year'); ?>&nbsp;&nbsp;&nbsp;
								<?php echo JText::_('COM_KA_SEARCH_ADV_MOVIES_YEAR_TO_LABEL'); ?> <?php echo JHTML::_('select.genericlist', $this->items->movies->to_year, 'form[movies][to_year]', array('class'=>'span3'), 'value', 'text', '', 'form_movies_to_year'); ?>
							</div>
						</div>
					</div>
				</div>

				<div class="row-fluid uk-form-row">
					<div class="span12 uk-width-1-1">
						<div class="control-group uk-width-1-1">
							<div class="control-label uk-width-1-6"><?php echo $this->setLabel('form_movies_country', 'COM_KA_SEARCH_ADV_MOVIES_COUNTRY_LABEL'); ?></div>
							<div class="controls uk-width-1-2"><?php echo JHTML::_('select.genericlist', $this->items->movies->countries, 'form[movies][country]', array('class'=>'span10 uk-width-1-2'), 'value', 'text', '', 'form_movies_country'); ?></div>
						</div>
					</div>
				</div>

				<div class="row-fluid uk-form-row">
					<div class="span12 uk-width-1-1">
						<div class="control-group uk-width-1-1">
							<div class="control-label uk-width-1-6"><?php echo $this->setLabel('form_movies_vendor', 'COM_KA_SEARCH_ADV_MOVIES_VENDOR_LABEL'); ?></div>
							<div class="controls uk-width-1-2"><?php echo JHTML::_('select.genericlist', $this->items->movies->vendors, 'form[movies][vendor]', array('class'=>'span10 uk-width-1-2'), 'value', 'text', '', 'form_movies_vendor'); ?></div>
						</div>
					</div>
				</div>

				<div class="row-fluid uk-form-row">
					<div class="span12 uk-width-1-1">
						<div class="control-group uk-width-1-1">
							<div class="control-label uk-width-1-6"><?php echo $this->setLabel('form_movies_genre', 'COM_KA_GENRE'); ?></div>
							<div class="controls uk-width-1-2"><?php echo JHTML::_('select.genericlist', $this->items->movies->genres, 'form[movies][genre]', array('class'=>'span10 uk-width-1-2', 'multiple'=>true), 'value', 'text', '', 'form_movies_genre'); ?></div>
						</div>
					</div>
				</div>

				<div class="row-fluid uk-form-row">
					<div class="span6 uk-width-1-1">
						<div class="control-group uk-width-1-1">
							<div class="control-label uk-width-1-6"><?php echo $this->setLabel('form_movies_mpaa', 'COM_KA_MPAA'); ?></div>
							<div class="controls uk-width-1-2"><?php echo JHTML::_('select.genericlist', $this->items->movies->mpaa, 'form[movies][mpaa]', array('class'=>'span6 uk-width-1-6'), 'value', 'text', '', 'form_movies_mpaa'); ?></div>
						</div>
					</div>
					<div class="span6 uk-width-1-1">
						<div class="control-group uk-width-1-1">
							<div class="control-label uk-width-1-6"><?php echo $this->setLabel('form_movies_age_restrict', 'COM_KA_SEARCH_ADV_MOVIES_RU_AGE_RESTICT_LABEL'); ?></div>
							<div class="controls uk-width-1-2"><?php echo JHTML::_('select.genericlist', $this->items->movies->age_restrict, 'form[movies][age_restrict]', array('class'=>'span6 uk-width-1-6'), 'value', 'text', '', 'form_movies_age_restrict'); ?></div>
						</div>
					</div>
				</div>

				<div class="row-fluid uk-form-row">
					<div class="span12 uk-width-1-1">
						<div class="control-group uk-width-1-1">
							<div class="control-label uk-width-1-6"><?php echo $this->setLabel('form_movies_ua_rate', 'COM_KA_UA_RATE'); ?></div>
							<div class="controls uk-width-1-2"><?php echo JHTML::_('select.genericlist', $this->items->movies->ua_rate, 'form[movies][ua_rate]', array('class'=>'span6 uk-width-1-2'), 'value', 'text', '', 'form_movies_ua_rate'); ?></div>
						</div>
					</div>
				</div>

				<div class="row-fluid uk-form-row">
					<div class="span12 uk-width-1-1">
						<div class="control-group uk-width-1-1">
							<div class="control-label uk-width-1-6"><?php echo $this->setLabel('form_movies_rate', 'COM_KA_RATE'); ?></div>
							<div class="controls uk-width-1-2" style="padding-top: 4px;">
								<div class="span6">
									<div id="form_movies_rate" style="margin-top: 4px;"></div>
								</div>
								<span id="rate_val" style="color: #f6931f; font-weight: bold; padding-left: 1em;"></span>
							</div>
						</div>
					</div>
				</div>

				<div class="row-fluid uk-form-row">
					<div class="span12 uk-width-1-1">
						<div class="control-group uk-width-1-1">
							<div class="control-label uk-width-1-6"><?php echo $this->setLabel('form_movies_imdbrate', 'COM_KA_SEARCH_ADV_MOVIES_IMDB_RATE'); ?></div>
							<div class="controls uk-width-1-2" style="padding-top: 4px;">
								<div class="span6">
									<div id="form_movies_imdbrate" style="margin-top: 4px;"></div>
								</div>
								<span id="imdbrate_val" style="color: #f6931f; font-weight: bold; padding-left: 1em;"></span>
							</div>
						</div>
					</div>
				</div>

				<div class="row-fluid uk-form-row">
					<div class="span12 uk-width-1-1">
						<div class="control-group uk-width-1-1">
							<div class="control-label uk-width-1-6"><?php echo $this->setLabel('form_movies_kprate', 'COM_KA_SEARCH_ADV_MOVIES_KP_RATE'); ?></div>
							<div class="controls uk-width-1-2" style="padding-top: 4px;">
								<div class="span6">
									<div id="form_movies_kprate" style="margin-top: 4px;"></div>
								</div>
								<span id="kprate_val" style="color: #f6931f; font-weight: bold; padding-left: 1em;"></span>
							</div>
						</div>
					</div>
				</div>

				<div class="row-fluid uk-form-row">
					<div class="span12 uk-width-1-1">
						<div class="control-group uk-width-1-1">
							<div class="control-label uk-width-1-6"><?php echo $this->setLabel('form_movies_rtrate', 'COM_KA_SEARCH_ADV_MOVIES_RT_RATE'); ?></div>
							<div class="controls uk-width-1-2" style="padding-top: 4px;">
								<div class="span6">
									<div id="form_movies_rtrate" style="margin-top: 4px;"></div>
								</div>
								<span id="rtrate_val" style="color: #f6931f; font-weight: bold; padding-left: 1em;"></span>
							</div>
						</div>
					</div>
				</div>

				<div class="row-fluid uk-form-row">
					<div class="span12 uk-width-1-1">
						<div class="control-group uk-width-1-1">
							<div class="control-label uk-width-1-6"><?php echo $this->setLabel('form_movies_from_budget', 'COM_KA_BUDGET'); ?></div>
							<div class="controls uk-width-1-2">
								<?php echo JText::_('COM_KA_SEARCH_ADV_RANGE_FROM_LABEL'); ?> <?php echo JHTML::_('select.genericlist', $this->items->movies->from_budget, 'form[movies][from_budget]', array('class'=>'span4 uk-width-1-4'), 'value', 'text', '-', 'form_movies_from_budget'); ?>&nbsp;&nbsp;&nbsp;
								<?php echo JText::_('COM_KA_SEARCH_ADV_RANGE_TO_LABEL'); ?> <?php echo JHTML::_('select.genericlist', $this->items->movies->to_budget, 'form[movies][to_budget]', array('class'=>'span4 uk-width-1-4'), 'value', 'text', '-', 'form_movies_to_budget'); ?>
							</div>
						</div>
					</div>
				</div>
			</fieldset>

			<input type="hidden" name="option" value="com_kinoarhiv" />
			<input type="hidden" name="view" value="movies" />
			<input type="hidden" name="task" value="search" />
			<?php echo JHtml::_('form.token'); ?>
			<input type="submit" class="btn btn-primary uk-button uk-button-primary" value="<?php echo JText::_('JSEARCH_FILTER_SUBMIT'); ?>" />
			<input type="reset" class="btn btn-default uk-button cmd-reset" value="<?php echo JText::_('JSEARCH_FILTER_CLEAR'); ?>" />
		</form>
	</div>

	<div class="advsearch-names well uk-panel uk-panel-box">
		<form action="<?php echo JRoute::_('index.php');?>" id="form_names" method="post" autocomplete="off">
			<fieldset class="form-horizontal uk-form">
				<legend class="uk-panel-title"><?php echo JText::_('COM_KA_SEARCH_ADV_NAMES_TITLE'); ?></legend>

				<div class="row-fluid uk-form-row">
					<div class="span12 uk-width-1-1">
						<div class="control-group uk-width-1-1">
							<div class="control-label uk-width-1-6"><?php echo $this->setLabel('form_names_title', 'COM_KA_SEARCH_ADV_NAMES_TITLE_LABEL'); ?></div>
							<div class="controls uk-width-1-2"><input name="form[names][title]" type="text" id="form_names_title" class="span10 uk-width-1-1" value="" required /></div>
						</div>
					</div>
				</div>
			</fieldset>

			<input type="hidden" name="option" value="com_kinoarhiv" />
			<input type="hidden" name="view" value="names" />
			<input type="hidden" name="task" value="search" />
			<?php echo JHtml::_('form.token'); ?>
			<input type="submit" class="btn btn-primary uk-button uk-button-primary" value="<?php echo JText::_('JSEARCH_FILTER_SUBMIT'); ?>" />
			<input type="reset" class="btn btn-default uk-button cmd-reset" value="<?php echo JText::_('JSEARCH_FILTER_CLEAR'); ?>" />
		</form>
	</div>
</div>
