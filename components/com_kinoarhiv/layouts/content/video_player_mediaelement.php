<?php
/**
 * @package     Kinoarhiv.Site
 * @subpackage  com_kinoarhiv
 *
 * @copyright   Copyright (C) 2018 Libra.ms. All rights reserved.
 * @license     GNU General Public License version 2 or later
 * @url         http://киноархив.com
 */

defined('_JEXEC') or die;

$document = JFactory::getDocument();

JHtml::_('jquery.framework');
JHtml::_('stylesheet', 'media/com_kinoarhiv/players/mediaelement/mediaelementplayer.min.css');
JHtml::_('script', 'media/com_kinoarhiv/players/mediaelement/mediaelement-and-player.min.js');
KAComponentHelper::getScriptLanguage('', 'media/com_kinoarhiv/players/mediaelement/lang', true, true);
JHtml::_('script', 'media/com_kinoarhiv/js/player.init.min.js');

/** @var array $displayData */
$item      = $displayData['item'];
$videoSrc  = $item->files['video'];
$subtitles = $item->files['subtitles'];
$chapters  = $item->files['chapters'];
?>
<div style="overflow: hidden; width: 100%;">
	<video controls="controls" preload="none" poster="<?php echo $item->screenshot; ?>" class="mejs__player"
		   width="<?php echo $item->player_width; ?>" height="<?php echo $item->player_height; ?>"
		   style="width: 100%; height: 100%;"
		   data-mejsoptions='{"pluginPath": "<?php echo JUri::base(); ?>media/com_kinoarhiv/players/mediaelement/"}'>
	<?php foreach ($videoSrc as $video):
		$mp4_file = ($video['type'] == 'video/mp4') ? $video['src'] : ''; ?>
		<source type="<?php echo $video['type']; ?>" src="<?php echo $video['src']; ?>"/>
	<?php endforeach;

	if (count($subtitles) > 0):
		foreach ($subtitles as $subtitle): ?>
			<track kind="subtitles" src="<?php echo $subtitle['file']; ?>"
				   srclang="<?php echo $subtitle['lang_code']; ?>"
				   label="<?php echo $subtitle['lang']; ?>"
				   <?php echo $subtitle['default'] ? ' default="default"' : ''; ?> />
		<?php endforeach;
	endif;

	if (count($chapters) > 0): ?>
		<track kind="chapters" src="<?php echo $chapters['file']; ?>" srclang="en" default="default"/>
	<?php endif; ?>
		<object width="<?php echo $item->player_width; ?>" height="<?php echo $item->player_height; ?>"
				type="application/x-shockwave-flash"
				data="<?php echo JUri::base(); ?>media/com_kinoarhiv/players/mediaelement/mediaelement-flash-video.swf">
			<param name="movie" value="<?php echo JUri::base(); ?>media/com_kinoarhiv/players/mediaelement/mediaelement-flash-video.swf"/>
			<param name="flashvars" value="controls=true&file=<?php echo $mp4_file; ?>"/>
			<img src="<?php echo $item->screenshot; ?>" width="<?php echo $item->player_width; ?>" height="<?php echo $item->player_height; ?>"
				 alt="No video playback capabilities"/>
		</object>
	</video>
</div>
