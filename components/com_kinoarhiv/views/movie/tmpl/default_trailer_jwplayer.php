<?php defined('_JEXEC') or die;
$sfw = $this->params->get('player_swf');
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta name="viewport" content="width=device-width, initial-scale=1.0" />
	<meta http-equiv="content-type" content="text/html; charset=utf-8" />
	<title><?php echo $this->escape($this->item->title); ?></title>
	<link href="components/com_kinoarhiv/assets/themes/ui/<?php echo $this->params->get('ui_theme'); ?>/jquery-ui.css" rel="stylesheet" type="text/css" />
	<link href="components/com_kinoarhiv/assets/themes/component/<?php echo $this->params->get('ka_theme'); ?>/css/style.css" rel="stylesheet" type="text/css" />
	<?php KAComponentHelper::loadPlayerAssets($this->params->get('player_type')); ?>
</head>
<body style="margin: 0; padding: 0; background-color: #333333;">
<?php if (isset($this->item) && count($this->item) > 0):
$item_trailer = $this->item; ?>
	<div class="ui-widget">
		<div>
		<?php if ($item_trailer->embed_code != ''):
			echo $item_trailer->embed_code;
		else: ?>
			<?php if (count($item_trailer->files['video']) > 0): ?>
			<div id="video"><img src="components/com_kinoarhiv/assets/themes/component/<?php echo $this->params->get('ka_theme'); ?>/images/icons/loading.gif"> Loading the player...</div>
			<script type="text/javascript">
				jwplayer('video').setup({
					playlist: [{
						file: '<?php echo $item_trailer->path.$item_trailer->files['video'][0]['src']; ?>',
						image: '<?php echo $item_trailer->path.$item_trailer->screenshot; ?>',
						tracks: [
						<?php if (count($item_trailer->files['subtitles']) > 0):
							foreach ($item_trailer->files['subtitles'] as $subtitle): ?>
							{ file: '<?php echo $item_trailer->path.$subtitle['file']; ?>', label: '<?php echo $subtitle['lang']; ?>', kind: 'captions', 'default': true },
							<?php endforeach;
						endif; ?>
						<?php if (count($item_trailer->files['chapters']) > 0): ?>
							{ file: '<?php echo $item_trailer->path.$item_trailer->files['chapters']['file']; ?>', kind: 'chapters' }
						<?php endif; ?>
						]
					}],
					skin: '<?php echo JURI::base(); ?>components/com_kinoarhiv/assets/players/<?php echo $this->params->get('player_type'); ?>/five.xml',
					flashplayer: '<?php echo !empty($sfw) ? $sfw : JURI::base().'components/com_kinoarhiv/assets/players/'.$this->params->get('player_type').'/jwplayer.flash.swf'; ?>',
					html5player: '<?php echo JURI::base().'components/com_kinoarhiv/assets/players/'.$this->params->get('player_type').'/jwplayer.html5.js'; ?>',
					width: '100%',
					aspectratio: '<?php echo $item_trailer->dar; ?>'
				});
			</script>
			<?php else: ?>
			<div style="height: <?php echo $item_trailer->player_height; ?>px;"><img src="<?php echo $item_trailer->path.$item_trailer->screenshot; ?>" /></div>
			<?php endif; ?>
			<?php if (isset($item_trailer->files['video_links']) && (count($item_trailer->files['video_links']) > 0 && $this->params->get('allow_movie_download') == 1)): ?>
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
</body>
</html>
