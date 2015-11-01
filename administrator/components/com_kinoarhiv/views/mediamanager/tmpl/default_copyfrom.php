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

$input = JFactory::getApplication()->input;
JFormHelper::addFieldPath(JPATH_COMPONENT . '/models/fields');

if ($input->get('section', '', 'word') == 'movie')
{
	$field = JFormHelper::loadFieldType('movies');
	$element = 'movies';
	$title = 'COM_KA_MOVIES_GALLERY_COPYFROM_LABEL';
	$desc = 'COM_KA_MOVIES_GALLERY_COPYFROM_DESC';
}
elseif ($input->get('section', '', 'word') == 'name')
{
	$field = JFormHelper::loadFieldType('names');
	$element = 'names';
	$title = 'COM_KA_NAMES_GALLERY_COPYFROM_LABEL';
	$desc = 'COM_KA_NAMES_GALLERY_COPYFROM_DESC';
}
else
{
	echo 'Wrong \'section\' variable in request!';

	return;
}
?>
<script type="text/javascript" src="<?php echo JURI::base(); ?>components/com_kinoarhiv/assets/js/utils.js"></script>
<div class="row-fluid">
	<div class="span12">
		<form action="<?php echo JUri::base(); ?>index.php?option=com_kinoarhiv&controller=mediamanager&task=copyfrom&format=json" id="form_copyfrom">
			<fieldset class="form-horizontal copy">
				<div class="control-group">
					<div class="control-label">
						<?php
						echo $field->getLabel(
							'item_id', $title, $desc, array('required' => true)
						);
						?>
					</div>
					<div class="controls copy-from">
						<?php
						echo $field->getInput(
							'item_id', '', array(
								'class'            => 'span12 required',
								'data-ac-type'     => $element,
								'data-allow-clear' => true,
								'data-sel-size'    => 1,
								'data-ignore-ids'  => $input->get('id', 0, 'int')
							)
						);
						?>
					</div>
				</div>
				<div class="control-group">
					<div class="control-label">
						<label class="required" for="item_subtype"><?php echo JText::_('COM_KA_MOVIES_GALLERY_COPYFROM_ITEMTYPE_LABEL'); ?></label>
					</div>
					<div class="controls copy-from">
						<select name="item_subtype" id="item_subtype" class="span7 required">
							<?php if ($input->get('section', '', 'word') == 'movie'): ?>
								<option value="1"><?php echo JText::_('COM_KA_MOVIES_WALLPP'); ?></option>
								<option value="2"><?php echo JText::_('COM_KA_MOVIES_POSTERS'); ?></option>
								<option value="3"><?php echo JText::_('COM_KA_MOVIES_SCRSHOTS'); ?></option>
							<?php elseif ($input->get('section', '', 'word') == 'name'): ?>
								<option value="1"><?php echo JText::_('COM_KA_NAMES_GALLERY_WALLPP'); ?></option>
								<option value="2"><?php echo JText::_('COM_KA_NAMES_GALLERY_POSTERS'); ?></option>
								<option value="3"><?php echo JText::_('COM_KA_NAMES_GALLERY_PHOTO'); ?></option>
							<?php endif; ?>
						</select>
					</div>
				</div>
			</fieldset>
			<input type="hidden" name="item_type" id="item_type" value="<?php echo $input->get('item_type', '', 'word'); ?>"/>
			<input type="hidden" name="section" id="section" value="<?php echo $input->get('section', '', 'word'); ?>"/>
			<input type="hidden" name="id" id="id" value="<?php echo $input->get('id', 0, 'int'); ?>"/><!-- Parent movie/name ID -->
		</form>
	</div>
</div>
