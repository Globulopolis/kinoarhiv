<?php
/**
 * @package     Kinoarhiv.Site
 * @subpackage  com_kinoarhiv
 *
 * @copyright   Copyright (C) 2018 Libra.ms. All rights reserved.
 * @license     GNU General Public License version 2 or later
 * @url         http://киноархив.com
 */

defined('_JEXEC') or die;

if ($this->params->get('search_names_enable') == 0)
{
	return;
}
?>
<script type="text/javascript">
	jQuery(document).ready(function($){
		$('.cmd-reset-names').click(function(){
			$('#form_names_title').select2('val', '');
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
						<div class="control-label uk-width-1-6"><?php echo $this->form->getLabel('name', 'names'); ?></div>
						<div class="controls uk-width-1-2"><?php echo $this->form->getInput('name', 'names'); ?></div>
					</div>
				</div>
			</div>
			<?php endif; ?>

			<?php if ($this->params->get('search_names_birthday') == 1 || $this->params->get('search_names_gender') == 1): ?>
			<div class="row-fluid uk-form-row">
				<div class="span12 uk-width-1-1">
					<div class="control-group uk-width-1-1">
						<div class="control-label uk-width-1-2"><?php echo $this->form->getLabel('birthday', 'names'); ?></div>
						<div class="controls uk-width-1-1">
							<?php if ($this->params->get('search_names_birthday') == 1): ?>
								<?php echo $this->form->getInput('birthday', 'names'); ?>
							<?php endif; ?>
							<?php if ($this->params->get('search_names_gender') == 1): ?>
							&nbsp;&nbsp;&nbsp;<?php echo JText::_('COM_KA_SEARCH_ADV_NAMES_GENDER_LABEL'); ?>&nbsp;&nbsp;<?php echo $this->form->getInput('gender', 'names'); ?>
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
						<div class="control-label uk-width-1-6"><?php echo $this->form->getLabel('title', 'names'); ?></div>
						<div class="controls uk-width-1-2"><?php echo $this->form->getInput('title', 'names'); ?></div>
					</div>
				</div>
			</div>
			<?php endif; ?>

			<?php if ($this->params->get('search_names_birthplace') == 1): ?>
			<div class="row-fluid uk-form-row">
				<div class="span12 uk-width-1-1">
					<div class="control-group uk-width-1-1">
						<div class="control-label uk-width-1-4"><?php echo $this->form->getLabel('birthplace', 'names'); ?></div>
						<div class="controls uk-width-1-2"><?php echo $this->form->getInput('birthplace', 'names'); ?></div>
					</div>
				</div>
			</div>
			<?php endif; ?>

			<?php if ($this->params->get('search_names_birthcountry') == 1): ?>
			<div class="row-fluid uk-form-row">
				<div class="span12 uk-width-1-1">
					<div class="control-group uk-width-1-1">
						<div class="control-label uk-width-1-4"><?php echo $this->form->getLabel('country', 'names'); ?></div>
						<div class="controls uk-width-1-2"><?php echo $this->form->getInput('country', 'names'); ?></div>
					</div>
				</div>
			</div>
			<?php endif; ?>

			<?php if ($this->params->get('search_names_amplua') == 1): ?>
			<div class="row-fluid uk-form-row">
				<div class="span12 uk-width-1-1">
					<div class="control-group uk-width-1-1">
						<div class="control-label uk-width-1-6"><?php echo $this->form->getLabel('amplua', 'names'); ?></div>
						<div class="controls uk-width-1-2"><?php echo $this->form->getInput('amplua', 'names'); ?></div>
					</div>
				</div>
			</div>
			<?php endif; ?>
		</fieldset>

		<input type="hidden" name="option" value="com_kinoarhiv" />
		<input type="hidden" name="task" value="search.results" />
		<input type="hidden" name="content" value="names" />
		<input type="hidden" name="m_itemid" value="<?php echo $this->home_itemid['names']; ?>" />
		<?php echo JHtml::_('form.token'); ?>
		<input type="submit" class="btn btn-primary uk-button uk-button-primary validate" value="<?php echo JText::_('JSEARCH_FILTER_SUBMIT'); ?>" />
		<input type="reset" class="btn uk-button cmd-reset-names" value="<?php echo JText::_('JSEARCH_FILTER_CLEAR'); ?>" />
	</form>
</div>
