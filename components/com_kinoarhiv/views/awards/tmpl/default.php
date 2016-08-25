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
?>
<div class="uk-article ka-content">
	<div class="awards-list">
	<?php if (count($this->items) > 0): ?>
	<?php if ($this->params->get('pagevan_top') == 1): ?>
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

	<?php if ($this->params->get('pagevan_bottom') == 1): ?>
			<div class="pagination bottom">
				<form action="<?php echo htmlspecialchars(JUri::getInstance()->toString()); ?>" method="post" name="adminForm" id="adminForm" style="clear: both;" autocomplete="off">
				<?php echo $this->pagination->getPagesLinks(); ?><br />
				<?php echo $this->pagination->getResultsCounter(); ?>
				<?php echo $this->pagination->getLimitBox(); ?>
				</form>
			</div>
		<?php endif;
	else: ?>
		<br /><div><?php echo KAComponentHelper::showMsg(JText::_('COM_KA_NO_ITEMS')); ?></div>
	<?php endif; ?>
	</div>
</div>
