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

use Joomla\String\StringHelper;

$total_trailers = count($this->item->trailer);
$total_movies = count($this->item->movie);
$this->tr_collapsed = ' in';
$this->mov_collapsed = ' in';

// Set collapsed trailer
if ($this->item->attribs->trailer_collapsed === '')
{
	if ($this->params->get('trailer_collapsed') == 1)
	{
		$this->tr_collapsed = '';
	}
}
elseif ($this->item->attribs->trailer_collapsed == 1)
{
	$this->tr_collapsed = '';
}

// Set collapsed movie
if ($this->item->attribs->movie_collapsed === '')
{
	if ($this->params->get('movie_collapsed') == 1)
	{
		$this->mov_collapsed = '';
	}
}
elseif ($this->item->attribs->movie_collapsed == 1)
{
	$this->mov_collapsed = '';
}

if (StringHelper::substr($this->params->get('media_rating_image_root_www'), 0, 1) == '/')
{
	$rating_image_www = JUri::base() . StringHelper::substr($this->params->get('media_rating_image_root_www'), 1);
}
else
{
	$rating_image_www = $this->params->get('media_rating_image_root_www');
}

JHtml::_('script', 'components/com_kinoarhiv/assets/js/jquery.colorbox.min.js');
KAComponentHelper::getScriptLanguage('jquery.colorbox-', 'js/i18n/colorbox');
JHtml::_('script', 'components/com_kinoarhiv/assets/js/ui.aurora.min.js');
JHtml::_('script', 'components/com_kinoarhiv/assets/js/jquery.rateit.min.js');
JHtml::_('script', 'components/com_kinoarhiv/assets/js/jquery.plugin.min.js');
JHtml::_('script', 'components/com_kinoarhiv/assets/js/jquery.countdown.min.js');
KAComponentHelper::getScriptLanguage('jquery.countdown-', 'js/i18n/countdown');

if (isset($this->item->slides) && !empty($this->item->slides)):
	if (($this->item->attribs->slider == '' && $this->params->get('slider') == 1) || $this->item->attribs->slider == 1):
		JHtml::_('script', 'components/com_kinoarhiv/assets/js/jquery.bxslider.min.js');
	endif;
