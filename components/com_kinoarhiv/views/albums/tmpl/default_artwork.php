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

JHtml::_('script', 'media/com_kinoarhiv/js/jquery.plugin.min.js');
JHtml::_('script', 'media/com_kinoarhiv/js/jquery.more.min.js');
?>
<script type="text/javascript">
	jQuery(document).ready(function ($) {
		$('span.cr-list').more({
			length: 50,
			moreText: '<?php echo JText::_('COM_KA_READ_MORE'); ?>',
			lessText: '<?php echo JText::_('COM_KA_READ_LESS'); ?>'
		});
	});
</script>
<ul class="thumbnails">
<?php foreach ($this->items as $item):
	$title = KAContentHelper::formatItemTitle($item->title, '', $item->year); ?>

	<li class="span3">
		<div class="thumbnail">
			<a href="<?php echo JRoute::_('index.php?option=com_kinoarhiv&view=' . substr($this->view, 0, -1) . '&id=' . $item->id . '&Itemid=' . $this->itemid); ?>"
			   title="<?php echo $this->escape($title); ?>">
				<img data-original="<?php echo $item->cover; ?>" class="lazy"
					 alt="<?php echo JText::_('COM_KA_ARTWORK_ALT') . $this->escape($title); ?>"
					 width="<?php echo $item->coverWidth; ?>" height="<?php echo $item->coverHeight; ?>" />
			</a>
			<h5><a href="<?php echo JRoute::_('index.php?option=com_kinoarhiv&view=' . substr($this->view, 0, -1) . '&id=' . $item->id . '&Itemid=' . $this->itemid); ?>"
				   title="<?php echo $this->escape($title); ?>"><?php echo $item->title; ?></a>
			</h5>
			<p>
				<span><?php echo $item->text; ?></span>
				<span class="muted"><?php echo $item->year; ?></span>
			</p>
		</div>
	</li>

	<?php echo $item->event->afterDisplayContent;
endforeach; ?>
</ul>
