<?php defined('_JEXEC') or die; ?>
<script src="<?php echo JURI::base(); ?>components/com_kinoarhiv/assets/js/jquery-ui.min.js" type="text/javascript"></script>

<?php if ($this->params->get('player_type') == 'mediaelement'): ?>
<script type="text/javascript">
	jQuery(document).ready(function($){
		$('video').mediaelementplayer({
			pluginPath: '<?php echo JURI::base(); ?>components/com_kinoarhiv/assets/players/mediaelement/',
			flashName: 'flashmediaelement.swf',
			silverlightName: 'silverlightmediaelement.xap'
		});
	});
</script>
<?php elseif ($this->params->get('player_type') == 'flowplayer' || $this->params->get('player_type') == 'jwplayer'): ?>
<script type="text/javascript">
	jQuery(document).ready(function($){
		$('a.play').click(function(e){
			e.preventDefault();

			if ($(this).hasClass('watch-trailer')) {
				if (!window.open('<?php echo JRoute::_('index.php?option=com_kinoarhiv&view=movie&task=watch&type=trailer&id='.$this->item->id.'&Itemid='.$this->itemid.'&format=raw', false); ?>')) {
					showMsg('.watch-buttons', '<?php echo JText::sprintf('COM_KA_NEWWINDOW_BLOCKED', JRoute::_('index.php?option=com_kinoarhiv&view=movie&task=watch&type=trailer&id='.$this->item->id.'&Itemid='.$this->itemid.'&format=raw', false))?>');
				}
			} else if ($(this).hasClass('watch-movie')) {
				if (!window.open('<?php echo JRoute::_('index.php?option=com_kinoarhiv&view=movie&task=watch&type=movie&id='.$this->item->id.'&Itemid='.$this->itemid.'&format=raw', false); ?>')) {
					showMsg('.watch-buttons', '<?php echo JText::sprintf('COM_KA_NEWWINDOW_BLOCKED', JRoute::_('index.php?option=com_kinoarhiv&view=movie&task=watch&type=movie&id='.$this->item->id.'&Itemid='.$this->itemid.'&format=raw', false))?>');
				}
			}
		});
	});
</script>
<?php endif; ?>