endif;
?>
<script type="text/javascript">
	jQuery(document).ready(function ($) {
		<?php if (!$this->user->guest): ?>
		<?php if ($this->params->get('allow_votes') == 1): ?>
		$('.rateit').bind('over', function (e, v) {
			$(this).attr('title', v);
		});
		$('.rate .rateit').bind('rated reset', function (e) {
			var _this = $(this);
			var value = _this.rateit('value');

			$.ajax({
				type: 'POST',
				url: '<?php echo JRoute::_(
					'index.php?option=com_kinoarhiv&view=movie&task=vote&id=' . $this->item->id . '&Itemid=' . $this->itemid . '&format=raw',
					false
				); ?>',
				data: {'value': value}
			}).done(function (response) {
				var my_votes = $('.rate .my_votes'),
					my_vote  = $('.rate .my_vote');

				if (my_votes.is(':hidden')) {
					my_votes.show();
				}

				if (value != 0) {
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
		<?php if ($this->params->get('link_watched') == 1): ?>
		$('.watched a').click(function (e) {
			e.preventDefault();
			var _this = $(this);

			$.ajax({
				url: _this.attr('href') + '&format=raw'
			}).done(function (response) {
				if (response.success) {
					_this.text(response.text);
					_this.attr('href', response.url);
					if (_this.hasClass('delete')) {
						_this.removeClass('delete').addClass('add');
					} else {
						_this.removeClass('add').addClass('delete');
					}
					showMsg($('.mark-links'), response.message);
				} else {
					showMsg($('.mark-links'), '<?php echo JText::_('JERROR_AN_ERROR_HAS_OCCURRED'); ?>');
				}
			}).fail(function (xhr, status, error) {
				showMsg($('.mark-links'), error);
			});
		});
		<?php endif; ?>
		<?php endif; ?>

		$('#mpaa').click(function (e) {
			e.preventDefault();
			$.colorbox({
				html: '<div class="desc">' + $(this).next('.mpaa-desc').html() + '</div>',
				height: '80%',
				width: '80%'
			});
		});
		$('#rrate').click(function (e) {
			e.preventDefault();
			$.colorbox({
				html: '<div class="desc">' + $(this).next('.rrate-desc').html() + '</div>',
				height: '80%',
				width: '80%'
			});
		});
		$('#ua-rate').click(function (e) {
			e.preventDefault();
			$.colorbox({
				html: '<div class="desc">' + $(this).next('.uarate-desc').html() + '</div>',
				height: '80%',
				width: '80%'
			});
		});
		$('.premiere-info-icon').click(function (e) {
			e.preventDefault();
			var _this = $(this);

			$.colorbox({html: '<div class="overlay">' + _this.next('div').html() + '</div>'});
		});
		<?php
		if ($this->params->get('player_type') == 'flowplayer' && ($total_trailers > 0 || $total_movies > 0)): ?>
		$('.watch-buttons a').click(function (e) {
			e.preventDefault();

			if ($(this).hasClass('watch-trailer')) {
				if (!window.open('<?php echo JRoute::_('index.php?option=com_kinoarhiv&view=movie&task=watch&type=trailer&id=' . $this->item->id . '&Itemid=' . $this->itemid . '&format=raw', false); ?>')) {
					showMsg('.watch-buttons', '<?php echo JText::sprintf('COM_KA_NEWWINDOW_BLOCKED', JRoute::_('index.php?option=com_kinoarhiv&view=movie&task=watch&type=trailer&id=' . $this->item->id . '&Itemid=' . $this->itemid . '&format=raw', false))?>');
				}
			} else if ($(this).hasClass('watch-movie')) {
				if (!window.open('<?php echo JRoute::_('index.php?option=com_kinoarhiv&view=movie&task=watch&type=movie&id=' . $this->item->id . '&Itemid=' . $this->itemid . '&format=raw', false); ?>')) {
					showMsg('.watch-buttons', '<?php echo JText::sprintf('COM_KA_NEWWINDOW_BLOCKED', JRoute::_('index.php?option=com_kinoarhiv&view=movie&task=watch&type=movie&id=' . $this->item->id . '&Itemid=' . $this->itemid . '&format=raw', false))?>');
				}
			}
		});
		<?php endif; ?>

		<?php if (isset($this->item->slides) && !empty($this->item->slides)):
			if (($this->item->attribs->slider == '' && $this->params->get('slider') == 1) || $this->item->attribs->slider == 1): ?>
		$('.bxslider').bxSlider({
			pager: false,
			minSlides: <?php echo (int) $this->params->get('slider_min_item'); ?>,
			maxSlides: <?php echo count($this->item->slides); ?>,
			slideWidth: <?php echo (int) $this->params->get('size_x_scr'); ?>,
			slideMargin: 5,
			infiniteLoop: false
		});

		$('.screenshot-slider li a').colorbox({returnFocus: false, maxHeight: '90%', maxWidth: '90%', rel: 'slideGroup', photo: true});
			<?php endif;
		endif; ?>

		$('.countdown-premiere').each(function(){
			var el = $(this);
			var el_datetime = el.data('premiere-datetime');

			if (typeof el_datetime === 'string') {
				var time = el_datetime.split(/[- :]/);
				var datetime = new Date(time[0], time[1] - 1, time[2], time[3] || 0, time[4] || 0, time[5] || 0);

				el.countdown({
					until: datetime,
					format: 'yodHM',
					layout: '{y<}{yn} {yl}{y>} {o<}{on} {ol}{o>} {d<}{dn} {dl}{d>} {hn} {hl} {mn} {ml}',
					alwaysExpire: true,
					onExpiry: function () {
						el.countdown('destroy');
					}
				});
			}
		});

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
<div class="content movie" itemscope itemtype="http://schema.org/Movie">
	<meta itemprop="contentRating" content="MPAA <?php echo strtoupper($this->item->mpaa); ?>">
	<meta itemprop="duration" content="<?php echo $this->item->_length; ?>">
	<meta itemprop="isFamilyFriendly" content="<?php echo ($this->item->mpaa == 'g' || $this->item->mpaa == 'pg') ? 'True' : 'False';?>">

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

		<div class="info">
			<div class="left-col">
				<div class="poster">
					<a href="<?php echo JRoute::_('index.php?option=com_kinoarhiv&view=movie&page=posters&id=' . $this->item->id . '&Itemid=' . $this->itemid); ?>" title="<?php echo $this->escape(KAContentHelper::formatItemTitle($this->item->title, '', $this->item->year)); ?>"><img src="<?php echo $this->item->poster; ?>" border="0" alt="<?php echo JText::_('COM_KA_POSTER_ALT') . $this->escape($this->item->title); ?>" itemprop="image"/></a>
				</div>
				<div class="ratings">
					<?php if (!empty($this->item->rate_custom)): ?>
						<div><?php echo $this->item->rate_custom; ?></div>
					<?php else: ?>
						<?php if (($this->item->attribs->ratings_show_remote == '' && $this->params->get('ratings_show_remote') == 1) || $this->item->attribs->ratings_show_remote == 1): ?>
							<?php if ($this->params->get('ratings_show_img') == 1): ?>
								<div style="text-align: center;">
									<?php if ($this->params->get('ratings_img_imdb') != 0 && !empty($this->item->imdb_id))
									{
										if (file_exists($this->params->get('media_rating_image_root') . '/imdb/' . $this->item->id . '_big.png'))
										{ ?>
											<a href="http://www.imdb.com/title/tt<?php echo $this->item->imdb_id; ?>/" rel="nofollow" target="_blank"><img src="<?php echo $rating_image_www; ?>/imdb/<?php echo $this->item->id; ?>_big.png" border="0"/></a>
										<?php }
									} ?>
									<?php if ($this->params->get('ratings_img_kp') != 0 && !empty($this->item->kp_id)): ?>
										<a href="https://www.kinopoisk.ru/film/<?php echo $this->item->kp_id; ?>/" rel="nofollow" target="_blank">
											<?php if ($this->params->get('ratings_img_kp_remote') == 0): ?>
												<img src="<?php echo $rating_image_www; ?>/kinopoisk/<?php echo $this->item->id; ?>_big.png" border="0"/>
											<?php else: ?>
												<img src="https://www.kinopoisk.ru/rating/<?php echo $this->item->kp_id; ?>.gif" border="0" style="padding-left: 1px;"/>
											<?php endif; ?>
										</a>
									<?php endif; ?>
									<?php if ($this->params->get('ratings_img_rotten') != 0 && !empty($this->item->rottentm_id))
									{
										if (file_exists($this->params->get('media_rating_image_root') . '/rottentomatoes/' . $this->item->id . '_big.png'))
										{ ?>
											<a href="https://www.rottentomatoes.com/m/<?php echo $this->item->rottentm_id; ?>/" rel="nofollow" target="_blank"><img src="<?php echo $rating_image_www; ?>/rottentomatoes/<?php echo $this->item->id; ?>_big.png" border="0"/></a>
										<?php }
									} ?>
									<?php if ($this->params->get('ratings_img_metacritic') != 0 && !empty($this->item->metacritics_id))
									{
										if (file_exists($this->params->get('media_rating_image_root') . '/metacritic/' . $this->item->id . '_big.png'))
										{ ?>
											<a href="http://www.metacritic.com/movie/<?php echo $this->item->metacritics_id; ?>" rel="nofollow" target="_blank"><img src="<?php echo $rating_image_www; ?>/metacritic/<?php echo $this->item->id; ?>_big.png" border="0"/></a>
										<?php }
									} ?>
								</div>

							<?php else: ?>
								<?php if (!empty($this->item->imdb_votesum) && !empty($this->item->imdb_votes)): ?>
									<div id="rate-imdb">
										<span class="a"><?php echo JText::_('COM_KA_RATE_IMDB'); ?></span>
										<span class="b"><a href="http://www.imdb.com/title/tt<?php echo $this->item->imdb_id; ?>/?ref_=fn_al_tt_1" rel="nofollow" target="_blank"><?php echo $this->item->imdb_votesum; ?> (<?php echo $this->item->imdb_votes; ?>)</a></span>
									</div>
								<?php else: ?>
									<div id="rate-imdb">
										<span class="a"><?php echo JText::_('COM_KA_RATE_IMDB'); ?></span> <?php echo JText::_('COM_KA_RATE_NO'); ?>
									</div>
								<?php endif; ?>
								<?php if (!empty($this->item->kp_votesum) && !empty($this->item->kp_votes)): ?>
									<br/><br/>
									<div id="rate-kp"><span class="a"><?php echo JText::_('COM_KA_RATE_KP'); ?></span>
										<span class="b"><a href="https://www.kinopoisk.ru/film/<?php echo $this->item->kp_id; ?>/" rel="nofollow" target="_blank"><?php echo $this->item->kp_votesum; ?> (<?php echo $this->item->kp_votes; ?>)</a></span>
									</div>
								<?php else: ?>
									<div id="rate-kp">
										<span class="a"><?php echo JText::_('COM_KA_RATE_KP'); ?></span> <?php echo JText::_('COM_KA_RATE_NO'); ?>
									</div>
								<?php endif; ?>
								<?php if (!empty($this->item->rate_fc)): ?>
									<br/><br/>
									<div id="rate-rt"><span class="a"><?php echo JText::_('COM_KA_RATE_RT'); ?></span>
										<span class="b"><a href="https://www.rottentomatoes.com/m/<?php echo $this->item->rottentm_id; ?>/" rel="nofollow" target="_blank"><?php echo $this->item->rate_fc; ?> %</a></span>
									</div>
								<?php else: ?>
									<div id="rate-rt">
										<span class="a"><?php echo JText::_('COM_KA_RATE_RT'); ?></span> <?php echo JText::_('COM_KA_RATE_NO'); ?>
									</div>
								<?php endif; ?>
								<?php if (!empty($this->item->metacritics)): ?>
									<br/><br/>
									<div id="rate-mc"><span class="a"><?php echo JText::_('COM_KA_RATE_MC'); ?></span>
										<span class="b"><a href="http://www.metacritic.com/movie/<?php echo $this->item->metacritics_id; ?>/" rel="nofollow" target="_blank"><?php echo $this->item->metacritics; ?> %</a></span>
									</div>
								<?php else: ?>
									<div id="rate-mc">
										<span class="a"><?php echo JText::_('COM_KA_RATE_MC'); ?></span> <?php echo JText::_('COM_KA_RATE_NO'); ?>
									</div>
								<?php endif; ?>

							<?php endif; ?>
						<?php endif; ?>
					<?php endif; ?>
				</div>
			</div>
			<div class="right-col">
				<?php if (!$this->user->guest): ?>
					<div class="mark-links">
						<?php if ($this->params->get('link_watched') == 1): ?>
							<div class="watched">
								<?php if ($this->item->watched == 1): ?>
									<a href="<?php echo JRoute::_('index.php?option=com_kinoarhiv&view=movie&task=watched&action=delete&Itemid=' . $this->itemid . '&id=' . $this->item->id); ?>" class="delete"><?php echo JText::_('COM_KA_REMOVEFROM_WATCHED'); ?></a>
								<?php else: ?>
									<a href="<?php echo JRoute::_('index.php?option=com_kinoarhiv&view=movie&task=watched&action=add&Itemid=' . $this->itemid . '&id=' . $this->item->id); ?>" class="add"><?php echo JText::_('COM_KA_ADDTO_WATCHED'); ?></a>
								<?php endif; ?>
							</div>
						<?php endif; ?>
						<?php if ($this->params->get('link_favorite') == 1): ?>
							<div class="favorite">
								<?php if ($this->item->favorite == 1): ?>
									<a href="<?php echo JRoute::_('index.php?option=com_kinoarhiv&task=favorite&action=delete&Itemid=' . $this->itemid . '&id=' . $this->item->id); ?>" class="cmd-favorite delete" data-msg_placement=".mark-links"><?php echo JText::_('COM_KA_REMOVEFROM_FAVORITE'); ?></a>
								<?php else: ?>
									<a href="<?php echo JRoute::_('index.php?option=com_kinoarhiv&task=favorite&action=add&Itemid=' . $this->itemid . '&id=' . $this->item->id); ?>" class="cmd-favorite add" data-msg_placement=".mark-links"><?php echo JText::_('COM_KA_ADDTO_FAVORITE'); ?></a>
								<?php endif; ?>
							</div>
						<?php endif; ?>
					</div>
					<div class="clear"></div>
				<?php endif; ?>
				<div class="movie-info">
					<div>
						<span class="f-col"><?php echo JText::_('COM_KA_YEAR'); ?></span>
						<span class="s-col"><a href="<?php echo JRoute::_('index.php?option=com_kinoarhiv&view=movies&task=search&filters[movies][year]=' . $this->item->year . '&Itemid=' . $this->itemid); ?>" rel="nofollow"><?php echo $this->item->year; ?></a></span>
					</div>
					<?php if (!empty($this->item->countries)): ?>
						<div>
							<span class="f-col"><?php echo count($this->item->countries) > 1 ? JText::_('COM_KA_COUNTRIES') : JText::_('COM_KA_COUNTRY'); ?></span>
							<span class="s-col">
								<?php $cn_count = count($this->item->countries);
								for ($i = 0, $n = $cn_count; $i < $n; $i++):
									$country = $this->item->countries[$i]; ?>
									<img src="components/com_kinoarhiv/assets/themes/component/<?php echo $this->params->get('ka_theme'); ?>/images/icons/countries/<?php echo $country->code; ?>.png" border="0" class="ui-icon-country" alt="<?php echo $country->name; ?>"/>
									<a href="<?php echo JRoute::_('index.php?option=com_kinoarhiv&view=movies&filters[movies][country]=' . $country->id . '&Itemid=' . $this->itemid); ?>" title="<?php echo $country->name; ?>" rel="nofollow"><?php echo $country->name; ?></a><?php echo ($i + 1 == $n) ? '' : ', '; ?>
								<?php endfor; ?>
							</span>
						</div>
					<?php endif; ?>
					<?php if (!empty($this->item->slogan)): ?>
						<div>
							<span class="f-col"><?php echo JText::_('COM_KA_SLOGAN'); ?></span>
							<span class="s-col">
								<span lang="<?php echo substr($this->lang->getTag(), 0, 2); ?>"><q><?php echo $this->item->slogan; ?></q></span>
							</span>
						</div>
					<?php endif; ?>
					<?php if (isset($this->item->crew) && count($this->item->crew) > 0):
						foreach ($this->item->crew as $person): ?>
							<div>
								<span class="f-col"><?php echo $person['career']; ?></span>
								<span class="s-col">
									<?php $person_count = count($person['items']);
									for ($i = 0, $n = $person_count; $i < $n; $i++):
										$name = $person['items'][$i];
										$itemprop = ($name['directors'] == 1) ? 'itemprop="director"' : '';
									?>
										<a href="<?php echo JRoute::_('index.php?option=com_kinoarhiv&view=name&id=' . $name['id'] . '&Itemid=' . $this->itemid); ?>" title="<?php echo $name['name']; ?>" <?php echo $itemprop; ?>><?php echo $name['name']; ?></a><?php if ($i + 1 == $n): ?><?php if ($n < $person['total_items']): ?>,&nbsp;
									<a href="<?php echo JRoute::_('index.php?option=com_kinoarhiv&view=movie&page=cast&id=' . $this->item->id . '&Itemid=' . $this->itemid); ?>#<?php echo JFilterOutput::stringURLSafe($person['career']); ?>" title="<?php echo JText::_('COM_KA_READMORE'); ?>" class="hasTooltip ui-icon-next"></a><?php endif; ?>
									<?php else:
										echo ', ';
									endif; ?>
									<?php endfor; ?>
								</span>
							</div>
						<?php endforeach;
					endif; ?>
					<?php if (isset($this->item->cast) && count($this->item->cast) > 0):
						foreach ($this->item->cast as $person): ?>
							<div>
								<span class="f-col"><?php echo $person['career']; ?></span>
								<span class="s-col">
									<?php $person_count = count($person['items']);
									for ($i = 0, $n = $person_count; $i < $n; $i++):
										$name = $person['items'][$i]; ?>
										<a href="<?php echo JRoute::_('index.php?option=com_kinoarhiv&view=name&id=' . $name['id'] . '&Itemid=' . $this->itemid); ?>" title="<?php echo $name['name']; ?>" itemprop="actor"><?php echo $name['name']; ?></a><?php if ($i + 1 == $n): ?><?php if ($n < $person['total_items']): ?>,&nbsp;
									<a href="<?php echo JRoute::_('index.php?option=com_kinoarhiv&view=movie&page=cast&id=' . $this->item->id . '&Itemid=' . $this->itemid); ?>#<?php echo JFilterOutput::stringURLSafe($person['career']); ?>" title="<?php echo JText::_('COM_KA_READMORE'); ?>" class="hasTooltip ui-icon-next"></a><?php endif; ?>
									<?php else:
										echo ', ';
									endif; ?>
									<?php endfor; ?>
								</span>
							</div>
						<?php endforeach;
					endif; ?>
					<?php if (isset($this->item->genres) && count($this->item->genres) > 0): ?>
						<div>
							<span class="f-col"><?php echo JText::_('COM_KA_GENRE'); ?></span>
							<span class="s-col">
								<?php $genre_count = count($this->item->genres);
								for ($i = 0, $n = $genre_count; $i < $n; $i++):
									$genre = $this->item->genres[$i]; ?>
									<a href="<?php echo JRoute::_('index.php?option=com_kinoarhiv&view=movies&filters[movies][genre][]=' . $genre->id . '&Itemid=' . $this->itemid); ?>" title="<?php echo $genre->name; ?>" itemprop="genre" rel="nofollow"><?php echo $genre->name; ?></a><?php echo ($i + 1 == $n) ? '' : ', '; ?>
								<?php endfor; ?>
							</span>
						</div>
					<?php endif; ?>
					<?php if (!empty($this->item->budget)): ?>
						<div>
							<span class="f-col"><?php echo JText::_('COM_KA_BUDGET'); ?></span>
							<span class="s-col"><a href="<?php echo JRoute::_('index.php?option=com_kinoarhiv&view=movies&filters[movies][from_budget]=' . $this->item->budget . '&Itemid=' . $this->itemid); ?>" rel="nofollow"><?php echo $this->item->budget; ?></a></span>
						</div>
					<?php endif; ?>
					<?php if (count($this->item->premieres) > 0):
						foreach ($this->item->premieres as $premiere):
							$company_title = KAContentHelper::formatItemTitle($premiere->company_name, $premiere->company_name_intl); ?>
							<div>
								<span class="f-col"><?php echo ($premiere->country == '') ? JText::_('COM_KA_PREMIERE_DATE_WORLDWIDE') : JText::sprintf(JText::_('COM_KA_PREMIERE_DATE_LOC'), $premiere->country); ?></span>
								<span class="s-col">
									<a href="<?php echo JRoute::_('index.php?option=com_kinoarhiv&view=premieres&filters[movies][date]=' . date('Y-m', strtotime($premiere->premiere_date)) . '&Itemid=' . $this->itemid); ?>"><?php echo JHtml::_('date', $premiere->premiere_date, JText::_('DATE_FORMAT_LC3')); ?></a><?php if (!empty($company_title)): ?>, <a href="<?php echo JRoute::_('index.php?option=com_kinoarhiv&view=premieres&filters[movies][vendor]=' . $premiere->vendor_id . '&Itemid=' . $this->itemid); ?>"><?php echo $company_title; ?></a>
										<?php if ($premiere->info != ''): ?>
											<a href="#" class="ui-icon-bullet-arrow-down premiere-info-icon"></a>
											<div class="premiere-info"><?php echo $premiere->info; ?></div><?php endif; ?>
									<?php endif; ?>
									<div class="countdown-premiere" data-premiere-datetime="<?php echo $premiere->premiere_date; ?>"></div>
								</span>
							</div>
						<?php endforeach;
					endif; ?>
					<?php if (count($this->item->releases) > 0):
						foreach ($this->item->releases as $release):
							$company_title = KAContentHelper::formatItemTitle($release->company_name, $release->company_name_intl); ?>
							<div>
								<span class="f-col"><?php echo JText::sprintf('COM_KA_RELEASES_MEDIATYPE', JHtml::_('string.truncate', $release->media_type, 14)); ?></span>
								<span class="s-col">
									<?php echo JHtml::_('date', $release->release_date, JText::_('DATE_FORMAT_LC3')); ?><?php if (!empty($company_title)): ?>, <?php echo $company_title; ?><?php endif; ?><?php echo ($release->country != '') ? ', ' . $release->country : ''; ?>&nbsp;<a href="<?php echo JRoute::_('index.php?option=com_kinoarhiv&view=release&id=' . $release->movie_id . '&Itemid=' . $this->itemid); ?>#row-<?php echo $release->id; ?>" title="<?php echo JText::_('COM_KA_READMORE'); ?>" class="hasTooltip ui-icon-next"></a>
								</span>
							</div>
						<?php endforeach;
					endif; ?>
					<?php if ($this->item->mpaa == -1 && $this->item->age_restrict == -1 && $this->item->ua_rate == -1): ?>
					<?php else: ?>
						<div>
							<span class="f-col"><?php echo JText::_('COM_KA_RATES'); ?></span>
							<span class="s-col">
								<?php if ($this->item->mpaa > -1): ?>
									<div class="rating">
										<div id="mpaa" class="mpaa-icon hasTooltip" title="<?php echo JText::sprintf(JText::_('COM_KA_RATE_HELP'), JText::_('COM_KA_MPAA')); ?>">
											<strong><?php echo strtoupper($this->item->mpaa); ?></strong></div>
										<div class="mpaa-desc"><?php echo JText::_('COM_KA_MPAA_DESC'); ?></div>
									</div>
								<?php endif; ?>
								<?php if ($this->item->age_restrict > -1): ?>
									<div class="rating">
										<div id="rrate" class="rrate-icon hasTooltip" title="<?php echo JText::sprintf(JText::_('COM_KA_RATE_HELP'), JText::_('COM_KA_RU_RATE')); ?>">
											<strong><?php echo strtoupper($this->item->age_restrict); ?>+</strong></div>
										<div class="rrate-desc"><?php echo JText::_('COM_KA_RU_RATE_DESC'); ?></div>
									</div>
								<?php endif; ?>
								<?php if ($this->item->ua_rate > -1): ?>
									<div class="rating">
										<div id="ua-rate" class="uar-icon uar-icon-<?php echo (int) $this->item->ua_rate; ?> hasTooltip" title="<?php echo JText::sprintf(JText::_('COM_KA_RATE_HELP'), JText::_('COM_KA_UA_RATE')); ?>">
											&nbsp;</div>
										<div class="uarate-desc"><?php echo JText::_('COM_KA_UA_RATE_DESC'); ?></div>
									</div>
								<?php endif; ?>
							</span>
						</div>
					<?php endif; ?>
					<div>
						<span class="f-col"><?php echo JText::_('COM_KA_LENGTH'); ?></span>
						<span class="s-col"><?php echo $this->item->_hr_length; ?><?php echo JText::_('COM_KA_LENGTH_MINUTES'); ?>
							| <?php echo $this->item->_length; ?></span>
					</div>
					<?php if ($this->item->attribs->show_tags == 1 && isset($this->item->tags)):
						if (count($this->item->tags) > 0): ?>
							<div>
								<span class="f-col"><?php echo JText::_('JTAG'); ?></span>
								<span class="s-col">
									<?php foreach ($this->item->tags as $tag): ?>
										<a href="<?php echo JRoute::_('index.php?option=com_kinoarhiv&view=movies&filters[movies][tags]=' . $tag->tag_id . '&Itemid=' . $this->itemid); ?>" class="label label-info uk-badge tags" title="<?php echo $tag->tag_title; ?>"><?php echo $tag->tag_title; ?></a>
									<?php endforeach; ?>
								</span>
							</div>
						<?php endif; ?>
					<?php endif; ?>
				</div>
			</div>
		</div>

		<div class="clear"></div>
		<div class="buy">
			<p><?php echo $this->item->buy_urls; ?></p>
		</div>

		<?php if (($this->item->attribs->allow_votes == '' && $this->params->get('allow_votes') == 1) || $this->item->attribs->allow_votes == 1): ?>
			<?php if (!$this->user->get('guest') && $this->params->get('allow_votes') == 1): ?>
				<?php if ($this->params->get('ratings_show_local') == 1): ?>
					<div class="clear"></div>
					<div class="rate">
						<strong><?php echo JText::_('COM_KA_RATE'); ?></strong><br/>
						<select id="rate_field" autocomplete="off">
							<?php for ($i = 0, $n = (int) $this->params->get('vote_summ_num') + 1; $i < $n; $i++): ?>
								<option value="<?php echo $i; ?>"<?php echo ($i == round($this->item->rate_loc_label)) ? ' selected="selected"' : ''; ?>><?php echo $i; ?></option>
							<?php endfor; ?>
						</select>

						<div class="rateit" data-rateit-value="<?php echo round($this->item->rate_loc_label); ?>" data-rateit-backingfld="#rate_field"></div>
						&nbsp;<span><?php echo $this->item->rate_loc_label; ?></span>

						<div class="my_votes" style="<?php echo ($this->item->my_vote == 0) ? 'display: none;' : ''; ?>">
							<div class="my_vote"><?php echo JText::sprintf('COM_KA_RATE_MY', $this->item->my_vote, (int) $this->params->get('vote_summ_num')); ?>
								&nbsp;<span class="small">(<?php echo JHtml::_('date', $this->item->_datetime, JText::_('DATE_FORMAT_LC3')); ?>
									)</span></div>
							<a href="<?php echo JRoute::_('index.php?option=com_kinoarhiv&view=profile&page=votes&Itemid=' . $this->itemid); ?>" class="small"><?php echo JText::_('COM_KA_RATE_MY_ALL'); ?></a>
						</div>
					</div>
				<?php endif; ?>
			<?php else: ?>
				<?php if ($this->params->get('ratings_show_local') == 1): ?>
					<div class="clear"></div>
					<div class="rate">
						<strong><?php echo JText::_('COM_KA_RATE'); ?></strong><br/>

						<div class="rateit" data-rateit-value="<?php echo $this->item->rate_loc_c; ?>" data-rateit-min="0" data-rateit-max="<?php echo (int) $this->params->get('vote_summ_num'); ?>" data-rateit-ispreset="true" data-rateit-readonly="true"></div>
						&nbsp;<?php echo $this->item->rate_loc_label; ?>

						<?php if ($this->params->get('allow_votes') == 1): ?>
							<div><?php echo KAComponentHelper::showMsg(JText::sprintf(JText::_('COM_KA_VOTES_AUTHREQUIRED'), '<a href="' . JRoute::_('index.php?option=com_users&view=registration') . '">' . JText::_('COM_KA_REGISTER') . '</a>', '<a href="' . JRoute::_('index.php?option=com_users&view=login') . '">' . JText::_('COM_KA_LOGIN') . '</a>')); ?></div>
						<?php endif; ?>
					</div>
				<?php endif; ?>
			<?php endif; ?>
			<div class="clear"></div>
		<?php endif; ?>

		<?php if (isset($this->item->slides) && !empty($this->item->slides)):
			if (($this->item->attribs->slider == '' && $this->params->get('slider') == 1) || $this->item->attribs->slider == 1): ?>
				<div class="screenshot-slider">
					<ul class="bxslider">
						<?php foreach ($this->item->slides as $slide): ?>
							<li>
								<a href="<?php echo $slide->image; ?>" target="_blank" rel="slideGroup"><img src="<?php echo $slide->th_image; ?>" width="<?php echo $slide->th_image_width; ?>" height="<?php echo $slide->th_image_height; ?>"/></a>
							</li>
						<?php endforeach; ?>
					</ul>
				</div>
			<?php endif;
		endif; ?>

		<?php
		if ($total_trailers > 0 || $total_movies > 0)
		{
			$player_layout = ($this->params->get('player_type') == '-1') ? 'trailer' : 'trailer_' . $this->params->get('player_type');

			// Needed to avoid a bugs. Flowplayer redirect when SEF is turned on.
			if ($this->params->get('player_type') == 'flowplayer')
			{
				?>
				<div class="clear"></div>
				<div class="watch-buttons">
					<?php if ($total_trailers > 0): ?>
						<a href="#" class="btn btn-info watch-trailer"><span class="icon-play"></span> <?php echo JText::_('COM_KA_WATCH_TRAILER'); ?>
						</a>
					<?php endif; ?>
					<?php if ($total_movies > 0): ?>
						<a href="#" class="btn btn-info watch-movie"><span class="icon-play"></span> <?php echo JText::_('COM_KA_WATCH_MOVIE'); ?>
						</a>
					<?php endif; ?>
				</div>
				<?php
			}
			else
			{
				try
				{
					echo $this->loadTemplate($player_layout);
				}
				catch (Exception $e)
				{
					KAComponentHelper::eventLog(JText::sprintf('COM_KA_PLAYER_FOLDER_NOT_FOUND', $player_layout));
					echo $this->loadTemplate('trailer');
				}
			}
		}
		?>

		<?php if (!empty($this->item->plot)): ?>
			<div class="plot">
				<div class="ui-corner-all ui-widget-header header-small"><?php echo JText::_('COM_KA_PLOT'); ?></div>
				<div class="content" itemprop="description"><?php echo $this->item->plot; ?></div>
			</div>
		<?php endif; ?>

		<?php if (!empty($this->item->known)): ?>
			<div class="known">
				<div class="ui-corner-all ui-widget-header header-small"><?php echo JText::_('COM_KA_KNOWN'); ?></div>
				<div class="content"><?php echo $this->item->known; ?></div>
			</div>
		<?php endif; ?>

		<?php if (!empty($this->item->desc)): ?>
			<div class="ui-widget desc" id="desc">
				<div class="accordion-group">
					<div class="accordion-heading">
						<a class="accordion-toggle" data-toggle="collapse" data-parent="#desc" href="#showTechDescription"><?php echo JText::_('COM_KA_TECH'); ?></a>
					</div>
					<div id="showTechDescription" class="accordion-body collapse">
						<div class="accordion-inner"><p><?php echo $this->item->desc; ?></p></div>
					</div>
				</div>
			</div>
		<?php endif; ?>

		<?php if ($this->params->get('allow_movie_download') == 1): ?>
			<div class="urls">
				<div class="content"><p><?php echo $this->item->urls; ?></p></div>
			</div>
		<?php endif; ?>

		<?php echo $this->item->event->afterDisplayContent; ?>
		<?php if ($this->params->get('show_reviews') == 1):
			echo $this->loadTemplate('reviews');
		endif; ?>
	</article>
</div>
