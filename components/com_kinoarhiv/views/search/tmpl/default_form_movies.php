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

if ($this->params->get('search_movies_enable') == 0)
{
	return;
}
?>
<script type="text/javascript">
	jQuery(document).ready(function($){
		document.formvalidator.setHandler('date', function(value){
			var matches = /^(\d{4})[-\/](\d{2})[-\/](\d{2})$/.exec(value),
				year, month, day, composed_date;

			if (matches == null) {
				// Check for 4 digits e.g. 2012
				var regex = /^\d{4}$/;

				if (regex.test(value)) {
					if (value > 1800 && value < 2100) {
						return true;
					}
				} else {
					// Check for date e.g. 2012-01
					matches = /^(\d{4})[-\/](\d{2})$/.exec(value);

					if (matches !== null) {
						year = matches[1];
						month = matches[2] - 1;
						composed_date = new Date(year, month);

						if (composed_date.getFullYear() == year && composed_date.getMonth() == month) {
							if (composed_date.getFullYear() > 1800 && composed_date.getFullYear() < 2100) {
								return true;
							}
						}
					}
				}
			} else {
				// Check for mySql date e.g. 2012-01-01
				year = matches[1];
				month = matches[2] - 1;
				day = matches[3];
				composed_date = new Date(year, month, day);

				if (composed_date.getFullYear() == year && composed_date.getMonth() == month && composed_date.getDate() == day) {
					if (composed_date.getFullYear() > 1800 && composed_date.getFullYear() < 2100) {
						return true;
					}
				}
			}

			return false;
		});

		$('.cmd-reset-movies').click(function(){
			$('#movie_country, #movie_vendor, #movie_genre, #movie_cast, #movie_tags').select2('val', '');
			$('.hasSlider').slider('refresh');
		});
	});
