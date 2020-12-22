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

/** @var array $displayData */
$item      = $displayData['item'];
$videoSrc  = $item->files['video'];
$subtitles = $item->files['subtitles'];
$chapters  = $item->files['chapters'];

if (!empty($item->resolution))
{
	$resolution = explode('x', $item->resolution);
	$padding    = round($resolution[1] / $resolution[0] * 100, 2);
}
else
{
	$dar     = explode(':', $item->dar);
	$padding = round($dar[1] / $dar[0] * 100, 2);
}
?>
<div class="video-responsive" style="padding-bottom: <?php echo $padding; ?>%;">
	<video controls preload="none" poster="<?php echo $item->screenshot; ?>"
		   width="<?php echo $item->player_width; ?>" height="<?php echo $item->player_height; ?>">
	<?php foreach ($videoSrc as $video): ?>
		<source type="<?php echo $video['type']; ?>" src="<?php echo $video['src']; ?>"/>
	<?php endforeach;

	if (count($subtitles) > 0):
		foreach ($subtitles as $subtitle): ?>
			<track kind="subtitles" src="<?php echo $subtitle['file']; ?>"
				   srclang="<?php echo $subtitle['lang_code']; ?>" label="<?php echo $subtitle['lang']; ?>"
				   <?php echo $subtitle['default'] ? ' default' : ''; ?> />
		<?php endforeach;
	endif;

	if (count($chapters) > 0): ?>
		<track kind="chapters" src="<?php echo $chapters['file']; ?>" srclang="en" default/>
	<?php endif; ?>
	</video>
</div>
