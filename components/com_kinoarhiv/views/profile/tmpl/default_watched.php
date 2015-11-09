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

$plural = $this->lang->getPluralSuffixes($this->pagination->total);
JHtml::_('script', 'components/com_kinoarhiv/assets/js/ui.aurora.min.js');
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

		$('.hasTip, .hasTooltip').attr('data-uk-tooltip', '');

		$('.cmd-wt-delete').click(function (e) {
			e.preventDefault();
			var _this = $(this);

			$.ajax({
				url: _this.attr('href') + '&format=raw'
			}).done(function (response) {
				if (response.success) {
					_this.closest('div').remove();
					showMsg('.wt-list', response.message);
				} else {
					showMsg('.wt-list', '<?php echo JText::_('JERROR_AN_ERROR_HAS_OCCURRED'); ?>');
				}
			}).fail(function (xhr, status, error) {
				showMsg('.wt-list', error);
			});
		});

		$('#checkall-toggle').click(function () {
			if ($(this).is(':checked')) {
				$('.wt-list .title-small :checkbox').prop('checked', true);
			} else {
				$('.wt-list .title-small :checkbox').prop('checked', false);
			}
		});

		$('#adminForm').submit(function (e) {
			var items = $('input', this).filter(':checked');

			if (items.length == 0 || items.length < 0) {
				return false;
			}
		});
	});
	//]]>
</script>
<div class="uk-article ka-content user-profile watched">
	<?php echo $this->loadTemplate('tabs'); ?>
	<?php if (count($this->items) > 0): ?>
		<form action="<?php JRoute::_('index.php'); ?>" method="post" id="adminForm" autocomplete="off">
			<div class="total-watched"><?php echo JText::_('COM_KA_PROFILE_TOTAL_WATCHED') . $this->pagination->total . JText::_('COM_KA_PROFILE_TOTAL_MOVIES_' . $plural[0]); ?></div>
			<div class="wt-list">
				<?php foreach ($this->items as $i => $item): ?>
					<div class="title-small">
						<span><input id="cb<?php echo $i; ?>" type="checkbox" value="<?php echo $item->id; ?>" name="ids[]"> <a href="<?php echo JRoute::_('index.php?option=com_kinoarhiv&view=movie&id=' . $item->id . '&Itemid=' . $this->itemid); ?>"><?php echo $item->title . $item->year_str; ?></a></span>
						<span style="float: right;"><a class="cmd-wt-delete" href="<?php echo JRoute::_('index.php?option=com_kinoarhiv&task=watched&action=delete&Itemid=' . $this->itemid . '&id=' . $item->id); ?>" title="<?php echo JText::_('COM_KA_REMOVEFROM_WATCHED'); ?>"><img src="components/com_kinoarhiv/assets/themes/component/default/images/icons/delete_16.png" border="0"/></a></span>
					</div>
				<?php endforeach; ?>
				<input class="hasTooltip" type="checkbox" title="<?php echo JText::_('COM_KA_CHECK_ALL'); ?>" value="" name="checkall-toggle" id="checkall-toggle"><label for="checkall-toggle"><?php echo JText::_('COM_KA_CHECK_ALL'); ?></label>
			</div>
			<br/>
			<input type="hidden" name="boxchecked" value="0"/>
			<input type="hidden" name="option" value="com_kinoarhiv"/>
			<input type="hidden" name="task" value="watched"/>
			<input type="hidden" name="action" value="delete"/>
			<input type="hidden" name="Itemid" value="<?php echo $this->itemid; ?>"/>
			<?php echo JHtml::_('form.token'); ?>
			<input type="submit" class="btn btn-primary uk-button uk-button-primary" value="<?php echo JText::_('COM_KA_REMOVE_SELECTED'); ?>"/>
		</form>

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
		<div><?php echo KAComponentHelper::showMsg(JText::_('COM_KA_NO_ITEMS')); ?></div>
	<?php endif; ?>
</div>
