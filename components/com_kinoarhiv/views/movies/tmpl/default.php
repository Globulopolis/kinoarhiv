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
<div class="uk-article ka-content">
	<?php if ($this->params->get('use_alphabet') == 1):
		echo JLayoutHelper::render(
			'layouts.navigation.movie_alphabet',
			array('url' => 'index.php?option=com_kinoarhiv&view=movies&content=movies&Itemid=' . $this->moviesItemid, 'params' => $this->params),
			JPATH_COMPONENT
		);
	endif; ?>

	<?php if ($this->params->get('show_feed_link', 1)):
		$link = 'index.php?option=com_kinoarhiv&view=movies&format=feed&Itemid=' . $this->moviesItemid; ?>
		<div class="feed-link">
			<a href="<?php echo JRoute::_($link . '&type=rss'); ?>" title="RSS" rel="noindex">RSS</a>
			<a href="<?php echo JRoute::_($link . '&type=atom'); ?>" title="Atom" rel="noindex">Atom</a>
		</div>
	<?php endif; ?>

	<?php if (count($this->items) > 0):
		if (property_exists($this, 'filtersData') && (is_object($this->filtersData) && $this->filtersData->exists('movies'))):
			$plural = $this->lang->getPluralSuffixes($this->pagination->total);
			echo '<br />' . JText::sprintf('COM_KA_SEARCH_VIDEO_N_RESULTS_' . $plural[0], $this->pagination->total);
		endif; ?>

		<?php if ($this->params->get('pagevan_top') == 1): ?>
		<div class="pagination top">
			<?php echo $this->pagination->getPagesLinks(); ?>
		</div>
		<?php endif;

		foreach ($this->items as $item):
			$title       = $this->escape(KAContentHelper::formatItemTitle($item->title, '', $item->year));
			$hasPremiere = !empty($item->premiere_date) ? 'premiere hasPremiere' : '';
			?>
			<article class="item" data-permalink="<?php echo $item->params->get('url'); ?>">
				<?php
				echo JLayoutHelper::render(
					'layouts.navigation.movie_item_header',
					array(
						'params' => $this->params,
						'item'   => $item,
						'itemid' => $this->moviesItemid,
						'guest'  => $this->user->get('guest'),
						'url'    => 'index.php?option=com_kinoarhiv&view=' . substr($this->view, 0, -1) . '&id=' . $item->id . '&Itemid=' . $this->itemid
					),
					JPATH_COMPONENT
				);
				?>
				<?php echo $item->event->afterDisplayTitle; ?>
				<?php echo $item->event->beforeDisplayContent; ?>
				<div class="clear"></div>
				<div class="content content-list clearfix">
					<div>
						<div class="poster">
							<a href="<?php echo JRoute::_('index.php?option=com_kinoarhiv&view=' . substr($this->view, 0, -1) . '&id=' . $item->id . '&Itemid=' . $this->itemid); ?>" title="<?php echo $title; ?>">
								<img data-original="<?php echo $item->poster->posterThumb; ?>" class="lazy"
									 alt="<?php echo JText::_('COM_KA_POSTER_ALT') . $this->escape($item->title); ?>"
									 width="<?php echo $item->poster->posterThumbWidth; ?>" height="<?php echo $item->poster->posterThumbHeight; ?>"/>
							</a>
						</div>
						<div class="introtext <?php echo $hasPremiere; ?>">
							<div class="text"><?php echo $item->text; ?></div>

							<?php if (!empty($item->plot)): ?>
							<div class="separator"></div>
							<div class="plot"><?php echo $item->plot; ?></div>
							<?php endif; ?>

							<?php if ($this->params->get('ratings_show_frontpage') == 1):
								echo JLayoutHelper::render(
									'layouts.content.ratings_movie',
									array('params' => $this->params, 'item' => $item),
									JPATH_COMPONENT
								);
							endif;

							if ($this->params->get('ratings_show_frontpage') == 1):
								echo JLayoutHelper::render('layouts.content.votes_movie',
									array(
										'params' => $this->params,
										'item'   => $item,
										'guest'  => $this->user->get('guest'),
										'itemid' => $this->itemid,
										'view'   => $this->view
									),
									JPATH_COMPONENT
								);
							endif; ?>
						</div>
						<?php
						echo JLayoutHelper::render('layouts.content.premiere_date',
							array('item' => $item, 'itemid' => $this->itemid),
							JPATH_COMPONENT
						);
						?>
					</div>
					<div class="links">
						<?php
						echo JLayoutHelper::render('layouts.content.readmore',
							array(
								'link'   => JRoute::_('index.php?option=com_kinoarhiv&view=' . substr($this->view, 0, -1) . '&id=' . $item->id . '&Itemid=' . $this->itemid),
								'item'   => $item,
								'params' => $this->params,
								'lang'   => $this->lang
							),
							JPATH_COMPONENT
						);
						?>
					</div>
				</div>
			</article>
			<?php echo $item->event->afterDisplayContent; ?>
		<?php endforeach; ?>

		<?php
		echo JLayoutHelper::render('layouts.navigation.pagination',
			array('params' => $this->params, 'pagination' => $this->pagination),
			JPATH_COMPONENT
		);
	else: ?>
		<br/>
		<div><?php echo (property_exists($this, 'filtersData') && $this->params->get('search_movies_enable') && $this->filtersData->exists('movies')) ? JText::sprintf('COM_KA_SEARCH_ADV_N_RESULTS', 0) : KAComponentHelper::showMsg(JText::_('COM_KA_NO_ITEMS')); ?></div>
	<?php endif; ?>
</div>
