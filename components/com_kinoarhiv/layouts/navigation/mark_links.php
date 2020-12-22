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

/** @var array $displayData */
$params     = $displayData['params'];
$item       = $displayData['item'];
$itemid     = $displayData['itemid'];
$controller = $displayData['controller'];
$view       = JFactory::getApplication()->input->getWord('view');
$msgPlaceAt = isset($displayData['msgPlaceAt']) ? (string) $displayData['msgPlaceAt'] : '.mark-links';

if (empty($view))
{
	return;
}

if (!$displayData['guest']): ?>
	<div class="mark-links">
		<?php if ($params->get('link_watched') == 1 && property_exists($item, 'watched')): ?>
			<div class="watched">
				<?php if ($item->watched == 1): ?>
					<a href="<?php echo JRoute::_('index.php?option=com_kinoarhiv&view=' . $view . '&task=' . $controller . '.watched&action=delete&Itemid=' . $itemid . '&id=' . $item->id); ?>"
					   class="cmd-watched delete"
					   data-ka-msg-place="<?php echo $msgPlaceAt; ?>"><?php echo JText::_('COM_KA_REMOVEFROM_WATCHED'); ?></a>
				<?php else: ?>
					<a href="<?php echo JRoute::_('index.php?option=com_kinoarhiv&view=' . $view . '&task=' . $controller . '.watched&action=add&Itemid=' . $itemid . '&id=' . $item->id); ?>"
					   class="cmd-watched add" data-ka-msg-place="<?php echo $msgPlaceAt; ?>"><?php echo JText::_('COM_KA_ADDTO_WATCHED'); ?></a>
				<?php endif; ?>
			</div>
		<?php endif;

		if ($params->get('link_favorite') == 1 && property_exists($item, 'favorite')): ?>
			<div class="favorite">
				<?php if ($item->favorite == 1): ?>
					<a href="<?php echo JRoute::_('index.php?option=com_kinoarhiv&view=' . $view . '&task=' . $controller . '.favorite&action=delete&Itemid=' . $itemid . '&id=' . $item->id); ?>"
					   class="cmd-favorite delete"
					   data-ka-msg-place="<?php echo $msgPlaceAt; ?>"><?php echo JText::_('COM_KA_REMOVEFROM_FAVORITE'); ?></a>
				<?php else: ?>
					<a href="<?php echo JRoute::_('index.php?option=com_kinoarhiv&view=' . $view . '&task=' . $controller . '.favorite&action=add&Itemid=' . $itemid . '&id=' . $item->id); ?>"
					   class="cmd-favorite add" data-ka-msg-place="<?php echo $msgPlaceAt; ?>"><?php echo JText::_('COM_KA_ADDTO_FAVORITE'); ?></a>
				<?php endif; ?>
			</div>
		<?php endif; ?>
	</div>
<?php endif;
