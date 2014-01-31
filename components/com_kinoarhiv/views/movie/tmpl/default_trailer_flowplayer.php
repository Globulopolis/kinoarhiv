<?php defined('_JEXEC') or die; ?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta name="viewport" content="width=device-width, initial-scale=1.0" />
	<meta http-equiv="content-type" content="text/html; charset=utf-8" />
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
</head>
<body style="margin: 0; padding: 0;">
<?php if (isset($this->item) && count($this->item) > 0): ?>
	<div class="trailer">
		<?php if ($this->item->embed_code != ''):
			echo $this->item->embed_code;
		else: ?>
			<div id="trailer" style="width: <?php echo $this->item->player_width; ?>px; height: <?php echo $this->item->player_height; ?>px;">
				<video preload="none" poster="<?php echo $this->item->path.$this->item->screenshot; ?>" width="<?php echo $this->item->player_width; ?>" height="<?php echo $this->item->player_height; ?>">
				<?php if (count($this->item->files['video']) > 0):
					foreach ($this->item->files['video'] as $item): ?>
						<source type="<?php echo $item['type']; ?>" src="<?php echo $this->item->path.$item['src']; ?>" />
					<?php endforeach;
				endif; ?>
				<?php if (count($this->item->files['subtitles']) > 0):
					foreach ($this->item->files['subtitles'] as $subtitle): ?>
						<track kind="subtitles" src="<?php echo $this->item->path.$subtitle['file']; ?>" srclang="<?php echo $subtitle['lang_code']; ?>" label="<?php echo $subtitle['lang']; ?>"<?php echo $subtitle['default'] ? ' default="default"' : ''; ?> />
					<?php endforeach;
				endif; ?>
				</video>
			</div>
		<?php endif; ?>
	</div>
<?php else:
	echo JText::_('COM_KA_NO_ITEMS');
endif; ?>
</body>
</html>
