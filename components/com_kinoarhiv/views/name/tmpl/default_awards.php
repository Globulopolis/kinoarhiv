<?php defined('_JEXEC') or die; ?>
<div class="content name awards">
	<?php if ($this->params->get('use_alphabet') == 1):
		echo $this->loadTemplate('alphabet');
	endif; ?>

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
				<div class="well uk-panel uk-panel-box">
					<h5 class="uk-panel-title"><a href="<?php echo JRoute::_('index.php?option=com_kinoarhiv&view=awards&id='.$award->id.'&Itemid='.$this->itemid); ?>"><?php echo $this->escape($award->aw_title); ?></a><?php echo ($award->year != '0000') ? ', '.$award->year : ''; ?></h5>
					<?php echo $award->desc; ?>
				</div>
				<?php endforeach; ?>
			<?php else: ?>
			<div><?php echo GlobalHelper::showMsg(JText::_('COM_KA_NO_ITEMS')); ?></div>
			<?php endif; ?>
		</div>
	</article>
</div>
