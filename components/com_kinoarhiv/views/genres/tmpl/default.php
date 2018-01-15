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
<div class="uk-article ka-content">
	<div class="genre-list">
	<?php $items = count($this->items);

	for ($i = 0; $i < $items; $i++):
		$item = $this->items[$i]; ?>
		<span class="genre-item">
			<a href="<?php echo JRoute::_('index.php?option=com_kinoarhiv&view=movies&filters[movies][genre][]=' . $item->id . '&Itemid=' . $this->itemid); ?>"><?php echo ucfirst($item->name); ?></a> <span class="badge badge-info pull-right"><?php echo $item->stats; ?></span>
		</span>
	<?php endfor; ?>
	</div>
	<div class="clear"></div>
</div>
