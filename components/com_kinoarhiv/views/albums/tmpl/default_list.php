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
		<header>
			<h1 class="uk-article-title title title-small">
				<?php if ($item->attribs->link_titles === ''): ?>
					<?php if ($this->params->get('link_titles') == 1): ?>
						<a href="<?php echo $item->params->get('url'); ?>" class="brand" title="<?php echo $title; ?>"><?php echo $title; ?></a>
					<?php else: ?>
						<span class="brand"><?php echo $title; ?></span>
					<?php endif; ?>
				<?php elseif ($item->attribs->link_titles == 1): ?>
					<a href="<?php echo $item->params->get('url'); ?>" class="brand" title="<?php echo $title; ?>"><?php echo $title; ?></a>
				<?php elseif ($item->attribs->link_titles == 0): ?>
					<span class="brand"><?php echo $title; ?></span>
				<?php endif; ?>
			</h1>

			<div class="middle-nav clearfix">
				<p class="meta">
					<?php if ($item->attribs->show_author === '' && !empty($item->username)): ?>
						<?php if ($this->params->get('show_author') == 1): ?>
							<span class="icon-user"></span> <?php echo JText::_('JAUTHOR'); ?>: <?php echo $item->username; ?>
							<br/>
						<?php endif; ?>
					<?php elseif ($item->attribs->show_author == 1 && !empty($item->username)): ?>
						<span class="icon-user"></span> <?php echo JText::_('JAUTHOR'); ?>: <?php echo $item->username; ?>
						<br/>
					<?php endif; ?>

					<?php if ($item->attribs->show_create_date === ''): ?>
						<?php if ($this->params->get('show_pubdate') == 1): ?>
							<span class="icon-calendar"></span> <?php echo JText::_('COM_KA_CREATED_DATE_ON'); ?>
							<time itemprop="dateCreated" datetime="<?php echo JHtml::_('date', $item->created, 'c'); ?>"><?php echo JHtml::_('date', $item->created, JText::_('DATE_FORMAT_LC3')); ?></time>
						<?php endif; ?>
					<?php elseif ($item->attribs->show_create_date == 1): ?>
						<span class="icon-calendar"></span> <?php echo JText::_('COM_KA_CREATED_DATE_ON'); ?>
						<time itemprop="dateCreated" datetime="<?php echo JHtml::_('date', $item->created, 'c'); ?>"><?php echo JHtml::_('date', $item->created, JText::_('DATE_FORMAT_LC3')); ?></time>
					<?php endif; ?>

					<?php
					if ((
							($item->attribs->show_create_date === '' && $this->params->get('show_pubdate') == 1) || $item->attribs->show_create_date == 1
						) && (
							($item->attribs->show_modify_date === '' && $this->params->get('show_moddate') == 1) || $item->attribs->show_modify_date == 1
						)
					):
						echo ' &bull; ';
					endif; ?>

					<?php if ($item->attribs->show_modify_date === ''): ?>
						<?php if ($this->params->get('show_moddate') == 1): ?>
							<?php echo JText::_('COM_KA_LAST_UPDATED'); ?>
							<time itemprop="dateModified" datetime="<?php echo JHtml::_('date', $item->modified, 'c'); ?>"><?php echo JHtml::_('date', $item->modified, JText::_('DATE_FORMAT_LC3')); ?></time>
						<?php endif; ?>
					<?php elseif ($item->attribs->show_modify_date == 1): ?>
						<?php echo JText::_('COM_KA_LAST_UPDATED'); ?>
						<time itemprop="dateModified" datetime="<?php echo JHtml::_('date', $item->modified, 'c'); ?>"><?php echo JHtml::_('date', $item->modified, JText::_('DATE_FORMAT_LC3')); ?></time>
					<?php endif; ?>
				</p>
				<?php if (!$this->user->guest && $this->params->get('link_favorite') == 1): ?>
					<p class="favorite">
						<?php if ($item->favorite == 1): ?>
							<a href="<?php echo JRoute::_('index.php?option=com_kinoarhiv&view=albums&task=albums.favorite&action=delete&Itemid=' . $this->itemid . '&id=' . $item->id); ?>" class="cmd-favorite delete" data-ka-msg-place="div"><?php echo JText::_('COM_KA_REMOVEFROM_FAVORITE'); ?></a>
						<?php else: ?>
							<a href="<?php echo JRoute::_('index.php?option=com_kinoarhiv&view=albums&task=albums.favorite&action=add&Itemid=' . $this->itemid . '&id=' . $item->id); ?>" class="cmd-favorite add" data-ka-msg-place="div"><?php echo JText::_('COM_KA_ADDTO_FAVORITE'); ?></a>
						<?php endif; ?>
					</p>
				<?php endif; ?>
			</div>
		</header>
		<?php echo $item->event->afterDisplayTitle; ?>
		<?php echo $item->event->beforeDisplayContent; ?>
		<div class="clear"></div>
		<div class="content content-list clearfix">
			<div>
				<div class="poster span3">
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
				<a href="<?php echo JRoute::_('index.php?option=com_kinoarhiv&view=album&id=' . $item->id . '&Itemid=' . $this->itemid); ?>"
				   class="btn btn-default uk-button readmore-link hasTooltip" title="<?php echo $title; ?>"><?php echo JText::_('COM_KA_READMORE'); ?>
					<span class="icon-chevron-right"></span></a>
			</div>
		</div>
	</article>
	<?php echo $item->event->afterDisplayContent;
endforeach;