</script>
<div class="advsearch-movies<?php echo (JFactory::getApplication()->input->get('task', '', 'cmd') != 'movies') ? ' well uk-panel uk-panel-box' : ''; ?>">
	<form action="<?php echo JRoute::_('index.php?option=com_kinoarhiv&view=search'); ?>" id="filters_movies" method="post" autocomplete="off" class="form-validate">
		<fieldset class="form-horizontal uk-form">
			<legend class="uk-panel-title"><?php echo JText::_('COM_KA_SEARCH_ADV_MOVIES_TITLE'); ?></legend>

			<?php if ($this->params->get('search_movies_title') == 1): ?>
			<div class="row-fluid uk-form-row">
				<div class="span12 uk-width-1-1">
					<div class="control-group uk-width-1-1">
						<div class="control-label uk-width-1-6"><?php echo $this->form->getLabel('title', 'movie'); ?></div>
						<div class="controls uk-width-1-2"><?php echo $this->form->getInput('title', 'movie'); ?></div>
					</div>
				</div>
			</div>
			<?php endif; ?>

			<?php if ($this->params->get('search_movies_year') == 1): ?>
			<div class="row-fluid uk-form-row">
				<div class="span12 uk-width-1-1">
					<div class="control-group uk-width-1-1">
						<div class="control-label uk-width-1-6"><?php echo $this->form->getLabel('year', 'movie'); ?></div>
						<div class="controls uk-width-1-2"><?php echo $this->form->getInput('year', 'movie'); ?></div>
					</div>
				</div>
			</div>
			<?php endif; ?>

			<?php if ($this->params->get('search_movies_year_range') == 1): ?>
			<div class="row-fluid uk-form-row">
				<div class="span12 uk-width-1-3">
					<div class="control-group uk-width-1-1">
						<div class="control-label uk-width-1-2"><?php echo $this->form->getLabel('year_range', 'movie'); ?></div>
						<div class="controls uk-width-1-3"><?php echo $this->form->getInput('year_range', 'movie'); ?></div>
					</div>
				</div>
			</div>
			<?php endif; ?>

			<?php if ($this->params->get('search_movies_country') == 1): ?>
			<div class="row-fluid uk-form-row">
				<div class="span12 uk-width-1-1">
					<div class="control-group uk-width-1-1">
						<div class="control-label uk-width-1-6"><?php echo $this->form->getLabel('country', 'movie'); ?></div>
						<div class="controls uk-width-1-2"><?php echo $this->form->getInput('country', 'movie'); ?></div>
					</div>
				</div>
			</div>
			<?php endif; ?>

			<?php if ($this->params->get('search_movies_cast') == 1): ?>
			<div class="row-fluid uk-form-row">
				<div class="span12 uk-width-1-1">
					<div class="control-group uk-width-1-1">
						<div class="control-label uk-width-1-6"><?php echo $this->form->getLabel('cast', 'movie'); ?></div>
						<div class="controls uk-width-1-2"><?php echo $this->form->getInput('cast', 'movie'); ?></div>
					</div>
				</div>
			</div>
			<?php endif; ?>

			<?php if ($this->params->get('search_movies_vendor') == 1): ?>
			<div class="row-fluid uk-form-row">
				<div class="span12 uk-width-1-1">
					<div class="control-group uk-width-1-1">
						<div class="control-label uk-width-1-6"><?php echo $this->form->getLabel('vendor', 'movie'); ?></div>
						<div class="controls uk-width-1-2"><?php echo $this->form->getInput('vendor', 'movie'); ?></div>
					</div>
				</div>
			</div>
			<?php endif; ?>

			<?php if ($this->params->get('search_movies_genre') == 1): ?>
			<div class="row-fluid uk-form-row">
				<div class="span12 uk-width-1-1">
					<div class="control-group uk-width-1-1">
						<div class="control-label uk-width-1-6"><?php echo $this->form->getLabel('genre', 'movie'); ?></div>
						<div class="controls uk-width-1-2"><?php echo $this->form->getInput('genre', 'movie'); ?></div>
					</div>
				</div>
			</div>
			<?php endif; ?>

			<?php if ($this->params->get('search_movies_mpaa') == 1 || $this->params->get('search_movies_age_restrict') == 1): ?>
			<div class="row-fluid uk-form-row">
				<?php if ($this->params->get('search_movies_mpaa') == 1): ?>
				<div class="span5 uk-width-1-1">
					<div class="control-group uk-width-1-1">
						<div class="control-label uk-width-1-6"><?php echo $this->form->getLabel('mpaa', 'movie'); ?></div>
						<div class="controls uk-width-1-2"><?php echo $this->form->getInput('mpaa', 'movie'); ?></div>
					</div>
				</div>
				<?php endif; ?>
				<?php if ($this->params->get('search_movies_age_restrict') == 1): ?>
				<div class="span7 uk-width-1-1">
					<div class="control-group uk-width-1-1">
						<div class="control-label uk-width-1-6"><?php echo $this->form->getLabel('age_restrict', 'movie'); ?></div>
						<div class="controls uk-width-1-2"><?php echo $this->form->getInput('age_restrict', 'movie'); ?></div>
					</div>
				</div>
				<?php endif; ?>
			</div>
			<?php endif; ?>

			<?php if ($this->params->get('search_movies_ua_rate') == 1): ?>
			<div class="row-fluid uk-form-row">
				<div class="span12 uk-width-1-1">
					<div class="control-group uk-width-1-1">
						<div class="control-label uk-width-1-3"><?php echo $this->form->getLabel('ua_rate', 'movie'); ?></div>
						<div class="controls uk-width-1-2"><?php echo $this->form->getInput('ua_rate', 'movie'); ?></div>
					</div>
				</div>
			</div>
			<?php endif; ?>

			<?php if ($this->params->get('search_movies_rate') == 1): ?>
			<div class="row-fluid uk-form-row">
				<div class="span12 uk-width-1-1">
					<div class="control-group uk-width-1-1">
						<div class="control-label uk-width-1-6"><?php echo $this->form->getLabel('rate_min', 'movie'); ?></div>
						<div class="controls uk-width-1-2" style="padding-top: 4px;">
							<div class="span3 rate-input">
								<?php echo $this->form->getInput('rate_min', 'movie'); ?> - <?php echo $this->form->getInput('rate_max', 'movie'); ?>
							</div>
							<div class="span6" style="padding-top: 0.2em;"><?php echo $this->form->getInput('rate_slider', 'movie'); ?></div>
						</div>
					</div>
				</div>
			</div>
			<?php endif; ?>

			<?php if ($this->params->get('search_movies_imdbrate') == 1): ?>
			<div class="row-fluid uk-form-row">
				<div class="span12 uk-width-1-1">
					<div class="control-group uk-width-1-1">
						<div class="control-label uk-width-1-6"><?php echo $this->form->getLabel('imdb_rate_min', 'movie'); ?></div>
						<div class="controls uk-width-1-2" style="padding-top: 4px;">
							<div class="span3 rate-input">
								<?php echo $this->form->getInput('imdb_rate_min', 'movie'); ?> - <?php echo $this->form->getInput('imdb_rate_max', 'movie'); ?>
							</div>
							<div class="span6" style="padding-top: 0.2em;"><?php echo $this->form->getInput('imdb_rate_slider', 'movie'); ?></div>
						</div>
					</div>
				</div>
			</div>
			<?php endif; ?>

			<?php if ($this->params->get('search_movies_kprate') == 1): ?>
			<div class="row-fluid uk-form-row">
				<div class="span12 uk-width-1-1">
					<div class="control-group uk-width-1-1">
						<div class="control-label uk-width-1-6"><?php echo $this->form->getLabel('kp_rate_min', 'movie'); ?></div>
						<div class="controls uk-width-1-2" style="padding-top: 4px;">
							<div class="span3 rate-input">
								<?php echo $this->form->getInput('kp_rate_min', 'movie'); ?> - <?php echo $this->form->getInput('kp_rate_max', 'movie'); ?>
							</div>
							<div class="span6" style="padding-top: 0.2em;"><?php echo $this->form->getInput('kp_rate_slider', 'movie'); ?></div>
						</div>
					</div>
				</div>
			</div>
			<?php endif; ?>

			<?php if ($this->params->get('search_movies_rtrate') == 1): ?>
			<div class="row-fluid uk-form-row">
				<div class="span12 uk-width-1-1">
					<div class="control-group uk-width-1-1">
						<div class="control-label uk-width-1-6"><?php echo $this->form->getLabel('rt_rate_min', 'movie'); ?></div>
						<div class="controls uk-width-1-2" style="padding-top: 4px;">
							<div class="span3 rate-input">
								<?php echo $this->form->getInput('rt_rate_min', 'movie'); ?> - <?php echo $this->form->getInput('rt_rate_max', 'movie'); ?>
							</div>
							<div class="span6" style="padding-top: 0.2em;"><?php echo $this->form->getInput('rt_rate_slider', 'movie'); ?></div>
						</div>
					</div>
				</div>
			</div>
			<?php endif; ?>

			<?php if ($this->params->get('search_movies_metacritic') == 1): ?>
			<div class="row-fluid uk-form-row">
				<div class="span12 uk-width-1-1">
					<div class="control-group uk-width-1-1">
						<div class="control-label uk-width-1-6"><?php echo $this->form->getLabel('mc_rate_min', 'movie'); ?></div>
						<div class="controls uk-width-1-2" style="padding-top: 4px;">
							<div class="span3 rate-input">
								<?php echo $this->form->getInput('mc_rate_min', 'movie'); ?> - <?php echo $this->form->getInput('mc_rate_max', 'movie'); ?>
							</div>
							<div class="span6" style="padding-top: 0.2em;"><?php echo $this->form->getInput('mc_rate_slider', 'movie'); ?></div>
						</div>
					</div>
				</div>
			</div>
			<?php endif; ?>

			<?php if ($this->params->get('search_movies_budget') == 1): ?>
			<div class="row-fluid uk-form-row">
				<div class="span12 uk-width-1-1">
					<div class="control-group uk-width-1-1">
						<div class="control-label uk-width-1-6"><?php echo $this->form->getLabel('budget', 'movie'); ?></div>
						<div class="controls uk-width-1-2"><?php echo $this->form->getInput('budget', 'movie'); ?></div>
					</div>
				</div>
			</div>
			<?php endif; ?>

			<?php if ($this->params->get('search_movies_premiere') == 1): ?>
				<div class="row-fluid uk-form-row">
					<div class="span12 uk-width-1-1">
						<div class="control-group uk-width-1-1">
							<div class="control-label uk-width-1-6"><label id="movie_premiere_country-lbl" for="movie_premiere_country"><?php echo JText::_('COM_KA_SEARCH_ADV_MOVIES_PREMIERE'); ?></label></div>
							<div class="controls uk-width-1-2">
								<?php echo $this->form->getInput('premiere_country', 'movie'); ?>&nbsp;&nbsp;&nbsp;<?php echo $this->form->getInput('premiere_date', 'movie'); ?>
							</div>
						</div>
					</div>
				</div>
			<?php endif; ?>

			<?php if ($this->params->get('search_movies_release') == 1): ?>
				<div class="row-fluid uk-form-row">
					<div class="span12 uk-width-1-1">
						<div class="control-group uk-width-1-1">
							<div class="control-label uk-width-1-6"><label id="movie_release_country-lbl" for="movie_release_country"><?php echo JText::_('COM_KA_SEARCH_ADV_MOVIES_RELEASE'); ?></label></div>
							<div class="controls uk-width-1-2">
								<?php echo $this->form->getInput('release_country', 'movie'); ?>&nbsp;&nbsp;&nbsp;<?php echo $this->form->getInput('release_date', 'movie'); ?>
							</div>
						</div>
					</div>
				</div>
			<?php endif; ?>

			<?php if ($this->params->get('search_movies_tags') == 1): ?>
			<div class="row-fluid uk-form-row">
				<div class="span12 uk-width-1-1">
					<div class="control-group uk-width-1-1">
						<div class="control-label uk-width-1-6"><?php echo $this->form->getLabel('tags', 'movie'); ?></div>
						<div class="controls uk-width-1-2"><?php echo $this->form->getInput('tags', 'movie'); ?></div>
					</div>
				</div>
			</div>
			<?php endif; ?>
		</fieldset>

		<input type="hidden" name="option" value="com_kinoarhiv" />
		<input type="hidden" name="task" value="search.results" />
		<input type="hidden" name="content" value="movie" />
		<input type="hidden" name="Itemid" value="<?php echo $this->home_itemid['movies']; ?>" />
		<?php echo JHtml::_('form.token'); ?>
		<input type="submit" class="btn btn-primary uk-button uk-button-primary validate" value="<?php echo JText::_('JSEARCH_FILTER_SUBMIT'); ?>" />
		<input type="reset" class="btn uk-button cmd-reset-movies" value="<?php echo JText::_('JSEARCH_FILTER_CLEAR'); ?>" />
	</form>
</div>
