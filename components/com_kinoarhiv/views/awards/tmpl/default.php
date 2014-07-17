<?php defined('_JEXEC') or die; ?>
<div class="uk-article ka-content">
	<div class="awards-list">
	<?php if (count($this->items) > 0): ?>
	<?php if ($this->params->get('pagevan_top') == 1 && $this->pagination->total >= $this->pagination->limit): ?>
		<div class="pagination top">
			<?php echo $this->pagination->getPagesLinks(); ?>
		</div>
	<?php endif;

		foreach ($this->items as $item): ?>
		<div class="well uk-panel uk-panel-box">
			<h4 class="uk-panel-title"><?php echo $item->title; ?></h4>
			<div class="award-desc"><?php echo $item->desc; ?></div>
		</div>
		<?php endforeach; ?>

	<?php if ($this->params->get('pagevan_bottom') == 1 && $this->pagination->total >= $this->pagination->limit): ?>
			<div class="pagination bottom">
				<form action="<?php echo htmlspecialchars(JURI::getInstance()->toString()); ?>" method="post" name="adminForm" id="adminForm" style="clear: both;" autocomplete="off">
				<?php echo $this->pagination->getPagesLinks(); ?><br />
				<?php echo $this->pagination->getResultsCounter(); ?>
				<?php echo $this->pagination->getLimitBox(); ?>
				</form>
			</div>
		<?php endif;
	else: ?>
		<br /><div><?php echo GlobalHelper::showMsg(JText::_('COM_KA_NO_ITEMS')); ?></div>
	<?php endif; ?>
	</div>
</div>
