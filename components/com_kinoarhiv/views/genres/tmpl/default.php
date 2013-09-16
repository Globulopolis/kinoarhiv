<?php defined('_JEXEC') or die; ?>
<?php if ($this->doctype == 'html'): ?>
<script src="<?php echo JURI::base(); ?>components/com_kinoarhiv/assets/js/jquery-ui.min.js" type="text/javascript"></script>
<script type="text/javascript">
//<![CDATA[
	jQuery(document).ready(function($){
		$('.tabbar .movies, .tabbar .persons, .tabbar .premieres').button();
	});
//]]>
</script>
<?php endif; ?>
<div class="ka-content">
	<?php if ($this->params->get('tabbar_frontpage') == 1 && $this->doctype == 'html'): ?>
	<div class="tabbar">
		<div>
			<a href="<?php echo JRoute::_('index.php?option=com_kinoarhiv&view=movies&Itemid='.$this->itemid); ?>" class="button movies"><?php echo JText::_('COM_KA_MOVIES'); ?></a>
		</div>
		<div>
			<a href="<?php echo JRoute::_('index.php?option=com_kinoarhiv&view=persons&Itemid='.$this->itemid); ?>" class="button persons"><?php echo JText::_('COM_KA_PERSONS'); ?></a>
		</div>
		<div>
			<a href="<?php echo JRoute::_('index.php?option=com_kinoarhiv&view=premieres&Itemid='.$this->itemid); ?>" class="button premieres"><?php echo JText::_('COM_KA_PREMIERES'); ?></a>
		</div>
	</div>
	<div class="clear"></div>
	<?php endif; ?>
	<div class="genre-list">
	<?php for ($i=0, $n=count($this->items); $i<$n; $i++):
		$item = $this->items[$i]; ?>
		<div class="genre-item"><a href="<?php echo JRoute::_('index.php?option=com_kinoarhiv&view=movies&filter_by=genres&gid[]='.$item->id.'&Itemid='.$this->itemid); ?>"><?php echo ucfirst($item->name); ?></a> (<?php echo $item->stats; ?>)</div>
	<?php endfor; ?>
	</div>
	<div class="clear"></div>
</div>
