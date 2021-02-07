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

/** @var array $displayData */
$item     = $displayData['item'];
$showMeta = !isset($displayData['meta']);
$params   = $displayData['params'];
$itemid   = $displayData['itemid'];
$title    = $this->escape(KAContentHelper::formatItemTitle($item->title, '', $item->year));
?>
<header>
	<h1 class="uk-article-title title" itemprop="name">
		<?php if ($item->attribs->link_titles === ''): ?>
			<?php if ($params->get('link_titles') == 1): ?>
				<a href="<?php echo JRoute::_($displayData['url']); ?>" class="brand" title="<?php echo $title; ?>"><?php echo $title; ?></a>
			<?php else: ?>
				<span class="brand"><?php echo $title; ?></span>
			<?php endif; ?>
		<?php elseif ($item->attribs->link_titles == 1): ?>
			<a href="<?php echo JRoute::_($displayData['url']); ?>" class="brand" title="<?php echo $title; ?>"><?php echo $title; ?></a>
		<?php elseif ($item->attribs->link_titles == 0): ?>
			<span class="brand"><?php echo $title; ?></span>
		<?php endif; ?>
	</h1>
</header>
<div class="middle-nav clearfix">
	<?php if ($showMeta): ?>
	<p class="meta">
		<?php if ($item->attribs->show_author === '' && !empty($item->username)): ?>
			<?php if ($params->get('show_author') == 1): ?>
				<span class="icon-user"></span> <?php echo JText::_('JAUTHOR'); ?>: <?php echo $item->username; ?>
				<br/>
			<?php endif; ?>
		<?php elseif ($item->attribs->show_author == 1 && !empty($item->username)): ?>
			<span class="icon-user"></span> <?php echo JText::_('JAUTHOR'); ?>: <?php echo $item->username; ?>
			<br/>
		<?php endif; ?>

		<?php if ($item->attribs->show_create_date === ''): ?>
			<?php if ($params->get('show_pubdate') == 1): ?>
				<span class="icon-calendar"></span> <?php echo JText::_('COM_KA_CREATED_DATE_ON'); ?>
				<time itemprop="dateCreated" datetime="<?php echo JHtml::_('date', $item->created, 'c'); ?>"><?php echo JHtml::_('date', $item->created, JText::_('DATE_FORMAT_LC3')); ?></time>
			<?php endif; ?>
		<?php elseif ($item->attribs->show_create_date == 1): ?>
			<span class="icon-calendar"></span> <?php echo JText::_('COM_KA_CREATED_DATE_ON'); ?>
			<time itemprop="dateCreated" datetime="<?php echo JHtml::_('date', $item->created, 'c'); ?>"><?php echo JHtml::_('date', $item->created, JText::_('DATE_FORMAT_LC3')); ?></time>
		<?php endif; ?>

		<?php
		if (
			(($item->attribs->show_create_date === '' && $params->get('show_pubdate') == 1) || $item->attribs->show_create_date == 1)
			&& (($item->attribs->show_modify_date === '' && $params->get('show_moddate') == 1) || $item->attribs->show_modify_date == 1)
		):
			echo ' &bull; ';
		endif; ?>

		<?php if ($item->attribs->show_modify_date === ''): ?>
			<?php if ($params->get('show_moddate') == 1): ?>
				<?php echo JText::_('COM_KA_LAST_UPDATED'); ?>
				<time itemprop="dateModified" datetime="<?php echo JHtml::_('date', $item->modified, 'c'); ?>"><?php echo JHtml::_('date', $item->modified, JText::_('DATE_FORMAT_LC3')); ?></time>
			<?php endif; ?>
		<?php elseif ($item->attribs->show_modify_date == 1): ?>
			<?php echo JText::_('COM_KA_LAST_UPDATED'); ?>
			<time itemprop="dateModified" datetime="<?php echo JHtml::_('date', $item->modified, 'c'); ?>"><?php echo JHtml::_('date', $item->modified, JText::_('DATE_FORMAT_LC3')); ?></time>
		<?php endif; ?>
	</p>
	<?php endif; ?>

	<?php
	echo JLayoutHelper::render(
		'layouts.navigation.mark_links',
		array(
			'params'     => $params,
			'item'       => $item,
			'guest'      => $displayData['guest'],
			'itemid'     => $itemid,
			'controller' => 'albums',
			'msgPlaceAt' => 'div.middle-nav'
		),
		JPATH_COMPONENT
	);
	?>
</div>
