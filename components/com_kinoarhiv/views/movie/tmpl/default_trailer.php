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

$playerLayout = ($this->params->get('player_type') == '-1') ? 'video_player' : 'video_player_' . $this->params->get('player_type');

if (isset($this->item->trailer) && count(get_object_vars($this->item->trailer)) > 0):
	$itemTrailer = $this->item->trailer;
?>
	<div class="clear"></div>
	<div id="trailer">
		<div class="video-title corner-top"><?php echo JText::_('COM_KA_WATCH_TRAILER'); ?></div>
		<?php if ($itemTrailer->embed_code != ''):
			echo '<div class="video-embed">' . $itemTrailer->embed_code . '</div>';
		else:
			if (count($itemTrailer->files['video']) > 0):
				echo JLayoutHelper::render('layouts.content.' . $playerLayout,
					array(
						'params' => $this->params,
						'item'   => $itemTrailer
					),
					JPATH_COMPONENT
				);
			else: ?>
				<div style="height: <?php echo $itemTrailer->player_height; ?>px;">
					<img src="<?php echo $itemTrailer->screenshot; ?>" height="<?php echo $itemTrailer->player_height; ?>"/></div>
			<?php endif; ?>

		<?php if (isset($itemTrailer->files['video_links'])
			&& (count($itemTrailer->files['video_links']) > 0 && $this->params->get('allow_movie_download') == 1)):
			?>
			<div class="video-footer corner-bottom">
				<div class="video-links">
					<span class="title"><?php echo JText::_('COM_KA_DOWNLOAD_MOVIE_OTHER_FORMAT'); ?></span>
					<?php foreach ($itemTrailer->files['video_links'] as $item): ?>
						<div>
							<a href="<?php echo $item['src']; ?>"><?php echo $item['src']; ?></a>
						</div>
					<?php endforeach; ?>
				</div>
			</div>
		<?php endif; ?>
		<?php endif; ?>
	</div>
	<br/>
<?php endif;

if ((isset($this->item->movie)
	&& count(get_object_vars($this->item->movie)) > 0)
	&& ($this->params->get('allow_guest_watch') == 1 && $this->user->guest || $this->user->id != '')):
		$itemMovie = $this->item->movie;
?>
	<div class="clear"></div>
	<div id="movie">
		<div class="video-title corner-top"><?php echo JText::_('COM_KA_WATCH_MOVIE'); ?></div>
		<?php if ($itemMovie->embed_code != ''):
			echo '<div class="video-embed">' . $itemMovie->embed_code . '</div>';
		else:
			if (count($itemMovie->files['video']) > 0):
				echo JLayoutHelper::render('layouts.content.' . $playerLayout,
					array(
						'params' => $this->params,
						'item'   => $itemMovie
					),
					JPATH_COMPONENT
				);
			else: ?>
				<div style="height: <?php echo $itemMovie->player_height; ?>px;">
					<img src="<?php echo $itemMovie->screenshot; ?>"/>
				</div>
			<?php endif;

			if (isset($itemMovie->files['video_links'])
				&& (count($itemMovie->files['video_links']) > 0 && $this->params->get('allow_movie_download') == 1)):
				?>
			<div class="video-footer corner-bottom">
				<div class="video-links">
					<span class="title"><?php echo JText::_('COM_KA_DOWNLOAD_MOVIE_OTHER_FORMAT'); ?></span>
					<?php foreach ($itemMovie->files['video_links'] as $item): ?>
						<div>
							<a href="<?php echo $item['src']; ?>"><?php echo $item['src']; ?></a>
						</div>
					<?php endforeach; ?>
				</div>
			</div>
			<?php endif;
		endif; ?>
	</div>
	<br/>
<?php endif;
