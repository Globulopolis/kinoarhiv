<?php
/**
 * @package     Kinoarhiv.Site
 * @subpackage  com_kinoarhiv
 *  
 * @copyright   Copyright (C) 2017 Libra.ms. All rights reserved.
 * @license     GNU General Public License version 2 or later
 * @url         http://киноархив.com
 */

defined('_JEXEC') or die;
?>
<div class="tabs breadcrumb">
	<a href="<?php echo JRoute::_('index.php?option=com_kinoarhiv&view=profile&page=reviews&Itemid=' . $this->itemid); ?>" class="tab-reviews<?php echo ($this->tab == 'reviews') ? ' current uk-active' : ''; ?>"><?php echo JText::_('COM_KA_REVIEWS'); ?></a>
	<a href="<?php echo JRoute::_('index.php?option=com_kinoarhiv&view=profile&page=favorite&Itemid=' . $this->itemid); ?>" class="tab-favorite<?php echo ($this->tab == 'favorite') ? ' current uk-active' : ''; ?>"><?php echo JText::_('COM_KA_FAVORITE'); ?></a>
	<a href="<?php echo JRoute::_('index.php?option=com_kinoarhiv&view=profile&page=watched&Itemid=' . $this->itemid); ?>" class="tab-watched<?php echo ($this->tab == 'watched') ? ' current uk-active' : ''; ?>"><?php echo JText::_('COM_KA_WATCHED'); ?></a>
	<a href="<?php echo JRoute::_('index.php?option=com_kinoarhiv&view=profile&page=votes&Itemid=' . $this->itemid); ?>" class="tab-votes<?php echo ($this->tab == 'votes') ? ' current uk-active' : ''; ?>"><?php echo JText::_('COM_KA_PROFILE_VOTES'); ?></a>
</div>
<div class="clear"></div>
