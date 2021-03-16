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

JHtml::_('script', 'media/com_kinoarhiv/js/frontend.min.js');
JHtml::_('stylesheet', 'media/com_kinoarhiv/css/colorbox.css');
JHtml::_('script', 'media/com_kinoarhiv/js/jquery.colorbox.min.js');
KAComponentHelper::getScriptLanguage('jquery.colorbox-', 'media/com_kinoarhiv/js/i18n/colorbox');

/** @var array $displayData */
$params = $displayData['params'];
$item   = $displayData['item'];
$lang   = JFactory::getLanguage();
$namesItemid = KAContentHelper::getItemid('names');
?>
<div class="player-layout">
	<div class="row-fluid">
		<table class="track-list table table-striped">
			<thead class="hidden-phone">
				<tr>
					<th>#</th>
					<th></th>
					<th></th>
					<th class="hidden-phone"></th>
				</tr>
			</thead>
			<tbody>
			<?php foreach ($item->tracks as $index => $track):
				if ($track->album_id == $item->id):
					// TODO Вывести поле комментария
					$performer = '';

					if (!empty($track->performed_by)):
						$performerUrl = JRoute::_('index.php?option=com_kinoarhiv&view=name&id=' . $track->performed_by_id . '&Itemid=' . $namesItemid . '&lang=' . $lang->getTag());
						$performer = ' <span class="small">(' . JText::sprintf('COM_KA_TRACK_PERFORMED_BY', $performerUrl, $track->performed_by) . ')</span>';
					endif;
					?>
				<tr class="track-row" itemprop="track" itemscope itemtype="https://schema.org/MusicRecording">
					<td class="track-number span1"><?php echo !empty($track->track_number) ? $track->track_number . '. ' : ''; ?></td>
					<td class="track-title span9">
					<?php if (!$displayData['guest']): ?>
						<a href="<?php echo $track->src; ?>" class="cmd-play-audio"
						   data-trackid="<?php echo $index; ?>"><?php echo $this->escape($track->title); ?></a>
						<?php echo $performer; ?>
						<div class="hidden-desktop" style="padding-top: 1em;">
							<a href="#" class="cmd-playlist-add"
							   title="<?php echo JText::_('COM_KA_PLAYLIST_ADD'); ?>"><?php echo JText::_('COM_KA_PLAYLIST_ADD'); ?></a><br/>
							<a href="#" class="cmd-track-info" data-id="<?php echo $index; ?>"
							   title="<?php echo JText::_('COM_KA_TRACK_INFO'); ?>"><?php echo JText::_('COM_KA_TRACK_INFO'); ?></a>
						</div>
					<?php else: ?>
						<span itemprop="name"><?php echo $this->escape($track->title); ?></span><?php echo $performer; ?>
						<div class="hidden-desktop" style="padding-top: 1em;">
							<a href="#" class="cmd-track-info" data-id="<?php echo $index; ?>"
							   title="<?php echo JText::_('COM_KA_TRACK_INFO'); ?>"><?php echo JText::_('COM_KA_TRACK_INFO'); ?></a>
						</div>
					<?php endif; ?>
					</td>
					<td class="track-length span1">
						<meta content="<?php echo KAContentHelper::timeToISO8601($track->length); ?>" itemprop="duration" />
						<?php echo KAContentHelper::formatTrackLength($track->length); ?>
					</td>
					<td class="hidden-phone span1">
						<?php if (!$displayData['guest']): ?>
						<a href="#" class="cmd-playlist-add hasTooltip" title="<?php echo JText::_('COM_KA_PLAYLIST_ADD'); ?>"></a>
						<?php endif; ?>
						<a href="#" class="cmd-track-info hasTooltip" data-id="<?php echo $index; ?>"
						   title="<?php echo JText::_('COM_KA_TRACK_INFO'); ?>"></a>
						<span class="track-info" id="info_<?php echo $index; ?>">
							<strong><?php echo !empty($track->track_number) ? $track->track_number . '. ' : ''; ?><?php echo $this->escape($track->title); ?></strong><br/>
							<?php echo $track->isrc; ?><br/>
							<?php echo $track->comments; ?>
						</span>
					<?php if (!empty($track->buy_url)):
						$buyUrl = preg_replace_callback(
							'#_(.*)_#u',
							function ($matches)
							{
								return JText::_($matches[1]);
							},
							$track->buy_url
						);
						?>
						<span class="buyurl"><?php echo $buyUrl; ?></span>
					<?php endif; ?>
					</td>
				</tr>
				<?php endif;
			endforeach; ?>
			</tbody>
		</table>
	</div>
</div>
