<?php defined('_JEXEC') or die; ?>
<div class="tabs breadcrumb">
	<a href="<?php echo JRoute::_('index.php?option=com_kinoarhiv&view=movie&id='.$this->item->id.'&Itemid='.$this->itemid); ?>" class="tab-about<?php echo ($this->tab == '') ? ' current' : ''; ?>"><?php echo JText::_('COM_KA_MOVIE_TAB_INFO'); ?></a>
	<a href="<?php echo JRoute::_('index.php?option=com_kinoarhiv&view=movie&tab=wallpp&id='.$this->item->id.'&Itemid='.$this->itemid); ?>" class="tab-wallpp<?php echo ($this->tab == 'wallpp') ? ' current' : ''; ?>"><?php echo JText::_('COM_KA_MOVIE_TAB_WALLPP'); ?></a>
	<a href="<?php echo JRoute::_('index.php?option=com_kinoarhiv&view=movie&tab=posters&id='.$this->item->id.'&Itemid='.$this->itemid); ?>" class="tab-posters<?php echo ($this->tab == 'posters') ? ' current' : ''; ?>"><?php echo JText::_('COM_KA_MOVIE_TAB_POSTERS'); ?></a>
	<a href="<?php echo JRoute::_('index.php?option=com_kinoarhiv&view=movie&tab=screenshots&id='.$this->item->id.'&Itemid='.$this->itemid); ?>" class="tab-screenshots<?php echo ($this->tab == 'screenshots') ? ' current' : ''; ?>"><?php echo JText::_('COM_KA_MOVIE_TAB_SCRSHOTS'); ?></a>
	<a href="<?php echo JRoute::_('index.php?option=com_kinoarhiv&view=movie&tab=awards&id='.$this->item->id.'&Itemid='.$this->itemid); ?>" class="tab-awards<?php echo ($this->tab == 'awards') ? ' current' : ''; ?>"><?php echo JText::_('COM_KA_MOVIE_TAB_AWARDS'); ?></a>
	<a href="<?php echo JRoute::_('index.php?option=com_kinoarhiv&view=movie&tab=tr&id='.$this->item->id.'&Itemid='.$this->itemid); ?>" class="tab-trailers<?php echo ($this->tab == 'tr') ? ' current' : ''; ?>"><?php echo JText::_('COM_KA_MOVIE_TAB_TRAILERS'); ?></a>
	<a href="<?php echo JRoute::_('index.php?option=com_kinoarhiv&view=movie&tab=sound&id='.$this->item->id.'&Itemid='.$this->itemid); ?>" class="tab-sound<?php echo ($this->tab == 'sound') ? ' current' : ''; ?>"><?php echo JText::_('COM_KA_MOVIE_TAB_SOUND'); ?></a>
</div>
<div class="clear"></div>
