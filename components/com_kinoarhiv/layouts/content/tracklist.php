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

/** @var object $displayData */
?>
<br />
<div class="row-fluid">
<?php if (!$displayData->user->get('guest')): ?>
	<div class="span12"><?php echo $displayData->loadTemplate('player'); ?></div>
<?php endif; ?>
</div>
<div class="row-fluid">
	<table class="track-list table table-striped" data-trackpath="<?php echo $displayData->item->tracks_path_www; ?>/">
		<thead>
			<tr>
				<th>#</th>
				<th colspan="2"></th>
			</tr>
		</thead>
		<tbody>
		<?php foreach ($displayData->item->tracks as $track):
			if ($track->album_id == $displayData->item->id): ?>
			<tr class="track-row" itemprop="track" itemscope itemtype="https://schema.org/MusicRecording">
				<td class="track-number span1"><?php echo !empty($track->track_number) ? $track->track_number . '. ' : ''; ?></td>
				<td class="track-title span9">
				<?php if (!$displayData->user->get('guest')): ?>
					<a href="#" class="cmd-play-audio" data-track="<?php echo $track->filename; ?>"><?php echo $this->escape($track->title); ?></a>
				<?php else: ?>
					<span itemprop="name"><?php echo $this->escape($track->title); ?></span>
				<?php endif; ?>
				</td>
				<td class="track-length span2">
					<meta content="<?php echo KAContentHelper::timeToISO8601($track->length); ?>" itemprop="duration" />
					<?php echo KAContentHelper::formatTrackLength($track->length); ?>
				</td>
			</tr>
			<?php endif;
		endforeach; ?>
		</tbody>
	</table>
</div>
