<?php defined('_JEXEC') or die; ?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta name="viewport" content="width=device-width, initial-scale=1.0" />
	<meta http-equiv="content-type" content="text/html; charset=utf-8" />
	<link href="<?php echo JURI::base(); ?>components/com_kinoarhiv/assets/themes/ui/<?php echo $this->params->get('ui_theme'); ?>/jquery-ui.min.css" rel="stylesheet" type="text/css" />
	<link href="<?php echo JURI::base(); ?>components/com_kinoarhiv/assets/themes/component/<?php echo $this->params->get('ka_theme'); ?>/css/style.css" rel="stylesheet" type="text/css" />
	<?php GlobalHelper::loadPlayerAssets($this->params->get('ka_theme'), $this->params->get('player_type')); ?>
	<script type="text/javascript">
		jQuery(document).ready(function($){
			$('#trailer').mediaelementplayer({
				enablePluginDebug: true,
				pluginPath: '<?php echo JURI::base(); ?>components/com_kinoarhiv/assets/js/players/mediaelement/',
				flashName: 'flashmediaelement.swf',
				silverlightName: 'silverlightmediaelement.xap',
				poster: '<?php echo $this->item->path.$this->item->screenshot; ?>'
			});
		});
	</script>
</head>
<body style="margin: 0; padding: 0; width: <?php echo $this->item->player_width; ?>px;">
<?php if (isset($this->item) && count($this->item) > 0): ?>
	<div class="ui-widget trailer" style="background-color: #000;">
		<?php if ($this->item->embed_code != ''):
			echo $this->item->embed_code;
		else:
			$mp4_file = ''; ?>
			<?php if (count($this->item->files['video']) > 0): ?>
			<video id="trailer" controls="controls" preload="none" width="<?php echo $this->item->player_width; ?>" height="<?php echo $this->item->player_height; ?>">
				<?php foreach ($this->item->files['video'] as $item):
					$mp4_file = ($item['type'] == 'video/mp4') ? $this->item->path.$item['src'] : ''; ?>
					<source type="<?php echo $item['type']; ?>" src="<?php echo $this->item->path.$item['src']; ?>" />
				<?php endforeach; ?>
				<?php if (count($this->item->files['subtitles']) > 0):
					foreach ($this->item->files['subtitles'] as $subtitle): ?>
						<track kind="subtitles" src="<?php echo $this->item->path.$subtitle['file']; ?>" srclang="<?php echo $subtitle['lang_code']; ?>" label="<?php echo $subtitle['lang']; ?>"<?php echo $subtitle['default'] ? ' default="default"' : ''; ?> />
					<?php endforeach;
				endif; ?>
				<?php if (count($this->item->files['chapters']) > 0): ?>
					<track kind="chapters" src="<?php echo $this->item->path.$this->item->files['chapters']['file']; ?>" srclang="en" default="default" />
				<?php endif; ?>
				<object width="<?php echo $this->item->player_width; ?>" height="<?php echo $this->item->player_height; ?>" type="application/x-shockwave-flash" data="components/com_kinoarhiv/assets/js/players/mediaelement/flashmediaelement.swf">
					<param name="movie" value="<?php echo JURI::base(); ?>components/com_kinoarhiv/assets/js/players/mediaelement/flashmediaelement.swf" />
					<param name="flashvars" value="controls=true&file=<?php echo $mp4_file; ?>" />
					<img src="<?php echo $this->item->path.$this->item->screenshot; ?>" width="<?php echo $this->item->player_width; ?>" height="<?php echo $this->item->player_height; ?>" title="No video playback capabilities" />
				</object>
			</video>
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
<?php else:
	echo JText::_('COM_KA_NO_ITEMS');
endif; ?>
</body>
</html>
