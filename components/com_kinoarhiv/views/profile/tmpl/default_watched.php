<?php defined('_JEXEC') or die;
$plural = $this->lang->getPluralSuffixes($this->pagination->get('total'));
?>
<script src="<?php echo JURI::base(); ?>components/com_kinoarhiv/assets/js/ui.aurora.min.js" type="text/javascript"></script>
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

		$('.cmd-wt-delete').click(function(e){
			e.preventDefault();
			var _this = $(this);

			$.ajax({
				url: _this.attr('href') + '&format=raw'
			}).done(function(response){
				if (response.success) {
					_this.closest('div').remove();
					showMsg('.wt-list', response.message);
				} else {
					showMsg('.wt-list', '<?php echo JText::_('JERROR_AN_ERROR_HAS_OCCURRED'); ?>');
				}
			}).fail(function(xhr, status, error){
				showMsg('.wt-list', error);
			});
		});

		$('#adminForm').submit(function(e){
			var items = $('input', this).filter(':checked');

			if (items.length == 0 || items.length < 0) {
				return false;
			}
		});
	});
//]]>
</script>
<div class="ka-content user-profile watched">
	<?php echo $this->loadTemplate('tabs'); ?>
	<?php if (count($this->items) > 0): ?>
	<form action="<?php JRoute::_('index.php'); ?>" method="post" id="adminForm" autocomplete="off">
		<div class="total-watched"><?php echo JText::_('COM_KA_PROFILE_TOTAL_WATCHED').$this->pagination->get('total').JText::_('COM_KA_PROFILE_TOTAL_MOVIES_'.$plural[0]); ?></div>
		<div class="wt-list">
			<?php foreach ($this->items as $i=>$item): ?>
			<div class="title-small">
				<span><?php echo JHtml::_('grid.id', $i, $item->id, false, 'ids'); ?> <a href="<?php echo JRoute::_('index.php?option=com_kinoarhiv&view=movie&id='.$item->id.'&Itemid='.$this->itemid); ?>"><?php echo $item->title.$item->year_str; ?></a></span>
				<span style="float: right;"><a class="cmd-wt-delete" href="<?php echo JRoute::_('index.php?option=com_kinoarhiv&task=watched&action=delete&Itemid='.$this->itemid.'&id='.$item->id); ?>" title="<?php echo JText::_('COM_KA_REMOVEFROM_WATCHED'); ?>"><img src="components/com_kinoarhiv/assets/themes/component/default/images/icons/delete_16.png" border="0" /></a></span>
			</div>
			<?php endforeach; ?>
			<input class="hasTooltip" type="checkbox" onclick="Joomla.checkAll(this)" title="<?php echo JText::_('COM_KA_CHECK_ALL'); ?>" value="" name="checkall-toggle" id="checkall-toggle"><label for="checkall-toggle"><?php echo JText::_('COM_KA_CHECK_ALL'); ?></label>
		</div><br />
		<input type="hidden" name="boxchecked" value="0" />
		<input type="hidden" name="option" value="com_kinoarhiv" />
		<input type="hidden" name="task" value="watched" />
		<input type="hidden" name="action" value="delete" />
		<input type="hidden" name="Itemid" value="<?php echo $this->itemid; ?>" />
		<input type="submit" class="btn btn-primary" value="<?php echo JText::_('COM_KA_REMOVE_SELECTED'); ?>" />
		<?php echo JHtml::_('form.token'); ?>
	</form>
	<?php else: ?>
		<br /><div><?php echo GlobalHelper::showMsg(JText::_('COM_KA_NO_ITEMS')); ?></div>
	<?php endif; ?>
	<?php if ($this->pagination->get('total') >= $this->pagination->get('limit')): ?>
		<div class="pagination bottom">
			<?php echo $this->pagination->getPagesLinks(); ?><br />
			<?php echo $this->pagination->getResultsCounter(); ?>
		</div>
	<?php endif; ?>
</div>
