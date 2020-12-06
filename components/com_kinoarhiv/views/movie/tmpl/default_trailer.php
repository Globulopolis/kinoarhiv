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

if (isset($this->item->trailer) && count(get_object_vars($this->item->trailer)) > 0):
	$itemTrailer = $this->item->trailer;

	if (!empty($itemTrailer->resolution))
	{
		$resolution = explode('x', $itemTrailer->resolution);
		$trPadding  = round($resolution[1] / $resolution[0] * 100, 2);
	}
	else
	{
		$dar       = explode(':', $itemTrailer->dar);
		$trPadding = round($dar[1] / $dar[0] * 100, 2);
	}
?>
	<div class="clear"></div>
	<span id="trailer"></span>
	<div class="accordion" id="tr_accordion">
		<div class="accordion-group">
			<div class="accordion-heading">
				<a class="accordion-toggle" data-toggle="collapse" data-parent="#tr_accordion" href="#toggleTrailer"><?php echo JText::_('COM_KA_WATCH_TRAILER'); ?></a>
			</div>
			<div id="toggleTrailer" class="accordion-body collapse<?php echo $this->tr_collapsed; ?>">
				<div class="accordion-inner">
					<div>
						<?php if ($itemTrailer->embed_code != ''):
							echo '<div class="video-embed">' . $itemTrailer->embed_code . '</div>';
						else: ?>
							<?php if (count($itemTrailer->files['video']) > 0): ?>
								<div class="video-responsive" style="padding-bottom: <?php echo $trPadding; ?>%;">
									<video controls preload="none" poster="<?php echo $itemTrailer->screenshot; ?>"
										   width="<?php echo $itemTrailer->player_width; ?>" height="<?php echo $itemTrailer->player_height; ?>">
										<?php foreach ($itemTrailer->files['video'] as $item): ?>
											<source type="<?php echo $item['type']; ?>" src="<?php echo $item['src']; ?>"/>
										<?php endforeach; ?>
										<?php if (count($itemTrailer->files['subtitles']) > 0):
											foreach ($itemTrailer->files['subtitles'] as $subtitle): ?>
												<track kind="subtitles" src="<?php echo $subtitle['file']; ?>"
													srclang="<?php echo $subtitle['lang_code']; ?>"
													label="<?php echo $subtitle['lang']; ?>"
													<?php echo $subtitle['default'] ? ' default' : ''; ?> />
											<?php endforeach;
										endif; ?>
										<?php if (count($itemTrailer->files['chapters']) > 0): ?>
											<track kind="chapters" src="<?php echo $itemTrailer->files['chapters']['file']; ?>" srclang="en" default/>
										<?php endif; ?>
									</video>
								</div>
							<?php else: ?>
								<div style="height: <?php echo $itemTrailer->player_height; ?>px;">
									<img src="<?php echo $itemTrailer->screenshot; ?>" height="<?php echo $itemTrailer->player_height; ?>"/></div>
							<?php endif; ?>
							<?php if (isset($itemTrailer->files['video_links'])
								&& (count($itemTrailer->files['video_links']) > 0 && $this->params->get('allow_movie_download') == 1)):
								?>
								<div class="video-links">
									<span class="title"><?php echo JText::_('COM_KA_DOWNLOAD_MOVIE_OTHER_FORMAT'); ?></span>
									<?php foreach ($itemTrailer->files['video_links'] as $item): ?>
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
		$itemMovie = $this->item->movie;

		if (!empty($itemMovie->resolution))
		{
			$resolution   = explode('x', $itemMovie->resolution);
			$moviePadding = round($resolution[1] / $resolution[0] * 100, 2);
		}
		else
		{
			$dar          = explode(':', $itemMovie->dar);
			$moviePadding = round($dar[1] / $dar[0] * 100, 2);
		}
?>
	<div class="clear"></div>
	<span id="movie"></span>
	<div class="accordion" id="movie_accordion">
		<div class="accordion-group">
			<div class="accordion-heading">
				<a class="accordion-toggle" data-toggle="collapse" data-parent="#movie_accordion" href="#toggleMovie"><?php echo JText::_('COM_KA_WATCH_MOVIE'); ?></a>
			</div>
			<div id="toggleMovie" class="accordion-body collapse<?php echo $this->mov_collapsed; ?>">
				<div class="accordion-inner">
					<div>
						<?php if ($itemMovie->embed_code != ''):
							echo '<div class="video-embed">' . $itemMovie->embed_code . '</div>';
						else: ?>
							<?php if (count($itemMovie->files['video']) > 0): ?>
								<div class="video-responsive" style="padding-bottom: <?php echo $moviePadding; ?>%;">
									<video controls preload="none" poster="<?php echo $itemMovie->screenshot; ?>"
										   width="<?php echo $itemMovie->player_width; ?>" height="<?php echo $itemMovie->player_height; ?>">
										<?php foreach ($itemMovie->files['video'] as $item): ?>
											<source type="<?php echo $item['type']; ?>" src="<?php echo $item['src']; ?>"/>
										<?php endforeach; ?>
										<?php if (count($itemMovie->files['subtitles']) > 0):
											foreach ($itemMovie->files['subtitles'] as $subtitle): ?>
												<track kind="subtitles" src="<?php echo $subtitle['file']; ?>"
													   srclang="<?php echo $subtitle['lang_code']; ?>"
													   label="<?php echo $subtitle['lang']; ?>"
													<?php echo $subtitle['default'] ? ' default' : ''; ?> />
											<?php endforeach;
										endif; ?>
										<?php if (count($itemMovie->files['chapters']) > 0): ?>
											<track kind="chapters" src="<?php echo $itemMovie->files['chapters']['file']; ?>" srclang="en" default/>
										<?php endif; ?>
									</video>
								</div>
							<?php else: ?>
								<div style="height: <?php echo $itemMovie->player_height; ?>px;">
									<img src="<?php echo $itemMovie->screenshot; ?>"/></div>
							<?php endif; ?>
							<?php if (isset($itemMovie->files['video_links'])
								&& (count($itemMovie->files['video_links']) > 0
									&& $this->params->get('allow_movie_download') == 1)):
								?>
								<div class="video-links">
									<span class="title"><?php echo JText::_('COM_KA_DOWNLOAD_MOVIE_OTHER_FORMAT'); ?></span>
									<?php foreach ($itemMovie->files['video_links'] as $item): ?>
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
