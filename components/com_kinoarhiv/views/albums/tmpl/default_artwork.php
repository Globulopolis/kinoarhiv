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
<ul class="thumbnails">
<?php foreach ($this->items as $item): ?>

	<li class="span3">
		<div class="thumbnail">
			<a href="<?php echo JRoute::_('index.php?option=com_kinoarhiv&view=album&id=' . $item->id . '&Itemid=' . $this->itemid); ?>"
			   title="<?php echo $this->escape($item->title); ?>">
				<img data-original="<?php echo $item->cover; ?>" class="lazy"
					 alt="<?php echo JText::_('COM_KA_ARTWORK_ALT') . $this->escape($item->title); ?>"
					 width="<?php echo $item->coverWidth; ?>" height="<?php echo $item->coverHeight; ?>" />
			</a>
			<h5><a href="<?php echo JRoute::_('index.php?option=com_kinoarhiv&view=album&id=' . $item->id . '&Itemid=' . $this->itemid); ?>"
				   title="<?php echo $this->escape($item->title); ?>"><?php echo $item->title; ?></a>
			</h5>
			<p><span><?php echo $item->composer; ?></span><br />
				<span class="muted"><?php echo $item->year; ?></span>
			</p>
		</div>
	</li>

	<?php echo $item->event->afterDisplayContent;
	endforeach; ?>
</ul>
