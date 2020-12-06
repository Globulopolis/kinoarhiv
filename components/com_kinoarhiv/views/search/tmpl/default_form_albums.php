<?php
/**
 * @package     Kinoarhiv.Site
 * @subpackage  com_kinoarhiv
 *
 * @copyright   Copyright (C) 2018 Libra.ms. All rights reserved.
 * @license     GNU General Public License version 2 or later
 * @url         http://киноархив.com
 */
// TODO Поиск по альбомам, трекам
defined('_JEXEC') or die;

if (!$this->params->get('search_albums_enable'))
{
	return;
}
?>
<script type="text/javascript">
	jQuery(document).ready(function($){
		$('.cmd-reset-albums').click(function(){
			$('#form_albums_title').select2('val', '');
		});
	});
</script>
<div class="advsearch-albums">
	<form action="<?php echo JRoute::_('index.php?option=com_kinoarhiv&view=albums', false); ?>"
		  id="filters_albums" method="post" autocomplete="off" class="form-validate">
		<fieldset class="form-horizontal uk-form">
			<?php if ($this->params->get('search_albums_title') == 1): ?>
			<div class="row-fluid uk-form-row">
				<div class="span12 uk-width-1-1">
					<div class="control-group uk-width-1-1">
						<div class="control-label uk-width-1-6"><?php echo $this->form->getLabel('title', 'albums'); ?></div>
						<div class="controls uk-width-1-2"><?php echo $this->form->getInput('title', 'albums'); ?></div>
					</div>
				</div>
			</div>
			<?php endif; ?>

			<?php if ($this->params->get('search_albums_year') == 1): ?>
				<div class="row-fluid uk-form-row">
					<div class="span12 uk-width-1-1">
						<div class="control-group uk-width-1-1">
							<div class="control-label uk-width-1-6"><?php echo $this->form->getLabel('year', 'albums'); ?></div>
							<div class="controls uk-width-1-2"><?php echo $this->form->getInput('year', 'albums'); ?></div>
						</div>
					</div>
				</div>
			<?php endif; ?>

			<?php if ($this->params->get('search_albums_year_range') == 1): ?>
				<div class="row-fluid uk-form-row">
					<div class="span12 uk-width-1-3">
						<div class="control-group uk-width-1-1">
							<div class="control-label uk-width-1-2"><?php echo $this->form->getLabel('year_range', 'albums'); ?></div>
							<div class="controls uk-width-1-3"><?php echo $this->form->getInput('year_range', 'albums'); ?></div>
						</div>
					</div>
				</div>
			<?php endif; ?>
		</fieldset>

		<input type="hidden" name="option" value="com_kinoarhiv" />
		<input type="hidden" name="task" value="search.results" />
		<input type="hidden" name="content" value="albums" />
		<input type="hidden" name="menu" value="<?php echo $this->homeItemid['albums']; ?>" />
		<?php echo JHtml::_('form.token'); ?>
		<input type="submit" class="btn btn-primary uk-button uk-button-primary validate" value="<?php echo JText::_('JSEARCH_FILTER_SUBMIT'); ?>" />
		<input type="reset" class="btn uk-button cmd-reset-albums" value="<?php echo JText::_('JSEARCH_FILTER_CLEAR'); ?>" />
	</form>
</div>