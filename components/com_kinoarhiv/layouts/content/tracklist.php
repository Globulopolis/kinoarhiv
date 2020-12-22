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

JHtml::_('stylesheet', 'media/com_kinoarhiv/css/colorbox.css');
JHtml::_('script', 'media/com_kinoarhiv/js/jquery.colorbox.min.js');
KAComponentHelper::getScriptLanguage('jquery.colorbox-', 'media/com_kinoarhiv/js/i18n/colorbox');

/** @var array $displayData */
$params = $displayData['params'];
$item   = $displayData['item'];
$tracks = array();
?>
<script type="text/javascript">
	jQuery(document).ready(function ($) {
		$('.cmd-track-info').click(function(e){
			e.preventDefault();

			$.colorbox({
				html: '<div class="desc">' + $('span#info_' + $(this).data('id')).html() + '</div>',
				height: '80%',
				width: '80%'
			});
		});

		$('.cmd-playlist-add').click(function(e){
			e.preventDefault();

			alert('Not implemented.');
		});
	});
</script>
<br />
<div class="player-layout">
	<div class="row-fluid">
	<?php if (!$displayData['guest']): ?>
		<div class="span12">
		<?php $playerLayout = ($params->get('player_type') == '-1') ? 'player' : 'player_' . $params->get('player_type');
			echo JLayoutHelper::render('layouts.content.audio_' . $playerLayout,
				array('id' => $item->id, 'tracks' => json_encode($item->playlist), 'total' => count($item->playlist)),
				JPATH_COMPONENT
			);
		?>
		</div>
	<?php endif; ?>
	</div>
	<div class="row-fluid">
		<table class="track-list table table-striped">
			<thead>
				<tr>
					<th>#</th>
					<th></th>
					<th></th>
					<th class="hidden-phone"></th>
				</tr>
			</thead>
			<tbody>
			<?php foreach ($item->tracks as $index => $track):
				if ($track->album_id == $item->id): ?>
				<tr class="track-row" itemprop="track" itemscope itemtype="https://schema.org/MusicRecording">
					<td class="track-number span1"><?php echo !empty($track->track_number) ? $track->track_number . '. ' : ''; ?></td>
					<td class="track-title span9">
					<?php if (!$displayData['guest']): ?>
						<a href="<?php echo $track->src; ?>" class="cmd-play-audio"
						   data-trackid="<?php echo $index; ?>"><?php echo $this->escape($track->title); ?> (<?php echo $track->composer; ?>)</a>
						<div class="hidden-desktop" style="padding-top: 1em;">
							<a href="#" class="cmd-playlist-add"
							   title="<?php echo JText::_('COM_KA_PLAYLIST_ADD'); ?>"><?php echo JText::_('COM_KA_PLAYLIST_ADD'); ?></a><br/>
							<a href="#" class="cmd-track-info" data-id="<?php echo $index; ?>"
							   title="<?php echo JText::_('COM_KA_TRACK_INFO'); ?>"><?php echo JText::_('COM_KA_TRACK_INFO'); ?></a>
						</div>
					<?php else: ?>
						<span itemprop="name"><?php echo $this->escape($track->title); ?></span>
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
						</span>
					<?php if (!empty($track->buy_url)):
						//echo $track->buy_url;
					endif; ?>
					</td>
				</tr>
				<?php endif;
			endforeach; ?>
			</tbody>
		</table>
	</div>
</div>
