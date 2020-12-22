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
$params = $displayData['params'];
$item   = $displayData['item'];
$page   = $displayData['page'];
$view   = JFactory::getApplication()->input->getWord('view', '');
?>
<div class="tabs breadcrumb">
	<?php if ($view === 'name'): ?>
	<a href="<?php echo JRoute::_('index.php?option=com_kinoarhiv&view=name&id=' . $item->id); ?>"
	   class="tab-about<?php echo ($page == '') ? ' current uk-active' : ''; ?>"><?php echo JText::_('COM_KA_NAMES_TAB_INFO'); ?></a>
	<?php endif; ?>

	<?php if (($item->attribs->tab_name_wallpp == '' && $params->get('tab_name_wallpp') == 1) || $item->attribs->tab_name_wallpp == 1): ?>
		<a href="<?php echo JRoute::_('index.php?option=com_kinoarhiv&view=name&page=wallpapers&id=' . $item->id); ?>"
		   class="tab-wallpp<?php echo ($page == 'wallpapers') ? ' current uk-active' : ''; ?>"><?php echo JText::_('COM_KA_NAMES_TAB_WALLPAPERS'); ?></a>
	<?php endif; ?>

	<?php if (($item->attribs->tab_name_photos == '' && $params->get('tab_name_photos') == 1) || $item->attribs->tab_name_photos == 1): ?>
		<a href="<?php echo JRoute::_('index.php?option=com_kinoarhiv&view=name&page=photos&id=' . $item->id); ?>"
		   class="tab-photo<?php echo ($page == 'photos') ? ' current uk-active' : ''; ?>"><?php echo JText::_('COM_KA_NAMES_TAB_PHOTOS'); ?></a>
	<?php endif; ?>

	<?php if (($item->attribs->tab_name_awards == '' && $params->get('tab_name_awards') == 1) || $item->attribs->tab_name_awards == 1): ?>
		<a href="<?php echo JRoute::_('index.php?option=com_kinoarhiv&view=name&page=awards&id=' . $item->id); ?>"
		   class="tab-awards<?php echo ($page == 'awards') ? ' current uk-active' : ''; ?>"><?php echo JText::_('COM_KA_NAMES_TAB_AWARDS'); ?></a>
	<?php endif; ?>
</div>
<div class="clear"></div>
