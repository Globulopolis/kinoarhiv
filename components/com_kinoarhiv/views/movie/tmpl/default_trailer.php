<?php
/**
 * @package     Kinoarhiv.Site
 * @subpackage  com_kinoarhiv
 *  
 * @copyright   Copyright (C) 2017 Libra.ms. All rights reserved.
 * @license     GNU General Public License version 2 or later
 * @url         http://киноархив.com
 */

defined('_JEXEC') or die;

if (isset($this->item->trailer) && count(get_object_vars($this->item->trailer)) > 0):
	$item_trailer = $this->item->trailer;

	if (!empty($item_trailer->resolution))
	{
		$resolution = explode('x', $item_trailer->resolution);
		$tr_padding = round($resolution[1] / $resolution[0] * 100, 2);
	}
	else
	{
		$dar = explode(':', $item_trailer->dar);
		$tr_padding = round($dar[1] / $dar[0] * 100, 2);
	}
?>
	<div class="clear"></div>
	<a name="trailer"></a>
	<div class="accordion" id="tr_accordion">
		<div class="accordion-group">
			<div class="accordion-heading">
				<a class="accordion-toggle" data-toggle="collapse" data-parent="#tr_accordion" href="#toggleTrailer"><?php echo JText::_('COM_KA_WATCH_TRAILER'); ?></a>
			</div>
			<div id="toggleTrailer" class="accordion-body collapse<?php echo $this->tr_collapsed; ?>">
				<div class="accordion-inner">
					<div>
						<?php if ($item_trailer->embed_code != ''):
							echo '<div class="video-embed">' . $item_trailer->embed_code . '</div>';
						else: ?>
							<?php if (count($item_trailer->files['video']) > 0): ?>
								<div class="video-responsive" style="padding-bottom: <?php echo $tr_padding; ?>%;">
									<video controls preload="none" poster="<?php echo $item_trailer->screenshot; ?>"
										width="<?php echo $item_trailer->player_width; ?>" height="<?php echo $item_trailer->player_height; ?>">
										<?php foreach ($item_trailer->files['video'] as $item): ?>
											<source type="<?php echo $item['type']; ?>" src="<?php echo $item['src']; ?>"/>
										<?php endforeach; ?>
										<?php if (count($item_trailer->files['subtitles']) > 0):
											foreach ($item_trailer->files['subtitles'] as $subtitle): ?>
												<track kind="subtitles" src="<?php echo $subtitle['file']; ?>"
													srclang="<?php echo $subtitle['lang_code']; ?>"
													label="<?php echo $subtitle['lang']; ?>"
													<?php echo $subtitle['default'] ? ' default' : ''; ?> />
											<?php endforeach;
										endif; ?>
										<?php if (count($item_trailer->files['chapters']) > 0): ?>
											<track kind="chapters" src="<?php echo $item_trailer->files['chapters']['file']; ?>" srclang="en" default/>
										<?php endif; ?>
									</video>
								</div>
							<?php else: ?>
								<div style="height: <?php echo $item_trailer->player_height; ?>px;">
									<img src="<?php echo $item_trailer->screenshot; ?>" height="<?php echo $item_trailer->player_height; ?>"/></div>
							<?php endif; ?>
							<?php if (isset($item_trailer->files['video_links'])
								&& (count($item_trailer->files['video_links']) > 0 && $this->params->get('allow_movie_download') == 1)):
								?>
								<div class="video-links">
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
			</div>
		</div>
	</div>
<?php endif;

if ((isset($this->item->movie)
	&& count(get_object_vars($this->item->movie)) > 0)
	&& ($this->params->get('allow_guest_watch') == 1 && $this->user->guest || $this->user->id != '')):
		$item_movie = $this->item->movie;

		if (!empty($item_movie->resolution))
		{
			$resolution = explode('x', $item_movie->resolution);
			$movie_padding = round($resolution[1] / $resolution[0] * 100, 2);
		}
		else
		{
			$dar = explode(':', $item_movie->dar);
			$movie_padding = round($dar[1] / $dar[0] * 100, 2);
		}
?>
	<div class="clear"></div>
	<a name="movie"></a>
	<div class="accordion" id="movie_accordion">
		<div class="accordion-group">
			<div class="accordion-heading">
				<a class="accordion-toggle" data-toggle="collapse" data-parent="#movie_accordion" href="#toggleMovie"><?php echo JText::_('COM_KA_WATCH_MOVIE'); ?></a>
			</div>
			<div id="toggleMovie" class="accordion-body collapse<?php echo $this->mov_collapsed; ?>">
				<div class="accordion-inner">
					<div>
						<?php if ($item_movie->embed_code != ''):
							echo '<div class="video-embed">' . $item_movie->embed_code . '</div>';
						else: ?>
							<?php if (count($item_movie->files['video']) > 0): ?>
								<div class="video-responsive" style="padding-bottom: <?php echo $movie_padding; ?>%;">
									<video controls preload="none" poster="<?php echo $item_movie->screenshot; ?>"
										   width="<?php echo $item_movie->player_width; ?>" height="<?php echo $item_movie->player_height; ?>">
										<?php foreach ($item_movie->files['video'] as $item): ?>
											<source type="<?php echo $item['type']; ?>" src="<?php echo $item['src']; ?>"/>
										<?php endforeach; ?>
										<?php if (count($item_movie->files['subtitles']) > 0):
											foreach ($item_movie->files['subtitles'] as $subtitle): ?>
												<track kind="subtitles" src="<?php echo $subtitle['file']; ?>"
													   srclang="<?php echo $subtitle['lang_code']; ?>"
													   label="<?php echo $subtitle['lang']; ?>"
													<?php echo $subtitle['default'] ? ' default' : ''; ?> />
											<?php endforeach;
										endif; ?>
										<?php if (count($item_movie->files['chapters']) > 0): ?>
											<track kind="chapters" src="<?php echo $item_movie->files['chapters']['file']; ?>" srclang="en" default/>
										<?php endif; ?>
									</video>
								</div>
							<?php else: ?>
								<div style="height: <?php echo $item_movie->player_height; ?>px;">
									<img src="<?php echo $item_movie->screenshot; ?>"/></div>
							<?php endif; ?>
							<?php if (isset($item_movie->files['video_links'])
								&& (count($item_movie->files['video_links']) > 0
									&& $this->params->get('allow_movie_download') == 1)):
								?>
								<div class="video-links">
									<span class="title"><?php echo JText::_('COM_KA_DOWNLOAD_MOVIE_OTHER_FORMAT'); ?></span>
									<?php foreach ($item_movie->files['video_links'] as $item): ?>
										<div>
											<a href="<?php echo $item['src']; ?>"><?php echo $item['src']; ?></a>
										</div>
									<?php endforeach; ?>
								</div>
							<?php endif; ?>
						<?php endif; ?>
					</div>
				</div>
			</div>
		</div>
	</div>
<?php endif; ?>
