<?php defined('_JEXEC') or die; ?>
<?php if ($this->params->get('tab_movie_wallpp') || $this->params->get('tab_movie_posters') || $this->params->get('tab_movie_scr') || $this->params->get('tab_movie_awards') || $this->params->get('tab_movie_tr') || $this->params->get('tab_movie_snd')): ?>
	<div class="tabs">
		<a href="<?php echo JRoute::_('index.php?option=com_kinoarhiv&view=movie&id='.$this->item->id.'&Itemid='.$this->itemid); ?>" class="tab-about<?php echo ($this->page == '') ? ' active' : ''; ?>"><?php echo JText::_('COM_KA_MOVIE_TAB_INFO'); ?></a>

		<?php if ($this->params->get('tab_movie_wallpp')): ?>
		<a href="<?php echo JRoute::_('index.php?option=com_kinoarhiv&view=movie&page=wallpapers&id='.$this->item->id.'&Itemid='.$this->itemid); ?>" class="tab-wallpp<?php echo ($this->page == 'wallpapers') ? ' active' : ''; ?>"><?php echo JText::_('COM_KA_MOVIE_TAB_WALLPP'); ?></a>
		<?php endif; ?>

		<?php if ($this->params->get('tab_movie_posters')): ?>
		<a href="<?php echo JRoute::_('index.php?option=com_kinoarhiv&view=movie&page=posters&id='.$this->item->id.'&Itemid='.$this->itemid); ?>" class="tab-posters<?php echo ($this->page == 'posters') ? ' active' : ''; ?>"><?php echo JText::_('COM_KA_MOVIE_TAB_POSTERS'); ?></a>
		<?php endif; ?>

		<?php if ($this->params->get('tab_movie_scr')): ?>
		<a href="<?php echo JRoute::_('index.php?option=com_kinoarhiv&view=movie&page=screenshots&id='.$this->item->id.'&Itemid='.$this->itemid); ?>" class="tab-screenshots<?php echo ($this->page == 'screenshots') ? ' active' : ''; ?>"><?php echo JText::_('COM_KA_MOVIE_TAB_SCRSHOTS'); ?></a>
		<?php endif; ?>

		<?php if ($this->params->get('tab_movie_awards')): ?>
		<a href="<?php echo JRoute::_('index.php?option=com_kinoarhiv&view=movie&page=awards&id='.$this->item->id.'&Itemid='.$this->itemid); ?>" class="tab-awards<?php echo ($this->page == 'awards') ? ' active' : ''; ?>"><?php echo JText::_('COM_KA_MOVIE_TAB_AWARDS'); ?></a>
		<?php endif; ?>

		<?php if ($this->params->get('tab_movie_tr')): ?>
		<a href="<?php echo JRoute::_('index.php?option=com_kinoarhiv&view=movie&page=trailers&id='.$this->item->id.'&Itemid='.$this->itemid); ?>" class="tab-trailers<?php echo ($this->page == 'trailers') ? ' active' : ''; ?>"><?php echo JText::_('COM_KA_MOVIE_TAB_TRAILERS'); ?></a>
		<?php endif; ?>

		<?php if ($this->params->get('tab_movie_snd')): ?>
		<a href="<?php echo JRoute::_('index.php?option=com_kinoarhiv&view=movie&page=soundtracks&id='.$this->item->id.'&Itemid='.$this->itemid); ?>" class="tab-sound<?php echo ($this->page == 'soundtracks') ? ' active' : ''; ?>"><?php echo JText::_('COM_KA_MOVIE_TAB_SOUND'); ?></a>
		<?php endif; ?>
	</div>
	<div class="clear"></div>
<?php endif; ?>
