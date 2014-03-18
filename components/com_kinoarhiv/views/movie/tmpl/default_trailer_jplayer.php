<?php defined('_JEXEC') or die;
GlobalHelper::loadPlayerAssets($this->params->get('ka_theme'), $this->params->get('player_type'));

if (isset($this->item->trailer) && count($this->item->trailer) > 0):
	$item_trailer = $this->item->trailer;
	$trailer_media_supplied = ''; ?>
	<script type="text/javascript">
		jQuery(document).ready(function($){
			var trailer_player = $('#trailer_player'),
				trailer_player_data,
				fixFlash_mp4,
				fixFlash_mp4_id,
				ignore_timeupdate,
				options = {
					ready: function(event){
						if (event.jPlayer.status.noVolume) {
							$('.jp-gui').addClass('jp-no-volume');
							$('.jp-current-volume').text('0');
						}

						$('.jp-current-volume').text((event.jPlayer.options.volume * 100).toFixed(0));
						fixFlash_mp4 = event.jPlayer.flash.used && /m4a|m4v/.test(event.jPlayer.options.supplied);

						$(this).jPlayer('setMedia', {
						<?php foreach ($item_trailer->files['video'] as $item):
							$type = explode('/', $item['type']);
							if ($type[1] == 'mp4') {
								$type[1] = 'm4v';
							}
							$movie_media_supplied .= $type[1].', '; ?>
							<?php echo $type[1]; ?>: '<?php echo $item_trailer->path.$item['src']; ?>',
						<?php endforeach; ?>
							poster: '<?php echo $item_trailer->path.$item_trailer->screenshot; ?>'
						});
					},
					timeupdate: function(event){
						if (!ignore_timeupdate) {
							myControl.progress.slider('value', event.jPlayer.status.currentPercentAbsolute);
						}
					},
					volumechange: function(event){
						if (event.jPlayer.options.muted) {
							myControl.volume.slider('value', 0);
							$('.jp-current-volume').text('0');
						} else {
							myControl.volume.slider('value', event.jPlayer.options.volume);
							$('.jp-current-volume').text((event.jPlayer.options.volume * 100).toFixed(0));
						}
					},
					swfPath: '<?php echo JURI::base(); ?>components/com_kinoarhiv/assets/players/jplayer/jplayer.swf',
					supplied: '<?php echo substr($movie_media_supplied, 0, -2); ?>',
					cssSelectorAncestor: '#movie_player_data',
					wmode: 'window',
					keyEnabled: true,
					size: {
						width: '<?php echo $item_trailer->player_width; ?>px',
						height: '<?php echo $item_trailer->player_height; ?>px'
					},
					preload: 'none',
					timeFormat: {
						showHour: true
					}
				},
				myControl = {
					progress: $(options.cssSelectorAncestor + ' .jp-progress-slider'),
					volume: $(options.cssSelectorAncestor + ' .jp-volume-slider')
				};

				trailer_player.jPlayer(options);
				trailer_player_data = trailer_player.data('jPlayer');

				$('.jp-gui ul li').hover(
					function() { $(this).addClass('ui-state-hover'); },
					function() { $(this).removeClass('ui-state-hover'); }
				);

				myControl.progress.slider({
					animate: 'fast',
					max: 100,
					range: 'min',
					step: 0.1,
					value : 0,
					slide: function(event, ui){
						var sp = trailer_player_data.status.seekPercent;
						if (sp > 0) {
							if (fixFlash_mp4) {
								ignore_timeupdate = true;
								clearTimeout(fixFlash_mp4_id);
								fixFlash_mp4_id = setTimeout(function(){
									ignore_timeupdate = false;
								}, 1000);
							}

							trailer_player.jPlayer('playHead', ui.value * (100 / sp));
						} else {
							setTimeout(function(){
								myControl.progress.slider('value', 0);
							}, 0);
						}
					}
				});

				myControl.volume.slider({
					animate: 'fast',
					max: 1,
					range: 'min',
					step: 0.01,
					value: $.jPlayer.prototype.options.volume,
					slide: function(event, ui){
						trailer_player.jPlayer('option', 'muted', false);
						trailer_player.jPlayer('option', 'volume', ui.value);
					}
				});
		});
	</script>
	<div class="clear"></div>
	<div class="ui-widget trailer">
		<h3><?php echo JText::_('COM_KA_WATCH_TRAILER'); ?></h3>
		<div>
		<?php if ($item_trailer->embed_code != ''):
			echo $item_trailer->embed_code;
		else: ?>
			<?php if (count($item_trailer->files['video']) > 0): ?>
			<div>
				<div id="trailer_player"></div>
				<div id="trailer_player_container">
					<div class="jp-gui ui-widget ui-widget-content ui-corner-all">
						<ul>
							<li class="jp-play ui-state-default ui-corner-all">
								<a href="javascript:;" class="jp-play ui-icon ui-icon-play hasTooltip" tabindex="1" title="play">play</a>
							</li>
							<li class="jp-pause ui-state-default ui-corner-all">
								<a href="javascript:;" class="jp-pause ui-icon ui-icon-pause hasTooltip" tabindex="1" title="pause">pause</a>
							</li>
							<li class="jp-stop ui-state-default ui-corner-all">
								<a href="javascript:;" class="jp-stop ui-icon ui-icon-stop hasTooltip" tabindex="1" title="stop">stop</a>
							</li>
							<li class="jp-repeat ui-state-default ui-corner-all">
								<a href="javascript:;" class="jp-repeat ui-icon ui-icon-refresh hasTooltip" tabindex="1" title="repeat">repeat</a>
							</li>
							<li class="jp-repeat-off ui-state-default ui-state-active ui-corner-all">
								<a href="javascript:;" class="jp-repeat-off ui-icon ui-icon-arrow-1-e hasTooltip" tabindex="1" title="repeat off">repeat off</a>
							</li>
							<li class="jp-full-screen ui-state-default ui-corner-all">
								<a href="javascript:;" class="jp-full-screen ui-icon ui-icon-arrow-4-diag hasTooltip" tabindex="1" title="full screen">full screen</a>
							</li>
							<li class="jp-restore-screen ui-state-default ui-state-active ui-corner-all">
								<a href="javascript:;" class="jp-restore-screen ui-icon ui-icon-arrow-2-e-w hasTooltip" tabindex="1" title="restore screen">restore screen</a>
							</li>
							<li class="jp-mute ui-state-default ui-corner-all">
								<a href="javascript:;" class="jp-mute ui-icon ui-icon-volume-off hasTooltip" tabindex="1" title="mute">mute</a>
							</li>
							<li class="jp-unmute ui-state-default ui-state-active ui-corner-all">
								<a href="javascript:;" class="jp-unmute ui-icon ui-icon-volume-off hasTooltip" tabindex="1" title="unmute">unmute</a>
							</li>
							<li class="jp-volume-max ui-state-default ui-corner-all">
								<a href="javascript:;" class="jp-volume-max ui-icon ui-icon-volume-on hasTooltip" tabindex="1" title="max volume">max volume</a>
							</li>
						</ul>
						<div class="jp-progress-slider"></div>
						<div class="jp-volume-slider"></div>
						<div class="jp-current-volume"></div>
						<div class="jp-current-time"></div>
						<div class="jp-duration"></div>
						<div class="jp-clearboth"></div>
					</div>
					<div class="jp-no-solution">
						<span>Update Required</span>
						To play the media you will need to either update your browser to a recent version or update your <a href="http://get.adobe.com/flashplayer/" target="_blank">Flash plugin</a>.
					</div>
				</div>
			</div>
			<?php else: ?>
			<div style="height: <?php echo $item_trailer->player_height; ?>px;"><img src="<?php echo $item_trailer->path.$item_trailer->screenshot; ?>" /></div>
			<?php endif; ?>
			<?php if (isset($item_trailer->files['video_links']) && (count($item_trailer->files['video_links']) > 0 && $this->params->get('allow_movie_download') == 1)): ?>
			<div class="video-links">
				<span class="title"><?php echo JText::_('COM_KA_DOWNLOAD_MOVIE_OTHER_FORMAT'); ?></span>
				<?php foreach ($item_trailer->files['video_links'] as $item): ?>
					<div><a href="<?php echo $item_trailer->path.$item['src']; ?>"><?php echo $item['src']; ?></a></div>
				<?php endforeach; ?>
			</div>
			<?php endif; ?>
		<?php endif; ?>
		</div>
	</div>
