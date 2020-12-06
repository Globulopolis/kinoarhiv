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

if (isset($this->item->trailer) && count(get_object_vars($this->item->trailer)) > 0):
	$itemTrailer = $this->item->trailer; ?>
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
							<?php if (count($itemTrailer->files['video']) > 0):
								$tposter = $itemTrailer->screenshot != '' ? 'poster="' . $itemTrailer->screenshot . '"' : ''; ?>
								<video class="video-js vjs-default-skin vjs-big-play-centered" controls preload="none" <?php echo $tposter; ?>
									   width="<?php echo $itemTrailer->player_width; ?>" height="<?php echo $itemTrailer->player_height; ?>"
									   data-setup='{"techOrder": ["html5", "flash"], "fluid": true, "language": "<?php echo $this->lang->getTag(); ?>"}'>
									<?php foreach ($itemTrailer->files['video'] as $item): ?>
										<source type="<?php echo $item['type']; ?>" src="<?php echo $item['src']; ?>"/>
									<?php endforeach; ?>

									<?php if (count($itemTrailer->files['subtitles']) > 0):
										foreach ($itemTrailer->files['subtitles'] as $subtitle): ?>
											<track kind="subtitles" src="<?php echo $subtitle['file']; ?>" srclang="<?php echo $subtitle['lang_code']; ?>"
												   label="<?php echo $subtitle['lang']; ?>"<?php echo $subtitle['default'] ? ' default' : ''; ?> />
										<?php endforeach;
									endif; ?>

									<?php if (count($itemTrailer->files['chapters']) > 0): ?>
										<track kind="chapters" src="<?php echo $itemTrailer->files['chapters']['file']; ?>" srclang="en" default/>
									<?php endif; ?>
									<p class="vjs-no-js">To view this video please enable JavaScript, and consider upgrading to a web browser that <a href="http://videojs.com/html5-video-support/" target="_blank">supports HTML5 video</a></p>
								</video>
							<?php else: ?>
								<div style="height: <?php echo $itemTrailer->player_height; ?>px; text-align: center;">
									<img src="<?php echo $itemTrailer->screenshot; ?>" style="height: <?php echo $itemTrailer->player_height; ?>px; width: <?php echo $itemTrailer->player_height; ?>px;"/></div>
							<?php endif; ?>

							<?php if (isset($itemTrailer->files['video_links']) && (count($itemTrailer->files['video_links']) > 0 && $this->params->get('allow_movie_download') == 1)): ?>
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

if ((isset($this->item->movie) && count(get_object_vars($this->item->movie)) > 0)
	&& ($this->params->get('allow_guest_watch') == 1 && $this->user->guest || $this->user->id != '')):
	$itemMovie = $this->item->movie;
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
							<?php if (count($itemMovie->files['video']) > 0):
								$mposter = $itemMovie->screenshot != '' ? 'poster="' . $itemMovie->screenshot . '"' : ''; ?>
								<video class="video-js vjs-default-skin vjs-big-play-centered" controls preload="none" <?php echo $mposter; ?>
									   width="<?php echo $itemMovie->player_width; ?>" height="<?php echo $itemMovie->player_height; ?>"
									   data-setup='{"techOrder": ["html5", "flash"], "fluid": true, "language": "<?php echo $this->lang->getTag(); ?>"}'>
									<?php foreach ($itemMovie->files['video'] as $item): ?>
										<source type="<?php echo $item['type']; ?>" src="<?php echo $item['src']; ?>"/>
									<?php endforeach; ?>
									<?php if (count($itemMovie->files['subtitles']) > 0):
										foreach ($itemMovie->files['subtitles'] as $subtitle): ?>
											<track kind="subtitles" src="<?php echo $subtitle['file']; ?>" srclang="<?php echo $subtitle['lang_code']; ?>"
												   label="<?php echo $subtitle['lang']; ?>"<?php echo $subtitle['default'] ? ' default' : ''; ?> />
										<?php endforeach;
									endif; ?>
									<?php if (count($itemMovie->files['chapters']) > 0): ?>
										<track kind="chapters" src="<?php echo $itemMovie->files['chapters']['file']; ?>" srclang="en" default/>
									<?php endif; ?>
									<p class="vjs-no-js">To view this video please enable JavaScript, and consider upgrading to a web browser that <a href="http://videojs.com/html5-video-support/" target="_blank">supports HTML5 video</a></p>
								</video>
							<?php else: ?>
								<div style="height: <?php echo $itemMovie->player_height; ?>px;">
									<img src="<?php echo $itemMovie->screenshot; ?>"/></div>
							<?php endif; ?>
							<?php if (isset($itemMovie->files['video_links'])
								&& (count($itemMovie->files['video_links']) > 0 && $this->params->get('allow_movie_download') == 1)
							):
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