<div class="content movie trailers">
	<article>
		<header>
			<h1 class="title">
				<a href="<?php echo JRoute::_('index.php?option=com_kinoarhiv&view=movie&id='.$this->item->id.'&Itemid='.$this->itemid); ?>" class="brand" title="<?php echo $this->escape($this->item->title.$this->item->year_str); ?>"><?php echo $this->escape($this->item->title.$this->item->year_str); ?></a>
			</h1>
		</header>
		<?php echo $this->item->event->afterDisplayTitle; ?>
		<?php echo $this->loadTemplate('tabs'); ?>
		<?php echo $this->item->event->beforeDisplayContent; ?>
		<?php if (isset($this->item->trailers) && count($this->item->trailers) > 0):
			if ($this->params->get('player_type') != '-1') {
				GlobalHelper::loadPlayerAssets($this->params->get('ka_theme'), $this->params->get('player_type'));
			}

			$trailers_obj = $this->item->trailers; ?>
		<div class="ui-widget">
			<?php foreach ($trailers_obj as $item_trailer): ?>
			<div class="trailer">
				<div class="ui-widget-header ui-corner-top"><?php echo ($item_trailer->title == '') ? JText::_('COM_KA_TRAILER') : $item_trailer->title; ?></div>
				<div class="ui-widget-content">
					<?php if ($item_trailer->embed_code != ''):
						echo $item_trailer->embed_code;
					else:
						if (count($item_trailer->files['video']) > 0): ?>
						<div>
							<?php if ($this->params->get('player_type') == '-1'): ?>

							<video controls preload="none" poster="<?php echo $item_trailer->path.$item_trailer->screenshot; ?>" width="<?php echo $this->item->player_width; ?>" height="<?php echo $item_trailer->player_height; ?>">
								<?php foreach ($item_trailer->files['video'] as $item): ?>
									<source type="<?php echo $item['type']; ?>" src="<?php echo $item_trailer->path.$item['src']; ?>" />
								<?php endforeach; ?>
								<?php if (count($item_trailer->files['subtitles']) > 0):
									foreach ($item_trailer->files['subtitles'] as $subtitle): ?>
										<track kind="subtitles" src="<?php echo $item_trailer->path.$subtitle['file']; ?>" srclang="<?php echo $subtitle['lang_code']; ?>" label="<?php echo $subtitle['lang']; ?>"<?php echo $subtitle['default'] ? ' default' : ''; ?> />
									<?php endforeach;
								endif; ?>
								<?php if (count($item_trailer->files['chapters']) > 0): ?>
									<track kind="chapters" src="<?php echo $item_trailer->path.$item_trailer->files['chapters']['file']; ?>" srclang="en" default />
								<?php endif; ?>
							</video>

							<?php elseif ($this->params->get('player_type') == 'videojs'): ?>

							<video class="video-js vjs-default-skin vjs-big-play-centered" controls preload="none" poster="<?php echo $item_trailer->path.$item_trailer->screenshot; ?>" width="<?php echo $this->item->player_width; ?>" height="<?php echo $item_trailer->player_height; ?>" data-setup="{&quot;techOrder&quot;: [&quot;html5&quot;, &quot;flash&quot;], &quot;plugins&quot;: {&quot;persistVolume&quot;: {&quot;namespace&quot;: &quot;<?php echo $this->user->get('guest') ? md5('video-js'.$this->item->id) : md5(crc32($this->user->get('id')).$this->item->id); ?>&quot;}}}">
								<?php foreach ($item_trailer->files['video'] as $item): ?>
									<source type="<?php echo $item['type']; ?>" src="<?php echo $item_trailer->path.$item['src']; ?>" />
								<?php endforeach; ?>
								<?php if (count($item_trailer->files['subtitles']) > 0):
									foreach ($item_trailer->files['subtitles'] as $subtitle): ?>
										<track kind="subtitles" src="<?php echo $item_trailer->path.$subtitle['file']; ?>" srclang="<?php echo $subtitle['lang_code']; ?>" label="<?php echo $subtitle['lang']; ?>"<?php echo $subtitle['default'] ? ' default' : ''; ?> />
									<?php endforeach;
								endif; ?>
								<?php /*if (count($item_trailer->files['chapters']) > 0): Chapters is broken in VJS 4+ ?>
									<track kind="chapters" src="<?php echo $item_trailer->path.$item_trailer->files['chapters']['file']; ?>" srclang="en" default />
								<?php endif;*/ ?>
							</video>

							<?php elseif ($this->params->get('player_type') == 'mediaelement'): ?>

							<video controls="controls" preload="none" poster="<?php echo $item_trailer->path.$item_trailer->screenshot; ?>" width="<?php echo $this->item->player_width; ?>" height="<?php echo $item_trailer->player_height; ?>">
								<?php foreach ($item_trailer->files['video'] as $item):
									$mp4_file = ($item['type'] == 'video/mp4') ? $item_trailer->path.$item['src'] : ''; ?>
									<source type="<?php echo $item['type']; ?>" src="<?php echo $item_trailer->path.$item['src']; ?>" />
								<?php endforeach; ?>
								<?php if (count($item_trailer->files['subtitles']) > 0):
									foreach ($item_trailer->files['subtitles'] as $subtitle): ?>
										<track kind="subtitles" src="<?php echo $item_trailer->path.$subtitle['file']; ?>" srclang="<?php echo $subtitle['lang_code']; ?>" label="<?php echo $subtitle['lang']; ?>"<?php echo $subtitle['default'] ? ' default="default"' : ''; ?> />
									<?php endforeach;
								endif; ?>
								<?php if (count($item_trailer->files['chapters']) > 0): ?>
									<track kind="chapters" src="<?php echo $item_trailer->path.$item_trailer->files['chapters']['file']; ?>" srclang="en" default="default" />
								<?php endif; ?>
								<object width="<?php echo $this->item->player_width; ?>" height="<?php echo $item_trailer->player_height; ?>" type="application/x-shockwave-flash" data="components/com_kinoarhiv/assets/players/mediaelement/flashmediaelement.swf">
									<param name="movie" value="<?php echo JURI::base(); ?>components/com_kinoarhiv/assets/players/mediaelement/flashmediaelement.swf" />
									<param name="flashvars" value="controls=true&file=<?php echo $mp4_file; ?>" />
									<img src="<?php echo $item_trailer->path.$item_trailer->screenshot; ?>" width="<?php echo $this->item->player_width; ?>" height="<?php echo $item_trailer->player_height; ?>" title="No video playback capabilities" />
								</object>
							</video>

							<?php elseif ($this->params->get('player_type') == 'flowplayer' || $this->params->get('player_type') == 'jwplayer'):
								$watch = $item_trailer->is_movie ? 'watch-movie' : 'watch-trailer';
								$ln_watch = $item_trailer->is_movie ? JText::_('COM_KA_WATCH_MOVIE') : JText::_('COM_KA_WATCH_TRAILER'); ?>

							<div style="height: <?php echo $item_trailer->player_height; ?>px;">
								<a href="#" class="play hasTooltip <?php echo $watch; ?>" title="<?php echo $ln_watch; ?>"><img src="<?php echo $item_trailer->path.$item_trailer->screenshot; ?>" /></a>
							</div>

							<?php endif; ?>
						</div>
						<?php else: ?>
						<div style="height: <?php echo $item_trailer->player_height; ?>px;"><img src="<?php echo $item_trailer->path.$item_trailer->screenshot; ?>" /></div>
						<?php endif; ?>
					<?php endif; ?>
				</div>
				<div class="ui-widget-content ui-corner-bottom">
					<?php if (isset($item_trailer->files['video_links']) && (count($item_trailer->files['video_links']) > 0 && $this->params->get('allow_movie_download') == 1)): ?>
					<div class="video-links">
						<span class="title"><?php echo JText::_('COM_KA_DOWNLOAD_MOVIE_OTHER_FORMAT'); ?></span>
						<?php foreach ($item_trailer->files['video_links'] as $item): ?>
							<div><a href="<?php echo $item_trailer->path.$item['src']; ?>"><?php echo $item['src']; ?></a></div>
						<?php endforeach; ?>
					</div>
					<?php endif; ?>
				</div>
			</div>
			<?php endforeach; ?>
		</div>
		<?php else: ?>
		<div><?php echo GlobalHelper::showMsg(JText::_('COM_KA_NO_ITEMS')); ?></div>
		<?php endif; ?>
	</article>
	<?php echo $this->item->event->afterDisplayContent; ?>
</div>
