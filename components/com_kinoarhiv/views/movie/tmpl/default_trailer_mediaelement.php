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
$document->addScriptDeclaration("mejs.i18n.language('" . substr(JFactory::getLanguage()->getTag(), 0, 2) . "');");
JHtml::_('script', 'media/com_kinoarhiv/players/mediaelement/mediaelement-and-player.min.js');
KAComponentHelper::getScriptLanguage('', 'media/com_kinoarhiv/players/mediaelement/lang', true, true);
$document->addScriptDeclaration("
	jQuery(document).ready(function($){
		$('video').mediaelementplayer({
			success: function(player, node){
				$(player).closest('.mejs__container').attr('lang', mejs.i18n.language());
			}
		});
	});
");

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
					else:
						$mp4_file = ''; ?>
						<?php if (count($itemTrailer->files['video']) > 0): ?>

						<div style="overflow: hidden; width: 100%;">
							<video id="trailer" controls="controls" preload="none" poster="<?php echo $itemTrailer->screenshot; ?>"
								   width="<?php echo $itemTrailer->player_width; ?>" height="<?php echo $itemTrailer->player_height; ?>"
								   style="width: 100%; height: 100%;"
								   data-mejsoptions='{"pluginPath": "<?php echo JUri::base(); ?>media/com_kinoarhiv/players/mediaelement/"}'>
								<?php foreach ($itemTrailer->files['video'] as $item):
									$mp4_file = ($item['type'] == 'video/mp4') ? $item['src'] : ''; ?>
									<source type="<?php echo $item['type']; ?>" src="<?php echo $item['src']; ?>"/>
								<?php endforeach; ?>
								<?php if (count($itemTrailer->files['subtitles']) > 0):
									foreach ($itemTrailer->files['subtitles'] as $subtitle): ?>
										<track kind="subtitles" src="<?php echo $subtitle['file']; ?>"
											srclang="<?php echo $subtitle['lang_code']; ?>"
											label="<?php echo $subtitle['lang']; ?>"
											<?php echo $subtitle['default'] ? ' default="default"' : ''; ?> />
									<?php endforeach;
								endif; ?>
								<?php if (count($itemTrailer->files['chapters']) > 0): ?>
									<track kind="chapters" src="<?php echo $itemTrailer->files['chapters']['file']; ?>" srclang="en" default="default"/>
								<?php endif; ?>
								<object width="<?php echo $itemTrailer->player_width; ?>" height="<?php echo $itemTrailer->player_height; ?>"
										type="application/x-shockwave-flash"
										data="<?php echo JUri::base(); ?>media/com_kinoarhiv/players/mediaelement/mediaelement-flash-video.swf">
									<param name="movie" value="<?php echo JUri::base(); ?>media/com_kinoarhiv/players/mediaelement/mediaelement-flash-video.swf"/>
									<param name="flashvars" value="controls=true&file=<?php echo $mp4_file; ?>"/>
									<img src="<?php echo $itemTrailer->screenshot; ?>" width="<?php echo $itemTrailer->player_width; ?>" height="<?php echo $itemTrailer->player_height; ?>" title="No video playback capabilities"/>
								</object>
							</video>
						</div>

						<?php else: ?>
							<div style="height: <?php echo $itemTrailer->player_height; ?>px;">
								<img src="<?php echo $itemTrailer->screenshot; ?>"/>
							</div>
						<?php endif; ?>

						<?php if (isset($itemTrailer->files['video_links'])
						&& (count($itemTrailer->files['video_links']) > 0 && $this->params->get('allow_movie_download') == 1)
						):
						?>
							<div class="video-links">
								<span class="title"><?php echo JText::_('COM_KA_DOWNLOAD_MOVIE_OTHER_FORMAT'); ?></span>
								<?php foreach ($itemTrailer->files['video_links'] as $item): ?>
									<div><a href="<?php echo $item['src']; ?>"><?php echo $item['src']; ?></a></div>
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
	$itemMovie = $this->item->movie; ?>

	<div class="clear"></div>
	<span id="movie"></span>
	<div class="accordion" id="movie_accordion">
		<div class="accordion-group">
			<div class="accordion-heading">
				<a class="accordion-toggle" data-toggle="collapse" data-parent="#movie_accordion" href="#toggleTrailer"><?php echo JText::_('COM_KA_WATCH_TRAILER'); ?></a>
			</div>
			<div id="toggleTrailer" class="accordion-body collapse<?php echo $this->tr_collapsed; ?>">
				<div class="accordion-inner">
					<div>

					<?php if ($itemMovie->embed_code != ''):
						echo '<div class="video-embed">' . $itemMovie->embed_code . '</div>';
					else:
						$mp4_file = ''; ?>
						<?php if (count($itemMovie->files['video']) > 0): ?>

						<div style="overflow: hidden; width: 100%;">
							<video id="movie" controls="controls" preload="none" poster="<?php echo $itemMovie->screenshot; ?>"
								   width="<?php echo $itemMovie->player_width; ?>" height="<?php echo $itemMovie->player_height; ?>"
								   style="width: 100%; height: 100%;"
								   data-mejsoptions='{"pluginPath": "<?php echo JUri::base(); ?>media/com_kinoarhiv/players/mediaelement/"}'>
								<?php foreach ($itemMovie->files['video'] as $item):
									$mp4_file = ($item['type'] == 'video/mp4') ? $item['src'] : ''; ?>
									<source type="<?php echo $item['type']; ?>" src="<?php echo $item['src']; ?>"/>
								<?php endforeach; ?>
								<?php if (count($itemMovie->files['subtitles']) > 0):
									foreach ($itemMovie->files['subtitles'] as $subtitle): ?>
										<track kind="subtitles" src="<?php echo $subtitle['file']; ?>"
											srclang="<?php echo $subtitle['lang_code']; ?>"
											label="<?php echo $subtitle['lang']; ?>"
											<?php echo $subtitle['default'] ? ' default="default"' : ''; ?> />
									<?php endforeach;
								endif; ?>
								<?php if (count($itemMovie->files['chapters']) > 0): ?>
									<track kind="chapters" src="<?php echo $itemMovie->files['chapters']['file']; ?>" srclang="en" default="default"/>
								<?php endif; ?>
								<object width="<?php echo $itemMovie->player_width; ?>" height="<?php echo $itemMovie->player_height; ?>"
										type="application/x-shockwave-flash"
										data="<?php echo JUri::base(); ?>media/com_kinoarhiv/players/mediaelement/mediaelement-flash-video.swf">
									<param name="movie" value="<?php echo JUri::base(); ?>media/com_kinoarhiv/players/mediaelement/mediaelement-flash-video.swf"/>
									<param name="flashvars" value="controls=true&file=<?php echo $mp4_file; ?>"/>
									<img src="<?php echo $itemMovie->screenshot; ?>" width="<?php echo $itemMovie->player_width; ?>" height="<?php echo $itemMovie->player_height; ?>" title="No video playback capabilities"/>
								</object>
							</video>
						</div>

						<?php else: ?>
							<div style="height: <?php echo $itemMovie->player_height; ?>px;">
								<img src="<?php echo $itemMovie->screenshot; ?>"/>
							</div>
						<?php endif; ?>

						<?php if (isset($itemMovie->files['video_links'])
						&& (count($itemMovie->files['video_links']) > 0 && $this->params->get('allow_movie_download') == 1)
						):
						?>
							<div class="video-links">
								<span class="title"><?php echo JText::_('COM_KA_DOWNLOAD_MOVIE_OTHER_FORMAT'); ?></span>
								<?php foreach ($itemMovie->files['video_links'] as $item): ?>
									<div><a href="<?php echo $item['src']; ?>"><?php echo $item['src']; ?></a></div>
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
