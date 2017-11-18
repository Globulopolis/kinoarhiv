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

JHtml::_('bootstrap.loadcss');
JHtml::_('stylesheet', 'media/com_kinoarhiv/css/colorbox.css');
JHtml::_('script', 'media/com_kinoarhiv/js/jquery.colorbox.min.js');
KAComponentHelper::getScriptLanguage('jquery.colorbox-', 'media/com_kinoarhiv/js/i18n/colorbox');
JHtml::_('script', 'media/com_kinoarhiv/js/jquery.rateit.min.js');
?>
<script type="text/javascript">
	jQuery(document).ready(function ($) {
		<?php if (!$this->user->guest): ?>
		<?php if ($this->params->get('allow_votes') == 1): ?>
		$('.rateit').bind('over', function (e, v) {
			$(this).attr('title', v);
		});
		$('.rate .rateit').bind('rated reset', function (e) {
			var $this = $(this),
				value = $this.rateit('value'),
				url = $this.data('url');

			$.ajax({
				type: 'POST',
				url: url,
				data: {'value': value}
			}).done(function (response) {
				var my_votes = $('.rate .my_votes'),
					my_vote = $('.rate .my_vote');

				if (my_votes.is(':hidden')) {
					my_votes.show();
				}

				if (value !== 0) {
					if (my_vote.is(':hidden')) {
						my_vote.show();
					}
					$('.rate .my_vote span.small').text('<?php echo JText::_('COM_KA_RATE_MY_CURRENT'); ?>' + value);
				} else {
					$('.rate .my_vote span').text('').parent().hide();
				}
				showMsg($('.my_vote').next(), response.message);
			}).fail(function (xhr, status, error) {
				showMsg($('.my_vote').next(), error);
			});
		});
		<?php endif; ?>
		<?php endif; ?>
	});
</script>
<div class="ka-content">
	<?php if ($this->params->get('use_alphabet') == 1):
		echo JLayoutHelper::render('layouts.navigation.alphabet', array('params' => $this->params, 'itemid' => $this->itemid), JPATH_COMPONENT);
	endif; ?>

	<article class="uk-article">
		<?php
		echo JLayoutHelper::render(
			'layouts.navigation.movie_item_header',
			array('params' => $this->params, 'item' => $this->item, 'itemid' => $this->itemid),
			JPATH_COMPONENT
		);
		echo $this->item->event->afterDisplayTitle;
		echo $this->loadTemplate('tabs');
		echo $this->item->event->beforeDisplayContent; ?>

		<div class="snd-list">
			<?php if (!empty($this->item->albums)): ?>
			<ul class="media-list">

			<?php foreach ($this->item->albums as $album):
					$composer = KAContentHelper::formatItemTitle($album->name, $album->latin_name);
					$cover_size = explode('x', $this->params->get('music_covers_size'));
			?>

				<li class="media">
					<a class="pull-left album-art poster" href="<?php echo $album->cover['poster']; ?>"><img src="<?php echo $album->cover['th_poster']; ?>" class="media-object" width="<?php echo $album->cover['size']->width; ?>" height="<?php echo $album->cover['size']->height; ?>" /></a>
					<div class="media-body">
						<h3 class="media-heading album-title"><?php echo $this->escape($album->title); ?></h3>
						<span class="album-info">
							<?php if (!empty($composer)): ?>
							<span class="album-composer"><?php echo $composer; ?></span>
							<?php endif; ?>
							<?php if (!empty($album->year) && $album->year != '0000'): ?>
							<span class="album-year">(<?php echo $album->year; ?>)</span>
							<?php endif; ?>
						</span>

						<?php
						echo JLayoutHelper::render('layouts.content.votes_album',
							array(
								'params'  => $this->params,
								'item'    => $album,
								'guest'   => $this->user->get('guest'),
								'itemid'  => $this->itemid
							),
							JPATH_COMPONENT
						);
						?>

						<table class="track-list table table-striped table-condensed">
						<?php foreach ($this->item->tracks as $track):
							if ($track->album_id == $album->id): ?>
							<tr class="track-row">
								<td class="track-number"><?php echo !empty($track->track_number) ? $track->track_number . '. ' : ''; ?></td>
								<td class="track-title"><?php echo $this->escape($track->title); ?></td>
								<td class="track-length"><?php echo $track->length; ?></td>
							</tr>
							<?php endif;
						endforeach; ?>
						</table>
					</div>
				</li>
			<?php endforeach; ?>
			</ul>
			<?php else: ?>
				<div><?php echo KAComponentHelper::showMsg(JText::_('COM_KA_NO_ITEMS')); ?></div>
			<?php endif; ?>
		</div>
	</article>
	<?php echo $this->item->event->afterDisplayContent; ?>
</div>
