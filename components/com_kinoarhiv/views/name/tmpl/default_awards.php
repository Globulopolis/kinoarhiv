<?php defined('_JEXEC') or die; ?>
<div class="content movie awards">
	<article class="uk-article">
		<header>
			<h1 class="uk-article-title title">
				<a href="<?php echo JRoute::_('index.php?option=com_kinoarhiv&view=name&id='.$this->item->id.'&Itemid='.$this->itemid); ?>" class="brand" title="<?php echo $this->item->title; ?>"><?php echo $this->item->title; ?></a>
			</h1>
		</header>
		<?php echo $this->loadTemplate('tabs'); ?>
		<div class="awards-list">
			<?php if (count($this->item->awards) > 0):
				foreach ($this->item->awards as $award): ?>
				<div class="well">
					<h5><?php echo $award->aw_title; ?><?php echo ($award->year != '0000') ? ', '.$award->year : ''; ?></h5>
					<div class="small"><?php echo $award->aw_desc; ?></div>
					<?php echo $award->desc; ?>
				</div>
				<?php endforeach; ?>
			<?php else: ?>
			<div><?php echo GlobalHelper::showMsg(JText::_('COM_KA_NO_ITEMS')); ?></div>
			<?php endif; ?>
		</div>
	</article>
</div>
