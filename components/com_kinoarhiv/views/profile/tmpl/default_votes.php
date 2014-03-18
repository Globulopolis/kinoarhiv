<?php defined('_JEXEC') or die; ?>
<script src="<?php echo JURI::base(); ?>components/com_kinoarhiv/assets/js/ui.aurora.min.js" type="text/javascript"></script>
<script src="<?php echo JURI::base(); ?>components/com_kinoarhiv/assets/js/jquery.rateit.min.js" type="text/javascript"></script>
<script type="text/javascript">
//<![CDATA[
	jQuery(document).ready(function($){
		function showMsg(selector, text) {
			$(selector).aurora({
				text: text,
				placement: 'before',
				button: 'close',
				button_title: '[<?php echo JText::_('COM_KA_CLOSE'); ?>]'
			});
		}

		$('.rateit').bind('over', function(e, v){ $(this).attr('title', v); });
		$('.rate .rateit').bind('rated reset', function(e){
			var _this = $(this);
			var value = _this.rateit('value');
			var id = _this.attr('data-rateit-item');

			$.ajax({
				type: 'POST',
				url: '<?php echo JRoute::_('index.php?option=com_kinoarhiv&view=movie&task=vote&Itemid='.$this->itemid.'&format=raw', false); ?>'+'&id='+id,
				data: { 'value': value }
			}).done(function(response){
				if (response.success) {
					if (value == 0) {
						_this.closest('.item-row').remove();
					}
				}

				showMsg($('.v-list'), response.message);

			}).fail(function(xhr, status, error){
				showMsg($('.v-list'), error);
			});
		});
	});
//]]>
</script>
<div class="ka-content user-profile votes">
	<?php echo $this->loadTemplate('tabs'); ?>
	<?php if (count($this->items) > 0): ?>
		<div class="total-votes"><?php echo JText::sprintf('COM_KA_PROFILE_TOTAL_VOTES', $this->pagination->total); ?></div>
		<div class="v-list">
			<?php foreach ($this->items as $item): ?>
			<div class="title-small item-row">
				<div>
					<a href="<?php echo JRoute::_('index.php?option=com_kinoarhiv&view=movie&id='.$item->id.'&Itemid='.$this->itemid); ?>"><?php echo $item->title.$item->year_str; ?></a>
				</div>
				<div>
					<div class="rate">
						<select class="rate_field" autocomplete="off">
							<?php for ($i=0, $n=(int)$this->params->get('vote_summ_num')+1; $i<$n; $i++): ?>
							<option value="<?php echo $i; ?>"<?php echo ($i == round($item->rate_loc_label)) ? ' selected="selected"' : ''; ?>><?php echo $i; ?></option>
							<?php endfor; ?>
						</select><?php echo JText::_('COM_KA_RATE_MY_VOTE'); ?>
						<div class="rateit" data-rateit-value="<?php echo round($item->rate_loc_label); ?>" data-rateit-backingfld=".rate_field" data-rateit-item="<?php echo $item->id; ?>"></div>&nbsp;<span class="rate_loc_my"><?php echo $item->my_vote; ?> <?php echo JText::_('COM_KA_FROM'); ?> <?php echo (int)$this->params->get('vote_summ_num'); ?></span> <span class="small">(<?php echo JHtml::_('date', $item->_datetime, JText::_('DATE_FORMAT_LC3')); ?>)</span>
						<div class="rate_loc_total"><?php echo JText::_('COM_KA_RATE_VOTES_TOTAL').$item->total_voted; ?></div>
						<div class="rate_loc_movie"><?php echo JText::_('COM_KA_RATE_MY_MOVIE').$item->rate_loc_label; ?></div>
					</div>
				</div>
			</div>
			<?php endforeach; ?>
		</div>
	<?php else: ?>
		<br /><div><?php echo GlobalHelper::showMsg(JText::_('COM_KA_RATE_NORATE')); ?></div>
	<?php endif; ?>
	<?php if ($this->pagination->total >= $this->pagination->limit): ?>
		<div class="pagination bottom">
			<?php echo $this->pagination->getPagesLinks(); ?><br />
			<?php echo $this->pagination->getResultsCounter(); ?>
		</div>
	<?php endif; ?>
</div>
