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

// TODO Refactor
JHtml::_('bootstrap.loadcss');
JHtml::_('stylesheet', 'media/com_kinoarhiv/css/colorbox.css');
JHtml::_('script', 'media/com_kinoarhiv/js/jquery.colorbox.min.js');
KAComponentHelper::getScriptLanguage('jquery.colorbox-', 'media/com_kinoarhiv/js/i18n/colorbox');
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
			<?php if (!empty($this->item->albums)): ?>
			<ul class="media-list">

			<?php foreach ($this->item->albums as $album):
					$composer = KAContentHelper::formatItemTitle($album->name, $album->latin_name);
			?>

				<li class="media">
					<a class="pull-left album-art poster" href="<?php echo $album->cover; ?>"><img src="<?php echo $album->cover; ?>" class="media-object" width="<?php echo $album->coverWidth; ?>" height="<?php echo $album->coverHeight; ?>" /></a>
					<div class="media-body">
						<h3 class="media-heading album-title">
							<a href="<?php echo JRoute::_('index.php?option=com_kinoarhiv&view=album&id=' . $album->id . '&Itemid=' . $this->albumsItemid); ?>"><?php echo $this->escape($album->title); ?></a>
						</h3>
						<span class="album-info">
							<?php if (!empty($composer)): ?>
							<span class="album-composer"><?php echo $composer; ?></span>
							<?php endif; ?>
							<?php if (!empty($album->year) && $album->year != '0000'): ?>
							<span class="album-year">(<?php echo $album->year; ?>)</span>
							<?php endif; ?>
						</span>

						<?php
						/*echo JLayoutHelper::render('layouts.content.votes_album',
							array(
								'params' => $this->params,
								'item'   => $album,
								'guest'  => $this->user->get('guest'),
								'itemid' => $this->itemid
							),
							JPATH_COMPONENT
						);*/
						?>

						<?php
						/*echo JLayoutHelper::render('layouts.content.tracklist',
							array(
								'tracks'  => $this->item->tracks,
								'guest'   => $this->user->get('guest'),
								'albumID' => $album->id
							),
							JPATH_COMPONENT
						);*/
						?>
					</div>
				</li>
			<?php endforeach; ?>
			</ul>
			<?php else: ?>
				<div><?php echo KAComponentHelper::showMsg(JText::_('COM_KA_NO_ITEMS')); ?></div>
			<?php endif; ?>
		</div>
	</article>
	<?php echo $this->item->event->afterDisplayContent; ?>
</div>
