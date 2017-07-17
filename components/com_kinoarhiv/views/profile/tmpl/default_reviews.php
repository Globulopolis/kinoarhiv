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
<script type="text/javascript">
	jQuery(document).ready(function($){
		$('#checkall-toggle').click(function () {
			if ($(this).is(':checked')) {
				$('.r-list .title-small :checkbox').prop('checked', true);
			} else {
				$('.r-list .title-small :checkbox').prop('checked', false);
			}
		});

		$('#adminForm').submit(function(e){
			var items = $('input', this).filter(':checked');

			if (items.length == 0 || items.length < 0) {
				return false;
			}
		});
	});
</script>
<div class="uk-article ka-content user-profile reviews">
	<?php echo $this->loadTemplate('tabs'); ?>

	<?php if (count($this->items) > 0): ?>
		<form action="<?php JRoute::_('index.php'); ?>" method="post" id="adminForm" autocomplete="off">
			<div class="total-reviews"><?php echo JText::_('COM_KA_PROFILE_TOTAL_REVIEWS') . $this->pagination->total; ?></div>
			<div class="r-list">
				<?php foreach ($this->items as $i => $item):
					$ui_class = ($item->state == 0) ? 'muted' : '';
					$title = $this->escape(KAContentHelper::formatItemTitle($item->title, '', $item->year));
					$ip = !empty($item->ip) ? $item->ip : JText::_('COM_KA_REVIEWS_IP_NULL');
				?>
					<div class="title-small <?php echo $ui_class; ?>">
						<span><input id="cb<?php echo $i; ?>" type="checkbox" value="<?php echo $item->id; ?>" name="review_ids[]"> <a href="<?php echo JRoute::_('index.php?option=com_kinoarhiv&view=movie&id=' . $item->movie_id . '&Itemid=' . $this->itemid . '&review=' . $item->id); ?>#review-<?php echo $item->id; ?>"><strong><?php echo $title; ?></strong></a></span>
						<span style="float: right;"><a class="cmd-r-delete" href="<?php echo JRoute::_('index.php?option=com_kinoarhiv&task=reviews.delete&Itemid=' . $this->itemid . '&review_id=' . $item->id); ?>" title="<?php echo JText::_('JACTION_DELETE'); ?>"><img src="media/com_kinoarhiv/images/icons/delete_16.png" border="0"/></a></span>

						<div class="small timestamp"><?php echo JText::sprintf('COM_KA_REVIEWS_DATETIME', $item->created, $ip); ?></div>
						<div class="review"><?php echo $item->review; ?></div>
					</div>
				<?php endforeach; ?>
				<input type="checkbox" title="<?php echo JText::_('COM_KA_CHECK_ALL'); ?>" value="" name="checkall-toggle" id="checkall-toggle"><label for="checkall-toggle"><?php echo JText::_('COM_KA_CHECK_ALL'); ?></label>
			</div>
			<br/>

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
