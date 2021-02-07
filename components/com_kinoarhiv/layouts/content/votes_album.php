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

JHtml::_('script', 'media/com_kinoarhiv/js/jquery.rateit.min.js');

/** @var array $displayData */
$params        = $displayData['params'];
$item          = $displayData['item'];
$guest         = $displayData['guest'];
$itemid        = $displayData['itemid'];
$profileItemid = isset($displayData['profileItemid']);
$view          = $displayData['view'];
$voteURL       = 'index.php?option=com_kinoarhiv&Itemid=' . $itemid . '&format=json&' . JSession::getFormToken() . '=1';

$rateDivClass = '';

if ($view == 'album')
{
	$rateDivClass = 'rate';
}
?>
<?php if (($item->attribs->allow_votes == '' && $params->get('allow_votes')) || $item->attribs->allow_votes): ?>
	<?php if (!$guest && $params->get('allow_votes') && $view == 'album'): ?>
		<?php if ($params->get('ratings_show_local')): ?>
			<div class="clear"></div>
			<div class="local-rt<?php echo $item->rate_label_class; ?> <?php echo $rateDivClass; ?>">
				<p><strong><?php echo JText::_('COM_KA_MUSIC_RATE'); ?></strong></p>

				<div class="rateit" data-rateit-value="<?php echo $item->rate_value; ?>" data-rateit-step="1"
					 data-rateit-min="0" data-rateit-max="<?php echo (int) $params->get('vote_summ_num'); ?>"
					 data-rateit-url="<?php echo JRoute::_($voteURL, false); ?>" data-rateit-content="albums"
					 data-rateit-id="<?php echo $item->id; ?>"></div>
				&nbsp;<span><?php echo $item->rate_label; ?></span>
				<?php if (isset($item->total_votes)): ?>
				<span class="total-votes small" title="<?php echo JText::_('COM_KA_RATE_VOTES_TOTAL'); ?>">(<?php echo $item->total_votes; ?>)</span>
				<?php endif; ?>

				<div class="my_votes" style="<?php echo ($item->my_vote == 0) ? 'display: none;' : ''; ?>">
					<div class="my_vote">
						<span class="vote_rate">
							<?php echo JText::_('COM_KA_RATE_MY'); ?>
							<?php echo JText::sprintf('COM_KA_RATE_LOCAL_MORE', $item->my_vote, (int) $params->get('vote_summ_num')); ?>
						</span>
						&nbsp;<span class="vote_date small">(<?php echo JHtml::_('date', $item->_datetime, JText::_('DATE_FORMAT_LC3')); ?>)</span>
					</div>
					<?php if ($profileItemid): ?>
					<a href="<?php echo JRoute::_('index.php?option=com_kinoarhiv&view=profile&page=votes&tab=albums&Itemid=' . $displayData['profileItemid']); ?>"><?php echo JText::_('COM_KA_RATE_MY_ALL'); ?></a>
					<?php endif; ?>
				</div>
			</div>
		<?php endif; ?>
	<?php else: ?>
		<?php if ($params->get('ratings_show_local')): ?>
			<div class="clear"></div>
			<div class="local-rt<?php echo $item->rate_label_class; ?> <?php echo $rateDivClass; ?>">
				<?php if ($view == 'album'): ?><p><strong><?php echo JText::_('COM_KA_MUSIC_RATE'); ?></strong></p><?php endif; ?>

				<div class="rateit" data-rateit-value="<?php echo $item->rate_value; ?>" data-rateit-min="0"
					 data-rateit-max="<?php echo (int) $params->get('vote_summ_num'); ?>" data-rateit-ispreset="true"
					 data-rateit-readonly="true"></div>
				&nbsp;<?php echo $item->rate_label; ?>
				<?php if ($view == 'album' && isset($item->total_votes)): ?>
				<span class="total-votes small" title="<?php echo JText::_('COM_KA_RATE_VOTES_TOTAL'); ?>">(<?php echo $item->total_votes; ?>)</span>
				<?php endif; ?>

				<?php if ($params->get('allow_votes') && $view === 'album'): ?>
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
<?php endif;
