<?php defined('_JEXEC') or die; ?>
<script src="components/com_kinoarhiv/assets/players/flowplayer/flowplayer.min.js" type="text/javascript"></script>
<?php GlobalHelper::loadPlayerAssets($this->params->get('ka_theme'), $this->params->get('player_type')); ?>
<script type="text/javascript">
	jQuery(document).ready(function($){
		$('#trailer').flowplayer({
			swf: '<?php echo JURI::base(); ?>components/com_kinoarhiv/assets/players/flowplayer/flowplayer.swf',
			embed: {
				library: '<?php echo JURI::base(); ?>components/com_kinoarhiv/assets/players/flowplayer/flowplayer.min.js',
				script: '<?php echo JURI::base(); ?>components/com_kinoarhiv/assets/players/flowplayer/embed.min.js'
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
		<?php if ($item_trailer->embed_code != ''):
			echo $item_trailer->embed_code;
		else: ?>
			<?php if (count($item_trailer->files['video']) > 0): ?>
			<div id="trailer" style="width: <?php echo $item_trailer->player_width; ?>px; height: <?php echo $item_trailer->player_height; ?>px;">
				<video preload="none" poster="<?php echo $item_trailer->path.$item_trailer->screenshot; ?>" width="<?php echo $item_trailer->player_width; ?>" height="<?php echo $item_trailer->player_height; ?>">
				<?php foreach ($item_trailer->files['video'] as $item): ?>
					<source type="<?php echo $item['type']; ?>" src="<?php echo $item_trailer->path.$item['src']; ?>" />
				<?php endforeach; ?>
				<?php if (count($item_trailer->files['subtitles']) > 0):
					foreach ($item_trailer->files['subtitles'] as $subtitle): ?>
						<track kind="subtitles" src="<?php echo $item_trailer->path.$subtitle['file']; ?>" srclang="<?php echo $subtitle['lang_code']; ?>" label="<?php echo $subtitle['lang']; ?>"<?php echo $subtitle['default'] ? ' default="default"' : ''; ?> />
					<?php endforeach;
				endif; ?>
				</video>
			</div>
			<?php else: ?>
			<div style="height: <?php echo $item_trailer->player_height; ?>px;"><img src="<?php echo $item_trailer->path.$item_trailer->screenshot; ?>" /></div>
			<?php endif; ?>
			<?php if (count($item_trailer->files['video_links']) > 0): ?>
			<div class="ui-widget-content video-links">
				<span class="title"><?php echo JText::_('COM_KA_DOWNLOAD_MOVIE_OTHER_FORMAT'); ?></span>
				<?php foreach ($item_trailer->files['video_links'] as $item): ?>
					<div><a href="<?php echo $item_trailer->path.$item['src']; ?>"><?php echo $item['src']; ?></a></div>
				<?php endforeach; ?>
			</div>
			<?php endif; ?>
		<?php endif; ?>
		</div>
	</div>
<?php endif; ?>
