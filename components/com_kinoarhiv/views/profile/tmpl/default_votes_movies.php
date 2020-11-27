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
<div class="uk-article ka-content user-profile votes">
	<?php echo $this->loadTemplate('tabs'); ?>

	<div class="subtabs breadcrumb">
		<a href="<?php echo JRoute::_('index.php?option=com_kinoarhiv&view=profile&page=votes&tab=movies&Itemid=' . $this->itemid); ?>"
		   class="subtab-movie<?php echo ($this->tab == 'movies') ? ' current' : ''; ?>"><?php echo JText::_('COM_KA_MOVIES'); ?></a>
		<a href="<?php echo JRoute::_('index.php?option=com_kinoarhiv&view=profile&page=votes&tab=albums&Itemid=' . $this->itemid); ?>"
		   class="subtab-album<?php echo ($this->tab == 'albums') ? ' current' : ''; ?>"><?php echo JText::_('COM_KA_MUSIC_ALBUMS'); ?></a>
	</div>

	<?php if (count($this->items) > 0): ?>
		<div class="total-votes"><?php echo JText::sprintf('COM_KA_PROFILE_TOTAL_VOTES', $this->pagination->total); ?></div>

		<form action="<?php JRoute::_('index.php'); ?>" method="post" id="profileForm" autocomplete="off">
			<table class="table table-striped items-list">
				<thead>
				<tr>
					<th></th>
					<th><?php echo JText::_('COM_KA_SEARCH_ADV_MOVIES_TITLE_LABEL'); ?></th>
					<th><?php echo substr(JText::_('COM_KA_RATE_MY'), 0, -2); ?></th>
					<th><?php echo JText::_('JDATE'); ?></th>
					<th><?php echo JText::_('COM_KA_RATE'); ?></th>
				</tr>
				</thead>
				<tbody>
				<?php foreach ($this->items as $i => $item):
					$title = $this->escape(KAContentHelper::formatItemTitle($item->title, '', $item->year));

					if (!empty($item->rate_sum_loc) && !empty($item->rate_loc))
					{
						$plural = $this->lang->getPluralSuffixes($item->rate_loc);
						$item->rate_loc_value = round($item->rate_sum_loc / $item->rate_loc, (int) $this->params->get('vote_summ_precision'));
						$item->rate_loc_label = JText::sprintf(
							'COM_KA_RATE_LOCAL_' . $plural[0],
							$item->rate_loc_value,
							(int) $this->params->get('vote_summ_num'),
							$item->rate_loc
						);
					}
					else
					{
						$item->rate_loc_value = 0;
						$item->rate_loc_label = JText::_('COM_KA_RATE_NO');
					}
					?>
					<tr>
						<td width="2%">
							<input id="cb<?php echo $i; ?>" type="checkbox" value="<?php echo $item->id; ?>"
								   name="ids[]" title="<?php echo JText::_('JSELECT')?>" />
						</td>
						<td>
							<a href="<?php echo JRoute::_('index.php?option=com_kinoarhiv&view=movie&id=' . $item->id . '&Itemid=' . $this->itemid); ?>"><?php echo $title; ?></a>
						</td>
						<td width="15%"><?php echo $item->my_vote; ?></td>
						<td width="17%"><?php echo $item->_datetime; ?></td>
						<td width="15%"><?php echo $item->rate_loc_label; ?></td>
					</tr>
				<?php endforeach; ?>
				</tbody>
				<tfoot>
				<tr>
					<td colspan="5">
						<input type="checkbox" title="<?php echo JText::_('COM_KA_CHECK_ALL'); ?>" value=""
							   name="checkall-toggle" id="checkall-toggle">
						<label for="checkall-toggle"><?php echo JText::_('COM_KA_CHECK_ALL'); ?></label>
					</td>
				</tr>
				</tfoot>
			</table>

			<input type="hidden" name="option" value="com_kinoarhiv"/>
			<input type="hidden" name="view" value="<?php echo $this->tab; ?>"/>
			<input type="hidden" name="task" value="movies.votesRemove"/>
			<input type="hidden" name="return" value="<?php echo base64_encode('view=profile&page=votes&tab=movies'); ?>"/>
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
		<div><?php echo KAComponentHelper::showMsg(JText::_('COM_KA_RATE_NORATE')); ?></div>
	<?php endif; ?>
</div>
