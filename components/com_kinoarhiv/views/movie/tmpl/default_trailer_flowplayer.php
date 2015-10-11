<?php
/**
 * @package     Kinoarhiv.Administrator
 * @subpackage  com_kinoarhiv
 *
 * @copyright   Copyright (C) 2010 Libra.ms. All rights reserved.
 * @license     GNU General Public License version 2 or later
 * @url            http://киноархив.com/
 */

defined('_JEXEC') or die;

$sfw = $this->params->get('player_swf');
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
	<meta http-equiv="content-type" content="text/html; charset=utf-8"/>
	<title><?php echo $this->escape($this->item->title); ?></title>
	<link href="components/com_kinoarhiv/assets/themes/ui/<?php echo $this->params->get('ui_theme'); ?>/jquery-ui.css" rel="stylesheet" type="text/css"/>
	<link href="components/com_kinoarhiv/assets/themes/component/<?php echo $this->params->get('ka_theme'); ?>/css/style.css" rel="stylesheet" type="text/css"/>
	<?php KAComponentHelper::loadPlayerAssets($this->params->get('player_type')); ?>
	<script type="text/javascript">
		jQuery(document).ready(function ($) {
			$('#trailer').flowplayer({
				swf: '<?php echo !empty($sfw) ? $sfw : JURI::base().'components/com_kinoarhiv/assets/players/flowplayer/flowplayer.swf'; ?>',
				embed: {
					library: '<?php echo JURI::base(); ?>components/com_kinoarhiv/assets/players/flowplayer/flowplayer.min.js',
					script: '<?php echo JURI::base(); ?>components/com_kinoarhiv/assets/players/flowplayer/embed.min.js'
				},
				key: '<?php echo $this->params->get('player_key'); ?>',
				logo: '<?php echo $this->params->get('player_logo'); ?>'
			});
		});
	</script>
</head>
<body style="margin: 0; padding: 0; background-color: #333333;">
<?php if (isset($this->item) && count($this->item) > 0):
	$item_trailer = $this->item;
	$ratio_raw = explode(':', $item_trailer->dar);
	$ratio = round($ratio_raw[1] / $ratio_raw[0], 4); ?>
	<div class="ui-widget">
		<div>
			<?php if ($item_trailer->embed_code != ''):
				echo $item_trailer->embed_code;
			else: ?>
				<?php if (count($item_trailer->files['video']) > 0): ?>
					<div id="trailer" class="minimalist" data-nativesubtitles="true" data-ratio="<?php echo $ratio; ?>">
						<video preload="none" poster="<?php echo $item_trailer->screenshot; ?>">
							<?php foreach ($item_trailer->files['video'] as $item): ?>
								<source type="<?php echo $item['type']; ?>" src="<?php echo $item['src']; ?>"/>
							<?php endforeach; ?>
							<?php if (count($item_trailer->files['subtitles']) > 0):
								foreach ($item_trailer->files['subtitles'] as $subtitle): ?>
									<track kind="subtitles" src="<?php echo $subtitle['file']; ?>" srclang="<?php echo $subtitle['lang_code']; ?>" label="<?php echo $subtitle['lang']; ?>"<?php echo $subtitle['default'] ? ' default' : ''; ?> />
								<?php endforeach;
							endif; ?>
						</video>
					</div>
				<?php else: ?>
					<div style="height: <?php echo $item_trailer->player_height; ?>px;">
						<img src="<?php echo $item_trailer->screenshot; ?>"/></div>
				<?php endif; ?>
				<?php if (isset($item_trailer->files['video_links'])
					&& (count($item_trailer->files['video_links']) > 0 && $this->params->get('allow_movie_download') == 1)):
				?>
					<div class="ui-widget-content video-links">
						<span class="title"><?php echo JText::_('COM_KA_DOWNLOAD_MOVIE_OTHER_FORMAT'); ?></span>
						<?php foreach ($item_trailer->files['video_links'] as $item): ?>
							<div>
								<a href="<?php echo $item['src']; ?>"><?php echo $item['src']; ?></a>
							</div>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>
			<?php endif; ?>
		</div>
	</div>
<?php endif; ?>
</body>
</html>
