<?php defined('_JEXEC') or die; ?>
<?php GlobalHelper::loadPlayerAssets($this->params->get('ka_theme'), $this->params->get('player_type')); ?>
<script type="text/javascript">
	jQuery(document).ready(function($){
		$('#trailer').flowplayer({
			swf: '<?php echo JURI::base(); ?>components/com_kinoarhiv/assets/js/players/flowplayer/flowplayer.swf',
			embed: {
				library: '<?php echo JURI::base(); ?>components/com_kinoarhiv/assets/js/players/flowplayer/flowplayer.min.js',
				script: '<?php echo JURI::base(); ?>components/com_kinoarhiv/assets/js/players/flowplayer/embed.min.js'
			}
		});
	});
</script>
<?php if (isset($this->item->trailer) && count($this->item->trailer) > 0):
$item_trailer = $this->item->trailer; ?>
	<div class="clear"></div>
	<div class="ui-widget trailer">
		<h3><?php echo JText::_('COM_KA_WATCH_TRAILER'); ?></h3>
		<div>
		<?php if ($this->item->embed_code != ''):
			echo $this->item->embed_code;
		else: ?>
			<?php if (count($this->item->files['video']) > 0): ?>
			<div id="trailer" style="width: <?php echo $this->item->player_width; ?>px; height: <?php echo $this->item->player_height; ?>px;">
				<video preload="none" poster="<?php echo $this->item->path.$this->item->screenshot; ?>" width="<?php echo $this->item->player_width; ?>" height="<?php echo $this->item->player_height; ?>">
				<?php foreach ($this->item->files['video'] as $item): ?>
					<source type="<?php echo $item['type']; ?>" src="<?php echo $this->item->path.$item['src']; ?>" />
				<?php endforeach; ?>
				<?php if (count($this->item->files['subtitles']) > 0):
					foreach ($this->item->files['subtitles'] as $subtitle): ?>
						<track kind="subtitles" src="<?php echo $this->item->path.$subtitle['file']; ?>" srclang="<?php echo $subtitle['lang_code']; ?>" label="<?php echo $subtitle['lang']; ?>"<?php echo $subtitle['default'] ? ' default="default"' : ''; ?> />
					<?php endforeach;
				endif; ?>
				</video>
			</div>
			<?php else: ?>
			<div style="height: <?php echo $this->item->player_height; ?>px;"><img src="<?php echo $this->item->path.$this->item->screenshot; ?>" /></div>
			<?php endif; ?>
			<?php if (count($this->item->files['video_links']) > 0): ?>
			<div class="ui-widget-content video-links">
				<span class="title"><?php echo JText::_('COM_KA_DOWNLOAD_MOVIE_OTHER_FORMAT'); ?></span>
				<?php foreach ($this->item->files['video_links'] as $item): ?>
					<div><a href="<?php echo $this->item->path.$item['src']; ?>"><?php echo $item['src']; ?></a></div>
				<?php endforeach; ?>
			</div>
			<?php endif; ?>
		<?php endif; ?>
		</div>
	</div>
<?php endif; ?>
