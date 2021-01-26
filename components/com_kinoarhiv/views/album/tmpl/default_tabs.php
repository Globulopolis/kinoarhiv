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
	<a href="<?php echo JRoute::_('index.php?option=com_kinoarhiv&view=album&id=' . $this->item->id . '&Itemid=' . $this->itemid); ?>"
	   class="tab-about<?php echo ($this->page == '') ? ' current uk-active' : ''; ?>"><?php echo JText::_('COM_KA_MOVIE_TAB_INFO'); ?></a>

<?php if (($this->item->attribs->tab_album_posters === '' && $this->params->get('tab_album_posters') == 1) || $this->item->attribs->tab_album_posters == 1): ?>
	<a href="<?php echo JRoute::_('index.php?option=com_kinoarhiv&view=album&page=posters&id=' . $this->item->id . '&Itemid=' . $this->itemid); ?>"
	   class="tab-posters<?php echo ($this->page == 'posters') ? ' current uk-active' : ''; ?>"><?php echo JText::_('COM_KA_MOVIE_TAB_POSTERS'); ?></a>
<?php endif; ?>

<?php if (($this->item->attribs->tab_album_awards === '' && $this->params->get('tab_album_awards') == 1) || $this->item->attribs->tab_album_awards == 1): ?>
	<a href="<?php echo JRoute::_('index.php?option=com_kinoarhiv&view=album&page=awards&id=' . $this->item->id . '&Itemid=' . $this->itemid); ?>"
	   class="tab-awards<?php echo ($this->page == 'awards') ? ' current uk-active' : ''; ?>"><?php echo JText::_('COM_KA_MOVIE_TAB_AWARDS'); ?></a>
<?php endif; ?>
</div>
<div class="clear"></div>
