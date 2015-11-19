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

$data = $displayData;
$title = $this->escape(KAContentHelper::formatItemTitle($data['item']->title, '', $data['item']->year));
?>
<header>
	<h1 class="uk-article-title title" itemprop="name">
		<?php if ($data['item']->attribs->link_titles === ''): ?>
			<?php if ($data['params']->get('link_titles') == 1): ?>
				<a href="<?php echo JRoute::_('index.php?option=com_kinoarhiv&view=movie&id=' . $data['item']->id . '&Itemid=' . $data['itemid']); ?>" class="brand" title="<?php echo $title; ?>"><?php echo $title; ?></a>
			<?php else: ?>
				<span class="brand"><?php echo $title; ?></span>
			<?php endif; ?>
		<?php elseif ($data['item']->attribs->link_titles == 1): ?>
			<a href="<?php echo JRoute::_('index.php?option=com_kinoarhiv&view=movie&id=' . $data['item']->id . '&Itemid=' . $data['itemid']); ?>" class="brand" title="<?php echo $title; ?>"><?php echo $title; ?></a>
		<?php elseif ($data['item']->attribs->link_titles == 0): ?>
			<span class="brand"><?php echo $title; ?></span>
		<?php endif; ?>
	</h1>
</header>

<div class="middle-nav clearfix">
	<p class="meta">
		<?php if ($data['item']->attribs->show_author === '' && !empty($data['item']->username)): ?>
			<?php if ($data['params']->get('show_author') == 1): ?>
				<span class="icon-user"></span> <?php echo JText::_('JAUTHOR'); ?>: <?php echo $data['item']->username; ?>
				<br/>
			<?php endif; ?>
		<?php elseif ($data['item']->attribs->show_author == 1 && !empty($data['item']->username)): ?>
			<span class="icon-user"></span> <?php echo JText::_('JAUTHOR'); ?>: <?php echo $data['item']->username; ?>
			<br/>
		<?php endif; ?>

		<?php if ($data['item']->attribs->show_create_date === ''): ?>
			<?php if ($data['params']->get('show_pubdate') == 1): ?>
				<span class="icon-calendar"></span> <?php echo JText::_('COM_KA_CREATED_DATE_ON'); ?>
				<time pubdate="" datetime="<?php echo $data['item']->created; ?>"><?php echo date('j F Y', strtotime($data['item']->created)); ?></time>
			<?php endif; ?>
		<?php elseif ($data['item']->attribs->show_create_date == 1): ?>
			<span class="icon-calendar"></span> <?php echo JText::_('COM_KA_CREATED_DATE_ON'); ?>
			<time pubdate="" datetime="<?php echo $data['item']->created; ?>"><?php echo date('j F Y', strtotime($data['item']->created)); ?></time>
		<?php endif; ?>

		<?php
		if (
			(($data['item']->attribs->show_create_date === '' && $data['params']->get('show_pubdate') == 1)
				|| $data['item']->attribs->show_create_date == 1)
			&& (($data['item']->attribs->show_modify_date === '' && $data['params']->get('show_moddate') == 1)
				|| $data['item']->attribs->show_modify_date == 1)
		):
			echo ' &bull; ';
		endif; ?>

		<?php if ($data['item']->attribs->show_modify_date === ''): ?>
			<?php if ($data['params']->get('show_moddate') == 1): ?>
				<?php echo JText::_('COM_KA_LAST_UPDATED'); ?>
				<time pubdate="" datetime="<?php echo $data['item']->modified; ?>"><?php echo date('j F Y', strtotime($data['item']->modified)); ?></time>
			<?php endif; ?>
		<?php elseif ($data['item']->attribs->show_modify_date == 1): ?>
			<?php echo JText::_('COM_KA_LAST_UPDATED'); ?>
			<time pubdate="" datetime="<?php echo $data['item']->modified; ?>"><?php echo date('j F Y', strtotime($data['item']->modified)); ?></time>
		<?php endif; ?>
	</p>
</div>
