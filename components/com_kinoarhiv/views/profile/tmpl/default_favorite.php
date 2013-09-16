<?php defined('_JEXEC') or die;
if ($this->page == 'names') {
	$view = 'name';
} else {
	$view = 'movie';
}
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

		$('.cmd-fav-delete').click(function(e){
			e.preventDefault();
			var _this = $(this);

			$.ajax({
				url: _this.attr('href') + '&format=raw'
			}).done(function(response){
				if (response.success) {
					_this.closest('div').remove();
					showMsg('.fav-list', response.message);
				} else {
					showMsg('.fav-list', '<?php echo JText::_('JERROR_AN_ERROR_HAS_OCCURRED'); ?>');
				}
			}).fail(function(xhr, status, error){
				showMsg('.fav-list', error);
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
<div class="ka-content user-profile favorite">
	<?php echo $this->loadTemplate('tabs'); ?>
	<div class="subtabs breadcrumb">
		<a href="<?php echo JRoute::_('index.php?option=com_kinoarhiv&view=profile&tab=favorite&page=movies&Itemid='.$this->itemid); ?>" class="subtab-movie<?php echo ($this->page == 'movies') ? ' current' : ''; ?>"><?php echo JText::_('COM_KA_MOVIES'); ?></a>
		<a href="<?php echo JRoute::_('index.php?option=com_kinoarhiv&view=profile&tab=favorite&page=names&Itemid='.$this->itemid); ?>" class="subtab-name<?php echo ($this->page == 'names') ? ' current' : ''; ?>"><?php echo JText::_('COM_KA_PERSONS'); ?></a>
	</div>
	<?php if (count($this->items) > 0): ?>
	<form action="<?php JRoute::_('index.php'); ?>" method="post" id="adminForm" autocomplete="off">
		<div class="total-favorite"><?php echo JText::_('COM_KA_PROFILE_TOTAL_FAVORITE').$this->pagination->get('total').JText::_('COM_KA_PROFILE_TOTAL_'.strtoupper($this->page).'_'.$plural[0]); ?></div>
		<div class="fav-list">
			<?php foreach ($this->items as $i=>$item): ?>
			<div class="title-small">
				<span><?php echo JHtml::_('grid.id', $i, $item->id, false, 'ids'); ?> <a href="<?php echo JRoute::_('index.php?option=com_kinoarhiv&view='.$view.'&id='.$item->id.'&Itemid='.$this->itemid); ?>"><?php echo $item->title.$item->year_str; ?></a></span>
				<span style="float: right;"><a class="cmd-fav-delete" href="<?php echo JRoute::_('index.php?option=com_kinoarhiv&view='.$this->page.'&task=favorite&action=delete&Itemid='.$this->itemid.'&id='.$item->id); ?>" title="<?php echo JText::_('COM_KA_REMOVEFROM_FAVORITE'); ?>"><img src="components/com_kinoarhiv/assets/themes/component/default/images/icons/delete_16.png" border="0" /></a></span>
			</div>
			<?php endforeach; ?>
			<input class="hasTooltip" type="checkbox" onclick="Joomla.checkAll(this)" title="<?php echo JText::_('COM_KA_CHECK_ALL'); ?>" value="" name="checkall-toggle" id="checkall-toggle"><label for="checkall-toggle"><?php echo JText::_('COM_KA_CHECK_ALL'); ?></label>
		</div><br />
		<input type="hidden" name="boxchecked" value="0" />
		<input type="hidden" name="option" value="com_kinoarhiv" />
		<input type="hidden" name="view" value="<?php echo $this->page; ?>" />
		<input type="hidden" name="task" value="favorite" />
		<input type="hidden" name="tab" value="favorite" />
		<input type="hidden" name="action" value="delete" />
		<input type="hidden" name="Itemid" value="<?php echo $this->itemid; ?>" />
		<input type="hidden" name="return" value="profile" />
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
