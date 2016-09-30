<?php
/**
 * @package     Kinoarhiv.Site
 * @subpackage  com_kinoarhiv
 *
 * @copyright   Copyright (C) 2010 Libra.ms. All rights reserved.
 * @license     GNU General Public License version 2 or later
 * @url            http://киноархив.com/
 */

defined('_JEXEC') or die;

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

	<?php if ($this->params->get('player_type') == 'mediaelement'): ?>
		<script type="text/javascript">
			jQuery(document).ready(function ($) {
				$('video').mediaelementplayer({
					pluginPath: '<?php echo JUri::base(); ?>components/com_kinoarhiv/assets/players/mediaelement/',
					flashName: 'flashmediaelement.swf',
					silverlightName: 'silverlightmediaelement.xap',
					success: function(player, node){
						$(player).closest('.mejs-container').attr('lang', mejs.i18n.getLanguage());
					}
				});
			});
		</script>
	<?php elseif ($this->params->get('player_type') == 'flowplayer'): ?>
		<script type="text/javascript">
			jQuery(document).ready(function ($) {
				$('a.play').click(function (e) {
					e.preventDefault();

					if ($(this).hasClass('watch-trailer')) {
						if (!window.open('<?php echo JRoute::_('index.php?option=com_kinoarhiv&view=movie&task=watch&type=trailer&id='.$this->item->id.'&Itemid='.$this->itemid.'&format=raw', false); ?>')) {
							showMsg('.watch-buttons', '<?php echo JText::sprintf('COM_KA_NEWWINDOW_BLOCKED', JRoute::_('index.php?option=com_kinoarhiv&view=movie&task=watch&type=trailer&id='.$this->item->id.'&Itemid='.$this->itemid.'&format=raw', false))?>');
						}
					} else if ($(this).hasClass('watch-movie')) {
						if (!window.open('<?php echo JRoute::_('index.php?option=com_kinoarhiv&view=movie&task=watch&type=movie&id='.$this->item->id.'&Itemid='.$this->itemid.'&format=raw', false); ?>')) {
							showMsg('.watch-buttons', '<?php echo JText::sprintf('COM_KA_NEWWINDOW_BLOCKED', JRoute::_('index.php?option=com_kinoarhiv&view=movie&task=watch&type=movie&id='.$this->item->id.'&Itemid='.$this->itemid.'&format=raw', false))?>');
						}
					}
				});
			});
		</script>
	<?php endif;
endif; ?>

<div class="content movie trailers">
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
			if ($this->params->get('player_type') != '-1')
			{
				KAComponentHelper::loadPlayerAssets($this->params->get('player_type'));
			}

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
										<div class="video-responsive" style="padding-bottom: <?php echo $video_padding; ?>%;">
											<?php if ($this->params->get('player_type') == '-1'): ?>

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
														<a href="http://videojs.com/html5-video-support/" target="_blank">supports
															HTML5 video</a></p>
												</video>

											<?php elseif ($this->params->get('player_type') == 'mediaelement'): ?>

												<div style="overflow: hidden; width: 100%;">
													<video controls="controls" preload="none" poster="<?php echo $item_trailer->screenshot; ?>"
														   width="<?php echo $this->item->player_width; ?>"
														   height="<?php echo $item_trailer->player_height; ?>"
														   style="width: 100%; height: 100%;">
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
														<object width="<?php echo $this->item->player_width; ?>" height="<?php echo $item_trailer->player_height; ?>" type="application/x-shockwave-flash" data="components/com_kinoarhiv/assets/players/mediaelement/flashmediaelement.swf">
															<param name="movie" value="<?php echo JUri::base(); ?>components/com_kinoarhiv/assets/players/mediaelement/flashmediaelement.swf"/>
															<param name="flashvars" value="controls=true&file=<?php echo $mp4_file; ?>"/>
															<img src="<?php echo $item_trailer->screenshot; ?>" width="<?php echo $this->item->player_width; ?>" height="<?php echo $item_trailer->player_height; ?>" title="No video playback capabilities"/>
														</object>
													</video>
												</div>

											<?php elseif ($this->params->get('player_type') == 'flowplayer'):
												$watch = $item_trailer->is_movie ? 'watch-movie' : 'watch-trailer';
												$ln_watch = $item_trailer->is_movie ? JText::_('COM_KA_WATCH_MOVIE') : JText::_('COM_KA_WATCH_TRAILER');
												?>

												<div style="height: <?php echo $item_trailer->player_height; ?>px;">
													<a href="#" class="play <?php echo $watch; ?>" title="<?php echo $ln_watch; ?>"><img src="<?php echo $item_trailer->screenshot; ?>" style="width: 100%;"/></a>
												</div>

											<?php endif; ?>
										</div>
									<?php else: ?>
										<div style="height: <?php echo $item_trailer->player_height; ?>px;">
											<img src="<?php echo $item_trailer->screenshot; ?>"/>
										</div>
									<?php endif; ?>
								<?php endif; ?>
							</div>
						</div>
						<?php if (isset($item_trailer->files['video_links']) && (count($item_trailer->files['video_links']) > 0 && $this->params->get('allow_movie_download') == 1)): ?>
							<div class="ui-widget-content">
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
