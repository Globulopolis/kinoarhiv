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

/** @var array $displayData */
$tracks = $displayData['tracks'];
?>
<br />
<table class="track-list table table-striped">
	<thead>
		<tr>
			<th>#</th>
			<th colspan="2"></th>
		</tr>
	</thead>
	<tbody>
	<?php foreach ($tracks as $track):
		if ($track->album_id == $displayData['albumID']): ?>
		<tr class="track-row" itemprop="track" itemscope itemtype="https://schema.org/MusicRecording">
			<td class="track-number span1"><?php echo !empty($track->track_number) ? $track->track_number . '. ' : ''; ?></td>
			<td class="track-title span9"><span itemprop="name"><?php echo $this->escape($track->title); ?></span></td>
			<td class="track-length span2">
				<meta content="<?php echo KAContentHelper::timeToISO8601($track->length); ?>" itemprop="duration" /><?php echo $track->length; ?>
			</td>
		</tr>
		<?php endif;
	endforeach; ?>
	</tbody>
</table>
