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

$document = JFactory::getDocument();

JHtml::_('jquery.framework');
JHtml::_('stylesheet', 'media/com_kinoarhiv/players/mediaelement/mediaelementplayer.min.css');
$document->addScriptDeclaration("mejs.i18n.language('" . substr(JFactory::getLanguage()->getTag(), 0, 2) . "');");
JHtml::_('script', 'media/com_kinoarhiv/players/mediaelement/mediaelement-and-player.min.js');
KAComponentHelper::getScriptLanguage('', 'media/com_kinoarhiv/players/mediaelement/lang', true, true);
?>
<script type="text/javascript">
	jQuery(document).ready(function ($) {
		var playlist = <?php echo $displayData['tracks']; ?>,
			playing  = 0, // Current playing track index.
			state    = 0, // Playing state.
			total    = <?php echo (int) $displayData['total'] - 1; ?>;

		var player = new MediaElementPlayer('player_<?php echo $displayData['id']; ?>', {
			success: function(player, node, instance){
				var container = document.querySelector('.mejs__container');

				container.lang = mejs.i18n.language();
				player.setSrc(playlist[0].src);

				player.addEventListener('playing', function(e){
					$('.track-list a.cmd-play-audio').removeClass('audio-paused');
					$('.track-list a[data-trackid="' + playing + '"]').addClass('audio-playing');
					state = 1;
				});
				player.addEventListener('pause', function(e){
					$('.track-list a[data-trackid="' + playing + '"]').addClass('audio-paused');
					state = 2;
				});
				player.addEventListener('ended', function(e){
					$('.track-list a.cmd-play-audio').removeClass('audio-playing audio-paused');

					// Play the next track until the list ends.
					if (playing < total) {
						var next = playing + 1;
						player.setSrc(playlist[next].src);
						playing  = next;
						player.play();
					} else {
						state = 0;
					}
				});
			},
			error: function(player, node, instance){
				state = 0;
				Aurora.message([{text: '<?php echo JText::_('JERROR_LAYOUT_REQUESTED_RESOURCE_WAS_NOT_FOUND'); ?>', type: 'error'}], $('div.player'), {replace: true});
			}
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
				player.setSrc(playlist[index].src);
				playing = index;
				state   = 1;
				player.play();
			}

			$('.track-list a[data-trackid="' + index + '"]').addClass('audio-playing');
		});
	});
</script>
<div class="player">
	<audio controls style="width: 100%;" id="player_<?php echo $displayData['id']; ?>">Your browser doesn't support HTML5 audio.</audio>
</div>
