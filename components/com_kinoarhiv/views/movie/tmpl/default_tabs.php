<?php
/**
 * @package     Kinoarhiv.Site
 * @subpackage  com_kinoarhiv
 *
 * @copyright   Copyright (C) 2010 Libra.ms. All rights reserved.
 * @license     GNU General Public License version 2 or later
 * @url            http://киноархив.com/
 */

defined('_JEXEC') or die;
?>
<div class="tabs breadcrumb">
	<a href="<?php echo JRoute::_('index.php?option=com_kinoarhiv&view=movie&id=' . $this->item->id . '&Itemid=' . $this->itemid); ?>" class="tab-about<?php echo ($this->page == '') ? ' current uk-active' : ''; ?>"><?php echo JText::_('COM_KA_MOVIE_TAB_INFO'); ?></a>

	<?php if (($this->item->attribs->tab_movie_wallpp == '' && $this->params->get('tab_movie_wallpp') == 1) || $this->item->attribs->tab_movie_wallpp == 1): ?>
		<a href="<?php echo JRoute::_('index.php?option=com_kinoarhiv&view=movie&page=wallpapers&id=' . $this->item->id . '&Itemid=' . $this->itemid); ?>" class="tab-wallpp<?php echo ($this->page == 'wallpapers') ? ' current uk-active' : ''; ?>"><?php echo JText::_('COM_KA_MOVIE_TAB_WALLPP'); ?></a>
	<?php endif; ?>

	<?php if (($this->item->attribs->tab_movie_posters == '' && $this->params->get('tab_movie_posters') == 1) || $this->item->attribs->tab_movie_posters == 1): ?>
		<a href="<?php echo JRoute::_('index.php?option=com_kinoarhiv&view=movie&page=posters&id=' . $this->item->id . '&Itemid=' . $this->itemid); ?>" class="tab-posters<?php echo ($this->page == 'posters') ? ' current uk-active' : ''; ?>"><?php echo JText::_('COM_KA_MOVIE_TAB_POSTERS'); ?></a>
	<?php endif; ?>

	<?php if (($this->item->attribs->tab_movie_scr == '' && $this->params->get('tab_movie_scr') == 1) || $this->item->attribs->tab_movie_scr == 1): ?>
		<a href="<?php echo JRoute::_('index.php?option=com_kinoarhiv&view=movie&page=screenshots&id=' . $this->item->id . '&Itemid=' . $this->itemid); ?>" class="tab-screenshots<?php echo ($this->page == 'screenshots') ? ' current uk-active' : ''; ?>"><?php echo JText::_('COM_KA_MOVIE_TAB_SCRSHOTS'); ?></a>
	<?php endif; ?>

	<?php if (($this->item->attribs->tab_movie_awards == '' && $this->params->get('tab_movie_awards') == 1) || $this->item->attribs->tab_movie_awards == 1): ?>
		<a href="<?php echo JRoute::_('index.php?option=com_kinoarhiv&view=movie&page=awards&id=' . $this->item->id . '&Itemid=' . $this->itemid); ?>" class="tab-awards<?php echo ($this->page == 'awards') ? ' current uk-active' : ''; ?>"><?php echo JText::_('COM_KA_MOVIE_TAB_AWARDS'); ?></a>
	<?php endif; ?>

	<?php if (($this->item->attribs->tab_movie_tr == '' && $this->params->get('tab_movie_tr') == 1) || $this->item->attribs->tab_movie_tr == 1): ?>
		<a href="<?php echo JRoute::_('index.php?option=com_kinoarhiv&view=movie&page=trailers&id=' . $this->item->id . '&Itemid=' . $this->itemid); ?>" class="tab-trailers<?php echo ($this->page == 'trailers') ? ' current uk-active' : ''; ?>"><?php echo JText::_('COM_KA_MOVIE_TAB_TRAILERS'); ?></a>
	<?php endif; ?>

	<?php if (($this->item->attribs->tab_movie_snd == '' && $this->params->get('tab_movie_snd') == 1) || $this->item->attribs->tab_movie_snd == 1): ?>
		<a href="<?php echo JRoute::_('index.php?option=com_kinoarhiv&view=movie&page=soundtracks&id=' . $this->item->id . '&Itemid=' . $this->itemid); ?>" class="tab-sound<?php echo ($this->page == 'soundtracks') ? ' current uk-active' : ''; ?>"><?php echo JText::_('COM_KA_MOVIE_TAB_SOUND'); ?></a>
	<?php endif; ?>
</div>
<div class="clear"></div>
