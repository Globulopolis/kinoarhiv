<?php defined('_JEXEC') or die; ?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta name="viewport" content="width=device-width, initial-scale=1.0" />
	<meta http-equiv="content-type" content="text/html; charset=utf-8" />
	<link href="components/com_kinoarhiv/assets/themes/component/<?php echo $this->params->get('ka_theme'); ?>/css/style.css" rel="stylesheet" type="text/css" />
	<?php GlobalHelper::loadPlayerAssets($this->params->get('ka_theme'), $this->params->get('player_type')); ?>
</head>
<body style="margin: 0; padding: 0;">
<?php if (isset($this->item) && count($this->item) > 0): ?>
	<div class="trailer">
		<?php if ($this->item->embed_code != ''):
			echo $this->item->embed_code;
		else:
			if ($this->item->urls != ''): ?>
			<?php else:
				if ($this->params->get('player_type') == 'jwplayer'): ?>
					<div id="jwplayer">Loading the player ...</div>
					<script type="text/javascript">
						jwplayer("jwplayer").setup({
							playlist: [{
								image: '<?php echo $this->item->path.$this->item->screenshot; ?>',
								sources: [<?php if (count($this->item->files['video']) > 0): ?>
									<?php foreach ($this->item->files['video'] as $item): ?>
									{file: '<?php echo $this->item->path.$item['src']; ?>', label: 'videowebm'},
									<?php endforeach; ?>
								<?php endif; ?>]
							}],
							listbar: {
								position: 'bottom',
								layout: 'basic'
							},
							height: <?php echo $this->item->player_height; ?>,
							width: <?php echo $this->item->player_width; ?>,
							flashplayer: '<?php echo JURI::base(); ?>components/com_kinoarhiv/assets/js/players/jwplayer/jwplayer.flash.swf',
							html5player: '<?php echo JURI::base(); ?>components/com_kinoarhiv/assets/js/players/jwplayer/jwplayer.html5.js',
							skin: '<?php echo JURI::base(); ?>components/com_kinoarhiv/assets/themes/component/<?php echo $this->params->get('ka_theme'); ?>/css/jwplayer-five.xml'
						});
					</script>
				<?php endif; ?>
			<?php endif; ?>
		<?php endif; ?>
	</div>
<?php else:
	echo JText::_('COM_KA_NO_ITEMS');
endif; ?>
</body>
</html>
