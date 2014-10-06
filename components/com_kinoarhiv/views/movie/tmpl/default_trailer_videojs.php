<?php defined('_JEXEC') or die;
GlobalHelper::loadPlayerAssets($this->params->get('player_type'));

if (isset($this->item->trailer) && count($this->item->trailer) > 0):
$item_trailer = $this->item->trailer; ?>
	<div class="clear"></div>
	<div class="ui-widget trailer" id="trailer_accordion">
		<h3><?php echo JText::_('COM_KA_WATCH_TRAILER'); ?></h3>
		<div>
		<?php if ($item_trailer->embed_code != ''):
			echo $item_trailer->embed_code;
		else: ?>
			<?php if (count($item_trailer->files['video']) > 0):
				$tposter = $item_trailer->screenshot != '' ? 'poster="'.$item_trailer->path.$item_trailer->screenshot.'"' : ''; ?>
			<video
				class="video-js vjs-default-skin vjs-big-play-centered"
				controls
				preload="none" 
				<?php echo $tposter; ?>
				width="<?php echo $item_trailer->player_width; ?>"
				height="<?php echo $item_trailer->player_height; ?>"
				data-setup='{"techOrder": ["html5", "flash"]}'
			>
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
				<p class="vjs-no-js">To view this video please enable JavaScript, and consider upgrading to a web browser that <a href="http://videojs.com/html5-video-support/" target="_blank">supports HTML5 video</a></p>
			</video>
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
$item_movie = $this->item->movie; ?>
	<div class="clear"></div>
	<div class="ui-widget trailer" id="movie_accordion">
		<h3><?php echo JText::_('COM_KA_WATCH_MOVIE'); ?></h3>
		<div>
		<?php if ($item_movie->embed_code != ''):
			echo $item_movie->embed_code;
		else: ?>
			<?php if (count($item_movie->files['video']) > 0):
				$mposter = $item_movie->screenshot != '' ? 'poster="'.$item_movie->path.$item_movie->screenshot.'"' : ''; ?>
			<video
				class="video-js vjs-default-skin vjs-big-play-centered"
				controls
				preload="none"
				<?php echo $mposter; ?>
				width="<?php echo $item_movie->player_width; ?>"
				height="<?php echo $item_movie->player_height; ?>"
				data-setup='{"techOrder": ["html5", "flash"]}'
			>
				<?php foreach ($item_movie->files['video'] as $item): ?>
					<source type="<?php echo $item['type']; ?>" src="<?php echo $item_movie->path.$item['src']; ?>" />
				<?php endforeach; ?>
				<?php if (count($item_movie->files['subtitles']) > 0):
					foreach ($item_movie->files['subtitles'] as $subtitle): ?>
						<track kind="subtitles" src="<?php echo $item_movie->path.$subtitle['file']; ?>" srclang="<?php echo $subtitle['lang_code']; ?>" label="<?php echo $subtitle['lang']; ?>"<?php echo $subtitle['default'] ? ' default' : ''; ?> />
					<?php endforeach;
				endif; ?>
				<?php if (count($item_movie->files['chapters']) > 0): ?>
					<track kind="chapters" src="<?php echo $item_movie->path.$item_movie->files['chapters']['file']; ?>" srclang="en" default />
				<?php endif; ?>
				<p class="vjs-no-js">To view this video please enable JavaScript, and consider upgrading to a web browser that <a href="http://videojs.com/html5-video-support/" target="_blank">supports HTML5 video</a></p>
			</video>
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
