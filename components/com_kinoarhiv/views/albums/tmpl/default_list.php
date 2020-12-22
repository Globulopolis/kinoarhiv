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

foreach ($this->items as $item):
	$item->composer = (!empty($item->name) || !empty($item->latin_name))
		? KAContentHelper::formatItemTitle($item->name, $item->latin_name) : $item->composer;
	$item->composer = !empty($item->composer) ? $item->composer . ' - ' : '';
	$title = $this->escape(KAContentHelper::formatItemTitle($item->composer . $item->title, '', $item->year)); ?>

	<article class="item" data-permalink="<?php echo $item->params->get('url'); ?>">
		<?php
		echo JLayoutHelper::render(
			'layouts.navigation.album_item_header',
			array(
				'params' => $this->params,
				'item'   => $item,
				'itemid' => $this->itemid,
				'guest'  => $this->user->get('guest'),
				'url'    => 'index.php?option=com_kinoarhiv&view=album&id=' . $item->id . '&Itemid=' . $this->itemid
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
					<a href="<?php echo JRoute::_('index.php?option=com_kinoarhiv&view=album&id=' . $item->id . '&Itemid=' . $this->itemid); ?>"
					   title="<?php echo $title; ?>">
						<img data-original="<?php echo $item->cover; ?>" class="lazy"
							 alt="<?php echo JText::_('COM_KA_ARTWORK_ALT') . $this->escape($item->title); ?>"
							 width="<?php echo $item->coverWidth; ?>" height="<?php echo $item->coverHeight; ?>" />
					</a>
				</div>
				<div class="introtext">
					<?php if ($this->params->get('ratings_show_frontpage') == 1):
						echo JLayoutHelper::render('layouts.content.votes_album',
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
			</div>
			<div class="links">
				<?php
				echo JLayoutHelper::render('layouts.content.readmore',
					array(
						'link'   => JRoute::_('index.php?option=com_kinoarhiv&view=album&id=' . $item->id . '&Itemid=' . $this->itemid),
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
	<?php echo $item->event->afterDisplayContent;
endforeach;
