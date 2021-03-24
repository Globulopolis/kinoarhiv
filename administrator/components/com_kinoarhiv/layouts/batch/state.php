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
<label id="batch-state-lbl" for="batch-state" class="modalTooltip"
	   title="<?php echo JText::_('JFIELD_PUBLISHED_DESC'); ?>">
	<?php echo JText::_('JSTATUS'); ?></label>
<?php echo JHtml::_(
	'select.genericlist',
	array(
		array('text' => JText::_('JLIB_HTML_BATCH_NOCHANGE'), 'value' => ''),
		array('text' => JText::_('JPUBLISHED'), 'value' => 1),
		array('text' => JText::_('JUNPUBLISHED'), 'value' => 0),
		array('text' => JText::_('JARCHIVED'), 'value' => 2),
		array('text' => JText::_('JTRASHED'), 'value' => -2)
	),
	'batch[state]', '', 'value', 'text', '', 'batch-state'
);
