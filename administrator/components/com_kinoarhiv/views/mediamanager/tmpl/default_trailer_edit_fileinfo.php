<?php
/**
 * @package     Kinoarhiv.Administrator
 * @subpackage  com_kinoarhiv
 *
 * @copyright   Copyright (C) 2018 Libra.ms. All rights reserved.
 * @license     GNU General Public License version 2 or later
 * @url         http://киноархив.com
 */

defined('_JEXEC') or die;

$input   = JFactory::getApplication()->input;
$list    = $input->getWord('type', '');
$id      = $input->getInt('id', 0);
$item_id = $input->getInt('item_id', 0);
$item    = $input->getInt('item', null);
$is_new  = $input->getInt('new', 0);
?>
<form action="<?php echo JRoute::_('index.php?option=com_kinoarhiv'); ?>" method="post" name="adminFormFile"
	  id="fileinfo-item-form" class="form-validate container-fluid">
	<div class="form-horizontal">
		<?php foreach ($this->form->getFieldset('fileinfo_' . $list) as $field): ?>
			<div class="control-group">
				<div class="control-label"><?php echo $field->label; ?></div>
				<div class="controls">
					<?php echo $field->input; ?>

					<?php if (($field->fieldname == 'src' || $field->fieldname == 'file') && $is_new === 1): ?>
						<p><?php echo JText::sprintf('COM_KA_TRAILERS_HEADING_VIDEOS_DATA_EDIT_NEW', $this->path); ?></p>
					<?php endif; ?>
				</div>
			</div>
		<?php endforeach; ?>
	</div>

	<input type="hidden" name="list" value="<?php echo $list; ?>"/>
	<input type="hidden" name="id" value="<?php echo $id; ?>"/>
	<input type="hidden" name="item_id" value="<?php echo $item_id; ?>"/>
	<input type="hidden" name="item" value="<?php echo $item; ?>"/>
	<input type="hidden" name="new" value="<?php echo $is_new; ?>"/>
	<?php echo JHtml::_('form.token'); ?>
</form>
