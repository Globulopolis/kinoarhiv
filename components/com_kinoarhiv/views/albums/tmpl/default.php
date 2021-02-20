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

JHtml::_('script', 'media/com_kinoarhiv/js/jquery.lazyload.min.js');
?>
<div class="uk-article ka-content ka-albums">
	<?php if ($this->params->get('use_alphabet', 1)):
		echo JLayoutHelper::render(
			'layouts.navigation.album_alphabet',
			array('url' => 'index.php?option=com_kinoarhiv&view=albums&content=albums&Itemid=' . $this->albumsItemid, 'params' => $this->params),
			JPATH_COMPONENT
		);
	endif; ?>

	<?php if ($this->params->get('show_feed_link', 1)):
		$link = 'index.php?option=com_kinoarhiv&view=albums&format=feed&Itemid=' . $this->albumsItemid; ?>
		<div class="feed-link">
			<a href="<?php echo JRoute::_($link . '&type=rss'); ?>" title="RSS" rel="noindex">RSS</a>
			<a href="<?php echo JRoute::_($link . '&type=atom'); ?>" title="Atom" rel="noindex">Atom</a>
		</div>
	<?php endif; ?>

	<?php if (count($this->items) > 0):
		if ($this->params->get('search_albums_enable') && is_object($this->filtersData) && $this->filtersData->exists('albums')):
			$plural = $this->lang->getPluralSuffixes($this->pagination->total);
			echo '<br />' . JText::sprintf('COM_KA_SEARCH_MUSIC_N_RESULTS_' . $plural[0], $this->pagination->total);
		endif; ?>

		<?php if ($this->params->get('page_type', 'list') === 'artwork'): ?><br/><br/><?php endif; ?>

		<?php if ($this->params->get('pagevan_top')): ?>
		<div class="pagination top">
			<?php echo $this->pagination->getPagesLinks(); ?>
		</div>
		<?php endif;

		echo $this->loadTemplate($this->params->get('page_type', 'list'));
		echo JLayoutHelper::render('layouts.navigation.pagination',
			array('params' => $this->params, 'pagination' => $this->pagination),
			JPATH_COMPONENT
		);
	else: ?>
		<br/>
		<div><?php echo ($this->params->get('search_albums_enable') && $this->filtersData->exists('albums'))
			? JText::sprintf('COM_KA_SEARCH_ADV_N_RESULTS', 0)
			: KAComponentHelper::showMsg(JText::_('COM_KA_NO_ITEMS')); ?>
		</div>
	<?php endif; ?>
</div>
