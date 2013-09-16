<?php defined('_JEXEC') or die; ?>
<div class="tabs breadcrumb">
	<a href="<?php echo JRoute::_('index.php?option=com_kinoarhiv&view=name&id='.$this->item->id.'&Itemid='.$this->itemid); ?>" class="tab-about<?php echo ($this->tab == '') ? ' current' : '';?>"><?php echo JText::_('COM_KA_NAMES_TAB_INFO'); ?></a>
	<a href="<?php echo JRoute::_('index.php?option=com_kinoarhiv&view=name&tab=wallpp&id='.$this->item->id.'&Itemid='.$this->itemid); ?>" class="tab-wallpp<?php echo ($this->tab == 'wallpp') ? ' current' : '';?>"><?php echo JText::_('COM_KA_NAMES_TAB_WALLPP'); ?></a>
	<a href="<?php echo JRoute::_('index.php?option=com_kinoarhiv&view=name&tab=photo&id='.$this->item->id.'&Itemid='.$this->itemid); ?>" class="tab-photo<?php echo ($this->tab == 'photo') ? ' current' : '';?>"><?php echo JText::_('COM_KA_NAMES_TAB_PHOTO'); ?></a>
	<a href="<?php echo JRoute::_('index.php?option=com_kinoarhiv&view=name&tab=awards&id='.$this->item->id.'&Itemid='.$this->itemid); ?>" class="tab-awards<?php echo ($this->tab == 'awards') ? ' current' : '';?>"><?php echo JText::_('COM_KA_NAMES_TAB_AWARDS'); ?></a>
</div>
<div class="clear"></div>
