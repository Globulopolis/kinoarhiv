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

$allowReviewForm      = $this->params->get('allow_reviews');
$allowReviewsList     = $this->params->get('show_reviews');
$itemAllowReviewsForm = $this->item->attribs->allow_reviews;
?>
<div class="reviews" id="reviews">
<?php
	if ($this->params->get('custom_review_component') != 'default'):
		// JComments
		if ($this->params->get('custom_review_component') == 'jc' && file_exists(JPATH_ROOT . '/components/com_jcomments/jcomments.php')):
			include_once JPATH_ROOT . '/components/com_jcomments/jcomments.php';
			$jc = new JComments;
			echo $jc::show($this->item->id, 'com_kinoarhiv', $this->escape(KAContentHelper::formatItemTitle($this->item->title, '', $this->item->year)));
		endif;
	elseif ($this->params->get('custom_review_component') == 'default'):
		$reviewNumber = $this->pagination->limitstart + 1;
		$cmdInsertUsername = '';

		if ($allowReviewForm == 1 && !$this->user->guest && $itemAllowReviewsForm == 1):
			// Default review system
			$cmdInsertUsername = ' cmd-insert-username';
		endif; ?>

		<br />
		<h3><?php echo JText::_('COM_KA_REVIEWS'); ?></h3>

	<?php $totalReviews = count($this->items);
		if ($totalReviews > 0): ?>
		<div class="content">

		<?php
		if ($allowReviewsList == 1):
			for ($i = 0, $n = $totalReviews; $i < $n; $i++):
				$review = $this->items[$i];

				if ($review->type == 1)
				{
					$uiClass = 'neutral';
				}
				elseif ($review->type == 2)
				{
					$uiClass = 'positive';
				}
				elseif ($review->type == 3)
				{
					$uiClass = 'negative';
				}
				else
				{
					$uiClass = '';
				} ?>
				<div class="review-row">
					<span id="review-<?php echo $review->id; ?>"></span>

					<div class="review-title corner-top">
						<span class="number"><?php echo $reviewNumber++; ?>. </span>
						<span class="username<?php echo $cmdInsertUsername; ?>"><?php echo !empty($review->name) ? $review->name : $review->username; ?></span>
						<span><a href="<?php echo JRoute::_('index.php?option=com_kinoarhiv&view=movie&id=' . $this->item->id . '&limitstart=' . $this->pagination->limitstart . '&Itemid=' . $this->itemid) . '#review-' . $review->id; ?>"
								 title="<?php echo JText::_('COM_KA_REVIEWS_PERMALINK'); ?>"
								 class="hasTooltip permalink"><img src="media/com_kinoarhiv/images/icons/link_16.png" alt="" /></a></span>
						<span class="date"><?php echo $review->created; ?></span>
					</div>

					<?php if (!$this->user->guest && ($allowReviewForm == 1 && $itemAllowReviewsForm == 1)): ?>

						<div class="review review-content <?php echo $uiClass; ?>"><?php echo $review->review; ?></div>
						<div class="review-footer corner-bottom">
							<a href="#" class="cmd-insert-quote"><?php echo JText::_('COM_KA_REVIEWS_QUOTELINK'); ?></a>
							<?php if ($this->user->get('isRoot') || ($this->user->authorise('core.delete.reviews', 'com_kinoarhiv') && $review->uid == $this->user->get('id'))): ?>
								<a href="<?php echo JRoute::_('index.php?option=com_kinoarhiv&task=reviews.delete&review_id=' . $review->id . '&id=' . $review->item_id . '&' . JSession::getFormToken() . '=1&return=' . base64_encode('view=movie&id=' . $review->item_id)); ?>"
								   class="cmd-delete-review" rel="nofollow"><?php echo JText::_('JACTION_DELETE'); ?></a>
							<?php endif; ?>
						</div>

					<?php else: ?>
						<div class="review-footer corner-bottom review-content <?php echo $uiClass; ?>"><?php echo $review->review; ?></div>
					<?php endif; ?>
				</div>
			<?php endfor;

			echo JLayoutHelper::render('layouts.navigation.pagination',
				array('params' => $this->params, 'pagination' => $this->pagination, 'limitstart' => true, 'task' => true),
				JPATH_COMPONENT
			);
		endif; ?>
		</div>
	<?php else: ?>
		<div><?php echo KAComponentHelper::showMsg(JText::_('COM_KA_REVIEWS_NO')); ?></div>
	<?php endif; ?>

	<?php
		// Show "Add review" form
		if (!$this->user->guest):
			if ($allowReviewForm == 1 && $itemAllowReviewsForm == 1):
				echo JLayoutHelper::render(
					'layouts.editors.editor_' . $this->params->get('review_editor'),
					(object) array(
						'params' => $this->params,
						'form'   => $this->form,
						'id'     => $this->item->id,
						'task'   => 'reviews.save',
						'view'   => 'movie'
					),
					JPATH_COMPONENT
				);
			else:
				echo KAComponentHelper::showMsg(JText::_('COM_KA_REVIEWS_DISABLED'), 'alert-error');
			endif;
		else: ?>
		<br/>
		<div><?php echo KAComponentHelper::showMsg(
			JText::sprintf(
				JText::_('COM_KA_REVIEWS_AUTHREQUIRED'),
				'<a href="' . JRoute::_('index.php?option=com_users&view=registration') . '">' . JText::_('COM_KA_REGISTER') . '</a>',
				'<a href="' . JRoute::_('index.php?option=com_users&view=login') . '">' . JText::_('COM_KA_LOGIN') . '</a>'
			)
		); ?>
		</div>
		<?php endif;
	endif; ?>
</div>
