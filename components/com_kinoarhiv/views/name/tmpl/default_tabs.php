<?php defined('_JEXEC') or die; ?>
<div class="tabs breadcrumb">
	<a href="<?php echo JRoute::_('index.php?option=com_kinoarhiv&view=name&id='.$this->item->id.'&Itemid='.$this->itemid); ?>" class="tab-about<?php echo ($this->page == '') ? ' current' : '';?>"><?php echo JText::_('COM_KA_NAMES_TAB_INFO'); ?></a>
	<a href="<?php echo JRoute::_('index.php?option=com_kinoarhiv&view=name&page=wallpapers&id='.$this->item->id.'&Itemid='.$this->itemid); ?>" class="tab-wallpp<?php echo ($this->page == 'wallpapers') ? ' current' : '';?>"><?php echo JText::_('COM_KA_NAMES_TAB_WALLPP'); ?></a>
	<a href="<?php echo JRoute::_('index.php?option=com_kinoarhiv&view=name&page=photos&id='.$this->item->id.'&Itemid='.$this->itemid); ?>" class="tab-photo<?php echo ($this->page == 'photos') ? ' current' : '';?>"><?php echo JText::_('COM_KA_NAMES_TAB_PHOTO'); ?></a>
	<a href="<?php echo JRoute::_('index.php?option=com_kinoarhiv&view=name&page=awards&id='.$this->item->id.'&Itemid='.$this->itemid); ?>" class="tab-awards<?php echo ($this->tpageab == 'awards') ? ' current' : '';?>"><?php echo JText::_('COM_KA_NAMES_TAB_AWARDS'); ?></a>
</div>
<div class="clear"></div>
