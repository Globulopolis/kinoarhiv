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

JHtml::_('jquery.framework');

$document = JFactory::getDocument();
$total_trailers = count($this->item->trailers);

if (isset($this->item->trailers) && $total_trailers > 0): ?>
	<script type="text/javascript">
		jQuery(document).ready(function ($) {
			var embed = $("iframe[src^='//player.vimeo.com'], iframe[src*='www.youtube.com'], iframe[src*='www.youtube-nocookie.com'], object, embed"),
				embed_container = $('.video-embed');

			embed.each(function(){
				$(this).attr('data-aspectRatio', this.height / this.width).removeAttr('height').removeAttr('width');
			});

			$(window).resize(function(){
				var new_width = embed_container.width();

				embed.each(function(){
					var $this = $(this);

					$this.width(new_width).height(new_width * $this.attr('data-aspectRatio'));
				});
			}).resize();


		});
	</script>
<?php endif;

if ($this->params->get('player_type') == 'videojs')
{
	JHtml::_('stylesheet', 'media/com_kinoarhiv/players/videojs/video-js.min.css');
	JHtml::_('script', 'media/com_kinoarhiv/players/videojs/ie8/videojs-ie8.min.js');
	JHtml::_('script', 'media/com_kinoarhiv/players/videojs/video.min.js');
	KAComponentHelper::getScriptLanguage('', 'media/com_kinoarhiv/players/videojs/lang');
	JFactory::getDocument()->addScriptDeclaration("videojs.options.flash.swf='" . JUri::base() . "media/com_kinoarhiv/players/videojs/video-js.swf';");
}
elseif ($this->params->get('player_type') == 'mediaelement')
{
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
}
elseif ($this->params->get('player_type') == 'flowplayer')
{
	JHtml::_('stylesheet', 'media/com_kinoarhiv/players/flowplayer/skin/skin.css');
	JHtml::_('script', 'media/com_kinoarhiv/players/flowplayer/flowplayer.min.js');
}
?>
<div class="ka-content">
	<?php if ($this->params->get('use_alphabet') == 1):
		echo JLayoutHelper::render('layouts.navigation.alphabet', array('params' => $this->params, 'itemid' => $this->itemid), JPATH_COMPONENT);
	endif; ?>

	<article class="uk-article">
		<?php
		echo JLayoutHelper::render('layouts.navigation.movie_item_header', array('params' => $this->params, 'item' => $this->item, 'itemid' => $this->itemid), JPATH_COMPONENT);
		echo $this->item->event->afterDisplayTitle;
		echo $this->loadTemplate('tabs');
		echo $this->item->event->beforeDisplayContent; ?>

		<?php if (isset($this->item->trailers) && $total_trailers > 0):
			$trailers_obj = $this->item->trailers; ?>
			<div class="accordion" id="tr_accordion">

				<?php foreach ($trailers_obj as $key => $item_trailer):
					if (!empty($item_trailer->resolution))
					{
						$resolution = explode('x', $item_trailer->resolution);
						$video_padding = round($resolution[1] / $resolution[0] * 100, 2);
					}
					else
					{
						$dar = explode(':', $item_trailer->dar);
						$video_padding = round($dar[1] / $dar[0] * 100, 2);
					}
				?>
					<div class="accordion-group">
						<div class="accordion-heading">
							<a class="accordion-toggle" data-toggle="collapse" href="#toggleVideo-<?php echo $key; ?>">
								<?php echo ($item_trailer->title == '') ? JText::_('COM_KA_TRAILER') : $item_trailer->title; ?>
							</a>
						</div>
						<div id="toggleVideo-<?php echo $key; ?>" class="accordion-body collapse in">
							<div class="accordion-inner">
								<?php if ($item_trailer->embed_code != ''):
									echo '<div class="video-embed">' . $item_trailer->embed_code . '</div>';
								else:
									if (count($item_trailer->files['video']) > 0): ?>
										<?php if ($this->params->get('player_type') == '-1'): ?>

											<div class="video-responsive" style="padding-bottom: <?php echo $video_padding; ?>%;">
												<video controls preload="none" poster="<?php echo $item_trailer->screenshot; ?>"
													   width="<?php echo $this->item->player_width; ?>" height="<?php echo $item_trailer->player_height; ?>">
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

										<?php elseif ($this->params->get('player_type') == 'videojs'): ?>

											<video class="video-js vjs-default-skin vjs-big-play-centered" controls
												   preload="none" poster="<?php echo $item_trailer->screenshot; ?>"
												   width="<?php echo $this->item->player_width; ?>" height="<?php echo $item_trailer->player_height; ?>"
												   data-setup='{"techOrder": ["html5", "flash"], "fluid": true, "language": "<?php echo JFactory::getLanguage()->getTag(); ?>"}'>
												<?php foreach ($item_trailer->files['video'] as $item): ?>
													<source type="<?php echo $item['type']; ?>" src="<?php echo $item['src']; ?>"/>
												<?php endforeach; ?>
												<?php if (count($item_trailer->files['subtitles']) > 0):
													foreach ($item_trailer->files['subtitles'] as $subtitle): ?>
														<track kind="subtitles" src="<?php echo $subtitle['file']; ?>"
															   srclang="<?php echo $subtitle['lang_code']; ?>"
															   label="<?php echo $subtitle['lang']; ?>"
															<?php echo $subtitle['default'] ? ' default="default"' : ''; ?> />
													<?php endforeach;
												endif; ?>
												<?php if (count($item_trailer->files['chapters']) > 0): ?>
													<track kind="chapters" src="<?php echo $item_trailer->files['chapters']['file']; ?>" srclang="en" default/>
												<?php endif; ?>
												<p class="vjs-no-js">To view this video please enable JavaScript, and
													consider upgrading to a web browser that
													<a href="http://videojs.com/html5-video-support/" target="_blank">supports HTML5 video</a></p>
											</video>

										<?php elseif ($this->params->get('player_type') == 'mediaelement'): ?>

											<div style="overflow: hidden; width: 100%;">
												<video controls="controls" preload="none" poster="<?php echo $item_trailer->screenshot; ?>"
													   width="<?php echo $this->item->player_width; ?>"
													   height="<?php echo $item_trailer->player_height; ?>"
													   style="width: 100%; height: 100%;"
													   data-mejsoptions='{"pluginPath": "<?php echo JUri::base(); ?>media/com_kinoarhiv/players/mediaelement/"}'>
													<?php foreach ($item_trailer->files['video'] as $item):
														$mp4_file = ($item['type'] == 'video/mp4') ? $item['src'] : ''; ?>
														<source type="<?php echo $item['type']; ?>" src="<?php echo $item['src']; ?>"/>
													<?php endforeach; ?>
													<?php if (count($item_trailer->files['subtitles']) > 0):
														foreach ($item_trailer->files['subtitles'] as $subtitle): ?>
															<track kind="subtitles" src="<?php echo $subtitle['file']; ?>"
																   srclang="<?php echo $subtitle['lang_code']; ?>"
																   label="<?php echo $subtitle['lang']; ?>"
																<?php echo $subtitle['default'] ? ' default="default"' : ''; ?> />
														<?php endforeach;
													endif; ?>
													<?php if (count($item_trailer->files['chapters']) > 0): ?>
														<track kind="chapters" src="<?php echo $item_trailer->files['chapters']['file']; ?>" srclang="en" default="default"/>
													<?php endif; ?>
													<object width="<?php echo $this->item->player_width; ?>" height="<?php echo $item_trailer->player_height; ?>" type="application/x-shockwave-flash" data="media/com_kinoarhiv/players/mediaelement/mediaelement-flash-video.swf">
														<param name="movie" value="<?php echo JUri::base(); ?>media/com_kinoarhiv/players/mediaelement/mediaelement-flash-video.swf"/>
														<param name="flashvars" value="controls=true&file=<?php echo $mp4_file; ?>"/>
														<img src="<?php echo $item_trailer->screenshot; ?>" width="<?php echo $this->item->player_width; ?>" height="<?php echo $item_trailer->player_height; ?>" title="No video playback capabilities"/>
													</object>
												</video>
											</div>

										<?php elseif ($this->params->get('player_type') == 'flowplayer'):
											$ratio_raw = explode(':', $item_trailer->dar);
											$ratio = round($ratio_raw[1] / $ratio_raw[0], 4);
											?>

											<div class="flowplayer fp-full is-splash" data-ratio="<?php echo $ratio; ?>"
												 data-splash="<?php echo $item_trailer->screenshot; ?>"
												 data-swf="<?php echo JUri::base(); ?>media/com_kinoarhiv/players/flowplayer/flowplayer.swf"
												 data-swfHls="<?php echo JUri::base(); ?>media/com_kinoarhiv/players/flowplayer/flowplayerhls.swf">
												<video>
													<?php foreach ($item_trailer->files['video'] as $item): ?>
														<source type="<?php echo $item['type']; ?>" src="<?php echo $item['src']; ?>"/>
													<?php endforeach; ?>
													<?php if (count($item_trailer->files['subtitles']) > 0):
														foreach ($item_trailer->files['subtitles'] as $subtitle): ?>
															<track kind="subtitles" src="<?php echo $subtitle['file']; ?>" srclang="<?php echo $subtitle['lang_code']; ?>"
																   label="<?php echo $subtitle['lang']; ?>"<?php echo $subtitle['default'] ? ' default' : ''; ?> />
														<?php endforeach;
													endif; ?>
													<?php if (count($item_trailer->files['chapters']) > 0): ?>
														<track kind="chapters" src="<?php echo $item_trailer->files['chapters']['file']; ?>" srclang="en" default/>
													<?php endif; ?>
												</video>
											</div>

										<?php endif; ?>
									<?php else: ?>
										<div style="height: <?php echo $item_trailer->player_height; ?>px;">
											<img src="<?php echo $item_trailer->screenshot; ?>"/>
										</div>
									<?php endif; ?>
								<?php endif; ?>
							</div>
						</div>
						<?php if (isset($item_trailer->files['video_links']) && (count($item_trailer->files['video_links']) > 0 && $this->params->get('allow_movie_download') == 1)): ?>
							<div>
								<div class="video-links">
									<span class="title"><?php echo JText::_('COM_KA_DOWNLOAD_MOVIE_OTHER_FORMAT'); ?></span>
									<?php foreach ($item_trailer->files['video_links'] as $item): ?>
										<div>
											<a href="<?php echo $item['src']; ?>"><?php echo $item['src']; ?></a>
										</div>
									<?php endforeach; ?>
								</div>
							</div>
						<?php endif; ?>
					</div>
				<?php endforeach; ?>
			</div>
		<?php else: ?>
			<div><?php echo KAComponentHelper::showMsg(JText::_('COM_KA_NO_ITEMS')); ?></div>
		<?php endif; ?>
	</article>
	<?php echo $this->item->event->afterDisplayContent; ?>
</div>
