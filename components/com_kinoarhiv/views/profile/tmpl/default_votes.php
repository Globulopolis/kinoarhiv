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
JHtml::_('script', 'components/com_kinoarhiv/assets/js/ui.aurora.min.js');
JHtml::_('script', 'components/com_kinoarhiv/assets/js/jquery.rateit.min.js');
?>
<script type="text/javascript">
	//<![CDATA[
	jQuery(document).ready(function ($) {
		function showMsg(selector, text) {
			$(selector).aurora({
				text: text,
				placement: 'before',
				button: 'close',
				button_title: '[<?php echo JText::_('COM_KA_CLOSE'); ?>]'
			});
		}

		$('.rateit').bind('over', function (e, v) {
			$(this).attr('title', v);
		});
		$('.rate .rateit').bind('rated reset', function (e) {
			var _this = $(this);
			var value = _this.rateit('value');
			var id = _this.data('rateit-item');

			$.ajax({
				type: 'POST',
				url: '<?php echo JRoute::_('index.php?option=com_kinoarhiv&view=movie&task=vote&Itemid='.$this->itemid.'&format=raw', false); ?>' + '&id=' + id,
				data: {'value': value}
			}).done(function (response) {
				if (response.success) {
					if (value == 0) {
						_this.closest('.item-row').remove();
					}
				}

				showMsg($('.v-list'), response.message);

			}).fail(function (xhr, status, error) {
				showMsg($('.v-list'), error);
			});
		});
	});
	//]]>
</script>
<div class="uk-article ka-content user-profile votes">
	<?php echo $this->loadTemplate('tabs'); ?>
	<?php if (count($this->items) > 0): ?>
		<div class="total-votes"><?php echo JText::sprintf('COM_KA_PROFILE_TOTAL_VOTES', $this->pagination->total); ?></div>
		<div class="v-list">
			<?php foreach ($this->items as $item): ?>
				<div class="item-row">
					<div>
						<a href="<?php echo JRoute::_('index.php?option=com_kinoarhiv&view=movie&id=' . $item->id . '&Itemid=' . $this->itemid); ?>"><?php echo $item->title . $item->year_str; ?></a>
					</div>
					<div>
						<div class="rate">
							<select id="rate_field_<?php echo $item->id; ?>" autocomplete="off">
								<?php for ($i = 0, $n = (int) $this->params->get('vote_summ_num') + 1; $i < $n; $i++): ?>
									<option value="<?php echo $i; ?>"<?php echo ($i == round($item->rate_loc_label)) ? ' selected="selected"' : ''; ?>><?php echo $i; ?></option>
								<?php endfor; ?>
							</select><?php echo JText::_('COM_KA_RATE_MY_VOTE'); ?>
							<div class="rateit" data-rateit-value="<?php echo round($item->rate_loc_label); ?>" data-rateit-backingfld="#rate_field_<?php echo $item->id; ?>" data-rateit-item="<?php echo $item->id; ?>"></div>
							<?php if ($item->my_vote != 0): ?>
								&nbsp;
								<span class="rate_loc_my"><?php echo JText::sprintf('COM_KA_RATE_MY', $item->my_vote, (int) $this->params->get('vote_summ_num')); ?></span>&nbsp;
							<?php else: ?>
								&nbsp;<span class="rate_loc_my"><?php echo JText::_('COM_KA_RATE_NO'); ?></span>&nbsp;
							<?php endif; ?>
							<?php if ($item->_datetime != '0000-00-00 00:00:00'): ?><span class="small">
								(<?php echo JHtml::_('date', $item->_datetime, JText::_('DATE_FORMAT_LC3')); ?>
								)</span><?php endif; ?>
							<div class="rate_loc_total"><?php echo JText::_('COM_KA_RATE_VOTES_TOTAL') . $item->total_voted; ?></div>
							<div class="rate_loc_movie"><?php echo JText::_('COM_KA_RATE_MY_MOVIE') . $item->rate_loc_label; ?></div>
						</div>
					</div>
				</div>
			<?php endforeach; ?>
		</div>

		<form action="<?php echo htmlspecialchars(JURI::getInstance()->toString()); ?>" method="post" name="adminForm" id="adminForm" style="clear: both;" autocomplete="off">
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
		<div><?php echo KAComponentHelper::showMsg(JText::_('COM_KA_RATE_NORATE')); ?></div>
	<?php endif; ?>
</div>
