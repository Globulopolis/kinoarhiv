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
?>
<label id="batch-type-lbl" for="batch-type" class="modalTooltip"
	   title="<?php echo JText::_('JLIB_HTML_BATCH_GALLERY_TYPE'); ?>">
	<?php echo JText::_('JLIB_HTML_BATCH_GALLERY_TYPE'); ?></label>
<?php echo JHtml::_(
	'select.genericlist',
	array(
		array('text' => JText::_('JLIB_HTML_BATCH_NOCHANGE'), 'value' => ''),
		array('text' => 'Front', 'value' => 1),
		array('text' => 'Back', 'value' => 2),
		array('text' => 'Artist', 'value' => 3),
		array('text' => 'Disc', 'value' => 4),
	),
	'batch[type_id]', '', 'value', 'text', '', 'batch-type-id'
);
