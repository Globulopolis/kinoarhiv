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

JHtml::_('stylesheet', 'media/com_kinoarhiv/players/videojs/video-js.min.css');
JHtml::_('script', 'media/com_kinoarhiv/players/videojs/video.min.js');
KAComponentHelper::getScriptLanguage('', 'media/com_kinoarhiv/players/videojs/lang');

/** @var array $displayData */
$item      = $displayData['item'];
$videoSrc  = $item->files['video'];
$subtitles = $item->files['subtitles'];
$chapters  = $item->files['chapters'];
?>
<div>
	<video class="video-js vjs-default-skin vjs-big-play-centered" controls preload="none" poster="<?php echo $item->screenshot; ?>"
		   width="<?php echo $item->player_width; ?>" height="<?php echo $item->player_height; ?>"
		   data-setup='{"fluid": true, "language": "<?php echo JFactory::getLanguage()->getTag(); ?>"}'>
	<?php foreach ($videoSrc as $video): ?>
		<source type="<?php echo $video['type']; ?>" src="<?php echo $video['src']; ?>"/>
	<?php endforeach;

	if (count($subtitles) > 0):
		foreach ($subtitles as $subtitle): ?>
			<track kind="subtitles" src="<?php echo $subtitle['file']; ?>" srclang="<?php echo $subtitle['lang_code']; ?>"
				   label="<?php echo $subtitle['lang']; ?>"<?php echo $subtitle['default'] ? ' default' : ''; ?> />
		<?php endforeach;
	endif;

	if (count($chapters) > 0): ?>
		<track kind="chapters" src="<?php echo $chapters['file']; ?>" srclang="en" default/>
	<?php endif; ?>
		<p class="vjs-no-js">To view this video please enable JavaScript, and consider upgrading to a web browser that <a href="http://videojs.com/html5-video-support/" target="_blank">supports HTML5 video</a></p>
	</video>
</div>
