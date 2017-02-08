<?php
/**
 * @package     Kinoarhiv.Administrator
 * @subpackage  com_kinoarhiv
 *
 * @copyright   Copyright (C) 2010 Libra.ms. All rights reserved.
 * @license     GNU General Public License version 2 or later
 * @url            http://киноархив.com/
 */

defined('_JEXEC') or die;

if ($this->section != 'movie' && $this->section != 'name')
{
	echo 'Wrong \'section\' variable in request!';

	return;
}
?>
<div class="row-fluid">
	<div class="span12">
		<fieldset class="form-horizontal copy">
			<div class="control-group">
				<div class="control-label">
					<label id="from_id-lbl" for="from_id"><?php echo JText::_('COM_KA_' . strtoupper($this->section) . 'S_GALLERY_COPYFROM_LABEL'); ?><span class="star">&nbsp;*</span></label>
				</div>
				<div class="controls copy-from">
					<input type="hidden" name="from_id" value="" id="from_id" class="hasAutocomplete span12 required" data-allow-clear="true" data-placeholder="<?php echo JText::_('JGLOBAL_SELECT_AN_OPTION'); ?>" data-minimum-input-length="1" data-lang="*" data-content="<?php echo $this->section . 's'; ?>" data-key="id" data-remote="true" required="required" />
				</div>
			</div>
			<div class="control-group">
				<div class="control-label">
					<label class="required" for="from_tab"><?php echo JText::_('COM_KA_MOVIES_GALLERY_COPYFROM_ITEMTYPE_LABEL'); ?></label>
				</div>
				<div class="controls copy-from">
					<select name="from_tab" id="from_tab" class="span6 required">
						<?php if ($this->section == 'movie'): ?>
							<option value="1"><?php echo JText::_('COM_KA_MOVIES_WALLPP'); ?></option>
							<option value="2"><?php echo JText::_('COM_KA_MOVIES_POSTERS'); ?></option>
							<option value="3"><?php echo JText::_('COM_KA_MOVIES_SCRSHOTS'); ?></option>
						<?php elseif ($this->section == 'name'): ?>
							<option value="1"><?php echo JText::_('COM_KA_NAMES_GALLERY_WALLPP'); ?></option>
							<option value="2"><?php echo JText::_('COM_KA_NAMES_GALLERY_POSTERS'); ?></option>
							<option value="3"><?php echo JText::_('COM_KA_NAMES_GALLERY_PHOTO'); ?></option>
						<?php endif; ?>
					</select>
				</div>
			</div>
		</fieldset>
	</div>
</div>
