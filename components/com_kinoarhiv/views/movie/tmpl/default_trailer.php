<?php defined('_JEXEC') or die; ?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta name="viewport" content="width=device-width, initial-scale=1.0" />
	<meta http-equiv="content-type" content="text/html; charset=utf-8" />
	<link href="components/com_kinoarhiv/assets/themes/component/<?php echo $this->params->get('ka_theme'); ?>/css/style.css" rel="stylesheet" type="text/css" />
	<link href="components/com_kinoarhiv/assets/themes/component/<?php echo $this->params->get('ka_theme'); ?>/css/vjs-player-default.css" rel="stylesheet" type="text/css" />
	<script src="components/com_kinoarhiv/assets/js/players/vjs/video.min.js" type="text/javascript"></script>
	<script src="components/com_kinoarhiv/assets/js/players/vjs/video.persistvolume.min.js" type="text/javascript"></script>
	<script type="text/javascript">
	//<![CDATA[
		videojs.options.flash.swf = 'components/com_kinoarhiv/assets/js/players/vjs/video.swf';
	//]]>
	</script>
</head>
<body style="margin: 0; padding: 0;">
<?php if (isset($this->item) && count($this->item) > 0): ?>
	<div class="trailer">
		<?php if ($this->item->embed_code != ''):
			echo $this->item->embed_code;
		else: ?>
			<video id="trailer" class="video-js vjs-default-skin vjs-big-play-centered" controls preload="none" poster="<?php echo $this->item->path.$this->item->screenshot; ?>" width="<?php echo $this->item->player_width; ?>" height="<?php echo $this->item->player_height; ?>" data-setup="{&quot;techOrder&quot;: [&quot;html5&quot;, &quot;flash&quot;], &quot;plugins&quot;: {&quot;persistVolume&quot;: {&quot;namespace&quot;: &quot;<?php echo $this->user->get('guest') ? md5('video-js'.$this->item->id) : md5(crc32($this->user->get('id')).$this->item->id); ?>&quot;}}}">
				<?php foreach (json_decode($this->item->filename) as $item): ?>
					<source type="<?php echo $item->type; ?>" src="<?php echo $this->item->path.$item->src; ?>" />
				<?php endforeach; ?>
				<?php $subtitles = json_decode($this->item->_subtitles);
				foreach ($subtitles as $lang_tag=>$subtitle):
					foreach ($subtitle as $sub): ?>
					<track kind="subtitles" src="<?php echo $this->item->path.$sub->file; ?>" srclang="<?php echo $lang_tag; ?>" label="<?php echo $sub->lang; ?>"<?php echo $sub->default ? ' default' : ''; ?> />
					<?php endforeach;
				endforeach; ?>
				<?php $chapters = json_decode($this->item->_chapters);
				if (!is_array($chapters)): // Chapters is broken in VideoJS 4.x ?>
					<track kind="chapters" src="<?php echo $this->item->path.$chapters[0]; ?>" srclang="en" default />
				<?php endif; ?>
			</video>
		<?php endif; ?>
	</div>
<?php else:
	echo JText::_('COM_KA_NO_ITEMS');
endif; ?>
</body>
</html>