<?php endif;

if ((isset($this->item->movie) && count($this->item->movie) > 0) && ($this->params->get('allow_guest_watch') == 1 && $this->user->guest || $this->user->id != '')):
	$item_movie = $this->item->movie;
	$movie_media_supplied = ''; ?>
	<script type="text/javascript">
		jQuery(document).ready(function($){
			var movie_player = $('#movie_player'),
				movie_player_data,
				fixFlash_mp4,
				fixFlash_mp4_id,
				ignore_timeupdate,
				options = {
					ready: function(event){
						if (event.jPlayer.status.noVolume) {
							$('.jp-gui').addClass('jp-no-volume');
							$('.jp-current-volume').text('0');
						}

						$('.jp-current-volume').text((event.jPlayer.options.volume * 100).toFixed(0));
						fixFlash_mp4 = event.jPlayer.flash.used && /m4a|m4v/.test(event.jPlayer.options.supplied);

						$(this).jPlayer('setMedia', {
						<?php foreach ($item_movie->files['video'] as $item):
							$type = explode('/', $item['type']);
							if ($type[1] == 'mp4') {
								$type[1] = 'm4v';
							}
							$movie_media_supplied .= $type[1].', '; ?>
							<?php echo $type[1]; ?>: '<?php echo $item_movie->path.$item['src']; ?>',
						<?php endforeach; ?>
							poster: '<?php echo $item_movie->path.$item_movie->screenshot; ?>'
						});
					},
					timeupdate: function(event){
						if (!ignore_timeupdate) {
							myControl.progress.slider('value', event.jPlayer.status.currentPercentAbsolute);
						}
					},
					volumechange: function(event){
						if (event.jPlayer.options.muted) {
							myControl.volume.slider('value', 0);
							$('.jp-current-volume').text('0');
						} else {
							myControl.volume.slider('value', event.jPlayer.options.volume);
							$('.jp-current-volume').text((event.jPlayer.options.volume * 100).toFixed(0));
						}
					},
					swfPath: '<?php echo JURI::base(); ?>components/com_kinoarhiv/assets/players/jplayer/jplayer.swf',
					supplied: '<?php echo substr($movie_media_supplied, 0, -2); ?>',
					cssSelectorAncestor: '#movie_player_data',
					wmode: 'window',
					keyEnabled: true,
					size: {
						width: '<?php echo $item_movie->player_width; ?>px',
						height: '<?php echo $item_movie->player_height; ?>px'
					},
					preload: 'none',
					timeFormat: {
						showHour: true
					}
				},
				myControl = {
					progress: $(options.cssSelectorAncestor + ' .jp-progress-slider'),
					volume: $(options.cssSelectorAncestor + ' .jp-volume-slider')
				};

				movie_player.jPlayer(options);
				movie_player_data = movie_player.data('jPlayer');

				$('.jp-gui ul li').hover(
					function() { $(this).addClass('ui-state-hover'); },
					function() { $(this).removeClass('ui-state-hover'); }
				);

				myControl.progress.slider({
					animate: 'fast',
					max: 100,
					range: 'min',
					step: 0.1,
					value : 0,
					slide: function(event, ui){
						var sp = movie_player_data.status.seekPercent;
						if (sp > 0) {
							if (fixFlash_mp4) {
								ignore_timeupdate = true;
								clearTimeout(fixFlash_mp4_id);
								fixFlash_mp4_id = setTimeout(function(){
									ignore_timeupdate = false;
								}, 1000);
							}

							movie_player.jPlayer('playHead', ui.value * (100 / sp));
						} else {
							setTimeout(function(){
								myControl.progress.slider('value', 0);
							}, 0);
						}
					}
				});

				myControl.volume.slider({
					animate: 'fast',
					max: 1,
					range: 'min',
					step: 0.01,
					value: $.jPlayer.prototype.options.volume,
					slide: function(event, ui){
						movie_player.jPlayer('option', 'muted', false);
						movie_player.jPlayer('option', 'volume', ui.value);
					}
				});
		});
	</script>
	<div class="clear"></div>
	<div class="ui-widget trailer">
		<h3><?php echo JText::_('COM_KA_WATCH_MOVIE'); ?></h3>
		<div>
		<?php if ($item_movie->embed_code != ''):
			echo $item_movie->embed_code;
		else: ?>
			<div>
			<?php if (count($item_movie->files['video']) > 0): ?>
				<div id="movie_player"></div>
				<div id="movie_player_data">
					<div class="jp-gui ui-widget ui-widget-content ui-corner-all">
						<ul>
							<li class="jp-play ui-state-default ui-corner-all">
								<a href="javascript:;" class="jp-play ui-icon ui-icon-play hasTooltip" tabindex="1" title="play">play</a>
							</li>
							<li class="jp-pause ui-state-default ui-corner-all">
								<a href="javascript:;" class="jp-pause ui-icon ui-icon-pause hasTooltip" tabindex="1" title="pause">pause</a>
							</li>
							<li class="jp-stop ui-state-default ui-corner-all">
								<a href="javascript:;" class="jp-stop ui-icon ui-icon-stop hasTooltip" tabindex="1" title="stop">stop</a>
							</li>
							<li class="jp-repeat ui-state-default ui-corner-all">
								<a href="javascript:;" class="jp-repeat ui-icon ui-icon-refresh hasTooltip" tabindex="1" title="repeat">repeat</a>
							</li>
							<li class="jp-repeat-off ui-state-default ui-state-active ui-corner-all">
								<a href="javascript:;" class="jp-repeat-off ui-icon ui-icon-arrow-1-e hasTooltip" tabindex="1" title="repeat off">repeat off</a>
							</li>
							<li class="jp-full-screen ui-state-default ui-corner-all">
								<a href="javascript:;" class="jp-full-screen ui-icon ui-icon-arrow-4-diag hasTooltip" tabindex="1" title="full screen">full screen</a>
							</li>
							<li class="jp-restore-screen ui-state-default ui-state-active ui-corner-all">
								<a href="javascript:;" class="jp-restore-screen ui-icon ui-icon-arrow-2-e-w hasTooltip" tabindex="1" title="restore screen">restore screen</a>
							</li>
							<li class="jp-mute ui-state-default ui-corner-all">
								<a href="javascript:;" class="jp-mute ui-icon ui-icon-volume-off hasTooltip" tabindex="1" title="mute">mute</a>
							</li>
							<li class="jp-unmute ui-state-default ui-state-active ui-corner-all">
								<a href="javascript:;" class="jp-unmute ui-icon ui-icon-volume-off hasTooltip" tabindex="1" title="unmute">unmute</a>
							</li>
							<li class="jp-volume-max ui-state-default ui-corner-all">
								<a href="javascript:;" class="jp-volume-max ui-icon ui-icon-volume-on hasTooltip" tabindex="1" title="max volume">max volume</a>
							</li>
						</ul>
						<div class="jp-progress-slider"></div>
						<div class="jp-volume-slider"></div>
						<div class="jp-current-volume"></div>
						<div class="jp-current-time"></div>
						<div class="jp-duration"></div>
						<div class="jp-clearboth"></div>
					</div>
					<div class="jp-no-solution">
						<span>Update Required</span>
						To play the media you will need to either update your browser to a recent version or update your <a href="http://get.adobe.com/flashplayer/" target="_blank">Flash plugin</a>.
					</div>
				</div>
			</div>
			<?php else: ?>
			<div style="height: <?php echo $item_movie->player_height; ?>px;"><img src="<?php echo $item_movie->path.$item_movie->screenshot; ?>" /></div>
			<?php endif; ?>
			<?php if (isset($item_movie->files['video_links']) && (count($item_movie->files['video_links']) > 0 && $this->params->get('allow_movie_download') == 1)): ?>
			<div class="video-links">
				<span class="title"><?php echo JText::_('COM_KA_DOWNLOAD_MOVIE_OTHER_FORMAT'); ?></span>
				<?php foreach ($item_movie->files['video_links'] as $item): ?>
					<div><a href="<?php echo $item_movie->path.$item['src']; ?>"><?php echo $item['src']; ?></a></div>
				<?php endforeach; ?>
			</div>
			<?php endif; ?>
		<?php endif; ?>
		</div>
	</div>
<?php endif; ?>