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

$totalTrailers = count($this->item->trailers);
$playerLayout  = ($this->params->get('player_type') == '-1') ? 'video_player' : 'video_player_' . $this->params->get('player_type');

if (isset($this->item->trailers) && $totalTrailers > 0): ?>
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
?>
<div class="ka-content">
	<?php if ($this->params->get('use_alphabet') == 1):
		echo JLayoutHelper::render(
			'layouts.navigation.movie_alphabet',
			array('url' => 'index.php?option=com_kinoarhiv&view=movies&content=movies&Itemid=' . $this->moviesItemid, 'params' => $this->params),
			JPATH_COMPONENT
		);
	endif; ?>

	<article class="uk-article item">
		<?php
		echo JLayoutHelper::render(
			'layouts.navigation.movie_item_header',
			array(
				'params' => $this->params,
				'item'   => $this->item,
				'itemid' => $this->itemid,
				'guest'  => $this->user->get('guest'),
				'url'    => 'index.php?option=com_kinoarhiv&view=movie&id=' . $this->item->id . '&Itemid=' . $this->itemid
			),
			JPATH_COMPONENT
		);
		?>
		<?php echo $this->item->event->afterDisplayTitle; ?>
		<?php echo $this->loadTemplate('tabs'); ?>
		<?php echo $this->item->event->beforeDisplayContent; ?>

		<?php if (isset($this->item->trailers) && $totalTrailers > 0): ?>
			<div class="trailers">

			<?php foreach ($this->item->trailers as $key => $itemTrailer): ?>
				<div class="video-title corner-top">
					<?php echo ($itemTrailer->title == '') ? JText::_('COM_KA_TRAILER') : $itemTrailer->title; ?>
				</div>

				<?php if ($itemTrailer->embed_code != ''):
					echo '<div class="video-embed">' . $itemTrailer->embed_code . '</div>';
				else:
					$itemTrailer->player_width = $this->item->player_width;

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
						<img src="<?php echo $itemTrailer->screenshot; ?>" height="<?php echo $itemTrailer->player_height; ?>"/>
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
				<br/>
			<?php endforeach; ?>

			</div>
		<?php else: ?>
			<div><?php echo KAComponentHelper::showMsg(JText::_('COM_KA_NO_ITEMS')); ?></div>
		<?php endif; ?>
	</article>
	<?php echo $this->item->event->afterDisplayContent; ?>
</div>
