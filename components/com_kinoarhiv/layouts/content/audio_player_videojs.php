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

JHtml::_('stylesheet', 'media/com_kinoarhiv/players/videojs/video-js.min.css');
JHtml::_('script', 'media/com_kinoarhiv/players/videojs/video.min.js');
KAComponentHelper::getScriptLanguage('', 'media/com_kinoarhiv/players/videojs/lang');
?>
<script type="text/javascript">
	jQuery(document).ready(function ($) {
		var player = videojs('player_<?php echo $displayData['id']; ?>', {
			controlBar: {fullscreenToggle: false}
		});
		var playlist = <?php echo $displayData['tracks']; ?>,
			playing  = 0, // Current playing track index.
			state    = 0, // Playing state.
			total    = <?php echo (int) $displayData['total'] - 1; ?>;

		player.src(playlist[0].src);

		player.on('fullscreenchange', function(e){
			if (player.isFullscreen()) {
				player.exitFullscreen();
			}
		});
		player.on('playing', function(e){
			$('.track-list a.cmd-play-audio').removeClass('audio-paused');
			$('.track-list a[data-trackid="' + playing + '"]').addClass('audio-playing');
			state = 1;
		});
		player.on('pause', function(e){
			$('.track-list a[data-trackid="' + playing + '"]').addClass('audio-paused');
			state = 2;
		});
		player.on('ended', function(e){
			$('.track-list a.cmd-play-audio').removeClass('audio-playing audio-paused');

			// Play the next track until the list ends.
			if (playing < total) {
				var next = playing + 1;
				player.src(playlist[next].src);
				playing  = next;
				player.play();
			} else {
				state = 0;
			}
		});
		player.on('error', function(e){
			state = 0;
			Aurora.message([{text: '<?php echo JText::_('JERROR_LAYOUT_REQUESTED_RESOURCE_WAS_NOT_FOUND'); ?>', type: 'error'}], $('div.player'), {replace: true});
		});

		$('.cmd-play-audio').click(function(e){
			e.preventDefault();

			var index = parseInt($(this).data('trackid'));

			$('.track-list a.cmd-play-audio').removeClass('audio-playing audio-paused');

			if (state === 2 && playing === index) {
				player.play();
				state = 1;
			} else if (state === 1 && playing === index) {
				player.pause();
				state = 2;
			} else {
				player.src(playlist[index].src);
				playing    = index;
				state      = 1;
				player.play();
			}

			$('.track-list a[data-trackid="' + index + '"]').addClass('audio-playing');
		});
	});
</script>
<div class="player">
	<audio controls preload="none" style="width: 100%;" class="video-js vjs-default-skin vjs-b" height="30"
		   id="player_<?php echo $displayData['id']; ?>">Your browser doesn't support HTML5 audio.</audio>
</div>
