<?php
/**
 * @package     Kinoarhiv.Administrator
 * @subpackage  com_kinoarhiv
 *  
 * @copyright   Copyright (C) 2017 Libra.ms. All rights reserved.
 * @license     GNU General Public License version 2 or later
 * @url         http://киноархив.com
 */

defined('_JEXEC') or die;
?>
<fieldset class="form-horizontal">
	<legend><?php echo JText::_('COM_KA_SETTINGS_AP_AB_LABEL'); ?></legend>
	<div class="control-group">
		<div class="control-label"><?php echo $this->form->getLabel('use_alphabet'); ?></div>
		<div class="controls"><?php echo $this->form->getInput('use_alphabet'); ?></div>
	</div>

	<?php echo $this->form->getLabel('alphabet_movies'); ?>

	<div class="movie-ab">
	<?php if (count($this->data->params->get('movie_alphabet')) == 0): ?>
		<div class="row-fluid">
			<div class="span4">
				<div class="control-group">
					<div class="control-label"><label><?php echo JText::_('COM_KA_SETTINGS_AP_AB_LANG_LABEL'); ?></label></div>
					<div class="controls"><input type="text" name="letters[movie][lang][]" value="" class="letters-lang span12" /></div>
				</div>
			</div>
			<div class="span8">
				<div class="control-group">
					<div class="control-label"><label class="hasTooltip" title="<?php echo JText::_('COM_KA_SETTINGS_AP_AB_LETTERS_DESC'); ?>"><?php echo JText::_('COM_KA_SETTINGS_AP_AB_LETTERS_LABEL'); ?></label></div>
					<div class="controls">
						<div class="btn-group span10">
							<input type="text" name="letters[movie][letters][]" value="" class="letters span12" />
							<button type="button" class="btn btn-success cmd-abm-new-row"><span class="icon-plus"></span></button>
							<button type="button" class="btn btn-danger cmd-abm-remove-row"><span class="icon-minus"></span></button>
						</div>
					</div>
				</div>
			</div>
		</div>
	<?php else:
		foreach ($this->data->params->get('movie_alphabet') as $letters): ?>
		<div class="row-fluid">
			<div class="span4">
				<div class="control-group">
					<div class="control-label"><label><?php echo JText::_('COM_KA_SETTINGS_AP_AB_LANG_LABEL'); ?></label></div>
					<div class="controls"><input type="text" name="letters[movie][lang][]" value="<?php echo $letters->lang; ?>" class="letters-lang span12" /></div>
				</div>
			</div>
			<div class="span8">
				<div class="control-group">
					<div class="control-label"><label class="hasTooltip" title="<?php echo JText::_('COM_KA_SETTINGS_AP_AB_LETTERS_DESC'); ?>"><?php echo JText::_('COM_KA_SETTINGS_AP_AB_LETTERS_LABEL'); ?></label></div>
					<div class="controls">
						<div class="btn-group span10">
							<input type="text" name="letters[movie][letters][]" value="<?php echo implode(',', $letters->letters); ?>" class="letters span12" />
							<button type="button" class="btn btn-success cmd-abm-new-row"><span class="icon-plus"></span></button>
							<button type="button" class="btn btn-danger cmd-abm-remove-row"><span class="icon-minus"></span></button>
						</div>
					</div>
				</div>
			</div>
		</div>
		<?php endforeach;
	endif; ?>
	</div>

	<?php echo $this->form->getLabel('alphabet_names'); ?>

	<div class="name-ab">
	<?php if (count($this->data->params->get('name_alphabet')) == 0): ?>
		<div class="row-fluid">
			<div class="span4">
				<div class="control-group">
					<div class="control-label"><label><?php echo JText::_('COM_KA_SETTINGS_AP_AB_LANG_LABEL'); ?></label></div>
					<div class="controls"><input type="text" name="letters[name][lang][]" value="" class="letters-lang span12" /></div>
				</div>
			</div>
			<div class="span8">
				<div class="control-group">
					<div class="control-label"><label class="hasTooltip" title="<?php echo JText::_('COM_KA_SETTINGS_AP_AB_LETTERS_DESC'); ?>"><?php echo JText::_('COM_KA_SETTINGS_AP_AB_LETTERS_LABEL'); ?></label></div>
					<div class="controls">
						<div class="btn-group span10">
							<input type="text" name="letters[name][letters][]" value="" class="letters span12" />
							<button type="button" class="btn btn-success cmd-abn-new-row"><span class="icon-plus"></span></button>
							<button type="button" class="btn btn-danger cmd-abn-remove-row"><span class="icon-minus"></span></button>
						</div>
					</div>
				</div>
			</div>
		</div>
	<?php else:
		foreach ($this->data->params->get('name_alphabet') as $letters): ?>
		<div class="row-fluid">
			<div class="span4">
				<div class="control-group">
					<div class="control-label"><label><?php echo JText::_('COM_KA_SETTINGS_AP_AB_LANG_LABEL'); ?></label></div>
					<div class="controls"><input type="text" name="letters[name][lang][]" value="<?php echo $letters->lang; ?>" class="letters-lang span12" /></div>
				</div>
			</div>
			<div class="span8">
				<div class="control-group">
					<div class="control-label"><label class="hasTooltip" title="<?php echo JText::_('COM_KA_SETTINGS_AP_AB_LETTERS_DESC'); ?>"><?php echo JText::_('COM_KA_SETTINGS_AP_AB_LETTERS_LABEL'); ?></label></div>
					<div class="controls">
						<div class="btn-group span10">
							<input type="text" name="letters[name][letters][]" value="<?php echo implode(',', $letters->letters); ?>" class="letters span12" />
							<button type="button" class="btn btn-success cmd-abn-new-row"><span class="icon-plus"></span></button>
							<button type="button" class="btn btn-danger cmd-abn-remove-row"><span class="icon-minus"></span></button>
						</div>
					</div>
				</div>
			</div>
		</div>
		<?php endforeach;
	endif; ?>
	</div>
</fieldset>
