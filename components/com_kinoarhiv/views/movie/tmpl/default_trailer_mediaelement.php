<?php defined('_JEXEC') or die; ?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta name="viewport" content="width=device-width, initial-scale=1.0" />
	<meta http-equiv="content-type" content="text/html; charset=utf-8" />
	<link href="components/com_kinoarhiv/assets/themes/component/<?php echo $this->params->get('ka_theme'); ?>/css/style.css" rel="stylesheet" type="text/css" />
	<link href="components/com_kinoarhiv/assets/themes/component/<?php echo $this->params->get('ka_theme'); ?>/css/mediaelement-default.css" rel="stylesheet" type="text/css" />
	<script src="media/jui/js/jquery.js" type="text/javascript"></script>
	<script src="components/com_kinoarhiv/assets/js/players/mediaelement/mediaelement-and-player.min.js" type="text/javascript"></script>
	<script type="text/javascript">
		jQuery(document).ready(function($){
			$('video').mediaelementplayer({
				mode: 'auto',
				plugins: ['flash', 'silverlight'],
				pluginPath: '<?php echo JURI::base(); ?>components/com_kinoarhiv/assets/js/players/mediaelement/',
				flashName: 'flashmediaelement.swf',
				silverlightName: 'silverlightmediaelement.xap'
			});
		});
	</script>
</head>
<body style="margin: 0; padding: 0;">
<?php if (isset($this->item) && count($this->item) > 0): ?>
	<div class="trailer">
		<?php if ($this->item->embed_code != ''):
			echo $this->item->embed_code;
		else:
			if ($this->item->urls != ''): ?>
			<?php else:
				$mp4_file = ''; ?>
				<video id="trailer" controls preload="none" poster="<?php echo $this->item->path.$this->item->screenshot; ?>" width="<?php echo $this->item->player_width; ?>" height="<?php echo $this->item->player_height; ?>">
					<?php if (count($this->item->files['video']) > 0):
						foreach ($this->item->files['video'] as $item):
							if ($item['type'] == 'video/mp4') {
								$mp4_file = $this->item->path.$item['src'];
							} ?>
							<source type="<?php echo $item['type']; ?>" src="<?php echo $this->item->path.$item['src']; ?>" />
						<?php endforeach;
					endif; ?>
					<?php if (count($this->item->files['subtitles']) > 0):
						foreach ($this->item->files['subtitles'] as $subtitle): ?>
							<track kind="subtitles" src="<?php echo $this->item->path.$subtitle['file']; ?>" srclang="<?php echo $subtitle['lang_code']; ?>" label="<?php echo $subtitle['lang']; ?>"<?php echo $subtitle['default'] ? ' default' : ''; ?> />
						<?php endforeach;
					endif; ?>
					<?php if (count($this->item->files['chapters']) > 0): ?>
						<track kind="chapters" src="<?php echo $this->item->path.$this->item->files['chapters']['file']; ?>" srclang="en" default />
					<?php endif; ?>
					<object width="<?php echo $this->item->player_width; ?>" height="<?php echo $this->item->player_height; ?>" type="application/x-shockwave-flash" data="components/com_kinoarhiv/assets/js/players/mediaelement/flashmediaelement.swf">
						<param name="movie" value="<?php echo JURI::base(); ?>components/com_kinoarhiv/assets/js/players/mediaelement/flashmediaelement.swf" />
						<param name="flashvars" value="controls=true&file=<?php echo $mp4_file; ?>" />
						<img src="<?php echo $this->item->path.$this->item->screenshot; ?>" width="<?php echo $this->item->player_width; ?>" height="<?php echo $this->item->player_height; ?>" title="No video playback capabilities" />
					</object>
				</video>
			<?php endif; ?>
		<?php endif; ?>
	</div>
<?php else:
	echo JText::_('COM_KA_NO_ITEMS');
endif; ?>
</body>
</html>
