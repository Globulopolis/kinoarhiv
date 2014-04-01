<?php defined('_JEXEC') or die; ?>
<div class="uk-article ka-content">
	<div class="genre-list">
	<?php for ($i=0, $n=count($this->items); $i<$n; $i++):
		$item = $this->items[$i]; ?>
		<div class="genre-item"><a href="<?php echo JRoute::_('index.php?option=com_kinoarhiv&view=movies&filter_by=genres&gid[]='.$item->id.'&Itemid='.$this->itemid); ?>"><?php echo ucfirst($item->name); ?></a> (<?php echo $item->stats; ?>)</div>
	<?php endfor; ?>
	</div>
	<div class="clear"></div>
</div>
