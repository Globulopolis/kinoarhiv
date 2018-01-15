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
?>
<div class="tabs breadcrumb">
	<a href="<?php echo JRoute::_('index.php?option=com_kinoarhiv&view=name&id=' . $this->item->id . '&Itemid=' . $this->itemid); ?>" class="tab-about<?php echo ($this->page == '') ? ' current uk-active' : ''; ?>"><?php echo JText::_('COM_KA_NAMES_TAB_INFO'); ?></a>

	<?php if (($this->item->attribs->tab_name_wallpp == '' && $this->params->get('tab_name_wallpp') == 1) || $this->item->attribs->tab_name_wallpp == 1): ?>
		<a href="<?php echo JRoute::_('index.php?option=com_kinoarhiv&view=name&page=wallpapers&id=' . $this->item->id . '&Itemid=' . $this->itemid); ?>" class="tab-wallpp<?php echo ($this->page == 'wallpapers') ? ' current uk-active' : ''; ?>"><?php echo JText::_('COM_KA_NAMES_TAB_WALLPAPERS'); ?></a>
	<?php endif; ?>

	<?php if (($this->item->attribs->tab_name_photos == '' && $this->params->get('tab_name_photos') == 1) || $this->item->attribs->tab_name_photos == 1): ?>
		<a href="<?php echo JRoute::_('index.php?option=com_kinoarhiv&view=name&page=photos&id=' . $this->item->id . '&Itemid=' . $this->itemid); ?>" class="tab-photo<?php echo ($this->page == 'photos') ? ' current uk-active' : ''; ?>"><?php echo JText::_('COM_KA_NAMES_TAB_PHOTOS'); ?></a>
	<?php endif; ?>

	<?php if (($this->item->attribs->tab_name_awards == '' && $this->params->get('tab_name_awards') == 1) || $this->item->attribs->tab_name_awards == 1): ?>
		<a href="<?php echo JRoute::_('index.php?option=com_kinoarhiv&view=name&page=awards&id=' . $this->item->id . '&Itemid=' . $this->itemid); ?>" class="tab-awards<?php echo ($this->page == 'awards') ? ' current uk-active' : ''; ?>"><?php echo JText::_('COM_KA_NAMES_TAB_AWARDS'); ?></a>
	<?php endif; ?>
</div>
<div class="clear"></div>
