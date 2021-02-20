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

JHtml::_('bootstrap.loadcss');
JHtml::_('stylesheet', 'media/com_kinoarhiv/css/colorbox.css');
JHtml::_('script', 'media/com_kinoarhiv/js/jquery.colorbox.min.js');
KAComponentHelper::getScriptLanguage('jquery.colorbox-', 'media/com_kinoarhiv/js/i18n/colorbox');
JHtml::_('script', 'media/com_kinoarhiv/js/jquery.lazyload.min.js');
JHtml::_('script', 'media/com_kinoarhiv/js/jquery.rateit.min.js');
?>
<div class="ka-content">
	<?php if ($this->params->get('use_alphabet') == 1):
		echo JLayoutHelper::render(
			'layouts.navigation.movie_alphabet',
			array('url' => 'index.php?option=com_kinoarhiv&view=movies&content=movies&Itemid=' . $this->moviesItemid, 'params' => $this->params),
			JPATH_COMPONENT
		);
	endif; ?>

	<article class="uk-article item">
		<?php
		echo JLayoutHelper::render(
			'layouts.navigation.movie_item_header',
			array(
				'params' => $this->params,
				'item'   => $this->item,
				'itemid' => $this->itemid,
				'guest'  => $this->user->get('guest'),
				'url'    => 'index.php?option=com_kinoarhiv&view=movie&id=' . $this->item->id . '&Itemid=' . $this->itemid
			),
			JPATH_COMPONENT
		);
		?>
		<?php echo $this->item->event->afterDisplayTitle; ?>
		<?php echo $this->loadTemplate('tabs'); ?>
		<?php echo $this->item->event->beforeDisplayContent; ?>

		<div class="snd-list">
		<?php if (!empty($this->items)): ?>
			<?php foreach ($this->items as $album):

				echo JLayoutHelper::render(
					'layouts.navigation.album_item_header',
					array(
						'params' => $this->params,
						'item'   => $album,
						'itemid' => $this->albumsItemid,
						'guest'  => $this->user->get('guest'),
						'url'    => 'index.php?option=com_kinoarhiv&view=album&id=' . $album->id . '&Itemid=' . $this->albumsItemid,
						'meta'   => false
					),
					JPATH_COMPONENT
				);
				?>

			<div class="clear"></div>
			<div class="content content-list clearfix">
				<div>
					<div class="poster">
						<a href="<?php echo JRoute::_('index.php?option=com_kinoarhiv&view=album&id=' . $album->id . '&Itemid=' . $this->albumsItemid); ?>"
						   title="<?php echo $this->escape($album->title); ?>">
							<img data-original="<?php echo $album->cover; ?>" class="lazy"
								 alt="<?php echo JText::_('COM_KA_ARTWORK_ALT') . $this->escape($album->title); ?>"
								 width="<?php echo $album->coverWidth; ?>" height="<?php echo $album->coverHeight; ?>" />
						</a>
					</div>
					<div class="introtext">
						<div class="text"><?php echo $album->text; ?></div>

						<?php if ($this->params->get('ratings_show_frontpage') == 1):
							echo JLayoutHelper::render('layouts.content.votes_album',
								array(
									'params' => $this->params,
									'item'   => $album,
									'guest'  => $this->user->get('guest'),
									'itemid' => $this->itemid,
									'view'   => 'movie'
								),
								JPATH_COMPONENT
							);
						endif; ?>

						<?php
						echo JLayoutHelper::render('layouts.content.tracklist',
							array(
								'params' => $this->params,
								'item'   => $album,
								'guest'  => $this->user->get('guest')
							),
							JPATH_COMPONENT
						);
						?>
					</div>
				</div>
			</div>

			<?php endforeach; ?>
		<?php else: ?>
			<div><?php echo KAComponentHelper::showMsg(JText::_('COM_KA_NO_ITEMS')); ?></div>
		<?php endif; ?>
		</div>
	</article>
	<?php echo $this->item->event->afterDisplayContent; ?>
</div>
