<?php defined('_JEXEC') or die; ?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta name="viewport" content="width=device-width, initial-scale=1.0" />
	<meta http-equiv="content-type" content="text/html; charset=utf-8" />
	<link href="<?php echo JURI::base(); ?>components/com_kinoarhiv/assets/themes/ui/<?php echo $this->params->get('ui_theme'); ?>/jquery-ui.min.css" rel="stylesheet" type="text/css" />
	<link href="<?php echo JURI::base(); ?>components/com_kinoarhiv/assets/themes/component/<?php echo $this->params->get('ka_theme'); ?>/css/style.css" rel="stylesheet" type="text/css" />
	<?php GlobalHelper::loadPlayerAssets($this->params->get('ka_theme'), $this->params->get('player_type')); ?>
</head>
<body style="margin: 0; padding: 0; width: <?php echo $this->item->player_width; ?>px;">
<?php if (isset($this->item) && count($this->item) > 0): ?>
	<div class="trailer">
		<?php if ($this->item->embed_code != ''):
			echo $this->item->embed_code;
		else: ?>
			<?php if (count($this->item->files['video']) > 0): ?>
			<div id="player">Loading the player ...</div>
			<script type="text/javascript">
				jwplayer('player').setup({
					playlist: [{
					<?php foreach ($this->item->files['video'] as $item):
						if ($item['type'] == 'video/mp4'): ?>
						image: '<?php echo $this->item->path.$this->item->screenshot; ?>',
						sources: [{
							file: '<?php echo $this->item->path.$item['src']; ?>'
						}]/*,
						tracks: [
							<?php foreach($this->item->files['subtitles'] as $item): ?>
							{
								file: '<?php echo $this->item->path.$item['file']; ?>',
								label: '<?php echo $item['lang']; ?>',
								kind: 'captions'<?php if ($item['default']): ?>,
								'default': true
								<?php endif; ?>
							},
							<?php endforeach; ?>
							<?php if ($this->item->files['chapters'] > 0): ?>
							{
								file: '<?php echo $this->item->path.$this->item->files['chapters']['file']; ?>',
								kind: 'chapters'
							}
							<?php endif; ?>
						]*/
						<?php endif; ?>
					<?php endforeach; ?>
					}],
					height: <?php echo $this->item->player_height; ?>,
					width: <?php echo $this->item->player_width; ?>,
					flashplayer: '<?php echo JURI::base(); ?>components/com_kinoarhiv/assets/js/players/jwplayer/jwplayer.flash.swf',
					html5player: '<?php echo JURI::base(); ?>components/com_kinoarhiv/assets/js/players/jwplayer/jwplayer.html5.js',
					skin: '<?php echo JURI::base(); ?>components/com_kinoarhiv/assets/themes/component/<?php echo $this->params->get('ka_theme'); ?>/css/jwplayer-five.xml',
					displaytitle: false
				});
			</script>
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
