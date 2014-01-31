<?php defined('_JEXEC') or die; ?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta name="viewport" content="width=device-width, initial-scale=1.0" />
	<meta http-equiv="content-type" content="text/html; charset=utf-8" />
	<?php GlobalHelper::loadPlayerAssets($this->params->get('ka_theme'), $this->params->get('player_type')); ?>
</head>
<body style="margin: 0; padding: 0;">
<?php if (isset($this->item) && count($this->item) > 0): ?>
	<div class="trailer">
		<?php if ($this->item->embed_code != ''):
			echo $this->item->embed_code;
		else: ?>
			<div id="player">Loading the player ...</div>
			<script type="text/javascript">
				jwplayer("player").setup({
					playlist: [{
					<?php for($i=0, $n=1; $i<$n; $i++): ?>
						image: '<?php echo $this->item->path.$this->item->screenshot; ?>',
						sources: [{
							file: '<?php echo $this->item->path.$this->item->files['video'][$i]['src']; ?>'
						}]<?php if (count($this->item->files['video']) > 0): ?>,
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
						]
						<?php endif; ?>
					<?php endfor; ?>
					}],
					height: <?php echo $this->item->player_height; ?>,
					width: <?php echo $this->item->player_width; ?>,
					flashplayer: '<?php echo JURI::base(); ?>components/com_kinoarhiv/assets/js/players/jwplayer/jwplayer.flash.swf',
					html5player: '<?php echo JURI::base(); ?>components/com_kinoarhiv/assets/js/players/jwplayer/jwplayer.html5.js',
					skin: '<?php echo JURI::base(); ?>components/com_kinoarhiv/assets/themes/component/<?php echo $this->params->get('ka_theme'); ?>/css/jwplayer-five.xml',
					displaytitle: false
				});
			</script>
		<?php endif; ?>
	</div>
<?php else:
	echo JText::_('COM_KA_NO_ITEMS');
endif; ?>
</body>
</html>
