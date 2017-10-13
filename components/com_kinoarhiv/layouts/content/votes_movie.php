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

$params   = $displayData['params'];
$item     = $displayData['item'];
$guest    = $displayData['guest'];
$itemid   = $displayData['itemid'];
$auth_msg = isset($displayData['auth_msg']) ? true : false;
?>
<?php if (($item->attribs->allow_votes == '' && $params->get('allow_votes') == 1) || $item->attribs->allow_votes == 1): ?>
	<?php if (!$guest && $params->get('allow_votes') == 1): ?>
		<?php if ($params->get('ratings_show_local') == 1): ?>
			<div class="clear"></div>
			<div class="rate">
				<strong><?php echo JText::_('COM_KA_RATE'); ?></strong><br/>
				<select id="rate_field" autocomplete="off">
					<?php for ($i = 0, $n = (int) $params->get('vote_summ_num') + 1; $i < $n; $i++): ?>
						<option value="<?php echo $i; ?>"<?php echo ($i == round($item->rate_loc_label)) ? ' selected="selected"' : ''; ?>><?php echo $i; ?></option>
					<?php endfor; ?>
				</select>

				<div class="rateit" data-rateit-value="<?php echo round($item->rate_loc_label); ?>" data-rateit-backingfld="#rate_field"
					 data-url="<?php echo JRoute::_('index.php?option=com_kinoarhiv&view=movie&task=vote&id=' . $item->id . '&Itemid=' . $itemid . '&format=raw', false); ?>"></div>
				&nbsp;<span><?php echo $item->rate_loc_label; ?></span>

				<div class="my_votes" style="<?php echo ($item->my_vote == 0) ? 'display: none;' : ''; ?>">
					<div class="my_vote"><?php echo JText::sprintf('COM_KA_RATE_MY', $item->my_vote, (int) $params->get('vote_summ_num')); ?>
						&nbsp;<span class="small">(<?php echo JHtml::_('date', $item->_datetime, JText::_('DATE_FORMAT_LC3')); ?>
							)</span></div>
					<a href="<?php echo JRoute::_('index.php?option=com_kinoarhiv&view=profile&page=votes&Itemid=' . $itemid); ?>" class="small"><?php echo JText::_('COM_KA_RATE_MY_ALL'); ?></a>
				</div>
			</div>
		<?php endif; ?>
	<?php else: ?>
		<?php if ($params->get('ratings_show_local') == 1): ?>
			<div class="clear"></div>
			<div class="rate">
				<strong><?php echo JText::_('COM_KA_RATE'); ?></strong><br/>

				<div class="rateit" data-rateit-value="<?php echo $item->rate_loc_c; ?>" data-rateit-min="0" data-rateit-max="<?php echo (int) $params->get('vote_summ_num'); ?>" data-rateit-ispreset="true" data-rateit-readonly="true"></div>
				&nbsp;<?php echo $item->rate_loc_label; ?>

				<?php if ($params->get('allow_votes') == 1 && $auth_msg): ?>
					<div>
						<?php echo KAComponentHelper::showMsg(
							JText::sprintf(
								JText::_('COM_KA_VOTES_AUTHREQUIRED'),
								'<a href="' . JRoute::_('index.php?option=com_users&view=registration') . '">' . JText::_('COM_KA_REGISTER') . '</a>',
								'<a href="' . JRoute::_('index.php?option=com_users&view=login') . '">' . JText::_('COM_KA_LOGIN') . '</a>'
							)
						); ?>
					</div>
				<?php endif; ?>
			</div>
		<?php endif; ?>
	<?php endif; ?>
	<div class="clear"></div>
<?php endif; ?>
