<?php defined('_JEXEC') or die; ?>
<div class="content movie awards">
	<article>
		<header>
			<h1 class="title">
				<a href="<?php echo JRoute::_('index.php?option=com_kinoarhiv&view=movie&id='.$this->item->id.'&Itemid='.$this->itemid); ?>" class="brand" title="<?php echo $this->escape($this->item->title.$this->item->year_str); ?>"><?php echo $this->escape($this->item->title.$this->item->year_str); ?></a>
			</h1>
		</header>
		<?php echo $this->loadTemplate('tabs'); ?>
		<div class="awards-list">
			<?php if (count($this->item->awards) > 0):
				foreach ($this->item->awards as $award): ?>
				<div class="well">
					<h5><?php echo $this->escape($award->aw_title); ?><?php echo ($award->year != '0000') ? ', '.$award->year : ''; ?></h5>
					<div class="small"><?php echo $award->aw_desc; ?></div>
					<?php echo $award->desc; ?>
				</div>
				<?php endforeach; ?>
			<?php else: ?>
			<div><?php echo GlobalHelper::showMsg(JText::sprintf('COM_KA_NO_AWARDS', JText::_('COM_KA_MOVIE'))); ?></div>
			<?php endif; ?>
		</div>
	</article>
</div>