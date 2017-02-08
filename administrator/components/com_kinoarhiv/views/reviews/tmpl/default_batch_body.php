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

$batch_types = array(
	'' => JText::_('COM_KA_REVIEWS_TYPE_BATCH_NONE'),
	0  => JText::_('COM_KA_REVIEWS_TYPE_0'),
	1  => JText::_('COM_KA_REVIEWS_TYPE_1'),
	2  => JText::_('COM_KA_REVIEWS_TYPE_2'),
	3  => JText::_('COM_KA_REVIEWS_TYPE_3')
);
?>
<div class="row-fluid">
	<div class="control-group span6">
		<div class="controls">
			<label id="batch-type-lbl" for="batch-type"><?php echo JText::_('COM_KA_REVIEWS_FIELD_TYPE_TITLE'); ?></label>
			<?php echo JHtml::_('select.genericlist', $batch_types, 'batch[type]', null, 'value', 'text', '', 'batch-type'); ?>
		</div>
	</div>
	<div class="control-group span6">
		<div class="controls">
			<?php echo JHtml::_('batch.user'); ?>
		</div>
	</div>
</div>
