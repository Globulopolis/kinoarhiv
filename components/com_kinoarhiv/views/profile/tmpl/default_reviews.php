<?php
/**
 * @package     Kinoarhiv.Site
 * @subpackage  com_kinoarhiv
 *
 * @copyright   Copyright (C) 2017 Libra.ms. All rights reserved.
 * @license     GNU General Public License version 2 or later
 * @url         http://киноархив.com
 */

defined('_JEXEC') or die;
?>
<div class="uk-article ka-content user-profile reviews">
	<?php echo $this->loadTemplate('tabs'); ?>

	<?php if (count($this->items) > 0): ?>
		<form action="<?php JRoute::_('index.php'); ?>" method="post" id="profileForm" autocomplete="off">
			<div class="total-reviews"><?php echo JText::_('COM_KA_PROFILE_TOTAL_REVIEWS') . $this->pagination->total; ?></div>
			<table class="table table-striped items-list">
				<thead>
				<tr>
					<th></th>
					<th><?php echo JText::_('COM_KA_SEARCH_ADV_MOVIES_TITLE_LABEL'); ?></th>
				</tr>
				</thead>
				<tbody>
				<?php foreach ($this->items as $i => $item):
					$title = $this->escape(KAContentHelper::formatItemTitle($item->title, '', $item->year));
					$ip = !empty($item->ip) ? $item->ip : JText::_('COM_KA_REVIEWS_IP_NULL');

					if ($item->type == 1)
					{
						$ui_class = 'neutral';
					}
					elseif ($item->type == 2)
					{
						$ui_class = 'positive';
					}
					elseif ($item->type == 3)
					{
						$ui_class = 'negative';
					}
					else
					{
						$ui_class = '';
					}
				?>
					<tr>
						<td width="2%">
							<input id="cb<?php echo $i; ?>" type="checkbox" value="<?php echo $item->id; ?>" name="review_ids[]" title="<?php echo JText::_('JSELECT')?>" />
						</td>
						<td>
							<a href="<?php echo JRoute::_('index.php?option=com_kinoarhiv&view=movie&id=' . $item->movie_id . '&Itemid=' . $this->itemid . '&review=' . $item->id); ?>#review-<?php echo $item->id; ?>"><strong><?php echo $title; ?></strong></a>
							<div class="review-row">
								<div class="small timestamp"><?php echo JText::sprintf('COM_KA_REVIEWS_DATETIME', $item->created, $ip); ?></div>
								<div class="<?php echo $ui_class; ?>"><?php echo $item->review; ?></div>
							</div>
						</td>
					</tr>
				<?php endforeach; ?>
				</tbody>
				<tfoot>
				<tr>
					<td colspan="2">
						<input type="checkbox" title="<?php echo JText::_('COM_KA_CHECK_ALL'); ?>" value="" name="checkall-toggle" id="checkall-toggle">
						<label for="checkall-toggle"><?php echo JText::_('COM_KA_CHECK_ALL'); ?></label>
					</td>
				</tr>
				</tfoot>
			</table>

			<input type="hidden" name="boxchecked" value="0"/>
			<input type="hidden" name="option" value="com_kinoarhiv"/>
			<input type="hidden" name="task" value="reviews.delete"/>
			<input type="hidden" name="return" value="profile"/>
			<input type="hidden" name="Itemid" value="<?php echo $this->itemid; ?>"/>
			<?php echo JHtml::_('form.token'); ?>
			<input type="submit" class="btn btn-primary uk-button uk-button-primary" value="<?php echo JText::_('COM_KA_REMOVE_SELECTED'); ?>"/>
		</form>

		<form action="<?php echo htmlspecialchars(JUri::getInstance()->toString()); ?>" method="post" name="adminForm" id="adminForm" style="clear: both;" autocomplete="off">
			<?php if ($this->pagination->total >= $this->pagination->limit): ?>
				<div class="pagination bottom">
					<?php echo $this->pagination->getPagesLinks(); ?><br/>
					<?php echo $this->pagination->getResultsCounter(); ?>
					<?php echo $this->pagination->getLimitBox(); ?>
				</div>
			<?php endif; ?>
		</form>
	<?php else: ?>
		<br/>
		<div><?php echo KAComponentHelper::showMsg(JText::_('COM_KA_NO_ITEMS')); ?></div>
	<?php endif; ?>
</div>
