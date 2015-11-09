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
<a name="reviews"></a>
<div class="reviews">
	<?php if ($this->params->get('allow_reviews') == 1 && $this->params->get('custom_review_component') != 'default'):
		// JComments
		if ($this->params->get('custom_review_component') == 'jc' && file_exists(JPATH_ROOT . '/components/com_jcomments/jcomments.php')):
			include_once JPATH_ROOT . '/components/com_jcomments/jcomments.php';
			$jc = new JComments;
			echo $jc::show($this->item->id, 'com_kinoarhiv', $this->escape($this->item->title . $this->item->year_str));
		endif;
	elseif ($this->params->get('allow_reviews') == 1 && $this->params->get('custom_review_component') == 'default'):
		$review_number = $this->pagination->limitstart + 1;
		$cmd_insert_username = '';

		if ($this->params->get('allow_reviews') == 1 && !$this->user->guest && $this->item->attribs->allow_reviews == 1):
			// Default review system
			$cmd_insert_username = ' cmd-insert-username';
		endif; ?>

		<div class="ui-corner-all ui-widget-header header-small"><?php echo JText::_('COM_KA_REVIEWS'); ?></div>
	<?php $count_items = count($this->items);
		if ($count_items > 0): ?>
		<div class="content">

			<?php for ($i = 0, $n = $count_items; $i < $n; $i++):
				$review = $this->items[$i];

				if ($review->type == 2)
				{
					$ui_class = 'ui-state-highlight';
				}
				elseif ($review->type == 3)
				{
					$ui_class = 'ui-state-error';
				}
				else
				{
					$ui_class = 'ui-state-default';
				} ?>
				<div class="review-row">
					<a name="review-<?php echo $review->id; ?>"></a>

					<div class="review-row-title ui-widget ui-widget-content ui-corner-top <?php echo $ui_class; ?>">
						<span class="number"><?php echo $review_number++; ?>. </span>
						<span class="username<?php echo $cmd_insert_username; ?>"><?php echo !empty($review->name) ? $review->name : $review->username; ?></span>
						<span><a href="<?php echo JRoute::_('index.php?option=com_kinoarhiv&view=movie&id=' . $this->item->id . '&limitstart=' . $this->pagination->limitstart . '&Itemid=' . $this->itemid) . '#review-' . $review->id; ?>" title="<?php echo JText::_('COM_KA_REVIEWS_PERMALINK'); ?>" class="hasTip permalink"><img src="components/com_kinoarhiv/assets/themes/component/<?php echo $this->params->get('ka_theme'); ?>/images/icons/link_16.png" border="0"/></a></span>
						<span class="date"><?php echo $review->created; ?></span>
					</div>
					<?php if (!$this->user->guest && $this->item->attribs->allow_reviews == 1): ?>
						<div class="ui-widget ui-widget-content review"><?php echo $review->review; ?></div>
						<div class="ui-widget ui-widget-content ui-corner-bottom footer">
							<a href="#" class="cmd-insert-quote"><?php echo JText::_('COM_KA_REVIEWS_QUOTELINK'); ?></a>
							<?php if ($this->user->authorise('core.delete.reviews', 'com_kinoarhiv') && $review->uid == $this->user->get('id')): ?>
								<a href="<?php echo JRoute::_(
									'index.php?option=com_kinoarhiv&task=reviews.delete&return=movie&review_id=' . $review->id . '&id=' . $review->movie_id
									); ?>" class="cmd-delete-quote" rel="nofollow">
									<?php echo JText::_('JACTION_DELETE'); ?>
								</a>
							<?php endif; ?>
						</div>
					<?php else: ?>
						<div class="ui-widget ui-widget-content review ui-corner-bottom footer"><?php echo $review->review; ?></div>
					<?php endif; ?>
				</div>
			<?php endfor; ?>

			<div class="pagination bottom">
				<form action="<?php echo htmlspecialchars(JURI::getInstance()->toString()); ?>" method="post" name="adminForm"
					id="adminForm" style="clear: both;" autocomplete="off">
					<?php echo $this->pagination->getPagesLinks(); ?><br/>
					<?php echo $this->pagination->getResultsCounter(); ?>&nbsp;
					<label for="limit" class="element-invisible"><?php echo JText::_('JGLOBAL_DISPLAY_NUM'); ?></label>
					<?php echo $this->pagination->getLimitBox(); ?>
					<input type="hidden" name="limitstart" value=""/>
					<input type="hidden" name="task" value=""/>
				</form>
			</div>
		</div>
	<?php else: ?>
		<div><?php echo KAComponentHelper::showMsg(JText::_('COM_KA_REVIEWS_NO')); ?></div>
	<?php endif; ?>

	<?php
	// Show "Add review" form
	if (!$this->user->guest):
		if ($this->item->attribs->allow_reviews == 1):
			echo JLayoutHelper::render(
				'layouts/editors/editor_' . $this->params->get('review_editor'),
				(object) array(
					'params' => $this->params,
					'form'   => $this->form,
					'id'     => $this->item->id,
					'task'   => 'reviews.save'
				),
				JPATH_COMPONENT
			);
		else:
			echo KAComponentHelper::showMsg(JText::_('COM_KA_REVIEWS_DISABLED'));
		endif;
	else: ?>
	<br/>
		<div><?php echo KAComponentHelper::showMsg(JText::sprintf(JText::_('COM_KA_REVIEWS_AUTHREQUIRED'), '<a href="' . JRoute::_('index.php?option=com_users&view=registration') . '">' . JText::_('COM_KA_REGISTER') . '</a>', '<a href="' . JRoute::_('index.php?option=com_users&view=login') . '">' . JText::_('COM_KA_LOGIN') . '</a>')); ?></div>
	<?php endif;
	endif; ?>
</div>
