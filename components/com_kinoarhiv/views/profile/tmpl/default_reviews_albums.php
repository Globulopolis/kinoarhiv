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
<div class="uk-article ka-content user-profile">
	<?php echo $this->loadTemplate('tabs'); ?>

	<div class="subtabs breadcrumb">
		<a href="<?php echo JRoute::_('index.php?option=com_kinoarhiv&view=profile&page=reviews&tab=movies&Itemid=' . $this->itemid); ?>"
		   class="subtab-movie<?php echo ($this->tab == 'movies') ? ' current' : ''; ?>"><?php echo JText::_('COM_KA_MOVIES'); ?></a>
		<a href="<?php echo JRoute::_('index.php?option=com_kinoarhiv&view=profile&page=reviews&tab=albums&Itemid=' . $this->itemid); ?>"
		   class="subtab-album<?php echo ($this->tab == 'albums') ? ' current' : ''; ?>"><?php echo JText::_('COM_KA_MUSIC_ALBUMS'); ?></a>
	</div>

	<?php if (count($this->items) > 0): ?>
		<div class="total-reviews"><?php echo JText::_('COM_KA_PROFILE_TOTAL_REVIEWS') . $this->pagination->total; ?></div>

		<form action="<?php JRoute::_('index.php'); ?>" method="post" id="profileForm" autocomplete="off">
			<table class="table table-striped items-list">
				<thead>
				<tr>
					<th></th>
					<th><?php echo JText::_('COM_KA_MUSIC_ALBUM_TITLE'); ?></th>
				</tr>
				</thead>
				<tbody>
				<?php foreach ($this->items as $i => $item):
					$title = $this->escape(KAContentHelper::formatItemTitle($item->title, '', $item->year));
					$ip = !empty($item->ip) ? $item->ip : JText::_('COM_KA_REVIEWS_IP_NULL');

					if ($item->type == 1)
					{
						$uiClass = 'neutral';
					}
					elseif ($item->type == 2)
					{
						$uiClass = 'positive';
					}
					elseif ($item->type == 3)
					{
						$uiClass = 'negative';
					}
					else
					{
						$uiClass = '';
					}
				?>
					<tr>
						<td width="2%">
							<input id="cb<?php echo $i; ?>" type="checkbox" value="<?php echo $item->id; ?>"
								   name="review_ids[]" title="<?php echo JText::_('JSELECT')?>" />
						</td>
						<td>
							<a href="<?php echo JRoute::_('index.php?option=com_kinoarhiv&view=album&id=' . $item->item_id . '&Itemid=' . $this->itemid . '&review=' . $item->id); ?>#review-<?php echo $item->id; ?>"><strong><?php echo $title; ?></strong></a>
							<div class="review-row">
								<div class="small timestamp"><?php echo JText::sprintf('COM_KA_REVIEWS_DATETIME', $item->created, $ip); ?></div>
								<div class="<?php echo $uiClass; ?>"><?php echo $item->review; ?></div>
							</div>
						</td>
					</tr>
				<?php endforeach; ?>
				</tbody>
				<tfoot>
				<tr>
					<td colspan="2">
						<input type="checkbox" title="<?php echo JText::_('COM_KA_CHECK_ALL'); ?>" value=""
							   name="checkall-toggle" id="checkall-toggle">
						<label for="checkall-toggle"><?php echo JText::_('COM_KA_CHECK_ALL'); ?></label>
					</td>
				</tr>
				</tfoot>
			</table>

			<input type="hidden" name="option" value="com_kinoarhiv"/>
			<input type="hidden" name="view" value="profile"/>
			<input type="hidden" name="task" value="reviews.delete"/>
			<input type="hidden" name="return" value="<?php echo base64_encode('view=profile&page=reviews&tab=albums'); ?>"/>
			<input type="hidden" name="Itemid" value="<?php echo $this->itemid; ?>"/>
			<?php echo JHtml::_('form.token'); ?>
			<input type="submit" class="btn btn-primary uk-button uk-button-primary" value="<?php echo JText::_('COM_KA_REMOVE_SELECTED'); ?>"/>
		</form>

		<form action="<?php echo htmlspecialchars(JUri::getInstance()->toString()); ?>" method="post" name="adminForm"
			  id="adminForm" style="clear: both;" autocomplete="off">
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
